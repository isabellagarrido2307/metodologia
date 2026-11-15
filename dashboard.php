<?php
session_start();

$seccion_activa = 'inicio';

include __DIR__ . '/templates/header.php';
include('procedures/bddConexion.php');
include('procedures/utilidades.php');

// Total de pacientes activos
$resultado_total = $mysqli->query("SELECT COUNT(*) AS total FROM pacientes WHERE activo = 1");
$total_pacientes = (int) ($resultado_total->fetch_assoc()['total'] ?? 0);

// Pacientes más recientes
$resultado_recientes = $mysqli->query("SELECT id_paciente, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, created_at
                                       FROM pacientes ORDER BY created_at DESC LIMIT 5");

// Estadísticas clínicas
$tiene_consultas = tabla_existe($mysqli, 'consultas');
$tiene_vacunas = tabla_existe($mysqli, 'vacunas');
$total_consultas = 0;
$total_vacunas = 0;
$vacunas_por_revisar = 0;
$alertas_vacunas = [];

if ($tiene_consultas) {
    $total_consultas = (int) ($mysqli->query("SELECT COUNT(*) AS total FROM consultas")->fetch_assoc()['total'] ?? 0);
}

if ($tiene_vacunas) {
    $totales_vacunas = $mysqli->query("SELECT COUNT(*) AS total,
                                              SUM(proxima_dosis <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)) AS por_revisar
                                       FROM vacunas")->fetch_assoc();
    $total_vacunas = (int) ($totales_vacunas['total'] ?? 0);
    $vacunas_por_revisar = (int) ($totales_vacunas['por_revisar'] ?? 0);

    $resultado_alertas = $mysqli->query("SELECT v.*, p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido
                                         FROM vacunas v
                                         INNER JOIN pacientes p ON p.id_paciente = v.id_paciente
                                         WHERE v.proxima_dosis IS NOT NULL
                                           AND v.proxima_dosis <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                         ORDER BY v.proxima_dosis ASC
                                         LIMIT 3");

    if ($resultado_alertas) {
        while ($fila_alerta = $resultado_alertas->fetch_assoc()) {
            $alertas_vacunas[] = $fila_alerta;
        }
    }
}
?>

<body class="cuerpo-dashboard">

    <div class="contenedor-fluid-personalizado">
        <div class="fila-principal">

            <!-- BARRA LATERAL (SIDEBAR) -->
            <?php include __DIR__ . '/templates/sidebar.php'; ?>

            <!-- CONTENIDO PRINCIPAL -->
            <main class="col-md-9 col-lg-10 ms-sm-auto area-contenido">

                <!-- Cabecera Superior -->
                <header class="cabecera-principal flex-wrap gap-3">
                    <div>
                        <h2 class="titulo-pagina">Inicio</h2>
                        <p class="subtitulo-pagina">Busque pacientes, revise registros y agregue información desde un solo lugar.</p>
                    </div>
                    <div class="acciones-cabecera">
                        <!-- Barra de Búsqueda -->
                        <form method="GET" action="pacientes.php">
                            <div class="barra-busqueda">
                                <span class="icono-busqueda"><i class="bi bi-search"></i></span>
                                <input type="text" name="busqueda" class="input-busqueda" placeholder="Ingrese nombre del paciente">
                            </div>
                        </form>
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
                            <h3 class="valor-stat"><?php echo $total_pacientes; ?></h3>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-azul">
                                    <i class="bi bi-journal-medical"></i>
                                </div>
                                <span class="etiqueta-stat">Consultas registradas</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $total_consultas; ?></h3>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-azul">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <span class="etiqueta-stat">Dosis registradas</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $total_vacunas; ?></h3>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat <?php echo $vacunas_por_revisar > 0 ? 'icono-amarillo' : 'icono-azul'; ?>">
                                    <i class="bi bi-calendar2-check"></i>
                                </div>
                                <span class="etiqueta-stat">Vacunas por revisar</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $vacunas_por_revisar; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- CONTENIDO EN DOS COLUMNAS -->
                <div class="row g-4 align-items-start">

                    <!-- COLUMNA IZQUIERDA -->
                    <div class="col-lg-8">

                        <!-- Pacientes recientes -->
                        <div class="tarjeta-dashboard contenedor-seccion">
                            <div class="encabezado-seccion">
                                <div>
                                    <h5 class="titulo-seccion-principal">Pacientes recientes</h5>
                                    <small class="descripcion-seccion">Acceso rápido a los pacientes que revisó más recientemente.</small>
                                </div>
                                <a href="pacientes.php" class="enlace-accion">Ver todos</a>
                            </div>

                            <div class="lista-elementos">
                                <?php if ($resultado_recientes && $resultado_recientes->num_rows > 0): ?>
                                    <?php while ($paciente = $resultado_recientes->fetch_assoc()): ?>
                                        <div class="elemento-item">
                                            <div class="info-item-izq">
                                                <div>
                                                    <p class="nombre-item"><?php echo htmlspecialchars(nombre_paciente($paciente)); ?></p>
                                                    <small class="subtitulo-item">Registrado el: <?php echo date('d/m/Y', strtotime($paciente['created_at'])); ?></small>
                                                </div>
                                            </div>
                                            <a href="ver_paciente.php?id=<?php echo $paciente['id_paciente']; ?>" class="boton-secundario text-decoration-none">Ver más</a>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-muted small text-center py-3 mb-0">No hay pacientes registrados recientemente.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Accesos rápidos -->
                        <div class="tarjeta-dashboard contenedor-seccion">
                            <div class="encabezado-seccion">
                                <div>
                                    <h5 class="titulo-seccion-principal">Accesos rápidos</h5>
                                    <small class="descripcion-seccion">Las tareas más frecuentes del día a día.</small>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <a href="nuevo_paciente.php" class="enlace-acceso-rapido">
                                        <div class="info-acceso">
                                            <div class="icono-acceso fondo-azul"><i class="bi bi-person-plus"></i></div>
                                            <div>
                                                <span class="titulo-acceso">Registrar paciente</span>
                                                <span class="subtitulo-acceso">Crear un nuevo expediente</span>
                                            </div>
                                        </div>
                                        <i class="bi bi-chevron-right flecha-acceso"></i>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a href="historiales.php" class="enlace-acceso-rapido">
                                        <div class="info-acceso">
                                            <div class="icono-acceso fondo-cian"><i class="bi bi-journal-medical"></i></div>
                                            <div>
                                                <span class="titulo-acceso">Nueva consulta</span>
                                                <span class="subtitulo-acceso">Documentar una atención</span>
                                            </div>
                                        </div>
                                        <i class="bi bi-chevron-right flecha-acceso"></i>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a href="vacunacion.php" class="enlace-acceso-rapido">
                                        <div class="info-acceso">
                                            <div class="icono-acceso fondo-amarillo"><i class="bi bi-shield-plus"></i></div>
                                            <div>
                                                <span class="titulo-acceso">Registrar vacuna</span>
                                                <span class="subtitulo-acceso">Actualizar el esquema</span>
                                            </div>
                                        </div>
                                        <i class="bi bi-chevron-right flecha-acceso"></i>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a href="configuracion.php" class="enlace-acceso-rapido">
                                        <div class="info-acceso">
                                            <div class="icono-acceso fondo-verde"><i class="bi bi-gear"></i></div>
                                            <div>
                                                <span class="titulo-acceso">Configuración</span>
                                                <span class="subtitulo-acceso">Datos y seguridad</span>
                                            </div>
                                        </div>
                                        <i class="bi bi-chevron-right flecha-acceso"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA DERECHA -->
                    <div class="col-lg-4">

                        <!-- Registros de Vacunación -->
                        <div class="tarjeta-dashboard contenedor-seccion">
                            <div class="encabezado-seccion mb-2">
                                <h5 class="titulo-seccion-principal mb-0">Registros de Vacunación</h5>
                                <a href="vacunacion.php" class="enlace-accion">Ver más</a>
                            </div>
                            <small class="descripcion-seccion d-block mb-3">Pacientes con inmunizaciones programadas o vencidas.</small>

                            <div class="lista-vacunas">
                                <?php if (!empty($alertas_vacunas)): ?>
                                    <?php foreach ($alertas_vacunas as $alerta): ?>
                                        <?php $es_critica = strtotime($alerta['proxima_dosis']) < strtotime(date('Y-m-d')); ?>
                                        <div class="elemento-vacuna">
                                            <div class="info-vacuna">
                                                <div>
                                                    <h6 class="nombre-vacuna"><?php echo htmlspecialchars(nombre_paciente($alerta)); ?></h6>
                                                    <small class="<?php echo $es_critica ? 'texto-alerta-critica' : 'texto-alerta-normal'; ?>">
                                                        <?php echo htmlspecialchars($alerta['nombre_vacuna']); ?> ·
                                                        <?php echo $es_critica ? 'Venció el ' : 'Próxima dosis: '; ?>
                                                        <?php echo htmlspecialchars(formatear_fecha($alerta['proxima_dosis'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <a href="ver_paciente.php?id=<?php echo (int) $alerta['id_paciente']; ?>#vacunas" class="boton-revision text-decoration-none <?php echo $es_critica ? 'boton-revision-critica' : ''; ?>">
                                                Revisar
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="elemento-vacuna">
                                        <div class="info-vacuna">
                                            <div>
                                                <h6 class="nombre-vacuna">Sin alertas pendientes</h6>
                                                <small class="texto-alerta-normal">Al día</small>
                                            </div>
                                        </div>
                                        <a href="vacunacion.php" class="boton-revision text-decoration-none">Revisar</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Resumen del consultorio -->
                        <div class="tarjeta-dashboard contenedor-seccion">
                            <h5 class="titulo-seccion-principal mb-3">Resumen del consultorio</h5>
                            <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                                <span class="descripcion-seccion">Pacientes activos</span>
                                <strong><?php echo $total_pacientes; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                                <span class="descripcion-seccion">Consultas registradas</span>
                                <strong><?php echo $total_consultas; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                                <span class="descripcion-seccion">Dosis registradas</span>
                                <strong><?php echo $total_vacunas; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="descripcion-seccion">Por revisar</span>
                                <strong class="<?php echo $vacunas_por_revisar > 0 ? 'text-warning-emphasis' : ''; ?>"><?php echo $vacunas_por_revisar; ?></strong>
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
    <?php mostrar_alerta_swal(); ?>
</body>

</html>
