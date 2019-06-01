$(function(){
	
	var sliderContainer = $('.sliderContainer');
	if(sliderContainer.length){

		var slider = sliderContainer.find('.slider'), slides = slider.children('div');
		slides.first().addClass('active');

		slider.nextStep = function(){
			var activeItem = slides.filter('.active'), activeIndex = slides.index(activeItem);

			var next = (activeIndex + 1 == slides.length) ? 0 : activeIndex + 1;
			next = slides.eq(next);
			activeItem.removeClass('active');
			next.addClass('active');
		};
		setInterval(function(){
			slider.nextStep();
		}, 5000);
		slider.nextStep();

		var sliderSearch=sliderContainer.find('.search'), logo=$('header.sidebar .logo');
		var th=logo.outerHeight();
		var sliderInitHeight = false;

		$(window.scrollEventItem).on(window.scrollEvent, function(){
			if(sliderInitHeight === false) sliderInitHeight = slider.height();
			var scrolled=$(window.scrollEventItem).scrollTop();
			sliderContainer.height(sliderInitHeight-scrolled);

			(sliderSearch.offset().top - scrolled <= th) ? sliderSearch.addClass('hidden') : sliderSearch.removeClass('hidden');

		});




		var listsSlider=[];
		$('div.lists').each(function(index, e){
			var list = $(e), listMenu = list.find('.listMenu');

			listsSlider[index]=list.find('.listsInner').slick({
				accessibility: false,
				adaptiveHeight: true,
				swipe: false,
				mobileFirst: true,
				arrows: false,
				touchMove: false,
				draggable: false
			});


			listMenu.find('a').click(function(){
				var a=$(this);
				listMenu.find('a').removeClass('active');
				a.addClass('active');
				listsSlider[index].slick('slickGoTo', a.data('list'));
				listMenu.children('span').animate({
					width: a.outerWidth(), left: ((a.offset().left - listMenu.offset().left) / listMenu.width())*100 + '%'
				}, 500);
			});
			listMenu.find('a').first().trigger('click');
		});
	}


	$('.purissimoBanner a').on('click', function(ev){
		var a = $(this);
		ga('send', 'event', {
			'eventCategory': 'banner',
			'eventAction': 'click',
			'eventLabel': 'purissimoHome',
			/*'hitCallback': function(){
			}*/
		});
	});
	
});