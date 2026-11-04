<?php

declare(strict_types=1);

final class CertificateController
{
    public function __construct(private readonly PDO $db) {}

    public function register(int $userId): void
    {
        if (empty($_SESSION['certificate_csrf'])) $_SESSION['certificate_csrf'] = bin2hex(random_bytes(32));
        require_once __DIR__ . '/../Models/Certificate.php';
        require_once __DIR__ . '/../Services/AuthorizationService.php';
        (new AuthorizationService($this->db))->require($userId, 'certificados.crear');
        require_once __DIR__ . '/../Models/Participant.php';
        require_once __DIR__ . '/../Models/Course.php';
        require_once __DIR__ . '/../Models/Template.php';
        require_once __DIR__ . '/../Models/CertificateType.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals((string) $_SESSION['certificate_csrf'], (string) ($_POST['_token'] ?? ''))) {
                http_response_code(419);
                exit('Token de seguridad inválido.');
            }
            $courseQuery = $this->db->prepare('SELECT tipo_certificado_id, plantilla_id FROM cursos WHERE id=:id AND deleted_at IS NULL');
            $courseQuery->execute(['id' => (int) $_POST['curso_id']]);
            $courseConfig = $courseQuery->fetch() ?: [];
            $observaciones = trim((string)($_POST['observaciones'] ?? ''));
            $observaciones = function_exists('mb_strtoupper') ? mb_strtoupper($observaciones, 'UTF-8') : strtoupper($observaciones);
            $data = ['p' => (int)$_POST['participante_id'], 'c' => (int)$_POST['curso_id'], 't' => (int)($courseConfig['tipo_certificado_id'] ?? 0), 'pl' => (int)($courseConfig['plantilla_id'] ?? 0) ?: null, 'h' => 0, 'fs' => null, 'fi' => null, 'ff' => null, 'o' => $observaciones ?: null, 'u' => $userId];
            $editId = (int)($_POST['certificate_id'] ?? 0);
            if ($editId > 0) {
                $q = $this->db->prepare('UPDATE certificados SET participante_id=:p,curso_id=:c,tipo_certificado_id=:t,plantilla_id=:pl,horas_academicas=:h,fecha_sesion=:fs,fecha_inicio=:fi,fecha_fin=:ff,observaciones=:o,updated_by=:u WHERE id=:id AND deleted_at IS NULL');
                $q->execute([...$data, 'fs' => $_POST['fecha_sesion'] ?: null, 'fi' => $_POST['fecha_inicio'] ?: null, 'ff' => $_POST['fecha_fin'] ?: null, 'id' => $editId]);
            } else {
                (new Certificate($this->db))->create($data);
            }
            header('Location: registrar_certificado?ok=1');
            exit;
        }

        $certificateEdit = null;
        if (isset($_GET['edit'])) {
            $q = $this->db->prepare('SELECT * FROM certificados WHERE id=:id AND deleted_at IS NULL');
            $q->execute(['id' => (int)$_GET['edit']]);
            $certificateEdit = $q->fetch() ?: null;
        }
        $participants = (new Participant($this->db))->active();
        $courses = (new Course($this->db))->active();
        $types = (new CertificateType($this->db))->active();
        $templates = (new Template($this->db))->active();
        ob_start();
        require __DIR__ . '/../Views/certificates/register.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Asignar certificado | Sistema de Certificados';
        $userParts = preg_split('/\s+/', trim((string) ($_SESSION['auth_user']['name'] ?? '')));
        $displayName = trim(($userParts[0] ?? '') . ' ' . ($userParts[2] ?? $userParts[1] ?? ''));
        $roleName = (string) ($_SESSION['auth_user']['role'] ?? '');
        require __DIR__ . '/../Views/layouts/dashboard.php';
    }
}
