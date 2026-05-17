<?php
declare(strict_types=1);

$reviewsData = data_load_reviews();
$reviewItems = $reviewsData['items'];
$pendingData = data_load_pending_reviews();
$pendingItems = $pendingData['items'];
?>
<?php if ($pendingItems !== []): ?>
<section class="admin-card">
  <h2 style="margin-top:0">На модерации (<?= count($pendingItems) ?>)</h2>
  <p class="admin-muted">Отзывы с сайта — одобрите, чтобы они появились в общем списке.</p>
  <table class="admin-table">
    <thead>
      <tr>
        <th>Автор</th>
        <th>Услуга</th>
        <th>Оценка</th>
        <th>Текст</th>
        <th>Дата</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pendingItems as $review): ?>
      <tr>
        <td><?= e((string) $review['author']) ?></td>
        <td><?= e((string) $review['service']) ?></td>
        <td><?= e(review_score_label((float) ($review['rating'] ?? 5))) ?></td>
        <td style="max-width:280px"><?= e((string) $review['text']) ?></td>
        <td><?= e((string) ($review['date'] ?? '')) ?></td>
        <td>
          <div class="admin-actions">
            <form method="post">
              <input type="hidden" name="action" value="review_approve" />
              <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
              <input type="hidden" name="id" value="<?= e((string) $review['id']) ?>" />
              <button type="submit" class="admin-btn">Одобрить</button>
            </form>
            <form method="post" onsubmit="return confirm('Отклонить отзыв?');">
              <input type="hidden" name="action" value="review_reject" />
              <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
              <input type="hidden" name="id" value="<?= e((string) $review['id']) ?>" />
              <button type="submit" class="admin-btn admin-btn--danger">Отклонить</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
<?php endif; ?>

<section class="admin-card">
  <p class="admin-muted" style="margin:0 0 1rem">Средняя оценка и счётчик на сайте считаются автоматически по опубликованным отзывам (сейчас <?= e(reviews_count_label(count($reviewItems))) ?>, средняя оценка <?= e(review_score_label(reviews_average_rating($reviewItems))) ?>).</p>
  <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
    <h2 style="margin:0">Опубликованные отзывы</h2>
    <a class="admin-btn" href="/admin/?page=review-edit">Добавить вручную</a>
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>Автор</th>
        <th>Услуга</th>
        <th>Оценка</th>
        <th>Дата</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($reviewItems === []): ?>
      <tr><td colspan="5" class="admin-muted">Пока нет опубликованных отзывов.</td></tr>
      <?php endif; ?>
      <?php foreach ($reviewItems as $review): ?>
      <tr>
        <td><?= e((string) $review['author']) ?></td>
        <td><?= e((string) $review['service']) ?></td>
        <td><?= e(review_score_label((float) ($review['rating'] ?? 5))) ?></td>
        <td><?= e((string) $review['date']) ?></td>
        <td>
          <div class="admin-actions">
            <a class="admin-btn admin-btn--ghost" href="/admin/?page=review-edit&amp;id=<?= e((string) $review['id']) ?>">Изменить</a>
            <form method="post" onsubmit="return confirm('Удалить отзыв?');">
              <input type="hidden" name="action" value="review_delete" />
              <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
              <input type="hidden" name="id" value="<?= e((string) $review['id']) ?>" />
              <button type="submit" class="admin-btn admin-btn--danger">Удалить</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
