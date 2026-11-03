<?php

declare(strict_types=1);
final class AuditController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'auditoria.ver');
        $entries = $db->query('SELECT a.*,CONCAT(u.nombres," ",u.apellidos) usuario FROM auditoria a LEFT JOIN usuarios u ON u.id=a.usuario_id ORDER BY a.created_at DESC LIMIT 100')->fetchAll();
        ob_start();
        require dirname(__DIR__) . '/Views/audit/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'AuditorÃ­a | Sistema de Certificados';
        $pageTitle = 'Auditoría | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
