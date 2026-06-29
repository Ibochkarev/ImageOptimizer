<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array<string, bool|string>
 */
function imageoptimizer_detect_encoders(modX $modx): array
{
    return [
        'cwebp' => imageoptimizer_command_available('cwebp'),
        'avifenc' => imageoptimizer_command_available('avifenc'),
        'gd_webp' => function_exists('imagewebp'),
        'gd_avif' => function_exists('imageavif'),
        'imagick_webp' => extension_loaded('imagick') && in_array('WEBP', Imagick::queryFormats('WEBP'), true),
        'imagick_heic' => extension_loaded('imagick') && Imagick::queryFormats('HEIC') !== [],
        'heif_convert' => imageoptimizer_command_available('heif-convert'),
    ];
}

function imageoptimizer_server_readiness_score(array $encoders): int
{
    $checks = ['cwebp', 'gd_webp', 'imagick_webp', 'avifenc', 'gd_avif'];
    $available = 0;
    foreach ($checks as $key) {
        if (!empty($encoders[$key])) {
            $available++;
        }
    }

    return (int) round(($available / count($checks)) * 100);
}
