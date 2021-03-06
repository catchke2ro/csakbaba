$(function(){

	var productList = $('div.productList');
	if(productList.length){

		$('div.productImages').click(function (){
			if($(this).css('cursor')=='pointer') window.location.href=$(this).closest('.front').find('.productName a').attr('href');
		});

		productCountChange();
		var moreProducts=$('div.moreProducts');
		if(moreProducts.length){
			moreProducts.click(function (){
				if($('.productAfterListLoad').css('display')!='none') return false;
				infiniteScroll();
			});
		}

	}






	var productDetails = $('div.productDetails');
	if(productDetails.length){

		productDetails.find('.productImagesWrapper .productImages').slick({
		  adaptiveHeight: true, dots: true, infinite: false
		});


		$('div.addToCart a').click(function(ev){
			var a=$(this), div=a.parent();

			if(div.hasClass('haveToLogin')){
				ev.preventDefault();
				$('div.header .loginLink .login').click();
				$('div.header .loginreg .infoText').text(div.data('info')).addClass('visible');
				return false;
			} else {
				div.siblings('.cassa').addClass('opened');
			}
		});


		$('div.cassaSubmit').find('input[type=submit]').click(function(ev){
			var submit=$(this);
			if(submit.hasClass('clicked')){
				ev.preventDefault();
				return false;
			}
			//if(submit.closest('form').find('input[type=checkbox]').prop('checked')==true){
			submit.addClass('clicked');
			submit.closest('form').submit();
			submit.attr('disabled', 'disabled');
			//}
			ev.preventDefault();
			return false;
		});



		$('.magnificParent').magnificPopup({
			delegate: '.magnificImage',
			type: 'image',
			image: {
				titleSrc: 'alt'
			},
			gallery:{
				enabled:true
			}
		});

		var productImagesWrapper = $('.productImagesWrapper'), headerMobile = $('.productHeaderMobile'), productDetailsWrapper = $('.productDetailsWrapper');
		$(window).on('resize', function(){
			if(headerMobile.css('display') != 'none' && headerMobile.find('h1').length == 0){
				productDetailsWrapper.find('h1').appendTo(headerMobile);
				productDetailsWrapper.find('.productPrice, .productUser').appendTo(headerMobile);
			}
			if(headerMobile.css('display') == 'none' && productDetailsWrapper.find('h1').length == 0){
				headerMobile.find('h1').prependTo(productDetailsWrapper);
				productDetailsWrapper.find('.productOptions').after(headerMobile.find('.productPrice, .productUser'));
			}
		});
		$(window).trigger('resize');

	}


	
});


function productCountChange(){
	var ul=$('ul.products'), loaded=ul.children('li').length, all=parseInt(ul.data('productscount')), moreProducts=$('div.moreProducts');
	ul.siblings('p.count').find('span').text(all);
	moreProducts.find('span.productsUntil').text('még '+(all-loaded));
	if(all-loaded<=0) moreProducts.hide();
	else moreProducts.show();
}

function infiniteScroll(){
	var ul=$('div.productList').find('ul.products'), page=ul.data('page');
	$('.productAfterListLoad').addClass('show');
	$.ajax({
		url: '/market/productsajax?page='+(parseInt(page)+1)+'&category_id='+ul.data('categoryid'),
		type: 'POST',
		data: $('form.filter').serialize(),
		complete: function(request){
			ul.append(request.responseText);
			ul.data('page', (parseInt(page)+1));
			ul.data('productscount', request.getResponseHeader('X-CSB-PRC'));
			productCountChange();
			$('.productAfterListLoad').removeClass('show');
		}
	});
}