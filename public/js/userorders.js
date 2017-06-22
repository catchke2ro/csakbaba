$(function(){

	var userOrderList = $('div.orders .productList');
	if(userOrderList.length){

		var filterFn = function (index, e){
			var product = $(e);
			var show = false;
			orderFilter.find('a.active').each(function(index, buttonE){
				var a=$(this);
				show |= product.hasClass('status'+a.data('status'));
			});
			product.toggleClass('hidden', !show);
		};

		$('li.order.nem-ertekelt .rating').each(function(index, e){
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
				success: function(responseText){
					window.location.reload();
				},
				error: function(xhr){
					rating.html(xhr.responseText);
				}
			});
			ev.preventDefault();
			return false;
		});


		var orderFilter = $('.orderFilter');
		orderFilter.find('a').each(function(index, e){
			var a = $(e);
			a.find('span').text(userOrderList.find('li.product.status'+a.data('status')).length);
		});
		orderFilter.find('a').click(function(){
			$(this).toggleClass('active');
			userFilterProducts(orderFilter, userOrderList, filterFn, 'userOrdersProductsFilter');

			var checkedStatuses = [];
			orderFilter.find('.statusButton').each(function(index, e2){
				if($(e2).hasClass('active')) checkedStatuses.push($(e2).data('status'));
			});

			if(checkedStatuses.length){
				$.cookie('userOrdersProductsFilter', checkedStatuses.join(','), {expires: 1, path: '/'})
			}
		});



		var checkedStatuses = 'nemertekelt';
		if($.cookie('userOrdersProductsFilter')) checkedStatuses = $.cookie('userOrdersProductsFilter');
		checkedStatuses = checkedStatuses.split(',');

		orderFilter.find('.statusButton').removeClass('active');
		checkedStatuses.forEach(function(status){
			orderFilter.find('a[data-status='+status+']').addClass('active');
		});
		userFilterProducts(orderFilter, userOrderList, filterFn);

	}

	
	
	
});