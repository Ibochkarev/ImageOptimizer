<?php

/** @var modX $modx */

require_once MODX_CORE_PATH . 'components/imageoptimizer/include/helpers.php';
require_once MODX_CORE_PATH . 'components/imageoptimizer/include/events.php';
imageoptimizer_handle_event($modx, $modx->event->name);
