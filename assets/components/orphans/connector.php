<?php
/**
 * Orphans Connector
 *
 * @package orphans
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$orphansCorePath = $modx->getOption('orphans.core_path',null,$modx->getOption('core_path').'components/orphans/');
require_once $orphansCorePath.'model/orphans/orphans.class.php';
$modx->orphans = new Orphans($modx);

$modx->getService('lexicon', 'modLexicon');
$modx->lexicon->load('orphans:default');

/* handle request */
$path = $modx->getOption('processorsPath',$modx->orphans->config,$orphansCorePath.'processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));