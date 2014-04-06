/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.plugins.add('microdata', {
	requires: 'dialog',
	lang: 'de,en,hu',
	init: function( editor ) {
		editor.addCommand('microdataDialog', new CKEDITOR.dialogCommand('microdataDialog'));

		if ( editor.ui.addButton ) {
			editor.ui.addButton('Microdata', {
				label: editor.lang.microdata.toolbar,
				command: 'microdataDialog',
				icon: this.path + 'images/microdata.png'
			});
		}

		CKEDITOR.dialog.add('microdataDialog', this.path + 'dialogs/microdata.js' );

		CKEDITOR.addCss(
			'div[itemtype] { border: 1px dashed #cccccc; margin: 2px; padding: 2px }'
		);
	}
});

CKEDITOR.on('instanceCreated', function(e) {
	e.editor.on('key', function(evt) {
		if (evt.data.keyCode === 13) {
			setTimeout(function () {
				var se = e.editor.getSelection().getStartElement();
				se.removeAttribute("itemtype");
				se.removeAttribute("itemscope");
				se.removeAttribute("itemprop");
			}, 10);
		}
	});
});










/*afterInit: function( editor ) {
	// Register a filter to displaying placeholders after mode change.

	var dataProcessor = editor.dataProcessor,
		dataFilter = dataProcessor && dataProcessor.dataFilter,
		htmlFilter = dataProcessor && dataProcessor.htmlFilter,
		pathFilters = editor._.elementsPath && editor._.elementsPath.filters;

	if ( dataFilter ) {
		dataFilter.addRules({
			elements: {
				a: function( element ) {
					var attributes = element.attributes;
					if ( !attributes.name )
						return null;

					var isEmpty = !element.children.length;

					if ( CKEDITOR.plugins.link.synAnchorSelector ) {
						// IE needs a specific class name to be applied
						// to the anchors, for appropriate styling.
						var ieClass = isEmpty ? 'cke_anchor_empty' : 'cke_anchor';
						var cls = attributes[ 'class' ];
						if ( attributes.name && ( !cls || cls.indexOf( ieClass ) < 0 ) )
							attributes[ 'class' ] = ( cls || '' ) + ' ' + ieClass;

						if ( isEmpty && CKEDITOR.plugins.link.emptyAnchorFix ) {
							attributes.contenteditable = 'false';
							attributes[ 'data-cke-editable' ] = 1;
						}
					} else if ( CKEDITOR.plugins.link.fakeAnchor && isEmpty )
						return editor.createFakeParserElement( element, 'cke_anchor', 'anchor' );

					return null;
				}
			}
		});
	}

	if ( CKEDITOR.plugins.link.emptyAnchorFix && htmlFilter ) {
		htmlFilter.addRules({
			elements: {
				a: function( element ) {
					delete element.attributes.contenteditable;
				}
			}
		});
	}

	if ( pathFilters ) {
		pathFilters.push( function( element, name ) {
			if ( name == 'a' ) {
				if ( CKEDITOR.plugins.link.tryRestoreFakeAnchor( editor, element ) || ( element.getAttribute( 'name' ) && ( !element.getAttribute( 'href' ) || !element.getChildCount() ) ) ) {
					return 'anchor';
				}
			}
		});
	}
}
});

*//**
 * Set of link plugin's helpers.
 *
 * @class
 * @singleton
 *//*
CKEDITOR.plugins.link = {
	*//**
	 * Get the surrounding link element of current selection.
	 *
	 *		CKEDITOR.plugins.link.getSelectedLink( editor );
	 *
	 *		// The following selection will all return the link element.
	 *
	 *		<a href="#">li^nk</a>
	 *		<a href="#">[link]</a>
	 *		text[<a href="#">link]</a>
	 *		<a href="#">li[nk</a>]
	 *		[<b><a href="#">li]nk</a></b>]
	 *		[<a href="#"><b>li]nk</b></a>
	 *
	 * @since 3.2.1
	 * @param {CKEDITOR.editor} editor
	 *//*
	getSelectedLink: function( editor ) {
		var selection = editor.getSelection();
		var selectedElement = selection.getSelectedElement();
		if ( selectedElement && selectedElement.is( 'a' ) )
			return selectedElement;

		var range = selection.getRanges( true )[ 0 ];

		if ( range ) {
			range.shrink( CKEDITOR.SHRINK_TEXT );
			return editor.elementPath( range.getCommonAncestor() ).contains( 'a', 1 );
		}
		return null;
	},

	*//**
	 * Opera and WebKit don't make it possible to select empty anchors. Fake
	 * elements must be used for them.
	 *
	 * @readonly
	 * @property {Boolean}
	 *//*
	fakeAnchor: CKEDITOR.env.opera || CKEDITOR.env.webkit,

	*//**
	 * For browsers that don't support CSS3 `a[name]:empty()`, note IE9 is included because of #7783.
	 *
	 * @readonly
	 * @property {Boolean}
	 *//*
	synAnchorSelector: CKEDITOR.env.ie,

	*//**
	 * For browsers that have editing issue with empty anchor.
	 *
	 * @readonly
	 * @property {Boolean}
	 *//*
	emptyAnchorFix: CKEDITOR.env.ie && CKEDITOR.env.version < 8,

	*//**
	 * @param {CKEDITOR.editor} editor
	 * @param {CKEDITOR.dom.element} element
	 * @todo
	 *//*
	tryRestoreFakeAnchor: function( editor, element ) {
		if ( element && element.data( 'cke-real-element-type' ) && element.data( 'cke-real-element-type' ) == 'anchor' ) {
			var link = editor.restoreRealElement( element );
			if ( link.data( 'cke-saved-name' ) )
				return link;
		}
	}
};

// TODO Much probably there's no need to expose these as public objects.

CKEDITOR.unlinkCommand = function() {};
CKEDITOR.unlinkCommand.prototype = {
	exec: function( editor ) {
		var style = new CKEDITOR.style( { element:'a',type:CKEDITOR.STYLE_INLINE,alwaysRemoveElement:1 } );
		editor.removeStyle( style );
	},

	refresh: function( editor, path ) {
		// Despite our initial hope, document.queryCommandEnabled() does not work
		// for this in Firefox. So we must detect the state by element paths.

		var element = path.lastElement && path.lastElement.getAscendant( 'a', true );

		if ( element && element.getName() == 'a' && element.getAttribute( 'href' ) && element.getChildCount() )
			this.setState( CKEDITOR.TRISTATE_OFF );
		else
			this.setState( CKEDITOR.TRISTATE_DISABLED );
	},

	contextSensitive: 1,
	startDisabled: 1
};

CKEDITOR.removeAnchorCommand = function() {};
CKEDITOR.removeAnchorCommand.prototype = {
	exec: function( editor ) {
		var sel = editor.getSelection(),
			bms = sel.createBookmarks(),
			anchor;
		if ( sel && ( anchor = sel.getSelectedElement() ) && ( CKEDITOR.plugins.link.fakeAnchor && !anchor.getChildCount() ? CKEDITOR.plugins.link.tryRestoreFakeAnchor( editor, anchor ) : anchor.is( 'a' ) ) )
			anchor.remove( 1 );
		else {
			if ( ( anchor = CKEDITOR.plugins.link.getSelectedLink( editor ) ) ) {
				if ( anchor.hasAttribute( 'href' ) ) {
					anchor.removeAttributes( { name:1,'data-cke-saved-name':1 } );
					anchor.removeClass( 'cke_anchor' );
				} else
					anchor.remove( 1 );
			}
		}
		sel.selectBookmarks( bms );
	}
};

CKEDITOR.tools.extend( CKEDITOR.config, {
	*//**
	 * @cfg {Boolean} [linkShowAdvancedTab=true]
	 * @member CKEDITOR.config
	 * @todo
	 *//*
	linkShowAdvancedTab: true,

	*//**
	 * @cfg {Boolean} [linkShowTargetTab=true]
	 * @member CKEDITOR.config
	 * @todo
	 *//*
	linkShowTargetTab: true
});*/
