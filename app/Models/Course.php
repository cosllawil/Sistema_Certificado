<?php

declare(strict_types=1);
final class Course
{
    public function __construct(private readonly PDO $db) {}
    public function active(): array
    {
        return $this->db->query('SELECT * FROM cursos WHERE estado="activo" AND deleted_at IS NULL ORDER BY nombre')->fetchAll();
    }
    public function all(): array
    {
        return $this->db->query('SELECT * FROM cursos ORDER BY nombre')->fetchAll();
    }
    public function setStatus(int $id, string $status): void
    {
        $q = $this->db->prepare('UPDATE cursos SET estado=:estado, deleted_at=:deleted WHERE id=:id');
        $q->execute(['estado' => $status, 'deleted' => $status === 'inactivo' ? date('Y-m-d H:i:s') : null, 'id' => $id]);
    }
    public function create(array $data): int
    {
        $q = $this->db->prepare('INSERT INTO cursos (codigo,nombre,descripcion,horas_academicas,fecha_inicio,fecha_fin) VALUES (:codigo,:nombre,:descripcion,:horas,:inicio,:fin)');
        $q->execute($data);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id, array $data): void
    {
        $q = $this->db->prepare('UPDATE cursos SET codigo=:codigo,nombre=:nombre,descripcion=:descripcion,horas_academicas=:horas,fecha_inicio=:inicio,fecha_fin=:fin WHERE id=:id');
        $data['id'] = $id;
        $q->execute($data);
    }
}
