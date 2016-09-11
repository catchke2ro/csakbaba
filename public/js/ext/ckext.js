Ext.define('Ext.CKeditor',{
	extend: 'Ext.form.field.TextArea',
	alias: 'widget.ckeditor',
	CKConfig: '',
	ed:'',
	initComponent: function(){
		this.callParent(arguments);
		this.on('afterrender',function(){
			this.CKConfig={
				customConfig: '',
				language: 'hu',
				toolbar:	[
					{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat','-','Styles' ] },
					{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
					'/',
					{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
					{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Iframe' ] },
					{ name: 'document', items : [ 'Source','-','Templates' ] },
					{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteFromWord','-','Undo','Redo' ] },
					{ name: 'tools', items : [ 'ShowBlocks'] }
				],
				baseFloatZIndex: 20000,
				stylesSet: [
					{ name : 'H1', element : 'h1'},
					{ name : 'H2', element : 'h2' },
					{ name : 'H3', element : 'h3' }
				],
				filebrowserBrowseUrl: '',
				filebrowserImageBrowseUrl: '',
				filebrowserFlashBrowseUrl: ''
			};
			this.editor=CKEDITOR.replace(this.inputEl.id, this.CKConfig);
			this.editorId=this.editor.id;
			this.setValue(this.rawValue);
		}, this);
	},
	onRender: function(ct, position){
		if(!this.el){
			this.defaultAutoCreate ={tag: 'textarea', autocomplete: 'off' };
		}
		this.callParent(arguments)
	},
	refreshSize: function(){
		this.ed.editor.resize(this.getWidth(), this.getHeight());
	},
	setValue: function(value){
		this.callParent(arguments);
		if(this.editor){
			this.editor.setData(value);
		}
	},
	getRawValue: function(){
		if(this.editor){
			return this.editor.getData()
		} else {
			return Ext.isDefined(this.value) ? this.value : '';
		}
	}
});

CKEDITOR.on('instanceReady',function(e){
	var o = Ext.ComponentQuery.query('ckeditor[editorId="'+ e.editor.id +'"]'),
			comp=o[0];
	var height=0;
	comp.ed=e;
	if(comp.height==0){
		comp.up('panel').items.each(function(item, index){
			height+=item.getHeight();
		});
		e.editor.resize(comp.getWidth(), (comp.up('panel').getHeight()-height+comp.getHeight()-Ext.get(e.editor.id+'_top').getHeight()-Ext.get(e.editor.id+'_bottom').getHeight()));
	} else {
		e.editor.resize(comp.getWidth(), comp.getHeight());
	}

	comp.on('resize',function(c,adjWidth,adjHeight){
		c.editor.resize(adjWidth, adjHeight)
	})
});


