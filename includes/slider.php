<?php declare(strict_types=1); ?>
        <section class="tpl-sect__promo" aria-label="Акции <?= e($site_name) ?>">
          <div class="tpl-container">
            <div class="tpl-promo-slider-shell">
              <button type="button" class="tpl-promo-slider__arrow tpl-promo-slider__arrow--prev" id="promo-slider-prev" aria-label="Предыдущий слайд">
                <svg viewBox="0 0 20 20" width="20" height="20" aria-hidden="true"><path d="M12 5 7 10 12 15" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" /></svg>
              </button>
              <div class="tpl-promo-slider" id="promo-slider" role="region" aria-label="Слайдер акций" tabindex="0">
                <div class="tpl-promo-slider__viewport" aria-live="polite">
                  <div class="tpl-promo-slider__track" id="promo-slider-track">
                  <div class="tpl-promo-slide is-active" role="group" aria-roledescription="slide" aria-label="1 из 3" data-offer-strong="Комбо «Маникюр + гель-лак»" data-offer-text="— скидка 15% при записи онлайн" data-offer-label="Записаться на комбо: маникюр и гель-лак со скидкой 15%">
                    <div class="tpl-row tpl-align-vertical-center tpl-align-horizontal-between tpl-promo__row">
                      <div class="tpl-promo-content">
                        <div class="tpl-promo-content__title tpl-content"><h1 id="promo-heading">Маникюрный салон «<?= e($brand_short) ?>» — красота в каждой детали</h1></div>
                        <div class="tpl-promo-content__text tpl-content"><p class="tpl-tag">АКЦИЯ МЕСЯЦА</p><p>Скидка 15% на первый визит при онлайн-записи. Стерильные инструменты, премиальные материалы и опытные мастера.</p></div>
                      </div>
                      <div class="tpl-promo-img"><img src="<?= e(asset_url('assets/images/banner.png')) ?>" alt="Мастер выполняет маникюр в <?= e($site_name) ?>" width="306" height="252" fetchpriority="high" loading="eager" decoding="async" /></div>
                    </div>
                  </div>
                  <div class="tpl-promo-slide" role="group" aria-roledescription="slide" aria-label="2 из 3" data-offer-strong="Гель-лак + укрепление" data-offer-text="— фиксированная цена при записи онлайн" data-offer-label="Записаться на гель-лак и укрепление">
                    <div class="tpl-row tpl-align-vertical-center tpl-align-horizontal-between tpl-promo__row">
                      <div class="tpl-promo-content">
                        <div class="tpl-promo-content__title tpl-content"><h2>Гель-лак и авторский дизайн</h2></div>
                        <div class="tpl-promo-content__text tpl-content"><p class="tpl-tag">ПРЕМИУМ ПОКРЫТИЕ</p><p>Стойкое покрытие до 3 недель, большая палитра оттенков и аккуратная работа с кутикулой.</p></div>
                      </div>
                      <div class="tpl-promo-img"><img src="<?= e(asset_url('assets/images/banner.png')) ?>" alt="Покрытие гель-лаком в <?= e($site_name) ?>" width="306" height="252" loading="lazy" decoding="async" /></div>
                    </div>
                  </div>
                  <div class="tpl-promo-slide" role="group" aria-roledescription="slide" aria-label="3 из 3" data-offer-strong="SPA-уход для рук" data-offer-text="— подарок при записи на педикюр" data-offer-label="Записаться на SPA-уход и педикюр">
                    <div class="tpl-row tpl-align-vertical-center tpl-align-horizontal-between tpl-promo__row">
                      <div class="tpl-promo-content">
                        <div class="tpl-promo-content__title tpl-content"><h2>Педикюр и SPA-уход для рук</h2></div>
                        <div class="tpl-promo-content__text tpl-content"><p class="tpl-tag">ЗАБОТА О СЕБЕ</p><p>Аппаратный педикюр, расслабляющий массаж и питательные маски — для безупречного результата.</p></div>
                      </div>
                      <div class="tpl-promo-img"><img src="<?= e(asset_url('assets/images/banner.png')) ?>" alt="SPA-уход для рук в <?= e($site_name) ?>" width="306" height="252" loading="lazy" decoding="async" /></div>
                    </div>
                  </div>
                  </div>
                </div>
                <div class="tpl-promo-slider__controls">
                  <div class="tpl-promo-slider__dots" id="promo-slider-dots" role="tablist" aria-label="Слайды акций">
                    <button type="button" class="tpl-promo-slider__dot is-active" role="tab" aria-selected="true" aria-label="Слайд 1" data-promo-go="0"></button>
                    <button type="button" class="tpl-promo-slider__dot" role="tab" aria-selected="false" aria-label="Слайд 2" data-promo-go="1"></button>
                    <button type="button" class="tpl-promo-slider__dot" role="tab" aria-selected="false" aria-label="Слайд 3" data-promo-go="2"></button>
                  </div>
                </div>
                <a class="tpl-promo-offer" id="promo-offer" href="#offers-form-heading" aria-label="Записаться на комбо: маникюр и гель-лак со скидкой 15%">
                  <span class="tpl-promo-offer__text"><strong id="promo-offer-strong">Комбо «Маникюр + гель-лак»</strong><span id="promo-offer-text">— скидка 15% при записи онлайн</span></span>
                  <span class="tpl-promo-offer__arrow" aria-hidden="true"><svg viewBox="0 0 20 20" width="20" height="20" aria-hidden="true"><path d="M11 15 16 10 11 5M15 10H4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg></span>
                </a>
              </div>
              <button type="button" class="tpl-promo-slider__arrow tpl-promo-slider__arrow--next" id="promo-slider-next" aria-label="Следующий слайд">
                <svg viewBox="0 0 20 20" width="20" height="20" aria-hidden="true"><path d="M8 5 13 10 8 15" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" /></svg>
              </button>
            </div>
          </div>
        </section>
