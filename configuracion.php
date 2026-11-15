<?php
session_start();

$seccion_activa = 'configuracion';

include __DIR__ . '/templates/header.php';
include('procedures/bddConexion.php');
include('procedures/utilidades.php');

$pediatra = null;
$id_sesion = isset($_SESSION['id_pediatra']) ? (int) $_SESSION['id_pediatra'] : 0;

if ($id_sesion > 0) {
    $stmt = $mysqli->prepare('SELECT * FROM pediatras WHERE id_pediatra = ? LIMIT 1');
    $stmt->bind_param('i', $id_sesion);
    $stmt->execute();
    $pediatra = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
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
                        <h2 class="titulo-pagina">Configuración</h2>
                        <p class="subtitulo-pagina">Actualice su información profesional y las credenciales de acceso al sistema.</p>
                    </div>
                </header>

                <?php if (!$pediatra): ?>
                    <div class="tarjeta-dashboard contenedor-seccion h-auto">
                        <div class="text-center py-5">
                            <i class="bi bi-shield-lock fs-1 text-secondary d-block mb-3"></i>
                            <h4 class="fw-bold">Inicie sesión para continuar</h4>
                            <p class="descripcion-seccion mb-4">La configuración solo está disponible para pediatras autenticados.</p>
                            <a href="Index.php" class="boton-revision border-0 px-4 py-2 text-decoration-none">Ir al inicio de sesión</a>
                        </div>
                    </div>
                <?php else: ?>

                    <div class="row g-4 align-items-start">
                        <!-- PERFIL -->
                        <div class="col-lg-7">
                            <div class="tarjeta-dashboard contenedor-seccion h-auto" style="height: auto !important;">
                                <div class="encabezado-seccion pb-3 mb-3 border-bottom border-light-subtle">
                                    <div>
                                        <h5 class="titulo-seccion-principal">Perfil profesional</h5>
                                        <small class="descripcion-seccion">Estos datos identifican al pediatra dentro del sistema.</small>
                                    </div>
                                    <div class="icono-stat icono-azul mb-0"><i class="bi bi-person-badge"></i></div>
                                </div>

                                <form action="procedures/actualizarPerfil.php" method="POST">
                                    <input type="hidden" name="accion" value="perfil">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="config_nombres" class="form-label fw-semibold text-secondary small">Nombres</label>
                                            <input type="text" class="form-control" id="config_nombres" name="nombres" value="<?php echo htmlspecialchars($pediatra['nombres']); ?>" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="config_apellidos" class="form-label fw-semibold text-secondary small">Apellidos</label>
                                            <input type="text" class="form-control" id="config_apellidos" name="apellidos" value="<?php echo htmlspecialchars($pediatra['apellidos']); ?>" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="config_cedula" class="form-label fw-semibold text-secondary small">Cédula de identidad</label>
                                            <input type="text" class="form-control" id="config_cedula" value="<?php echo htmlspecialchars($pediatra['cedula']); ?>" readonly style="border-color: #eaf4fe; background-color: #eaf4fe;">
                                            <small class="text-muted">La cédula de acceso no puede modificarse.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="config_mpps" class="form-label fw-semibold text-secondary small">Nro. MPPS</label>
                                            <input type="text" class="form-control" id="config_mpps" value="<?php echo htmlspecialchars($pediatra['mpps']); ?>" readonly style="border-color: #eaf4fe; background-color: #eaf4fe;">
                                            <small class="text-muted">Registro profesional no editable.</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="config_colegio" class="form-label fw-semibold text-secondary small">Colegio médico</label>
                                            <input type="text" class="form-control" id="config_colegio" name="colegio_medico" value="<?php echo htmlspecialchars($pediatra['colegio_medico'] ?? ''); ?>" placeholder="Ej. CM-4567" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-secondary small">Especialidad</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($pediatra['especialidad']); ?>" readonly style="border-color: #eaf4fe; background-color: #eaf4fe;">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="config_email" class="form-label fw-semibold text-secondary small">Correo electrónico</label>
                                            <input type="email" class="form-control" id="config_email" name="email" value="<?php echo htmlspecialchars($pediatra['email']); ?>" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="config_telefono" class="form-label fw-semibold text-secondary small">Teléfono</label>
                                            <input type="text" class="form-control" id="config_telefono" name="telefono" value="<?php echo htmlspecialchars($pediatra['telefono'] ?? ''); ?>" placeholder="Ej. 04126131156" style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                        </div>
                                    </div>

                                    <div class="d-grid d-sm-flex justify-content-sm-end mt-4">
                                        <button type="submit" class="boton-revision border-0 px-4 py-2">Guardar cambios</button>
                                    </div>
                                </form>
                            </div>

                            <!-- SEGURIDAD -->
                            <div class="tarjeta-dashboard contenedor-seccion h-auto mt-4" style="height: auto !important;">
                                <div class="encabezado-seccion pb-3 mb-3 border-bottom border-light-subtle">
                                    <div>
                                        <h5 class="titulo-seccion-principal">Seguridad</h5>
                                        <small class="descripcion-seccion">Cambie su contraseña cuando lo necesite.</small>
                                    </div>
                                    <div class="icono-stat icono-azul mb-0"><i class="bi bi-key"></i></div>
                                </div>

                                <form action="procedures/actualizarPerfil.php" method="POST">
                                    <input type="hidden" name="accion" value="password">

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="config_actual" class="form-label fw-semibold text-secondary small">Contraseña actual</label>
                                            <input type="password" class="form-control" id="config_actual" name="password_actual" placeholder="••••••••" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="config_nueva" class="form-label fw-semibold text-secondary small">Nueva contraseña</label>
                                            <input type="password" class="form-control" id="config_nueva" name="password_nueva" placeholder="Mínimo 6 caracteres" minlength="6" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="config_confirmar" class="form-label fw-semibold text-secondary small">Confirmar contraseña</label>
                                            <input type="password" class="form-control" id="config_confirmar" name="password_confirmar" placeholder="Repita la nueva contraseña" minlength="6" required style="border-color: #eaf4fe; background-color: #f9fcfe;">
                                        </div>
                                    </div>

                                    <div class="d-grid d-sm-flex justify-content-sm-end mt-4">
                                        <button type="submit" class="boton-secundario border-0 px-4 py-2">Actualizar contraseña</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- INFORMACIÓN DE LA CUENTA -->
                        <div class="col-lg-5">
                            <div class="tarjeta-dashboard contenedor-seccion h-auto text-center">
                                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
                                     style="width: 82px; height: 82px; background-color: #eaf4fe; color: #4a80bc; font-size: 1.7rem; font-weight: 700;">
                                    <?php echo htmlspecialchars(mb_strtoupper(mb_substr($pediatra['nombres'], 0, 1) . mb_substr($pediatra['apellidos'], 0, 1))); ?>
                                </div>
                                <h4 class="fw-bold mb-1">Dr(a). <?php echo htmlspecialchars($pediatra['nombres'] . ' ' . $pediatra['apellidos']); ?></h4>
                                <p class="descripcion-seccion mb-3"><?php echo htmlspecialchars($pediatra['especialidad']); ?></p>

                                <span class="badge rounded-pill <?php echo (int) $pediatra['activo'] === 1 ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'; ?> px-3 py-2 mb-4">
                                    <?php echo (int) $pediatra['activo'] === 1 ? 'Cuenta activa' : 'Cuenta inactiva'; ?>
                                </span>

                                <div class="text-start border-top border-light-subtle pt-3">
                                    <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                                        <span class="descripcion-seccion">Cédula</span>
                                        <strong><?php echo htmlspecialchars($pediatra['cedula']); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                                        <span class="descripcion-seccion">MPPS</span>
                                        <strong><?php echo htmlspecialchars($pediatra['mpps']); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                                        <span class="descripcion-seccion">Correo</span>
                                        <strong style="font-size: 0.82rem;"><?php echo htmlspecialchars($pediatra['email']); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-2">
                                        <span class="descripcion-seccion">Miembro desde</span>
                                        <strong><?php echo htmlspecialchars(formatear_fecha($pediatra['created_at'])); ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="tarjeta-dashboard contenedor-seccion h-auto mt-4">
                                <h5 class="titulo-seccion-principal mb-3">Consejo de seguridad</h5>
                                <p class="descripcion-seccion mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Use una contraseña difícil de adivinar y no la comparta. Si sospecha que alguien más la conoce,
                                    cámbiela de inmediato desde esta sección.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php mostrar_alerta_swal(); ?>
</body>

</html>
