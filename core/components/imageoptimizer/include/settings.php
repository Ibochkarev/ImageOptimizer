<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_get_setting(modX $modx, string $key, mixed $default = null): mixed
{
    return $modx->getOption('imageoptimizer_' . $key, null, $default);
}

function imageoptimizer_is_enabled(modX $modx): bool
{
    return (bool) imageoptimizer_get_setting($modx, 'enabled', true);
}

/**
 * @return list<string>
 */
function imageoptimizer_settings_keys(): array
{
    return [
        'enabled', 'formats', 'avif_enabled', 'quality', 'quality_jpeg', 'quality_png',
        'breakpoints', 'variant_pattern', 'upscale', 'responsive_min_width', 'default_sizes',
        'inject_frontend', 'inject_email', 'skip_classes', 'skip_src_pattern',
        'respect_existing_srcset', 'respect_existing_picture', 'respect_existing_loading',
        'method_priority', 'cron_limit', 'html_cache', 'convert_on_upload',
        'convert_on_upload_sync_timeout', 'reencode_if_unchanged', 'preserve_exif', 'preserve_icc',
        'max_memory_limit', 'stuck_minutes', 'retention_days', 'max_html_size',
        'skip_lazy_first_images', 'cleanup_on_uninstall', 'disk_warn_gb',
    ];
}
