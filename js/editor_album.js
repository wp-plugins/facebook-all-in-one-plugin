/**
 * Responsible for hooking Maps to the WP editor interface. 
 */


function wdfbaioOpenAlbumEditor () {
	jQuery(document).trigger('wdfbaio_album_editor_open');
	return false;
}
function wdfbaioCloseAlbumEditor () {
	tb_remove();
	jQuery(document).trigger('wdfbaio_album_editor_close');
	return false;
}


(function($){
$(function() {
	
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
	var html = '<ul class="wdfbaio_albums">';
	$.each(data.albums.data, function (idx, album) {
		album.count = ("count" in album) ? album.count : 0;
		html += '<li>';
		
		html += album.name + ' (' + album.count + ') <br />';
		html += '<a class="wdfbaio_insert_album" href="#' + album.id + '">' + l10nwdfbaioEditor.insert_album + '</a>';
		html += '&nbsp;';
		html += '<a class="wdfbaio_show_album_photos" href="#' + album.id + '">' + l10nwdfbaioEditor.insert_album_photos + '</a>';
		
		html += '</li>';
	});
	html += '</ul>';
	$("#wdfbaio_album_container").html(html);
}

function createAlbumPhotosMarkup (data) {
	var status = parseInt(data.status);
	if (!status) {
		$("#wdfbaio_album_container").html(
				"Please log in to your FB account first"
		);
		return false;
	}
	var html = '<p>';
	html += '<input type="button" id="wdfbaio_insert_album_photo_items" value="' + l10nwdfbaioEditor.insert + '" />';
	html += '<input type="button" id="wdfbaio_back_to_albums" value="' + l10nwdfbaioEditor.go_back + '" />';
	html += '</p>';
	
	html += '<ul class="wdfbaio_album_photos">';
	$.each(data.photos.data, function (idx, photo) {
		var iconSrc = photo.images[photo.images.length-1].source;
		var imgSrc = photo.images[0].source;
		html += '<li>';
		
		html += '<img src="' + iconSrc+ '" width="90" /><br />';
		html += '<input type="checkbox" id="wdfbaio_image_item' + idx + '" class="wdfbaio_album_photo_item" value="' + imgSrc + '" /><label for="wdfbaio_image_item' + idx + '">' + l10nwdfbaioEditor.use_this_image + '</label>';
		
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

function loadAlbumPhotos ($me) {
	$("#wdfbaio_album_container").html(l10nwdfbaioEditor.please_wait + ' <img src="' + _wdfbaio_root_url + '/img/waiting.gif">');
	var albumId = parseAlbumIdHref($me.attr('href'));
	$.post(ajaxurl, {"action": "wdfbaio_list_fb_album_photos", "album_id": albumId}, function (response) {
		createAlbumPhotosMarkup(response);
	});
}

function insertAlbum ($me) {
	var albumId = parseAlbumIdHref($me.attr('href'));
	var markup = '';
	$("#wdfbaio_album_container").html(l10nwdfbaioEditor.please_wait + ' <img src="' + _wdfbaio_root_url + '/img/waiting.gif">');
	$.post(ajaxurl, {"action": "wdfbaio_list_fb_album_photos", "album_id": albumId}, function (response) {
		var status = parseInt(response.status);
		if (!status) return false;
		markup += '<div class="wdfbaio_fb_album">';
		markup += '<ul>';
		$.each(response.photos.data, function (idx, photo) {
			var iconSrc = photo.images[photo.images.length-1].source;
			var imgSrc = photo.images[0].source;
			markup += '<li>';
			markup += '<a href="' + imgSrc + '">';
			markup += '<img src="' + iconSrc + '" />';
			markup += '</a>';
			markup += '</li>';
		});
		markup += '</ul>';
		markup += '</div>';
		console.log(markup);
		updateEditorContents(markup);
		wdfbaioCloseAlbumEditor();
	});
	return false;
}

function insertAlbumPhotos () {
	var markup = '';
	$('.wdfbaio_album_photo_item:checked').each(function () {
		markup += '<img src="' + $(this).val() + '" />';
	});
	updateEditorContents(markup);
	wdfbaioCloseAlbumEditor();
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


// Find Media Buttons strip and add the new one
var mbuttons_container = $('#media-buttons');
if (!mbuttons_container.length) return;

mbuttons_container.append('' + 
	'<a onclick="return wdfbaioOpenAlbumEditor();" title="' + l10nwdfbaioEditor.add_fb_photo + '" class="thickbox" id="add_fb_photo" href="#TB_inline?width=640&height=594&inlineId=wdfbaio_album_root_container">' +
		'<img onclick="return false;" alt="' + l10nwdfbaioEditor.add_fb_photo + '" src="' + _wdfbaio_root_url + '/img/fb_photo.png">' +
	'</a>'
);

// Create the needed editor container HTML
$('body').append('<div id="wdfbaio_album_root_container" style="display:none"><div id="wdfbaio_album_container"></div></div>');

// --- Bind events ---

$(document).bind('wdfbaio_album_editor_open', function () {
	loadAlbums();
});

$('a.wdfbaio_show_album_photos').live('click', function () {
	loadAlbumPhotos($(this));
});
$('a.wdfbaio_insert_album').live('click', function () {
	insertAlbum($(this));
});
$('#wdfbaio_back_to_albums').live('click', function () {
	loadAlbums();
});
$('#wdfbaio_insert_album_photo_items').live('click', function () {
	insertAlbumPhotos();
});
	
});
})(jQuery);