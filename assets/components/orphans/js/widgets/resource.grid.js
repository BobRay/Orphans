
Orphans.grid.Resources = function(config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel();

    Ext.applyIf(config,{
        url: Orphans.config.connector_url
        ,baseParams: {
            action: 'mgr/resource/getList'
            ,thread: config.thread
        }
        ,fields: ['id','pagetitle','template','templatename','alias','deleted','published','createdon','editedon','hidemenu']
        ,paging: true
        ,autosave: false
        ,remoteSort: true
        ,autoExpandColumn: 'pagetitle'
        ,cls: 'orphans-grid'
        ,sm: this.sm
        ,columns: [this.sm,{
            header: _('id')
            ,dataIndex: 'id'
            ,sortable: true
            ,width: 60
        },{
            header: _('pagetitle')
            ,dataIndex: 'pagetitle'
            ,sortable: true
            ,width: 100
        },{
            header: _('alias')
            ,dataIndex: 'alias'
            ,sortable: true
            ,width: 100
        },{
            header: _('orphans.template')
            ,dataIndex: 'templatename'
            ,sortable: true
            ,width: 120
        },{
            header: _('orphans.published')
            ,dataIndex: 'published'
            ,sortable: true
            ,editor: { xtype: 'combo-boolean' ,renderer: 'boolean' }
            ,width: 80
        },{
            header: _('orphans.hidemenu')
            ,dataIndex: 'hidemenu'
            ,sortable: true
            ,editor: { xtype: 'combo-boolean' ,renderer: 'boolean' }
            ,width: 80
        }]
        ,viewConfig: {
            forceFit:true,
            enableRowBody:true,
            showPreview:true,
            getRowClass : function(rec, ri, p){
                var cls = 'orphans-row';
                if (!rec.data.published) cls += ' orphans-unpublished';
                if (rec.data.deleted) cls += ' orphans-deleted';
                if (rec.data.hidemenu) cls += ' orphans-hidemenu';

                if(this.showPreview){
                    //p.body = '<div class="orphans-resource-body">'+rec.data.content+'</div>';
                    return cls+' orphans-resource-expanded';
                }
                return cls+' orphans-resource-collapsed';
            }
        }
        ,tbar: [{
            text: _('orphans.bulk_actions')
            ,menu: this.getBatchMenu()
        },{
            xtype: 'button'
          /*   ,id: 'orphans-filter-clear' */
            ,text: _('orphans.reload')
            ,listeners: {
                'click': {fn: this.clearFilter, scope: this}
            }
        }]
    });
    Orphans.grid.Resources.superclass.constructor.call(this,config)
};
Ext.extend(Orphans.grid.Resources,MODx.grid.Grid,{
    search: function(tf,nv,ov) {
        this.getStore().setBaseParam('search',tf.getValue());
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,filterTemplate: function(cb,nv,ov) {
        this.getStore().setBaseParam('template',cb.getValue());
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,clearFilter: function() {
    	this.getStore().baseParams = {
            action: 'mgr/resource/getList'
    	};
        /* Ext.getCmp('orphans-search').reset(); */
       /*  Ext.getCmp('orphans-template').reset(); */
    	this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,_renderUrl: function(v,md,rec) {
        return '<a href="'+rec.data.url+'" target="_blank">'+rec.data.pagetitle+'</a>';
    }
    ,_showMenu: function(g,ri,e) {
        e.stopEvent();
        e.preventDefault();
        this.menu.record = this.getStore().getAt(ri).data;
        if (!this.getSelectionModel().isSelected(ri)) {
            this.getSelectionModel().selectRow(ri);
        }
        this.menu.removeAll();

        var m = [];
        if (this.menu.record.menu) {
            m = this.menu.record.menu;
            if (m.length > 0) {
                this.addContextMenuItem(m);
                this.menu.show(e.target);
            }
        } else {
            var z = this.getBatchMenu();

            for (var zz=0;zz < z.length;zz++) {
                this.menu.add(z[zz]);
            }
            this.menu.show(e.target);
        }
    }
    ,getSelectedAsList: function() {
        var sels = this.getSelectionModel().getSelections();
        if (sels.length <= 0) return false;

        var cs = '';
        for (var i=0;i<sels.length;i++) {
            cs += ','+sels[i].data.id;
        }
        cs = cs.substr(1);
        return cs;
    }
    
    ,batchAction: function(act,btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/resource/batch'
                ,resources: cs
                ,batch: act
            }
            ,listeners: {
                'success': {fn:function(r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                       var t = Ext.getCmp('modx-resource-tree');
                       if (t) { t.refresh(); }
                },scope:this}
            }
        });
        return true;
    }
    ,changeParent: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        var r = {resources: cs};
        if (!this.changeParentWindow) {
            this.changeParentWindow = MODx.load({
                xtype: 'orphans-window-change-parent'
                ,record: r
                ,listeners: {
                    'success': {fn:function(r) {
                       this.refresh();
                       var t = Ext.getCmp('modx-resource-tree');
                       if (t) { t.refresh(); }
                    },scope:this}
                }
            });
        }
        this.changeParentWindow.setValues(r);
        this.changeParentWindow.show(e.target);
        return true;
    }
    ,changeTemplate: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        var r = {resources: cs};
        if (!this.changeTemplateWindow) {
            this.changeTemplateWindow = MODx.load({
                xtype: 'orphans-window-change-template'
                ,record: r
                ,listeners: {
                    'success': {fn:function(r) {
                       this.refresh();
                    },scope:this}
                }
            });
        }
        this.changeTemplateWindow.setValues(r);
        this.changeTemplateWindow.show(e.target);
        return true;
    }
    ,changeAuthors: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        var r = {resources: cs};
        if (!this.changeAuthorsWindow) {
            this.changeAuthorsWindow = MODx.load({
                xtype: 'orphans-window-change-authors'
                ,record: r
                ,listeners: {
                    'success': {fn:function(r) {
                       this.refresh();
                    },scope:this}
                }
            });
        }
        this.changeAuthorsWindow.setValues(r);
        this.changeAuthorsWindow.show(e.target);
        return true;
    }
    ,changeDates: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        var r = {resources: cs};
        if (!this.changeDatesWindow) {
            this.changeDatesWindow = MODx.load({
                xtype: 'orphans-window-change-dates'
                ,record: r
                ,listeners: {
                    'success': {fn:function(r) {
                       this.refresh();
                    },scope:this}
                }
            });
        }
        this.changeDatesWindow.setValues(r);
        this.changeDatesWindow.show(e.target);
        return true;
    }

    ,getBatchMenu: function() {
        var bm = [];
        bm.push({
            text: _('orphans.toggle')
            ,menu: {
                items: [{
                    text: _('orphans.published')
                    ,handler: function(btn,e) {
                        this.batchAction('publish',btn,e);
                    }
                    ,scope: this
                },{
                    text: _('orphans.unpublished')
                    ,handler: function(btn,e) {
                        this.batchAction('unpublish',btn,e);
                    }
                    ,scope: this
                },'-',{
                    text: _('orphans.hidemenu')
                    ,handler: function(btn,e) {
                        this.batchAction('hidemenu',btn,e);
                    }
                    ,scope: this
                },{
                    text: _('orphans.unhidemenu')
                    ,handler: function(btn,e) {
                        this.batchAction('unhidemenu',btn,e);
                    }
                    ,scope: this
                },'-',{
                    text: _('orphans.cacheable')
                    ,handler: function(btn,e) {
                        this.batchAction('cacheable',btn,e);
                    }
                    ,scope: this
                },{
                    text: _('orphans.uncacheable')
                    ,handler: function(btn,e) {
                        this.batchAction('cacheable',btn,e);
                    }
                    ,scope: this
                },'-',{
                    text: _('orphans.searchable')
                    ,handler: function(btn,e) {
                        this.batchAction('searchable',btn,e);
                    }
                    ,scope: this
                },{
                    text: _('orphans.unsearchable')
                    ,handler: function(btn,e) {
                        this.batchAction('unsearchable',btn,e);
                    }
                    ,scope: this
                },'-',{
                    text: _('orphans.richtext')
                    ,handler: function(btn,e) {
                        this.batchAction('richtext',btn,e);
                    }
                    ,scope: this
                },{
                    text: _('orphans.unrichtext')
                    ,handler: function(btn,e) {
                        this.batchAction('unrichtext',btn,e);
                    }
                    ,scope: this
                },'-',{
                    text: _('orphans.deleted')
                    ,handler: function(btn,e) {
                        this.batchAction('delete',btn,e);
                    }
                    ,scope: this
                },{
                    text: _('orphans.undeleted')
                    ,handler: function(btn,e) {
                        this.batchAction('undelete',btn,e);
                    }
                    ,scope: this
                }]
            }
        },{
            text: _('orphans.change_parent')
            ,handler: this.changeParent
            ,scope: this
        },{
            text: _('orphans.change_template')
            ,handler: this.changeTemplate
            ,scope: this
        },{
            text: _('orphans.change_dates')
            ,handler: this.changeDates
            ,scope: this
        },{
            text: _('orphans.change_authors')
            ,handler: this.changeAuthors
            ,scope: this
        });
        return bm;
    }
});
Ext.reg('orphans-grid-resource',Orphans.grid.Resources);


Orphans.window.ChangeParent = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('orphans.change_parent')
        ,url: Orphans.config.connector_url
        ,baseParams: {
            action: 'mgr/resource/changeparent'
        }
        ,width: 400
        ,fields: [{
            xtype: 'hidden'
            ,name: 'resources'
        },{
            xtype: 'textfield'
            ,fieldLabel: _('orphans.parent')
            ,name: 'parent'
            ,anchor: '90%'
        }]
    });
    Orphans.window.ChangeParent.superclass.constructor.call(this,config);
};
Ext.extend(Orphans.window.ChangeParent,MODx.Window);
Ext.reg('orphans-window-change-parent',Orphans.window.ChangeParent);

Orphans.window.ChangeTemplate = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('orphans.change_template')
        ,url: Orphans.config.connector_url
        ,baseParams: {
            action: 'mgr/resource/changetemplate'
        }
        ,width: 400
        ,fields: [{
            xtype: 'hidden'
            ,name: 'resources'
        },{
            xtype: 'modx-combo-template'
            ,fieldLabel: _('orphans.template')
            ,name: 'template'
            ,hiddenName: 'template'
            ,anchor: '90%'
        }]
    });
    Orphans.window.ChangeTemplate.superclass.constructor.call(this,config);
};
Ext.extend(Orphans.window.ChangeTemplate,MODx.Window);
Ext.reg('orphans-window-change-template',Orphans.window.ChangeTemplate);


Orphans.window.ChangeAuthors = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('orphans.change_authors')
        ,url: Orphans.config.connector_url
        ,baseParams: {
            action: 'mgr/resource/changeauthors'
        }
        ,width: 400
        ,fields: [{
            xtype: 'hidden'
            ,name: 'resources'
        },{
            xtype: 'modx-combo-user'
            ,fieldLabel: _('orphans.createdby')
            ,name: 'createdby'
            ,hiddenName: 'createdby'
            ,anchor: '90%'
        },{
            xtype: 'modx-combo-user'
            ,fieldLabel: _('orphans.editedby')
            ,name: 'editedby'
            ,hiddenName: 'editedby'
            ,anchor: '90%'
        },{
            xtype: 'modx-combo-user'
            ,fieldLabel: _('orphans.publishedby')
            ,name: 'publishedby'
            ,hiddenName: 'publishedby'
            ,anchor: '90%'
        }]
    });
    Orphans.window.ChangeAuthors.superclass.constructor.call(this,config);
};
Ext.extend(Orphans.window.ChangeAuthors,MODx.Window);
Ext.reg('orphans-window-change-authors',Orphans.window.ChangeAuthors);


Orphans.window.ChangeDates = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('orphans.change_dates')
        ,url: Orphans.config.connector_url
        ,baseParams: {
            action: 'mgr/resource/changedates'
        }
        ,width: 500
        ,fields: [{
            xtype: 'hidden'
            ,name: 'resources'
        },{
            xtype: 'xdatetime'
            ,fieldLabel: _('orphans.createdon')
            ,name: 'createdon'
            ,hiddenName: 'createdon'
            ,anchor: '90%'
            ,allowBlank: true
            ,dateFormat: MODx.config.manager_date_format
            ,timeFormat: MODx.config.manager_time_format
            ,dateWidth: 120
            ,timeWidth: 120
        },{
            xtype: 'xdatetime'
            ,fieldLabel: _('orphans.editedon')
            ,name: 'editedon'
            ,hiddenName: 'editedon'
            ,anchor: '90%'
            ,allowBlank: true
            ,dateFormat: MODx.config.manager_date_format
            ,timeFormat: MODx.config.manager_time_format
            ,dateWidth: 120
            ,timeWidth: 120
        },{
            xtype: 'xdatetime'
            ,fieldLabel: _('orphans.pub_date')
            ,name: 'pub_date'
            ,hiddenName: 'pub_date'
            ,anchor: '90%'
            ,allowBlank: true
            ,dateFormat: MODx.config.manager_date_format
            ,timeFormat: MODx.config.manager_time_format
            ,dateWidth: 120
            ,timeWidth: 120
        },{
            xtype: 'xdatetime'
            ,fieldLabel: _('orphans.unpub_date')
            ,name: 'unpub_date'
            ,hiddenName: 'unpub_date'
            ,anchor: '90%'
            ,allowBlank: true
            ,dateFormat: MODx.config.manager_date_format
            ,dateWidth: 120
            ,timeWidth: 120
        }]
    });
    Orphans.window.ChangeDates.superclass.constructor.call(this,config);
};
Ext.extend(Orphans.window.ChangeDates,MODx.Window);
Ext.reg('orphans-window-change-dates',Orphans.window.ChangeDates);