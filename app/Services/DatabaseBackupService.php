<?php

declare(strict_types=1);

final class DatabaseBackupService
{
    private string $backupDirectory;

    public function __construct()
    {
        $this->backupDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($this->backupDirectory)) mkdir($this->backupDirectory, 0750, true);
    }

    public function createBackup(): string
    {
        $config = $this->connectionConfig();
        $filename = 'database_' . date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(3)) . '.sql';
        $path = $this->backupDirectory . DIRECTORY_SEPARATOR . $filename;
        $command = sprintf('%s --single-transaction --routines --triggers --default-character-set=utf8mb4 -h %s -P %s -u %s %s', escapeshellarg($this->mysqlExecutable('mysqldump.exe')), escapeshellarg($config['host']), escapeshellarg($config['port']), escapeshellarg($config['username']), escapeshellarg($config['database']));
        $this->runCommand($command, $path, $config['password']);
        return $path;
    }

    public function restore(string $uploadedPath): void
    {
        if (!is_file($uploadedPath) || strtolower(pathinfo($uploadedPath, PATHINFO_EXTENSION)) !== 'sql') throw new RuntimeException('El archivo debe tener extensión .sql.');
        $this->createBackup();
        $config = $this->connectionConfig();
        $command = sprintf('%s --default-character-set=utf8mb4 -h %s -P %s -u %s %s', escapeshellarg($this->mysqlExecutable('mysql.exe')), escapeshellarg($config['host']), escapeshellarg($config['port']), escapeshellarg($config['username']), escapeshellarg($config['database']));
        $this->runCommand($command, $uploadedPath, $config['password'], true);
    }

    public function listBackups(): array
    {
        $files = glob($this->backupDirectory . DIRECTORY_SEPARATOR . 'database_*.sql') ?: [];
        usort($files, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
        return array_map(fn(string $path): array => [
            'name' => basename($path),
            'path' => $path,
            'size' => (int) filesize($path),
            'created_at' => date('d/m/Y H:i:s', (int) filemtime($path)),
            'restore_count' => $this->restoreCount(basename($path)),
        ], $files);
    }

    public function restoreBackup(string $filename): void
    {
        $safeName = basename($filename);
        if ($safeName !== $filename || !preg_match('/^database_[0-9a-f_-]+\.sql$/', $safeName)) {
            throw new RuntimeException('Backup no válido.');
        }
        $path = $this->backupDirectory . DIRECTORY_SEPARATOR . $safeName;
        if (!is_file($path)) throw new RuntimeException('El backup seleccionado no existe.');
        $this->restore($path);
        $this->incrementRestoreCount($safeName);
    }

    private function metadataPath(string $filename): string
    {
        return $this->backupDirectory . DIRECTORY_SEPARATOR . $filename . '.meta.json';
    }

    private function restoreCount(string $filename): int
    {
        $metadata = $this->metadataPath($filename);
        if (!is_readable($metadata)) return 0;
        $data = json_decode((string) file_get_contents($metadata), true);
        return max(0, (int) ($data['restore_count'] ?? 0));
    }

    private function incrementRestoreCount(string $filename): void
    {
        file_put_contents($this->metadataPath($filename), json_encode(['restore_count' => $this->restoreCount($filename) + 1], JSON_THROW_ON_ERROR), LOCK_EX);
    }

    private function runCommand(string $command, string $file, string $password, bool $inputFile = false): void
    {
        $command .= $inputFile ? ' < ' . escapeshellarg($file) : ' > ' . escapeshellarg($file);
        $environment = getenv();
        if (!is_array($environment)) $environment = [];
        $environment['MYSQL_PWD'] = $password;
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $environment);
        if (!is_resource($process)) throw new RuntimeException('No se pudo iniciar la herramienta de MySQL.');
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            if (!$inputFile && is_file($file)) unlink($file);
            throw new RuntimeException(trim($error) ?: 'La operación de base de datos falló.');
        }
    }

    private function connectionConfig(): array
    {
        $values = [];
        $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (is_readable($file)) foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim($value);
        }
        return ['host' => $values['DB_HOST'] ?? '127.0.0.1', 'port' => $values['DB_PORT'] ?? '3306', 'database' => $values['DB_DATABASE'] ?? '', 'username' => $values['DB_USERNAME'] ?? '', 'password' => $values['DB_PASSWORD'] ?? ''];
    }

    private function mysqlExecutable(string $executable): string
    {
        $knownPaths = [
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\' . $executable,
            'C:\\xampp\\mysql\\bin\\' . $executable,
        ];
        foreach ($knownPaths as $path) if (is_file($path)) return $path;
        return $executable;
    }
}
