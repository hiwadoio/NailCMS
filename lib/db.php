<?php
declare(strict_types=1);

function db_config(): array
{
    static $config = null;
    if ($config === null) {
        $path = SETTINGS_DIR . '/db.php';
        if (!is_file($path)) {
            throw new RuntimeException(
                'Файл settings/db.php не найден. Скопируйте settings/db.example.php в settings/db.php.'
            );
        }
        $config = require $path;
    }

    return $config;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = db_config();
    $host = (string) ($config['host'] ?? 'localhost');
    $port = (int) ($config['port'] ?? 3306);
    $database = (string) ($config['database'] ?? '');
    $username = (string) ($config['username'] ?? '');
    $password = (string) ($config['password'] ?? '');
    $charset = (string) ($config['charset'] ?? 'utf8mb4');

    if ($database === '' || $username === '') {
        throw new RuntimeException('Укажите database и username в settings/db.php.');
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci";
    }

    $pdo = new PDO($dsn, $username, $password, $options);

    return $pdo;
}
