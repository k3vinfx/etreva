<?php
date_default_timezone_set('America/La_Paz');

class AdminBacktesting {

    private $pdo;

    // Umbrales de palabras ofensivas (expandible)
    private $PALABRAS_OFENSIVAS = [
        'asco', 'feo', 'basura', 'horrible', 'pésimo', 'pesimo', 'mierda',
        'idiota', 'estupido', 'estúpido', 'malo', 'roban', 'robo',
        'perjudiquen', 'caída', 'caida', 'terrible', 'pésima', 'pesima',
        'porquería', 'porqueria', 'inútil', 'inutil', 'bodrio'
    ];

    public function __CONSTRUCT() {
        try {
            $this->pdo = Database::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  KPIs generales del dashboard
    // ══════════════════════════════════════════════════════════════════════
    public function obtenerKPIs() {
        try {
            $kpis = [];

            // Total feedbacks
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM itinerarios.back_testing_mr");
            $kpis['total_feedbacks'] = (int)$stmt->fetchColumn();

            // Positivos vs Negativos
            $stmt = $this->pdo->query("
                SELECT
                    SUM(CASE WHEN (nueva_metricas_json_reajuste->>'feedback_tipo') = 'positivo' THEN 1 ELSE 0 END) AS positivos,
                    SUM(CASE WHEN (nueva_metricas_json_reajuste->>'feedback_tipo') = 'negativo' THEN 1 ELSE 0 END) AS negativos
                FROM itinerarios.back_testing_mr
            ");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $kpis['positivos']  = (int)($row['positivos'] ?? 0);
            $kpis['negativos']  = (int)($row['negativos'] ?? 0);

            // Promedio calificación
            $stmt = $this->pdo->query("SELECT ROUND(AVG(calificacion_usuario)::numeric, 2) FROM itinerarios.back_testing_mr");
            $kpis['promedio_calif'] = (float)($stmt->fetchColumn() ?? 0);

            // Comentarios ofensivos (contiene palabras clave)
            $condiciones = array_map(fn($p) => "LOWER(comentario_usuario) LIKE '%$p%'", $this->PALABRAS_OFENSIVAS);
            $whereOfensivo = implode(' OR ', $condiciones);
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM itinerarios.back_testing_mr WHERE $whereOfensivo");
            $kpis['comentarios_ofensivos'] = (int)$stmt->fetchColumn();

            // Feedbacks hoy
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM itinerarios.back_testing_mr WHERE DATE(created_at) = CURRENT_DATE");
            $kpis['feedbacks_hoy'] = (int)$stmt->fetchColumn();

            // Modelos activos
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM recomendaciones.modelos_mr WHERE estado = 'activo'");
            $kpis['modelos_activos'] = (int)$stmt->fetchColumn();

            // Tasa de mejora (comparar promedio con ajuste vs sin ajuste)
            $stmt = $this->pdo->query("
                SELECT
                    ROUND(AVG(calificacion_usuario)::numeric, 2) as prom_total,
                    COUNT(*) FILTER (WHERE nueva_metricas_json_reajuste->>'nuevo_input_vector' != '[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]') as con_ajuste
                FROM itinerarios.back_testing_mr
            ");
            $row2 = $stmt->fetch(PDO::FETCH_ASSOC);
            $kpis['con_ajuste'] = (int)($row2['con_ajuste'] ?? 0);

            return $kpis;

        } catch (PDOException $e) {
            error_log("AdminBacktestingModel::obtenerKPIs ERROR: " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Listar feedbacks con filtros
    //  $filtros: [tipo, modelo_id, ofensivo, calificacion_min, calificacion_max,
    //             buscar, page, per_page, orden]
    // ══════════════════════════════════════════════════════════════════════
    public function listarFeedbacks($filtros = []) {
        try {
            $page     = max(1, (int)($filtros['page']     ?? 1));
            $perPage  = max(5, min(100, (int)($filtros['per_page'] ?? 20)));
            $offset   = ($page - 1) * $perPage;
            $orden    = in_array($filtros['orden'] ?? '', ['asc','desc']) ? strtoupper($filtros['orden']) : 'DESC';

            $where  = ['1=1'];
            $params = [];

            // Filtro tipo (positivo/negativo)
            if (!empty($filtros['tipo']) && in_array($filtros['tipo'], ['positivo','negativo'])) {
                $where[]  = "(nueva_metricas_json_reajuste->>'feedback_tipo') = ?";
                $params[] = $filtros['tipo'];
            }

            // Filtro modelo
            if (!empty($filtros['modelo_id'])) {
                $where[]  = "(modelo_mr_fk->>'modelo_id')::int = ?";
                $params[] = (int)$filtros['modelo_id'];
            }

            // Filtro calificación mínima
            if (isset($filtros['calif_min']) && $filtros['calif_min'] !== '') {
                $where[]  = "calificacion_usuario >= ?";
                $params[] = (float)$filtros['calif_min'];
            }

            // Filtro calificación máxima
            if (isset($filtros['calif_max']) && $filtros['calif_max'] !== '') {
                $where[]  = "calificacion_usuario <= ?";
                $params[] = (float)$filtros['calif_max'];
            }

            // Filtro ofensivos
            if (!empty($filtros['ofensivo'])) {
                $conds = array_map(fn($p) => "LOWER(comentario_usuario) LIKE '%$p%'", $this->PALABRAS_OFENSIVAS);
                $where[] = '(' . implode(' OR ', $conds) . ')';
            }

            // Filtro estado
            if (!empty($filtros['estado']) && in_array($filtros['estado'], ['completado','pendiente','revisado','bloqueado'])) {
                $where[]  = "estado = ?";
                $params[] = $filtros['estado'];
            }

            // Búsqueda en comentario
            if (!empty($filtros['buscar'])) {
                $where[]  = "LOWER(comentario_usuario) LIKE ?";
                $params[] = '%' . strtolower($filtros['buscar']) . '%';
            }

            $whereSQL = implode(' AND ', $where);

            // Total para paginación
            $stmtC = $this->pdo->prepare("SELECT COUNT(*) FROM itinerarios.back_testing_mr WHERE $whereSQL");
            $stmtC->execute($params);
            $total = (int)$stmtC->fetchColumn();

            // Datos
            $sql = "
                SELECT
                    bt.id,
                    bt.itinerario_id,
                    bt.atraccion_id,
                    bt.calificacion_usuario,
                    bt.comentario_usuario,
                    bt.modelo_mr_fk,
                    bt.nueva_metricas_json_reajuste,
                    bt.estado,
                    bt.created_at,
                    a.nombre AS atraccion_nombre,
                    m.nombre AS modelo_nombre
                FROM itinerarios.back_testing_mr bt
                LEFT JOIN catalogo.atracciones a ON a.id = bt.atraccion_id
                LEFT JOIN recomendaciones.modelos_mr m ON m.id = (bt.modelo_mr_fk->>'modelo_id')::int
                WHERE $whereSQL
                ORDER BY bt.created_at $orden
                LIMIT $perPage OFFSET $offset
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Marcar si es ofensivo
            foreach ($filas as &$f) {
                $f['es_ofensivo'] = false;
                if (!empty($f['comentario_usuario'])) {
                    $lower = strtolower($f['comentario_usuario']);
                    foreach ($this->PALABRAS_OFENSIVAS as $p) {
                        if (str_contains($lower, $p)) {
                            $f['es_ofensivo'] = true;
                            break;
                        }
                    }
                }
                // Decodificar JSON
                if (is_string($f['nueva_metricas_json_reajuste'])) {
                    $f['metricas'] = json_decode($f['nueva_metricas_json_reajuste'], true) ?? [];
                } else {
                    $f['metricas'] = $f['nueva_metricas_json_reajuste'] ?? [];
                }
                // Decodificar modelo_mr_fk
                if (is_string($f['modelo_mr_fk'])) {
                    $f['modelo_mr_fk'] = json_decode($f['modelo_mr_fk'], true) ?? [];
                }
                unset($f['nueva_metricas_json_reajuste']);
            }

            return [
                'total'    => $total,
                'pagina'   => $page,
                'per_page' => $perPage,
                'paginas'  => (int)ceil($total / $perPage),
                'datos'    => $filas,
            ];

        } catch (PDOException $e) {
            error_log("AdminBacktestingModel::listarFeedbacks ERROR: " . $e->getMessage());
            return ['total'=>0,'pagina'=>1,'per_page'=>20,'paginas'=>0,'datos'=>[]];
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Cambiar estado de un feedback
    //  Estados válidos: completado | revisado | bloqueado | pendiente
    // ══════════════════════════════════════════════════════════════════════
    public function cambiarEstado($id, $estado) {
        $estados = ['completado','revisado','bloqueado','pendiente'];
        if (!in_array($estado, $estados)) return false;

        try {
            $stmt = $this->pdo->prepare("
                UPDATE itinerarios.back_testing_mr
                SET estado = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$estado, (int)$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminBacktestingModel::cambiarEstado ERROR: " . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Eliminar feedback (solo admin)
    // ══════════════════════════════════════════════════════════════════════
    public function eliminarFeedback($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM itinerarios.back_testing_mr WHERE id = ?");
            $stmt->execute([(int)$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminBacktestingModel::eliminarFeedback ERROR: " . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  KPIs por modelo: promedio calificación, positivos, negativos, total
    // ══════════════════════════════════════════════════════════════════════
    public function kpisPorModelo() {
        try {
            $sql = "
                SELECT
                    m.id,
                    m.nombre,
                    m.version,
                    m.estado,
                    COUNT(bt.id)                                         AS total_feedbacks,
                    ROUND(AVG(bt.calificacion_usuario)::numeric, 2)      AS promedio_calif,
                    SUM(CASE WHEN (bt.nueva_metricas_json_reajuste->>'feedback_tipo')='positivo' THEN 1 ELSE 0 END) AS positivos,
                    SUM(CASE WHEN (bt.nueva_metricas_json_reajuste->>'feedback_tipo')='negativo' THEN 1 ELSE 0 END) AS negativos,
                    m.created_at
                FROM recomendaciones.modelos_mr m
                LEFT JOIN itinerarios.back_testing_mr bt
                    ON (bt.modelo_mr_fk->>'modelo_id')::int = m.id
                GROUP BY m.id, m.nombre, m.version, m.estado, m.created_at
                ORDER BY promedio_calif DESC NULLS LAST
            ";
            $stmt = $this->pdo->query($sql);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($filas as &$f) {
                $total = (int)$f['total_feedbacks'];
                $pos   = (int)$f['positivos'];
                $f['tasa_positiva'] = $total > 0 ? round(($pos / $total) * 100, 1) : 0;
            }

            return $filas;
        } catch (PDOException $e) {
            error_log("AdminBacktestingModel::kpisPorModelo ERROR: " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Evolución de calificaciones por día (para gráfica)
    // ══════════════════════════════════════════════════════════════════════
    public function evolucionCalificaciones($dias = 30) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    DATE(created_at) AS fecha,
                    ROUND(AVG(calificacion_usuario)::numeric, 2) AS promedio,
                    COUNT(*) AS total,
                    SUM(CASE WHEN (nueva_metricas_json_reajuste->>'feedback_tipo')='positivo' THEN 1 ELSE 0 END) AS positivos,
                    SUM(CASE WHEN (nueva_metricas_json_reajuste->>'feedback_tipo')='negativo' THEN 1 ELSE 0 END) AS negativos
                FROM itinerarios.back_testing_mr
                WHERE created_at >= NOW() - INTERVAL '? days'
                GROUP BY DATE(created_at)
                ORDER BY fecha ASC
            ");
            $stmt->execute([$dias]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Fallback con formato correcto
            try {
                $stmt = $this->pdo->query("
                    SELECT
                        DATE(created_at) AS fecha,
                        ROUND(AVG(calificacion_usuario)::numeric, 2) AS promedio,
                        COUNT(*) AS total,
                        SUM(CASE WHEN (nueva_metricas_json_reajuste->>'feedback_tipo')='positivo' THEN 1 ELSE 0 END) AS positivos,
                        SUM(CASE WHEN (nueva_metricas_json_reajuste->>'feedback_tipo')='negativo' THEN 1 ELSE 0 END) AS negativos
                    FROM itinerarios.back_testing_mr
                    WHERE created_at >= NOW() - INTERVAL '30 days'
                    GROUP BY DATE(created_at)
                    ORDER BY fecha ASC
                ");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e2) {
                error_log("AdminBacktestingModel::evolucionCalificaciones ERROR: " . $e2->getMessage());
                return [];
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Tags más usados en feedbacks negativos
    // ══════════════════════════════════════════════════════════════════════
    public function tagsNegativosTop() {
        try {
            $stmt = $this->pdo->query("
                SELECT
                    tag,
                    COUNT(*) AS frecuencia
                FROM itinerarios.back_testing_mr,
                     jsonb_array_elements_text(nueva_metricas_json_reajuste->'tags') AS tag
                WHERE (nueva_metricas_json_reajuste->>'feedback_tipo') = 'negativo'
                GROUP BY tag
                ORDER BY frecuencia DESC
                LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminBacktestingModel::tagsNegativosTop ERROR: " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Análisis de mejora del backtesting:
    //  Compara input_original vs nuevo_input_vector para ver
    //  cuánto cambió el modelo en respuesta al feedback
    // ══════════════════════════════════════════════════════════════════════
    public function analisisMejora() {
        try {
            $stmt = $this->pdo->query("
                SELECT
                    bt.id,
                    bt.atraccion_id,
                    a.nombre AS atraccion_nombre,
                    bt.calificacion_usuario,
                    bt.nueva_metricas_json_reajuste->>'feedback_tipo' AS tipo,
                    bt.nueva_metricas_json_reajuste->'input_original' AS input_original,
                    bt.nueva_metricas_json_reajuste->'nuevo_input_vector' AS nuevo_vector,
                    (bt.modelo_mr_fk->>'modelo_id')::int AS modelo_id,
                    m.nombre AS modelo_nombre
                FROM itinerarios.back_testing_mr bt
                LEFT JOIN catalogo.atracciones a ON a.id = bt.atraccion_id
                LEFT JOIN recomendaciones.modelos_mr m ON m.id = (bt.modelo_mr_fk->>'modelo_id')::int
                ORDER BY bt.created_at DESC
                LIMIT 100
            ");
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $mejoras  = 0;
            $igual    = 0;
            $degradado = 0;

            foreach ($filas as &$f) {
                $orig  = json_decode($f['input_original'] ?? '[]', true)  ?? [];
                $nuevo = json_decode($f['nuevo_vector']   ?? '[]', true)  ?? [];

                $sumOrig  = array_sum($orig);
                $sumNuevo = array_sum($nuevo);

                if ($sumNuevo === 0 && $sumOrig > 0) {
                    $f['cambio'] = 'reset';
                    $degradado++;
                } elseif ($sumNuevo < $sumOrig) {
                    $f['cambio'] = 'reduccion';
                    $degradado++;
                } elseif ($sumNuevo > $sumOrig) {
                    $f['cambio'] = 'aumento';
                    $mejoras++;
                } else {
                    $f['cambio'] = 'sin_cambio';
                    $igual++;
                }
            }

            return [
                'detalle'   => $filas,
                'mejoras'   => $mejoras,
                'igual'     => $igual,
                'degradado' => $degradado,
                'total'     => count($filas),
            ];

        } catch (PDOException $e) {
            error_log("AdminBacktestingModel::analisisMejora ERROR: " . $e->getMessage());
            return ['detalle'=>[],'mejoras'=>0,'igual'=>0,'degradado'=>0,'total'=>0];
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Listar modelos con sus métricas de desempeño
    // ══════════════════════════════════════════════════════════════════════
    public function listarModelos() {
        try {
            // Intentar con fecha_entrenamiento, si no existe usar created_at como fallback
            $stmt = $this->pdo->query("
                SELECT id, nombre, version, descripcion, estado,
                       metricas_desempeno,
                       COALESCE(fecha_entrenamiento, created_at) AS fecha_entrenamiento,
                       created_at
                FROM recomendaciones.modelos_mr
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminBacktestingModel::listarModelos ERROR: " . $e->getMessage());
            // Fallback sin fecha_entrenamiento
            try {
                $stmt2 = $this->pdo->query("
                    SELECT id, nombre, version, descripcion, estado,
                           metricas_desempeno, created_at,
                           created_at AS fecha_entrenamiento
                    FROM recomendaciones.modelos_mr
                    ORDER BY created_at DESC
                ");
                return $stmt2->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e2) {
                error_log("AdminBacktestingModel::listarModelos FALLBACK ERROR: " . $e2->getMessage());
                return [];
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Cambiar estado de modelo
    // ══════════════════════════════════════════════════════════════════════
    public function cambiarEstadoModelo($id, $estado) {
        $estados = ['activo','inactivo','entrenando','deprecado'];
        if (!in_array($estado, $estados)) return false;
        try {
            $stmt = $this->pdo->prepare("
                UPDATE recomendaciones.modelos_mr SET estado = ?, updated_at = NOW() WHERE id = ?
            ");
            $stmt->execute([$estado, (int)$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminBacktestingModel::cambiarEstadoModelo ERROR: " . $e->getMessage());
            return false;
        }
    }
}