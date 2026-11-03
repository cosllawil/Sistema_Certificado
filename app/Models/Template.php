<?php

declare(strict_types=1);
final class Template
{
    public function __construct(private readonly PDO $db) {}
    public function active(): array
    {
        return $this->db->query('SELECT id,nombre FROM plantillas WHERE activo=1 AND deleted_at IS NULL ORDER BY nombre')->fetchAll();
    }
    public function create(array $data): void
    {
        $q = $this->db->prepare('INSERT INTO plantillas (nombre,descripcion,fondo_path,formato,orientacion,configuracion_json,created_by) VALUES (:nombre,:descripcion,:fondo_path,:formato,:orientacion,:configuracion_json,:created_by)');
        $q->execute($data);
    }
    public function update(int $id, array $data): void
    {
        $q = $this->db->prepare('UPDATE plantillas SET nombre=:nombre,descripcion=:descripcion,formato=:formato,orientacion=:orientacion,configuracion_json=:configuracion_json,fondo_path=COALESCE(:fondo_path,fondo_path),version=version+1 WHERE id=:id');
        $q->execute([...$data, 'id' => $id]);
    }
    public function setStatus(int $id, int $active): void
    {
        $q = $this->db->prepare('UPDATE plantillas SET activo=:activo WHERE id=:id');
        $q->execute(['activo' => $active, 'id' => $id]);
    }
}
