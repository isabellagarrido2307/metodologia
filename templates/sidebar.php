<!-- BARRA LATERAL (SIDEBAR REUTILIZABLE) -->
<nav class="col-md-3 col-lg-2 barra-lateral d-flex flex-column justify-content-between min-vh-100">
    <div>
        <!-- Logotipo / Marca -->
        <div class="logo">
            <div class="contenedor-logo">
                <img src="img/medi_logo.svg" alt="Logo MediPortal" class="d-block mb-3" style="width: 150px;">
            </div>
        </div>

        <!-- Sección Workspace -->
        <div class="bloque-menu">
            <ul class="nav flex-column lista-enlaces">
                <li class="nav-item">
                    <a href="dashboard.php" class="enlace-navegacion <?php echo ($seccion_activa === 'inicio') ? 'activo' : ''; ?>">
                        <i class="bi bi-house-door"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pacientes.php" class="enlace-navegacion <?php echo ($seccion_activa === 'pacientes') ? 'activo' : ''; ?>">
                        <i class="bi bi-people"></i> Pacientes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="historiales.php" class="enlace-navegacion <?php echo ($seccion_activa === 'historiales') ? 'activo' : ''; ?>">
                        <i class="bi bi-journal-medical"></i> Historiales clínicos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="vacunacion.php" class="enlace-navegacion <?php echo ($seccion_activa === 'vacunacion') ? 'activo' : ''; ?>">
                        <i class="bi bi-shield-plus"></i> Registros de Vacunación
                    </a>
                </li>
                <li class="nav-item">
                    <a href="ayuda.php" class="enlace-navegacion <?php echo ($seccion_activa === 'ayuda') ? 'activo' : ''; ?>">
                        <i class="bi bi-file-earmark-text"></i> ¿Cómo usar el sistema?
                    </a>
                </li>
                <li class="nav-item">
                    <a href="configuracion.php" class="enlace-navegacion <?php echo ($seccion_activa === 'configuracion') ? 'activo' : ''; ?>">
                        <i class="bi bi-gear"></i> Configuración
                    </a>
                </li>
                <li class="nav-item">
                    <a href="procedures/cerrarSesion.php" class="enlace-navegacion">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>