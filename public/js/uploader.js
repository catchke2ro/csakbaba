function fileUploadInit(){
	jQuery('.fileupload').each(function(index, e){

		jQuery(e).fileupload({
			url: jQuery(this).data('url'),
			dataType: 'json',
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			maxFileSize: 5000000, // 5 MB,
			maxNumberOfFiles: 5,
			previewMaxWidth: 100,
			previewMaxHeight: 100,
			previewCrop: true,
			dropZone: jQuery(e).find('.dropzone'),
			singleFileUploads: true,
			limitMultiFileUploads: 1,
			autoUpload: true,
			filesContainer: jQuery(e).closest('.fileUploadContainer').find('.files'),
			getNumberOfFiles: function(){
				return this.filesContainer.children().length;
			},
			messages: {
				maxNumberOfFiles: 'Elérted a maximális képszámot',
				acceptFileTypes: 'A fájltípus nem engedélyezett (Engedélyezett fájltípusok: jpg, png, gif)',
				maxFileSize: 'A fájl mérete túl nagy (A maximális méret 5MB)'
			},
			uploadTemplate: function(data) {
				var tpls=[];
				jQuery.each(data.files, function(index, file){
					var tpl=jQuery(data.options.filesContainer).closest('.fileUploadContainer').children('.template-upload').clone();
					tpl.find('.filename').text(file.name);
					tpl.find('.size').text(data.formatFileSize(file.size));
					if(file.error) tpl.find('.error').text(file.error);
					tpls.push(tpl);
				});
				return tpls;
			},
			downloadTemplate: function(data) {
				var tpls=[];
				jQuery.each(data.files, function(index, file){
					if(!file) return;
					var tpl=jQuery(data.options.filesContainer).closest('.fileUploadContainer').children('.template-download').clone();
					tpl.find('.filename').text(file.name);
					tpl.find('img.preview').prop('src', file.small);
					if(file.error) tpl.find('.error').text(file.error);
					tpls.push(tpl);
				});
				return tpls;
			},
			formData: { targetdir: jQuery(e).data('targetdir'), name: jQuery(e).data('name')+'file'  }
		}).on('fileuploaddone', function(ev, data){
			var that=this;
			if(!jQuery(that).data('savevalues')) jQuery(that).data('savevalues', []);
			if(data.result.files){
				jQuery.each(data.result.files, function(index, file){
					jQuery(that).data('savevalues').push(file);
				});
			}
			jQuery(that).siblings('input.saveinput').val(JSON.stringify(jQuery(that).data('savevalues')));
		}).on('fileuploaddestroyed', function(ev, data){
			var that=this, filename=jQuery(data.context).find('.filename').html();
			jQuery.each(jQuery(that).data('savevalues'), function(index, file){
				if(file.name==filename) delete jQuery(that).data('savevalues')[index];
			});
			jQuery(that).siblings('input.saveinput').val(JSON.stringify(jQuery(that).data('savevalues')));
		});

		if(jQuery(e).siblings('input.saveinput').val()){
			var val=JSON.parse(jQuery(e).siblings('input.saveinput').val()), that=jQuery(e);
			jQuery(e).fileupload('option', 'done').call(that, null, {result: {files: val}});
			jQuery(e).trigger('fileuploaddone', [{result: {files: val}}]);
		}
	});
}

jQuery(function () {
	fileUploadInit();
	jQuery(document).ajaxComplete(function(){
		fileUploadInit();
	});


	if($('.dropZone').length){
		jQuery(document).bind('dragover', function (ev) {
			var dropZone = jQuery('.dropzone'),	timeout = window.dropZoneTimeout;
			if (!timeout) {	dropZone.addClass('in'); }
			else { clearTimeout(timeout); }
			var found = false, node = ev.target;

			do {
				if(node === dropZone[0]){
					found = true;
					break;
				}
				node = node.parentNode;
			} while (node != null);
			if (found) {
				dropZone.addClass('hover');
			} else {
				dropZone.removeClass('hover');
			}
			window.dropZoneTimeout = setTimeout(function () {
				window.dropZoneTimeout = null;
				dropZone.removeClass('in hover');
			}, 100);
		});
	}


});