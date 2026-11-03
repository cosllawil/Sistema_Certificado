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
            require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
            $pdf = new \setasign\Fpdi\Fpdi();
            $pages = $pdf->setSourceFile($full);
            for ($page = 1; $page <= $pages; $page++) {
                $tpl = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }
            $pdf->SetTitle('Plantilla');
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Plantilla.pdf"');
            $pdf->Output('I', 'Plantilla.pdf');
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
            $name = strtoupper(trim((string)$_POST['nombre']));
            if ($name === '') {
                $_SESSION['template_error'] = 'Ingresa el nombre de la plantilla.';
            } else {
                $path = null;
                if (!empty($_FILES['fondo']['name'])) {
                    if ($_FILES['fondo']['error'] !== UPLOAD_ERR_OK || strtolower(pathinfo($_FILES['fondo']['name'], PATHINFO_EXTENSION)) !== 'pdf') $_SESSION['template_error'] = 'Solo se permiten archivos PDF.';
                    else {
                        $dir = dirname(__DIR__, 2) . '/storage/templates';
                        if (!is_dir($dir)) mkdir($dir, 0775, true);
                        $file = 'template_' . bin2hex(random_bytes(8)) . '.pdf';
                        if (move_uploaded_file($_FILES['fondo']['tmp_name'], $dir . '/' . $file)) $path = 'storage/templates/' . $file;
                        else $_SESSION['template_error'] = 'No se pudo guardar el archivo PDF.';
                    }
                }
                if (!isset($_SESSION['template_error'])) {
                    $config = ['datos' => ['fecha_sesion' => (string)($_POST['fecha_sesion'] ?? '')]];
                    $data = ['nombre' => $name, 'descripcion' => trim((string)($_POST['descripcion'] ?? '')) ?: null, 'fondo_path' => $path, 'formato' => strtoupper(trim((string)($_POST['formato'] ?? 'A4'))), 'orientacion' => ($_POST['orientacion'] ?? 'horizontal'), 'configuracion_json' => json_encode($config, JSON_UNESCAPED_UNICODE)];
                    $id = (int)($_POST['template_id'] ?? 0);
                    if ($id > 0) (new Template($db))->update($id, $data);
                    else (new Template($db))->create([...$data, 'created_by' => (int)$_SESSION['auth_user']['id']]);
                }
            }
            header('Location: plantillas');
            exit;
        }
        $templates = $db->query('SELECT id,nombre,descripcion,fondo_path,formato,orientacion,version,activo,configuracion_json,created_at FROM plantillas WHERE deleted_at IS NULL ORDER BY nombre')->fetchAll();
        foreach ($templates as &$template) {
            $template['config_token'] = rtrim(strtr(base64_encode((string)$template['id']), '+/', '-_'), '=') . '.' . hash_hmac('sha256', (string)$template['id'], 'Sistema_Certificado_template_key');
        }
        unset($template);
        $templateError = $_SESSION['template_error'] ?? null;
        unset($_SESSION['template_error']);
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci");

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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci");

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
        $db->exec("CREATE TABLE IF NOT EXISTS transformaciones_texto (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, codigo VARCHAR(30) NOT NULL, nombre VARCHAR(120) NOT NULL, orden INT UNSIGNED NOT NULL DEFAULT 0, activo TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id), UNIQUE KEY uq_transformaciones_texto_codigo (codigo), KEY idx_transformaciones_texto_activo_orden (activo, orden, nombre)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci");
        $transforms = [['none', 'Texto original'], ['uppercase', 'TODO EN MAYÚSCULAS'], ['title', 'Primeras Letras En Mayúscula'], ['lowercase', 'todo en minúsculas']];
        $insertTransform = $db->prepare('INSERT IGNORE INTO transformaciones_texto (codigo, nombre, orden) VALUES (:codigo, :nombre, :orden)');
        foreach ($transforms as $order => [$code, $name]) $insertTransform->execute(['codigo' => $code, 'nombre' => $name, 'orden' => ($order + 1) * 10]);
    }
}
