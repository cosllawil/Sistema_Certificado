<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['auth_user']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$raw = json_decode((string)file_get_contents('php://input'), true);
$ids = array_values(array_unique(array_filter(array_map('intval', (array)($raw['ids'] ?? [])))));
if (!$ids) {
    http_response_code(422);
    echo json_encode(['error' => 'No se seleccionaron certificados']);
    exit;
}

$expires = time() + 300;
$batch = implode(',', $ids);
$payload = 'batch|' . $expires . '|' . $batch;
$secret = (string)(getenv('APP_KEY') ?: 'Sistema_Certificado_pdf_token_v1');
$signature = hash_hmac('sha256', $payload, $secret);
$token = rtrim(strtr(base64_encode($payload . '|' . $signature), '+/', '-_'), '=');

echo json_encode(['url' => 'certificado_pdf?token=' . rawurlencode($token)]);
