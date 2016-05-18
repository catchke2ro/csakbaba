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
		  adaptiveHeight: false, dots: true,
		});


		$('div.addToCart a').click(function(ev){
			var a=$(this), div=a.parent();
			if(div.hasClass('haveToLogin')){
				$('div.usermenu').addClass('opened');
				ev.preventDefault();
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

		$('div.commentForm #commentsubscribe').change(function(index, e){
			var productId=$(this).closest('.productDetails').data('id'), checked=$(this).prop('checked');
			$.ajax({
				url: '/market/commentsubscribe?pid='+productId+'&checked='+(checked ? 1 : 0), complete: function(){

				}
			})
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