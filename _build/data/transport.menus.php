<?php
/**
 * menus transport file for Orphans extra
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
/* @var xPDOObject[] $menus */


$menus[1] = $modx->newObject('modMenu');
$menus[1]->fromArray( array (
  'text' => 'orphans',
  'parent' => 'components',
  'action' => 'index',
  'description' => 'orphans.menu_desc',
  'icon' => '',
  'menuindex' => 1,
  'params' => '',
  'handler' => '',
  'permissions' => '',
  'namespace' => 'orphans',
  'id' => 1,
), '', true, true);

return $menus;
