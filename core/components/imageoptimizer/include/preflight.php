<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_is_image_mime(string $mime): bool
{
    return str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml';
}

function imageoptimizer_preflight(modX $modx, modMediaSource $source, string $absolutePath, string $mime): ?SkipReason
{
    if (!imageoptimizer_is_filesystem_source($source)) {
        return SkipReason::NonFilesystemSource;
    }
    if ($mime === 'image/svg+xml') {
        return SkipReason::SvgSkip;
    }
    if ($mime === 'image/webp') {
        return SkipReason::AlreadyWebp;
    }
    if (imageoptimizer_is_animated_image($absolutePath, $mime)) {
        return SkipReason::AnimatedNotSupported;
    }
    if (in_array($mime, ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'], true)
        && !imageoptimizer_can_decode_heic($modx)) {
        return SkipReason::HeicNoDecoder;
    }

    return null;
}

function imageoptimizer_is_filesystem_source(modMediaSource $source): bool
{
    $classKey = (string) $source->get('class_key');
    if ($classKey === '' || $classKey === 'sources.modFileMediaSource') {
        return true;
    }

    return str_contains(strtolower($classKey), 'file');
}

function imageoptimizer_is_animated_image(string $path, string $mime): bool
{
    if ($mime === 'image/gif' && function_exists('imagecreatefromgif')) {
        $img = @imagecreatefromgif($path);
        if ($img) {
            $frames = 0;
            for ($i = 0; $i < 2; $i++) {
                if (@imagecolortransparent($img) >= 0 || @imagecolorat($img, 0, 0) !== false) {
                    $frames++;
                }
            }
            imagedestroy($img);
            // GIF with multiple frames often reports as animated via file scan
        }
    }

    if ($mime === 'image/gif' || $mime === 'image/png') {
        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return false;
        }
        $header = fread($handle, 1024);
        fclose($handle);
        if ($mime === 'image/gif' && str_contains($header, 'NETSCAPE2.0')) {
            return true;
        }
        if ($mime === 'image/png' && preg_match('/acTL/', $header)) {
            return true;
        }
    }

    return false;
}

function imageoptimizer_can_decode_heic(modX $modx): bool
{
    if (imageoptimizer_command_available('heif-convert')) {
        return true;
    }
    if (extension_loaded('imagick')) {
        $imagick = new Imagick();
        $formats = $imagick->queryFormats('HEIC');
        if ($formats !== []) {
            return true;
        }
    }

    return false;
}

function imageoptimizer_command_available(string $command): bool
{
    $path = trim((string) shell_exec('command -v ' . escapeshellarg($command) . ' 2>/dev/null'));
    if ($path !== '' && is_executable($path)) {
        return true;
    }

    $homebrew = '/opt/homebrew/bin/' . $command;
    if (is_executable($homebrew)) {
        return true;
    }

    $usrLocal = '/usr/local/bin/' . $command;
    if (is_executable($usrLocal)) {
        return true;
    }

    return false;
}
