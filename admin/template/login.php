<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Вход — админка «<?= e($brand_short) ?>»</title>
    <link rel="stylesheet" href="/admin/css/admin.css" />
  </head>
  <body class="admin admin-login">
    <div class="admin-card">
      <h1 style="margin-top:0">Вход в админку</h1>
      <?php if (!empty($adminError)): ?>
        <p class="admin-flash is-error" role="alert"><?= e($adminError) ?></p>
      <?php endif; ?>
      <form method="post" class="admin-form">
        <input type="hidden" name="action" value="login" />
        <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
        <label>
          Логин
          <input type="text" name="username" autocomplete="username" required />
        </label>
        <label>
          Пароль
          <input type="password" name="password" autocomplete="current-password" required />
        </label>
        <button type="submit" class="admin-btn">Войти</button>
      </form>
    </div>
  </body>
</html>
