<?php

defined('MODX_CORE_PATH') || exit;

require_once __DIR__ . '/file_lifecycle.php';
require_once __DIR__ . '/inject.php';

function imageoptimizer_handle_event(modX $modx, string $eventName): void
{
    if (!imageoptimizer_is_enabled($modx)) {
        return;
    }

    switch ($eventName) {
        case 'OnFileManagerUpload':
        case 'OnFileManagerFileAdd':
        case 'OnFileManagerFileCreate':
            imageoptimizer_on_file_add($modx);
            break;
        case 'OnFileManagerFileUpdate':
            imageoptimizer_on_file_update($modx);
            break;
        case 'OnFileManagerFileRemove':
            imageoptimizer_on_file_remove($modx);
            break;
        case 'OnWebPagePrerender':
            imageoptimizer_on_web_page_prerender($modx);
            break;
        case 'OnSiteRefresh':
        case 'OnCacheUpdate':
            imageoptimizer_clear_html_cache($modx);
            break;
    }
}
