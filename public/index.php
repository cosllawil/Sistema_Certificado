<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/Controllers/AuthPageController.php';
(new AuthPageController())->login();
