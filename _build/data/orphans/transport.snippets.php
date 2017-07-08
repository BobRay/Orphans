<?php
/**
 * snippets transport file for Orphans extra
 *
 * Copyright 2013-2017 Bob Ray <https://bobsguides.com>
 * Created on 02-07-2015
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
/* @var xPDOObject[] $snippets */


$snippets = array();

$snippets[1] = $modx->newObject('modSnippet');
$snippets[1]->fromArray(array (
  'id' => 1,
  'description' => 'Standalone Version of Orphans',
  'name' => 'Orphans',
), '', true, true);
$snippets[1]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/orphans.snippet.php'));

return $snippets;
