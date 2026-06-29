<?php

defined('MODX_CORE_PATH') || exit;

require_once __DIR__ . '/encoder.php';

final class ImageOptimizerCwebpEncoder implements ImageOptimizerEncoder
{
    public function name(): string
    {
        return 'cwebp';
    }

    public function isAvailable(): bool
    {
        return imageoptimizer_command_available('cwebp');
    }

    public function encode(string $src, string $dst, ImageOptimizerEncodeOptions $options): bool
    {
        $cmd = 'cwebp -quiet -q ' . (int) $options->quality;
        if ($options->preserveIcc) {
            $cmd .= ' -metadata icc';
        }
        $cmd .= ' ' . escapeshellarg($src) . ' -o ' . escapeshellarg($dst);
        exec($cmd, $output, $code);

        return $code === 0;
    }
}

final class ImageOptimizerGdWebpEncoder implements ImageOptimizerEncoder
{
    public function name(): string
    {
        return 'gd';
    }

    public function isAvailable(): bool
    {
        return function_exists('imagewebp');
    }

    public function encode(string $src, string $dst, ImageOptimizerEncodeOptions $options): bool
    {
        $image = imageoptimizer_gd_load_image($src);
        if (!$image) {
            return false;
        }
        $result = imagewebp($image, $dst, $options->quality);
        imagedestroy($image);

        return (bool) $result;
    }
}

final class ImageOptimizerImagickWebpEncoder implements ImageOptimizerEncoder
{
    public function name(): string
    {
        return 'imagick';
    }

    public function isAvailable(): bool
    {
        return extension_loaded('imagick');
    }

    public function encode(string $src, string $dst, ImageOptimizerEncodeOptions $options): bool
    {
        try {
            $imagick = new Imagick($src);
            $imagick->setImageFormat('webp');
            $imagick->setImageCompressionQuality($options->quality);
            $imagick->writeImage($dst);
            $imagick->clear();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}

/**
 * @return GdImage|false
 */
function imageoptimizer_gd_load_image(string $path)
{
    $info = @getimagesize($path);
    if (!$info) {
        return false;
    }
    return match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_PNG => @imagecreatefrompng($path),
        IMAGETYPE_GIF => @imagecreatefromgif($path),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
        default => false,
    };
}

function imageoptimizer_gd_resize(GdImage $image, int $targetWidth): GdImage
{
    $width = imagesx($image);
    $height = imagesy($image);
    if ($targetWidth >= $width) {
        return $image;
    }
    $targetHeight = (int) round($height * ($targetWidth / $width));
    $resized = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
    imagedestroy($image);

    return $resized;
}
