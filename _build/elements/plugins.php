<?php

return [
    'ImageOptimizer' => [
        'file' => 'plugin.imageoptimizer',
        'description' => 'WebP/AVIF conversion, queue, and frontend picture injection.',
        'events' => [
            'OnFileManagerUpload' => [],
            'OnFileManagerFileCreate' => [],
            'OnFileManagerFileUpdate' => [],
            'OnFileManagerFileRemove' => [],
            'OnWebPagePrerender' => [],
            'OnSiteRefresh' => [],
            'OnCacheUpdate' => [],
        ],
    ],
];
