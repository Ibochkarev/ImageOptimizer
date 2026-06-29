<?php

defined('MODX_CORE_PATH') || exit;

/**
 * @param mixed $default
 * @return mixed
 */
function imageoptimizer_post(string $key, $default = null)
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];
        return is_array($value) ? $value : trim((string) $value);
    }

    return $default;
}

/**
 * @param array<string, mixed> $payload
 */
function imageoptimizer_json_response(array $payload, int $status = 200): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * @param array<string, mixed>|list<mixed> $data
 */
function imageoptimizer_json_success($data = [], ?int $total = null): void
{
    $payload = ['success' => true, 'data' => $data];
    if ($total !== null) {
        $payload['total'] = $total;
    }
    imageoptimizer_json_response($payload);
}

function imageoptimizer_json_error(string $message, int $status = 200, ?modX $modx = null): void
{
    if ($modx !== null) {
        $message = imageoptimizer_lex_error($modx, $message);
    }
    imageoptimizer_json_response(['success' => false, 'message' => $message], $status);
}

function imageoptimizer_lex_error(modX $modx, string $key): string
{
    $lexKey = 'imageoptimizer.error.' . $key;
    $translated = $modx->lexicon($lexKey);
    if ($translated !== $lexKey && $translated !== '') {
        return $translated;
    }

    return $key;
}

function imageoptimizer_int_post(string $key, int $default = 0): int
{
    return (int) imageoptimizer_post($key, $default);
}

function imageoptimizer_require_permission(modX $modx, string $perm): void
{
    if (!$modx->hasPermission($perm)) {
        imageoptimizer_json_error('permission_denied', 403, $modx);
    }
}

function imageoptimizer_require_mgr_auth(modX $modx): void
{
    $modx->initialize('mgr');
    $modx->getRequest();
    if (!$modx->user || !$modx->user->isAuthenticated('mgr')) {
        imageoptimizer_json_error('unauthorized', 401, $modx);
    }
    $expected = (string) $modx->user->getUserToken($modx->context->get('key'));
    $modAuth = (string) ($_SERVER['HTTP_MODAUTH'] ?? imageoptimizer_post('HTTP_MODAUTH', ''));
    if ($expected === '' || $modAuth === '' || $modAuth !== $expected) {
        imageoptimizer_json_error('unauthorized', 401, $modx);
    }
    imageoptimizer_load_lexicon($modx, 'default');
    imageoptimizer_add_package($modx);
}
