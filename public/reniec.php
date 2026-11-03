<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['auth_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$numero = preg_replace('/\D/', '', (string) ($_GET['numero'] ?? ''));
if (strlen($numero) !== 8) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El DNI debe tener 8 dígitos']);
    exit;
}

$urls = [
    'https://pback.vesalio.com.pe/persona/reniec/' . rawurlencode($numero),
];
$response = false;
$status = 0;
$curlError = '';
foreach ($urls as $url) {
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: Sistema-Certificados/1.0']]);
    $candidate = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);
    if (is_string($candidate) && $status >= 200 && $status < 300) {
        $response = $candidate;
        break;
    }
}

if (!is_string($response)) {
    http_response_code(502);
    echo json_encode(['success' => false, 'message' => $curlError !== '' ? 'Error de conexión con la API: ' . $curlError : 'La API respondió con estado HTTP ' . $status]);
    exit;
}
$payload = json_decode($response, true);
if (isset($payload['data']['nombres'])) {
    $payload = ['success' => true, 'nombres' => $payload['data']['nombres'], 'apellidoPaterno' => $payload['data']['apellido_paterno'] ?? '', 'apellidoMaterno' => $payload['data']['apellido_materno'] ?? ''];
}
echo json_encode($payload, JSON_UNESCAPED_UNICODE);
