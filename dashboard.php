<?php
session_start();
// Opcional: Validar si el médico ha iniciado sesión
// if (!isset($_SESSION['id_pediatra'])) { header("Location: index.php"); exit(); }
$nombre_medico = $_SESSION['nombre'] ?? 'Sarah Jenkins';

// 1. Incluir la conexión a la base de datos
include('bddConexion.php');

// 2. Consultar el total de pacientes activos para la tarjeta de estadística
$sql_total = "SELECT COUNT(*) as total FROM pacientes WHERE activo = 1";
$resultado_total = $mysqli->query($sql_total);
$fila_total = $resultado_total->fetch_assoc();
$total_pacientes = $fila_total['total'] ?? 0;

// 3. Consultar los pacientes más recientes para la lista de la izquierda
$sql_recientes = "SELECT id_paciente, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, created_at FROM pacientes ORDER BY created_at DESC LIMIT 5";
$resultado_recientes = $mysqli->query($sql_recientes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediPortal - Dashboard Principal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Hoja de estilos personalizada -->
    <link rel="stylesheet" href="dashboard.css">
</head>
<body class="cuerpo-dashboard">

    <div class="contenedor-fluid-personalizado">
        <div class="fila-principal">
            
            <!-- BARRA LATERAL (SIDEBAR) -->
            <nav class="col-md-3 col-lg-2 barra-lateral d-flex flex-column justify-content-between min-vh-100">
                <div>
                    <!-- Logotipo / Marca -->
                    <div class="contenedor-logo">
                        <div class="icono-logo">
                            <i class="bi bi-plus-lg"></i>
                        </div>
                        <div>
                            <span class="texto-logo-principal">MediPortal</span>
                        </div>
                    </div>

                    <!-- Sección Workspace -->
                    <div class="bloque-menu">
                        <ul class="nav flex-column lista-enlaces">
                            <li class="nav-item">
                                <a href="#" class="enlace-navegacion activo">
                                    <i class="bi bi-house-door"></i> Inicio
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="enlace-navegacion">
                                    <i class="bi bi-people"></i> Pacientes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="enlace-navegacion">
                                    <i class="bi bi-journal-medical"></i> Clinical Notes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="enlace-navegacion">
                                    <i class="bi bi-shield-plus"></i> Registros de Vacunación
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="enlace-navegacion">
                                    <i class="bi bi-file-earmark-text"></i> ¿Cómo usar el sistema?
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="enlace-navegacion">
                                    <i class="bi bi-gear"></i> Configuración
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="cerrarSesion.php" class="enlace-navegacion">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- CONTENIDO PRINCIPAL -->
            <main class="col-md-9 col-lg-10 ms-sm-auto area-contenido">
                
                <!-- Cabecera Superior -->
                <header class="cabecera-principal">
                    <div>
                        <h2 class="titulo-pagina">Inicio</h2>
                        <p class="subtitulo-pagina">Busque pacientes, revise registros y agregue información desde un solo lugar.</p>
                    </div>
                    <div class="acciones-cabecera">
                        <!-- Barra de Búsqueda -->
                        <div class="barra-busqueda">
                            <span class="icono-busqueda"><i class="bi bi-search"></i></span>
                            <input type="text" class="input-busqueda" placeholder="Ingrese nombre del paciente">
                        </div>
                    </div>
                </header>

                <!-- TARJETAS DE ESTADÍSTICAS -->
                <div class="row g-3 rejilla-estadisticas">
                    <div class="col-xl-3 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-azul">
                                    <i class="bi bi-people"></i>
                                </div>
                                <span class="etiqueta-stat">Pacientes activos</span>
                            </div>
                            <!-- Mostramos dinámicamente el total de la base de datos -->
                            <h3 class="valor-stat"><?php echo $total_pacientes; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- CONTENIDO EN DOS COLUMNAS -->
                <div class="row g-4 align-items-start">
                    
                    <!-- COLUMNA IZQUIERDA -->
                    <div class="col-lg-8">
                        
                        <!-- Recent Patients -->
                        <div class="tarjeta-dashboard contenedor-seccion">
                            <div class="encabezado-seccion">
                                <div>
                                    <h5 class="titulo-seccion-principal">Pacientes recientes</h5>
                                    <small class="descripcion-seccion">Acceso rápido a los pacientes que revisó más recientemente.</small>
                                </div>
                                <a href="#" class="enlace-accion">Ver todos</a>
                            </div>

                            <div class="lista-elementos">
                                <?php if ($resultado_recientes && $resultado_recientes->num_rows > 0): ?>
                                    <?php while($paciente = $resultado_recientes->fetch_assoc()): ?>
                                        <div class="elemento-item">
                                            <div class="info-item-izq">
                                                <div>
                                                    <!-- Mostramos el nombre y apellido real del paciente -->
                                                    <h6 class="nombre-item"><?php echo htmlspecialchars($paciente['primer_nombre'] . ' ' . $paciente['segundo_nombre'] . ' ' . $paciente['primer_apellido'] . ' ' . $paciente['segundo_apellido']); ?></h6>
                                                    <small class="subtitulo-item">Registrado el: <?php echo date('d/m/Y', strtotime($paciente['created_at'])); ?></small>
                                                </div>
                                            </div>
                                            <!-- Pasamos el ID del paciente por la URL para abrir su expediente -->
                                            <a href="ver_paciente.php?id=<?php echo $paciente['id_paciente']; ?>" class="boton-secundario text-decoration-none">Ver más</a>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted small text-center py-3 mb-0">No hay pacientes registrados recientemente.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA -->
                    <div class="col-lg-4">
                        
                        <!-- Vaccination Records Widget -->
                        <div class="tarjeta-dashboard contenedor-seccion">
                            <div class="encabezado-seccion mb-2">
                                <h5 class="titulo-seccion-principal mb-0">Registros de Vacunación</h5>
                                <a href="#" class="enlace-accion">Ver más</a>
                            </div>
                            <small class="descripcion-seccion d-block mb-3">Pacientes con inmunizaciones programadas o vencidas.</small>

                            <div class="lista-vacunas">
                                <div class="elemento-vacuna">
                                    <div class="info-vacuna">
                                        <div>
                                            <h6 class="nombre-vacuna">Sin alertas pendientes</h6>
                                            <small class="texto-alerta-normal">Al día</small>
                                        </div>
                                    </div>
                                    <button class="boton-revision">Review</button>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </main>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>