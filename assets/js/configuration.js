document.addEventListener("DOMContentLoaded", () => {
  const modal = document.querySelector("#configuration-modal");
  const form = modal?.querySelector("form");
  const title = modal?.querySelector("h2");
  if (title)
    title.innerHTML =
      '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18M3 12h18M5 5l14 14M19 5 5 19"/></svg>Editar configuración';
  const edit = document.querySelector(".configuration-edit");
  let selected = null;
  const rows = [...document.querySelectorAll(".configuration-row")];
  rows.forEach((row) =>
    row.addEventListener("click", () => {
      rows.forEach((item) => item.classList.remove("selected"));
      row.classList.add("selected");
      selected = row;
      if (edit) edit.disabled = false;
    }),
  );
  edit?.addEventListener("click", () => {
    if (!selected) return;
    form.elements.clave.value = selected.dataset.key;
    form.elements.clave_visible.value = selected.dataset.key;
    form.elements.valor.value = selected.dataset.value || "";
    modal.removeAttribute("hidden");
  });
  document
    .querySelectorAll("[data-configuration-close]")
    .forEach((button) =>
      button.addEventListener("click", () => modal?.setAttribute("hidden", "")),
    );
});
