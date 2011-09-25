<div class="wrap">
	<h2><?php _e('Facebook All Ine One Settings', 'wdfbaio');?></h2>

<?php if (WP_NETWORK_ADMIN) { ?>
	<form action="settings.php" method="post">
<?php } else { ?>
	<form action="admin.php" method="post">
<?php } ?>

	<?php settings_fields('wdfbaio'); ?>
<div id="wdtg_accordion">
	<?php do_settings_sections('wdfbaio_options_page'); ?>
</div>
	<p class="submit">
		<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
	</p>
	</form>
</div>

<script type="text/javascript">
(function($) {
$(function () {

$('#wdtg_accordion :checkbox').each(function () {
	var $me = $(this);
	var val = ($me.is(':checked')) ? $me.val() : 0;
	$me
		.after(
			'<input type="hidden" class="wdfbaio_checkbox_helper" name="' + $me.attr('name') + '" value="' + val + '" />'
		)
		.click(function() {
			var val = ($me.is(':checked')) ? $me.val() : 0;
			$('input.wdfbaio_checkbox_helper[name="' + $me.attr('name') + '"]').val(val);
		})
	;
});


$('#wdtg_accordion h3').each(function () {
	$(this).html('<a href="#">' + $(this).text() + '</a>');
});

$('#wdtg_accordion').accordion({
	"fillSpace": true,
	"clearStyle": true
});

// Disable all other steps if we don't have enough credentials
if (!$('#api_key').val() || !$('#app_key').val() || !$('#secret_key').val()) {
	$("#wdtg_accordion").accordion({
		'change': function () {
		$("#wdtg_accordion").accordion("activate", 0);
		}
	});
	$('#api_key').parents('table').find('input.wdfbaio_next_step').hide();
}

$('.wdfbaio_next_step').click(function () {
	var active = $("#wdtg_accordion").accordion("option", "active");
	if (active < $('#wdtg_accordion h3').length) {
		$("#wdtg_accordion").accordion("activate", active+1);
	}
	return false;
});

$('.wdfbaio_skip_to_step_0').click(function () {
	$("#wdtg_accordion").accordion("activate", 0);
	return false;
});
$('.wdfbaio_skip_to_step_1').click(function () {
	$("#wdtg_accordion").accordion("activate", 1);
	return false;
});

$('.wdfbaio_import_comments_now').click(function () {
	var $me = $(this);
	var oldHtml = $me.html();
	$me.html('<img src="' + _wdfbaio_root_url + '/img/waiting.gif">');
	$.post(ajaxurl, {"action": "wdfbaio_import_comments"}, function (data) {
		$me.html(oldHtml);
	});
	return false;
});

$('#wdfbaio_connect_add_mapping').click(function () {
	var $clone = $('.wdfbaio_connect_wp_registration:last').clone();

	var oldName = $clone.find('input:text').attr('name');
	if (!oldName) return false;

	var oldId = parseInt(oldName.replace(/[^0-9]/g, ''));
	if (!oldId) return false;

	var newId = oldId+1;
	var newNameWp = oldName.replace(/\[\d+\]/, '[' + newId + ']');
	var newNameFb = newNameWp.replace(/\[wp\]/, '[fb]');

	$clone
		.find('input:text')
			.attr('name', newNameWp)
			.val('')
			.end()
		.find('select')
			.attr('name', newNameFb)
			.val('')
			.find('option:first').attr('selected', true)
	;
	$('#wdfbaio_connect_wp_registration_container').append($clone);
	return false;
});

// Auto-check blog trumping settings
if ($("#prevent_blog_settings").is(":checked")) {
	$("#_override_all").attr("checked", true);
	$('input.wdfbaio_checkbox_helper[name="_override_all"]').val(1);
}
$("#prevent_blog_settings").change(function () {
	if ($(this).is(":checked")) {
		$("#_override_all").attr("checked", true);
		$('input.wdfbaio_checkbox_helper[name="_override_all"]').val(1);
	} else {
		$("#_override_all").attr("checked", false);
		$('input.wdfbaio_checkbox_helper[name="_override_all"]').val(0);
	}
});

});
})(jQuery);
</script>