CREATE TABLE IF NOT EXISTS tipos_participante (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(80) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id), UNIQUE KEY uq_tipo_participante_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO tipos_participante (nombre) VALUES ('ASISTENTE'), ('INSTRUCTOR');

CREATE TABLE IF NOT EXISTS profesiones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(80) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id), UNIQUE KEY uq_profesion_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO profesiones (nombre) VALUES ('ABOGADO'), ('OTROS');

ALTER TABLE participantes
  ADD COLUMN tipo_participante_id BIGINT UNSIGNED NULL AFTER nro_registro_cas,
  ADD COLUMN profesion_id BIGINT UNSIGNED NULL AFTER tipo_participante_id,
  ADD KEY idx_participante_tipo (tipo_participante_id),
  ADD KEY idx_participante_profesion (profesion_id),
  ADD CONSTRAINT fk_participante_tipo FOREIGN KEY (tipo_participante_id) REFERENCES tipos_participante(id),
  ADD CONSTRAINT fk_participante_profesion FOREIGN KEY (profesion_id) REFERENCES profesiones(id);

UPDATE participantes p
SET p.tipo_participante_id = (SELECT id FROM tipos_participante WHERE nombre='ASISTENTE' LIMIT 1),
    p.profesion_id = (SELECT id FROM profesiones WHERE nombre='OTROS' LIMIT 1)
WHERE p.tipo_participante_id IS NULL OR p.profesion_id IS NULL;
