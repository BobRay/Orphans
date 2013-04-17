Orphans.panel.Home = function(config) {
    config = config || {};
    Ext.apply(config,{
        border: false
        ,baseCls: 'modx-formpanel'
        ,items: [{
            html: '<h2>'+_('orphans')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype: 'modx-tabs'
            ,bodyStyle: 'padding: 10px'
            ,defaults: { border: false ,autoHeight: true }
            ,border: true
            ,stateful: true
            ,stateId: 'orphans-home-tabpanel'
            ,stateEvents: ['tabchange']
            ,getState:function() {
                return {activeTab:this.items.indexOf(this.getActiveTab())};
            }
            ,items: [{
                title: _('orphans.resources')
                ,tabTip: 'Orphans Orphans Orphans (mushroom mushroom!)'
                ,defaults: { autoHeight: true }
                ,items: [{
                    html: '<p>'+_('orphans.intro_msg')+'</p>'
                    ,border: false
                    ,bodyStyle: 'padding: 10px'
                },{
                    xtype: 'orphans-grid-resource'
                    ,preventRender: true
                }]
            },{
                title: _('orphans.templates')
                ,defaults: { autoHeight: true }
                ,items: [{
                    html: '<p>'+_('orphans.templates.intro_msg')+'</p>'
                    ,border: false
                    ,bodyStyle: 'padding: 10px'
                },{
                    xtype: 'orphans-grid-template'
                    ,preventRender: true
                }]

            }
,{
                title: _('orphans.templates')
                ,defaults: { autoHeight: true }
                ,items: [{
                    html: '<p>'+_('orphans.templates.intro_msg')+'</p>'
                    ,border: false
                    ,bodyStyle: 'padding: 10px'
                },{
                    xtype: 'orphans-grid-template'
                    ,preventRender: true
                }]

            }
            ]
        }]
    });
    Orphans.panel.Home.superclass.constructor.call(this,config);
};
Ext.extend(Orphans.panel.Home,MODx.Panel);
Ext.reg('orphans-panel-home',Orphans.panel.Home);
