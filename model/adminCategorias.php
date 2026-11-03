<?php
/*
 * model/adminCategorias.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Gestión de atraccion_categorias:
 *   - categoria_rangos   → JSONB array [{categoria, rango_inicio, rango_fin, estado}]
 *   - subcategoria_rangos → JSONB array [{subcategoria, rango_inicio, rango_fin, estado}]
 *
 * El campo "estado" (activo|inactivo) vive DENTRO de cada objeto del JSON.
 * Si un objeto no tiene estado se asume "activo".
 * ─────────────────────────────────────────────────────────────────────────────
 */
class AdminCategorias
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
    //  OBTENER TODAS LAS FILAS de atraccion_categorias
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerTodos(): array
    {
        $sql  = "SELECT ac.id,
                        ac.categoria_rangos,
                        ac.subcategoria_rangos,
                        ac.created_at,
                        ac.updated_at,
                        m.nombre       AS modelo_nombre,
                        m.descripcion  AS modelo_descripcion,
                        m.version      AS modelo_version
                 FROM catalogo.atraccion_categorias ac
                 LEFT JOIN recomendaciones.modelos_mr m ON m.id = ac.id
                 ORDER BY ac.id ASC";
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['categoria_rangos']    = json_decode($row['categoria_rangos']    ?? '[]', true) ?: [];
            $row['subcategoria_rangos'] = json_decode($row['subcategoria_rangos'] ?? '[]', true) ?: [];

            // Asegurar campo estado en cada objeto
            foreach ($row['categoria_rangos'] as &$c) {
                if (!isset($c['estado'])) $c['estado'] = 'activo';
            }
            foreach ($row['subcategoria_rangos'] as &$s) {
                if (!isset($s['estado'])) $s['estado'] = 'activo';
            }
        }
        return $rows;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  OBTENER UNA FILA POR ID
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerPorId(int $id): ?array
    {
        $sql  = "SELECT id, categoria_rangos, subcategoria_rangos, created_at, updated_at
                 FROM catalogo.atraccion_categorias WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return null;

        $row['categoria_rangos']    = json_decode($row['categoria_rangos']    ?? '[]', true) ?: [];
        $row['subcategoria_rangos'] = json_decode($row['subcategoria_rangos'] ?? '[]', true) ?: [];

        foreach ($row['categoria_rangos'] as &$c) {
            if (!isset($c['estado'])) $c['estado'] = 'activo';
        }
        foreach ($row['subcategoria_rangos'] as &$s) {
            if (!isset($s['estado'])) $s['estado'] = 'activo';
        }
        return $row;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  STATS GENERALES
    // ═══════════════════════════════════════════════════════════════════════
    public function obtenerStats(): array
    {
        $rows     = $this->obtenerTodos();
        $totalCat = $totalSub = $activasCat = $activasSub = 0;

        foreach ($rows as $row) {
            foreach ($row['categoria_rangos'] as $c) {
                $totalCat++;
                if (($c['estado'] ?? 'activo') === 'activo') $activasCat++;
            }
            foreach ($row['subcategoria_rangos'] as $s) {
                $totalSub++;
                if (($s['estado'] ?? 'activo') === 'activo') $activasSub++;
            }
        }
        return [
            'total_grupos'    => count($rows),
            'total_categorias'=> $totalCat,
            'activas_cat'     => $activasCat,
            'inactivas_cat'   => $totalCat - $activasCat,
            'total_sub'       => $totalSub,
            'activas_sub'     => $activasSub,
            'inactivas_sub'   => $totalSub - $activasSub,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TOGGLE ESTADO CATEGORÍA
    //  Busca por nombre exacto dentro de categoria_rangos del row $grupoId
    // ═══════════════════════════════════════════════════════════════════════
    public function toggleEstadoCategoria(int $grupoId, string $nombreCategoria): array
    {
        $row = $this->obtenerPorId($grupoId);
        if (!$row) return ['ok' => false, 'msg' => 'Grupo no encontrado'];

        $cats    = $row['categoria_rangos'];
        $changed = false;
        $nuevoEstado = '';

        foreach ($cats as &$c) {
            if (($c['categoria'] ?? '') === $nombreCategoria) {
                $actual       = $c['estado'] ?? 'activo';
                $nuevoEstado  = $actual === 'activo' ? 'inactivo' : 'activo';
                $c['estado']  = $nuevoEstado;
                $changed      = true;
                break;
            }
        }
        if (!$changed) return ['ok' => false, 'msg' => 'Categoría no encontrada'];

        $ok = $this->guardarCategorias($grupoId, $cats);
        return [
            'ok'          => $ok,
            'msg'         => $ok ? "Categoría {$nuevoEstado}" : 'Error al guardar',
            'nuevo_estado'=> $nuevoEstado,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TOGGLE ESTADO SUBCATEGORÍA
    // ═══════════════════════════════════════════════════════════════════════
    public function toggleEstadoSubcategoria(int $grupoId, string $keySubcat): array
    {
        $row = $this->obtenerPorId($grupoId);
        if (!$row) return ['ok' => false, 'msg' => 'Grupo no encontrado'];

        $subs    = $row['subcategoria_rangos'];
        $changed = false;
        $nuevoEstado = '';

        foreach ($subs as &$s) {
            if (($s['subcategoria'] ?? '') === $keySubcat) {
                $actual      = $s['estado'] ?? 'activo';
                $nuevoEstado = $actual === 'activo' ? 'inactivo' : 'activo';
                $s['estado'] = $nuevoEstado;
                $changed     = true;
                break;
            }
        }
        if (!$changed) return ['ok' => false, 'msg' => 'Subcategoría no encontrada'];

        $ok = $this->guardarSubcategorias($grupoId, $subs);
        return [
            'ok'          => $ok,
            'msg'         => $ok ? "Subcategoría {$nuevoEstado}" : 'Error al guardar',
            'nuevo_estado'=> $nuevoEstado,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  AGREGAR CATEGORÍA a un grupo existente
    // ═══════════════════════════════════════════════════════════════════════
    public function agregarCategoria(int $grupoId, string $nombre, int $rangoIni, int $rangoFin): array
    {
        $row = $this->obtenerPorId($grupoId);
        if (!$row) return ['ok' => false, 'msg' => 'Grupo no encontrado'];

        // Verificar duplicado
        foreach ($row['categoria_rangos'] as $c) {
            if (strtolower($c['categoria'] ?? '') === strtolower($nombre)) {
                return ['ok' => false, 'msg' => 'Ya existe una categoría con ese nombre'];
            }
        }

        $cats   = $row['categoria_rangos'];
        $cats[] = [
            'categoria'    => $nombre,
            'rango_inicio' => $rangoIni,
            'rango_fin'    => $rangoFin,
            'estado'       => 'activo',
        ];

        $ok = $this->guardarCategorias($grupoId, $cats);
        return ['ok' => $ok, 'msg' => $ok ? 'Categoría agregada' : 'Error al guardar'];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  AGREGAR SUBCATEGORÍA
    // ═══════════════════════════════════════════════════════════════════════
    public function agregarSubcategoria(int $grupoId, string $key, int $rangoIni, int $rangoFin): array
    {
        $row = $this->obtenerPorId($grupoId);
        if (!$row) return ['ok' => false, 'msg' => 'Grupo no encontrado'];

        foreach ($row['subcategoria_rangos'] as $s) {
            if (($s['subcategoria'] ?? '') === $key) {
                return ['ok' => false, 'msg' => 'Ya existe esa subcategoría'];
            }
        }

        $subs   = $row['subcategoria_rangos'];
        $subs[] = [
            'subcategoria' => $key,
            'rango_inicio' => $rangoIni,
            'rango_fin'    => $rangoFin,
            'estado'       => 'activo',
        ];

        $ok = $this->guardarSubcategorias($grupoId, $subs);
        return ['ok' => $ok, 'msg' => $ok ? 'Subcategoría agregada' : 'Error al guardar'];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  EDITAR RANGOS DE CATEGORÍA
    // ═══════════════════════════════════════════════════════════════════════
    public function editarCategoria(int $grupoId, string $nombre, int $rangoIni, int $rangoFin): array
    {
        $row = $this->obtenerPorId($grupoId);
        if (!$row) return ['ok' => false, 'msg' => 'Grupo no encontrado'];

        $cats    = $row['categoria_rangos'];
        $changed = false;
        foreach ($cats as &$c) {
            if (($c['categoria'] ?? '') === $nombre) {
                $c['rango_inicio'] = $rangoIni;
                $c['rango_fin']    = $rangoFin;
                $changed = true;
                break;
            }
        }
        if (!$changed) return ['ok' => false, 'msg' => 'Categoría no encontrada'];

        $ok = $this->guardarCategorias($grupoId, $cats);
        return ['ok' => $ok, 'msg' => $ok ? 'Rangos actualizados' : 'Error al guardar'];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  EDITAR RANGOS DE SUBCATEGORÍA
    // ═══════════════════════════════════════════════════════════════════════
    public function editarSubcategoria(int $grupoId, string $key, int $rangoIni, int $rangoFin): array
    {
        $row = $this->obtenerPorId($grupoId);
        if (!$row) return ['ok' => false, 'msg' => 'Grupo no encontrado'];

        $subs    = $row['subcategoria_rangos'];
        $changed = false;
        foreach ($subs as &$s) {
            if (($s['subcategoria'] ?? '') === $key) {
                $s['rango_inicio'] = $rangoIni;
                $s['rango_fin']    = $rangoFin;
                $changed = true;
                break;
            }
        }
        if (!$changed) return ['ok' => false, 'msg' => 'Subcategoría no encontrada'];

        $ok = $this->guardarSubcategorias($grupoId, $subs);
        return ['ok' => $ok, 'msg' => $ok ? 'Rangos actualizados' : 'Error al guardar'];
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPERS PRIVADOS — persisten JSON en BD
    // ═══════════════════════════════════════════════════════════════════════
    private function guardarCategorias(int $grupoId, array $cats): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE catalogo.atraccion_categorias
             SET categoria_rangos = :json, updated_at = NOW()
             WHERE id = :id"
        );
        return $stmt->execute([':json' => json_encode($cats), ':id' => $grupoId]);
    }

    private function guardarSubcategorias(int $grupoId, array $subs): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE catalogo.atraccion_categorias
             SET subcategoria_rangos = :json, updated_at = NOW()
             WHERE id = :id"
        );
        return $stmt->execute([':json' => json_encode($subs), ':id' => $grupoId]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  CUÁNTAS ATRACCIONES tiene cada categoría/subcategoría
    // ═══════════════════════════════════════════════════════════════════════
    public function contarAtraccionesPorGrupo(): array
    {
        try {
            $sql  = "SELECT atraccion_categoria_id, COUNT(*) AS total
                     FROM catalogo.atracciones
                     GROUP BY atraccion_categoria_id";
            $stmt = $this->pdo->query($sql);
            $res  = [];
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                $res[(int)$r['atraccion_categoria_id']] = (int)$r['total'];
            }
            return $res;
        } catch (\Exception $e) {
            return [];
        }
    }
}