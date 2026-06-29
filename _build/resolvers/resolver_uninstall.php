<?php

/**
 * Resolver: uninstall cleanup when cleanup_on_uninstall is enabled.
 *
 * @package imageoptimizer
 */

use xPDO\Transport\xPDOTransport;

/** @var xPDOTransport $transport */
/** @var array $options */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return true;
}

if ($options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_UNINSTALL) {
    return true;
}

$modx = $transport->xpdo;
require_once MODX_CORE_PATH . 'components/imageoptimizer/include/paths.php';
require_once imageoptimizer_core_path($modx) . 'include/helpers.php';

$cleanup = (bool) imageoptimizer_get_setting($modx, 'cleanup_on_uninstall', false);
if ($cleanup) {
    imageoptimizer_add_package($modx);
    foreach (imageoptimizer_queue_distinct_paths($modx) as $entry) {
        imageoptimizer_delete_variants($modx, $entry['source'], $entry['path']);
    }
    $table = $modx->getTableName('ioQueue');
    $modx->exec('DROP TABLE IF EXISTS ' . $table);
    imageoptimizer_clear_html_cache($modx);
    $cacheRoot = imageoptimizer_cache_path($modx);
    if (is_dir($cacheRoot)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cacheRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                @rmdir($fileInfo->getPathname());
            } else {
                @unlink($fileInfo->getPathname());
            }
        }
        @rmdir($cacheRoot);
    }
}

$settings = $modx->getCollection('modSystemSetting', ['key:LIKE' => 'imageoptimizer_%']);
foreach ($settings as $setting) {
    $setting->remove();
}

return true;
