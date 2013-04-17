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
 * Loads the home page.
 *
 * @package orphans
 * @subpackage controllers
 */

if (empty($_REQUEST['template'])) return $modx->error->failure($modx->lexicon('orphans.template_err_ns'));
$template = $modx->getObject('modTemplate',$_REQUEST['template']);
if (empty($template)) return $modx->error->failure($modx->lexicon('orphans.template_err_nf'));

/* get all resources for template */
$c = $modx->newQuery('modResource');
$c->where(array(
    'template' => $template->get('id'),
));
$c->sortby('pagetitle','ASC');
$resources = $template->getMany('Resources',$c);
$ro = '<p>'.$modx->lexicon('orphans.resources_affect').'</p><ol>';
$i = 1;
foreach ($resources as $resource) {
    if ($i > 50) {
        $ro .= '<li>- '.$modx->lexicon('orphans.and_others',array('count' => (count($resources) - $i - 1))).'</li>';
        break;
    }
    $ro .= '<li>- '.$resource->get('pagetitle').' ('.$resource->get('id').')</li>';
    $i++;
}
$ro .= '</ol>';

$tj = $template->get(array('id','templatename','description'));
$tj = $modx->toJSON($tj);
$modx->regClientStartupHTMLBlock('<script type="text/javascript">Ext.onReady(function() {
    Orphans.template = '.$tj.';
    Orphans.resources = "'.$ro.'";
});</script>');

$managerUrl = $modx->getOption('manager_url');
$modx->regClientStartupScript($managerUrl.'assets/modext/util/datetime.js');
$modx->regClientStartupScript($managerUrl.'assets/modext/widgets/element/modx.panel.tv.renders.js');
$modx->regClientStartupScript($orphans->config['jsUrl'].'widgets/template/template.tvs.panel.js');
$modx->regClientStartupScript($orphans->config['jsUrl'].'sections/template/tvs.js');
$output = '<div id="orphans-panel-template-tvs-div"></div>';

return $output;
