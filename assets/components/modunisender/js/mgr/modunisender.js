var modunisender = function (config) {
	config = config || {};
	modunisender.superclass.constructor.call(this, config);
};
Ext.extend(modunisender, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('modunisender', modunisender);

modunisender = new modunisender();