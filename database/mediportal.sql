-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 15, 2026 at 09:17 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mediportal`
--

-- --------------------------------------------------------

--
-- Table structure for table `consultas`
--

CREATE TABLE `consultas` (
  `id_consulta` int NOT NULL,
  `id_paciente` int NOT NULL,
  `id_pediatra` int DEFAULT NULL,
  `fecha_consulta` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `motivo_consulta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `examen_fisico` text COLLATE utf8mb4_unicode_ci,
  `diagnostico` text COLLATE utf8mb4_unicode_ci,
  `tratamiento` text COLLATE utf8mb4_unicode_ci,
  `peso` decimal(5,2) DEFAULT NULL,
  `talla` decimal(5,2) DEFAULT NULL,
  `circunferencia_cefalica` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pacientes`
--

CREATE TABLE `pacientes` (
  `id_paciente` int NOT NULL,
  `primer_nombre` varchar(100) NOT NULL,
  `segundo_nombre` varchar(100) DEFAULT NULL,
  `primer_apellido` varchar(100) NOT NULL,
  `segundo_apellido` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` enum('M','F') NOT NULL,
  `lugar_nacimiento` varchar(100) DEFAULT NULL,
  `nombre_representante` varchar(150) NOT NULL,
  `apellido_representante` varchar(100) NOT NULL,
  `cedula_representante` varchar(20) NOT NULL,
  `parentesco` varchar(50) NOT NULL DEFAULT 'Madre',
  `telefono_contacto` varchar(20) NOT NULL,
  `direccion` text,
  `antecedentes` text,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pacientes`
--

INSERT INTO `pacientes` (`id_paciente`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `fecha_nacimiento`, `sexo`, `lugar_nacimiento`, `nombre_representante`, `apellido_representante`, `cedula_representante`, `parentesco`, `telefono_contacto`, `direccion`, `antecedentes`, `activo`, `created_at`) VALUES
(1, 'Chino', 'Nacho', 'Medina', 'Marval', '2026-10-04', 'M', 'El Valle', 'Pepe', 'Vila', '29592626', 'Padre', '04126131156', 'Los Robles', NULL, 1, '2026-10-26 02:38:55'),
(2, 'mia', 'cristina', 'garrido', 'anuel', '2026-11-01', 'M', 'el valle', 'valeria', 'garrido', '9429920', 'Madre', '04266954488', 'los robles', '{\"emergencia\":[\"Alergia a Penicilinas \\/ Amoxicilina \\/ Betalactámicos\",\"Alergia a AINEs (Ibuprofeno, Aspirina, Dipirona)\",\"Alergia a Sulfas u otros antibióticos\",\"Alergia Alimentaria Severa (Maní, Frutos secos, Leche, Huevo)\",\"Alergia a Picaduras de Insectos \\/ Abejas\",\"Alergia al Látex\",\"Asma Bronquial \\/ Hiperreactividad Bronquial\",\"Diabetes Mellitus Tipo 1 (Insulinodependiente)\",\"Cardiopatía Congénita \\/ Arritmia cardíaca\",\"Tratamiento Médico Crónico \\/ Medicación Continua Vital\"],\"condiciones\":[\"Rinitis Alérgica \\/ Sinusitis Crónica\",\"Enfermedad Celíaca \\/ Intolerancia a la Lactosa\",\"Reflujo Gastroesofágico (ERGE)\"],\"grupo_sanguineo\":\"\",\"otros\":[]}', 1, '2026-11-04 02:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `pediatras`
--

CREATE TABLE `pediatras` (
  `id_pediatra` int NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `mpps` varchar(30) NOT NULL,
  `colegio_medico` varchar(30) DEFAULT NULL,
  `especialidad` varchar(100) NOT NULL DEFAULT 'Pediatría y Puericultura',
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pediatras`
--

INSERT INTO `pediatras` (`id_pediatra`, `nombres`, `apellidos`, `cedula`, `mpps`, `colegio_medico`, `especialidad`, `email`, `password_hash`, `telefono`, `activo`, `created_at`) VALUES
(1, 'Isabella', 'Garrido', '31257420', '1234', '', 'Pediatría y Puericultura', 'isabellasofia2006@gmail.com', 'nube2002', '', 1, '2026-10-26 01:03:34'),
(3, 'Mary', 'Anuel', '9222222', '123456', '1122', 'Pediatría y Puericultura', 'mary@gmail.com', 'mia2006', '1234567', 1, '2026-10-26 02:25:52');

-- --------------------------------------------------------

--
-- Table structure for table `vacunas`
--

CREATE TABLE `vacunas` (
  `id_vacuna` int NOT NULL,
  `id_paciente` int NOT NULL,
  `id_pediatra` int DEFAULT NULL,
  `nombre_vacuna` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosis` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_aplicacion` date DEFAULT NULL,
  `proxima_dosis` date DEFAULT NULL,
  `lote` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vacunas`
--

INSERT INTO `vacunas` (`id_vacuna`, `id_paciente`, `id_pediatra`, `nombre_vacuna`, `dosis`, `fecha_aplicacion`, `proxima_dosis`, `lote`, `observaciones`, `created_at`) VALUES
(1, 1, 1, 'Polio (VPI/VPO)', '2da dosis', '2026-11-15', NULL, '560', '', '2026-11-15 21:15:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id_consulta`),
  ADD KEY `idx_consultas_paciente` (`id_paciente`),
  ADD KEY `idx_consultas_pediatra` (`id_pediatra`);

--
-- Indexes for table `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_paciente`);

--
-- Indexes for table `pediatras`
--
ALTER TABLE `pediatras`
  ADD PRIMARY KEY (`id_pediatra`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `mpps` (`mpps`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vacunas`
--
ALTER TABLE `vacunas`
  ADD PRIMARY KEY (`id_vacuna`),
  ADD KEY `idx_vacunas_paciente` (`id_paciente`),
  ADD KEY `idx_vacunas_pediatra` (`id_pediatra`),
  ADD KEY `idx_vacunas_proxima` (`proxima_dosis`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id_consulta` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_paciente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pediatras`
--
ALTER TABLE `pediatras`
  MODIFY `id_pediatra` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vacunas`
--
ALTER TABLE `vacunas`
  MODIFY `id_vacuna` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `fk_consultas_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_consultas_pediatra` FOREIGN KEY (`id_pediatra`) REFERENCES `pediatras` (`id_pediatra`) ON DELETE SET NULL;

--
-- Constraints for table `vacunas`
--
ALTER TABLE `vacunas`
  ADD CONSTRAINT `fk_vacunas_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vacunas_pediatra` FOREIGN KEY (`id_pediatra`) REFERENCES `pediatras` (`id_pediatra`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
