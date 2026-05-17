<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/bootstrap.php';
require_once dirname(__DIR__) . '/lib/admin-auth.php';

security_send_noindex_headers();

$adminFlash = null;
$adminError = null;

function admin_set_flash(string $message): void
{
    global $adminFlash;
    $adminFlash = $message;
}

function admin_set_error(string $message): void
{
    global $adminError;
    $adminError = $message;
}

function admin_require_valid_id(string $id, string $redirectPage): void
{
    if (!security_valid_id($id)) {
        admin_set_error('Некорректный идентификатор.');
        admin_redirect('?page=' . $redirectPage);
    }
}

function admin_handle_post(): bool
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return false;
    }

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'login') {
        if (!admin_verify_csrf($_POST['csrf'] ?? null)) {
            admin_set_error('Ошибка безопасности. Обновите страницу.');
            return true;
        }
        if (admin_is_login_locked()) {
            admin_set_error('Слишком много попыток входа. Попробуйте через 15 минут.');
            return true;
        }
        $user = trim((string) ($_POST['username'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        if (admin_login($user, $pass)) {
            admin_redirect('');
        }
        admin_set_error(admin_is_login_locked()
            ? 'Слишком много попыток входа. Попробуйте через 15 минут.'
            : 'Неверный логин или пароль.');
        return true;
    }

    if (!admin_verify_csrf($_POST['csrf'] ?? null)) {
        admin_set_error('Ошибка безопасности. Обновите страницу.');
        return true;
    }

    if ($action === 'logout') {
        admin_logout();
        admin_redirect('');
    }

    admin_require_auth();

    if ($action === 'service_save') {
        admin_save_service();
        return true;
    }
    if ($action === 'service_delete') {
        admin_delete_service();
        return true;
    }
    if ($action === 'review_save') {
        admin_save_review();
        return true;
    }
    if ($action === 'review_delete') {
        admin_delete_review();
        return true;
    }
    if ($action === 'review_approve') {
        admin_approve_pending_review();
        return true;
    }
    if ($action === 'review_reject') {
        admin_reject_pending_review();
        return true;
    }
    if ($action === 'settings_save') {
        admin_save_site_settings();
        return true;
    }
    if ($action === 'booking_status') {
        admin_update_booking_status();
        return true;
    }
    if ($action === 'booking_delete') {
        admin_delete_booking();
        return true;
    }

    return true;
}

function admin_save_site_settings(): void
{
    $settings = data_site_settings_from_post();

    if ($settings['site_url'] === '' || !filter_var($settings['site_url'], FILTER_VALIDATE_URL)) {
        admin_set_error('Укажите корректный адрес сайта (например https://example.ru).');
        admin_redirect('?page=settings');
    }

    if ($settings['site_name'] === '') {
        admin_set_error('Укажите название салона.');
        admin_redirect('?page=settings');
    }

    if ($settings['email'] !== '' && !filter_var($settings['email'], FILTER_VALIDATE_EMAIL)) {
        admin_set_error('Укажите корректный email.');
        admin_redirect('?page=settings');
    }

    if (!data_save_site_settings($settings)) {
        admin_set_error('Не удалось сохранить настройки. Проверьте подключение к MySQL.');
        admin_redirect('?page=settings');
    }

    admin_set_flash('Настройки сайта сохранены.');
    admin_redirect('?page=settings');
}

function admin_approve_pending_review(): void
{
    $id = trim((string) ($_POST['id'] ?? ''));
    admin_require_valid_id($id, 'reviews');
    if (!data_approve_pending_review($id)) {
        admin_set_error('Не удалось одобрить отзыв.');
    } else {
        admin_set_flash('Отзыв одобрен и опубликован на сайте.');
    }
    admin_redirect('?page=reviews');
}

function admin_reject_pending_review(): void
{
    $id = trim((string) ($_POST['id'] ?? ''));
    admin_require_valid_id($id, 'reviews');
    if (!data_reject_pending_review($id)) {
        admin_set_error('Не удалось отклонить отзыв.');
    } else {
        admin_set_flash('Отзыв отклонён.');
    }
    admin_redirect('?page=reviews');
}

function admin_save_service(): void
{
    $services = data_load_services();
    $id = trim((string) ($_POST['id'] ?? ''));
    $isNew = $id === '';
    $name = trim((string) ($_POST['name'] ?? ''));

    if ($name === '') {
        admin_set_error('Укажите название услуги.');
        admin_redirect('?page=service-edit' . ($isNew ? '' : '&id=' . rawurlencode($id)));
    }

    if ($isNew) {
        $id = data_unique_id('services', data_slugify($name));
    } else {
        admin_require_valid_id($id, 'services');
    }

    $image = trim((string) ($_POST['image'] ?? ''));
    if ($image !== '' && !security_safe_path($image, 'assets/images/services')) {
        admin_set_error('Недопустимый путь к изображению.');
        admin_redirect('?page=service-edit' . ($isNew ? '' : '&id=' . rawurlencode($id)));
    }
    $uploaded = admin_handle_service_image_upload($id);
    if ($uploaded !== null) {
        $image = $uploaded;
    }

    if ($image === '') {
        admin_set_error('Укажите путь к изображению или загрузите фото.');
        admin_redirect('?page=service-edit' . ($isNew ? '' : '&id=' . rawurlencode($id)));
    }

    $service = [
        'id' => $id,
        'name' => $name,
        'price' => max(0, (int) ($_POST['price'] ?? 0)),
        'price_from' => !empty($_POST['price_from']),
        'image' => $image,
        'text' => trim((string) ($_POST['text'] ?? '')),
        'detail' => trim((string) ($_POST['detail'] ?? '')),
        'schema_description' => trim((string) ($_POST['schema_description'] ?? '')),
    ];

    $found = false;
    foreach ($services as $index => $item) {
        if ($item['id'] === $id) {
            $services[$index] = data_normalize_service($service);
            $found = true;
            break;
        }
    }
    if (!$found) {
        $services[] = data_normalize_service($service);
    }

    if (!data_save_services($services)) {
        admin_set_error('Не удалось сохранить услуги. Проверьте подключение к MySQL.');
        admin_redirect('?page=services');
    }

    admin_set_flash($found ? 'Услуга обновлена.' : 'Услуга добавлена.');
    admin_redirect('?page=services');
}

function admin_delete_service(): void
{
    $id = trim((string) ($_POST['id'] ?? ''));
    admin_require_valid_id($id, 'services');
    $services = array_values(array_filter(
        data_load_services(),
        static fn(array $item): bool => $item['id'] !== $id
    ));

    if (!data_save_services($services)) {
        admin_set_error('Не удалось удалить услугу.');
    } else {
        admin_set_flash('Услуга удалена.');
    }
    admin_redirect('?page=services');
}

function admin_save_review(): void
{
    $data = data_load_reviews();
    $items = $data['items'];
    $id = trim((string) ($_POST['id'] ?? ''));
    $isNew = $id === '';
    $author = trim((string) ($_POST['author'] ?? ''));

    if ($author === '') {
        admin_set_error('Укажите имя автора.');
        admin_redirect('?page=review-edit' . ($isNew ? '' : '&id=' . rawurlencode($id)));
    }

    if ($isNew) {
        $id = data_unique_id('reviews', 'r-' . bin2hex(random_bytes(4)));
    } else {
        admin_require_valid_id($id, 'reviews');
    }

    $review = [
        'id' => $id,
        'author' => $author,
        'date' => trim((string) ($_POST['date'] ?? date('Y-m-d'))),
        'service' => trim((string) ($_POST['service'] ?? '')),
        'rating' => min(5, max(1, (float) ($_POST['rating'] ?? 5))),
        'text' => trim((string) ($_POST['text'] ?? '')),
    ];

    $found = false;
    foreach ($items as $index => $item) {
        if ($item['id'] === $id) {
            $items[$index] = $review;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $items[] = $review;
    }

    $data['items'] = $items;
    if (!data_save_reviews($data)) {
        admin_set_error('Не удалось сохранить отзыв.');
        admin_redirect('?page=reviews');
    }

    admin_set_flash($found ? 'Отзыв обновлён.' : 'Отзыв добавлен.');
    admin_redirect('?page=reviews');
}

function admin_delete_review(): void
{
    $id = trim((string) ($_POST['id'] ?? ''));
    admin_require_valid_id($id, 'reviews');
    $data = data_load_reviews();
    $data['items'] = array_values(array_filter(
        $data['items'],
        static fn(array $item): bool => $item['id'] !== $id
    ));

    if (!data_save_reviews($data)) {
        admin_set_error('Не удалось удалить отзыв.');
    } else {
        admin_set_flash('Отзыв удалён.');
    }
    admin_redirect('?page=reviews');
}

function admin_handle_service_image_upload(string $id): ?string
{
    if (empty($_FILES['image_file']['name']) || !is_uploaded_file($_FILES['image_file']['tmp_name'])) {
        return null;
    }

    $file = $_FILES['image_file'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        admin_set_error('Ошибка загрузки изображения.');
        return null;
    }

    $maxBytes = 3 * 1024 * 1024;
    if ((int) ($file['size'] ?? 0) > $maxBytes) {
        admin_set_error('Изображение не должно превышать 3 МБ.');
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = (string) finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }
    if ($mime === '' && function_exists('mime_content_type')) {
        $mime = (string) mime_content_type($file['tmp_name']);
    }
    if (!isset($allowed[$mime])) {
        admin_set_error('Допустимы только JPG, PNG или WebP.');
        return null;
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        admin_set_error('Файл не является изображением.');
        return null;
    }

    $dir = ROOT_DIR . '/assets/images/services';
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        admin_set_error('Не удалось создать папку для изображений.');
        return null;
    }

    $filename = data_slugify($id) . '-' . time() . '.' . $allowed[$mime];
    $target = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        admin_set_error('Не удалось сохранить изображение.');
        return null;
    }

    return 'assets/images/services/' . $filename;
}

function admin_find_service(string $id): ?array
{
    foreach (data_load_services() as $service) {
        if ($service['id'] === $id) {
            return $service;
        }
    }
    return null;
}

function admin_find_review(string $id): ?array
{
    foreach (data_load_reviews()['items'] as $review) {
        if ($review['id'] === $id) {
            return $review;
        }
    }
    return null;
}

function admin_update_booking_status(): void
{
    $id = trim((string) ($_POST['id'] ?? ''));
    admin_require_valid_id($id, 'bookings');
    $status = trim((string) ($_POST['status'] ?? ''));

    if (!data_update_booking_status($id, $status)) {
        admin_set_error('Не удалось обновить статус заявки.');
    } else {
        admin_set_flash('Статус заявки обновлён.');
    }
    admin_redirect('?page=bookings');
}

function admin_delete_booking(): void
{
    $id = trim((string) ($_POST['id'] ?? ''));
    admin_require_valid_id($id, 'bookings');
    if (!data_delete_booking($id)) {
        admin_set_error('Не удалось удалить заявку.');
    } else {
        admin_set_flash('Заявка удалена.');
    }
    admin_redirect('?page=bookings');
}
