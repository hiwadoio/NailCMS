<?php
declare(strict_types=1);

$settings = data_load_site_settings();
?>
<section class="admin-card">
  <h2 style="margin-top:0">Настройки сайта</h2>
  <p class="admin-muted">Эти значения используются на всём сайте: шапка, SEO, контакты, подвал. Данные сохраняются в MySQL (таблица <code>site_settings</code>).</p>

  <form method="post" class="admin-form" style="margin-top:1.25rem">
    <input type="hidden" name="action" value="settings_save" />
    <input type="hidden" name="csrf" value="<?= e(admin_csrf_token()) ?>" />

    <h3 class="admin-form-section-title">Основное</h3>
    <div class="admin-form__row admin-form__row--2">
      <label>
        Адрес сайта (без слэша в конце) *
        <input type="url" name="site_url" value="<?= e($settings['site_url']) ?>" required placeholder="https://example.ru" />
      </label>
      <label>
        Город
        <input type="text" name="city" value="<?= e($settings['city']) ?>" />
      </label>
    </div>

    <div class="admin-form__row admin-form__row--2">
      <label>
        Название салона *
        <input type="text" name="site_name" value="<?= e($settings['site_name']) ?>" required />
      </label>
      <label>
        Короткое имя бренда
        <input type="text" name="brand_short" value="<?= e($settings['brand_short']) ?>" />
      </label>
    </div>

    <div class="admin-form__row admin-form__row--2">
      <label>
        Слоган
        <input type="text" name="brand_tagline" value="<?= e($settings['brand_tagline']) ?>" />
      </label>
      <label>
        Альтернативное название (Schema.org)
        <input type="text" name="brand_alternate" value="<?= e($settings['brand_alternate']) ?>" />
      </label>
    </div>

    <label>
      Meta description (главная страница)
      <textarea name="site_description" rows="3"><?= e($settings['site_description']) ?></textarea>
    </label>

    <h3 class="admin-form-section-title">Контакты</h3>
    <div class="admin-form__row admin-form__row--2">
      <label>
        Телефон (как показывать)
        <input type="text" name="phone_display" value="<?= e($settings['phone_display']) ?>" placeholder="+7 (900) 000-00-00" />
      </label>
      <label>
        Телефон для ссылки tel:
        <input type="text" name="phone_tel" value="<?= e($settings['phone_tel']) ?>" placeholder="+79000000000" />
      </label>
    </div>

    <label>
      Email
      <input type="email" name="email" value="<?= e($settings['email']) ?>" />
    </label>

    <h3 class="admin-form-section-title">SEO и соцсети</h3>
    <label>
      Заголовок страницы (title)
      <input type="text" name="site_title" value="<?= e($settings['site_title']) ?>" />
    </label>

    <label>
      Ключевые слова (keywords)
      <textarea name="site_keywords" rows="2"><?= e($settings['site_keywords']) ?></textarea>
    </label>

    <div class="admin-form__row admin-form__row--2">
      <label>
        Open Graph — заголовок
        <input type="text" name="og_title" value="<?= e($settings['og_title']) ?>" />
      </label>
      <label>
        Twitter — заголовок
        <input type="text" name="twitter_title" value="<?= e($settings['twitter_title']) ?>" />
      </label>
    </div>

    <div class="admin-form__row admin-form__row--2">
      <label>
        Open Graph — описание
        <textarea name="og_description" rows="2"><?= e($settings['og_description']) ?></textarea>
      </label>
      <label>
        Twitter — описание
        <textarea name="twitter_description" rows="2"><?= e($settings['twitter_description']) ?></textarea>
      </label>
    </div>

    <label>
      Alt-текст главного изображения (OG)
      <input type="text" name="og_image_alt" value="<?= e($settings['og_image_alt']) ?>" />
    </label>

    <h3 class="admin-form-section-title">Schema.org</h3>
    <label>
      Описание организации
      <textarea name="schema_org_description" rows="2"><?= e($settings['schema_org_description']) ?></textarea>
    </label>

    <label>
      Описание сайта
      <textarea name="schema_website_description" rows="2"><?= e($settings['schema_website_description']) ?></textarea>
    </label>

    <h3 class="admin-form-section-title">Оформление</h3>
    <label>
      Цвет темы (theme-color)
      <input type="text" name="theme_color" value="<?= e($settings['theme_color']) ?>" placeholder="#f2fae6" />
    </label>

    <div class="admin-actions" style="margin-top:0.5rem">
      <button type="submit" class="admin-btn">Сохранить настройки</button>
      <a class="admin-btn admin-btn--ghost" href="<?= e(site_url()) ?>" target="_blank" rel="noopener">Открыть сайт</a>
    </div>
  </form>
</section>
