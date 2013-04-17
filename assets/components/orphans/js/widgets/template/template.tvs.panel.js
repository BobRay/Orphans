Orphans.panel.TemplateTVs = function(config) {
    config = config || {};
    Ext.apply(config,{
        id: 'modx-panel-resource'
        ,url: Orphans.config.connector_url
        ,baseParams: {
            action: 'mgr/template/'+config.processor
        }
        ,fileUpload: true
        ,border: false
        ,baseCls: 'modx-formpanel'
        ,items: [{
            html: '<h2>'+_('template')+': '+Orphans.template.templatename+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
            ,id: 'orphans-panel-header'
        },{
            xtype: 'modx-tabs'
            ,bodyStyle: 'padding: 10px'
            ,defaults: { border: false ,autoHeight: true }
            ,border: true
            ,stateful: true
            ,stateId: 'orphans-template-tvs-tabpanel'
            ,stateEvents: ['tabchange']
            ,getState:function() {
                return {activeTab:this.items.indexOf(this.getActiveTab())};
            }
            ,items: [{
                title: _('orphans.tvs')
                ,defaults: { autoHeight: true }
                ,items: [{
                    html: '<p>'+_(config.intromsg)+'</p>'
                    ,border: false
                    ,bodyStyle: 'padding: 10px'
                    ,height: 10
                    ,maxHeight: 10
                    ,autoScroll: true
                },{
                    xtype: 'hidden'
                    ,name: 'template'
                    ,value: Orphans.template.id
                },{
                    html: ''
                    ,xtype: 'panel'
                    ,border: false
                    ,width: '97%'
                    ,anchor: '100%'
                    ,bodyStyle: 'padding: 15px;'
                    ,autoHeight: true
                    ,autoLoad: {
                        url: Orphans.config.connectorUrl
                        ,method: 'GET'
                        ,params: {
                           action: 'mgr/loadtvs'
                           ,class_key: 'modResource'
                           ,template: Orphans.template.id
                           ,resource: 0
                           ,showCheckbox: 1
                        }
                        ,scripts: true
                        ,callback: function() {
                            MODx.fireEvent('ready');
                            
                        }
                        ,scope: this
                    }
                },{
                    html: (Orphans.resources ? '<hr />'+Orphans.resources : '')
                    ,border: false
                    ,bodyStyle: 'padding: 15px'
                }]
            }]
        }]
    });
    Orphans.panel.TemplateTVs.superclass.constructor.call(this,config);
};
Ext.extend(Orphans.panel.TemplateTVs,MODx.FormPanel);
Ext.reg('orphans-panel-template-tvs',Orphans.panel.TemplateTVs);


MODx.triggerRTEOnChange = function() {
};
MODx.fireResourceFormChange = function(f,nv,ov) {
    Ext.getCmp('modx-panel-resource').fireEvent('fieldChange');
};