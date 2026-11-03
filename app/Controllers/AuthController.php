<?php

declare(strict_types=1);

final class AuthController
{
    public function login(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $identity = trim((string) ($payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        try {
            require_once dirname(__DIR__) . '/Services/AuthService.php';
            $user = (new AuthService(databaseConnection()))->authenticate($identity, $password);
            session_regenerate_id(true);
            $_SESSION['auth_user'] = ['id' => $user['id'], 'username' => $user['username'], 'name' => trim($user['nombres'] . ' ' . $user['apellidos']), 'role' => $user['rol_nombre']];
            echo json_encode(['ok' => true, 'message' => 'Inicio de sesión correcto.']);
        } catch (Throwable $exception) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => $exception->getMessage()]);
        }
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
        header('Location: ../login');
        exit;
    }
}
