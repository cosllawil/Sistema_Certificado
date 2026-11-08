document.addEventListener("click", (event) => {
  const clickedTypeRow = event.target.closest(".type-row");
  if (
    document.querySelector(".type-row") &&
    !clickedTypeRow &&
    !event.target.closest(".type-edit, .type-disable, .participant-action-new, .participant-modal")
  ) {
    document.querySelectorAll(".type-row.selected").forEach((row) => row.classList.remove("selected"));
    document.querySelector(".type-edit")?.setAttribute("disabled", "");
    document.querySelector(".type-disable")?.setAttribute("disabled", "");
  }
  const dashboardAction = event.target.closest(".recent .row-actions button");
  if (dashboardAction) {
    const row = dashboardAction.closest(".dashboard-certificate-row");
    if (
      row &&
      dashboardAction === row.querySelector(".row-actions button:first-child")
    ) {
      if (window.innerWidth <= 720) {
        window.open(
          "certificado_pdf?pdf=" + row.dataset.certificateId,
          "_blank",
          "noopener",
        );
        return;
      }
      const viewer = document.querySelector("#dashboard-pdf-modal");
      if (viewer) {
        viewer.querySelector("iframe").src =
          "certificado_pdf?pdf=" +
          row.dataset.certificateId +
          "&view=configured-v5&_=" +
          Date.now();
        viewer.removeAttribute("hidden");
      }
    } else if (
      row &&
      dashboardAction === row.querySelector(".row-actions button:nth-child(2)")
    ) {
      window.location.href =
        "registrar_certificado?edit=" + row.dataset.certificateId;
    }
    return;
  }
  if (event.target.closest(".template-config")) {
    const selected = document.querySelector(".template-row.selected");
    if (selected) {
      location.href = "plantillas?config=" + selected.dataset.templateId;
      return;
    }
  }
  const modal = document.querySelector("#participant-modal");
  if (
    event.target.closest(".participant-action-new") &&
    document.querySelector("#type-modal")
  ) {
    const typeModal = document.querySelector("#type-modal");
    typeModal.querySelector("[name=type_id]").value = "";
    typeModal.querySelector("[name=codigo]").value =
      typeModal.dataset.nextCode || "TC-001";
    typeModal.querySelector("[name=nombre]").value = "";
    typeModal.querySelector("[name=descripcion]").value = "";
    typeModal.removeAttribute("hidden");
    return;
  }
  if (
    event.target.closest(".participant-action-new") &&
    document.querySelector("#template-modal")
  ) {
    const modal = document.querySelector("#template-modal");
    modal.querySelector("h2").innerHTML =
      '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="m6 16 4-4 3 3 2-2 3 3M8 8h.01"/></svg>Nueva plantilla';
    modal.querySelector("[name=template_id]").value = "";
    ["horas_academicas", "fecha_sesion", "fecha_inicio", "fecha_fin"].forEach(
      (name) => {
        modal.querySelector(`[name=${name}]`).value = "";
      },
    );
    modal.removeAttribute("hidden");
    return;
  }
  if (
    event.target.closest(".signer-new") &&
    document.querySelector("#signer-modal")
  ) {
    const modal = document.querySelector("#signer-modal");
    const form = modal.querySelector(".signer-form");
    form.reset();
    form.elements.signer_id.value = "";
    modal.querySelector("h2").innerHTML =
      '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0"/></svg>Nuevo firmante';
    modal.removeAttribute("hidden");
    return;
  }
  if (event.target.closest(".signer-edit")) {
    const row = document.querySelector(".signer-row.selected");
    const modal = document.querySelector("#signer-modal");
    if (row && modal) {
      const form = modal.querySelector(".signer-form");
      form.elements.signer_id.value = row.dataset.signerId;
      form.elements.nombres.value = row.dataset.nombres || "";
      form.elements.apellidos.value = row.dataset.apellidos || "";
      form.elements.cargo.value = row.dataset.cargo || "";
      modal.querySelector("h2").innerHTML =
        '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0"/></svg>Editar firmante';
      modal.removeAttribute("hidden");
      form.elements.nombres.focus();
    }
    return;
  }
  if (event.target.closest(".signer-disable")) {
    const row = document.querySelector(".signer-row.selected");
    const modal = document.querySelector("#signer-confirm");
    if (row && modal) {
      const inactive = row.dataset.active !== "1";
      modal.querySelector("h2").textContent = inactive
        ? "Activar firmante"
        : "Desactivar firmante";
      modal.querySelector("p").textContent = inactive
        ? "¿Deseas activar el firmante seleccionado?"
        : "¿Deseas desactivar el firmante seleccionado?";
      modal.querySelector("[name=signer_id]").value = row.dataset.signerId;
      modal.querySelector("[name=estado]").value = inactive ? "1" : "0";
      modal.querySelector(".participant-confirm-ok").innerHTML = inactive
        ? '<svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>Activar'
        : '<svg viewBox="0 0 24 24"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar';
      modal.removeAttribute("hidden");
    }
    return;
  }
  if (
    event.target.closest(
      "#signer-modal .course-cancel, #signer-modal .participant-modal-close",
    )
  )
    document.querySelector("#signer-modal").setAttribute("hidden", "");
  if (event.target.closest(".template-edit")) {
    const row = document.querySelector(".template-row.selected");
    const modal = document.querySelector("#template-modal");
    if (row && modal) {
      modal.querySelector("h2").innerHTML =
        '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="m6 16 4-4 3 3 2-2 3 3M8 8h.01"/></svg>Editar plantilla';
      modal.querySelector("[name=template_id]").value = row.dataset.templateId;
      modal.querySelector("[name=nombre]").value = row.dataset.name || "";
      modal.querySelector("[name=descripcion]").value =
        row.dataset.description || "";
      modal.querySelector("[name=formato]").value = row.dataset.format || "A4";
      modal.querySelector("[name=orientacion]").value =
        row.dataset.orientation || "horizontal";
      let config = {};
      try {
        config = JSON.parse(row.dataset.config || "{}");
      } catch (_) {}
      const data = config.datos || {};
      ["horas_academicas", "fecha_sesion", "fecha_inicio", "fecha_fin"].forEach(
        (name) => {
          const key = name === "horas_academicas" ? "horas" : name;
          modal.querySelector(`[name=${name}]`).value =
            key === "horas" && data[key] !== ""
              ? String(parseInt(data[key], 10) || 0)
              : data[key] || "";
        },
      );
      modal.removeAttribute("hidden");
      modal.querySelector("[name=nombre]").focus();
    }
    return;
  }
  if (event.target.closest(".template-disable")) {
    const row = document.querySelector(".template-row.selected");
    const modal = document.querySelector("#template-confirm");
    if (row && modal) {
      const inactive = row.dataset.active !== "1";
      modal.querySelector("h2").lastChild.textContent = inactive
        ? "Activar plantilla"
        : "Desactivar plantilla";
      modal.querySelector("p").textContent = inactive
        ? "¿Deseas activar la plantilla seleccionada?"
        : "¿Deseas desactivar la plantilla seleccionada?";
      modal.querySelector("[name=template_id]").value = row.dataset.templateId;
      modal.querySelector("[name=activo]").value = inactive ? "1" : "0";
      modal.querySelector(".participant-confirm-ok").innerHTML = inactive
        ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Activar'
        : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar';
      modal.removeAttribute("hidden");
    }
    return;
  }
  if (
    event.target.closest(
      "#template-modal .course-cancel, #template-modal .participant-modal-close",
    )
  )
    document.querySelector("#template-modal").setAttribute("hidden", "");
  if (event.target.closest(".type-edit")) {
    const row = document.querySelector(".type-row.selected");
    const modal = document.querySelector("#type-modal");
    if (row && modal) {
      modal.querySelector("h2").innerHTML =
        '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="9" r="5"/><path d="m9 13-1 7 4-2 4 2-1-7M10 9l2-2 2 2-2 2z"/></svg>Editar tipo de certificado';
      modal.querySelector("[name=type_id]").value = row.dataset.typeId;
      modal.querySelector("[name=codigo]").value = row.dataset.code || "";
      modal.querySelector("[name=nombre]").value = row.dataset.name || "";
      modal.querySelector("[name=descripcion]").value =
        row.dataset.description || "";
      modal.removeAttribute("hidden");
      modal.querySelector("[name=codigo]").focus();
    }
    return;
  }
  if (event.target.closest("#type-modal .course-cancel"))
    document.querySelector("#type-modal").setAttribute("hidden", "");
  if (event.target.closest(".participant-action-new") && modal) {
    document.querySelector(".participant-form").reset();
    document.querySelector(".participant-form").elements.participant_id.value =
      "";
    document.querySelector(
      ".participant-form",
    ).elements.numero_documento.disabled = true;
    document.querySelector(".participant-form").elements.tipo_documento.value =
      "";
    document
      .querySelector(".participant-form")
      .elements.tipo_documento.dispatchEvent(
        new Event("change", { bubbles: true }),
      );
    document.querySelector("#participant-modal-title").innerHTML =
      '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0"/></svg>Nuevo participante';
    modal.removeAttribute("hidden");
    return;
  }
  if (event.target.closest(".course-modal .participant-action-new")) {
    const form = document.querySelector(".course-form");
    form?.reset();
    if (form) form.elements.course_id.value = "";
    document.querySelector("#course-modal h2").innerHTML =
      '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h6"/></svg>Nuevo curso';
  }
  if (
    modal &&
    (event.target.closest(
      ".participant-modal-close, .participant-modal-cancel",
    ) ||
      event.target === modal)
  ) {
    modal.setAttribute("hidden", "");
  }
  const row = event.target.closest(".participant-row");
  if (row) {
    document
      .querySelectorAll(".participant-row")
      .forEach((item) => item.classList.remove("selected"));
    row.classList.add("selected");
    document.querySelector(".participant-action-edit").disabled = false;
    document.querySelector(".participant-action-disable").disabled = false;
    const inactive =
      row.querySelector(".badge")?.textContent.trim() === "Desactivado";
    const actionButton = document.querySelector(".participant-action-disable");
    actionButton.classList.toggle("participant-activate", inactive);
    actionButton.innerHTML = inactive
      ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Activar participante'
      : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar participante';
  }
  const courseRow = event.target.closest(".course-row");
  if (courseRow) {
    document
      .querySelectorAll(".course-row")
      .forEach((item) => item.classList.remove("selected"));
    courseRow.classList.add("selected");
    document.querySelector(".course-edit").disabled = false;
    document.querySelector(".course-disable").disabled = false;
    const inactive =
      courseRow.querySelector(".badge")?.textContent.trim() === "Desactivado";
    document
      .querySelector(".course-disable")
      .classList.toggle("course-activate", inactive);
    document.querySelector(".course-disable").innerHTML = inactive
      ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Activar curso'
      : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar curso';
  }
  const typeRow = event.target.closest(".type-row");
  if (typeRow) {
    document
      .querySelectorAll(".type-row")
      .forEach((item) => item.classList.remove("selected"));
    typeRow.classList.add("selected");
    const inactive = typeRow.dataset.active !== "1";
    const button = document.querySelector(".type-disable");
    button.disabled = false;
    document.querySelector(".type-edit").disabled = false;
    button.classList.toggle("course-activate", inactive);
    button.innerHTML = inactive
      ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Activar tipo de certificado'
      : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar tipo de certificado';
  }
  if (event.target.closest(".type-disable")) {
    const row = document.querySelector(".type-row.selected");
    if (row) {
      const inactive = row.dataset.active !== "1";
      const modal = document.querySelector("#type-confirm");
      modal.querySelector("h2").textContent = inactive
        ? "Activar tipo de certificado"
        : "Desactivar tipo de certificado";
      modal.querySelector("p").textContent = inactive
        ? "¿Deseas activar el tipo seleccionado?"
        : "¿Deseas desactivar el tipo seleccionado?";
      modal.querySelector("[name=type_id]").value = row.dataset.typeId;
      modal.querySelector("[name=activo]").value = inactive ? "1" : "0";
      modal.querySelector(".participant-confirm-ok").innerHTML = inactive
        ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Activar'
        : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar';
      modal.removeAttribute("hidden");
    }
  }
  const templateRow = event.target.closest(".template-row");
  const certificateRow = event.target.closest(".certificate-row");
  if (certificateRow) {
    document
      .querySelectorAll(".certificate-row")
      .forEach((item) => item.classList.remove("selected"));
    certificateRow.classList.add("selected");
    const checkbox = certificateRow.querySelector("input[name='ids[]']");
    if (checkbox && event.target !== checkbox) {
      checkbox.checked = true;
      checkbox.dispatchEvent(new Event("change", { bubbles: true }));
    }
    const editButton = document.querySelector(".certificate-edit");
    if (editButton)
      editButton.disabled = certificateRow.dataset.state !== "borrador";
    return;
  }
  if (event.target.closest("#bulk-print-button")) {
    const ids = [
      ...document.querySelectorAll("input[name='ids[]']:checked"),
    ].map((box) => box.value);
    const viewer = document.querySelector("#certificate-pdf-modal");
    if (ids.length && viewer) {
      if (window.innerWidth <= 720) {
        window.open(
          "certificado_pdf?batch=" + ids.join(",") + "&_=" + Date.now(),
          "_blank",
          "noopener",
        );
        return;
      }
      viewer.querySelector("iframe").src =
        "certificado_pdf?batch=" + ids.join(",") + "&_=" + Date.now();
      viewer.removeAttribute("hidden");
    }
    return;
  }
  if (event.target.closest(".pdf-viewer-close")) {
    const viewer =
      event.target.closest(".pdf-viewer-modal") ||
      document.querySelector("#certificate-pdf-modal");
    if (viewer) {
      viewer.setAttribute("hidden", "");
      const frame = viewer.querySelector("iframe");
      if (frame) frame.src = "about:blank";
    }
    return;
  }
  if (event.target.closest(".certificate-edit")) {
    const row = document.querySelector(".certificate-row.selected");
    if (row && row.dataset.state === "borrador")
      window.location.href =
        "registrar_certificado?edit=" + row.dataset.certificateId;
    return;
  }
  if (templateRow) {
    document
      .querySelectorAll(".template-row")
      .forEach((item) => item.classList.remove("selected"));
    templateRow.classList.add("selected");
    const inactive = templateRow.dataset.active !== "1";
    const button = document.querySelector(".template-disable");
    button.disabled = false;
    document.querySelector(".template-edit").disabled = false;
    document.querySelector(".template-config").disabled = false;
    button.classList.toggle("course-activate", inactive);
    button.innerHTML = inactive
      ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Activar plantilla'
      : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar plantilla';
    return;
  }
  if (event.target.closest(".template-config")) {
    const row = document.querySelector(".template-row.selected");
    const modal = document.querySelector("#template-config-modal");
    if (row && modal) {
      modal.dataset.fondo = row.dataset.fondo || "";
      const cfg = JSON.parse(row.dataset.config || "{}");
      modal.querySelector("[name=config_template_id]").value =
        row.dataset.templateId;
      modal
        .querySelectorAll(".template-fields input[type=checkbox], .template-signature-toggle input[type=checkbox]")
        .forEach(
          (box) => (box.checked = Boolean(cfg.campos && cfg.campos[box.value])),
        );
      if (cfg.fuente) modal.querySelector("[name=fuente]").value = cfg.fuente;
      if (cfg.tamano) modal.querySelector("[name=tamano]").value = cfg.tamano;
      if (cfg.color) modal.querySelector("[name=color]").value = cfg.color;
      renderTemplatePositions(modal);
      Object.entries(cfg.campos || {}).forEach(([key, value]) => {
        modal.querySelectorAll(`[data-position="${key}"]`).forEach((input) => {
          const legacyDefault = value.x === 35 && value.y === 100;
          if (!legacyDefault && value[input.dataset.axis] !== undefined)
            input.value = value[input.dataset.axis];
        });
      });
      renderTemplatePreview(modal);
      modal.removeAttribute("hidden");
    }
    return;
  }
  if (
    event.target.closest(
      "#template-config-modal .course-cancel, #template-config-modal .participant-modal-close",
    )
  )
    document.querySelector("#template-config-modal").setAttribute("hidden", "");
  const signerRow = event.target.closest(".signer-row");
  if (signerRow) {
    document
      .querySelectorAll(".signer-row")
      .forEach((item) => item.classList.remove("selected"));
    signerRow.classList.add("selected");
    const inactive = signerRow.dataset.active !== "1";
    const button = document.querySelector(".signer-disable");
    button.disabled = false;
    document.querySelector(".signer-edit").disabled = false;
    button.classList.toggle("course-activate", inactive);
    button.innerHTML = inactive
      ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Activar firmante'
      : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar firmante';
    return;
  }
  if (event.target.closest("#type-confirm .participant-confirm-cancel"))
    document.querySelector("#type-confirm").setAttribute("hidden", "");
  if (event.target.closest("#template-confirm .participant-confirm-cancel"))
    document.querySelector("#template-confirm").setAttribute("hidden", "");
  if (event.target.closest("#signer-confirm .participant-confirm-cancel"))
    document.querySelector("#signer-confirm").setAttribute("hidden", "");
  if (event.target.closest(".course-edit")) {
    const selected = document.querySelector(".course-row.selected");
    if (selected) {
      const form = document.querySelector(".course-form");
      form.elements.course_id.value = selected.dataset.courseId;
      form.elements.codigo.value = selected.dataset.codigo;
      form.elements.nombre.value = selected.dataset.nombre;
      form.elements.horas_academicas.value = String(
        parseInt(selected.dataset.horas, 10) || 0,
      );
      form.elements.fecha_inicio.value = selected.dataset.inicio;
      form.elements.fecha_fin.value = selected.dataset.fin;
      form.elements.tipo_certificado_id.value = selected.dataset.tipoCertificado || "";
      form.elements.tipo_certificado_id.dispatchEvent(new Event("change", { bubbles: true }));
      form.elements.plantilla_id.value = selected.dataset.plantilla || "";
      form.elements.plantilla_id.dispatchEvent(new Event("change", { bubbles: true }));
      form.elements.descripcion.value = selected.dataset.descripcion;
      document.querySelector("#course-modal h2").innerHTML =
        '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h6"/></svg>Editar curso';
      document.querySelector("#course-modal").removeAttribute("hidden");
    }
  }
  if (event.target.closest(".course-disable")) {
    const selected = document.querySelector(".course-row.selected");
    if (selected) {
      const inactive =
        selected.querySelector(".badge")?.textContent.trim() === "Desactivado";
      document.querySelector(".participant-confirm-card h2").textContent =
        inactive ? "Activar curso" : "Desactivar curso";
      document.querySelector(".participant-confirm-card p").textContent =
        inactive
          ? "¿Deseas activar el curso seleccionado?"
          : "¿Deseas desactivar el curso seleccionado?";
      const ok = document.querySelector(".participant-confirm-ok");
      ok.dataset.courseAction = inactive ? "activate" : "deactivate";
      ok.innerHTML = inactive
        ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Activar'
        : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5"/></svg>Desactivar';
      document.querySelector("#participant-confirm").removeAttribute("hidden");
    }
  }
  if (event.target.closest(".participant-action-edit")) {
    const selected = document.querySelector(".participant-row.selected");
    if (selected) {
      const form = document.querySelector(".participant-form");
      form.elements.participant_id.value = selected.dataset.participantId;
      form.elements.action.value = "save";
      form.elements.numero_documento.disabled = false;
      form.elements.tipo_documento.value =
        selected.getAttribute("data-tipo-documento") || "";
      form.elements.tipo_documento.dispatchEvent(
        new Event("change", { bubbles: true }),
      );
      const participantFields = {
        "tipo-documento": "tipo_documento",
        "numero-documento": "numero_documento",
        nombres: "nombres",
        apellidos: "apellidos",
        email: "email",
        telefono: "telefono",
        cas: "nro_registro_cas",
      };
      Object.entries(participantFields)
        .filter(([attribute]) => attribute !== "tipo-documento")
        .forEach(([attribute, field]) => {
          form.elements[field].value =
            selected.getAttribute(`data-${attribute}`) || "";
        });
      document.querySelector("#participant-modal-title").innerHTML =
        '<svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0"/></svg>Editar participante';
      document.querySelector("#participant-modal").removeAttribute("hidden");
    }
  }
  if (event.target.closest(".participant-action-disable")) {
    const selected = document.querySelector(".participant-row.selected");
    if (selected) {
      const inactive =
        selected.querySelector(".badge")?.textContent.trim() === "Desactivado";
      document.querySelector(".participant-confirm-card h2").textContent =
        inactive ? "Activar participante" : "Desactivar participante";
      document.querySelector(".participant-confirm-card p").textContent =
        inactive
          ? "¿Deseas activar el participante seleccionado?"
          : "¿Deseas desactivar el participante seleccionado?";
      document.querySelector(".participant-confirm-ok").innerHTML = inactive
        ? "✓&nbsp; Activar"
        : "✓&nbsp; Desactivar";
      document.querySelector(".participant-confirm-ok").dataset.action =
        inactive ? "activate" : "deactivate";
      document
        .querySelector(".participant-confirm-card")
        .classList.toggle("confirm-activate", inactive);
      document.querySelector("#participant-confirm").removeAttribute("hidden");
    }
  }
  if (event.target.closest(".participant-confirm-cancel"))
    document.querySelector("#participant-confirm").setAttribute("hidden", "");
  if (event.target.closest(".participant-confirm-ok")) {
    const course = document.querySelector(".course-row.selected");
    if (
      course &&
      event.target.closest(".participant-confirm-ok").dataset.courseAction
    ) {
      const form = document.querySelector(".course-form");
      form.elements.course_id.value = course.dataset.courseId;
      form.elements.action.value = event.target.closest(
        ".participant-confirm-ok",
      ).dataset.courseAction;
      form.submit();
      return;
    }
    const selected = document.querySelector(".participant-row.selected");
    const form = document.querySelector(".participant-form");
    if (selected) {
      form.elements.participant_id.value = selected.dataset.participantId;
      form.elements.action.value =
        event.target.closest(".participant-confirm-ok").dataset.action ||
        "deactivate";
      form.submit();
    }
  }
  if (
    !event.target.closest(".table-wrap") &&
    !event.target.closest(".participant-actions") &&
    !event.target.closest(".certificate-edit")
  ) {
    document
      .querySelectorAll(".participant-row")
      .forEach((item) => item.classList.remove("selected"));
    document
      .querySelectorAll(".course-row")
      .forEach((item) => item.classList.remove("selected"));
    document.querySelector(".participant-action-edit").disabled = true;
    document.querySelector(".participant-action-disable").disabled = true;
    document
      .querySelector(".participant-action-disable")
      .classList.remove("participant-activate");
    document.querySelector(".course-edit")?.setAttribute("disabled", "");
    document.querySelector(".course-disable")?.setAttribute("disabled", "");
    document
      .querySelector(".course-disable")
      ?.classList.remove("course-activate");
    document
      .querySelectorAll(".template-row")
      .forEach((item) => item.classList.remove("selected"));
    document.querySelector(".template-edit")?.setAttribute("disabled", "");
    document.querySelector(".template-disable")?.setAttribute("disabled", "");
    document
      .querySelector(".template-disable")
      ?.classList.remove("course-activate");
    document.querySelector(".template-config")?.setAttribute("disabled", "");
    document
      .querySelectorAll(".signer-row")
      .forEach((item) => item.classList.remove("selected"));
    document.querySelector(".signer-edit")?.setAttribute("disabled", "");
    document.querySelector(".signer-disable")?.setAttribute("disabled", "");
    document
      .querySelector(".signer-disable")
      ?.classList.remove("course-activate");
    document
      .querySelectorAll(".certificate-row")
      .forEach((item) => item.classList.remove("selected"));
    document.querySelector(".certificate-edit")?.setAttribute("disabled", "");
  }
});


document.addEventListener("click", (event) => {
  if (!document.querySelector(".certificate-row")) return;
  if (
    event.target.closest(
      ".certificate-row, .certificate-edit, .participant-action-new",
    )
  )
    return;
  if (!event.target.closest(".table-wrap")) {
    document
      .querySelectorAll(".certificate-row")
      .forEach((row) => row.classList.remove("selected"));
    document.querySelector(".certificate-edit")?.setAttribute("disabled", "");
  }
});

document.addEventListener("submit", (event) => {
  const certificateForm = event.target.closest(".form-card");
  if (certificateForm) {
    const fields = [
      "participante_id",
      "curso_id",
      "tipo_certificado_id",
      "plantilla_id",
    ];
    const labels = {
      participante_id: "participante",
      curso_id: "curso",
      tipo_certificado_id: "tipo de certificado",
      plantilla_id: "plantilla",
      fecha_sesion: "fecha de sesión",
      fecha_inicio: "fecha de inicio",
      fecha_fin: "fecha de fin",
    };
    const missing = fields.find(
      (name) => !certificateForm.elements[name].value.trim(),
    );
    if (missing) {
      event.preventDefault();
      showParticipantAlert(`Ingresa la ${labels[missing]} para continuar.`);
      certificateForm.elements[missing].focus();
    }
    return;
  }
  const signerForm = event.target.closest(".signer-form");
  if (signerForm) {
    const isNewSigner = !signerForm.elements.signer_id.value;
    const requiredFields = isNewSigner ? ["tipo_documento", "numero_documento", "nombres", "apellidos", "cargo"] : ["nombres", "apellidos", "cargo"];
    const missing = requiredFields.find(
      (name) => !signerForm.elements[name].value.trim(),
    );
    if (missing) {
      event.preventDefault();
      const field = signerForm.elements[missing];
      showParticipantAlert(`Completa el campo ${missing.replaceAll("_", " ")}.`);
      field.focus();
      return;
    }
    if (!isNewSigner) return;
    const type = signerForm.elements.tipo_documento.value;
    const number = signerForm.elements.numero_documento.value.trim();
    const validNumber = type === "DNI" ? /^\d{8}$/.test(number) : type === "CE" ? /^\d{1,12}$/.test(number) : type === "PASAPORTE" ? /^[a-z0-9]{1,12}$/i.test(number) : /^[a-z0-9]{1,12}$/i.test(number);
    if (!validNumber) {
      event.preventDefault();
      const field = signerForm.elements.numero_documento;
      showParticipantAlert(type === "DNI" ? "El DNI debe tener exactamente 8 dígitos." : "El número de documento no es válido.");
      field.focus();
      return;
    }
    if (!signerForm.elements.signer_id.value && !signerForm.elements.firma.files.length) {
      event.preventDefault();
      const field = signerForm.elements.firma;
      showParticipantAlert("Selecciona el archivo de firma.");
      field.focus();
    }
    return;
  }
  const typeForm = event.target.closest(".type-form");
  if (typeForm) {
    const missing = ["codigo", "nombre"].find(
      (name) => !typeForm.elements[name].value.trim(),
    );
    if (missing) {
      event.preventDefault();
      showParticipantAlert(`Ingresa ${missing} para continuar.`);
      typeForm.elements[missing].focus();
    }
    return;
  }
  const courseForm = event.target.closest(".course-form");
  if (courseForm) {
    const field = [
      "codigo",
      "nombre",
      "horas_academicas",
      "fecha_inicio",
      "fecha_fin",
    ].find((name) => !courseForm.elements[name].value.trim());
    if (field) {
      event.preventDefault();
      showParticipantAlert(
        `Ingresa ${field.replace("_", " ")} para continuar.`,
      );
      courseForm.elements[field].focus();
    }
    return;
  }
  const form = event.target.closest(".participant-form");
  if (!form) return;
  const type = form.elements.tipo_documento.value;
  const documentNumber = form.elements.numero_documento.value.trim();
  const phone = form.elements.telefono.value.trim();
  const email = form.elements.email.value.trim();
  if (phone && !/^\d{9}$/.test(phone)) {
    event.preventDefault();
    showParticipantAlert("El teléfono debe tener exactamente 9 números.");
    form.elements.telefono.focus();
    return;
  }
  if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    event.preventDefault();
    showParticipantAlert("Ingresa un correo válido que contenga @.");
    form.elements.email.focus();
    return;
  }
  const documentError =
    type === "DNI" && !/^\d{8}$/.test(documentNumber)
      ? "El DNI debe tener exactamente 8 números."
      : type === "CE" && !/^\d{9}$/.test(documentNumber)
        ? "El CE debe tener exactamente 9 números."
        : type === "PASAPORTE" && !/^[a-z0-9]{1,12}$/i.test(documentNumber)
          ? "El pasaporte debe tener entre 1 y 12 caracteres alfanuméricos."
          : "";
  if (documentError) {
    event.preventDefault();
    showParticipantAlert(documentError);
    form.elements.numero_documento.focus();
    return;
  }
  const labels = {
    tipo_documento: "tipo de documento",
    numero_documento: "número de documento",
    nombres: "nombres",
    apellidos: "apellidos",
    nro_registro_cas: "registro CAS",
  };
  const missing = [
    "tipo_documento",
    "numero_documento",
    "nombres",
    "apellidos",
    "nro_registro_cas",
  ].find((name) => !form.elements[name].value.trim());
  if (!missing) return;
  event.preventDefault();
  const message = document.createElement("div");
  message.className = "form-alert";
  message.innerHTML = `<div class="alert-card"><div class="alert-main"><div class="alert-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m10.3 3.5-8 14A2 2 0 0 0 4 20.5h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01" stroke-linecap="round"/></svg></div><div class="alert-content"><strong>Validación</strong><span>Ingresa el ${labels[missing]} para continuar.</span></div></div><div class="alert-actions"><button type="button" class="alert-confirm">✓&nbsp; Entendido</button></div></div>`;
  document.body.append(message);
  message.querySelector("button").onclick = () => message.remove();
  form.elements[missing].focus();
});

document.addEventListener("keydown", (event) => {
  if (
    event.key === "Enter" &&
    event.target.matches('#template-modal button[type="submit"]')
  ) {
    event.preventDefault();
    return;
  }
  if (
    event.key !== "Enter" ||
    !event.target.closest(
      ".participant-form, .course-form, .type-form, .signer-form, .form-card",
    ) ||
    event.target.matches("button")
  )
    return;
  event.preventDefault();
  const form = event.target.closest(
    ".participant-form, .course-form, .type-form, .signer-form, .form-card",
  );
  const fields = [
    ...form.querySelectorAll(
      'select, input:not([type="hidden"]), textarea, button[type="submit"]',
    ),
  ].filter((field) => !field.disabled);
  const index = fields.indexOf(event.target);
  if (index >= 0 && fields[index + 1]) fields[index + 1].focus();
});

function showParticipantAlert(text) {
  document.querySelector(".form-alert")?.remove();
  const message = document.createElement("div");
  message.className = "form-alert";
  message.innerHTML = `<div class="alert-card"><div class="alert-main"><div class="alert-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m10.3 3.5-8 14A2 2 0 0 0 4 20.5h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01" stroke-linecap="round"/></svg></div><div class="alert-content"><strong>Validación</strong><span>${text}</span></div></div><div class="alert-actions"><button type="button" class="alert-confirm">✓&nbsp; Entendido</button></div></div>`;
  document.body.append(message);
  message.querySelector("button").onclick = () => message.remove();
}

function showSuccessAlert(text) {
  const message = document.createElement("div");
  message.className = "form-alert success-alert";
  message.innerHTML =
    '<div class="alert-card"><div class="alert-main"><div class="alert-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6"/></svg></div><div class="alert-content"><strong>Éxito</strong><span>' +
    text +
    '</span></div></div><div class="alert-actions"><button type="button" class="alert-confirm">✓&nbsp; Entendido</button></div></div>';
  document.body.append(message);
  message.querySelector("button").onclick = () => message.remove();
}
function renderTemplatePositions(modal) {
  let box = modal.querySelector(".template-position-fields");
  if (!box) {
    box = document.createElement("div");
    box.className = "template-position-fields";
    modal.querySelector(".template-fields").after(box);
  }
  const previousPositions = {};
  box.querySelectorAll("[data-position][data-axis]").forEach((input) => {
    previousPositions[input.dataset.position] ||= {};
    previousPositions[input.dataset.position][input.dataset.axis] = input.value;
  });
  box.innerHTML = "";
  modal
    .querySelectorAll('.template-fields input[type="checkbox"]:checked')
    .forEach((check, index) => {
      const row = document.createElement("div");
      row.className = "template-position-row";
      const defaultX = check.value === "participante" ? 463 : 650;
      const defaultY = Math.min(540, 90 + box.children.length * 58);
      row.innerHTML = `<strong>${check.parentElement.textContent.trim()}</strong><label>X <input type="number" data-position="${check.value}" data-axis="x" value="35"></label><label>Y <input type="number" data-position="${check.value}" data-axis="y" value="100"></label><label>Alineación <select data-position="${check.value}" data-axis="align"><option>left</option><option>center</option><option>right</option></select></label>`;
      row.querySelector('[data-axis="x"]').value = defaultX;
      row.querySelector('[data-axis="y"]').value = defaultY;
      const previous = previousPositions[check.value];
      if (previous) {
        if (previous.x !== undefined)
          row.querySelector('[data-axis="x"]').value = previous.x;
        if (previous.y !== undefined)
          row.querySelector('[data-axis="y"]').value = previous.y;
        if (previous.align !== undefined)
          row.querySelector('[data-axis="align"]').value = previous.align;
      }
      box.append(row);
    });
}

function renderTemplatePreview(modal) {
  let wrap = modal.querySelector(".template-preview-wrap");
  if (!wrap) {
    wrap = document.createElement("div");
    wrap.className = "template-preview-wrap";
    wrap.innerHTML =
      '<label>Vista previa y posición de campos</label><div class="template-preview-canvas"><b class="template-preview-title">CERTIFICADO</b><div class="template-preview-items"></div></div>';
    modal.querySelector(".template-position-fields").after(wrap);
  }
  const items = wrap.querySelector(".template-preview-items");
  items.innerHTML = "";
  modal
    .querySelectorAll('.template-fields input[type="checkbox"]:checked')
    .forEach((check, index) => {
      const key = check.value;
      const item = document.createElement("span");
      item.className = "template-preview-item";
      item.textContent = check.parentElement.textContent.trim();
      const x = modal.querySelector(`[data-position="${key}"][data-axis="x"]`);
      const y = modal.querySelector(`[data-position="${key}"][data-axis="y"]`);
      item.style.left = `${Math.min(92, Math.max(8, Number(x?.value || 35) / 2))}%`;
      item.style.top = `${Math.min(90, Math.max(12, Number(y?.value || 100) / 2))}%`;
      items.append(item);
    });
  modal
    .querySelectorAll(
      ".template-position-fields input,.template-position-fields select",
    )
    .forEach((input) => {
      input.oninput = () => renderTemplatePreview(modal);
    });
}

function renderTemplatePreview(modal) {
  let wrap = modal.querySelector(".template-preview-wrap");
  if (!wrap) {
    wrap = document.createElement("div");
    wrap.className = "template-preview-wrap";
    wrap.innerHTML =
      '<label>Vista previa del PDF original y posición de campos</label><div class="template-preview-canvas"><iframe class="template-preview-pdf" title="PDF original"></iframe><div class="template-preview-items"></div></div>';
    modal.querySelector(".template-position-fields").after(wrap);
  }
  const frame = wrap.querySelector(".template-preview-pdf");
  if (modal.dataset.fondo) frame.src = modal.dataset.fondo;
  const items = wrap.querySelector(".template-preview-items");
  items.innerHTML = "";
  modal
    .querySelectorAll('.template-fields input[type="checkbox"]:checked')
    .forEach((check, index) => {
      const key = check.value,
        item = document.createElement("span");
      item.className = "template-preview-item";
      item.textContent = check.parentElement.textContent.trim();
      const x = modal.querySelector(`[data-position="${key}"][data-axis="x"]`),
        y = modal.querySelector(`[data-position="${key}"][data-axis="y"]`);
      item.style.left = `${Math.min(92, Math.max(8, Number(x?.value || 35) / 2))}%`;
      item.style.top = `${Math.min(90, Math.max(12, Number(y?.value || 100) / 2))}%`;
      item.addEventListener("pointerdown", (event) => {
        event.preventDefault();
        item.setPointerCapture(event.pointerId);
        const canvas = wrap.querySelector(".template-preview-canvas");
        const move = (moveEvent) => {
          const rect = canvas.getBoundingClientRect();
          const px = Math.min(
            98,
            Math.max(2, ((moveEvent.clientX - rect.left) / rect.width) * 100),
          );
          const py = Math.min(
            98,
            Math.max(2, ((moveEvent.clientY - rect.top) / rect.height) * 100),
          );
          item.style.left = px + "%";
          item.style.top = py + "%";
          if (x) x.value = Math.round(px * 2);
          if (y) y.value = Math.round(py * 2);
        };
        item.onpointermove = move;
        item.onpointerup = () => {
          item.onpointermove = null;
        };
      });
      items.append(item);
    });
  modal
    .querySelectorAll(
      ".template-position-fields input,.template-position-fields select",
    )
    .forEach((input) => {
      input.oninput = () => renderTemplatePreview(modal);
    });
}

document.addEventListener("DOMContentLoaded", () => {
  const courseModal = document.querySelector("#course-modal");
  const courseType = courseModal?.querySelector('[name="tipo_certificado_id"]');
  const courseTemplate = courseModal?.querySelector('[name="plantilla_id"]');
  if (courseType && courseTemplate) {
    const syncCourseTemplate = () => {
      const selected = courseType.options[courseType.selectedIndex];
      const templateId = selected?.dataset.templateId || "";
      courseTemplate.value = templateId;
      courseTemplate.dispatchEvent(new Event("change", { bubbles: true }));
    };
    courseType.addEventListener("change", syncCourseTemplate);
    courseType.querySelectorAll("option").forEach((option) => {
      const type = option.value;
      const row = document.querySelector(`.type-row[data-type-id="${type}"]`);
      if (row?.dataset.templateId) option.dataset.templateId = row.dataset.templateId;
    });
  }
  if (
    document.querySelector(".report-page") &&
    window.history?.replaceState &&
    window.location.search
  ) {
    const cleanUrl = new URL(window.location.href);
    ["curso_id", "estado", "desde", "hasta"].forEach((key) =>
      cleanUrl.searchParams.delete(key),
    );
    window.history.replaceState(
      {},
      document.title,
      cleanUrl.pathname + cleanUrl.search + cleanUrl.hash,
    );
  }
  const reportTable = document.querySelector(".report-table");
  if (reportTable) {
    const rows = [...reportTable.querySelectorAll(".report-row")];
    const search = reportTable.querySelector(".report-search");
    const prev = reportTable.querySelector(".report-prev");
    const next = reportTable.querySelector(".report-next");
    const pageNumber = reportTable.querySelector(".report-page-number");
    const count = reportTable.querySelector(".report-count");
    if (prev)
      prev.innerHTML =
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg><span>Anterior</span>';
    if (next)
      next.innerHTML =
        '<span>Siguiente</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';
    let page = 1;
    const perPage = 10;
    const render = () => {
      const query = (search?.value || "").toLowerCase().trim();
      const filtered = rows.filter((row) =>
        row.textContent.toLowerCase().includes(query),
      );
      const pages = Math.max(1, Math.ceil(filtered.length / perPage));
      page = Math.min(page, pages);
      rows.forEach((row) => {
        row.hidden = true;
      });
      let emptyRow = reportTable.querySelector(".report-search-empty");
      if (!filtered.length) {
        if (!emptyRow) {
          emptyRow = document.createElement("tr");
          emptyRow.className = "report-search-empty";
          emptyRow.innerHTML =
            '<td colspan="7"><span class="report-empty-icon">⌕</span><span>No se encontraron registros</span></td>';
          reportTable.querySelector("tbody").append(emptyRow);
        }
        emptyRow.hidden = false;
      } else if (emptyRow) emptyRow.hidden = true;
      filtered.slice((page - 1) * perPage, page * perPage).forEach((row) => {
        row.hidden = false;
      });
      if (count)
        count.textContent = filtered.length
          ? `Mostrando ${(page - 1) * perPage + 1} a ${Math.min(page * perPage, filtered.length)} de ${filtered.length} registros`
          : "No se encontraron registros";
      if (pageNumber) pageNumber.textContent = page;
      if (prev) prev.disabled = page <= 1;
      if (next) next.disabled = page >= pages;
    };
    search?.addEventListener("input", () => {
      page = 1;
      render();
    });
    prev?.addEventListener("click", () => {
      page--;
      render();
    });
    next?.addEventListener("click", () => {
      page++;
      render();
    });
    render();
  }
  if (
    document.querySelector("#bulk-emit-modal") &&
    window.history?.replaceState
  ) {
    const cleanUrl = new URL(window.location.href);
    cleanUrl.searchParams.delete("emitidos");
    window.history.replaceState(
      {},
      document.title,
      cleanUrl.pathname + cleanUrl.search + cleanUrl.hash,
    );
  }
  document
    .querySelectorAll("[data-close-bulk]")
    .forEach((button) =>
      button.addEventListener("click", () =>
        document.querySelector("#bulk-emit-modal")?.remove(),
      ),
    );
  const courseFilter = document.querySelector("#certificate-course-filter");
  const selectAll = document.querySelector("#certificate-select-all");
  const emitButton = document.querySelector("#bulk-emit-button");
  const printButton = document.querySelector("#bulk-print-button");
  const updateEmitButton = () => {
    const selected = [
      ...document.querySelectorAll("input[name='ids[]']:checked"),
    ];
    const states = selected.map(
      (box) => box.closest(".certificate-row")?.dataset.state,
    );
    const onlyDrafts =
      selected.length > 0 && states.every((state) => state === "borrador");
    const onlyIssued =
      selected.length > 0 && states.every((state) => state === "emitido");
    if (emitButton) emitButton.disabled = !onlyDrafts;
    if (printButton) printButton.disabled = !onlyIssued;
  };
  document
    .querySelectorAll("input[name='ids[]']")
    .forEach((box) => box.addEventListener("change", updateEmitButton));
  updateEmitButton();
  if (courseFilter)
    courseFilter.addEventListener("change", () => {
      document.querySelectorAll(".certificate-row").forEach((row) => {
        row.hidden = Boolean(
          courseFilter.value && row.dataset.course !== courseFilter.value,
        );
      });
    });
  if (selectAll)
    selectAll.addEventListener("change", () => {
      document
        .querySelectorAll(
          ".certificate-row:not([hidden]) input[name='ids[]']:not(:disabled)",
        )
        .forEach((box) => {
          box.checked = selectAll.checked;
        });
      updateEmitButton();
    });
});

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".legacy-select2-disabled").forEach((select) => {
    const wrapper = document.createElement("div");
    wrapper.className = "select2-lite";
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
    select.classList.add("select2-native");
    const trigger = document.createElement("button");
    trigger.type = "button";
    trigger.className = "select2-trigger";
    trigger.textContent = select.options[select.selectedIndex]?.text || "";
    wrapper.appendChild(trigger);
    const dropdown = document.createElement("div");
    dropdown.className = "select2-dropdown";
    const search = document.createElement("input");
    search.type = "search";
    search.placeholder = "Buscar...";
    dropdown.appendChild(search);
    const list = document.createElement("div");
    list.className = "select2-options";
    const normalizeSearch = (value) =>
      value
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase();
    const syncOptions = () => {
      const searchText = normalizeSearch(search.value.trim());
      list.replaceChildren();
      [...select.options].forEach((option) => {
        const item = document.createElement("button");
        item.type = "button";
        item.className = "select2-option";
        item.textContent = option.text;
        item.dataset.value = option.value;
        item.hidden = !normalizeSearch(option.text).includes(searchText);
        item.addEventListener("click", () => {
          select.value = option.value;
          select.dispatchEvent(new Event("change", { bubbles: true }));
          dropdown.classList.remove("open");
        });
        list.appendChild(item);
      });
      trigger.textContent = select.selectedOptions[0]?.textContent || "";
    };
    dropdown.appendChild(list);
    wrapper.appendChild(dropdown);
    syncOptions();
    // El selector de campos recibe opciones dinámicamente tras cargar la plantilla.
    new MutationObserver(syncOptions).observe(select, {
      childList: true,
      subtree: true,
      characterData: true,
    });
    select.addEventListener("change", () => {
      trigger.textContent = select.selectedOptions[0]?.textContent || "";
    });
    trigger.addEventListener("click", () => dropdown.classList.toggle("open"));
    search.addEventListener("input", syncOptions);
    document.addEventListener("click", (event) => {
      if (!wrapper.contains(event.target)) dropdown.classList.remove("open");
    });
  });
  const serverAlert = document.querySelector(".server-form-alert");
  if (serverAlert)
    showParticipantAlert(
      serverAlert.dataset.message ||
        "No se pudo guardar el tipo de certificado.",
    );
  const successAlert = document.querySelector(".server-success-alert");
  if (successAlert)
    showSuccessAlert(
      successAlert.dataset.message || "Operación realizada correctamente.",
    );
  const templateForm = document.querySelector("#template-modal form");
  if (templateForm) {
    templateForm.dataset.allowSubmit = "0";
    templateForm.addEventListener(
      "keydown",
      (event) => {
        if (event.key !== "Enter") return;
        event.preventDefault();
        const fields = [
          ...templateForm.querySelectorAll(
            'input:not([type="hidden"]), select, textarea, button[type="submit"]',
          ),
        ].filter((field) => !field.disabled);
        const index = fields.indexOf(event.target);
        if (index >= 0 && fields[index + 1]) fields[index + 1].focus();
      },
      true,
    );
    templateForm.addEventListener("submit", (event) => {
      event.preventDefault();
    });
    templateForm
      .querySelector('button[type="submit"]')
      .addEventListener("click", () => {
        if (!templateForm.elements.fecha_sesion.value.trim()) {
          showParticipantAlert(
            "Ingresa la fecha de sesión de la plantilla para continuar.",
          );
          templateForm.elements.fecha_sesion.focus();
          return;
        }
        if (!templateForm.elements.nombre.value.trim()) {
          showParticipantAlert(
            "Ingresa el nombre de la plantilla para continuar.",
          );
          templateForm.elements.nombre.focus();
          return;
        }
        HTMLFormElement.prototype.submit.call(templateForm);
      });
  }
  const configForm = document.querySelector(".template-config-form");
  const signatureToggle = configForm?.querySelector(".template-signature-toggle");
  const visibleFields = configForm?.querySelector(".template-fields");
  if (signatureToggle && visibleFields) visibleFields.append(signatureToggle);
  if (configForm) {
    const configModal = document.querySelector("#template-config-modal");
    const templateFields = configForm.querySelector(".template-fields");
    if (
      templateFields &&
      !templateFields.querySelector('input[value="codigo_verificacion"]')
    ) {
      const label = document.createElement("label");
      label.innerHTML =
        '<input type="checkbox" value="codigo_verificacion"> Código de verificación';
      templateFields.append(label);
      try {
        const savedConfig = JSON.parse(configModal?.dataset.config || "{}");
        label.querySelector('input[value="codigo_verificacion"]').checked =
          Boolean(savedConfig.campos?.codigo_verificacion?.visible);
      } catch (_) {}
    }
    configForm
      .querySelectorAll(".template-fields input[type=checkbox], .template-signature-toggle input[type=checkbox]")
      .forEach((box) => {
        box.onchange = () => renderTemplatePositions(configModal);
      });
    configForm.addEventListener("submit", () => {
      const campos = {};
      configForm
        .querySelectorAll(".template-fields input[type=checkbox]")
        .forEach((box) => {
          if (box.checked) campos[box.value] = { visible: true };
        });
      configForm
        .querySelectorAll(
          ".template-position-fields [data-position][data-axis]",
        )
        .forEach((input) => {
          campos[input.dataset.position] ??= { visible: true };
          campos[input.dataset.position][input.dataset.axis] =
            input.type === "number" ? Number(input.value) : input.value;
        });
      configForm.elements.configuracion_json.value = JSON.stringify({
        version: 3,
        coordinate_system: "pdf-points",
        canvas_width: 842,
        canvas_height: 595,
        campos,
        fuente: configForm.elements.fuente.value,
        tamano: Number(configForm.elements.tamano.value),
        color: configForm.elements.color.value,
      });
    });
  }
});

document.addEventListener("change", (event) => {
  if (!event.target.matches('.participant-form select[name="tipo_documento"], .signer-form select[name="tipo_documento"]'))
    return;
  const input = event.target.form.elements.numero_documento;
  input.value = "";
  const names = event.target.form.classList.contains("participant-form")
    ? ["nombres", "apellidos", "email", "telefono", "nro_registro_cas"]
    : ["nombres", "apellidos"];
  names.forEach((name) => {
    event.target.form.elements[name].value = "";
  });
  input.maxLength = event.target.value === "DNI" ? 8 : 12;
  input.inputMode =
    event.target.value === "DNI" || event.target.value === "CE"
      ? "numeric"
      : "text";
  input.disabled = !event.target.value;
  input.focus();
});

document.addEventListener("input", (event) => {
  if (
    event.target.matches(
      '.type-form input[name="codigo"], .type-form input[name="nombre"], .type-form textarea[name="descripcion"]',
    )
  )
    event.target.value = event.target.value.toUpperCase();
  if (
    event.target.matches(
      '.signer-form input[name="nombres"], .signer-form input[name="apellidos"], .signer-form input[name="cargo"]',
    )
  )
    event.target.value = event.target.value
      .replace(/[^\p{L} ]/gu, "")
      .toUpperCase();
});

document.addEventListener("change", (event) => {
  if (event.target.matches('.file-upload input[type="file"]'))
    event.target
      .closest(".file-upload")
      .querySelector(".file-upload-name").textContent =
      event.target.files[0]?.name || "Ningún archivo seleccionado";
});

document.addEventListener("input", (event) => {
  if (event.target.matches(".data-search")) {
    const table = event.target.closest(".recent")?.querySelector("table");
    const query = event.target.value.toLowerCase().trim();
    if (table) {
      let visible = 0;
      table.querySelectorAll("tbody tr").forEach((row) => {
        if (!row.classList.contains("empty")) {
          row.hidden = !row.textContent.toLowerCase().includes(query);
          if (!row.hidden) visible++;
        }
      });
      let empty = table.querySelector(".search-empty");
      if (!visible && query) {
        if (!empty) {
          empty = document.createElement("tr");
          empty.className = "search-empty";
          const cell = document.createElement("td");
          cell.colSpan = table.querySelectorAll("thead th").length;
          cell.innerHTML =
            '<span class="search-empty-icon">⌕</span><span>No se encontraron registros</span>';
          empty.append(cell);
          table.querySelector("tbody").append(empty);
        }
        empty.hidden = false;
      } else if (empty) empty.hidden = true;
    }
  }
  if (
    event.target.matches(
      '.participant-form input[name="nombres"], .participant-form input[name="apellidos"]',
    )
  ) {
    event.target.value = event.target.value
      .replace(/[^a-záéíóúüñ\s]/gi, "")
      .toUpperCase();
  }
});

document.addEventListener("input", (event) => {
  if (
    event.target.matches(
      '.participant-form input[name="nro_registro_cas"], .participant-form input[name="telefono"]',
    )
  ) {
    event.target.value = event.target.value.replace(/\D/g, "");
  }
});

document.addEventListener("input", (event) => {
    if (!event.target.matches('.participant-form input[name="numero_documento"], .signer-form input[name="numero_documento"]'))
    return;
  const type = event.target.form.elements.tipo_documento.value;
  if (type === "DNI" || type === "CE") {
    event.target.value = event.target.value.replace(/\D/g, "");
  } else if (type === "PASAPORTE") {
    event.target.value = event.target.value.replace(/[^a-z0-9]/gi, "");
  }
  if (
    event.target.matches(
      '.participant-form input[name="telefono"], .participant-form input[name="nro_registro_cas"]',
    )
  ) {
    event.target.value = event.target.value.replace(/\D/g, "");
  }
  if (event.target.matches('.participant-form input[name="email"]')) {
    event.target.value = event.target.value.toUpperCase();
  }
  if (event.target.matches('.course-modal input[name="nombre"]')) {
    event.target.value = event.target.value
      .replace(/[^a-záéíóúüñ\s]/gi, "")
      .toUpperCase();
  }
  if (event.target.matches('.course-modal input[name="horas_academicas"]')) {
    event.target.value = event.target.value.replace(/[^0-9]/g, "");
  }
  if (event.target.matches('.course-modal textarea[name="descripcion"]')) {
    event.target.value = event.target.value.toUpperCase();
  }
});

document.addEventListener("input", (event) => {
  if (event.target.matches('.course-modal input[name="codigo"]')) {
    event.target.value = event.target.value.toUpperCase();
  }
  if (event.target.matches('.course-modal input[name="nombre"]'))
    event.target.value = event.target.value
      .replace(/[^a-záéíóúüñ\s]/gi, "")
      .toUpperCase();
  if (event.target.matches('.course-modal input[name="horas_academicas"]'))
    event.target.value = event.target.value.replace(/[^0-9]/g, "");
  if (event.target.matches('.course-modal textarea[name="descripcion"]'))
    event.target.value = event.target.value.toUpperCase();
});

document.addEventListener(
  "blur",
  async (event) => {
    if (
      !event.target.matches('.participant-form input[name="numero_documento"], .signer-form input[name="numero_documento"]')
    )
      return;
    const form = event.target.form;
    if (
      form.elements.tipo_documento.value !== "DNI" ||
      !/^\d{8}$/.test(event.target.value.trim())
    )
      return;
    const loading = document.createElement("div");
    loading.className = "reniec-loading";
    loading.innerHTML =
      '<div class="reniec-loading-card"><span class="reniec-spinner"></span><span>Buscando en RENIEC<span class="reniec-dots">...</span></span></div>';
    document.body.append(loading);
    try {
      const response = await fetch(
        `reniec?numero=${encodeURIComponent(event.target.value.trim())}`,
        { headers: { Accept: "application/json" } },
      );
      const result = await response.json();
      if (result.success) {
        form.elements.nombres.value = result.nombres || "";
        form.elements.apellidos.value = [
          result.apellidoPaterno,
          result.apellidoMaterno,
        ]
          .filter(Boolean)
          .join(" ");
      } else {
        showParticipantAlert(
          result.message || "No se encontraron datos para el DNI.",
        );
      }
    } catch {
      showParticipantAlert("No se pudo consultar los datos del DNI.");
    } finally {
      loading.remove();
    }
  },
  true,
);
document.addEventListener("DOMContentLoaded", () => {
  const page = document.querySelector(".template-config-view");
  const modal = document.querySelector("#template-config-modal");
  if (page && modal && typeof renderTemplatePositions === "function") {
    renderTemplatePositions(modal);
    let cfg = {};
    try {
      cfg = JSON.parse(modal.dataset.config || "{}");
    } catch (e) {}
    Object.entries(cfg.campos || {}).forEach(([key, value]) => {
      modal.querySelectorAll(`[data-position="${key}"]`).forEach((input) => {
        const legacyDefault = value.x === 35 && value.y === 100;
        if (!legacyDefault && value[input.dataset.axis] !== undefined)
          input.value = value[input.dataset.axis];
      });
    });
    if (typeof renderTemplatePreview === "function")
      renderTemplatePreview(modal);
    setTimeout(() => renderTemplatePreview(modal), 0);
  }
});
function renderTemplatePreview(modal) {
  const wrap = modal.querySelector(".template-preview-wrap");
  if (!wrap) return;
  const canvas = wrap.querySelector(".template-preview-canvas");
  canvas.dataset.coordinateSystem = "pdf-points";
  const items = wrap.querySelector(".template-preview-items");
  items.innerHTML = "";
  const PDF_W = 842,
    PDF_H = 595;
  const months = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
  ];
  const today = new Date();
  const currentSystemDate = `Chimbote, ${today.getDate()} de ${months[today.getMonth()]} de ${today.getFullYear()}`;
  let templateData = {};
  try {
    templateData = JSON.parse(modal.dataset.config || "{}").datos || {};
  } catch (_) {}
  const formatTemplateDate = (value) => {
    if (!value) return "";
    const parts = String(value).split("-");
    if (parts.length !== 3) return String(value);
    const date = new Date(
      Number(parts[0]),
      Number(parts[1]) - 1,
      Number(parts[2]),
    );
    return `${date.getDate()} de ${months[date.getMonth()]} de ${date.getFullYear()}`;
  };
  const samples = {
    participante: "KATHERINE OLIVERA ROJAS",
    registro_cas: "12345",
    fecha_sesion:
      formatTemplateDate(templateData.fecha_sesion) || "Fecha de sesión",
    fecha_inicio:
      formatTemplateDate(modal.dataset.previewStart || templateData.fecha_inicio) || "Fecha de inicio",
    fecha_fin: formatTemplateDate(modal.dataset.previewEnd || templateData.fecha_fin) || "Fecha de fin",
    fecha_sistema: currentSystemDate,
    curso: modal.dataset.previewCourse || "CURSO DE EJEMPLO",
    tipo: modal.dataset.previewType || "TIPO DE CERTIFICADO",
    horas: (() => {
      const value = modal.dataset.previewHours || templateData.horas || "";
      const number = Number(value);
      return value !== "" && Number.isFinite(number)
        ? String(number).replace(/\.0+$/, "")
        : value || "Horas académicas";
    })(),
    codigo_verificacion: "CERT-2026-8F4K92",
    firmante: modal.dataset.previewSigner || "FIRMANTE",
  };
  modal
        .querySelectorAll('.template-fields input[type="checkbox"]:checked, .template-signature-toggle input[type="checkbox"]:checked')
    .forEach((check) => {
      const key = check.value,
        item = document.createElement("span");
      item.className = "template-preview-item";
      item.dataset.field = key;
      if (key === "firma" && modal.dataset.previewSignature) {
        item.textContent = "";
        item.style.width = "90px";
        item.style.height = "45px";
        item.style.backgroundImage = `url("${modal.dataset.previewSignature}")`;
        item.style.backgroundPosition = "center";
        item.style.backgroundRepeat = "no-repeat";
        item.style.backgroundSize = "contain";
      } else {
        item.textContent = samples[key] || check.parentElement.textContent.trim();
      }
      const x = modal.querySelector(`[data-position="${key}"][data-axis="x"]`),
        y = modal.querySelector(`[data-position="${key}"][data-axis="y"]`);
      const px = Number(x?.value || 100),
        py = Number(y?.value || 100);
      item.style.left =
        (key === "participante" ? 55 : (px / PDF_W) * 100) + "%";
      item.style.top = (py / PDF_H) * 100 + "%";
      item.addEventListener("pointerdown", (e) => {
        e.preventDefault();
        item.setPointerCapture(e.pointerId);
        item.onpointermove = (m) => {
          const r = items.getBoundingClientRect();
          const nx = Math.max(
            0,
            Math.min(PDF_W, ((m.clientX - r.left) / r.width) * PDF_W),
          );
          const ny = Math.max(
            0,
            Math.min(PDF_H, ((m.clientY - r.top) / r.height) * PDF_H),
          );
          item.style.left =
            (key === "participante" ? 55 : (nx / PDF_W) * 100) + "%";
          item.style.top = (ny / PDF_H) * 100 + "%";
          if (x && key !== "participante") x.value = Math.round(nx);
          if (y) y.value = Math.round(ny);
        };
        item.onpointerup = () => (item.onpointermove = null);
      });
      items.append(item);
    });
  modal.dispatchEvent(new CustomEvent("template-fields-rendered"));
}

// Propiedades individuales: cada campo conserva su estilo, independientemente del selector.
document.addEventListener("DOMContentLoaded", () => {
  const page = document.querySelector(".template-config-view");
  if (!page) return;
  const modal = page.querySelector("#template-config-modal");
  const form = page.querySelector(".template-config-form");
  const selector = page.querySelector("#template-style-target");
  const styleSelect = page.querySelector("#template-font-style");
  const caseSelect = page.querySelector("#template-text-case");
  if (!modal || !form || !selector || !styleSelect || !caseSelect) return;
  let saved = {};
  try {
    saved = JSON.parse(modal.dataset.config || "{}");
  } catch (_) {}
  const defaults = {
    fuente: saved.fuente || "Helvetica",
    tamano: Number(saved.tamano) || 12,
    color: saved.color || "#000000",
    estilo: "",
    transformacion: "none",
  };
  const styles = {};
  const checks = () => [
    ...form.querySelectorAll('.template-fields input[type="checkbox"]'),
  ];
  const fieldStyle = (field) => {
    if (!styles[field]) {
      const previous = saved.campos?.[field] || {};
      styles[field] = {
        fuente: previous.fuente || defaults.fuente,
        tamano: Number(previous.tamano ?? defaults.tamano),
        color: previous.color || defaults.color,
        estilo: previous.estilo || "",
        transformacion: previous.transformacion || "none",
        ancho: Number(previous.ancho) || 90,
        alto: Number(previous.alto) || 45,
      };
    }
    return styles[field];
  };
  let active = "";
  function readControls() {
    if (!active) return;
    const style = fieldStyle(active);
    style.fuente = form.elements.fuente.value;
    style.tamano = Math.max(
      6,
      Math.min(72, Number(form.elements.tamano.value) || 12),
    );
    style.color = form.elements.color.value;
    style.estilo = styleSelect.value;
    style.transformacion = caseSelect.value;
    if (active === "firma") {
      style.ancho = Math.max(20, Math.min(300, Number(form.elements.firma_ancho.value) || 90));
      style.alto = Math.max(10, Math.min(200, Number(form.elements.firma_alto.value) || 45));
    }
  }
  function loadControls(field) {
    active = field;
    const style = fieldStyle(field);
    form.elements.fuente.value = style.fuente;
    form.elements.tamano.value = style.tamano;
    form.elements.color.value = style.color;
    styleSelect.value = style.estilo;
    caseSelect.value = style.transformacion || "none";
    form.elements.firma_ancho.value = style.ancho;
    form.elements.firma_alto.value = style.alto;
    form.querySelectorAll(".signature-size-control").forEach((field) => { field.hidden = active !== "firma"; });
    paint();
  }
  function paint() {
    const overlay = modal.querySelector(".template-preview-items");
    if (!overlay) return;
    const pdfWidth = Number(
      modal.querySelector(".template-preview-render")?.dataset.pdfWidth || 842,
    );
    const scale = (overlay.getBoundingClientRect().width || 842) / pdfWidth;
    overlay.querySelectorAll(".template-preview-item").forEach((node) => {
      const name = node.dataset.field;
      if (!name) return;
      const style = fieldStyle(name);
      const originalText = node.dataset.originalText || node.textContent;
      node.dataset.originalText = originalText;
      if (style.transformacion === "uppercase")
        node.textContent = originalText.toLocaleUpperCase("es");
      else if (style.transformacion === "lowercase")
        node.textContent = originalText.toLocaleLowerCase("es");
      else if (style.transformacion === "title")
        node.textContent = originalText
          .toLocaleLowerCase("es")
          .replace(
            /(^|[\s\-])([\p{L}])/gu,
            (_, prefix, letter) => prefix + letter.toLocaleUpperCase("es"),
          );
      else node.textContent = originalText;
      node.style.fontFamily = ["Helvetica", "Times", "Courier"].includes(
        style.fuente,
      )
        ? style.fuente === "Times"
          ? "Times New Roman, serif"
          : style.fuente === "Courier"
            ? "Courier New, monospace"
            : "Arial, sans-serif"
        : '"' + style.fuente + '", serif';
      node.style.fontSize = `${Math.max(4, style.tamano * (96 / 72) * scale)}px`;
      node.style.color = style.color;
      node.style.fontWeight = style.estilo.includes("B") ? "bold" : "normal";
      node.style.fontStyle = style.estilo.includes("I") ? "italic" : "normal";
      if (name === "firma") {
        node.style.width = `${style.ancho * scale}px`;
        node.style.height = `${style.alto * scale}px`;
        node.style.minWidth = "0";
        node.style.minHeight = "0";
      }
      if (name === "participante") {
        // Eje central igual que el generador PDF; cada nombre puede ser mas largo o corto.
        node.style.left = "55%";
        node.style.whiteSpace = "nowrap";
        node.style.maxWidth = "68%";
        let sizePx = parseFloat(node.style.fontSize) || 12;
        const maxPx = overlay.getBoundingClientRect().width * 0.68 - 3;
        while (sizePx > 8 && node.scrollWidth > maxPx) {
          sizePx -= 0.5;
          node.style.fontSize = `${sizePx}px`;
        }
      }
    });
  }
  function buildOptions() {
    const previous = active;
    selector.replaceChildren();
    checks().forEach((check) => {
      const option = document.createElement("option");
      option.value = check.value;
      option.textContent = check.parentElement.textContent.trim();
      selector.append(option);
      fieldStyle(check.value);
    });
    const first =
      checks().find((check) => check.checked)?.value ||
      checks()[0]?.value ||
      "";
    const next = checks().some((check) => check.value === previous)
      ? previous
      : first;
    if (next) {
      selector.value = next;
      loadControls(next);
    }
  }
  selector.addEventListener("change", () => {
    readControls();
    const next = selector.value;
    const check = checks().find((c) => c.value === next);
    if (check && !check.checked) {
      check.checked = true;
      renderTemplatePositions(modal);
      renderTemplatePreview(modal);
    }
    loadControls(next);
  });
  [
    form.elements.fuente,
    form.elements.tamano,
    form.elements.color,
    form.elements.firma_ancho,
    form.elements.firma_alto,
    styleSelect,
    caseSelect,
  ].forEach((control) => {
    control.addEventListener("input", () => {
      readControls();
      paint();
    });
    control.addEventListener("change", () => {
      readControls();
      paint();
    });
  });
  checks().forEach((check) =>
    check.addEventListener("change", () => {
      readControls();
      renderTemplatePositions(modal);
      renderTemplatePreview(modal);
      buildOptions();
    }),
  );
  modal.addEventListener("template-fields-rendered", paint);
  window.addEventListener("template-pdf-rendered", paint);
  modal
    .querySelector(".template-preview-items")
    ?.addEventListener("pointerdown", (event) => {
      const marker = event.target.closest(".template-preview-item");
      if (!marker || !marker.dataset.field || active === marker.dataset.field)
        return;
      readControls();
      selector.value = marker.dataset.field;
      selector.dispatchEvent(new Event("change", { bubbles: true }));
    });
  // Primer listener construye posiciones; este segundo guarda TODOS los estilos por campo.
  form.addEventListener("submit", () => {
    readControls();
    let data = {};
    try {
      data = JSON.parse(form.elements.configuracion_json.value || "{}");
    } catch (_) {}
    data.campos ||= {};
    if (saved.datos && typeof saved.datos === "object")
      data.datos = { ...saved.datos, ...(data.datos || {}) };
    checks()
      .filter((c) => c.checked)
      .forEach((check) => {
        const style = fieldStyle(check.value);
        data.campos[check.value] ||= { visible: true };
        Object.assign(data.campos[check.value], {
          fuente: style.fuente,
          tamano: style.tamano,
          color: style.color,
          estilo: style.estilo,
          transformacion: style.transformacion,
          ...(check.value === "firma" ? { ancho: style.ancho, alto: style.alto } : {}),
        });
      });
    form.elements.configuracion_json.value = JSON.stringify(data);
  });

  let saving = false;
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (saving) return;
    const saveButton = form.querySelector('button[type="submit"]');
    let status = form.querySelector(".template-save-feedback");
    if (!status) {
      status = document.createElement("span");
      status.className = "template-save-feedback";
      status.setAttribute("role", "status");
      status.setAttribute("aria-live", "polite");
      const actions = form.querySelector(".form-actions");
      if (actions) actions.prepend(status);
    }
    saving = true;
    if (saveButton) saveButton.disabled = true;
    status.textContent = "Guardando configuración...";
    status.style.color = "#93c5fd";
    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        credentials: "same-origin",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      });
      const payload = await response.json();
      if (!response.ok || !payload.success)
        throw new Error(payload.message || "No se pudo guardar.");
      status.textContent =
        "✓ Guardado. Puedes seguir configurando otros campos.";
      status.remove();
      showSuccessAlert(
        payload.message || "Guardado. Puedes seguir configurando otros campos.",
      );

      modal.dataset.config = form.elements.configuracion_json.value;
    } catch (error) {
      status.textContent =
        "No se guardó: " + (error.message || "Error de conexión");
      status.style.color = "#fca5a5";
    } finally {
      saving = false;
      if (saveButton) saveButton.disabled = false;
    }
  });
  buildOptions();
  paint();
});
document.addEventListener("click", (event) => {
  if (window.innerWidth > 720) return;
  const action = event.target.closest(".recent .row-actions button:first-child");
  if (!action) return;
  const row = action.closest(".dashboard-certificate-row");
  const id = row?.dataset.certificateId;
  if (!id) return;
  event.preventDefault();
  event.stopImmediatePropagation();
  window.open(`certificado_pdf?pdf=${encodeURIComponent(id)}`, "_blank", "noopener");
}, true);
document.addEventListener("click", (event) => {
  if (window.innerWidth > 720) return;
  const button = event.target.closest("#bulk-print-button");
  if (!button) return;
  const ids = [...document.querySelectorAll("input[name='ids[]']:checked")].map((box) => box.value);
  if (!ids.length) return;
  event.preventDefault();
  event.stopImmediatePropagation();
  fetch(new URL("certificado_token", document.baseURI).href, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    body: JSON.stringify({ ids }),
  })
    .then((response) => response.json().then((data) => ({ response, data })))
    .then(({ response, data }) => {
      if (!response.ok || !data.url) throw new Error(data.error || "No se pudo generar el token.");
      window.location.href = data.url.replace("certificado_pdf", "certificado_viewer");
    })
    .catch((error) => window.alert(error.message));
}, true);
