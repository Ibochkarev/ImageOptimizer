<?php

/**
 * @package imageoptimizer
 */

$_lang['setting_imageoptimizer_enabled'] = 'Enabled';
$_lang['setting_imageoptimizer_enabled_desc'] = 'Enable ImageOptimizer processing.';

$_lang['setting_imageoptimizer_formats'] = 'Output formats';
$_lang['setting_imageoptimizer_formats_desc'] = 'Comma-separated list (webp, avif).';

$_lang['setting_imageoptimizer_avif_enabled'] = 'AVIF encoding';
$_lang['setting_imageoptimizer_avif_enabled_desc'] = 'Generate AVIF variants when encoders are available.';

$_lang['setting_imageoptimizer_quality'] = 'Default quality';
$_lang['setting_imageoptimizer_quality_desc'] = 'Fallback quality (1–100) when quality_jpeg/quality_png are not used.';

$_lang['setting_imageoptimizer_quality_jpeg'] = 'JPEG quality';
$_lang['setting_imageoptimizer_quality_jpeg_desc'] = 'Quality for JPEG sources (1–100).';

$_lang['setting_imageoptimizer_quality_png'] = 'PNG quality';
$_lang['setting_imageoptimizer_quality_png_desc'] = 'Quality for PNG sources (1–100).';

$_lang['setting_imageoptimizer_method_priority'] = 'Encoder priority';
$_lang['setting_imageoptimizer_method_priority_desc'] = 'Order of encoders: cwebp, avifenc, gd, imagick.';

$_lang['setting_imageoptimizer_cron_limit'] = 'Cron batch size';
$_lang['setting_imageoptimizer_cron_limit_desc'] = 'Max queue items per cron run.';

$_lang['setting_imageoptimizer_retention_days'] = 'Queue retention (days)';
$_lang['setting_imageoptimizer_retention_days_desc'] = 'Delete completed queue rows older than N days.';

$_lang['setting_imageoptimizer_inject_frontend'] = 'Auto <picture> on frontend';
$_lang['setting_imageoptimizer_inject_frontend_desc'] = 'Wrap <img> tags in <picture> with WebP/AVIF sources.';

$_lang['setting_imageoptimizer_default_sizes'] = 'Default sizes attribute';
$_lang['setting_imageoptimizer_default_sizes_desc'] = 'CSS sizes for responsive images when not set on <img>.';

$_lang['setting_imageoptimizer_skip_src_pattern'] = 'Skip URL pattern';
$_lang['setting_imageoptimizer_skip_src_pattern_desc'] = 'Substring in src URL to skip (e.g. thumb3x).';

$_lang['setting_imageoptimizer_skip_classes'] = 'Skip CSS classes';
$_lang['setting_imageoptimizer_skip_classes_desc'] = 'Comma-separated classes on <img> to skip.';

$_lang['setting_imageoptimizer_convert_on_upload'] = 'Convert on upload';
$_lang['setting_imageoptimizer_convert_on_upload_desc'] = 'Enqueue or sync-convert when files are uploaded via File Manager.';

$_lang['setting_imageoptimizer_breakpoints'] = 'Breakpoints';
$_lang['setting_imageoptimizer_breakpoints_desc'] = 'Responsive widths (CSV). Empty = original size only. Default: 480,768,1024,1440,1920.';

$_lang['setting_imageoptimizer_variant_pattern'] = 'Variant filename pattern';
$_lang['setting_imageoptimizer_variant_pattern_desc'] = 'Placeholders: {basename}, {width}, {ext}. Width 0 → image.jpg.webp.';

$_lang['setting_imageoptimizer_upscale'] = 'Upscale breakpoints';
$_lang['setting_imageoptimizer_upscale_desc'] = 'Generate variants wider than the original. Default: skip (upscale_skip).';

$_lang['setting_imageoptimizer_responsive_min_width'] = 'Min responsive width';
$_lang['setting_imageoptimizer_responsive_min_width_desc'] = 'Do not generate breakpoints narrower than this (px).';

$_lang['setting_imageoptimizer_inject_email'] = 'Inject in email HTML';
$_lang['setting_imageoptimizer_inject_email_desc'] = 'Wrap <img> in <picture> in outgoing email HTML when context allows.';

$_lang['setting_imageoptimizer_respect_existing_srcset'] = 'Respect existing srcset';
$_lang['setting_imageoptimizer_respect_existing_srcset_desc'] = 'Skip <img> tags that already have a srcset attribute.';

$_lang['setting_imageoptimizer_respect_existing_picture'] = 'Respect existing picture';
$_lang['setting_imageoptimizer_respect_existing_picture_desc'] = 'Skip <img> inside an existing <picture> element.';

$_lang['setting_imageoptimizer_respect_existing_loading'] = 'Respect existing loading';
$_lang['setting_imageoptimizer_respect_existing_loading_desc'] = 'Do not overwrite the loading attribute on <img>.';

$_lang['setting_imageoptimizer_html_cache'] = 'HTML injection cache';
$_lang['setting_imageoptimizer_html_cache_desc'] = 'Cache <picture> injection output under core/cache/imageoptimizer/html/.';

$_lang['setting_imageoptimizer_convert_on_upload_sync_timeout'] = 'Upload sync timeout (sec)';
$_lang['setting_imageoptimizer_convert_on_upload_sync_timeout_desc'] = 'Seconds to wait for sync conversion on File Manager upload.';

$_lang['setting_imageoptimizer_reencode_if_unchanged'] = 'Re-encode if unchanged';
$_lang['setting_imageoptimizer_reencode_if_unchanged_desc'] = 'Re-queue done items when the source file hash is unchanged.';

$_lang['setting_imageoptimizer_preserve_exif'] = 'Preserve EXIF';
$_lang['setting_imageoptimizer_preserve_exif_desc'] = 'Pass EXIF into WebP/AVIF when the encoder supports it.';

$_lang['setting_imageoptimizer_preserve_icc'] = 'Preserve ICC profile';
$_lang['setting_imageoptimizer_preserve_icc_desc'] = 'Embed color profile in output files.';

$_lang['setting_imageoptimizer_max_html_size'] = 'Max HTML size (bytes)';
$_lang['setting_imageoptimizer_max_html_size_desc'] = 'Skip HTML larger than N bytes (OOM guard). Default 1048576.';

$_lang['setting_imageoptimizer_skip_lazy_first_images'] = 'Skip lazy for first N images';
$_lang['setting_imageoptimizer_skip_lazy_first_images_desc'] = 'First N <img> on a page get loading=eager instead of lazy.';

$_lang['setting_imageoptimizer_cleanup_on_uninstall'] = 'Cleanup on uninstall';
$_lang['setting_imageoptimizer_cleanup_on_uninstall_desc'] = 'Remove variants, queue table, and cache when the package is uninstalled.';

$_lang['setting_imageoptimizer_disk_warn_gb'] = 'Disk warning threshold (GB)';
$_lang['setting_imageoptimizer_disk_warn_gb_desc'] = 'Show admin warning when free disk space is below N GB.';

$_lang['setting_imageoptimizer_stuck_minutes'] = 'Stuck timeout (minutes)';
$_lang['setting_imageoptimizer_stuck_minutes_desc'] = 'Reset processing items stuck longer than N minutes.';

$_lang['setting_imageoptimizer_max_memory_limit'] = 'Max memory limit';
$_lang['setting_imageoptimizer_max_memory_limit_desc'] = 'PHP memory_limit bump for large images (e.g. 512M).';
