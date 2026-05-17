<?php
declare(strict_types=1);

$pendingCount = data_pending_reviews_count();
$newBookingsCount = data_new_bookings_count();
$bookingsCount = count(data_load_bookings()['items']);
?>
<section class="admin-card">
  <h2 style="margin-top:0">Панель управления</h2>
  <p class="admin-muted">Управляйте заявками, услугами и отзывами — изменения сразу появятся на сайте.</p>
  <?php if ($newBookingsCount > 0): ?>
    <p class="admin-flash" style="margin-top:1rem">Новых заявок на запись: <strong><?= $newBookingsCount ?></strong></p>
  <?php endif; ?>
  <?php if ($pendingCount > 0): ?>
    <p class="admin-flash" style="margin-top:1rem">Новых отзывов на модерации: <strong><?= $pendingCount ?></strong></p>
  <?php endif; ?>
  <div class="admin-actions" style="margin-top:1rem">
    <a class="admin-btn" href="/admin/?page=settings">Настройки сайта</a>
    <a class="admin-btn" href="/admin/?page=bookings">Записи (<?= $bookingsCount ?>)<?= $newBookingsCount > 0 ? ' · +' . $newBookingsCount : '' ?></a>
    <a class="admin-btn" href="/admin/?page=services">Услуги (<?= count($services) ?>)</a>
    <a class="admin-btn" href="/admin/?page=reviews">Отзывы (<?= count($reviews) ?><?= $pendingCount > 0 ? ', +' . $pendingCount . ' на модерации' : '' ?>)</a>
    <a class="admin-btn admin-btn--ghost" href="<?= e(site_url()) ?>" target="_blank" rel="noopener">Открыть сайт</a>
  </div>
</section>
