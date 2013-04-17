<?php
/**
 * Orphans
 *
 * Copyright 2010 by Shaun McCormick <shaun@modxcms.com>
 *
 * This file is part of Orphans, a batch resource editing Extra.
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
 * Change parent for multiple resources
 *
 * @package orphans
 * @subpackage processors
 */
if (!$modx->hasPermission('save_document')) return $modx->error->failure($modx->lexicon('access_denied'));

if (empty($scriptProperties['resources'])) {
    return $modx->error->failure($modx->lexicon('orphans.resources_err_ns'));
}
/* get parent */
if (empty($scriptProperties['parent'])) {
    return $modx->error->failure($modx->lexicon('orphans.parent_err_ns'));
}
$parentResource = $modx->getObject('modResource',$scriptProperties['parent']);
if (empty($parentResource)) return $modx->error->failure($modx->lexicon('orphans.parent_err_nf'));

/* iterate over resources */
$resourceIds = explode(',',$scriptProperties['resources']);
foreach ($resourceIds as $resourceId) {
    $resource = $modx->getObject('modResource',$resourceId);
    if ($resource == null) continue;

    $resource->set('parent',$scriptProperties['parent']);

    if ($resource->save() === false) {
        
    }
}

return $modx->error->success();
