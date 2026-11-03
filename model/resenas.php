<?php
require_once __DIR__ . '/imagenAtraccion.php';
require_once __DIR__ . '/avatar.php';
require_once __DIR__ . '/catalogoI18n.php';
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!class_exists('ResenasModel')):

class ResenasModel {

    private $pdo;

    private $CATEGORIA_ID_MAP = [
        1 => 'Trekking',
        2 => 'Montañismo',
        3 => 'Cultura',
        4 => 'Downhill',
        5 => 'Vida Nocturna',
        6 => 'Gastronomía',
        7 => 'Iconos Urbanos',
    ];

    public function __CONSTRUCT() {
        try {
            $this->pdo = Database::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    private function extraerImagen($ubicacion_json) {
        if (empty($ubicacion_json)) return 'assets/img/default.jpg';
        $ub = is_array($ubicacion_json)
            ? $ubicacion_json
            : (json_decode($ubicacion_json, true) ?: []);

        if (!empty($ub['rutas'][0])) return $ub['rutas'][0];

        return $ub['imagen']     ??
               $ub['imagen_url'] ??
               $ub['foto']       ??
               $ub['image']      ??
               $ub['img']        ??
               'assets/img/default.jpg';
    }

    private function resolverCategoria($cat_id, $atraccion_id) {
        if (!empty($cat_id) && isset($this->CATEGORIA_ID_MAP[(int)$cat_id])) {
            $claves = [1 => 'trekking', 2 => 'montanismo', 3 => 'cultura', 4 => 'downhill',
                       5 => 'vida_nocturna', 6 => 'gastronomia', 7 => 'iconos_urbanos'];
            if (class_exists('Lang') && isset($claves[(int)$cat_id])) {
                $t = Lang::get('categorias.' . $claves[(int)$cat_id]);
                if ($t !== 'categorias.' . $claves[(int)$cat_id]) return $t;
            }
            return $this->CATEGORIA_ID_MAP[(int)$cat_id];
        }
        try {
            $stmt = $this->pdo->prepare("
                SELECT categoria_rangos FROM catalogo.atraccion_categorias LIMIT 20
            ");
            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($filas as $fila) {
                $rangos = json_decode($fila['categoria_rangos'], true) ?: [];
                foreach ($rangos as $r) {
                    if ($atraccion_id >= $r['rango_cliente'] && $atraccion_id <= $r['rango_fin']) {
                        return $r['categoria'] ?? 'Atracción';
                    }
                }
            }
        } catch (Exception $e) {
            error_log("ResenasModel::resolverCategoria error: " . $e->getMessage());
        }
        return 'Atracción';
    }

    public function obtenerTodasResenas($limite = 200, $offset = 0) {
        try {
            $sql = "SELECT
                        bt.id                       AS resena_id,
                        bt.itinerario_id,
                        bt.atraccion_id,
                        bt.calificacion_usuario     AS calificacion,
                        bt.comentario_usuario       AS comentario,
                        bt.nueva_metricas_json_reajuste,
                        bt.created_at,
                        bt.updated_at,
                        a.nombre                    AS atraccion_nombre,
                        a.descripcion               AS atraccion_descripcion,
                        a.ubicacion,
                        a.ciudad,
                        a.atraccion_categoria_id,
                        i.usuario_id,
                        u.nombre                    AS usuario_nombre,
                        u.apellido                  AS usuario_apellido,
                        u.email                     AS usuario_email,
                        u.configuracion_json        AS usuario_cfg
                    FROM itinerarios.back_testing_mr bt
                    LEFT JOIN " . CatalogoI18n::atracciones() . " a
                        ON a.id = bt.atraccion_id
                    LEFT JOIN itinerarios.itinerarios i
                        ON i.id = bt.itinerario_id
                    LEFT JOIN usuarios.usuarios u
                        ON u.id = i.usuario_id
                    WHERE bt.estado = 'completado'
                    ORDER BY bt.created_at DESC
                    LIMIT ? OFFSET ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limite, $offset]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("ResenasModel::obtenerTodasResenas total=" . count($result));

            foreach ($result as &$row) {
                $row = $this->normalizarResena($row);
            }

            return $result;

        } catch (PDOException $e) {
            error_log("ResenasModel::obtenerTodasResenas error: " . $e->getMessage());
            return [];
        }
    }

    public function contarTodasResenas() {
        try {
            $sql = "SELECT COUNT(*) FROM itinerarios.back_testing_mr WHERE estado = 'completado'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("ResenasModel::contarTodasResenas error: " . $e->getMessage());
            return 0;
        }
    }

    public function obtenerResenasDeUsuario($usuario_id) {
        try {
            $sql = "SELECT
                        bt.id                       AS resena_id,
                        bt.itinerario_id,
                        bt.atraccion_id,
                        bt.calificacion_usuario     AS calificacion,
                        bt.comentario_usuario       AS comentario,
                        bt.nueva_metricas_json_reajuste,
                        bt.created_at,
                        bt.updated_at,
                        a.nombre                    AS atraccion_nombre,
                        a.descripcion               AS atraccion_descripcion,
                        a.ubicacion,
                        a.ciudad,
                        a.atraccion_categoria_id,
                        i.usuario_id,
                        u.nombre                    AS usuario_nombre,
                        u.apellido                  AS usuario_apellido,
                        u.email                     AS usuario_email,
                        u.configuracion_json        AS usuario_cfg
                    FROM itinerarios.back_testing_mr bt
                    LEFT JOIN itinerarios.itinerarios i
                        ON i.id = bt.itinerario_id
                    LEFT JOIN " . CatalogoI18n::atracciones() . " a
                        ON a.id = bt.atraccion_id
                    LEFT JOIN usuarios.usuarios u
                        ON u.id = i.usuario_id
                    WHERE bt.estado = 'completado'
                      AND i.usuario_id = ?
                    ORDER BY bt.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("ResenasModel::obtenerResenasDeUsuario usuario=$usuario_id total=" . count($result));

            foreach ($result as &$row) {
                $row = $this->normalizarResena($row);
            }

            return $result;

        } catch (PDOException $e) {
            error_log("ResenasModel::obtenerResenasDeUsuario error: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerResenaPorId($resena_id, $usuario_id) {
        try {
            $sql = "SELECT
                        bt.id                   AS resena_id,
                        bt.itinerario_id,
                        bt.atraccion_id,
                        bt.calificacion_usuario AS calificacion,
                        bt.comentario_usuario   AS comentario,
                        bt.nueva_metricas_json_reajuste,
                        bt.created_at,
                        bt.updated_at,
                        a.nombre                AS atraccion_nombre,
                        i.usuario_id
                    FROM itinerarios.back_testing_mr bt
                    LEFT JOIN itinerarios.itinerarios i ON i.id = bt.itinerario_id
                    LEFT JOIN " . CatalogoI18n::atracciones() . " a    ON a.id = bt.atraccion_id
                    WHERE bt.id = ?
                      AND i.usuario_id = ?
                      AND bt.estado = 'completado'
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$resena_id, $usuario_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            error_log("ResenasModel::obtenerResenaPorId error: " . $e->getMessage());
            return null;
        }
    }

    public function editarResena($resena_id, $usuario_id, $calificacion, $comentario) {
        try {
            $sqlVerif = "SELECT bt.id FROM itinerarios.back_testing_mr bt
                         LEFT JOIN itinerarios.itinerarios i ON i.id = bt.itinerario_id
                         WHERE bt.id = ? AND i.usuario_id = ? AND bt.estado = 'completado'
                         LIMIT 1";
            $stmtV = $this->pdo->prepare($sqlVerif);
            $stmtV->execute([$resena_id, $usuario_id]);
            if (!$stmtV->fetch()) {
                error_log("ResenasModel::editarResena - no pertenece al usuario o no existe");
                return false;
            }

            $sql = "UPDATE itinerarios.back_testing_mr
                    SET calificacion_usuario = ?,
                        comentario_usuario   = ?,
                        updated_at           = NOW()
                    WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$calificacion, $comentario, $resena_id]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("ResenasModel::editarResena error: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarResena($resena_id, $usuario_id) {
        try {
            $sqlVerif = "SELECT bt.id FROM itinerarios.back_testing_mr bt
                         LEFT JOIN itinerarios.itinerarios i ON i.id = bt.itinerario_id
                         WHERE bt.id = ? AND i.usuario_id = ? AND bt.estado = 'completado'
                         LIMIT 1";
            $stmtV = $this->pdo->prepare($sqlVerif);
            $stmtV->execute([$resena_id, $usuario_id]);
            if (!$stmtV->fetch()) {
                error_log("ResenasModel::eliminarResena - no pertenece al usuario");
                return false;
            }

            $sql = "UPDATE itinerarios.back_testing_mr
                    SET estado     = 'eliminado',
                        updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$resena_id]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("ResenasModel::eliminarResena error: " . $e->getMessage());
            return false;
        }
    }

    public function buscarResenas($termino, $solo_usuario_id = null) {
        try {
            $todas = ($solo_usuario_id)
                ? $this->obtenerResenasDeUsuario($solo_usuario_id)
                : $this->obtenerTodasResenas(500, 0);

            $t = mb_strtolower(trim($termino));

            $filtradas = array_values(array_filter($todas, function($row) use ($t) {
                return str_contains(mb_strtolower($row['atraccion_nombre'] ?? ''), $t)
                    || str_contains(mb_strtolower($row['comentario']       ?? ''), $t)
                    || str_contains(mb_strtolower($row['categoria']        ?? ''), $t);
            }));

            error_log("ResenasModel::buscarResenas termino='$termino' total=" . count($filtradas));
            return $filtradas;

        } catch (Exception $e) {
            error_log("ResenasModel::buscarResenas ERROR: " . $e->getMessage());
            return [];
        }
    }

    private function normalizarResena(array $row): array {
        // Autor: avatar y correo de contacto solo si lo permitió (nunca se envía el email ni la config completa)
        $autor = Avatar::autorPublico($row['usuario_email'] ?? null, $row['usuario_cfg'] ?? null);
        $row['usuario_avatar']   = $autor['avatar'];
        $row['usuario_contacto'] = $autor['contacto'];
        unset($row['usuario_email'], $row['usuario_cfg']);

        $row['atraccion_nombre'] = $row['atraccion_nombre'] ?? 'Atracción';
        $idAtr = (int)($row['atraccion_id'] ?? 0);
        $row['imagen_url']       = $idAtr ? ImagenAtraccion::galeria($idAtr, 1)['fotos'][0]
                                          : $this->extraerImagen($row['ubicacion'] ?? null);
        $row['categoria']        = $this->resolverCategoria(
            $row['atraccion_categoria_id'] ?? null,
            (int)($row['atraccion_id'] ?? 0)
        );

        $row['tags'] = [];
        if (!empty($row['nueva_metricas_json_reajuste'])) {
            $metricas = is_array($row['nueva_metricas_json_reajuste'])
                ? $row['nueva_metricas_json_reajuste']
                : (json_decode($row['nueva_metricas_json_reajuste'], true) ?: []);
            $row['tags'] = $metricas['tags'] ?? [];
        }

        $nombre   = $row['usuario_nombre']   ?? 'Usuario';
        $apellido = $row['usuario_apellido'] ?? '';
        $row['usuario_display'] = $nombre . (!empty($apellido) ? ' ' . mb_substr($apellido, 0, 1) . '.' : '');

        unset(
            $row['ubicacion'],
            $row['atraccion_categoria_id'],
            $row['nueva_metricas_json_reajuste']
        );

        return $row;
    }
}

endif; // class_exists ResenasModel