<?php

declare(strict_types=1);
final class VerificationController
{
    public function show(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submittedCode = strtoupper(trim((string) ($_POST['codigo'] ?? '')));
            if ($submittedCode !== '') {
                $_SESSION['verification_code_once'] = $submittedCode;
                header('Location: verificar', true, 303);
                exit;
            }
        }
        $certificate = null;
        $verificationToken = null;
        $searched = false;
        $code = strtoupper(trim((string) ($_GET['codigo'] ?? $_SESSION['verification_code_once'] ?? '')));
        unset($_SESSION['verification_code_once']);
        if ($code !== '') {
            $searched = true;
            $db = databaseConnection();
            $q = $db->prepare('SELECT c.id,c.codigo_verificacion,c.estado,c.fecha_emision,c.horas_academicas,CONCAT(p.nombres," ",p.apellidos) participante,cu.nombre curso,t.nombre tipo FROM certificados c INNER JOIN participantes p ON p.id=c.participante_id INNER JOIN cursos cu ON cu.id=c.curso_id INNER JOIN tipos_certificado t ON t.id=c.tipo_certificado_id WHERE c.codigo_verificacion=:codigo AND c.deleted_at IS NULL LIMIT 1');
            $q->execute(['codigo' => $code]);
            $certificate = $q->fetch() ?: null;
            if ($certificate) {
                $expires = time() + 300;
                $payload = (int)$certificate['id'] . '|' . $expires . '|' . strtoupper((string)$certificate['codigo_verificacion']);
                $secret = (string)(getenv('APP_KEY') ?: 'Sistema_Certificado_pdf_token_v1');
                $signature = hash_hmac('sha256', $payload, $secret);
                $verificationToken = rtrim(strtr(base64_encode($payload . '|' . $signature), '+/', '-_'), '=');
            }
        }
        require dirname(__DIR__) . '/Views/auth/verify_certificate.php';
    }
}
