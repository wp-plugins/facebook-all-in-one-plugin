(function ($) { 
$(function () { 
	
function check_perms () {
	var $perms = $(".wdfbaio_grant_perms:first");
	if (!$perms.length) return false;
	var query = "SELECT " + $perms.attr("wdfbaio:perms") + " FROM permissions WHERE uid=me()";
	FB.api({
		"method": "fql.query",
		"query": query
	}, function (resp) {
		var all_good = true;
		try {
			$.each(resp[0], function (idx, el) {
				if(el !== "1") all_good = false;
			});
		} catch (e) {
			all_good = false;
		}
		if (all_good) {
			$("p.wdfbaio_perms_not_granted").hide();
			$("p.wdfbaio_perms_granted").show();
		} else {
			$("p.wdfbaio_perms_not_granted").show();
			$(".wdfbaio_grant_perms").show();
			$("p.wdfbaio_perms_granted").hide();
		}
	});
}

function init () {
	$(".wdfbaio_perms_root").show();
	$(".wdfbaio_grant_perms, .wdfbaio_perms_granted, .wdfbaio_perms_not_granted").hide();
	check_perms();
}

init();
	
$(".wdfbaio_grant_perms").click(function () { 
	var $me = $(this);
	var perms = $me.attr("wdfbaio:perms"); 
	var locale = $me.attr("wdfbaio:locale");
	FB.ui({ 
		"method": "permissions.request", 
		"perms": perms, 
		"locale": locale
	}, function () {
		window.location.href = window.location.href;
	}); 
	return false; 
}); 
	
}); 
})(jQuery);