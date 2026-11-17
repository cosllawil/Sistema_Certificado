<?php

declare(strict_types=1);
final class ConfigurationController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'configuracion.administrar');
        if (empty($_SESSION['configuration_csrf'])) $_SESSION['configuration_csrf'] = bin2hex(random_bytes(32));
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['database_action'])) {
            if (!hash_equals((string)$_SESSION['configuration_csrf'], (string)($_POST['_token'] ?? ''))) { http_response_code(419); exit('Token de seguridad inválido.'); }
            try {
                $service = new DatabaseBackupService();
                if ($_POST['database_action'] === 'backup') {
                    $service->createBackup();
                    $_SESSION['configuration_notice'] = 'El backup fue creado correctamente.';
                }
                if ($_POST['database_action'] === 'restore' && isset($_POST['backup_name'])) {
                    $service->restoreBackup((string) $_POST['backup_name']);
                    $_SESSION['configuration_notice'] = 'La base de datos fue restaurada correctamente.';
                }
            } catch (Throwable $error) {
                $_SESSION['configuration_error'] = $error->getMessage();
            }
            $_SESSION['configuration_active_tab'] = 'database';
            header('Location: configuracion'); exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['branding_action'])) {
            if (!hash_equals((string)$_SESSION['configuration_csrf'], (string)($_POST['_token'] ?? ''))) { http_response_code(419); exit('Token de seguridad inválido.'); }
            $institutionQuery = $db->prepare('INSERT INTO configuraciones (clave,valor,tipo,updated_by) VALUES (:c,:v,"texto",:u) ON DUPLICATE KEY UPDATE valor=VALUES(valor),updated_by=VALUES(updated_by)');
            $institutionQuery->execute(['c'=>'branding.institucion','v'=>trim((string)($_POST['branding_institucion'] ?? 'Colegio de Abogados del Santa')),'u'=>(int)$_SESSION['auth_user']['id']]);
            $verificationQuery = $db->prepare('INSERT INTO configuraciones (clave,valor,tipo,updated_by) VALUES (:c,:v,"texto",:u) ON DUPLICATE KEY UPDATE valor=VALUES(valor),updated_by=VALUES(updated_by)');
            $verificationQuery->execute(['c'=>'branding.verificacion','v'=>trim((string)($_POST['branding_verificacion'] ?? 'Consulta la autenticidad y validez de un certificado emitido por el Colegio de Abogados del Santa.')),'u'=>(int)$_SESSION['auth_user']['id']]);
            $_SESSION['configuration_branding_notice'] = 'La identidad visual se actualizó correctamente.';
            $dir = dirname(__DIR__, 2) . '/assets/images';
            $files = ['branding_logo_principal'=>['logo_principal','logo-colegio-abogados.png'], 'branding_logo_secundario'=>['logo_secundario','logoblanco.png'], 'branding_icono'=>['icono','icono-birrete-diploma.png'], 'branding_logo_movil'=>['logo_movil','logoblanco-mobil.png']];
            foreach ($files as $key => [$field, $filename]) {
                if (empty($_FILES[$field]['tmp_name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK || (int)$_FILES[$field]['size'] > 2*1024*1024) continue;
                $info = @getimagesize($_FILES[$field]['tmp_name']);
                if (!$info || !in_array($info['mime'], ['image/png','image/jpeg','image/webp'], true)) continue;
                $ext = ['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp'][$info['mime']];
                $target = $dir . '/' . $filename;
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
                    $name = $filename;
                    $q=$db->prepare('INSERT INTO configuraciones (clave,valor,tipo,updated_by) VALUES (:c,:v,"texto",:u) ON DUPLICATE KEY UPDATE valor=VALUES(valor),updated_by=VALUES(updated_by)'); $q->execute(['c'=>$key,'v'=>'assets/images/'.$name,'u'=>(int)$_SESSION['auth_user']['id']]);
                }
            }
            $q=$db->prepare('INSERT INTO configuraciones (clave,valor,tipo,updated_by) VALUES (:c,:v,"texto",:u) ON DUPLICATE KEY UPDATE valor=VALUES(valor),updated_by=VALUES(updated_by)');
            foreach (['branding.nombre'=>trim((string)($_POST['branding_nombre']??'CertiForma')), 'branding.subtitulo'=>trim((string)($_POST['branding_subtitulo']??'Sistema de Gestión de Cursos y Certificados'))] as $c=>$v) $q->execute(['c'=>$c,'v'=>$v,'u'=>(int)$_SESSION['auth_user']['id']]);
            header('Location: configuracion'); exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clave'])) {
            if (!hash_equals((string)$_SESSION['configuration_csrf'], (string)($_POST['_token'] ?? ''))) {
                http_response_code(419);
                exit('Token de seguridad inválido.');
            }
            $q = $db->prepare('UPDATE configuraciones SET valor=:valor, updated_by=:updated_by WHERE clave=:clave');
            $q->execute(['valor' => trim((string)($_POST['valor'] ?? '')), 'updated_by' => (int)$_SESSION['auth_user']['id'], 'clave' => (string)$_POST['clave']]);
            header('Location: configuracion');
            exit;
        }
        $settings = $db->query('SELECT clave,valor,tipo,updated_at FROM configuraciones ORDER BY clave')->fetchAll();
        $branding = []; foreach ($settings as $setting) if (str_starts_with((string)$setting['clave'], 'branding.')) $branding[$setting['clave']] = (string)$setting['valor'];
        $databaseNotice = $_SESSION['configuration_notice'] ?? null; unset($_SESSION['configuration_notice']);
        $databaseError = $_SESSION['configuration_error'] ?? null; unset($_SESSION['configuration_error']);
        $brandingNotice = $_SESSION['configuration_branding_notice'] ?? null; unset($_SESSION['configuration_branding_notice']);
        $databaseBackups = (new DatabaseBackupService())->listBackups();
        $activeConfigurationTab = ($_SESSION['configuration_active_tab'] ?? '') === 'database' ? 'configuration-database' : 'configuration-general';
        unset($_SESSION['configuration_active_tab']);
        foreach ($settings as &$setting) $setting['updated_at'] = !empty($setting['updated_at']) ? date('d/m/Y H:i', strtotime((string)$setting['updated_at'])) : '';
        unset($setting);
        ob_start();
        require dirname(__DIR__) . '/Views/configuration/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Configuración | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
