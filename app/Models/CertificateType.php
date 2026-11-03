<?php

declare(strict_types=1);
final class CertificateType
{
    public function __construct(private readonly PDO $db) {}
    public function active(): array
    {
        return $this->db->query('SELECT id,codigo,nombre,descripcion,activo FROM tipos_certificado WHERE activo=1 ORDER BY nombre')->fetchAll();
    }
    public function all(): array
    {
        return $this->db->query('SELECT id,codigo,nombre,descripcion,activo FROM tipos_certificado ORDER BY nombre')->fetchAll();
    }
    public function setStatus(int $id, int $active): void
    {
        $q = $this->db->prepare('UPDATE tipos_certificado SET activo=:activo WHERE id=:id');
        $q->execute(['activo' => $active, 'id' => $id]);
    }
    public function create(array $data): void
    {
        $q = $this->db->prepare('INSERT INTO tipos_certificado (codigo,nombre,descripcion,activo) VALUES (:codigo,:nombre,:descripcion,1)');
        $q->execute($data);
    }
    public function update(int $id, array $data): void
    {
        $q = $this->db->prepare('UPDATE tipos_certificado SET codigo=:codigo,nombre=:nombre,descripcion=:descripcion WHERE id=:id');
        $q->execute([...$data, 'id' => $id]);
    }
    public function codeExists(string $code, int $excludeId = 0): bool
    {
        $q = $this->db->prepare('SELECT COUNT(*) FROM tipos_certificado WHERE codigo=:codigo AND id<>:id');
        $q->execute(['codigo' => $code, 'id' => $excludeId]);
        return (int)$q->fetchColumn() > 0;
    }
}
