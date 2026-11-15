<?php
session_start();
include('bddConexion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['id_pediatra'])) {
    header('Location: ../configuracion.php');
    exit();
}

$id_pediatra = (int) $_SESSION['id_pediatra'];
$accion = $_POST['accion'] ?? '';

$stmt = $mysqli->prepare('SELECT * FROM pediatras WHERE id_pediatra = ? LIMIT 1');
$stmt->bind_param('i', $id_pediatra);
$stmt->execute();
$pediatra = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pediatra) {
    $_SESSION['swal_message'] = [
        'icon' => 'error',
        'title' => 'Cuenta no encontrada',
        'text' => 'No se pudo cargar la información de su cuenta.',
    ];
    header('Location: ../configuracion.php');
    exit();
}

function limpiar_campo_perfil($valor): string
{
    return trim((string) $valor);
}

if ($accion === 'perfil') {
    $nombres = limpiar_campo_perfil($_POST['nombres'] ?? '');
    $apellidos = limpiar_campo_perfil($_POST['apellidos'] ?? '');
    $colegio_medico = limpiar_campo_perfil($_POST['colegio_medico'] ?? '');
    $email = limpiar_campo_perfil($_POST['email'] ?? '');
    $telefono = limpiar_campo_perfil($_POST['telefono'] ?? '');

    if ($nombres === '' || $apellidos === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Datos inválidos',
            'text' => 'Complete su nombre, apellido y un correo electrónico válido.',
        ];
        header('Location: ../configuracion.php');
        exit();
    }

    // Verificar que el correo no esté siendo usado por otro pediatra.
    $stmt = $mysqli->prepare('SELECT id_pediatra FROM pediatras WHERE email = ? AND id_pediatra <> ? LIMIT 1');
    $stmt->bind_param('si', $email, $id_pediatra);
    $stmt->execute();
    $correo_duplicado = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($correo_duplicado) {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Correo en uso',
            'text' => 'Ya existe otra cuenta registrada con ese correo electrónico.',
        ];
        header('Location: ../configuracion.php');
        exit();
    }

    $sql = 'UPDATE pediatras SET nombres = ?, apellidos = ?, colegio_medico = ?, email = ?, telefono = ? WHERE id_pediatra = ?';

    try {
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('sssssi', $nombres, $apellidos, $colegio_medico, $email, $telefono, $id_pediatra);

        if ($stmt->execute()) {
            $_SESSION['nombre'] = $nombres;
            $_SESSION['swal_message'] = [
                'icon' => 'success',
                'title' => 'Perfil actualizado',
                'text' => 'Sus datos profesionales se guardaron correctamente.',
            ];
        } else {
            $_SESSION['swal_message'] = [
                'icon' => 'error',
                'title' => 'Error al guardar',
                'text' => 'No se pudieron actualizar sus datos: ' . $stmt->error,
            ];
        }

        $stmt->close();
    } catch (Throwable $e) {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Error al guardar',
            'text' => 'No se pudieron actualizar sus datos. Intente de nuevo.',
        ];
    }

    $mysqli->close();
    header('Location: ../configuracion.php');
    exit();
}

if ($accion === 'password') {
    $password_actual = (string) ($_POST['password_actual'] ?? '');
    $password_nueva = (string) ($_POST['password_nueva'] ?? '');
    $password_confirmar = (string) ($_POST['password_confirmar'] ?? '');

    if ($password_actual !== $pediatra['password_hash']) {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Contraseña incorrecta',
            'text' => 'La contraseña actual no coincide con la registrada.',
        ];
        header('Location: ../configuracion.php');
        exit();
    }

    if (strlen($password_nueva) < 6) {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Contraseña muy corta',
            'text' => 'La nueva contraseña debe tener al menos 6 caracteres.',
        ];
        header('Location: ../configuracion.php');
        exit();
    }

    if ($password_nueva !== $password_confirmar) {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'No coinciden',
            'text' => 'La nueva contraseña y su confirmación deben ser iguales.',
        ];
        header('Location: ../configuracion.php');
        exit();
    }

    try {
        $stmt = $mysqli->prepare('UPDATE pediatras SET password_hash = ? WHERE id_pediatra = ?');
        $stmt->bind_param('si', $password_nueva, $id_pediatra);

        if ($stmt->execute()) {
            $_SESSION['swal_message'] = [
                'icon' => 'success',
                'title' => 'Contraseña actualizada',
                'text' => 'Use su nueva contraseña la próxima vez que inicie sesión.',
            ];
        } else {
            $_SESSION['swal_message'] = [
                'icon' => 'error',
                'title' => 'Error al guardar',
                'text' => 'No se pudo cambiar la contraseña: ' . $stmt->error,
            ];
        }

        $stmt->close();
    } catch (Throwable $e) {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Error al guardar',
            'text' => 'No se pudo cambiar la contraseña. Intente de nuevo.',
        ];
    }

    $mysqli->close();
    header('Location: ../configuracion.php');
    exit();
}

header('Location: ../configuracion.php');
exit();
