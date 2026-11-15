<?php
session_start();
require_once __DIR__ . '/bddConexion.php';
require_once __DIR__ . '/utilidades.php';

$redirect_to = $_POST['redirect_to'] ?? '../pacientes.php';
// Asegurar que la redirección no se salga del sistema
if (!str_starts_with($redirect_to, '../') && !str_starts_with($redirect_to, 'pacientes.php') && !str_starts_with($redirect_to, 'ver_paciente.php')) {
    $redirect_to = '../pacientes.php';
}
if (!str_starts_with($redirect_to, '../') && (str_starts_with($redirect_to, 'pacientes.php') || str_starts_with($redirect_to, 'ver_paciente.php'))) {
    $redirect_to = '../' . $redirect_to;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect_to);
    exit();
}

$id_paciente = filter_input(INPUT_POST, 'id_paciente', FILTER_VALIDATE_INT);
if (!$id_paciente) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Error',
        'text' => 'Paciente no válido o no especificado.',
    ];
    header('Location: ' . $redirect_to);
    exit();
}

// Verificar que el paciente exista antes de modificar sus antecedentes.
$stmt_paciente = $mysqli->prepare('SELECT id FROM pacientes WHERE id = ? LIMIT 1');
$stmt_paciente->bind_param('i', $id_paciente);
$stmt_paciente->execute();
$paciente_existe = $stmt_paciente->get_result()->num_rows > 0;
$stmt_paciente->close();

if (!$paciente_existe) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Paciente no encontrado',
        'text' => 'El expediente indicado no existe en el sistema.',
    ];
    header('Location: ' . $redirect_to);
    exit();
}

// Recopilar datos recibidos
$datos_emergencia = isset($_POST['datos_emergencia']) && is_array($_POST['datos_emergencia'])
    ? $_POST['datos_emergencia']
    : [];

$condiciones_comunes = isset($_POST['condiciones_comunes']) && is_array($_POST['condiciones_comunes'])
    ? $_POST['condiciones_comunes']
    : [];

$grupo_sanguineo = trim((string)($_POST['grupo_sanguineo'] ?? ''));

$otros_antecedentes = isset($_POST['otros_antecedentes']) && is_array($_POST['otros_antecedentes'])
    ? $_POST['otros_antecedentes']
    : [];

try {
    $mysqli->begin_transaction();
    guardar_antecedentes_paciente($mysqli, (int) $id_paciente, $datos_emergencia, $condiciones_comunes, $grupo_sanguineo, $otros_antecedentes);
    $mysqli->commit();

    $_SESSION['swal_message'] = [
        'icon' => 'success',
        'title' => '¡Antecedentes actualizados!',
        'text' => 'Los antecedentes médicos y datos de emergencia fueron guardados correctamente.',
    ];
} catch (Throwable $e) {
    $mysqli->rollback();
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Error inesperado',
        'text' => 'Ocurrió un error al procesar la solicitud: ' . $e->getMessage(),
    ];
}

$mysqli->close();
header('Location: ' . $redirect_to);
exit();
