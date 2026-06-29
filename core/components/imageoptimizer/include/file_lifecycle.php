<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_directory_relative_path(mixed $directory): string
{
    if (!is_object($directory)) {
        return '';
    }
    if (method_exists($directory, 'getPath')) {
        return imageoptimizer_normalize_relative_path((string) $directory->getPath());
    }
    if (method_exists($directory, 'get')) {
        $path = $directory->get('pathname') ?? $directory->get('path') ?? '';

        return imageoptimizer_normalize_relative_path((string) $path);
    }

    return '';
}

function imageoptimizer_event_source_id(modX $modx): ?int
{
    $source = $modx->event->params['source'] ?? null;
    if ($source instanceof modMediaSource) {
        return (int) $source->get('id');
    }
    if (is_numeric($source)) {
        return (int) $source;
    }

    return null;
}

function imageoptimizer_event_relative_path(modX $modx): ?string
{
    $params = $modx->event->params;
    if (isset($params['path']) && is_string($params['path'])) {
        return imageoptimizer_normalize_relative_path($params['path']);
    }

    $directory = imageoptimizer_directory_relative_path($params['directory'] ?? null);
    $files = $params['files'] ?? null;
    $names = imageoptimizer_extract_upload_names($files);
    if ($names === []) {
        $file = $params['file'] ?? null;
        if (is_array($file) && isset($file['name'])) {
            $names = [(string) $file['name']];
        }
    }
    if ($names === []) {
        return null;
    }

    $name = $names[0];
    if ($directory !== '') {
        return imageoptimizer_normalize_relative_path($directory . '/' . $name);
    }

    return imageoptimizer_normalize_relative_path($name);
}

/**
 * @return string[]
 */
function imageoptimizer_extract_upload_names(mixed $files): array
{
    if (!is_array($files)) {
        return [];
    }
    if (!isset($files['name'])) {
        $names = [];
        foreach ($files as $file) {
            if (is_array($file) && isset($file['name'])) {
                $names[] = (string) $file['name'];
            }
        }

        return $names;
    }
    if (is_array($files['name'])) {
        return array_values(array_filter(array_map('strval', $files['name'])));
    }

    return [(string) $files['name']];
}

/**
 * @return string[]
 */
function imageoptimizer_event_upload_paths(modX $modx): array
{
    $params = $modx->event->params;
    $directory = imageoptimizer_directory_relative_path($params['directory'] ?? null);
    $names = imageoptimizer_extract_upload_names($params['files'] ?? null);
    if ($names === [] && is_array($params['file'] ?? null) && isset($params['file']['name'])) {
        $names = [(string) $params['file']['name']];
    }

    $paths = [];
    foreach ($names as $name) {
        if ($directory !== '') {
            $paths[] = imageoptimizer_normalize_relative_path($directory . '/' . $name);
        } else {
            $paths[] = imageoptimizer_normalize_relative_path($name);
        }
    }

    return $paths;
}

function imageoptimizer_absolute_source_path(modMediaSource $source, string $relativePath): string
{
    $resolved = imageoptimizer_resolve_path_within_source($source, $relativePath);

    return $resolved ?? '';
}

function imageoptimizer_is_processable_upload(modX $modx, modMediaSource $source, string $relativePath): bool
{
    $absolute = imageoptimizer_resolve_path_within_source($source, $relativePath);
    if ($absolute === null || !is_file($absolute)) {
        return false;
    }
    $mime = (string) (mime_content_type($absolute) ?: '');
    if (!imageoptimizer_is_image_mime($mime)) {
        return false;
    }

    return imageoptimizer_preflight($modx, $source, $absolute, $mime) === null;
}

function imageoptimizer_process_upload_sync(modX $modx, int $sourceId, string $path): void
{
    if (!imageoptimizer_get_setting($modx, 'convert_on_upload', true)) {
        return;
    }
    $timeout = max(1, (int) imageoptimizer_get_setting($modx, 'convert_on_upload_sync_timeout', 5));
    $startedAt = microtime(true);
    $path = imageoptimizer_normalize_relative_path($path);

    while ((microtime(true) - $startedAt) < $timeout) {
        $items = imageoptimizer_queue_claim_for_path($modx, $sourceId, $path, 1);
        if ($items === []) {
            break;
        }
        imageoptimizer_convert_queue_item($modx, $items[0]);
    }
}

function imageoptimizer_delete_variants(modX $modx, int $sourceId, string $path): int
{
    $path = imageoptimizer_normalize_relative_path($path);
    $source = imageoptimizer_get_media_source($modx, $sourceId);
    if (!$source) {
        return 0;
    }

    $pattern = (string) imageoptimizer_get_setting($modx, 'variant_pattern', '{basename}.{width}.{ext}');
    $formats = imageoptimizer_get_active_formats($modx);
    $breakpoints = imageoptimizer_parse_breakpoints($modx);
    $removed = 0;

    foreach ($formats as $format) {
        $variantRel = imageoptimizer_build_variant_path($path, 0, $format, $pattern);
        $absolute = imageoptimizer_resolve_path_within_source($source, $variantRel);
        if ($absolute !== null && is_file($absolute) && @unlink($absolute)) {
            $removed++;
        }
        foreach ($breakpoints as $bp) {
            $variantRel = imageoptimizer_build_variant_path($path, $bp, $format, $pattern);
            $absolute = imageoptimizer_resolve_path_within_source($source, $variantRel);
            if ($absolute !== null && is_file($absolute) && @unlink($absolute)) {
                $removed++;
            }
        }
    }

    $collection = $modx->getCollection('ioQueue', ['source' => $sourceId, 'path' => $path]);
    foreach ($collection as $item) {
        $item->remove();
        $removed++;
    }

    return $removed;
}

function imageoptimizer_scan_source(modX $modx, int $sourceId, ?string $subdir = null, bool $enqueue = true): int
{
    $source = imageoptimizer_get_media_source($modx, $sourceId);
    if (!$source || !imageoptimizer_is_filesystem_source($source)) {
        return 0;
    }

    $source->initialize();
    $sourceRoot = rtrim((string) $source->getBasePath(), '/') . '/';
    $scanRoot = $sourceRoot;
    if ($subdir !== null && $subdir !== '') {
        $scanRoot .= imageoptimizer_normalize_relative_path($subdir) . '/';
    }
    if (!is_dir($scanRoot)) {
        return 0;
    }

    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tif', 'tiff', 'heic', 'heif'];
    $enqueued = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile()) {
            continue;
        }
        $ext = strtolower($fileInfo->getExtension());
        if (!in_array($ext, $extensions, true)) {
            continue;
        }
        if (str_ends_with($fileInfo->getFilename(), '.webp') || str_ends_with($fileInfo->getFilename(), '.avif')) {
            continue;
        }

        $absolute = $fileInfo->getPathname();
        if (!str_starts_with($absolute, $sourceRoot)) {
            continue;
        }
        $relative = imageoptimizer_normalize_relative_path(substr($absolute, strlen($sourceRoot)));

        $mime = (string) (mime_content_type($absolute) ?: '');
        if (!imageoptimizer_is_image_mime($mime)) {
            continue;
        }
        if (imageoptimizer_preflight($modx, $source, $absolute, $mime) !== null) {
            continue;
        }

        if ($enqueue) {
            imageoptimizer_enqueue_variants($modx, $sourceId, $relative, (int) $fileInfo->getSize());
        }
        $enqueued++;
    }

    return $enqueued;
}

function imageoptimizer_rebuild_path(modX $modx, int $sourceId, string $path, bool $dryRun = false): int
{
    $path = imageoptimizer_normalize_relative_path($path);
    if ($path === '') {
        return 0;
    }

    $source = imageoptimizer_get_media_source($modx, $sourceId);
    if (!$source) {
        return 0;
    }

    $absolute = imageoptimizer_resolve_path_within_source($source, $path);
    if ($absolute === null) {
        return 0;
    }

    if (is_dir($absolute)) {
        return imageoptimizer_scan_source($modx, $sourceId, $path, !$dryRun);
    }

    if (!is_file($absolute) || !imageoptimizer_is_processable_upload($modx, $source, $path)) {
        return 0;
    }

    if (!$dryRun) {
        imageoptimizer_enqueue_variants($modx, $sourceId, $path, (int) filesize($absolute));
    }

    return 1;
}

function imageoptimizer_on_file_add(modX $modx): void
{
    if (!imageoptimizer_get_setting($modx, 'convert_on_upload', true)) {
        return;
    }

    $sourceId = imageoptimizer_event_source_id($modx);
    if ($sourceId === null) {
        return;
    }
    $source = imageoptimizer_get_media_source($modx, $sourceId);
    if (!$source) {
        return;
    }

    foreach (imageoptimizer_event_upload_paths($modx) as $path) {
        if (!imageoptimizer_is_processable_upload($modx, $source, $path)) {
            continue;
        }
        $absolute = imageoptimizer_absolute_source_path($source, $path);
        imageoptimizer_enqueue_variants($modx, $sourceId, $path, (int) filesize($absolute));
        imageoptimizer_process_upload_sync($modx, $sourceId, $path);
    }
}

function imageoptimizer_on_file_update(modX $modx): void
{
    $sourceId = imageoptimizer_event_source_id($modx);
    $path = imageoptimizer_event_relative_path($modx);
    if ($sourceId === null || $path === null) {
        return;
    }
    $source = imageoptimizer_get_media_source($modx, $sourceId);
    if (!$source) {
        return;
    }
    if (!imageoptimizer_is_processable_upload($modx, $source, $path)) {
        return;
    }

    $absolute = imageoptimizer_absolute_source_path($source, $path);
    imageoptimizer_enqueue_variants($modx, $sourceId, $path, (int) filesize($absolute));
    imageoptimizer_process_upload_sync($modx, $sourceId, $path);
    imageoptimizer_clear_html_cache($modx);
}

function imageoptimizer_on_file_remove(modX $modx): void
{
    $sourceId = imageoptimizer_event_source_id($modx);
    $path = imageoptimizer_event_relative_path($modx);
    if ($sourceId === null || $path === null) {
        return;
    }

    imageoptimizer_delete_variants($modx, $sourceId, $path);
    imageoptimizer_clear_html_cache($modx);
}

function imageoptimizer_on_web_page_prerender(modX $modx): void
{
    if (isset($modx->resource->_output)) {
        imageoptimizer_inject_html($modx, $modx->resource->_output);
    }
}

function imageoptimizer_prune_queue(modX $modx): int
{
    $days = max(1, (int) imageoptimizer_get_setting($modx, 'retention_days', 30));
    $cutoff = date('Y-m-d H:i:s', time() - ($days * 86400));
    $collection = $modx->getCollection('ioQueue', [
        'status' => QueueStatus::Done->value,
        'processed_at:<=' => $cutoff,
    ]);
    $removed = 0;
    foreach ($collection as $item) {
        if ($item->remove()) {
            $removed++;
        }
    }

    return $removed;
}
