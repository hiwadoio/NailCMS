<?php
declare(strict_types=1);
/** @var string $page */
/** @var string $templatePath */

$adminPendingReviews = data_pending_reviews_count();
$adminNewBookings = data_new_bookings_count();
?>
<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Админка — <?= e($site_name) ?></title>
    <link rel="stylesheet" href="/admin/css/admin.css" />
  </head>
  <body class="admin">
    <div class="admin-wrap">
      <header class="admin-header">
        <h1>Админка «<?= e($brand_short) ?>»</h1>
        <nav class="admin-nav" aria-label="Разделы админки">
          <a href="/admin/" class="<?= $page === 'dashboard' ? 'is-active' : '' ?>">Главная</a>
          <a href="/admin/?page=settings" class="<?= $page === 'settings' ? 'is-active' : '' ?>">Настройки</a>
          <a href="/admin/?page=services" class="<?= str_starts_with($page, 'service') ? 'is-active' : '' ?>">Услуги</a>
          <a href="/admin/?page=bookings" class="<?= $page === 'bookings' ? 'is-active' : '' ?>">Записи<?= $adminNewBookings > 0 ? ' (' . $adminNewBookings . ')' : '' ?></a>
          <?php
            $adminPublishedReviews = count($reviews);
            $adminReviewsNavLabel = 'Отзывы';
            if ($adminPendingReviews > 0) {
                $adminReviewsNavLabel .= ' (+' . $adminPendingReviews . ')';
            } elseif ($adminPublishedReviews > 0) {
                $adminReviewsNavLabel .= ' (' . $adminPublishedReviews . ')';
            }
            ?>
          <a href="/admin/?page=reviews" class="<?= str_starts_with($page, 'review') ? 'is-active' : '' ?>"><?= e($adminReviewsNavLabel) ?></a>
          <a href="<?= e(site_url()) ?>" target="_blank" rel="noopener">Сайт</a>
          <form method="post" style="display:inline">
            <input type="hidden" name="action" value="logout" />
            <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
            <button type="submit" class="admin-nav">Выйти</button>
          </form>
        </nav>
      </header>

      <?php if (!empty($adminFlash)): ?>
        <p class="admin-flash" role="status"><?= e($adminFlash) ?></p>
      <?php endif; ?>
      <?php if (!empty($adminError)): ?>
        <p class="admin-flash is-error" role="alert"><?= e($adminError) ?></p>
      <?php endif; ?>

      <?php require $templatePath; ?>
    </div>
  </body>
</html>
