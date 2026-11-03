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
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'participantes.ver');
        if (empty($_SESSION['participant_csrf'])) $_SESSION['participant_csrf'] = bin2hex(random_bytes(32));
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals((string) $_SESSION['participant_csrf'], (string) ($_POST['_token'] ?? ''))) {
                http_response_code(419);
                exit('Token de seguridad inválido.');
            }
            if (($_POST['action'] ?? '') === 'deactivate') {
                (new Participant($db))->deactivate((int) ($_POST['participant_id'] ?? 0));
                header('Location: participantes');
                exit;
            }
            if (($_POST['action'] ?? '') === 'activate') {
                (new Participant($db))->activate((int) ($_POST['participant_id'] ?? 0));
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
            ];
            $documentValid = match ($data['tipo']) {
                'DNI' => preg_match('/^\d{8}$/', $data['numero']) === 1,
                'CE' => preg_match('/^\d{9}$/', $data['numero']) === 1,
                'PASAPORTE' => preg_match('/^[a-z0-9]{1,12}$/i', $data['numero']) === 1,
                default => $data['numero'] !== '',
            };
            $phoneValid = $data['telefono'] === null || preg_match('/^\d{9}$/', $data['telefono']) === 1;
            $emailValid = $data['email'] === null || filter_var($data['email'], FILTER_VALIDATE_EMAIL) !== false;
            $casValid = $data['cas'] !== null && preg_match('/^\d+$/', (string) $data['cas']) === 1;
            if (!$documentValid || !$phoneValid || !$emailValid || !$casValid || $data['nombres'] === '' || $data['apellidos'] === '') {
                $error = 'Verifica el tipo y número de documento y completa los campos obligatorios.';
                $error = 'Completa los campos obligatorios: número de documento, nombres, apellidos y registro CAS.';
            } else {
                $participantId = (int) ($_POST['participant_id'] ?? 0);
                if ($participantId > 0) (new Participant($db))->update($participantId, $data);
                else (new Participant($db))->create($data);
                header('Location: participantes?ok=1');
                exit;
            }
        }
        $participants = (new Participant($db))->all();
        ob_start();
        require dirname(__DIR__) . '/Views/participants/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Participantes | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
