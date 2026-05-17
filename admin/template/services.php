<?php declare(strict_types=1); ?>
<section class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
    <h2 style="margin:0">Услуги</h2>
    <a class="admin-btn" href="/admin/?page=service-edit">Добавить услугу</a>
  </div>
  <table class="admin-table">
    <thead>
      <tr>
        <th>Фото</th>
        <th>Название</th>
        <th>Цена</th>
        <th>Действия</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($services as $service): ?>
      <tr>
        <td><img class="admin-thumb" src="/<?= e(ltrim($service['image'], '/')) ?>" alt="" /></td>
        <td><?= e($service['name']) ?></td>
        <td><?= e($service['price_display']) ?></td>
        <td>
          <div class="admin-actions">
            <a class="admin-btn admin-btn--ghost" href="/admin/?page=service-edit&amp;id=<?= e($service['id']) ?>">Изменить</a>
            <form method="post" onsubmit="return confirm('Удалить услугу?');">
              <input type="hidden" name="action" value="service_delete" />
              <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />
              <input type="hidden" name="id" value="<?= e($service['id']) ?>" />
              <button type="submit" class="admin-btn admin-btn--danger">Удалить</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
