-- ============================================================
--  MediPortal - Esquema clínico complementario
--  Ejecutar una sola vez sobre la base de datos `mediportal`.
-- ============================================================

USE mediportal;

-- Antecedentes del paciente (campo usado por el formulario de registro).
-- Se agrega de forma condicional para que el script pueda ejecutarse más de una vez.
SET @columna_antecedentes := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'pacientes'
      AND COLUMN_NAME = 'antecedentes'
);

SET @sql_antecedentes := IF(
    @columna_antecedentes = 0,
    'ALTER TABLE pacientes ADD COLUMN antecedentes TEXT NULL AFTER direccion',
    'SELECT 1'
);

PREPARE stmt_antecedentes FROM @sql_antecedentes;
EXECUTE stmt_antecedentes;
DEALLOCATE PREPARE stmt_antecedentes;

-- Consultas / historiales clínicos
CREATE TABLE IF NOT EXISTS consultas (
    id_consulta INT NOT NULL AUTO_INCREMENT,
    id_paciente INT NOT NULL,
    id_pediatra INT NULL,
    fecha_consulta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    motivo_consulta VARCHAR(255) NOT NULL,
    examen_fisico TEXT NULL,
    diagnostico TEXT NULL,
    tratamiento TEXT NULL,
    peso DECIMAL(5,2) NULL,
    talla DECIMAL(5,2) NULL,
    circunferencia_cefalica DECIMAL(5,2) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_consulta),
    KEY idx_consultas_paciente (id_paciente),
    KEY idx_consultas_pediatra (id_pediatra),
    CONSTRAINT fk_consultas_paciente FOREIGN KEY (id_paciente)
        REFERENCES pacientes (id_paciente) ON DELETE CASCADE,
    CONSTRAINT fk_consultas_pediatra FOREIGN KEY (id_pediatra)
        REFERENCES pediatras (id_pediatra) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registros de vacunación
CREATE TABLE IF NOT EXISTS vacunas (
    id_vacuna INT NOT NULL AUTO_INCREMENT,
    id_paciente INT NOT NULL,
    id_pediatra INT NULL,
    nombre_vacuna VARCHAR(100) NOT NULL,
    dosis VARCHAR(50) NOT NULL,
    fecha_aplicacion DATE NULL,
    proxima_dosis DATE NULL,
    lote VARCHAR(50) NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_vacuna),
    KEY idx_vacunas_paciente (id_paciente),
    KEY idx_vacunas_pediatra (id_pediatra),
    KEY idx_vacunas_proxima (proxima_dosis),
    CONSTRAINT fk_vacunas_paciente FOREIGN KEY (id_paciente)
        REFERENCES pacientes (id_paciente) ON DELETE CASCADE,
    CONSTRAINT fk_vacunas_pediatra FOREIGN KEY (id_pediatra)
        REFERENCES pediatras (id_pediatra) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
