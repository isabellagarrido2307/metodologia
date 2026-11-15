<?php
session_start();

$seccion_activa = 'historiales';

include __DIR__ . '/templates/header.php';
include('procedures/bddConexion.php');
include('procedures/utilidades.php');

$busqueda = trim($_GET['busqueda'] ?? '');
$tiene_consultas = tabla_existe($mysqli, 'consultas');

$consultas = [];
$total_consultas = 0;
$pacientes_atendidos = 0;
$consultas_mes = 0;

if ($tiene_consultas) {
    $sql = "SELECT c.*,
                   p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido,
                   ped.nombres AS ped_nombres, ped.apellidos AS ped_apellidos
            FROM consultas c
            INNER JOIN pacientes p ON p.id_paciente = c.id_paciente
            LEFT JOIN pediatras ped ON ped.id_pediatra = c.id_pediatra";

    $tipos = '';
    $parametros = [];

    if ($busqueda !== '') {
        $like = '%' . $busqueda . '%';
        $sql .= " WHERE (p.primer_nombre LIKE ? OR p.segundo_nombre LIKE ?
                        OR p.primer_apellido LIKE ? OR p.segundo_apellido LIKE ?
                        OR c.motivo_consulta LIKE ? OR c.diagnostico LIKE ?)";
        $tipos = 'ssssss';
        $parametros = array_fill(0, 6, $like);
    }

    $sql .= " ORDER BY c.fecha_consulta DESC LIMIT 200";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$parametros);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            $consultas[] = $fila;
        }
        $stmt->close();
    }

    $totales = $mysqli->query("SELECT COUNT(*) AS total,
                                      COUNT(DISTINCT id_paciente) AS pacientes,
                                      SUM(DATE_FORMAT(fecha_consulta, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS del_mes
                               FROM consultas")->fetch_assoc();
    $total_consultas = (int) ($totales['total'] ?? 0);
    $pacientes_atendidos = (int) ($totales['pacientes'] ?? 0);
    $consultas_mes = (int) ($totales['del_mes'] ?? 0);
}

$pacientes_selector = $mysqli->query("SELECT id_paciente, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido
                                      FROM pacientes WHERE activo = 1
                                      ORDER BY primer_nombre ASC, primer_apellido ASC");
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
                        <h2 class="titulo-pagina">Historiales clínicos</h2>
                        <p class="subtitulo-pagina">Consulte las consultas registradas y abra el expediente de cada paciente.</p>
                    </div>
                    <div class="acciones-cabecera">
                        <form method="GET" action="historiales.php">
                            <div class="barra-busqueda">
                                <span class="icono-busqueda"><i class="bi bi-search"></i></span>
                                <input type="text" name="busqueda" class="input-busqueda" placeholder="Buscar por paciente, motivo o diagnóstico..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                        </form>
                        <button type="button" class="boton-revision py-2 px-3 border-0 d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalNuevaConsulta" <?php echo $pacientes_selector && $pacientes_selector->num_rows > 0 ? '' : 'disabled'; ?>>
                            <i class="bi bi-clipboard2-plus"></i> Nueva consulta
                        </button>
                    </div>
                </header>

                <!-- TARJETAS DE RESUMEN -->
                <div class="row g-3 rejilla-estadisticas">
                    <div class="col-xl-4 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-azul"><i class="bi bi-journal-medical"></i></div>
                                <span class="etiqueta-stat">Consultas registradas</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $total_consultas; ?></h3>
                            <span class="detalle-stat">Total de atenciones en el sistema.</span>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-azul"><i class="bi bi-people"></i></div>
                                <span class="etiqueta-stat">Pacientes atendidos</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $pacientes_atendidos; ?></h3>
                            <span class="detalle-stat">Pacientes con al menos una consulta.</span>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="tarjeta-dashboard">
                            <div class="encabezado-tarjeta-stat">
                                <div class="icono-stat icono-azul"><i class="bi bi-calendar2-check"></i></div>
                                <span class="etiqueta-stat">Consultas este mes</span>
                            </div>
                            <h3 class="valor-stat"><?php echo $consultas_mes; ?></h3>
                            <span class="detalle-stat">Atenciones del mes en curso.</span>
                        </div>
                    </div>
                </div>

                <?php if (!$tiene_consultas): ?>
                    <div class="tarjeta-dashboard contenedor-seccion h-auto">
                        <div class="text-center py-4">
                            <i class="bi bi-database-exclamation fs-2 text-secondary d-block mb-2"></i>
                            <h5 class="titulo-seccion-principal">Falta habilitar el esquema clínico</h5>
                            <p class="descripcion-seccion mb-0">Ejecute el archivo <code>database/esquema_clinico.sql</code> en la base de datos <strong>mediportal</strong> para guardar consultas y vacunas.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tarjeta-dashboard contenedor-seccion h-auto" style="height: auto !important;">
                        <div class="encabezado-seccion flex-wrap gap-3 pb-3 mb-3 border-bottom border-light-subtle">
                            <div>
                                <h5 class="titulo-seccion-principal">Últimas consultas</h5>
                                <small class="descripcion-seccion">
                                    <?php echo $busqueda !== '' ? 'Resultados para “' . htmlspecialchars($busqueda) . '”.' : 'Historial de atenciones ordenado de la más reciente a la más antigua.'; ?>
                                </small>
                            </div>
                            <?php if ($busqueda !== ''): ?>
                                <a href="historiales.php" class="enlace-accion">Limpiar búsqueda</a>
                            <?php endif; ?>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="border-bottom: 2px solid #eaf4fe; color: #64748b; font-size: 0.85rem;">
                                        <th scope="col" class="pb-3 fw-bold">PACIENTE</th>
                                        <th scope="col" class="pb-3 fw-bold">FECHA</th>
                                        <th scope="col" class="pb-3 fw-bold">MOTIVO</th>
                                        <th scope="col" class="pb-3 fw-bold">DIAGNÓSTICO</th>
                                        <th scope="col" class="pb-3 fw-bold">PEDIATRA</th>
                                        <th scope="col" class="pb-3 fw-bold text-end">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($consultas)): ?>
                                        <?php foreach ($consultas as $consulta): ?>
                                            <tr class="elemento-item" style="border-bottom: 1px solid #f9fcfe; display: table-row;">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icono-stat icono-azul me-3" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                            <?php echo htmlspecialchars(iniciales_paciente($consulta)); ?>
                                                        </div>
                                                        <h6 class="nombre-item mb-0"><?php echo htmlspecialchars(nombre_paciente($consulta)); ?></h6>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="subtitulo-item"><?php echo htmlspecialchars(formatear_fecha($consulta['fecha_consulta'], 'd/m/Y')); ?></span><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars(formatear_fecha($consulta['fecha_consulta'], 'H:i')); ?></small>
                                                </td>
                                                <td><span class="subtitulo-item"><?php echo htmlspecialchars(resumen_texto($consulta['motivo_consulta'], 45)); ?></span></td>
                                                <td><span class="subtitulo-item"><?php echo htmlspecialchars(resumen_texto($consulta['diagnostico'], 45)); ?></span></td>
                                                <td>
                                                    <span class="subtitulo-item">
                                                        <?php
                                                        $nombre_doctor = nombre_pediatra($consulta);
                                                        echo $nombre_doctor !== '' ? 'Dr(a). ' . htmlspecialchars($nombre_doctor) : 'No asignado';
                                                        ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="ver_paciente.php?id=<?php echo (int) $consulta['id_paciente']; ?>" class="boton-secundario text-decoration-none d-inline-block">
                                                        Ver expediente
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted small">
                                                <i class="bi bi-journal-x fs-3 d-block mb-2 text-secondary"></i>
                                                <?php echo $busqueda !== '' ? 'No se encontraron consultas con ese criterio.' : 'Todavía no hay consultas registradas. Use el botón “Nueva consulta” para crear la primera.'; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <!-- MODAL NUEVA CONSULTA -->
    <div class="modal fade" id="modalNuevaConsulta" tabindex="-1" aria-labelledby="tituloNuevaConsulta" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <form action="procedures/guardarConsulta.php" method="POST">
                    <div class="modal-header border-light-subtle">
                        <div>
                            <h5 class="modal-title fw-bold" id="tituloNuevaConsulta">Nueva consulta</h5>
                            <small class="text-muted">Registre la atención en el historial del paciente.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="origen" value="historiales">

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="consulta_paciente" class="form-label fw-semibold text-secondary small">Paciente</label>
                                <select class="form-select" id="consulta_paciente" name="id_paciente" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
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
                                <label for="consulta_fecha" class="form-label fw-semibold text-secondary small">Fecha y hora</label>
                                <input type="datetime-local" class="form-control" id="consulta_fecha" name="fecha_consulta" value="<?php echo date('Y-m-d\TH:i'); ?>" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>

                            <div class="col-12">
                                <label for="consulta_motivo" class="form-label fw-semibold text-secondary small">Motivo de consulta</label>
                                <input type="text" class="form-control" id="consulta_motivo" name="motivo_consulta" placeholder="Ej. Control pediátrico, fiebre, tos..." required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>

                            <div class="col-md-4">
                                <label for="consulta_peso" class="form-label fw-semibold text-secondary small">Peso (kg)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="consulta_peso" name="peso" placeholder="Ej. 14.5" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <div class="col-md-4">
                                <label for="consulta_talla" class="form-label fw-semibold text-secondary small">Talla (cm)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="consulta_talla" name="talla" placeholder="Ej. 98" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <div class="col-md-4">
                                <label for="consulta_cc" class="form-label fw-semibold text-secondary small">Circunferencia cefálica (cm)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="consulta_cc" name="circunferencia_cefalica" placeholder="Ej. 48" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>

                            <div class="col-12">
                                <label for="consulta_examen" class="form-label fw-semibold text-secondary small">Examen físico</label>
                                <textarea class="form-control" id="consulta_examen" name="examen_fisico" rows="2" placeholder="Hallazgos del examen físico..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="consulta_diagnostico" class="form-label fw-semibold text-secondary small">Diagnóstico</label>
                                <textarea class="form-control" id="consulta_diagnostico" name="diagnostico" rows="3" placeholder="Diagnóstico clínico..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="consulta_tratamiento" class="form-label fw-semibold text-secondary small">Indicaciones y tratamiento</label>
                                <textarea class="form-control" id="consulta_tratamiento" name="tratamiento" rows="3" placeholder="Medicamentos, pautas y recomendaciones..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-light-subtle">
                        <button type="button" class="btn btn-light border-0" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="boton-revision border-0 px-4">Guardar consulta</button>
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
