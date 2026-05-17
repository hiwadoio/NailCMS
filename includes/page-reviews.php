<?php declare(strict_types=1); ?>
        <section class="tpl-sect__reviews tpl-gutter-top tpl-page-section" aria-labelledby="reviews-page-heading">
          <div class="tpl-container">
            <div class="tpl-sect-heading">
              <h1 id="reviews-page-heading">Все отзывы</h1>
              <p class="tpl-sect-heading__lead">Отзывы гостей о визитах в <?= e($site_name) ?></p>
            </div>
<?php
$reviewsTotal = count($reviews);
$reviewsAverage = $reviewsTotal > 0 ? reviews_average_rating($reviews) : 0;
$reviewsAverageLabel = $reviewsTotal > 0 ? review_score_label($reviewsAverage) : '—';

if ($reviewsTotal > 0):
?>
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
    $reviews_list = $reviews;
    include __DIR__ . '/content/reviews-list.php';
else:
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
            <div class="tpl-page-back">
              <a class="tpl-page-back__link" href="<?= e(site_url()) ?>">На главную</a>
            </div>
          </div>
        </section>
