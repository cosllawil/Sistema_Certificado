<?php

declare(strict_types=1);
final class DashboardPageController
{
    public function show(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        require_once dirname(__DIR__) . '/Repositories/DashboardRepository.php';
        require_once dirname(__DIR__) . '/Services/DashboardService.php';
        require_once dirname(__DIR__) . '/Models/Permission.php';
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'dashboard.ver');
        $summary = (new DashboardService(new DashboardRepository($db)))->summary((int) $_SESSION['auth_user']['id']);
        $user = htmlspecialchars((string) $_SESSION['auth_user']['name'], ENT_QUOTES, 'UTF-8');
        $nameParts = preg_split('/\\s+/', trim($user));
        $displayName = htmlspecialchars(($nameParts[0] ?? '') . ' ' . ($nameParts[count($nameParts) - 1] ?? ''), ENT_QUOTES, 'UTF-8');
        $roleName = htmlspecialchars((string) ($_SESSION['auth_user']['role'] ?? 'Sin rol'), ENT_QUOTES, 'UTF-8');
        $permissions = (new Permission($db))->forUser((int) $_SESSION['auth_user']['id']);
        ob_start();
        require dirname(__DIR__) . '/Views/dashboard/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Dashboard | Sistema de Certificados';
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
