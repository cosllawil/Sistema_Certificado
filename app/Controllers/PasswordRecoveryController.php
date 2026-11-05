<?php

declare(strict_types=1);

final class PasswordRecoveryController
{
    public function show(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require dirname(__DIR__) . '/Views/auth/forgot_password.php';
            return;
        }

        $json = str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
        $respond = static function (bool $ok, string $message, int $status = 200) use ($json): void {
            if ($json) {
                http_response_code($status);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
            } else {
                header('Location: forgot_password');
            }
            exit;
        };

        $email = trim((string) ($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $respond(false, 'Ingresa un correo electrónico válido.', 422);
        }

        $db = databaseConnection();
        $query = $db->prepare('SELECT id, nombres, email FROM usuarios WHERE email=:email AND estado=\'activo\' AND deleted_at IS NULL LIMIT 1');
        $query->execute(['email' => $email]);
        $user = $query->fetch();
        if (!$user) {
            $respond(false, 'El correo electrónico no existe en el sistema.', 404);
        }

        $limitQuery = $db->prepare(
            'SELECT COUNT(*) FROM password_reset_tokens WHERE usuario_id=:user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        );
        $limitQuery->execute(['user_id' => $user['id']]);
        if ((int) $limitQuery->fetchColumn() >= 2) {
            $respond(false, 'Has alcanzado el límite de 2 solicitudes. Intenta nuevamente dentro de una hora.', 429);
        }

        $token = bin2hex(random_bytes(32));
        $insert = $db->prepare('INSERT INTO password_reset_tokens (usuario_id, token_hash, expires_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))');
        $insert->execute(['user_id' => $user['id'], 'token_hash' => hash('sha256', $token)]);
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath = str_contains($scriptPath, '/Sistema_Certificado') ? '/Sistema_Certificado' : '';
        $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? ($scheme . '://' . $host . $basePath)), '/');
        $link = $baseUrl . '/reset_password?token=' . urlencode($token);
        $name = htmlspecialchars((string) $user['nombres'], ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
        $logoPath = dirname(__DIR__, 2) . '/assets/images/logo-colegio-abogados.png';
        $logoSrc = is_file($logoPath) ? 'cid:system-logo' : htmlspecialchars($baseUrl . '/assets/images/logo-colegio-abogados.png', ENT_QUOTES, 'UTF-8');
        $styledHtml = '<!doctype html><html lang="es"><body style="margin:0;background:#eef3f8;font-family:Arial,Helvetica,sans-serif;color:#19304a"><div style="max-width:600px;margin:30px auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 8px 25px rgba(15,42,70,.12)"><div style="padding:28px 34px;background:#0b1d33;text-align:center"><img src="' . $logoSrc . '" alt="Sistema de Certificados" style="max-width:230px;max-height:72px;object-fit:contain"><div style="margin-top:16px;color:#bcd2eb;font-size:13px;letter-spacing:1px;text-transform:uppercase">Sistema de Certificados</div></div><div style="padding:38px 42px"><h1 style="margin:0 0 22px;color:#132f4c;font-size:25px">Recuperación de contraseña</h1><p style="font-size:16px;line-height:1.6;margin:0 0 16px">Hola <strong>' . $name . '</strong>,</p><p style="font-size:16px;line-height:1.6;margin:0 0 26px">Recibimos una solicitud para restablecer la contraseña de tu cuenta.</p><div style="text-align:center;margin:30px 0"><a href="' . $safeLink . '" style="display:inline-block;padding:14px 28px;background:#1683f7;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;font-size:16px">Restablecer contraseña</a></div><p style="font-size:14px;line-height:1.6;color:#526b85;margin:22px 0 0">Este enlace es válido durante <strong>1 hora</strong>. Si no solicitaste este cambio, puedes ignorar este correo.</p><p style="font-size:12px;line-height:1.5;color:#7890a8;margin:24px 0 0;word-break:break-all">Si el botón no funciona, copia este enlace:<br>' . $safeLink . '</p></div><div style="padding:18px 34px;background:#f5f8fb;color:#71869b;text-align:center;font-size:12px">Este mensaje fue enviado automáticamente. No respondas a este correo.</div></div></body></html>';
        $html = '<p>Hola ' . $name . ',</p><p>Solicitaste recuperar tu contraseña.</p><p><a href="' . $safeLink . '">Restablecer contraseña</a></p><p>Este enlace vence en 1 hora.</p>';
        $html = $styledHtml;
        try {
            (new SmtpMailService())->send($email, 'Recuperación de contraseña', $html);
        } catch (Throwable) {
            $respond(false, 'No se pudo enviar el correo. Intenta nuevamente.', 500);
        }
        $respond(true, 'El correo fue enviado correctamente.');
    }
}
