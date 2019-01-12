
<?php
require dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/' . 'config.core.php';

include MODX_CORE_PATH . 'model/modx/modx.class.php';

/* @var $modx modX */
$modx = new modX();

$modx->initialize('web');

$modx->getService('error', 'error.modError', '', '');
$modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE
    ? 'ECHO'
    : 'HTML');

require dirname(dirname(dirname(__FILE__))) . '/core/components/orphans/model/orphans/orphans.class.php';

$orphans = new Orphans($modx);

$output = $orphans->process('modTemplateVar');

echo print_r($output, true);