/**
 * JS file for Orphans extra
 *
 * Copyright 2013-2017 Bob Ray <https://bobsguides.com>
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

Orphans.grid.Templates = function(config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel();

    Ext.applyIf(config,{
        url: Orphans.config.connector_url
        ,baseParams: {
           action: 'mgr/dummy'
            /* ,thread: config.thread */
        }
        , pageSize: 300
        , fields: [
            {name: 'id', sortType: Ext.data.SortTypes.asInt}
            , {name: 'templatename', sortType: Ext.data.SortTypes.asUCString}
            , {name: 'category', sortType: Ext.data.SortTypes.asUCString}
            , {name: 'description'}
        ]
        ,paging: true
        ,autosave: false
        ,remoteSort: false
        ,autoExpandColumn: 'description'
        ,cls: 'orphans-grid'
        ,sm: this.sm
        ,columns: [this.sm,{
            header: _('id')
            ,dataIndex: 'id'
            ,sortable: true
            ,width: 50
        },{
            header: _('name')
            ,dataIndex: 'templatename'
            ,sortable: true
            ,width: 100
        },{
            header: _('category')
            ,dataIndex: 'category'
            ,sortable: true
            ,width: 120
        }
        ,{
            header: _('description')
            ,dataIndex: 'description'
            ,sortable: false
            ,width: 300
        }
        ]
        ,viewConfig: {
            forceFit:true,
            enableRowBody:true,
            showPreview:true,
            getRowClass : function(rec, ri, p){
                var cls = 'orphans-row';

                if(this.showPreview){
                    return cls+' orphans-resource-expanded';
                }
                return cls+' orphans-resource-collapsed';
            }
        }
        ,tbar: [{
            text: _('orphans.bulk_actions')
            ,menu: this.getBatchMenu()
        }
        ,{xtype: 'tbspacer', width: 200}

        ,{
        xtype: 'button'
        ,id: 'orphans-templates-reload'
        ,text: _('orphans.reload')

        ,listeners: {
            'click': {fn: this.reloadTemplates, scope: this}
        }

        }]
    });
    Orphans.grid.Templates.superclass.constructor.call(this,config)
};
Ext.extend(Orphans.grid.Templates,MODx.grid.Grid,{
    reloadTemplates: function () {
        this.getStore().baseParams = {
            action: 'mgr/getList'
            , orphanSearch: 'modTemplate'
        };

        this.getBottomToolbar().changePage(1);

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
        cs = Ext.util.Format.substr(cs,1);
        return cs;
    }

    ,changeCategory: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        var r = {templates: cs};
        if (!this.changeCategoryWindow) {
            this.changeCategoryWindow = MODx.load({
                xtype: 'orphans-template-window-change-category'
                ,record: r
                ,listeners: {
                    'success': {fn:function(r) {
                        // this.refresh();
                        var sels = this.getSelectionModel().getSelections();
                        var cat = Ext.getCmp('orphans-template-category-combo').lastSelectionText;
                        var s = this.getStore();
                        for (var i = 0; i < sels.length; i = i + 1) {
                            var id = sels[i].get('id');
                            var ri = id;
                            var record = s.getById(ri);
                            record.set("category", cat);
                            record.commit();
                        }
                        this.getSelectionModel().clearSelections(false);
                    },scope:this}
                }
            });
        }
        this.changeCategoryWindow.setValues(r);
        this.changeCategoryWindow.show(e.target);
        return true;
    }
    , templateRename: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
                url: this.config.url, params: {
                action: 'mgr/rename'
                , templates: cs
                , orphanSearch: 'modTemplate'
            }, listeners: {
                'success': {fn: function (r) {
                    var sels = this.getSelectionModel().getSelections();
                    if (sels.length <= 0) return false;
                    var s = this.getStore();
                    for (var i = 0; i < sels.length; i = i + 1) {
                        var prefix = Orphans.config.prefix;
                        var id = sels[i].get('id');
                        var name = sels[i].get('templatename');
                        var pos = name.indexOf(prefix);
                        if (pos != -1) {
                            continue;
                        }
                        var ri = id;
                        var record = s.getById(ri);
                        record.set("templatename", prefix + name);
                        record.commit();
                    }
                    this.getSelectionModel().clearSelections(false);

                    /*var t = Ext.getCmp('modx-element-tree');
                    if (t) {
                        t.refresh();
                    }*/
                }, scope: this}
            }
        });
        return true;

    }
    , templateUnRename: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
            url: this.config.url, params: {
                action: 'mgr/unrename'
                , templates: cs
                , orphanSearch: 'modTemplate'
            }, listeners: {
                'success': {fn: function (r) {
                    var sels = this.getSelectionModel().getSelections();
                    if (sels.length <= 0) return false;
                    var s = this.getStore();
                    for (var i = 0; i < sels.length; i = i + 1) {
                        var prefix = Orphans.config.prefix;
                        var id = sels[i].get('id');
                        var name = sels[i].get('templatename');
                        var pos = name.indexOf(prefix);
                        if (pos == -1) {
                            continue;
                        }
                        var ri = id;
                        var record = s.getById(ri);
                        name = name.replace(prefix, '');

                        record.set("templatename", name);
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

    , templateAddToIgnore: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
            url: this.config.url, params: {
                action: 'mgr/addtoignore'
                , templates: cs /* batch: act */
                , orphanSearch: 'modTemplate'
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

    , templateDelete: function () {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        MODx.msg.confirm({
             title: _('orphans.delete')
             , text: _('orphans.confirm_delete')
             , url: this.config.url
             , params: {
                action: 'mgr/delete'
                , templates: cs
                , orphanSearch: 'modTemplate'
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


    ,getBatchMenu: function() {
        var bm = [];
        bm.push({
            text: _('orphans.change_category')
            ,handler: this.changeCategory
            ,scope: this
        }
        ,'-'
        ,{
            text: _('orphans.rename_template')
            ,handler: this.templateRename
            ,scope: this
        }
            , '-'
        , {
                text: _('orphans.unrename_template')
                , handler: this.templateUnRename
                , scope: this
        }
            , '-'
            , {
                text: _('orphans.add_to_ignore')
                , handler: this.templateAddToIgnore
                , scope: this
            }

        , '-'
        , '-'
        ,{
            text: _('orphans.delete_template')
            ,handler: this.templateDelete
            ,scope: this
        });
        return bm;
    }
});
Ext.reg('orphans-grid-template',Orphans.grid.Templates);


Orphans.window.ChangeCategory = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('orphans.change_category')
        ,url: Orphans.config.connector_url
        ,baseParams: {
            action: 'mgr/changecategory'
            , orphanSearch: 'modTemplate'
        }
        ,width: 400
        ,fields: [{
            xtype: 'hidden'
            ,name: 'templates'
        }
        ,{
            xtype: 'modx-combo-category'
            ,id: 'orphans-template-category-combo'
            ,fieldLabel: _('orphans.category')
            ,name: 'category'
            ,hiddenName: 'category'
            ,anchor: '90%'
        }]
    });
    Orphans.window.ChangeCategory.superclass.constructor.call(this,config);
};
Ext.extend(Orphans.window.ChangeCategory,MODx.Window);
Ext.reg('orphans-template-window-change-category',Orphans.window.ChangeCategory);
