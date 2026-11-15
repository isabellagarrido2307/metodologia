<?php
session_start();
include __DIR__ . '/templates/header.php';
$seccion_activa = 'pacientes';
include('procedures/bddConexion.php');
include('procedures/utilidades.php');

asegurar_columna_antecedentes($mysqli);

// 1. Obtener parámetros de ordenamiento / filtro de los combobox
$orden_fecha = $_GET['orden_fecha'] ?? 'DESC'; 
$orden_nombre = $_GET['orden_nombre'] ?? '';   
$busqueda = $_GET['busqueda'] ?? '';

// 2. Construir la consulta SQL dinámica
$sql = "SELECT id_paciente, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, fecha_nacimiento, antecedentes, created_at FROM pacientes WHERE activo = 1";

if (!empty($busqueda)) {
    $busqueda_escape = $mysqli->real_escape_string($busqueda);
    $sql .= " AND (primer_nombre LIKE '%$busqueda_escape%' OR primer_apellido LIKE '%$busqueda_escape%')";
}

if (!empty($orden_nombre)) {
    $orden_nombre_clean = ($orden_nombre === 'DESC') ? 'DESC' : 'ASC';
    $sql .= " ORDER BY primer_nombre $orden_nombre_clean, primer_apellido $orden_nombre_clean";
} else {
    $orden_fecha_clean = ($orden_fecha === 'ASC') ? 'ASC' : 'DESC';
    $sql .= " ORDER BY created_at $orden_fecha_clean";
}

$resultado_pacientes = $mysqli->query($sql);
?>

<!-- Corrección puntual para que la tabla no se rompa por clases heredadas -->
<style>
    tr.elemento-item {
        display: table-row !important;
    }
    tr.elemento-item td {
        vertical-align: middle !important;
    }
</style>

<body class="cuerpo-dashboard">

    <div class="contenedor-fluid-personalizado">
        <div class="fila-principal">
            
            <!-- BARRA LATERAL -->
            <?php include __DIR__ . '/templates/sidebar.php'; ?>

            <!-- CONTENIDO PRINCIPAL -->
            <main class="col-md-9 col-lg-10 ms-sm-auto area-contenido">
                
                <!-- CABECERA -->
                <header class="cabecera-principal flex-wrap gap-3">
                    <div>
                        <h2 class="titulo-pagina">Pacientes</h2>
                        <p class="subtitulo-pagina">Gestione la lista de pacientes registrados y consulte sus expedientes.</p>
                    </div>
                    <div class="acciones-cabecera">
                        <form method="GET" action="pacientes.php" class="d-flex align-items-center">
                            <input type="hidden" name="orden_fecha" value="<?php echo htmlspecialchars($orden_fecha); ?>">
                            <input type="hidden" name="orden_nombre" value="<?php echo htmlspecialchars($orden_nombre); ?>">
                            
                            <div class="barra-busqueda">
                                <span class="icono-busqueda"><i class="bi bi-search"></i></span>
                                <input type="text" name="busqueda" class="input-busqueda" placeholder="Buscar paciente por nombre..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            </div>
                        </form>
                    </div>
                </header>

                <!-- SECCIÓN AÑADIR PACIENTE -->
                <div class="tarjeta-dashboard contenedor-seccion mb-4 h-auto" style="height: auto !important;">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <div>
                            <h5 class="titulo-seccion-principal mb-1">Registrar Nuevo Paciente</h5>
                            <p class="descripcion-seccion mb-0">Añada un paciente pediátrico al sistema para iniciar su expediente médico y esquema de vacunación.</p>
                        </div>
                        <a href="nuevo_paciente.php" class="boton-secundario text-decoration-none d-inline-flex align-items-center justify-content-center gap-2 py-2 px-4 whitespace-nowrap">
                            <i class="bi bi-person-plus-fill"></i> Añadir Paciente
                        </a>
                    </div>
                </div>

                <!-- TABLA DE PACIENTES -->
                  <div class="tarjeta-dashboard contenedor-seccion h-auto" style="height: auto !important;">
                    
                    <div class="encabezado-seccion flex-wrap gap-3 pb-3 mb-3 border-bottom border-light-subtle">
                        <div>
                            <h5 class="titulo-seccion-principal">Listado de Pacientes</h5>
                            <small class="descripcion-seccion">Mostrando pacientes activos en la base de datos.</small>
                        </div>

                        <!-- Filtros (Combobox) -->
                        <form method="GET" action="pacientes.php" class="d-flex flex-wrap align-items-center gap-2">
                            <?php if (!empty($busqueda)): ?>
                                <input type="hidden" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>">
                            <?php endif; ?>

                            <select name="orden_nombre" class="form-select form-select-sm border-0 shadow-none" style="background-color: #eaf4fe; color: #4a80bc; font-weight: 600; width: auto; cursor: pointer;" onchange="this.form.submit()">
                                <option value="">Ordenar por Nombre...</option>
                                <option value="ASC" <?php echo ($orden_nombre === 'ASC') ? 'selected' : ''; ?>>Nombre (A - Z)</option>
                                <option value="DESC" <?php echo ($orden_nombre === 'DESC') ? 'selected' : ''; ?>>Nombre (Z - A)</option>
                            </select>

                            <select name="orden_fecha" class="form-select form-select-sm border-0 shadow-none" style="background-color: #eaf4fe; color: #4a80bc; font-weight: 600; width: auto; cursor: pointer;" onchange="this.form.submit()">
                                <option value="DESC" <?php echo ($orden_fecha === 'DESC') ? 'selected' : ''; ?>>Más recientes primero</option>
                                <option value="ASC" <?php echo ($orden_fecha === 'ASC') ? 'selected' : ''; ?>>Más antiguos primero</option>
                            </select>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr style="border-bottom: 2px solid #eaf4fe; color: #64748b; font-size: 0.85rem;">
                                    <th scope="col" class="pb-3 fw-bold">PACIENTE</th>
                                    <th scope="col" class="pb-3 fw-bold">FECHA NACIMIENTO</th>
                                    <th scope="col" class="pb-3 fw-bold">FECHA REGISTRO</th>
                                    <th scope="col" class="pb-3 fw-bold text-end">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado_pacientes && $resultado_pacientes->num_rows > 0): ?>
                                    <?php while($paciente = $resultado_pacientes->fetch_assoc()): ?>
                                        <tr class="elemento-item" style="border-bottom: 1px solid #f9fcfe;">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icono-stat icono-azul me-3" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="nombre-item mb-0">
                                                            <?php echo htmlspecialchars($paciente['primer_nombre'] . ' ' . $paciente['segundo_nombre'] . ' ' . $paciente['primer_apellido'] . ' ' . $paciente['segundo_apellido']); ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="subtitulo-item">
                                                    <?php echo !empty($paciente['fecha_nacimiento']) ? date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) : 'No registrada'; ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="subtitulo-item">
                                                    <?php echo date('d/m/Y', strtotime($paciente['created_at'])); ?>
                                                </span>
                                            </td>

                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center justify-content-end gap-2 flex-wrap">
                                                    <button type="button" 
                                                            class="boton-secundario text-decoration-none border-0 d-inline-flex align-items-center justify-content-center gap-1 py-1 px-3 btn-abrir-antecedentes"
                                                            data-id="<?php echo $paciente['id_paciente']; ?>"
                                                            data-nombre="<?php echo htmlspecialchars(nombre_paciente($paciente), ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-antecedentes="<?php echo htmlspecialchars($paciente['antecedentes'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-redirect="pacientes.php"
                                                            title="Gestionar antecedentes y datos de emergencia"
                                                            style="font-size: 0.8rem; font-weight: 600;">
                                                        <i class="bi bi-clipboard2-pulse"></i> Antecedentes
                                                    </button>
                                                    <a href="ver_paciente.php?id=<?php echo $paciente['id_paciente']; ?>" 
                                                       class="boton-secundario text-decoration-none d-inline-flex align-items-center justify-content-center gap-1 py-1 px-3"
                                                       style="font-size: 0.8rem; font-weight: 600;">
                                                        <i class="bi bi-folder2-open"></i> Ver Expediente
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">
                                            <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                                            No se encontraron pacientes registrados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

            </main>

        </div>
    </div>

    <!-- Modal de Gestión de Antecedentes -->
    <?php include __DIR__ . '/templates/modal_antecedentes.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php mostrar_alerta_swal(); ?>
</body>
</html>