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
$sexo                 = limpiar_campo($_POST['sexo'] ?? '');
$lugar_nacimiento     = limpiar_campo($_POST['lugar_nacimiento'] ?? '');
$nombre_representante = limpiar_campo($_POST['nombre_representante'] ?? '');
$apellido_representante = limpiar_campo($_POST['apellido_representante'] ?? '');
$cedula_representante = limpiar_campo($_POST['cedula_representante'] ?? '');
$parentesco           = limpiar_campo($_POST['parentesco'] ?? 'Madre');
$telefono_contacto    = limpiar_campo($_POST['telefono_contacto'] ?? '');
$direccion            = limpiar_campo($_POST['direccion'] ?? '');
$antecedentes_raw     = limpiar_campo($_POST['antecedentes'] ?? '');

$datos_emergencia     = isset($_POST['datos_emergencia']) && is_array($_POST['datos_emergencia']) ? $_POST['datos_emergencia'] : [];
$condiciones_comunes  = isset($_POST['condiciones_comunes']) && is_array($_POST['condiciones_comunes']) ? $_POST['condiciones_comunes'] : [];
$grupo_sanguineo      = limpiar_campo($_POST['grupo_sanguineo'] ?? '');
$otros_antecedentes   = isset($_POST['otros_antecedentes']) && is_array($_POST['otros_antecedentes']) ? $_POST['otros_antecedentes'] : [];

if (!empty($datos_emergencia) || !empty($condiciones_comunes) || $grupo_sanguineo !== '' || !empty($otros_antecedentes)) {
    $antecedentes = empaquetar_antecedentes($datos_emergencia, $condiciones_comunes, $grupo_sanguineo, $otros_antecedentes);
} else {
    $antecedentes = $antecedentes_raw;
}

$errores = [];

if ($primer_nombre === '' || $primer_apellido === '') {
    $errores[] = 'El primer nombre y el primer apellido son obligatorios.';
}

if ($fecha_nacimiento === '' || !strtotime($fecha_nacimiento)) {
    $errores[] = 'Indique una fecha de nacimiento válida.';
} elseif (strtotime($fecha_nacimiento) > strtotime(date('Y-m-d'))) {
    $errores[] = 'La fecha de nacimiento no puede estar en el futuro.';
}

if (!in_array($sexo, ['M', 'F'], true)) {
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

// El campo `antecedentes` se agregó con database/esquema_clinico.sql.
// Se comprueba su existencia y se asegura que exista en la base de datos.
asegurar_columna_antecedentes($mysqli);
$columna_antecedentes = false;
$resultado_columna = $mysqli->query("SHOW COLUMNS FROM pacientes LIKE 'antecedentes'");
if ($resultado_columna && $resultado_columna->num_rows > 0) {
    $columna_antecedentes = true;
}

$columnas = 'primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, sexo, lugar_nacimiento, nombre_representante, apellido_representante, cedula_representante, parentesco, telefono_contacto, direccion';
$marcadores = '?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?';
$tipos = 'sssssssssssss';
$valores = [
    $primer_nombre,
    $segundo_nombre,
    $primer_apellido,
    $segundo_apellido,
    $fecha_nacimiento,
    $sexo,
    $lugar_nacimiento,
    $nombre_representante,
    $apellido_representante,
    $cedula_representante,
    $parentesco,
    $telefono_contacto,
    $direccion,
];

if ($columna_antecedentes) {
    $columnas .= ', antecedentes';
    $marcadores .= ', ?';
    $tipos .= 's';
    $valores[] = $antecedentes;
}

$sql = "INSERT INTO pacientes ($columnas, activo) VALUES ($marcadores, 1)";

try {
    $stmt = $mysqli->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar el registro del paciente.');
    }

    $stmt->bind_param($tipos, ...$valores);

    if ($stmt->execute()) {
        $id_paciente = $stmt->insert_id;
        $_SESSION['swal_message'] = [
            'icon' => 'success',
            'title' => '¡Paciente registrado!',
            'text' => 'El expediente de ' . $primer_nombre . ' ' . $primer_apellido . ' quedó creado correctamente.',
        ];
        $pagina_destino = '../ver_paciente.php?id=' . $id_paciente;
    } else {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Error al registrar',
            'text' => 'No se pudo guardar el paciente: ' . $stmt->error,
        ];
    }

    $stmt->close();
} catch (Throwable $e) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Error al registrar',
        'text' => 'No se pudo guardar el paciente. Verifique los datos e intente de nuevo.',
    ];
}

$mysqli->close();
header('Location: ' . $pagina_destino);
exit();
