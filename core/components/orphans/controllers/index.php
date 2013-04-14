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
 * @package orphans
 * @subpackage controllers
 */
// require_once dirname(dirname(__FILE__)).'/model/orphans/orphans.class.php';
// $orphans = new Orphans($modx);
//return $orphans->initialize('mgr');

/* @var $modx modX */
// $modx->lexicon->load
if (! $modx->user->hasSessionContext('mgr')) return $modx->lexicon('access_denied');

require_once $modx->getOption('orphans.core_path', null, MODX_CORE_PATH . 'components/orphans/') . 'model/orphans/orphans.class.php';
$orphans = new Orphans($modx);

$modx->regClientCSS($modx->getOption('orphans.assets_url', null, MODX_ASSETS_URL . 'components/orphans/') . 'css/orphans.css');

$orphans->process();
$output = $orphans->getOutput();
if (php_sapi_name() != 'cli') {
    return $output;
} else {
    echo $output;
}
return '';

