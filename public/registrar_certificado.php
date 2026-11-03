<?php

declare(strict_types=1);
session_start();
if (!isset($_SESSION['auth_user'])) {
    header('Location: login');
    exit;
}
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/app/bootstrap.php';
(new CertificateController(databaseConnection()))->register((int) $_SESSION['auth_user']['id']);
