Orphans.grid.Chunks = function (config) {
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
                , id: 'orphans-chunks-reload'
                , text: _('orphans.reload')
                , listeners: {
                'click': {fn: this.reloadChunks, scope: this}
            }
            }
        ]
    });
    Orphans.grid.Chunks.superclass.constructor.call(this, config)
};
Ext.extend(Orphans.grid.Chunks, MODx.grid.Grid, {
     reloadChunks: function () {
        this.getStore().baseParams = {
            action: 'mgr/chunk/getList'
            ,orphanSearch: 'modChunk'
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

        var r = {chunks: cs};
        if (!this.changeCategoryWindow) {
            this.changeCategoryWindow = MODx.load({
                  xtype: 'orphans-chunk-window-change-category'
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
    , chunkRename: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
                url: this.config.url, params: {
                action: 'mgr/chunk/rename',
                chunks: cs /* batch: act */
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
    , chunkUnRename: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
                              url: this.config.url, params: {
                action: 'mgr/chunk/unrename',
                chunks: cs /* batch: act */
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
    , chunkDelete: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        MODx.msg.confirm({
             title: _('orphans.delete')
             , text: _('orphans.confirm_delete')
             , url: this.config.url
             , params: {
                action: 'mgr/chunk/delete'
                , chunks: cs
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
                    text: _('orphans.rename_chunk')
                    ,handler: this.chunkRename
                    ,scope: this
              }
            , '-'
            , {
                text: _('orphans.unrename_chunk')
                , handler: this.chunkUnRename
                , scope: this
            }
            , '-'
            , '-'
            , {
                text: _('orphans.delete_chunk')
                ,handler: this.chunkDelete
                , scope: this
            });
        return bm;
    }
});
Ext.reg('orphans-grid-chunk', Orphans.grid.Chunks);


Orphans.window.ChangeCategory = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('orphans.change_category')
        , url: Orphans.config.connector_url
        , baseParams: {
            action: 'mgr/chunk/changecategory'
            }
        ,width: 400
        ,fields: [{
            xtype: 'hidden'
            ,name: 'chunks'
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
Ext.reg('orphans-chunk-window-change-category', Orphans.window.ChangeCategory);

/* Ext.data.Store.commitChanges() is a client-side-only method. It does not communicate with the server in any form. */