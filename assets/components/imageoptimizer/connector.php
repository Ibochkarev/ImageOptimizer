<?php
/**
 * ImageOptimizer JSON connector
 *
 * @package imageoptimizer
 */

ob_start();

if (file_exists(dirname(__FILE__, 4) . '/config.core.php')) {
    require_once dirname(__FILE__, 4) . '/config.core.php';
} else {
    require_once dirname(__FILE__, 5) . '/config.core.php';
}
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

require_once MODX_CORE_PATH . 'components/imageoptimizer/include/paths.php';
require_once imageoptimizer_core_path($modx) . 'include/helpers.php';

imageoptimizer_require_mgr_auth($modx);

$action = (string) imageoptimizer_post('action', '');
$handlers = imageoptimizer_action_handlers();

if ($action === '' || !isset($handlers[$action])) {
    imageoptimizer_json_error('invalid action', 400);
}

[$handler, $perm] = $handlers[$action];
if ($perm !== null) {
    imageoptimizer_require_permission($modx, $perm);
}
$handler($modx);
