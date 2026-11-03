<?php

declare(strict_types=1);

function databaseConnection(): PDO
{
    static $connection;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $environment = [];
    $environmentFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

    if (is_readable($environmentFile)) {
        foreach (file($environmentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $environment[trim($key)] = trim($value);
        }
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $environment['DB_HOST'] ?? '127.0.0.1',
        $environment['DB_PORT'] ?? '3306',
        $environment['DB_DATABASE'] ?? ''
    );

    $connection = new PDO($dsn, $environment['DB_USERNAME'] ?? '', $environment['DB_PASSWORD'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
