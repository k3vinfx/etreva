<?php
/*
 * model/adminModelosMR.php
 * ─────────────────────────────────────────────────────────────────────────────
 * CRUD completo sobre recomendaciones.modelos_mr para el panel admin.
 *
 * Tabla: recomendaciones.modelos_mr
 *   id, nombre, version, descripcion,
 *   configuracion_json  (JSONB) → metadata, alternativas[], mapeo_caracteristicas,
 *                                  indices_por_clasificacion
 *   fecha_entrenamiento,
 *   metricas_desempeno  (JSONB) → total_e, mapeo_input, tipo_modelo,
 *                                  features_por_input, total_alternativas,
 *                                  estado_entrenamiento, dataset_entrenamiento[]
 *   estado ('activo' | 'inactivo' | 'entrenando'),
 *   created_at, updated_at
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AdminModelosMR
{
    private $pdo;

    public function __CONSTRUCT()
    {
        try {
            $this->pdo = Database::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  STATS globales para KPI strip
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerStats(): array
    {
        try {
            $row = $this->pdo->query("
                SELECT
                    COUNT(*)                                            AS total,
                    COUNT(*) FILTER (WHERE estado = 'activo')           AS activos,
                    COUNT(*) FILTER (WHERE estado = 'inactivo')         AS inactivos,
                    COUNT(*) FILTER (WHERE estado = 'entrenando')       AS entrenando,
                    COALESCE(SUM(
                        (metricas_desempeno->>'total_alternativas')::int
                    ) FILTER (
                        WHERE metricas_desempeno->>'total_alternativas' ~ '^\d+$'
                    ), 0)                                               AS total_alternativas,
                    COALESCE(SUM(
                        jsonb_array_length(
                            COALESCE(metricas_desempeno->'dataset_entrenamiento','[]'::jsonb)
                        )
                    ), 0)                                               AS total_registros_dataset
                FROM recomendaciones.modelos_mr
            ")->fetch(PDO::FETCH_ASSOC);

            return $row ?: [
                'total'=>0,'activos'=>0,'inactivos'=>0,'entrenando'=>0,
                'total_alternativas'=>0,'total_registros_dataset'=>0
            ];
        } catch (PDOException $e) {
            error_log("AdminModelosMR::obtenerStats: " . $e->getMessage());
            return ['total'=>0,'activos'=>0,'inactivos'=>0,'entrenando'=>0,
                    'total_alternativas'=>0,'total_registros_dataset'=>0];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  LISTAR con filtros y paginación (SIN los arrays pesados del JSON)
    // ═══════════════════════════════════════════════════════════════════════
    public function listar(array $f = []): array
    {
        try {
            $page    = max(1, (int)($f['page'] ?? 1));
            $perPage = min(50, max(5, (int)($f['per_page'] ?? 10)));
            $offset  = ($page - 1) * $perPage;

            $where  = ['1=1'];
            $params = [];

            if (!empty($f['buscar'])) {
                $where[]  = "(m.nombre ILIKE ? OR m.descripcion ILIKE ? OR m.version ILIKE ?)";
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
            }
            if (!empty($f['estado'])) {
                $where[]  = "m.estado = ?";
                $params[] = $f['estado'];
            }

            $sortCol = match($f['sort'] ?? 'id') {
                'nombre'  => 'm.nombre',
                'version' => 'm.version',
                'estado'  => 'm.estado',
                'fecha'   => 'm.fecha_entrenamiento',
                'updated' => 'm.updated_at',
                default   => 'm.id',
            };
            $orden    = strtoupper(in_array($f['orden'] ?? '', ['asc','desc']) ? $f['orden'] : 'desc');
            $whereSQL = implode(' AND ', $where);

            $stmtC = $this->pdo->prepare(
                "SELECT COUNT(*) FROM recomendaciones.modelos_mr m WHERE $whereSQL"
            );
            $stmtC->execute($params);
            $total = (int)$stmtC->fetchColumn();

            // Solo extraemos los campos clave de los JSON, NO los arrays pesados
            $stmt = $this->pdo->prepare("
                SELECT
                    m.id,
                    m.nombre,
                    m.version,
                    m.descripcion,
                    m.estado,
                    m.fecha_entrenamiento,
                    m.created_at,
                    m.updated_at,
                    -- metadata de configuracion_json
                    m.configuracion_json->'metadata'->>'categoria_principal'  AS categoria_principal,
                    m.configuracion_json->'metadata'->>'ciudad'               AS ciudad,
                    m.configuracion_json->'metadata'->>'pais'                 AS pais,
                    m.configuracion_json->'metadata'->>'fecha_generacion'     AS fecha_generacion_config,
                    (m.configuracion_json->'metadata'->>'total_alternativas')::int AS total_alt_config,
                    jsonb_array_length(
                        COALESCE(m.configuracion_json->'alternativas','[]'::jsonb)
                    )                                                         AS cant_alternativas,
                    -- métricas clave (sin dataset_entrenamiento)
                    m.metricas_desempeno->>'tipo_modelo'                      AS tipo_modelo,
                    m.metricas_desempeno->>'features_por_input'               AS features_por_input,
                    m.metricas_desempeno->>'total_alternativas'               AS total_alt_metricas,
                    m.metricas_desempeno->>'estado_entrenamiento'             AS estado_entrenamiento,
                    m.metricas_desempeno->>'total_e'                          AS total_epochs,
                    jsonb_array_length(
                        COALESCE(m.metricas_desempeno->'dataset_entrenamiento','[]'::jsonb)
                    )                                                         AS cant_dataset
                FROM recomendaciones.modelos_mr m
                WHERE $whereSQL
                ORDER BY $sortCol $orden
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute($params);

            return [
                'total'    => $total,
                'pagina'   => $page,
                'per_page' => $perPage,
                'paginas'  => max(1, (int)ceil($total / $perPage)),
                'datos'    => $stmt->fetchAll(PDO::FETCH_ASSOC),
            ];

        } catch (PDOException $e) {
            error_log("AdminModelosMR::listar: " . $e->getMessage());
            return ['total'=>0,'pagina'=>1,'per_page'=>10,'paginas'=>0,'datos'=>[]];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  LISTAR TODO para exportar (sin arrays pesados)
    // ═══════════════════════════════════════════════════════════════════════
    public function listarTodo(array $f = []): array
    {
        try {
            $where  = ['1=1'];
            $params = [];

            if (!empty($f['buscar'])) {
                $where[]  = "(m.nombre ILIKE ? OR m.descripcion ILIKE ?)";
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
            }
            if (!empty($f['estado'])) {
                $where[]  = "m.estado = ?";
                $params[] = $f['estado'];
            }

            $whereSQL = implode(' AND ', $where);
            $stmt = $this->pdo->prepare("
                SELECT
                    m.id, m.nombre, m.version, m.descripcion, m.estado,
                    m.fecha_entrenamiento, m.created_at, m.updated_at,
                    m.configuracion_json->'metadata'->>'categoria_principal' AS categoria_principal,
                    m.configuracion_json->'metadata'->>'ciudad'              AS ciudad,
                    m.metricas_desempeno->>'tipo_modelo'                     AS tipo_modelo,
                    m.metricas_desempeno->>'features_por_input'              AS features_por_input,
                    m.metricas_desempeno->>'total_alternativas'              AS total_alternativas,
                    m.metricas_desempeno->>'estado_entrenamiento'            AS estado_entrenamiento,
                    m.metricas_desempeno->>'total_e'                         AS total_epochs,
                    jsonb_array_length(
                        COALESCE(m.metricas_desempeno->'dataset_entrenamiento','[]'::jsonb)
                    )                                                        AS cant_dataset
                FROM recomendaciones.modelos_mr m
                WHERE $whereSQL
                ORDER BY m.id ASC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminModelosMR::listarTodo: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  OBTENER RESUMEN — metadata + métricas SIN dataset ni alternativas
    //  Usado para el panel lateral de detalle
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerResumen(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT
                    m.id, m.nombre, m.version, m.descripcion, m.estado,
                    m.fecha_entrenamiento, m.created_at, m.updated_at,
                    -- configuracion_json: metadata y mapeo (sin alternativas)
                    m.configuracion_json->'metadata'                             AS metadata,
                    m.configuracion_json->'mapeo_caracteristicas'                AS mapeo_caracteristicas,
                    m.configuracion_json->'indices_por_clasificacion'            AS indices_clasificacion,
                    jsonb_array_length(
                        COALESCE(m.configuracion_json->'alternativas','[]'::jsonb)
                    )                                                            AS cant_alternativas,
                    -- metricas_desempeno: todo menos dataset_entrenamiento
                    (m.metricas_desempeno - 'dataset_entrenamiento')             AS metricas,
                    jsonb_array_length(
                        COALESCE(m.metricas_desempeno->'dataset_entrenamiento','[]'::jsonb)
                    )                                                            AS cant_dataset
                FROM recomendaciones.modelos_mr m
                WHERE m.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            foreach (['metadata','mapeo_caracteristicas','indices_clasificacion','metricas'] as $c) {
                if (is_string($row[$c])) {
                    $row[$c] = json_decode($row[$c], true) ?? [];
                }
            }
            return $row;
        } catch (PDOException $e) {
            error_log("AdminModelosMR::obtenerResumen: " . $e->getMessage());
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  OBTENER ALTERNATIVAS paginadas desde configuracion_json->'alternativas'
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerAlternativas(int $id, int $page = 1, int $perPage = 10): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $stmt = $this->pdo->prepare("
                SELECT
                    jsonb_array_length(
                        COALESCE(configuracion_json->'alternativas','[]'::jsonb)
                    ) AS total,
                    (
                        SELECT jsonb_agg(elem)
                        FROM (
                            SELECT elem
                            FROM jsonb_array_elements(
                                COALESCE(configuracion_json->'alternativas','[]'::jsonb)
                            ) AS elem
                            LIMIT ? OFFSET ?
                        ) sub
                    ) AS alternativas
                FROM recomendaciones.modelos_mr WHERE id = ?
            ");
            $stmt->execute([$perPage, $offset, $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['total'=>0,'paginas'=>0,'pagina'=>$page,'datos'=>[]];

            $total = (int)$row['total'];
            $datos = json_decode($row['alternativas'] ?? '[]', true) ?: [];

            return [
                'total'   => $total,
                'paginas' => max(1, (int)ceil($total / $perPage)),
                'pagina'  => $page,
                'datos'   => $datos,
            ];
        } catch (PDOException $e) {
            error_log("AdminModelosMR::obtenerAlternativas: " . $e->getMessage());
            return ['total'=>0,'paginas'=>0,'pagina'=>1,'datos'=>[]];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  OBTENER DATASET paginado desde metricas_desempeno->'dataset_entrenamiento'
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerDataset(int $id, int $page = 1, int $perPage = 15): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $stmt = $this->pdo->prepare("
                SELECT
                    jsonb_array_length(
                        COALESCE(metricas_desempeno->'dataset_entrenamiento','[]'::jsonb)
                    ) AS total,
                    (
                        SELECT jsonb_agg(elem)
                        FROM (
                            SELECT elem
                            FROM jsonb_array_elements(
                                COALESCE(metricas_desempeno->'dataset_entrenamiento','[]'::jsonb)
                            ) AS elem
                            LIMIT ? OFFSET ?
                        ) sub
                    ) AS registros
                FROM recomendaciones.modelos_mr WHERE id = ?
            ");
            $stmt->execute([$perPage, $offset, $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['total'=>0,'paginas'=>0,'pagina'=>$page,'datos'=>[]];

            $total = (int)$row['total'];
            $datos = json_decode($row['registros'] ?? '[]', true) ?: [];

            return [
                'total'   => $total,
                'paginas' => max(1, (int)ceil($total / $perPage)),
                'pagina'  => $page,
                'datos'   => $datos,
            ];
        } catch (PDOException $e) {
            error_log("AdminModelosMR::obtenerDataset: " . $e->getMessage());
            return ['total'=>0,'paginas'=>0,'pagina'=>1,'datos'=>[]];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  OBTENER MAPEO INPUT (mapeo_input de metricas_desempeno)
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerMapeoInput(int $id): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT metricas_desempeno->'mapeo_input' AS mapeo_input
                FROM recomendaciones.modelos_mr WHERE id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || !$row['mapeo_input']) return [];
            return json_decode($row['mapeo_input'], true) ?? [];
        } catch (PDOException $e) {
            error_log("AdminModelosMR::obtenerMapeoInput: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  STATS CRUZADAS DE UN MODELO (recomendaciones + catálogo + contextos)
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerStatsModelo(int $id): array
    {
        try {
            // Recomendaciones generadas por este modelo
            $stmtR = $this->pdo->prepare("
                SELECT
                    COUNT(*)                                                          AS total_recomendaciones,
                    COUNT(*) FILTER (WHERE feedback_usuario->>'tipo' = 'positivo')    AS feedback_positivos,
                    COUNT(*) FILTER (WHERE feedback_usuario->>'tipo' = 'negativo')    AS feedback_negativos,
                    ROUND(AVG(score_relevancia)::numeric, 4)                          AS score_promedio,
                    COUNT(DISTINCT atraccion_id)                                      AS atracciones_distintas
                FROM recomendaciones.recomendaciones
                WHERE modelo_mr_id = ?
            ");
            $stmtR->execute([$id]);
            $statsR = $stmtR->fetch(PDO::FETCH_ASSOC) ?: [];

            // Parámetros del catálogo para este modelo
            $stmtP = $this->pdo->prepare("
                SELECT
                    COUNT(*)                                   AS total_parametros,
                    COUNT(*) FILTER (WHERE estado = true)      AS parametros_activos,
                    COUNT(DISTINCT grupo)                      AS grupos_distintos,
                    array_agg(DISTINCT grupo ORDER BY grupo)   AS grupos_lista
                FROM recomendaciones.catalogo_parametros
                WHERE modelo_mr_id = ?
            ");
            $stmtP->execute([$id]);
            $statsP = $stmtP->fetch(PDO::FETCH_ASSOC) ?: [];

            // Contextos asociados a este modelo
            $stmtC = $this->pdo->prepare("
                SELECT
                    COUNT(*)                                   AS total_contextos,
                    COUNT(*) FILTER (WHERE estado = true)      AS contextos_activos,
                    COUNT(DISTINCT tipo_contexto)              AS tipos_distintos,
                    array_agg(DISTINCT tipo_contexto)          AS tipos_lista
                FROM recomendaciones.contexto_relacion
                WHERE modelo_mr_id = ?
            ");
            $stmtC->execute([$id]);
            $statsC = $stmtC->fetch(PDO::FETCH_ASSOC) ?: [];

            return array_merge($statsR, $statsP, $statsC);
        } catch (PDOException $e) {
            error_log("AdminModelosMR::obtenerStatsModelo: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CREAR
    // ═══════════════════════════════════════════════════════════════════════
    public function crear(array $d): int|false
    {
        try {
            $configJson   = $this->toJsonb($d['configuracion_json']  ?? '{}');
            $metricasJson = $this->toJsonb($d['metricas_desempeno']   ?? '{}');

            $stmt = $this->pdo->prepare("
                INSERT INTO recomendaciones.modelos_mr
                    (nombre, version, descripcion, configuracion_json,
                     fecha_entrenamiento, metricas_desempeno, estado,
                     created_at, updated_at)
                VALUES (?, ?, ?, ?::jsonb, ?, ?::jsonb, ?, NOW(), NOW())
                RETURNING id
            ");
            $stmt->execute([
                trim($d['nombre']       ?? ''),
                trim($d['version']      ?? '1.0.0'),
                trim($d['descripcion']  ?? ''),
                $configJson,
                !empty($d['fecha_entrenamiento']) ? $d['fecha_entrenamiento'] : null,
                $metricasJson,
                $this->estadoValido($d['estado'] ?? 'inactivo'),
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['id'] ?? 0);
        } catch (PDOException $e) {
            error_log("AdminModelosMR::crear: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACTUALIZAR — campos básicos
    // ═══════════════════════════════════════════════════════════════════════
    public function actualizar(int $id, array $d): bool
    {
        try {
            $sets   = [];
            $params = [];

            foreach (['nombre','version','descripcion'] as $c) {
                if (isset($d[$c])) { $sets[] = "$c = ?"; $params[] = trim($d[$c]); }
            }
            if (isset($d['estado'])) {
                $sets[]   = "estado = ?";
                $params[] = $this->estadoValido($d['estado']);
            }
            if (array_key_exists('fecha_entrenamiento', $d)) {
                $sets[]   = "fecha_entrenamiento = ?";
                $params[] = !empty($d['fecha_entrenamiento']) ? $d['fecha_entrenamiento'] : null;
            }
            // Los formularios mandan solo las partes que editan (metadata, mapeo_input, dataset…):
            // se FUSIONAN con lo guardado, nunca reemplazan el JSON completo
            // (antes, guardar desde "Editar" borraba alternativas, dataset y mapeo).
            $actual = null;
            if (isset($d['configuracion_json']) || isset($d['metricas_desempeno'])) {
                $st = $this->pdo->prepare("SELECT * FROM recomendaciones.modelos_mr WHERE id = ?");
                $st->execute([$id]);
                $actual = $st->fetch(PDO::FETCH_ASSOC);
                if (!$actual) return false;
            }
            foreach (['configuracion_json', 'metricas_desempeno'] as $col) {
                if (!isset($d[$col])) continue;
                $nuevo = json_decode($this->toJsonb($d[$col]), true);
                if (!is_array($nuevo)) continue;
                $viejo = json_decode((string)($actual[$col] ?? ''), true) ?: [];
                $sets[]   = "$col = ?::jsonb";
                $params[] = json_encode(self::fusionar($viejo, $nuevo), JSON_UNESCAPED_UNICODE);
            }

            if (empty($sets)) return false;
            if ($actual) $this->respaldar($actual);

            $sets[]   = "updated_at = NOW()";
            $params[] = $id;

            $stmt = $this->pdo->prepare(
                "UPDATE recomendaciones.modelos_mr SET " . implode(', ', $sets) . " WHERE id = ?"
            );
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminModelosMR::actualizar: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CAMBIAR ESTADO
    // ═══════════════════════════════════════════════════════════════════════
    public function cambiarEstado(int $id, string $nuevoEstado): array
    {
        try {
            $nuevoEstado = $this->estadoValido($nuevoEstado);
            $stmt = $this->pdo->prepare("
                UPDATE recomendaciones.modelos_mr
                SET estado = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$nuevoEstado, $id]);
            return ['ok' => $stmt->rowCount() > 0, 'estado' => $nuevoEstado];
        } catch (PDOException $e) {
            error_log("AdminModelosMR::cambiarEstado: " . $e->getMessage());
            return ['ok' => false, 'estado' => null];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════════════
    /** Fusión por claves: los objetos se combinan; listas (alternativas, dataset) y valores se reemplazan */
    private static function fusionar(array $viejo, array $nuevo): array
    {
        foreach ($nuevo as $k => $v) {
            $esObj = fn($x) => is_array($x) && $x !== [] && !array_is_list($x);
            $viejo[$k] = ($esObj($v) && isset($viejo[$k]) && $esObj($viejo[$k])) ? self::fusionar($viejo[$k], $v) : $v;
        }
        return $viejo;
    }

    /**
     * Copia de seguridad del modelo antes de cada cambio: sql_backups/modelos/modelo_{id}_{fecha}.json
     * (carpeta no pública). Se conservan las últimas 20 por modelo.
     */
    private function respaldar(array $fila): void
    {
        $dir = __DIR__ . '/../sql_backups/modelos';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents("$dir/modelo_{$fila['id']}_" . date('Ymd_His') . '.json', json_encode($fila, JSON_UNESCAPED_UNICODE));
        $copias = glob("$dir/modelo_{$fila['id']}_*.json") ?: [];
        sort($copias);
        foreach (array_slice($copias, 0, max(0, count($copias) - 20)) as $f) @unlink($f);
    }

    private function toJsonb($v): string
    {
        if (is_array($v))  return json_encode($v, JSON_UNESCAPED_UNICODE);
        if (is_string($v)) {
            json_decode($v);
            return json_last_error() === JSON_ERROR_NONE ? $v : '{}';
        }
        return '{}';
    }

    private function estadoValido(string $e): string
    {
        return in_array($e, ['activo','inactivo','entrenando']) ? $e : 'inactivo';
    }
}