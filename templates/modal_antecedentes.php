<?php
/**
 * Modal reutilizable para visualizar y actualizar antecedentes médicos del paciente.
 * Requiere que se hayan cargado procedures/utilidades.php y Bootstrap 5.
 */
$emergencias_lista = lista_datos_emergencia();
$condiciones_lista = lista_condiciones_comunes();
$grupos_lista      = lista_grupos_sanguineos();
?>

<!-- MODAL GESTIÓN DE ANTECEDENTES -->
<div class="modal fade" id="modalAntecedentes" tabindex="-1" aria-labelledby="tituloModalAntecedentes" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-custom">
            
            <div class="modal-header border-bottom border-light-subtle py-3 px-4" style="background-color: #f9fcfe;">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="icono-stat icono-azul d-flex align-items-center justify-content-center rounded-3" style="width: 34px; height: 34px; font-size: 0.95rem;">
                            <i class="bi bi-clipboard2-pulse"></i>
                        </div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="tituloModalAntecedentes">Antecedentes Médicos</h5>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Paciente: <span id="modal_ant_nombre" class="fw-semibold text-primary"></span>
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formModalAntecedentes" action="procedures/actualizarAntecedentes.php" method="POST">
                <input type="hidden" name="id_paciente" id="modal_ant_id_paciente" value="">
                <input type="hidden" name="redirect_to" id="modal_ant_redirect_to" value="pacientes.php">

                <div class="modal-body p-4" style="max-height: calc(85vh - 130px); overflow-y: auto;">
                    
                    <!-- 1. DATOS MÉDICOS CRÍTICOS Y ALERTA DE EMERGENCIA -->
                    <div class="p-3 rounded-4 mb-4" style="background-color: #fff8f8; border: 1.5px solid #fecaca;">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom border-danger-subtle">
                            <div class="d-flex align-items-center gap-2 text-danger fw-bold fs-7 text-uppercase" style="letter-spacing: 0.05em;">
                                <i class="bi bi-exclamation-octagon-fill fs-6"></i>
                                <span>Datos Médicos Críticos y Emergencia</span>
                            </div>
                            <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2 py-1 fs-7">
                                Prioridad Alta
                            </span>
                        </div>

                        <!-- Grupo Sanguíneo y Factor Rh -->
                        <div class="row align-items-center mb-3 g-2">
                            <label for="modal_grupo_sanguineo" class="col-sm-4 col-form-label col-form-label-sm fw-semibold text-danger">
                                <i class="bi bi-droplet-fill me-1"></i> Grupo Sanguíneo / Rh:
                            </label>
                            <div class="col-sm-8">
                                <select class="form-select form-select-sm" id="modal_grupo_sanguineo" name="grupo_sanguineo" style="border-color: #fca5a5; background-color: #ffffff;">
                                    <option value="">No determinado / Desconocido</option>
                                    <?php foreach ($grupos_lista as $grupo): ?>
                                        <option value="<?php echo htmlspecialchars($grupo); ?>"><?php echo htmlspecialchars($grupo); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Checkboxes de Emergencia -->
                        <div class="row g-2">
                            <?php foreach ($emergencias_lista as $idx => $emergencia): ?>
                                <div class="col-md-6">
                                    <div class="form-check p-2 rounded-2 h-100 d-flex align-items-start gap-2" style="background-color: #ffffff; border: 1px solid #fee2e2;">
                                        <input class="form-check-input mt-1 modal-check-emergencia" type="checkbox" name="datos_emergencia[]" value="<?php echo htmlspecialchars($emergencia); ?>" id="modal_eme_<?php echo $idx; ?>" style="accent-color: #dc2626; cursor: pointer;">
                                        <label class="form-check-label small fw-semibold text-secondary" for="modal_eme_<?php echo $idx; ?>" style="cursor: pointer; line-height: 1.3;">
                                            <?php echo htmlspecialchars($emergencia); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 2. CONDICIONES MÉDICAS COMUNES -->
                    <div class="p-3 rounded-4 mb-4" style="background-color: #f8fbff; border: 1.5px solid #dbeafe;">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom border-primary-subtle text-primary fw-bold fs-7 text-uppercase" style="letter-spacing: 0.05em;">
                            <i class="bi bi-heart-pulse-fill fs-6"></i>
                            <span>Condiciones Médicas Comunes</span>
                        </div>

                        <div class="row g-2">
                            <?php foreach ($condiciones_lista as $idx => $condicion): ?>
                                <div class="col-md-6">
                                    <div class="form-check p-2 rounded-2 h-100 d-flex align-items-start gap-2" style="background-color: #ffffff; border: 1px solid #e0f2fe;">
                                        <input class="form-check-input mt-1 modal-check-condicion" type="checkbox" name="condiciones_comunes[]" value="<?php echo htmlspecialchars($condicion); ?>" id="modal_cond_<?php echo $idx; ?>" style="accent-color: #4a80bc; cursor: pointer;">
                                        <label class="form-check-label small fw-semibold text-secondary" for="modal_cond_<?php echo $idx; ?>" style="cursor: pointer; line-height: 1.3;">
                                            <?php echo htmlspecialchars($condicion); ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 3. OTROS ANTECEDENTES NO LISTADOS (DINÁMICO) -->
                    <div class="p-3 rounded-4" style="background-color: #ffffff; border: 1.5px solid #e2e8f0;">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center gap-2 text-secondary fw-bold fs-7 text-uppercase" style="letter-spacing: 0.05em;">
                                <i class="bi bi-pencil-square fs-6 text-primary"></i>
                                <span>Otros Antecedentes (No listados)</span>
                            </div>
                            <small class="text-muted">Añada antecedentes personalizados</small>
                        </div>
                        <p class="text-muted small mb-3">Si el paciente presenta alguna condición o antecedente no reflejado en las casillas anteriores, escríbalo aquí:</p>

                        <!-- Contenedor dinámico de campos de texto -->
                        <div id="modal_contenedor_otros"></div>

                        <!-- Botón para agregar otro cuadro de texto -->
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-2 py-1 px-3 rounded-pill fw-semibold" id="modal_btn_agregar_otro" style="font-size: 0.82rem;">
                                <i class="bi bi-plus-circle-fill"></i> Agregar otro antecedente
                            </button>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top border-light-subtle py-2 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-light border-0 px-3 py-2 fw-semibold" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="boton-revision border-0 px-4 py-2 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle"></i> Guardar Antecedentes
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
(function() {
    // Función auxiliar para escapar texto en HTML
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Función para agregar una fila dinámica de "Otro antecedente" al modal
    window.agregarFilaOtroAntecedenteModal = function(valor = '') {
        const contenedor = document.getElementById('modal_contenedor_otros');
        if (!contenedor) return;

        const fila = document.createElement('div');
        fila.className = 'input-group mb-2 fila-otro-modal';
        fila.innerHTML = `
            <span class="input-group-text bg-white border-end-0" style="border-color: #eaf4fe; color: #4a80bc;">
                <i class="bi bi-dot fs-5"></i>
            </span>
            <input type="text" name="otros_antecedentes[]" class="form-control border-start-0" 
                   placeholder="Especifique otro antecedente médico..." value="${escapeHtml(valor)}"
                   style="border-color: #eaf4fe; background-color: #f9fcfe;">
            <button type="button" class="btn btn-outline-danger btn-eliminar-otro-modal px-3" title="Eliminar este antecedente">
                <i class="bi bi-trash3"></i>
            </button>
        `;

        fila.querySelector('.btn-eliminar-otro-modal').addEventListener('click', function() {
            const totalFilas = contenedor.querySelectorAll('.fila-otro-modal').length;
            if (totalFilas > 1) {
                fila.remove();
            } else {
                fila.querySelector('input').value = '';
            }
        });

        contenedor.appendChild(fila);
    };

    document.addEventListener('DOMContentLoaded', function() {
        const btnAgregarOtro = document.getElementById('modal_btn_agregar_otro');
        if (btnAgregarOtro) {
            btnAgregarOtro.addEventListener('click', function() {
                agregarFilaOtroAntecedenteModal('');
            });
        }

        // Delegación de eventos para abrir el modal desde cualquier botón .btn-abrir-antecedentes
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-abrir-antecedentes');
            if (!btn) return;

            e.preventDefault();

            const id = btn.getAttribute('data-id') || '';
            const nombre = btn.getAttribute('data-nombre') || 'Paciente';
            const redirect = btn.getAttribute('data-redirect') || window.location.pathname.split('/').pop() || 'pacientes.php';
            const rawAntecedentes = btn.getAttribute('data-antecedentes') || '';

            // Asignar ID, nombre y redirect
            const inputId = document.getElementById('modal_ant_id_paciente');
            const spanNombre = document.getElementById('modal_ant_nombre');
            const inputRedirect = document.getElementById('modal_ant_redirect_to');
            if (inputId) inputId.value = id;
            if (spanNombre) spanNombre.textContent = nombre;
            if (inputRedirect) inputRedirect.value = redirect;

            // Desmarcar todos los checkboxes
            document.querySelectorAll('.modal-check-emergencia, .modal-check-condicion').forEach(chk => {
                chk.checked = false;
            });

            // Reset grupo sanguíneo
            const selectGrupo = document.getElementById('modal_grupo_sanguineo');
            if (selectGrupo) selectGrupo.value = '';

            // Limpiar contenedor dinámico
            const contenedorOtros = document.getElementById('modal_contenedor_otros');
            if (contenedorOtros) contenedorOtros.innerHTML = '';

            // Parsear datos de antecedentes
            let datos = { emergencia: [], condiciones: [], grupo_sanguineo: '', otros: [] };
            if (rawAntecedentes.trim() !== '') {
                const trim = rawAntecedentes.trim();
                if (trim.startsWith('{') && trim.endsWith('}')) {
                    try {
                        const parsed = JSON.parse(trim);
                        if (parsed && typeof parsed === 'object') {
                            datos.emergencia = Array.isArray(parsed.emergencia) ? parsed.emergencia : [];
                            datos.condiciones = Array.isArray(parsed.condiciones) ? parsed.condiciones : [];
                            datos.grupo_sanguineo = parsed.grupo_sanguineo || '';
                            datos.otros = Array.isArray(parsed.otros) ? parsed.otros : [];
                        }
                    } catch (err) {
                        datos.otros = [trim];
                    }
                } else {
                    // Texto plano legacy: separar por líneas
                    const lineas = trim.split(/\r?\n/).map(l => l.trim()).filter(l => l !== '');
                    datos.otros = lineas.length > 0 ? lineas : [trim];
                }
            }

            // Marcar grupo sanguíneo
            if (selectGrupo && datos.grupo_sanguineo) {
                selectGrupo.value = datos.grupo_sanguineo;
            }

            // Marcar checkboxes de emergencias
            if (datos.emergencia.length > 0) {
                document.querySelectorAll('.modal-check-emergencia').forEach(chk => {
                    if (datos.emergencia.includes(chk.value)) {
                        chk.checked = true;
                    }
                });
            }

            // Marcar checkboxes de condiciones comunes
            if (datos.condiciones.length > 0) {
                document.querySelectorAll('.modal-check-condicion').forEach(chk => {
                    if (datos.condiciones.includes(chk.value)) {
                        chk.checked = true;
                    }
                });
            }

            // Cargar otros antecedentes dinámicos
            if (datos.otros.length > 0) {
                datos.otros.forEach(otro => {
                    agregarFilaOtroAntecedenteModal(otro);
                });
            } else {
                // Siempre añadir al menos un campo vacío
                agregarFilaOtroAntecedenteModal('');
            }

            // Abrir el modal de Bootstrap
            const modalEl = document.getElementById('modalAntecedentes');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        });
    });
})();
</script>
