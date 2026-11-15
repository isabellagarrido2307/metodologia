<?php
session_start();

$seccion_activa = 'pacientes';

include __DIR__ . '/templates/header.php';
include('procedures/bddConexion.php');
include('procedures/utilidades.php');

$id_paciente = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$paciente = null;
$consultas = [];
$vacunas = [];
$tiene_consultas = tabla_existe($mysqli, 'consultas');
$tiene_vacunas = tabla_existe($mysqli, 'vacunas');

if ($id_paciente) {
    $stmt = $mysqli->prepare('SELECT * FROM pacientes WHERE id_paciente = ? LIMIT 1');
    $stmt->bind_param('i', $id_paciente);
    $stmt->execute();
    $paciente = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($paciente && $tiene_consultas) {
    $stmt = $mysqli->prepare("SELECT c.*, ped.nombres AS ped_nombres, ped.apellidos AS ped_apellidos
                              FROM consultas c
                              LEFT JOIN pediatras ped ON ped.id_pediatra = c.id_pediatra
                              WHERE c.id_paciente = ?
                              ORDER BY c.fecha_consulta DESC");
    $stmt->bind_param('i', $id_paciente);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($fila = $resultado->fetch_assoc()) {
        $consultas[] = $fila;
    }
    $stmt->close();
}

if ($paciente && $tiene_vacunas) {
    $stmt = $mysqli->prepare('SELECT * FROM vacunas WHERE id_paciente = ? ORDER BY fecha_aplicacion DESC, id_vacuna DESC');
    $stmt->bind_param('i', $id_paciente);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($fila = $resultado->fetch_assoc()) {
        $vacunas[] = $fila;
    }
    $stmt->close();
}

$proxima_dosis = null;
foreach ($vacunas as $vacuna) {
    if (!empty($vacuna['proxima_dosis'])) {
        $proxima_dosis = $vacuna['proxima_dosis'];
        break;
    }
}
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
                        <a href="pacientes.php" class="enlace-accion d-inline-flex align-items-center gap-2 text-decoration-none fw-semibold mb-1">
                            <i class="bi bi-arrow-left"></i> Volver al listado de pacientes
                        </a>
                        <h2 class="titulo-pagina">Expediente del paciente</h2>
                        <p class="subtitulo-pagina">Consulte los datos personales, el historial de consultas y el esquema de vacunación.</p>
                    </div>
                    <?php if ($paciente): ?>
                        <div class="acciones-cabecera">
                            <a href="descargar_expediente.php?id=<?php echo $paciente['id_paciente']; ?>" target="_blank" class="boton-secundario py-2 px-3 border-0 d-inline-flex align-items-center gap-2 text-decoration-none" style="color: #dc2626; background-color: #fff1f2; border: 1px solid #fecaca; font-weight: 600;">
                                <i class="bi bi-file-earmark-pdf-fill"></i> Descargar PDF
                            </a>
                            <button type="button" class="boton-secundario py-2 px-3 border-0 d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalConsultaExpediente">
                                <i class="bi bi-clipboard2-plus"></i> Nueva consulta
                            </button>
                            <button type="button" class="boton-revision py-2 px-3 border-0 d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalVacunaExpediente">
                                <i class="bi bi-shield-plus"></i> Registrar vacuna
                            </button>
                        </div>
                    <?php endif; ?>
                </header>

                <?php if (!$paciente): ?>
                    <div class="tarjeta-dashboard contenedor-seccion h-auto">
                        <div class="text-center py-5">
                            <i class="bi bi-person-x fs-1 text-secondary d-block mb-3"></i>
                            <h4 class="fw-bold">Paciente no encontrado</h4>
                            <p class="descripcion-seccion mb-4">El expediente solicitado no existe o fue dado de baja del sistema.</p>
                            <a href="pacientes.php" class="boton-revision border-0 px-4 py-2 text-decoration-none">Ver listado de pacientes</a>
                        </div>
                    </div>
                <?php else: ?>

                    <div class="row g-4 align-items-start">
                        <!-- COLUMNA IZQUIERDA: FICHA DEL PACIENTE -->
                        <div class="col-lg-4">
                            <div class="tarjeta-dashboard contenedor-seccion h-auto text-center">
                                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                                     style="width: 82px; height: 82px; background-color: #eaf4fe; color: #4a80bc; font-size: 1.7rem; font-weight: 700;">
                                    <?php echo htmlspecialchars(iniciales_paciente($paciente)); ?>
                                </div>

                                <h4 class="fw-bold mb-2"><?php echo htmlspecialchars(nombre_paciente($paciente)); ?></h4>

                                <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-3 py-2"><?php echo htmlspecialchars(calcular_edad($paciente['fecha_nacimiento'])); ?></span>
                                    <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-3 py-2"><?php echo htmlspecialchars(etiqueta_sexo($paciente['sexo'])); ?></span>
                                    <span class="badge rounded-pill <?php echo (int) $paciente['activo'] === 1 ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'; ?> px-3 py-2">
                                        <?php echo (int) $paciente['activo'] === 1 ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </div>

                                <div class="text-start border-top border-light-subtle pt-3">
                                    <div class="d-flex align-items-start gap-2 mb-3">
                                        <i class="bi bi-telephone text-secondary mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Teléfono de contacto</small>
                                            <span class="fw-semibold"><?php echo htmlspecialchars($paciente['telefono_contacto'] ?: 'No registrado'); ?></span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2 mb-3">
                                        <i class="bi bi-person-heart text-secondary mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Representante</small>
                                            <span class="fw-semibold"><?php echo htmlspecialchars(trim(($paciente['nombre_representante'] ?? '') . ' ' . ($paciente['apellido_representante'] ?? '')) ?: 'No registrado'); ?></span>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($paciente['parentesco'] ?: 'Parentesco no indicado'); ?></small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-calendar3 text-secondary mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Registrado el</small>
                                            <span class="fw-semibold"><?php echo htmlspecialchars(formatear_fecha($paciente['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- RESUMEN RÁPIDO -->
                            <div class="tarjeta-dashboard contenedor-seccion h-auto mt-4">
                                <h5 class="titulo-seccion-principal mb-3">Resumen clínico</h5>
                                <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                                    <span class="descripcion-seccion">Consultas</span>
                                    <strong><?php echo count($consultas); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                                    <span class="descripcion-seccion">Dosis registradas</span>
                                    <strong><?php echo count($vacunas); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between py-2">
                                    <span class="descripcion-seccion">Próxima dosis</span>
                                    <strong><?php echo htmlspecialchars(formatear_fecha($proxima_dosis, 'd/m/Y', 'No programada')); ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- COLUMNA DERECHA: INFORMACIÓN Y PESTAÑAS -->
                        <div class="col-lg-8">
                            <div class="tarjeta-dashboard contenedor-seccion h-auto" style="height: auto !important;">
                                <ul class="nav nav-tabs nav-tabs-expediente mb-4" id="tabsExpediente" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tab-resumen" data-bs-toggle="tab" data-bs-target="#panel-resumen" type="button" role="tab" aria-controls="panel-resumen" aria-selected="true">
                                            <i class="bi bi-person-vcard me-1"></i> Datos del paciente
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-consultas" data-bs-toggle="tab" data-bs-target="#panel-consultas" type="button" role="tab" aria-controls="panel-consultas" aria-selected="false">
                                            <i class="bi bi-journal-medical me-1"></i> Consultas <span class="badge bg-primary-subtle text-primary-emphasis ms-1"><?php echo count($consultas); ?></span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-vacunas" data-bs-toggle="tab" data-bs-target="#panel-vacunas" type="button" role="tab" aria-controls="panel-vacunas" aria-selected="false">
                                            <i class="bi bi-shield-check me-1"></i> Vacunas <span class="badge bg-primary-subtle text-primary-emphasis ms-1"><?php echo count($vacunas); ?></span>
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="contenidoExpediente">
                                    <!-- DATOS DEL PACIENTE -->
                                    <div class="tab-pane fade show active" id="panel-resumen" role="tabpanel" aria-labelledby="tab-resumen">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 h-100" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                    <small class="text-muted d-block mb-1">Fecha de nacimiento</small>
                                                    <strong><?php echo htmlspecialchars(formatear_fecha($paciente['fecha_nacimiento'])); ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 h-100" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                    <small class="text-muted d-block mb-1">Edad</small>
                                                    <strong><?php echo htmlspecialchars(calcular_edad($paciente['fecha_nacimiento'])); ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 h-100" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                    <small class="text-muted d-block mb-1">Sexo</small>
                                                    <strong><?php echo htmlspecialchars(etiqueta_sexo($paciente['sexo'])); ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 h-100" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                    <small class="text-muted d-block mb-1">Natural de</small>
                                                    <strong><?php echo htmlspecialchars($paciente['lugar_nacimiento'] ?: 'No registrado'); ?></strong>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 h-100" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                    <small class="text-muted d-block mb-1">Representante</small>
                                                    <strong><?php echo htmlspecialchars(trim(($paciente['nombre_representante'] ?? '') . ' ' . ($paciente['apellido_representante'] ?? '')) ?: 'No registrado'); ?></strong>
                                                    <small class="text-muted d-block"><?php echo htmlspecialchars($paciente['parentesco'] ?: 'Parentesco no indicado'); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 h-100" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                    <small class="text-muted d-block mb-1">Cédula del representante</small>
                                                    <strong><?php echo htmlspecialchars($paciente['cedula_representante'] ?: 'No registrada'); ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 h-100" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                    <small class="text-muted d-block mb-1">Teléfono</small>
                                                    <strong><?php echo htmlspecialchars($paciente['telefono_contacto'] ?: 'No registrado'); ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-3 rounded-3 h-100" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                    <small class="text-muted d-block mb-1">Dirección</small>
                                                    <strong style="font-size: 0.9rem;"><?php echo htmlspecialchars($paciente['direccion'] ?: 'No registrada'); ?></strong>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="p-3 rounded-3" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom border-light-subtle">
                                                        <small class="text-muted d-block fw-semibold">Antecedentes Médicos</small>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 border-0 py-0 px-2 fw-semibold btn-abrir-antecedentes"
                                                                data-id="<?php echo $paciente['id_paciente']; ?>"
                                                                data-nombre="<?php echo htmlspecialchars(nombre_paciente($paciente), ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-antecedentes="<?php echo htmlspecialchars($paciente['antecedentes'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-redirect="ver_paciente.php?id=<?php echo $paciente['id_paciente']; ?>"
                                                                title="Editar antecedentes médicos">
                                                            <i class="bi bi-pencil-square"></i> Editar antecedentes
                                                        </button>
                                                    </div>
                                                    <div>
                                                        <?php echo renderizar_antecedentes_html($paciente['antecedentes'] ?? ''); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- CONSULTAS -->
                                    <div class="tab-pane fade" id="panel-consultas" role="tabpanel" aria-labelledby="tab-consultas">
                                        <?php if (!empty($consultas)): ?>
                                            <div class="lista-elementos">
                                                <?php foreach ($consultas as $consulta): ?>
                                                    <div class="p-3 rounded-3" style="background-color: #f9fcfe; border: 1px solid #eaf4fe;">
                                                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                                            <div>
                                                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($consulta['motivo_consulta']); ?></h6>
                                                                <small class="text-muted">
                                                                    <i class="bi bi-calendar3 me-1"></i><?php echo htmlspecialchars(formatear_fecha($consulta['fecha_consulta'], 'd/m/Y H:i')); ?>
                                                                    <?php
                                                                    $doctor = nombre_pediatra($consulta);
                                                                    if ($doctor !== '') {
                                                                        echo ' · Dr(a). ' . htmlspecialchars($doctor);
                                                                    }
                                                                    ?>
                                                                </small>
                                                            </div>
                                                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">Consulta</span>
                                                        </div>

                                                        <?php if (!empty($consulta['diagnostico'])): ?>
                                                            <p class="mb-1 small"><strong>Diagnóstico:</strong> <?php echo htmlspecialchars($consulta['diagnostico']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($consulta['tratamiento'])): ?>
                                                            <p class="mb-1 small"><strong>Tratamiento:</strong> <?php echo htmlspecialchars($consulta['tratamiento']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if (!empty($consulta['examen_fisico'])): ?>
                                                            <p class="mb-1 small"><strong>Examen físico:</strong> <?php echo htmlspecialchars($consulta['examen_fisico']); ?></p>
                                                        <?php endif; ?>

                                                        <?php if ($consulta['peso'] !== null || $consulta['talla'] !== null || $consulta['circunferencia_cefalica'] !== null): ?>
                                                            <div class="d-flex flex-wrap gap-3 mt-2 pt-2 border-top border-light-subtle">
                                                                <?php if ($consulta['peso'] !== null): ?><small class="text-muted"><strong>Peso:</strong> <?php echo htmlspecialchars($consulta['peso']); ?> kg</small><?php endif; ?>
                                                                <?php if ($consulta['talla'] !== null): ?><small class="text-muted"><strong>Talla:</strong> <?php echo htmlspecialchars($consulta['talla']); ?> cm</small><?php endif; ?>
                                                                <?php if ($consulta['circunferencia_cefalica'] !== null): ?><small class="text-muted"><strong>CC:</strong> <?php echo htmlspecialchars($consulta['circunferencia_cefalica']); ?> cm</small><?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-5 text-muted small">
                                                <i class="bi bi-journal-x fs-2 d-block mb-2 text-secondary"></i>
                                                Este paciente todavía no tiene consultas registradas.
                                                <div class="mt-3">
                                                    <button type="button" class="boton-revision border-0 px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalConsultaExpediente">
                                                        Registrar primera consulta
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- VACUNAS -->
                                    <div class="tab-pane fade" id="panel-vacunas" role="tabpanel" aria-labelledby="tab-vacunas">
                                        <?php if (!empty($vacunas)): ?>
                                            <div class="table-responsive">
                                                <table class="table align-middle mb-0">
                                                    <thead>
                                                        <tr style="border-bottom: 2px solid #eaf4fe; color: #64748b; font-size: 0.85rem;">
                                                            <th class="pb-3 fw-bold">VACUNA</th>
                                                            <th class="pb-3 fw-bold">DOSIS</th>
                                                            <th class="pb-3 fw-bold">APLICACIÓN</th>
                                                            <th class="pb-3 fw-bold">PRÓXIMA</th>
                                                            <th class="pb-3 fw-bold">ESTADO</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($vacunas as $vacuna): ?>
                                                            <?php $estado = estado_dosis($vacuna); ?>
                                                            <tr style="border-bottom: 1px solid #f9fcfe;">
                                                                <td class="fw-semibold"><?php echo htmlspecialchars($vacuna['nombre_vacuna']); ?></td>
                                                                <td><span class="subtitulo-item"><?php echo htmlspecialchars($vacuna['dosis']); ?></span></td>
                                                                <td><span class="subtitulo-item"><?php echo htmlspecialchars(formatear_fecha($vacuna['fecha_aplicacion'])); ?></span></td>
                                                                <td><span class="subtitulo-item"><?php echo htmlspecialchars(formatear_fecha($vacuna['proxima_dosis'], 'd/m/Y', 'No programada')); ?></span></td>
                                                                <td><span class="badge rounded-pill <?php echo htmlspecialchars($estado['clase']); ?> px-3 py-2"><?php echo htmlspecialchars($estado['texto']); ?></span></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-5 text-muted small">
                                                <i class="bi bi-shield-x fs-2 d-block mb-2 text-secondary"></i>
                                                No hay dosis registradas para este paciente.
                                                <div class="mt-3">
                                                    <button type="button" class="boton-revision border-0 px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalVacunaExpediente">
                                                        Registrar vacuna
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL CONSULTA -->
                    <div class="modal fade" id="modalConsultaExpediente" tabindex="-1" aria-labelledby="tituloConsultaExpediente" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">
                                <form action="procedures/guardarConsulta.php" method="POST">
                                    <div class="modal-header border-light-subtle">
                                        <div>
                                            <h5 class="modal-title fw-bold" id="tituloConsultaExpediente">Nueva consulta</h5>
                                            <small class="text-muted">Paciente: <?php echo htmlspecialchars(nombre_paciente($paciente)); ?></small>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_paciente" value="<?php echo (int) $paciente['id_paciente']; ?>">
                                        <input type="hidden" name="origen" value="expediente">

                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label for="exp_fecha" class="form-label fw-semibold text-secondary small">Fecha y hora</label>
                                                <input type="datetime-local" class="form-control" id="exp_fecha" name="fecha_consulta" value="<?php echo date('Y-m-d\TH:i'); ?>" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                            </div>
                                            <div class="col-md-7">
                                                <label for="exp_motivo" class="form-label fw-semibold text-secondary small">Motivo de consulta</label>
                                                <input type="text" class="form-control" id="exp_motivo" name="motivo_consulta" placeholder="Ej. Control pediátrico, fiebre, tos..." required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                            </div>

                                            <div class="col-md-4">
                                                <label for="exp_peso" class="form-label fw-semibold text-secondary small">Peso (kg)</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="exp_peso" name="peso" placeholder="Ej. 14.5" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="exp_talla" class="form-label fw-semibold text-secondary small">Talla (cm)</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="exp_talla" name="talla" placeholder="Ej. 98" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="exp_cc" class="form-label fw-semibold text-secondary small">Circunferencia cefálica (cm)</label>
                                                <input type="number" step="0.01" min="0" class="form-control" id="exp_cc" name="circunferencia_cefalica" placeholder="Ej. 48" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                            </div>

                                            <div class="col-12">
                                                <label for="exp_examen" class="form-label fw-semibold text-secondary small">Examen físico</label>
                                                <textarea class="form-control" id="exp_examen" name="examen_fisico" rows="2" placeholder="Hallazgos del examen físico..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="exp_diagnostico" class="form-label fw-semibold text-secondary small">Diagnóstico</label>
                                                <textarea class="form-control" id="exp_diagnostico" name="diagnostico" rows="3" placeholder="Diagnóstico clínico..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="exp_tratamiento" class="form-label fw-semibold text-secondary small">Indicaciones y tratamiento</label>
                                                <textarea class="form-control" id="exp_tratamiento" name="tratamiento" rows="3" placeholder="Medicamentos, pautas y recomendaciones..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
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

                    <!-- MODAL VACUNA -->
                    <div class="modal fade" id="modalVacunaExpediente" tabindex="-1" aria-labelledby="tituloVacunaExpediente" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">
                                <form action="procedures/guardarVacuna.php" method="POST">
                                    <div class="modal-header border-light-subtle">
                                        <div>
                                            <h5 class="modal-title fw-bold" id="tituloVacunaExpediente">Registrar vacuna</h5>
                                            <small class="text-muted">Paciente: <?php echo htmlspecialchars(nombre_paciente($paciente)); ?></small>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_paciente" value="<?php echo (int) $paciente['id_paciente']; ?>">
                                        <input type="hidden" name="origen" value="expediente">

                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label for="exp_vacuna" class="form-label fw-semibold text-secondary small">Vacuna</label>
                                                <input type="text" class="form-control" id="exp_vacuna" name="nombre_vacuna" placeholder="Ej. Pentavalente" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="exp_dosis" class="form-label fw-semibold text-secondary small">Dosis</label>
                                                <select class="form-select" id="exp_dosis" name="dosis" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
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
                                                <label for="exp_fecha_vacuna" class="form-label fw-semibold text-secondary small">Fecha de aplicación</label>
                                                <input type="date" class="form-control" id="exp_fecha_vacuna" name="fecha_aplicacion" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="exp_proxima" class="form-label fw-semibold text-secondary small">Próxima dosis (opcional)</label>
                                                <input type="date" class="form-control" id="exp_proxima" name="proxima_dosis" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="exp_lote" class="form-label fw-semibold text-secondary small">Lote (opcional)</label>
                                                <input type="text" class="form-control" id="exp_lote" name="lote" placeholder="Ej. L-2026-04" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="exp_observaciones" class="form-label fw-semibold text-secondary small">Observaciones (opcional)</label>
                                                <input type="text" class="form-control" id="exp_observaciones" name="observaciones" placeholder="Reacciones, recomendaciones..." style="border-color: #eaf4fe; background-color: #f9fcfe;">
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
                <?php endif; ?>

            </main>
        </div>
    </div>

    <?php if ($paciente): ?>
        <?php include __DIR__ . '/templates/modal_antecedentes.php'; ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Abre la pestaña de vacunas cuando se llega desde el enlace ...#vacunas
        document.addEventListener('DOMContentLoaded', function () {
            if (window.location.hash === '#vacunas') {
                const trigger = document.querySelector('#tab-vacunas');
                if (trigger) {
                    new bootstrap.Tab(trigger).show();
                }
            }
            if (window.location.hash === '#consultas') {
                const trigger = document.querySelector('#tab-consultas');
                if (trigger) {
                    new bootstrap.Tab(trigger).show();
                }
            }
        });
    </script>
    <?php mostrar_alerta_swal(); ?>
</body>

</html>
