<?php

declare(strict_types=1);
final class Participant
{
    public function __construct(private readonly PDO $db) {}
    public function active(): array
    {
        return $this->db->query('SELECT * FROM participantes WHERE deleted_at IS NULL ORDER BY apellidos,nombres')->fetchAll();
    }
    public function all(): array
    {
        return $this->db->query('SELECT * FROM participantes ORDER BY apellidos,nombres')->fetchAll();
    }
    public function create(array $data): int
    {
        $query = $this->db->prepare('INSERT INTO participantes (tipo_documento, numero_documento, nombres, apellidos, email, telefono, nro_registro_cas) VALUES (:tipo, :numero, :nombres, :apellidos, :email, :telefono, :cas)');
        $query->execute($data);
        return (int) $this->db->lastInsertId();
    }
    public function update(int $id, array $data): void
    {
        $query = $this->db->prepare('UPDATE participantes SET tipo_documento=:tipo, numero_documento=:numero, nombres=:nombres, apellidos=:apellidos, email=:email, telefono=:telefono, nro_registro_cas=:cas WHERE id=:id');
        $data['id'] = $id;
        $query->execute($data);
    }
    public function deactivate(int $id): void
    {
        $query = $this->db->prepare('UPDATE participantes SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        $query->execute(['id' => $id]);
    }
    public function activate(int $id): void
    {
        $query = $this->db->prepare('UPDATE participantes SET deleted_at = NULL WHERE id = :id');
        $query->execute(['id' => $id]);
    }
}
