<?php

/**
 * Resolver: preserve settings on upgrade.
 *
 * @package imageoptimizer
 */

use xPDO\Transport\xPDOTransport;

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

$settingsTable = $modx->getTableName('modSystemSetting');
$modx->exec(
    'UPDATE ' . $settingsTable
    . ' SET namespace=' . $modx->quote('imageoptimizer')
    . ', area=' . $modx->quote('imageoptimizer')
    . ' WHERE `key` LIKE ' . $modx->quote('imageoptimizer_%')
);

return true;
