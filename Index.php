<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro Médico El Valle - Acceso Profesional</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="Index.css">
</head>
<body class="bg-clean-light min-vh-100 d-flex align-items-center">

    <div class="container py-5">
        <div class="row align-items-center justify-content-between">
            
            <!-- Columna Izquierda: Identidad Institucional y Título -->
            <div class="col-lg-6 mb-5 mb-lg-0 pe-lg-5">
                <div class="d-inline-flex align-items-center bg-white border border-info-subtle rounded-pill px-3 py-2 mb-4 shadow-sm">
                    <span class="badge bg-primary rounded-pill me-2 px-2 py-1 fs-7">MediPortal</span>
                    <span class="fw-bold text-primary fs-7 tracking-wider">CENTRO MÉDICO EL VALLE</span>
                </div>

                <h1 class="display-4 fw-bold text-dark mb-3 lh-tight">
                    Gestión Médica y <br>Vacunación <span class="text-primary">Segura</span>
                </h1>
                <p class="text-secondary fs-6 mb-0 pe-lg-4">
                    Consulte el historial clínico y esquema de vacunación de sus pacientes a un solo toque.
                </p>
            </div>

            <!-- Columna Derecha: Tarjeta de Acceso Profesional -->
            <div class="col-lg-5 col-md-8 mx-auto">
                <div class="card border-0 shadow-custom rounded-4 p-4 p-md-5 bg-white">
                    <div class="mb-4">
                        <h3 class="fw-bold text-dark mb-1">Acceso Profesional</h3>
                        <p class="text-muted small mb-0">Ingrese sus credenciales para iniciar sesión.</p>
                    </div>

                    <!-- Mensajes de alerta si vienen de la sesión -->
                    <?php if (isset($_SESSION['swal_message'])): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                            <?php echo $_SESSION['swal_message']['text']; ?>
                        </div>
                        <?php unset($_SESSION['swal_message']); // Limpiar mensaje después de mostrarlo ?>
                    <?php endif; ?>

                    <!-- El formulario apunta al archivo procesador -->
                    <form action="buscarPediatra.php" method="POST">
                        <div class="mb-3">
                            <label for="Cedula" class="form-label text-secondary fs-7 fw-bold">Ingrese su cédula</label>
                            <input type="text" class="form-control bg-light-subtle border-light-subtle py-2" id="Cedula" name="Cedula" placeholder="ej. 12345678" required>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label text-secondary fs-7 fw-bold mb-0">Contraseña</label>
                                <a href="#" class="text-primary small text-decoration-none fw-medium">¿Olvidó su contraseña?</a>
                            </div>
                            <div class="input-group bg-light-subtle rounded-2 overflow-hidden border border-light-subtle">
                                <input type="password" class="form-control bg-transparent border-0 py-2 shadow-none" id="password" name="password" required>
                                <button class="btn btn-link text-muted text-decoration-none border-0 px-3" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid mb-4 pt-2">
                            <div class="line-separator text-center mb-3">
                                <a href="registro.php" class="text-primary small text-decoration-none fw-medium">¿No tiene una cuenta? Regístrese</a>
                            </div>

                            <button type="submit" class="btn btn-primary py-2.5 fw-medium rounded-3 shadow-sm">Iniciar Sesión</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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