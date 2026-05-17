<?php
declare(strict_types=1);

function security_send_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function security_send_noindex_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');
}

function security_json_headers(): void
{
    security_send_headers();
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
}

function security_json_response(array $payload, int $code = 200): never
{
    security_json_headers();
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function security_is_same_origin_request(bool $strict = false): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return !$strict;
    }

    $secFetchSite = strtolower((string) ($_SERVER['HTTP_SEC_FETCH_SITE'] ?? ''));
    if ($secFetchSite === 'same-origin') {
        return true;
    }

    $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
    if ($origin !== '') {
        $originHost = parse_url($origin, PHP_URL_HOST);

        return is_string($originHost) && strcasecmp($originHost, $host) === 0;
    }

    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    if ($referer !== '') {
        $refererHost = parse_url($referer, PHP_URL_HOST);

        return is_string($refererHost) && strcasecmp($refererHost, $host) === 0;
    }

    return !$strict;
}

function security_honeypot_filled(?string $value): bool
{
    return trim((string) $value) !== '';
}

function security_valid_id(string $id): bool
{
    return $id !== '' && preg_match('/^[a-z0-9][a-z0-9-]{0,63}$/i', $id) === 1;
}

function security_safe_path(string $path, string $allowedPrefix): bool
{
    $path = str_replace('\\', '/', $path);
    if (str_contains($path, '..') || str_starts_with($path, '/')) {
        return false;
    }

    return str_starts_with($path, rtrim($allowedPrefix, '/') . '/')
        || $path === rtrim($allowedPrefix, '/');
}

function security_rate_limit(string $key, int $maxAttempts, int $windowSeconds): bool
{
    require_once __DIR__ . '/public-session.php';
    public_session_start();

    $now = time();
    $bucketKey = 'rate_' . preg_replace('/[^a-z0-9_]/i', '', $key);
    $bucket = $_SESSION[$bucketKey] ?? ['count' => 0, 'reset' => $now + $windowSeconds];

    if ($now >= (int) ($bucket['reset'] ?? 0)) {
        $bucket = ['count' => 0, 'reset' => $now + $windowSeconds];
    }

    $bucket['count'] = (int) $bucket['count'] + 1;
    $_SESSION[$bucketKey] = $bucket;

    return $bucket['count'] <= $maxAttempts;
}

function security_require_api_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        security_json_response(['ok' => false, 'message' => 'Метод не поддерживается.'], 405);
    }

    if (!security_is_same_origin_request(true)) {
        security_json_response(['ok' => false, 'message' => 'Недопустимый источник запроса.'], 403);
    }
}

function security_require_api_get(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
        security_json_response(['ok' => false, 'message' => 'Метод не поддерживается.'], 405);
    }

    if (!security_is_same_origin_request(true)) {
        security_json_response(['ok' => false, 'message' => 'Недопустимый источник запроса.'], 403);
    }

    if (!security_rate_limit('captcha_fetch', 40, 3600)) {
        security_json_response(['ok' => false, 'message' => 'Слишком много запросов. Попробуйте позже.'], 429);
    }
}

function security_require_public_form(): void
{
    security_require_api_post();

    if (!security_rate_limit('public_form', 12, 3600)) {
        security_json_response(['ok' => false, 'message' => 'Слишком много запросов. Попробуйте позже.'], 429);
    }
}

function security_require_service_choice(string $service, array $services): void
{
    if ($service === '') {
        security_json_response(['ok' => false, 'message' => 'Выберите услугу.'], 422);
    }

    $serviceNames = array_column($services, 'name');
    if (!in_array($service, $serviceNames, true)) {
        security_json_response(['ok' => false, 'message' => 'Выберите услугу из списка.'], 422);
    }
}
