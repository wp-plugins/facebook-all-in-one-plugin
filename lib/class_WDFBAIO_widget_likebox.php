<?php
/**
 * Shows Facebook Recommendations box.
 * See http://developers.facebook.com/docs/reference/plugins/recommendations/
 */
class wdfbaio_WidgetLikebox extends WP_Widget {

	function wdfbaio_WidgetLikebox () {
		$widget_ops = array('classname' => __CLASS__, 'description' => __('Shows Facebook Like box.', 'wdfbaio'));
		parent::WP_Widget(__CLASS__, 'Facebook Like Box', $widget_ops);
	}

	function form($instance) {
		$title = esc_attr($instance['title']);
		$url = esc_attr($instance['url']);
		$width = esc_attr($instance['width']);
		$show_header = esc_attr($instance['show_header']);
		$show_faces = esc_attr($instance['show_faces']);
		$show_stream = esc_attr($instance['show_stream']);
		$color_scheme = esc_attr($instance['color_scheme']);

		// Set defaults
		// ...

		$html = '<p>';
		$html .= '<label for="' . $this->get_field_id('title') . '">' . __('Title:', 'wdfbaio') . '</label>';
		$html .= '<input type="text" name="' . $this->get_field_name('title') . '" id="' . $this->get_field_id('title') . '" class="widefat" value="' . $title . '"/>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('url') . '">' . __('Facebook page URL:', 'wdfbaio') . '</label>';
		$html .= '<input type="text" name="' . $this->get_field_name('url') . '" id="' . $this->get_field_id('url') . '" class="widefat" value="' . $url . '"/>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('width') . '">' . __('Width:', 'wdfbaio') . '</label>';
		$html .= '<input type="text" name="' . $this->get_field_name('width') . '" id="' . $this->get_field_id('width') . '" size="3" value="' . $width . '"/>';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('show_header') . '">' . __('Show header:', 'wdfbaio') . '</label>';
		$html .= '<input type="checkbox" name="' . $this->get_field_name('show_header') . '" id="' . $this->get_field_id('show_header') . '" value="1" ' . ($show_header ? 'checked="checked"' : '') . ' />';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('show_faces') . '">' . __('Show faces:', 'wdfbaio') . '</label>';
		$html .= '<input type="checkbox" name="' . $this->get_field_name('show_faces') . '" id="' . $this->get_field_id('show_faces') . '" value="1" ' . ($show_faces ? 'checked="checked"' : '') . ' />';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('show_stream') . '">' . __('Show stream:', 'wdfbaio') . '</label>';
		$html .= '<input type="checkbox" name="' . $this->get_field_name('show_stream') . '" id="' . $this->get_field_id('show_stream') . '" value="1" ' . ($show_stream ? 'checked="checked"' : '') . ' />';
		$html .= '</p>';

		$html .= '<p>';
		$html .= '<label for="' . $this->get_field_id('color_scheme') . '">' . __('Color scheme:', 'wdfbaio') . '</label>';
		$html .= '<select name="' . $this->get_field_name('color_scheme') . '" id="' . $this->get_field_id('color_scheme') . '">';
		$html .= '<option value="light" ' . (('light' == $color_scheme) ? 'selected="selected"' : '') . '>Light</option>';
		$html .= '<option value="dark" ' . (('dark' == $color_scheme) ? 'selected="selected"' : '') . '>Dark</option>';
		$html .= '</select>';
		$html .= '</p>';

		echo $html;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['url'] = strip_tags($new_instance['url']);
		$instance['show_header'] = strip_tags($new_instance['show_header']);
		$instance['show_faces'] = strip_tags($new_instance['show_faces']);
		$instance['show_stream'] = strip_tags($new_instance['show_stream']);
		$instance['color_scheme'] = strip_tags($new_instance['color_scheme']);

		return $instance;
	}

	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$width = $instance['width'];
		$width = $width ? $width : 250;
		$url = rawurlencode($instance['url']);
		$show_header = (int)@$instance['show_header'];
		$show_header = $show_header ? 'true' : 'false';
		$show_faces = (int)@$instance['show_faces'];
		$show_faces = $show_faces ? 'true' : 'false';
		$show_stream = (int)@$instance['show_stream'];
		$show_stream = $show_stream ? 'true' : 'false';
		$color_scheme = $instance['color_scheme'];
		$color_scheme = $color_scheme ? $color_scheme : 'light';

		$height = ('true' == $show_stream || 'true' == $show_faces) ? 427 : 62;

		echo $before_widget;
		if ($title) echo $before_title . $title . $after_title;

		echo '<iframe src="http://www.facebook.com/plugins/likebox.php?href=' . $url . '&amp;width=' . $width . '&amp;colorscheme=' . $color_scheme . '&amp;show_faces=' . $show_faces . '&amp;stream=' . $show_stream . '&amp;header=' . $show_header . '&amp;height='. $height . '" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:' . $width .'px; height:' . $height . 'px;" allowTransparency="true"></iframe>';

		echo $after_widget;
	}
}