<?php

declare(strict_types=1);
final class Role
{
    public function __construct(private readonly PDO $db) {}
    public function all(): array
    {
        return $this->db->query('SELECT r.id,r.codigo,r.nombre,r.descripcion,COUNT(rp.permiso_id) permisos FROM roles r LEFT JOIN rol_permisos rp ON rp.rol_id=r.id WHERE r.deleted_at IS NULL GROUP BY r.id,r.codigo,r.nombre,r.descripcion ORDER BY r.nombre')->fetchAll();
    }
    public function create(array $data): int
    {
        $q = $this->db->prepare('INSERT INTO roles (codigo,nombre,descripcion) VALUES (:codigo,:nombre,:descripcion)');
        $q->execute($data);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id, array $data): void
    {
        $q = $this->db->prepare('UPDATE roles SET codigo=:codigo,nombre=:nombre,descripcion=:descripcion WHERE id=:id AND deleted_at IS NULL');
        $data['id'] = $id;
        $q->execute($data);
    }
    public function permissions(): array
    {
        return $this->db->query('SELECT id,codigo,nombre,modulo,descripcion FROM permisos ORDER BY modulo,nombre')->fetchAll();
    }
    public function permissionIds(int $roleId): array
    {
        $q = $this->db->prepare('SELECT permiso_id FROM rol_permisos WHERE rol_id=:id');
        $q->execute(['id' => $roleId]);
        return array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));
    }
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->prepare('DELETE FROM rol_permisos WHERE rol_id=:id')->execute(['id' => $roleId]);
        $q = $this->db->prepare('INSERT INTO rol_permisos (rol_id,permiso_id) VALUES (:rol_id,:permiso_id)');
        foreach (array_unique(array_filter(array_map('intval', $permissionIds))) as $permissionId) $q->execute(['rol_id' => $roleId, 'permiso_id' => $permissionId]);
    }
}
