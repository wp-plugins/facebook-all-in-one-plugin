(function ($) {
$(function () {
	
	
function selectBoxFromTarget ($me) {
	var id = $me.attr('href');
	var $target = $(id);
	if (!$target.length) return false;
	$(".wdfbaio_connect_target").css('display', 'none');
	$target.css('display', 'block');
	$(".wdfbaio_connect_widget_container ul.wdfbaio_connect_widget_action_links li a").removeClass('wdfbaio_active');
	$me.addClass('wdfbaio_active');
}

$(".wdfbaio_connect_widget_container ul.wdfbaio_connect_widget_action_links li a")
	.unbind('click')
	.click(function (e) {
		e.stopPropagation();
		selectBoxFromTarget($(this));
		return false;
	})
;

selectBoxFromTarget( $(".wdfbaio_connect_widget_container ul.wdfbaio_connect_widget_action_links li:first a") );
	
});
})(jQuery);