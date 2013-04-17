<?php
/**
 * chunks transport file for Orphans extra
 *
 * Copyright 2013 by Bob Ray <http://bobsguides.com>
 * Created on 04-14-2013
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
/* @var xPDOObject[] $chunks */


$chunks = array();

$chunks[1] = $modx->newObject('modChunk');
$chunks[1]->fromArray(array(
    'id' => '1',
    'name' => 'OrphansTplChunk',
), '', true, true);
$chunks[1]->setContent(file_get_contents($sources['source_core'] . '/elements/chunks/orphanstplchunk.chunk.html'));

return $chunks;
