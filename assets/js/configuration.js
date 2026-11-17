document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".database-server-alert .alert-confirm").forEach((button) => {
    button.addEventListener("click", () => {
      button.closest(".database-server-alert")?.remove();
      document.documentElement.style.overflow = "";
      document.body.style.overflow = "";
    });
  });
  if (document.querySelector(".database-server-alert")) {
    window.scrollTo(0, 0);
    document.documentElement.style.overflow = "hidden";
    document.body.style.overflow = "hidden";
  }
  const backupTable = document.querySelector(".database-backup-table-wrap table");
  const backupPagination = document.querySelector(".database-backup-footer");
  if (backupTable && backupPagination) {
    const rows = [...backupTable.querySelectorAll("tbody tr")].filter((row) => row.querySelector("td")?.colSpan !== 4);
    const backupSearch = document.querySelector(".database-backup-search");
    const pageSize = 10;
    const pageCount = Math.max(1, Math.ceil(rows.length / pageSize));
    let page = 1;
    const renderFooter = () => {
      const buttons = [];
      buttons.push('<button class="pagination-prev" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg><span>Anterior</span></button>');
      for (let index = 1; index <= pageCount; index += 1) buttons.push('<button type="button" class="' + (index === page ? 'current' : '') + '">' + index + '</button>');
      buttons.push('<button class="pagination-next" type="button"><span>Siguiente</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></button>');
      backupPagination.hidden = false;
      backupPagination.innerHTML = '<span>Mostrando ' + (rows.length ? ((page - 1) * pageSize + 1) + ' a ' + Math.min(page * pageSize, rows.length) : '0') + ' de ' + rows.length + ' registros</span><div>' + buttons.join('') + '</div>';
      backupPagination.querySelector('.pagination-prev').disabled = page === 1;
      backupPagination.querySelector('.pagination-next').disabled = page === pageCount;
      backupPagination.querySelectorAll('div button').forEach((button, index) => button.addEventListener('click', () => {
        if (index === 0 && page > 1) page -= 1;
        else if (index === pageCount + 1 && page < pageCount) page += 1;
        else if (index > 0 && index <= pageCount) page = index;
        render();
      }));
    };
    const render = () => {
      rows.forEach((row, index) => { row.hidden = Math.floor(index / pageSize) + 1 !== page; });
      renderFooter();
    };
    render();
    backupSearch?.addEventListener("input", () => {
      const term = backupSearch.value.trim().toLowerCase();
      rows.forEach((row) => { row.hidden = !row.textContent.toLowerCase().includes(term); });
      const searchEmptyRow = document.querySelector(".database-search-empty-row");
      if (searchEmptyRow) searchEmptyRow.hidden = !term || rows.some((row) => !row.hidden);
      document.querySelector(".database-empty-initial")?.toggleAttribute("hidden", Boolean(term));
      document.querySelector(".database-empty-search")?.toggleAttribute("hidden", !term);
    });
    const restoreTrigger = document.querySelector(".database-restore-trigger");
    const restoreModal = document.querySelector("#database-restore-confirm");
    let selectedBackup = null;
    backupTable.querySelectorAll(".database-backup-row").forEach((row) => row.addEventListener("click", () => {
      backupTable.querySelectorAll(".database-backup-row").forEach((item) => item.classList.remove("selected"));
      row.classList.add("selected");
      selectedBackup = row.dataset.backupName;
      if (restoreTrigger) restoreTrigger.disabled = false;
    }));
    restoreTrigger?.addEventListener("click", () => {
      if (!selectedBackup || !restoreModal) return;
      restoreModal.querySelector("[name=backup_name]").value = selectedBackup;
      restoreModal.removeAttribute("hidden");
    });
    restoreModal?.querySelector(".participant-confirm-cancel")?.addEventListener("click", () => restoreModal.setAttribute("hidden", ""));
    document.addEventListener("click", (event) => {
      if (event.target.closest(".database-backup-table-wrap, .database-restore-trigger, #database-restore-confirm")) return;
      backupTable.querySelectorAll(".database-backup-row").forEach((item) => item.classList.remove("selected"));
      selectedBackup = null;
      if (restoreTrigger) restoreTrigger.disabled = true;
    });
  }
  const general = document.querySelector('#configuration-general');
  const recent = document.querySelector('.panel.recent');
  if (general && recent) general.appendChild(recent);
  document.querySelectorAll('[data-configuration-tab]').forEach((tab) => tab.addEventListener('click', () => {
    document.querySelectorAll('[data-configuration-tab]').forEach((item) => { item.classList.toggle('is-active', item === tab); item.setAttribute('aria-selected', item === tab ? 'true' : 'false'); });
    document.querySelectorAll('.configuration-tab-panel').forEach((panel) => { const active = panel.id === tab.dataset.configurationTab; panel.classList.toggle('is-active', active); panel.hidden = !active; panel.style.display = active ? '' : 'none'; });
  }));
  const requestedTab = document.body.dataset.activeConfigurationTab;
  if (requestedTab === "configuration-database") {
    document.querySelector('[data-configuration-tab="configuration-database"]')?.click();
  }
  document.querySelectorAll('.branding-field input[type="file"]').forEach((input) => input.addEventListener('change', () => {
    const file = input.files?.[0];
    if (!file || !file.type.startsWith('image/')) return;
    const preview = input.closest('.branding-field')?.querySelector('img');
    if (!preview) return;
    const url = URL.createObjectURL(file);
    preview.src = url;
    preview.onload = () => URL.revokeObjectURL(url);
    const name = input.closest('.branding-field')?.querySelector('.branding-file-name');
    if (name) name.textContent = file.name;
  }));
  const brandingForm = document.querySelector('.branding-form');
  brandingForm?.addEventListener('submit', () => {
    const saveButton = brandingForm.querySelector('button[type="submit"]');
    if (!saveButton || saveButton.dataset.saving === '1') return;
    saveButton.dataset.saving = '1';
    saveButton.disabled = true;
    saveButton.innerHTML = '<span class="template-submit-spinner" aria-hidden="true"></span><span>Guardando...</span>';
  });
  if (!document.querySelector(".configuration-row, #configuration-modal")) return;
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
