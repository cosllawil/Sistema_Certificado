(() => {
  "use strict";
  const canvas = document.querySelector("#space-background");
  const context = canvas?.getContext("2d");
  const particles = [];
  const pointer = { x: -9999, y: -9999 };
  window.addEventListener("mousemove", (event) => {
    pointer.x = event.clientX;
    pointer.y = event.clientY;
  });
  const resizeCanvas = () => {
    if (!canvas || !context) return;
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  };
  const createParticles = () => {
    particles.length = 0;
    const total = Math.min(
      90,
      Math.floor((window.innerWidth * window.innerHeight) / 18000),
    );
    for (let index = 0; index < total; index += 1) {
      particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        radius: Math.random() * 2 + 0.6,
        speed: Math.random() * 0.22 + 0.06,
        alpha: Math.random() * 0.5 + 0.35,
      });
    }
  };
  const animateParticles = () => {
    if (!canvas || !context) return;
    context.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach((particle) => {
      particle.y -= particle.speed;
      if (particle.y < -4) particle.y = canvas.height + 4;
      const distanceX = particle.x - pointer.x;
      const distanceY = particle.y - pointer.y;
      const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);
      if (distance < 150 && distance > 0) {
        const force = (150 - distance) / 150;
        particle.x -= (distanceX / distance) * force * 1.8;
        particle.y -= (distanceY / distance) * force * 1.8;
      }
      context.beginPath();
      context.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
      context.fillStyle = `rgba(255, 255, 255, ${Math.min(particle.alpha + 0.2, 1)})`;
      context.fill();
    });
    particles.forEach((particle, index) => {
      particles.slice(index + 1).forEach((other) => {
        const distanceX = particle.x - other.x;
        const distanceY = particle.y - other.y;
        const distance = Math.sqrt(
          distanceX * distanceX + distanceY * distanceY,
        );
        if (distance < 150) {
          context.beginPath();
          context.moveTo(particle.x, particle.y);
          context.lineTo(other.x, other.y);
          context.strokeStyle = `rgba(190, 225, 255, ${0.28 * (1 - distance / 150)})`;
          context.lineWidth = 0.7;
          context.stroke();
        }
      });
    });
    window.requestAnimationFrame(animateParticles);
  };
  if (canvas && context) {
    resizeCanvas();
    createParticles();
    animateParticles();
    window.addEventListener("resize", () => {
      resizeCanvas();
      createParticles();
    });
  }
  const password = document.querySelector("#password");
  const toggle = document.querySelector(".toggle-password");
  const form = document.querySelector("#login-form");
  const usernameInput = document.querySelector("#username");
  const passwordInput = document.querySelector("#password");
  const submitButton = document.querySelector(".primary-button");
  [usernameInput, passwordInput].forEach((input) => {
    input?.addEventListener("focus", () => input.removeAttribute("readonly"), {
      once: true,
    });
    input?.addEventListener("click", () => input.removeAttribute("readonly"), {
      once: true,
    });
  });
  usernameInput?.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
      event.preventDefault();
      passwordInput?.focus();
    }
  });
  passwordInput?.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
      event.preventDefault();
      submitButton?.focus();
    }
  });
  const focusUsername = () => {
    usernameInput?.focus({ preventScroll: true });
    usernameInput?.select();
  };
  window.addEventListener("load", focusUsername);
  setTimeout(focusUsername, 150);
  const submitIcon = document.querySelector(".primary-button span");
  const secureNote = document.querySelector(".secure-note");
  const headerEmblem = document.querySelector(".login-header .cap");
  if (headerEmblem) {
    const graduationIcon = document.createElement("img");
    graduationIcon.className = "graduation-icon";
    graduationIcon.src = "assets/images/icono-birrete-diploma.png";
    graduationIcon.alt = "Birrete de graduación";
    headerEmblem.replaceWith(graduationIcon);
  }
  const benefitIcons = document.querySelectorAll(".benefit-icon");
  const benefitSvg = [
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h5M8 12h8M8 16h6"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 3h14v14H5z"/><path d="M8 7h8M8 11h5M9 21l3-4 3 4"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3 20 6v5c0 5-3.4 8.4-8 10-4.6-1.6-8-5-8-10V6l8-3Z"/><path d="m8.5 12 2.2 2.2 4.8-5"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>',
  ];
  benefitIcons.forEach((icon, index) => {
    icon.innerHTML = benefitSvg[index];
  });
  const eyeHidden =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/><path d="m4 4 16 16" stroke-linecap="round"/></svg>';
  const eyeVisible =
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>';
  if (submitIcon && form) {
    submitIcon.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 3h7v18h-7"/><path d="M3 12h12"/><path d="m11 8 4 4-4 4"/></svg>';
  }
  if (secureNote && form) {
    secureNote.innerHTML =
      '<svg viewBox="0 0 24 24" fill="#1674ed" aria-hidden="true"><path d="M12 2 21 5v6c0 5.5-3.7 9.1-9 11-5.3-1.9-9-5.5-9-11V5l9-3Zm-1.1 13.5 5.4-5.4-1.4-1.4-4 4-1.9-1.9-1.4 1.4 3.3 3.3Z"/></svg><span>Acceso seguro al sistema</span>';
  }
  const userIcon = document.querySelector("#user-field .input-icon");
  const lockIcon = document.querySelector("#password-field .input-icon");
  if (userIcon) {
    userIcon.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c.7-3.4 3.2-5.2 7.5-5.2s6.8 1.8 7.5 5.2"/></svg>';
  }
  if (lockIcon) {
    lockIcon.innerHTML =
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>';
  }
  if (toggle) {
    toggle.innerHTML = eyeHidden;
  }
  form?.setAttribute("autocomplete", "off");
  document.querySelector("#username")?.setAttribute("autocomplete", "off");
  document.querySelector("#password")?.setAttribute("autocomplete", "off");
  toggle?.addEventListener("click", () => {
    const visible = password.type === "text";
    password.type = visible ? "password" : "text";
    toggle.innerHTML = visible ? eyeHidden : eyeVisible;
    toggle.setAttribute(
      "aria-label",
      visible ? "Mostrar contraseña" : "Ocultar contraseña",
    );
  });
  form?.addEventListener("submit", (event) => {
    event.preventDefault();
    const username = document.querySelector("#username");
    const showAlert = (title, text) => {
      document.querySelector(".form-alert")?.remove();
      const alert = document.createElement("div");
      alert.className = "form-alert";
      alert.setAttribute("role", "alert");
      alert.innerHTML = `<div class="alert-card"><div class="alert-main"><div class="alert-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m10.3 3.5-8 14A2 2 0 0 0 4 20.5h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01" stroke-linecap="round"/></svg></div><div class="alert-content"><strong>Validación</strong><span>${text}</span></div></div><div class="alert-actions"><button class="alert-confirm" type="button">✓&nbsp; Entendido</button></div></div>`;
      document.querySelector(".login-card")?.prepend(alert);
      alert
        .querySelectorAll("button")
        .forEach((button) =>
          button.addEventListener("click", () => alert.remove()),
        );
    };
    if (!username.value.trim()) {
      showAlert("Usuario requerido", "Ingresa tu usuario para continuar.");
      username.focus();
      return;
    }
    if (!password.value) {
      showAlert(
        "Contraseña requerida",
        "Ingresa tu contraseña para continuar.",
      );
      password.focus();
      return;
    }

    fetch("/Sistema_Certificado/auth/login", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        username: username.value.trim(),
        password: password.value,
      }),
    })
      .then((response) =>
        response.json().then((data) => ({ ok: response.ok, data })),
      )
      .then(({ ok, data }) => {
        if (!ok || !data.ok) {
          showAlert(
            "Acceso denegado",
            data.message || "No fue posible iniciar sesión.",
          );
          return;
        }
        window.location.href = "dashboard";
      })
      .catch(() =>
        showAlert("Error de conexión", "No se pudo contactar al servidor."),
      );
  });
})();
