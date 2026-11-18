<?php

declare(strict_types=1);

final class DatabaseBackupService
{
    private string $backupDirectory;

    public function __construct()
    {
        $this->backupDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($this->backupDirectory) && !mkdir($this->backupDirectory, 0750, true) && !is_dir($this->backupDirectory)) throw new RuntimeException('No se pudo crear el directorio de backups.');
    }

    public function createBackup(): string
    {
        $filename = 'database_' . date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(3)) . '.sql';
        $path = $this->backupDirectory . DIRECTORY_SEPARATOR . $filename;
        $db = databaseConnection();
        $tables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
        $sql = "SET FOREIGN_KEY_CHECKS=0;\n\n";
        foreach ($tables as [$table]) {
            $quotedTable = $this->quoteIdentifier((string) $table);
            $create = $db->query('SHOW CREATE TABLE ' . $quotedTable)->fetch(PDO::FETCH_NUM);
            $sql .= "DROP TABLE IF EXISTS {$quotedTable};\n" . $create[1] . ";\n\n";
            foreach ($db->query('SELECT * FROM ' . $quotedTable)->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $columns = implode(', ', array_map([$this, 'quoteIdentifier'], array_keys($row)));
                $values = implode(', ', array_map(fn(mixed $value): string => $value === null ? 'NULL' : $db->quote((string) $value), array_values($row)));
                $sql .= "INSERT INTO {$quotedTable} ({$columns}) VALUES ({$values});\n";
            }
            $sql .= "\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        if (file_put_contents($path, $sql, LOCK_EX) === false) throw new RuntimeException('No se pudo guardar el backup.');
        return $path;
    }

    public function restore(string $uploadedPath): void
    {
        if (!is_file($uploadedPath) || strtolower(pathinfo($uploadedPath, PATHINFO_EXTENSION)) !== 'sql') throw new RuntimeException('El archivo debe tener extensión .sql.');
        $db = databaseConnection();
        $db->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->sqlStatements((string) file_get_contents($uploadedPath)) as $statement) $db->exec($statement);
        $db->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    public function listBackups(): array
    {
        $files = glob($this->backupDirectory . DIRECTORY_SEPARATOR . 'database_*.sql') ?: [];
        usort($files, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
        return array_map(fn(string $path): array => ['name' => basename($path), 'path' => $path, 'size' => (int) filesize($path), 'created_at' => date('d/m/Y H:i:s', (int) filemtime($path)), 'restore_count' => $this->restoreCount(basename($path))], $files);
    }

    public function restoreBackup(string $filename): void
    {
        $safeName = basename($filename);
        if ($safeName !== $filename || !preg_match('/^database_[0-9a-f_-]+\.sql$/', $safeName)) throw new RuntimeException('Backup no válido.');
        $path = $this->backupDirectory . DIRECTORY_SEPARATOR . $safeName;
        if (!is_file($path)) throw new RuntimeException('El backup seleccionado no existe.');
        $this->restore($path);
        $this->incrementRestoreCount($safeName);
    }

    private function metadataPath(string $filename): string { return $this->backupDirectory . DIRECTORY_SEPARATOR . $filename . '.meta.json'; }
    private function restoreCount(string $filename): int
    {
        $metadata = $this->metadataPath($filename);
        if (!is_readable($metadata)) return 0;
        $data = json_decode((string) file_get_contents($metadata), true);
        return max(0, (int) ($data['restore_count'] ?? 0));
    }
    private function incrementRestoreCount(string $filename): void { file_put_contents($this->metadataPath($filename), json_encode(['restore_count' => $this->restoreCount($filename) + 1], JSON_THROW_ON_ERROR), LOCK_EX); }
    private function quoteIdentifier(string $identifier): string { return '`' . str_replace('`', '``', $identifier) . '`'; }
    private function sqlStatements(string $sql): array
    {
        $statements = [];
        foreach (preg_split("/;(?=(?:[^']*'[^']*')*[^']*$)/", $sql) ?: [] as $statement) {
            $statement = trim($statement);
            if ($statement !== '' && !preg_match('/^(SET\s+FOREIGN_KEY_CHECKS|--|#)/i', $statement)) $statements[] = $statement;
        }
        return $statements;
    }
}
