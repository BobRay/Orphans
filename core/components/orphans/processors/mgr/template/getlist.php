<?php
/**
 * Orphans
 *
 * Copyright 2013-2017 Bob Ray <https://bobsguides.com>
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
/**
 * Get a list of templates
 *
 * @package orphans
 * @subpackage processors
 */

/* @var $modx modX */
/* @var $this modProcessor */
/* @var $orphans Orphans */

$orphans = $modx->orphans;
$results = $orphans->process('modTemplate');
$count = count($results);

return $this->outputArray($results, $count);

/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$isCombo = !empty($scriptProperties['combo']);
$start = $modx->getOption('start',$scriptProperties,0);
$limit = $modx->getOption('limit',$scriptProperties,10);
$sort = $modx->getOption('sort',$scriptProperties,'templatename');
$dir = $modx->getOption('dir',$scriptProperties,'ASC');

$c = $modx->newQuery('modTemplate');
$c->leftJoin('modCategory','Category');
if (!empty($scriptProperties['search'])) {
    $c->where(array(
        'templatename:LIKE' => '%'.$scriptProperties['search'].'%',
        'OR:description:LIKE' => '%'.$scriptProperties['search'].'%',
    ));
}
$count = $modx->getCount('modTemplate',$c);
$c->select(array(
    'modTemplate.id',
    'modTemplate.templatename',
    'modTemplate.description',
));
$c->select(array(
    'category_name' => 'Category.category',
));
if ($sort == 'category') {
    $sort = 'Category.category';
}
$c->sortby($sort,$dir);
if ($isLimit) {
    $c->limit($limit,$start);
}
$templates = $modx->getCollection('modTemplate',$c);
//echo $c->toSql();

$list = array();
$fields = array(
    'id',
    'templatename',
    'description',
);
foreach ($templates as $template) {
    // $templateArray = $template->toArray();
    foreach ($fields as $field) {
        $templateArray[$field] = $template->get($field);
    }
    $templateArray['category'] = $template->get('category_name');
    $list[]= $templateArray;
}
return $this->outputArray($list,$count);