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
 * Get a list of templates
 *
 * @package orphans
 * @subpackage processors
 */

/* @var $modx modX */
/* @var $this modProcessor */

$v = @include MODX_CORE_PATH . 'docs/version.inc.php';
$isMODX3 = $v['version'] >= 3;

if ($isMODX3) {
    require_once MODX_CORE_PATH . 'vendor/autoload.php';
} else {
    require_once MODX_CORE_PATH . 'model/modx/modprocessor.class.php';
}

if ($isMODX3) {
    abstract class DynamicOrphansGetlistProcessor extends MODX\Revolution\Processors\Processor {
    }
} else {
    abstract class DynamicOrphansGetlistProcessor extends modProcessor {
    }
}


/* @var $orphans Orphans */
class orphansGetListProcessor extends DynamicOrphansGetlistProcessor {
    public function initialize() {

        $orphansCorePath = $this->modx->getOption('orphans.core_path', null, $this->modx->getOption('core_path') . 'components/orphans/');
        require_once $orphansCorePath . 'model/orphans/orphans.class.php';
        return parent::initialize();
    }

    public function process(array $scriptProperties = array()) {
        return $this->modx->toJSON($this->getData());
    }

    public function getData() {
        $data = array();
        $limit = intval($this->getProperty('limit'));
        $start = intval($this->getProperty('start'));
        $class = $this->getProperty('orphanSearch');
        // $orphans = $this->modx->orphans;
        $orphans = new Orphans($this->modx);

        $results = $orphans->process($class);
        $count = count($results);

        $data['total'] = $count;
        $data['results'] = $results;
        return $data;

    }
}

return 'orphansGetListProcessor';
