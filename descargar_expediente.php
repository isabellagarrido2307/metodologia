<?php
session_start();
require_once __DIR__ . '/procedures/bddConexion.php';
require_once __DIR__ . '/procedures/utilidades.php';

$id_paciente = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$auto_descargar = isset($_GET['download']) && $_GET['download'] == '1';

if (!$id_paciente) {
    die('ID de paciente no válido.');
}

// 1. Obtener datos del paciente y de su representante
$stmt = $mysqli->prepare('SELECT p.*,
                                 r.nombre AS nombre_representante,
                                 r.apellido AS apellido_representante,
                                 r.cedula AS cedula_representante,
                                 r.parentesco AS parentesco,
                                 r.telefono AS telefono_contacto,
                                 r.direccion AS direccion
                          FROM pacientes p
                          LEFT JOIN representantes r ON r.id = p.representante_id
                          WHERE p.id = ? LIMIT 1');
$stmt->bind_param('i', $id_paciente);
$stmt->execute();
$paciente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$paciente) {
    die('El expediente solicitado no existe o fue dado de baja.');
}

// 2. Obtener consultas médicas
$consultas = [];
if (tabla_existe($mysqli, 'consultas')) {
    $stmt = $mysqli->prepare("SELECT c.*,
                                     c.fecha_hora AS fecha_consulta,
                                     c.indicaciones_tratamiento AS tratamiento,
                                     ped.nombres AS ped_nombres, ped.apellidos AS ped_apellidos
                              FROM consultas c
                              LEFT JOIN pediatras ped ON ped.id_pediatra = c.id_pediatra
                              WHERE c.paciente_id = ?
                              ORDER BY c.fecha_hora DESC");
    $stmt->bind_param('i', $id_paciente);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($fila = $res->fetch_assoc()) {
        $consultas[] = $fila;
    }
    $stmt->close();
}

// 3. Obtener vacunas
$vacunas = [];
if (tabla_existe($mysqli, 'vacunas')) {
    $stmt = $mysqli->prepare('SELECT * FROM vacunas WHERE paciente_id = ? ORDER BY fecha_aplicacion DESC, id_vacuna DESC');
    $stmt->bind_param('i', $id_paciente);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($fila = $res->fetch_assoc()) {
        $vacunas[] = $fila;
    }
    $stmt->close();
}

$antecedentes_datos = obtener_antecedentes_paciente($mysqli, (int) $id_paciente);
$nombre_completo = nombre_paciente($paciente);
$nombre_archivo = 'Expediente_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $nombre_completo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expediente Médico - <?php echo htmlspecialchars($nombre_completo); ?></title>
    
    <!-- Google Fonts: Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- html2pdf.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif !important;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }

        /* Barra de herramientas superior flotante */
        .barra-herramientas-pdf {
            position: sticky;
            top: 0;
            z-index: 1050;
            background-color: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        /* Hoja de papel tipo expediente */
        .hoja-expediente {
            background-color: #ffffff;
            width: 100%;
            max-width: 920px;
            margin: 1.5rem auto 3rem auto;
            padding: 2.5rem 3rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        /* Encabezado clínico */
        .encabezado-clinico {
            border-bottom: 2.5px solid #4a80bc;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .titulo-expediente {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: 0.02em;
        }

        .subtitulo-expediente {
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Títulos de sección */
        .titulo-bloque-seccion {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #4a80bc;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 0.35rem;
            margin-top: 1.25rem;
            margin-bottom: 0.75rem;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Tablas internas */
        .tabla-datos-clinicos {
            width: 100%;
            font-size: 0.82rem;
            border-collapse: collapse;
            margin-bottom: 0.5rem;
        }

        .tabla-datos-clinicos th,
        .tabla-datos-clinicos td {
            padding: 0.45rem 0.65rem;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .tabla-datos-clinicos th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            width: 25%;
        }

        .tabla-datos-clinicos td {
            color: #1e293b;
            background-color: #ffffff;
        }

        /* Cajas de antecedentes */
        .caja-emergencia-pdf {
            background-color: #fff8f8;
            border: 1.5px solid #fecaca;
            border-radius: 0.4rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            page-break-inside: avoid;
        }

        .caja-comunes-pdf {
            background-color: #f8fbff;
            border: 1.5px solid #dbeafe;
            border-radius: 0.4rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            page-break-inside: avoid;
        }

        .badge-alerta-pdf {
            display: inline-block;
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.76rem;
            font-weight: 600;
            margin: 0.15rem;
        }

        .badge-comun-pdf {
            display: inline-block;
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.76rem;
            font-weight: 600;
            margin: 0.15rem;
        }

        .badge-grupo-pdf {
            display: inline-block;
            background-color: #dc2626;
            color: #ffffff;
            padding: 0.2rem 0.6rem;
            border-radius: 0.25rem;
            font-size: 0.78rem;
            font-weight: 700;
            margin-right: 0.4rem;
        }

        /* Tablas de consultas y vacunas */
        .tabla-registros {
            width: 100%;
            font-size: 0.78rem;
            border-collapse: collapse;
            margin-bottom: 0.75rem;
        }

        .tabla-registros th,
        .tabla-registros td {
            padding: 0.45rem 0.55rem;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .tabla-registros th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 0.76rem;
            text-transform: uppercase;
        }

        .seccion-bloque {
            page-break-inside: avoid;
        }

        /* Área de firmas */
        .area-firmas {
            margin-top: 2.5rem;
            page-break-inside: avoid;
        }

        .linea-firma {
            border-top: 1px solid #64748b;
            width: 80%;
            margin: 3.5rem auto 0.5rem auto;
        }

        /* Estilos de Impresión nativa */
        @media print {
            .barra-herramientas-pdf {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .hoja-expediente {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            @page {
                size: letter portrait;
                margin: 12mm;
            }
        }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR DE HERRAMIENTAS (No imprimible) -->
    <div class="barra-herramientas-pdf d-print-none">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-2 max-w-920" style="max-width: 920px; margin: 0 auto;">
            <div class="d-flex align-items-center gap-2">
                <a href="pacientes.php" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 fw-semibold py-1 px-3">
                    <i class="bi bi-arrow-left"></i> Volver a Pacientes
                </a>
                <span class="text-muted small d-none d-sm-inline">| Expediente de <?php echo htmlspecialchars($nombre_completo); ?></span>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 fw-semibold py-1 px-3" onclick="window.print()">
                    <i class="bi bi-printer-fill"></i> Imprimir
                </button>
                <button type="button" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-2 fw-semibold py-1 px-3" id="btnDescargarPDF" onclick="descargarPDF()">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Descargar PDF
                </button>
            </div>
        </div>
    </div>

    <!-- DOCUMENTO DE EXPEDIENTE (Contenido a exportar e imprimir) -->
    <div class="hoja-expediente" id="documento_expediente">
        
        <!-- ENCABEZADO OFICIAL -->
        <div class="encabezado-clinico">
            <div class="row align-items-center">
                <div class="col-8">
                    <div class="d-flex align-items-center gap-3">
                        <img src="img/medi_logo.svg" alt="MediPortal Logo" style="height: 48px; width: auto;" onerror="this.style.display='none'">
                        <div>
                            <h2 class="titulo-expediente mb-0">Centro Médico El Valle</h2>
                            <p class="subtitulo-expediente mb-0">Servicio de Pediatría y Puericultura · RIF: J-40123456-7</p>
                            <small class="text-muted" style="font-size: 0.75rem;">Av. Principal El Valle, Isla de Margarita, Venezuela · Tel: (0295) 263-1234</small>
                        </div>
                    </div>
                </div>
                <div class="col-4 text-end">
                    <div class="p-2 rounded bg-light border text-center d-inline-block">
                        <span class="d-block fw-bold text-dark" style="font-size: 0.85rem;">EXPEDIENTE MÉDICO</span>
                        <span class="d-block text-primary fw-bold" style="font-size: 0.95rem;">#EXP-<?php echo str_pad((string) $paciente['id'], 5, '0', STR_PAD_LEFT); ?></span>
                        <small class="text-muted d-block" style="font-size: 0.7rem;">Fecha: <?php echo date('d/m/Y H:i'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. DATOS PERSONALES DEL PACIENTE -->
        <div class="seccion-bloque">
            <h6 class="titulo-bloque-seccion">
                <i class="bi bi-person-badge"></i> 1. Datos Personales del Paciente
            </h6>
            <table class="tabla-datos-clinicos">
                <tr>
                    <th>Nombre Completo:</th>
                    <td colspan="3"><strong><?php echo htmlspecialchars($nombre_completo); ?></strong></td>
                </tr>
                <tr>
                    <th>Fecha de Nacimiento:</th>
                    <td><?php echo !empty($paciente['fecha_nacimiento']) ? date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) : 'No registrada'; ?></td>
                    <th>Edad Actual:</th>
                    <td><strong><?php echo htmlspecialchars(calcular_edad($paciente['fecha_nacimiento'])); ?></strong></td>
                </tr>
                <tr>
                    <th>Sexo:</th>
                    <td><?php echo htmlspecialchars(etiqueta_sexo($paciente['sexo'])); ?></td>
                    <th>Natural de:</th>
                    <td><?php echo htmlspecialchars($paciente['lugar_nacimiento'] ?: 'No registrado'); ?></td>
                </tr>
                <tr>
                    <th>Fecha de Registro:</th>
                    <td><?php echo date('d/m/Y', strtotime($paciente['created_at'])); ?></td>
                    <th>Estado del Expediente:</th>
                    <td><?php echo (int)$paciente['activo'] === 1 ? 'Activo' : 'Inactivo'; ?></td>
                </tr>
            </table>
        </div>

        <!-- 2. DATOS DEL REPRESENTANTE LEGAL -->
        <div class="seccion-bloque">
            <h6 class="titulo-bloque-seccion">
                <i class="bi bi-people"></i> 2. Datos del Padre o Representante Legal
            </h6>
            <table class="tabla-datos-clinicos">
                <tr>
                    <th>Representante:</th>
                    <td><?php echo htmlspecialchars(trim(($paciente['nombre_representante'] ?? '') . ' ' . ($paciente['apellido_representante'] ?? '')) ?: 'No registrado'); ?></td>
                    <th>Parentesco:</th>
                    <td><?php echo htmlspecialchars($paciente['parentesco'] ?: 'No indicado'); ?></td>
                </tr>
                <tr>
                    <th>Cédula de Identidad:</th>
                    <td><?php echo htmlspecialchars($paciente['cedula_representante'] ?: 'No registrada'); ?></td>
                    <th>Teléfono de Contacto:</th>
                    <td><?php echo htmlspecialchars($paciente['telefono_contacto'] ?: 'No registrado'); ?></td>
                </tr>
                <tr>
                    <th>Dirección de Residencia:</th>
                    <td colspan="3"><?php echo htmlspecialchars($paciente['direccion'] ?: 'No registrada'); ?></td>
                </tr>
            </table>
        </div>

        <!-- 3. ANTECEDENTES MÉDICOS Y DATOS DE EMERGENCIA -->
        <div class="seccion-bloque">
            <h6 class="titulo-bloque-seccion">
                <i class="bi bi-heart-pulse"></i> 3. Antecedentes Médicos y Datos de Emergencia
            </h6>

            <?php 
            $hay_emergencia = !empty($antecedentes_datos['grupo_sanguineo']) || !empty($antecedentes_datos['emergencia']);
            $hay_condiciones = !empty($antecedentes_datos['condiciones']);
            $hay_otros = !empty($antecedentes_datos['otros']);
            ?>

            <?php if (!$hay_emergencia && !$hay_condiciones && !$hay_otros): ?>
                <p class="text-muted small py-2 px-3 bg-light rounded border mb-2">
                    <i class="bi bi-info-circle me-1"></i> No se han registrado antecedentes médicos ni condiciones de alerta para este paciente.
                </p>
            <?php else: ?>

                <!-- Alertas de Emergencia y Grupo Sanguíneo -->
                <?php if ($hay_emergencia): ?>
                    <div class="caja-emergencia-pdf">
                        <div class="d-flex align-items-center gap-1 text-danger fw-bold text-uppercase mb-2" style="font-size: 0.78rem;">
                            <i class="bi bi-exclamation-octagon-fill"></i> Datos Críticos y Alertas de Emergencia
                        </div>
                        <div>
                            <?php if (!empty($antecedentes_datos['grupo_sanguineo'])): ?>
                                <span class="badge-grupo-pdf">
                                    <i class="bi bi-droplet-fill"></i> Grupo: <?php echo htmlspecialchars($antecedentes_datos['grupo_sanguineo']); ?>
                                </span>
                            <?php endif; ?>

                            <?php foreach ($antecedentes_datos['emergencia'] as $item): ?>
                                <span class="badge-alerta-pdf">
                                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($item); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Condiciones Médicas Comunes -->
                <?php if ($hay_condiciones): ?>
                    <div class="caja-comunes-pdf">
                        <div class="d-flex align-items-center gap-1 text-primary fw-bold text-uppercase mb-2" style="font-size: 0.78rem;">
                            <i class="bi bi-clipboard2-pulse-fill"></i> Condiciones Médicas Comunes
                        </div>
                        <div>
                            <?php foreach ($antecedentes_datos['condiciones'] as $item): ?>
                                <span class="badge-comun-pdf">
                                    <i class="bi bi-check2-circle"></i> <?php echo htmlspecialchars($item); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Otros Antecedentes -->
                <?php if ($hay_otros): ?>
                    <div class="p-2 px-3 rounded bg-white border mb-2">
                        <div class="fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.78rem;">
                            <i class="bi bi-card-text"></i> Otros Antecedentes y Observaciones Médicas:
                        </div>
                        <ul class="mb-0 ps-3 text-secondary" style="font-size: 0.8rem;">
                            <?php foreach ($antecedentes_datos['otros'] as $item): ?>
                                <li><?php echo htmlspecialchars($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>

        <!-- 4. HISTORIAL DE CONSULTAS MÉDICAS -->
        <div class="seccion-bloque">
            <h6 class="titulo-bloque-seccion">
                <i class="bi bi-journal-medical"></i> 4. Historial de Consultas Médicas
            </h6>
            <?php if (!empty($consultas)): ?>
                <table class="tabla-registros">
                    <thead>
                        <tr>
                            <th style="width: 14%;">Fecha</th>
                            <th style="width: 20%;">Pediatra</th>
                            <th style="width: 22%;">Motivo</th>
                            <th style="width: 24%;">Diagnóstico</th>
                            <th style="width: 20%;">Antropometría</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultas as $consulta): ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('d/m/Y', strtotime($consulta['fecha_consulta'])); ?></strong><br>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($consulta['fecha_consulta'])); ?></small>
                                </td>
                                <td>
                                    <?php 
                                    $medico = nombre_pediatra($consulta);
                                    echo $medico !== '' ? 'Dr(a). ' . htmlspecialchars($medico) : 'No asignado';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($consulta['motivo_consulta']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($consulta['diagnostico'] ?: '—'); ?>
                                    <?php if (!empty($consulta['tratamiento'])): ?>
                                        <div class="mt-1 text-muted" style="font-size: 0.74rem;">
                                            <em>Tratamiento:</em> <?php echo htmlspecialchars($consulta['tratamiento']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $medidas = [];
                                    if ($consulta['peso'] !== null) $medidas[] = 'Peso: ' . $consulta['peso'] . ' kg';
                                    if ($consulta['talla'] !== null) $medidas[] = 'Talla: ' . $consulta['talla'] . ' cm';
                                    if ($consulta['circunferencia_cefalica'] !== null) $medidas[] = 'CC: ' . $consulta['circunferencia_cefalica'] . ' cm';
                                    echo !empty($medidas) ? implode('<br>', $medidas) : '—';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted small py-2 px-3 bg-light rounded border mb-2">
                    No registra consultas médicas en el sistema.
                </p>
            <?php endif; ?>
        </div>

        <!-- 5. ESQUEMA DE VACUNACIÓN -->
        <div class="seccion-bloque">
            <h6 class="titulo-bloque-seccion">
                <i class="bi bi-shield-check"></i> 5. Esquema de Vacunación e Inmunizaciones
            </h6>
            <?php if (!empty($vacunas)): ?>
                <table class="tabla-registros">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Vacuna</th>
                            <th style="width: 14%;">Dosis</th>
                            <th style="width: 16%;">F. Aplicación</th>
                            <th style="width: 14%;">Lote</th>
                            <th style="width: 14%;">Próxima Dosis</th>
                            <th style="width: 14%;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vacunas as $vacuna): ?>
                            <?php $est = estado_dosis($vacuna); ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($vacuna['nombre_vacuna']); ?></strong></td>
                                <td><?php echo htmlspecialchars($vacuna['dosis']); ?></td>
                                <td><?php echo !empty($vacuna['fecha_aplicacion']) ? date('d/m/Y', strtotime($vacuna['fecha_aplicacion'])) : '—'; ?></td>
                                <td><?php echo htmlspecialchars($vacuna['lote'] ?: '—'); ?></td>
                                <td><?php echo !empty($vacuna['proxima_dosis']) ? date('d/m/Y', strtotime($vacuna['proxima_dosis'])) : '—'; ?></td>
                                <td>
                                    <span style="font-size: 0.74rem; font-weight: 600;">
                                        <?php echo htmlspecialchars($est['texto']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted small py-2 px-3 bg-light rounded border mb-2">
                    No registra vacunas aplicadas en el sistema.
                </p>
            <?php endif; ?>
        </div>

        <!-- ÁREA DE FIRMA Y SELLO -->
        <div class="area-firmas">
            <div class="row text-center mt-5">
                <div class="col-6">
                    <div class="linea-firma"></div>
                    <span class="d-block fw-bold" style="font-size: 0.8rem;">Médico Pediatra Tratante</span>
                    <small class="text-muted" style="font-size: 0.72rem;">Firma y Sello Profesional</small>
                </div>
                <div class="col-6">
                    <div class="linea-firma"></div>
                    <span class="d-block fw-bold" style="font-size: 0.8rem;">Dirección Médica / Archivo Clínico</span>
                    <small class="text-muted" style="font-size: 0.72rem;">Centro Médico El Valle</small>
                </div>
            </div>
            
            <div class="text-center mt-4 pt-3 border-top text-muted" style="font-size: 0.7rem;">
                Documento clínico confidencial emitido electrónicamente por el Sistema MediPortal. Válido para trámites médicos y escolares.
            </div>
        </div>

    </div>

    <!-- Script para generar y descargar PDF -->
    <script>
        function descargarPDF() {
            const btn = document.getElementById('btnDescargarPDF');
            const textoOriginal = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generando PDF...';
            btn.disabled = true;

            const elemento = document.getElementById('documento_expediente');
            const opt = {
                margin:       [10, 8, 10, 8],
                filename:     '<?php echo $nombre_archivo; ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(elemento).save().then(function() {
                btn.innerHTML = textoOriginal;
                btn.disabled = false;
            }).catch(function(err) {
                console.error('Error al generar PDF:', err);
                btn.innerHTML = textoOriginal;
                btn.disabled = false;
                alert('Ocurrió un error al generar el PDF. Puede utilizar la opción "Imprimir" y seleccionar "Guardar como PDF".');
            });
        }

        <?php if ($auto_descargar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                descargarPDF();
            }, 600);
        });
        <?php endif; ?>
    </script>
</body>
</html>
