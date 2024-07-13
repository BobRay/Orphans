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

$v = @include MODX_CORE_PATH . 'docs/version.inc.php';
$isMODX3 = $v['version'] >= 3;

if ($isMODX3) {
    require_once MODX_CORE_PATH . 'vendor/autoload.php';
} else {
    require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';
}

/**
 * Encapsulates the interaction of MODx manager with an HTTP request.
 *
 * {@inheritdoc}
 *
 * @package orphans
 *
 */

if ($isMODX3) {
    abstract class DynamicOrphansControllerRequest extends MODX\Revolution\modManagerRequest {
    }
} else {
    abstract class DynamicOrphansControllerRequest extends modRequest {
    }
}

class OrphansControllerRequest extends DynamicOrphansControllerRequest {
    public $orphans = null;
    public $actionVar = 'action';
    public $defaultAction = 'home';

    function __construct(Orphans &$orphans) {
        parent:: __construct($orphans->modx);
        $this->orphans =& $orphans;
    }

    /**
     * Extends modRequest::handleRequest and loads the proper error handler and
     * actionVar value.
     *
     * {@inheritdoc}
     */
    public function handleRequest() {
        $this->loadErrorHandler();

        /* save page to manager object. allow custom actionVar choice for extending classes. */
        $this->action = isset($_REQUEST[$this->actionVar]) ? $_REQUEST[$this->actionVar] : $this->defaultAction;

        return $this->_respond();
    }

    /**
     * Prepares the MODx response to a mgr request that is being handled.
     *
     * @access public
     * @return boolean True if the response is properly prepared.
     */
    private function _respond() {
        $modx =& $this->modx;
        $orphans =& $this->orphans;
        $viewHeader = include $this->orphans->config['corePath'] . 'controllers/mgr/header.php';

        $f = $this->orphans->config['corePath'] . 'controllers/mgr/' . $this->action . '.php';
        if (file_exists($f)) {
            $viewOutput = include $f;
        } else {
            $viewOutput = 'Action not found: ' . $f;
        }

        return $viewHeader . $viewOutput;
    }
}
