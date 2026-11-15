<?php
session_start();
include('bddConexion.php');
include('utilidades.php');

function limpiar_campo_consulta($valor): string
{
    return trim((string) $valor);
}

function volver_a_origen(string $origen, int $id_paciente): string
{
    if ($origen === 'historiales') {
        return '../historiales.php';
    }

    return '../ver_paciente.php?id=' . $id_paciente . '#consultas';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../historiales.php');
    exit();
}

$id_paciente = (int) ($_POST['id_paciente'] ?? 0);
$origen = ($_POST['origen'] ?? '') === 'historiales' ? 'historiales' : 'expediente';
$destino = volver_a_origen($origen, $id_paciente);

$motivo = limpiar_campo_consulta($_POST['motivo_consulta'] ?? '');
$fecha_consulta = limpiar_campo_consulta($_POST['fecha_consulta'] ?? date('Y-m-d\TH:i'));
$examen_fisico = limpiar_campo_consulta($_POST['examen_fisico'] ?? '');
$diagnostico = limpiar_campo_consulta($_POST['diagnostico'] ?? '');
$tratamiento = limpiar_campo_consulta($_POST['tratamiento'] ?? '');
$peso = limpiar_campo_consulta($_POST['peso'] ?? '');
$talla = limpiar_campo_consulta($_POST['talla'] ?? '');
$circunferencia = limpiar_campo_consulta($_POST['circunferencia_cefalica'] ?? '');

if ($id_paciente <= 0 || $motivo === '') {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Datos incompletos',
        'text' => 'Seleccione un paciente y escriba el motivo de la consulta.',
    ];
    header('Location: ' . $destino);
    exit();
}

if (empty($_SESSION['id_pediatra'])) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Sesión requerida',
        'text' => 'Inicie sesión para registrar consultas médicas.',
    ];
    header('Location: ../Index.php');
    exit();
}

if (!tabla_existe($mysqli, 'consultas')) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Esquema incompleto',
        'text' => 'Ejecute database/mediportal.sql para habilitar los historiales clínicos.',
    ];
    header('Location: ' . $destino);
    exit();
}

// Verificar que el paciente exista y esté activo.
$stmt_paciente = $mysqli->prepare('SELECT id FROM pacientes WHERE id = ? AND activo = 1 LIMIT 1');
$stmt_paciente->bind_param('i', $id_paciente);
$stmt_paciente->execute();
$paciente_existe = $stmt_paciente->get_result()->num_rows > 0;
$stmt_paciente->close();

if (!$paciente_existe) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Paciente no encontrado',
        'text' => 'No se pudo asociar la consulta al paciente indicado.',
    ];
    header('Location: ../historiales.php');
    exit();
}

$timestamp_consulta = strtotime($fecha_consulta);
$fecha_consulta_sql = $timestamp_consulta ? date('Y-m-d H:i:s', $timestamp_consulta) : date('Y-m-d H:i:s');
// peso y talla son NOT NULL en el esquema: se envían en 0 cuando no se indican.
$peso = $peso === '' ? 0.0 : (float) str_replace(',', '.', $peso);
$talla = $talla === '' ? 0.0 : (float) str_replace(',', '.', $talla);
$circunferencia = $circunferencia === '' ? null : (float) str_replace(',', '.', $circunferencia);
$id_pediatra = (int) $_SESSION['id_pediatra'];

$sql = 'INSERT INTO consultas
        (paciente_id, id_pediatra, fecha_hora, motivo_consulta, peso, talla, circunferencia_cefalica, examen_fisico, diagnostico, indicaciones_tratamiento)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

try {
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar el registro de la consulta.');
    }

    $stmt->bind_param(
        'iissdddsss',
        $id_paciente,
        $id_pediatra,
        $fecha_consulta_sql,
        $motivo,
        $peso,
        $talla,
        $circunferencia,
        $examen_fisico,
        $diagnostico,
        $tratamiento
    );

    if ($stmt->execute()) {
        $_SESSION['swal_message'] = [
            'icon' => 'success',
            'title' => 'Consulta registrada',
            'text' => 'La consulta se guardó correctamente en el historial del paciente.',
        ];
    } else {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Error al guardar',
            'text' => 'No se pudo registrar la consulta: ' . $stmt->error,
        ];
    }

    $stmt->close();
} catch (Throwable $e) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Error al guardar',
        'text' => 'No se pudo registrar la consulta. Verifique los datos e intente de nuevo.',
    ];
}

$mysqli->close();
header('Location: ' . $destino);
exit();
