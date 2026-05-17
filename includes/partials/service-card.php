<?php
declare(strict_types=1);

if (!isset($service)) {
    return;
}
?>
              <article class="tpl-service-card" role="listitem">
                <div class="tpl-service-card__media">
                  <img src="<?= e(asset_url($service['image'])) ?>" alt="<?= e($service['name']) ?>" width="640" height="480" loading="lazy" decoding="async" />
                </div>
                <div class="tpl-service-card__body">
                  <div class="tpl-service-card__title-block">
                      <span class="tpl-service-card__kicker">Услуга</span>
                      <h3 class="tpl-service-card__title"><?= e($service['name']) ?></h3>
                  </div>
                  <p class="tpl-service-card__text"><?= e($service['text']) ?></p>
                  <div class="tpl-service-card__footer">
                    <p class="tpl-service-card__price"><?= e($service['price_display']) ?></p>
                    <button type="button" class="tpl-btn tpl-service-card__btn tpl-js-service-open"
                      data-title="<?= e($service['name']) ?>"
                      data-price="<?= e($service['price_display']) ?>"
                      data-image="<?= e(asset_url($service['image'])) ?>"
                      data-text="<?= e($service['text']) ?>"
                      data-detail="<?= e($service['detail']) ?>">
                      Об услуге
                    </button>
                  </div>
                </div>
              </article>
