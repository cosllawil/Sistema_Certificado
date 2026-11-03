<?php

declare(strict_types=1);

final class AuthService
{
    public function __construct(private readonly PDO $database) {}

    public function authenticate(string $identity, string $password): array
    {
        $query = $this->database->prepare(
            'SELECT u.id, u.nombres, u.apellidos, u.username, u.email, u.password_hash, u.estado,
                    u.intentos_fallidos, u.bloqueado_hasta,
                    COALESCE((SELECT r.nombre FROM usuario_roles ur INNER JOIN roles r ON r.id = ur.rol_id
                              WHERE ur.usuario_id = u.id AND r.deleted_at IS NULL ORDER BY ur.asignado_at DESC LIMIT 1), "Sin rol") AS rol_nombre
             FROM usuarios u
             WHERE (u.username = :identity_username OR u.email = :identity_email)
               AND u.deleted_at IS NULL
             LIMIT 1'
        );
        $query->execute([
            'identity_username' => $identity,
            'identity_email' => $identity,
        ]);
        $user = $query->fetch();

        if (!$user || $user['estado'] !== 'activo') {
            throw new RuntimeException('El usuario o la contraseña no son válidos.');
        }

        if ($user['bloqueado_hasta'] !== null && strtotime((string) $user['bloqueado_hasta']) > time()) {
            throw new RuntimeException('La cuenta está bloqueada temporalmente. Intenta nuevamente más tarde.');
        }

        if (!password_verify($password, $user['password_hash'])) {
            $failedAttempts = ((int) $user['intentos_fallidos']) + 1;
            if ($failedAttempts >= 5) {
                $update = $this->database->prepare(
                    'UPDATE usuarios SET intentos_fallidos=:attempts, bloqueado_hasta=DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id=:id'
                );
                $update->execute(['attempts' => $failedAttempts, 'id' => $user['id']]);
                throw new RuntimeException('Has superado 5 intentos fallidos. La cuenta ha sido bloqueada durante 15 minutos.');
            } else {
                $update = $this->database->prepare(
                    'UPDATE usuarios SET intentos_fallidos=:attempts WHERE id=:id'
                );
                $update->execute(['attempts' => $failedAttempts, 'id' => $user['id']]);
                $remainingAttempts = 5 - $failedAttempts;
                throw new RuntimeException('El usuario o la contraseña no son válidos. Te quedan ' . $remainingAttempts . ' intento(s).');
            }
        }

        $update = $this->database->prepare(
            'UPDATE usuarios SET ultimo_acceso_at=NOW(), intentos_fallidos=0, bloqueado_hasta=NULL WHERE id=:id'
        );
        $update->execute(['id' => $user['id']]);

        return $user;
    }
}
