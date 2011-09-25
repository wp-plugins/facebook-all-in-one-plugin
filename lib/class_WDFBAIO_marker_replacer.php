<?php
/**
 * Handles shortcodes.
 */
class wdfbaio_MarkerReplacer {

	var $data;
	var $model;
	var $buttons = array (
		'like_button' => 'wdfbaio_like_button',
		'events' => 'wdfbaio_events',
		'connect' => 'wdfbaio_connect'
	);

	function __construct () {
		$this->model = new wdfbaio_Model;
		$this->data =& wdfbaio_OptionsRegistry::get_instance();
	}

	function wdfbaio_MarkerReplacer () {
		$this->__construct();
	}

	function get_button_tag ($b) {
		if (!isset($this->buttons[$b])) return '';
		return '[' . $this->buttons[$b] . ']';
	}

	function process_connect_code ($atts, $content='') {
		if (!$this->data->get_option('wdfbaio_connect', 'allow_facebook_registration')) return $content;
		$content = $content ? $content : __('Log in with Facebook', 'wdfbaio');
		if (!class_exists('wdfbaio_WidgetConnect')) {
			echo '<script type="text/javascript" src="' . wdfbaio_PLUGIN_URL . '/js/wdfbaio_facebook_login.js"></script>';
		}
		$user = wp_get_current_user();
		$html = '';
		if (!$user->ID) {
			$html = '<p><fb:login-button perms="' . wdfbaio_EXTENDED_PERMISSIONS . '" redirect-url="' . home_url() . '">' . $content . '</fb:login-button></p>';
		} else {
			$logout = site_url('wp-login.php?action=logout&redirect_to=' . rawurlencode(home_url()));
			$html .= get_avatar($user->ID, 32);
			$html .= "<br /><a href='{$logout}'>Log out</a>";
		}
		return $html;
	}

	function process_like_button_code ($atts, $content='') {
		$allow = $this->data->get_option('wdfbaio_button', 'allow_facebook_button');
		if (!$allow) return '';

		$atts = shortcode_atts(array(
			'forced' => false,
		), $atts);
		$forced = ($atts['forced'] && 'no' != $atts['forced']) ? true : false;

		$in_types = $this->data->get_option('wdfbaio_button', 'not_in_post_types');
		if (@in_array(get_post_type(), $in_types) && !$forced) return '';

		$send = $this->data->get_option('wdfbaio_button', 'show_send_button');
		$layout = $this->data->get_option('wdfbaio_button', 'button_appearance');
		$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if (is_home() && $this->data->get_option('wdfbaio_button', 'show_on_front_page')) {
			$tmp_url = get_permalink();
			$url = $tmp_url ? $tmp_url : $url;
			$url = rawurlencode($url);
			$height = ("box_count" == $layout) ? 60 : 25;
			return "<div class='wdfbaio_like_button'><iframe src='http://www.facebook.com/plugins/like.php?&amp;href={$url}&amp;send=false&amp;layout={$layout}&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height={$height}' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:450px; height:{$height}px;' allowTransparency='true'></iframe></div>";
		}

		return '<div class="wdfbaio_like_button"><fb:like href="http://' . $url . '" send="' . ($send ? 'true' : 'false') . '" layout="' . $layout . '" width="450" show_faces="true" font=""></fb:like></div>';
	}

	function process_events_code ($atts, $content='') {
		$post_id = get_the_ID();
		if (!$post_id) return '';

		$atts = shortcode_atts(array(
			'for' => false,
			'starting_from' => false,
			'only_future' => false,
			'show_image' => "true",
			'show_location' => "true",
			'show_start_date' => "true",
			'show_end_date' => "true",
		), $atts);

		if (!$atts['for']) return ''; // We don't know whose events to show

		// Attempt to fetch the freshest events
		// Update cache if we can
		$new_events = $this->model->get_events_for($atts['for']);
		if(!empty($new_events['data'])) {
			$events = $new_events['data'];
			update_post_meta($post_id, 'wdfbaio_events', $events);
		} else {
			$events = get_post_meta($post_id, 'wdfbaio_events');
			$events = $events[0];
		}

		if (!is_array($events)) return $content;

		$show_image = ("true" == $atts['show_image']) ? true : false;
		$show_location = ("true" == $atts['show_location']) ? true : false;
		$show_start_date = ("true" == $atts['show_start_date']) ? true : false;
		$show_end_date = ("true" == $atts['show_end_date']) ? true : false;
		$timestamp_format = get_option('date_format') . ' ' . get_option('time_format');

		$date_threshold = $atts['starting_from'] ? strtotime($atts['starting_from']) : false;
		if ($atts['only_future'] && 'false' != $atts['only_future']) {
			$now = time();
			$date_threshold = ($date_threshold && $date_threshold > $now) ? $date_threshold : $now;
		}

		ob_start();
		foreach ($events as $event) {
			if ($date_threshold > strtotime($event['start_time'])) continue;
			include (wdfbaio_PLUGIN_BASE_DIR . '/lib/forms/event_item.php');
		}
		$ret = ob_get_contents();
		ob_end_clean();

		return "<div><ul>{$ret}</ul></div>";
	}

	/**
	 * Registers shortcode handlers.
	 */
	function register () {
		foreach ($this->buttons as $key=>$shortcode) {
			//var_export("process_{$key}_code");
			add_shortcode($shortcode, array($this, "process_{$key}_code"));
		}
	}
}