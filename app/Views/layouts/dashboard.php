<?php

declare(strict_types=1);
$pageTitle = $pageTitle ?? 'CertiForma';
$pageContent = $pageContent ?? '';
$displayName = $displayName ?? 'Wilmer Coscol';
$roleName = $roleName ?? 'Superadministrador';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['change_password_csrf'])) $_SESSION['change_password_csrf'] = bin2hex(random_bytes(32));
$branding = [];
try { foreach (databaseConnection()->query("SELECT clave,valor FROM configuraciones WHERE clave LIKE 'branding.%'")->fetchAll() as $row) $branding[$row['clave']] = (string)$row['valor']; } catch (Throwable $e) { }
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/register-certificate.css?v=7">
    <link rel="stylesheet" href="assets/css/dashboard-menu.css?v=logo-62">
    <script src="assets/js/dashboard.js?v=32" defer></script>
    <script src="assets/js/security.js?v=2" defer></script>
    <link rel="stylesheet" href="assets/css/participants.css?v=176">
    <link rel="stylesheet" href="assets/css/configuration.css?v=17">
    <link rel="stylesheet" href="assets/css/responsive-cleanup.css?v=19">
    <link rel="stylesheet" href="assets/css/users.css?v=1">
    <script src="assets/js/participants.js?v=229" defer></script>
    <script src="assets/js/select2-lite.js?v=10" defer></script>
    <script src="assets/js/users.js?v=7" defer></script>
    <script src="assets/js/configuration.js?v=21" defer></script>
    <script src="assets/js/roles.js?v=2" defer></script>
    <script src="assets/js/audit.js?v=1" defer></script>
    <link rel="stylesheet" href="assets/css/certificate-fonts.css?v=5">
</head>

<body data-change-password-csrf="<?= htmlspecialchars((string)$_SESSION['change_password_csrf'], ENT_QUOTES, 'UTF-8') ?>" data-active-configuration-tab="<?= htmlspecialchars((string)($activeConfigurationTab ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-branding-institution="<?= htmlspecialchars((string)($branding['branding.institucion'] ?? 'Colegio de Abogados del Santa'), ENT_QUOTES, 'UTF-8') ?>">
    <div class="dashboard-shell"><?php require __DIR__ . '/../components/sidebar.php'; ?><main class="content"><?php require __DIR__ . '/../components/header.php'; ?><?= $pageContent ?><?php require __DIR__ . '/../components/footer.php'; ?></main>
    </div>
</body>

</html>
