function imgPreview(){
	var $container = $('<div/>').attr('id', 'imgPreviewContainer').append('<img/>').hide().css('position', 'absolute').appendTo('body'),
			$img = $('img', $container).css({width: '200px'});

	$(document).on('mouseover', '.previewImg', function(e){
		var link = this;
		$container.addClass('loading').show();
		$img.load(function () {
			$container.removeClass('loading');
			$img.show();
		}).attr('src', $(link).attr('data-preview'));

		var height=jQuery(window).height();
		if((e.screenY+$container.height())>=height){
			$container.css({
				top: e.pageY - $container.height() - 10 + 'px',
				left: e.pageX + 10 + 'px'
			});
		} else {
			$container.css({
				top: e.pageY + 10 + 'px',
				left: e.pageX + 10 + 'px'
			});
		}

	});
	$(document).on('mouseout', '.previewImg', function () {

		$container.hide();
		$img.unbind('load').attr('src', '').hide();

	});

	$(document).on('mousemove', '.previewImg', function(e){
		var height=jQuery(window).height();
		if((e.screenY+$container.height())>=height){
			$container.css({
				top: e.pageY - $container.height() - 10 + 'px',
				left: e.pageX + 10 + 'px'
			});
		} else {
			$container.css({
				top: e.pageY + 10 + 'px',
				left: e.pageX + 10 + 'px'
			});
		}
	});


}