/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

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
