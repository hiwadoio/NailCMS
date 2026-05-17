<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';

public_init_page(
    'reviews',
    'Все отзывы',
    'Отзывы гостей о визитах в салон «' . $brand_short . '»: оценки, впечатления и рекомендации.'
);

require __DIR__ . '/header.php';
?>
      <main class="tpl-main" id="main-content">
<?php require __DIR__ . '/page-reviews.php'; ?>
      </main>
<?php require __DIR__ . '/footer.php'; ?>
