<?php
session_start();

$seccion_activa = 'ayuda';

include __DIR__ . '/templates/header.php';
include('procedures/utilidades.php');
?>

<body class="cuerpo-dashboard">

    <div class="contenedor-fluid-personalizado">
        <div class="fila-principal">

            <!-- BARRA LATERAL -->
            <?php include __DIR__ . '/templates/sidebar.php'; ?>

            <!-- CONTENIDO PRINCIPAL -->
            <main class="col-md-9 col-lg-10 ms-sm-auto area-contenido">

                <header class="cabecera-principal">
                    <div>
                        <h2 class="titulo-pagina">¿Cómo usar el sistema?</h2>
                        <p class="subtitulo-pagina">Guía rápida para aprovechar todas las herramientas de MediPortal.</p>
                    </div>
                </header>

                <!-- BIENVENIDA -->
                <div class="tarjeta-dashboard contenedor-seccion h-auto mb-4">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mb-2 px-3 py-2">Guía de inicio</span>
                            <h3 class="fw-bold mb-2">Bienvenido(a) a MediPortal</h3>
                            <p class="descripcion-seccion mb-0" style="font-size: 0.92rem;">
                                MediPortal centraliza la gestión de pacientes pediátricos del Centro Médico El Valle.
                                Desde aquí puede registrar pacientes, documentar consultas, controlar la vacunación y
                                consultar el expediente clínico en cualquier momento.
                            </p>
                        </div>
                        <div class="col-lg-4 text-center">
                            <div class="icono-stat icono-azul mx-auto" style="width: 76px; height: 76px; font-size: 2rem;">
                                <i class="bi bi-heart-pulse"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PASOS -->
                <h5 class="fw-bold mb-3">Primeros pasos</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="tarjeta-dashboard h-100 p-4">
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mb-3">Paso 1</span>
                            <div class="icono-stat icono-azul mb-3" style="width: 46px; height: 46px; font-size: 1.2rem;">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Registre su cuenta</h6>
                            <p class="descripcion-seccion mb-0">Complete sus datos profesionales en la pantalla de registro de pediatras.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="tarjeta-dashboard h-100 p-4">
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mb-3">Paso 2</span>
                            <div class="icono-stat icono-azul mb-3" style="width: 46px; height: 46px; font-size: 1.2rem;">
                                <i class="bi bi-box-arrow-in-right"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Inicie sesión</h6>
                            <p class="descripcion-seccion mb-0">Acceda con su cédula y contraseña para entrar al panel principal.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="tarjeta-dashboard h-100 p-4">
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mb-3">Paso 3</span>
                            <div class="icono-stat icono-azul mb-3" style="width: 46px; height: 46px; font-size: 1.2rem;">
                                <i class="bi bi-people"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Registre pacientes</h6>
                            <p class="descripcion-seccion mb-0">Cree el expediente con los datos del paciente y su representante.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="tarjeta-dashboard h-100 p-4">
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mb-3">Paso 4</span>
                            <div class="icono-stat icono-azul mb-3" style="width: 46px; height: 46px; font-size: 1.2rem;">
                                <i class="bi bi-journal-medical"></i>
                            </div>
                            <h6 class="fw-bold mb-2">Documente la atención</h6>
                            <p class="descripcion-seccion mb-0">Agregue consultas y dosis de vacunas desde el expediente del paciente.</p>
                        </div>
                    </div>
                </div>

                <!-- MÓDULOS -->
                <div class="row g-4 align-items-start mb-4">
                    <div class="col-lg-6">
                        <div class="tarjeta-dashboard contenedor-seccion h-auto">
                            <h5 class="titulo-seccion-principal mb-3">Módulos del sistema</h5>

                            <div class="d-flex align-items-start gap-3 p-3 rounded-3 mb-2" style="background-color: #f9fcfe;">
                                <div class="icono-stat icono-azul"><i class="bi bi-house-door"></i></div>
                                <div>
                                    <strong class="d-block">Inicio</strong>
                                    <small class="text-muted">Resumen de pacientes activos, consultas recientes y alertas de vacunación.</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded-3 mb-2" style="background-color: #f9fcfe;">
                                <div class="icono-stat icono-azul"><i class="bi bi-people"></i></div>
                                <div>
                                    <strong class="d-block">Pacientes</strong>
                                    <small class="text-muted">Listado, búsqueda y acceso al expediente de cada paciente registrado.</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded-3 mb-2" style="background-color: #f9fcfe;">
                                <div class="icono-stat icono-azul"><i class="bi bi-journal-medical"></i></div>
                                <div>
                                    <strong class="d-block">Historiales clínicos</strong>
                                    <small class="text-muted">Consultas médicas con motivo, diagnóstico, tratamiento y antropometría.</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded-3 mb-2" style="background-color: #f9fcfe;">
                                <div class="icono-stat icono-azul"><i class="bi bi-shield-plus"></i></div>
                                <div>
                                    <strong class="d-block">Registros de Vacunación</strong>
                                    <small class="text-muted">Dosis aplicadas, próximas dosis y esquema de referencia pediátrica.</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 p-3 rounded-3" style="background-color: #f9fcfe;">
                                <div class="icono-stat icono-azul"><i class="bi bi-gear"></i></div>
                                <div>
                                    <strong class="d-block">Configuración</strong>
                                    <small class="text-muted">Actualice sus datos profesionales y su contraseña de acceso.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="tarjeta-dashboard contenedor-seccion h-auto">
                            <h5 class="titulo-seccion-principal mb-3">Preguntas frecuentes</h5>

                            <div class="accordion accordion-flush" id="acordeonAyuda">
                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed px-0 bg-transparent shadow-none fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="false">
                                            ¿Cómo registro un paciente nuevo?
                                        </button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#acordeonAyuda">
                                        <div class="accordion-body px-0 pt-0 text-muted small">
                                            Vaya a <strong>Pacientes</strong> y pulse <strong>Añadir Paciente</strong>. Complete los datos personales, el representante y guarde. El sistema abrirá automáticamente el expediente.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed px-0 bg-transparent shadow-none fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">
                                            ¿Dónde agrego una consulta médica?
                                        </button>
                                    </h2>
                                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#acordeonAyuda">
                                        <div class="accordion-body px-0 pt-0 text-muted small">
                                            Puede hacerlo desde <strong>Historiales clínicos</strong> con el botón <strong>Nueva consulta</strong>, o desde el expediente del paciente en la pestaña <strong>Consultas</strong>.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed px-0 bg-transparent shadow-none fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">
                                            ¿Cómo controlo las próximas dosis de vacunas?
                                        </button>
                                    </h2>
                                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#acordeonAyuda">
                                        <div class="accordion-body px-0 pt-0 text-muted small">
                                            Al registrar una dosis puede indicar la fecha de la próxima. En <strong>Registros de Vacunación</strong> verá las dosis próximas y vencidas, y en Inicio aparecerán las alertas.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed px-0 bg-transparent shadow-none fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false">
                                            ¿Puedo cambiar mi contraseña?
                                        </button>
                                    </h2>
                                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#acordeonAyuda">
                                        <div class="accordion-body px-0 pt-0 text-muted small">
                                            Sí. Ingrese a <strong>Configuración</strong>, sección <strong>Seguridad</strong>, indique su contraseña actual y la nueva. Los cambios se guardan de inmediato.
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item bg-transparent">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed px-0 bg-transparent shadow-none fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false">
                                            ¿Qué hago si no aparecen los historiales o vacunas?
                                        </button>
                                    </h2>
                                    <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#acordeonAyuda">
                                        <div class="accordion-body px-0 pt-0 text-muted small">
                                            Verifique que se haya ejecutado el archivo <code>database/esquema_clinico.sql</code> en la base de datos <strong>mediportal</strong>. Ese script crea las tablas de consultas y vacunas.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTACTO / BUENAS PRÁCTICAS -->
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="tarjeta-dashboard contenedor-seccion h-auto">
                            <h5 class="titulo-seccion-principal mb-3">Buenas prácticas</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex gap-2 mb-2"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="small">Verifique la cédula del representante antes de guardar el paciente.</span></li>
                                <li class="d-flex gap-2 mb-2"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="small">Registre el motivo de consulta y el diagnóstico en cada atención.</span></li>
                                <li class="d-flex gap-2 mb-2"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="small">Programe la próxima dosis para mantener el esquema de vacunación al día.</span></li>
                                <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success mt-1"></i><span class="small">Cierre sesión al terminar si comparte el equipo con otros profesionales.</span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="tarjeta-dashboard contenedor-seccion h-auto">
                            <h5 class="titulo-seccion-principal mb-3">Soporte</h5>
                            <p class="descripcion-seccion">¿Tiene dudas sobre el funcionamiento del sistema? Contacte al equipo de soporte del Centro Médico El Valle.</p>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-envelope text-secondary"></i>
                                <span class="small">soporte@centromedicoelvalle.org</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-telephone text-secondary"></i>
                                <span class="small">+58 412-000 0000</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-clock text-secondary"></i>
                                <span class="small">Lunes a viernes, 8:00 a.m. – 5:00 p.m.</span>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php mostrar_alerta_swal(); ?>
</body>

</html>
