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
            ,items: [
                {
                    title: _('orphans.chunks')
                    , defaults: { autoHeight: true }
                    , items: [{
                        html: '<p>' + _('orphans.chunks.intro_msg') + '</p>'
                        , border: false
                        , bodyStyle: 'padding: 10px'
                    }
                    ,{
                        xtype: 'orphans-grid-chunk'
                        , preventRender: true
                    }
                    ]

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
                ,{
                    title: _('orphans.tvs'), defaults: { autoHeight: true }, items: [
                    {
                        html: '<p>' + _('orphans.tvs.intro_msg') + '</p>'
                        , border: false
                        , bodyStyle: 'padding: 10px'
                    },
                    {
                        xtype: 'orphans-grid-tv'
                        , preventRender: true
                    }
                ]

                }
                ,{
                    title: _('orphans.snippets')
                    ,defaults: { autoHeight: true }
                    ,items: [{
                        html: '<p>'+_('orphans.snippets.intro_msg')+'</p>'
                        ,border: false
                        ,bodyStyle: 'padding: 10px'
                    },{
                        xtype: 'orphans-grid-snippet'
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
