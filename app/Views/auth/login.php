<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acceso a CertiForma">
    <link rel="icon" type="image/png" href="assets/images/icono-birrete-diploma.png">
    <title>CertiForma | Iniciar sesión</title>
    <link rel="stylesheet" href="assets/css/login.css?v=7">
    <link rel="stylesheet" href="assets/css/compact.css?v=14">
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
                    <img class="graduation-icon" src="assets/images/icono-birrete-diploma.png" alt="CertiForma">
                    <h2>CertiForma</h2>
                    <p>Sistema de Gestión de Cursos y Certificados</p>
                </header>
                <form id="login-form" novalidate>
                    <div class="field" id="user-field"><label for="username">Usuario</label>
                        <div class="input-wrap"><span class="input-icon" aria-hidden="true">&#128100;</span><input id="username" name="username" type="text" autocomplete="off" readonly placeholder="Ingresa tu usuario"></div>
                    </div>
                    <div class="field" id="password-field"><label for="password">Contraseña</label>
                        <div class="input-wrap"><span class="input-icon" aria-hidden="true">&#128274;</span><input id="password" name="password" type="password" autocomplete="off" readonly placeholder="Ingresa tu contraseña"><button class="toggle-password" type="button" aria-label="Mostrar contraseña">&#128065;</button></div>
                    </div>
                    <div class="form-options"><label class="remember"><input id="remember" type="checkbox"><span class="checkmark"></span>Recordarme</label><a class="forgot-link" href="forgot_password">¿Olvidaste tu contraseña?</a></div>
                    <button class="primary-button" type="submit">Ingresar <span>&rarr;</span></button>
                </form>
            </div>
        </section>
    </main>
    <script src="assets/js/security.js" defer></script>
    <script src="assets/js/login.js?v=10" defer></script>
</body>

</html>
