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
 * Remove orphans prefix from multiple tvs
 *
 * @package orphans
 * @subpackage processors
 */
if (!$modx->hasPermission('save_tv')) return $modx->error->failure($modx->lexicon('access_denied'));

if (empty($scriptProperties['tvs'])) {
    return $modx->error->failure($modx->lexicon('orphans.tvs_err_ns'));
}
/* get parent */

/* iterate over tvs */
$tvIds = explode(',',$scriptProperties['tvs']);
$prefix = $modx->getOption('orphans.prefix', null, 'aaOrphan.');
foreach ($tvIds as $tvId) {
    $tv = $modx->getObject('modTemplateVar',$tvId);
    if ($tv == null) continue;
    
    $name = $tv->get('name');
    $name = str_replace($prefix, '', $name);
    $tv->set('name', $name);
    $tv->save(3600);
}

return $modx->error->success();
