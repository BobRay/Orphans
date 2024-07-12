<?php
/**
 * Orphans
 *
 * Copyright 2013-2024 Bob Ray <https://bobsguides.com>
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
 * rename multiple chunks
 *
 * @package orphans
 * @subpackage processors
 */
class OrphansUnrenameProcessor extends modProcessor {
    public function process() {
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
        $prefix = $this->modx->getOption('orphans.prefix', null, 'aaOrphan.');
        $objectIds = explode(',', $objects);
        foreach ($objectIds as $objectId) {
            $object = $this->modx->getObject($class, $objectId);
            if ($object == null) {
                continue;
            }

            $nameField = $class == 'modTemplate' ? 'templatename' : 'name';
            $name = $object->get($nameField);
            if (strstr($name, $prefix)) {
                $name = str_replace($prefix, '', $name);
                $object->set($nameField, $name);
                $object->save(3600);
            }
        }
        return $this->modx->error->success();
    }
}

return 'OrphansUnrenameProcessor';
