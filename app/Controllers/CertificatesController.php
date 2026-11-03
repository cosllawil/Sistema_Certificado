<?php

declare(strict_types=1);
final class CertificatesController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) { header('Location: login'); exit; }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'certificados.ver');
        if (empty($_SESSION['certificate_csrf'])) $_SESSION['certificate_csrf'] = bin2hex(random_bytes(32));
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emitir'])) {
            if (!hash_equals((string)$_SESSION['certificate_csrf'], (string)($_POST['_token'] ?? ''))) { http_response_code(419); exit('Token inválido.'); }
            $ids = array_values(array_filter(array_map('intval', (array)($_POST['ids'] ?? [])), static fn(int $id): bool => $id > 0));
            if ($ids) {
                $marks = implode(',', array_fill(0, count($ids), '?'));
                $q = $db->prepare("UPDATE certificados SET estado='emitido', fecha_emision=CURDATE(), emitido_at=NOW(), codigo_verificacion=COALESCE(NULLIF(codigo_verificacion,''), CONCAT('CERT-', YEAR(CURDATE()), '-', UPPER(SUBSTRING(REPLACE(UUID(),'-',''),1,10)))) WHERE estado='borrador' AND id IN ($marks)");
                $q->execute($ids);
            }
            $_SESSION['certificados_emitidos'] = $ids;
            header('Location: certificados'); exit;
        }
        $certificates = $db->query('SELECT c.id,c.numero,c.estado,c.fecha_emision,c.participante_id,c.curso_id,c.tipo_certificado_id,c.plantilla_id,c.horas_academicas,c.fecha_sesion,c.fecha_inicio,c.fecha_fin,c.observaciones,CONCAT(p.nombres," ",p.apellidos) participante,cu.nombre curso FROM certificados c INNER JOIN participantes p ON p.id=c.participante_id INNER JOIN cursos cu ON cu.id=c.curso_id WHERE c.deleted_at IS NULL ORDER BY c.created_at DESC')->fetchAll();
        ob_start();
        require dirname(__DIR__) . '/Views/certificates/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Certificados | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }
}
