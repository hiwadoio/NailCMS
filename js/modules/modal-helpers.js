/** Сообщения об успехе/ошибке в модальных формах */
export function createFormMessage(messageEl) {
  const showMessage = (text, type) => {
    if (!messageEl) return;
    messageEl.textContent = text;
    messageEl.hidden = false;
    messageEl.classList.remove("is-success", "is-error");
    messageEl.classList.add(type === "success" ? "is-success" : "is-error");
  };

  const hideMessage = () => {
    if (!messageEl) return;
    messageEl.hidden = true;
    messageEl.textContent = "";
    messageEl.classList.remove("is-success", "is-error");
  };

  return { showMessage, hideMessage };
}

/** Загрузка вопроса капчи с API */
export function createCaptchaLoader(captchaUrl, captchaQuestion, captchaInput) {
  return async function loadCaptcha() {
    try {
      const response = await fetch(captchaUrl, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
      });
      const data = await response.json();
      if (data.ok && captchaQuestion) {
        captchaQuestion.textContent = data.question;
      }
      if (captchaInput) {
        captchaInput.value = "";
      }
    } catch {
      if (captchaQuestion) {
        captchaQuestion.textContent = "?";
      }
    }
  };
}
