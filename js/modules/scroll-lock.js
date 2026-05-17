/** Блокировка прокрутки страницы (модальные окна) */
export function createScrollLock() {
  let scrollY = 0;
  let depth = 0;

  const onTouchMove = (e) => {
    if (depth > 0 && !e.target.closest(".tpl-service-modal")) {
      e.preventDefault();
    }
  };

  return {
    lock() {
      if (depth === 0) {
        scrollY = window.scrollY;
        document.documentElement.classList.add("tpl-modal-open");
        document.body.classList.add("tpl-modal-open");
        document.body.style.top = `-${scrollY}px`;
        document.addEventListener("touchmove", onTouchMove, { passive: false });
      }
      depth += 1;
    },
    unlock() {
      if (depth === 0) return;
      depth -= 1;
      if (depth > 0) return;

      document.documentElement.classList.remove("tpl-modal-open");
      document.body.classList.remove("tpl-modal-open");
      document.body.style.top = "";
      document.removeEventListener("touchmove", onTouchMove);
      window.scrollTo(0, scrollY);
    },
  };
}
