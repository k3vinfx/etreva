<?php
require_once __DIR__ . '/avatar.php';
require_once __DIR__ . '/catalogoI18n.php';
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AtraccionesModel {

    private $pdo;

    public function __CONSTRUCT() {
        try {
            $this->pdo = Database::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER: parsear tiempo_estimado desde JSON real de la BD
    //
    // Formato BD: {"visita": "2 horas", "traslado": "15 minutos"}
    //             {"visita": "4 dias",  "traslado": "4 horas 30 minutos"}
    //             {"visita": "45 minutos", "traslado": "10 minutos"}
    //
    // Retorna array:
    //   ['tipo'        => 'minutos'|'dias',
    //    'valor'       => N,           // días o minutos reales
    //    'label'       => string,
    //    'restriccion' => N]           // minutos de restricción para validar
    //
    // Reglas de restricción:
    //   - dias      → 0   (sin restricción de hora, solo aviso)
    //   - > 4 horas → 60  (1 hora de restricción)
    //   - ≤ 4 horas → minutos reales
    //   - sin dato  → 30  (mínimo por defecto)
    // ─────────────────────────────────────────────────────────────────────────
    private function parsearTiempoEstimado($tiempo_estimado): array {
        $default = ['tipo'=>'minutos','valor'=>30,'label'=>'Aprox. 30 min','restriccion'=>30];

        if (is_null($tiempo_estimado) || $tiempo_estimado === '' || $tiempo_estimado === []) {
            return $default;
        }

        // Decodificar si viene como string JSON
        if (is_string($tiempo_estimado)) {
            $decoded = json_decode($tiempo_estimado, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $tiempo_estimado = $decoded;
            } else {
                // String plano numérico
                if (is_numeric($tiempo_estimado)) {
                    $v = max(30, intval($tiempo_estimado));
                    return ['tipo'=>'minutos','valor'=>$v,'label'=>$v.' min',
                            'restriccion'=> $v > 240 ? 60 : $v];
                }
                return $default;
            }
        }

        // Número directo (legacy)
        if (is_numeric($tiempo_estimado)) {
            $v = max(30, intval($tiempo_estimado));
            return ['tipo'=>'minutos','valor'=>$v,'label'=>$v.' min',
                    'restriccion'=> $v > 240 ? 60 : $v];
        }

        if (!is_array($tiempo_estimado)) return $default;

        // ── Extraer campo "visita" ──
        $visita = strtolower(trim($tiempo_estimado['visita'] ?? ''));
        if (empty($visita)) return $default;

        // ¿Días?
        if (preg_match('/(\d+)\s*d[ií]a/u', $visita, $m)) {
            $nd    = intval($m[1]);
            $label = $nd . ($nd === 1 ? ' día' : ' días');
            return ['tipo'=>'dias','valor'=>$nd,'label'=>$label,'restriccion'=>0];
        }

        // Horas y/o minutos
        $mins = 0;
        if (preg_match('/(\d+)\s*hora/u', $visita, $h)) $mins += intval($h[1]) * 60;
        if (preg_match('/(\d+)\s*minuto/u', $visita, $mi)) $mins += intval($mi[1]);
        if ($mins === 0) $mins = 30;

        // Etiqueta legible
        if ($mins >= 60) {
            $hh  = intdiv($mins, 60);
            $mm  = $mins % 60;
            $lbl = $hh . 'h' . ($mm ? ' ' . $mm . 'min' : '');
        } else {
            $lbl = $mins . ' min';
        }

        $restriccion = $mins > 240 ? 60 : $mins;
        return ['tipo'=>'minutos','valor'=>$mins,'label'=>$lbl,'restriccion'=>$restriccion];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Verificar conflicto de horario con itinerarios existentes del mismo día
    //
    // Lógica:
    //   - Si el itinerario existente es de tipo "dias" → solo aviso informativo
    //     (la persona pudo haberse retirado antes), NO bloquea.
    //   - Si el itinerario nuevo o existente tiene > 4 hs → restricción = 60 min
    //   - En otros casos → restricción = tiempo real
    //   - Si no hay tiempo_estimado → 30 min mínimo
    // ─────────────────────────────────────────────────────────────────────────
    public function verificarConflictoHorario(
        int      $id_usuario,
        int      $atraccion_id_nueva,
        DateTime $dt_nueva_visita
    ): array {

        $fecha_dia = $dt_nueva_visita->format('Y-m-d');
        $tz        = new DateTimeZone('America/La_Paz');

        /* Tiempo estimado de la atracción nueva */
        try {
            $stmt = $this->pdo->prepare(
                "SELECT tiempo_estimado FROM catalogo.atracciones WHERE id = ?"
            );
            $stmt->execute([$atraccion_id_nueva]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("verificarConflictoHorario (nueva): " . $e->getMessage());
            return ['hay_conflicto' => false];
        }

        $te_nueva   = $this->parsearTiempoEstimado($row['tiempo_estimado'] ?? null);
        $mins_nueva = $te_nueva['restriccion'];   // usa restricción, no valor real

        // Si la nueva es de días → sin restricción de hora, pero aviso si hay solapamiento
        $nueva_es_dias = $te_nueva['tipo'] === 'dias';

        $dt_nueva_fin = $mins_nueva > 0
            ? (clone $dt_nueva_visita)->modify("+{$mins_nueva} minutes")
            : (clone $dt_nueva_visita)->modify('+30 minutes');

        /* Itinerarios del usuario ese día */
        try {
            $stmt = $this->pdo->prepare("
                SELECT i.id,
                       i.nombre,
                       i.fecha_visita,
                       a.nombre          AS atraccion_nombre,
                       a.tiempo_estimado AS tiempo_estimado
                FROM   itinerarios.itinerarios i
                JOIN   catalogo.atracciones a ON a.id = i.atracciones_id
                WHERE  i.usuario_id       = ?
                  AND  DATE(i.fecha_visita) = ?
                ORDER  BY i.fecha_visita ASC
            ");
            $stmt->execute([$id_usuario, $fecha_dia]);
            $existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("verificarConflictoHorario (dia): " . $e->getMessage());
            return ['hay_conflicto' => false];
        }

        if (empty($existentes)) return ['hay_conflicto' => false];

        foreach ($existentes as $ex) {
            /* Parsear datetime de la BD */
            $fv = $ex['fecha_visita'];
            $dt_ex = DateTime::createFromFormat('Y-m-d H:i:s', $fv, $tz)
                  ?: DateTime::createFromFormat('Y-m-d H:i',   substr($fv, 0, 16), $tz);
            if (!$dt_ex) continue;

            $te_ex    = $this->parsearTiempoEstimado($ex['tiempo_estimado'] ?? null);
            $mins_ex  = $te_ex['restriccion'];
            $ex_es_dias = $te_ex['tipo'] === 'dias';

            $dt_ex_fin = $mins_ex > 0
                ? (clone $dt_ex)->modify("+{$mins_ex} minutes")
                : (clone $dt_ex)->modify('+30 minutes');

            /* Detectar solapamiento: [A_cliente, A_fin) ∩ [B_cliente, B_fin) ≠ ∅ */
            $hay_solape = $dt_nueva_visita < $dt_ex_fin
                       && $dt_ex           < $dt_nueva_fin;

            if (!$hay_solape) continue;

            $lugar = $ex['atraccion_nombre'] ?? 'una actividad anterior';

            /* ── Caso especial: actividad existente de DÍAS → aviso, no bloqueo ── */
            if ($ex_es_dias) {
                $mensaje = "Ya tienes <strong>{$lugar}</strong> programado ese día "
                         . "(<strong>" . $te_ex['label'] . "</strong>).<br>"
                         . "Si ya no estás en esa actividad, puedes continuar igual.";
                return [
                    'hay_conflicto' => true,
                    'conflict_type' => 'dias',
                    'mensaje'       => $mensaje,
                ];
            }

            /* ── Caso especial: nueva actividad de días → aviso, no bloqueo ── */
            if ($nueva_es_dias) {
                $mensaje = "La actividad que quieres guardar dura varios días "
                         . "y se solapa con <strong>{$lugar}</strong>.<br>"
                         . "Si lo deseas, puedes guardarla de todos modos.";
                return [
                    'hay_conflicto' => true,
                    'conflict_type' => 'dias',
                    'mensaje'       => $mensaje,
                ];
            }

            /* ── Conflicto de horas normal → bloquea ── */
            $disponible_desde = $dt_ex_fin->format('H:i');

            // Indicar si se aplicó la restricción de 1h (actividad > 4hs)
            $nota_restriccion = '';
            if ($te_ex['valor'] > 240 && $mins_ex === 60) {
                $nota_restriccion = '<br><small class="text-muted">('
                    . $te_ex['label'] . ' de duración; se aplica restricción mínima de 1h)</small>';
            }

            $mensaje = "Ya tienes <strong>{$lugar}</strong> a las "
                     . $dt_ex->format('H:i') . " (duración: <strong>"
                     . $te_ex['label'] . "</strong>).<br>"
                     . "Puedes agendar tu próxima visita a partir de las "
                     . "<strong>{$disponible_desde}</strong>."
                     . $nota_restriccion;

            return [
                'hay_conflicto'     => true,
                'conflict_type'     => 'horas',
                'mensaje'           => $mensaje,
                'disponible_desde'  => $disponible_desde,
            ];
        }

        return ['hay_conflicto' => false];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Guardar itinerario (schema real de la BD)
    // ─────────────────────────────────────────────────────────────────────────
    public function guardarEnItinerario(
        int    $id_usuario,
        int    $atraccion_id,
        string $nombre,
        string $descripcion = '',
        string $fecha_visita = null
    ) {
        try {
            $sql = "
                INSERT INTO itinerarios.itinerarios
                    (usuario_id, nombre, descripcion, atracciones_id,
                     fecha_visita, estado, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'pendiente', NOW(), NOW())
                RETURNING id
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $id_usuario,
                $nombre,
                $descripcion,
                $atraccion_id,
                $fecha_visita ?: null,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id'] ?? true;
        } catch (PDOException $e) {
            error_log("AtraccionesModel::guardarEnItinerario error: " . $e->getMessage());
            return false;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Resto de métodos sin cambios
    // ─────────────────────────────────────────────────────────────────────────

public function obtenerDetalle($id) {
    try {
        $sql  = "SELECT * FROM " . CatalogoI18n::atracciones() . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        // Decodificar campos JSON
        $jsonFields = ['estilo_musical','tipo_cocina','tipo_ambiente','servicios',
                       'info_alergenos','certificaciones','clima_ideal','temporada_ideal',
                       'idiomas_disponibles','deportes_disponibles','servicios_bienestar',
                       'tipos_cultura','estilos_viaje','transporte_cercano','horarios',
                       'tiempo_estimado']; // ← AÑADIDO
        foreach ($jsonFields as $field) {
            if (isset($row[$field]) && is_string($row[$field])) {
                $row[$field] = json_decode($row[$field], true) ?? [];
            }
        }

        // Garantizar tiempo_estimado mínimo
        if (empty($row['tiempo_estimado']) || !is_array($row['tiempo_estimado'])) {
            $row['tiempo_estimado'] = ['visita' => '30 minutos', 'traslado' => '0 minutos'];
        } elseif (!isset($row['tiempo_estimado']['visita'])) {
            $row['tiempo_estimado']['visita'] = '30 minutos';
        }

        // ... resto (ubicación, fotos, etc.)
        return $row;
    } catch (PDOException $e) {
        error_log("obtenerDetalle error: " . $e->getMessage());
        return null;
    }
}

  public function obtenerCategoria($id_atraccion, bool $traducida = false) {
        try {
            // $traducida: nombres en el idioma de la sesión (solo para mostrar)
            $tabla = $traducida ? CatalogoI18n::categorias() : 'catalogo.atraccion_categorias';
            $sql   = "SELECT categoria_rangos, subcategoria_rangos FROM {$tabla}";
            $stmt  = $this->pdo->query($sql);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $categoria = 'Otros';
            $subcategoria = 'General';

            foreach ($filas as $fila) {
                $cats          = json_decode($fila['categoria_rangos'], true) ?: [];
                $subcategorias = json_decode($fila['subcategoria_rangos'], true) ?: [];
                $encontrada    = false;

                // Buscar categoría por rango
                foreach ($cats as $cat) {
                    // Fallback para claves de inicio/fin
                    $inicio = $cat['rango_cliente'] ?? $cat['rango_inicio'] ?? $cat['desde'] ?? null;
                    $fin    = $cat['rango_fin']     ?? $cat['hasta']        ?? null;

                    if ($inicio !== null && $fin !== null && $id_atraccion >= $inicio && $id_atraccion <= $fin) {
                        $categoria = $cat['categoria'] ?? 'Otros';
                        $encontrada = true;
                        break;
                    }
                }

                // Si encontramos la categoría, buscamos la subcategoria en el mismo rango
                if ($encontrada) {
                    foreach ($subcategorias as $sub) {
                        $inicio = $sub['rango_cliente'] ?? $sub['rango_inicio'] ?? $sub['desde'] ?? null;
                        $fin    = $sub['rango_fin']     ?? $sub['hasta']        ?? null;

                        if ($inicio !== null && $fin !== null && $id_atraccion >= $inicio && $id_atraccion <= $fin) {
                            $n = $sub['subcategoria'] ?? 'General';
                            // Limpiar si tiene prefijo " - "
                            if (strpos($n, ' - ') !== false) {
                                $n = trim(explode(' - ', $n, 2)[1]);
                            }
                            $subcategoria = $n;
                            break;
                        }
                    }
                    break; // Salir del bucle principal
                }
            }

            return ['categoria' => $categoria, 'subcategoria' => $subcategoria];

        } catch (PDOException $e) {
            error_log("AtraccionesModel::obtenerCategoria error: " . $e->getMessage());
            return ['categoria' => 'Otros', 'subcategoria' => 'General'];
        }
    }

    public function obtenerFotos($id_atraccion) {
        try {
            $sql  = "SELECT * FROM catalogo.atracciones_fotos
                     WHERE atraccion_id = ? ORDER BY orden ASC LIMIT 10";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_atraccion]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AtraccionesModel::obtenerFotos error: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerResenas($id_atraccion, $limite = 5) {
        try {
            // Reseñas = calificaciones completadas (desde Itinerarios o directamente desde este detalle)
            $sql  = "SELECT bt.id, bt.calificacion_usuario AS puntuacion, bt.comentario_usuario AS comentario,
                            bt.created_at AS fecha_resena, u.nombre, u.apellido,
                            u.email AS usuario_email, u.configuracion_json AS usuario_cfg
                     FROM itinerarios.back_testing_mr bt
                     JOIN itinerarios.itinerarios i ON i.id = bt.itinerario_id
                     LEFT JOIN usuarios.usuarios u ON u.id = i.usuario_id
                     WHERE bt.atraccion_id = ? AND bt.estado = 'completado'
                     ORDER BY (bt.comentario_usuario IS NULL OR bt.comentario_usuario = ''), bt.created_at DESC
                     LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_atraccion, $limite]);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($filas as &$f) {   // solo datos públicos del autor
                $autor = Avatar::autorPublico($f['usuario_email'] ?? null, $f['usuario_cfg'] ?? null);
                $f['usuario_avatar']   = $autor['avatar'];
                $f['usuario_contacto'] = $autor['contacto'];
                unset($f['usuario_email'], $f['usuario_cfg']);
            }
            return $filas;
        } catch (PDOException $e) {
            error_log("AtraccionesModel::obtenerResenas error: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerRelacionadas($categoria, $excluir_id, $limite = 4) {
        try {
            $sql_cat = "SELECT categoria_rangos FROM catalogo.atraccion_categorias";
            $stmt    = $this->pdo->query($sql_cat);
            $filas   = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $rangos = [];
            foreach ($filas as $fila) {
                $cats = json_decode($fila['categoria_rangos'], true) ?: [];
                foreach ($cats as $cat) {
                    if (strtolower($cat['categoria'] ?? '') === strtolower($categoria)) {
                        $inicio = $cat['rango_cliente'] ?? $cat['rango_inicio'] ?? $cat['desde'] ?? null;
                        $fin    = $cat['rango_fin']     ?? $cat['hasta']        ?? null;
                        if ($inicio !== null && $fin !== null) {
                            $rangos[] = ['inicio' => $inicio, 'fin' => $fin];
                        }
                    }
                }
            }

            if (empty($rangos)) return [];

            $condiciones = [];
            foreach ($rangos as $r) {
                $condiciones[] = "(id BETWEEN {$r['inicio']} AND {$r['fin']})";
            }

            $sql = "SELECT id, nombre, descripcion, ciudad, puntuacion_promedio,
                           ubicacion->'rutas'->>0 AS imagen_url
                    FROM " . CatalogoI18n::atracciones() . "
                    WHERE (" . implode(' OR ', $condiciones) . ") AND id != ?
                    ORDER BY puntuacion_promedio DESC NULLS LAST
                    LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$excluir_id, $limite]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("AtraccionesModel::obtenerRelacionadas error: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Reseña directa desde el detalle (el turista puede opinar aunque no lo haya agendado aquí,
     * p. ej. si fue con otra agencia). Se guarda como las calificaciones de Itinerarios:
     * un itinerario con estado 'resena' (no se lista como itinerario) + su fila en back_testing_mr.
     * Una sola reseña directa por usuario y atracción: si ya existe, se actualiza.
     */
    public function guardarResena($id_usuario, $id_atraccion, $puntuacion, $comentario = '') {
        require_once __DIR__ . '/itinerarios.php';
        $nota = max(1, min(5, (int)round($puntuacion)));
        $comentario = mb_substr(trim((string)$comentario), 0, 1000);
        try {
            $this->pdo->beginTransaction();
            $st = $this->pdo->prepare("SELECT nombre FROM catalogo.atracciones WHERE id = ?");
            $st->execute([$id_atraccion]);
            $nombre = $st->fetchColumn();
            if ($nombre === false) throw new RuntimeException('Atracción no encontrada');

            $st = $this->pdo->prepare("SELECT id FROM itinerarios.itinerarios
                                       WHERE usuario_id = ? AND atracciones_id = ? AND estado = 'resena' LIMIT 1");
            $st->execute([$id_usuario, $id_atraccion]);
            $itId = $st->fetchColumn();
            if (!$itId) {
                $st = $this->pdo->prepare("INSERT INTO itinerarios.itinerarios
                        (usuario_id, nombre, descripcion, atracciones_id, fecha_visita, estado, created_at, updated_at)
                        VALUES (?, ?, 'Reseña directa', ?, NOW(), 'resena', NOW(), NOW()) RETURNING id");
                $st->execute([$id_usuario, $nombre, $id_atraccion]);
                $itId = $st->fetchColumn();
            }

            // Reajuste del modelo (misma lógica que GuardarCalificacion)
            $it  = new ItinerariosModel();
            $mod = $it->obtenerModeloPorAtraccion($id_atraccion) ?: [];
            $orig = $mod['input_original'] ?? [];
            $metricas = [
                'calificacion'       => $nota,
                'tags'               => [],
                'feedback_tipo'      => $nota >= 4 ? 'positivo' : ($nota === 3 ? 'neutral' : 'negativo'),
                'origen'             => 'resena_directa',
                'atraccion_id'       => (int)$id_atraccion,
                'input_original'     => $orig,
                'nuevo_input_vector' => $orig ? $it->construirInputVector($mod['mapeo'] ?? [], ['_input_original' => $orig], $nota) : [],
                'modelo_origen'      => ['id' => $mod['id'] ?? null, 'nombre' => $mod['nombre'] ?? null, 'version' => $mod['version'] ?? null],
                'fecha_reajuste'     => date('Y-m-d H:i:s'),
            ];
            $params = [$nota, $comentario !== '' ? $comentario : null, json_encode(['modelo_id' => $mod['id'] ?? null]),
                       json_encode($metricas, JSON_UNESCAPED_UNICODE)];

            $st = $this->pdo->prepare("UPDATE itinerarios.back_testing_mr
                    SET calificacion_usuario = ?, comentario_usuario = ?, modelo_mr_fk = ?::jsonb,
                        nueva_metricas_json_reajuste = ?::jsonb, estado = 'completado', updated_at = NOW(), created_at = NOW()
                    WHERE itinerario_id = ?");
            $st->execute(array_merge($params, [$itId]));
            if (!$st->rowCount()) {
                $st = $this->pdo->prepare("INSERT INTO itinerarios.back_testing_mr
                        (itinerario_id, atraccion_id, calificacion_usuario, comentario_usuario, modelo_mr_fk,
                         nueva_metricas_json_reajuste, estado, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?::jsonb, ?::jsonb, 'completado', NOW(), NOW())");
                $st->execute(array_merge([$itId, $id_atraccion], $params));
            }
            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("AtraccionesModel::guardarResena error: " . $e->getMessage());
            return false;
        }
    }

}