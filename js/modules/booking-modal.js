import { createScrollLock } from "./scroll-lock.js";
import { createCaptchaLoader, createFormMessage } from "./modal-helpers.js";

const VISIT_DATE_FORMATTER = new Intl.DateTimeFormat("ru-RU", {
  day: "numeric",
  month: "long",
  year: "numeric",
});

/** Попап подтверждения записи с капчей */
export function initBookingModal(bookingForm) {
  const modal = document.getElementById("booking-modal");
  const confirmForm = document.getElementById("booking-confirm-form");
  if (!modal || !confirmForm || !bookingForm) return null;

  const summaryEl = document.getElementById("booking-modal-summary");
  const captchaQuestion = document.getElementById("booking-captcha-question");
  const captchaInput = document.getElementById("booking-captcha");
  const messageEl = document.getElementById("booking-form-message");
  const submitBtn = document.getElementById("booking-form-submit");
  const hiddenFields = {
    name: document.getElementById("booking-hidden-name"),
    phone: document.getElementById("booking-hidden-phone"),
    email: document.getElementById("booking-hidden-email"),
    service: document.getElementById("booking-hidden-service"),
    date: document.getElementById("booking-hidden-date"),
    comment: document.getElementById("booking-hidden-comment"),
  };

  const scrollLock = createScrollLock();
  const { showMessage, hideMessage } = createFormMessage(messageEl);
  const loadCaptcha = createCaptchaLoader(
    "/api/booking-captcha.php",
    captchaQuestion,
    captchaInput
  );
  let lastFocus = null;
  let isOpen = false;

  const formatVisitDate = (value) => {
    if (!value) return "не указана";
    const parts = value.split("-").map(Number);
    if (parts.length !== 3) return value;
    const date = new Date(parts[0], parts[1] - 1, parts[2]);
    if (Number.isNaN(date.getTime())) return value;
    return VISIT_DATE_FORMATTER.format(date);
  };

  const readBookingForm = () => ({
    name: bookingForm.elements.namedItem("name")?.value?.trim() ?? "",
    phone: bookingForm.elements.namedItem("phone")?.value?.trim() ?? "",
    email: bookingForm.elements.namedItem("email")?.value?.trim() ?? "",
    service: bookingForm.elements.namedItem("service")?.value?.trim() ?? "",
    date: bookingForm.elements.namedItem("date")?.value?.trim() ?? "",
    comment: bookingForm.elements.namedItem("comment")?.value?.trim() ?? "",
  });

  const syncHiddenFields = (data) => {
    hiddenFields.name.value = data.name;
    hiddenFields.phone.value = data.phone;
    hiddenFields.email.value = data.email;
    hiddenFields.service.value = data.service;
    hiddenFields.date.value = data.date;
    hiddenFields.comment.value = data.comment;
  };

  const renderSummary = (data) => {
    if (!summaryEl) return;

    const rows = [
      ["Имя", data.name],
      ["Телефон", data.phone],
      ["Email", data.email || "не указан"],
      ["Услуга", data.service],
      ["Дата визита", formatVisitDate(data.date)],
    ];

    if (data.comment) {
      rows.push(["Комментарий", data.comment]);
    }

    summaryEl.replaceChildren(
      ...rows.map(([label, value]) => {
        const dt = document.createElement("dt");
        dt.textContent = label;
        const dd = document.createElement("dd");
        dd.textContent = value;
        return [dt, dd];
      }).flat()
    );
  };

  const open = async (data) => {
    if (isOpen) return;
    isOpen = true;
    lastFocus = document.activeElement;
    hideMessage();
    syncHiddenFields(data);
    renderSummary(data);
    await loadCaptcha();
    modal.classList.add("tpl-service-modal--open");
    modal.setAttribute("aria-hidden", "false");
    scrollLock.lock();
    captchaInput?.focus();
  };

  const close = () => {
    if (!isOpen) return;
    isOpen = false;
    modal.classList.remove("tpl-service-modal--open");
    modal.setAttribute("aria-hidden", "true");
    scrollLock.unlock();
    hideMessage();
    confirmForm.reset();
    if (lastFocus instanceof HTMLElement) {
      lastFocus.focus();
    }
  };

  modal.querySelectorAll("[data-booking-close]").forEach((el) => {
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

  confirmForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    hideMessage();

    if (!confirmForm.reportValidity()) return;

    if (submitBtn) {
      submitBtn.disabled = true;
    }

    try {
      const formData = new FormData(confirmForm);
      const response = await fetch("/api/submit-booking.php", {
        method: "POST",
        body: formData,
        credentials: "same-origin",
        headers: { Accept: "application/json" },
      });

      const data = await response.json();

      if (data.ok) {
        showMessage(data.message || "Заявка принята.", "success");
        bookingForm.reset();
        bookingForm.classList.remove("was-validated");
        window.setTimeout(close, 2200);
      } else {
        showMessage(data.message || "Не удалось отправить заявку.", "error");
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

  return { open, close, readBookingForm };
}
