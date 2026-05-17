<?php
declare(strict_types=1);

/**
 * Скопируйте в admin.php.
 *
 * Пароль (обязательно на продакшене):
 *   php -r "echo password_hash('ваш-пароль', PASSWORD_DEFAULT), PHP_EOL;"
 * Вставьте результат в password_hash и не храните открытый пароль в файле.
 */
return [
    'username' => 'manage',
    'password_hash' => '',
    'session_key' => 'blesk_admin_auth',
    'csrf_key' => 'blesk_admin_csrf',
    'login_max_attempts' => 5,
    'login_lockout_seconds' => 900,
];
