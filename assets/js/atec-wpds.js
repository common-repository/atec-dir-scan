function jsTreeCloseAll() { jQuery("#dirScan").jstree("close_all"); return false; }
function jsTreeOpenAll() { jQuery("#dirScan").jstree("open_all"); return false; }
	
var instance;
function lightBox(url,type) 
{ 	
	const esc = '<div style="background: white; padding: 5px; height: 50px; border-bottom: solid 1px #aaa; width: 100%;">Hit ESC or click anywhere outside to close.<br>File: '+url+'</div>';
	const size = '';
	switch (type) 
	{
		case 'format-image': instance=basicLightbox.create(esc+'<div><img '+size+' src="'+url+'"></div>'); break;
		case 'media-video': instance=basicLightbox.create(esc+'<div><video '+size+' controls autoplay><source src="'+url+'"></video></div>'); break;
		case 'pdf':
		case 'media-spreadsheet':
		case 'media-interactive':
		case 'media-document': instance=basicLightbox.create(esc+'<div><iframe width="100%" height="100%" src="'+url+'"></iframe></div>'); break;
		case 'media-text':
		case 'media-code': instance=basicLightbox.create(esc+'<div '+size+'><pre id="textarea" class="widefat textarea" id="textarea">Loading ...</pre></div>'); textArea(url); break;

	}
	if (instance) instance.show();
}

function textArea(url)
{
	jQuery.ajax({ type: "GET", url: url, complete: function(data) { jQuery('#textarea').html(data.responseText); } });
}