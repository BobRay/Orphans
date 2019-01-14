<?php
/**
 * Orphans
 *
 * Copyright 2013-2019 Bob Ray <https://bobsguides.com>
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
 * Change category for multiple resources
 *
 * @package orphans
 * @subpackage processors
 */
class OrphansChangeCategoryProcessor extends modProcessor {
    public function process(array $scriptProperties = array()) {
        $class = $this->getProperty('orphanSearch');
        if ($class == 'modTemplateVar') {
            $name = 'tv';
        } else {
            $name = strtolower(str_replace('mod', '', $class));
        }
        if (!$this->modx->hasPermission('save_' . $name)) {
            return $this->modx->error->failure($this->modx->lexicon('access_denied'));
        }

        $objects = $this->getProperty($name . 's');

        if (empty($objects)) {
            return $this->modx->error->failure($this->modx->lexicon('orphans.' . $name . 's' . '_err_ns'));
        }

        $category = $this->getProperty('category');

        if (!empty($category)) {
            $categoryObj = $this->modx->getObject('modCategory', (int) $category);
            if (empty($categoryObj)) {
                return $this->modx->error->failure($this->modx->lexicon('orphans.category_err_nf', array('id' => $category)));
            }
        }
        $objectIds = explode(',', $objects);
        foreach ($objectIds as $objectId) {
            $object = $this->modx->getObject($class, (int) $objectId);
            if ($object == null) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not get ' . $class . ' with ID ' . $objectId);
                continue;
            }
            $object->set('category', $category);
            $object->save(3600);

        }

        return $this->modx->error->success();
    }

}

return 'OrphansChangeCategoryProcessor';
/*if (!$modx->hasPermission('save_template')) return $modx->error->failure($modx->lexicon('access_denied'));

if (empty($scriptProperties['templates'])) {
    return $modx->error->failure($modx->lexicon('orphans.templates_err_ns'));
}

if (!empty($scriptProperties['category'])) {
    $category = $modx->getObject('modCategory',$scriptProperties['category']);
    if (empty($category)) return $modx->error->failure($modx->lexicon('orphans.category_err_nf',array('id' => $scriptProperties['category'])));
}


$templateIds = explode(',',$scriptProperties['templates']);
foreach ($templateIds as $templateId) {
    $template = $modx->getObject('modTemplate',$templateId);
    if ($template == null) continue;

    $template->set('category',$scriptProperties['category']);
    $template->save(3600);
}

return $modx->error->success();

*/

