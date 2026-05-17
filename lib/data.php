<?php
declare(strict_types=1);

/** Ключи настроек сайта (единый список для БД, админки и шаблонов). */
function data_site_setting_keys(): array
{
    return [
        'site_url',
        'site_name',
        'site_description',
        'site_title',
        'site_keywords',
        'og_title',
        'og_description',
        'twitter_title',
        'twitter_description',
        'brand_short',
        'brand_tagline',
        'brand_alternate',
        'phone_display',
        'phone_tel',
        'email',
        'city',
        'theme_color',
        'schema_org_description',
        'schema_website_description',
        'og_image_alt',
    ];
}

function data_normalize_service(array $service): array
{
    $price = max(0, (int) ($service['price'] ?? 0));
    $priceFrom = !empty($service['price_from']);
    $formatted = number_format($price, 0, '', ' ') . ' ₽';

    $service['price'] = $price;
    $service['price_from'] = $priceFrom;
    $service['price_display'] = $priceFrom ? 'от ' . $formatted : $formatted;
    $service['schema_description'] = trim((string) ($service['schema_description'] ?? $service['text'] ?? ''));

    return $service;
}

function data_map_service_row(array $row): array
{
    return data_normalize_service([
        'id' => (string) $row['id'],
        'name' => (string) $row['name'],
        'price' => (int) $row['price'],
        'price_from' => (bool) $row['price_from'],
        'image' => (string) $row['image'],
        'text' => (string) $row['text'],
        'detail' => (string) $row['detail'],
        'schema_description' => (string) $row['schema_description'],
    ]);
}

function data_map_review_row(array $row): array
{
    return [
        'id' => (string) $row['id'],
        'author' => (string) $row['author'],
        'date' => (string) $row['review_date'],
        'service' => (string) $row['service'],
        'rating' => (float) $row['rating'],
        'text' => (string) $row['text'],
    ];
}

function data_map_pending_review_row(array $row): array
{
    $item = data_map_review_row($row);
    $item['submitted_at'] = data_format_datetime_iso((string) $row['submitted_at']);

    return $item;
}

function data_map_booking_row(array $row): array
{
    return [
        'id' => (string) $row['id'],
        'name' => (string) $row['name'],
        'phone' => (string) $row['phone'],
        'email' => (string) $row['email'],
        'service' => (string) $row['service'],
        'visit_date' => $row['visit_date'] !== null ? (string) $row['visit_date'] : '',
        'comment' => (string) $row['comment'],
        'status' => (string) $row['status'],
        'submitted_at' => data_format_datetime_iso((string) $row['submitted_at']),
        'updated_at' => $row['updated_at'] !== null ? data_format_datetime_iso((string) $row['updated_at']) : '',
    ];
}

function data_format_datetime_iso(string $value): string
{
    if ($value === '') {
        return '';
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value)
        ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $value);

    return $dt !== false ? $dt->format('c') : $value;
}

function data_unique_id(string $table, string $base): string
{
    $allowed = ['services', 'reviews', 'pending_reviews', 'bookings'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Invalid table for id generation.');
    }

    $pdo = db();
    $id = $base;
    $counter = 2;
    $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE id = ? LIMIT 1");

    while (true) {
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() === false) {
            return $id;
        }
        $id = $base . '-' . $counter;
        $counter++;
    }
}

function data_ensure_database_installed(): void
{
    static $verified = false;
    if ($verified) {
        return;
    }
    $verified = true;

    if (is_file(SETTINGS_DIR . '/install.lock')) {
        return;
    }

    $settingsCount = (int) db()->query('SELECT COUNT(*) FROM site_settings')->fetchColumn();

    if ($settingsCount === 0) {
        throw new RuntimeException(
            'База данных не настроена. Пройдите установку на /install/ или импортируйте database/schema.sql.'
        );
    }

    $requiredKeys = data_site_setting_keys();
    $placeholders = implode(',', array_fill(0, count($requiredKeys), '?'));
    $stmt = db()->prepare(
        "SELECT setting_key FROM site_settings WHERE setting_key IN ({$placeholders})"
    );
    $stmt->execute($requiredKeys);
    $present = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $missing = array_values(array_diff($requiredKeys, array_map('strval', $present)));

    if ($missing !== []) {
        throw new RuntimeException(
            'В site_settings не хватает ключей: ' . implode(', ', $missing) . '. Пройдите /install/ или импортируйте database/schema.sql.'
        );
    }
}

function data_load_service_names(): array
{
    $stmt = db()->query(
        'SELECT name FROM services ORDER BY sort_order ASC, name ASC'
    );

    return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function data_load_services(): array
{
    $stmt = db()->query(
        'SELECT id, name, price, price_from, image, text, detail, schema_description
         FROM services ORDER BY sort_order ASC, name ASC'
    );
    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $items[] = data_map_service_row($row);
    }

    return $items;
}

function data_save_services(array $items): bool
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $keepIds = [];
        $stmt = $pdo->prepare(
            'INSERT INTO services (id, name, price, price_from, image, text, detail, schema_description, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               name = VALUES(name),
               price = VALUES(price),
               price_from = VALUES(price_from),
               image = VALUES(image),
               text = VALUES(text),
               detail = VALUES(detail),
               schema_description = VALUES(schema_description),
               sort_order = VALUES(sort_order)'
        );

        foreach (array_values($items) as $index => $service) {
            $normalized = data_normalize_service($service);
            $keepIds[] = $normalized['id'];
            $stmt->execute([
                $normalized['id'],
                $normalized['name'],
                $normalized['price'],
                $normalized['price_from'] ? 1 : 0,
                $normalized['image'],
                $normalized['text'],
                $normalized['detail'],
                $normalized['schema_description'],
                $index,
            ]);
        }

        if ($keepIds === []) {
            $pdo->exec('DELETE FROM services');
        } else {
            $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
            $delete = $pdo->prepare("DELETE FROM services WHERE id NOT IN ({$placeholders})");
            $delete->execute($keepIds);
        }

        $pdo->commit();

        return true;
    } catch (Throwable) {
        $pdo->rollBack();

        return false;
    }
}

function data_reviews_stats(): array
{
    $row = db()->query(
        'SELECT COUNT(*) AS cnt, COALESCE(AVG(rating), 0) AS avg_rating FROM reviews'
    )->fetch(PDO::FETCH_ASSOC);

    return [
        'count' => (int) ($row['cnt'] ?? 0),
        'average' => (float) ($row['avg_rating'] ?? 0),
    ];
}

function data_load_reviews_limited(int $limit): array
{
    $limit = max(1, min(100, $limit));
    $stmt = db()->prepare(
        'SELECT id, author, review_date, service, rating, text
         FROM reviews ORDER BY review_date DESC, id DESC LIMIT ?'
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();

    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $items[] = data_map_review_row($row);
    }

    return $items;
}

function data_load_reviews(): array
{
    $stmt = db()->query(
        'SELECT id, author, review_date, service, rating, text
         FROM reviews ORDER BY review_date DESC, id DESC'
    );
    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $items[] = data_map_review_row($row);
    }

    return ['items' => $items];
}

function data_save_reviews(array $data): bool
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $items = array_values($data['items'] ?? []);
        $keepIds = [];
        $stmt = $pdo->prepare(
            'INSERT INTO reviews (id, author, review_date, service, rating, text)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               author = VALUES(author),
               review_date = VALUES(review_date),
               service = VALUES(service),
               rating = VALUES(rating),
               text = VALUES(text)'
        );

        foreach ($items as $review) {
            $id = (string) ($review['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $keepIds[] = $id;
            $stmt->execute([
                $id,
                trim((string) ($review['author'] ?? '')),
                trim((string) ($review['date'] ?? date('Y-m-d'))),
                trim((string) ($review['service'] ?? '')),
                min(5, max(1, (float) ($review['rating'] ?? 5))),
                trim((string) ($review['text'] ?? '')),
            ]);
        }

        if ($keepIds === []) {
            $pdo->exec('DELETE FROM reviews');
        } else {
            $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
            $delete = $pdo->prepare("DELETE FROM reviews WHERE id NOT IN ({$placeholders})");
            $delete->execute($keepIds);
        }

        $pdo->commit();

        return true;
    } catch (Throwable) {
        $pdo->rollBack();

        return false;
    }
}

function data_load_pending_reviews(): array
{
    $stmt = db()->query(
        'SELECT id, author, review_date, service, rating, text, submitted_at
         FROM pending_reviews ORDER BY submitted_at DESC'
    );
    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $items[] = data_map_pending_review_row($row);
    }

    return ['items' => $items];
}

function data_add_pending_review(array $review): bool
{
    $id = data_unique_id('pending_reviews', 'pending-' . bin2hex(random_bytes(4)));
    $submittedAt = $review['submitted_at'] ?? date('Y-m-d H:i:s');
    if (str_contains($submittedAt, 'T')) {
        $dt = date_create($submittedAt);
        $submittedAt = $dt !== false ? $dt->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
    }

    $stmt = db()->prepare(
        'INSERT INTO pending_reviews (id, author, review_date, service, rating, text, submitted_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    return $stmt->execute([
        $id,
        trim((string) ($review['author'] ?? '')),
        trim((string) ($review['date'] ?? date('Y-m-d'))),
        trim((string) ($review['service'] ?? '')),
        min(5, max(1, (float) ($review['rating'] ?? 5))),
        trim((string) ($review['text'] ?? '')),
        $submittedAt,
    ]);
}

function data_find_pending_review(string $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, author, review_date, service, rating, text, submitted_at
         FROM pending_reviews WHERE id = ? LIMIT 1'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row !== false ? data_map_pending_review_row($row) : null;
}

function data_approve_pending_review(string $id): bool
{
    $pending = data_find_pending_review($id);
    if ($pending === null) {
        return false;
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $reviewId = data_unique_id('reviews', 'r-' . bin2hex(random_bytes(4)));
        $insert = $pdo->prepare(
            'INSERT INTO reviews (id, author, review_date, service, rating, text)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([
            $reviewId,
            $pending['author'],
            $pending['date'],
            $pending['service'],
            $pending['rating'],
            $pending['text'],
        ]);

        $delete = $pdo->prepare('DELETE FROM pending_reviews WHERE id = ?');
        $delete->execute([$id]);

        $pdo->commit();

        return true;
    } catch (Throwable) {
        $pdo->rollBack();

        return false;
    }
}

function data_reject_pending_review(string $id): bool
{
    $stmt = db()->prepare('DELETE FROM pending_reviews WHERE id = ?');

    $stmt->execute([$id]);

    return $stmt->rowCount() > 0;
}

function data_pending_reviews_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM pending_reviews')->fetchColumn();
}

function data_load_bookings(): array
{
    $stmt = db()->query(
        'SELECT id, name, phone, email, service, visit_date, comment, status, submitted_at, updated_at
         FROM bookings ORDER BY submitted_at DESC'
    );
    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $items[] = data_map_booking_row($row);
    }

    return ['items' => $items];
}

function data_add_booking(array $booking): bool
{
    try {
        $id = data_unique_id('bookings', 'b-' . bin2hex(random_bytes(4)));
        $submittedAt = $booking['submitted_at'] ?? date('Y-m-d H:i:s');
        if (str_contains((string) $submittedAt, 'T')) {
            $dt = date_create((string) $submittedAt);
            $submittedAt = $dt !== false ? $dt->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
        }

        $visitDate = trim((string) ($booking['visit_date'] ?? ''));

        $stmt = db()->prepare(
            'INSERT INTO bookings (id, name, phone, email, service, visit_date, comment, status, submitted_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        return $stmt->execute([
            $id,
            trim((string) ($booking['name'] ?? '')),
            trim((string) ($booking['phone'] ?? '')),
            trim((string) ($booking['email'] ?? '')),
            trim((string) ($booking['service'] ?? '')),
            $visitDate !== '' ? $visitDate : null,
            trim((string) ($booking['comment'] ?? '')),
            BOOKING_STATUS_NEW,
            $submittedAt,
        ]);
    } catch (Throwable $e) {
        error_log('data_add_booking: ' . $e->getMessage());

        return false;
    }
}

function data_update_booking_status(string $id, string $status): bool
{
    if (!booking_is_valid_status($status)) {
        return false;
    }

    $stmt = db()->prepare(
        'UPDATE bookings SET status = ?, updated_at = ? WHERE id = ?'
    );

    $stmt->execute([$status, date('Y-m-d H:i:s'), $id]);

    return $stmt->rowCount() > 0;
}

function data_delete_booking(string $id): bool
{
    $stmt = db()->prepare('DELETE FROM bookings WHERE id = ?');
    $stmt->execute([$id]);

    return $stmt->rowCount() > 0;
}

function data_new_bookings_count(): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM bookings WHERE status = ?');
    $stmt->execute([BOOKING_STATUS_NEW]);

    return (int) $stmt->fetchColumn();
}

function data_sort_bookings_for_admin(array $items): array
{
    usort($items, static function (array $a, array $b): int {
        $dateA = trim((string) ($a['visit_date'] ?? ''));
        $dateB = trim((string) ($b['visit_date'] ?? ''));

        if ($dateA !== '' && $dateB !== '') {
            $cmp = strcmp($dateA, $dateB);
            if ($cmp !== 0) {
                return $cmp;
            }
        }
        if ($dateA !== '' && $dateB === '') {
            return -1;
        }
        if ($dateA === '' && $dateB !== '') {
            return 1;
        }

        return strcmp((string) ($b['submitted_at'] ?? ''), (string) ($a['submitted_at'] ?? ''));
    });

    return $items;
}

function data_normalize_site_settings(array $settings): array
{
    $normalized = [];
    foreach (data_site_setting_keys() as $key) {
        $normalized[$key] = trim((string) ($settings[$key] ?? ''));
    }

    $normalized['site_url'] = rtrim($normalized['site_url'], '/');
    $normalized['phone_tel'] = preg_replace('/\D+/', '', $normalized['phone_tel']) ?? '';
    if ($normalized['phone_tel'] !== '' && !str_starts_with($normalized['phone_tel'], '+')) {
        $normalized['phone_tel'] = '+' . $normalized['phone_tel'];
    }

    if ($normalized['theme_color'] !== '' && $normalized['theme_color'][0] !== '#') {
        $normalized['theme_color'] = '#' . $normalized['theme_color'];
    }

    return $normalized;
}

function data_load_site_settings(): array
{
    $stmt = db()->query('SELECT setting_key, setting_value FROM site_settings');
    $stored = [];
    foreach ($stmt->fetchAll() as $row) {
        $stored[(string) $row['setting_key']] = (string) $row['setting_value'];
    }

    if ($stored === []) {
        throw new RuntimeException(
            'Настройки сайта не найдены в БД. Пройдите /install/ или импортируйте database/schema.sql.'
        );
    }

    return data_normalize_site_settings($stored);
}

function data_save_site_settings(array $settings): bool
{
    $settings = data_normalize_site_settings($settings);
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO site_settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );

        foreach (data_site_setting_keys() as $key) {
            $stmt->execute([$key, (string) ($settings[$key] ?? '')]);
        }

        $pdo->commit();

        return true;
    } catch (Throwable) {
        $pdo->rollBack();

        return false;
    }
}

function data_site_settings_from_post(): array
{
    $settings = [];
    foreach (data_site_setting_keys() as $key) {
        $settings[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    return $settings;
}

function data_apply_site_settings(array $settings): void
{
    $settings = data_normalize_site_settings($settings);

    foreach (data_site_setting_keys() as $key) {
        $GLOBALS[$key] = $settings[$key];
    }
}

function data_slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $map = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');

    return $text !== '' ? $text : 'item-' . bin2hex(random_bytes(4));
}
