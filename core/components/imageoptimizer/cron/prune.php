<?php

/**
 * Cron: prune done queue rows older than retention_days.
 *
 * php core/components/imageoptimizer/cron/prune.php
 */

$root = dirname(__DIR__, 4);
if (!is_file($root . '/config.core.php')) {
    $root = dirname(__DIR__, 5);
}
require_once $root . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
if (!$modx->initialize('mgr')) {
    fwrite(STDERR, "MODX init failed\n");
    exit(1);
}

require_once MODX_CORE_PATH . 'components/imageoptimizer/include/helpers.php';

$removed = imageoptimizer_prune_queue($modx);
echo 'Pruned done rows: ' . $removed . PHP_EOL;
