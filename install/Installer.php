<?php
declare(strict_types=1);

final class Installer
{
    private string $root;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function isInstalled(): bool
    {
        require_once $this->root . '/lib/install-guard.php';

        return app_is_installed();
    }

    public function siteSettingKeys(): array
    {
        require_once $this->root . '/lib/data.php';

        return data_site_setting_keys();
    }

    public function testDatabaseConnection(array $config): void
    {
        $pdo = $this->createPdo($config);
        $pdo->query('SELECT 1');
        $this->assertDatabaseEmpty($pdo);
    }

    public function install(
        string $mode,
        array $dbConfig,
        array $siteInput,
        string $adminUsername,
        string $adminPassword
    ): void {
        if (!in_array($mode, ['empty', 'demo'], true)) {
            throw new InvalidArgumentException('Некорректный режим установки.');
        }

        $pdo = $this->createPdo($dbConfig);
        $this->assertDatabaseEmpty($pdo);

        if ($mode === 'demo') {
            $this->executeSqlFile($pdo, $this->root . '/database/schema.sql');
        } else {
            $this->executeSqlFile($pdo, $this->root . '/install/schema-tables.sql');
            $this->insertEmptySiteSettings($pdo, $siteInput);
        }

        $this->writeDbConfig($dbConfig);
        $this->writeAdminConfig($adminUsername, $adminPassword);
        $this->writeInstallLock($mode);
    }

    public function settingsDirWritable(): bool
    {
        $dir = $this->root . '/settings';
        if (!is_dir($dir)) {
            return false;
        }

        return is_writable($dir);
    }

    private function createPdo(array $config): PDO
    {
        $host = (string) ($config['host'] ?? 'localhost');
        $port = (int) ($config['port'] ?? 3306);
        $database = (string) ($config['database'] ?? '');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        $charset = (string) ($config['charset'] ?? 'utf8mb4');
        if (!in_array($charset, ['utf8mb4', 'utf8'], true)) {
            throw new InvalidArgumentException('Недопустимая кодировка подключения.');
        }

        if ($database === '' || $username === '') {
            throw new RuntimeException('Укажите имя базы данных и пользователя MySQL.');
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

        return new PDO($dsn, $username, $password, $options);
    }

    private function assertDatabaseEmpty(PDO $pdo): void
    {
        $tables = ['site_settings', 'services', 'reviews', 'pending_reviews', 'bookings'];
        foreach ($tables as $table) {
            try {
                $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            } catch (PDOException) {
                continue;
            }

            if ($count > 0) {
                throw new RuntimeException(
                    'В базе уже есть данные (таблица ' . $table . '). Создайте новую пустую базу или очистите существующую.'
                );
            }
        }
    }

    private function executeSqlFile(PDO $pdo, string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException('SQL-файл не найден: ' . $path);
        }

        $sql = (string) file_get_contents($path);
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql) ?? $sql;

        $buffer = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($inString) {
                $buffer .= $char;
                if ($char === $stringChar && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $inString = false;
                }
                continue;
            }

            if ($char === "'" || $char === '"') {
                $inString = true;
                $stringChar = $char;
                $buffer .= $char;
                continue;
            }

            if ($char === ';') {
                $statement = trim($buffer);
                if ($statement !== '') {
                    $pdo->exec($statement);
                }
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $statement = trim($buffer);
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }

    private function insertEmptySiteSettings(PDO $pdo, array $siteInput): void
    {
        $siteUrl = rtrim(trim((string) ($siteInput['site_url'] ?? '')), '/');
        $siteName = trim((string) ($siteInput['site_name'] ?? ''));
        $siteDescription = trim((string) ($siteInput['site_description'] ?? ''));
        $siteTitle = trim((string) ($siteInput['site_title'] ?? ''));
        if ($siteTitle === '' && $siteName !== '') {
            $siteTitle = $siteName;
        }

        $values = [];
        foreach ($this->siteSettingKeys() as $key) {
            $values[$key] = '';
        }

        $values['site_url'] = $siteUrl;
        $values['site_name'] = $siteName;
        $values['site_description'] = $siteDescription;
        $values['site_title'] = $siteTitle;
        $values['theme_color'] = '#f2fae6';

        $stmt = $pdo->prepare(
            'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)'
        );

        foreach ($values as $key => $value) {
            $stmt->execute([$key, $value]);
        }
    }

    private function writeDbConfig(array $config): void
    {
        $data = [
            'host' => (string) ($config['host'] ?? 'localhost'),
            'port' => (int) ($config['port'] ?? 3306),
            'database' => (string) ($config['database'] ?? ''),
            'username' => (string) ($config['username'] ?? ''),
            'password' => (string) ($config['password'] ?? ''),
            'charset' => (string) ($config['charset'] ?? 'utf8mb4'),
        ];

        $this->writePhpReturnFile($this->root . '/settings/db.php', $data);
    }

    private function writeAdminConfig(string $username, string $plainPassword): void
    {
        $data = [
            'username' => trim($username),
            'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'session_key' => 'blesk_admin_auth',
            'csrf_key' => 'blesk_admin_csrf',
            'login_max_attempts' => 5,
            'login_lockout_seconds' => 900,
        ];

        $this->writePhpReturnFile($this->root . '/settings/admin.php', $data);
    }

    private function writeInstallLock(string $mode): void
    {
        $this->writePhpReturnFile($this->root . '/settings/install.lock', [
            'installed_at' => date('c'),
            'mode' => $mode,
        ]);
    }

    private function writePhpReturnFile(string $path, array $data): void
    {
        $exported = var_export($data, true);
        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn {$exported};\n";
        $this->writeFile($path, $content);
    }

    private function writeFile(string $path, string $content): void
    {
        if (@file_put_contents($path, $content, LOCK_EX) === false) {
            throw new RuntimeException('Не удалось записать файл: ' . $path);
        }
        @chmod($path, 0640);
    }
}
