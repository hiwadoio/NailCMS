<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

public_init_page(
    'services',
    'Все услуги',
    'Полный каталог услуг салона «' . $brand_short . '»: маникюр, педикюр, гель-лак и nail-арт. Запись онлайн.'
);

require __DIR__ . '/header.php';
?>
      <main class="tpl-main" id="main-content">
<?php require __DIR__ . '/page-services.php'; ?>
      </main>
<?php require __DIR__ . '/footer.php'; ?>
