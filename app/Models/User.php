<?php

declare(strict_types=1);
final class User
{
    public function __construct(private readonly PDO $db) {}
    public function findById(int $id): array|false
    {
        $q = $this->db->prepare('SELECT * FROM usuarios WHERE id=:id AND deleted_at IS NULL');
        $q->execute(['id' => $id]);
        return $q->fetch();
    }
    public function all(): array
    {
        return $this->db->query('SELECT id,nombres,apellidos,email,username,estado,ultimo_acceso_at FROM usuarios WHERE deleted_at IS NULL ORDER BY apellidos,nombres')->fetchAll();
    }
    public function create(array $data): int
    {
        $q = $this->db->prepare('INSERT INTO usuarios (nombres, apellidos, email, username, password_hash) VALUES (:nombres,:apellidos,:email,:username,:password_hash)');
        $q->execute($data);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id, array $data): void
    {
        $hasPassword = !empty($data['password_hash']);
        $sql = 'UPDATE usuarios SET nombres=:nombres, apellidos=:apellidos, email=:email, username=:username' . ($hasPassword ? ', password_hash=:password_hash' : '') . ' WHERE id=:id AND deleted_at IS NULL';
        if (!$hasPassword) unset($data['password_hash']);
        $q = $this->db->prepare($sql);
        $data['id'] = $id;
        $q->execute($data);
    }
    public function setStatus(int $id, string $status): void
    {
        $q = $this->db->prepare("UPDATE usuarios SET estado=:estado WHERE id=:id AND deleted_at IS NULL");
        $q->execute(['estado' => $status, 'id' => $id]);
    }
    public function roleId(int $id): int
    {
        $q = $this->db->prepare('SELECT rol_id FROM usuario_roles WHERE usuario_id=:id LIMIT 1');
        $q->execute(['id' => $id]);
        return (int)$q->fetchColumn();
    }
    public function assignRole(int $userId, int $roleId): void
    {
        $this->db->prepare('DELETE FROM usuario_roles WHERE usuario_id=:id')->execute(['id' => $userId]);
        if ($roleId > 0) $this->db->prepare('INSERT INTO usuario_roles (usuario_id,rol_id) VALUES (:usuario_id,:rol_id)')->execute(['usuario_id' => $userId, 'rol_id' => $roleId]);
    }
}
