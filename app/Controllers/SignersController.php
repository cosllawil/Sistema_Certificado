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
        // Compatibilidad con instalaciones existentes: agrega la relación con cursos una sola vez.
        $courseColumn = $db->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='firmantes' AND COLUMN_NAME='curso_id'");
        $courseColumn->execute();
        if (!(int)$courseColumn->fetchColumn()) {
            $db->exec('ALTER TABLE firmantes ADD COLUMN curso_id BIGINT UNSIGNED NULL AFTER cargo');
        }
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
            $cursoId = (int)($_POST['curso_id'] ?? 0);
            $signerId = (int)($_POST['signer_id'] ?? 0);
            $firma = null;
            $documentValid = $tipoDocumento === 'DNI' ? preg_match('/^\d{8}$/', $numeroDocumento) : ($tipoDocumento === 'CE' ? preg_match('/^\d{1,12}$/', $numeroDocumento) : preg_match('/^[a-z0-9]{1,12}$/i', $numeroDocumento));
            $courseSignerCount = 0;
            if ($cursoId > 0) {
                $countQuery = $db->prepare('SELECT COUNT(*) FROM firmantes WHERE curso_id=:curso_id AND deleted_at IS NULL AND id<>:id');
                $countQuery->execute(['curso_id' => $cursoId, 'id' => $signerId]);
                $courseSignerCount = (int)$countQuery->fetchColumn();
            }
            if ((!$signerId && ($tipoDocumento === '' || $numeroDocumento === '' || !$documentValid)) || $nombres === '' || $apellidos === '' || $cargo === '' || $cursoId <= 0) $_SESSION['signer_error'] = 'Completa correctamente los datos del documento, nombres, apellidos, cargo y curso.';
            elseif ($courseSignerCount >= 3) $_SESSION['signer_error'] = 'No se puede agregar más de 3 firmas para este curso.';
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
                try {
                    $id = $signerId;
                    if ($id > 0) {
                        $q = $db->prepare('UPDATE firmantes SET tipo_documento=:tipo_documento,numero_documento=:numero_documento,nombres=:nombres,apellidos=:apellidos,cargo=:cargo,curso_id=:curso_id,firma_path=COALESCE(:firma_path,firma_path) WHERE id=:id');
                        $q->execute(['tipo_documento' => $tipoDocumento, 'numero_documento' => $numeroDocumento, 'nombres' => $nombres, 'apellidos' => $apellidos, 'cargo' => $cargo, 'curso_id' => $cursoId, 'firma_path' => $firma, 'id' => $id]);
                    } else {
                        $q = $db->prepare('INSERT INTO firmantes (tipo_documento,numero_documento,nombres,apellidos,cargo,curso_id,firma_path) VALUES (:tipo_documento,:numero_documento,:nombres,:apellidos,:cargo,:curso_id,:firma_path)');
                        $q->execute(['tipo_documento' => $tipoDocumento, 'numero_documento' => $numeroDocumento, 'nombres' => $nombres, 'apellidos' => $apellidos, 'cargo' => $cargo, 'curso_id' => $cursoId, 'firma_path' => $firma]);
                    }
                } catch (PDOException $exception) {
                    if ((string)$exception->errorInfo[0] === '23000' && (int)($exception->errorInfo[1] ?? 0) === 1062) {
                        $_SESSION['signer_error'] = 'Ya existe un firmante registrado con ese tipo y número de documento.';
                    } else {
                        throw $exception;
                    }
                }
                if (!isset($_SESSION['signer_error'])) $_SESSION['signer_success'] = $signerId > 0 ? 'Los datos del firmante se actualizaron correctamente.' : 'El firmante se registró correctamente.';
            }
            header('Location: firmantes');
            exit;
        }
        $signers = $db->query("SELECT f.id,f.tipo_documento,f.numero_documento,f.nombres,f.apellidos,f.cargo,f.curso_id,f.estado,f.firma_path,COALESCE(cu.nombre,'Sin curso asignado') curso FROM firmantes f LEFT JOIN cursos cu ON cu.id=f.curso_id WHERE f.deleted_at IS NULL ORDER BY f.apellidos,f.nombres")->fetchAll();
        $courses = $db->query('SELECT id,nombre FROM cursos WHERE deleted_at IS NULL ORDER BY nombre')->fetchAll();
        $signerError = $_SESSION['signer_error'] ?? null;
        $signerSuccess = $_SESSION['signer_success'] ?? null;
        unset($_SESSION['signer_error'], $_SESSION['signer_success']);
        ob_start();
        require dirname(__DIR__) . '/Views/signers/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Firmantes | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
