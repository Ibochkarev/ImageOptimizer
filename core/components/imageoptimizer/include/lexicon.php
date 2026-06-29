<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @param modX $modx
 */
function imageoptimizer_load_lexicon(modX $modx, string $topic = 'default'): void
{
    $modx->lexicon->load('imageoptimizer:' . $topic);
}

/**
 * @return array<string, string>
 */
function imageoptimizer_build_mgr_lexicon(modX $modx): array
{
    imageoptimizer_load_lexicon($modx, 'default');
    imageoptimizer_load_lexicon($modx, 'setting');

    $entries = [];
    foreach ($modx->lexicon->fetch('imageoptimizer') as $key => $value) {
        if (is_string($value)) {
            $entries[$key] = $value;
        }
    }

    return $entries;
}
