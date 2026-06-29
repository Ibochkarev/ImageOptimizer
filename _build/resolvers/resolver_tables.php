<?php

/**
 * Resolver: создание таблицы imageoptimizer_queue при установке/обновлении.
 *
 * @package imageoptimizer
 */

use xPDO\Transport\xPDOTransport;
use xPDO\xPDO;

/** @var xPDOTransport $transport */
/** @var array $options */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return true;
}

$modx = $transport->xpdo;

if ($options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_INSTALL
    && $options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_UPGRADE
) {
    return true;
}

$modelPath = $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/imageoptimizer/model/';
$modx->addPackage('imageoptimizer', $modelPath);
$manager = $modx->getManager();

if (!$manager->createObjectContainer('ioQueue')) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[imageoptimizer] Failed to create table imageoptimizer_queue');
    return false;
}

$level = $modx->getLogLevel();
$modx->setLogLevel(xPDO::LOG_LEVEL_FATAL);

foreach (['uniq_variant', 'status', 'source', 'path', 'locked_at'] as $index) {
    $manager->addIndex('ioQueue', $index);
}

$modx->setLogLevel($level);

$modx->log(modX::LOG_LEVEL_INFO, '[imageoptimizer] Table imageoptimizer_queue ready.');

return true;
