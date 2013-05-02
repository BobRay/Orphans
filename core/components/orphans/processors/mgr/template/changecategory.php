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
 * Change template for multiple resources
 *
 * @package orphans
 * @subpackage processors
 */
if (!$modx->hasPermission('save_template')) return $modx->error->failure($modx->lexicon('access_denied'));

if (empty($scriptProperties['templates'])) {
    return $modx->error->failure($modx->lexicon('orphans.templates_err_ns'));
}
/* get parent */
if (!empty($scriptProperties['category'])) {
    $category = $modx->getObject('modCategory',$scriptProperties['category']);
    if (empty($category)) return $modx->error->failure($modx->lexicon('orphans.category_err_nf',array('id' => $scriptProperties['category'])));
}

/* iterate over resources */
$templateIds = explode(',',$scriptProperties['templates']);
foreach ($templateIds as $templateId) {
    $template = $modx->getObject('modTemplate',$templateId);
    if ($template == null) continue;

    $template->set('category',$scriptProperties['category']);
    $template->save(3600);
}

return $modx->error->success();
