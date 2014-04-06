CKEDITOR.editorConfig = function( config ) {

	config.toolbar_Mini= [
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','BulletedList','-','Link','Unlink' ] },
	];
	config.toolbar= 'Mini';


	// Let's have it basic on dialogs as well.
	config.contentsCss=['/js/ck/contents.css', '/css/ck.css'];
	config.removeDialogTabs = 'link:advanced';

	//config.bodyId='ckeditorbody';
};