<?php
/*
 * model/adminCatalogoParametros.php
 * ─────────────────────────────────────────────────────────────────────────────
 * CRUD completo sobre recomendaciones.catalogo_parametros para el panel admin.
 *
 * Tabla: recomendaciones.catalogo_parametros
 *   id, modelo_mr_id, grupo, valor, descripcion, estado, fecha_creacion
 *
 * FK: modelo_mr_id → recomendaciones.modelos_mr.id
 * Eliminar = cambio de estado (soft delete / toggle)
 * ─────────────────────────────────────────────────────────────────────────────
 */
class adminCatalogoParametros
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
    //  STATS
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerStats(): array
    {
        try {
            $row = $this->pdo->query("
                SELECT
                    COUNT(*)                                   AS total,
                    COUNT(*) FILTER (WHERE estado = true)      AS activos,
                    COUNT(*) FILTER (WHERE estado = false)     AS inactivos,
                    COUNT(DISTINCT modelo_mr_id)               AS modelos_usados,
                    COUNT(DISTINCT grupo)                      AS grupos_distintos
                FROM recomendaciones.catalogo_parametros
            ")->fetch(PDO::FETCH_ASSOC);

            return $row ?: ['total'=>0,'activos'=>0,'inactivos'=>0,'modelos_usados'=>0,'grupos_distintos'=>0];
        } catch (PDOException $e) {
            error_log("AdminCatalogoParametros::obtenerStats: " . $e->getMessage());
            return ['total'=>0,'activos'=>0,'inactivos'=>0,'modelos_usados'=>0,'grupos_distintos'=>0];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  LISTAR con filtros, búsqueda y paginación
    // ═══════════════════════════════════════════════════════════════════════
    public function listar(array $f = []): array
    {
        try {
            $page    = max(1, (int)($f['page'] ?? 1));
            $perPage = min(100, max(10, (int)($f['per_page'] ?? 20)));
            $offset  = ($page - 1) * $perPage;

            $where  = ['1=1'];
            $params = [];

            if (!empty($f['buscar'])) {
                $where[]  = "(cp.valor ILIKE ? OR cp.descripcion ILIKE ? OR cp.grupo ILIKE ? OR m.nombre ILIKE ?)";
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
            }
            if (!empty($f['modelo_id'])) {
                $where[]  = "cp.modelo_mr_id = ?";
                $params[] = (int)$f['modelo_id'];
            }
            if (!empty($f['grupo'])) {
                $where[]  = "cp.grupo = ?";
                $params[] = $f['grupo'];
            }
            if (($f['estado'] ?? '') === 'activo')   { $where[] = "cp.estado = true";  }
            if (($f['estado'] ?? '') === 'inactivo')  { $where[] = "cp.estado = false"; }

            $sortCol = match($f['sort'] ?? 'id') {
                'modelo'      => 'm.nombre',
                'grupo'       => 'cp.grupo',
                'valor'       => 'cp.valor',
                'descripcion' => 'cp.descripcion',
                'estado'      => 'cp.estado',
                'fecha'       => 'cp.fecha_creacion',
                default       => 'cp.id',
            };
            $orden    = strtoupper(in_array($f['orden'] ?? '', ['asc','desc']) ? $f['orden'] : 'asc');
            $whereSQL = implode(' AND ', $where);

            $stmtC = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM recomendaciones.catalogo_parametros cp
                JOIN recomendaciones.modelos_mr m ON m.id = cp.modelo_mr_id
                WHERE $whereSQL
            ");
            $stmtC->execute($params);
            $total = (int)$stmtC->fetchColumn();

            $stmt = $this->pdo->prepare("
                SELECT
                    cp.id, cp.modelo_mr_id, cp.grupo, cp.valor,
                    cp.descripcion, cp.estado, cp.fecha_creacion,
                    m.nombre  AS modelo_nombre,
                    m.version AS modelo_version,
                    m.estado  AS modelo_estado
                FROM recomendaciones.catalogo_parametros cp
                JOIN recomendaciones.modelos_mr m ON m.id = cp.modelo_mr_id
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
            error_log("AdminCatalogoParametros::listar: " . $e->getMessage());
            return ['total'=>0,'pagina'=>1,'per_page'=>20,'paginas'=>0,'datos'=>[]];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  LISTAR TODO — sin paginación, para exportar
    // ═══════════════════════════════════════════════════════════════════════
    public function listarTodo(array $f = []): array
    {
        try {
            $where  = ['1=1'];
            $params = [];

            if (!empty($f['buscar'])) {
                $where[]  = "(cp.valor ILIKE ? OR cp.descripcion ILIKE ? OR cp.grupo ILIKE ? OR m.nombre ILIKE ?)";
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
                $params[] = '%'.$f['buscar'].'%';
            }
            if (!empty($f['modelo_id'])) {
                $where[]  = "cp.modelo_mr_id = ?";
                $params[] = (int)$f['modelo_id'];
            }
            if (!empty($f['grupo'])) {
                $where[]  = "cp.grupo = ?";
                $params[] = $f['grupo'];
            }
            if (($f['estado'] ?? '') === 'activo')   { $where[] = "cp.estado = true";  }
            if (($f['estado'] ?? '') === 'inactivo')  { $where[] = "cp.estado = false"; }

            $whereSQL = implode(' AND ', $where);
            $stmt = $this->pdo->prepare("
                SELECT
                    cp.id, cp.modelo_mr_id, cp.grupo, cp.valor,
                    cp.descripcion, cp.estado, cp.fecha_creacion,
                    m.nombre AS modelo_nombre, m.version AS modelo_version
                FROM recomendaciones.catalogo_parametros cp
                JOIN recomendaciones.modelos_mr m ON m.id = cp.modelo_mr_id
                WHERE $whereSQL
                ORDER BY cp.modelo_mr_id, cp.grupo, cp.valor
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminCatalogoParametros::listarTodo: " . $e->getMessage());
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
                SELECT cp.*,
                       m.nombre  AS modelo_nombre,
                       m.version AS modelo_version
                FROM recomendaciones.catalogo_parametros cp
                JOIN recomendaciones.modelos_mr m ON m.id = cp.modelo_mr_id
                WHERE cp.id = ?
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log("AdminCatalogoParametros::obtenerPorId: " . $e->getMessage());
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

            $estado = (isset($d['estado']) && $d['estado'] !== false && $d['estado'] !== 'false')
                ? 'true' : 'false';

            $stmt = $this->pdo->prepare("
                INSERT INTO recomendaciones.catalogo_parametros
                    (modelo_mr_id, grupo, valor, descripcion, estado, fecha_creacion)
                VALUES (?, ?, ?, ?, ?::boolean, NOW())
                RETURNING id
            ");
            $stmt->execute([
                (int)$d['modelo_mr_id'],
                trim($d['grupo']       ?? ''),
                trim($d['valor']       ?? ''),
                trim($d['descripcion'] ?? ''),
                $estado,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['id'] ?? 0);
        } catch (PDOException $e) {
            error_log("AdminCatalogoParametros::crear: " . $e->getMessage());
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

            $estado = (isset($d['estado']) && $d['estado'] !== false && $d['estado'] !== 'false' && $d['estado'] !== 0)
                ? 'true' : 'false';

            $stmt = $this->pdo->prepare("
                UPDATE recomendaciones.catalogo_parametros SET
                    modelo_mr_id = ?,
                    grupo        = ?,
                    valor        = ?,
                    descripcion  = ?,
                    estado       = ?::boolean
                WHERE id = ?
            ");
            $stmt->execute([
                (int)$d['modelo_mr_id'],
                trim($d['grupo']       ?? ''),
                trim($d['valor']       ?? ''),
                trim($d['descripcion'] ?? ''),
                $estado,
                $id,
            ]);
            return $stmt->rowCount() >= 0; // ✅ >= 0 por si los valores no cambiaron
        } catch (PDOException $e) {
            error_log("AdminCatalogoParametros::actualizar: " . $e->getMessage());
            return false;
        }
    }
    

    // ═══════════════════════════════════════════════════════════════════════
    //  CAMBIAR ESTADO — soft delete (toggle on/off)
    // ═══════════════════════════════════════════════════════════════════════
    
    public function cambiarEstado(int $id): array
    {
        try {
            $actual = $this->pdo->prepare(
                "SELECT estado FROM recomendaciones.catalogo_parametros WHERE id = ?"
            );
            $actual->execute([$id]);
            $row = $actual->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['ok' => false, 'estado' => null];

            // ✅ Normalizar igual que en JS
            $estadoActual = ($row['estado'] === true || $row['estado'] === 't' || $row['estado'] === '1');
            $nuevoEstado  = $estadoActual ? 'false' : 'true';

            $stmt = $this->pdo->prepare(
                "UPDATE recomendaciones.catalogo_parametros SET estado = ?::boolean WHERE id = ?"
            );
            $stmt->execute([$nuevoEstado, $id]);
            return ['ok' => $stmt->rowCount() > 0, 'estado' => ($nuevoEstado === 'true')];
        } catch (PDOException $e) {
            error_log("AdminCatalogoParametros::cambiarEstado: " . $e->getMessage());
            return ['ok' => false, 'estado' => null];
        }
    } 

    // ═══════════════════════════════════════════════════════════════════════
    //  GRUPOS DISTINTOS — para filtro dropdown
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerGrupos(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT DISTINCT grupo
                FROM recomendaciones.catalogo_parametros
                ORDER BY grupo ASC
            ");
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'grupo');
        } catch (PDOException $e) {
            error_log("AdminCatalogoParametros::obtenerGrupos: " . $e->getMessage());
            return [];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  MODELOS
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
            error_log("AdminCatalogoParametros::obtenerTodosModelos: " . $e->getMessage());
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
            error_log("AdminCatalogoParametros::obtenerModelosActivos: " . $e->getMessage());
            return [];
        }
    }

    // ─── Helper ──────────────────────────────────────────────────────────────
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