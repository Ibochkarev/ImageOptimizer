<?php

defined('MODX_CORE_PATH') || exit;

require_once __DIR__ . '/encoder.php';
require_once __DIR__ . '/webp.php';

final class ImageOptimizerAvifencEncoder implements ImageOptimizerEncoder
{
    public function name(): string
    {
        return 'avifenc';
    }

    public function isAvailable(): bool
    {
        return imageoptimizer_command_available('avifenc');
    }

    public function encode(string $src, string $dst, ImageOptimizerEncodeOptions $options): bool
    {
        $cmd = 'avifenc --quiet -q ' . (int) $options->quality
            . ' ' . escapeshellarg($src) . ' ' . escapeshellarg($dst);
        exec($cmd, $output, $code);

        return $code === 0;
    }
}

final class ImageOptimizerGdAvifEncoder implements ImageOptimizerEncoder
{
    public function name(): string
    {
        return 'gd_avif';
    }

    public function isAvailable(): bool
    {
        return function_exists('imageavif');
    }

    public function encode(string $src, string $dst, ImageOptimizerEncodeOptions $options): bool
    {
        $image = imageoptimizer_gd_load_image($src);
        if (!$image) {
            return false;
        }
        $result = imageavif($image, $dst, $options->quality);
        imagedestroy($image);

        return (bool) $result;
    }
}
