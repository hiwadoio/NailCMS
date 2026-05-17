<?php
declare(strict_types=1);

if (!isset($review)) {
    return;
}

$rating = (float) ($review['rating'] ?? 5);
$scoreLabel = review_score_label($rating);
$dateIso = (string) ($review['date'] ?? '');
$dateDisplay = review_format_date($dateIso);
$avatar = review_initials((string) ($review['author'] ?? ''));
?>
              <article class="tpl-review-card" role="listitem">
                <header class="tpl-review-card__head">
                  <div class="tpl-review-card__profile">
                    <span class="tpl-review-card__avatar" aria-hidden="true"><?= e($avatar) ?></span>
                    <div class="tpl-review-card__person">
                      <cite class="tpl-review-card__author"><?= e((string) $review['author']) ?></cite>
                      <time class="tpl-review-card__date" datetime="<?= e($dateIso) ?>"><?= e($dateDisplay) ?></time>
                    </div>
                  </div>
                  <div class="tpl-review-card__meta">
                    <span class="tpl-review-card__service"><?= e((string) $review['service']) ?></span>
                    <div class="tpl-review-card__rating" aria-label="Оценка <?= e($scoreLabel) ?> из 5">
                      <span class="tpl-review-card__score"><?= e($scoreLabel) ?></span>
                      <span class="tpl-review-stars" aria-hidden="true"><?= review_stars_html($rating) ?></span>
                    </div>
                  </div>
                </header>
                <div class="tpl-review-card__text"><p><?= e((string) $review['text']) ?></p></div>
              </article>
