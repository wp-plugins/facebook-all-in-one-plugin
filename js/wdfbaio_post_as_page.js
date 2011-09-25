(function ($) {
$(function () {
	
$("#post_as_page").change(function () {
	if ($("#post_as_page").is(":checked")) FB.ui({
		"method": "permissions.request",
		"perms": "offline_access"
	}, function (resp) { 
		if ("offline_access" != resp.perms) $("#post_as_page").attr("checked", false);
	});
	return true;
});

});
})(jQuery);