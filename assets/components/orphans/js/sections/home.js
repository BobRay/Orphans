Ext.onReady(function() {
    MODx.load({ xtype: 'orphans-page-home'});
});

Orphans.page.Home = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'orphans-panel-home'
            ,renderTo: 'orphans-panel-home-div'
        }]
    }); 
    Orphans.page.Home.superclass.constructor.call(this,config);
};
Ext.extend(Orphans.page.Home,MODx.Component);
Ext.reg('orphans-page-home',Orphans.page.Home);