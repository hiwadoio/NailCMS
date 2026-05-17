<?php
declare(strict_types=1);

$public_page = $public_page ?? 'home';
$isHomePage = public_page_is_home();
$canonical = $isHomePage ? site_url() : site_url($public_page);
if ($isHomePage) {
    $documentTitle = setting_filled($site_title)
        ? $site_title
        : (setting_filled($site_name) ? $site_name : 'Главная');
} else {
    $pageTitle = trim((string) ($public_page_title ?? ''));
    $documentTitle = $pageTitle !== ''
        ? (setting_filled($site_name) ? $pageTitle . ' | ' . $site_name : $pageTitle)
        : (setting_filled($site_name) ? $site_name : 'Страница');
}

$metaDescription = $isHomePage
    ? $site_description
    : (string) ($public_page_description ?? $site_description);
$ogTitle = $isHomePage ? $og_title : (string) ($public_page_title ?? $og_title);
$ogDescription = $isHomePage ? $og_description : (string) ($public_page_description ?? $og_description);
$twitterTitle = setting_filled($twitter_title) ? $twitter_title : $ogTitle;
$twitterDescription = setting_filled($twitter_description) ? $twitter_description : $ogDescription;
$hasMetaDescription = setting_filled($metaDescription);
$hasMetaKeywords = setting_filled($site_keywords);
$hasMetaAuthor = setting_filled($site_name);
$hasThemeColor = setting_filled($theme_color);
$hasCity = setting_filled($city);
$hasOgTitle = setting_filled($ogTitle);
$hasOgDescription = setting_filled($ogDescription);
$hasTwitterTitle = setting_filled($twitterTitle);
$hasTwitterDescription = setting_filled($twitterDescription);
$hasOgImageAlt = setting_filled($og_image_alt);
$hasBrandTagline = setting_filled($brand_tagline);
$og_image = site_url('assets/images/banner.png');
$logo_url = site_url('assets/images/logo.png');
$navHome = $isHomePage ? '#promo-heading' : public_home_section_url('promo-heading');
$navServices = $isHomePage ? '#offers-top-heading' : site_url('services');
$navBooking = public_home_section_url('offers-form-heading');
$navReviews = $isHomePage ? '#reviews-heading' : site_url('reviews');
$navPromo = public_home_section_url('promo-heading');
$navPrice = public_home_section_url('offers-top-heading');
?>
<!doctype html>
<html lang="ru" prefix="og: https://ogp.me/ns#">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= e($documentTitle) ?></title>
<?php if ($hasMetaDescription): ?>
    <meta name="description" content="<?= e($metaDescription) ?>" />
<?php endif; ?>
<?php if ($hasMetaKeywords): ?>
    <meta name="keywords" content="<?= e($site_keywords) ?>" />
<?php endif; ?>
<?php if ($hasMetaAuthor): ?>
    <meta name="author" content="<?= e($site_name) ?>" />
<?php endif; ?>
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="googlebot" content="index, follow" />
    <meta name="format-detection" content="telephone=yes" />
<?php if ($hasThemeColor): ?>
    <meta name="theme-color" content="<?= e($theme_color) ?>" />
<?php endif; ?>
<?php if ($hasMetaAuthor): ?>
    <meta name="application-name" content="<?= e($site_name) ?>" />
<?php endif; ?>
<?php if ($hasCity): ?>
    <meta name="geo.region" content="RU-MOW" />
    <meta name="geo.placename" content="<?= e($city) ?>" />
<?php endif; ?>
    <link rel="canonical" href="<?= e($canonical) ?>" />
    <link rel="alternate" hreflang="ru" href="<?= e($canonical) ?>" />
    <link rel="alternate" hreflang="x-default" href="<?= e($canonical) ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="ru_RU" />
<?php if ($hasMetaAuthor): ?>
    <meta property="og:site_name" content="<?= e($site_name) ?>" />
<?php endif; ?>
<?php if ($hasOgTitle): ?>
    <meta property="og:title" content="<?= e($ogTitle) ?>" />
<?php endif; ?>
<?php if ($hasOgDescription): ?>
    <meta property="og:description" content="<?= e($ogDescription) ?>" />
<?php endif; ?>
    <meta property="og:url" content="<?= e($canonical) ?>" />
    <meta property="og:image" content="<?= e($og_image) ?>" />
<?php if ($hasOgImageAlt): ?>
    <meta property="og:image:alt" content="<?= e($og_image_alt) ?>" />
<?php endif; ?>
    <meta property="og:image:width" content="612" />
    <meta property="og:image:height" content="504" />
    <meta name="twitter:card" content="summary_large_image" />
<?php if ($hasTwitterTitle): ?>
    <meta name="twitter:title" content="<?= e($twitterTitle) ?>" />
<?php endif; ?>
<?php if ($hasTwitterDescription): ?>
    <meta name="twitter:description" content="<?= e($twitterDescription) ?>" />
<?php endif; ?>
    <meta name="twitter:image" content="<?= e($og_image) ?>" />
<?php if ($hasOgImageAlt): ?>
    <meta name="twitter:image:alt" content="<?= e($og_image_alt) ?>" />
<?php endif; ?>
    <link rel="icon" href="<?= e(asset_url('assets/images/logo.png')) ?>" type="image/png" />
    <link rel="apple-touch-icon" href="<?= e(asset_url('assets/images/logo.png')) ?>" />
    <link rel="sitemap" type="application/xml" title="Sitemap" href="<?= e(site_url('sitemap.xml')) ?>" />
    <link rel="stylesheet" href="<?= e(asset_url('css/main.css')) ?>" />
    <script type="application/ld+json"><?= schema_ld_json_script() ?></script>
  </head>
  <body data-booking-url="<?= e($navBooking) ?>">
    <div class="tpl tpl-body-bg">
      <a class="tpl-skip-link" href="#main-content">Перейти к содержимому</a>

      <header class="tpl-header">
        <div class="tpl-container tpl-header__inner">
          <div class="tpl-header__bar">
            <div class="tpl-logo-col">
                <a class="tpl-logo" href="<?= e(site_url()) ?>" aria-label="<?= e($site_name) ?> — на главную">
                  <img class="tpl-logo__mark" src="<?= e(asset_url('assets/images/logo.png')) ?>" alt="" width="150" height="70" fetchpriority="high" decoding="async" />
                  <span class="tpl-logo__brand">
                    <span class="tpl-logo__name"><?= setting_filled($brand_short) ? e($brand_short) : (setting_filled($site_name) ? e($site_name) : 'Салон') ?></span>
<?php if ($hasBrandTagline): ?>
                    <span class="tpl-logo__tagline"><?= e($brand_tagline) ?></span>
<?php endif; ?>
                  </span>
                </a>
            </div>
            <div class="tpl-header__actions">
              <a class="tpl-nav__link tpl-nav__link--cta-toolbar" href="<?= e($navBooking) ?>">Записаться</a>
              <button type="button" class="tpl-nav__burger" id="nav-burger" aria-expanded="false" aria-controls="nav-panel" aria-label="Открыть меню">
                <svg class="tpl-nav__burger-icon" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                  <path class="tpl-nav__burger-line" d="M4 7h16" />
                  <path class="tpl-nav__burger-line" d="M4 12h16" />
                  <path class="tpl-nav__burger-line" d="M4 17h16" />
                </svg>
              </button>
            </div>
          </div>
          <nav class="tpl-nav" id="nav-panel" aria-label="Навигация по сайту">
            <button type="button" class="tpl-nav__close" id="nav-close" aria-label="Закрыть меню">
                  <span aria-hidden="true">&times;</span>
                </button>
                <ul class="tpl-nav__list">
                  <li class="tpl-nav__item"><a class="tpl-nav__link" href="<?= e($navHome) ?>">Главная</a></li>
                  <li class="tpl-nav__item"><a class="tpl-nav__link" href="<?= e($navServices) ?>">Услуги</a></li>
                  <li class="tpl-nav__item"><a class="tpl-nav__link" href="<?= e($navBooking) ?>">Запись</a></li>
                  <li class="tpl-nav__item tpl-nav__item--has-dropdown">
                    <button type="button" class="tpl-nav__link tpl-nav__dropdown-trigger" id="nav-dropdown-trigger" aria-expanded="false" aria-controls="nav-dropdown-panel" aria-haspopup="true">
                      Салон
                      <svg class="tpl-nav__dropdown-caret" width="10" height="10" viewBox="0 0 10 10" aria-hidden="true" focusable="false"><path fill="currentColor" d="M2 3.25 5 6.25 8 3.25z" /></svg>
                    </button>
                    <div class="tpl-nav__dropdown" id="nav-dropdown-panel" role="menu" aria-labelledby="nav-dropdown-trigger" hidden>
                      <a class="tpl-nav__dropdown-link" role="menuitem" href="<?= e($navReviews) ?>">Отзывы</a>
                      <a class="tpl-nav__dropdown-link" role="menuitem" href="<?= e($navPromo) ?>">Акции</a>
                      <a class="tpl-nav__dropdown-link" role="menuitem" href="<?= e($navPrice) ?>">Прайс</a>
                    </div>
                  </li>
                  <li class="tpl-nav__item tpl-nav__item--cta-nav"><a class="tpl-nav__link tpl-nav__link--cta" href="<?= e($navBooking) ?>">Записаться</a></li>
                </ul>
          </nav>
        </div>
      </header>

      <button type="button" class="tpl-nav__backdrop" id="nav-backdrop" hidden aria-label="Закрыть меню"></button>
