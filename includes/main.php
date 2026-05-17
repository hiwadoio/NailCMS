<?php declare(strict_types=1);

$servicesTotal = $services_total_count ?? count($services);
$servicesHome = array_slice($services, 0, PUBLIC_HOME_SERVICES_LIMIT);
$showAllServicesLink = $servicesTotal > PUBLIC_HOME_SERVICES_LIMIT;

$reviewsTotal = $reviews_total_count ?? count($reviews);
$reviewsHome = $reviews;
$showAllReviewsLink = $reviewsTotal > PUBLIC_HOME_REVIEWS_LIMIT;
$reviewsAverage = $reviewsTotal > 0
    ? ($reviews_home_average ?? reviews_average_rating($reviews))
    : 0;
$reviewsAverageLabel = $reviewsTotal > 0 ? review_score_label($reviewsAverage) : '—';
?>
        <section class="tpl-sect__offers-top tpl-gutter-top" aria-labelledby="offers-top-heading">
          <div class="tpl-container">
            <div class="tpl-sect-heading">
              <h2 id="offers-top-heading">Наши услуги</h2>
              <p class="tpl-sect-heading__lead">Маникюр, педикюр и nail-арт — выберите услугу и запишитесь онлайн</p>
            </div>
<?php if ($servicesTotal === 0): ?>
<?php
    $empty_variant = 'services';
    $empty_message = 'Мы пока не оказываем услуги';
    $empty_hint = 'Следите за обновлениями — скоро здесь появится прайс.';
    $empty_action_label = 'Перейти к записи';
    $empty_action_href = public_home_section_url('offers-form-heading');
    $empty_action_id = '';
    include __DIR__ . '/partials/section-empty.php';
?>
<?php else: ?>
<?php
    $services_list = $servicesHome;
    include __DIR__ . '/content/services-list.php';
    if ($showAllServicesLink):
        $more_href = site_url('services');
        $more_label = 'Посмотреть все услуги';
        $more_meta = 'Всего ' . services_count_label($servicesTotal);
        $more_variant = 'services';
        include __DIR__ . '/partials/section-more.php';
    endif;
?>
<?php endif; ?>
          </div>
        </section>

        <section class="tpl-sect__offers tpl-gutter-top" aria-labelledby="offers-form-heading">
          <div class="tpl-container">
            <div class="tpl-sect-heading">
              <h2 id="offers-form-heading">Онлайн-запись</h2>
              <p class="tpl-sect-heading__lead">Оформите заявку на услугу — мы перезвоним и подтвердим время визита</p>
            </div>
            <form id="booking-form" class="tpl-order" novalidate>
              <div class="tpl-order__head">
                <p class="tpl-order__label">Заявка на услугу</p>
                <p class="tpl-order__hint">Укажите контактные данные и услугу — администратор перезвонит для подтверждения записи</p>
              </div>
              <div class="tpl-order__fields">
                <div class="tpl-form-group">
                  <label class="tpl-form-label" for="indexform-name">Имя</label>
                  <input type="text" id="indexform-name" class="tpl-form-input" name="name" autocomplete="name" placeholder="Как к вам обращаться" required />
                </div>
                <div class="tpl-form-group">
                  <label class="tpl-form-label" for="indexform-phone">Телефон</label>
                  <input type="tel" id="indexform-phone" class="tpl-form-input" name="phone" autocomplete="tel" inputmode="tel" placeholder="+7 (___) ___-__-__" required />
                </div>
                <div class="tpl-form-group">
                  <label class="tpl-form-label" for="indexform-email">Email</label>
                  <input type="email" id="indexform-email" class="tpl-form-input" name="email" autocomplete="email" placeholder="example@mail.ru" />
                </div>
                <div class="tpl-form-group">
                  <label class="tpl-form-label" for="indexform-service">Услуга</label>
                  <div class="tpl-form-select-wrap">
                    <select id="indexform-service" class="tpl-form-input tpl-form-select" name="service" required>
                      <option value="">Выберите услугу</option>
<?php foreach ($services as $service): ?>
                      <option value="<?= e($service['name']) ?>"><?= e(service_form_label($service)) ?></option>
<?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="tpl-form-group">
                  <label class="tpl-form-label" for="indexform-date">Желаемая дата</label>
                  <input type="date" id="indexform-date" class="tpl-form-input" name="date" />
                </div>
                <div class="tpl-form-group tpl-form-group--full">
                  <label class="tpl-form-label" for="indexform-comment">Комментарий</label>
                  <textarea id="indexform-comment" class="tpl-form-input" name="comment" rows="3" placeholder="Пожелания к записи или мастеру"></textarea>
                </div>
              </div>
              <div class="tpl-order__actions">
                <button type="submit" class="tpl-btn tpl-order__submit">Записаться на приём</button>
                <p class="tpl-safety">
                  <svg viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false"><rect width="16" height="16" rx="8" fill="currentColor" /><path d="M6.91335 10.5146L4.72834 8.32955L4 9.05788L6.91335 11.9712L13.1562 5.72834L12.4279 5L6.91335 10.5146Z" fill="#fff" /></svg>
                  Мы не передаём ваши данные третьим лицам
                </p>
              </div>
            </form>
          </div>
        </section>

        <section class="tpl-sect__reviews tpl-gutter-top" aria-labelledby="reviews-heading">
          <div class="tpl-container">
            <div class="tpl-sect-heading">
              <h2 id="reviews-heading">Отзывы гостей</h2>
              <p class="tpl-sect-heading__lead">Что говорят клиенты о визитах в <?= e($site_name) ?></p>
            </div>
<?php if ($reviewsTotal > 0): ?>
            <div class="tpl-reviews-summary">
              <div class="tpl-reviews-summary__main">
                <p class="tpl-reviews-summary__label">Средняя оценка</p>
                <div class="tpl-reviews-summary__score-row">
                  <p class="tpl-reviews-summary__score"><?= e($reviewsAverageLabel) ?></p>
                  <span class="tpl-review-stars tpl-reviews-summary__stars" aria-hidden="true"><?= review_stars_html($reviewsAverage) ?></span>
                </div>
              </div>
              <p class="tpl-reviews-summary__count">На основе <strong><?= e(reviews_count_label($reviewsTotal)) ?></strong></p>
            </div>
<?php
    $reviews_list = $reviewsHome;
    include __DIR__ . '/content/reviews-list.php';
    if ($showAllReviewsLink):
        $more_href = site_url('reviews');
        $more_label = 'Посмотреть все отзывы';
        $more_meta = 'Всего ' . reviews_count_label($reviewsTotal);
        $more_variant = 'reviews';
        include __DIR__ . '/partials/section-more.php';
    endif;
elseif ($reviewsTotal === 0):
    $empty_variant = 'reviews';
    $empty_message = 'Отзывов пока нет — будьте первым';
    $empty_hint = 'Расскажите о визите: отзыв появится на сайте после модерации.';
    $empty_action_label = 'Написать отзыв';
    $empty_action_id = 'review-modal-open';
    $empty_action_href = '';
    include __DIR__ . '/partials/section-empty.php';
endif;
?>
            <div class="tpl-reviews-cta">
              <div class="tpl-reviews-cta__text-block">
                <p class="tpl-reviews-cta__title">Поделитесь впечатлением</p>
                <p class="tpl-reviews-cta__text">Были у нас? Расскажите о визите — это помогает другим гостям.</p>
              </div>
              <button type="button" class="tpl-btn tpl-reviews-cta__btn" id="review-modal-open">Написать отзыв</button>
            </div>
          </div>
        </section>
