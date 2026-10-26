<?php
session_start();
include('bddConexion.php'); 

$pagina_destino = 'index.php'; 

function validate($data){
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data); 
}

if (isset($_POST['Cedula']) && isset($_POST['password'])) { 
    
    $cedula_ingresada = validate($_POST['Cedula']); 
    $password_ingresada = $_POST['password']; // Contraseña que escribió el médico

    // 1. Asegúrate de que 'contrasena' sea el nombre exacto de tu columna en la BD
    $sql = "SELECT id_pediatra, nombres, apellidos, cedula, password_hash FROM pediatras WHERE cedula = ? LIMIT 1";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $cedula_ingresada);

        if ($stmt->execute()) {
            $resultado = $stmt->get_result();

            if($resultado->num_rows === 1) {
                $row = $resultado->fetch_assoc();
                
                // 2. COMO NO USAS HASH: Comparamos directamente el texto plano
                // (Cambia $row['contrasena'] por el nombre real de tu columna en la BD si es diferente)
                if ($password_ingresada === $row['password_hash']) {
                    
                    $_SESSION['id_pediatra'] = $row['id_pediatra'];
                    $_SESSION['cedula'] = $row['cedula'];
                    $_SESSION['nombre'] = $row['nombres']; 
                    
                    $_SESSION['swal_message'] = [
                        'icon' => 'success',
                        'title' => '¡Bienvenido(a)!',
                        'text' => 'Dr(a). ' . htmlspecialchars($row['nombres'])
                    ];
                    
                    $pagina_destino = 'dashboard.php'; 
                } else {
                    // Contraseña incorrecta
                    $_SESSION['swal_message'] = [
                        'icon' => 'error',
                        'title' => 'Error de Autenticación',
                        'text' => 'La contraseña ingresada es incorrecta.'
                    ];
                    $pagina_destino = 'Index.php';
                }
                
            } else {
                // Cédula no encontrada
                $_SESSION['swal_message'] = [
                    'icon' => 'error',
                    'title' => 'No Encontrado',
                    'text' => 'No se encontró ningún pediatra registrado con esa cédula.'
                ];
                $pagina_destino = 'Index.php'; 
            }
        } else {
            $_SESSION['swal_message'] = [
                'icon' => 'error',
                'title' => 'Error Interno',
                'text' => 'Error al ejecutar la consulta: ' . $stmt->error
            ];
            $pagina_destino = 'Index.php';
        }
        $stmt->close();
        
    } else {
        $_SESSION['swal_message'] = [
            'icon' => 'error',
            'title' => 'Error Interno',
            'text' => 'Error al preparar la consulta SQL: ' . $mysqli->error
        ];
        $pagina_destino = 'Index.php'; 
    }
    
    $mysqli->close(); 
    header("Location: " . $pagina_destino);
    exit();

} else {
    header("Location: Index.php");
    exit();
}
?>