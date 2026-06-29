<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @return array<string, array{0: callable(modX): void, 1: string|null}>
 */
function imageoptimizer_action_handlers(): array
{
    return [
        'queue/list' => ['imageoptimizer_handle_queue_list', 'imageoptimizer_view'],
        'queue/retry' => ['imageoptimizer_handle_queue_retry', 'imageoptimizer_run'],
        'queue/rebuild' => ['imageoptimizer_handle_queue_rebuild', 'imageoptimizer_run'],
        'queue/clear' => ['imageoptimizer_handle_queue_clear', 'imageoptimizer_run'],
        'queue/reset_stuck' => ['imageoptimizer_handle_queue_reset_stuck', 'imageoptimizer_run'],
        'queue/process' => ['imageoptimizer_handle_queue_process', 'imageoptimizer_run'],
        'stats/summary' => ['imageoptimizer_handle_stats_summary', 'imageoptimizer_view'],
        'settings/get' => ['imageoptimizer_handle_settings_get', 'imageoptimizer_view'],
        'settings/update' => ['imageoptimizer_handle_settings_update', 'imageoptimizer_settings'],
        'server/check' => ['imageoptimizer_handle_server_check', 'imageoptimizer_view'],
        'compatibility/list' => ['imageoptimizer_handle_compatibility_list', 'imageoptimizer_view'],
    ];
}
