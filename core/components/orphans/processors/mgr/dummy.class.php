<?php
/**
 * DO NOT DELETE
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
 * returns an empty list of objects to initialize grid
 *
 * @package orphans
 * @subpackage processors
 */

$v = @include MODX_CORE_PATH . 'docs/version.inc.php';
$isMODX3 = $v['version'] >= 3;

if ($isMODX3) {
    require_once MODX_CORE_PATH . 'vendor/autoload.php';
} else {
    require_once MODX_CORE_PATH . 'model/modx/modprocessor.class.php';
}

if ($isMODX3) {
    abstract class DynamicOrphansDummyProcessor extends MODX\Revolution\Processors\Processor {
    }
} else {
    abstract class DynamicOrphansDummyProcessor extends modProcessor {
    }
}


class orphansDummyProcessor extends DynamicOrphansDummyProcessor {

    public function process(array $scriptProperties = array()) {
        $fields = array(
            'id' => '',
            'name' => '',
            'category' => '',
            'description' => '',
        );

        /* @var $this modProcessor */
        return $this->outputArray($fields, 1);
    }


}

return 'orphansDummyProcessor';
