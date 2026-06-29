<?php

defined('MODX_CORE_PATH') || exit;

function imageoptimizer_handle_settings_get(modX $modx): void
{
    imageoptimizer_json_success(imageoptimizer_settings_export($modx));
}

function imageoptimizer_handle_settings_update(modX $modx): void
{
    $payload = imageoptimizer_post('settings', []);
    if (is_string($payload)) {
        $decoded = json_decode($payload, true);
        $payload = is_array($decoded) ? $decoded : [];
    }
    if (!is_array($payload)) {
        $payload = [];
    }
    $allowed = imageoptimizer_settings_keys();
    $updated = 0;
    foreach ($payload as $key => $value) {
        if (!in_array($key, $allowed, true)) {
            continue;
        }
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        if ($key === 'variant_pattern' && is_string($value) && !imageoptimizer_is_valid_variant_pattern($value)) {
            continue;
        }
        $optionKey = 'imageoptimizer_' . $key;
        $setting = $modx->getObject('modSystemSetting', ['key' => $optionKey]);
        if (!$setting) {
            continue;
        }
        $setting->set('value', is_scalar($value) ? (string) $value : json_encode($value));
        if ($setting->save()) {
            $updated++;
            $modx->setOption($optionKey, $setting->get('value'));
        }
    }
    imageoptimizer_clear_html_cache($modx);
    imageoptimizer_json_success(['updated' => $updated]);
}

/**
 * @return array<string, mixed>
 */
function imageoptimizer_settings_export(modX $modx): array
{
    $result = [];
    foreach (imageoptimizer_settings_keys() as $key) {
        $result[$key] = imageoptimizer_get_setting($modx, $key);
    }

    return $result;
}
