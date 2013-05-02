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
 * rename multiple snippets
 *
 * @package orphans
 * @subpackage processors
 */
if (!$modx->hasPermission('save_snippet')) return $modx->error->failure($modx->lexicon('access_denied'));

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
    $name = str_replace($prefix, '', $name);
    $snippet->set('name', $name);
    if ($snippet->save(3600) === false) {
        
    }
}

return $modx->error->success();
