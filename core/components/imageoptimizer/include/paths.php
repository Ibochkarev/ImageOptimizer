<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_core_path(modX $modx): string
{
    return $modx->getOption('imageoptimizer.core_path', null, $modx->getOption('core_path') . 'components/imageoptimizer/');
}

function imageoptimizer_assets_url(modX $modx): string
{
    return $modx->getOption('imageoptimizer.assets_url', null, $modx->getOption('assets_url') . 'components/imageoptimizer/');
}

function imageoptimizer_assets_base_url(modX $modx): string
{
    $assetsUrl = imageoptimizer_assets_url($modx);
    if (strpos($assetsUrl, 'http') !== 0) {
        $base = rtrim((string) $modx->getOption('site_url', null, ''), '/');
        $assetsUrl = $base . '/' . ltrim($assetsUrl, '/');
    }

    return rtrim($assetsUrl, '/') . '/';
}

function imageoptimizer_cache_path(modX $modx): string
{
    $path = $modx->getOption('imageoptimizer.cache_path', null, '');
    if ($path !== '') {
        return rtrim($path, '/') . '/';
    }

    return rtrim((string) $modx->getOption('core_path', null, MODX_CORE_PATH), '/') . '/cache/imageoptimizer/';
}

function imageoptimizer_add_package(modX $modx): void
{
    $modx->addPackage('imageoptimizer', imageoptimizer_core_path($modx) . 'model/');
}

function imageoptimizer_bootstrap(): void
{
    // Module load only.
}

function imageoptimizer_normalize_relative_path(string $path): string
{
    $path = str_replace('\\', '/', $path);
    $path = ltrim($path, '/');
    if ($path === '' || str_contains($path, "\0") || preg_match('#(^|/)\.\.(/|$)#', $path)) {
        return '';
    }

    return $path;
}

function imageoptimizer_media_source_base_path(modMediaSource $source): string
{
    $source->initialize();

    return rtrim(str_replace('\\', '/', (string) $source->getBasePath()), '/') . '/';
}

function imageoptimizer_is_path_within_base(string $basePath, string $absolutePath): bool
{
    $basePath = rtrim(str_replace('\\', '/', $basePath), '/') . '/';
    $absolutePath = str_replace('\\', '/', $absolutePath);

    if ($absolutePath === '' || !str_starts_with($absolutePath, $basePath)) {
        return false;
    }

    $suffix = substr($absolutePath, strlen($basePath));
    if ($suffix === '') {
        return true;
    }

    return !preg_match('#(^|/)\.\.(/|$)#', $suffix);
}

function imageoptimizer_resolve_path_within_source(modMediaSource $source, string $relativePath): ?string
{
    $relativePath = imageoptimizer_normalize_relative_path($relativePath);
    if ($relativePath === '') {
        return null;
    }

    $basePath = imageoptimizer_media_source_base_path($source);
    $absolute = $basePath . $relativePath;
    if (!imageoptimizer_is_path_within_base($basePath, $absolute)) {
        return null;
    }

    $realBase = realpath(rtrim($basePath, '/'));
    if ($realBase === false) {
        return null;
    }
    $realBase = rtrim(str_replace('\\', '/', $realBase), '/') . '/';

    if (is_file($absolute) || is_dir($absolute)) {
        $realPath = realpath($absolute);
        if ($realPath === false) {
            return null;
        }
        $realPath = str_replace('\\', '/', $realPath);
        if (!str_starts_with($realPath . '/', $realBase) && $realPath . '/' !== $realBase) {
            return null;
        }

        return $realPath;
    }

    return $absolute;
}

function imageoptimizer_get_media_source(modX $modx, int $sourceId): ?modMediaSource
{
    if ($sourceId <= 0) {
        return null;
    }

    foreach (['sources.modMediaSource', 'MODX\\Revolution\\Sources\\modMediaSource', 'modMediaSource'] as $classKey) {
        $source = $modx->getObject($classKey, $sourceId);
        if ($source instanceof modMediaSource) {
            return $source;
        }
    }

    return null;
}

/**
 * @return list<modMediaSource>
 */
function imageoptimizer_get_filesystem_media_sources(modX $modx): array
{
    $sources = [];
    foreach (['sources.modMediaSource', 'MODX\\Revolution\\Sources\\modMediaSource', 'modMediaSource'] as $classKey) {
        $collection = $modx->getCollection($classKey);
        if (!$collection) {
            continue;
        }
        foreach ($collection as $source) {
            if (!$source instanceof modMediaSource || !imageoptimizer_is_filesystem_source($source)) {
                continue;
            }
            $sources[(int) $source->get('id')] = $source;
        }
        if ($sources !== []) {
            break;
        }
    }

    return array_values($sources);
}

/**
 * @param 'read'|'write'|'remove' $operation
 */
function imageoptimizer_user_can_access_media_source(modX $modx, modMediaSource $source, string $operation): bool
{
    if (!$source->initialize()) {
        return false;
    }

    $policy = match ($operation) {
        'write' => 'save',
        'remove' => 'remove',
        default => 'view',
    };
    if (!$source->checkPolicy($policy)) {
        return false;
    }

    $permission = match ($operation) {
        'write' => 'file_upload',
        'remove' => 'file_remove',
        default => 'file_list',
    };

    return $source->hasPermission($permission);
}

/**
 * @param 'read'|'write'|'remove' $operation
 */
function imageoptimizer_require_media_source(modX $modx, int $sourceId, string $operation = 'read'): modMediaSource
{
    $source = imageoptimizer_get_media_source($modx, $sourceId);
    if (!$source || !imageoptimizer_user_can_access_media_source($modx, $source, $operation)) {
        imageoptimizer_json_error('media_source_forbidden', 403, $modx);
    }

    return $source;
}

