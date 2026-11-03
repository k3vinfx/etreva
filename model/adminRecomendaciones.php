<?php
/*
 * model/adminRecomendaciones.php
 * ─────────────────────────────────────────────────────────────────────────────
 * CRUD completo sobre recomendaciones.recomendaciones para el panel de admin.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AdminRecomendaciones
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
    //  STATS para KPI strip
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerStats(): array
    {
        try {
            $row = $this->pdo->query("
                SELECT
                    COUNT(*)                                                          AS total,
                    COUNT(*) FILTER (WHERE feedback_usuario->>'tipo' = 'positivo')   AS positivos,
                    COUNT(*) FILTER (WHERE feedback_usuario->>'tipo' = 'negativo')   AS negativos,
                    COUNT(*) FILTER (WHERE feedback_usuario IS NULL
                                       OR feedback_usuario = '{}'::jsonb)            AS sin_feedback,
                    ROUND(AVG(score_relevancia)::numeric, 2)                         AS score_promedio
                FROM recomendaciones.recomendaciones
            ")->fetch(PDO::FETCH_ASSOC);

            return $row ?: ['total'=>0,'positivos'=>0,'negativos'=>0,'sin_feedback'=>0,'score_promedio'=>0];
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::obtenerStats: " . $e->getMessage());
            return ['total'=>0,'positivos'=>0,'negativos'=>0,'sin_feedback'=>0,'score_promedio'=>0];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  LISTAR con filtros, búsqueda y paginación
    // ═══════════════════════════════════════════════════════════════════════
    public function listar(array $f = []): array
    {
        try {
            $page    = max(1,   (int)($f['page']     ?? 1));
            $perPage = min(100, max(10, (int)($f['per_page'] ?? 20)));
            $offset  = ($page - 1) * $perPage;

            $where  = ['1=1'];
            $params = [];

            if (!empty($f['buscar'])) {
                $where[]  = "(a.nombre ILIKE ? OR m.nombre ILIKE ?)";
                $params[] = '%' . $f['buscar'] . '%';
                $params[] = '%' . $f['buscar'] . '%';
            }
            if (!empty($f['modelo_id'])) {
                $where[]  = "r.modelo_mr_id = ?";
                $params[] = (int)$f['modelo_id'];
            }
            if (($f['feedback'] ?? '') === 'positivo') {
                $where[] = "r.feedback_usuario->>'tipo' = 'positivo'";
            } elseif (($f['feedback'] ?? '') === 'negativo') {
                $where[] = "r.feedback_usuario->>'tipo' = 'negativo'";
            } elseif (($f['feedback'] ?? '') === 'sin_feedback') {
                $where[] = "(r.feedback_usuario IS NULL OR r.feedback_usuario = '{}'::jsonb)";
            }
            if (is_numeric($f['score_min'] ?? null)) {
                $where[]  = "r.score_relevancia >= ?";
                $params[] = (float)$f['score_min'];
            }

            $sortCol = match($f['sort'] ?? 'id') {
                'score'      => 'r.score_relevancia',
                'atraccion'  => 'a.nombre',
                'modelo'     => 'm.nombre',
                'generacion' => 'r.fecha_generacion',
                'mostrado'   => 'r.fecha_mostrado',
                default      => 'r.id',
            };
            $orden    = strtoupper(in_array($f['orden'] ?? '', ['asc','desc']) ? $f['orden'] : 'desc');
            $whereSQL = implode(' AND ', $where);

            $stmtC = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM recomendaciones.recomendaciones r
                JOIN catalogo.atracciones a       ON a.id = r.atraccion_id
                JOIN recomendaciones.modelos_mr m ON m.id = r.modelo_mr_id
                WHERE $whereSQL
            ");
            $stmtC->execute($params);
            $total = (int)$stmtC->fetchColumn();

            $sql = "
                SELECT
                    r.id, r.atraccion_id, r.modelo_mr_id, r.score_relevancia,
                    r.razon_recomendacion, r.contexto_recomendacion,
                    r.feedback_usuario, r.fecha_generacion, r.fecha_mostrado,
                    r.created_at, r.updated_at,
                    a.nombre   AS atraccion_nombre,
                    a.ciudad   AS atraccion_ciudad,
                    a.estado   AS atraccion_estado,
                    m.nombre   AS modelo_nombre,
                    m.version  AS modelo_version,
                    m.estado   AS modelo_estado
                FROM recomendaciones.recomendaciones r
                JOIN catalogo.atracciones a       ON a.id = r.atraccion_id
                JOIN recomendaciones.modelos_mr m ON m.id = r.modelo_mr_id
                WHERE $whereSQL
                ORDER BY $sortCol $orden
                LIMIT $perPage OFFSET $offset
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($filas as &$row) {
                foreach (['razon_recomendacion','contexto_recomendacion','feedback_usuario'] as $campo) {
                    if (is_string($row[$campo])) {
                        $row[$campo] = json_decode($row[$campo], true) ?? [];
                    }
                }
            }

            return [
                'total'    => $total,
                'pagina'   => $page,
                'per_page' => $perPage,
                'paginas'  => max(1, (int)ceil($total / $perPage)),
                'datos'    => $filas,
            ];

        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::listar: " . $e->getMessage());
            return ['total'=>0,'pagina'=>1,'per_page'=>20,'paginas'=>0,'datos'=>[]];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  OBTENER por ID
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerPorId(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT r.*,
                       a.nombre  AS atraccion_nombre,
                       a.ciudad  AS atraccion_ciudad,
                       m.nombre  AS modelo_nombre,
                       m.version AS modelo_version
                FROM recomendaciones.recomendaciones r
                JOIN catalogo.atracciones a       ON a.id = r.atraccion_id
                JOIN recomendaciones.modelos_mr m ON m.id = r.modelo_mr_id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            foreach (['razon_recomendacion','contexto_recomendacion','feedback_usuario'] as $campo) {
                if (is_string($row[$campo])) {
                    $row[$campo] = json_decode($row[$campo], true) ?? [];
                }
            }
            return $row;
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CREAR
    // ═══════════════════════════════════════════════════════════════════════
    public function crear(array $d): int|false
    {
        try {
            if (!$this->existeAtraccion((int)($d['atraccion_id'] ?? 0))) return false;
            if (!$this->existeModelo((int)($d['modelo_mr_id'] ?? 0)))    return false;

            $razon    = $this->buildJsonb($d['razon_recomendacion']   ?? []);
            $contexto = $this->buildJsonb($d['contexto_recomendacion'] ?? []);

            $stmt = $this->pdo->prepare("
                INSERT INTO recomendaciones.recomendaciones
                    (atraccion_id, modelo_mr_id, score_relevancia,
                     razon_recomendacion, contexto_recomendacion,
                     feedback_usuario, fecha_generacion, created_at, updated_at)
                VALUES (?, ?, ?, ?::jsonb, ?::jsonb, '{}'::jsonb, NOW(), NOW(), NOW())
                RETURNING id
            ");
            $stmt->execute([
                (int)$d['atraccion_id'],
                (int)$d['modelo_mr_id'],
                $this->score($d['score_relevancia'] ?? 0),
                $razon,
                $contexto,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['id'] ?? 0);
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::crear: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACTUALIZAR
    // ═══════════════════════════════════════════════════════════════════════
    public function actualizar(int $id, array $d): bool
    {
        try {
            if (!$this->existeAtraccion((int)($d['atraccion_id'] ?? 0))) return false;
            if (!$this->existeModelo((int)($d['modelo_mr_id']   ?? 0))) return false;

            $razon    = $this->buildJsonb($d['razon_recomendacion']   ?? []);
            $contexto = $this->buildJsonb($d['contexto_recomendacion'] ?? []);

            $stmt = $this->pdo->prepare("
                UPDATE recomendaciones.recomendaciones SET
                    atraccion_id           = ?,
                    modelo_mr_id           = ?,
                    score_relevancia       = ?,
                    razon_recomendacion    = ?::jsonb,
                    contexto_recomendacion = ?::jsonb,
                    updated_at             = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                (int)$d['atraccion_id'],
                (int)$d['modelo_mr_id'],
                $this->score($d['score_relevancia'] ?? 0),
                $razon,
                $contexto,
                $id,
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::actualizar: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ELIMINAR
    // ═══════════════════════════════════════════════════════════════════════
    public function eliminar(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM recomendaciones.recomendaciones WHERE id = ?"
            );
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::eliminar: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  LIMPIAR FEEDBACK
    // ═══════════════════════════════════════════════════════════════════════
    public function limpiarFeedback(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE recomendaciones.recomendaciones
                SET feedback_usuario = '{}'::jsonb, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::limpiarFeedback: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SELECTORES GENERALES (atracciones + modelos activos)
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerAtraccionesActivas(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT id, nombre, ciudad, atraccion_categoria_id
                FROM catalogo.atracciones
                WHERE estado = 'activo'
                ORDER BY nombre ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::obtenerAtraccionesActivas: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerModelosActivos(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT id, nombre, version, estado
                FROM recomendaciones.modelos_mr
                WHERE estado = 'activo'
                ORDER BY id ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::obtenerModelosActivos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerTodosModelos(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT id, nombre, version, estado
                FROM recomendaciones.modelos_mr
                ORDER BY id ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::obtenerTodosModelos: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ★ NUEVO — PARÁMETROS DEL MODELO
    //  Devuelve catalogo_parametros agrupado por grupo para un modelo dado.
    //  Resultado: { "interes": [ {valor, descripcion}, ... ], "dificultad": [...] }
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerParametrosPorModelo(int $modeloId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT grupo, valor, descripcion
                FROM recomendaciones.catalogo_parametros
                WHERE modelo_mr_id = ?
                  AND estado = true
                ORDER BY grupo ASC, valor ASC
            ");
            $stmt->execute([$modeloId]);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar por grupo
            $agrupado = [];
            foreach ($filas as $fila) {
                $agrupado[$fila['grupo']][] = [
                    'valor'       => $fila['valor'],
                    'descripcion' => $fila['descripcion'] ?? '',
                ];
            }
            return $agrupado;
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::obtenerParametrosPorModelo: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ★ NUEVO — CONTEXTO DEL MODELO
    //  Devuelve contexto_relacion agrupado por tipo_contexto para un modelo dado.
    //  Resultado: { "clima": ["seco","lluvioso"], "ciudad": ["La Paz"] }
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerContextoPorModelo(int $modeloId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT tipo_contexto, valor
                FROM recomendaciones.contexto_relacion
                WHERE modelo_mr_id = ?
                  AND estado = true
                ORDER BY tipo_contexto ASC, valor ASC
            ");
            $stmt->execute([$modeloId]);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar por tipo_contexto, valores únicos
            $agrupado = [];
            foreach ($filas as $fila) {
                $tipo = $fila['tipo_contexto'];
                if (!in_array($fila['valor'], $agrupado[$tipo] ?? [])) {
                    $agrupado[$tipo][] = $fila['valor'];
                }
            }
            return $agrupado;
        } catch (PDOException $e) {
            error_log("AdminRecomendaciones::obtenerContextoPorModelo: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPERS INTERNOS
    // ═══════════════════════════════════════════════════════════════════════
    private function score($v): float
    {
        return max(0.0, min(100.0, (float)$v));
    }

    private function buildJsonb($v): string
    {
        if (is_array($v))  return json_encode($v, JSON_UNESCAPED_UNICODE);
        if (is_string($v)) {
            json_decode($v);
            return json_last_error() === JSON_ERROR_NONE ? $v : json_encode(['raw' => $v]);
        }
        return '{}';
    }

    private function existeAtraccion(int $id): bool
    {
        if (!$id) return false;
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM catalogo.atracciones WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }

    private function existeModelo(int $id): bool
    {
        if (!$id) return false;
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM recomendaciones.modelos_mr WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return (bool)$stmt->fetchColumn();
    }
}