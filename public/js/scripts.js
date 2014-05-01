Shadowbox.init();
CKEDITOR.basePath='/js/ckfrontend/';
CKEDITOR.timestamp='aaaa';

function shorten(text, maxLength) {
	var ret = text;
	if (ret.length > maxLength) {
		ret = ret.substr(0,maxLength-3) + "...";
	}
	return ret;
}

function filter(data, categoryId, hash){
	$('.content').find('ul.products').addClass('hidden');
	data.push({name: 'category_id', value: categoryId});
	window.location.hash='#!filter:'+hash.join('+');
	$.ajax({
		url: '/market/filter',
		type: 'POST',
		data: data,
		complete: function(request){
			$('div.productList').replaceWith(request.responseText);
			$('.content').find('ul.products').removeClass('hidden');
		}
	});
}

function productEditFormLoad(category_id, product_id, formDiv){
	if($('.productEditAddForm form').length){
		if(confirm('A nem mentett változtatások elvesznek. Folytatod?')){
			$('.productEditAddForm').html('');
		}	else {
			return;
		}
	}

	$.ajax({
		url: '/shop/userproducteditform',
		data: {category_id: category_id, product_id: product_id},
		complete: function(request){
			$(formDiv).html(request.responseText);
			$(formDiv).addClass('opened');
			$('html, body').animate({ scrollTop: formDiv.offset().top-100	}, 500);
			if(!window.isMobile){ $(formDiv).find('textarea').ckeditor();}
			$(formDiv).find('input.range').each(function(index, el){
				initRange(el);
			});
		}
	});
}

function productPromoteFormLoad(product_id, formDiv){
	$.ajax({
		url: '/shop/userpromote',
		data: {product_id: product_id},
		complete: function(request){
			$(formDiv).html(request.responseText);
			$(formDiv).addClass('opened');
			$('html, body').animate({ scrollTop: formDiv.offset().top-100	}, 500);
			if(!window.isMobile){ $(formDiv).find('textarea').ckeditor();}
		}
	});
}

function promoteAllFormLoad(formDiv){
	$.ajax({
		url: '/shop/userpromote',
		data: {product_id: 'all'},
		complete: function(request){
			$(formDiv).html(request.responseText);
			$(formDiv).addClass('opened');
			$('html, body').animate({ scrollTop: formDiv.offset().top-100	}, 500);
		}
	});
}

function productRenewFormLoad(product_id, formDiv, renew){
	$.ajax({
		url: '/shop/userrenew',
		data: {product_id: product_id, renew: renew},
		complete: function(request){
			if(renew){
				ga('send', 'event', { 'eventCategory': 'product',	'eventAction': 'renew', 'hitCallback': function(){
						window.location.hash='#t';
						window.location.reload();
				}});
				setTimeout(function(){
					window.location.hash='#t';
					window.location.reload();
				}, 2000);
			}
			$(formDiv).html(request.responseText);
			$(formDiv).addClass('opened');
			$('html, body').animate({ scrollTop: formDiv.offset().top-100	}, 500);
		}
	});
}

function initTooltip(el){
	$(el).tooltip({
		items: '.hasTooltip',
		content: function(){ return ($(this).siblings('.tooltip').length>0 ? $(this).siblings('.tooltip').html() : $(this).attr('title')); }
	});
}

function initRange(el){
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
}

$(document).ready(function(){


	var container=$('#container');
	window.isMobile=$('html').hasClass('mobile');





/******************************************************************************************************************************************
 * Á L T A L Á N O S
 ******************************************************************************************************************************************/

	$('a[href^=mailto]').each(function(){
		var a=$(this), address=a.html().replace('-nospam', '').replace('(KUKAC)', '@');
		a.attr('href', 'mailto:'+address);
		a.html(address);
	});

	if(window.location.hash=='#t'){
		$('html, body').scrollTop(0);
	}

	$(window).scroll(function(){
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

	/**
	 * Telefon input
	 */
	$('li.maskPhone').find('input').mask('(9?9) 999-999?9');

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

	/**
	 * Selector
	 */
	$.ajax({
		url: '/index/categoryselector',
		complete: function(request){
			$('div.categorySelector').replaceWith(request.responseText);
			$('.categorySelector div.title div.link').click(function(){
				$('.categorySelector').toggleClass('opened');
			});

			$('div.categorySelector').find('li').each(function(index, e){
				var li=$(e);
				li.children('div.link').click(function(ev){
					var ul=li.children('ul');
					if(ul.length==0 || $(ev.target).hasClass('jump')){
						window.location=$(this).data('url');
						return true;
					}
					if(!ul.hasClass('opened')){
						var doo=true, parentLi=li;
						while(doo){
							if(parentLi.parent().parent('.categorySelector').length){ doo=false; }
							parentLi.siblings().find('ul').removeClass('opened');
							parentLi=parentLi.parent().closest('li');
						}
						ul.addClass('opened');
					} else {
						ul.removeClass('opened');
					}
					ev.preventDefault();
					return false;
				});
			});

		}
	})




	/**
	 * Shadowbox open
	 */
	$('a.sbLink').click(function(ev){
		var a=$(this);
		Shadowbox.open({ content: a.data('url'), player: 'iframe' });
		ev.preventDefault();
		return false;
	});


	/**
	 * CKEditor init
	 */
	if(!window.isMobile){ $('li.textarea.ck').find('textarea').ckeditor(); }


	/**
	 * ToolTip init
	 */
	initTooltip(document);


	/**
	 * BxSlider init
	 */
	//$('.slider').bxSlider({ mode: "fade", ticker: false, pager: false, auto: true, autoStart: true, controls: false });


	/**
	 * FLASH messages
	 */
	setTimeout(function(){
		$('div.flash.visible').removeClass('visible').delay(1000).remove();
	}, 10000);
	$(document).ajaxStop(function(){
		setTimeout(function(){
			$('div.flash.visible').removeClass('visible').delay(1000).remove();
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


	/**
	 * Usermenu & Category selector
	 */
	$('.usermenu a.login').click(function(){
		$('.usermenu').toggleClass('opened');
	});
	$('div.header div.search').find('input[type=submit]').click(function(ev){
		if($(this).closest('form').find('input[type=text]').width()==0){
			$(this).closest('div.search').toggleClass('opened');
			ev.preventDefault();
			return false;
		}
	});

	$(document).on('click', function(ev){
		if($(ev.target).closest('div.header').length>0 || $(ev.target).hasClass('header')){
			return true;
		}
		$('div.header').find('div.headerInner').children().removeClass('opened');
	});


	/**
	 * Sidebar functions
	 */
	var contentFooterDivs=$('div.content, div.footer');
	$('html:not(.mobile) header.sidebar').hover(
		function(){ contentFooterDivs.addClass('slided');},
		function(){ contentFooterDivs.removeClass('slided');}
	);
	$('.stick_it a').click(function(){
		var sb=$(this).closest('header.sidebar'), val=!sb.hasClass('stick');
		$.cookie('stick', val, {expires: 30, path: '/'});
		val ? sb.addClass('stick') : sb.removeClass('stick');
		val ? contentFooterDivs.addClass('stick') : contentFooterDivs.removeClass('stick');
	});
	$('ul.navigation').find('a.user').click(function(ev){
		$(this).closest('li').toggleClass('active');
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
		var a=$(this), li=a.parent(), col=li.data('col'), link=$('link[href^="/css/global_"], link[href^="/min/?f=/css"]');
		$.get('/css/global_'+col+'.css', function(contents){
			var newLink=link.clone();
			$('head').append('<style type="text/css" id="animationHead">html * {-webkit-transition: background-color 2s, color 2s !important; transition: background-color 2s, color 2s !important;}</style>');
			newLink.attr('href', newLink.attr('href').replace(/css\/global\_[^.]+\.css/i, 'css/global_'+col+'.css'));
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

	$('div.productList .back .productName a').each(function(index, e){
		$(e).text(shorten($(e).text(), 40));
	});
	$('div.productList .front .productName a').each(function(index, e){
		$(e).text(shorten($(e).text(), 60));
	});
	jQuery('div.productGallery img').imgPreview({
		imgCSS: { 'max-height': '250px' },
		preloadImages: false,
		srcAttr: 'data-mid'
	});






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

	if(container.hasClass('felhasznalotermekek')){

		function categorySelect(parent){
			$.ajax({
				url: '/shop/categoryselectlist', data: {parent: parent}, complete: function(request){
					var cs=$('.categorySelect');
					if(request.responseText=='LOAD'){
						productEditFormLoad($('.categorySelect select').last().val(), false, cs.next());
						cs.hide();
					}
					cs.html(request.responseText);
				}
			});
		}

		$(document).on('change', '.categorySelect select', function(){
			var values=[], changedSelect=$(this), num=changedSelect.attr('name').replace('selcat', '');
			$('.categorySelect select').each(function(index, e){
				if(parseInt(num) < parseInt($(e).attr('name').replace('selcat', ''))) return;
				values.push($(e).val());
			});
			var foundEmpty=false, filteredValues=[];
			for(var i in values){
				if(values[i]=='' || foundEmpty) foundEmpty=true;
				else filteredValues.push(values[i]);
			}
			categorySelect(filteredValues.join(';'));
		});
		$('.userAddProduct').click(function(){
			categorySelect('');
			$('.categorySelect').show();
		});


		jQuery('div.productList.user div.productImages img').imgPreview({
			imgCSS: { width: '200px' },
			preloadImages: false,
			srcAttr: 'data-mid'
		});

		$(document).on('click', '.userEditProduct', function(){
			var prodDiv=$(this).closest('li.product');
			productEditFormLoad(prodDiv.data('categoryid'), prodDiv.data('id'), prodDiv.next());
		});

		$(document).on('click', '.userPromoteProduct', function(){
			var prodDiv=$(this).closest('li.product');
			productPromoteFormLoad(prodDiv.data('id'), prodDiv.next());
		});

		$(document).on('click', '.userPromoteAll', function(){
			promoteAllFormLoad($(this).siblings('.productEditAddForm'));
		});

		$(document).on('click', '.userRenewProduct', function(){
			var prodDiv=$(this).closest('li.product');
			productRenewFormLoad(prodDiv.data('id'), prodDiv.next(), false);
		});

		$(document).on('click', 'a.renewButton', function(){
			var prodDiv=$(this).closest('.productEditAddForm').prev();
			productRenewFormLoad(prodDiv.data('id'), prodDiv.next(), true);
		});

		$(document).on('click', 'div.productEditAddForm li.cancelButton button', function(){
			$(this).closest('.productEditAddForm').html('');
		});

		$(document).on('click', '.userDeleteProduct', function(){
			if(!confirm('Biztosan törlöd a terméket?')){ return; }
			$.ajax({
				url: '/shop/userproductdelete/productid/'+$(this).closest('li.product').data('id'),
				complete: function(){window.location.hash='#t'; window.location.reload(); }
			});
		});

		$(document).on('click', '.previewButton button', function(){
			var form=$(this).closest('form');
			Shadowbox.open({
				content: '/shop/userproductpreview?'+form.serialize(),
				player: 'iframe'
			});
		});

		$(document).on('submit', 'div.productEditAddForm > form', function(e){
			var form=this;
			$('.formLoad').show();
			$.ajax({
				url: '/shop/userproducteditform', type: 'POST', data: $(form).serialize(), complete: function(request){
					var peadf=$(form).closest('.productEditAddForm');
					if(request.status!=400){
						peadf.html('');
						if(peadf.hasClass('add')){
							ga('send', 'event', 'product', 'add');
							peadf.siblings('.productList').find('ul').prepend('<div class="productEditAddForm"></div>');
							peadf.siblings('.productList').find('ul').prepend(request.responseText);
							$('div.productFilter').find('a[data-status=1]').trigger('click');
						} else {
							peadf.prev().replaceWith(request.responseText);
							peadf.removeClass('opened');
							$('div.productFilter').find('a.active').trigger('click');
						}
					} else {
						peadf.html(request.responseText);
						if(!window.isMobile){ peadf.find('textarea').ckeditor(); }
						initTooltip(peadf);
						peadf.find('input.range').each(function(index, el){
							initRange(el);
						});
					}
				}
			});
			e.preventDefault();
			return false;
		});




		$(document).on('change', '.userpromote input[type=checkbox]', function(){
			var promote=$(this).closest('div.userpromote');
			var submit=promote.find('input[type=submit]'), balance=parseInt(promote.find('div.balance').find('span').text()), form=promote.find('form');
			if(typeof submit.data('value')=='undefined'){ submit.data('value', submit.prop('value')); }
			var price=0, data=form.data('prices');
			form.find('input[name*=types]').each(function(index, e){
				if(jQuery(e).prop('checked') && !jQuery(e).prop('disabled')){ price+=data[jQuery(e).val()];}
			});
			if(balance<price){
				submit.prop('disabled', 'disabled');
				promote.find('li.note').addClass('active');
			} else {
				submit.prop('disabled', '');
				promote.find('li.note').removeClass('active');
			}
			submit.prop('value', submit.data('value')+' ('+price+' Ft)');
		});

		$(document).on('submit', 'div.productEditAddForm .userpromote form', function(e){
			var form=$(this);
			var pid=form.closest('.userpromote').data('productid');
			$.ajax({
				url: '/shop/userpromote?product_id='+pid, type: 'POST', data: form.serialize(), complete: function(request){
					var peadf=form.closest('.productEditAddForm');
					if(request.status!=400){
						ga('send', 'event', 'product', 'promote');
						if(pid!='all'){ peadf.prev().replaceWith(request.responseText); }
						else { peadf.replaceWith(request.responseText);}
						peadf.removeClass('opened');
					} else {
						peadf.html(request.responseText);
						initTooltip(peadf);
					}
				}
			});
			e.preventDefault();
			return false;
		});



		$('.productFilter a').click(function(){
			if($('.productEditAddForm form').length){
				if(confirm('A nem mentett változtatások elvesznek. Folytatod?')){
					$('.productEditAddForm').html('');
				}	else {
					return;
				}
			}
			var me=jQuery(this), ul=$('ul.products.user');
			me.siblings('a').removeClass('active');
			me.addClass('active');
			ul.find('li').removeClass('active');
			ul.find('li.status'+me.data('status')).addClass('active');
		});
		$('.productFilter a[data-status=1]').trigger('click');

	}







/******************************************************************************************************************************************
 * F E L H A S Z N Á L Ó   -   R E N D E L É S E K
 ******************************************************************************************************************************************/

	if(container.hasClass('ertekelesek')){
		$('.orders a.tab').click(function(){
			var me=jQuery(this), ul=me.closest('.orders').find('div.productList > ul');
			me.siblings('a.tab').removeClass('active');
			me.addClass('active');
			ul.find('li').removeClass('active');
			ul.find('li.'+me.data('div')).addClass('active');
		});
		$('.orders a.tab[data-div=unrated]').trigger('click');

		$('li.order.unrated .rating').each(function(index, e){
			$.ajax({
				url: '/user/ratingform', type: 'GET', data: {oid: $(e).closest('li').data('orderid'), seller: $(e).closest('li').data('seller')},
				complete: function(request){
					$(e).html(request.responseText);
				}
			})
		});

		$(document).on('click', '.ratingForm input[type=submit]', function(ev){
			var me=jQuery(this), rating=me.closest('div.rating');
			$.ajax({
				url: '/user/ratingform?oid='+me.closest('li.product').data('orderid')+'&seller='+me.closest('li.product').data('seller'),
				type: 'POST', data: me.closest('form').serialize(),
				complete: function(request){
					if(request.responseText==''){window.location.hash='#t';  window.location.reload();}
					rating.html(request.responseText);
				}
			});
			ev.preventDefault();
			return false;
		});
	}







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
	$('div.productDetails .productImagesWrapper .productImages').bxSlider({
		adaptiveHeight: false, infiniteLoop: false
	});
	if(container.hasClass('details')){
		$('div.addToCart a').click(function(){
			var a=$(this), div=a.parent();
			if(div.hasClass('haveToLogin')){
				$('div.usermenu').addClass('opened');
			} else {
				div.siblings('.cassa').addClass('opened');
			}
		});

		$('div.cassaSubmit').find('input[type=submit]').click(function(ev){
			var submit=$(this);
			//if(submit.closest('form').find('input[type=checkbox]').prop('checked')==true){
				submit.closest('form').submit();
			//}
			ev.preventDefault();
			return false;
		});

		$('div.commentForm form').submit(function(ev){
			var form=$(this), ul=form.closest('.commentContainer').find('.comments ul');
			$.ajax({
				url: '/market/comment', type: 'POST', data: form.serialize(), complete: function(request){
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







/******************************************************************************************************************************************
 * F Ő O L D A L
 ******************************************************************************************************************************************/

	function tut(){
		if($('div.tut').length){
			$('div.tut').toggleClass('hidden');
			return;
		}
		$('header.sidebar .menu').append('<div class="tut tutBalmenu hidden"></div>');
		$('div.categorySelector').append('<div class="tut tutKategoriak hidden"></div>');
		$('div.search').append('<div class="tut tutKereses hidden"></div>');
		$('div.usermenu').append('<div class="tut tutLogin hidden"></div>');
		$('div.footer').append('<div class="tut tutFooter hidden"></div>');
		$('.tut').removeClass('hidden');
	}

	$('.buttons a.tutorial').click(function(){
		tut();
	});

	if(container.hasClass('fooldal')){
		var listsSlider=[];
		$('div.lists').each(function(index, e){
			listsSlider[index]=$(e).find('.listsInner').bxSlider({ controls: false, pager: false, maxSlides: 1, touchEnabled: false });
			$(e).find('.listMenu').find('a').click(function(){
				var a=$(this);
				a.siblings().removeClass('active');
				a.addClass('active');
				listsSlider[index].goToSlide(a.data('list'));
			});
			$(e).children('a').first().trigger('click');
		});

	}



  $('.promote.first .promoteSlider .productList .products').bxSlider({
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
  });



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
				var sidebar=$(this);
				if(direction=='right'){
					sidebar.addClass('opened');
					//contentScrollToggle(false);
					history.pushState({id: 'MENUOPENED'}, null, null);
				} else if(direction=='left'){
					sidebar.removeClass('opened');
					//contentScrollToggle(true);
					history.back();
				}
			},
			threshold:0
		});

		$('.hamburger_menu').click(function(){
			var sidebar=$('header.sidebar');
			sidebar.toggleClass('opened');
			contentFooterDivs.toggleClass('slided');
			if(sidebar.hasClass('opened')){
				//contentScrollToggle(false);
				history.pushState({id: 'MENUOPENED'}, null, null);
			} else {
				//contentScrollToggle(true);
				history.back();
			}
		});

		window.onpopstate=function(){
			$('header.sidebar').removeClass('opened');
			//contentScrollToggle(true);
			contentFooterDivs.removeClass('slided');
		};
	}

});