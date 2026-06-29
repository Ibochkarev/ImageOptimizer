<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return int[]
 */
function imageoptimizer_parse_breakpoints(modX $modx): array
{
    $csv = trim((string) imageoptimizer_get_setting($modx, 'breakpoints', '480,768,1024,1440,1920'));
    if ($csv === '') {
        return [];
    }

    $values = [];
    foreach (explode(',', $csv) as $part) {
        $width = (int) trim($part);
        if ($width >= 16 && $width <= 8000) {
            $values[$width] = $width;
        }
    }

    return array_values($values);
}

function imageoptimizer_should_generate_variant(modX $modx, int $originalWidth, int $breakpoint): bool
{
    $minWidth = (int) imageoptimizer_get_setting($modx, 'responsive_min_width', 320);
    if ($breakpoint < $minWidth) {
        return false;
    }
    $upscale = (bool) imageoptimizer_get_setting($modx, 'upscale', false);
    if (!$upscale && $breakpoint >= $originalWidth) {
        return false;
    }

    return true;
}

function imageoptimizer_is_valid_variant_pattern(string $pattern): bool
{
    if ($pattern === '' || preg_match('#[/\\\\\0]#', $pattern)) {
        return false;
    }

    $probe = str_replace(
        ['{basename}', '{width}', '{ext}'],
        ['sample.jpg', '480', 'webp'],
        $pattern
    );

    return imageoptimizer_normalize_relative_path($probe) !== '';
}

function imageoptimizer_build_variant_path(string $sourcePath, int $width, string $format, string $pattern): string
{
    $dir = dirname($sourcePath);
    $basename = basename($sourcePath);
    $ext = $format === 'avif' ? 'avif' : 'webp';

    if ($width === 0) {
        $variantName = $basename . '.' . $ext;
    } else {
        $variantName = str_replace(
            ['{basename}', '{width}', '{ext}'],
            [$basename, (string) $width, $ext],
            $pattern
        );
    }

    if ($dir === '.' || $dir === '') {
        return imageoptimizer_normalize_relative_path($variantName);
    }

    return imageoptimizer_normalize_relative_path(rtrim(str_replace('\\', '/', $dir), '/') . '/' . $variantName);
}

function imageoptimizer_resolve_sizes(string $dataAttr, string $imgSizes, string $default): string
{
    $dataAttr = trim($dataAttr);
    if ($dataAttr !== '') {
        return $dataAttr;
    }
    $imgSizes = trim($imgSizes);
    if ($imgSizes !== '') {
        return $imgSizes;
    }

    return trim($default);
}

/**
 * @param array<int, string> $variants width => url
 */
function imageoptimizer_build_srcset(array $variants): string
{
    $parts = [];
    foreach ($variants as $width => $url) {
        if ($width > 0 && $url !== '') {
            $parts[] = $url . ' ' . $width . 'w';
        }
    }
    if ($parts !== []) {
        return implode(', ', $parts);
    }
    $full = trim((string) ($variants[0] ?? ''));

    return $full;
}

function imageoptimizer_get_active_formats(modX $modx): array
{
    $formats = [];
    $configured = strtolower(trim((string) imageoptimizer_get_setting($modx, 'formats', 'webp')));
    if (str_contains($configured, 'webp')) {
        $formats[] = 'webp';
    }
    if (imageoptimizer_get_setting($modx, 'avif_enabled', false)) {
        $formats[] = 'avif';
    }
    if ($formats === []) {
        $formats[] = 'webp';
    }

    return $formats;
}
