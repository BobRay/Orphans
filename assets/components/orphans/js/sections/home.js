/**
 * JS file for Orphans extra
 *
 * Copyright 2013-2019 Bob Ray <https://bobsguides.com>
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