<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/settings/app.php';
require_once __DIR__ . '/install-guard.php';
install_guard_maybe_redirect();
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/app-context.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/bookings-helpers.php';
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/reviews-helpers.php';
require_once __DIR__ . '/helpers.php';

security_send_headers();

$services = [];
$reviews = [];
$services_total_count = null;
$reviews_total_count = null;
$reviews_home_average = null;

try {
    data_ensure_database_installed();
    data_apply_site_settings(data_load_site_settings());

    switch (app_load_profile()) {
        case 'minimal':
        case 'api_minimal':
            break;

        case 'admin':
            $services = data_load_services();
            $reviews = data_load_reviews()['items'];
            break;

        case 'api_booking':
            $services = array_map(
                static fn (string $name): array => ['name' => $name],
                data_load_service_names()
            );
            break;

        case 'services_page':
            $services = data_load_services();
            break;

        case 'reviews_page':
            $reviews = data_load_reviews()['items'];
            break;

        case 'home':
        default:
            $services = data_load_services();
            $services_total_count = count($services);

            $reviewsStats = data_reviews_stats();
            $reviews_total_count = $reviewsStats['count'];
            $reviews_home_average = $reviewsStats['average'];
            if ($reviews_total_count > 0) {
                $reviews = data_load_reviews_limited(PUBLIC_HOME_REVIEWS_LIMIT);
            }
            break;
    }
} catch (Throwable $e) {
    error_log('Bootstrap error: ' . $e->getMessage());
    http_response_code(503);
    exit('Сервис временно недоступен. Откройте /install/ для настройки или проверьте settings/db.php.');
}
