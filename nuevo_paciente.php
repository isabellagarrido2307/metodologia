<?php
session_start();
include __DIR__ . '/templates/header.php';
$seccion_activa = 'pacientes';
include('procedures/bddConexion.php');
include('procedures/utilidades.php');

$emergencias_lista = lista_datos_emergencia($mysqli);
$condiciones_lista = lista_condiciones_comunes($mysqli);
$grupos_lista      = lista_grupos_sanguineos();
?>

<body class="cuerpo-dashboard min-vh-100 py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <!-- Botón para regresar al listado -->
                <div class="mb-3">
                    <a href="pacientes.php" class="enlace-accion d-inline-flex align-items-center gap-2 text-decoration-none fw-semibold">
                        <i class="bi bi-arrow-left"></i> Volver al listado de pacientes
                    </a>
                </div>

                <div class="tarjeta-dashboard p-4 p-md-5 bg-white rounded-4 shadow-custom h-auto" style="height: auto !important;">
                    
                    <!-- Encabezado -->
                    <div class="text-center mb-4 pb-3 border-bottom border-light-subtle">
                        <div class="icono-stat icono-azul mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.25rem;">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">Registro de Nuevo Paciente</h3>
                        <p class="text-muted small mb-0">Complete la ficha clínica inicial para registrar al paciente en el sistema del Centro Médico El Valle.</p>
                    </div>

                    <!-- Mensaje de Alerta (si existe) -->
                    <?php if (isset($_SESSION['swal_message'])): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> 
                            <?php echo $_SESSION['swal_message']['text']; ?>
                        </div>
                        <?php unset($_SESSION['swal_message']); ?>
                    <?php endif; ?>

                    <!-- Formulario de Registro de Paciente -->
                    <form action="procedures/registrarPaciente.php" method="POST">
                        
                        <!-- DATOS PERSONALES DEL PACIENTE -->
                        <h6 class="fw-bold text-uppercase fs-7 mb-3" style="color: #4a80bc; letter-spacing: 0.05em;">1. Datos Personales del Paciente</h6>
                        
                        <div class="row">
                            <!-- Primer Nombre -->
                            <div class="col-md-3 mb-3">
                                <label for="primer_nombre" class="form-label fw-semibold text-secondary small">1er Nombre</label>
                                <input type="text" class="form-control" id="primer_nombre" name="primer_nombre" placeholder="Ej. Juan" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Segundo Nombre -->
                            <div class="col-md-3 mb-3">
                                <label for="segundo_nombre" class="form-label fw-semibold text-secondary small">2do Nombre</label>
                                <input type="text" class="form-control" id="segundo_nombre" name="segundo_nombre" placeholder="Ej. Carlos" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Primer Apellido -->
                            <div class="col-md-3 mb-3">
                                <label for="primer_apellido" class="form-label fw-semibold text-secondary small">1er Apellido</label>
                                <input type="text" class="form-control" id="primer_apellido" name="primer_apellido" placeholder="Ej. Pérez" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Segundo Apellido -->
                            <div class="col-md-3 mb-3">
                                <label for="segundo_apellido" class="form-label fw-semibold text-secondary small">2do Apellido</label>
                                <input type="text" class="form-control" id="segundo_apellido" name="segundo_apellido" placeholder="Ej. Gómez" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Fecha de Nacimiento -->
                            <div class="col-md-4 mb-3">
                                <label for="fecha_nacimiento" class="form-label fw-semibold text-secondary small">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" max="<?php echo date('Y-m-d'); ?>" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Sexo -->
                            <div class="col-md-4 mb-3">
                                <label for="sexo" class="form-label fw-semibold text-secondary small">Sexo</label>
                                <select class="form-select" id="sexo" name="sexo" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                    <option value="">Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                            <!-- Lugar de nacimiento -->
                            <div class="col-md-4 mb-3">
                                <label for="lugar_nacimiento" class="form-label fw-semibold text-secondary small">Natural de</label>
                                <input type="text" class="form-control" id="lugar_nacimiento" name="lugar_nacimiento" placeholder="Ej. Margarita, Venezuela" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                        </div>

                        <!-- DATOS DEL REPRESENTANTE Y CONSULTA -->
                        <h6 class="fw-bold text-uppercase fs-7 mt-4 mb-3" style="color: #4a80bc; letter-spacing: 0.05em;">2. Padre o Representante y Motivo de Consulta</h6>

                        <div class="row">
                            <!-- Primer Nombre Representante -->
                            <div class="col-md-3 mb-3">
                                <label for="nombre_representante" class="form-label fw-semibold text-secondary small">1er Nombre Rep.</label>
                                <input type="text" class="form-control" id="nombre_representante" name="nombre_representante" placeholder="Ej. María" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Primer Apellido Representante -->
                            <div class="col-md-3 mb-3">
                                <label for="apellido_representante" class="form-label fw-semibold text-secondary small">1er Apellido Rep.</label>
                                <input type="text" class="form-control" id="apellido_representante" name="apellido_representante" placeholder="Ej. Gómez" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Cédula Representante -->
                            <div class="col-md-3 mb-3">
                                <label for="cedula_representante" class="form-label fw-semibold text-secondary small">Cédula</label>
                                <input type="text" class="form-control" id="cedula_representante" name="cedula_representante" placeholder="Ej. 12345678" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Parentesco -->
                            <div class="col-md-3 mb-3">
                                <label for="parentesco" class="form-label fw-semibold text-secondary small">Parentesco</label>
                                <select class="form-select" id="parentesco" name="parentesco" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                    <option value="Madre" selected>Madre</option>
                                    <option value="Padre">Padre</option>
                                    <option value="Abuelo/a">Abuelo/a</option>
                                    <option value="Tío/a">Tío/a</option>
                                    <option value="Hermano/a">Hermano/a</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Teléfono -->
                            <div class="col-md-12 mb-3">
                                <label for="telefono_contacto" class="form-label fw-semibold text-secondary small">Teléfono de Contacto</label>
                                <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto" placeholder="Ej. 0412-1234567" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label fw-semibold text-secondary small">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2" placeholder="Dirección de residencia del paciente..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
                        </div>

                        <!-- 3. ANTECEDENTES MÉDICOS Y DATOS DE EMERGENCIA -->
                        <div class="mt-4 mb-4 pt-3 border-top border-light-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-uppercase mb-0" style="color: #4a80bc; letter-spacing: 0.05em; font-size: 0.88rem;">
                                    3. Antecedentes Médicos y Datos de Emergencia
                                </h6>
                                <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.76rem;">Ficha Médica</span>
                            </div>

                            <!-- DATOS DE EMERGENCIA Y GRUPO SANGUÍNEO -->
                            <div class="p-3 rounded-4 mb-3" style="background-color: #fff9f9; border: 1.5px solid #fecaca;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom border-danger-subtle">
                                    <div class="d-flex align-items-center gap-2 text-danger fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 0.82rem;">
                                        <i class="bi bi-exclamation-octagon-fill fs-6"></i>
                                        <span>Datos Médicos Críticos y Emergencia</span>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2 py-1" style="font-size: 0.74rem;">
                                        Información Vital
                                    </span>
                                </div>

                                <!-- Grupo Sanguíneo y Factor Rh -->
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3 p-2 px-3 rounded-3 bg-white border border-danger-subtle">
                                    <label for="grupo_sanguineo" class="fw-semibold text-danger mb-0 d-flex align-items-center gap-1" style="font-size: 0.86rem; min-width: 220px;">
                                        <i class="bi bi-droplet-fill"></i> Grupo Sanguíneo y Factor Rh:
                                    </label>
                                    <select class="form-select form-select-sm" id="grupo_sanguineo" name="grupo_sanguineo" style="max-width: 280px; font-size: 0.86rem; border-color: #fca5a5; background-color: #ffffff;">
                                        <option value="">No determinado / Desconocido</option>
                                        <?php foreach ($grupos_lista as $grupo): ?>
                                            <option value="<?php echo htmlspecialchars($grupo); ?>"><?php echo htmlspecialchars($grupo); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Checkboxes de Emergencia -->
                                <div class="row g-2">
                                    <?php foreach ($emergencias_lista as $idx => $emergencia): ?>
                                        <div class="col-md-6">
                                            <label class="item-checkbox-antecedente emergencia-box w-100" for="reg_eme_<?php echo $idx; ?>">
                                                <input class="form-check-input flex-shrink-0 m-0" type="checkbox" name="datos_emergencia[]" value="<?php echo htmlspecialchars($emergencia); ?>" id="reg_eme_<?php echo $idx; ?>">
                                                <span class="texto-checkbox"><?php echo htmlspecialchars($emergencia); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- CONDICIONES MÉDICAS COMUNES -->
                            <div class="p-3 rounded-4 mb-3" style="background-color: #f8fbff; border: 1.5px solid #dbeafe;">
                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-primary-subtle text-primary fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 0.82rem;">
                                    <i class="bi bi-heart-pulse-fill fs-6"></i>
                                    <span>Condiciones Médicas Comunes</span>
                                </div>

                                <div class="row g-2">
                                    <?php foreach ($condiciones_lista as $idx => $condicion): ?>
                                        <div class="col-md-6">
                                            <label class="item-checkbox-antecedente comun-box w-100" for="reg_cond_<?php echo $idx; ?>">
                                                <input class="form-check-input flex-shrink-0 m-0" type="checkbox" name="condiciones_comunes[]" value="<?php echo htmlspecialchars($condicion); ?>" id="reg_cond_<?php echo $idx; ?>">
                                                <span class="texto-checkbox"><?php echo htmlspecialchars($condicion); ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- OTROS ANTECEDENTES NO LISTADOS (DINÁMICO) -->
                            <div class="p-3 rounded-4" style="background-color: #ffffff; border: 1.5px solid #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-light-subtle">
                                    <div class="d-flex align-items-center gap-2 text-secondary fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 0.82rem;">
                                        <i class="bi bi-pencil-square fs-6 text-primary"></i>
                                        <span>Otros Antecedentes Médicos (No listados)</span>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.78rem;">Opcional</small>
                                </div>
                                <p class="text-muted mb-3" style="font-size: 0.84rem; line-height: 1.4;">
                                    Si el paciente presenta alguna condición o antecedente médico que no aparezca en las opciones anteriores, ingréselo a continuación:
                                </p>

                                <!-- Contenedor dinámico de campos de texto -->
                                <div id="contenedor_otros_antecedentes">
                                    <div class="input-group mb-2 fila-otro-antecedente">
                                        <span class="input-group-text bg-white border-end-0" style="border-color: #eaf4fe; color: #4a80bc;">
                                            <i class="bi bi-dot fs-5"></i>
                                        </span>
                                        <input type="text" name="otros_antecedentes[]" class="form-control border-start-0" 
                                               placeholder="Especifique otro antecedente médico..."
                                               style="border-color: #eaf4fe; background-color: #f9fcfe; font-size: 0.86rem;">
                                        <button type="button" class="btn btn-outline-danger btn-eliminar-otro px-3 d-inline-flex align-items-center justify-content-center" title="Eliminar / Limpiar">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Botón para agregar otro cuadro de texto -->
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2 py-1 px-3 rounded-pill fw-semibold" id="btn_agregar_otro_antecedente" style="font-size: 0.83rem;">
                                        <i class="bi bi-plus-circle-fill"></i> Agregar otro antecedente
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- EXAMEN FÍSICO Y ANTROPOMETRÍA
                        <h6 class="fw-bold text-uppercase fs-7 mt-4 mb-3" style="color: #4a80bc; letter-spacing: 0.05em;">3. Examen Físico y Evaluación Médica</h6>

                        <div class="mb-3">
                            <label for="ex_fisico" class="form-label fw-semibold text-secondary small">Examen Físico (Ex. Físico)</label>
                            <textarea class="form-control" id="ex_fisico" name="ex_fisico" rows="2" placeholder="Hallazgos del examen físico..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
                        </div>

                        <div class="row">
                            <!-- Peso -->
                            <!-- <div class="col-md-4 mb-3">
                                <label for="peso" class="form-label fw-semibold text-secondary small">Peso (kg)</label>
                                <input type="text" class="form-control" id="peso" name="peso" placeholder="Ej. 14.5 kg" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Talla -->
                            <!-- <div class="col-md-4 mb-3">
                                <label for="talla" class="form-label fw-semibold text-secondary small">Talla (cm)</label>
                                <input type="text" class="form-control" id="talla" name="talla" placeholder="Ej. 98 cm" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div> --> 
                            <!-- CC (Circunferencia Cefálica) -->
                            <!-- <div class="col-md-4 mb-3">
                                <label for="cc" class="form-label fw-semibold text-secondary small">Circunferencia Cefálica (CC)</label>
                                <input type="text" class="form-control" id="cc" name="cc" placeholder="Ej. 48 cm" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="diagnostico" class="form-label fw-semibold text-secondary small">Diagnóstico</label>
                            <textarea class="form-control" id="diagnostico" name="diagnostico" rows="2" placeholder="Diagnóstico clínico..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="indicaciones" class="form-label fw-semibold text-secondary small">Indicaciones y Tratamiento</label>
                            <textarea class="form-control" id="indicaciones" name="indicaciones" rows="3" placeholder="Instrucciones médicas, medicamentos o pautas..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
                        </div>  -->


                        <!-- Botón de Registro -->
                        <div class="d-grid mb-2">
                            <button type="submit" class="boton-revision py-2 fw-bold text-center border-0 text-decoration-none" style="background-color: #9bc7f8; color: #1e293b; font-size: 0.95rem; padding: 0.6rem 1rem;">
                                Guardar y Registrar Paciente
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const contenedor = document.getElementById('contenedor_otros_antecedentes');
        const btnAgregar = document.getElementById('btn_agregar_otro_antecedente');

        function vincularBotonEliminar(fila) {
            const btnEliminar = fila.querySelector('.btn-eliminar-otro');
            if (!btnEliminar) return;

            btnEliminar.addEventListener('click', function() {
                const filas = contenedor.querySelectorAll('.fila-otro-antecedente');
                if (filas.length > 1) {
                    fila.remove();
                } else {
                    fila.querySelector('input').value = '';
                }
            });
        }

        // Vincular la fila inicial
        if (contenedor) {
            contenedor.querySelectorAll('.fila-otro-antecedente').forEach(vincularBotonEliminar);
        }

        // Agregar nueva fila dinámicamente
        if (btnAgregar && contenedor) {
            btnAgregar.addEventListener('click', function() {
                const nuevaFila = document.createElement('div');
                nuevaFila.className = 'input-group mb-2 fila-otro-antecedente';
                nuevaFila.innerHTML = `
                    <span class="input-group-text bg-white border-end-0" style="border-color: #eaf4fe; color: #4a80bc;">
                        <i class="bi bi-dot fs-5"></i>
                    </span>
                    <input type="text" name="otros_antecedentes[]" class="form-control border-start-0" 
                           placeholder="Especifique otro antecedente médico..."
                           style="border-color: #eaf4fe; background-color: #f9fcfe;">
                    <button type="button" class="btn btn-outline-danger btn-eliminar-otro px-3" title="Eliminar este antecedente">
                        <i class="bi bi-trash3"></i>
                    </button>
                `;
                vincularBotonEliminar(nuevaFila);
                contenedor.appendChild(nuevaFila);
                nuevaFila.querySelector('input').focus();
            });
        }
    });
    </script>
    <?php mostrar_alerta_swal(); ?>
</body>
</html>