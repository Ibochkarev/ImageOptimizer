<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_html_cache_generation(modX $modx): int
{
    $path = imageoptimizer_html_cache_generation_path($modx);
    if (!is_file($path)) {
        return 0;
    }

    return (int) trim((string) file_get_contents($path));
}

function imageoptimizer_bump_html_cache_generation(modX $modx): void
{
    $path = imageoptimizer_html_cache_generation_path($modx);
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $generation = imageoptimizer_html_cache_generation($modx) + 1;
    file_put_contents($path, (string) $generation, LOCK_EX);
}

function imageoptimizer_html_cache_generation_path(modX $modx): string
{
    return imageoptimizer_cache_path($modx) . 'html_generation.txt';
}

function imageoptimizer_html_cache_allowed(modX $modx): bool
{
    if (!(bool) imageoptimizer_get_setting($modx, 'html_cache', true)) {
        return false;
    }

    $contextKey = (string) $modx->context->get('key');
    if ($modx->user && method_exists($modx->user, 'isAuthenticated') && $modx->user->isAuthenticated($contextKey)) {
        return false;
    }

    if ($modx->getPlaceholder('imageoptimizer.skip_html_cache')) {
        return false;
    }

    return true;
}

function imageoptimizer_clear_html_cache(modX $modx): void
{
    $dir = imageoptimizer_cache_path($modx) . 'html/';
    if (is_dir($dir)) {
        foreach (glob($dir . '*.html') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
    $generationPath = imageoptimizer_html_cache_generation_path($modx);
    if (is_file($generationPath)) {
        @unlink($generationPath);
    }
}
