<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

spl_autoload_register(static function (string $class): void {
    foreach (['Controllers', 'Models', 'Repositories', 'Services'] as $directory) {
        $file = __DIR__ . '/' . $directory . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
