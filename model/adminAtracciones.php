<?php
/*
 * model/adminAtracciones.php
 * ─────────────────────────────────────────────────────────────────────────────
 * CRUD completo sobre catalogo.atracciones para el panel de administración.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AdminAtracciones
{
    private $pdo;

    private const DIAS = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];

    public function __CONSTRUCT()
    {
        try {
            $this->pdo = Database::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  STATS para el strip de KPIs
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerStats(): array
    {
        try {
            $row = $this->pdo->query("
                SELECT
                    COUNT(*)                                             AS total,
                    COUNT(*) FILTER (WHERE estado = 'activo')           AS activas,
                    COUNT(*) FILTER (WHERE estado = 'inactivo')         AS inactivas,
                    COUNT(*) FILTER (WHERE precio_entrada = 0)          AS gratuitas,
                    ROUND(AVG(puntuacion_promedio)::numeric, 2)         AS promedio_puntaje
                FROM catalogo.atracciones
            ")->fetch(PDO::FETCH_ASSOC);

            return $row ?: ['total'=>0,'activas'=>0,'inactivas'=>0,'gratuitas'=>0,'promedio_puntaje'=>0];
        } catch (PDOException $e) {
            error_log("AdminAtracciones::obtenerStats: " . $e->getMessage());
            return ['total'=>0,'activas'=>0,'inactivas'=>0,'gratuitas'=>0,'promedio_puntaje'=>0];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  LISTAR con filtros, búsqueda y paginación
    // ═══════════════════════════════════════════════════════════════════════
    public function listar(array $f = []): array
    {
        try {
            $page    = max(1, (int)($f['page']     ?? 1));
            $perPage = min(100, max(10, (int)($f['per_page'] ?? 20)));
            $offset  = ($page - 1) * $perPage;
            $orden   = in_array($f['orden'] ?? '', ['asc','desc']) ? strtoupper($f['orden']) : 'DESC';

            $where  = ['1=1'];
            $params = [];

            if (!empty($f['buscar'])) {
                $where[]  = "(a.nombre ILIKE ? OR a.descripcion ILIKE ? OR a.ciudad ILIKE ?)";
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
            }

            if (!empty($f['estado']) && in_array($f['estado'], ['activo','inactivo'])) {
                $where[]  = "a.estado = ?";
                $params[] = $f['estado'];
            }

            if (!empty($f['categoria_id'])) {
                $where[]  = "a.atraccion_categoria_id = ?";
                $params[] = (int)$f['categoria_id'];
            }

            if (!empty($f['ciudad'])) {
                $where[]  = "a.ciudad ILIKE ?";
                $params[] = '%'.$f['ciudad'].'%';
            }

            // Ordenamiento
            $sortCol = match($f['sort'] ?? 'id') {
                'nombre'     => 'a.nombre',
                'puntuacion' => 'a.puntuacion_promedio',
                'precio'     => 'a.precio_entrada',
                'categoria'  => 'a.atraccion_categoria_id',
                'updated_at' => 'a.updated_at',
                default      => 'a.id',
            };

            $whereSQL = implode(' AND ', $where);

            // Count
            $stmtC = $this->pdo->prepare(
                "SELECT COUNT(*) FROM catalogo.atracciones a WHERE $whereSQL"
            );
            $stmtC->execute($params);
            $total = (int)$stmtC->fetchColumn();

            // Data
            $sql = "
                SELECT
                    a.id,
                    a.nombre,
                    a.descripcion,
                    a.ciudad,
                    a.pais,
                    a.direccion,
                    a.latitud,
                    a.longitud,
                    a.ubicacion,
                    a.horario_apertura,
                    a.horario_cierre,
                    a.dias_operacion,
                    a.precio_entrada,
                    a.moneda,
                    a.tiempo_estimado,
                    a.puntuacion_promedio,
                    a.estado,
                    a.atraccion_categoria_id,
                    a.created_at,
                    a.updated_at,
                    m.nombre AS categoria_nombre
                FROM catalogo.atracciones a
                LEFT JOIN recomendaciones.modelos_mr m ON m.id = a.atraccion_categoria_id
                WHERE $whereSQL
                ORDER BY $sortCol $orden
                LIMIT $perPage OFFSET $offset
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Decodificar JSON
            foreach ($filas as &$row) {
                foreach (['ubicacion','dias_operacion','tiempo_estimado'] as $campo) {
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
            error_log("AdminAtracciones::listar: " . $e->getMessage());
            return ['total'=>0,'pagina'=>1,'per_page'=>20,'paginas'=>0,'datos'=>[]];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  OBTENER una atracción por ID
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerPorId(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*,
                       m.nombre AS categoria_nombre
                FROM catalogo.atracciones a
                LEFT JOIN recomendaciones.modelos_mr m ON m.id = a.atraccion_categoria_id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            foreach (['ubicacion','dias_operacion','tiempo_estimado'] as $campo) {
                if (is_string($row[$campo])) {
                    $row[$campo] = json_decode($row[$campo], true) ?? [];
                }
            }
            return $row;
        } catch (PDOException $e) {
            error_log("AdminAtracciones::obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CREAR atracción
    // ═══════════════════════════════════════════════════════════════════════
    public function crear(array $d): int|false
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO catalogo.atracciones
                    (nombre, descripcion, ubicacion, latitud, longitud, direccion,
                     ciudad, pais, horario_apertura, horario_cierre, dias_operacion,
                     precio_entrada, moneda, tiempo_estimado, puntuacion_promedio,
                     estado, atraccion_categoria_id, created_at, updated_at)
                VALUES
                    (?,?,?::jsonb,?,?,?,?,?,?,?,?::jsonb,?,?,?::jsonb,?,?,?,NOW(),NOW())
                RETURNING id
            ");
            $stmt->execute([
                $this->str($d['nombre']),
                $this->str($d['descripcion'] ?? ''),
                $this->encodeJson(['zona' => $d['zona'] ?? '']),
                $this->num($d['latitud']  ?? null),
                $this->num($d['longitud'] ?? null),
                $this->str($d['direccion'] ?? ''),
                $this->str($d['ciudad']    ?? 'La Paz'),
                $this->str($d['pais']      ?? 'Bolivia'),
                $this->hora($d['horario_apertura'] ?? null),
                $this->hora($d['horario_cierre']   ?? null),
                $this->buildDias($d['dias_operacion'] ?? []),
                (float)($d['precio_entrada'] ?? 0),
                $this->str($d['moneda'] ?? 'BOB'),
                $this->buildTiempo($d['visita'] ?? '', $d['traslado'] ?? ''),
                (float)($d['puntuacion_promedio'] ?? 0),
                in_array($d['estado'] ?? 'activo', ['activo','inactivo']) ? $d['estado'] : 'activo',
                !empty($d['atraccion_categoria_id']) ? (int)$d['atraccion_categoria_id'] : null,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['id'] ?? 0);
        } catch (PDOException $e) {
            error_log("AdminAtracciones::crear: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACTUALIZAR atracción
    // ═══════════════════════════════════════════════════════════════════════
    public function actualizar(int $id, array $d): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE catalogo.atracciones SET
                    nombre                = ?,
                    descripcion           = ?,
                    ubicacion             = ?::jsonb,
                    latitud               = ?,
                    longitud              = ?,
                    direccion             = ?,
                    ciudad                = ?,
                    pais                  = ?,
                    horario_apertura      = ?,
                    horario_cierre        = ?,
                    dias_operacion        = ?::jsonb,
                    precio_entrada        = ?,
                    moneda                = ?,
                    tiempo_estimado       = ?::jsonb,
                    estado                = ?,
                    atraccion_categoria_id= ?,
                    updated_at            = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $this->str($d['nombre']),
                $this->str($d['descripcion'] ?? ''),
                $this->encodeJson(['zona' => $d['zona'] ?? '']),
                $this->num($d['latitud']  ?? null),
                $this->num($d['longitud'] ?? null),
                $this->str($d['direccion'] ?? ''),
                $this->str($d['ciudad']    ?? 'La Paz'),
                $this->str($d['pais']      ?? 'Bolivia'),
                $this->hora($d['horario_apertura'] ?? null),
                $this->hora($d['horario_cierre']   ?? null),
                $this->buildDias($d['dias_operacion'] ?? []),
                (float)($d['precio_entrada'] ?? 0),
                $this->str($d['moneda'] ?? 'BOB'),
                $this->buildTiempo($d['visita'] ?? '', $d['traslado'] ?? ''),
                in_array($d['estado'] ?? 'activo', ['activo','inactivo']) ? $d['estado'] : 'activo',
                !empty($d['atraccion_categoria_id']) ? (int)$d['atraccion_categoria_id'] : null,
                $id,
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminAtracciones::actualizar: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TOGGLE ESTADO  activo ↔ inactivo
    // ═══════════════════════════════════════════════════════════════════════
    public function toggleEstado(int $id): array
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE catalogo.atracciones
                SET estado     = CASE WHEN estado='activo' THEN 'inactivo' ELSE 'activo' END,
                    updated_at = NOW()
                WHERE id = ?
                RETURNING estado
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['ok' => true, 'nuevo_estado' => $row['estado'] ?? 'activo'];
        } catch (PDOException $e) {
            error_log("AdminAtracciones::toggleEstado: " . $e->getMessage());
            return ['ok' => false];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ELIMINAR  (hard delete — solo si estado = inactivo)
    // ═══════════════════════════════════════════════════════════════════════
    public function eliminar(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM catalogo.atracciones WHERE id = ? AND estado = 'inactivo'
            ");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminAtracciones::eliminar: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CATEGORÍAS para el select del modal (modelos_mr como fuente de nombres)
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerCategorias(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT ac.id, m.nombre, m.version
                FROM catalogo.atraccion_categorias ac
                LEFT JOIN recomendaciones.modelos_mr m ON m.id = ac.id
                ORDER BY ac.id ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminAtracciones::obtenerCategorias: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPERS INTERNOS
    // ═══════════════════════════════════════════════════════════════════════

    private function str(?string $v): string { return trim($v ?? ''); }
    private function num($v): ?float { return is_numeric($v) ? (float)$v : null; }
    private function hora(?string $v): ?string {
        if (!$v) return null;
        if (preg_match('/^\d{2}:\d{2}$/', $v)) return $v . ':00';
        return $v;
    }
    private function encodeJson(array $v): string { return json_encode($v, JSON_UNESCAPED_UNICODE); }

    private function buildDias(array $dias): string
    {
        $result = [];
        foreach (self::DIAS as $d) {
            $result[$d] = in_array($d, $dias) || ($dias[$d] ?? false) === true;
        }
        return json_encode($result);
    }

    private function buildTiempo(string $visita, string $traslado): string
    {
        return json_encode([
            'visita'   => $this->str($visita)   ?: '30 minutos',
            'traslado' => $this->str($traslado) ?: '0 minutos',
        ], JSON_UNESCAPED_UNICODE);
    }
}