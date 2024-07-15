<?php
/**
 * Orphans snippet for Orphans extra
 *
 * Copyright 2013-2024 Bob Ray <https://bobsguides.com>
 * Created on 02-07-2015
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
 * Description
 * -----------
 * Standalone Version of Orphans
 *
 * Comment out line 39 (exit) before running
 * Comment it out again after running it
 *
 * Variables
 * ---------
 * @var $modx modX
 * @var $scriptProperties array
 *
 * @package orphans
 **/
exit;
if (!defined('MODX_CORE_PATH')) {
    @include(dirname(__FILE__) . '/config.core.php');

    if (!defined('MODX_CORE_PATH')) {
        @include dirname(__FILE__,6 ) . '/config.core.php';
    }
    if (!defined('MODX_CORE_PATH')) {
        die('No config.core.php');
    }
    @include MODX_CORE_PATH . 'model/modx/modx.class.php';
    $modx = new modX();
    if ($modx) {
        $modx->initialize('mgr');
    } else {
        die('No MODX');
    }


    /* Make it run in either MODX 2 or MODX 3 */
    $prefix = $modx->getVersionData()['version'] >= 3
      ? 'MODX\Revolution\\'
      : '';

    $modx->getService('error', 'error.modError', '', '');

    $modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
    if (php_sapi_name() == 'cli') {
        $cliMode = true;
        $modx->setLogTarget('ECHO');
    } else {
        $modx->setLogTarget('HTML');
    }
    $modx->getRequest();
    $homeId = $modx->getOption('site_start');
    $homeResource = $modx->getObject($prefix . 'modResource', $homeId);

    if ($homeResource) {
        $modx->resource = $homeResource;
    } else {
        $homeResource = $modx->getObject($prefix . 'modResource', 1);
        if ($homeResource) {
            $modx->resource = $homeResource;
        }
    }

    $myUser = $modx->getObject($prefix . 'modUser');
    if ($myUser) {
        $modx->user = $myUser;
    }
}

@include MODX_CORE_PATH . 'components/orphans/model/orphans/orphans.class.php';

$types = array(
    'modChunk',
    'modTemplate',
    'modTemplateVar',
    'modSnippet',
);

function output($msg, $suppressCr = false) {
    if (php_sapi_name() === 'cli') {
        $output = "\n" . $msg;
    } else {
        $output = "<br />" . $msg;
    }
    echo $output;
}

$logLevel = $modx->setLogLevel(MODX::LOG_LEVEL_ERROR);
$orphans = new Orphans($modx);
if ($orphans) {
    $orphans->initialize();
} else {
    die('No Orphans class');
}

if (php_sapi_name() !== 'cli') {
    output('<pre>');
}
foreach ($types as $type) {
    output('--------------------------------');
    output(strtoupper(substr($type, 3) . 'S'));
    $results = $orphans->process($type);

    if (empty($results)) {
        $output('    No orphans found');
    } else {
        foreach ($results as $result) {
            output('   ' . $result['name'] . ' (' . $result['id'] . ')');
            output('      Category: ' . $result['category']);
            output('      Description: ' . $result['description']);
            output('');
        }
    }
}

output("Finished!");
$modx->setLogLevel($logLevel);

exit;
