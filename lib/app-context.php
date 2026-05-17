<?php

declare(strict_types=1);

/**
 * Профиль загрузки данных: меньше запросов к MySQL на виртуальном хостинге.
 *
 * home          — главная (лимит услуг/отзывов + счётчики)
 * services_page — каталог услуг
 * reviews_page  — все отзывы (+ услуги для Schema.org в шапке)
 * api_booking   — имена услуг для проверки форм записи и отзыва
 * api_minimal   — капча и прочие API без каталога
 * minimal       — sitemap, robots
 * admin         — админка (контент грузит сама)
 */
function app_load_profile(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    if (str_contains($script, '/admin/')) {
        return 'admin';
    }
    if (
        str_contains($script, '/api/submit-booking')
        || str_contains($script, '/api/booking-captcha')
        || str_contains($script, '/api/submit-review')
    ) {
        return 'api_booking';
    }
    if (str_contains($script, '/api/')) {
        return 'api_minimal';
    }
    if (str_ends_with($script, 'services.php')) {
        return 'services_page';
    }
    if (str_ends_with($script, 'reviews.php')) {
        return 'reviews_page';
    }
    if (str_contains($script, 'sitemap.php') || str_contains($script, 'robots.php')) {
        return 'minimal';
    }

    return 'home';
}
