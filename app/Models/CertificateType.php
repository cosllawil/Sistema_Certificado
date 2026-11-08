<?php

declare(strict_types=1);
final class CertificateType
{
    public function __construct(private readonly PDO $db) {}
    public function active(): array
    {
        return $this->db->query('SELECT t.id,t.codigo,t.nombre,t.descripcion,COALESCE(t.plantilla_id,(SELECT c.plantilla_id FROM cursos c WHERE c.tipo_certificado_id=t.id AND c.plantilla_id IS NOT NULL LIMIT 1)) plantilla_id,t.activo FROM tipos_certificado t WHERE t.activo=1 ORDER BY t.nombre')->fetchAll();
    }
    public function all(): array
    {
        return $this->db->query('SELECT t.id,t.codigo,t.nombre,t.descripcion,COALESCE(t.plantilla_id,(SELECT c.plantilla_id FROM cursos c WHERE c.tipo_certificado_id=t.id AND c.plantilla_id IS NOT NULL LIMIT 1)) plantilla_id,t.activo FROM tipos_certificado t ORDER BY t.nombre')->fetchAll();
    }
    public function setStatus(int $id, int $active): void
    {
        $q = $this->db->prepare('UPDATE tipos_certificado SET activo=:activo WHERE id=:id');
        $q->execute(['activo' => $active, 'id' => $id]);
    }
    public function create(array $data): void
    {
        $q = $this->db->prepare('INSERT INTO tipos_certificado (codigo,nombre,descripcion,plantilla_id,activo) VALUES (:codigo,:nombre,:descripcion,:plantilla_id,1)');
        $q->execute($data);
    }
    public function update(int $id, array $data): void
    {
        $q = $this->db->prepare('UPDATE tipos_certificado SET codigo=:codigo,nombre=:nombre,descripcion=:descripcion,plantilla_id=:plantilla_id WHERE id=:id');
        $q->execute([...$data, 'id' => $id]);
    }
    public function codeExists(string $code, int $excludeId = 0): bool
    {
        $q = $this->db->prepare('SELECT COUNT(*) FROM tipos_certificado WHERE codigo=:codigo AND id<>:id');
        $q->execute(['codigo' => $code, 'id' => $excludeId]);
        return (int)$q->fetchColumn() > 0;
    }
}
