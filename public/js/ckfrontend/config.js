CKEDITOR.editorConfig = function( config ) {

	var el = $(this.element.$);

	var tb = 'Mini';
	if(el.data('tb')) tb = el.data('tb');

	var ss = 'default';
	if(el.data('ss')) ss = el.data('ss');

	if(el.data('height')){
		config.height = parseInt(el.data('height'));
	}

	config.toolbar_Mini= [
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','BulletedList','-','Link','Unlink' ] },
	];
	config.toolbar_Full= [
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },

		{ name: 'links', items: [ 'Link', 'Unlink' ] },
		{ name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule' ] },
		'/',
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'styles', items: [ 'Styles', 'Format' ] },
		{ name: 'document', items: [ 'Source', 'ShowBlocks' ] },
	];
	config.toolbar= tb;
	config.removePlugins= 'elementspath';
	config.resize_enabled = false;
	config.extraPlugins = 'divarea';

	config.filebrowserBrowseUrl='/js/ckfrontend/plugins/uploadsimple/upload.php';
	config.filebrowserWindowWidth='300';
	config.filebrowserWindowHeight='200';

	config.stylesSet= ss;


	// Let's have it basic on dialogs as well.
	config.contentsCss=['/js/ck/contents.css'];
	config.removeDialogTabs = 'link:advanced';

	//config.bodyId='ckeditorbody';
};