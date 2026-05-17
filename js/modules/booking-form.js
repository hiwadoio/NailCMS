import { initBookingModal } from "./booking-modal.js";

/** Форма онлайн-записи — открывает попап с капчей */
export function initBookingForm() {
  const form = document.getElementById("booking-form");
  if (!form) return;

  const bookingModal = initBookingModal(form);
  if (!bookingModal) return;

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    form.classList.add("was-validated");
    if (!form.reportValidity()) return;

    bookingModal.open(bookingModal.readBookingForm());
  });
}
