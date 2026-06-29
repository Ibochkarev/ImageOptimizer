<?php

/**
 * Resolver: namespace и кэш-директории ImageOptimizer.
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

$corePath = '{core_path}components/imageoptimizer/';
$assetsPath = '{assets_path}components/imageoptimizer/';
$nsTable = $modx->getTableName('modNamespace');

$ns = $modx->getObject('modNamespace', ['name' => 'imageoptimizer']);
if ($ns) {
    $ns->set('path', $corePath);
    $ns->set('assets_path', $assetsPath);
    $ns->save();
} else {
    $ns = $modx->newObject('modNamespace');
    $ns->fromArray([
        'name' => 'imageoptimizer',
        'path' => $corePath,
        'assets_path' => $assetsPath,
    ], '', true, true);
    if (!$ns->save()) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[imageoptimizer] Could not create namespace.');
    }
}

require_once MODX_CORE_PATH . 'components/imageoptimizer/include/paths.php';

$cacheDirs = [
    imageoptimizer_cache_path($modx),
    imageoptimizer_cache_path($modx) . 'html/',
];
foreach ($cacheDirs as $dir) {
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
            $modx->log(modX::LOG_LEVEL_WARN, '[imageoptimizer] Could not create cache dir: ' . $dir);
        }
    }
}

$menu = $modx->newObject('modMenu');
if ($menu) {
    foreach (['', 'topnav', 'usernav'] as $menuRoot) {
        $menu->rebuildCache($menuRoot);
    }
}

return true;
