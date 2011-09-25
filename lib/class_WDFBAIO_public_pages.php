<?php
class wdfbaio_PublicPages {

	var $data;
	var $replacer;
	var $fb;

	function __construct () {
		$this->data =& wdfbaio_OptionsRegistry::get_instance();
		$this->model = new wdfbaio_Model;
		$this->replacer = new wdfbaio_MarkerReplacer;
	}

	function wdfbaio_PublicPages () {
		$this->__construct();
	}

	/**
	 * Main entry point.
	 *
	 * @static
	 */
	function serve () {
		$me = new wdfbaio_PublicPages;
		$me->add_hooks();
	}

	function js_load_scripts () {
		wp_enqueue_script('jquery');
		$locale = preg_replace('/-/', '_', get_locale());
		wp_enqueue_script('facebook-all', 'http://connect.facebook.net/' . $locale . '/all.js');
	}

	function js_inject_fb_login_script () {
		echo '<script type="text/javascript" src="' . wdfbaio_PLUGIN_URL . '/js/wdfbaio_facebook_login.js"></script>';
	}
	function js_setup_ajaxurl () {
		printf('<script type="text/javascript">var _wdfbaio_ajaxurl="%s";</script>', admin_url('admin-ajax.php'));
		printf(
			'<script type="text/javascript">var _wdfbaio_root_url="%s";</script>',
			wdfbaio_PLUGIN_URL
		);
	}
	function css_load_styles () {
		wp_enqueue_style('wdfbaio_style', wdfbaio_PLUGIN_URL . '/css/wdfbaio.css');
	}

	/**
	 * Inject Facebook button into post content.
	 * This is triggered only for automatic injection.
	 * Adds shortcode in proper place, and lets replacer do its job later on.
	 */
	function inject_facebook_button ($body) {
		if (
			(is_home() && !$this->data->get_option('wdfbaio_button', 'show_on_front_page'))
			||
			(!is_home() && !is_singular())
		) return $body;

		$position = $this->data->get_option('wdfbaio_button', 'button_position');
		if ('top' == $position || 'both' == $position) {
			$body = $this->replacer->get_button_tag('like_button') . " " . $body;
		}
		if ('bottom' == $position || 'both' == $position) {
			$body .= " " . $this->replacer->get_button_tag('like_button');
		}
		return $body;
	}

	/**
	 * Inject OpenGraph info in the HEAD
	 */
	function inject_opengraph_info () {
		$title = $url = $site_name = $description = $id = $image = false;
		if (is_singular()) {
			if (have_posts()) while (have_posts()) {
				the_post();
				$title = get_the_title($post->post_title);
				$url = get_permalink();
				$site_name = get_option('blogname');
				$content = function_exists('load_membership_plugins') ? strip_shortcodes(get_the_content()) : do_shortcode(get_the_content());
				$text = htmlspecialchars(strip_tags($content), ENT_QUOTES);
				if (strlen($text) > 250) $description = substr($text, 0, 250) . "&hellip;";
				else $description = $text;
				$id = get_the_ID();
			}
		} else {
			$title = get_option('blogname');
			$url = home_url('/');
			$site_name = get_option('blogname');
			$description = get_option('blogdescription');
		}
		$image = wdfbaio_get_og_image($id);
		if (is_singular()) {
			echo "<meta property='og:type' content='article' />\n";
		} else {
			echo "<meta property='og:type' content='website' />\n";
		}
		if ($title) echo "<meta property='og:title' content='{$title}' />\n";
		if ($url) echo "<meta property='og:url' content='{$url}' />\n";
		if ($site_name) echo "<meta property='og:site_name' content='{$site_name}' />\n";
		if ($description) echo "<meta property='og:description' content='{$description}' />\n";
		if ($image) echo "<meta property='og:image' content='{$image}' />\n";
	}

	function inject_fb_init_js () {
		echo "<script>
         FB.init({
            appId: '" . $this->data->get_option('wdfbaio_api', 'app_key') . "',
            status: true,
            cookie: true,
            xfbml: true
         });
      </script>";
	}

	/**
	 * Injects Facebook root div needed for XFBML near page footer.
	 */
	function inject_fb_root_div () {
		echo "<div id='fb-root'></div>";
	}

	function inject_fb_login () {
		echo '<p><fb:login-button perms="' . wdfbaio_EXTENDED_PERMISSIONS . '" redirect-url="' . admin_url() . '">' . __("Login with Facebook", 'wdfbaio') . '</fb:login-button></p>';
		if (isset($_GET['loggedout'])) {
			$this->model->fb->setSession(null);
			echo '<script type="text/javascript">(function ($) { $(function () { FB.logout(); }) })(jQuery);</script>';
		}
	}

	function inject_fb_login_for_bp () {
		echo '<p><fb:login-button perms="' . wdfbaio_EXTENDED_PERMISSIONS . '" redirect-url="' . home_url() . '">' . __("Login with Facebook", 'wdfbaio') . '</fb:login-button></p>';
	}

	function inject_fb_comments_admin_og () {
		$app_id = $this->data->get_option('wdfbaio_api', 'app_key');
		if (!$app_id) return false;
		echo "<meta property='fb:app_id' content='{$app_id}' />\n";
	}

	function inject_fb_comments ($defaults) {
		if (!comments_open() && !$this->data->get_option('wdfbaio_comments', 'override_wp_comments_settings')) return $defaults;

		$link = get_permalink();
		$xid = rawurlencode($link);

		$width = (int)$this->data->get_option('wdfbaio_comments', 'fb_comments_width');
		$width = $width ? $width : '550';

		$num_posts = (int)$this->data->get_option('wdfbaio_comments', 'fb_comments_number');

		$reverse = $this->data->get_option('wdfbaio_comments', 'fb_comments_reverse') ? 'true' : 'false';

		echo "<fb:comments href='{$link}' " .
			"xid='{$xid}' " .
			"num_posts='{$num_posts}' " .
			"width='{$width}px' " .
			"reverse='{$reverse}' " .
			"publish_feed='true'></fb:comments>";
		return $defaults;
	}

	function handle_logout () {
		if (isset($_GET['action']) && 'logout' == $_GET['action']) {
			$next = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : home_url();
			$redirect = $this->model->fb->getLogoutUrl(array('next'=>$next));
			$this->model->fb->setSession(null);
			$this->model->wp_logout($redirect);
			//wp_redirect ($redirect);
			exit();
		}
	}

	/**
	 * This happens only if allow_facebook_registration is true.
	 */
	function handle_fb_session_state () {
		$session = $this->model->fb->getSession();

		// User logs out
		if ($session && isset($_GET['action']) && 'logout' == $_GET['action']) {
			$redirect = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : home_url();
			$fb_redirect = $this->model->fb->getLogoutUrl(array('next'=>$redirect));
			$this->model->fb->setSession(null);
			$this->model->wp_logout($fb_redirect);
			//wp_redirect($redirect);
			exit();
		}

		if ($session) {
			$user_id = $this->model->get_wp_user_from_fb();
			if (!$user_id) $user_id = $this->model->map_fb_to_current_wp_user();
			if ($user_id) {
				$user = get_userdata($user_id);
				wp_set_current_user($user->ID, $user->user_login);
				@wp_set_auth_cookie($user->ID, true); // Logged in with Facebook, yay
				do_action('wp_login', $user->user_login);

				// BuddyPress :/
				if (function_exists('bp_core_setup_globals') && defined('BP_VERSION')) bp_core_setup_globals();
			}
		}
	}

	function clear_auth_cookies_on_logout () {
		if (isset($_GET['loggedout'])) wp_clear_auth_cookie();
	}

	function get_commenter_avatar ($old, $comment, $size) {
		if (!is_object($comment)) return $old;
		$meta = get_comment_meta($comment->comment_ID, 'wdfbaio_comment', true);
		if (!$meta) return $old;

		return '<img src="http://graph.facebook.com/' . $meta['fb_author_id'] . '/picture" class="avatar avatar-' . $size . ' photo" height="' . $size . '" width="' . $size . '" />';
	}

	function get_fb_avatar ($avatar, $id_or_email) {
		$fb_uid = false;
		$wp_uid = false;
		if (is_object($id_or_email)) return $avatar;
		if (is_numeric($id_or_email)) {
			$wp_uid = (int)$id_or_email;
		} else if (is_email($id_or_email)) {
			$user = get_user_by('email', $id_or_email);
			if ($user) $wp_uid = $user->ID;
		} else return $avatar;
		if (!$wp_uid) return $avatar;

		$fb_uid = $this->model->get_fb_user_from_wp($wp_uid);
		if (!$fb_uid) return $avatar;

		return "<img src='http://graph.facebook.com/{$fb_uid}/picture' />";
	}

	function inject_optional_facebook_registration_button () {
		$url = add_query_arg('fb_registration_page', 1);
		echo '<p><a class="wdfbaio_register_button" href="' . $url . '"><span>' . __('Register with Facebook', 'wdfbaio') . '</span></a></p>';
	}

	function process_facebook_registration () {
		// Should we even be here?
		if ($this->data->get_option('wdfbaio_connect', 'force_facebook_registration')) {
			global $pagenow;
			if ('wp-signup.php' == $pagenow) $_GET['fb_registration_page'] = 1;
			if ('wp-login.php' == $pagenow && isset($_GET['action']) && 'register' == $_GET['action']) $_GET['fb_registration_page'] = 1;

			if (defined('BP_VERSION')) { // BuddyPress :/
				global $bp;
				if ('register' == $bp->current_component) $_GET['fb_registration_page'] = 1;
			}
		}
		if (!isset($_GET['fb_registration_page']) && !isset($_GET['fb_register'])) return false;

		// Are registrations allowed?
		$wp_grant_blog = false;
		if (is_multisite()) {
			$reg = get_site_option('registration');
			if ('all' == $reg) $wp_grant_blog = true;
			else if ('user' != $reg) return false;
		} else {
			if (!(int)get_option('users_can_register')) return false;
		}

		// We're here, so registration is allowed
		$registration_success = false;
		$errors = array();
		// Process registration data
		if (isset($_GET['fb_register'])) {
			list($encoded_sig, $payload) = explode('.', $_REQUEST['signed_request'], 2);
			$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

			// We're good here
			if ($data['registration']) {
				$user_id = $this->model->register_fb_user();
				if ($user_id && $wp_grant_blog) {
					$new_blog_title = '';
					$new_blog_url = '';
					remove_filter('wpmu_validate_blog_signup', 'signup_nonce_check');

					// Set up proper blog name
					$blog_domain = preg_replace('/[^a-z0-9]/', '', strtolower($data['registration']['blog_domain']));
					// All numbers? Fix that
					if (preg_match('/^[0-9]$/', $blog_domain)) {
						$letters = shuffle(range('a', 'z'));
						$blog_domain .= $letters[0];
					}
					// Set up proper title
					$blog_title = $data['registration']['blog_title'];
					$blog_title = $blog_title ? $blog_title : __("My new blog", 'wdfbaio');

					$result = wpmu_validate_blog_signup($blog_domain, $blog_title);
					$iteration = 0;
					// Blog domain failed, try making it unique
					while ($result['errors']->get_error_code()) {
						if ($iteration > 10) break; // We should really gtfo
						$blog_domain .= rand();
						$result = wpmu_validate_blog_signup($blog_domain, $blog_title);
						$iteration++;
					}

					if (!$result['errors']->get_error_code()) {
						global $current_site;
						$blog_meta = array('public' => 1);
						$blog_id = wpmu_create_blog($result['domain'], $result['path'], $result['blog_title'], $user_id, $blog_meta, $current_site->id);
						$new_blog_title = $result['blog_title'];
						$new_blog_url = get_blog_option($blog_id, 'siteurl');
						$registration_success = true;
					} else {
						// Remove user
						$this->model->delete_wp_user($user_id);
						$errors = array_merge($errors, array_values($result['errors']->errors));
					}
				} else if ($user_id) {
					$registration_success = true;
				} else {
					$errors[] = __('Could not register such user', 'wdfbaio');
				}
			}
		}

		$page = (isset($_GET['fb_register']) && $registration_success) ? wdfbaio_PLUGIN_BASE_DIR . '/lib/forms/registration_page_success.php' : wdfbaio_PLUGIN_BASE_DIR . '/lib/forms/registration_page.php';
		require_once $page;
		exit();
	}

	/**
	 * Hooks to appropriate places and adds stuff as needed.
	 *
	 * @access private
	 */
	function add_hooks () {
		// Step1a: Add script and style dependencies
		add_action('wp_print_scripts', array($this, 'js_load_scripts'));
		add_action('wp_print_styles', array($this, 'css_load_styles'));
		add_action('wp_head', array($this, 'js_setup_ajaxurl'));

		add_action('get_footer', array($this, 'inject_fb_root_div'));
		add_action('get_footer', array($this, 'inject_fb_init_js'));

		// Automatic Facebook button
		if ('manual' != $this->data->get_option('wdfbaio_button', 'button_position')) {
			add_filter('the_content', array($this, 'inject_facebook_button'), 1); // Do this VERY early in content processing
		}

		// OpenGraph
		if ($this->data->get_option('wdfbaio_opengraph', 'use_opengraph')) {
			add_action('wp_head', array($this, 'inject_opengraph_info'));
		}

		// Connect
		if ($this->data->get_option('wdfbaio_connect', 'allow_facebook_registration')) {
			add_action('init', array($this, 'handle_logout'));

			add_filter('get_avatar', array($this, 'get_fb_avatar'), 10, 2);

			if(!defined('BP_VERSION')) add_action('wp_loaded', array($this, 'handle_fb_session_state'));
			add_action('login_head', array($this, 'js_inject_fb_login_script'));
			add_action('login_head', array($this, 'js_setup_ajaxurl'));
			add_action('login_form', array($this, 'inject_fb_login'));
			add_action('login_footer', array($this, 'inject_fb_root_div'));
			add_action('login_footer', array($this, 'inject_fb_init_js'));
			add_action('login_form_login', array($this, 'clear_auth_cookies_on_logout'));

			// BuddyPress
			if (defined('BP_VERSION')) {
				add_action('bp_setup_globals', array($this, 'handle_fb_session_state'));
				add_action('bp_before_profile_edit_content', 'wdfbaio_dashboard_profile_widget');
				add_action('bp_before_sidebar_login_form', array($this, 'inject_fb_login_for_bp'));
				add_action('wp_head', array($this, 'js_inject_fb_login_script'));

				// Have to kill BuddyPress redirection, or our registration doesn't work
				remove_action('wp', 'bp_core_wpsignup_redirect');
				remove_action('init', 'bp_core_wpsignup_redirect');
			}

			// New login/register
			// First, do optionals
			if (is_multisite()) add_action('before_signup_form', array($this, 'inject_optional_facebook_registration_button'));
			else if (isset($_GET['action']) && 'register' == $_GET['action']) {
				add_action('login_head', create_function('', 'echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"' . wdfbaio_PLUGIN_URL . '/css/wdfbaio.css\" />";'));
				add_action('login_message', array($this, 'inject_optional_facebook_registration_button'));
			}

			// BuddyPress
			add_filter('bp_before_register_page', array($this, 'inject_optional_facebook_registration_button')); // BuddyPress

			// Jack the signup
			add_action('init', array($this, 'process_facebook_registration'), 20);
		}

		// Comments
		if ($this->data->get_option('wdfbaio_comments', 'use_fb_comments')) {
			$hook = $this->data->get_option('wdfbaio_comments', 'fb_comments_custom_hook');
			add_action('wp_head', array($this, 'inject_fb_comments_admin_og'));
			if (!$hook) {
				add_filter('comment_form_defaults', array($this, 'inject_fb_comments'));
				add_filter('bp_before_blog_comment_list', array($this, 'inject_fb_comments')); // BuddyPress :/
			} else {
				add_action($hook, array($this, 'inject_fb_comments'));
			}
		}
		add_filter('get_avatar', array($this, 'get_commenter_avatar'), 10, 3);

		$rpl = $this->replacer->register();
	}
}