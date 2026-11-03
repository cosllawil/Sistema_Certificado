<?php

declare(strict_types=1);

final class DashboardRepository
{
    public function __construct(private readonly PDO $database) {}

    public function count(string $table, ?string $condition = null): int
    {
        $allowedTables = ['certificados', 'plantillas', 'participantes', 'cursos', 'impresiones_certificado'];
        if (!in_array($table, $allowedTables, true)) {
            throw new InvalidArgumentException('Tabla no permitida.');
        }

        $sql = "SELECT COUNT(*) FROM `{$table}`";
        if ($condition !== null) {
            $sql .= " WHERE {$condition}";
        }

        return (int) $this->database->query($sql)->fetchColumn();
    }

    public function recentCertificates(): array
    {
        $sql = 'SELECT c.id, c.numero, c.fecha_emision, c.estado,
                       CONCAT(p.nombres, " ", p.apellidos) AS participante,
                       cu.nombre AS curso
                FROM certificados c
                LEFT JOIN participantes p ON p.id = c.participante_id
                LEFT JOIN cursos cu ON cu.id = c.curso_id
                WHERE c.deleted_at IS NULL
                ORDER BY c.created_at DESC
                LIMIT 5';

        return $this->database->query($sql)->fetchAll();
    }

    public function monthlyEmissions(): array
    {
        $sql = 'SELECT MONTH(fecha_emision) AS month_number, COUNT(*) AS total
                FROM certificados
                WHERE deleted_at IS NULL AND fecha_emision IS NOT NULL
                GROUP BY MONTH(fecha_emision)
                ORDER BY month_number';
        return $this->database->query($sql)->fetchAll();
    }

    public function statusSummary(): array
    {
        $sql = 'SELECT estado, COUNT(*) AS total
                FROM certificados
                WHERE deleted_at IS NULL
                GROUP BY estado';
        $result = ['activo' => 0, 'pendiente' => 0, 'impreso' => 0, 'anulado' => 0];
        foreach ($this->database->query($sql)->fetchAll() as $row) {
            $status = strtolower((string) $row['estado']);
            $key = ['emitido' => 'activo', 'borrador' => 'pendiente', 'anulado' => 'anulado'][$status] ?? null;
            if ($key !== null) $result[$key] = (int) $row['total'];
        }
        $result['impreso'] = (int)$this->database->query('SELECT COUNT(DISTINCT certificado_id) FROM impresiones_certificado')->fetchColumn();
        return $result;
    }

    public function monthlyTrends(): array
    {
        $metrics = [
            'certificates' => "estado='emitido'",
            'pending' => "estado='borrador'",
        ];
        $result = [];
        foreach ($metrics as $key => $condition) {
            $current = (int)$this->database->query("SELECT COUNT(*) FROM certificados WHERE deleted_at IS NULL AND $condition AND created_at >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')")->fetchColumn();
            $previous = (int)$this->database->query("SELECT COUNT(*) FROM certificados WHERE deleted_at IS NULL AND $condition AND created_at >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m-01') AND created_at < DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')")->fetchColumn();
            $result[$key] = [$current, $previous];
        }
        foreach (['printed' => 'impresiones_certificado', 'templates' => 'plantillas'] as $key => $table) {
            $where = $table === 'plantillas' ? 'deleted_at IS NULL AND activo=1' : '1=1';
            $result[$key] = [(int)$this->database->query("SELECT COUNT(*) FROM $table WHERE $where AND created_at >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')")->fetchColumn(), (int)$this->database->query("SELECT COUNT(*) FROM $table WHERE $where AND created_at >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y-%m-01') AND created_at < DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')")->fetchColumn()];
        }
        return $result;
    }

    public function unreadNotifications(int $userId): int
    {
        $statement = $this->database->prepare(
            'SELECT COUNT(*) FROM notificaciones WHERE usuario_id = :usuario_id AND leida_at IS NULL'
        );
        $statement->execute(['usuario_id' => $userId]);

        return (int) $statement->fetchColumn();
    }
}
