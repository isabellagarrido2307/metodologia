<?php
session_start();
include('bddConexion.php'); 

$pagina_destino = '../registro.php'; 

function limpiar_dato($data){
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Capturar y limpiar los datos del formulario
    $nombres = limpiar_dato($_POST['nombres'] ?? '');
    $apellidos = limpiar_dato($_POST['apellidos'] ?? '');
    $cedula = limpiar_dato($_POST['cedula'] ?? '');
    $mpps = limpiar_dato($_POST['mpps'] ?? '');
    $colegio_medico = !empty($_POST['colegio_medico']) ? limpiar_dato($_POST['colegio_medico']) : NULL;
    $especialidad = limpiar_dato($_POST['especialidad'] ?? 'Pediatría y Puericultura');
    $email = limpiar_dato($_POST['email'] ?? '');
    $telefono = limpiar_dato($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? ''; // Contraseña en texto plano tal como manejas en tu proyecto

    // Validar campos obligatorios
    if (!empty($nombres) && !empty($apellidos) && !empty($cedula) && !empty($mpps) && !empty($email) && !empty($password)) {
        
        // Verificar si la cédula o el email ya están registrados
        $sql_verificar = "SELECT id_pediatra FROM pediatras WHERE cedula = ? OR email = ? LIMIT 1";
        
        if ($stmt_verificar = $mysqli->prepare($sql_verificar)) {
            $stmt_verificar->bind_param("ss", $cedula, $email);
            $stmt_verificar->execute();
            $stmt_verificar->store_result();

            if ($stmt_verificar->num_rows > 0) {
                $_SESSION['swal_message'] = [
                    'icon' => 'error',
                    'title' => 'Registro Duplicado',
                    'text' => 'Ya existe un pediatra registrado con esta cédula o correo electrónico.'
                ];
                $stmt_verificar->close();
                header("Location: ../registro.php");
                exit();
            }
            $stmt_verificar->close();
        }

        // Insertar el nuevo pediatra en la base de datos
        // Nota: la columna `password` almacena la clave en texto plano (esquema actual).
        $sql_insertar = "INSERT INTO pediatras (nombres, apellidos, cedula, mpps, colegio_medico, especialidad, email, password, telefono, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

        if ($stmt = $mysqli->prepare($sql_insertar)) {
            $stmt->bind_param("sssssssss", $nombres, $apellidos, $cedula, $mpps, $colegio_medico, $especialidad, $email, $password, $telefono);

            if ($stmt->execute()) {
                $_SESSION['swal_message'] = [
                    'icon' => 'success',
                    'title' => '¡Registro Exitoso!',
                    'text' => 'Dr(a). ' . $nombres . ', ya puede iniciar sesión en el sistema.'
                ];
                $pagina_destino = '../Index.php'; // Redirigir al login tras registrarse con éxito
            } else {
                $_SESSION['swal_message'] = [
                    'icon' => 'error',
                    'title' => 'Error al Registrar',
                    'text' => 'No se pudo completar el registro: ' . $stmt->error
                ];
            }
            $stmt->close();
        } else {
            $_SESSION['swal_message'] = [
                'icon' => 'error',
                'title' => 'Error Interno',
                'text' => 'Error al preparar la consulta SQL.'
            ];
        }

    } else {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Campos Incompletos',
            'text' => 'Por favor, llene todos los campos obligatorios.'
        ];
    }

    $mysqli->close();
    header("Location: " . $pagina_destino);
    exit();

} else {
    header("Location: ../registro.php");
    exit();
}
?>