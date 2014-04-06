/*Ext.define('Categories', {
	extend: 'Ext.data.TreeStore', autoLoad:true, autoSync:true, storeId: 'Categories',
	fields: [
		{name: 'id', type: 'string'}, {name: 'name'}, {name: 'slug'}, {name: 'o', type: 'int'}, {name: 'parent_id', type: 'string'}, {name: 'active', type:'boolean'}, {name: 'options', type:'auto'}, {name: 'images', type:'auto'}
	],
	defaultRootId: '0',
	proxy: {
		type: 'ajax',
		api: {create:'/ext/model/create/model/category',read:'/ext/model/read/model/category',update:'/ext/model/update/model/category',destroy:'/ext/model/destroy/model/category'},
		reader: {type: 'json', root: 'category', async: false, idProperty: 'id'}
	},
	sorters: {property: 'o', direction: 'asc'},
	refreshOrder: function(parent){
		for(var i in parent.childNodes){
			parent.childNodes[i].set('o', (parseInt(i)+1));
			if(parent.childNodes[i].childNodes) this.refreshOrder(parent.childNodes[i]);
		}
	}
});*/

Ext.define('Users', {
	extend: 'Ext.data.Store', autoLoad:true, autoSync:true, storeId: 'Users',
	fields: [{name: 'id'},{name: 'username'},{name: 'email'},{name: 'gender'},{name: 'phone'},{name: 'date_reg'},{name: 'date_last_login'},{name: 'active', type: 'boolean'},{name: 'address', type: 'auto'},{name: 'avatar', 'type': 'auto'},{name:'balance', type: 'int'},{name: 'favourites', type: 'auto'},{name: 'promotes', type: 'auto'}],
	proxy: {
		type: 'ajax',
		api: {create:'/ext/model/create/model/user',read:'/ext/model/read/model/user',update:'/ext/model/update/model/user',destroy:'/ext/model/destroy/model/user'},
		reader: {type: 'json', root: 'user', async: false, idProperty: 'id'}
	},
	sorters: {property: 'username', direction: 'asc'}
});

Ext.define('Products', {
	extend: 'Ext.data.Store', autoLoad:true, autoSync:true, storeId: 'Products',
	fields: [{name: 'id'},{name: 'name'},{name: 'category'},{name: 'user', type: 'auto'},{name: 'price'},{name: 'date_added'},{name: 'date_period'},{name: 'status', type: 'int'},{name: 'promotes', type: 'auto'},{name: 'autorenew'}],
	proxy: {
		type: 'ajax',
		api: {create:'/ext/model/create/model/product',read:'/ext/model/read/model/product',update:'/ext/model/update/model/product',destroy:'/ext/model/destroy/model/product'},
		reader: {type: 'json', root: 'product', async: false, idProperty: 'id'}
	},
	sorters: {property: 'date_added', direction: 'desc'}
});

Ext.define('Orders', {
	extend: 'Ext.data.Store', autoLoad:true, autoSync:true, storeId: 'Orders',
	fields: [{name: 'id'},{name: 'date'},{name: 'product', type: 'auto'},{name: 'user', type: 'auto'},{name: 'shop_user', type: 'auto'}],
	proxy: {
		type: 'ajax',
		api: {create:'/ext/model/create/model/order',read:'/ext/model/read/model/order',update:'/ext/model/update/model/order',destroy:'/ext/model/destroy/model/order'},
		reader: {type: 'json', root: 'order', async: false, idProperty: 'id'}
	},
	sorters: {property: 'date', direction: 'desc'}
});

Ext.define('Ratings', {
	extend: 'Ext.data.Store', autoLoad:true, autoSync:true, storeId: 'Ratings',
	fields: [{name: 'id'},{name: 'date'},{name: 'seller', type: 'boolean'},{name: 'success', type: 'boolean'},{name: 'positive', type: 'boolean'},{name: 'text'},{name: 'product', type: 'auto'}],
	proxy: {
		type: 'ajax',
		api: {create:'/ext/model/create/model/rating',read:'/ext/model/read/model/rating',update:'/ext/model/update/model/rating',destroy:'/ext/model/destroy/model/rating'},
		reader: {type: 'json', root: 'rating', async: false, idProperty: 'id'}
	},
	sorters: {property: 'date', direction: 'desc'}
});

Ext.define('BlogPosts', {
	extend: 'Ext.data.Store', autoLoad:true, autoSync:true, storeId: 'BlogPosts',
	fields: [{name: 'id'},{name: 'date'},{name: 'title'},{name: 'slug'},{name: 'body'},{name: 'teaser'}],
	proxy: {
		type: 'ajax',
		api: {create:'/ext/model/create/model/blogPost',read:'/ext/model/read/model/blogPost',update:'/ext/model/update/model/blogPost',destroy:'/ext/model/destroy/model/blogPost'},
		reader: {type: 'json', root: 'blogPost', async: false, idProperty: 'id'}
	},
	sorters: {property: 'date', direction: 'desc'}
});