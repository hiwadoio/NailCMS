/** Выпадающее меню «Салон» в шапке */
export function initNavDropdown() {
  const trigger = document.getElementById("nav-dropdown-trigger");
  const panel = document.getElementById("nav-dropdown-panel");
  if (!trigger || !panel) return;

  const item = trigger.closest(".tpl-nav__item--has-dropdown");
  const mq = window.matchMedia("(max-width: 991px)");
  let isOpen = false;

  const close = () => {
    if (!isOpen) return;
    isOpen = false;
    trigger.setAttribute("aria-expanded", "false");
    panel.hidden = true;
    item?.classList.remove("is-open");
  };

  const open = () => {
    if (isOpen) return;
    isOpen = true;
    trigger.setAttribute("aria-expanded", "true");
    panel.hidden = false;
    item?.classList.add("is-open");
  };

  const resetForViewport = () => {
    close();
  };

  trigger.addEventListener("click", (event) => {
    event.stopPropagation();
    if (isOpen) close();
    else open();
  });

  document.addEventListener("click", (event) => {
    if (mq.matches || !isOpen) return;
    if (!item?.contains(event.target)) close();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && isOpen) close();
  });

  panel.querySelectorAll(".tpl-nav__dropdown-link").forEach((link) => {
    link.addEventListener("click", () => {
      if (!mq.matches) close();
    });
  });

  mq.addEventListener("change", resetForViewport);
  resetForViewport();
}
