<?php

/**
 * Cron: process pending queue items with flock lock.
 *
 * php core/components/imageoptimizer/cron/convert.php
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

if (!imageoptimizer_acquire_lock($modx, 'cron')) {
    fwrite(STDERR, "Another convert cron is already running\n");
    exit(2);
}

$stuckMinutes = max(1, (int) imageoptimizer_get_setting($modx, 'stuck_minutes', 30));
$reset = imageoptimizer_queue_reset_stuck($modx, $stuckMinutes);
$limit = max(1, (int) imageoptimizer_get_setting($modx, 'cron_limit', 50));
$startedAt = microtime(true);
$timeBudget = imageoptimizer_default_time_budget();

try {
    $processed = imageoptimizer_process_queue($modx, $limit, $startedAt, $timeBudget);
} catch (ImageOptimizerTimeBudgetException) {
    $processed = 0;
}

imageoptimizer_release_lock();

echo 'Reset stuck: ' . $reset . ', processed: ' . $processed . PHP_EOL;
