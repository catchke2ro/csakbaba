Ext.Loader.setPath('Ext.ux', '/js/ext/ux');
Ext.require('Ext.ux.upload.Dialog');

function generateSlug(text){var slug=text.toLowerCase().replace(new RegExp(" ", 'g'),'-').replace(new RegExp("[áÁ]", 'g'),'a').replace(new RegExp("[öóőÖÓŐ]", 'g'),'o').replace(new RegExp("[üúűÜÚŰ]", 'g'),'u').replace(new RegExp("[í]", 'g'),'i').replace(new RegExp("[éÉ]", 'g'),'e').replace(new RegExp("[^a-z0-9\-]", 'g'),'');return slug;}
Ext.define('CB.form.field.TextWSlug', {extend: 'Ext.form.field.Text',enableKeyEvents: true,listeners: {keyup: function(field){var slugField=field.next();slugField.setValue(generateSlug(field.getValue()));}},alias: 'widget.textwslug'});


Ext.define('Attachments', {
	extend: 'Ext.data.Store', storeId: 'Attachments', autoLoad: false, autoSync:false, fields: [{	name : 'name',	type : 'string' }, {	name : 'type', type : 'string' },{ name : 'mid', type : 'string' },{ name : 'small', type : 'string' },{ name : 'url', type : 'string' },{ name : 'date', type : 'string' }]
});
var attStore=Ext.create('Attachments');



Ext.define('CB.view.AttachmentGrid', {
	extend: 'Ext.grid.Panel', title: 'Fájlfeltöltés', store: attStore , cls: 'attachmentsGrid', border: false, manageHeight: false, mainPanel: '', descriptions: false, imageClass: false,
	tools: [
		{type: 'minus', tooltip: 'Törlés', disabled: true, itemId: 'removeAttachmentButton', handler: function(){
			var sm=this.up('gridpanel').getSelectionModel(); this.up('gridpanel').store.remove(sm.getSelection()); sm.deselectAll();
		}}
	],
	columns: [
		{	dataIndex : 'url', tdCls: 'gridImage', header : 'Kép', flex : 1, width: 80, sortable: false, renderer: function(value, metaData, record){
			if(record.get('type').indexOf('image')==-1){ value='/img/elements/fileicons/'+value.split('.').pop()+'.png';  }
			return '<img src="'+value+'"/>';
		}},
		{	dataIndex : 'name', tdCls: 'fontBold', header : 'Filenév', flex : 2},	{ dataIndex : 'date', header : 'Dátum', flex: 1 }, {	dataIndex : 'type',	header : 'Típus', flex: 1 }
	],
	listeners: {
		'selectionchange': function(view, records) { this.down('#removeAttachmentButton').setDisabled(!records.length); },
		'itemdblclick': function(view, record){ view.up('panel').mainPanel.selectedOperation(view, record); },
		beforerender: function(){ if(this.up('panel').doubleClick){ this.title=this.title+' '+'Kiválasztás dupla kattintással'; }
		}
	},
	//plugins: [ {ptype: 'cellediting', clicksToEdit:1} ],
	initComponent: function(){
		var grid=this;
		//if(this.columns.length<5){ if(this.descriptions){ grid.columns.push({header: loc('upload.Description'), dataIndex: 'desc', flex: 2, editor: {xtype: 'textfield'}}); grid.columns[1].flex=1;	} if(this.imageClass){ grid.columns.push({header: loc('upload.Class'), dataIndex: 'class', flex: 2, editor: {xtype: 'textfield'}}); grid.columns[1].flex=1;}}
		this.callParent(arguments);
	}
});

Ext.define('CB.upload', {
	extend: 'Ext.panel.Panel', title: false, height: 498, width: 598, autoScroll: true, fileType: 'all', store: attStore, descriptions: false, imageClass: false, url: '/ext/upload', doubleClick: true, gridViewConfig: {}, targetdir: '',
	selectedOperation: function(view, record){},
	uploadComplete: function(dialog, manager, items){
		var store=Ext.getStore(this.store), stopSync=false;
		if(store.autoSync){ store.autoSync=false; stopSync=true; }
		Ext.Array.each(items, function(item, index){
			store.add(Ext.Object.merge({date: Ext.Date.format(new Date(), 'Y-m-d H:i:s')}, item.info));
		});
		if(stopSync){ store.autoSync=true; store.sync(); }
		dialog.down('grid').getStore().removeAll();
	},
	initComponent: function(){
		var srgupload=this;
		this.callParent(arguments);

		this.add(Ext.create('Ext.ux.upload.Dialog', {
			layout: 'fit', width: '100%', height: '100%', border: false, uploadUrl: '/admin/index/upload/?td='+srgupload.targetdir, buttonText: 'Feltöltés', textFilename: 'Fájl', textSize: 'Méret', textType: 'Típus', textStatus: 'Státusz',
			listeners: { uploadcomplete: function(dialog, manager, items, errorCount){ srgupload.uploadComplete(dialog, manager, items); }}
		}));

		//var store=Ext.create('CB.store.'+srgupload.store);
		var store=srgupload.store;
		store.clearFilter();
		store.filter({filterFn: function(item){ return (srgupload.fileType=='all') ? true : (item.get('type').indexOf(srgupload.fileType)!=-1); }});
		this.add(Ext.create('CB.view.AttachmentGrid', {
			store: srgupload.store,	mainPanel: srgupload,	descriptions: srgupload.descriptions,	imageClass: srgupload.imageClass,	viewConfig: srgupload.gridViewConfig
		}));
	}
});


