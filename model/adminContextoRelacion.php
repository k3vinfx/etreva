<?php
/*
 * model/adminContextoRelacion.php
 * ─────────────────────────────────────────────────────────────────────────────
 * CRUD completo sobre recomendaciones.contexto_relacion para el panel de admin.
 *
 * Tabla: recomendaciones.contexto_relacion
 *   id, modelo_mr_id, tipo_contexto, valor, estado, fecha_creacion
 *
 * FK: modelo_mr_id → recomendaciones.modelos_mr.id
 * Eliminar = cambio de estado (soft delete)
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AdminContextoRelacion
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
                    COUNT(*)                                      AS total,
                    COUNT(*) FILTER (WHERE estado = true)         AS activos,
                    COUNT(*) FILTER (WHERE estado = false)        AS inactivos,
                    COUNT(DISTINCT modelo_mr_id)                  AS modelos_usados,
                    COUNT(DISTINCT tipo_contexto)                 AS tipos_distintos
                FROM recomendaciones.contexto_relacion
            ")->fetch(PDO::FETCH_ASSOC);

            return $row ?: ['total'=>0,'activos'=>0,'inactivos'=>0,'modelos_usados'=>0,'tipos_distintos'=>0];
        } catch (PDOException $e) {
            error_log("AdminContextoRelacion::obtenerStats: " . $e->getMessage());
            return ['total'=>0,'activos'=>0,'inactivos'=>0,'modelos_usados'=>0,'tipos_distintos'=>0];
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

            // Búsqueda por valor o tipo
            if (!empty($f['buscar'])) {
                $where[]  = "(cr.valor ILIKE ? OR cr.tipo_contexto ILIKE ? OR m.nombre ILIKE ?)";
                $params[] = '%' . $f['buscar'] . '%';
                $params[] = '%' . $f['buscar'] . '%';
                $params[] = '%' . $f['buscar'] . '%';
            }

            // Filtro por modelo
            if (!empty($f['modelo_id'])) {
                $where[]  = "cr.modelo_mr_id = ?";
                $params[] = (int)$f['modelo_id'];
            }

            // Filtro por tipo_contexto
            if (!empty($f['tipo_contexto'])) {
                $where[]  = "cr.tipo_contexto = ?";
                $params[] = $f['tipo_contexto'];
            }

            // Filtro por estado
            if ($f['estado'] ?? '' === 'activo') {
                $where[] = "cr.estado = true";
            } elseif ($f['estado'] ?? '' === 'inactivo') {
                $where[] = "cr.estado = false";
            }

            // Ordenamiento
            $sortCol = match($f['sort'] ?? 'id') {
                'modelo'        => 'm.nombre',
                'tipo_contexto' => 'cr.tipo_contexto',
                'valor'         => 'cr.valor',
                'estado'        => 'cr.estado',
                'fecha'         => 'cr.fecha_creacion',
                default         => 'cr.id',
            };
            $orden = strtoupper(in_array($f['orden'] ?? '', ['asc','desc']) ? $f['orden'] : 'asc');

            $whereSQL = implode(' AND ', $where);

            // Count total
            $stmtC = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM recomendaciones.contexto_relacion cr
                JOIN recomendaciones.modelos_mr m ON m.id = cr.modelo_mr_id
                WHERE $whereSQL
            ");
            $stmtC->execute($params);
            $total = (int)$stmtC->fetchColumn();

            // Data
            $sql = "
                SELECT
                    cr.id,
                    cr.modelo_mr_id,
                    cr.tipo_contexto,
                    cr.valor,
                    cr.estado,
                    cr.fecha_creacion,
                    m.nombre   AS modelo_nombre,
                    m.version  AS modelo_version,
                    m.estado   AS modelo_estado
                FROM recomendaciones.contexto_relacion cr
                JOIN recomendaciones.modelos_mr m ON m.id = cr.modelo_mr_id
                WHERE $whereSQL
                ORDER BY $sortCol $orden
                LIMIT $perPage OFFSET $offset
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total'    => $total,
                'pagina'   => $page,
                'per_page' => $perPage,
                'paginas'  => max(1, (int)ceil($total / $perPage)),
                'datos'    => $filas,
            ];

        } catch (PDOException $e) {
            error_log("AdminContextoRelacion::listar: " . $e->getMessage());
            return ['total'=>0,'pagina'=>1,'per_page'=>20,'paginas'=>0,'datos'=>[]];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  LISTAR TODOS para reporte (sin paginación)
    // ═══════════════════════════════════════════════════════════════════════
    public function listarTodo(array $f = []): array
    {
        try {
            $where  = ['1=1'];
            $params = [];

            if (!empty($f['buscar'])) {
                $where[]  = "(cr.valor ILIKE ? OR cr.tipo_contexto ILIKE ? OR m.nombre ILIKE ?)";
                $params[] = '%' . $f['buscar'] . '%';
                $params[] = '%' . $f['buscar'] . '%';
                $params[] = '%' . $f['buscar'] . '%';
            }
            if (!empty($f['modelo_id'])) {
                $where[]  = "cr.modelo_mr_id = ?";
                $params[] = (int)$f['modelo_id'];
            }
            if (!empty($f['tipo_contexto'])) {
                $where[]  = "cr.tipo_contexto = ?";
                $params[] = $f['tipo_contexto'];
            }
            if ($f['estado'] ?? '' === 'activo') {
                $where[] = "cr.estado = true";
            } elseif ($f['estado'] ?? '' === 'inactivo') {
                $where[] = "cr.estado = false";
            }

            $whereSQL = implode(' AND ', $where);
            $stmt = $this->pdo->prepare("
                SELECT
                    cr.id, cr.modelo_mr_id, cr.tipo_contexto, cr.valor,
                    cr.estado, cr.fecha_creacion,
                    m.nombre AS modelo_nombre, m.version AS modelo_version
                FROM recomendaciones.contexto_relacion cr
                JOIN recomendaciones.modelos_mr m ON m.id = cr.modelo_mr_id
                WHERE $whereSQL
                ORDER BY cr.modelo_mr_id, cr.tipo_contexto, cr.valor
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminContextoRelacion::listarTodo: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  OBTENER por ID
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerPorId(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT cr.*,
                       m.nombre  AS modelo_nombre,
                       m.version AS modelo_version
                FROM recomendaciones.contexto_relacion cr
                JOIN recomendaciones.modelos_mr m ON m.id = cr.modelo_mr_id
                WHERE cr.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log("AdminContextoRelacion::obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CREAR
    // ═══════════════════════════════════════════════════════════════════════
    public function crear(array $d): int|false
    {
        try {
            if (!$this->existeModelo((int)($d['modelo_mr_id'] ?? 0))) return false;

            $stmt = $this->pdo->prepare("
                INSERT INTO recomendaciones.contexto_relacion
                    (modelo_mr_id, tipo_contexto, valor, estado, fecha_creacion)
                VALUES (?, ?, ?, ?::boolean, NOW())
                RETURNING id
            ");
            $stmt->execute([
                (int)$d['modelo_mr_id'],
                trim($d['tipo_contexto'] ?? ''),
                trim($d['valor']         ?? ''),
                (isset($d['estado']) && $d['estado'] !== false && $d['estado'] !== 'false') ? 'true' : 'false',
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['id'] ?? 0);
        } catch (PDOException $e) {
            error_log("AdminContextoRelacion::crear: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ACTUALIZAR
    // ═══════════════════════════════════════════════════════════════════════
    public function actualizar(int $id, array $d): bool
    {
        try {
            if (!$this->existeModelo((int)($d['modelo_mr_id'] ?? 0))) return false;

            // Convertir explícitamente a string para PostgreSQL
            $estado = (isset($d['estado']) && $d['estado'] !== false && $d['estado'] !== 'false' && $d['estado'] !== 0)
                ? 'true'
                : 'false';

            $stmt = $this->pdo->prepare("
                UPDATE recomendaciones.contexto_relacion SET
                    modelo_mr_id  = ?,
                    tipo_contexto = ?,
                    valor         = ?,
                    estado        = ?::boolean
                WHERE id = ?
            ");
            $stmt->execute([
                (int)$d['modelo_mr_id'],
                trim($d['tipo_contexto'] ?? ''),
                trim($d['valor']         ?? ''),
                $estado,
                $id,
            ]);

            // rowCount() puede ser 0 si los valores no cambiaron pero el registro existe
            return $stmt->rowCount() >= 0;

        } catch (PDOException $e) {
            error_log("AdminContextoRelacion::actualizar: " . $e->getMessage());
            return false;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ELIMINAR (soft delete — cambio de estado)
    // ═══════════════════════════════════════════════════════════════════════
    public function cambiarEstado(int $id): array
    {
        try {
            $actual = $this->pdo->prepare(
                "SELECT estado FROM recomendaciones.contexto_relacion WHERE id = ?"
            );
            $actual->execute([$id]);
            $row = $actual->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['ok' => false, 'estado' => null];

            // ✅ Normalizar estado actual igual que en el JS
            $estadoActual = ($row['estado'] === true || $row['estado'] === 't' || $row['estado'] === '1');
            $nuevoEstado  = $estadoActual ? 'false' : 'true'; // string para PostgreSQL

            $stmt = $this->pdo->prepare(
                "UPDATE recomendaciones.contexto_relacion SET estado = ?::boolean WHERE id = ?"
            );
            $stmt->execute([$nuevoEstado, $id]);

            return ['ok' => $stmt->rowCount() > 0, 'estado' => ($nuevoEstado === 'true')];
        } catch (PDOException $e) {
            error_log("AdminContextoRelacion::cambiarEstado: " . $e->getMessage());
            return ['ok' => false, 'estado' => null];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TIPOS DISTINTOS (para el filtro dropdown)
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerTipos(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT DISTINCT tipo_contexto
                FROM recomendaciones.contexto_relacion
                ORDER BY tipo_contexto ASC
            ");
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'tipo_contexto');
        } catch (PDOException $e) {
            error_log("AdminContextoRelacion::obtenerTipos: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  MODELOS (para dropdowns)
    // ═══════════════════════════════════════════════════════════════════════
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
            error_log("AdminContextoRelacion::obtenerTodosModelos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerModelosActivos(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT id, nombre, version
                FROM recomendaciones.modelos_mr
                WHERE estado = 'activo'
                ORDER BY id ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminContextoRelacion::obtenerModelosActivos: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════════════
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