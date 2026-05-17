<?php

declare(strict_types=1);

const PUBLIC_HOME_SERVICES_LIMIT = 6;
const PUBLIC_HOME_REVIEWS_LIMIT = 6;

function public_init_page(string $page, ?string $title = null, ?string $description = null): void
{
    global $public_page, $public_page_title, $public_page_description;
    $public_page = $page;
    if ($title !== null) {
        $public_page_title = $title;
    }
    if ($description !== null) {
        $public_page_description = $description;
    }
}

function public_page_is_home(): bool
{
    global $public_page;

    return ($public_page ?? 'home') === 'home';
}

function public_home_section_url(string $fragment): string
{
    $fragment = ltrim($fragment, '#');

    return rtrim(site_url(), '/') . '/#' . $fragment;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function setting_filled(string $value): bool
{
    return trim($value) !== '';
}

function format_ru_date(string $date, string $ifEmpty = ''): string
{
    $date = trim($date);
    if ($date === '') {
        return $ifEmpty;
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    if ($dt === false) {
        return $date;
    }

    static $months = [
        1 => 'января',
        2 => 'февраля',
        3 => 'марта',
        4 => 'апреля',
        5 => 'мая',
        6 => 'июня',
        7 => 'июля',
        8 => 'августа',
        9 => 'сентября',
        10 => 'октября',
        11 => 'ноября',
        12 => 'декабря',
    ];

    $month = (int) $dt->format('n');

    return $dt->format('j') . ' ' . ($months[$month] ?? '') . ' ' . $dt->format('Y');
}

function site_url(string $path = ''): string
{
    global $site_url;
    $base = rtrim((string) $site_url, '/');
    if ($base === '') {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $base = ($https ? 'https' : 'http') . '://' . $host;
    }
    if ($path === '') {
        return $base . '/';
    }

    return $base . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return site_url(ltrim($path, '/'));
}

function services_count_label(int $count): string
{
    $mod10 = $count % 10;
    $mod100 = $count % 100;

    if ($mod100 >= 11 && $mod100 <= 14) {
        return $count . ' услуг';
    }

    if ($mod10 === 1) {
        return $count . ' услуга';
    }

    if ($mod10 >= 2 && $mod10 <= 4) {
        return $count . ' услуги';
    }

    return $count . ' услуг';
}

function service_form_label(array $service): string
{
    return $service['name'] . ' — ' . $service['price_display'];
}

function services_schema_offers(): array
{
    global $services;

    $offers = [];
    foreach ($services as $service) {
        $offers[] = [
            '@type' => 'Offer',
            'itemOffered' => [
                '@type' => 'Service',
                'name' => $service['name'],
                'description' => $service['schema_description'],
            ],
            'price' => (string) $service['price'],
            'priceCurrency' => 'RUB',
        ];
    }

    return $offers;
}

function services_schema_offers_json(): string
{
    return json_encode(
        services_schema_offers(),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    );
}

function schema_ld_json_script(): string
{
    global $services, $canonical, $site_name, $schema_website_description, $schema_org_description;
    global $brand_short, $brand_alternate, $phone_tel, $email, $city, $logo_url, $og_image;

    $website = [
        '@type' => 'WebSite',
        '@id' => site_url('#website'),
        'url' => $canonical,
        'name' => $site_name,
        'inLanguage' => 'ru-RU',
        'publisher' => ['@id' => site_url('#organization')],
    ];
    if (setting_filled($schema_website_description)) {
        $website['description'] = $schema_website_description;
    }

    $organization = [
        '@type' => 'NailSalon',
        '@id' => site_url('#organization'),
        'name' => $site_name,
        'url' => $canonical,
        'logo' => $logo_url,
        'image' => $og_image,
        'priceRange' => '₽₽',
        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday',
                    'Sunday',
                ],
                'opens' => '10:00',
                'closes' => '21:00',
            ],
        ],
    ];

    if (setting_filled($brand_alternate)) {
        $organization['alternateName'] = $brand_alternate;
    }
    if (setting_filled($schema_org_description)) {
        $organization['description'] = $schema_org_description;
    }
    if (setting_filled($phone_tel)) {
        $organization['telephone'] = $phone_tel;
    }
    if (setting_filled($email)) {
        $organization['email'] = $email;
    }
    if (setting_filled($city)) {
        $organization['address'] = [
            '@type' => 'PostalAddress',
            'addressLocality' => $city,
            'addressRegion' => $city,
            'addressCountry' => 'RU',
        ];
    }

    if (count($services) > 0) {
        $catalogName = setting_filled($brand_short)
            ? 'Услуги салона «' . $brand_short . '»'
            : (setting_filled($site_name) ? 'Услуги — ' . $site_name : 'Услуги салона');
        $organization['hasOfferCatalog'] = [
            '@type' => 'OfferCatalog',
            'name' => $catalogName,
            'itemListElement' => services_schema_offers(),
        ];
    }

    $payload = [
        '@context' => 'https://schema.org',
        '@graph' => [$website, $organization],
    ];

    return json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_THROW_ON_ERROR
    );
}
