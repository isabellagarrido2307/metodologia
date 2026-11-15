-- =====================================================
-- CREACIÓN DE LA BASE DE DATOS MEDIPORTAL
-- =====================================================
CREATE DATABASE IF NOT EXISTS mediportal
CHARACTER SET utf8mb4
COLLATE utf8mb4_0900_ai_ci;

USE mediportal;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. TABLA REPRESENTANTES
-- =====================================================
DROP TABLE IF EXISTS representantes;
CREATE TABLE representantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    parentesco VARCHAR(50) NOT NULL, -- Ej: Madre, Padre, Tutor legal
    direccion TEXT NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =====================================================
-- 2. TABLA PACIENTES
-- =====================================================
DROP TABLE IF EXISTS pacientes;
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uid_nfc VARCHAR(100) UNIQUE DEFAULT NULL, -- Identificador único del chip NFC
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50) DEFAULT NULL,
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50) DEFAULT NULL,
    fecha_nacimiento DATE NOT NULL,
    sexo ENUM('Masculino', 'Femenino') NOT NULL,
    lugar_nacimiento VARCHAR(150) NOT NULL,
    representante_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1, -- 1: Activo, 0: Inactivo
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_paciente_representante
        FOREIGN KEY (representante_id)
        REFERENCES representantes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =====================================================
-- 3. TABLA CATÁLOGO DE ANTECEDENTES (Checkboxes)
-- =====================================================
DROP TABLE IF EXISTS cat_antecedentes;
CREATE TABLE cat_antecedentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria ENUM('Perinatal', 'Patológico', 'Familiar', 'Alérgico') NOT NULL,
    nombre_antecedente VARCHAR(150) NOT NULL,
    es_critico TINYINT(1) DEFAULT 0 -- 1 = Alerta roja de emergencia, 0 = Condición común / estándar
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =====================================================
-- 4. TABLA PACIENTE_ANTECEDENTES (Intermedia)

-- =====================================================
DROP TABLE IF EXISTS paciente_antecedentes;
CREATE TABLE paciente_antecedentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    antecedente_id INT NOT NULL,
    observacion_corta VARCHAR(150) DEFAULT NULL, -- Nota breve opcional para el antecedente marcado
    CONSTRAINT fk_pa_paciente
        FOREIGN KEY (paciente_id)
        REFERENCES pacientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pa_antecedente
        FOREIGN KEY (antecedente_id)
        REFERENCES cat_antecedentes(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =====================================================
-- 5. TABLA PEDIATRAS
-- =====================================================
DROP TABLE IF EXISTS pediatras;
CREATE TABLE pediatras (
    id_pediatra INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    mpps VARCHAR(30) UNIQUE NOT NULL,           -- Código del Ministerio de Salud
    colegio_medico VARCHAR(30) DEFAULT NULL,
    especialidad VARCHAR(100) NOT NULL DEFAULT 'Pediatría y Puericultura',
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =====================================================
-- 6. TABLA CONSULTAS (Historial clínico privado por médico)
-- =====================================================
DROP TABLE IF EXISTS consultas;
CREATE TABLE consultas (
    id_consulta INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    id_pediatra INT NOT NULL,                     -- Vínculo estricto de autoría médica
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    motivo_consulta VARCHAR(255) NOT NULL,
    peso DECIMAL(5,2) NOT NULL,                   -- en kg
    talla DECIMAL(5,2) NOT NULL,                  -- en cm
    circunferencia_cefalica DECIMAL(5,2) DEFAULT NULL, -- en cm
    examen_fisico TEXT NOT NULL,
    diagnostico TEXT NOT NULL,
    indicaciones_tratamiento TEXT NOT NULL,
    CONSTRAINT fk_consulta_paciente
        FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_consulta_pediatra
        FOREIGN KEY (id_pediatra) REFERENCES pediatras(id_pediatra)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =====================================================
-- 7. TABLA VACUNAS (Inmunizaciones pediátricas)
-- =====================================================
DROP TABLE IF EXISTS vacunas;
CREATE TABLE vacunas (
    id_vacuna INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    id_pediatra INT DEFAULT NULL,
    nombre_vacuna VARCHAR(100) NOT NULL,
    dosis VARCHAR(50) NOT NULL,
    fecha_aplicacion DATE DEFAULT NULL,
    proxima_dosis DATE DEFAULT NULL,
    lote VARCHAR(50) DEFAULT NULL,
    observaciones TEXT DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_vacunas_paciente (paciente_id),
    KEY idx_vacunas_pediatra (id_pediatra),
    KEY idx_vacunas_proxima (proxima_dosis),
    CONSTRAINT fk_vacunas_paciente FOREIGN KEY (paciente_id)
        REFERENCES pacientes (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_vacunas_pediatra FOREIGN KEY (id_pediatra)
        REFERENCES pediatras (id_pediatra) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =====================================================
-- SEED DATA: CATÁLOGO DE ANTECEDENTES
-- =====================================================
INSERT INTO cat_antecedentes (categoria, nombre_antecedente, es_critico) VALUES
-- ALERGIAS Y CONDICIONES CRÍTICAS (Sección Roja de Emergencia)
('Alérgico', 'Alergia a Penicilinas / Amoxicilina / Betalactámicos', 1),
('Alérgico', 'Alergia a AINEs (Ibuprofeno, Aspirina, Dipirona)', 1),
('Alérgico', 'Alergia a Sulfas u otros antibióticos', 1),
('Alérgico', 'Alergia Alimentaria Severa (Maní, Frutos secos, Leche, Huevo)', 1),
('Alérgico', 'Alergia a Picaduras de Insectos / Abejas', 1),
('Alérgico', 'Alergia al Látex', 1),
('Patológico', 'Asma Bronquial / Hiperreactividad Bronquial', 1),
('Patológico', 'Diabetes Mellitus Tipo 1 (Insulindependiente)', 1),
('Patológico', 'Cardiopatía Congénita / Arritmia cardíaca', 1),
('Patológico', 'Tratamiento Médico Crónico / Medicación Continua Vital', 1),

-- CONDICIONES MÉDICAS COMUNES Y OTROS ANTECEDENTES (Sección Azul / Estándar)
('Patológico', 'Rinitis Alérgica / Sinusitis Crónica', 0),
('Patológico', 'Enfermedad Celíaca / Intolerancia a la Lactosa', 0),
('Patológico', 'Reflujo Gastroesofágico (ERGE)', 0),

-- Perinatales (Historial base del nacimiento)
('Perinatal', 'Prematurez (< 37 semanas)', 0),
('Perinatal', 'Asfixia perinatal / Hipoxia', 0),
('Perinatal', 'Ictericia neonatal requiriendo fototerapia', 0),
('Perinatal', 'Cesárea de emergencia', 0),

-- Familiares (Heridofamiliares)
('Familiar', 'Diabetes mellitus en familiares directos', 0),
('Familiar', 'Hipertensión arterial familiar', 0),
('Familiar', 'Atopia / Dermatitis atópica familiar', 0),
('Familiar', 'Antecedente familiar de asma', 0);

-- =====================================================
-- SEED DATA: PEDIATRAS INICIALES
-- =====================================================
INSERT INTO pediatras (id_pediatra, nombres, apellidos, cedula, mpps, colegio_medico, especialidad, email, password, telefono, activo, created_at) VALUES
(1, 'Isabella', 'Garrido', '31257420', '1234', '', 'Pediatría y Puericultura', 'isabellasofia2006@gmail.com', 'nube2002', '', 1, '2026-10-26 01:03:34'),
(3, 'Mary', 'Anuel', '9222222', '123456', '1122', 'Pediatría y Puericultura', 'mary@gmail.com', 'mia2006', '1234567', 1, '2026-10-26 02:25:52');

-- =====================================================
-- SEED DATA: REPRESENTANTES Y PACIENTES INICIALES
-- =====================================================
INSERT INTO representantes (id, nombre, apellido, cedula, parentesco, direccion, telefono, created_at) VALUES
(1, 'Pepe', 'Vila', '29592626', 'Padre', 'Los Robles', '04126131156', '2026-10-25 22:38:55'),
(2, 'Valeria', 'Garrido', '9429920', 'Madre', 'Los Robles', '04266954488', '2026-11-03 22:29:31');

INSERT INTO pacientes (id, uid_nfc, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, sexo, lugar_nacimiento, representante_id, activo, created_at) VALUES
(1, NULL, 'Chino', 'Nacho', 'Medina', 'Marval', '2026-10-04', 'Masculino', 'El Valle', 1, 1, '2026-10-25 22:38:55'),
(2, NULL, 'Mía', 'Cristina', 'Garrido', 'Anuel', '2026-11-01', 'Femenino', 'El Valle', 2, 1, '2026-11-03 22:29:31');

INSERT INTO paciente_antecedentes (paciente_id, antecedente_id) VALUES
(2, 1),
(2, 2),
(2, 7),
(2, 11);

INSERT INTO vacunas (id_vacuna, paciente_id, id_pediatra, nombre_vacuna, dosis, fecha_aplicacion, proxima_dosis, lote, observaciones, created_at) VALUES
(1, 1, 1, 'Polio (VPI/VPO)', '2da dosis', '2026-11-15', NULL, '560', '', '2026-11-15 21:15:34');

SET FOREIGN_KEY_CHECKS = 1;

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

