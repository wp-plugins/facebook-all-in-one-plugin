<?php
/**
 * Handles all data - both Facebook requests and local WP database reuests.
 */
class wdfbaio_Model {
	var $fb;
	var $db;
	var $data;

	function __construct () {
		global $wpdb;
		$this->data =& wdfbaio_OptionsRegistry::get_instance();
		$this->db = $wpdb;
		$this->fb = new Facebook(array(
			'appId' => $this->data->get_option('wdfbaio_api', 'app_key'),
			'secret' => $this->data->get_option('wdfbaio_api', 'secret_key'),
			'cookie' => true,
		));
	}

	function wdfbaio_Model () {
		$this->__construct();
	}

	/**
	 * Returns all blogs on the current site.
	 */
	function get_blog_ids () {
		global $current_blog;
		$site_id = 0;
		if ($current_blog) {
			$site_id = $current_blog->site_id;
		}
		$sql = "SELECT blog_id FROM " . $this->db->blogs . " WHERE site_id={$site_id} AND public='1' AND archived= '0' AND spam='0' AND deleted='0' ORDER BY registered DESC";
		return $this->db->get_results($sql, ARRAY_A);
	}

	/**
	 * Logs the user out of the site and Facebook.
	 */
	function wp_logout ($redirect=false) {
		setcookie('fbs_' . $this->fb->getAppId(), '', time()-100, '/', COOKIE_DOMAIN); // Yay for retardness in FB SDK
		if ($redirect) wp_redirect($redirect);
		wp_logout();
		wp_set_current_user(0);
	}

	/**
	 * Lists registered BuddyPress profile fields.
	 */
	function get_bp_xprofile_fields () {
		if (!defined('BP_VERSION')) return true;
		$sql = "SELECT id, name FROM " . $this->db->base_prefix . "bp_xprofile_fields";
		return $this->db->get_results($sql, ARRAY_A);
	}

	/**
	 * Create/update the BuddyPress profile field.
	 */
	function set_bp_xprofile_field ($field_id, $user_id, $data) {
		if (!defined('BP_VERSION')) return true;

		$field_id = (int)$field_id;
		$user_id = (int)$user_id;
		if (!$field_id || !$user_id) return false;

		if (is_array($data)) $data = $data['name']; // For complex FB fields that return JSON objects
		if (!$data) return false; // Don't waste cycles if we don't need to

		$sql = "SELECT id FROM " . $this->db->base_prefix . "bp_xprofile_data WHERE field_id={$field_id} AND user_id={$user_id}";
		$id = $this->db->get_var($sql);

		if ($id) {
			$sql = "UPDATE " . $this->db->base_prefix . "bp_xprofile_data SET data='" . $data . "' WHERE id={$id}";
		} else {
			$sql = "INSERT INTO " . $this->db->base_prefix . "bp_xprofile_data (field_id, user_id, value, last_updated) VALUES (" .
				(int)$field_id . ', ' . (int)$user_id . ", '" . $data . "', '" . date('Y-m-d H:i:s') . "')";
		}
		return $this->db->query($sql);
	}

	/**
	 * Gets FB profile image and sets it as BuddyPress avatar.
	 */
	function set_fb_image_as_bp_avatar ($user_id, $me) {
		if (!defined('BP_VERSION')) return true;
		if (!defined('BP_AVATAR_UPLOAD_PATH')) return true;
		if (!$me || !@$me['id']) return false;

		$fb_uid = $me['id'];

		$path = BP_AVATAR_UPLOAD_PATH . '/avatars/' . $user_id;
		if (!realpath($path)) @wp_mkdir_p($path);

		// Get FB picture
		$fb_img = file_get_contents("http://graph.facebook.com/{$fb_uid}/picture?type=large");
		$filename = md5($fb_uid);
		$filepath = "{$path}/{$filename}";
		file_put_contents($filepath, $fb_img);

		// Determine the right extension
		$info = getimagesize($filepath);
		$extension = false;

		switch ($info[2]) {
			case IMAGETYPE_GIF:
				$extension = 'gif';
				break;
			case IMAGETYPE_JPEG:
				$extension = 'jpg';
				break;
			case IMAGETYPE_PNG:
				$extension = 'png';
				break;
		}
		// Unknown file type, clean up
		if (!$extension) {
			@unlink($filepath);
			return false;
		}

		// Clear old avatars
		$imgs = glob($path . '/*.{gif,png,jpg}', GLOB_BRACE);
		if (is_array($imgs)) foreach ($imgs as $old) {
			@unlink($old);
		}

		// Create new avatar
		copy($filepath, "{$filepath}-bpthumb.{$extension}");
		copy($filepath, "{$filepath}-bpfull.{$extension}");
		@unlink($filepath);
		return true;
	}

	function get_all_user_tokens () {
		$sql = "SELECT * FROM " . $this->db->base_prefix . "usermeta WHERE meta_key='wdfbaio_api_accounts'";
		return $this->db->get_results($sql, ARRAY_A);
	}

	function comment_already_imported ($fb_cid) {
		if (!$fb_cid) return false;
		$key = '%s:13:"fb_comment_id";s:' . strlen($fb_cid) . ':"' . $fb_cid . '";%';
		$sql = "SELECT meta_id FROM " . $this->db->prefix . "commentmeta WHERE meta_value LIKE '{$key}'";
		return $this->db->get_var($sql);
	}

	function get_wp_user_from_fb () {
		$fb_user_id = $this->fb->getUser();

		$sql = "SELECT user_id FROM " . $this->db->base_prefix . "usermeta WHERE meta_key='wdfbaio_fb_uid' AND meta_value=%s";
		$res = $this->db->get_results($this->db->prepare($sql, $fb_user_id), ARRAY_A);
		if ($res) return $res[0]['user_id'];

		// User not yet linked. Try finding her by email.
		$me = false;
		try {
			$me = $this->fb->api('/me');
		} catch (Exception $e) {
			return false;
		}
		if (!$me || !isset($me['email'])) return false;

		$sql = "SELECT ID FROM " . $this->db->base_prefix . "users WHERE user_email=%s";
		$res = $this->db->get_results($this->db->prepare($sql, $me['email']), ARRAY_A);

		if (!$res) return false;

		return $this->map_fb_to_wp_user($res[0]['ID']);
	}

	function get_fb_user_from_wp ($wp_uid) {
		$fb_uid = get_user_meta($wp_uid, 'wdfbaio_fb_uid', true);
		return $fb_uid;
	}

	function map_fb_to_wp_user ($wp_uid) {
		if (!$wp_uid) return false;
		update_usermeta($wp_uid, 'wdfbaio_fb_uid', $this->fb->getUser());
		return $wp_uid;
	}

	function map_fb_to_current_wp_user () {
		$user = wp_get_current_user();
		$id = $user->ID;
		$this->map_fb_to_wp_user($id);

	}

	function register_fb_user () {
		$uid = $this->get_wp_user_from_fb();
		if ($uid) return $this->map_fb_to_wp_user($uid);

		return $this->create_new_wp_user_from_fb();
	}

	function delete_wp_user ($uid) {
		$uid = (int)$uid;
		if (!$uid) return false;
		$this->db->query("DELETE FROM {$this->db->users} WHERE ID={$uid}");
		$this->db->query("DELETE FROM {$this->db->usermeta} WHERE user_id={$uid}");
	}

	function create_new_wp_user_from_fb () {
		try {
			$me = $this->fb->api('/me');
		} catch (Exception $e) {
			$me = $this->fb->registration;
			$me['id'] = $this->fb->user_id;
		}
		if (!$me) return false;

		$username = $this->_create_username_from_fb_response($me);
		$password = wp_generate_password(12, false);
		$user_id = wp_create_user($username, $password, $me['email']);

		if (defined('BP_VERSION')) $this->populate_bp_fields_from_fb($user_id, $me); // BuddyPress
		else $this->populate_wp_fields_from_fb($user_id, $me); // WordPress

		return $this->map_fb_to_wp_user($user_id);
	}

	function populate_bp_fields_from_fb ($user_id, $me=false) {
		if (!defined('BP_VERSION')) return true;
		if (!$me) {
			try {
				$me = $this->fb->api('/me');
			} catch (Exception $e) {
				return false;
			}
			if (!$me) return false;
		}

		$this->set_fb_image_as_bp_avatar($user_id, $me);

		$bp_fields = $this->get_bp_xprofile_fields();
		if (is_array($bp_fields)) foreach ($bp_fields as $bpf) {
			$fb_value = $this->data->get_option('wdfbaio_connect', 'buddypress_registration_fields_' . $bpf['id']);
			if ($fb_value && @$me[$fb_value]) $this->set_bp_xprofile_field($bpf['id'], $user_id, @$me[$fb_value]);
		}
		return true;
	}

	function populate_wp_fields_from_fb ($user_id, $me=false) {
		if (!$me) {
			try {
				$me = $this->fb->api('/me');
			} catch (Exception $e) {
				return false;
			}
			if (!$me) return false;
		}
		$wp_mappings = $this->data->get_option('wdfbaio_connect', 'wordpress_registration_fields');

		if (is_array($wp_mappings)) foreach($wp_mappings as $map) {
			if (!$map['wp'] || !$map['fb'] || !@$me[$map['fb']]) continue;
			if (is_array(@$me[$map['fb']]) && isset($me[$map['fb']]['name'])) $data = @$me[$map['fb']]['name'];
			else if (is_array(@$me[$map['fb']]) && isset($me[$map['fb']][0])) $data = join(', ', array_map(create_function('$m', 'return $m["name"];'), $me[$map['fb']]));
			else $data = @$me[$map['fb']];
			update_usermeta($user_id, $map['wp'], $data);
		}

		return true;
	}

	function get_current_user_fb_id () {
		$fb_uid = $this->fb->getUser();
		if ($fb_uid) return $fb_uid; // User is logged into FB, use that

		$user = wp_get_current_user();
		if (!$user || !$user->ID) return false; // User not logged into WP, skip

		$fb_uid = get_user_meta($user->ID, 'wdfbaio_fb_uid', true);
		return $fb_uid;
	}

	function get_pages_tokens () {
		$fid = $this->get_current_user_fb_id();
		try {
			$ret = $this->fb->api('/' . $fid . '/accounts/');
		} catch (Exception $e) {
			return false;
		}
		return $ret;
	}

	function post_on_facebook ($type, $fid, $post, $as_page=false) {
		$type = $type ? $type : 'feed';
		$fid = $fid ? $fid : $this->get_current_user_fb_id();

		$tokens = $this->data->get_option('wdfbaio_api', 'auth_tokens');
		$post['auth_token'] = $tokens[$fid];
		if ($as_page) $post['access_token'] = $tokens[$fid];

		try {
			$ret = $this->fb->api('/' . $fid . '/' . $type . '/', 'POST', $post);
		} catch (Exception $e) {
			return false;
		}
		return $ret;
	}

	function get_events_for ($for) {
		if (!$for) return false;

		$tokens = $this->data->get_option('wdfbaio_api', 'auth_tokens');
		$token = $tokens[$for];

		try {
			$res = $this->fb->api('/' . $for . '/events/?auth_token=' . $token);
		} catch (Exception $e) {
			return false;
		}
		return $res;
	}

	function get_albums_for ($for) {
		if (!$for) return false;

		$tokens = $this->data->get_option('wdfbaio_api', 'auth_tokens');
		$token = $tokens[$for];

		try {
			$res = $this->fb->api('/' . $for . '/albums/?auth_token=' . $token);
		} catch (Exception $e) {
			return false;
		}
		return $res;
	}

	function get_current_albums () {
		$user = wp_get_current_user();
		$fb_accounts = get_user_meta($user->ID, 'wdfbaio_api_accounts', true);
		$fb_accounts = isset($fb_accounts['auth_accounts']) ? $fb_accounts['auth_accounts'] : false;
		if (!$fb_accounts) return false;
		$albums = array('data'=>array());
		foreach ($fb_accounts as $fid => $label) {
			$res = $this->get_albums_for($fid);
			if (!$res) continue;
			$albums['data'] = array_merge($albums['data'], $res['data']);
		}
		return $albums ? $albums : false;
	}

	function get_album_photos ($aid, $limit=false) {
		if (!$aid) return false;
		$limit = $limit ? '?limit=' . $limit : '';
		try {
			$res = $this->fb->api('/' . $aid . '/photos/' . $limit);
		} catch (Exception $e) {
			return false;
		}
		return $res;
	}

	function get_feed_for ($uid, $limit=false) {
		if (!$uid) return false;
		$limit = $limit ? '?limit=' . $limit : '';

		$tokens = $this->data->get_option('wdfbaio_api', 'auth_tokens');
		$token = $tokens[$uid];

		$req = $limit ? $limit . '&auth_token=' . $token : '?auth_token=' . $token;

		try {
			$res = $this->fb->api('/' . $uid . '/feed/' . $req);
		} catch (Exception $e) {
			return false;
		}
		return $res;
	}

	function get_item_comments ($for) {
		$uid = $this->get_current_user_fb_id();

		$tokens = $this->data->get_option('wdfbaio_api', 'auth_tokens');
		$token = $tokens[$uid];

		try {
			$res = $this->fb->api('/' . $for . '/comments/?auth_token=' . $token);
		} catch (Exception $e) {
			return false;
		}
		return $res;
	}

	function _create_username_from_fb_response ($me) {
		if (@$me['first_name'] && @$me['last_name']) {
			$name = preg_replace('/[^a-zA-Z0-9_]+/', '', ucfirst($me['first_name']) . '_' . ucfirst($me['last_name']));
		} else if (isset($me['name'])) {
			$name = $me['name'];
		} else {
			list($name, $rest) = explode('@', $me['email']);
		}
		$username = strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '', $name));
		while (username_exists($username)) {
			$username .= rand();
		}
		return $username;
	}
}