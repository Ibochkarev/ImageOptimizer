<?php

defined('MODX_CORE_PATH') || exit;

final class ImageOptimizerMemoryLimitException extends RuntimeException
{
}

final class ImageOptimizerTimeBudgetException extends RuntimeException
{
}

function imageoptimizer_parse_memory_limit(string $limit): int
{
    $limit = trim($limit);
    if ($limit === '' || $limit === '-1') {
        return PHP_INT_MAX;
    }
    $unit = strtolower(substr($limit, -1));
    $value = (int) $limit;
    if ($unit === 'g') {
        return $value * 1024 * 1024 * 1024;
    }
    if ($unit === 'm') {
        return $value * 1024 * 1024;
    }
    if ($unit === 'k') {
        return $value * 1024;
    }

    return (int) $limit;
}

function imageoptimizer_bump_memory_limit(modX $modx, int $neededBytes): void
{
    $cap = imageoptimizer_parse_memory_limit((string) imageoptimizer_get_setting($modx, 'max_memory_limit', '512M'));
    if ($neededBytes > $cap) {
        throw new ImageOptimizerMemoryLimitException('Required memory exceeds imageoptimizer.max_memory_limit');
    }
    $current = imageoptimizer_parse_memory_limit((string) ini_get('memory_limit'));
    $target = min($cap, max($current, $neededBytes));
    if ($target > $current) {
        ini_set('memory_limit', (string) $target);
    }
}

function imageoptimizer_check_time_budget(float $startedAt, int $budgetSeconds): void
{
    if ($budgetSeconds <= 0) {
        return;
    }
    if ((microtime(true) - $startedAt) >= $budgetSeconds) {
        throw new ImageOptimizerTimeBudgetException('Time budget exceeded');
    }
}

function imageoptimizer_default_time_budget(): int
{
    $max = (int) ini_get('max_execution_time');
    if ($max <= 0) {
        return 300;
    }

    return max(30, (int) floor($max * 0.8));
}
