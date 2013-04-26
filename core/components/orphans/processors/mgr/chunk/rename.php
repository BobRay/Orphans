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
 * rename multiple chunks
 *
 * @package orphans
 * @subpackage processors
 */
if (!$modx->hasPermission('save_chunk')) return $modx->error->failure($modx->lexicon('access_denied'));

if (empty($scriptProperties['chunks'])) {
    return $modx->error->failure($modx->lexicon('orphans.chunks_err_ns'));
}
/* get parent */

/* iterate over chunks */
$chunkIds = explode(',',$scriptProperties['chunks']);
$prefix = $modx->getOption('orphans.prefix', null, 'aaOrphan.');
foreach ($chunkIds as $chunkId) {
    $chunk = $modx->getObject('modChunk',$chunkId);
    if ($chunk == null) continue;
    $name = $chunk->get('name');
    $name = $prefix . $name;
    if (! strstr($name, $prefix)) {
        $chunk->set('name', $name);
        $chunk->save();
    }
}

return $modx->error->success();
