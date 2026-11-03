document.addEventListener("DOMContentLoaded", () => {
  const rows = [...document.querySelectorAll(".user-row")];
  const modal = document.querySelector("#user-modal");
  const form = modal?.querySelector("form");
  const edit = document.querySelector(".user-edit");
  const status = document.querySelector(".user-status");
  const statusForm = document.querySelector("#user-status-form");
  const title = document.querySelector("#user-modal-title");
  const icon = '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0"/></svg>';
  let selected = null;
  const roleSelect = form?.elements.rol_id;
  if (roleSelect && !roleSelect.closest(".select2-lite")) {
    const wrapper = document.createElement("div");
    wrapper.className = "select2-lite user-role-select";
    roleSelect.parentNode.insertBefore(wrapper, roleSelect);
    wrapper.appendChild(roleSelect);
    roleSelect.classList.add("select2-native");
    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "select2-trigger";
    wrapper.appendChild(trigger);
    const dropdown = document.createElement("div");
    dropdown.className = "select2-dropdown";
    const search = document.createElement("input");
    search.type = "search";
    search.placeholder = "Buscar rol...";
    dropdown.appendChild(search);
    const list = document.createElement("div");
    list.className = "select2-options";
    dropdown.appendChild(list);
    wrapper.appendChild(dropdown);
    const normalize = (value) => value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
    const syncRoleOptions = () => {
      trigger.textContent = roleSelect.selectedOptions[0]?.textContent || "Seleccionar rol";
      list.replaceChildren();
      const query = normalize(search.value.trim());
      [...roleSelect.options].forEach((option) => {
        const item = document.createElement("button");
        item.type = "button";
        item.className = "select2-option";
        item.textContent = option.textContent;
        item.hidden = !normalize(option.textContent).includes(query);
        item.addEventListener("click", () => {
          roleSelect.value = option.value;
          roleSelect.dispatchEvent(new Event("change", { bubbles: true }));
          dropdown.classList.remove("open");
          syncRoleOptions();
        });
        list.appendChild(item);
      });
    };
    const positionDropdown = () => {
      const rect = trigger.getBoundingClientRect();
      dropdown.style.position = "fixed";
      dropdown.style.top = `${rect.bottom + 5}px`;
      dropdown.style.left = `${rect.left}px`;
      dropdown.style.width = `${rect.width}px`;
    };
    trigger.addEventListener("click", () => {
      const isOpen = dropdown.classList.toggle("open");
      if (isOpen) { positionDropdown(); search.focus(); }
    });
    window.addEventListener("resize", () => { if (dropdown.classList.contains("open")) positionDropdown(); });
    modal?.addEventListener("scroll", () => { if (dropdown.classList.contains("open")) positionDropdown(); });
    search.addEventListener("input", syncRoleOptions);
    roleSelect.addEventListener("change", syncRoleOptions);
    document.addEventListener("click", (event) => { if (!wrapper.contains(event.target)) dropdown.classList.remove("open"); });
    syncRoleOptions();
  }
  form?.setAttribute("autocomplete", "new-password");
  ["nombres", "apellidos", "email", "username"].forEach((name) => form?.elements[name]?.setAttribute("autocomplete", "new-password"));
  form?.addEventListener("keydown", (event) => {
    if (event.key !== "Enter" || event.target.tagName === "TEXTAREA" || event.target.matches("button")) return;
    event.preventDefault();
    const fields = [...form.querySelectorAll('input:not([type="hidden"]), select, button[type="submit"]')].filter((field) => !field.disabled);
    const next = fields[fields.indexOf(event.target) + 1];
    next?.focus();
  });
  form?.querySelectorAll("[required]").forEach((field) => field.removeAttribute("required"));
  const close = () => modal?.setAttribute("hidden", "");
  const formatNames = (event) => {
    event.target.value = event.target.value.toLocaleLowerCase("es").replace(/(^|[\s-])([\p{L}])/gu, (_, prefix, letter) => prefix + letter.toLocaleUpperCase("es"));
  };
  document.querySelectorAll("[data-user-close]").forEach((button) => button.addEventListener("click", close));
  ["nombres", "apellidos"].forEach((name) => {
    form?.elements[name]?.addEventListener("input", (event) => {
      event.target.value = event.target.value.replace(/(^|[\s-])([\p{Ll}])/gu, (_, prefix, letter) => prefix + letter.toLocaleUpperCase("es"));
    });
    form?.elements[name]?.addEventListener("blur", formatNames);
  });
  form?.elements.email?.addEventListener("input", (event) => { event.target.value = event.target.value.toUpperCase(); });
  form?.addEventListener("submit", (event) => {
    const required = [["nombres", "nombres"], ["apellidos", "apellidos"], ["email", "correo"], ["username", "usuario"], ["rol_id", "rol"]];
    let missing = required.find(([name]) => !form.elements[name].value.trim());
    const editing = Boolean(form.elements.user_id.value);
    if (!missing && !editing && !form.elements.password.value.trim()) missing = ["password", "contraseña"];
    if (!missing && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.elements.email.value.trim())) missing = ["email", "correo válido con @"];
    if (missing) { event.preventDefault(); if (typeof showParticipantAlert === "function") showParticipantAlert(`Completa el campo ${missing[1]}.`); form.elements[missing[0]].focus(); }
  });
  document.querySelector("[data-user-new]")?.addEventListener("click", () => {
    form.reset(); form.elements.user_id.value = ""; form.elements.rol_id.value = ""; form.elements.rol_id.dispatchEvent(new Event("change")); title.innerHTML = icon + "Nuevo usuario"; modal.removeAttribute("hidden");
  });
  rows.forEach((row) => row.addEventListener("click", () => {
    rows.forEach((item) => item.classList.remove("selected")); row.classList.add("selected"); selected = row;
    edit.disabled = false; status.disabled = false;
    status.innerHTML = row.dataset.status === "activo" ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar usuario' : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Activar usuario';
  }));
  edit?.addEventListener("click", () => {
    if (!selected) return;
    form.elements.user_id.value = selected.dataset.userId; form.elements.nombres.value = selected.dataset.nombres; form.elements.apellidos.value = selected.dataset.apellidos; form.elements.email.value = selected.dataset.email; form.elements.username.value = selected.dataset.username; form.elements.rol_id.value = selected.dataset.roleId || ""; form.elements.rol_id.dispatchEvent(new Event("change")); form.elements.password.value = "";
    title.innerHTML = icon + "Editar usuario"; modal.removeAttribute("hidden");
  });
  status?.addEventListener("click", () => {
    if (!selected) return;
    statusForm.elements.user_id.value = selected.dataset.userId; statusForm.elements.action.value = selected.dataset.status === "activo" ? "desactivar" : "activar"; statusForm.submit();
  });
});
