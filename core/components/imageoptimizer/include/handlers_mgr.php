<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_handle_stats_summary(modX $modx): void
{
    $counts = imageoptimizer_queue_count_by_status($modx);
    $encoders = imageoptimizer_detect_encoders($modx);
    imageoptimizer_json_success([
        'queue' => $counts,
        'readiness' => imageoptimizer_server_readiness_score($encoders),
        'encoders' => $encoders,
        'disk_warn_gb' => (float) imageoptimizer_get_setting($modx, 'disk_warn_gb', 1),
    ]);
}

function imageoptimizer_handle_server_check(modX $modx): void
{
    $encoders = imageoptimizer_detect_encoders($modx);
    imageoptimizer_json_success([
        'encoders' => $encoders,
        'readiness' => imageoptimizer_server_readiness_score($encoders),
        'php_version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
    ]);
}

function imageoptimizer_handle_compatibility_list(modX $modx): void
{
    imageoptimizer_json_success([
        'thumb3x' => imageoptimizer_package_installed($modx, 'thumb3x'),
        'pthumb' => imageoptimizer_package_installed($modx, 'phpthumbof') || imageoptimizer_package_installed($modx, 'pthumb'),
        'minishop3' => imageoptimizer_package_installed($modx, 'minishop3'),
        'vuetools' => imageoptimizer_package_installed($modx, 'vuetools'),
    ]);
}

/**
 * @return list<int>
 */
function imageoptimizer_parse_id_list(mixed $value): array
{
    if (is_string($value)) {
        $value = explode(',', $value);
    }
    if (!is_array($value)) {
        return [];
    }
    $ids = [];
    foreach ($value as $id) {
        $id = (int) $id;
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    return $ids;
}

function imageoptimizer_package_installed(modX $modx, string $name): bool
{
    $transport = $modx->getObject('transport.modTransportPackage', ['package_name' => $name]);
    if ($transport && (string) $transport->get('installed') !== '') {
        return true;
    }

    return is_dir(rtrim((string) $modx->getOption('core_path'), '/') . '/components/' . $name);
}
