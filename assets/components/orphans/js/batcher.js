var Orphans = function(config) {
    config = config || {};
    Orphans.superclass.constructor.call(this,config);
};
Ext.extend(Orphans,Ext.Component,{
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},config: {}
});
Ext.reg('orphans',Orphans);

var Orphans = new Orphans();