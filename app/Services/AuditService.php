<?php

declare(strict_types=1);
final class AuditService
{
    public function __construct(private readonly PDO $db) {}
    public function log(?int $userId, string $action, string $entity, ?int $entityId = null, ?array $before = null, ?array $after = null): void
    {
        $q = $this->db->prepare('INSERT INTO auditoria (usuario_id, accion, entidad, entidad_id, anterior_json, nuevo_json, ip, user_agent) VALUES (:usuario_id,:accion,:entidad,:entidad_id,:anterior_json,:nuevo_json,:ip,:user_agent)');
        $q->execute(['usuario_id' => $userId, 'accion' => $action, 'entidad' => $entity, 'entidad_id' => $entityId, 'anterior_json' => $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE), 'nuevo_json' => $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE), 'ip' => $_SERVER['REMOTE_ADDR'] ?? null, 'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 512) : null]);
        if ($userId !== null) {
            try {
                $notification = $this->db->prepare('INSERT INTO notificaciones (usuario_id, titulo, mensaje) VALUES (:usuario_id, :titulo, :mensaje)');
                $notification->execute(['usuario_id' => $userId, 'titulo' => $action, 'mensaje' => 'Se registró una actualización en ' . $entity . '.']);
            } catch (Throwable) {
                // Las notificaciones no deben impedir guardar la operación principal.
            }
        }
    }
}
