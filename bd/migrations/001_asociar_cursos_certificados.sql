ALTER TABLE cursos
    ADD COLUMN tipo_certificado_id SMALLINT UNSIGNED NULL AFTER fecha_fin,
    ADD COLUMN plantilla_id BIGINT UNSIGNED NULL AFTER tipo_certificado_id;

UPDATE cursos SET tipo_certificado_id = 4, plantilla_id = 1 WHERE id = 2;
UPDATE cursos SET tipo_certificado_id = 8, plantilla_id = 2 WHERE id = 3;
