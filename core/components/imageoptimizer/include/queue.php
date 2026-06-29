<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_queue_enqueue(
    modX $modx,
    int $source,
    string $path,
    string $format,
    int $width,
    ?int $originalSize = null
): ?ioQueue {
    $path = imageoptimizer_normalize_relative_path($path);
    if ($path === '') {
        return null;
    }

    $existing = $modx->getObject('ioQueue', [
        'source' => $source,
        'path' => $path,
        'format' => $format,
        'width' => $width,
    ]);
    if ($existing instanceof ioQueue) {
        if ($existing->get('status') === QueueStatus::Done->value && !imageoptimizer_get_setting($modx, 'reencode_if_unchanged', false)) {
            return $existing;
        }
        $existing->set('status', QueueStatus::Pending->value);
        $existing->set('error', null);
        $existing->set('skip_reason', null);
        $existing->set('processed_at', null);
        $existing->set('locked_at', null);
        if ($width === 0 && $originalSize !== null) {
            $existing->set('original_size', $originalSize);
        }
        $existing->save();

        return $existing;
    }

    $item = $modx->newObject('ioQueue');
    if (!$item instanceof ioQueue) {
        return null;
    }
    $item->fromArray([
        'source' => $source,
        'path' => $path,
        'format' => $format,
        'width' => $width,
        'status' => QueueStatus::Pending->value,
        'original_size' => $width === 0 ? $originalSize : null,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
    if (!$item->save()) {
        return null;
    }

    return $item;
}

/**
 * @return ioQueue[]
 */
function imageoptimizer_queue_claim(modX $modx, int $limit): array
{
    $limit = max(1, min(5000, $limit));
    $fullTable = $modx->getTableName('ioQueue');

    $modx->beginTransaction();
    $stmt = $modx->prepare(
        "SELECT id FROM {$fullTable} WHERE status = 'pending' ORDER BY id ASC LIMIT {$limit} FOR UPDATE"
    );
    if (!$stmt || !$stmt->execute()) {
        $modx->commit();
        return [];
    }

    $ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = (int) $row['id'];
    }
    if ($ids === []) {
        $modx->commit();
        return [];
    }

    $idList = implode(',', $ids);
    $now = date('Y-m-d H:i:s');
    $modx->exec(
        "UPDATE {$fullTable} SET status='processing', locked_at='{$now}', processed_at='{$now}' WHERE id IN ({$idList})"
    );
    $modx->commit();

    $claimed = [];
    foreach ($modx->getCollection('ioQueue', ['id:IN' => $ids]) as $item) {
        if ($item instanceof ioQueue) {
            $claimed[] = $item;
        }
    }

    return $claimed;
}

/**
 * @return ioQueue[]
 */
function imageoptimizer_queue_claim_for_path(modX $modx, int $sourceId, string $path, int $limit = 1): array
{
    $path = imageoptimizer_normalize_relative_path($path);
    if ($path === '' || $sourceId <= 0) {
        return [];
    }

    $limit = max(1, min(500, $limit));
    $fullTable = $modx->getTableName('ioQueue');
    $quotedPath = $modx->quote($path);

    $modx->beginTransaction();
    $stmt = $modx->prepare(
        "SELECT id FROM {$fullTable} WHERE status = 'pending' AND source = {$sourceId} AND path = {$quotedPath} ORDER BY id ASC LIMIT {$limit} FOR UPDATE"
    );
    if (!$stmt || !$stmt->execute()) {
        $modx->commit();

        return [];
    }

    $ids = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ids[] = (int) $row['id'];
    }
    if ($ids === []) {
        $modx->commit();

        return [];
    }

    $idList = implode(',', $ids);
    $now = date('Y-m-d H:i:s');
    $modx->exec(
        "UPDATE {$fullTable} SET status='processing', locked_at='{$now}', processed_at='{$now}' WHERE id IN ({$idList})"
    );
    $modx->commit();

    $claimed = [];
    foreach ($modx->getCollection('ioQueue', ['id:IN' => $ids]) as $item) {
        if ($item instanceof ioQueue) {
            $claimed[] = $item;
        }
    }

    return $claimed;
}

/**
 * @return list<array{source: int, path: string}>
 */
function imageoptimizer_queue_distinct_paths(modX $modx, int $sourceId = 0): array
{
    $fullTable = $modx->getTableName('ioQueue');
    $where = $sourceId > 0 ? ' WHERE source = ' . $sourceId : '';
    $stmt = $modx->query("SELECT DISTINCT source, path FROM {$fullTable}{$where}");
    if (!$stmt) {
        return [];
    }

    $paths = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $src = (int) ($row['source'] ?? 0);
        $path = imageoptimizer_normalize_relative_path((string) ($row['path'] ?? ''));
        if ($src > 0 && $path !== '') {
            $paths[] = ['source' => $src, 'path' => $path];
        }
    }

    return $paths;
}

function imageoptimizer_queue_mark_done(modX $modx, ioQueue $item, int $convertedSize): void
{
    $item->set('status', QueueStatus::Done->value);
    $item->set('converted_size', $convertedSize);
    $item->set('error', null);
    $item->set('skip_reason', null);
    $item->set('processed_at', date('Y-m-d H:i:s'));
    $item->set('locked_at', null);
    $item->save();
    imageoptimizer_bump_html_cache_generation($modx);
}

function imageoptimizer_queue_mark_failed(ioQueue $item, string $error): void
{
    $item->set('status', QueueStatus::Failed->value);
    $item->set('error', $error);
    $item->set('processed_at', date('Y-m-d H:i:s'));
    $item->set('locked_at', null);
    $item->save();
}

function imageoptimizer_queue_mark_skipped(ioQueue $item, SkipReason $reason): void
{
    $item->set('status', QueueStatus::Skipped->value);
    $item->set('skip_reason', $reason->value);
    $item->set('error', null);
    $item->set('processed_at', date('Y-m-d H:i:s'));
    $item->set('locked_at', null);
    $item->save();
}

function imageoptimizer_queue_reset_stuck(modX $modx, int $minutes): int
{
    $cutoff = date('Y-m-d H:i:s', time() - ($minutes * 60));
    $collection = $modx->getCollection('ioQueue', [
        'status' => QueueStatus::Processing->value,
        'locked_at:<=' => $cutoff,
    ]);
    $count = 0;
    foreach ($collection as $item) {
        $item->set('status', QueueStatus::Pending->value);
        $item->set('locked_at', null);
        if ($item->save()) {
            $count++;
        }
    }

    return $count;
}

/**
 * @return array<string, int>
 */
function imageoptimizer_queue_count_by_status(modX $modx): array
{
    $fullTable = $modx->getTableName('ioQueue');
    $stmt = $modx->query("SELECT status, COUNT(*) AS cnt FROM {$fullTable} GROUP BY status");
    $result = [];
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['status']] = (int) $row['cnt'];
        }
    }

    return $result;
}
