Ext.onReady(function() {
    MODx.load({ xtype: 'orphans-page-template-tvs'});
});

Orphans.page.TemplateTVs = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        formpanel: 'modx-panel-resource'
        ,actions: {
            'new': MODx.request.a
            ,edit: MODx.request.a
            ,cancel: MODx.request.a
        }
        ,components: [{
            xtype: 'orphans-panel-template-tvs'
            ,renderTo: 'orphans-panel-template-tvs-div'
            ,processor: 'changeTVs'
            ,intromsg: 'orphans.template.tvs.intro_msg'
        }]
        ,buttons: [{
            process: 'mgr/template/changetvs'
            ,text: _('save')
            ,method: 'remote'
            ,keys: [{
                key: 's'
                ,alt: true
                ,ctrl: true
            }]
        },'-',{
            process: 'cancel'
            ,text: _('cancel')
            ,params: {a:MODx.request.a}
        }]
    });
    Orphans.page.TemplateTVs.superclass.constructor.call(this,config);
};
Ext.extend(Orphans.page.TemplateTVs,MODx.Component);
Ext.reg('orphans-page-template-tvs',Orphans.page.TemplateTVs);