<?php

declare(strict_types=1);

function app_is_installed(): bool
{
    $root = dirname(__DIR__);

    if (is_file($root . '/settings/install.lock')) {
        return true;
    }

    $dbFile = $root . '/settings/db.php';
    $adminFile = $root . '/settings/admin.php';
    if (!is_file($dbFile) || !is_file($adminFile)) {
        return false;
    }

    $admin = require $adminFile;
    if (!is_array($admin)) {
        return false;
    }

    return trim((string) ($admin['password_hash'] ?? '')) !== '';
}

function install_guard_maybe_redirect(): void
{
    if (app_is_installed()) {
        return;
    }

    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $path = parse_url($uri, PHP_URL_PATH);
    $path = is_string($path) ? $path : '/';

    if (preg_match('#^/install(/|$)#', $path) === 1) {
        return;
    }

    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script === 'install.php' || str_starts_with($path, '/install')) {
        return;
    }

    header('Location: /install/', true, 302);
    exit;
}
