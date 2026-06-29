<?php

/**
 * CLI: bulk convert / process queue.
 *
 * php core/components/imageoptimizer/cli/convert.php --source=1 --scan --limit=100
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

$options = imageoptimizer_parse_cli_args($argv);
$json = (bool) $options['json'];
$startedAt = microtime(true);
$timeBudget = $options['time_budget'] ?? imageoptimizer_default_time_budget();

if ($options['breakpoints'] !== null) {
    $modx->setOption('imageoptimizer_breakpoints', $options['breakpoints']);
}
if ($options['format'] !== null) {
    if ($options['format'] === 'avif') {
        $modx->setOption('imageoptimizer_avif_enabled', true);
        $modx->setOption('imageoptimizer_formats', 'avif');
    } else {
        $modx->setOption('imageoptimizer_formats', 'webp');
    }
}

$sourceId = $options['source'];
if ($sourceId === null) {
    $sourceId = (int) $modx->getOption('default_media_source', null, 1);
}

$enqueued = 0;
if ($options['scan']) {
    $enqueued = imageoptimizer_scan_source($modx, $sourceId, $options['path']);
    imageoptimizer_cli_print_progress('Scanned and enqueued: ' . $enqueued, $json);
} elseif ($options['path'] !== null) {
    $enqueued = imageoptimizer_rebuild_path($modx, $sourceId, (string) $options['path'], false);
}

$processed = 0;
$failed = 0;
if (!$options['dry_run']) {
    try {
        $processed = imageoptimizer_process_queue($modx, (int) $options['limit'], $startedAt, $timeBudget);
    } catch (ImageOptimizerTimeBudgetException) {
        imageoptimizer_cli_print_progress('Time budget reached', $json);
    }
} else {
    $counts = imageoptimizer_queue_count_by_status($modx);
    imageoptimizer_cli_output([
        'dry_run' => true,
        'pending' => $counts[QueueStatus::Pending->value] ?? 0,
        'enqueued' => $enqueued,
    ], $json);
    exit(0);
}

$counts = imageoptimizer_queue_count_by_status($modx);
imageoptimizer_cli_output([
    'processed' => $processed,
    'enqueued' => $enqueued,
    'pending' => $counts[QueueStatus::Pending->value] ?? 0,
    'done' => $counts[QueueStatus::Done->value] ?? 0,
    'failed' => $counts[QueueStatus::Failed->value] ?? 0,
    'skipped' => $counts[QueueStatus::Skipped->value] ?? 0,
], $json);
