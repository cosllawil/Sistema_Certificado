<?php

declare(strict_types=1);
final class AuthPageController
{
    public function login(): void
    {
        require dirname(__DIR__) . '/Views/auth/login.php';
    }
}
