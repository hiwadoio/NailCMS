<?php
declare(strict_types=1);

$id = trim((string) ($_GET['id'] ?? ''));
$isNew = $id === '';
$service = null;

if (!$isNew) {
    if (!security_valid_id($id)) {
        admin_set_error('Некорректный идентификатор услуги.');
        admin_redirect('?page=services');
    }
    $service = admin_find_service($id);
    if ($service === null) {
        admin_set_error('Услуга не найдена.');
        admin_redirect('?page=services');
    }
}

$emptyService = [
    'id' => '',
    'name' => '',
    'price' => 0,
    'price_from' => false,
    'image' => '',
    'text' => '',
    'detail' => '',
    'schema_description' => '',
];
$item = $service ?? $emptyService;
?>
<section class="admin-card">
  <h2 style="margin-top:0"><?= $isNew ? 'Новая услуга' : 'Редактирование услуги' ?></h2>
  <form method="post" enctype="multipart/form-data" class="admin-form">
    <input type="hidden" name="action" value="service_save" />
    <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
    <input type="hidden" name="id" value="<?= e((string) $item['id']) ?>" />

    <div class="admin-form__row admin-form__row--2">
      <label>
        Название *
        <input type="text" name="name" value="<?= e((string) $item['name']) ?>" required />
      </label>
      <label>
        Цена (число, ₽) *
        <input type="number" name="price" min="0" step="1" value="<?= e((string) ($item['price'] ?? 0)) ?>" required />
      </label>
    </div>

    <label class="admin-check">
      <input type="checkbox" name="price_from" value="1" <?= !empty($item['price_from']) ? 'checked' : '' ?> />
      Показывать «от … ₽»
    </label>

    <label>
      Путь к изображению
      <input type="text" name="image" value="<?= e((string) ($item['image'] ?? '')) ?>" placeholder="assets/images/services/example.jpg" />
    </label>

    <label>
      Загрузить новое фото (JPG, PNG, WebP)
      <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" />
    </label>

    <?php if (!empty($item['image'])): ?>
      <p><img class="admin-thumb" src="<?= e(asset_url((string) $item['image'])) ?>" alt="" style="width:120px;height:90px" /></p>
    <?php endif; ?>

    <label>
      Краткое описание (карточка)
      <textarea name="text" required><?= e((string) ($item['text'] ?? '')) ?></textarea>
    </label>

    <label>
      Подробности (модальное окно)
      <textarea name="detail"><?= e((string) ($item['detail'] ?? '')) ?></textarea>
    </label>

    <label>
      Описание для поисковиков (Schema.org)
      <textarea name="schema_description"><?= e((string) ($item['schema_description'] ?? '')) ?></textarea>
    </label>

    <div class="admin-actions">
      <button type="submit" class="admin-btn">Сохранить</button>
      <a class="admin-btn admin-btn--ghost" href="/admin/?page=services">Отмена</a>
    </div>
  </form>
</section>
