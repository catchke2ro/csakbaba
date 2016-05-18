function openIframePopup(id, url, options){
	if(typeof options == 'undefined') options = {};

	if(window.isMobile){
		$('html').data('scrollTop', $(document).scrollTop());
		$('body').addClass('noScroll');
	}

	if(window.location.hash.length > 1){
		window.history.replaceState(undefined, undefined, '#'+id);
	} else {
		window.location.hash = '#'+id;
	}
	$(window).on('hashchange', function(){
		if(location.href.indexOf('#'+id) < 0) $.magnificPopup.close();
	});

	options.type = 'iframe';
	options.items = {src: url};
	options.callbacks = {
		open: function(){
		},
		close: function(){
			if(window.location.hash == '#'+id) window.history.replaceState(undefined, undefined, '#_');
			if(window.isMobile){
				$('body').removeClass('noScroll');
				$(document).scrollTop($('html').data('scrollTop'));
			}

		}
	};

	$.magnificPopup.open(options);

}


//Form functions
function initSelect2(root){
	var select2Selects=root.find('select.select2');

	var select2Format=function(state){
		var ret='<span>' + state.text + '</span>';
		if($(state.element).data('image')) ret='<img src="' + $(state.element).data('image') + '"/>'+ret;
		return ret;
	};
	select2Selects.each(function(index, e){
		if(typeof $(e).data('select2') !='undefined') return;
		$(e).select2({
			minimumResultsForSearch: $(e).hasClass('showSearch') ? 1 : -1,
			formatResult: select2Format,
			formatSelection: select2Format,
			dropdownCssClass: $(e).data('dropdownclass') ? $(e).data('dropdownclass') : '',
			escapeMarkup: function(m) { return m; },
			templateSelection: function (data) {
				return data.text.replace(new RegExp('\&nbsp;', 'g'), '');
			}
		});
		if($(e).prop('readonly')=='readonly') $(e).select2('readonly', true);
	});
}


function cbSetDisabled(cb, disabled){
	var cb = $(cb);
	cb.prop('disabled', disabled);
	cb.trigger('disableChange', disabled);
}
