<?php

declare(strict_types=1);

final class CertificateTypesController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'certificados.ver');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['codigo'])) {
                $code = strtoupper(trim((string) $_POST['codigo']));
                $name = strtoupper(trim((string) ($_POST['nombre'] ?? '')));
                $model = new CertificateType($db);
                $id = (int) ($_POST['type_id'] ?? 0);
                if ($id === 0) {
                    $next = (int)$db->query("SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)), 0) FROM tipos_certificado WHERE codigo LIKE 'TC-%'")->fetchColumn() + 1;
                    $code = 'TC-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
                }
                if ($code === '' || $name === '') $_SESSION['type_error'] = 'Ingresa el código y el nombre para continuar.';
                elseif ($model->codeExists($code, $id)) $_SESSION['type_error'] = 'El código del tipo de certificado ya existe.';
                elseif ($id > 0) $model->update($id, ['codigo' => $code, 'nombre' => $name, 'descripcion' => strtoupper(trim((string) ($_POST['descripcion'] ?? ''))) ?: null]);
                else $model->create(['codigo' => $code, 'nombre' => $name, 'descripcion' => strtoupper(trim((string) ($_POST['descripcion'] ?? ''))) ?: null]);
            } else (new CertificateType($db))->setStatus((int) ($_POST['type_id'] ?? 0), (int) ($_POST['activo'] ?? 0));
            header('Location: tipos_certificado');
            exit;
        }
        $types = (new CertificateType($db))->all();
        $nextTypeNumber = (int)$db->query("SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)), 0) FROM tipos_certificado WHERE codigo LIKE 'TC-%'")->fetchColumn() + 1;
        $nextTypeCode = 'TC-' . str_pad((string)$nextTypeNumber, 3, '0', STR_PAD_LEFT);
        $typeError = $_SESSION['type_error'] ?? null;
        unset($_SESSION['type_error']);
        ob_start();
        require dirname(__DIR__) . '/Views/certificate_types/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Tipos de certificado | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string) ($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
