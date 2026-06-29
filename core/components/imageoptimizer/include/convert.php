<?php

defined('MODX_CORE_PATH') || exit;

require_once __DIR__ . '/queue.php';
require_once __DIR__ . '/responsive.php';
require_once __DIR__ . '/limits.php';
require_once __DIR__ . '/preflight.php';
require_once __DIR__ . '/encoder.php';
require_once __DIR__ . '/webp.php';
require_once __DIR__ . '/avif.php';

function imageoptimizer_convert_queue_item(modX $modx, ioQueue $item): void
{
    $sourceId = (int) $item->get('source');
    $path = (string) $item->get('path');
    $format = (string) $item->get('format');
    $width = (int) $item->get('width');

    $source = imageoptimizer_get_media_source($modx, $sourceId);
    if (!$source) {
        imageoptimizer_queue_mark_failed($item, 'MediaSource not found');

        return;
    }

    $source->initialize();
    $basePath = imageoptimizer_media_source_base_path($source);
    $absoluteSource = imageoptimizer_resolve_path_within_source($source, $path);
    if ($absoluteSource === null || !is_file($absoluteSource)) {
        imageoptimizer_queue_mark_failed($item, 'Source file missing or path invalid');

        return;
    }

    $mime = (string) (mime_content_type($absoluteSource) ?: '');
    $skip = imageoptimizer_preflight($modx, $source, $absoluteSource, $mime);
    if ($skip !== null) {
        imageoptimizer_queue_mark_skipped($item, $skip);

        return;
    }

    $pattern = (string) imageoptimizer_get_setting($modx, 'variant_pattern', '{basename}.{width}.{ext}');
    $variantRel = imageoptimizer_build_variant_path($path, $width, $format, $pattern);
    if ($variantRel === '') {
        imageoptimizer_queue_mark_failed($item, 'Variant path invalid');

        return;
    }
    $absoluteDst = imageoptimizer_resolve_path_within_source($source, $variantRel);
    if ($absoluteDst === null) {
        imageoptimizer_queue_mark_failed($item, 'Variant path outside media source');

        return;
    }

    if (!imageoptimizer_get_setting($modx, 'reencode_if_unchanged', false)
        && is_file($absoluteDst)
        && filemtime($absoluteDst) >= filemtime($absoluteSource)) {
        imageoptimizer_queue_mark_done($modx, $item, (int) filesize($absoluteDst));

        return;
    }

    $info = @getimagesize($absoluteSource);
    if (!$info) {
        imageoptimizer_queue_mark_failed($item, 'Cannot read image dimensions');

        return;
    }

    $originalWidth = (int) $info[0];
    if ($width > 0 && !imageoptimizer_should_generate_variant($modx, $originalWidth, $width)) {
        imageoptimizer_queue_mark_skipped($item, SkipReason::UpscaleSkip);

        return;
    }

    $tmpDir = rtrim((string) $modx->getOption('core_path', null, MODX_CORE_PATH), '/') . '/cache/imageoptimizer';
    if (!is_dir($tmpDir) && !mkdir($tmpDir, 0755, true) && !is_dir($tmpDir)) {
        imageoptimizer_queue_mark_failed($item, 'Cannot create temp directory');

        return;
    }
    $tmp = $tmpDir . '/tmp_' . uniqid('', true) . '.src';
    if (!copy($absoluteSource, $tmp)) {
        imageoptimizer_queue_mark_failed($item, 'Cannot copy source to temp');

        return;
    }

    try {
        imageoptimizer_bump_memory_limit($modx, $originalWidth * (int) $info[1] * 4 * 2);
        $working = $tmp;
        if ($width > 0 && $originalWidth > $width) {
            $image = imageoptimizer_gd_load_image($tmp);
            if ($image) {
                $resized = imageoptimizer_gd_resize($image, $width);
                $resizedPath = $tmp . '.resize.png';
                imagepng($resized, $resizedPath);
                imagedestroy($resized);
                $working = $resizedPath;
            }
        }

        $options = imageoptimizer_build_encode_options($modx, $mime);
        $pipeline = imageoptimizer_build_encoder_pipeline($modx, $format);
        $dir = dirname($absoluteDst);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!$pipeline->encode($working, $absoluteDst, $options)) {
            imageoptimizer_queue_mark_failed($item, 'Encoder pipeline failed');

            return;
        }

        if ($width === 0) {
            $item->set('original_size', filesize($absoluteSource));
        }
        imageoptimizer_queue_mark_done($modx, $item, (int) filesize($absoluteDst));
    } catch (ImageOptimizerMemoryLimitException) {
        imageoptimizer_queue_mark_skipped($item, SkipReason::MemoryLimit);
    } catch (Throwable $e) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[imageoptimizer] Conversion error: ' . $e->getMessage());
        imageoptimizer_queue_mark_failed($item, 'Conversion failed');
    } finally {
        if (is_file($tmp)) {
            @unlink($tmp);
        }
        $resizedPath = $tmp . '.resize.png';
        if (is_file($resizedPath)) {
            @unlink($resizedPath);
        }
    }
}

function imageoptimizer_enqueue_variants(modX $modx, int $sourceId, string $path, ?int $originalSize = null): void
{
    $formats = imageoptimizer_get_active_formats($modx);
    $breakpoints = imageoptimizer_parse_breakpoints($modx);
    foreach ($formats as $format) {
        imageoptimizer_queue_enqueue($modx, $sourceId, $path, $format, 0, $originalSize);
        foreach ($breakpoints as $bp) {
            imageoptimizer_queue_enqueue($modx, $sourceId, $path, $format, $bp);
        }
    }
}

function imageoptimizer_process_queue(modX $modx, int $limit, ?float $startedAt = null, ?int $timeBudget = null): int
{
    $startedAt ??= microtime(true);
    $timeBudget ??= imageoptimizer_default_time_budget();
    $processed = 0;
    $items = imageoptimizer_queue_claim($modx, $limit);
    foreach ($items as $item) {
        imageoptimizer_check_time_budget($startedAt, $timeBudget);
        imageoptimizer_convert_queue_item($modx, $item);
        $processed++;
    }

    return $processed;
}
