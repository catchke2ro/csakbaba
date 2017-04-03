$(document).ready(function(){

	var blogForm = $('.blogForm');
	if(blogForm.length){
		blogForm.find('input#title').keyup(function(){
			if(blogForm.find('input#id').val() == ''){
				blogForm.find('input#slug').val(generateSlug($(this).val()));
			}
		});
	}

	var blogContent = $('.blogContent');
	if(blogContent.length){
		blogContent.find('.postItem.index img').on('click', function(){
			window.location = $(this).closest('.postItem').find('h2 > a').attr('href');
		});


		$(document).on('click', '.relatedPosts li', function(){
			window.location = $(this).data('url');
		});

	}


	var lazyRandoms = $('.list.lazyRandom');
	lazyRandoms.each(function(index, e){
		var lazyRandom = $(e), ul = lazyRandom.find('ul.products');


		var partialOptions = lazyRandom.data('partialoptions') ? lazyRandom.data('partialoptions') : '{}';
		$.ajax({
			url: '/market/getrandomproducts?count=10&partialOptions='+JSON.stringify(partialOptions),
			type: 'GET',
			complete: function(request){
				ul.append(request.responseText);

				ul.slick({
					accessibility: false, adaptiveHeight: true, swipe: false, mobileFirst: true, arrows: false,	infinite: false,
					touchMove: false, draggable: false,	slidesToScroll: 1, responsive: [
						{ breakpoint: 1600,	settings: {	slidesToShow: 7 }},
						{ breakpoint: 1180,	settings: {	slidesToShow: 6 }},
						{ breakpoint: 960,	settings: {	slidesToShow: 5 }},
						{ breakpoint: 800,	settings: {	slidesToShow: 4 }},
						{ breakpoint: 400,	settings: {	slidesToShow: 3, unslick: true  }},
					]
				});
			}
		});


		$(window.scrollEventItem).on(window.scrollEvent, function(){
			var scrolled=$(this).scrollTop();
			if(scrolled >= (lazyRandom.offset().top - $(window).height()) && !ul.data('sliderStarted')){
				ul.data('sliderStarted', true);
				setInterval(function(){
					$.ajax({
						url: '/market/getrandomproducts?count=1&partialOptions='+JSON.stringify(partialOptions),
						type: 'GET',
						complete: function(request){
							ul.slick('slickAdd', request.responseText);
							setTimeout(function(){
								ul.slick('slickNext');
								ul.one('afterChange', function(event, slick, direction){
									ul.slick('slickRemove', null, true);
								});
							}, 1000);

						}
					});
				}, 6500);
			}
		});

	});



});