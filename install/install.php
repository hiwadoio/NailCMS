<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/settings/app.php';
require_once dirname(__DIR__) . '/lib/init.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('blesk_install');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/install/',
        'httponly' => true,
        'samesite' => 'Strict',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require_once __DIR__ . '/Installer.php';

header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

const INSTALL_MIN_PHP = '8.1.0';
const INSTALL_MIN_PASSWORD = 8;
const INSTALL_MIN_USERNAME = 3;

$root = ROOT_DIR;
$installer = new Installer($root);

if (!isset($_SESSION['install']) || !is_array($_SESSION['install'])) {
    $_SESSION['install'] = [];
}

if (empty($_SESSION['install_csrf'])) {
    $_SESSION['install_csrf'] = bin2hex(random_bytes(32));
}

if ($installer->isInstalled()) {
    install_render_installed_page();
    exit;
}

$errors = [];
$step = install_resolve_step();
$mode = (string) ($_SESSION['install']['mode'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!install_csrf_verify($_POST['csrf'] ?? '')) {
        $errors[] = 'Сессия установки устарела. Обновите страницу и попробуйте снова.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'back') {
            $step = install_previous_step($step, $mode);
        } else {
            $errors = array_merge($errors, install_requirements_errors());
        }

        if ($errors === [] && $action === 'set_mode') {
            $chosen = (string) ($_POST['mode'] ?? '');
            if (!in_array($chosen, ['empty', 'demo'], true)) {
                $errors[] = 'Выберите вариант установки.';
            } else {
                $_SESSION['install']['mode'] = $chosen;
                $mode = $chosen;
                $step = 'database';
            }
        }

        if ($errors === [] && $action === 'save_database') {
            if ($mode === '') {
                $errors[] = 'Сначала выберите вариант установки.';
            }
            $db = install_collect_db_post();
            $errors = array_merge($errors, install_validate_db($db));
            if ($errors === []) {
                try {
                    $installer->testDatabaseConnection($db);
                    $_SESSION['install']['db'] = $db;
                    $step = $mode === 'empty' ? 'site' : 'admin';
                } catch (Throwable $e) {
                    error_log('Install DB test: ' . $e->getMessage());
                    $errors[] = install_user_error($e, 'Не удалось подключиться к базе данных. Проверьте хост, имя БД, логин и пароль.');
                }
            }
        }

        if ($errors === [] && $action === 'save_site') {
            if ($mode !== 'empty') {
                $errors[] = 'Неверный шаг установки.';
            } else {
                $site = install_collect_site_post();
                $errors = array_merge($errors, install_validate_site($site));
                if ($errors === []) {
                    $_SESSION['install']['site'] = $site;
                    $step = 'admin';
                }
            }
        }

        if ($errors === [] && $action === 'save_admin') {
            $username = trim((string) ($_POST['admin_username'] ?? ''));
            $password = (string) ($_POST['admin_password'] ?? '');
            $passwordConfirm = (string) ($_POST['admin_password_confirm'] ?? '');
            $errors = array_merge($errors, install_validate_admin($username, $password, $passwordConfirm));

            if ($errors === [] && $mode === '') {
                $errors[] = 'Сначала выберите вариант установки.';
            }

            if ($errors === [] && !isset($_SESSION['install']['db'])) {
                $errors[] = 'Укажите параметры подключения к базе данных.';
            }

            if ($errors === [] && $mode === 'empty' && !isset($_SESSION['install']['site'])) {
                $errors[] = 'Заполните данные сайта.';
            }

            if ($errors === [] && !$installer->settingsDirWritable()) {
                $errors[] = 'Каталог settings/ недоступен для записи. Установите права 750 и владельца веб-сервера.';
            }

            if ($errors === []) {
                try {
                    $db = $_SESSION['install']['db'];
                    $site = $mode === 'empty' ? ($_SESSION['install']['site'] ?? []) : [];
                    $installer->install($mode, $db, $site, $username, $password);
                    $_SESSION['install_complete'] = [
                        'username' => $username,
                        'password' => $password,
                        'mode' => $mode,
                    ];
                    unset($_SESSION['install'], $_SESSION['install_csrf']);
                    header('Location: /install/?step=complete', true, 302);
                    exit;
                } catch (Throwable $e) {
                    error_log('Install failed: ' . $e->getMessage());
                    $errors[] = install_user_error($e, 'Ошибка установки. Проверьте права на settings/ и что база данных пустая.');
                }
            }
        }
    }
}

install_render_page($step, $mode, $errors, $installer);

function install_resolve_step(): string
{
    $requested = (string) ($_GET['step'] ?? '');
    if ($requested === 'complete' && isset($_SESSION['install_complete'])) {
        return 'complete';
    }

    $mode = (string) ($_SESSION['install']['mode'] ?? '');
    if ($mode === '') {
        return 'mode';
    }

    if (!isset($_SESSION['install']['db'])) {
        return 'database';
    }

    if ($mode === 'empty' && !isset($_SESSION['install']['site'])) {
        return 'site';
    }

    return 'admin';
}

function install_steps_for_mode(string $mode): array
{
    if ($mode === 'demo') {
        return ['mode', 'database', 'admin'];
    }

    return ['mode', 'database', 'site', 'admin'];
}

function install_previous_step(string $current, string $mode): string
{
    $steps = install_steps_for_mode($mode);
    $index = array_search($current, $steps, true);
    if ($index === false || $index === 0) {
        return $steps[0];
    }

    $prev = $steps[$index - 1];
    if ($prev === 'database') {
        unset($_SESSION['install']['db']);
    }
    if ($prev === 'site') {
        unset($_SESSION['install']['site']);
    }
    if ($prev === 'mode') {
        unset($_SESSION['install']['mode'], $_SESSION['install']['db'], $_SESSION['install']['site']);
    }

    return $prev;
}

function install_csrf_token(): string
{
    return (string) ($_SESSION['install_csrf'] ?? '');
}

function install_csrf_verify(string $token): bool
{
    $expected = install_csrf_token();

    return $expected !== '' && hash_equals($expected, $token);
}

function install_h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function install_collect_db_post(): array
{
    return [
        'host' => trim((string) ($_POST['db_host'] ?? 'localhost')),
        'port' => (int) ($_POST['db_port'] ?? 3306),
        'database' => trim((string) ($_POST['db_name'] ?? '')),
        'username' => trim((string) ($_POST['db_user'] ?? '')),
        'password' => (string) ($_POST['db_password'] ?? ''),
        'charset' => 'utf8mb4',
    ];
}

function install_collect_site_post(): array
{
    return [
        'site_url' => trim((string) ($_POST['site_url'] ?? '')),
        'site_name' => trim((string) ($_POST['site_name'] ?? '')),
        'site_description' => trim((string) ($_POST['site_description'] ?? '')),
        'site_title' => trim((string) ($_POST['site_title'] ?? '')),
    ];
}

function install_validate_db(array $db): array
{
    $errors = [];
    if ($db['database'] === '') {
        $errors[] = 'Укажите имя базы данных.';
    }
    if ($db['username'] === '') {
        $errors[] = 'Укажите пользователя MySQL.';
    }
    if ($db['port'] < 1 || $db['port'] > 65535) {
        $errors[] = 'Некорректный порт MySQL.';
    }

    return $errors;
}

function install_validate_site(array $site): array
{
    $errors = [];
    $url = (string) ($site['site_url'] ?? '');
    if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) === false) {
        $errors[] = 'Укажите корректный URL сайта (например, https://example.ru).';
    }

    return $errors;
}

function install_validate_admin(string $username, string $password, string $confirm): array
{
    $errors = [];
    if (mb_strlen($username) < INSTALL_MIN_USERNAME) {
        $errors[] = 'Логин администратора: не менее ' . INSTALL_MIN_USERNAME . ' символов.';
    }
    if (strlen($password) < INSTALL_MIN_PASSWORD) {
        $errors[] = 'Пароль: не менее ' . INSTALL_MIN_PASSWORD . ' символов.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Пароли не совпадают.';
    }

    return $errors;
}

function install_requirements(): array
{
    return [
        'php' => version_compare(PHP_VERSION, INSTALL_MIN_PHP, '>='),
        'pdo_mysql' => extension_loaded('pdo_mysql'),
        'mbstring' => extension_loaded('mbstring'),
        'json' => extension_loaded('json'),
        'settings_writable' => is_dir(ROOT_DIR . '/settings') && is_writable(ROOT_DIR . '/settings'),
    ];
}

function install_requirements_errors(): array
{
    $requirements = install_requirements();
    $errors = [];

    if (!$requirements['php']) {
        $errors[] = 'Требуется PHP ' . INSTALL_MIN_PHP . ' или новее (сейчас ' . PHP_VERSION . ').';
    }
    if (!$requirements['pdo_mysql']) {
        $errors[] = 'Не найдено расширение pdo_mysql.';
    }
    if (!$requirements['mbstring']) {
        $errors[] = 'Не найдено расширение mbstring.';
    }
    if (!$requirements['json']) {
        $errors[] = 'Не найдено расширение json.';
    }
    if (!$requirements['settings_writable']) {
        $errors[] = 'Каталог settings/ недоступен для записи. Установите права 750.';
    }

    return $errors;
}

function install_user_error(Throwable $e, string $fallback): string
{
    if ($e instanceof RuntimeException || $e instanceof InvalidArgumentException) {
        return $e->getMessage();
    }

    return $fallback;
}

function install_render_installed_page(): void
{
    ?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Установка завершена</title>
  <link rel="stylesheet" href="/install/install.css" />
</head>
<body class="install-body">
  <div class="install-wrap">
    <div class="install-card">
      <header class="install-header">
        <h1>Сайт уже установлен</h1>
        <p>Мастер установки недоступен. Удалите каталог <code>install/</code>, если он ещё остался на сервере.</p>
      </header>
      <p><a class="install-btn install-btn--primary" href="/">Перейти на сайт</a>
         <a class="install-btn install-btn--secondary" href="/admin/">Войти в админку</a></p>
    </div>
  </div>
</body>
</html>
    <?php
}

function install_render_page(string $step, string $mode, array $errors, Installer $installer): void
{
    $requirements = install_requirements();
    $requirementsOk = !in_array(false, $requirements, true);
    $complete = $_SESSION['install_complete'] ?? null;

    if ($step === 'complete' && is_array($complete)) {
        install_render_complete($complete);
        unset($_SESSION['install_complete']);
        return;
    }

    $steps = $mode !== '' ? install_steps_for_mode($mode) : ['mode', 'database', 'site', 'admin'];
    $stepIndex = array_search($step, $steps, true);
    if ($stepIndex === false) {
        $stepIndex = 0;
    }

    $db = $_SESSION['install']['db'] ?? install_collect_db_post();
    $site = $_SESSION['install']['site'] ?? install_collect_site_post();

    ?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Установка — салон «Блеск»</title>
  <link rel="stylesheet" href="/install/install.css" />
</head>
<body class="install-body">
  <div class="install-wrap">
    <div class="install-card">
      <header class="install-header">
        <h1>Установка сайта</h1>
        <p>Мастер настройки для виртуального хостинга</p>
      </header>

      <nav class="install-steps" aria-label="Шаги установки">
        <?php foreach ($steps as $i => $code): ?>
          <?php
            $class = 'install-step';
            if ($i === $stepIndex) {
                $class .= ' is-active';
            } elseif ($i < $stepIndex) {
                $class .= ' is-done';
            }
            $labels = [
                'mode' => 'Режим',
                'database' => 'База',
                'site' => 'Сайт',
                'admin' => 'Админ',
            ];
            ?>
        <span class="<?= install_h($class) ?>"><?= install_h($labels[$code] ?? $code) ?></span>
        <?php endforeach; ?>
      </nav>

      <?php if (!$requirementsOk && $step === 'mode'): ?>
        <div class="install-alert install-alert--warn">
          <strong>Проверьте окружение:</strong>
          <ul class="install-req">
            <li class="<?= $requirements['php'] ? 'is-ok' : 'is-fail' ?>">
              PHP <?= INSTALL_MIN_PHP ?>+ (сейчас <?= install_h(PHP_VERSION) ?>)
            </li>
            <li class="<?= $requirements['pdo_mysql'] ? 'is-ok' : 'is-fail' ?>">Расширение pdo_mysql</li>
            <li class="<?= $requirements['mbstring'] ? 'is-ok' : 'is-fail' ?>">Расширение mbstring</li>
            <li class="<?= $requirements['json'] ? 'is-ok' : 'is-fail' ?>">Расширение json</li>
            <li class="<?= $requirements['settings_writable'] ? 'is-ok' : 'is-fail' ?>">
              Запись в каталог settings/
            </li>
          </ul>
        </div>
      <?php endif; ?>

      <?php foreach ($errors as $error): ?>
        <div class="install-alert install-alert--error"><?= install_h($error) ?></div>
      <?php endforeach; ?>

      <?php if ($step === 'mode'): ?>
        <form method="post" action="/install/">
          <input type="hidden" name="csrf" value="<?= install_h(install_csrf_token()) ?>" />
          <input type="hidden" name="action" value="set_mode" />
          <p>Выберите, как развернуть сайт:</p>
          <div class="install-choice">
            <label>
              <input type="radio" name="mode" value="empty" <?= $mode === 'empty' ? 'checked' : '' ?> required />
              <strong>Пустой сайт</strong>
              <span>Создаётся структура БД без услуг и отзывов. Укажете URL, название и описание; остальное — в админке.</span>
            </label>
            <label>
              <input type="radio" name="mode" value="demo" <?= $mode === 'demo' ? 'checked' : '' ?> />
              <strong>Сайт с тестовыми данными</strong>
              <span>Импорт демо-настроек, 6 услуг и 6 отзывов из database/schema.sql.</span>
            </label>
          </div>
          <div class="install-actions">
            <button type="submit" class="install-btn install-btn--primary" <?= $requirementsOk ? '' : 'disabled' ?>>
              Далее
            </button>
          </div>
        </form>

      <?php elseif ($step === 'database'): ?>
        <form method="post" action="/install/">
          <input type="hidden" name="csrf" value="<?= install_h(install_csrf_token()) ?>" />
          <input type="hidden" name="action" value="save_database" />
          <p>Укажите данные для подключения к MySQL. База должна быть уже создана в панели хостинга.</p>
          <div class="install-field">
            <label for="db_host">Хост</label>
            <input id="db_host" name="db_host" value="<?= install_h((string) $db['host']) ?>" required />
          </div>
          <div class="install-field">
            <label for="db_port">Порт</label>
            <input id="db_port" name="db_port" type="number" value="<?= (int) $db['port'] ?>" required />
          </div>
          <div class="install-field">
            <label for="db_name">Имя базы данных</label>
            <input id="db_name" name="db_name" value="<?= install_h((string) $db['database']) ?>" required />
          </div>
          <div class="install-field">
            <label for="db_user">Пользователь</label>
            <input id="db_user" name="db_user" value="<?= install_h((string) $db['username']) ?>" required autocomplete="off" />
          </div>
          <div class="install-field">
            <label for="db_password">Пароль</label>
            <input id="db_password" name="db_password" type="password" value="<?= install_h((string) $db['password']) ?>" autocomplete="new-password" />
          </div>
          <div class="install-actions">
            <button type="submit" name="action" value="back" class="install-btn install-btn--secondary" formnovalidate>
              Назад
            </button>
            <button type="submit" class="install-btn install-btn--primary">Проверить и продолжить</button>
          </div>
        </form>

      <?php elseif ($step === 'site'): ?>
        <form method="post" action="/install/">
          <input type="hidden" name="csrf" value="<?= install_h(install_csrf_token()) ?>" />
          <input type="hidden" name="action" value="save_site" />
          <p>Основные данные сайта. Пустые SEO-поля можно заполнить позже в админке — пустые meta-теги на сайте не выводятся.</p>
          <div class="install-field">
            <label for="site_url">Адрес сайта (URL)</label>
            <input id="site_url" name="site_url" type="url" placeholder="https://example.ru"
              value="<?= install_h((string) $site['site_url']) ?>" />
            <small>Без слэша в конце. Используется для canonical и sitemap.</small>
          </div>
          <div class="install-field">
            <label for="site_name">Название сайта</label>
            <input id="site_name" name="site_name" value="<?= install_h((string) $site['site_name']) ?>" />
          </div>
          <div class="install-field">
            <label for="site_title">Заголовок (title)</label>
            <input id="site_title" name="site_title" value="<?= install_h((string) $site['site_title']) ?>" />
            <small>Если пусто — будет совпадать с названием.</small>
          </div>
          <div class="install-field">
            <label for="site_description">Описание</label>
            <textarea id="site_description" name="site_description" rows="3"><?= install_h((string) $site['site_description']) ?></textarea>
          </div>
          <div class="install-actions">
            <button type="submit" name="action" value="back" class="install-btn install-btn--secondary" formnovalidate>Назад</button>
            <button type="submit" class="install-btn install-btn--primary">Далее</button>
          </div>
        </form>

      <?php elseif ($step === 'admin'): ?>
        <form method="post" action="/install/">
          <input type="hidden" name="csrf" value="<?= install_h(install_csrf_token()) ?>" />
          <input type="hidden" name="action" value="save_admin" />
          <p>Логин и пароль для входа в <code>/admin/</code>. Пароль будет сохранён в виде хеша.</p>
          <div class="install-field">
            <label for="admin_username">Логин администратора</label>
            <input id="admin_username" name="admin_username" value="<?= install_h((string) ($_POST['admin_username'] ?? '')) ?>" required autocomplete="username" />
          </div>
          <div class="install-field">
            <label for="admin_password">Пароль</label>
            <input id="admin_password" name="admin_password" type="password" required autocomplete="new-password" minlength="<?= INSTALL_MIN_PASSWORD ?>" />
          </div>
          <div class="install-field">
            <label for="admin_password_confirm">Повтор пароля</label>
            <input id="admin_password_confirm" name="admin_password_confirm" type="password" required autocomplete="new-password" minlength="<?= INSTALL_MIN_PASSWORD ?>" />
          </div>
          <div class="install-actions">
            <button type="submit" name="action" value="back" class="install-btn install-btn--secondary" formnovalidate>Назад</button>
            <button type="submit" class="install-btn install-btn--primary">Установить</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
    <?php
}

function install_render_complete(array $complete): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    $username = (string) ($complete['username'] ?? '');
    $password = (string) ($complete['password'] ?? '');
    $modeLabel = ($complete['mode'] ?? '') === 'demo' ? 'с тестовыми данными' : 'пустой';
    ?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Установка завершена</title>
  <link rel="stylesheet" href="/install/install.css" />
</head>
<body class="install-body">
  <div class="install-wrap">
    <div class="install-card">
      <header class="install-header">
        <h1>Спасибо за установку!</h1>
        <p>Сайт установлен (режим: <?= install_h($modeLabel) ?>).</p>
      </header>

      <div class="install-alert install-alert--success">
        Установка прошла успешно. Сохраните данные для входа в админку.
      </div>

      <div class="install-creds">
        <div>Админка: <a href="/admin/">/admin/</a></div><br />
        Логин: <?= install_h($username) ?><br />
        Пароль: <?= install_h($password) ?>
      </div>

      <h2>Рекомендации</h2>
      <ul class="install-list">
        <li><strong>Удалите</strong> каталог <code>install/</code> и файл <code>install/install.php</code> после проверки сайта.</li>
        <li>Права: <code>settings/</code>, <code>lib/</code>, <code>database/</code> — <strong>750</strong>; <code>settings/db.php</code>, <code>settings/admin.php</code> — <strong>640</strong>; <code>assets/images/services/</code> — <strong>775</strong>.</li>
        <li>Откройте главную страницу и войдите в админку. В разделе «Настройки» заполните контакты и SEO.</li>
        <?php if (($complete['mode'] ?? '') === 'empty'): ?>
        <li>Услуги и отзывы пока пустые — добавьте их в админке.</li>
        <?php endif; ?>
      </ul>

      <div class="install-alert install-alert--warn" style="margin-top: 1rem;">
        Не оставляйте мастер установки на сервере: любой сможет переустановить сайт, пока доступен <code>/install/</code>.
      </div>

      <div class="install-actions">
        <a class="install-btn install-btn--primary" href="/">Открыть сайт</a>
        <a class="install-btn install-btn--secondary" href="/admin/">Войти в админку</a>
      </div>
    </div>
  </div>
</body>
</html>
    <?php
}
