<?php
session_start();
include __DIR__ . '/templates/header.php';

$seccion_activa = 'inicio';
include('procedures/bddConexion.php');

// 2. Consultar el total de pacientes activos para la tarjeta de estadística
$sql_total = "SELECT COUNT(*) as total FROM pacientes WHERE activo = 1";
$resultado_total = $mysqli->query($sql_total);
$fila_total = $resultado_total->fetch_assoc();
$total_pacientes = $fila_total['total'] ?? 0;

// 3. Consultar los pacientes más recientes para la lista de la izquierda
$sql_recientes = "SELECT id_paciente, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, created_at FROM pacientes ORDER BY created_at DESC LIMIT 5";
$resultado_recientes = $mysqli->query($sql_recientes);
?>


<body class="cuerpo-dashboard">

    <div class="contenedor-fluid-personalizado">
        <div class="fila-principal">
            
            <!-- BARRA LATERAL (SIDEBAR) -->
            <?php include __DIR__ . '/templates/sidebar.php'; ?>

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
                                                    <p class="nombre-item"><?php echo htmlspecialchars($paciente['primer_nombre'] . ' ' . $paciente['segundo_nombre'] . ' ' . $paciente['primer_apellido'] . ' ' . $paciente['segundo_apellido']); ?></p>
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
                                    <button class="boton-revision">Revisar</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_SESSION['swal_message'])): ?>
    <script>
        Swal.fire(<?= json_encode($_SESSION['swal_message'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>);
    </script>
    <?php unset($_SESSION['swal_message']); endif; ?>
</body>
</html>