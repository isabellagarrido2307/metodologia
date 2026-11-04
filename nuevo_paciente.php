<?php
session_start();
include __DIR__ . '/templates/header.php';
$seccion_activa = 'pacientes';
?>

<!-- Enlace a la hoja de estilos personalizada -->
<link rel="stylesheet" href="styles/dashboard.css?v=<?php echo filemtime('styles/dashboard.css'); ?>">

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
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Sexo -->
                            <div class="col-md-4 mb-3">
                                <label for="sexo" class="form-label fw-semibold text-secondary small">Sexo</label>
                                <select class="form-select" id="sexo" name="sexo" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                    <option value="">Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                </select>
                            </div>
                            <!-- Natural de -->
                            <div class="col-md-4 mb-3">
                                <label for="natural_de" class="form-label fw-semibold text-secondary small">Natural de</label>
                                <input type="text" class="form-control" id="natural_de" name="natural_de" placeholder="Ej. Margarita, Venezuela" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                        </div>

                        <!-- DATOS DEL REPRESENTANTE Y CONSULTA -->
                        <h6 class="fw-bold text-uppercase fs-7 mt-4 mb-3" style="color: #4a80bc; letter-spacing: 0.05em;">2. Padre o Representante y Motivo de Consulta</h6>

                        <div class="row">
                            <!-- Primer Nombre Representante -->
                            <div class="col-md-3 mb-3">
                                <label for="rep_primer_nombre" class="form-label fw-semibold text-secondary small">1er Nombre Rep.</label>
                                <input type="text" class="form-control" id="rep_primer_nombre" name="rep_primer_nombre" placeholder="Ej. María" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                            <!-- Primer Apellido Representante -->
                            <div class="col-md-3 mb-3">
                                <label for="rep_primer_apellido" class="form-label fw-semibold text-secondary small">1er Apellido Rep.</label>
                                <input type="text" class="form-control" id="rep_primer_apellido" name="rep_primer_apellido" placeholder="Ej. Gómez" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Teléfono -->
                            <div class="col-md-12 mb-3">
                                <label for="telefono" class="form-label fw-semibold text-secondary small">Teléfono de Contacto</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Ej. 0412-1234567" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="antecedentes" class="form-label fw-semibold text-secondary small">Antecedentes</label>
                            <textarea class="form-control" id="antecedentes" name="antecedentes" rows="2" placeholder="Antecedentes médicos, familiares o perinatales..." style="border-color: #eaf4fe; background-color: #f9fcfe;"></textarea>
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
</body>
</html>