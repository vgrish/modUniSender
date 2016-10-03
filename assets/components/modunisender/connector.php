<?php
/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var modunisender $modunisender */
$modunisender = $modx->getService('modunisender', 'modunisender', $modx->getOption('modunisender_core_path', null,
        $modx->getOption('core_path') . 'components/modunisender/') . 'model/modunisender/');
$modx->lexicon->load('modunisender:default');

// handle request
$corePath = $modx->getOption('modunisender_core_path', null,
    $modx->getOption('core_path') . 'components/modunisender/');
$path = $modx->getOption('processorsPath', $modunisender->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location'        => '',
));