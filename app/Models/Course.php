<?php

declare(strict_types=1);
final class Course
{
    public function __construct(private readonly PDO $db) {}
    public function active(): array
    {
        return $this->db->query('SELECT c.*, COALESCE(c.plantilla_id, t.plantilla_id) AS plantilla_id FROM cursos c LEFT JOIN tipos_certificado t ON t.id=c.tipo_certificado_id WHERE c.estado="activo" AND c.deleted_at IS NULL ORDER BY c.nombre')->fetchAll();
    }
    public function all(): array
    {
        return $this->db->query('SELECT c.*, COALESCE(c.plantilla_id, t.plantilla_id) AS plantilla_id FROM cursos c LEFT JOIN tipos_certificado t ON t.id=c.tipo_certificado_id ORDER BY c.nombre')->fetchAll();
    }
    public function setStatus(int $id, string $status): void
    {
        $q = $this->db->prepare('UPDATE cursos SET estado=:estado, deleted_at=:deleted WHERE id=:id');
        $q->execute(['estado' => $status, 'deleted' => $status === 'inactivo' ? date('Y-m-d H:i:s') : null, 'id' => $id]);
    }
    public function create(array $data): int
    {
        $q = $this->db->prepare('INSERT INTO cursos (codigo,nombre,descripcion,horas_academicas,fecha_inicio,fecha_fin,tipo_certificado_id,plantilla_id) VALUES (:codigo,:nombre,:descripcion,:horas,:inicio,:fin,:tipo_certificado_id,:plantilla_id)');
        $q->execute($data);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id, array $data): void
    {
        $q = $this->db->prepare('UPDATE cursos SET codigo=:codigo,nombre=:nombre,descripcion=:descripcion,horas_academicas=:horas,fecha_inicio=:inicio,fecha_fin=:fin,tipo_certificado_id=:tipo_certificado_id,plantilla_id=:plantilla_id WHERE id=:id');
        $data['id'] = $id;
        $q->execute($data);
    }
}
