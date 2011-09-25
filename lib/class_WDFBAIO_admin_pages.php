<?php
/**
 * Handles all admin side stuff.
 */
class wdfbaio_AdminPages {

	/**
	 * Main entry point.
	 *
	 * @static
	 */
	function serve () {
		$me = new wdfbaio_AdminPages;
		$me->data =& wdfbaio_OptionsRegistry::get_instance();
		$me->model = new wdfbaio_Model;
		$me->add_hooks();
	}

	/**
	 * Registers settings and form handlers/elements for sitewide administration.
	 *
	 * @access private
	 */
	function register_site_settings () {
		$form = new wdfbaio_AdminFormRenderer;

		register_setting('wdfbaio', 'wdfbaio_api');
		add_settings_section('wdfbaio_api', __('Facebook API', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_api_info', __('Before we begin', 'wdfbaio'), array($form, 'api_info'), 'wdfbaio_options_page', 'wdfbaio_api');
		add_settings_field('wdfbaio_app_key', __('Application ID', 'wdfbaio'), array($form, 'create_app_key_box'), 'wdfbaio_options_page', 'wdfbaio_api');
		add_settings_field('wdfbaio_api_key', __('API key', 'wdfbaio'), array($form, 'create_api_key_box'), 'wdfbaio_options_page', 'wdfbaio_api');
		add_settings_field('wdfbaio_secret_key', __('Secret key', 'wdfbaio'), array($form, 'create_secret_key_box'), 'wdfbaio_options_page', 'wdfbaio_api');
		add_settings_field('wdfbaio_allow_propagation', __('Allow sub-sites to use these credentials', 'wdfbaio'), array($form, 'create_allow_propagation_box'), 'wdfbaio_options_page', 'wdfbaio_api');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_api');

		add_settings_section('wdfbaio_grant', __('Grant extended permissions', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_api_permissions', __('Allowing permissions', 'wdfbaio'), array($form, 'api_permissions'), 'wdfbaio_options_page', 'wdfbaio_grant');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_grant');

		register_setting('wdfbaio', 'wdfbaio_connect');
		add_settings_section('wdfbaio_connect', __('Facebook Connect', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_allow_facebook_registration', __('Allow users to register with Facebook', 'wdfbaio'), array($form, 'create_allow_facebook_registration_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
		add_settings_field('wdfbaio_force_facebook_registration', __('Force users to register with Facebook', 'wdfbaio'), array($form, 'create_force_facebook_registration_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
		add_settings_field('wdfbaio_easy_facebook_registration', __('Allow single-click registration', 'wdfbaio'), array($form, 'create_easy_facebook_registration_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
		add_settings_field('wdfbaio_captcha', __('Do not show CAPTCHA on registration pages', 'wdfbaio'), array($form, 'create_captcha_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
		if (defined('BP_VERSION')) { // BuddyPress
			add_settings_field('wdfbaio_buddypress_registration_fields', __('Map BuddyPress profile to Facebook', 'wdfbaio'), array($form, 'create_buddypress_registration_fields_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
		} else {
			add_settings_field('wdfbaio_wordrpess_registration_fields', __('Map WordPress profile to Facebook', 'wdfbaio'), array($form, 'create_wordpress_registration_fields_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
		}
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_connect');

		register_setting('wdfbaio', 'wdfbaio_button');
		add_settings_section('wdfbaio_button', __('Facebook Like/Send Button', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_show_button', __('Allow Facebook Like Button', 'wdfbaio'), array($form, 'create_allow_facebook_button_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_show_send_position', __('Show "Send" button too', 'wdfbaio'), array($form, 'create_show_send_button_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_show_front_page', __('Show on Front page posts', 'wdfbaio'), array($form, 'create_show_on_front_page_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_not_in_post_types', __('Do <strong>NOT</strong> show button in these types', 'wdfbaio'), array($form, 'create_do_not_show_button_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_button_position', __('Button position', 'wdfbaio'), array($form, 'create_button_position_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_button_appearance', __('Button appearance', 'wdfbaio'), array($form, 'create_button_appearance_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_button');

		register_setting('wdfbaio', 'wdfbaio_opengraph');
		add_settings_section('wdfbaio_opengraph', __('Facebook OpenGraph', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_use_opengraph', __('Use OpenGraph support', 'wdfbaio'), array($form, 'create_use_opengraph_box'), 'wdfbaio_options_page', 'wdfbaio_opengraph');
		add_settings_field('wdfbaio_always_use_image', __('Always use this image', 'wdfbaio'), array($form, 'create_always_use_image_box'), 'wdfbaio_options_page', 'wdfbaio_opengraph');
		add_settings_field('wdfbaio_fallback_image', __('Fallback image', 'wdfbaio'), array($form, 'create_fallback_image_box'), 'wdfbaio_options_page', 'wdfbaio_opengraph');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_opengraph');

		register_setting('wdfbaio', 'wdfbaio_comments');
		add_settings_section('wdfbaio_comments', __('Facebook Comments', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_import_fb_comments', __('Import Facebook comments', 'wdfbaio'), array($form, 'create_import_fb_comments_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_skip_import_fb_comments', __('Skip importing comments for these accounts', 'wdfbaio'), array($form, 'create_import_fb_comments_skip_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_limit', __('Limit', 'wdfbaio'), array($form, 'create_fb_comments_limit_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_notify_authors', __('Notify post authors', 'wdfbaio'), array($form, 'create_notify_authors_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_use_fb_comments', __('Use Facebook for comments', 'wdfbaio'), array($form, 'create_use_fb_comments_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		if (!defined('BP_VERSION')) add_settings_field('wdfbaio_override_wp_comments_settings', __('Override WordPress discussion settings', 'wdfbaio'), array($form, 'create_override_wp_comments_settings_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_width', __('Facebook Comments box width', 'wdfbaio'), array($form, 'create_fb_comments_width_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_reverse', __('Show Facebook Comments in reverse order?', 'wdfbaio'), array($form, 'create_fb_comments_reverse_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_number', __('Show this many Facebook Comments', 'wdfbaio'), array($form, 'create_fb_comments_number_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_custom_hook', __('Use a custom hook <small>(advanced)</small>', 'wdfbaio'), array($form, 'create_fb_comments_custom_hook_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_comments');

		register_setting('wdfbaio', 'wdfbaio_autopost');
		add_settings_section('wdfbaio_autopost', __('Autopost to Facebook', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_show_button', __('Allow autoposting new posts to Facebook', 'wdfbaio'), array($form, 'create_allow_autopost_box'), 'wdfbaio_options_page', 'wdfbaio_autopost');
		add_settings_field('wdfbaio_show_status_column', __('Show post Facebook status column', 'wdfbaio'), array($form, 'create_show_status_column_box'), 'wdfbaio_options_page', 'wdfbaio_autopost');
		add_settings_field('wdfbaio_autopost_types', __('Map WordPress types to Facebook locations', 'wdfbaio'), array($form, 'create_autopost_map_box'), 'wdfbaio_options_page', 'wdfbaio_autopost');
		add_settings_field('wdfbaio_allow_post_metabox', __('Do not allow individual posts to Facebook', 'wdfbaio'), array($form, 'create_allow_post_metabox_box'), 'wdfbaio_options_page', 'wdfbaio_autopost');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_autopost');

		register_setting('wdfbaio', 'wdfbaio_network');
		add_settings_section('wdfbaio_network', __('Network options', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_override_all', __('Override individual blog settings', 'wdfbaio'), array($form, 'create_override_all_box'), 'wdfbaio_options_page', 'wdfbaio_network');
		add_settings_field('wdfbaio_preserve_api', __('Preserve individual blog API settings', 'wdfbaio'), array($form, 'create_preserve_api_box'), 'wdfbaio_options_page', 'wdfbaio_network');
		add_settings_field('wdfbaio_prevent_blog_settings', __('Do not allow per-blog settings', 'wdfbaio'), array($form, 'create_prevent_blog_settings_box'), 'wdfbaio_options_page', 'wdfbaio_network');

		register_setting('wdfbaio', 'wdfbaio_widget_pack');
		add_settings_section('wdfbaio_widget_pack', __('Widget pack', 'wdfbaio'), create_function('', ''), 'wdfbaio_widget_options_page');
		add_settings_field('wdfbaio_widget_connect', __('Use Facebook Connect widget', 'wdfbaio'), array($form, 'create_widget_connect_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_albums', __('Use Facebook Albums widget', 'wdfbaio'), array($form, 'create_widget_albums_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_events', __('Use Facebook Events widget', 'wdfbaio'), array($form, 'create_widget_events_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_facepile', __('Use Facebook Facepile widget', 'wdfbaio'), array($form, 'create_widget_facepile_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_likebox', __('Use Facebook Like Box widget', 'wdfbaio'), array($form, 'create_widget_likebox_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_recommendations', __('Use Facebook Recommendations widget', 'wdfbaio'), array($form, 'create_widget_recommendations_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_dashboard_permissions', __('Use Facebook Dashboard widgets', 'wdfbaio'), array($form, 'create_dashboard_permissions_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
	}

	/**
	 * Registers settings and form handlers/elements for per-blog administration.
	 *
	 * @access private
	 */
	function register_blog_settings () {
		$form = new wdfbaio_AdminFormRenderer;

		register_setting('wdfbaio', 'wdfbaio_api');
		add_settings_section('wdfbaio_api', __('Facebook API', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_api_info', __('Before we begin', 'wdfbaio'), array($form, 'api_info'), 'wdfbaio_options_page', 'wdfbaio_api');
		add_settings_field('wdfbaio_app_key', __('Application ID', 'wdfbaio'), array($form, 'create_app_key_box'), 'wdfbaio_options_page', 'wdfbaio_api');
		add_settings_field('wdfbaio_api_key', __('API key', 'wdfbaio'), array($form, 'create_api_key_box'), 'wdfbaio_options_page', 'wdfbaio_api');
		add_settings_field('wdfbaio_secret_key', __('Secret key', 'wdfbaio'), array($form, 'create_secret_key_box'), 'wdfbaio_options_page', 'wdfbaio_api');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_api');

		add_settings_section('wdfbaio_grant', __('Grant extended permissions', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_api_permissions', __('Allowing permissions', 'wdfbaio'), array($form, 'api_permissions'), 'wdfbaio_options_page', 'wdfbaio_grant');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_grant');

		if (!is_multisite() || current_user_can('manage_network_options')) {
			register_setting('wdfbaio', 'wdfbaio_connect');
			add_settings_section('wdfbaio_connect', __('Facebook Connect', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
			add_settings_field('wdfbaio_allow_facebook_registration', __('Allow users to register with Facebook', 'wdfbaio'), array($form, 'create_allow_facebook_registration_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
			add_settings_field('wdfbaio_force_facebook_registration', __('Force users to register with Facebook', 'wdfbaio'), array($form, 'create_force_facebook_registration_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
			add_settings_field('wdfbaio_easy_facebook_registration', __('Allow single-click registration', 'wdfbaio'), array($form, 'create_easy_facebook_registration_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
			add_settings_field('wdfbaio_captcha', __('Do not show CAPTCHA on registration pages', 'wdfbaio'), array($form, 'create_captcha_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
			if (defined('BP_VERSION')) { // BuddyPress
				add_settings_field('wdfbaio_buddypress_registration_fields', __('Map BuddyPress profile to Facebook', 'wdfbaio'), array($form, 'create_buddypress_registration_fields_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
			} else {
				add_settings_field('wdfbaio_wordrpess_registration_fields', __('Map WordPress profile to Facebook', 'wdfbaio'), array($form, 'create_wordpress_registration_fields_box'), 'wdfbaio_options_page', 'wdfbaio_connect');
			}
			add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_connect');
		}

		register_setting('wdfbaio', 'wdfbaio_button');
		add_settings_section('wdfbaio_button', __('Facebook Like/Send Button', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_show_button', __('Allow Facebook Like Button', 'wdfbaio'), array($form, 'create_allow_facebook_button_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_show_send_position', __('Show "Send" button too', 'wdfbaio'), array($form, 'create_show_send_button_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_show_front_page', __('Show on Front page posts', 'wdfbaio'), array($form, 'create_show_on_front_page_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_not_in_post_types', __('Do <strong>NOT</strong> show button in these types', 'wdfbaio'), array($form, 'create_do_not_show_button_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_button_position', __('Button position', 'wdfbaio'), array($form, 'create_button_position_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('wdfbaio_button_appearance', __('Button appearance', 'wdfbaio'), array($form, 'create_button_appearance_box'), 'wdfbaio_options_page', 'wdfbaio_button');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_button');

		register_setting('wdfbaio', 'wdfbaio_opengraph');
		add_settings_section('wdfbaio_opengraph', __('Facebook OpenGraph', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_use_opengraph', __('Use OpenGraph support', 'wdfbaio'), array($form, 'create_use_opengraph_box'), 'wdfbaio_options_page', 'wdfbaio_opengraph');
		add_settings_field('wdfbaio_always_use_image', __('Always use this image', 'wdfbaio'), array($form, 'create_always_use_image_box'), 'wdfbaio_options_page', 'wdfbaio_opengraph');
		add_settings_field('wdfbaio_fallback_image', __('Fallback image', 'wdfbaio'), array($form, 'create_fallback_image_box'), 'wdfbaio_options_page', 'wdfbaio_opengraph');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_opengraph');

		register_setting('wdfbaio', 'wdfbaio_comments');
		add_settings_section('wdfbaio_comments', __('Facebook Comments', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_import_fb_comments', __('Import Facebook comments', 'wdfbaio'), array($form, 'create_import_fb_comments_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_skip_import_fb_comments', __('Skip importing comments for these accounts', 'wdfbaio'), array($form, 'create_import_fb_comments_skip_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_limit', __('Limit', 'wdfbaio'), array($form, 'create_fb_comments_limit_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_notify_authors', __('Notify post authors', 'wdfbaio'), array($form, 'create_notify_authors_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_import_now', __('Import comments now', 'wdfbaio'), array($form, 'create_import_now_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_use_fb_comments', __('Use Facebook for comments', 'wdfbaio'), array($form, 'create_use_fb_comments_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		if (!defined('BP_VERSION')) add_settings_field('wdfbaio_override_wp_comments_settings', __('Override WordPress discussion settings', 'wdfbaio'), array($form, 'create_override_wp_comments_settings_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_width', __('Facebook Comments box width', 'wdfbaio'), array($form, 'create_fb_comments_width_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_reverse', __('Show Facebook Comments in reverse order?', 'wdfbaio'), array($form, 'create_fb_comments_reverse_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_number', __('Show this many Facebook Comments', 'wdfbaio'), array($form, 'create_fb_comments_number_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('wdfbaio_fb_comments_custom_hook', __('Use a custom hook <small>(advanced)</small>', 'wdfbaio'), array($form, 'create_fb_comments_custom_hook_box'), 'wdfbaio_options_page', 'wdfbaio_comments');
		add_settings_field('', '', array($form, 'next_step'), 'wdfbaio_options_page', 'wdfbaio_comments');

		register_setting('wdfbaio', 'wdfbaio_autopost');
		add_settings_section('wdfbaio_autopost', __('Autopost to Facebook', 'wdfbaio'), create_function('', ''), 'wdfbaio_options_page');
		add_settings_field('wdfbaio_allow_autopost', __('Allow autoposting new posts to Facebook', 'wdfbaio'), array($form, 'create_allow_autopost_box'), 'wdfbaio_options_page', 'wdfbaio_autopost');
		add_settings_field('wdfbaio_show_status_column', __('Show post Facebook status column', 'wdfbaio'), array($form, 'create_show_status_column_box'), 'wdfbaio_options_page', 'wdfbaio_autopost');
		add_settings_field('wdfbaio_autopost_types', __('Map WordPress types to Facebook locations', 'wdfbaio'), array($form, 'create_autopost_map_box'), 'wdfbaio_options_page', 'wdfbaio_autopost');
		add_settings_field('wdfbaio_allow_post_metabox', __('Do not allow individual posts to Facebook', 'wdfbaio'), array($form, 'create_allow_post_metabox_box'), 'wdfbaio_options_page', 'wdfbaio_autopost');

		register_setting('wdfbaio', 'wdfbaio_widget_pack');
		add_settings_section('wdfbaio_widget_pack', __('Widget pack', 'wdfbaio'), create_function('', ''), 'wdfbaio_widget_options_page');
		add_settings_field('wdfbaio_widget_connect', __('Use Facebook Connect widget', 'wdfbaio'), array($form, 'create_widget_connect_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_albums', __('Use Facebook Albums widget', 'wdfbaio'), array($form, 'create_widget_albums_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_events', __('Use Facebook Events widget', 'wdfbaio'), array($form, 'create_widget_events_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_facepile', __('Use Facebook Facepile widget', 'wdfbaio'), array($form, 'create_widget_facepile_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_likebox', __('Use Facebook Like Box widget', 'wdfbaio'), array($form, 'create_widget_likebox_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_widget_recommendations', __('Use Facebook Recommendations widget', 'wdfbaio'), array($form, 'create_widget_recommendations_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
		add_settings_field('wdfbaio_dashboard_permissions', __('Use Facebook Dashboard widgets', 'wdfbaio'), array($form, 'create_dashboard_permissions_box'), 'wdfbaio_widget_options_page', 'wdfbaio_widget_pack');
	}

	/**
	 * Creates per-blog Admin menu entry.
	 *
	 * @access private
	 */
	function create_blog_admin_menu_entry () {
		if (@$_POST && isset($_POST['option_page'])) {
			if ('wdfbaio' == @$_POST['option_page']) {
				$keys = wdfbaio_Installer::get_keys();
				unset($keys['widget_pack']);
			} else if ('wdfbaio_widgets' == @$_POST['option_page']) {
				$keys = array('widget_pack');
			} else {
				$keys = false;
			}
			if ($keys) {
				foreach ($keys as $key) {
					if (isset($_POST["wdfbaio_{$key}"])) {
						update_option("wdfbaio_{$key}", $_POST["wdfbaio_{$key}"]);//echo "<p>we have $key</p>";
					}
				}
				$goback = add_query_arg( 'settings-updated', 'true',  wp_get_referer() );
				wp_redirect( $goback );
				die;
			}
		}
		add_menu_page('Facebook Plugin', 'Facebook', 'manage_options', 'wdfbaio', array($this, 'create_admin_page'), wdfbaio_PLUGIN_URL . '/img/facebook_icon.gif');
		add_submenu_page('wdfbaio', 'Facebook Plugin', 'Facebook Settings', 'manage_options', 'wdfbaio', array($this, 'create_admin_page'));
		add_submenu_page('wdfbaio', 'Widget Pack', 'Widget Pack', 'manage_options', 'wdfbaio_widgets', array($this, 'create_admin_widgets_page'));
		add_submenu_page('wdfbaio', 'Shortcodes', 'Shortcodes', 'manage_options', 'wdfbaio_shortcodes', array($this, 'create_admin_shortcodes_page'));
	}

	/**
	 * Creates sitewide Admin menu entry.
	 * Also, process settings.
	 *
	 * @access private
	 */
	function create_site_admin_menu_entry () {
		if (@$_POST && isset($_POST['option_page'])) {
			$override = false;
			if ('wdfbaio' == @$_POST['option_page']) {
				$keys = wdfbaio_Installer::get_keys();
				unset($keys['widget_pack']);
				$override = (int)@$_POST['_override_all'];
			} else if ('wdfbaio_widgets' == @$_POST['option_page']) {
				$opt = get_site_option('wdfbaio_network');
				$override = @$opt['prevent_blog_settings'] ? true : false;
				$keys = array('widget_pack');
			} else {
				$keys = false;
			}
			if ($keys) {
				if ($override) $blogs = $this->model->get_blog_ids(); // Get this list only once
				foreach ($keys as $key) {
					if (isset($_POST["wdfbaio_{$key}"])) {
						update_site_option("wdfbaio_{$key}", $_POST["wdfbaio_{$key}"]);
						if ($override) { // Override child settings
							if ('api' == $key && isset($_POST['_preserve_api'])) continue; // Preserve API
							if (!$blogs) continue;
							foreach ($blogs as $blog) update_blog_option($blog['blog_id'], "wdfbaio_{$key}", $_POST["wdfbaio_{$key}"]);
						}
					}
				}
				$goback = add_query_arg( 'settings-updated', 'true',  wp_get_referer() );
				wp_redirect( $goback );
				die;
			}
		}
		add_menu_page('Facebook Plugin', 'Facebook', 'manage_network_options', 'wdfbaio', array($this, 'create_admin_page'), wdfbaio_PLUGIN_URL . '/img/facebook_icon.gif');
		add_submenu_page('wdfbaio', 'Facebook Plugin', 'Facebook Settings', 'manage_network_options', 'wdfbaio', array($this, 'create_admin_page'));
		add_submenu_page('wdfbaio', 'Widget Pack', 'Widget Pack', 'manage_network_options', 'wdfbaio_widgets', array($this, 'create_admin_widgets_page'));
		add_submenu_page('wdfbaio', 'Shortcodes', 'Shortcodes', 'manage_network_options', 'wdfbaio_shortcodes', array($this, 'create_admin_shortcodes_page'));
	}

	/**
	 * Creates Admin menu page.
	 *
	 * @access private
	 */
	function create_admin_page () {
		include(wdfbaio_PLUGIN_BASE_DIR . '/lib/forms/plugin_settings.php');
	}

	/**
	 * Creates Admin Widgets pack page.
	 *
	 * @access private.
	 */
	function create_admin_widgets_page () {
		include(wdfbaio_PLUGIN_BASE_DIR . '/lib/forms/widget_pack_settings.php');
	}

	/**
	 * Creates Admin Shortcodes info page.
	 *
	 * @access private.
	 */
	function create_admin_shortcodes_page () {
		include(wdfbaio_PLUGIN_BASE_DIR . '/lib/forms/shortcodes_info.php');
	}

	function js_load_scripts () {
		wp_enqueue_script('jquery');
		$locale = preg_replace('/-/', '_', get_locale());
		wp_enqueue_script('facebook-all', 'http://connect.facebook.net/' . $locale . '/all.js');
		wp_enqueue_script('wdfbaio_post_as_page', wdfbaio_PLUGIN_URL . '/js/wdfbaio_post_as_page.js');

		if (!isset($_GET['page'])) return false;
		if ('wdfbaio' != $_GET['page'] && 'wdfbaio_widgets' != $_GET['page'] && 'wdfbaio_shortcodes' != $_GET['page']) return false;
		wp_enqueue_script('wdfbaio_jquery_ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js');
	}
	function js_editors () {
		wp_enqueue_script('thickbox');
		wp_enqueue_script('wdfbaio_editor_album', wdfbaio_PLUGIN_URL . '/js/editor_album.js');
		wp_localize_script('wdfbaio_editor_album', 'l10nwdfbaioEditor', array(
			'add_fb_photo' => __('Add FB Photo', 'wdfbaio'),
			'insert_album' => __('Insert album', 'wdfbaio'),
			'insert_album_photos' => __('Insert album photos', 'wdfbaio'),
			'insert' => __('Insert', 'wdfbaio'),
			'go_back' => __('Go back', 'wdfbaio'),
			'use_this_image' => __('Use this image', 'wdfbaio'),
			'please_wait' => __('Please, wait...', 'wdfbaio'),
		));
	}
	function css_load_styles () {
		wp_enqueue_style('wdfbaio_album_editor', wdfbaio_PLUGIN_URL . '/css/wdfbaio_album_editor.css');

		if (!isset($_GET['page'])) return false;
		if ('wdfbaio' != $_GET['page'] && 'wdfbaio_widgets' != $_GET['page'] && 'wdfbaio_shortcodes' != $_GET['page']) return false;
		wp_enqueue_style('wdfbaio_jquery_ui_style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css');
	}
	/**
	 * Introduces plugins_url() as root variable (global).
	 */
	function js_plugin_url () {
		printf(
			'<script type="text/javascript">var _wdfbaio_root_url="%s";</script>',
			wdfbaio_PLUGIN_URL
		);
	}

	function inject_fb_init_js () {
		echo "<script>
         FB.init({
            appId: '" . $this->data->get_option('wdfbaio_api', 'app_key') . "', cookie:true,
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

	/**
	 * This happens only if allow_facebook_registration is true.
	 */
	function handle_fb_session_state () {
		if (wp_validate_auth_cookie('')) return $this->handle_fb_auth_tokens();
		$session = $this->model->fb->getSession();

		if ($session) {
			$user_id = $this->model->get_wp_user_from_fb();
			if (!$user_id) $user_id = $this->model->map_fb_to_current_wp_user();
			if ($user_id) {
				$user = get_userdata($user_id);
				wp_set_current_user($user->ID, $user->user_login);
				wp_set_auth_cookie($user->ID); // Logged in with Facebook, yay
				do_action('wp_login', $user->user_login);
				wp_redirect(admin_url());
				exit();
			}
		}
	}

	function handle_fb_auth_tokens () {
		$tokens = $this->data->get_option('wdfbaio_api', 'auth_tokens');

		$fb_uid = $this->model->fb->getUser();

		$app_id = $this->data->get_option('wdfbaio_api', 'app_key');
		$app_secret = $this->data->get_option('wdfbaio_api', 'secret_key');
		if (!$app_id || !$app_secret) return false; // Plugin not yet configured

		$url = "https://graph.facebook.com/oauth/access_token?type=client_cred&client_id={$app_id}&client_secret={$app_secret}";
		$page = wp_remote_get($url, array(
			'method' 		=> 'GET',
			'timeout' 		=> '5',
			'redirection' 	=> '5',
			'user-agent' 	=> 'wdfbaio',
			'blocking'		=> true,
			'compress'		=> false,
			'decompress'	=> true,
			'sslverify'		=> false
		));
		if(is_wp_error($page)) return false; // Request fail
		if ((int)$page['response']['code'] != 200) return false; // Request fail

		$token = substr($page['body'], 13);
		if (!$token) return false;

		$page_tokens = $this->model->get_pages_tokens();
		$page_tokens = isset($page_tokens['data']) ? $page_tokens['data'] : array();

		$api = array();
		$api['auth_tokens'][$fb_uid] = $token;
		$api['auth_accounts'][$fb_uid] = sprintf(__("Me (%s)", 'wdfbaio'), $fb_uid);
		foreach ($page_tokens as $ptk) {
			if (!isset($ptk['id']) || !isset($ptk['access_token'])) continue;
			if ($ptk['id'] != $app_id) continue;
			$api['auth_tokens'][$ptk['id']] = $ptk['access_token'];
			$api['auth_accounts'][$ptk['id']] = $ptk['name'];
		}
		$user = wp_get_current_user();
		update_user_meta($user->ID, 'wdfbaio_api_accounts', $api);
		$this->merge_api_tokens();
		return true;
	}

	function merge_api_tokens () {
		$api = $this->data->get_key('wdfbaio_api');
		$auts_meta = $this->model->get_all_user_tokens();

		foreach ($auts_meta as $meta) {
			$data = unserialize($meta['meta_value']);
			if (is_array($data['auth_tokens'])) foreach ($data['auth_tokens'] as $fb_uid => $token) $api['auth_tokens'][$fb_uid] = $token;
			if (is_array($data['auth_accounts'])) foreach ($data['auth_accounts'] as $fb_uid => $acc) $api['auth_accounts'][$fb_uid] = $acc;
		}
		$this->data->set_key('wdfbaio_api', $api);
		update_option('wdfbaio_api', $api);
	}

	function add_facebook_publishing_metabox () {
		if ($this->data->get_option('wdfbaio_autopost', 'prevent_post_metabox')) return false;
		$types = get_post_types(array('public'=>true), 'names');
		foreach ($types as $type) {
			if ('attachment' == $type) continue;
			add_meta_box(
				'wdfbaio_facebook_publishing',
				__('Facebook Publishing', 'wdfbaio'),
				array($this, 'render_facebook_publishing_metabox'),
				$type
			);
		}
	}

	function render_facebook_publishing_metabox () {
		$frm = new wdfbaio_AdminFormRenderer;
		echo $frm->facebook_publishing_metabox();
	}

	function publish_post_on_facebook ($id) {
		if (!$id) return false;

		$post_id = $id;
		if ($rev = wp_is_post_revision($post_id)) $post_id = $rev;

		// Should we even try?
		if (
			!$this->data->get_option('wdfbaio_autopost', 'allow_autopost')
			&&
			!@$_POST['wdfbaio_metabox_publishing_publish']
		) return false;

		$post = get_post($post_id);
		if ('publish' != $post->post_status) return false; // Draft, auto-save or something else we don't want

		$is_published = get_post_meta($post_id, 'wdfbaio_published_on_fb', true);
		if ($is_published && !@$_POST['wdfbaio_metabox_publishing_publish']) return true; // Already posted and no manual override, nothing to do

		$post_type = $post->post_type;
		$post_title = @$_POST['wdfbaio_metabox_publishing_title'] ? $_POST['wdfbaio_metabox_publishing_title'] : $post->post_title;

		// If publishing semi-auto, always use wall
		$post_as = @$_POST['wdfbaio_metabox_publishing_publish'] ? 'feed' : $this->data->get_option('wdfbaio_autopost', "type_{$post_type}_fb_type");
		$post_to = @$_POST['wdfbaio_metabox_publishing_account'] ? $_POST['wdfbaio_metabox_publishing_account'] : $this->data->get_option('wdfbaio_autopost', "type_{$post_type}_fb_user");
		if (!$post_to) return false; // Don't know where to post, bail

		$as_page = false;
		if ($post_to != $this->model->get_current_user_fb_id()) {
			$as_page = isset($_POST['wdfbaio_post_as_page']) ? $_POST['wdfbaio_post_as_page'] : $this->data->get_option('wdfbaio_autopost', 'post_as_page');
		}

		if (!$post_as) return true; // Skip this type

		switch ($post_as) {
			case "notes":
				$send = array (
					'subject' => $post_title,
					'message' => $post->post_content,
				);
				break;
			case "events":
				$send = array(
					'name' => $post_title,
					'description' => $post->post_content,
					'start_time' => time(),
					'location' => 'someplace',
				);
				break;
			case "feed":
			default:
				$send = array(
					'caption' => substr($post->post_content, 0, 999),
					'message' => $post_title,
					'link' => get_permalink($post_id),
					'picture' => wdfbaio_get_og_image($post_id),
					'name' => $post->post_title,
					'description' => get_option('blogdescription'),
				);
				break;
		}
		$res = $this->model->post_on_facebook($post_as, $post_to, $send, $as_page);
		if ($res) update_post_meta($post_id, 'wdfbaio_published_on_fb', 1);
		add_filter('redirect_post_location', create_function('$loc', 'return add_query_arg("wdfbaio_published", ' . (int)$res . ', $loc);'));
	}

	function show_post_publish_error () {
		if (!isset($_GET['wdfbaio_published'])) return false;
		$done = ((int)$_GET['wdfbaio_published'] > 0) ? true : false;
		$class = $done ? 'updated' : 'error';
		$msg = $done ? __("Post published on Facebook", "wdfbaio") : __("Publishing on Facebook failed", "wdfbaio");
		echo "<div class='{$class}'><p>{$msg}</p></div>";
	}

	function insert_events_into_post_meta ($post) {
		if (!$post['post_content']) return $post;

		$post_id = (int)$_POST['post_ID'];
		if (!$post_id) return $post;

		// We need to have active FB session for this, else skip
		$fb_uid = $this->model->fb->getUser();
		if (!$fb_uid) return $post;

		// Process the shortcode
		$txt = stripslashes($post['post_content']);
		if (preg_match('~\[wdfbaio_events\s+for\s*=~', $txt)) {
			preg_match_all('~\[wdfbaio_events\s+for\s*=\s*(.+)\s*]~', $txt, $matches);
			$fors = $matches[1];
			if (!empty($fors)) foreach ($fors as $for) {
				$for = trim($for, '\'" ');
				$events = $this->model->get_events_for($for);
				if (!is_array($events) || empty($events['data'])) continue; // No events, skip to next
				update_post_meta($post_id, 'wdfbaio_events', $events['data']);
			}
		}
		return $post;
	}

	function add_published_status_column ($cols) {
		$cols['ufb_published'] = __('On Facebook', 'wdfbaio');
		return $cols;
	}
	function update_published_status_column ($col_name, $post_id) {
		if ('ufb_published' != $col_name) return false;
		$meta = get_post_meta($post_id, 'wdfbaio_published_on_fb', true);
		echo $meta ? __('Yes', 'wdfbaio') : __('No', 'wdfbaio');
	}

	function json_list_fb_albums () {
		$albums = $this->model->get_current_albums();
		$status = $albums ? 1 : 0;
		header('Content-type: application/json');
		echo json_encode(array(
			'status' => $status,
			'albums' => $albums,
		));
		exit();
	}

	function json_list_fb_album_photos () {
		$album_id = $_POST['album_id'];
		$photos = $this->model->get_album_photos($album_id);
		$status = $photos ? 1 : 0;
		header('Content-type: application/json');
		echo json_encode(array(
			'status' => $status,
			'photos' => $photos,
		));
		exit();
	}

	function json_import_comments () {
		wdfbaio_CommentsImporter::serve();
		echo json_encode(array(
			'status' => 1,
		));
		exit();
	}

	function json_populate_profile () {
		$user = wp_get_current_user();
		if (defined('BP_VERSION')) {
			$status = $this->model->populate_bp_fields_from_fb($user->ID);
		} else {
			$status = $this->model->populate_wp_fields_from_fb($user->ID);
		}
		echo json_encode(array(
			'status' => (int)$status,
		));
		exit();
	}

	function json_perhaps_create_wp_user () {
		$user = wp_get_current_user();
		if ($user->ID) die();

		$session = $this->model->fb->getSession();
		if ($session) {
			$user_id = $this->model->get_wp_user_from_fb();
			if (!$user_id) $user_id = $this->model->map_fb_to_current_wp_user();
			if (!$user_id && $this->data->get_option('wdfbaio_connect', 'easy_facebook_registration')) {
				$user_id = $this->model->register_fb_user();
			}
		}
		exit();
	}

	/**
	 * Hooks to appropriate places and adds stuff as needed.
	 *
	 * @access private
	 */
	function add_hooks () {
		// Step0: Register options and menu
		if (WP_NETWORK_ADMIN) {
			add_action('admin_init', array($this, 'register_site_settings'));
			add_action('network_admin_menu', array($this, 'create_site_admin_menu_entry'));
		} else {
			$opt = get_site_option('wdfbaio_network', array());
			if (!@$opt['prevent_blog_settings']) {
				add_action('admin_init', array($this, 'register_blog_settings'));
				add_action('admin_menu', array($this, 'create_blog_admin_menu_entry'));
			}
		}

		// Step1a: Add plugin script core requirements and editor interface
		add_action('admin_print_scripts', array($this, 'js_plugin_url'));

		add_action('admin_print_scripts', array($this, 'js_load_scripts'));
		add_action('admin_print_styles', array($this, 'css_load_styles'));

		add_action('admin_print_scripts-post.php', array($this, 'js_editors'));
		add_action('admin_print_scripts-post-new.php', array($this, 'js_editors'));
		//add_action('admin_print_scripts-widgets.php', array($this, 'js_widget_editors'));

		add_action('admin_footer', array($this, 'inject_fb_root_div'));
		add_action('admin_footer', array($this, 'inject_fb_init_js'));

		// Step2: Add AJAX request handlers
		add_action('wp_ajax_wdfbaio_list_fb_albums', array($this, 'json_list_fb_albums'));
		add_action('wp_ajax_wdfbaio_list_fb_album_photos', array($this, 'json_list_fb_album_photos'));
		add_action('wp_ajax_wdfbaio_import_comments', array($this, 'json_import_comments'));
		add_action('wp_ajax_wdfbaio_populate_profile', array($this, 'json_populate_profile'));


		// Step 3: Process conditional features:

		// Connect
		if ($this->data->get_option('wdfbaio_connect', 'allow_facebook_registration')) {
			add_action('after_setup_theme', array($this, 'handle_fb_session_state'));
			// Single-click registration enabled
			if ($this->data->get_option('wdfbaio_connect', 'easy_facebook_registration')) {
				add_action('wp_ajax_nopriv_wdfbaio_perhaps_create_wp_user', array($this, 'json_perhaps_create_wp_user'));
			}
		} else {
			add_action('admin_init', array($this, 'handle_fb_auth_tokens'));
		}

		// Autopost
		if ($this->data->get_option('wdfbaio_autopost', 'allow_autopost')) {
			// Attempt to process scheduled events.
			// Not yet.
			//add_action('transition_post_status', array($this, 'publish_queued_post_on_facebook'));
		}
		// Post columns
		if ($this->data->get_option('wdfbaio_autopost', 'show_status_column')) {
			add_filter('manage_posts_columns', array($this, 'add_published_status_column'));
			add_filter('manage_posts_custom_column', array($this, 'update_published_status_column'), 10, 2);
			add_filter('manage_pages_columns', array($this, 'add_published_status_column'));
			add_filter('manage_pages_custom_column', array($this, 'update_published_status_column'), 10, 2);
		}

		// Post metabox
		add_action('add_meta_boxes', array($this, 'add_facebook_publishing_metabox'));
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
			add_action('save_post', array($this, 'publish_post_on_facebook'));
		} else {
			add_action('post_updated', array($this, 'publish_post_on_facebook'));
		}
		add_action('admin_notices', array($this, 'show_post_publish_error'));

		// Events shortcode
		add_action('wp_insert_post_data', array($this, 'insert_events_into_post_meta'));

		// Register the shortcodes, so Membership picks them up
		$rpl = new wdfbaio_MarkerReplacer; $rpl->register();
	}
}