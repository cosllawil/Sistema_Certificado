<?php

declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
(new AuthController())->login();
