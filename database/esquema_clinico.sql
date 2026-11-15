-- ============================================================
--  MediPortal - Esquema clínico complementario y adaptaciones
-- ============================================================

USE mediportal;

-- 1. Tabla de Catálogo de Antecedentes
CREATE TABLE IF NOT EXISTS cat_antecedentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria ENUM('Perinatal', 'Patológico', 'Familiar', 'Alérgico') NOT NULL,
    nombre_antecedente VARCHAR(150) NOT NULL,
    es_critico TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 2. Tabla Relacional de Antecedentes por Paciente
CREATE TABLE IF NOT EXISTS paciente_antecedentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    antecedente_id INT NOT NULL,
    observacion_corta VARCHAR(150) DEFAULT NULL,
    CONSTRAINT fk_pa_paciente 
        FOREIGN KEY (paciente_id) 
        REFERENCES pacientes(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pa_antecedente 
        FOREIGN KEY (antecedente_id) 
        REFERENCES cat_antecedentes(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3. Tabla Consultas / Historiales Clínicos
CREATE TABLE IF NOT EXISTS consultas (
    id_consulta INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    id_pediatra INT NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    motivo_consulta VARCHAR(255) NOT NULL,
    peso DECIMAL(5,2) NOT NULL,
    talla DECIMAL(5,2) NOT NULL,
    circunferencia_cefalica DECIMAL(5,2) DEFAULT NULL,
    examen_fisico TEXT NOT NULL,
    diagnostico TEXT NOT NULL,
    indicaciones_tratamiento TEXT NOT NULL,
    CONSTRAINT fk_consultas_paciente FOREIGN KEY (paciente_id)
        REFERENCES pacientes (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_consultas_pediatra FOREIGN KEY (id_pediatra)
        REFERENCES pediatras (id_pediatra) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 4. Registros de Vacunación
CREATE TABLE IF NOT EXISTS vacunas (
    id_vacuna INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    id_pediatra INT NULL,
    nombre_vacuna VARCHAR(100) NOT NULL,
    dosis VARCHAR(50) NOT NULL,
    fecha_aplicacion DATE NULL,
    proxima_dosis DATE NULL,
    lote VARCHAR(50) NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vacunas_paciente FOREIGN KEY (paciente_id)
        REFERENCES pacientes (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_vacunas_pediatra FOREIGN KEY (id_pediatra)
        REFERENCES pediatras (id_pediatra) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
