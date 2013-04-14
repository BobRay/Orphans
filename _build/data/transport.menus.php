<?php
/**
 * menus transport file for Orphans extra
 *
 * Copyright 2013 by Bob Ray <http://bobsguides.com>
 * Created on 04-13-2013
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

$action = $modx->newObject('modAction');
$action->fromArray( array (
  'namespace' => 'orphans',
  'controller' => 'index',
  'haslayout' => 1,
  'lang_topics' => '',
  'assets' => '',
  'help_url' => '',
  'id' => 1,
), '', true, true);

$menus[1] = $modx->newObject('modMenu');
$menus[1]->fromArray( array (
  'text' => 'orphans~~Orphans',
  'parent' => 'components',
  'description' => 'orphans.menu_desc~~Find unused elements',
  'icon' => '',
  'menuindex' => 0,
  'params' => '',
  'handler' => '',
  'permissions' => '',
  'id' => 1,
), '', true, true);
$menus[1]->addOne($action);

return $menus;
