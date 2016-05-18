$(function(){

	var userProductList = $('.productList.user');
	if(userProductList.length){

		$(document).on('click', '.userAddProduct', function(){
			var a = $(this);
			var nobalance = a.closest('.addNewProductWrapper').find('.nobalance');
			if(nobalance.length){
				nobalance.parent().removeClass('hidden');
				return false;
			}
			var url = '/shop/userproductedit';
			if(a.data('opencategory')){
				url += '?category_id='+a.data('opencategory');
				a.data('opencategory', '');
			}
			openIframePopup('szerkesztes', url);

		});

		$(document).on('click', '.userEditProduct', function(){
			var prodDiv=$(this).closest('li.product');
			openIframePopup('szerkesztes', '/shop/userproductedit?category_id='+prodDiv.data('categoryid')+'&product_id='+prodDiv.data('id'));
		});

		$(document).on('click', '.userCopyProduct', function(){
			var prodDiv=$(this).closest('li.product');
			openIframePopup('szerkesztes', '/shop/userproductedit?category_id='+prodDiv.data('categoryid')+'&product_id=COPY_'+prodDiv.data('id'));
		});

		$(document).on('click', '.userDeleteProduct', function(){
			if(!confirm('Biztosan törlöd a terméket?')){ return; }
			$.ajax({
				url: '/shop/userproductdelete?id='+$(this).closest('li.product').data('id'),
				complete: function() {
					window.location.reload();
				}
			});
		});
		$(document).on('click', '.userRenewProduct', function(){
			$.ajax({
				url: '/shop/userrenew?id='+$(this).closest('li.product').data('id'),
				complete: function() {
					window.location.reload();
				}
			});
		});

		$(document).on('click', '.userproductfunctions div.menu', function(){
			var menu = $(this).closest('li.product').find('.productMenu');
			menu.toggleClass('open');
		});
		$(document).on('click', '.productMenu.open a', function(){
			$(this).closest('.productInner').find('.userproductfunctions div.menu').trigger('click');
		});




		var productFilter = $('.productFilter');
		productFilter.find('a').each(function(index, e){
			var a = $(e);
			a.find('span').text(userProductList.find('li.product.status'+a.data('status')).length);
		});
		productFilter.find('a').click(function(){
			$(this).toggleClass('active');
			userFilterProducts(productFilter, userProductList);
		});

		var textFilterTimeout;
		productFilter.find('.textFilter input').on('keyup', function(){
			clearTimeout(textFilterTimeout);
			textFilterTimeout=setTimeout(function(){
				userFilterProducts(productFilter, userProductList);
			}, 300);

		});


		if(window.location.hash.indexOf('uj') !== -1){
			var hashParts = window.location.hash.replace('#', '').split('__');
			var addLink = $('.userAddProduct');
			if(typeof hashParts[1] != 'undefined') addLink.data('opencategory', hashParts[1]);
			addLink.click();
		}



	}



	//TERMÉK HOZZÁADÁS - Kategória választó

	var productEditAddForm = $('.productEditAddForm');
	if(productEditAddForm.length){

		var categorySelects = productEditAddForm.find('.categorySelects');
		var form = productEditAddForm.find('div.form');

		categorySelects.find('.borzeIcons a').on('click', function(){
			var a = $(this), key = a.data('id');
			a.siblings().removeClass('active');
			a.addClass('active');
			categorySelects.find('.categorySelect').removeClass('visible').filter('.'+key).addClass('visible');
		});

		$(categorySelects).on('change', 'select', function(){
			var select = $(this);
			if(select.val()){
				productEditAddForm.find('.infoText.selectCategory').hide();
				var productId = productEditAddForm.data('productid');
				productEditFormLoad(select.val(), productId, form);
			}
		});
		categorySelects.find('select').trigger('change');




		$(form).on('click', 'button[name=moreButton], button[name=promoteButton]', function(){
			var button = $(this), fsClass = button.attr('name').replace('Button', '');
			var fs = form.find('li.fieldset.'+fsClass), openedInput = fs.find('input[name='+fsClass+'opened]');
			$(this).closest('li.fieldset').toggleClass('active');
			fs.toggleClass('hidden');
			if(openedInput.length) openedInput.val(fs.hasClass('hidden') ? 0 : 1);
		});

		$(document).on('click', 'div.productEditAddForm li.cancelButton button', function(){
			window.parent.$.magnificPopup.close();
		});

		$(document).on('submit', 'div.productEditAddForm > .form > form', function(ev){
			var formElement=this;
			$('.formLoad').addClass('show');
			$(form).find('input[type=submit]').prop('disabled', true);
			ev.preventDefault();

			$.ajax({
				url: '/shop/userproducteditform', type: 'POST', data: $(formElement).serialize(), complete: function(request){
					$(formElement).find('input[type=submit]').prop('disabled', false);
					$('.formLoad').removeClass('show');

					if(request.status!=400){
						form.html('');
						window.parent.ga('send', 'event', { 'eventCategory': 'product',	'eventAction': 'add', 'hitCallback': function(){
							window.parent.location.reload();
						}});

					} else {
						afterProductEditFormLoad(form, request);
					}
				}
			});
			return false;
		});


		$(document).on('change', '.fieldset.promote input[type=checkbox]', function(){
			var fs = $(this).closest('.fieldset.promote'), formElement=$(this).closest('form');
			var balance=parseInt(productEditAddForm.data('balance'));
			var price=0, data=formElement.data('promoteprices');
			fs.find('input[name*=types]').each(function(index, e){
				var cb = $(e);
				if(cb.prop('checked') && !cb.prop('disabled')){ price += data[cb.val()];}
			});
			fs.data('price', price);
			var available = balance - price - parseInt(formElement.data('amount'));
			fs.find('input[name*=types]').each(function(index, e){
				var cb = $(e);
				cbSetDisabled(cb, (!cb.prop('checked') && data[cb.val()] > available));
			});
			afterSaveBalance(formElement);
		});

		$(document).on('click', '.fieldset.promote div.radios label', function(){
			var fs = $(this).closest('.fieldset.promote'), noBalanceMessage = fs.find('li.field.note');

			if($(this).find('input').prop('disabled')){
				noBalanceMessage.addClass('visible');
			} else {
				noBalanceMessage.removeClass('visible');
			}

			setTimeout(function(){
				noBalanceMessage.removeClass('visible');
			}, 2000);
		});

	}






});

function afterSaveBalance(form){
	var balances = $('.infoText.balances');

	var basePrice = parseInt(form.data('amount'));
	var promotePrice = parseInt(form.find('li.fieldset.promote').data('price'));
	var balance = parseInt(balances.find('span.huf.balance').text());

	var price = balance - (basePrice + promotePrice);

	balances.find('span.huf.afterSave').text(price);
}

function afterProductEditFormLoad(formDiv, request){
	formDiv.html(request.responseText);
	formDiv.addClass('opened');
	$('html, body').animate({ scrollTop: formDiv.offset().top-100	}, 500);
	if(!window.isMobile) formDiv.find('textarea').ckeditor();
	initRange(formDiv);
	initTooltip(formDiv);
	formDiv.find('.fieldset.promote input[type=checkbox]').trigger('change');
	afterSaveBalance(formDiv.find('form'));
}

function productEditFormLoad(category_id, product_id, formDiv){
	$.ajax({
		url: '/shop/userproducteditform',
		data: {category_id: category_id, product_id: product_id},
		complete: function(request){
			afterProductEditFormLoad(formDiv, request);
		}
	});
}


function userFilterProducts(productFilterDiv, userProductList){
	var me=jQuery(this), ul=userProductList.children('ul'), q = productFilterDiv.find('.textFilter input').val().trim();

	ul.find('li').addClass('hidden');

	var textFilterSearch = function(q, product){
		if(!q) return true;
		var patt=new RegExp('.*'+q+'.*', 'igm');
		return patt.test(product.find('h3.productName').text()) || patt.test(product.find('div.productPrice').text()) || patt.test(product.find('div.frontDesc').text());
	};

	ul.find('li').each(function(index, e){
		var product = $(e), status = product.data('status');

		var show = productFilterDiv.find('a.s'+status).hasClass('active') && textFilterSearch(q, product);
		product.toggleClass('hidden', !show);
	});


}
