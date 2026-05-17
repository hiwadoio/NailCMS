<?php declare(strict_types=1); ?>
        <section class="tpl-sect__offers-top tpl-gutter-top tpl-page-section" aria-labelledby="services-page-heading">
          <div class="tpl-container">
            <div class="tpl-sect-heading">
              <h1 id="services-page-heading">Все услуги</h1>
              <p class="tpl-sect-heading__lead">Полный каталог услуг <?= e($site_name) ?> — выберите процедуру и запишитесь онлайн</p>
            </div>
<?php
$servicesTotal = count($services);
if ($servicesTotal === 0):
    $empty_variant = 'services';
    $empty_message = 'Мы пока не оказываем услуги';
    $empty_hint = 'Следите за обновлениями — скоро здесь появится прайс.';
    $empty_action_label = 'Записаться на приём';
    $empty_action_href = public_home_section_url('offers-form-heading');
    $empty_action_id = '';
    include __DIR__ . '/partials/section-empty.php';
else:
    $services_list = $services;
    include __DIR__ . '/content/services-list.php';
endif;
?>
            <div class="tpl-page-back">
              <a class="tpl-page-back__link" href="<?= e(site_url()) ?>">На главную</a>
            </div>
          </div>
        </section>
