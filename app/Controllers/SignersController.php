<?php

declare(strict_types=1);
final class SignersController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'firmantes.administrar');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signer_id'], $_POST['estado']) && !isset($_POST['nombres'])) {
            $q = $db->prepare('UPDATE firmantes SET estado=:estado WHERE id=:id');
            $q->execute(['estado' => (int)$_POST['estado'] === 1 ? 'activo' : 'inactivo', 'id' => (int)$_POST['signer_id']]);
            header('Location: firmantes');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombres'])) {
            $tipoDocumento = strtoupper(trim((string)($_POST['tipo_documento'] ?? '')));
            $numeroDocumento = trim((string)($_POST['numero_documento'] ?? ''));
            $nombres = strtoupper(trim((string)$_POST['nombres']));
            $apellidos = strtoupper(trim((string)$_POST['apellidos']));
            $cargo = strtoupper(trim((string)$_POST['cargo']));
            $signerId = (int)($_POST['signer_id'] ?? 0);
            $firma = null;
            $documentValid = $tipoDocumento === 'DNI' ? preg_match('/^\d{8}$/', $numeroDocumento) : ($tipoDocumento === 'CE' ? preg_match('/^\d{1,12}$/', $numeroDocumento) : preg_match('/^[a-z0-9]{1,12}$/i', $numeroDocumento));
            if ((!$signerId && ($tipoDocumento === '' || $numeroDocumento === '' || !$documentValid)) || $nombres === '' || $apellidos === '' || $cargo === '') $_SESSION['signer_error'] = 'Completa correctamente los datos del documento, nombres, apellidos y cargo.';
            elseif (!preg_match('/^[\p{L} ]+$/u', $nombres) || !preg_match('/^[\p{L} ]+$/u', $apellidos) || !preg_match('/^[\p{L} ]+$/u', $cargo)) $_SESSION['signer_error'] = 'Nombres, apellidos y cargo solo deben contener letras.';
            elseif (!$signerId && empty($_FILES['firma']['name'])) $_SESSION['signer_error'] = 'La firma es obligatoria para un nuevo firmante.';
            elseif (!empty($_FILES['firma']['name'])) {
                $ext = strtolower(pathinfo($_FILES['firma']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['png', 'jpg', 'jpeg', 'svg'], true) || $_FILES['firma']['error'] !== UPLOAD_ERR_OK) $_SESSION['signer_error'] = 'La firma debe ser una imagen válida.';
                else {
                    $dir = dirname(__DIR__, 2) . '/storage/signers';
                    if (!is_dir($dir)) mkdir($dir, 0775, true);
                    $file = 'signer_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    if (move_uploaded_file($_FILES['firma']['tmp_name'], $dir . '/' . $file)) $firma = 'storage/signers/' . $file;
                    else $_SESSION['signer_error'] = 'No se pudo guardar la firma.';
                }
            }
            if (!isset($_SESSION['signer_error'])) {
                $id = $signerId;
                if ($id > 0) {
                    $q = $db->prepare('UPDATE firmantes SET nombres=:nombres,apellidos=:apellidos,cargo=:cargo,firma_path=COALESCE(:firma_path,firma_path) WHERE id=:id');
                    $q->execute(['nombres' => $nombres, 'apellidos' => $apellidos, 'cargo' => $cargo, 'firma_path' => $firma, 'id' => $id]);
                } else {
                    $q = $db->prepare('INSERT INTO firmantes (nombres,apellidos,cargo,firma_path) VALUES (:nombres,:apellidos,:cargo,:firma_path)');
                    $q->execute(['nombres' => $nombres, 'apellidos' => $apellidos, 'cargo' => $cargo, 'firma_path' => $firma]);
                }
            }
            header('Location: firmantes');
            exit;
        }
        $signers = $db->query('SELECT id,nombres,apellidos,cargo,estado,firma_path FROM firmantes WHERE deleted_at IS NULL ORDER BY apellidos,nombres')->fetchAll();
        $signerError = $_SESSION['signer_error'] ?? null;
        unset($_SESSION['signer_error']);
        ob_start();
        require dirname(__DIR__) . '/Views/signers/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Firmantes | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
