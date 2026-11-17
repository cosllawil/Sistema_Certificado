<?php

declare(strict_types=1);
final class AuthPageController
{
    public function login(): void
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        require_once dirname(__DIR__) . '/bootstrap.php';
        $branding = [];
        try { foreach (databaseConnection()->query("SELECT clave,valor FROM configuraciones WHERE clave LIKE 'branding.%'")->fetchAll() as $row) $branding[$row['clave']] = (string)$row['valor']; } catch (Throwable $e) { }
        require dirname(__DIR__) . '/Views/auth/login.php';
    }
}
