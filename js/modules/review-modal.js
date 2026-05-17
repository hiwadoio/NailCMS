import { createScrollLock } from "./scroll-lock.js";
import { createCaptchaLoader, createFormMessage } from "./modal-helpers.js";

/** Модальное окно отправки отзыва на модерацию */
export function initReviewModal() {
  const modal = document.getElementById("review-modal");
  const openBtn = document.getElementById("review-modal-open");
  const form = document.getElementById("review-submit-form");
  if (!modal || !openBtn || !form) return;

  const captchaQuestion = document.getElementById("review-captcha-question");
  const captchaInput = document.getElementById("review-captcha");
  const messageEl = document.getElementById("review-form-message");
  const submitBtn = document.getElementById("review-form-submit");
  const ratingInput = document.getElementById("review-rating");
  const starButtons = modal.querySelectorAll(".tpl-review-rating-picker__star");
  const scrollLock = createScrollLock();
  const { showMessage, hideMessage } = createFormMessage(messageEl);
  const loadCaptcha = createCaptchaLoader(
    "/api/review-captcha.php",
    captchaQuestion,
    captchaInput
  );

  let lastFocus = null;
  let isOpen = false;

  const setRating = (value) => {
    if (!ratingInput) return;
    ratingInput.value = String(value);
    starButtons.forEach((btn) => {
      const starValue = Number(btn.getAttribute("data-rating"));
      btn.classList.toggle("is-active", starValue <= value);
      btn.setAttribute("aria-pressed", starValue <= value ? "true" : "false");
    });
  };

  const resetForm = () => {
    form.reset();
    setRating(5);
  };

  starButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const value = Number(btn.getAttribute("data-rating"));
      if (value >= 1 && value <= 5) {
        setRating(value);
      }
    });
  });

  setRating(5);

  const open = async () => {
    if (isOpen) return;
    isOpen = true;
    lastFocus = document.activeElement;
    hideMessage();
    resetForm();
    await loadCaptcha();
    modal.classList.add("tpl-service-modal--open");
    modal.setAttribute("aria-hidden", "false");
    scrollLock.lock();
    const firstField = form.querySelector("#review-author");
    if (firstField instanceof HTMLElement) {
      firstField.focus();
    }
  };

  const close = () => {
    if (!isOpen) return;
    isOpen = false;
    modal.classList.remove("tpl-service-modal--open");
    modal.setAttribute("aria-hidden", "true");
    scrollLock.unlock();
    hideMessage();
    if (lastFocus instanceof HTMLElement) {
      lastFocus.focus();
    }
  };

  openBtn.addEventListener("click", () => {
    open();
  });

  modal.querySelectorAll("[data-review-close]").forEach((el) => {
    el.addEventListener("click", close);
  });

  modal.addEventListener("click", (event) => {
    if (event.target === modal.querySelector(".tpl-service-modal__backdrop")) {
      close();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && isOpen) {
      close();
    }
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    hideMessage();

    if (submitBtn) {
      submitBtn.disabled = true;
    }

    try {
      const formData = new FormData(form);
      const response = await fetch("/api/submit-review.php", {
        method: "POST",
        body: formData,
        credentials: "same-origin",
        headers: { Accept: "application/json" },
      });

      const data = await response.json();

      if (data.ok) {
        showMessage(data.message || "Отзыв отправлен на модерацию.", "success");
        resetForm();
        await loadCaptcha();
        window.setTimeout(close, 2200);
      } else {
        showMessage(data.message || "Не удалось отправить отзыв.", "error");
        await loadCaptcha();
      }
    } catch {
      showMessage("Ошибка сети. Попробуйте ещё раз.", "error");
      await loadCaptcha();
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
      }
    }
  });
}
