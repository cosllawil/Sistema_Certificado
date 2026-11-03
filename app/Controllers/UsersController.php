<?php

declare(strict_types=1);
final class UsersController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'usuarios.administrar');
        if (empty($_SESSION['user_csrf'])) $_SESSION['user_csrf'] = bin2hex(random_bytes(32));
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals((string)$_SESSION['user_csrf'], (string)($_POST['_token'] ?? ''))) { http_response_code(419); exit('Token de seguridad inválido.'); }
            $userModel = new User($db); $id = (int)($_POST['user_id'] ?? 0); $action = (string)($_POST['action'] ?? ''); $roleId = (int)($_POST['rol_id'] ?? 0);
            if (in_array($action, ['activar', 'desactivar'], true)) { $userModel->setStatus($id, $action === 'activar' ? 'activo' : 'inactivo'); (new AuditService($db))->log((int)$_SESSION['auth_user']['id'], ucfirst($action).' usuario', 'usuarios', $id); header('Location: usuarios'); exit; }
            $data = ['nombres' => mb_convert_case(trim((string)($_POST['nombres'] ?? '')), MB_CASE_TITLE, 'UTF-8'), 'apellidos' => mb_convert_case(trim((string)($_POST['apellidos'] ?? '')), MB_CASE_TITLE, 'UTF-8'), 'email' => strtoupper(trim((string)($_POST['email'] ?? ''))), 'username' => trim((string)($_POST['username'] ?? '')), 'password_hash' => trim((string)($_POST['password'] ?? ''))];
            if ($data['nombres'] === '' || $data['apellidos'] === '' || $data['email'] === '' || $data['username'] === '' || $roleId <= 0 || ($id === 0 && $data['password_hash'] === '')) { $_SESSION['user_error'] = 'Completa los campos obligatorios.'; } else { $wasEdit = $id > 0; $data['password_hash'] = $data['password_hash'] ? password_hash($data['password_hash'], PASSWORD_DEFAULT) : null; if ($wasEdit) $userModel->update($id, $data); else $id = $userModel->create($data); $userModel->assignRole($id, $roleId); (new AuditService($db))->log((int)$_SESSION['auth_user']['id'], $wasEdit ? 'Editar usuario' : 'Crear usuario', 'usuarios', $id, null, ['username'=>$data['username'], 'rol_id'=>$roleId]); header('Location: usuarios'); exit; }
        }
        $userModel = new User($db); $users = $userModel->all(); $roles = $db->query('SELECT id,nombre FROM roles WHERE deleted_at IS NULL ORDER BY nombre')->fetchAll();
        foreach ($users as &$user) $user['rol_id'] = $userModel->roleId((int)$user['id']);
        unset($user);
        foreach ($users as &$user) $user['ultimo_acceso_at'] = !empty($user['ultimo_acceso_at']) ? date('d/m/Y H:i', strtotime((string)$user['ultimo_acceso_at'])) : 'Nunca';
        unset($user);
        $userError = $_SESSION['user_error'] ?? null; unset($_SESSION['user_error']);
        ob_start();
        require dirname(__DIR__) . '/Views/users/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Usuarios | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
