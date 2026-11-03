<?php

declare(strict_types=1);
final class RolesController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'roles.administrar');
        if (empty($_SESSION['role_csrf'])) $_SESSION['role_csrf'] = bin2hex(random_bytes(32));
        $model = new Role($db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals((string)$_SESSION['role_csrf'], (string)($_POST['_token'] ?? ''))) {
                http_response_code(419);
                exit('Token de seguridad inválido.');
            }
            $id = (int)($_POST['role_id'] ?? 0);
            $data = ['codigo' => trim((string)($_POST['codigo'] ?? '')), 'nombre' => trim((string)($_POST['nombre'] ?? '')), 'descripcion' => trim((string)($_POST['descripcion'] ?? '')) ?: null];
            if ($data['codigo'] === '' || $data['nombre'] === '') $_SESSION['role_error'] = 'Completa los campos obligatorios.';
            else {
                $wasEdit = $id > 0;
                if ($wasEdit) $model->update($id, $data);
                else $id = $model->create($data);
                $model->syncPermissions($id, (array)($_POST['permisos'] ?? []));
                (new AuditService($db))->log((int)$_SESSION['auth_user']['id'], $wasEdit ? 'Editar rol' : 'Crear rol', 'roles', $id, null, ['nombre' => $data['nombre'], 'permisos' => count((array)($_POST['permisos'] ?? []))]);
                header('Location: roles');
                exit;
            }
        }
        $roles = $model->all();
        foreach ($roles as &$role) $role['permisos_ids'] = $model->permissionIds((int)$role['id']);
        unset($role);
        $permissions = $model->permissions();
        ob_start();
        require dirname(__DIR__) . '/Views/roles/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Roles y permisos | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
