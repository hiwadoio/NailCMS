<?php declare(strict_types=1); ?>
      <footer class="tpl-footer" itemscope itemtype="https://schema.org/NailSalon">
        <meta itemprop="name" content="<?= e($site_name) ?>" />
        <meta itemprop="url" content="<?= e(site_url()) ?>" />
        <meta itemprop="priceRange" content="₽₽" />
        <div class="tpl-container">
          <div class="tpl-footer__main">
            <div class="tpl-footer__brand-head">
              <img class="tpl-footer__logo-mark" src="<?= e(asset_url('assets/images/logo.png')) ?>" alt="" width="52" height="52" decoding="async" />
              <div class="tpl-footer__brand-text">
                <p class="tpl-footer__eyebrow">Маникюрный салон</p>
                <p class="tpl-footer__brand-name">Салон <span>«<?= e($brand_short) ?>»</span></p>
              </div>
            </div>
            <div class="tpl-footer__info">
              <p class="tpl-footer__about"><?= e($site_name) ?> — профессиональный маникюрный салон в <?= e($city) ?>. Оказываем услуги маникюра, педикюра, покрытия гель-лаком и nail-арт с соблюдением санитарных норм и использованием сертифицированных материалов.</p>
              <p class="tpl-footer__details">Работаем ежедневно с 10:00 до 21:00. Запись на приём — по телефону, электронной почте или через форму на сайте. Перед процедурой мастер проводит консультацию и подбирает оптимальный формат ухода.</p>
            </div>
          </div>
          <div class="tpl-footer__bottom">
            <p class="tpl-footer__legal">© <?= date('Y') ?> <?= e($site_name) ?>. Все права защищены.</p>
            <address class="tpl-footer__contacts">
              <span class="tpl-footer__contacts-label">Контакты</span>
              <a class="tpl-footer__value tpl-footer__value--link" href="tel:<?= e($phone_tel) ?>" itemprop="telephone"><?= e($phone_display) ?></a>
              <span class="tpl-footer__contacts-sep" aria-hidden="true">·</span>
              <a class="tpl-footer__value tpl-footer__value--link" href="mailto:<?= e($email) ?>" itemprop="email"><?= e($email) ?></a>
            </address>
          </div>
          <p class="tpl-footer__disclaimer">
            <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.5" /><path d="M12 8v5M12 16h.01" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" /></svg>
            Маникюрные процедуры могут иметь противопоказания. Перед визитом сообщите мастеру об аллергиях, беременности и состоянии кожи или ногтей.
          </p>
        </div>
      </footer>

      <div class="tpl-service-modal tpl-review-modal" id="review-modal" role="dialog" aria-modal="true" aria-labelledby="review-modal-title" aria-hidden="true">
        <div class="tpl-service-modal__backdrop" data-review-close tabindex="-1"></div>
        <div class="tpl-service-modal__panel tpl-review-modal__panel" role="document">
          <button type="button" class="tpl-service-modal__close" data-review-close aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
          <div class="tpl-review-modal__body">
            <header class="tpl-review-modal__head">
              <h3 class="tpl-review-modal__title" id="review-modal-title">Написать отзыв</h3>
              <p class="tpl-review-modal__lead">После проверки отзыв появится на сайте</p>
            </header>
            <form id="review-submit-form" class="tpl-review-modal__form" novalidate>
              <div class="tpl-review-modal__grid">
                <div class="tpl-form-group">
                  <label class="tpl-form-label" for="review-author">Имя</label>
                  <input type="text" id="review-author" class="tpl-form-input tpl-review-modal__input" name="author" autocomplete="name" placeholder="Анна К." required maxlength="80" />
                </div>
                <div class="tpl-form-group">
                  <label class="tpl-form-label" for="review-service">Услуга</label>
                  <div class="tpl-form-select-wrap">
                    <select id="review-service" class="tpl-form-input tpl-form-select tpl-review-modal__input" name="service" required>
                      <option value="">Выберите услугу</option>
                      <?php foreach ($services as $service): ?>
                        <option value="<?= e($service['name']) ?>"><?= e($service['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="tpl-form-group tpl-review-modal__rating-field">
                <span class="tpl-form-label" id="review-rating-label">Оценка</span>
                <input type="hidden" name="rating" id="review-rating" value="5" required />
                <div class="tpl-review-rating-picker" role="radiogroup" aria-labelledby="review-rating-label">
                  <?php for ($star = 1; $star <= 5; $star++): ?>
                  <button type="button" class="tpl-review-rating-picker__star<?= $star === 5 ? ' is-active' : '' ?>" data-rating="<?= $star ?>" aria-label="<?= $star ?> из 5">★</button>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="tpl-form-group">
                <label class="tpl-form-label" for="review-text">Отзыв</label>
                <textarea id="review-text" class="tpl-form-input tpl-review-modal__input tpl-review-modal__textarea" name="text" rows="4" placeholder="Что понравилось, как прошёл визит…" required maxlength="2000"></textarea>
              </div>
              <div class="tpl-review-modal__captcha-row">
                <label class="tpl-review-modal__captcha-label" for="review-captcha">
                  <span class="tpl-form-label">Проверка</span>
                  <span class="tpl-review-modal__captcha-task">Сколько будет <strong id="review-captcha-question">?</strong>?</span>
                </label>
                <input type="number" id="review-captcha" class="tpl-form-input tpl-review-modal__input tpl-review-modal__captcha-input" name="captcha" inputmode="numeric" autocomplete="off" required />
              </div>
              <input type="text" class="tpl-review-modal__honeypot" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" />
              <p class="tpl-review-modal__message" id="review-form-message" hidden role="alert"></p>
              <div class="tpl-review-modal__actions">
                <button type="submit" class="tpl-btn tpl-review-modal__submit" id="review-form-submit">Отправить</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="tpl-service-modal tpl-booking-modal" id="booking-modal" role="dialog" aria-modal="true" aria-labelledby="booking-modal-title" aria-hidden="true">
        <div class="tpl-service-modal__backdrop" data-booking-close tabindex="-1"></div>
        <div class="tpl-service-modal__panel tpl-booking-modal__panel" role="document">
          <button type="button" class="tpl-service-modal__close" data-booking-close aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
          <div class="tpl-booking-modal__body">
            <header class="tpl-booking-modal__head">
              <h3 class="tpl-booking-modal__title" id="booking-modal-title">Подтверждение записи</h3>
              <p class="tpl-booking-modal__lead">Проверьте данные и решите пример — после этого заявка уйдёт администратору</p>
            </header>
            <dl class="tpl-booking-modal__summary" id="booking-modal-summary"></dl>
            <form id="booking-confirm-form" class="tpl-booking-modal__form" novalidate>
              <input type="hidden" name="name" id="booking-hidden-name" />
              <input type="hidden" name="phone" id="booking-hidden-phone" />
              <input type="hidden" name="email" id="booking-hidden-email" />
              <input type="hidden" name="service" id="booking-hidden-service" />
              <input type="hidden" name="date" id="booking-hidden-date" />
              <input type="hidden" name="comment" id="booking-hidden-comment" />
              <div class="tpl-booking-modal__captcha-row">
                <label class="tpl-booking-modal__captcha-label" for="booking-captcha">
                  <span class="tpl-form-label">Проверка</span>
                  <span class="tpl-booking-modal__captcha-task">Сколько будет <strong id="booking-captcha-question">?</strong>?</span>
                </label>
                <input type="number" id="booking-captcha" class="tpl-form-input tpl-booking-modal__input tpl-booking-modal__captcha-input" name="captcha" inputmode="numeric" autocomplete="off" required />
              </div>
              <input type="text" class="tpl-booking-modal__honeypot" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" />
              <p class="tpl-booking-modal__message" id="booking-form-message" hidden role="alert"></p>
              <div class="tpl-booking-modal__actions">
                <button type="button" class="tpl-booking-modal__back" data-booking-close>Назад</button>
                <button type="submit" class="tpl-btn tpl-booking-modal__submit" id="booking-form-submit">Отправить заявку</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="tpl-service-modal" id="service-modal" role="dialog" aria-modal="true" aria-labelledby="service-modal-title" aria-hidden="true">
        <div class="tpl-service-modal__backdrop" data-service-close tabindex="-1"></div>
        <div class="tpl-service-modal__panel" role="document">
          <button type="button" class="tpl-service-modal__close" data-service-close aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
          <div class="tpl-service-modal__media">
            <img class="tpl-service-modal__img" alt="" width="800" height="450" decoding="async" />
          </div>
          <div class="tpl-service-modal__body">
            <p class="tpl-service-modal__label">Услуга</p>
            <h3 class="tpl-service-modal__title" id="service-modal-title"></h3>
            <p class="tpl-service-modal__text" hidden></p>
            <p class="tpl-service-modal__detail" hidden></p>
          </div>
          <div class="tpl-service-modal__footer">
            <div class="tpl-service-modal__price-block">
              <span class="tpl-service-modal__price-label">Стоимость</span>
              <p class="tpl-service-modal__price"></p>
            </div>
            <button type="button" class="tpl-btn tpl-service-modal__book" id="service-modal-book">Записаться</button>
          </div>
        </div>
      </div>
    </div>
    <script type="module" src="<?= e(asset_url('js/main.js')) ?>"></script>
  </body>
</html>
