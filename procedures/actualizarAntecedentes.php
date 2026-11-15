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

// Asegurar que la columna antecedentes exista en la tabla
asegurar_columna_antecedentes($mysqli);

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

$antecedentes_json = empaquetar_antecedentes(
    $datos_emergencia,
    $condiciones_comunes,
    $grupo_sanguineo,
    $otros_antecedentes
);

try {
    $stmt = $mysqli->prepare("UPDATE pacientes SET antecedentes = ? WHERE id_paciente = ? LIMIT 1");
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar la actualización de antecedentes.');
    }

    $stmt->bind_param('si', $antecedentes_json, $id_paciente);
    if ($stmt->execute()) {
        $_SESSION['swal_message'] = [
            'icon' => 'success',
            'title' => '¡Antecedentes actualizados!',
            'text' => 'Los antecedentes médicos y datos de emergencia fueron guardados correctamente.',
        ];
    } else {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Error al guardar',
            'text' => 'No se pudieron actualizar los antecedentes: ' . $stmt->error,
        ];
    }
    $stmt->close();
} catch (Throwable $e) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Error inesperado',
        'text' => 'Ocurrió un error al procesar la solicitud: ' . $e->getMessage(),
    ];
}

header('Location: ' . $redirect_to);
exit();
