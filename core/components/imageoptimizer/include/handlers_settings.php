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
            $setting = $modx->newObject('modSystemSetting');
            $setting->set('key', $optionKey);
            $setting->set('namespace', 'imageoptimizer');
            $setting->set('area', 'default');
            $setting->set('xtype', 'textfield');
        }
        $setting->set('value', is_scalar($value) ? (string) $value : json_encode($value));
        if ($setting->save()) {
            $updated++;
            $modx->setOption($optionKey, $setting->get('value'));
        }
    }
    $modx->cacheManager->refresh(['system_settings' => []]);
    imageoptimizer_clear_html_cache($modx);
    imageoptimizer_json_success(['updated' => $updated]);
}

/**
 * @return array<string, mixed>
 */
function imageoptimizer_settings_export(modX $modx): array
{
    $boolKeys = imageoptimizer_settings_bool_keys();
    $result = [];
    foreach (imageoptimizer_settings_keys() as $key) {
        $value = imageoptimizer_get_setting($modx, $key);
        $result[$key] = in_array($key, $boolKeys, true) ? (bool) $value : $value;
    }

    return $result;
}
