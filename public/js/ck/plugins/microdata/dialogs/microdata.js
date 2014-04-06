CKEDITOR.dialog.add('microdataDialog', function( editor ) {

	return {
		title: editor.lang.microdata.title,
		minWidth:300,
		minHeight:60,
		nodeToChange: '',
		attrToChange: '',
		contents: [
			{id: 'type', label: editor.lang.microdata.microdataType, elements: [
				{type: 'text', id: 'typeInput', label: editor.lang.microdata.microdataType}
			]},
			{id: 'property', label: editor.lang.microdata.microdataProperty, elements: [
				{type: 'text', id: 'propertyInput', label: editor.lang.microdata.microdataProperty}
			]}
		],
		onOk: function(){
			var dialog=this;
			var def=dialog.definition;
			var selected;
			var selection=editor.getSelection();



			/*if(selection.getSelectedText().length>0){
				selected=selection.getStartElement();
				if(selected.hasAttribute('itemtype')){
					selected.setAttribute('itemtype', dialog.getValueOf('type', 'typeInput'));
				} else if(selected.hasAttribute('itemprop')){
					selected.setAttribute('itemprop', dialog.getValueOf('property', 'propertyInput'));
				} else {




					var parents=selected.getParents(true);
					var hasType=false;
					for(var i in parents){
						if(parents[i].hasAttribute('itemtype')) hasType=true;
					}
					if(hasType){
						selected.setAttribute('itemprop', dialog.getValueOf('property', 'propertyInput'));
					} else {
						selected.setAttribute('itemtype', dialog.getValueOf('type', 'typeInput'));
					}
				}
			} else {
				var typeDiv=editor.document.createElement('div');
				var p=editor.document.createElement('p');
				p.setHtml(dialog.getValueOf('type', 'typeInput'));
				typeDiv.setAttribute('itemscope', '');
				typeDiv.setAttribute('itemtype', 'http://schema.org/'+dialog.getValueOf('type', 'typeInput'));
				typeDiv.append(p);
				editor.insertElement(typeDiv);
			}*/

			if(def.nodeToChange===false){
				if(def.attrToChange=='itemtype'){
					var typeDiv=editor.document.createElement('div');
					var p=editor.document.createElement('p');
					p.setHtml(dialog.getValueOf('type', 'typeInput'));
					typeDiv.setAttribute('itemscope', '');
					typeDiv.setAttribute('itemtype', 'http://schema.org/'+dialog.getValueOf('type', 'typeInput'));
					typeDiv.append(p);
					editor.insertElement(typeDiv);
					def.nodeToChange=typeDiv;
				} else if(def.attrToChange=='itemprop'){
					var propSpan=editor.document.createElement('span');
					propSpan.setText(selection.getSelectedText());
					editor.insertElement(propSpan);
					def.nodeToChange=propSpan;
				}
			}

			if(def.nodeToChange!==false){
				if(def.attrToChange=='itemtype') { def.nodeToChange.setAttribute('itemtype', 'http://schema.org/'+dialog.getValueOf('type', 'typeInput')); }
				else if(def.attrToChange=='itemprop') { def.nodeToChange.setAttribute('itemprop', dialog.getValueOf('property', 'propertyInput')); }
			}

		},
		onShow: function(){
			var dialog=this;
			var def=dialog.definition;
			var selected;
			var selection=editor.getSelection();

			dialog.hidePage('property');

			selected=selection.getStartElement();
			var typeNode=def.findType(selection);
			var length=selection.getSelectedText().replace(" ", "").length;

			if(typeNode===true){
				def.nodeToChange=selected;
				def.attrToChange='itemtype';
			} else if(typeNode===false) {
				return;
			} else if(typeNode && length>0) {
				if(selected.getText()==selection.getSelectedText()){
					def.attrToChange='itemtype';
					def.nodeToChange=typeNode;
				} else {
					def.attrToChange='itemprop';
					def.nodeToChange=false;
				}
			} else if(length<1) {
				def.nodeToChange=false;
				def.attrToChange='itemtype';
			}

			if(def.nodeToChange!==false){
				if(def.attrToChange=='itemtype') {
					dialog.setValueOf('type', 'typeInput', def.nodeToChange.getAttribute('itemtype').replace("http://schema.org/", ""));
				}
				else if(def.attrToChange=='itemprop') {
					dialog.setValueOf('property', 'propertyInput', selected.getAttribute('itemprop'));
				}
			}
			if(def.attrToChange=='itemprop'){
				dialog.showPage('property');
				dialog.selectPage('property');
				dialog.hidePage('type');
			}
		},
		findType: function(selection){
			var selected=selection.getStartElement(), parents=selected.getParents(true), typeNode=null;
			if(selected.hasAttribute('itemtype')) return true;
			for(var i in parents){
				if(parents[i].hasAttribute('itemtype')) { typeNode=parents[i]; break; }
			}
			return typeNode;
		}
	};



	/*return {
		title: editor.lang.link.anchor.title,
		minWidth: 300,
		minHeight: 60,
		onOk: function() {
			var name = CKEDITOR.tools.trim( this.getValueOf( 'info', 'txtName' ) );
			var attributes = {
				id: name,
				name: name,
				'data-cke-saved-name': name
			};

			if ( this._.selectedElement ) {
				if ( this._.selectedElement.data( 'cke-realelement' ) ) {
					var newFake = createFakeAnchor( editor, editor.document.createElement( 'a', { attributes: attributes } ) );
					newFake.replace( this._.selectedElement );
				} else
					this._.selectedElement.setAttributes( attributes );
			} else {
				var sel = editor.getSelection(),
					range = sel && sel.getRanges()[ 0 ];

				// Empty anchor
				if ( range.collapsed ) {
					if ( CKEDITOR.plugins.link.synAnchorSelector )
						attributes[ 'class' ] = 'cke_anchor_empty';

					if ( CKEDITOR.plugins.link.emptyAnchorFix ) {
						attributes[ 'contenteditable' ] = 'false';
						attributes[ 'data-cke-editable' ] = 1;
					}

					var anchor = editor.document.createElement( 'a', { attributes: attributes } );

					// Transform the anchor into a fake element for browsers that need it.
					if ( CKEDITOR.plugins.link.fakeAnchor )
						anchor = createFakeAnchor( editor, anchor );

					range.insertNode( anchor );
				} else {
					if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
						attributes[ 'class' ] = 'cke_anchor';

					// Apply style.
					var style = new CKEDITOR.style({ element: 'a', attributes: attributes } );
					style.type = CKEDITOR.STYLE_INLINE;
					editor.applyStyle( style );
				}
			}
		},

		onHide: function() {
			delete this._.selectedElement;
		},

		onShow: function() {
			var selection = editor.getSelection(),
				fullySelected = selection.getSelectedElement(),
				partialSelected;

			// Detect the anchor under selection.
			if ( fullySelected ) {
				if ( CKEDITOR.plugins.link.fakeAnchor ) {
					var realElement = CKEDITOR.plugins.link.tryRestoreFakeAnchor( editor, fullySelected );
					realElement && loadElements.call( this, realElement );
					this._.selectedElement = fullySelected;
				} else if ( fullySelected.is( 'a' ) && fullySelected.hasAttribute( 'name' ) )
					loadElements.call( this, fullySelected );
			} else {
				partialSelected = CKEDITOR.plugins.link.getSelectedLink( editor );
				if ( partialSelected ) {
					loadElements.call( this, partialSelected );
					selection.selectElement( partialSelected );
				}
			}

			this.getContentElement( 'info', 'txtName' ).focus();
		},
		contents: [
			{
			id: 'info',
			label: editor.lang.link.anchor.title,
			accessKey: 'I',
			elements: [
				{
				type: 'text',
				id: 'txtName',
				label: editor.lang.link.anchor.name,
				required: true,
				validate: function() {
					if ( !this.getValue() ) {
						alert( editor.lang.link.anchor.errorName );
						return false;
					}
					return true;
				}
			}
			]
		}
		]
	};*/


});
