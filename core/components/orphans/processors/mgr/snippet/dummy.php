<?php
/**
 * Orphans
 *
 * Copyright 2010 by Shaun McCormick <shaun@modxcms.com>
 *
 * This file is part of Orphans, a batch resource editing Extra.
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

$fields = array(
    'id' => '',
    'name' => '',
    'catagory' => '',
    'description' => '',
);
return $this->outputArray($fields, 1);
/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$isCombo = !empty($scriptProperties['combo']);
$start = $modx->getOption('start', $scriptProperties, 0);
$limit = $modx->getOption('limit', $scriptProperties, 10);
$sort = $modx->getOption('sort', $scriptProperties, 'name');
$dir = $modx->getOption('dir', $scriptProperties, 'ASC');

$c = $modx->newQuery('modSnippet');
$c->leftJoin('modCategory', 'Category');
if (!empty($scriptProperties['search'])) {
    $c->where(array(
                   'name:LIKE' => '%' . $scriptProperties['search'] . '%',
                   'OR:description:LIKE' => '%' . $scriptProperties['search'] . '%',
              ));
}
$count = $modx->getCount('modSnippet', $c);
$c->select(array(
    'modSnippet.id',
    'modSnippet.name',
    'modSnippet.description',
           ));
$c->select(array(
                'category_name' => 'Category.category',
           ));
if ($sort == 'category') {
    $sort = 'Category.category';
}

$c->sortby($sort, $dir);
if ($isLimit) {
    $c->limit($limit, $start);
}
$snippets = $modx->getCollection('modSnippet', $c);
//echo $c->toSql();

$list = array();
$fields = array(
    'id',
    'name',
    'description'
);
foreach ($snippets as $snippet) {
    /* @var $snippet modSnippet */
    $snippetArray = $snippet->toArray('',true, true);
    /*foreach($fields as $field)  {
        $snippetArray[$field] = $snippet->get($field);
    }*/
    $snippetArray['category'] = $snippet->get('category_name');
    $list[] = $snippetArray;
}
/* @var $this modProcessor */
return $this->outputArray($list, $count);