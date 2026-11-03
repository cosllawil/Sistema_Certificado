<?php

declare(strict_types=1);
final class Certificate
{
    public function __construct(private readonly PDO $db) {}
    public function create(array $data): int
    {
        $data['codigo'] = 'CERT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(5)));
        $q = $this->db->prepare('INSERT INTO certificados (participante_id,curso_id,tipo_certificado_id,plantilla_id,codigo_verificacion,horas_academicas,fecha_sesion,fecha_inicio,fecha_fin,observaciones,estado,created_by) VALUES (:p,:c,:t,:pl,:codigo,:h,:fs,:fi,:ff,:o,"borrador",:u)');
        $q->execute($data);
        return (int)$this->db->lastInsertId();
    }
}
