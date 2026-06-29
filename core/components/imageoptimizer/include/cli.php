<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array<string, string|bool|int|null>
 */
function imageoptimizer_parse_cli_args(array $argv): array
{
    $options = [
        'source' => null,
        'limit' => 100,
        'resume' => true,
        'dry_run' => false,
        'format' => null,
        'breakpoints' => null,
        'time_budget' => null,
        'json' => false,
        'path' => null,
        'scan' => false,
    ];

    foreach ($argv as $arg) {
        if ($arg === '--resume') {
            $options['resume'] = true;
        } elseif ($arg === '--dry-run') {
            $options['dry_run'] = true;
        } elseif ($arg === '--json') {
            $options['json'] = true;
        } elseif ($arg === '--scan') {
            $options['scan'] = true;
        } elseif (str_starts_with($arg, '--source=')) {
            $options['source'] = (int) substr($arg, 9);
        } elseif (str_starts_with($arg, '--limit=')) {
            $options['limit'] = max(1, (int) substr($arg, 8));
        } elseif (str_starts_with($arg, '--format=')) {
            $options['format'] = strtolower(substr($arg, 9));
        } elseif (str_starts_with($arg, '--breakpoints=')) {
            $options['breakpoints'] = substr($arg, 14);
        } elseif (str_starts_with($arg, '--time-budget=')) {
            $options['time_budget'] = max(0, (int) substr($arg, 14));
        } elseif (str_starts_with($arg, '--path=')) {
            $options['path'] = imageoptimizer_normalize_relative_path(substr($arg, 7));
        }
    }

    return $options;
}

function imageoptimizer_cli_print_progress(string $message, bool $json = false): void
{
    if ($json) {
        return;
    }
    echo $message . PHP_EOL;
}

function imageoptimizer_cli_output(array $data, bool $json): void
{
    if ($json) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        return;
    }
    foreach ($data as $key => $value) {
        echo $key . ': ' . (is_scalar($value) ? $value : json_encode($value)) . PHP_EOL;
    }
}

function imageoptimizer_acquire_lock(modX $modx, string $name): bool
{
    $lockFile = imageoptimizer_cache_path($modx) . $name . '.lock';
    $dir = dirname($lockFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $handle = @fopen($lockFile, 'c+');
    if (!$handle) {
        return false;
    }
    if (!flock($handle, LOCK_EX | LOCK_NB)) {
        fclose($handle);

        return false;
    }
    fwrite($handle, (string) getmypid());
    fflush($handle);

    $GLOBALS['imageoptimizer_lock_handle'] = $handle;

    return true;
}

function imageoptimizer_release_lock(): void
{
    if (!empty($GLOBALS['imageoptimizer_lock_handle'])) {
        flock($GLOBALS['imageoptimizer_lock_handle'], LOCK_UN);
        fclose($GLOBALS['imageoptimizer_lock_handle']);
        $GLOBALS['imageoptimizer_lock_handle'] = null;
    }
}
