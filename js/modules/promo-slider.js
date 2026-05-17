/** Слайдер акций на главном экране */
export function initPromoSlider() {
  const root = document.getElementById("promo-slider");
  if (!root) return;

  const viewport = root.querySelector(".tpl-promo-slider__viewport");
  const track = document.getElementById("promo-slider-track");
  if (!track || !viewport) return;

  const slides = [...track.querySelectorAll(".tpl-promo-slide")];
  if (slides.length === 0) return;

  const dots = [...root.querySelectorAll(".tpl-promo-slider__dot")];
  const prevBtn = document.getElementById("promo-slider-prev");
  const nextBtn = document.getElementById("promo-slider-next");
  const offer = document.getElementById("promo-offer");
  const offerStrong = document.getElementById("promo-offer-strong");
  const offerText = document.getElementById("promo-offer-text");

  let index = slides.findIndex((slide) => slide.classList.contains("is-active"));
  if (index < 0) index = 0;

  let autoplayTimer = null;
  const AUTOPLAY_MS = 7000;
  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  const slideWidth = () => viewport.clientWidth;

  const applySlideWidths = () => {
    const width = Math.round(slideWidth());
    slides.forEach((slide) => {
      slide.style.flex = `0 0 ${width}px`;
      slide.style.width = `${width}px`;
      slide.style.minWidth = `${width}px`;
      slide.style.maxWidth = `${width}px`;
    });
    track.style.width = `${width * slides.length}px`;
  };

  const updateOffer = (slide) => {
    if (!slide) return;
    offerStrong && (offerStrong.textContent = slide.dataset.offerStrong || "");
    offerText && (offerText.textContent = slide.dataset.offerText || "");
    offer?.setAttribute("aria-label", slide.dataset.offerLabel || "");
  };

  const goTo = (nextIndex, { skipResize = false } = {}) => {
    index = (nextIndex + slides.length) % slides.length;
    if (!skipResize) applySlideWidths();

    const width = Math.round(slideWidth());
    const offset = index * width;
    track.style.transform = `translate3d(-${offset}px, 0, 0)`;

    slides.forEach((slide, i) => {
      slide.classList.toggle("is-active", i === index);
    });

    dots.forEach((dot, i) => {
      const active = i === index;
      dot.classList.toggle("is-active", active);
      dot.setAttribute("aria-selected", String(active));
    });

    updateOffer(slides[index]);
    restartAutoplay();
  };

  const stopAutoplay = () => {
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  };

  const restartAutoplay = () => {
    stopAutoplay();
    if (reducedMotion || document.hidden) return;
    autoplayTimer = setInterval(() => goTo(index + 1, { skipResize: true }), AUTOPLAY_MS);
  };

  document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
      stopAutoplay();
    } else {
      restartAutoplay();
    }
  });

  prevBtn?.addEventListener("click", () => goTo(index - 1));
  nextBtn?.addEventListener("click", () => goTo(index + 1));

  dots.forEach((dot) => {
    dot.addEventListener("click", () => {
      const i = Number(dot.dataset.promoGo);
      if (!Number.isNaN(i)) goTo(i);
    });
  });

  root.addEventListener("mouseenter", stopAutoplay);
  root.addEventListener("mouseleave", restartAutoplay);
  root.addEventListener("focusin", stopAutoplay);
  root.addEventListener("focusout", (event) => {
    if (!root.contains(event.relatedTarget)) restartAutoplay();
  });

  root.addEventListener("keydown", (event) => {
    if (event.key === "ArrowLeft") {
      event.preventDefault();
      goTo(index - 1);
    }
    if (event.key === "ArrowRight") {
      event.preventDefault();
      goTo(index + 1);
    }
  });

  window.addEventListener("resize", () => goTo(index));

  goTo(index);
}
