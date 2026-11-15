<?php
session_start();

$seccion_activa = 'vacunacion';

include __DIR__ . '/templates/header.php';
include('procedures/bddConexion.php');
include('procedures/utilidades.php');

$busqueda = trim($_GET['busqueda'] ?? '');
$tiene_vacunas = tabla_existe($mysqli, 'vacunas');

$vacunas = [];
$total_dosis = 0;
$pacientes_vacunados = 0;
$proximas_dosis = 0;
$dosis_vencidas = 0;

if ($tiene_vacunas) {
    $sql = "SELECT v.*, p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido
            FROM vacunas v
            INNER JOIN pacientes p ON p.id_paciente = v.id_paciente";

    $tipos = '';
    $parametros = [];

    if ($busqueda !== '') {
        $like = '%' . $busqueda . '%';
        $sql .= " WHERE (p.primer_nombre LIKE ? OR p.segundo_nombre LIKE ?
                        OR p.primer_apellido LIKE ? OR p.segundo_apellido LIKE ?
                        OR v.nombre_vacuna LIKE ?)";
        $tipos = 'sssss';
        $parametros = array_fill(0, 5, $like);
    }

    $sql .= " ORDER BY v.fecha_aplicacion DESC, v.id_vacuna DESC LIMIT 200";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$parametros);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            $vacunas[] = $fila;
        }
        $stmt->close();
    }

    $totales = $mysqli->query("SELECT COUNT(*) AS total,
                                      COUNT(DISTINCT id_paciente) AS pacientes,
                                      SUM(proxima_dosis BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)) AS proximas,
                                      SUM(proxima_dosis < CURDATE()) AS vencidas
                               FROM vacunas")->fetch_assoc();
    $total_dosis = (int) ($totales['total'] ?? 0);
    $pacientes_vacunados = (int) ($totales['pacientes'] ?? 0);
    $proximas_dosis = (int) ($totales['proximas'] ?? 0);
    $dosis_vencidas = (int) ($totales['vencidas'] ?? 0);
}

$pacientes_selector = $mysqli->query("SELECT id_paciente, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido
                                      FROM pacientes WHERE activo = 1
                                      ORDER BY primer_nombre ASC, primer_apellido ASC");

$esquema_vacunacion = [
    ['BCG', 'Recién nacido', 'Protege contra tuberculosis'],
    ['Hepatitis B', 'Recién nacido', 'Primera dosis al nacer'],
    ['Pentavalente', '2, 4 y 6 meses', 'Difteria, tétanos, tosferina, Hib y hepatitis B'],
    ['Polio (VPI/VPO)', '2, 4 y 6 meses', 'Prevención de la poliomielitis'],
    ['Rotavirus', '2 y 4 meses', 'Protección contra diarreas graves'],
    ['Neumococo', '2, 4 y 12 meses', 'Infecciones por neumococo'],
    ['SRP (Triple viral)', '12 meses', 'Sarampión, rubéola y parotiditis'],
    ['Varicela', '12 a 15 meses', 'Prevención de la varicela'],
    ['Hepatitis A', '12 a 18 meses', 'Protección hepática'],
    ['DPT / Tdap', '18 meses y refuerzos', 'Refuerzo difteria, tétanos y tosferina'],
    ['VPH', '9 a 14 años', 'Virus del papiloma humano'],
    ['Influenza', 'Anual', 'Vacunación estacional'],
];
?>

<body class="cuerpo-dashboard">

    <div class="contenedor-fluid-personalizado">
        <div class="fila-principal">

            <!-- BARRA LATERAL -->
            <?php include __DIR__ . '/templates/sidebar.php'; ?>

            <!-- CONTENIDO PRINCIPAL -->
            <main class="col-md-9 col-lg-10 ms-sm-auto area-contenido">

                <header class="cabecera-principal flex-wrap gap-3">
                    <div>
                        <h2 class="titulo-pagina">Registros de Vacunación</h2>
                        <p class="subtitulo-pagina">Controle las dosis aplicadas y las próximas fechas de vacunación de sus pacientes.</p>
                    </div>
                    <div class="acciones-cabecera">
                        <form method="GET" action="vacunacion.php">
                            <div class="barra-busqueda">
                                <span class="icono-busqueda"><i class="bi bi-search"></i></span>
                                <input type="text" name="busqueda" class="input-busqueda" placeholder="Buscar paciente o vacuna..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                        </form>
                        <button type="button" class="boton-revision py-2 px-3 border-0 d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalRegistrarVacuna" <?php echo $pacientes_selector && $pacientes_selector->num_rows > 0 ? '' : 'disabled'; ?>>
                            <i class="bi bi-shield-plus"></i> Registrar dosis
                        </button>
                    </div>
                </header>

                <!-- TARJETAS DE RESUMEN -->
                <div class="row g-3 rejilla-estadisticas">
                    <div class="col-xl-3 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-azul"><i class="bi bi-shield-check"></i></div>
                                <span class="etiqueta-stat">Dosis registradas</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $total_dosis; ?></h3>
                            <span class="detalle-stat">Aplicaciones guardadas en total.</span>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-azul"><i class="bi bi-people"></i></div>
                                <span class="etiqueta-stat">Pacientes vacunados</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $pacientes_vacunados; ?></h3>
                            <span class="detalle-stat">Pacientes con al menos una dosis.</span>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-amarillo"><i class="bi bi-calendar-event"></i></div>
                                <span class="etiqueta-stat">Próximas dosis</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $proximas_dosis; ?></h3>
                            <span class="detalle-stat">Programadas para los próximos 30 días.</span>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-rojo"><i class="bi bi-exclamation-triangle"></i></div>
                                <span class="etiqueta-stat">Dosis vencidas</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $dosis_vencidas; ?></h3>
                            <span class="detalle-stat">Refuerzos con fecha ya pasada.</span>
                        </div>
                    </div>
                </div>

                <?php if (!$tiene_vacunas): ?>
                    <div class="tarjeta-dashboard contenedor-seccion h-auto mb-4">
                        <div class="text-center py-4">
                            <i class="bi bi-database-exclamation fs-2 text-secondary d-block mb-2"></i>
                            <h5 class="titulo-seccion-principal">Falta habilitar el esquema clínico</h5>
                            <p class="descripcion-seccion mb-0">Ejecute el archivo <code>database/esquema_clinico.sql</code> en la base de datos <strong>mediportal</strong> para guardar los registros de vacunación.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- TABLA DE REGISTROS -->
                <div class="tarjeta-dashboard contenedor-seccion h-auto mb-4" style="height: auto !important;">
                    <div class="encabezado-seccion flex-wrap gap-3 pb-3 mb-3 border-bottom border-light-subtle">
                        <div>
                            <h5 class="titulo-seccion-principal">Dosis registradas</h5>
                            <small class="descripcion-seccion">
                                <?php echo $busqueda !== '' ? 'Resultados para “' . htmlspecialchars($busqueda) . '”.' : 'Historial de aplicaciones ordenado de la más reciente a la más antigua.'; ?>
                            </small>
                        </div>
                        <?php if ($busqueda !== ''): ?>
                            <a href="vacunacion.php" class="enlace-accion">Limpiar búsqueda</a>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr style="border-bottom: 2px solid #eaf4fe; color: #64748b; font-size: 0.85rem;">
                                    <th scope="col" class="pb-3 fw-bold">PACIENTE</th>
                                    <th scope="col" class="pb-3 fw-bold">VACUNA</th>
                                    <th scope="col" class="pb-3 fw-bold">DOSIS</th>
                                    <th scope="col" class="pb-3 fw-bold">APLICACIÓN</th>
                                    <th scope="col" class="pb-3 fw-bold">PRÓXIMA DOSIS</th>
                                    <th scope="col" class="pb-3 fw-bold">ESTADO</th>
                                    <th scope="col" class="pb-3 fw-bold text-end">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($vacunas)): ?>
                                    <?php foreach ($vacunas as $vacuna): ?>
                                        <?php $estado = estado_dosis($vacuna); ?>
                                        <tr class="elemento-item" style="border-bottom: 1px solid #f9fcfe; display: table-row;">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icono-stat icono-azul me-3" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                        <?php echo htmlspecialchars(iniciales_paciente($vacuna)); ?>
                                                    </div>
                                                    <h6 class="nombre-item mb-0"><?php echo htmlspecialchars(nombre_paciente($vacuna)); ?></h6>
                                                </div>
                                            </td>
                                            <td><span class="subtitulo-item"><?php echo htmlspecialchars($vacuna['nombre_vacuna']); ?></span></td>
                                            <td><span class="subtitulo-item"><?php echo htmlspecialchars($vacuna['dosis']); ?></span></td>
                                            <td><span class="subtitulo-item"><?php echo htmlspecialchars(formatear_fecha($vacuna['fecha_aplicacion'])); ?></span></td>
                                            <td><span class="subtitulo-item"><?php echo htmlspecialchars(formatear_fecha($vacuna['proxima_dosis'], 'd/m/Y', 'No programada')); ?></span></td>
                                            <td>
                                                <span class="badge rounded-pill <?php echo htmlspecialchars($estado['clase']); ?> px-3 py-2"><?php echo htmlspecialchars($estado['texto']); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <a href="ver_paciente.php?id=<?php echo (int) $vacuna['id_paciente']; ?>#vacunas" class="boton-secundario text-decoration-none d-inline-block">
                                                    Ver expediente
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted small">
                                            <i class="bi bi-shield-x fs-3 d-block mb-2 text-secondary"></i>
                                            <?php echo $busqueda !== '' ? 'No se encontraron registros con ese criterio.' : 'Todavía no hay dosis registradas. Use el botón “Registrar dosis” para crear el primero.'; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ESQUEMA DE REFERENCIA -->
                <div class="tarjeta-dashboard contenedor-seccion h-auto" style="height: auto !important;">
                    <div class="encabezado-seccion mb-3">
                        <div>
                            <h5 class="titulo-seccion-principal">Esquema de vacunación pediátrica</h5>
                            <small class="descripcion-seccion">Referencia rápida de las vacunas más comunes y su edad recomendada.</small>
                        </div>
                        <i class="bi bi-info-circle text-secondary"></i>
                    </div>

                    <div class="row g-3">
                        <?php foreach ($esquema_vacunacion as $item): ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="p-3 rounded-3 h-100" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <strong class="text-dark" style="font-size: 0.9rem;"><?php echo htmlspecialchars($item[0]); ?></strong>
                                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis"><?php echo htmlspecialchars($item[1]); ?></span>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.78rem;"><?php echo htmlspecialchars($item[2]); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- MODAL REGISTRAR VACUNA -->
    <div class="modal fade" id="modalRegistrarVacuna" tabindex="-1" aria-labelledby="tituloRegistrarVacuna" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <form action="procedures/guardarVacuna.php" method="POST">
                    <div class="modal-header border-light-subtle">
                        <div>
                            <h5 class="modal-title fw-bold" id="tituloRegistrarVacuna">Registrar dosis</h5>
                            <small class="text-muted">Guarde la aplicación de una vacuna a un paciente.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="origen" value="vacunacion">

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="vacuna_paciente" class="form-label fw-semibold text-secondary small">Paciente</label>
                                <select class="form-select" id="vacuna_paciente" name="id_paciente" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                    <option value="">Seleccione un paciente...</option>
                                    <?php if ($pacientes_selector): ?>
                                        <?php $pacientes_selector->data_seek(0); ?>
                                        <?php while ($paciente_opcion = $pacientes_selector->fetch_assoc()): ?>
                                            <option value="<?php echo (int) $paciente_opcion['id_paciente']; ?>"><?php echo htmlspecialchars(nombre_paciente($paciente_opcion)); ?></option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label for="vacuna_nombre" class="form-label fw-semibold text-secondary small">Vacuna</label>
                                <select class="form-select" id="vacuna_nombre" name="nombre_vacuna" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($esquema_vacunacion as $item): ?>
                                        <option value="<?php echo htmlspecialchars($item[0]); ?>"><?php echo htmlspecialchars($item[0]); ?></option>
                                    <?php endforeach; ?>
                                    <option value="Otra">Otra</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="vacuna_dosis" class="form-label fw-semibold text-secondary small">Dosis</label>
                                <select class="form-select" id="vacuna_dosis" name="dosis" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                    <option value="">Seleccione...</option>
                                    <option value="1ra dosis">1ra dosis</option>
                                    <option value="2da dosis">2da dosis</option>
                                    <option value="3ra dosis">3ra dosis</option>
                                    <option value="4ta dosis">4ta dosis</option>
                                    <option value="Refuerzo">Refuerzo</option>
                                    <option value="Dosis única">Dosis única</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="vacuna_fecha" class="form-label fw-semibold text-secondary small">Fecha de aplicación</label>
                                <input type="date" class="form-control" id="vacuna_fecha" name="fecha_aplicacion" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <div class="col-md-4">
                                <label for="vacuna_proxima" class="form-label fw-semibold text-secondary small">Próxima dosis (opcional)</label>
                                <input type="date" class="form-control" id="vacuna_proxima" name="proxima_dosis" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>

                            <div class="col-md-4">
                                <label for="vacuna_lote" class="form-label fw-semibold text-secondary small">Lote (opcional)</label>
                                <input type="text" class="form-control" id="vacuna_lote" name="lote" placeholder="Ej. L-2026-04" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <div class="col-md-8">
                                <label for="vacuna_observaciones" class="form-label fw-semibold text-secondary small">Observaciones (opcional)</label>
                                <input type="text" class="form-control" id="vacuna_observaciones" name="observaciones" placeholder="Reacciones, recomendaciones..." style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-light-subtle">
                        <button type="button" class="btn btn-light border-0" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="boton-revision border-0 px-4">Guardar dosis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php mostrar_alerta_swal(); ?>
</body>

</html>
