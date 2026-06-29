<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_handle_queue_list(modX $modx): void
{
    $offset = max(0, imageoptimizer_int_post('offset'));
    $limit = max(1, min(500, imageoptimizer_int_post('limit', 50)));
    $status = trim((string) imageoptimizer_post('status', ''));
    $source = imageoptimizer_int_post('source');
    $query = trim((string) imageoptimizer_post('query', ''));

    $criteria = [];
    if ($status !== '') {
        $queueStatus = QueueStatus::tryFrom($status);
        if ($queueStatus === null) {
            imageoptimizer_json_error('invalid_status', 400, $modx);
        }
        $criteria['status'] = $queueStatus->value;
    }
    if ($source > 0) {
        $criteria['source'] = $source;
    }
    if ($query !== '') {
        $criteria['path:LIKE'] = '%' . $query . '%';
    }

    $total = $modx->getCount('ioQueue', $criteria);
    $queryObj = $modx->newQuery('ioQueue');
    if ($criteria !== []) {
        $queryObj->where($criteria);
    }
    $queryObj->sortby('id', 'DESC');
    $queryObj->limit($limit, $offset);
    $collection = $modx->getCollection('ioQueue', $queryObj);

    $rows = [];
    foreach ($collection as $item) {
        if ($item instanceof ioQueue) {
            $rows[] = $item->toArray();
        }
    }

    imageoptimizer_json_success($rows, $total);
}

function imageoptimizer_handle_queue_retry(modX $modx): void
{
    $ids = imageoptimizer_parse_id_list(imageoptimizer_post('ids', []));
    if ($ids === []) {
        imageoptimizer_json_error('ids_required', 400, $modx);
    }

    $updated = 0;
    foreach ($ids as $id) {
        $item = $modx->getObject('ioQueue', $id);
        if (!$item) {
            continue;
        }
        if ($item->get('status') === QueueStatus::Failed->value || $item->get('status') === QueueStatus::Skipped->value) {
            $item->set('status', QueueStatus::Pending->value);
            $item->set('error', null);
            $item->set('skip_reason', null);
            $item->set('locked_at', null);
            if ($item->save()) {
                $updated++;
            }
        }
    }

    imageoptimizer_json_success(['updated' => $updated]);
}

function imageoptimizer_handle_queue_rebuild(modX $modx): void
{
    $sourceId = imageoptimizer_int_post('source');
    if ($sourceId <= 0) {
        $sourceId = (int) $modx->getOption('default_media_source', null, 1);
    }
    $path = trim((string) imageoptimizer_post('path', ''));
    $dryRun = (bool) imageoptimizer_post('dry_run', false);
    imageoptimizer_require_media_source($modx, $sourceId, 'read');

    if ($path !== '') {
        $path = imageoptimizer_normalize_relative_path($path);
        if ($path === '') {
            imageoptimizer_json_error('invalid_path', 400, $modx);
        }
        $enqueued = imageoptimizer_rebuild_path($modx, $sourceId, $path, $dryRun);
    } else {
        $enqueued = $dryRun ? imageoptimizer_scan_source($modx, $sourceId, null, false) : imageoptimizer_scan_source($modx, $sourceId);
    }

    imageoptimizer_json_success(['enqueued' => $enqueued, 'dry_run' => $dryRun]);
}

function imageoptimizer_handle_queue_clear(modX $modx): void
{
    $sourceId = imageoptimizer_int_post('source');
    $path = trim((string) imageoptimizer_post('path', ''));
    $dryRun = (bool) imageoptimizer_post('dry_run', false);
    $removed = 0;

    if ($path !== '' && $sourceId > 0) {
        imageoptimizer_require_media_source($modx, $sourceId, 'remove');
        $path = imageoptimizer_normalize_relative_path($path);
        if ($path === '') {
            imageoptimizer_json_error('invalid_path', 400, $modx);
        }
        $removed = $dryRun
            ? imageoptimizer_count_variants_for_path($modx, $sourceId, $path)
            : imageoptimizer_delete_variants($modx, $sourceId, $path);
    } else {
        if ($sourceId > 0) {
            imageoptimizer_require_media_source($modx, $sourceId, 'remove');
        }
        foreach (imageoptimizer_queue_distinct_paths($modx, $sourceId > 0 ? $sourceId : 0) as $entry) {
            $src = $entry['source'];
            $itemPath = $entry['path'];
            $source = imageoptimizer_get_media_source($modx, $src);
            if (!$source || !imageoptimizer_user_can_access_media_source($modx, $source, 'remove')) {
                continue;
            }
            if ($dryRun) {
                $removed += imageoptimizer_count_variants_for_path($modx, $src, $itemPath);
            } else {
                $removed += imageoptimizer_delete_variants($modx, $src, $itemPath);
            }
        }
    }

    if (!$dryRun) {
        imageoptimizer_clear_html_cache($modx);
    }

    imageoptimizer_json_success(['removed' => $removed, 'dry_run' => $dryRun]);
}

function imageoptimizer_handle_queue_reset_stuck(modX $modx): void
{
    $minutes = max(1, (int) imageoptimizer_get_setting($modx, 'stuck_minutes', 30));
    $reset = imageoptimizer_queue_reset_stuck($modx, $minutes);
    imageoptimizer_json_success(['reset' => $reset]);
}

function imageoptimizer_handle_queue_process(modX $modx): void
{
    if (!imageoptimizer_acquire_lock($modx, 'cron')) {
        imageoptimizer_json_error('worker_busy', 409, $modx);
    }

    try {
        $stuckMinutes = max(1, (int) imageoptimizer_get_setting($modx, 'stuck_minutes', 30));
        $reset = imageoptimizer_queue_reset_stuck($modx, $stuckMinutes);

        $defaultLimit = max(1, (int) imageoptimizer_get_setting($modx, 'cron_limit', 50));
        $limit = max(1, min(500, imageoptimizer_int_post('limit', $defaultLimit)));

        $startedAt = microtime(true);
        $timeBudget = imageoptimizer_default_time_budget();
        $processed = 0;
        $timeBudgetExceeded = false;

        try {
            $processed = imageoptimizer_process_queue($modx, $limit, $startedAt, $timeBudget);
        } catch (ImageOptimizerTimeBudgetException) {
            $timeBudgetExceeded = true;
        }

        imageoptimizer_json_success([
            'processed' => $processed,
            'reset' => $reset,
            'queue' => imageoptimizer_queue_count_by_status($modx),
            'limit' => $limit,
            'time_budget_exceeded' => $timeBudgetExceeded,
        ]);
    } finally {
        imageoptimizer_release_lock();
    }
}

function imageoptimizer_count_variants_for_path(modX $modx, int $sourceId, string $path): int
{
    $source = imageoptimizer_get_media_source($modx, $sourceId);
    if (!$source) {
        return 0;
    }
    $pattern = (string) imageoptimizer_get_setting($modx, 'variant_pattern', '{basename}.{width}.{ext}');
    $formats = imageoptimizer_get_active_formats($modx);
    $breakpoints = imageoptimizer_parse_breakpoints($modx);
    $count = 0;
    foreach ($formats as $format) {
        $variantRel = imageoptimizer_build_variant_path($path, 0, $format, $pattern);
        $absolute = imageoptimizer_resolve_path_within_source($source, $variantRel);
        if ($absolute !== null && is_file($absolute)) {
            $count++;
        }
        foreach ($breakpoints as $bp) {
            $variantRel = imageoptimizer_build_variant_path($path, $bp, $format, $pattern);
            $absolute = imageoptimizer_resolve_path_within_source($source, $variantRel);
            if ($absolute !== null && is_file($absolute)) {
                $count++;
            }
        }
    }

    return $count;
}
