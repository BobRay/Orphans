<?php
/**
 * systemSettings transport file for Orphans extra
 *
 * Copyright 2013-2024 Bob Ray <https://bobsguides.com>
 * Created on 05-02-2013
 *
 * @package orphans
 * @subpackage build
 */

if (! function_exists('stripPhpTags')) {
    function stripPhpTags($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<' . '?' . 'php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }
}
/* @var $modx modX */
/* @var $sources array */
/* @var xPDOObject[] $systemSettings */


$systemSettings = array();

$systemSettings[1] = $modx->newObject('modSystemSetting');
$systemSettings[1]->fromArray(array (
  'key' => 'orphans.prefix',
  'value' => 'aaOrphan.',
  'xtype' => 'textfield',
  'namespace' => 'orphans',
  'area' => 'orphans',
  'name' => 'Orphans Prefix',
  'description' => 'Prefix to use when renaming orphans',
), '', true, true);
return $systemSettings;
