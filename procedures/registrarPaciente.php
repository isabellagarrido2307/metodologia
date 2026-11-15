<?php
session_start();
include('bddConexion.php');
require_once __DIR__ . '/utilidades.php';

$pagina_destino = '../nuevo_paciente.php';

function limpiar_campo($valor): string
{
    return trim((string) $valor);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../nuevo_paciente.php');
    exit();
}

$primer_nombre        = limpiar_campo($_POST['primer_nombre'] ?? '');
$segundo_nombre       = limpiar_campo($_POST['segundo_nombre'] ?? '');
$primer_apellido      = limpiar_campo($_POST['primer_apellido'] ?? '');
$segundo_apellido     = limpiar_campo($_POST['segundo_apellido'] ?? '');
$fecha_nacimiento     = limpiar_campo($_POST['fecha_nacimiento'] ?? '');
$sexo                 = normalizar_sexo($_POST['sexo'] ?? '');
$lugar_nacimiento     = limpiar_campo($_POST['lugar_nacimiento'] ?? '');
$nombre_representante = limpiar_campo($_POST['nombre_representante'] ?? '');
$apellido_representante = limpiar_campo($_POST['apellido_representante'] ?? '');
$cedula_representante = limpiar_campo($_POST['cedula_representante'] ?? '');
$parentesco           = limpiar_campo($_POST['parentesco'] ?? 'Madre');
$telefono_contacto    = limpiar_campo($_POST['telefono_contacto'] ?? '');
$direccion            = limpiar_campo($_POST['direccion'] ?? '');

$datos_emergencia     = isset($_POST['datos_emergencia']) && is_array($_POST['datos_emergencia']) ? $_POST['datos_emergencia'] : [];
$condiciones_comunes  = isset($_POST['condiciones_comunes']) && is_array($_POST['condiciones_comunes']) ? $_POST['condiciones_comunes'] : [];
$grupo_sanguineo      = limpiar_campo($_POST['grupo_sanguineo'] ?? '');
$otros_antecedentes   = isset($_POST['otros_antecedentes']) && is_array($_POST['otros_antecedentes']) ? $_POST['otros_antecedentes'] : [];

$errores = [];

if ($primer_nombre === '' || $primer_apellido === '') {
    $errores[] = 'El primer nombre y el primer apellido son obligatorios.';
}

if ($fecha_nacimiento === '' || !strtotime($fecha_nacimiento)) {
    $errores[] = 'Indique una fecha de nacimiento válida.';
} elseif (strtotime($fecha_nacimiento) > strtotime(date('Y-m-d'))) {
    $errores[] = 'La fecha de nacimiento no puede estar en el futuro.';
}

if ($sexo === null) {
    $errores[] = 'Seleccione el sexo del paciente.';
}

if ($nombre_representante === '' || $apellido_representante === '') {
    $errores[] = 'El nombre y apellido del representante son obligatorios.';
}

if ($cedula_representante === '' || $telefono_contacto === '') {
    $errores[] = 'La cédula y el teléfono del representante son obligatorios.';
}

if (!empty($errores)) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Datos incompletos',
        'text' => implode(' ', $errores),
    ];
    header('Location: ' . $pagina_destino);
    exit();
}

try {
    $mysqli->begin_transaction();

    // 1. Reutilizar el representante si la cédula ya existe; si no, crearlo.
    $stmt_rep = $mysqli->prepare('SELECT id FROM representantes WHERE cedula = ? LIMIT 1');
    if (!$stmt_rep) {
        throw new RuntimeException('No se pudo consultar el representante.');
    }
    $stmt_rep->bind_param('s', $cedula_representante);
    $stmt_rep->execute();
    $representante = $stmt_rep->get_result()->fetch_assoc();
    $stmt_rep->close();

    if ($representante) {
        $representante_id = (int) $representante['id'];
        $stmt_rep = $mysqli->prepare('UPDATE representantes
                                      SET nombre = ?, apellido = ?, parentesco = ?, direccion = ?, telefono = ?
                                      WHERE id = ?');
        if (!$stmt_rep) {
            throw new RuntimeException('No se pudo actualizar el representante.');
        }
        $stmt_rep->bind_param('sssssi', $nombre_representante, $apellido_representante, $parentesco, $direccion, $telefono_contacto, $representante_id);
    } else {
        $stmt_rep = $mysqli->prepare('INSERT INTO representantes (nombre, apellido, cedula, parentesco, direccion, telefono)
                                      VALUES (?, ?, ?, ?, ?, ?)');
        if (!$stmt_rep) {
            throw new RuntimeException('No se pudo preparar el registro del representante.');
        }
        $stmt_rep->bind_param('ssssss', $nombre_representante, $apellido_representante, $cedula_representante, $parentesco, $direccion, $telefono_contacto);
    }

    if (!$stmt_rep->execute()) {
        throw new RuntimeException('No se pudo guardar el representante: ' . $stmt_rep->error);
    }

    $representante_id = $representante ? $representante_id : (int) $stmt_rep->insert_id;
    $stmt_rep->close();

    // 2. Registrar al paciente vinculado a su representante.
    $stmt = $mysqli->prepare('INSERT INTO pacientes
        (primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, sexo, lugar_nacimiento, representante_id, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)');

    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar el registro del paciente.');
    }

    $segundo_nombre = $segundo_nombre === '' ? null : $segundo_nombre;
    $segundo_apellido = $segundo_apellido === '' ? null : $segundo_apellido;

    $stmt->bind_param(
        'sssssssi',
        $primer_nombre,
        $segundo_nombre,
        $primer_apellido,
        $segundo_apellido,
        $fecha_nacimiento,
        $sexo,
        $lugar_nacimiento,
        $representante_id
    );

    if (!$stmt->execute()) {
        throw new RuntimeException('No se pudo guardar el paciente: ' . $stmt->error);
    }

    $id_paciente = (int) $stmt->insert_id;
    $stmt->close();

    // 3. Guardar antecedentes en las tablas relacionales.
    guardar_antecedentes_paciente($mysqli, $id_paciente, $datos_emergencia, $condiciones_comunes, $grupo_sanguineo, $otros_antecedentes);

    $mysqli->commit();

    $_SESSION['swal_message'] = [
        'icon' => 'success',
        'title' => '¡Paciente registrado!',
        'text' => 'El expediente de ' . $primer_nombre . ' ' . $primer_apellido . ' quedó creado correctamente.',
    ];
    $pagina_destino = '../ver_paciente.php?id=' . $id_paciente;
} catch (Throwable $e) {
    $mysqli->rollback();
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Error al registrar',
        'text' => 'No se pudo guardar el paciente. Verifique los datos e intente de nuevo.',
    ];
}

$mysqli->close();
header('Location: ' . $pagina_destino);
exit();
