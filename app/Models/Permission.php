<?php

declare(strict_types=1);
final class Permission
{
    public function __construct(private readonly PDO $db) {}
    public function forUser(int $userId): array
    {
        $q = $this->db->prepare('SELECT DISTINCT p.codigo FROM permisos p INNER JOIN rol_permisos rp ON rp.permiso_id=p.id INNER JOIN usuario_roles ur ON ur.rol_id=rp.rol_id WHERE ur.usuario_id=:id');
        $q->execute(['id' => $userId]);
        return array_map('strval', $q->fetchAll(PDO::FETCH_COLUMN));
    }
}
