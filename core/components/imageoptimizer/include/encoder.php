<?php

defined('MODX_CORE_PATH') || exit;

final readonly class ImageOptimizerEncodeOptions
{
    public function __construct(
        public int $quality,
        public bool $preserveIcc,
        public bool $preserveExif,
    ) {
    }
}

interface ImageOptimizerEncoder
{
    public function name(): string;

    public function isAvailable(): bool;

    public function encode(string $src, string $dst, ImageOptimizerEncodeOptions $options): bool;
}

final class ImageOptimizerEncoderPipeline
{
    /** @var ImageOptimizerEncoder[] */
    private array $encoders;

    public function __construct(array $encoders)
    {
        $this->encoders = $encoders;
    }

    public function encode(string $src, string $dst, ImageOptimizerEncodeOptions $options): bool
    {
        foreach ($this->encoders as $encoder) {
            if (!$encoder->isAvailable()) {
                continue;
            }
            if ($encoder->encode($src, $dst, $options)) {
                return is_file($dst) && filesize($dst) > 0;
            }
        }

        return false;
    }
}

function imageoptimizer_build_encoder_pipeline(modX $modx, string $format): ImageOptimizerEncoderPipeline
{
    $priority = array_map('trim', explode(',', (string) imageoptimizer_get_setting($modx, 'method_priority', 'cwebp,gd,imagick')));
    $encoders = [];
    foreach ($priority as $name) {
        if ($format === 'webp') {
            if ($name === 'cwebp') {
                $encoders[] = new ImageOptimizerCwebpEncoder();
            } elseif ($name === 'gd') {
                $encoders[] = new ImageOptimizerGdWebpEncoder();
            } elseif ($name === 'imagick') {
                $encoders[] = new ImageOptimizerImagickWebpEncoder();
            }
        } elseif ($format === 'avif') {
            if ($name === 'avifenc') {
                $encoders[] = new ImageOptimizerAvifencEncoder();
            } elseif ($name === 'gd') {
                $encoders[] = new ImageOptimizerGdAvifEncoder();
            }
        }
    }

    return new ImageOptimizerEncoderPipeline($encoders);
}

function imageoptimizer_build_encode_options(modX $modx, string $mime): ImageOptimizerEncodeOptions
{
    $quality = (int) imageoptimizer_get_setting($modx, 'quality', 82);
    if (str_contains($mime, 'png')) {
        $quality = (int) imageoptimizer_get_setting($modx, 'quality_png', 90);
    } elseif (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
        $quality = (int) imageoptimizer_get_setting($modx, 'quality_jpeg', 82);
    }

    return new ImageOptimizerEncodeOptions(
        max(1, min(100, $quality)),
        (bool) imageoptimizer_get_setting($modx, 'preserve_icc', true),
        (bool) imageoptimizer_get_setting($modx, 'preserve_exif', false),
    );
}
