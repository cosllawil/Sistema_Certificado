document.addEventListener("DOMContentLoaded", () => {
  const rows = [...document.querySelectorAll(".role-row")];
  const modal = document.querySelector("#role-modal");
  const form = modal?.querySelector("form");
  const edit = document.querySelector(".role-edit");
  const title = document.querySelector("#role-modal-title");
  const icon = '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 3 7l9 4 9-4-9-4Zm-7 8v5l7 4 7-4v-5"/></svg>';
  const buttons = modal?.querySelectorAll(".form-actions button");
  if (buttons?.[0]) buttons[0].innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>Cancelar';
  if (buttons?.[1]) buttons[1].innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h11l3 3v13H5zM8 4v5h8V4M8 16h8"/></svg>Guardar rol';
  let selected = null;
  document.querySelectorAll("[data-role-close]").forEach((button) => button.addEventListener("click", () => modal.setAttribute("hidden", "")));
  const permissionBoxes = () => [...form.querySelectorAll("input[name='permisos[]']")];
  document.querySelector("[data-role-new]")?.addEventListener("click", () => { form.reset(); form.elements.role_id.value = ""; permissionBoxes().forEach((box) => { box.checked = false; }); title.innerHTML = icon + "Nuevo rol"; modal.removeAttribute("hidden"); });
  rows.forEach((row) => row.addEventListener("click", () => { rows.forEach((item) => item.classList.remove("selected")); row.classList.add("selected"); selected = row; edit.disabled = false; }));
  edit?.addEventListener("click", () => { if (!selected) return; form.elements.role_id.value = selected.dataset.roleId; form.elements.nombre.value = selected.dataset.nombre; form.elements.codigo.value = selected.dataset.codigo; form.elements.descripcion.value = selected.dataset.descripcion; const assigned = JSON.parse(selected.dataset.permissions || "[]").map(String); permissionBoxes().forEach((box) => { box.checked = assigned.includes(box.value); }); title.innerHTML = icon + "Editar rol"; modal.removeAttribute("hidden"); });
});
