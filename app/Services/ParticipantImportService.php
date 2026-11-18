<?php

declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

final class ParticipantImportService
{
    public const HEADERS = ['tipo_documento', 'numero_documento', 'nombres', 'apellidos', 'email', 'telefono', 'profesion', 'nro_registro_cas', 'tipo_participante'];

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
            $professionQuery = $db->prepare('SELECT id,nombre FROM profesiones WHERE UPPER(nombre)=UPPER(:nombre) AND activo=1 LIMIT 1');
            $professionQuery->execute(['nombre' => $row['profesion']]);
            $profession = $professionQuery->fetch() ?: null;
            $typeQuery = $db->prepare('SELECT id,nombre FROM tipos_participante WHERE UPPER(nombre)=UPPER(:nombre) AND activo=1 LIMIT 1');
            $typeQuery->execute(['nombre' => $row['tipo_participante']]);
            $participantType = $typeQuery->fetch() ?: null;
            if (!$profession) $errors[] = 'ProfesiÃ³n invÃ¡lida';
            if (!$participantType) $errors[] = 'Tipo de participante invÃ¡lido';
            if ($profession && str_starts_with(strtoupper((string)$profession['nombre']), 'ABOGADO') && ($row['nro_registro_cas'] === '' || !preg_match('/^\d+$/', $row['nro_registro_cas']))) $errors[] = 'Registro CAS obligatorio para abogados';
            $row['_profesion_id'] = (int)($profession['id'] ?? 0);
            $row['_tipo_participante_id'] = (int)($participantType['id'] ?? 0);
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
        $sheet->fromArray([self::HEADERS, ['DNI', '00000000', 'NOMBRES', 'APELLIDOS', 'correo@ejemplo.com', '999999999', 'ABOGADO (A)', '12345', 'ASISTENTE']]);
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        foreach (range('A', 'I') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
        return $sheet->getParent();
    }
}
