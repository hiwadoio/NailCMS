<?php
declare(strict_types=1);

$bookingItems = data_sort_bookings_for_admin(data_load_bookings()['items']);
$statuses = booking_statuses();
?>
<section class="admin-card">
  <h2 style="margin-top:0">Заявки на запись</h2>
  <p class="admin-muted">Заявки с формы на сайта. Новых: <strong><?= data_new_bookings_count() ?></strong>, всего: <strong><?= count($bookingItems) ?></strong>.</p>

  <?php if ($bookingItems === []): ?>
    <p class="admin-muted" style="margin-top:1rem">Пока нет заявок.</p>
  <?php else: ?>
  <table class="admin-table admin-table--bookings">
    <thead>
      <tr>
        <th>Клиент</th>
        <th>Контакты</th>
        <th>Услуга</th>
        <th>Визит</th>
        <th>Комментарий</th>
        <th>Создана</th>
        <th>Статус</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bookingItems as $booking): ?>
      <?php
        $status = (string) ($booking['status'] ?? BOOKING_STATUS_NEW);
        $badgeClass = booking_status_badge_class($status);
        $phoneDisplay = (string) ($booking['phone'] ?? '');
        $phoneTel = preg_replace('/\D+/', '', $phoneDisplay) ?? '';
      ?>
      <tr>
        <td><?= e((string) ($booking['name'] ?? '')) ?></td>
        <td>
          <a href="tel:<?= e($phoneTel) ?>"><?= e($phoneDisplay) ?></a>
          <?php if (!empty($booking['email'])): ?>
            <br /><a href="mailto:<?= e((string) $booking['email']) ?>"><?= e((string) $booking['email']) ?></a>
          <?php endif; ?>
        </td>
        <td><?= e((string) ($booking['service'] ?? '')) ?></td>
        <td><?= e(booking_format_visit_date((string) ($booking['visit_date'] ?? ''))) ?></td>
        <td style="max-width:220px"><?= e((string) ($booking['comment'] ?? '')) ?: '—' ?></td>
        <td><?= e(booking_format_submitted_at((string) ($booking['submitted_at'] ?? ''))) ?></td>
        <td>
          <span class="admin-badge <?= e($badgeClass) ?>"><?= e(booking_status_label($status)) ?></span>
          <form method="post" class="admin-booking-status-form">
            <input type="hidden" name="action" value="booking_status" />
            <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
            <input type="hidden" name="id" value="<?= e((string) ($booking['id'] ?? '')) ?>" />
            <label class="admin-sr-only" for="status-<?= e((string) ($booking['id'] ?? '')) ?>">Статус</label>
            <select id="status-<?= e((string) ($booking['id'] ?? '')) ?>" name="status" class="admin-booking-status-select" onchange="this.form.submit()">
              <?php foreach ($statuses as $value => $label): ?>
              <option value="<?= e($value) ?>"<?= $status === $value ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
        <td>
          <form method="post" onsubmit="return confirm('Удалить заявку?');">
            <input type="hidden" name="action" value="booking_delete" />
            <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
            <input type="hidden" name="id" value="<?= e((string) ($booking['id'] ?? '')) ?>" />
            <button type="submit" class="admin-btn admin-btn--danger">Удалить</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>
