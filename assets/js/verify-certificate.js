document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".verification-search");
  document
    .querySelector(".verification-form-alert .alert-confirm")
    ?.addEventListener("click", () =>
      document.querySelector(".verification-form-alert")?.remove(),
    );
  const input = form?.querySelector("input[name='codigo']");
  let searching = false;
  form?.addEventListener("submit", (event) => {
    if (input?.value.trim()) {
      if (searching) {
        event.preventDefault();
        return;
      }
      event.preventDefault();
      searching = true;
      const loading = document.createElement("div");
      loading.className = "verification-loading";
      loading.setAttribute("role", "status");
      loading.innerHTML =
        '<div class="verification-loading-card"><span class="verification-spinner"></span><span>Buscando certificado<span>...</span></span></div>';
      document.body.append(loading);
      window.setTimeout(() => HTMLFormElement.prototype.submit.call(form), 650);
      return;
    }
    if (searching) {
      event.preventDefault();
      return;
    }
    event.preventDefault();
    document.querySelector(".form-alert")?.remove();
    const alert = document.createElement("div");
    alert.className = "form-alert";
    alert.setAttribute("role", "alert");
    alert.innerHTML =
      '<div class="alert-card"><div class="alert-main"><div class="alert-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m10.3 3.5-8 14A2 2 0 0 0 4 20.5h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01" stroke-linecap="round"/></svg></div><div class="alert-content"><strong>Validación</strong><span>Completa el código de verificación.</span></div></div><div class="alert-actions"><button class="alert-confirm" type="button">✓&nbsp; Entendido</button></div></div>';
    document.querySelector(".verification-shell")?.prepend(alert);
    alert
      .querySelector("button")
      ?.addEventListener("click", () => alert.remove());
    input?.focus();
  });
  input?.addEventListener("input", () => input.setCustomValidity(""));
  input?.addEventListener("input", () => {
    input.value = input.value.toUpperCase();
  });
  document
    .querySelector(".verification-actions a:last-child")
    ?.addEventListener("click", async (event) => {
      event.preventDefault();
      const code =
        document.querySelector(".code-badge")?.textContent.trim() || "";
      const viewerUrl = event.currentTarget.href.replace(/&download=1$/, "");
      if (window.innerWidth <= 720) {
        window.location.href = viewerUrl.replace("certificado_pdf", "certificado_viewer");
        return;
      }
      const modal = document.createElement("div");
      modal.className = "verification-pdf-modal";
      modal.innerHTML = `<div class="verification-pdf-dialog" role="dialog" aria-modal="true"><header><h2><svg viewBox="0 0 24 24"><path d="M5 3h11l3 3v15H5z"/><path d="M8 9h8M8 13h6M8 17h5"/></svg>Vista previa del certificado</h2><button type="button" class="verification-pdf-close">×</button></header><div class="verification-pdf-body"><iframe title="Vista previa del certificado" src="${viewerUrl}&verificar=1&codigo=${encodeURIComponent(code)}"></iframe></div><footer><button type="button" class="verification-pdf-close">×&nbsp; Cerrar</button></footer></div>`;
      document.body.append(modal);
      const closeButton = modal.querySelector("footer button.verification-pdf-close");
      if (closeButton) {
        closeButton.className = "participant-modal-cancel verification-pdf-close";
        closeButton.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>Cerrar';
      }
      modal
        .querySelectorAll(".verification-pdf-close")
        .forEach((button) =>
          button.addEventListener("click", () => modal.remove()),
        );
      modal.addEventListener("click", (clickEvent) => {
        if (clickEvent.target === modal) modal.remove();
      });
    });
});
