<?php

declare(strict_types=1);
final class ReportsController
{
    public function index(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (!isset($_SESSION['auth_user'])) {
            header('Location: login');
            exit;
        }
        $db = databaseConnection();
        (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'reportes.ver');
        $filterInput = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        $estado = trim((string)($filterInput['estado'] ?? ''));
        $cursoId = (int)($filterInput['curso_id'] ?? 0);
        $desde = trim((string)($filterInput['desde'] ?? ''));
        $hasta = trim((string)($filterInput['hasta'] ?? ''));
        $where = ['c.deleted_at IS NULL'];
        $params = [];
        if (in_array($estado, ['borrador', 'emitido', 'anulado'], true)) {
            $where[] = 'c.estado = :estado';
            $params['estado'] = $estado;
        }
        if ($cursoId > 0) {
            $where[] = 'c.curso_id = :curso_id';
            $params['curso_id'] = $cursoId;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $where[] = 'DATE(c.created_at) >= :desde';
            $params['desde'] = $desde;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $where[] = 'DATE(c.created_at) <= :hasta';
            $params['hasta'] = $hasta;
        }
        $base = ' FROM certificados c INNER JOIN participantes p ON p.id=c.participante_id INNER JOIN cursos cu ON cu.id=c.curso_id INNER JOIN tipos_certificado t ON t.id=c.tipo_certificado_id WHERE ' . implode(' AND ', $where);
        $q = $db->prepare('SELECT c.id,c.estado,c.fecha_emision,c.horas_academicas,CONCAT(p.nombres," ",p.apellidos) participante,cu.nombre curso,t.nombre tipo' . $base . ' ORDER BY c.created_at DESC');
        $q->execute($params);
        $certificates = $q->fetchAll();
        if (($_GET['export'] ?? '') === 'excel') {
            $this->exportExcel($certificates);
            exit;
        }
        $summary = ['total' => count($certificates), 'emitido' => 0, 'borrador' => 0, 'anulado' => 0, 'horas' => 0];
        foreach ($certificates as $certificate) {
            $key = (string)$certificate['estado'];
            if (isset($summary[$key])) $summary[$key]++;
            $summary['horas'] += (int)$certificate['horas_academicas'];
        }
        $courses = $db->query('SELECT id,nombre FROM cursos WHERE deleted_at IS NULL ORDER BY nombre')->fetchAll();
        ob_start();
        require dirname(__DIR__) . '/Views/reports/index.php';
        $pageContent = ob_get_clean();
        $pageTitle = 'Reportes | Sistema de Certificados';
        $displayName = 'Wilmer Coscol';
        $roleName = (string)($_SESSION['auth_user']['role'] ?? '');
        require dirname(__DIR__) . '/Views/layouts/dashboard.php';
    }

    private function exportExcel(array $certificates): void
    {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="reporte_certificados_' . date('Y-m-d_His') . '.xls"');
        echo "\xEF\xBB\xBF<table border=\"1\"><tr><th>N°</th><th>Participante</th><th>Curso</th><th>Tipo</th><th>Fecha emisión</th><th>Horas</th><th>Estado</th></tr>";
        foreach ($certificates as $index => $certificate) echo '<tr><td>' . ($index + 1) . '</td><td>' . htmlspecialchars((string)$certificate['participante'], ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars((string)$certificate['curso'], ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars((string)$certificate['tipo'], ENT_QUOTES, 'UTF-8') . '</td><td>' . (!empty($certificate['fecha_emision']) ? date('d/m/Y', strtotime((string)$certificate['fecha_emision'])) : '') . '</td><td>' . (int)$certificate['horas_academicas'] . '</td><td>' . htmlspecialchars(ucfirst((string)$certificate['estado']), ENT_QUOTES, 'UTF-8') . '</td></tr>';
        echo '</table>';
    }
}
