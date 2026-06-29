<?php

declare(strict_types=1);

$_lang = [];

define('MODX_CORE_PATH', dirname(__DIR__) . '/core/');
define('MODX_BASE_PATH', dirname(__DIR__) . '/tests/fixtures/site/');

require_once dirname(__DIR__) . '/tests/Support/TestModx.php';

$coreInclude = MODX_CORE_PATH . 'components/imageoptimizer/include/';

require_once $coreInclude . 'enum_status.php';
require_once $coreInclude . 'settings.php';
require_once $coreInclude . 'paths.php';
require_once $coreInclude . 'responsive.php';
require_once $coreInclude . 'preflight.php';
require_once $coreInclude . 'html_parser.php';
require_once $coreInclude . 'img_skip_rules.php';
require_once $coreInclude . 'picture_builder.php';
require_once $coreInclude . 'html_cache.php';
require_once $coreInclude . 'inject.php';
