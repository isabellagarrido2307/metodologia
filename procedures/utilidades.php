<?php
/**
 * Utilidades compartidas por las vistas de MediPortal.
 */

if (!function_exists('nombre_paciente')) {
    /**
     * Devuelve el nombre completo de un paciente a partir de sus columnas.
     */
    function nombre_paciente(array $paciente): string
    {
        $partes = [
            $paciente['primer_nombre'] ?? '',
            $paciente['segundo_nombre'] ?? '',
            $paciente['primer_apellido'] ?? '',
            $paciente['segundo_apellido'] ?? '',
        ];

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($partes))));
    }
}

if (!function_exists('iniciales_paciente')) {
    /**
     * Devuelve las iniciales usadas en los avatares del sistema.
     */
    function iniciales_paciente(array $paciente): string
    {
        $nombre = $paciente['primer_nombre'] ?? '';
        $apellido = $paciente['primer_apellido'] ?? '';

        if ($nombre === '' && $apellido === '') {
            return 'P';
        }

        return mb_strtoupper(mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1));
    }
}

if (!function_exists('nombre_pediatra')) {
    /**
     * Devuelve el nombre completo de un pediatra.
     */
    function nombre_pediatra(array $pediatra): string
    {
        $partes = [
            $pediatra['nombres'] ?? $pediatra['ped_nombres'] ?? '',
            $pediatra['apellidos'] ?? $pediatra['ped_apellidos'] ?? '',
        ];

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($partes))));
    }
}

if (!function_exists('calcular_edad')) {
    /**
     * Calcula la edad legible de una persona a partir de su fecha de nacimiento.
     */
    function calcular_edad(?string $fecha_nacimiento): string
    {
        if (empty($fecha_nacimiento)) {
            return 'No registrada';
        }

        try {
            $nacimiento = new DateTime($fecha_nacimiento);
            $hoy = new DateTime('today');
        } catch (Exception $e) {
            return 'No disponible';
        }

        if ($nacimiento > $hoy) {
            return 'No disponible';
        }

        $diferencia = $hoy->diff($nacimiento);

        if ($diferencia->y > 0) {
            return $diferencia->y . ' año' . ($diferencia->y === 1 ? '' : 's');
        }

        if ($diferencia->m > 0) {
            return $diferencia->m . ' mes' . ($diferencia->m === 1 ? '' : 'es');
        }

        return $diferencia->d . ' día' . ($diferencia->d === 1 ? '' : 's');
    }
}

if (!function_exists('formatear_fecha')) {
    /**
     * Formatea una fecha de la base de datos para mostrarla en pantalla.
     */
    function formatear_fecha(?string $fecha, string $formato = 'd/m/Y', string $fallback = '—'): string
    {
        if (empty($fecha)) {
            return $fallback;
        }

        $timestamp = strtotime($fecha);

        return $timestamp ? date($formato, $timestamp) : $fallback;
    }
}

if (!function_exists('etiqueta_sexo')) {
    function etiqueta_sexo(?string $sexo): string
    {
        return match ($sexo) {
            'M' => 'Masculino',
            'F' => 'Femenino',
            default => 'No registrado',
        };
    }
}

if (!function_exists('resumen_texto')) {
    /**
     * Recorta un texto largo para usarlo dentro de una tabla.
     */
    function resumen_texto(?string $texto, int $limite = 70): string
    {
        $texto = trim((string) $texto);

        if ($texto === '') {
            return '—';
        }

        return mb_strlen($texto) > $limite
            ? rtrim(mb_substr($texto, 0, $limite)) . '…'
            : $texto;
    }
}

if (!function_exists('tabla_existe')) {
    /**
     * Comprueba que una tabla exista para que las vistas no fallen si todavía
     * no se ha ejecutado el script database/esquema_clinico.sql.
     */
    function tabla_existe(mysqli $conexion, string $tabla): bool
    {
        try {
            $stmt = $conexion->prepare('SELECT 1 FROM information_schema.TABLES
                                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
                                        LIMIT 1');
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param('s', $tabla);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $existe = $resultado && $resultado->num_rows > 0;
            $stmt->close();

            return $existe;
        } catch (Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('estado_dosis')) {
    /**
     * Calcula la etiqueta y el color de un registro de vacunación.
     */
    function estado_dosis(array $vacuna): array
    {
        $proxima = $vacuna['proxima_dosis'] ?? null;

        if (empty($proxima)) {
            return ['texto' => 'Aplicada', 'clase' => 'bg-success-subtle text-success-emphasis'];
        }

        $hoy = strtotime(date('Y-m-d'));
        $fecha_proxima = strtotime($proxima);

        if ($fecha_proxima < $hoy) {
            return ['texto' => 'Refuerzo vencido', 'clase' => 'bg-danger-subtle text-danger-emphasis'];
        }

        if ($fecha_proxima <= strtotime('+30 days', $hoy)) {
            return ['texto' => 'Próxima dosis', 'clase' => 'bg-warning-subtle text-warning-emphasis'];
        }

        return ['texto' => 'Programada', 'clase' => 'bg-info-subtle text-info-emphasis'];
    }
}

if (!function_exists('mostrar_alerta_swal')) {
    /**
     * Imprime el mensaje SweetAlert pendiente en la sesión.
     */
    function mostrar_alerta_swal(): void
    {
        if (!isset($_SESSION['swal_message'])) {
            return;
        }

        $mensaje = $_SESSION['swal_message'];
        unset($_SESSION['swal_message']);

        echo '<script>document.addEventListener("DOMContentLoaded", function () { Swal.fire('
            . json_encode($mensaje, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
            . '); });</script>';
    }
}

if (!function_exists('lista_datos_emergencia')) {
    /**
     * Lista de condiciones críticas y datos de emergencia pediátrica.
     */
    function lista_datos_emergencia(?mysqli $mysqli = null): array
    {
        if ($mysqli) {
            try {
                $res = $mysqli->query("SELECT nombre_antecedente FROM cat_antecedentes WHERE es_critico = 1 ORDER BY id ASC");
                if ($res && $res->num_rows > 0) {
                    $lista = [];
                    while ($r = $res->fetch_assoc()) {
                        $n = $r['nombre_antecedente'];
                        if (!str_starts_with($n, 'Grupo Sanguíneo: ')) {
                            $lista[] = $n;
                        }
                    }
                    if (!empty($lista)) {
                        return $lista;
                    }
                }
            } catch (Throwable $e) {}
        }

        return [
            'Alergia a Penicilinas / Amoxicilina / Betalactámicos',
            'Alergia a AINEs (Ibuprofeno, Aspirina, Dipirona)',
            'Alergia a Sulfas u otros antibióticos',
            'Alergia Alimentaria Severa (Maní, Frutos secos, Leche, Huevo)',
            'Alergia a Picaduras de Insectos / Abejas',
            'Alergia al Látex',
            'Asma Bronquial / Hiperreactividad Bronquial',
            'Diabetes Mellitus Tipo 1 (Insulindependiente)',
            'Cardiopatía Congénita / Arritmia cardíaca',
            'Tratamiento Médico Crónico / Medicación Continua Vital',
        ];
    }
}

if (!function_exists('lista_condiciones_comunes')) {
    /**
     * Lista de condiciones comunes y antecedentes pediátricos frecuentes.
     */
    function lista_condiciones_comunes(?mysqli $mysqli = null): array
    {
        if ($mysqli) {
            try {
                $res = $mysqli->query("SELECT nombre_antecedente FROM cat_antecedentes WHERE es_critico = 0 ORDER BY id ASC");
                if ($res && $res->num_rows > 0) {
                    $lista = [];
                    while ($r = $res->fetch_assoc()) {
                        $lista[] = $r['nombre_antecedente'];
                    }
                    if (!empty($lista)) {
                        return $lista;
                    }
                }
            } catch (Throwable $e) {}
        }

        return [
            'Rinitis Alérgica / Sinusitis Crónica',
            'Enfermedad Celíaca / Intolerancia a la Lactosa',
            'Reflujo Gastroesofágico (ERGE)',
            'Prematurez (< 37 semanas)',
            'Asfixia perinatal / Hipoxia',
            'Ictericia neonatal requiriendo fototerapia',
            'Cesárea de emergencia',
            'Diabetes mellitus en familiares directos',
            'Hipertensión arterial familiar',
            'Atopia / Dermatitis atópica familiar',
            'Antecedente familiar de asma',
        ];
    }
}

if (!function_exists('lista_grupos_sanguineos')) {
    /**
     * Opciones de grupo sanguíneo y factor Rh.
     */
    function lista_grupos_sanguineos(): array
    {
        return ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
    }
}

if (!function_exists('obtener_antecedentes_paciente')) {
    /**
     * Obtiene los antecedentes registrados de un paciente desde la tabla relacional paciente_antecedentes.
     */
    function obtener_antecedentes_paciente(mysqli $mysqli, int $paciente_id): array
    {
        $resultado = [
            'emergencia' => [],
            'condiciones' => [],
            'grupo_sanguineo' => '',
            'otros' => [],
        ];

        if ($paciente_id <= 0) {
            return $resultado;
        }

        try {
            $stmt = $mysqli->prepare("SELECT ca.nombre_antecedente, ca.es_critico, ca.categoria, pa.observacion_corta 
                                      FROM paciente_antecedentes pa 
                                      INNER JOIN cat_antecedentes ca ON ca.id = pa.antecedente_id 
                                      WHERE pa.paciente_id = ?
                                      ORDER BY ca.es_critico DESC, ca.nombre_antecedente ASC");
            if (!$stmt) {
                return $resultado;
            }

            $stmt->bind_param('i', $paciente_id);
            $stmt->execute();
            $res = $stmt->get_result();

            while ($fila = $res->fetch_assoc()) {
                $nombre = $fila['nombre_antecedente'];
                if (str_starts_with($nombre, 'Grupo Sanguíneo: ')) {
                    $resultado['grupo_sanguineo'] = trim(str_replace('Grupo Sanguíneo: ', '', $nombre));
                } elseif (!empty($fila['observacion_corta'])) {
                    $resultado['otros'][] = $nombre . ' (' . $fila['observacion_corta'] . ')';
                } elseif ((int)$fila['es_critico'] === 1) {
                    $resultado['emergencia'][] = $nombre;
                } else {
                    $resultado['condiciones'][] = $nombre;
                }
            }
            $stmt->close();
        } catch (Throwable $e) {
            // Error silencioso
        }

        return $resultado;
    }
}

if (!function_exists('guardar_antecedentes_paciente')) {
    /**
     * Guarda los antecedentes de un paciente en las tablas relacionales cat_antecedentes y paciente_antecedentes.
     */
    function guardar_antecedentes_paciente(mysqli $mysqli, int $paciente_id, array $emergencia, array $condiciones, string $grupo_sanguineo, array $otros): void
    {
        if ($paciente_id <= 0) {
            return;
        }

        // 1. Eliminar asignaciones previas del paciente
        $stmt_del = $mysqli->prepare("DELETE FROM paciente_antecedentes WHERE paciente_id = ?");
        if ($stmt_del) {
            $stmt_del->bind_param('i', $paciente_id);
            $stmt_del->execute();
            $stmt_del->close();
        }

        // Función anónima para obtener o crear el antecedente en cat_antecedentes
        $obtener_o_crear_cat = function(string $nombre, string $categoria, int $es_critico) use ($mysqli): int {
            $nombre = trim($nombre);
            if ($nombre === '') return 0;

            $stmt = $mysqli->prepare("SELECT id FROM cat_antecedentes WHERE nombre_antecedente = ? LIMIT 1");
            if (!$stmt) return 0;
            $stmt->bind_param('s', $nombre);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($fila = $res->fetch_assoc()) {
                $stmt->close();
                return (int)$fila['id'];
            }
            $stmt->close();

            $stmt_ins = $mysqli->prepare("INSERT INTO cat_antecedentes (categoria, nombre_antecedente, es_critico) VALUES (?, ?, ?)");
            if (!$stmt_ins) return 0;
            $stmt_ins->bind_param('ssi', $categoria, $nombre, $es_critico);
            $stmt_ins->execute();
            $nuevo_id = $stmt_ins->insert_id;
            $stmt_ins->close();
            return $nuevo_id;
        };

        $stmt_pa = $mysqli->prepare("INSERT INTO paciente_antecedentes (paciente_id, antecedente_id, observacion_corta) VALUES (?, ?, ?)");
        if (!$stmt_pa) return;

        // 2. Guardar Grupo Sanguíneo si fue especificado
        $grupo_sanguineo = trim($grupo_sanguineo);
        if ($grupo_sanguineo !== '') {
            $cat_id = $obtener_o_crear_cat('Grupo Sanguíneo: ' . $grupo_sanguineo, 'Patológico', 1);
            if ($cat_id > 0) {
                $obs = null;
                $stmt_pa->bind_param('iis', $paciente_id, $cat_id, $obs);
                $stmt_pa->execute();
            }
        }

        // 3. Guardar Alertas de Emergencia marcadas
        foreach ($emergencia as $item) {
            $item = trim((string)$item);
            if ($item === '') continue;
            $cat_id = $obtener_o_crear_cat($item, 'Alérgico', 1);
            if ($cat_id > 0) {
                $obs = null;
                $stmt_pa->bind_param('iis', $paciente_id, $cat_id, $obs);
                $stmt_pa->execute();
            }
        }

        // 4. Guardar Condiciones Comunes marcadas
        foreach ($condiciones as $item) {
            $item = trim((string)$item);
            if ($item === '') continue;
            $cat_id = $obtener_o_crear_cat($item, 'Patológico', 0);
            if ($cat_id > 0) {
                $obs = null;
                $stmt_pa->bind_param('iis', $paciente_id, $cat_id, $obs);
                $stmt_pa->execute();
            }
        }

        // 5. Guardar Otros Antecedentes personalizados
        foreach ($otros as $item) {
            $item = trim((string)$item);
            if ($item === '') continue;
            $cat_id = $obtener_o_crear_cat($item, 'Patológico', 0);
            if ($cat_id > 0) {
                $obs = null;
                $stmt_pa->bind_param('iis', $paciente_id, $cat_id, $obs);
                $stmt_pa->execute();
            }
        }

        $stmt_pa->close();
    }
}

if (!function_exists('parsear_antecedentes')) {
    /**
     * Deserializa los antecedentes guardados (JSON, array estructurado o texto plano).
     */
    function parsear_antecedentes($antecedentes_raw): array
    {
        $vacio = [
            'emergencia' => [],
            'condiciones' => [],
            'grupo_sanguineo' => '',
            'otros' => [],
        ];

        if (empty($antecedentes_raw)) {
            return $vacio;
        }

        if (is_array($antecedentes_raw)) {
            return [
                'emergencia' => array_values(array_filter($antecedentes_raw['emergencia'] ?? [])),
                'condiciones' => array_values(array_filter($antecedentes_raw['condiciones'] ?? [])),
                'grupo_sanguineo' => trim((string)($antecedentes_raw['grupo_sanguineo'] ?? '')),
                'otros' => array_values(array_filter($antecedentes_raw['otros'] ?? [])),
            ];
        }

        $trim = trim((string)$antecedentes_raw);
        if ($trim === '') {
            return $vacio;
        }

        if (str_starts_with($trim, '{') && str_ends_with($trim, '}')) {
            $data = json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return [
                    'emergencia' => isset($data['emergencia']) && is_array($data['emergencia'])
                        ? array_values(array_filter($data['emergencia'], fn($v) => trim((string)$v) !== ''))
                        : [],
                    'condiciones' => isset($data['condiciones']) && is_array($data['condiciones'])
                        ? array_values(array_filter($data['condiciones'], fn($v) => trim((string)$v) !== ''))
                        : [],
                    'grupo_sanguineo' => trim((string)($data['grupo_sanguineo'] ?? '')),
                    'otros' => isset($data['otros']) && is_array($data['otros'])
                        ? array_values(array_filter($data['otros'], fn($v) => trim((string)$v) !== ''))
                        : [],
                ];
            }
        }

        $lineas = preg_split('/[\r\n]+/', $trim);
        $otros = [];
        foreach ($lineas as $linea) {
            $limpia = trim($linea);
            if ($limpia !== '') {
                $otros[] = $limpia;
            }
        }

        $vacio['otros'] = $otros;
        return $vacio;
    }
}

        $trim = trim($antecedentes_raw);
        if ($trim === '') {
            return $vacio;
        }

        // Si viene en formato JSON estructurado
        if (str_starts_with($trim, '{') && str_ends_with($trim, '}')) {
            $data = json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return [
                    'emergencia' => isset($data['emergencia']) && is_array($data['emergencia'])
                        ? array_values(array_filter($data['emergencia'], fn($v) => trim((string)$v) !== ''))
                        : [],
                    'condiciones' => isset($data['condiciones']) && is_array($data['condiciones'])
                        ? array_values(array_filter($data['condiciones'], fn($v) => trim((string)$v) !== ''))
                        : [],
                    'grupo_sanguineo' => trim((string)($data['grupo_sanguineo'] ?? '')),
                    'otros' => isset($data['otros']) && is_array($data['otros'])
                        ? array_values(array_filter($data['otros'], fn($v) => trim((string)$v) !== ''))
                        : [],
                ];
            }
        }

        // Si es texto plano previo, se distribuye en 'otros'
        $lineas = preg_split('/[\r\n]+/', $trim);
        $otros = [];
        foreach ($lineas as $linea) {
            $limpia = trim($linea);
            if ($limpia !== '') {
                $otros[] = $limpia;
            }
        }

        $vacio['otros'] = $otros;
        return $vacio;
    }
}

if (!function_exists('empaquetar_antecedentes')) {
    /**
     * Serializa los antecedentes en una estructura JSON para almacenarla en el campo TEXT.
     */
    function empaquetar_antecedentes(array $emergencia, array $condiciones, string $grupo_sanguineo, array $otros): string
    {
        $emergencia_limpia = [];
        foreach ($emergencia as $item) {
            $t = trim((string)$item);
            if ($t !== '' && !in_array($t, $emergencia_limpia, true)) {
                $emergencia_limpia[] = $t;
            }
        }

        $condiciones_limpias = [];
        foreach ($condiciones as $item) {
            $t = trim((string)$item);
            if ($t !== '' && !in_array($t, $condiciones_limpias, true)) {
                $condiciones_limpias[] = $t;
            }
        }

        $grupo_limpio = trim($grupo_sanguineo);

        $otros_limpios = [];
        foreach ($otros as $item) {
            $t = trim((string)$item);
            if ($t !== '') {
                $otros_limpios[] = $t;
            }
        }

        if (empty($emergencia_limpia) && empty($condiciones_limpias) && $grupo_limpio === '' && empty($otros_limpios)) {
            return '';
        }

        return json_encode([
            'emergencia' => $emergencia_limpia,
            'condiciones' => $condiciones_limpias,
            'grupo_sanguineo' => $grupo_limpio,
            'otros' => $otros_limpios,
        ], JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('renderizar_antecedentes_html')) {
    /**
     * Genera la vista visual de los antecedentes para la ficha o modal.
     */
    function renderizar_antecedentes_html(?string $antecedentes_raw): string
    {
        $datos = parsear_antecedentes($antecedentes_raw);
        $vacio = empty($datos['emergencia']) && empty($datos['condiciones']) && empty($datos['grupo_sanguineo']) && empty($datos['otros']);

        if ($vacio) {
            return '<span class="text-muted d-block py-1" style="font-size: 0.86rem;"><i class="bi bi-info-circle me-1"></i>Sin antecedentes registrados.</span>';
        }

        $html = '<div class="d-flex flex-column gap-3">';

        // 1. Datos críticos y Alertas de emergencia
        if (!empty($datos['grupo_sanguineo']) || !empty($datos['emergencia'])) {
            $html .= '<div class="p-3 rounded-3" style="background-color: #fff9f9; border: 1.5px solid #fecaca;">';
            $html .= '<div class="d-flex align-items-center gap-2 mb-2 text-danger fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 0.81rem;">';
            $html .= '<i class="bi bi-exclamation-octagon-fill"></i> Alertas y Datos Críticos de Emergencia';
            $html .= '</div>';
            $html .= '<div class="d-flex flex-wrap gap-2 align-items-center">';

            if (!empty($datos['grupo_sanguineo'])) {
                $html .= '<span class="badge bg-danger text-white px-2 py-1 shadow-sm" style="font-size: 0.8rem; font-weight: 600;"><i class="bi bi-droplet-fill me-1"></i>Grupo: ' . htmlspecialchars($datos['grupo_sanguineo']) . '</span>';
            }

            foreach ($datos['emergencia'] as $item) {
                $html .= '<span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-2 py-1" style="font-size: 0.8rem; font-weight: 500;"><i class="bi bi-exclamation-triangle-fill me-1"></i>' . htmlspecialchars($item) . '</span>';
            }
            $html .= '</div></div>';
        }

        // 2. Condiciones comunes
        if (!empty($datos['condiciones'])) {
            $html .= '<div class="p-3 rounded-3" style="background-color: #f8fbff; border: 1.5px solid #dbeafe;">';
            $html .= '<div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 0.81rem;">';
            $html .= '<i class="bi bi-heart-pulse-fill"></i> Condiciones Médicas Comunes';
            $html .= '</div>';
            $html .= '<div class="d-flex flex-wrap gap-2">';
            foreach ($datos['condiciones'] as $cond) {
                $html .= '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle px-2 py-1" style="font-size: 0.8rem; font-weight: 500;"><i class="bi bi-check-circle-fill me-1"></i>' . htmlspecialchars($cond) . '</span>';
            }
            $html .= '</div></div>';
        }

        // 3. Otros antecedentes
        if (!empty($datos['otros'])) {
            $html .= '<div class="p-3 rounded-3" style="background-color: #ffffff; border: 1.5px solid #e2e8f0;">';
            $html .= '<div class="d-flex align-items-center gap-2 mb-2 text-secondary fw-bold text-uppercase" style="letter-spacing: 0.04em; font-size: 0.81rem;">';
            $html .= '<i class="bi bi-card-text"></i> Otros Antecedentes Registrados';
            $html .= '</div>';
            $html .= '<ul class="mb-0 ps-3 text-secondary" style="font-size: 0.85rem; line-height: 1.45;">';
            foreach ($datos['otros'] as $otro) {
                $html .= '<li class="mb-1">' . htmlspecialchars($otro) . '</li>';
            }
            $html .= '</ul></div>';
        }

        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('resumen_antecedentes_texto')) {
    /**
     * Genera un resumen compacto en texto plano para tablas o badges.
     */
    function resumen_antecedentes_texto(?string $antecedentes_raw, int $limite = 50): string
    {
        $datos = parsear_antecedentes($antecedentes_raw);
        $items = [];
        if (!empty($datos['grupo_sanguineo'])) {
            $items[] = 'Grupo ' . $datos['grupo_sanguineo'];
        }
        foreach ($datos['emergencia'] as $e) {
            $items[] = $e;
        }
        foreach ($datos['condiciones'] as $c) {
            $items[] = $c;
        }
        foreach ($datos['otros'] as $o) {
            $items[] = $o;
        }

        if (empty($items)) {
            return 'Sin antecedentes';
        }

        $texto = implode(', ', $items);
        return mb_strlen($texto) > $limite ? mb_substr($texto, 0, $limite) . '…' : $texto;
    }
}
