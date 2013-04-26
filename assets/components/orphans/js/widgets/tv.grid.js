Orphans.grid.Tvs = function (config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel();

    Ext.applyIf(config, {
        url: Orphans.config.connector_url
        , baseParams: {
           action: 'mgr/dummy'
            /* ,thread: config.thread */
        }
        , pageSize: 300
        , fields: [
            {name:'id', sortType: Ext.data.SortTypes.asInt}
            , {name: 'name', sortType: Ext.data.SortTypes.asUCString}
            , {name: 'category', sortType: Ext.data.SortTypes.asUCString}
            , {name: 'description'}
         ]
        , paging: true
        , autosave: false
        , remoteSort: false
        , autoExpandColumn: 'description'
        , cls: 'orphans-grid'
        , sm: this.sm
        , columns: [this.sm, {
            header: _('id')
            ,dataIndex: 'id'
            ,sortable: true
            ,width: 50
        }, {
            header: _('name')
            ,dataIndex: 'name'
            ,sortable: true
            ,width: 100
                                                                                                           }, {
           header: _('category'),
            dataIndex: 'category',
            sortable: true,
            width: 120
        }, {
            header: _('description')
            , dataIndex: 'description'
            , sortable: false
            , width: 300
        }]
        ,viewConfig: {
            forceFit: true,
            enableRowBody: true,
            showPreview: true,
            getRowClass: function (rec, ri, p) {
                var cls = 'orphans-row';

                if (this.showPreview) {
                    //p.body = '<div class="orphans-resource-body">'+rec.data.content+'</div>';
                    return cls + ' orphans-resource-expanded';
                }
                return cls + ' orphans-resource-collapsed';
            }
        }
        , tbar: [{
                text: _('orphans.bulk_actions')
                , menu: this.getBatchMenu()
            }
            ,{xtype: 'tbspacer', width: 200}
            ,{
                xtype: 'button'
                , id: 'orphans-tvs-reload'
                , text: _('orphans.reload')
                , listeners: {
                'click': {fn: this.reloadTvs, scope: this}
            }
            }
        ]
    });
    Orphans.grid.Tvs.superclass.constructor.call(this, config)
};
Ext.extend(Orphans.grid.Tvs, MODx.grid.Grid, {
     reloadTvs: function () {
        this.getStore().baseParams = {
            action: 'mgr/tv/getList'
            ,orphanSearch: 'modTemplateVar'
        };
        // Ext.getCmp('orphans-grid-chunk').reset();
        this.getBottomToolbar().changePage(1);
        // this.refresh();

    }/*, _renderUrl: function (v, md, rec) {
        return '<a href="' + rec.data.url + '" target="_blank">' + rec.data.name + '</a>';
    }*/
    , _showMenu: function (g, ri, e) {
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

            for (var zz = 0; zz < z.length; zz++) {
                this.menu.add(z[zz]);
            }
            this.menu.show(e.target);
        }
    }
    , getSelectedAsList: function () {
        var sels = this.getSelectionModel().getSelections();
        if (sels.length <= 0) return false;

        var cs = '';
        for (var i = 0; i < sels.length; i++) {
            cs += ',' + sels[i].data.id;
        }
        cs = Ext.util.Format.substr(cs, 1);
        return cs;
    }/*, batchAction: function (act, btn, e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
                              url: this.config.url, params: {
                action: 'mgr/resource/batch', resources: cs, batch: act
            }, listeners: {
                'success': {fn: function (r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                    var t = Ext.getCmp('modx-resource-tree');
                    if (t) {
                        t.refresh();
                    }
                }, scope: this}
            }
                          });
        return true;
    }*/
    , changeTVValues: function (btn, e) {
        var sm = this.getSelectionModel();
        var cs = sm.getSelected();
        if (cs === false) return false;

        location.href = MODx.config.manager_url + '?a=' + MODx.request.a + '&action=template/tvs&template=' + cs.data.id;
    }, changeDefaultTVValues: function (btn, e) {
        var sm = this.getSelectionModel();
        var cs = sm.getSelected();
        if (cs === false) return false;

        location.href = MODx.config.manager_url + '?a=' + MODx.request.a + '&action=template/tvdefaults&template=' + cs.data.id;
    }, changeCategory: function (btn, e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        var r = {tvs: cs};
        if (!this.changeCategoryWindow) {
            this.changeCategoryWindow = MODx.load({
                  xtype: 'orphans-tv-window-change-category'
                  , record: r
                  , listeners: {
                    'success': {fn: function (r) {
                        this.refresh();
                    }, scope: this}
                }
                                                  });
        }
        this.changeCategoryWindow.setValues(r);
        this.changeCategoryWindow.show(e.target);
        return true;
    }
    , tvRename: function () {
        var cs = this.getSelectedAsList();
        // Ext.Msg.alert('Info', cs);
        if (cs === false) return false;
        /*var sels = cs.split(",");
        for (var i = 0, len = sels.length; i < len; i++) {
        // Ext.each(sels, function (sel) {

            Ext.Msg.alert('Info', sels[i]);
            *//*ips.push(sel.get('ip'));
            nodes.push(sel.get('node'));*//*
        }*/

        var sels = this.getSelectionModel().getSelections();
        if (sels.length <= 0) return false;
        var s = this.getStore();
        for (var i = 0; i < sels.length; i = i + 1) {
           var id = sels[i].get('id');
           var name = sels[i].get('name');
           var ri = id;
           var record = s.getById(ri);
           record.set("name", Orphans.config.prefix + name);
           record.commit();
        }
        MODx.Ajax.request({
                url: this.config.url, params: {
                action: 'mgr/tv/rename',
                tvs: cs /* batch: act */
            }, listeners: {
                'success': {fn: function (r) {
                    this.getSelectionModel().clearSelections(true);
                    // this.refresh();
                    /*var t = Ext.getCmp('modx-resource-tree');
                    if (t) {
                        t.refresh();
                    }*/
                }, scope: this}
            }
}       );
        return true;

    }
    , tvUnRename: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
                              url: this.config.url, params: {
                action: 'mgr/tv/unrename',
                tvs: cs /* batch: act */
            }, listeners: {
                'success': {fn: function (r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                    /*var t = Ext.getCmp('modx-resource-tree');
                    if (t) {
                        t.refresh();
                    }*/
                }, scope: this}
            }
                          });
        return true;

    }
    , tvDelete: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        MODx.msg.confirm({
             title: _('orphans.delete')
             , text: _('orphans.confirm_delete')
             , url: this.config.url
             , params: {
                action: 'mgr/tv/delete'
                , tvs: cs
            }, listeners: {
                'success': {fn: function (r) {
                    this.refresh();
                }
                , scope: this}
                , 'failure': {fn: function (r) {
                    MODx.msg.alert();
                }
                , scope: this}
            }
        });
        return true;
    }


    , getBatchMenu: function () {
        var bm = [];
        bm.push(
            {
                text: _('orphans.change_category')
                , handler: this.changeCategory
                , scope: this
            }
            , '-'
            , {
                    text: _('orphans.rename_tv')
                    ,handler: this.tvRename
                    ,scope: this
              }
            , '-'
            , {
                text: _('orphans.unrename_tv')
                , handler: this.tvUnRename
                , scope: this
            }
            , '-'
            , '-'
            , {
                text: _('orphans.delete_tv')
                ,handler: this.tvDelete
                , scope: this
            });
        return bm;
    }
});
Ext.reg('orphans-grid-tv', Orphans.grid.Tvs);


Orphans.window.ChangeCategory = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('orphans.change_category')
        , url: Orphans.config.connector_url
        , baseParams: {
            action: 'mgr/tv/changecategory'
            }
        ,width: 400
        ,fields: [{
            xtype: 'hidden'
            ,name: 'tvs'
        },{
            xtype: 'modx-combo-category'
            ,fieldLabel: _('orphans.category')
            ,name: 'category'
            ,hiddenName: 'category'
            ,anchor: '90%'
        }]
    });
    Orphans.window.ChangeCategory.superclass.constructor.call(this, config);
};
Ext.extend(Orphans.window.ChangeCategory, MODx.Window);
Ext.reg('orphans-tv-window-change-category', Orphans.window.ChangeCategory);

/* Ext.data.Store.commitChanges() is a client-side-only method. It does not communicate with the server in any form. http://jsfiddle.net/27fRh/ + rec.commit();

dataStore.each(function(rec){
 alert(rec.get(field1));
 }*/