<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class ParticipantImportService
{
    public const HEADERS = ['tipo_documento', 'numero_documento', 'nombres', 'apellidos', 'email', 'telefono', 'nro_registro_cas'];

    private function normalizeHeader(mixed $header): string
    {
        $value = mb_strtolower(trim((string)$header), 'UTF-8');
        $value = strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';
        return trim($value, '_');
    }

    public function read(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        $headers = array_map([$this, 'normalizeHeader'], array_shift($rows) ?: []);
        $indexes = array_flip($headers);
        $result = [];
        foreach ($rows as $line => $row) {
            if (!array_filter($row, static fn($value): bool => trim((string)$value) !== '')) continue;
            $item = [];
            foreach (self::HEADERS as $header) $item[$header] = trim((string)($row[$indexes[$header]] ?? ''));
            $item['_line'] = $line + 2;
            $result[] = $item;
        }
        if (array_diff(self::HEADERS, $headers)) throw new InvalidArgumentException('La plantilla no contiene todas las columnas requeridas.');
        return $result;
    }

    public function validate(array $rows, PDO $db): array
    {
        $seen = [];
        foreach ($rows as &$row) {
            $errors = [];
            $type = strtoupper($row['tipo_documento']);
            $row['tipo_documento'] = $type;
            $key = $type . '|' . $row['numero_documento'];
            $validDocument = $type === 'DNI' ? preg_match('/^\d{8}$/', $row['numero_documento']) : ($type === 'CE' ? preg_match('/^\d{9}$/', $row['numero_documento']) : $row['numero_documento'] !== '');
            if (!$validDocument) $errors[] = 'Documento inválido';
            if ($row['nombres'] === '' || $row['apellidos'] === '') $errors[] = 'Nombres y apellidos obligatorios';
            if ($row['email'] !== '' && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo inválido';
            if ($row['telefono'] !== '' && !preg_match('/^\d{9}$/', $row['telefono'])) $errors[] = 'Teléfono inválido';
            if ($row['nro_registro_cas'] === '' || !preg_match('/^\d+$/', $row['nro_registro_cas'])) $errors[] = 'Registro CAS obligatorio';
            if (isset($seen[$key])) $errors[] = 'Duplicado dentro del archivo';
            $q = $db->prepare('SELECT COUNT(*) FROM participantes WHERE tipo_documento=:tipo AND numero_documento=:numero');
            $q->execute(['tipo' => $type, 'numero' => $row['numero_documento']]);
            if ((int)$q->fetchColumn() > 0) $errors[] = 'Ya existe en el sistema';
            $seen[$key] = true;
            $row['_errors'] = $errors;
        }
        unset($row);
        return $rows;
    }

    public function template(): Spreadsheet
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        $sheet->fromArray([self::HEADERS, ['DNI', '00000000', 'NOMBRES', 'APELLIDOS', 'correo@ejemplo.com', '999999999', '12345']]);
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        foreach (range('A', 'G') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
        return $sheet->getParent();
    }
}
