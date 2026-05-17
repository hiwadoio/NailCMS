import { initPromoSlider } from "./modules/promo-slider.js";
import { initNavDropdown } from "./modules/nav-dropdown.js";
import { initNavBurger } from "./modules/nav-burger.js";
import { initServiceModal } from "./modules/service-modal.js";
import { initBookingForm } from "./modules/booking-form.js";
import { initReviewModal } from "./modules/review-modal.js";

document.addEventListener("DOMContentLoaded", () => {
  initPromoSlider();
  initNavDropdown();
  initNavBurger();
  initServiceModal();
  initReviewModal();
  initBookingForm();
});
