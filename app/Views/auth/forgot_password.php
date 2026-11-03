<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recuperación de contraseña">
    <title>Recuperar contraseña | Sistema de Certificados</title>
    <link rel="icon" type="image/png" href="assets/images/icono-birrete-diploma.png">
    <link rel="stylesheet" href="assets/css/login.css?v=5">
    <link rel="stylesheet" href="assets/css/compact.css?v=15">
</head>

<body>
    <canvas id="space-background" aria-hidden="true"></canvas>
    <main class="auth-shell">
        <section class="brand-panel" aria-label="Características del sistema">
            <img class="college-logo" src="assets/images/logo-colegio-abogados.png" alt="Colegio de Abogados del Santa">
            <p class="brand-copy">Gestiona, emite y verifica certificados de forma rápida, segura y digital.</p>
            <div class="rule"></div>
            <div class="benefits">
                <article class="benefit"><span class="benefit-icon">&#128196;</span><span><strong>Registro de participantes</strong><span>Organiza la información de manera sencilla.</span></span></article>
                <article class="benefit"><span class="benefit-icon">&#128220;</span><span><strong>Emisión de certificados</strong><span>Genera certificados en formato PDF.</span></span></article>
                <article class="benefit"><span class="benefit-icon">&#128737;</span><span><strong>Verificación en línea</strong><span>Valida la autenticidad de los certificados.</span></span></article>
                <article class="benefit"><span class="benefit-icon">&#128202;</span><span><strong>Reportes y estadísticas</strong><span>Consulta el historial de emisiones.</span></span></article>
            </div>
        </section>
        <section class="form-panel">
            <div class="login-card">
                <header class="login-header">
                    <img class="graduation-icon" src="assets/images/icono-birrete-diploma.png" alt="Sistema de Certificados">
                    <h2>Recuperar contraseña</h2>
                    <p>Ingresa tu correo electrónico registrado</p>
                </header>
                <?php if (isset($_GET['error'])): ?><div class="error"><?= htmlspecialchars((string) $_GET['error'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <form id="forgot-password-form" method="post" action="forgot_password" novalidate>
                    <div class="field"><label for="email">Correo electrónico</label>
                        <div class="input-wrap"><span class="input-icon" aria-hidden="true">&#9993;</span><input id="email" name="email" type="email" autocomplete="email" placeholder="Ingresa tu correo electrónico" required></div>
                    </div>
                    <button class="primary-button" type="submit">Enviar enlace <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M13 3h7v18h-7" />
                                <path d="M3 12h12" />
                                <path d="m11 8 4 4-4 4" />
                            </svg></span></button>
                </form>
                <p class="secure-note"><a href="login"><svg class="back-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="5" y="10" width="14" height="10" rx="2" />
                            <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                        </svg>Volver al inicio de sesión</a></p>
            </div>
        </section>
    </main>
    <script src="assets/js/security.js" defer></script>
    <script src="assets/js/forgot-password.js?v=7" defer></script>
    <script src="assets/js/login.js?v=4" defer></script>
</body>

</html>