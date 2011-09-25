(function($){
$(function() {
	
var $parent;
	
function parseAlbumIdHref (href) {
	return parseInt(href.substr(1));
}
	
function createAlbumsMarkup (data) {
	var status = parseInt(data.status);
	if (!status) {
		$("#wdfbaio_album_container").html(
			"Please log in to your FB account first"
		);
		return false;
	}
	var html = '<ul>';
	$.each(data.albums.data, function (idx, album) {
		album.count = ("count" in album) ? album.count : 0;
		html += '<li>';
		
		html += album.name + ' (' + album.count + ') <br />';
		html += '<a class="wdfbaio_insert_album" href="#' + album.id + '">' + l10nwdfbaioEditor.insert_album + '</a>';
		
		html += '</li>';
	});
	html += '</ul>';
	$("#wdfbaio_album_container").html(html);
}

function loadAlbums () {
	$("#wdfbaio_album_container").html(l10nwdfbaioEditor.please_wait + ' <img src="' + _wdfbaio_root_url + '/img/waiting.gif">');
	$.post(ajaxurl, {"action": "wdfbaio_list_fb_albums"}, function (response) {
		createAlbumsMarkup(response);
	});
}

function insertAlbum ($me) {
	var albumId = parseAlbumIdHref($me.attr('href'));
	$parent.find('input:text').val(albumId);
	tb_remove();
	return false;
}


/**
 * Inserts the map marker into editor.
 * Supports TinyMCE and regular editor (textarea).
 */
function updateEditorContents (markup) {	
	if (window.tinyMCE && ! $('#content').is(':visible')) window.tinyMCE.execCommand("mceInsertContent", true, markup);
	else insertAtCursor($("#content").get(0), markup);
}

/**
 * Inserts map marker into regular (textarea) editor.
 */
function insertAtCursor(fld, text) {
    // IE
    if (document.selection && !window.opera) {
    	fld.focus();
        sel = window.opener.document.selection.createRange();
        sel.text = text;
    }
    // Rest
    else if (fld.selectionStart || fld.selectionStart == '0') {
        var startPos = fld.selectionStart;
        var endPos = fld.selectionEnd;
        fld.value = fld.value.substring(0, startPos)
        + text
        + fld.value.substring(endPos, fld.value.length);
    } else {
    	fld.value += text;
    }
}

function openWidgetEditor () {
	var height = $(window).height(), adminbar_height = 0;
	if ($('body.admin-bar').length) adminbar_height = 28;
	height = height - 85 - adminbar_height;
	tb_show(l10nwdfbaioEditor.add_fb_photo, '#TB_inline?width=640&height=' + height + '&inlineId=wdfbaio_album_root_container');
	loadAlbums();
	return false;
}

// Create the needed editor container HTML
$('body').append('<div id="wdfbaio_album_root_container" style="display:none"><div id="wdfbaio_album_container"></div></div>');

// --- Bind events ---

$('a.wdfbaio_widget_open_editor').live('click', function () {
	$parent = $(this).parents('.wdfbaio_album_widget_select_album');
	openWidgetEditor();
	return false;
});

$('a.wdfbaio_insert_album').live('click', function () {
	insertAlbum($(this));
});
	
});
})(jQuery);