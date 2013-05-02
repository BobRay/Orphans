<?php
/**
 * Orphans
 *
 * Copyright 2013 by Bob Ray <http://bobsguides.com>
 *
 * This file is part of Orphans, a utility for finding unused elements.
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
 *
 * @package orphans
 */
/**
 * Loads the home page.
 *
 * @package orphans
 * @subpackage controllers
 */

$modx->regClientStartupScript($orphans->config['jsUrl'] . 'widgets/tv.grid.js');
$modx->regClientStartupScript($orphans->config['jsUrl'] . 'widgets/chunk.grid.js');
$modx->regClientStartupScript($orphans->config['jsUrl'].'widgets/template.grid.js');
$modx->regClientStartupScript($orphans->config['jsUrl'] . 'widgets/snippet.grid.js');
$modx->regClientStartupScript($orphans->config['jsUrl'].'widgets/home.panel.js');
$modx->regClientStartupScript($orphans->config['jsUrl'].'sections/home.js');
$output = '<div id="orphans-panel-home-div"></div>';

return $output;
