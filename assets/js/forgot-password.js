(() => {
  "use strict";
  const form = document.querySelector("#forgot-password-form");
  const email = document.querySelector("#email");
  if (!form || !email) return;
  form.setAttribute("autocomplete", "off");
  email.setAttribute("autocomplete", "off");

  window.addEventListener("load", () => email.focus({ preventScroll: true }));

  const emailIcon = document.querySelector(
    "#email + .input-icon, .input-wrap .input-icon",
  );
  if (emailIcon) {
    emailIcon.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>';
  }

  const showAlert = (title, message) => {
    document.querySelector(".validation-alert")?.remove();
    const alert = document.createElement("div");
    alert.className = "form-alert";
    if (title === "Correo enviado") alert.classList.add("success-alert");
    alert.innerHTML = `<div class="alert-card"><div class="alert-main"><div class="alert-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m10.3 3.5-8 14A2 2 0 0 0 4 20.5h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01" stroke-linecap="round"/></svg></div><div class="alert-content"><strong>Validación</strong><span>${message}</span></div></div><div class="alert-actions"><button class="alert-confirm" type="button">✓&nbsp; Entendido</button></div></div>`;
    if (title === "Correo enviado") {
      alert.querySelector(".alert-content strong").textContent =
        "Correo enviado";
      alert.querySelector(".alert-icon").innerHTML =
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>';
    }
    document.body.append(alert);
    alert
      .querySelector(".alert-confirm")
      .addEventListener("click", () => alert.remove());
    alert.addEventListener("click", (event) => {
      if (event.target === alert) alert.remove();
    });
  };

  const showSending = () => {
    const modal = document.createElement("div");
    modal.className = "sending-modal";
    modal.innerHTML =
      '<div class="sending-dialog" role="status" aria-live="polite"><span class="sending-spinner" aria-hidden="true"></span><div><strong>Enviando correo...</strong><span>Estamos procesando tu solicitud.</span></div></div>';
    document.body.append(modal);
  };

  form.addEventListener("submit", (event) => {
    const value = email.value.trim();
    if (!value) {
      event.preventDefault();
      showAlert(
        "Correo requerido",
        "Ingresa tu correo electrónico para continuar.",
      );
      email.focus();
      return;
    }
    if (!email.validity.valid) {
      event.preventDefault();
      showAlert("Correo inválido", "Ingresa un correo electrónico válido.");
      email.focus();
      return;
    }
    event.preventDefault();
    showSending();
    fetch("forgot_password", {
      method: "POST",
      headers: { Accept: "application/json" },
      body: new FormData(form),
    })
      .then((response) =>
        response.json().then((data) => ({ ok: response.ok, data })),
      )
      .then(({ ok, data }) => {
        document.querySelector(".sending-modal")?.remove();
        showAlert(
          ok && data.ok ? "Correo enviado" : "Validación",
          data.message || "No se pudo procesar la solicitud.",
        );
      })
      .catch(() => {
        document.querySelector(".sending-modal")?.remove();
        showAlert("Error", "No se pudo contactar al servidor.");
      });
  });
})();
