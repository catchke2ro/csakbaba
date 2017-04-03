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


function generateSlug(text){
	var onlyLetters=(typeof arguments[1] != 'undefined') ? arguments[1] : false;
	var spaceRpl=onlyLetters ? '' : '-';
	var hyphenRpl=onlyLetters ? '' : '-';
	var slug=text.toLowerCase()
			.replace(new RegExp("[ \/\.\,]", 'g'), spaceRpl);

	slug = charMapReplace(slug);
	slug = slug.trim()
			.replace(new RegExp("[^a-z0-9\-]", 'g'),'')
			.replace(new RegExp("[\-]+", 'g'), hyphenRpl)
			.replace(new RegExp("\-$", 'g'), '');
	return slug;
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



function charMapReplace(text){
	return text
			.replace(new RegExp('[ÄÀÁÂÃÅǺĂǍ]', 'g'), 'A')
			.replace(new RegExp('[ÖÒÔÕŌŎǑŐƠǾ]', 'g'), 'O')
			.replace(new RegExp('[ÜŮÙÚÛŨŬŰŲƯǓǕǗǙǛ]', 'g'), 'U')
			.replace(new RegExp('[ß]', 'g'), 'ss')
			.replace(new RegExp('[äàáâãǻăǎª]', 'g'), 'a')
			.replace(new RegExp('[öòôõōŏǒőóÓơǿº]', 'g'), 'o')
			.replace(new RegExp('[üůùúûũŭűųưǔǖǘǚǜ]', 'g'), 'u')
			.replace(new RegExp('[ČĈĊ]', 'g'), 'C')
			.replace(new RegExp('[ĎĐ]', 'g'), 'D')
			.replace(new RegExp('[ĚÈÉÊËĔĖ]', 'g'), 'E')
			.replace(new RegExp('[ŇÑ]', 'g'), 'N')
			.replace(new RegExp('[ŘŔŖ]', 'g'), 'R')
			.replace(new RegExp('[ŠŜȘ]', 'g'), 'S')
			.replace(new RegExp('[ŤŢȚŦ]', 'g'), 'T')
			.replace(new RegExp('[Ž]', 'g'), 'Z')
			.replace(new RegExp('[čĉċ©]', 'g'), 'c')
			.replace(new RegExp('[ďđ]', 'g'), 'd')
			.replace(new RegExp('[ěèéêëĕė]', 'g'), 'e')
			.replace(new RegExp('[ňñŉ]', 'g'), 'n')
			.replace(new RegExp('[řŕŗ]', 'g'), 'r')
			.replace(new RegExp('[šŝșſ]', 'g'), 's')
			.replace(new RegExp('[ťţțŧ]', 'g'), 't')
			.replace(new RegExp('[ž]', 'g'), 'z')
			.replace(new RegExp('[°₀]', 'g'), '0')
			.replace(new RegExp('[¹₁]', 'g'), '1')
			.replace(new RegExp('[²₂]', 'g'), '2')
			.replace(new RegExp('[³₃]', 'g'), '3')
			.replace(new RegExp('[⁴₄]', 'g'), '4')
			.replace(new RegExp('[⁵₅]', 'g'), '5')
			.replace(new RegExp('[⁶₆]', 'g'), '6')
			.replace(new RegExp('[⁷₇]', 'g'), '7')
			.replace(new RegExp('[⁸₈]', 'g'), '8')
			.replace(new RegExp('[⁹₉]', 'g'), '9')
			.replace(new RegExp('[æǽ]', 'g'), 'ae')
			.replace(new RegExp('[ÆǼ]', 'g'), 'AE')
			.replace(new RegExp('[å]', 'g'), 'aa')
			.replace(new RegExp('[Ð]', 'g'), 'Dj')
			.replace(new RegExp('[ð]', 'g'), 'dj')
			.replace(new RegExp('[ƒ]', 'g'), 'f')
			.replace(new RegExp('[ĜĠ]', 'g'), 'G')
			.replace(new RegExp('[ĝġ]', 'g'), 'g')
			.replace(new RegExp('[ĤĦ]', 'g'), 'H')
			.replace(new RegExp('[ĥħ]', 'g'), 'h')
			.replace(new RegExp('[ÌÍÎÏĨĬǏĮ]', 'g'), 'I')
			.replace(new RegExp('[Ĳ]', 'g'), 'IJ')
			.replace(new RegExp('[ìíîïĩĭǐį]', 'g'), 'i')
			.replace(new RegExp('[ĳ]', 'g'), 'ij')
			.replace(new RegExp('[Ĵ]', 'g'), 'J')
			.replace(new RegExp('[ĵ]', 'g'), 'j')
			.replace(new RegExp('[ĹĽĿ]', 'g'), 'L')
			.replace(new RegExp('[ĺľŀ]', 'g'), 'l')
			.replace(new RegExp('[ØŒ]', 'g'), 'OE')
			.replace(new RegExp('[øœ]', 'g'), 'oe')
			.replace(new RegExp('[Þ]', 'g'), 'TH')
			.replace(new RegExp('[þ]', 'g'), 'th')
			.replace(new RegExp('[Ŵ]', 'g'), 'W')
			.replace(new RegExp('[ŵ]', 'g'), 'w')
			.replace(new RegExp('[ÝŸŶ]', 'g'), 'Y')
			.replace(new RegExp('[ýÿŷ]', 'g'), 'y')
}

