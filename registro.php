<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro Médico El Valle - Registro Profesional</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="registro.css">
</head>
<body class="cuerpo-registro min-vh-100 d-flex align-items-center py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">
                
                <div class="tarjeta-registro p-4 p-md-5 bg-white rounded-4 border-0 shadow-custom">
                    
                    <!-- Encabezado -->
                    <div class="text-center mb-4">
                        <div class="icono-cabecera-registro mx-auto mb-3">
                            <i class="bi bi-person-plus-fill fs-4 text-primary"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Registro de Pediatra</h3>
                        <p class="text-muted small">Complete los datos profesionales para registrarse en el sistema del Centro Médico El Valle.</p>
                    </div>

                    <!-- Mensaje de Alerta (si existe) -->
                    <?php if (isset($_SESSION['swal_message'])): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                            <?php echo $_SESSION['swal_message']['text']; ?>
                        </div>
                        <?php unset($_SESSION['swal_message']); ?>
                    <?php endif; ?>

                    <!-- Formulario de Registro -->
                    <form action="registroPediatra.php" method="POST">
                        
                        <div class="row">
                            <!-- Nombres -->
                            <div class="col-md-6 mb-3">
                                <label for="nombres" class="form-label etiqueta-campo">Nombres</label>
                                <input type="text" class="form-control campo-formulario" id="nombres" name="nombres" placeholder="Ej. Isabella" required>
                            </div>
                            <!-- Apellidos -->
                            <div class="col-md-6 mb-3">
                                <label for="apellidos" class="form-label etiqueta-campo">Apellidos</label>
                                <input type="text" class="form-control campo-formulario" id="apellidos" name="apellidos" placeholder="Ej. Garrido" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Cédula -->
                            <div class="col-md-6 mb-3">
                                <label for="cedula" class="form-label etiqueta-campo">Cédula de Identidad</label>
                                <input type="text" class="form-control campo-formulario" id="cedula" name="cedula" placeholder="Ej. 31257420" required>
                            </div>
                            <!-- MPPS -->
                            <div class="col-md-6 mb-3">
                                <label for="mpps" class="form-label etiqueta-campo">Nro. de Registro MPPS</label>
                                <input type="text" class="form-control campo-formulario" id="mpps" name="mpps" placeholder="Ej. 1234" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Colegio Médico -->
                            <div class="col-md-6 mb-3">
                                <label for="colegio_medico" class="form-label etiqueta-campo">Colegio Médico (Opcional)</label>
                                <input type="text" class="form-control campo-formulario" id="colegio_medico" name="colegio_medico" placeholder="Ej. CM-4567">
                            </div>
                            <!-- Especialidad -->
                            <div class="col-md-6 mb-3">
                                <label for="especialidad" class="form-label etiqueta-campo">Especialidad</label>
                                <input type="text" class="form-control campo-formulario" id="especialidad" name="especialidad" value="Pediatría y Puericultura" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Correo Electrónico -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label etiqueta-campo">Correo Electrónico</label>
                                <input type="email" class="form-control campo-formulario" id="email" name="email" placeholder="correo@hospital.org" required>
                            </div>
                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label etiqueta-campo">Teléfono de Contacto</label>
                                <input type="text" class="form-control campo-formulario" id="telefono" name="telefono" placeholder="Ej. 04126131156" required>
                            </div>
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-4">
                            <label for="password" class="form-label etiqueta-campo">Contraseña</label>
                            <div class="input-group contenedor-grupo-password border rounded-2 overflow-hidden">
                                <input type="password" class="form-control campo-formulario border-0 shadow-none" id="password" name="password" placeholder="••••••••" required>
                                <button class="btn btn-link text-muted text-decoration-none border-0 px-3 bg-transparent" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Botón de Registro -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn boton-primario py-2.5 fw-medium text-white shadow-sm">Registrarse en el Sistema</button>
                        </div>

                        <!-- Enlace para volver al Login -->
                        <div class="text-center pt-2 border-top">
                            <p class="text-muted small mb-0">¿Ya posee una cuenta? <a href="Index.php" class="text-primary text-decoration-none fw-medium">Iniciar Sesión</a></p>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Script para mostrar/ocultar contraseña -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>