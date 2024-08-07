/**
 * JS file for Orphans extra
 *
 * Copyright 2013-2024 Bob Ray <https://bobsguides.com>
 * Created on 04-13-2013
 *
 * Orphans is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Orphans is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Orphans; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 * @package orphans
 */
/* These are for LexiconHelper:
 $modx->lexicon->load('orphans:default');
 include 'orphans.class.php'
 */

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
            action: 'mgr/getList'
            ,orphanSearch: 'modTemplateVar'
        };

        this.getBottomToolbar().changePage(1);
        // this.refresh();

    }
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
    }

    , changeCategory: function (btn, e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        var r = {tvs: cs};
        if (!this.changeCategoryWindow) {
            this.changeCategoryWindow = MODx.load({
                  xtype: 'orphans-tv-window-change-category'
                  , record: r
                  , listeners: {
                    'success': {fn: function (r) {
                        // this.refresh();
                        var sels = this.getSelectionModel().getSelections();
                        var cat = Ext.getCmp('orphans-tv-category-combo').lastSelectionText;
                        var s = this.getStore();
                        for (var i = 0; i < sels.length; i = i + 1) {
                            var id = sels[i].get('id');
                            var ri = id;
                            var record = s.getById(ri);
                            record.set("category", cat);
                            record.commit();
                        }
                        this.getSelectionModel().clearSelections(false);
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
        if (cs === false) return false;

        MODx.Ajax.request({
            url: this.config.url, params: {
            action: 'mgr/rename'
            , tvs: cs
            , orphanSearch: 'modTemplateVar'
            }, listeners: {
                'success': {fn: function (r) {
                    var sels = this.getSelectionModel().getSelections();
                    if (sels.length <= 0) return false;
                    var s = this.getStore();
                    for (var i = 0; i < sels.length; i = i + 1) {
                        var prefix = Orphans.config.prefix;
                        var id = sels[i].get('id');
                        var name = sels[i].get('name');
                        var pos = name.indexOf(prefix);
                        if (pos != -1) {
                            continue;
                        }
                        var ri = id;
                        var record = s.getById(ri);
                        record.set("name", prefix + name);
                        record.commit();
                    }
                    this.getSelectionModel().clearSelections(false);

                    /*var t = Ext.getCmp('modx-element-tree');
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
                action: 'mgr/unrename'
                , tvs: cs
                , orphanSearch: 'modTemplateVar'
            }
            , listeners: {
                'success': {fn: function (r) {
                    var sels = this.getSelectionModel().getSelections();
                    if (sels.length <= 0) return false;
                    var s = this.getStore();
                    for (var i = 0; i < sels.length; i = i + 1) {
                        var prefix = Orphans.config.prefix;
                        var id = sels[i].get('id');
                        var name = sels[i].get('name');
                        var pos = name.indexOf(prefix);
                        if (pos == -1) {
                            continue;
                        }
                        var ri = id;
                        var record = s.getById(ri);
                        name = name.replace(prefix, '');

                        record.set("name", name);
                        record.commit();
                    }
                    this.getSelectionModel().clearSelections(false);
                    // this.refresh();
                    /*var t = Ext.getCmp('modx-element-tree');
                    if (t) {
                        t.refresh();
                    }*/
                }, scope: this}
            }
        });
        return true;

    }

    , tvAddToIgnore: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
            url: this.config.url, params: {
                action: 'mgr/addtoignore'
                , tvs: cs /* batch: act */
                , orphanSearch: 'modTemplateVar'
            }
            , listeners: {
                'success': {fn: function (r) {
                    // this.refresh();
                    var sels = this.getSelectionModel().getSelections();
                    var s = this.getStore();
                    for (var i = 0; i < sels.length; i = i + 1) {

                        var id = sels[i].get('id');
                        var ri = id;
                        var record = s.getById(ri);
                        s.remove(record);
                    }
                }
                , scope: this}
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
                action: 'mgr/delete'
                , tvs: cs
                , orphanSearch: 'modTemplateVar'
            }
            , listeners: {
                'success': {fn: function (r) {
                    // this.refresh();
                    var sels = this.getSelectionModel().getSelections();
                    if (sels.length <= 0) return false;
                    var s = this.getStore();
                    for (var i = 0; i < sels.length; i = i + 1) {

                        var id = sels[i].get('id');
                        var ri = id;
                        var record = s.getById(ri);
                        s.remove(record);
                    }
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
                text: _('orphans.tvs_change_category')
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
            , {
                text: _('orphans.add_to_ignore')
                , handler: this.tvAddToIgnore
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
        title: _('orphans.tvs_change_category')
        , url: Orphans.config.connector_url
        , baseParams: {
            action: 'mgr/changecategory'
            , orphanSearch: 'modTemplateVar'
            }
        ,width: 400
        ,fields: [{
            xtype: 'hidden'
            ,name: 'tvs'
            }
            ,{
                xtype: 'modx-combo-category'
                ,id: 'orphans-tv-category-combo'
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
