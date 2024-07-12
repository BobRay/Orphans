<?php
/**
 * Validator for Orphans extra
 *
 * Copyright 2013-2024 by Bob Ray <https://bobsguides.com>
 * Created on 01-13-2019
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
 * @package orphans
 * @subpackage build
 */

/* @var $object xPDOObject */
/* @var $transport xPDOObject */
/* @var $modx modX */

/* @var array $options */

if ($transport) {
    $modx =& $transport->xpdo;
} else {
    $modx =& $object->xpdo;
}

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:

        /* Remove old action object */
        $action = $modx->getObject('modAction', array('namespace'=> 'orphans'));
        if ($action) {
            $action->remove();
        }

    case xPDOTransport::ACTION_UNINSTALL:
        break;
}


return true;
