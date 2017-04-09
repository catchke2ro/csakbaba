
function updateRows(){
	$('.clamp').each(function (index, e){
		if(!$(e).data('uncutted')) $(e).data('uncutted', $(e).text());
		$(e).html($(e).data('uncutted'));
		$clamp(e, {clamp: parseInt($(e).data('clamprows'))});
	});
}



/*function shorten(text, maxLength) {
	var ret = text;
	if (ret.length > maxLength) {
		ret = ret.substr(0,maxLength-3) + "...";
	}
	return ret;
}*/

function isMongoID(string){
	var regex = /^[0-9a-z]{24}$/;
	return regex.test(string);
}





function filter(data, categoryId, hash){
	var ul=$('div.productList').find('ul.products');
	ul.addClass('hidden');
	$('.productBeforeListLoad').addClass('show');
	data.push({name: 'category_id', value: categoryId});
	window.location.hash='#!filter:'+hash.join('+');
	$('html, body').animate({ scrollTop: 0 });
	$.ajax({
		url: '/market/productsajax',
		type: 'POST',
		data: data,
		complete: function(request){
			$('.productBeforeListLoad').removeClass('show');
			ul.html(request.responseText);
			ul.removeClass('hidden');
			ul.data('productscount', request.getResponseHeader('X-CSB-PRC'));
			ul.data('page', 1);
			productCountChange();
		}
	});
}


function initTooltip(root){
	$(root).tooltip({
		items: '.hasTooltip',
		content: function(){
			if($(this).siblings('.inputInfoText').length){
				return $(this).siblings('.inputInfoText').text();
			}
			return ($(this).siblings('.tooltip').length>0 ? $(this).siblings('.tooltip').html() : $(this).attr('title'));
		}
	});
}

function initRange(root){

	root.find('input.range').each(function(index, el){
		var e=$(el);
		e.before('<div class="slider"></div>');
		e.before('<div class="amount"></div>');
		var slider=e.siblings('.slider').slider({
			range: e.data('range') ? Boolean(e.data('range')) : false,
			min: parseInt(e.data('min')),
			max: parseInt(e.data('max')),
			step: parseInt(e.data('step')),
			values: e.val().split('-'),
			slide: function(ev, ui){
				e.siblings('.amount').html(ui.values[0]+($(this).slider('option', 'range') ? '-'+ui.values[1] : ''));
				e.val(ui.values[0]+($(this).slider('option', 'range') ? '-'+ui.values[1] : ''));
				e.trigger('change');
			}
		});
		e.siblings('.amount').html(slider.slider('values', 0)+(slider.slider('option', 'range') ? '-'+slider.slider('values', 1) : ''));
		return slider;
	});

}



$.extend(true, $.magnificPopup.defaults, {
	tClose: 'Bezárás (Esc)', tLoading: 'Töltés...',
	gallery: { tPrev: 'Előző (balra nyíl billentyű)', tNext: 'Következő (jobbra nyíl billentyű)', tCounter: '%curr% / %total%' },
	image: { tError: '<a href="%url%">A kép</a> nem betölthető.' },
	ajax: {	tError: '<a href="%url%">A tartalom</a> nem betölthető.' },
	fixedContentPos: true
});

$(document).ready(function(){


	var container=$('#container');
	window.isMobile=$('html').hasClass('mobile');

	window.scrollEventItem = window.isMobile ? 'body' : window;
	window.scrollEvent = window.isMobile ? 'touchmove' : 'scroll';

	if(window.self !== window.top){
		$('html').addClass('iframe');
	}

	initSelect2($('body'));


	var openableElements=$('header.sidebar, .header div.categorySelector, .header div.search, .header div.usermenu');
	$(document).on('click', function(ev){
		var el=$(ev.target);
		if(el.hasClass('skipDocumentClose')) return;
		var filtered=openableElements.filter(function(index, e){
			if(!($(e).hasClass('opened') || $(e).hasClass('slided'))) return false;
			if((el[0]==e || (el.closest('.opened') && el.closest('.opened')[0]==e) || (el.closest('.slided') && el.closest('.slided')[0]==e))) return false;
			return true;
		});
		filtered.removeClass('opened').removeClass('slided');
		if(window.isMobile){
			if(filtered.length && history.state!='base'){
				history.back();
			}
		}
	});


	var updateRowsTimeout;
	$(window).on('resize', function(){
		clearTimeout(updateRowsTimeout);
		updateRowsTimeout=setTimeout(function(){
			updateRows();
		}, 100);
	});
	updateRows();


	if(window.isMobile){
		history.pushState('base', null, null);
		window.historyBackFn={};
		openableElements.on('csbOpen', function(){
			history.pushState({id: 'somethingOpened'}, null, null);
		});
		openableElements.on('csbClose', function(){
			history.back();
		});

		window.onpopstate=function(ev){
			var state=(ev.state==null ? 'base' : ev.state);
			if(state=='base') $(document).trigger('click');

		};
	}


/******************************************************************************************************************************************
 * Á L T A L Á N O S
 ******************************************************************************************************************************************/

	$('a[href^=mailto]').each(function(){
		var a=$(this), address=a.html().replace('-nospam', '').replace('(KUKAC)', '@');
		a.attr('href', 'mailto:'+address);
		a.html(address);
	});

	imgPreview();

	$(document).on('hover, click', '.inputInfoIcon', function(){
		$(this).closest('li').find('.inputInfoText').addClass('visible');
	});

	if(window.location.hash=='#t'){
		$('html, body').scrollTop(0);
	}

	var logo=$('header.sidebar a.logo');
	$(window.scrollEventItem).on(window.scrollEvent, function(){
		var scrolled=$(this).scrollTop();
		if(scrolled > 10) logo.addClass('shrinked');
		else logo.removeClass('shrinked');
	});

	$(window.scrollEventItem).on(window.scrollEvent, function(){
		if($('html').scrollTop() > 10){
			$('.breadcrumb .social').addClass('fixed');
		} else {
			$('.breadcrumb .social').removeClass('fixed');
		}
	});

	$('div.breadcrumb div.social a').click(function(ev){
		var a=$(this);
		window.open(a.attr('href')+window.location.href, 'Facebook megosztás', 'status=1,height=400,width=400,resizeable=0');
		ev.preventDefault();
		return false;
	});

	$('div.breadcrumb.longBC li:nth-child(5)').click(function(){
		$(this).closest('.breadcrumb').removeClass('longBC');
	});

	/**
	 * Telefon input
	 */
	$('li.maskPhone').find('input').mask('99999999?9');

	/**
	 * Dátum input
	 */
	$('li.maskBirth').find('input').mask('9999-99-99');

	/** Number input **/
	jQuery('input[type=number]').each(function(index, e){
		var me=jQuery(e);
		if(e.type!='number'){
			var timer=0;
			jQuery(e).keyup(function(){
				clearTimeout(timer);
				timer=setTimeout(function(){
					if(me.attr('max') && parseInt(me.val())>me.attr('max')) me.val(me.attr('max'));
					if(me.attr('min') && parseInt(me.val())<me.attr('min')) me.val(me.attr('min'));
				}, 1000);
			});
		}
	});


	$(document).on('click', 'li.field.submit', function(ev){
		if($(ev.target).is($(this))) $(this).find('input').click();
	});

	/**
	 * Selector
	 */

	var categorySelector=$('div.categorySelector');
	categorySelector.find('.catSelMain').click(function(ev){
		var link=$(this);
		if(categorySelector.hasClass('opened')){
			if(window.isMobile) categorySelector.trigger('csbClose');
			categorySelector.removeClass('opened');
			if(categorySelector.find('.dropdown').data('main')==link.data('id')){
				ev.preventDefault();
				return false;
			}
		}
		categorySelector.trigger('csbOpen');
		categorySelector.find('.loading').addClass('show');
		$.ajax({
			url: '/index/categoryselector?main='+link.data('id'),
			complete: function(request){
				categorySelector.find('.loading').removeClass('show');
				categorySelector.find('.dropdown').html(request.responseText);
				categorySelector.find('.dropdown').data('main', link.data('id'));
				categorySelector.addClass('opened');
				/*$('.categorySelector div.title div.link').click(function(){
					$('.categorySelector').toggleClass('opened');
				});*/

				categorySelector.find('li').each(function(index, e){
					var li=$(e);
					li.children('div.link').click(function(ev){
						var ul=li.children('ul');
						if($(ev.target).hasClass('open')){
							if(!ul.hasClass('opened')){
								var doo=true, parentLi=li;
								while(doo){
									if(parentLi.parent().parent('.dropdown').length){ doo=false; }
									parentLi.siblings().find('ul').removeClass('opened');
									parentLi=parentLi.parent().closest('li');
								}
								ul.addClass('opened');
							} else {
								ul.removeClass('opened');
							}
						} else {
							window.location=$(this).data('url');
							return true;
						}

						ev.preventDefault();
						return false;
					});
				});
			}
		});
	});
	/**/



	var commentForm = $('div.commentForm');
	if(commentForm.length){
		$('div.commentForm #commentsubscribe').change(function(index, e){
			var productId=$(this).closest('.productDetails').data('id'), checked=$(this).prop('checked');
			$.ajax({
				url: '/index/commentsubscribe?pid='+productId+'&checked='+(checked ? 1 : 0), complete: function(){

				}
			})
		});
		$('div.commentForm form').submit(function(ev){
			var form=$(this), ul=form.closest('.commentContainer').find('.comments ul');
			$.ajax({
				url: '/index/comment', type: 'POST', data: form.serialize(), complete: function(request){
					ul.append(request.responseText);
					form[0].reset();
					setTimeout(function(){
						ul.find('li').last().removeClass('fresh');
					}, 500);
				}
			});
			ev.preventDefault();
			return false;
		});
	}







	/**
	 * CKEditor init
	 */
	if(!window.isMobile){ $('li.textarea.ck').find('textarea').ckeditor(); }


	/**
	 * ToolTip init
	 */
	initTooltip(document);



	/**
	 * FLASH messages
	 */
	setTimeout(function(){
		//$('div.flash.visible').removeClass('visible').delay(1000).remove();
	}, 10000);
	$(document).ajaxStop(function(){
		setTimeout(function(){
			//$('div.flash.visible').removeClass('visible').delay(1000).remove();
		}, 10000);
	});
	$(document).on('click', 'div.flash .close', function(){
		$(this).parent().removeClass('visible').delay(1000).remove();
	});


	/**
	 * RADIO init
	 */
	$(document).on('change', 'input[type=radio], input[type=checkbox]', function(){
		var label=$(this).closest('label');
		if($(this).prop('type')=='radio'){ label.siblings().removeClass('checked'); }
		$(this).prop('checked') ? label.addClass('checked') : label.removeClass('checked');
	});

	$(document).on('disableChange', 'input', function(ev, disabled){
		$(this).closest('label').toggleClass('disabled', disabled);
	});


	/**
	 * Usermenu & Category selector
	 */
	$('.usermenu a.login').click(function(){
		var usermenu=$('.usermenu');
		usermenu.trigger(usermenu.hasClass('opened') ? 'csbClose' : 'csbOpen');
		usermenu.toggleClass('opened');
	});

	$('div.header div.search').find('input[type=submit]').click(function(ev){
		if($(this).css('cursor')=='pointer'){
			ev.preventDefault();
			ev.stopPropagation();
			var search=$(this).closest('div.search');
			search.trigger(search.hasClass('opened') ? 'csbClose' : 'csbOpen');
			search.toggleClass('opened');
			$('input#q').focus();
			return false;
		}
	});

	$('div.header div.search').find('input#q').on('keypress', function(ev){
		if(ev.which == 13) $(this).closest('form').submit();
	});






	/**
	 * Sidebar functions
	 */
	var contentFooterDivs=$('div.content, div.footer, div.subheader');
	$('html:not(.mobile) header.sidebar').hover(
		function(){
			contentFooterDivs.addClass('slided');
			$('header.sidebar').addClass('slided');
		},
		function(){
			contentFooterDivs.removeClass('slided');
			$('header.sidebar').removeClass('slided');
		}
	);
	$('.stick_it a').click(function(){
		var sb=$(this).closest('header.sidebar'), val=!sb.hasClass('stick');
		$.cookie('stick', val, {expires: 30, path: '/'});
		val ? sb.addClass('stick') : sb.removeClass('stick');
		val ? contentFooterDivs.addClass('stick') : contentFooterDivs.removeClass('stick');

		/*if($('#container.fooldal').length){
			setTimeout(function(){
				$('div.sliderContainer').find('.slider').bxSlider('reloadShow');
			}, 2000);
		}*/
	});
	$('ul.navigation').find('a.user').click(function(ev){
		$(this).closest('li').toggleClass('opened');
		$(this).closest('.menu').toggleClass('menuDropped');
		ev.preventDefault();
		return false;
	});

	/**
	 * Színválasztó
	 */
	var colorClickEnabled=true;
	$('.colorChanger').find('a').click(function(ev){
		if(!colorClickEnabled){
			ev.preventDefault();
			return false;
		}
		colorClickEnabled=false;
		var a=$(this), li=a.parent(), col=li.data('col'), link=$('link[href^="/stylesheets/css/global_"], link[href^="/min/?f=/stylesheets/css"]');
		$.get('/stylesheets/css/global_'+col+'.css', function(contents){
			var newLink=link.clone();
			$('head').append('<style type="text/css" id="animationHead">html * {-webkit-transition: background-color 2s, color 2s !important; transition: background-color 2s, color 2s !important;}</style>');
			newLink.attr('href', newLink.attr('href').replace(/stylesheets\/css\/global\_[^.]+\.css/i, 'stylesheets/css/global_'+col+'.css'));
			link.after(newLink);
			setTimeout(function(){
				link.remove();
				$('#animationHead').remove();
				colorClickEnabled=true;
			}, 2000);
			li.siblings().removeClass('active');
			li.addClass('active');
			$.cookie('color', col, {expires: 365, path: '/'});
		});
	});








/******************************************************************************************************************************************
 * T E R M É K L I S T A
 ******************************************************************************************************************************************/

	







/******************************************************************************************************************************************
 * F E L H A S Z N Á L Ó   -   A D A T M Ó D O S Í T Á S
 ******************************************************************************************************************************************/

	if(container.hasClass('adatmodositas')){
		$('div.adatmodositas').find('.field.changePswd').find('label').click(function(){
			var newPswd=$(this).closest('ul').find('.newPswd');
			newPswd.first().hasClass('hidden') ? newPswd.removeClass('hidden') : newPswd.addClass('hidden');
		});
	}






/******************************************************************************************************************************************
 * F E L H A S Z N Á L Ó   -   T E R M É K K E Z E L É S
 ******************************************************************************************************************************************/





/******************************************************************************************************************************************
 * F E L H A S Z N Á L Ó   -   R E N D E L É S E K
 ******************************************************************************************************************************************/

	







	/******************************************************************************************************************************************
	 * F E L H A S Z N Á L Ó   -   E G Y E N L E G F E L T Ö L T É S
	 ******************************************************************************************************************************************/

	if(container.hasClass('egyenleg')){
		$('div.balance').find('input[name=amount]').change(function(){
			var input=$(this), availP=$('.chargeAvail');
			if(parseInt(input.val()) < parseInt(input.data('min'))){ input.val(input.data('min'));}
			availP.addClass('active').find('span').html(Math.floor(parseInt(input.val())/parseInt(input.data('upload'))));
		}).keyup(function(){
			var input=$(this), cto;
			clearTimeout(cto);
			cto=setTimeout(function(){
				input.trigger('change');
			}, 2000);
		});

	}








/******************************************************************************************************************************************
 * F E L H A S Z N Á L Ó   -   P R O F I L
 ******************************************************************************************************************************************/

	if(container.hasClass('profil')){
		$('.profile .marketProducts a.tab').click(function(){
			var me=jQuery(this), ul=me.closest('.profileFilter').siblings('.productList').find('ul');
			me.siblings('a.tab').removeClass('active');
			me.addClass('active');
			ul.find('li').removeClass('active');
			ul.find('li.'+me.data('div')).addClass('active');
		});
		$('.profile .marketProducts a.tab[data-div=status1]').trigger('click');
	}



/******************************************************************************************************************************************
 * P I A C - S Z Ű R Ő
 ******************************************************************************************************************************************/
	if(container.hasClass('piac')){

		if($('.subheaderInner').children().length==0){
			$('.subheader').hide();
			$('.breadcrumb').css('top', 40);
		}

		var cto;
		$('.subheader form').find('input').change(function(){
			var input=$(this);
			clearTimeout(cto);
			cto=setTimeout(function(){
				input.closest('form').submit();
			}, 500);
		});

		$('.subheader form').submit(function(ev){
			var values=$(this).closest('form').serializeArray(), form=$(this).closest('form'), input, submitValues=[], hash=[], name='';
			for(var i in values){
				name=values[i].name.replace(/[\[\]]/ig, '');
				input=form.find('input[name^='+name+']');
				if(input.attr('type')=='hidden' || (input.attr('type')=='text' && input.val()=='') || (typeof input.data('max')!='undefined' && input.val()==input.data('min')+'-'+input.data('max'))){ continue; }
				submitValues.push(values[i]);
				hash.push(''+name+':'+values[i].value);
			}
			filter(submitValues, form.find('input[name=category_id]').val(), hash);
			if(window.isMobile){
				$('.subheader').find('.option').removeClass('opened');
			}
			ev.preventDefault();
			return false;
		});

		if(window.location.hash.length && window.location.hash.indexOf('#!filter:')!==-1){
			var hash=window.location.hash.replace('#!filter:', '').split('+'), form=$('.subheader form');
			for(var i in hash){
				var name=hash[i].split(':')[0], value=hash[i].split(':')[1], input=form.find('input[name^='+name+']');
				if(input.attr('type')=='checkbox' || input.attr('type')=='radio'){
					input.filter('*[value='+value+']').prop('checked', true).trigger('change');
				}	else {
					input.val(value);
				}
			}
			//form.submit();
		}

		$('input.range').each(function(index, el){
			initRange(el);
		});
	}






/******************************************************************************************************************************************
 * P I A C   -   T E R M É K O L D A L
 ******************************************************************************************************************************************/
	







/******************************************************************************************************************************************
 * F Ő O L D A L
 ******************************************************************************************************************************************/

	if(container.hasClass('fooldal')){
		var listsSlider=[];


	}



  /*$('.promote.first .promoteSlider .productList .products').bxSlider({
	  ticker: false,
	  pager: false,
	  speed: 1000,
	  //auto: true,
	  //autoStart: true,
	  controls: true ,
	  minSlides: 1,
    maxSlides: 12,
	  slideWidth: 150 ,
	  slideMargin: 10,
	  hideControlOnEnd: true
	  //infiniteLoop: true
  });*/


	var mainSearch=$('#container.fooldal .sliderContainer .search li.text input, div.header div.search li.text input');
	mainSearch.autocomplete({
		source: '/shop/autocomplete',
		create: function(){
			$(this).data('uiAutocomplete')._renderItem=function(ul, item){
				return $('<li>').append('<div class="img" style="background-image: url('+item.image+')"></div><a><span>'+item.label+'</span><span>'+item.price+'</span></a>').appendTo(ul);
			};
		},
		select: function(event, ui){
			//alert(window.location.origin+ui.item.value);
			window.setTimeout(function(){
				location.href=window.location.origin+ui.item.value;
			}, 0);
			return false;
		},
		open: function(event, ui){
			var input=$(this);
			ga('send', 'event', 'search', 'autocomplete', input.val());
			input.data('uiAutocomplete').widget().outerWidth($(event.target).outerWidth());
			$('.ui-autocomplete').off('menufocus hover mouseover mouseenter');
		}
	});





	function mobileMenuOpen(open){
		var sidebar=$('header.sidebar');
		if(open){
			sidebar.trigger('csbOpen');
			sidebar.addClass('slided');
		} else {
			sidebar.trigger('csbClose');
			//sidebar.removeClass('slided');
			sidebar.removeClass('stick');
		}
	}

	if(window.isMobile){



		$('form.filter .option a').click(function(){
			if(!$(this).closest('.option').hasClass('opened')){
				$('form.filter .option').removeClass('opened');
			}
			$(this).closest('.option').toggleClass('opened');
		});

		function contentScrollToggle(enable){
			if(enable==true){
				$('.content').unbind('touchmove');
			} else {
				$('.content').bind('touchmove', function(e){e.preventDefault()});
			}
		}




		$("header.sidebar").swipe({
			swipe:function(event, direction, distance, duration, fingerCount) {
				mobileMenuOpen(direction=='right');
			},
			threshold:0
		});

	}

	$('.hamburger_menu').click(function(){
		mobileMenuOpen(!$('header.sidebar').hasClass('slided'));
	});

});





