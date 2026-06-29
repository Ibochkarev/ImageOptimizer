<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_inject_html(modX $modx, string &$html): void
{
    if (!imageoptimizer_get_setting($modx, 'inject_frontend', true)) {
        return;
    }
    if ($modx->context->get('key') === 'mgr') {
        return;
    }
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        return;
    }
    if ($modx->resource) {
        $contentTypeId = (int) $modx->resource->get('content_type');
        if ($contentTypeId > 0) {
            $contentType = $modx->getObject('modContentType', $contentTypeId);
            if ($contentType && stripos((string) $contentType->get('mime_type'), 'html') === false) {
                return;
            }
        }
    }

    $maxSize = (int) imageoptimizer_get_setting($modx, 'max_html_size', 1048576);
    if ($maxSize > 0 && strlen($html) > $maxSize) {
        return;
    }

    $cacheEnabled = (bool) imageoptimizer_get_setting($modx, 'html_cache', true);
    $cacheFile = null;
    if ($cacheEnabled) {
        $cacheFile = imageoptimizer_html_cache_file($modx);
        if ($cacheFile !== null && is_file($cacheFile)) {
            $html = (string) file_get_contents($cacheFile);

            return;
        }
    }

    $doc = imageoptimizer_load_html_document($html);
    if (!$doc) {
        return;
    }

    $skipRules = new ImageOptimizerImgSkipRules($modx);
    $defaultSizes = trim((string) imageoptimizer_get_setting($modx, 'default_sizes', '(min-width: 1280px) 50vw, 100vw'));
    $images = imageoptimizer_extract_img_nodes($doc);
    $index = 0;

    foreach ($images as $img) {
        $reason = $skipRules->shouldSkip($img);
        if ($reason === SkipReason::HasSrcset) {
            $skipRules->applyLazyAttributes($img, $index);
            $index++;
            continue;
        }
        if ($reason !== null) {
            $index++;
            continue;
        }

        $src = trim($img->getAttribute('src'));
        if ($src === '') {
            $src = trim($img->getAttribute('data-src'));
        }
        $asset = imageoptimizer_resolve_img_asset($modx, $src);
        if ($asset === null) {
            $skipRules->applyLazyAttributes($img, $index);
            $index++;
            continue;
        }

        $variants = imageoptimizer_collect_variants($modx, $asset['source'], $asset['relative']);
        if ($variants === []) {
            $skipRules->applyLazyAttributes($img, $index);
            $index++;
            continue;
        }

        $sizes = imageoptimizer_resolve_sizes(
            trim($img->getAttribute('data-imageoptimizer-sizes')),
            trim($img->getAttribute('sizes')),
            $defaultSizes
        );
        $picture = imageoptimizer_build_picture_element($doc, $img, $variants, $sizes);
        if ($picture === null) {
            $skipRules->applyLazyAttributes($img, $index);
            $index++;
            continue;
        }

        $imgInPicture = $picture->getElementsByTagName('img')->item(0);
        if ($imgInPicture instanceof DOMElement) {
            $skipRules->applyLazyAttributes($imgInPicture, $index);
        }

        imageoptimizer_replace_img_with_picture($doc, $img, $picture);
        $index++;
    }

    $html = imageoptimizer_serialize_document($doc);

    if ($cacheEnabled && $cacheFile !== null) {
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($cacheFile, $html);
    }
}

function imageoptimizer_html_cache_file(modX $modx): ?string
{
    if (!$modx->resource) {
        return null;
    }

    $contextKey = (string) $modx->context->get('key');
    $uri = (string) $modx->resource->get('uri');
    $editedOn = (string) $modx->resource->get('editedon');
    $settingsHash = imageoptimizer_settings_hash($modx);
    $variantsGen = imageoptimizer_html_cache_generation($modx);
    $key = md5($contextKey . '|' . $uri . '|' . $editedOn . '|' . $settingsHash . '|' . $variantsGen);

    return imageoptimizer_cache_path($modx) . 'html/' . $key . '.html';
}

function imageoptimizer_settings_hash(modX $modx): string
{
    $keys = [
        'formats', 'avif_enabled', 'breakpoints', 'variant_pattern', 'default_sizes',
        'skip_classes', 'skip_src_pattern', 'respect_existing_srcset', 'respect_existing_picture',
        'respect_existing_loading', 'skip_lazy_first_images', 'inject_frontend',
    ];
    $payload = [];
    foreach ($keys as $key) {
        $payload[$key] = imageoptimizer_get_setting($modx, $key);
    }

    return md5(json_encode($payload, JSON_UNESCAPED_UNICODE));
}
