<?php
session_start();
include('bddConexion.php');
include('utilidades.php');

function limpiar_campo_vacuna($valor): string
{
    return trim((string) $valor);
}

function volver_a_origen_vacuna(string $origen, int $id_paciente): string
{
    if ($origen === 'vacunacion') {
        return '../vacunacion.php';
    }

    return '../ver_paciente.php?id=' . $id_paciente . '#vacunas';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../vacunacion.php');
    exit();
}

$id_paciente = (int) ($_POST['id_paciente'] ?? 0);
$origen = ($_POST['origen'] ?? '') === 'vacunacion' ? 'vacunacion' : 'expediente';
$destino = volver_a_origen_vacuna($origen, $id_paciente);

$nombre_vacuna = limpiar_campo_vacuna($_POST['nombre_vacuna'] ?? '');
$dosis = limpiar_campo_vacuna($_POST['dosis'] ?? '');
$fecha_aplicacion = limpiar_campo_vacuna($_POST['fecha_aplicacion'] ?? '');
$proxima_dosis = limpiar_campo_vacuna($_POST['proxima_dosis'] ?? '');
$lote = limpiar_campo_vacuna($_POST['lote'] ?? '');
$observaciones = limpiar_campo_vacuna($_POST['observaciones'] ?? '');

if ($id_paciente <= 0 || $nombre_vacuna === '' || $dosis === '' || $fecha_aplicacion === '') {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Datos incompletos',
        'text' => 'Seleccione el paciente e indique la vacuna, la dosis y la fecha de aplicación.',
    ];
    header('Location: ' . $destino);
    exit();
}

if (!tabla_existe($mysqli, 'vacunas')) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Esquema incompleto',
        'text' => 'Ejecute database/mediportal.sql para habilitar los registros de vacunación.',
    ];
    header('Location: ' . $destino);
    exit();
}

$stmt_paciente = $mysqli->prepare('SELECT id FROM pacientes WHERE id = ? AND activo = 1 LIMIT 1');
$stmt_paciente->bind_param('i', $id_paciente);
$stmt_paciente->execute();
$paciente_existe = $stmt_paciente->get_result()->num_rows > 0;
$stmt_paciente->close();

if (!$paciente_existe) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Paciente no encontrado',
        'text' => 'No se pudo asociar la vacuna al paciente indicado.',
    ];
    header('Location: ../vacunacion.php');
    exit();
}

$fecha_aplicacion = date('Y-m-d', strtotime($fecha_aplicacion));
$proxima_dosis = $proxima_dosis === '' ? null : date('Y-m-d', strtotime($proxima_dosis));
$id_pediatra = isset($_SESSION['id_pediatra']) ? (int) $_SESSION['id_pediatra'] : null;

$sql = 'INSERT INTO vacunas (paciente_id, id_pediatra, nombre_vacuna, dosis, fecha_aplicacion, proxima_dosis, lote, observaciones)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

try {
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar el registro de la vacuna.');
    }

    $stmt->bind_param(
        'iissssss',
        $id_paciente,
        $id_pediatra,
        $nombre_vacuna,
        $dosis,
        $fecha_aplicacion,
        $proxima_dosis,
        $lote,
        $observaciones
    );

    if ($stmt->execute()) {
        $_SESSION['swal_message'] = [
            'icon' => 'success',
            'title' => 'Dosis registrada',
            'text' => 'La aplicación de ' . $nombre_vacuna . ' se guardó correctamente.',
        ];
    } else {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Error al guardar',
            'text' => 'No se pudo registrar la dosis: ' . $stmt->error,
        ];
    }

    $stmt->close();
} catch (Throwable $e) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Error al guardar',
        'text' => 'No se pudo registrar la dosis. Verifique los datos e intente de nuevo.',
    ];
}

$mysqli->close();
header('Location: ' . $destino);
exit();
