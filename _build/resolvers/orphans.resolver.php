<?php
/**
 * Resolver for Orphans extra
 *
 * Copyright 2013-2019 by Bob Ray <https://bobsguides.com>
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
/* @var $modx modX */

/* @var array $options */

class Remover {
    public function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }

    }
}

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            /*  Remove old files */
           //  Files:
            $files = array(
                MODX_ASSETS_PATH . 'components/orphans/index.html',
                MODX_CORE_PATH . 'components/orphans/controllers/index.php',
                MODX_CORE_PATH . 'components/orphans/processors/mgr/dummy.php',
                MODX_CORE_PATH . 'components/orphans/index.php'
            );

            foreach ($files as $file) {
                if(file_exists($file)) {
                    unlink($file);
                }
            }
            // Directories:
               $dirs = array(
                    MODX_CORE_PATH . 'components/orphans/elements/snippets',
                    MODX_CORE_PATH . 'components/orphans/processors/mgr/chunk',
                    MODX_CORE_PATH . 'components/orphans/processors/mgr/template',
                    MODX_CORE_PATH . 'components/orphans/processors/mgr/snippet',
                    MODX_CORE_PATH . 'components/orphans/processors/mgr/tv',
                );
            $remover = new Remover();
            foreach ($dirs as $dir) {
                if(is_dir($dir)) {
                    $remover->rrmdir($dir);
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;