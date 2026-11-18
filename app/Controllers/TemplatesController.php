<?php

declare(strict_types=1);
final class TemplatesController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'plantillas.ver');
        $this->ensureFontsTable($db);
        $fonts = $db->query('SELECT nombre FROM fuentes WHERE activo=1 ORDER BY orden, nombre')->fetchAll(PDO::FETCH_COLUMN);
        $fontStyles = $db->query('SELECT codigo, nombre FROM estilos_letra WHERE activo=1 ORDER BY orden, nombre')->fetchAll();
        $textTransforms = $db->query('SELECT codigo, nombre FROM transformaciones_texto WHERE activo=1 ORDER BY orden, nombre')->fetchAll();
        if (empty($_SESSION['template_csrf'])) $_SESSION['template_csrf'] = bin2hex(random_bytes(32));
        if (isset($_GET['pdf_template'])) {
            $q = $db->prepare('SELECT fondo_path FROM plantillas WHERE id=:id AND deleted_at IS NULL');
            $q->execute(['id' => (int)$_GET['pdf_template']]);
            $file = $q->fetchColumn();
            $full = $file ? dirname(__DIR__, 2) . '/' . $file : '';
            if (!$full || !is_file($full)) {
                http_response_code(404);
                exit;
            }
            header('Content-Type: application/pdf');
            header('Content-Length: ' . (string)filesize($full));
            header('Content-Disposition: inline; filename="Plantilla.pdf"');
            readfile($full);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['config_template_id'])) {
            $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
            if (!hash_equals((string)$_SESSION['template_csrf'], (string)($_POST['_token'] ?? ''))) {
                http_response_code(419);
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido.'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                exit('Token de seguridad inválido.');
            }
            $cfg = json_decode((string)($_POST['configuracion_json'] ?? ''), true);
            $id = (int) $_POST['config_template_id'];
            if (!is_array($cfg) || $id <= 0) {
                if ($isAjax) {
                    http_response_code(422);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'Configuración inválida.']);
                    exit;
                }
                http_response_code(422);
                exit('Configuración inválida.');
            }
            $requiredSignerSlots = [];
            foreach ([1, 2, 3] as $slot) {
                $nameKey = $slot === 1 ? 'firmante' : 'firmante_' . $slot;
                $signatureKey = $slot === 1 ? 'firma' : 'firma_' . $slot;
                if (!empty($cfg['campos'][$nameKey]['visible']) || !empty($cfg['campos'][$signatureKey]['visible'])) $requiredSignerSlots[] = $slot;
            }
            if ($requiredSignerSlots) {
                $availableSignatures = (int)$db->query("SELECT COUNT(*) FROM firmantes WHERE estado='activo' AND deleted_at IS NULL AND firma_path IS NOT NULL AND firma_path<>''")->fetchColumn();
                $requiredSlot = max($requiredSignerSlots);
                if ($availableSignatures < $requiredSlot) {
                    $message = 'No tienes registrada la firma necesaria para Firmante ' . $requiredSlot . '.';
                    if ($isAjax) {
                        http_response_code(422);
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    $_SESSION['template_error'] = $message;
                    header('Location: plantillas');
                    exit;
                }
            }
            $q = $db->prepare('UPDATE plantillas SET configuracion_json=:cfg WHERE id=:id AND deleted_at IS NULL');
            $q->execute(['cfg' => json_encode($cfg, JSON_UNESCAPED_UNICODE), 'id' => $id]);
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'message' => 'Configuración guardada. Puedes continuar editando.'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            header('Location: plantillas');
            exit;
        }
        if (isset($_GET['config']) && ctype_digit((string)$_GET['config'])) {
            $id = (int)$_GET['config'];
            $token = rtrim(strtr(base64_encode((string)$id), '+/', '-_'), '=') . '.' . hash_hmac('sha256', (string)$id, 'Sistema_Certificado_template_key');
            header('Location: plantillas?config=' . $token);
            exit;
        }
        if (isset($_GET['config'])) {
            $parts = explode('.', (string)$_GET['config'], 2);
            $id = (int)base64_decode(strtr($parts[0] ?? '', '-_', '+/'));
            $valid = $id > 0 && isset($parts[1]) && hash_equals(hash_hmac('sha256', (string)$id, 'Sistema_Certificado_template_key'), $parts[1]);
            if (!$valid) {
                http_response_code(404);
                exit('Configuración no encontrada');
            }
            $q = $db->prepare('SELECT id,nombre,fondo_path,configuracion_json FROM plantillas WHERE id=:id AND deleted_at IS NULL');
            $q->execute(['id' => $id]);
            $template = $q->fetch();
            if (!$template) {
                header('Location: plantillas');
                exit;
            }
            $cfg = json_decode((string)($template['configuracion_json'] ?? '{}'), true) ?: [];
            $previewQuery = $db->prepare('SELECT c.id,c.curso_id,c.fecha_sesion,c.fecha_inicio,c.fecha_fin,c.horas_academicas,CONCAT(p.nombres," ",p.apellidos) participante,p.nro_registro_cas registro_cas,tp.nombre tipo_participante,cu.nombre curso,cu.horas_academicas curso_horas,cu.fecha_inicio curso_inicio,cu.fecha_fin curso_fin,t.nombre tipo FROM certificados c INNER JOIN participantes p ON p.id=c.participante_id LEFT JOIN tipos_participante tp ON tp.id=p.tipo_participante_id INNER JOIN cursos cu ON cu.id=c.curso_id INNER JOIN tipos_certificado t ON t.id=c.tipo_certificado_id WHERE c.plantilla_id=:id AND c.deleted_at IS NULL ORDER BY c.id LIMIT 1');
            $previewQuery->execute(['id' => $id]);
            $previewCertificate = $previewQuery->fetch() ?: [];
            if (!empty($previewCertificate['fecha_sesion'])) {
                $cfg['datos']['fecha_sesion'] = $previewCertificate['fecha_sesion'];
            }
            $availableQuery = $db->prepare('SELECT COUNT(*) total, MAX(p.nro_registro_cas IS NOT NULL AND p.nro_registro_cas <> "") has_cas, MAX(c.fecha_sesion IS NOT NULL) has_session, MAX(COALESCE(c.fecha_inicio,cu.fecha_inicio) IS NOT NULL) has_start, MAX(COALESCE(c.fecha_fin,cu.fecha_fin) IS NOT NULL) has_end, MAX(COALESCE(NULLIF(c.horas_academicas,0),cu.horas_academicas) > 0) has_hours FROM certificados c INNER JOIN participantes p ON p.id=c.participante_id INNER JOIN cursos cu ON cu.id=c.curso_id WHERE c.plantilla_id=:id AND c.deleted_at IS NULL');
            $availableQuery->execute(['id' => $id]);
            $available = $availableQuery->fetch() ?: [];
            $availableFields = ['participante' => (int)($available['total'] ?? 0) > 0, 'tipo_participante' => (int)($available['total'] ?? 0) > 0, 'registro_cas' => !empty($available['has_cas']), 'curso' => (int)($available['total'] ?? 0) > 0, 'tipo' => (int)($available['total'] ?? 0) > 0, 'fecha_sesion' => !empty($available['has_session']), 'fecha_inicio' => !empty($available['has_start']), 'fecha_fin' => !empty($available['has_end']), 'fecha_sistema' => (int)($available['total'] ?? 0) > 0, 'horas' => !empty($available['has_hours']), 'codigo_verificacion' => (int)($available['total'] ?? 0) > 0];
            $templateData = is_array($cfg['datos'] ?? null) ? $cfg['datos'] : [];
            foreach (['fecha_sesion', 'fecha_inicio', 'fecha_fin', 'horas'] as $dataKey) {
                if (trim((string)($templateData[$dataKey] ?? '')) !== '') $availableFields[$dataKey] = true;
            }
            $previewParticipant = (string)($previewCertificate['participante'] ?? '');
            $previewParticipantType = (string)($previewCertificate['tipo_participante'] ?? '');
            $previewCas = (string)($previewCertificate['registro_cas'] ?? '');
            $previewTypeName = (string)($previewCertificate['tipo'] ?? '');
            $previewCourseName = (string)($previewCertificate['curso'] ?? '');
            $previewCourse = ['id' => (int)($previewCertificate['curso_id'] ?? 0), 'horas_academicas' => $previewCertificate['curso_horas'] ?? '', 'fecha_inicio' => $previewCertificate['curso_inicio'] ?? '', 'fecha_fin' => $previewCertificate['curso_fin'] ?? ''];
            $previewSigners = [];
            if (!empty($previewCourse['id'])) {
                $signerQuery = $db->prepare('SELECT CONCAT(nombres," ",apellidos) nombre,cargo,firma_path FROM firmantes WHERE curso_id=:curso_id AND estado="activo" AND deleted_at IS NULL ORDER BY apellidos,nombres LIMIT 3');
                $signerQuery->execute(['curso_id' => (int)$previewCourse['id']]);
                $previewSigners = $signerQuery->fetchAll();
            }
            foreach ($previewSigners as &$previewSigner) {
                $signatureFile = dirname(__DIR__, 2) . '/' . ltrim((string)($previewSigner['firma_path'] ?? ''), '/');
                if (is_file($signatureFile)) {
                    $mime = mime_content_type($signatureFile) ?: 'image/png';
                    $previewSigner['firma_data'] = 'data:' . $mime . ';base64,' . base64_encode((string)file_get_contents($signatureFile));
                }
            }
            unset($previewSigner);
            $previewSignerName = (string)($db->query('SELECT CONCAT(nombres, " ", apellidos) FROM firmantes WHERE deleted_at IS NULL ORDER BY estado="activo" DESC, apellidos, nombres LIMIT 1')->fetchColumn() ?: 'FIRMANTE');
            $previewSignerCargo = (string)($db->query('SELECT cargo FROM firmantes WHERE deleted_at IS NULL ORDER BY estado="activo" DESC, apellidos, nombres LIMIT 1')->fetchColumn() ?: 'CARGO');
            $previewSignerId = (int)($db->query('SELECT id FROM firmantes WHERE deleted_at IS NULL AND estado="activo" AND firma_path IS NOT NULL AND firma_path <> "" ORDER BY apellidos,nombres LIMIT 1')->fetchColumn() ?: 0);
            $previewSignerSignature = (string)($db->query('SELECT firma_path FROM firmantes WHERE id=' . $previewSignerId . ' LIMIT 1')->fetchColumn() ?: '');
            if ($previewSignerSignature !== '') $previewSignerSignature = '/Sistema_Certificado/' . ltrim($previewSignerSignature, '/');
            ob_start();
            require dirname(__DIR__) . '/Views/templates/configure.php';
            $pageContent = ob_get_clean();
            $pageTitle = 'Configurar plantilla | Sistema de Certificados';
            $displayName = 'Wilmer Coscol';
            $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
            require dirname(__DIR__) . '/Views/layouts/dashboard.php';
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['template_id'], $_POST['activo']) && !isset($_POST['nombre'])) {
            (new Template($db))->setStatus((int)$_POST['template_id'], (int)$_POST['activo']);
            header('Location: plantillas');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
            unset($_SESSION['template_error']);
            $name = strtoupper(trim((string)$_POST['nombre']));
            if ($name === '') {
                $_SESSION['template_error'] = 'Ingresa el nombre de la plantilla.';
            } else {
                $path = null;
                if (!empty($_FILES['fondo']['name'])) {
                    $uploadError = (int)($_FILES['fondo']['error'] ?? UPLOAD_ERR_NO_FILE);
                    $extension = strtolower(pathinfo((string)$_FILES['fondo']['name'], PATHINFO_EXTENSION));
                    if ($uploadError !== UPLOAD_ERR_OK) {
                        $uploadMessages = [
                            UPLOAD_ERR_INI_SIZE => 'El hosting rechazó el PDF porque supera el límite de tamaño configurado.',
                            UPLOAD_ERR_FORM_SIZE => 'El formulario rechazó el PDF por su tamaño.',
                            UPLOAD_ERR_PARTIAL => 'La subida del PDF quedó incompleta. Inténtalo nuevamente.',
                            UPLOAD_ERR_NO_TMP_DIR => 'El hosting no tiene carpeta temporal para subir archivos.',
                            UPLOAD_ERR_CANT_WRITE => 'El hosting no permite escribir el archivo subido.',
                            UPLOAD_ERR_EXTENSION => 'Una extensión del hosting bloqueó la subida del PDF.',
                        ];
                        $_SESSION['template_error'] = $uploadMessages[$uploadError] ?? 'No se pudo subir el PDF al hosting.';
                    } elseif ($extension !== 'pdf') {
                        $_SESSION['template_error'] = 'El archivo debe tener extensión .pdf.';
                    } else {
                        $dir = dirname(__DIR__, 2) . '/storage/templates';
                        if (!is_dir($dir)) mkdir($dir, 0775, true);
                        $file = 'template_' . bin2hex(random_bytes(8)) . '.pdf';
                        if (!is_writable($dir)) $_SESSION['template_error'] = 'La carpeta storage/templates no tiene permisos de escritura.';
                        elseif (move_uploaded_file($_FILES['fondo']['tmp_name'], $dir . '/' . $file)) $path = 'storage/templates/' . $file;
                        else $_SESSION['template_error'] = 'No se pudo guardar el archivo PDF.';
                    }
                }
                if (!isset($_SESSION['template_error'])) {
                    $config = ['datos' => [
                        'horas' => (string)($_POST['horas_academicas'] ?? ''),
                        'fecha_sesion' => (string)($_POST['fecha_sesion'] ?? ''),
                        'fecha_inicio' => (string)($_POST['fecha_inicio'] ?? ''),
                        'fecha_fin' => (string)($_POST['fecha_fin'] ?? ''),
                    ]];
                    $data = ['nombre' => $name, 'descripcion' => trim((string)($_POST['descripcion'] ?? '')) ?: null, 'fondo_path' => $path, 'formato' => strtoupper(trim((string)($_POST['formato'] ?? 'A4'))), 'orientacion' => ($_POST['orientacion'] ?? 'horizontal'), 'configuracion_json' => json_encode($config, JSON_UNESCAPED_UNICODE)];
                    $id = (int)($_POST['template_id'] ?? 0);
                    if ($id > 0) (new Template($db))->update($id, $data);
                    else (new Template($db))->create([...$data, 'created_by' => (int)$_SESSION['auth_user']['id']]);
                    $_SESSION['template_success'] = $id > 0 ? 'Los datos de la plantilla se actualizaron correctamente.' : 'La plantilla se registró correctamente.';
                }
            }
            header('Location: plantillas');
            exit;
        }
        $templates = $db->query('SELECT p.id,p.nombre,p.descripcion,p.fondo_path,p.formato,p.orientacion,p.version,p.activo,p.configuracion_json,p.created_at,COUNT(DISTINCT c.participante_id) AS participantes_asignados FROM plantillas p LEFT JOIN certificados c ON c.plantilla_id=p.id AND c.deleted_at IS NULL WHERE p.deleted_at IS NULL GROUP BY p.id,p.nombre,p.descripcion,p.fondo_path,p.formato,p.orientacion,p.version,p.activo,p.configuracion_json,p.created_at ORDER BY p.nombre')->fetchAll();
        foreach ($templates as &$template) {
            $template['config_token'] = rtrim(strtr(base64_encode((string)$template['id']), '+/', '-_'), '=') . '.' . hash_hmac('sha256', (string)$template['id'], 'Sistema_Certificado_template_key');
        }
        unset($template);
        $templateError = $_SESSION['template_error'] ?? null;
        $templateSuccess = $_SESSION['template_success'] ?? null;
        unset($_SESSION['template_error'], $_SESSION['template_success']);
        ob_start();
        require dirname(__DIR__) . '/Views/templates/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Plantillas | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }

    private function ensureFontsTable(PDO $db): void
    {
        $db->exec("CREATE TABLE IF NOT EXISTS fuentes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(120) NOT NULL,
            orden INT UNSIGNED NOT NULL DEFAULT 0,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_fuentes_nombre (nombre),
            KEY idx_fuentes_activo_orden (activo, orden, nombre)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $fonts = [
            'Helvetica',
            'Times',
            'Courier',
            'EB Garamond',
            'Lato',
            'Arimo',
            'Carlito',
            'Liberation Serif',
            'FreeSerif',
            'Great Vibes',
            'Alex Brush',
            'Pinyon Script',
            'Allura',
            'Citadel Script',
            'Gladiola',
            'MADE Evolve Sans',
            'Niconne',
            'Amoresa Aged',
            'Amoresa Regular',
            'Fineday Style One',
            'Fineday Style One Non-connect',
            'Fineday Style Two',
            'Fineday Style Two Non-connect',
        ];
        $insert = $db->prepare('INSERT IGNORE INTO fuentes (nombre, orden) VALUES (:nombre, :orden)');
        foreach ($fonts as $order => $name) {
            $insert->execute(['nombre' => $name, 'orden' => ($order + 1) * 10]);
        }

        $db->exec("CREATE TABLE IF NOT EXISTS estilos_letra (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            codigo VARCHAR(10) NOT NULL,
            nombre VARCHAR(80) NOT NULL,
            orden INT UNSIGNED NOT NULL DEFAULT 0,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_estilos_letra_codigo (codigo),
            KEY idx_estilos_letra_activo_orden (activo, orden, nombre)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $styles = [
            ['', 'Normal'],
            ['B', 'Negrita'],
            ['I', 'Cursiva'],
            ['BI', 'Negrita cursiva'],
        ];
        $insertStyle = $db->prepare('INSERT IGNORE INTO estilos_letra (codigo, nombre, orden) VALUES (:codigo, :nombre, :orden)');
        foreach ($styles as $order => [$code, $name]) {
            $insertStyle->execute(['codigo' => $code, 'nombre' => $name, 'orden' => ($order + 1) * 10]);
        }
        $db->exec("CREATE TABLE IF NOT EXISTS transformaciones_texto (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, codigo VARCHAR(30) NOT NULL, nombre VARCHAR(120) NOT NULL, orden INT UNSIGNED NOT NULL DEFAULT 0, activo TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), UNIQUE KEY uq_transformaciones_texto_codigo (codigo), KEY idx_transformaciones_texto_activo_orden (activo, orden, nombre)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $transforms = [['none', 'Texto original'], ['uppercase', 'TODO EN MAYÚSCULAS'], ['title', 'Primeras Letras En Mayúscula'], ['lowercase', 'todo en minúsculas']];
        $insertTransform = $db->prepare('INSERT IGNORE INTO transformaciones_texto (codigo, nombre, orden) VALUES (:codigo, :nombre, :orden)');
        foreach ($transforms as $order => [$code, $name]) $insertTransform->execute(['codigo' => $code, 'nombre' => $name, 'orden' => ($order + 1) * 10]);
    }
}
