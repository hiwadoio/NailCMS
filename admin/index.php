<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

admin_handle_post();

$page = (string) ($_GET['page'] ?? 'dashboard');
$isLoggedIn = admin_is_logged_in();

$templateDir = __DIR__ . '/template';

if (!$isLoggedIn) {
    require $templateDir . '/login.php';
    exit;
}

$pages = [
    'dashboard' => 'dashboard.php',
    'settings' => 'settings.php',
    'services' => 'services.php',
    'service-edit' => 'service-edit.php',
    'reviews' => 'reviews.php',
    'review-edit' => 'review-edit.php',
    'bookings' => 'bookings.php',
];

$templateFile = $pages[$page] ?? $pages['dashboard'];
$templatePath = $templateDir . '/' . $templateFile;

if (!is_file($templatePath)) {
    $page = 'dashboard';
    $templatePath = $templateDir . '/dashboard.php';
}

require $templateDir . '/layout.php';
