<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array{source: modMediaSource, relative: string, absolute: string}|null
 */
function imageoptimizer_resolve_img_asset(modX $modx, string $src): ?array
{
    $src = trim($src);
    if ($src === '' || str_starts_with($src, 'data:')) {
        return null;
    }

    $src = preg_replace('/[?#].*$/', '', $src) ?? $src;
    $siteUrl = rtrim((string) $modx->getOption('site_url'), '/');

    if (preg_match('#^https?://#i', $src)) {
        if (!str_starts_with($src, $siteUrl)) {
            return null;
        }
        $relativeUrl = substr($src, strlen($siteUrl));
    } elseif (str_starts_with($src, '//')) {
        return null;
    } elseif (str_starts_with($src, '/')) {
        $relativeUrl = $src;
    } else {
        $relativeUrl = '/' . $src;
    }

    if (preg_match('#(^|/)\.\.(/|$)#', $relativeUrl)) {
        return null;
    }

    $baseReal = realpath(rtrim(MODX_BASE_PATH, '/'));
    if ($baseReal === false) {
        return null;
    }
    $baseReal = rtrim(str_replace('\\', '/', $baseReal), '/') . '/';

    $absolute = rtrim(MODX_BASE_PATH, '/') . $relativeUrl;
    if (!is_file($absolute)) {
        return null;
    }

    $realAbsolute = realpath($absolute);
    if ($realAbsolute === false) {
        return null;
    }
    $realAbsolute = str_replace('\\', '/', $realAbsolute);
    if (!str_starts_with($realAbsolute . '/', $baseReal) && $realAbsolute . '/' !== $baseReal) {
        return null;
    }

    $source = imageoptimizer_find_source_for_absolute($modx, $realAbsolute);
    if (!$source) {
        return null;
    }

    $source->initialize();
    $basePath = rtrim((string) $source->getBasePath(), '/') . '/';
    $realBase = realpath(rtrim($basePath, '/'));
    if ($realBase === false) {
        return null;
    }
    $realBase = rtrim(str_replace('\\', '/', $realBase), '/') . '/';
    if (!str_starts_with($realAbsolute . '/', $realBase) && $realAbsolute . '/' !== $realBase) {
        return null;
    }

    $relative = imageoptimizer_normalize_relative_path(substr($realAbsolute, strlen($realBase)));

    return [
        'source' => $source,
        'relative' => $relative,
        'absolute' => $realAbsolute,
    ];
}

function imageoptimizer_find_source_for_absolute(modX $modx, string $absolutePath): ?modMediaSource
{
    foreach (imageoptimizer_get_filesystem_media_sources($modx) as $source) {
        $source->initialize();
        $basePath = rtrim((string) $source->getBasePath(), '/') . '/';
        if (str_starts_with($absolutePath, $basePath)) {
            return $source;
        }
    }

    return null;
}

function imageoptimizer_public_url_for_variant(modMediaSource $source, string $relativePath, int $width, string $format, string $pattern): ?string
{
    $variantRel = imageoptimizer_build_variant_path($relativePath, $width, $format, $pattern);
    $absolute = imageoptimizer_resolve_path_within_source($source, $variantRel);
    if ($absolute === null || !is_file($absolute)) {
        return null;
    }

    $source->initialize();
    $siteUrl = rtrim((string) $source->xpdo->getOption('site_url'), '/');
    $baseUrl = rtrim($siteUrl . '/' . ltrim((string) $source->getBaseUrl(), '/'), '/');

    return $baseUrl . '/' . $variantRel;
}

/**
 * @return array<string, array<int, string>>
 */
function imageoptimizer_collect_variants(modX $modx, modMediaSource $source, string $relativePath): array
{
    $pattern = (string) imageoptimizer_get_setting($modx, 'variant_pattern', '{basename}.{width}.{ext}');
    $formats = imageoptimizer_get_active_formats($modx);
    $breakpoints = imageoptimizer_parse_breakpoints($modx);
    $info = @getimagesize(imageoptimizer_resolve_path_within_source($source, $relativePath) ?? '');
    $originalWidth = $info ? (int) $info[0] : 0;
    $variants = [];

    foreach ($formats as $format) {
        $byWidth = [];
        $full = imageoptimizer_public_url_for_variant($source, $relativePath, 0, $format, $pattern);
        if ($full !== null) {
            $byWidth[0] = $full;
        }
        foreach ($breakpoints as $bp) {
            if ($originalWidth > 0 && !imageoptimizer_should_generate_variant($modx, $originalWidth, $bp)) {
                continue;
            }
            $url = imageoptimizer_public_url_for_variant($source, $relativePath, $bp, $format, $pattern);
            if ($url !== null) {
                $byWidth[$bp] = $url;
            }
        }
        if ($byWidth !== []) {
            $variants[$format] = $byWidth;
        }
    }

    return $variants;
}

function imageoptimizer_build_picture_element(
    DOMDocument $doc,
    DOMElement $img,
    array $variants,
    string $sizes
): ?DOMElement
{
    if ($variants === []) {
        return null;
    }

    $picture = $doc->createElement('picture');
    $mimeTypes = [
        'avif' => 'image/avif',
        'webp' => 'image/webp',
    ];
    $order = ['avif', 'webp'];
    foreach ($order as $format) {
        if (!isset($variants[$format])) {
            continue;
        }
        $srcset = imageoptimizer_build_srcset($variants[$format]);
        if ($srcset === '') {
            continue;
        }
        $source = $doc->createElement('source');
        $source->setAttribute('type', $mimeTypes[$format]);
        $source->setAttribute('srcset', $srcset);
        if ($sizes !== '') {
            $source->setAttribute('sizes', $sizes);
        }
        $picture->appendChild($source);
    }

    if ($picture->getElementsByTagName('source')->length === 0) {
        return null;
    }

    $clonedImg = $img->cloneNode(true);
    if ($clonedImg instanceof DOMElement) {
        $picture->appendChild($clonedImg);
    }

    return $picture;
}

function imageoptimizer_replace_img_with_picture(DOMDocument $doc, DOMElement $img, DOMElement $picture): void
{
    $parent = $img->parentNode;
    if (!$parent) {
        return;
    }
    $parent->replaceChild($picture, $img);
}
