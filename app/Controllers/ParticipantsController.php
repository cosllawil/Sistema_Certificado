<?php

declare(strict_types=1);
final class ParticipantsController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        $participantTypes = $db->query('SELECT id,nombre FROM tipos_participante WHERE activo=1 ORDER BY nombre')->fetchAll();
        $professions = $db->query('SELECT id,nombre FROM profesiones WHERE activo=1 ORDER BY nombre')->fetchAll();
        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
        require_once dirname(__DIR__) . '/Services/ParticipantImportService.php';
        $userId = (int)$_SESSION['auth_user']['id'];
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'participantes.ver');
        if (empty($_SESSION['participant_csrf'])) $_SESSION['participant_csrf'] = bin2hex(random_bytes(32));
        if (isset($_GET['download_template'])) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx((new ParticipantImportService())->template());
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="plantilla_participantes.xlsx"');
            $writer->save('php://output');
            exit;
        }
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals((string) $_SESSION['participant_csrf'], (string) ($_POST['_token'] ?? ''))) {
                http_response_code(419);
                exit('Token de seguridad inválido.');
            }
            if (($_POST['action'] ?? '') === 'import_preview') {
                try {
                    $file = $_FILES['archivo_excel'] ?? null;
                    if (!$file || $file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Selecciona un archivo Excel válido.');
                    $rows = (new ParticipantImportService())->read($file['tmp_name']);
                    $_SESSION['participant_import_rows'] = (new ParticipantImportService())->validate($rows, $db);
                } catch (Throwable $exception) {
                    $_SESSION['participant_import_error'] = $exception->getMessage();
                }
                header('Location: participantes?import_preview=1#importar');
                exit;
            }
            if (($_POST['action'] ?? '') === 'import_confirm') {
                $rows = $_SESSION['participant_import_rows'] ?? [];
                $model = new Participant($db);
                $count = 0;
                foreach ($rows as $row) {
                    if (!empty($row['_errors'])) continue;
                    $model->create(['tipo' => $row['tipo_documento'], 'numero' => $row['numero_documento'], 'nombres' => $row['nombres'], 'apellidos' => $row['apellidos'], 'email' => $row['email'] ?: null, 'telefono' => $row['telefono'] ?: null, 'cas' => $row['nro_registro_cas'] ?: null, 'tipo_participante_id' => $row['_tipo_participante_id'], 'profesion_id' => $row['_profesion_id']]);
                    $count++;
                }
                unset($_SESSION['participant_import_rows']);
                $_SESSION['participant_import_success'] = "Se importaron {$count} participantes válidos.";
                header('Location: participantes');
                exit;
            }
            if (($_POST['action'] ?? '') === 'deactivate') {
                $participantId = (int) ($_POST['participant_id'] ?? 0);
                (new Participant($db))->deactivate($participantId);
                (new AuditService($db))->log($userId, 'Desactivar participante', 'participantes', $participantId);
                header('Location: participantes');
                exit;
            }
            if (($_POST['action'] ?? '') === 'activate') {
                $participantId = (int) ($_POST['participant_id'] ?? 0);
                (new Participant($db))->activate($participantId);
                (new AuditService($db))->log($userId, 'Activar participante', 'participantes', $participantId);
                header('Location: participantes');
                exit;
            }
            $data = [
                'tipo' => (string) ($_POST['tipo_documento'] ?? 'DNI'),
                'numero' => trim((string) ($_POST['numero_documento'] ?? '')),
                'nombres' => trim((string) ($_POST['nombres'] ?? '')),
                'apellidos' => trim((string) ($_POST['apellidos'] ?? '')),
                'email' => strtoupper(trim((string) ($_POST['email'] ?? ''))) ?: null,
                'telefono' => trim((string) ($_POST['telefono'] ?? '')) ?: null,
                'cas' => trim((string) ($_POST['nro_registro_cas'] ?? '')) ?: null,
                'tipo_participante_id' => (int) ($_POST['tipo_participante_id'] ?? 0),
                'profesion_id' => (int) ($_POST['profesion_id'] ?? 0),
            ];
            $documentValid = match ($data['tipo']) {
                'DNI' => preg_match('/^\d{8}$/', $data['numero']) === 1,
                'CE' => preg_match('/^\d{9}$/', $data['numero']) === 1,
                'PASAPORTE' => preg_match('/^[a-z0-9]{1,12}$/i', $data['numero']) === 1,
                default => $data['numero'] !== '',
            };
            $phoneValid = $data['telefono'] === null || preg_match('/^\d{9}$/', $data['telefono']) === 1;
            $emailValid = $data['email'] === null || filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== false;
            $profession = $db->prepare('SELECT nombre FROM profesiones WHERE id=:id AND activo=1');
            $profession->execute(['id' => $data['profesion_id']]);
            $professionName = (string) ($profession->fetchColumn() ?: '');
            $isLawyer = str_starts_with(strtoupper(trim($professionName)), 'ABOGADO');
            $casValid = !$isLawyer || ($data['cas'] !== null && preg_match('/^\d+$/', (string) $data['cas']) === 1);
            if ($data['tipo_participante_id'] < 1 || $data['profesion_id'] < 1 || !$documentValid || !$phoneValid || !$emailValid || !$casValid || $data['nombres'] === '' || $data['apellidos'] === '') {
                $error = 'Verifica el tipo y número de documento y completa los campos obligatorios.';
                $error = 'Completa los campos obligatorios: número de documento, nombres, apellidos y registro CAS.';
            } else {
                $participantId = (int) ($_POST['participant_id'] ?? 0);
                $isParticipantEdit = $participantId > 0;
                if ($participantId > 0) {
                    (new Participant($db))->update($participantId, $data);
                    (new AuditService($db))->log($userId, 'Editar participante', 'participantes', $participantId);
                } else {
                    $participantId = (new Participant($db))->create($data);
                    (new AuditService($db))->log($userId, 'Crear participante', 'participantes', $participantId);
                }
                $_SESSION['participant_success'] = $isParticipantEdit ? 'Los datos del participante se actualizaron correctamente.' : 'El participante se registró correctamente.';
                header('Location: participantes');
                exit;
            }
        }
        $participants = (new Participant($db))->all();
        $showImportPreview = isset($_GET['import_preview']) && $_GET['import_preview'] === '1';
        if (!$showImportPreview) {
            unset($_SESSION['participant_import_rows'], $_SESSION['participant_import_error']);
        }
        $importRows = $showImportPreview ? ($_SESSION['participant_import_rows'] ?? []) : [];
        $importError = $showImportPreview ? ($_SESSION['participant_import_error'] ?? null) : null;
        $importSuccess = $_SESSION['participant_import_success'] ?? null;
        $participantSuccess = $_SESSION['participant_success'] ?? null;
        unset($_SESSION['participant_import_error'], $_SESSION['participant_import_success'], $_SESSION['participant_success']);
        ob_start();
        require dirname(__DIR__) . '/Views/participants/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Participantes | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
