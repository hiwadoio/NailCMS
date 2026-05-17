/** Бургер-меню шапки (≤991px) */
export function initNavBurger() {
  const burger = document.getElementById("nav-burger");
  const panel = document.getElementById("nav-panel");
  const closeBtn = document.getElementById("nav-close");
  const backdrop = document.getElementById("nav-backdrop");
  if (!burger || !panel) return;

  const mq = window.matchMedia("(max-width: 991px)");
  let isOpen = false;

  const close = () => {
    if (!isOpen) return;
    isOpen = false;
    burger.setAttribute("aria-expanded", "false");
    burger.setAttribute("aria-label", "Открыть меню");
    panel.classList.remove("is-open");
    panel.setAttribute("aria-hidden", "true");
    document.body.classList.remove("tpl-nav-open");
    backdrop?.setAttribute("hidden", "");
  };

  const open = () => {
    if (isOpen) return;
    isOpen = true;
    burger.setAttribute("aria-expanded", "true");
    burger.setAttribute("aria-label", "Закрыть меню");
    panel.classList.add("is-open");
    panel.setAttribute("aria-hidden", "false");
    document.body.classList.add("tpl-nav-open");
    backdrop?.removeAttribute("hidden");
  };

  burger.addEventListener("click", () => {
    if (isOpen) close();
    else open();
  });

  backdrop?.addEventListener("click", close);
  closeBtn?.addEventListener("click", close);

  panel.querySelectorAll("a.tpl-nav__link, a.tpl-nav__dropdown-link").forEach((link) => {
    link.addEventListener("click", () => {
      if (mq.matches) close();
    });
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && isOpen) close();
  });

  mq.addEventListener("change", () => {
    if (!mq.matches) {
      close();
      panel.removeAttribute("aria-hidden");
    } else {
      panel.setAttribute("aria-hidden", "true");
    }
  });

  if (mq.matches) {
    panel.setAttribute("aria-hidden", "true");
  } else {
    panel.removeAttribute("aria-hidden");
  }
}
