<?php

declare(strict_types=1);
final class ChangePasswordController
{
    public function update(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals((string)($_SESSION['change_password_csrf'] ?? ''), (string)($_POST['_token'] ?? ''))) {
            http_response_code(419);
            exit('Solicitud inválida.');
        }
        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $db = databaseConnection();
        $q = $db->prepare('SELECT password_hash FROM usuarios WHERE id=:id AND deleted_at IS NULL');
        $q->execute(['id' => (int)$_SESSION['auth_user']['id']]);
        $hash = (string)$q->fetchColumn();
        $error = !$current || !password_verify($current, $hash) ? 'La contraseña actual no es correcta.' : (!$new || strlen($new) < 8 ? 'La nueva contraseña debe tener al menos 8 caracteres.' : ($new !== $confirm ? 'Las contraseñas nuevas no coinciden.' : ''));
        if ($error) {
            $_SESSION['change_password_error'] = $error;
        } else {
            $db->prepare('UPDATE usuarios SET password_hash=:hash, updated_at=NOW() WHERE id=:id')->execute(['hash' => password_hash($new, PASSWORD_DEFAULT), 'id' => (int)$_SESSION['auth_user']['id']]);
            (new AuditService($db))->log((int)$_SESSION['auth_user']['id'], 'Cambiar contraseña', 'usuarios', (int)$_SESSION['auth_user']['id']);
            $_SESSION['change_password_success'] = 'Contraseña actualizada correctamente.';
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'dashboard'));
        exit;
    }
}
