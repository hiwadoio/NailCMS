<?php
declare(strict_types=1);

$id = trim((string) ($_GET['id'] ?? ''));
$isNew = $id === '';
$review = null;

if (!$isNew) {
    if (!security_valid_id($id)) {
        admin_set_error('Некорректный идентификатор отзыва.');
        admin_redirect('?page=reviews');
    }
    $review = admin_find_review($id);
    if ($review === null) {
        admin_set_error('Отзыв не найден.');
        admin_redirect('?page=reviews');
    }
}

$emptyReview = [
    'id' => '',
    'author' => '',
    'date' => date('Y-m-d'),
    'service' => '',
    'rating' => 5,
    'text' => '',
];
$item = $review ?? $emptyReview;
?>
<section class="admin-card">
  <h2 style="margin-top:0"><?= $isNew ? 'Новый отзыв' : 'Редактирование отзыва' ?></h2>
  <form method="post" class="admin-form">
    <input type="hidden" name="action" value="review_save" />
    <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
    <input type="hidden" name="id" value="<?= e((string) $item['id']) ?>" />

    <div class="admin-form__row admin-form__row--2">
      <label>
        Автор *
        <input type="text" name="author" value="<?= e((string) $item['author']) ?>" required placeholder="Анна К." />
      </label>
      <label>
        Дата
        <input type="date" name="date" value="<?= e((string) $item['date']) ?>" required />
      </label>
    </div>

    <div class="admin-form__row admin-form__row--2">
      <label>
        Услуга
        <select name="service">
          <?php foreach ($services as $service): ?>
            <option value="<?= e($service['name']) ?>" <?= ($item['service'] ?? '') === $service['name'] ? 'selected' : '' ?>><?= e($service['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        Оценка (1–5)
        <input type="number" name="rating" min="1" max="5" step="0.5" value="<?= e((string) ($item['rating'] ?? 5)) ?>" required />
      </label>
    </div>

    <label>
      Текст отзыва *
      <textarea name="text" required><?= e((string) ($item['text'] ?? '')) ?></textarea>
    </label>

    <div class="admin-actions">
      <button type="submit" class="admin-btn">Сохранить</button>
      <a class="admin-btn admin-btn--ghost" href="/admin/?page=reviews">Отмена</a>
    </div>
  </form>
</section>
