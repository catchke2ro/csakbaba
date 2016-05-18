
CKEDITOR.plugins.basePath='/js/ckfrontend/plugins/';
CKEDITOR.editorConfig = function( config ) {

	config.toolbar_Mini= [
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','-','BulletedList','-','Link','Unlink' ] },
	];
	config.toolbar= 'Mini';
	config.removePlugins= 'elementspath';
	config.resize_enabled = false;
	config.extraPlugins = 'divarea';


	// Let's have it basic on dialogs as well.
	config.contentsCss=['/js/ck/contents.css'];
	config.removeDialogTabs = 'link:advanced';

	//config.bodyId='ckeditorbody';
};