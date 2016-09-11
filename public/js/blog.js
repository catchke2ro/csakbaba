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
	}

});