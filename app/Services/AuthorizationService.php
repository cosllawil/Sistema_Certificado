<?php

declare(strict_types=1);
final class AuthorizationService
{
    public function __construct(private readonly PDO $db) {}
    public function allows(int $userId, string $permission): bool
    {
        $q = $this->db->prepare('SELECT 1 FROM usuario_roles ur INNER JOIN roles r ON r.id=ur.rol_id LEFT JOIN rol_permisos rp ON rp.rol_id=r.id LEFT JOIN permisos p ON p.id=rp.permiso_id WHERE ur.usuario_id=:uid AND r.deleted_at IS NULL AND (r.codigo="superadministrador" OR p.codigo=:permission) LIMIT 1');
        $q->execute(['uid' => $userId, 'permission' => $permission]);
        return (bool)$q->fetchColumn();
    }
    public function require(int $userId, string $permission): void
    {
        if (!$this->allows($userId, $permission)) {
            http_response_code(403);
            exit('No tienes permisos para acceder a este módulo.');
        }
    }
}
