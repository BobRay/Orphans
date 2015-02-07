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
 * add multiple snippets to ignore list
 *
 * @package orphans
 * @subpackage processors
 */
if (!$modx->hasPermission('save_chunk')) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[Orphans addtoignore.php] No save_chunk permission');
    return $modx->error->failure($modx->lexicon('access_denied'));
}

if (empty($scriptProperties['snippets'])) {
    return $modx->error->failure($modx->lexicon('orphans.snippets_err_ns'));
}
/* get parent */

/* iterate over snippets */
$snippetIds = explode(',',$scriptProperties['snippets']);
$prefix = $modx->getOption('orphans.prefix', null, 'aaOrphan.');
foreach ($snippetIds as $snippetId) {
    $snippet = $modx->getObject('modSnippet',$snippetId);
    if ($snippet == null) continue;
    $name = $snippet->get('name');
    /* @var $chunk modChunk */
    $chunk = $modx->getObject('modChunk', array('name' => 'OrphansIgnoreList'));
    if ($chunk) {
        $content = $chunk->getContent();
        $content .= "\n" . $name;
        $chunk->setContent($content);
        $chunk->save(3600);
    } else {
        $modx->log(modX::LOG_LEVEL_ERROR, '[Orphans] Could not get OrphansIgnoreList chunk');
    }
}

return $modx->error->success();
