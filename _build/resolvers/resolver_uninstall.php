<?php

/**
 * Resolver: uninstall cleanup.
 *
 * Never require_once component include/*.php as a hard dependency: on uninstall
 * MODX often runs file resolvers first and may already have deleted
 * core/components/imageoptimizer. A missing require_once becomes a PHP
 * warning/500 and freezes the mgr "OK" dialog.
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

/**
 * Read cleanup flag from DB only (no component helpers).
 */
$cleanup = false;
try {
    $setting = $modx->getObject('modSystemSetting', ['key' => 'imageoptimizer_cleanup_on_uninstall']);
    if ($setting) {
        $raw = $setting->get('value');
        $cleanup = ($raw === true || $raw === 1 || $raw === '1' || $raw === 'true');
    } else {
        $raw = $modx->getOption('imageoptimizer_cleanup_on_uninstall', null, false);
        $cleanup = ($raw === true || $raw === 1 || $raw === '1' || $raw === 'true');
    }
} catch (Throwable $e) {
    $modx->log(modX::LOG_LEVEL_WARN, '[imageoptimizer] uninstall: could not read cleanup setting: ' . $e->getMessage());
}

if ($cleanup) {
    // Variant files next to originals need component helpers. Attempt only if
    // files still exist; never fatal. Must run BEFORE dropping the queue table.
    $corePath = rtrim(MODX_CORE_PATH, '/\\') . '/components/imageoptimizer/';
    $helpersPath = $corePath . 'include/helpers.php';
    if (is_file($helpersPath)) {
        try {
            require_once $helpersPath;
            if (function_exists('imageoptimizer_add_package')
                && function_exists('imageoptimizer_queue_distinct_paths')
                && function_exists('imageoptimizer_delete_variants')
            ) {
                imageoptimizer_add_package($modx);
                foreach (imageoptimizer_queue_distinct_paths($modx) as $entry) {
                    imageoptimizer_delete_variants($modx, (int) $entry['source'], (string) $entry['path']);
                }
                $modx->log(modX::LOG_LEVEL_INFO, '[imageoptimizer] uninstall: deleted variant files');
            }
        } catch (Throwable $e) {
            $modx->log(
                modX::LOG_LEVEL_WARN,
                '[imageoptimizer] uninstall: variant cleanup skipped: ' . $e->getMessage()
            );
        }
    } else {
        $modx->log(
            modX::LOG_LEVEL_INFO,
            '[imageoptimizer] uninstall: variant files left on disk (component already removed)'
        );
    }

    // Drop queue table without loading the component model/package.
    $prefix = (string) $modx->getOption('table_prefix', null, '');
    $table = '`' . str_replace('`', '``', $prefix . 'imageoptimizer_queue') . '`';
    try {
        $modx->exec('DROP TABLE IF EXISTS ' . $table);
        $modx->log(modX::LOG_LEVEL_INFO, '[imageoptimizer] uninstall: dropped ' . $table);
    } catch (Throwable $e) {
        $modx->log(modX::LOG_LEVEL_WARN, '[imageoptimizer] uninstall: drop table failed: ' . $e->getMessage());
    }

    // Remove HTML/temp cache directory (no helpers).
    $cacheRoot = rtrim(MODX_CORE_PATH, '/\\') . '/cache/imageoptimizer';
    if (is_dir($cacheRoot)) {
        try {
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
            $modx->log(modX::LOG_LEVEL_INFO, '[imageoptimizer] uninstall: cleared cache/imageoptimizer');
        } catch (Throwable $e) {
            $modx->log(modX::LOG_LEVEL_WARN, '[imageoptimizer] uninstall: cache cleanup failed: ' . $e->getMessage());
        }
    }
}

// Always remove system settings for this namespace.
try {
    $settings = $modx->getCollection('modSystemSetting', ['key:LIKE' => 'imageoptimizer_%']);
    foreach ($settings as $setting) {
        $setting->remove();
    }
} catch (Throwable $e) {
    $modx->log(modX::LOG_LEVEL_WARN, '[imageoptimizer] uninstall: settings removal failed: ' . $e->getMessage());
}

return true;
