import { createScrollLock } from "./scroll-lock.js";

/** Модальное окно с описанием услуги */
export function initServiceModal() {
  const modal = document.getElementById("service-modal");
  if (!modal) return;

  const panel = modal.querySelector(".tpl-service-modal__panel");
  const img = modal.querySelector(".tpl-service-modal__img");
  const titleEl = modal.querySelector(".tpl-service-modal__title");
  const textEl = modal.querySelector(".tpl-service-modal__text");
  const detailEl = modal.querySelector(".tpl-service-modal__detail");
  const priceEl = modal.querySelector(".tpl-service-modal__price");
  const bookBtn = document.getElementById("service-modal-book");
  const serviceInput = document.getElementById("indexform-service");
  const formHeading = document.getElementById("offers-form-heading");
  const scrollLock = createScrollLock();

  let lastFocus = null;
  let isOpen = false;

  const setField = (el, value) => {
    const text = (value || "").trim();
    if (!el) return;
    el.textContent = text;
    el.hidden = !text;
  };

  const setImage = (url, alt) => {
    if (!img) return;
    const src = (url || "").trim();
    img.alt = alt || "";
    img.classList.add("tpl-service-modal__img--loading");

    if (!src) {
      img.removeAttribute("src");
      img.classList.remove("tpl-service-modal__img--loading");
      return;
    }

    const onDone = () => {
      img.classList.remove("tpl-service-modal__img--loading");
      img.removeEventListener("load", onDone);
      img.removeEventListener("error", onDone);
    };

    img.addEventListener("load", onDone, { once: true });
    img.addEventListener("error", onDone, { once: true });

    if (img.getAttribute("src") !== src) {
      img.src = src;
    } else {
      onDone();
    }
  };

  const getFocusable = () => {
    if (!panel) return [];
    return [...panel.querySelectorAll(
      'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )].filter((el) => el.offsetParent !== null);
  };

  const openFromButton = (btn) => {
    const title = btn.dataset.title || "";
    setField(titleEl, title);
    setField(textEl, btn.dataset.text || "");
    setField(detailEl, btn.dataset.detail || "");
    setField(priceEl, btn.dataset.price || "");
    setImage(btn.dataset.image || "", title);

    if (bookBtn) bookBtn.dataset.service = title;

    if (!isOpen) {
      lastFocus = document.activeElement;
      isOpen = true;
      modal.classList.add("tpl-service-modal--open");
      modal.setAttribute("aria-hidden", "false");
      scrollLock.lock();
    }

    requestAnimationFrame(() => {
      const focusable = getFocusable();
      (focusable[0] || modal.querySelector(".tpl-service-modal__close"))?.focus();
    });
  };

  const closeModal = ({ skipFocusRestore = false } = {}) => {
    if (!isOpen) return;
    isOpen = false;
    modal.classList.remove("tpl-service-modal--open");
    modal.setAttribute("aria-hidden", "true");
    scrollLock.unlock();

    if (!skipFocusRestore && lastFocus?.focus) {
      lastFocus.focus();
    }
    lastFocus = null;
  };

  const goToBooking = (service) => {
    if (!serviceInput) {
      closeModal({ skipFocusRestore: true });
      const bookingUrl = document.body.dataset.bookingUrl;
      if (bookingUrl) {
        window.location.href = bookingUrl;
      }
      return;
    }

    if (service) {
      serviceInput.value = service;
    }
    closeModal({ skipFocusRestore: true });
    formHeading?.scrollIntoView({ behavior: "smooth", block: "start" });
    requestAnimationFrame(() => serviceInput.focus({ preventScroll: true }));
  };

  document.addEventListener("click", (event) => {
    const openBtn = event.target.closest(".tpl-js-service-open");
    if (openBtn) {
      event.preventDefault();
      openFromButton(openBtn);
      return;
    }

    if (!isOpen) return;
    if (event.target.closest("[data-service-close]")) {
      event.preventDefault();
      closeModal();
    }
  });

  bookBtn?.addEventListener("click", (event) => {
    event.preventDefault();
    goToBooking(bookBtn.dataset.service || titleEl?.textContent || "");
  });

  document.addEventListener("keydown", (event) => {
    if (!isOpen) return;

    if (event.key === "Escape") {
      event.preventDefault();
      closeModal();
      return;
    }

    if (event.key !== "Tab" || !panel) return;

    const focusable = getFocusable();
    if (focusable.length === 0) return;

    const first = focusable[0];
    const last = focusable.at(-1);

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });
}
