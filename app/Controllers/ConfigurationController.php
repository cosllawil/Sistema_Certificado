<?php

declare(strict_types=1);
final class ConfigurationController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'configuracion.administrar');
        if (empty($_SESSION['configuration_csrf'])) $_SESSION['configuration_csrf'] = bin2hex(random_bytes(32));
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clave'])) {
            if (!hash_equals((string)$_SESSION['configuration_csrf'], (string)($_POST['_token'] ?? ''))) {
                http_response_code(419);
                exit('Token de seguridad inválido.');
            }
            $q = $db->prepare('UPDATE configuraciones SET valor=:valor, updated_by=:updated_by WHERE clave=:clave');
            $q->execute(['valor' => trim((string)($_POST['valor'] ?? '')), 'updated_by' => (int)$_SESSION['auth_user']['id'], 'clave' => (string)$_POST['clave']]);
            header('Location: configuracion');
            exit;
        }
        $settings = $db->query('SELECT clave,valor,tipo,updated_at FROM configuraciones ORDER BY clave')->fetchAll();
        foreach ($settings as &$setting) $setting['updated_at'] = !empty($setting['updated_at']) ? date('d/m/Y H:i', strtotime((string)$setting['updated_at'])) : '';
        unset($setting);
        ob_start();
        require dirname(__DIR__) . '/Views/configuration/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'ConfiguraciÃ³n | Sistema de Certificados';
        $pageTitle = 'Configuración | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
