<?php

declare(strict_types=1);

final class SmtpMailService
{
    public function send(string $to, string $subject, string $html): void
    {
        $env = [];
        foreach (file(dirname(__DIR__, 2) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
        $host = (string) ($env['SMTP_HOST'] ?? '');
        $port = (int) ($env['SMTP_PORT'] ?? 465);
        $user = (string) ($env['SMTP_USER'] ?? '');
        $pass = (string) ($env['SMTP_PASS'] ?? '');
        $from = (string) ($env['SMTP_FROM_EMAIL'] ?? $user);
        $fromName = (string) ($env['SMTP_FROM_NAME'] ?? 'Sistema de Certificados');
        $socket = @fsockopen('ssl://' . $host, $port, $errno, $error, 15);
        if (!$socket) throw new RuntimeException('No se pudo conectar con el servidor de correo.');
        $readResponse = function () use ($socket): int {
            $code = 0;
            do {
                $response = (string) fgets($socket, 512);
                $code = (int) substr($response, 0, 3);
            } while (isset($response[3]) && $response[3] === '-');
            return $code;
        };
        $readResponse();
        $command = function (string $line, int $expected) use ($socket, $readResponse): void {
            fwrite($socket, $line . "\r\n");
            if ($readResponse() !== $expected) throw new RuntimeException('El servidor de correo rechazó la solicitud.');
        };
        try {
            $command('EHLO localhost', 250);
            $command('AUTH LOGIN', 334);
            $command(base64_encode($user), 334);
            $command(base64_encode($pass), 235);
            $command('MAIL FROM:<' . $from . '>', 250);
            $command('RCPT TO:<' . $to . '>', 250);
            $command('DATA', 354);
            $headers = 'From: ' . $fromName . ' <' . $from . ">\r\n" . 'Reply-To: ' . $from . "\r\n" . 'To: <' . $to . ">\r\n" . 'Date: ' . date(DATE_RFC2822) . "\r\n" . 'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $host . ">\r\n" . 'Subject: ' . $subject . "\r\n" . "MIME-Version: 1.0\r\n";
            if (str_contains($html, 'cid:system-logo')) {
                $boundary = '=_Part_' . bin2hex(random_bytes(8));
                $logoPath = dirname(__DIR__, 2) . '/assets/images/logo-colegio-abogados.png';
                $logo = is_file($logoPath) ? base64_encode((string)file_get_contents($logoPath)) : '';
                $headers .= 'Content-Type: multipart/related; boundary="' . $boundary . '"\r\n\r\n';
                $body = '--' . $boundary . "\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n" . $html . "\r\n\r\n--" . $boundary . "\r\nContent-Type: image/png; name=logo.png\r\nContent-Transfer-Encoding: base64\r\nContent-ID: <system-logo>\r\nContent-Disposition: inline; filename=logo.png\r\n\r\n" . chunk_split($logo) . "\r\n--" . $boundary . "--\r\n";
            } else {
                $headers .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n";
                $body = $html;
            }
            fwrite($socket, $headers . $body . "\r\n.\r\n");
            if ($readResponse() !== 250) throw new RuntimeException('El servidor de correo no aceptó el mensaje.');
            $command('QUIT', 221);
        } finally {
            fclose($socket);
        }
    }
}
