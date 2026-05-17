<?php
declare(strict_types=1);

function admin_config(): array
{
    static $config = null;
    if ($config === null) {
        $path = SETTINGS_DIR . '/admin.php';
        if (!is_file($path)) {
            throw new RuntimeException('Файл settings/admin.php не найден. Скопируйте settings/admin.example.php.');
        }
        $config = require $path;
    }

    return $config;
}

function admin_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('blesk_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/admin/',
        'httponly' => true,
        'samesite' => 'Strict',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

function admin_verify_password(string $password, array $config): bool
{
    $hash = $config['password_hash'] ?? '';
    if (is_string($hash) && $hash !== '') {
        return password_verify($password, $hash);
    }

    if (isset($config['password']) && is_string($config['password']) && $config['password'] !== '') {
        return hash_equals($config['password'], $password);
    }

    return false;
}

function admin_is_login_locked(): bool
{
    admin_start_session();

    return (int) ($_SESSION['admin_lock_until'] ?? 0) > time();
}

function admin_register_failed_login(): void
{
    admin_start_session();
    $config = admin_config();
    $max = max(1, (int) ($config['login_max_attempts'] ?? 5));
    $lockout = max(60, (int) ($config['login_lockout_seconds'] ?? 900));

    $attempts = (int) ($_SESSION['admin_login_attempts'] ?? 0) + 1;
    $_SESSION['admin_login_attempts'] = $attempts;

    if ($attempts >= $max) {
        $_SESSION['admin_lock_until'] = time() + $lockout;
        $_SESSION['admin_login_attempts'] = 0;
    }
}

function admin_clear_login_attempts(): void
{
    admin_start_session();
    unset($_SESSION['admin_login_attempts'], $_SESSION['admin_lock_until']);
}

function admin_is_logged_in(): bool
{
    admin_start_session();
    $config = admin_config();

    return !empty($_SESSION[$config['session_key']]);
}

function admin_login(string $username, string $password): bool
{
    if (admin_is_login_locked()) {
        return false;
    }

    $config = admin_config();
    if ($username !== ($config['username'] ?? '') || !admin_verify_password($password, $config)) {
        admin_register_failed_login();

        return false;
    }

    admin_start_session();
    session_regenerate_id(true);
    $_SESSION[$config['session_key']] = true;
    admin_clear_login_attempts();

    return true;
}

function admin_logout(): void
{
    admin_start_session();
    $config = admin_config();
    unset($_SESSION[$config['session_key']]);
    session_regenerate_id(true);
}

function admin_csrf_token(): string
{
    admin_start_session();
    $config = admin_config();
    $key = $config['csrf_key'];

    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
    }

    return $_SESSION[$key];
}

function admin_verify_csrf(?string $token): bool
{
    admin_start_session();
    $config = admin_config();
    $key = $config['csrf_key'];

    return is_string($token)
        && !empty($_SESSION[$key])
        && hash_equals($_SESSION[$key], $token);
}

function admin_require_auth(): void
{
    if (!admin_is_logged_in()) {
        header('Location: /admin/');
        exit;
    }
}

function admin_redirect(string $path, array $query = []): void
{
    $url = '/admin/' . ltrim($path, '/');
    if ($query !== []) {
        $url .= '?' . http_build_query($query);
    }
    header('Location: ' . $url);
    exit;
}
