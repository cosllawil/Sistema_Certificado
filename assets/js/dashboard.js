function openChangePasswordModal() {
  document.querySelector(".change-password-modal")?.remove();
  const modal = document.createElement("div");
  modal.className = "change-password-modal";
  const token = document.body.dataset.changePasswordCsrf || "";
  modal.innerHTML = `<div class="change-password-dialog" role="dialog" aria-modal="true"><div class="change-password-header"><h2><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>Cambiar contraseña</h2><button type="button" class="participant-modal-close change-password-close">×</button></div><form method="post" action="cambiar_contrasena"><input type="hidden" name="_token" value="${token}"><label>Contraseña actual<input name="current_password" type="password" required></label><label>Nueva contraseña<input name="new_password" type="password" minlength="8" required></label><label>Confirmar nueva contraseña<input name="confirm_password" type="password" minlength="8" required></label><div class="change-password-actions"><button type="button" class="course-cancel change-password-cancel"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>Cancelar</button><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h11l3 3v13H5zM8 4v5h8V4M8 16h8"/></svg>Guardar contraseña</button></div></form></div>`;
  document.body.append(modal);
  const close = () => modal.remove();
  modal
    .querySelector(".change-password-close")
    .addEventListener("click", close);
  modal
    .querySelector(".change-password-cancel")
    .addEventListener("click", close);
  modal.addEventListener("click", (event) => {
    if (event.target === modal) close();
  });
  const passwordForm = modal.querySelector("form");
  passwordForm?.setAttribute("novalidate", "novalidate");
  passwordForm?.addEventListener("submit", (event) => {
    const fields = [
      ["current_password", "contraseña actual"],
      ["new_password", "nueva contraseña"],
      ["confirm_password", "confirmación de contraseña"],
    ];
    const missing = fields.find(
      ([name]) => !passwordForm.elements[name].value.trim(),
    );
    if (missing) {
      event.preventDefault();
      if (typeof showParticipantAlert === "function")
        showParticipantAlert(`Completa el campo ${missing[1]}.`);
      passwordForm.elements[missing[0]].focus();
      return;
    }
    if (passwordForm.elements.new_password.value.length < 8) {
      event.preventDefault();
      if (typeof showParticipantAlert === "function")
        showParticipantAlert(
          "La nueva contraseña debe tener al menos 8 caracteres.",
        );
      passwordForm.elements.new_password.focus();
      return;
    }
    if (
      passwordForm.elements.new_password.value !==
      passwordForm.elements.confirm_password.value
    ) {
      event.preventDefault();
      if (typeof showParticipantAlert === "function")
        showParticipantAlert("Las contraseñas nuevas no coinciden.");
      passwordForm.elements.confirm_password.focus();
    }
  });
  modal.querySelector("input")?.focus();
}

(() => {
  "use strict";
  const dashboardData = JSON.parse(
    document.querySelector("#dashboard-data")?.textContent || "{}",
  );
  const permissions = new Set(dashboardData.permissions || []);
  const notificationContent = () => {
    const items = dashboardData.notification_items || [];
    if (!items.length)
      return '<strong>Notificaciones</strong><span class="notification-empty">No tienes notificaciones nuevas.</span>';
    return '<strong>Notificaciones</strong><div class="notification-list">' + items.map((item) => `<article class="notification-item"><b>${item.titulo || 'Notificación'}</b><span>${item.mensaje || ''}</span><small>${item.created_at || ''}</small></article>`).join('') + '</div>';
  };
  document.addEventListener(
    "click",
    (event) => {
      const bell = event.target.closest(".notification");
      const current = document.querySelector(".notification-dropdown");
      if (bell) {
        event.stopPropagation();
        if (!current) {
          const panel = document.createElement("div");
          panel.className = "notification-dropdown";
          panel.innerHTML = notificationContent();
          bell.parentElement.append(panel);
        } else {
          current.hidden = !current.hidden;
        }
        return;
      }
      if (current && !event.target.closest(".notification-dropdown"))
        current.hidden = true;
    },
    true,
  );
  const menuPermissionMap = {
    Dashboard: "dashboard.ver",
    "Registrar Certificado": "certificados.crear",
    Certificados: "certificados.ver",
    Plantillas: "plantillas.ver",
    Reportes: "reportes.ver",
    Usuarios: "usuarios.administrar",
    Configuración: "configuracion.administrar",
    Participantes: "participantes.ver",
    Cursos: "cursos.ver",
    Firmantes: "firmantes.administrar",
    "Roles y permisos": "roles.administrar",
    Auditoría: "auditoria.ver",
  };
  document.querySelectorAll(".menu a").forEach((link) => {
    const label = link
      .querySelector("span:not(.menu-icon)")
      ?.textContent.trim();
    const requiredPermission = menuPermissionMap[label];
    if (
      requiredPermission &&
      permissions.size > 0 &&
      !permissions.has(requiredPermission)
    )
      link.remove();
  });
  const monthlyTotals = Array.from({ length: 12 }, () => 0);
  (dashboardData.monthly || []).forEach((row) => {
    monthlyTotals[Number(row.month_number) - 1] = Number(row.total);
  });
  const maxMonthly = Math.max(...monthlyTotals, 1);
  document.querySelectorAll(".bars i").forEach((bar, index) => {
    bar.style.height = `${Math.max(4, (monthlyTotals[index] / maxMonthly) * 100)}%`;
  });
  const statuses = dashboardData.statuses || {
    activo: 0,
    pendiente: 0,
    impreso: 0,
    anulado: 0,
  };
  const statusTotal = Object.values(statuses).reduce(
    (total, value) => total + Number(value),
    0,
  );
  const donut = document.querySelector(".donut");
  if (donut) {
    donut.querySelector("strong").textContent = statusTotal;
    const percent = (value) =>
      statusTotal ? (Number(value) / statusTotal) * 100 : 0;
    const active = percent(statuses.activo);
    const pending = active + percent(statuses.pendiente);
    const printed = pending + percent(statuses.impreso);
    donut.style.background = `conic-gradient(#22df8b 0 ${active}%, #f5b52e ${active}% ${pending}%, #62a0ef ${pending}% ${printed}%, #a5b5ce ${printed}% 100%)`;
  }
  const statusValues = document.querySelectorAll(".status-content li b");
  [
    statuses.activo,
    statuses.pendiente,
    statuses.impreso,
    statuses.anulado,
  ].forEach((value, index) => {
    if (statusValues[index]) statusValues[index].textContent = value;
  });
  const brandMark = document.querySelector(".brand-mark");
  if (brandMark) {
    brandMark.innerHTML =
      '<svg viewBox="0 0 48 40" fill="none" aria-hidden="true"><path d="M24 2 46 13 24 24 2 13 24 2Z" fill="#147BF4"/><path d="M9 18v10c0 3 7 7 15 7s15-4 15-7V18L24 24 9 18Z" fill="#0758C7"/><path d="M7 15v12" stroke="#9BC8FF" stroke-width="2" stroke-linecap="round"/><circle cx="7" cy="29" r="2" fill="#9BC8FF"/></svg>';
    brandMark.innerHTML =
      '<img src="assets/images/icono-birrete-diploma.png" alt="Emblema del sistema">';
    const toggle = document.createElement("button");
    toggle.className = "sidebar-toggle";
    toggle.type = "button";
    toggle.setAttribute("aria-label", "Contraer o expandir menú");
    toggle.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/></svg>';
    toggle.addEventListener("click", () => {
      if (window.matchMedia("(max-width: 760px)").matches) {
        document.body.classList.toggle("mobile-menu-open");
        toggle.setAttribute(
          "aria-expanded",
          String(document.body.classList.contains("mobile-menu-open")),
        );
      } else {
        document.body.classList.toggle("sidebar-collapsed");
      }
    });
    const topbar = document.querySelector(".topbar");
    const brand = document.querySelector(".brand");
    const placeSidebarToggle = () => {
      if (window.matchMedia("(max-width: 760px)").matches) {
        topbar?.prepend(toggle);
      } else {
        topbar?.prepend(toggle);
      }
    };
    placeSidebarToggle();
    window.addEventListener("resize", placeSidebarToggle);
    document.querySelectorAll(".menu a").forEach((link) => {
      link.addEventListener("click", () => {
        if (window.matchMedia("(max-width: 760px)").matches) {
          document.body.classList.remove("mobile-menu-open");
          toggle.setAttribute("aria-expanded", "false");
        }
      });
    });
    document.addEventListener("click", (event) => {
      if (
        !window.matchMedia("(max-width: 760px)").matches ||
        !document.body.classList.contains("mobile-menu-open")
      )
        return;
      const sidebar = document.querySelector(".sidebar");
      if (
        sidebar &&
        !sidebar.contains(event.target) &&
        event.target !== toggle &&
        !toggle.contains(event.target)
      ) {
        document.body.classList.remove("mobile-menu-open");
        toggle.setAttribute("aria-expanded", "false");
      }
    });
    document.addEventListener("keydown", (event) => {
      if (
        event.key === "Escape" &&
        document.body.classList.contains("mobile-menu-open")
      ) {
        document.body.classList.remove("mobile-menu-open");
        toggle.setAttribute("aria-expanded", "false");
      }
    });
  }
  const notification = document.querySelector(".notification");
  if (notification) {
    const unreadNotifications = Number(dashboardData.notifications || 0);
    notification.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg><i></i>';
    const notificationBadge = notification.querySelector("i");
    notificationBadge.textContent =
      unreadNotifications > 99 ? "99+" : String(unreadNotifications);
    notificationBadge.hidden = unreadNotifications === 0;
    const topDate = document.createElement("div");
    topDate.className = "top-date";
    const date = new Intl.DateTimeFormat("es-PE", {
      weekday: "long",
      day: "numeric",
      month: "long",
      year: "numeric",
    }).format(new Date());
    topDate.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 9h18M8 13h.01M12 13h.01M16 13h.01" stroke-linecap="round"/></svg><span><b>' +
      date.charAt(0).toUpperCase() +
      date.slice(1) +
      "</b><small>Colegio de Abogados del Santa</small></span>";
    notification.before(topDate);
    notification.setAttribute("role", "button");
    notification.setAttribute("tabindex", "0");
    const notificationBox = document.createElement("div");
    notificationBox.className = "notification-dropdown";
    notificationBox.hidden = true;
    notificationBox.innerHTML = notificationContent();
    notification.parentElement?.append(notificationBox);
    const toggleNotifications = (event) => {
      event.stopPropagation();
      notificationBox.hidden = !notificationBox.hidden;
    };
    notification.addEventListener("click", toggleNotifications);
    notification.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ")
        toggleNotifications(event);
    });
    document.addEventListener("click", () => {
      notificationBox.hidden = true;
    });
  }
  const topUser = document.querySelector(".top-user");
  document
    .querySelectorAll(".sidebar-profile-info b, .top-user > span > b")
    .forEach((name) => {
      const parts = name.textContent.trim().split(/\s+/);
      if (parts.length > 2) name.textContent = `${parts[0]} ${parts[2]}`;
    });
  if (topUser) {
    const profileParts = Array.from(topUser.children).filter(
      (item) =>
        !item.classList.contains("notification") &&
        !item.classList.contains("top-date"),
    );
    const profile = document.createElement("div");
    profile.className = "profile-menu";
    const trigger = document.createElement("button");
    trigger.className = "profile-menu-trigger";
    trigger.type = "button";
    trigger.setAttribute("aria-expanded", "false");
    profileParts.slice(0, 2).forEach((part) => trigger.append(part));
    const dropdown = document.createElement("div");
    dropdown.className = "profile-dropdown";
    dropdown.hidden = true;
    const logoutDropdownIcon =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v9"/><path d="M18.4 6.6a9 9 1 1 1-12.8 0"/></svg>';
    dropdown.innerHTML =
      '<a href="auth/logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H6v18h8"/><path d="M11 12h10"/><path d="m17 8 4 4-4 4"/></svg>Cerrar sesión</a>';
    dropdown.insertAdjacentHTML(
      "beforeend",
      '<button type="button" class="change-password-trigger"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>Cambiar contraseña</button>',
    );
    dropdown
      .querySelector(".change-password-trigger")
      ?.addEventListener("click", (event) => {
        event.stopPropagation();
        openChangePasswordModal();
      });
    profile.append(trigger, dropdown);
    const dropdownIcon = document
      .createRange()
      .createContextualFragment(logoutDropdownIcon).firstChild;
    dropdown.querySelector("svg")?.replaceWith(dropdownIcon);
    dropdown.querySelector("a")?.addEventListener("click", (event) => {
      event.preventDefault();
      document.querySelector(".sidebar-profile")?.click();
    });
    topUser.append(profile);
    profileParts.slice(2).forEach((part) => part.remove());
    trigger.addEventListener("click", (event) => {
      event.stopPropagation();
      dropdown.hidden = !dropdown.hidden;
      trigger.setAttribute("aria-expanded", String(!dropdown.hidden));
    });
    document.addEventListener("click", () => {
      dropdown.hidden = true;
      trigger.setAttribute("aria-expanded", "false");
    });
  }
  document.querySelectorAll(".sidebar-profile").forEach((profileCard) => {
    const logoutLink = profileCard.querySelector(".sidebar-profile-logout");
    if (logoutLink && !logoutLink.querySelector("svg")) {
      logoutLink.innerHTML =
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M14 4h5v16h-5"/></svg>';
    }
    profileCard.setAttribute("role", "button");
    profileCard.setAttribute("tabindex", "0");
    profileCard.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") profileCard.click();
    });
    profileCard.addEventListener("click", (event) => {
      event.preventDefault();
      document.querySelector(".logout-modal")?.remove();
      const modal = document.createElement("div");
      modal.className = "logout-modal";
      modal.innerHTML =
        '<div class="logout-dialog" role="dialog" aria-modal="true" aria-labelledby="logout-title"><div class="logout-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v10M7.5 6.5a8 8 0 1 0 9 0"/></svg></div><h2 id="logout-title">Cerrar sesión</h2><p>¿Deseas cerrar tu sesión?</p><div class="logout-actions"><button type="button" class="logout-cancel">Cancelar</button><a href="auth/logout.php" class="logout-confirm">Cerrar sesión</a></div></div>';
      modal.querySelector(".logout-cancel").innerHTML =
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m7 7 10 10M17 7 7 17"/></svg>Cancelar';
      modal.querySelector(".logout-confirm").innerHTML =
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>Cerrar sesión';
      document.body.append(modal);
      const close = () => modal.remove();
      modal.querySelector(".logout-cancel").addEventListener("click", close);
      modal.addEventListener("click", (event) => {
        if (event.target === modal) close();
      });
    });
  });
  const dateCard = document.querySelector(".date-card");
  document.querySelector(".welcome .date-card")?.remove();
  if (dateCard) {
    const date = new Intl.DateTimeFormat("es-PE", {
      weekday: "long",
      day: "numeric",
      month: "long",
      year: "numeric",
    }).format(new Date());
    dateCard.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 9h18M8 13h.01M12 13h.01M16 13h.01M8 17h.01M12 17h.01" stroke-linecap="round"/></svg><span><b>' +
      date.charAt(0).toUpperCase() +
      date.slice(1) +
      "</b><small>Colegio de Abogados del Santa</small></span>";
  }
  const icons = [
    '<svg viewBox="0 0 24 24" fill="currentColor"><path d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9Z"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h5M8 12h8M8 16h6"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h5M8 12h8M8 16h8"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 16l9 5 9-5"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>',
    '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M2 20c.6-4 3-6 7-6s6.4 2 7 6H2ZM15 14c3.4 0 5.5 1.8 6 5h-4.2c-.3-2-1-3.5-1.8-5Z"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/><circle cx="12" cy="12" r="4"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M3 20c.6-4 2.6-6 6-6s5.4 2 6 6M16 11a3 3 0 1 0 0-6M16 14c2.8 0 4.5 2 5 6"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19h16M6 19V8h12v11M9 8V5h6v3M9 12h6M9 15h6"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 4 7v5c0 5 3.5 8 8 9 4.5-1 8-4 8-9V7l-8-4Z"/><path d="m9 12 2 2 4-4"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3h8v3H8zM6 5H4v16h16V5h-2"/><path d="M8 12l2 2 5-5M8 18h8"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
  ];
  document
    .querySelectorAll(".menu .menu-icon")
    .forEach((icon) => icon.remove());
  document.querySelectorAll(".menu a").forEach((link, index) => {
    const label = link.querySelector("span");
    if (!label) return;
    Array.from(link.childNodes)
      .filter((node) => node.nodeType === Node.TEXT_NODE)
      .forEach((node) => node.remove());
    const menuIcon =
      icons[index] ||
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6M12 7v5l3 2"/></svg>';
    const icon = document.createElement("span");
    icon.className = "menu-icon";
    icon.innerHTML = menuIcon;
    link.insertBefore(icon, label);
    const routes = {
      Certificados: "certificados",
      "Registrar Certificado": "registrar_certificado",
      Dashboard: "dashboard",
      Plantillas: "plantillas",
      Participantes: "participantes",
      Cursos: "cursos",
      Firmantes: "firmantes",
      Usuarios: "usuarios",
      "Roles y permisos": "roles",
      Reportes: "reportes",
      Auditoría: "auditoria",
      Configuración: "configuracion",
    };
    if (routes[label.textContent.trim()])
      link.href = routes[label.textContent.trim()];
  });

  const trendData = dashboardData.trends || {};
  const trendKeys = ["certificates", "pending", "printed", "templates"];
  const statIcons = [
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h5M8 12h8M8 16h6"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5h20v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="m12 3 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5"/></svg>',
  ];
  const panelIcons = {
    quick:
      '<svg viewBox="0 0 24 24" fill="currentColor"><path d="m13 2-9 12h6l-1 8 9-12h-6l1-8Z"/></svg>',
    chart:
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>',
    status:
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m9 12 2 2 4-5"/></svg>',
    recent:
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h5M8 12h8M8 16h6"/></svg>',
  };
  const headings = [
    [".quick h2", panelIcons.quick],
    [".chart-panel h2", panelIcons.chart],
    [".status-panel h2", panelIcons.status],
    [".recent h2", panelIcons.recent],
  ];
  headings.forEach(([selector, icon]) => {
    const heading = document.querySelector(selector);
    if (!heading) return;
    if (heading.firstChild?.nodeType === Node.TEXT_NODE)
      heading.firstChild.textContent = heading.firstChild.textContent.replace(
        /^[^\p{L}\p{N}]+/u,
        "",
      );
    heading.insertAdjacentHTML("afterbegin", icon);
  });
  const quickIcons = [
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h5M8 12h8M8 16h6"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h5M8 12h8"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5h20v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 16l9 5 9-5"/></svg>',
  ];
  document.querySelectorAll(".quick-actions a").forEach((action, index) => {
    if (action.firstChild?.nodeType === Node.TEXT_NODE)
      action.firstChild.textContent = action.firstChild.textContent.replace(
        /^[^\p{L}\p{N}]+/u,
        "",
      );
    if (quickIcons[index])
      action.insertAdjacentHTML("afterbegin", quickIcons[index]);
  });
  const recentPanel = document.querySelector(".recent");
  if (recentPanel) {
    recentPanel.querySelector(".panel-heading a")?.remove();
    const emptyCell = recentPanel.querySelector(".empty");
    if (emptyCell) {
      emptyCell.innerHTML =
        '<span class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h5M8 12h8M8 16h6"/></svg></span><span>Aún no hay certificados registrados.</span>';
      emptyCell.classList.add("empty-state");
    }
    const footer = document.createElement("div");
    footer.className = "table-footer";
    const empty = recentPanel.querySelector(".empty-state");
    const records = empty ? 0 : recentPanel.querySelectorAll("tbody tr").length;
    const pages = Math.max(1, Math.ceil(records / 10));
    let buttons = "<button" + (pages === 1 ? " disabled" : "") + ">‹</button>";
    for (let page = 1; page <= pages; page += 1) {
      buttons += `<button class="${page === 1 ? "current" : ""}">${page}</button>`;
    }
    buttons += "<button" + (pages === 1 ? " disabled" : "") + ">›</button>";
    footer.innerHTML =
      "<span>Mostrando " +
      (records ? `1 a ${Math.min(records, 10)} de ${records}` : "0") +
      " registros</span><div>" +
      buttons +
      "</div>";
    const paginationButtons = footer.querySelectorAll("div button");
    if (paginationButtons.length >= 2) {
      paginationButtons[0].classList.add("pagination-prev");
      paginationButtons[0].innerHTML =
        '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg><span>Anterior</span>';
      paginationButtons[paginationButtons.length - 1].classList.add(
        "pagination-next",
      );
      paginationButtons[paginationButtons.length - 1].innerHTML =
        '<span>Siguiente</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>';
    }
    recentPanel.append(footer);
  }
  document.querySelectorAll(".stat-icon").forEach((icon, index) => {
    if (statIcons[index]) icon.innerHTML = statIcons[index];
  });
  document.querySelectorAll(".stat").forEach((card, index) => {
    const trend = trendData[trendKeys[index]];
    if (!trend) return;
    const current = Number(trend[0] || 0);
    const previous = Number(trend[1] || 0);
    const percent =
      previous === 0
        ? current > 0
          ? 100
          : 0
        : ((current - previous) / previous) * 100;
    const color = percent >= 0 ? "#20e58a" : "#f87171";
    const arrow = percent >= 0 ? "↑" : "↓";
    const content = card.querySelector("div");
    const meta = document.createElement("div");
    meta.className = `stat-trend ${["blue", "amber", "green", "purple"][index]}`;
    meta.innerHTML = "<span>vs. mes anterior</span><small>↑ 0%</small>";
    meta.innerHTML = `<span>vs. mes anterior</span><small style="color:${color}">${arrow} ${Math.abs(percent).toFixed(1)}%</small>`;
    content.append(meta);
    const sparkline = document.createElement("div");
    sparkline.className = `sparkline ${["blue", "amber", "green", "purple"][index]}`;
    sparkline.innerHTML =
      '<svg viewBox="0 0 180 42" preserveAspectRatio="none" aria-hidden="true"><polyline points="0,34 22,27 42,31 65,18 84,23 108,12 128,16 151,7 180,9"/></svg>';
    card.append(sparkline);
  });
})();
