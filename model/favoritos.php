<?php
require_once __DIR__ . '/imagenAtraccion.php';
require_once __DIR__ . '/catalogoI18n.php';
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class FavoritosModel {

    private $pdo;

    // Mismo mapa que usa Recomendaciones::obtenerTodasAtracciones()
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

    // ══════════════════════════════════════════════════════════════════════
    //  Extraer la URL de imagen desde el JSON de ubicacion de atracciones.
    //  El campo ubicacion guarda un JSON que incluye la ruta de la imagen.
    //  Ej: {"imagen": "assets/img/atracciones/foto.jpg", "lat": -16.5, ...}
    // ══════════════════════════════════════════════════════════════════════
    private function extraerImagen($ubicacion_json) {
        if (empty($ubicacion_json)) return 'assets/img/default.jpg';

        $ub = is_array($ubicacion_json)
            ? $ubicacion_json
            : (json_decode($ubicacion_json, true) ?: []);

        // Probar las claves más comunes donde se guarda la imagen
        return $ub['imagen']     ?? 
               $ub['imagen_url'] ?? 
               $ub['foto']       ?? 
               $ub['image']      ?? 
               $ub['img']        ?? 
               'assets/img/default.jpg';
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Resolver etiqueta de categoría a partir del atraccion_categoria_id
    //  usando el mismo CATEGORIA_ID_MAP que el resto del proyecto.
    //  Si el id no está en el mapa se consulta la tabla atraccion_categorias.
    // ══════════════════════════════════════════════════════════════════════
    private function resolverCategoria($cat_id, $atraccion_id) {
        // 1. Mapa rápido
        if (!empty($cat_id) && isset($this->CATEGORIA_ID_MAP[(int)$cat_id])) {
            $claves = [1 => 'trekking', 2 => 'montanismo', 3 => 'cultura', 4 => 'downhill',
                       5 => 'vida_nocturna', 6 => 'gastronomia', 7 => 'iconos_urbanos'];
            if (class_exists('Lang') && isset($claves[(int)$cat_id])) {
                $t = Lang::get('categorias.' . $claves[(int)$cat_id]);
                if ($t !== 'categorias.' . $claves[(int)$cat_id]) return $t;
            }
            return $this->CATEGORIA_ID_MAP[(int)$cat_id];
        }

        // 2. Buscar por rangos en atraccion_categorias (igual que obtenerAtraccionDeItinerario)
        try {
            $stmt = $this->pdo->prepare("
                SELECT categoria_rangos
                FROM catalogo.atraccion_categorias
                LIMIT 20
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
            error_log("FavoritosModel::resolverCategoria error: " . $e->getMessage());
        }

        return 'Atracción';
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Obtener todos los favoritos de un usuario
    //
    //  - imagen_url  → viene de a.ubicacion (JSON)
    //  - categoria   → viene de a.atraccion_categoria_id + CATEGORIA_ID_MAP
    //  - calificacion/comentario → última fila de back_testing_mr
    // ══════════════════════════════════════════════════════════════════════
    public function obtenerFavoritos($usuario_id) {
        try {
            $sql = "SELECT
                        i.id,
                        i.atracciones_id,
                        i.nombre,
                        i.descripcion,
                        i.fecha_visita,
                        i.estado,
                        i.created_at,
                        i.updated_at,
                        a.nombre                AS atraccion_nombre,
                        a.descripcion           AS atraccion_descripcion,
                        a.ciudad,
                        a.pais,
                        a.ubicacion,
                        a.atraccion_categoria_id,
                        bt.calificacion_usuario AS calificacion,
                        bt.comentario_usuario   AS comentario
                    FROM itinerarios.itinerarios i
                    LEFT JOIN " . CatalogoI18n::atracciones() . " a
                        ON a.id = i.atracciones_id
                    LEFT JOIN itinerarios.back_testing_mr bt
                        ON  bt.itinerario_id = i.id
                        AND bt.id = (
                            SELECT MAX(bt2.id)
                            FROM itinerarios.back_testing_mr bt2
                            WHERE bt2.itinerario_id = i.id
                        )
                    WHERE i.usuario_id = ?
                      AND i.estado = 'favorito'
                    ORDER BY i.fecha_visita DESC, i.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("FavoritosModel::obtenerFavoritos - usuario $usuario_id: " . count($result) . " registros");

            foreach ($result as &$row) {
                // Nombre y descripción: priorizar el de la atracción
                $row['nombre']      = $row['atraccion_nombre']      ?? $row['nombre']      ?? 'Atracción';
                $row['descripcion'] = $row['atraccion_descripcion'] ?? $row['descripcion'] ?? '';

                // Imagen: extraer del JSON de ubicacion
                $idAtr = (int)($row['atracciones_id'] ?? $row['atraccion_id'] ?? 0);
                $row['imagen_url'] = $idAtr ? ImagenAtraccion::galeria($idAtr, 1)['fotos'][0]
                                            : $this->extraerImagen($row['ubicacion'] ?? null);

                // Categoría: resolver desde atraccion_categoria_id
                $row['categoria'] = $this->resolverCategoria(
                    $row['atraccion_categoria_id'] ?? null,
                    (int)$row['atracciones_id']
                );

                // Limpiar columnas intermedias
                unset(
                    $row['atraccion_nombre'],
                    $row['atraccion_descripcion'],
                    $row['ubicacion'],
                    $row['atraccion_categoria_id']
                );
            }

            return $result;

        } catch (PDOException $e) {
            error_log("FavoritosModel::obtenerFavoritos error: " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Obtener favoritos filtrados por mes y año
    //  Reutiliza obtenerFavoritos() y filtra en PHP con DateTime,
    //  igual que ItinerariosModel::obtenerItinerariosFiltrados()
    // ══════════════════════════════════════════════════════════════════════
    public function obtenerFavoritosFiltrados($usuario_id, $mes, $anio) {
        try {
            $todos = $this->obtenerFavoritos($usuario_id);

            $tz = new DateTimeZone('America/La_Paz');

            $filtrados = array_values(array_filter($todos, function($row) use ($mes, $anio, $tz) {
                if (empty($row['fecha_visita'])) return false;
                try {
                    $dt = new DateTime($row['fecha_visita']);
                    $dt->setTimezone($tz);
                    return (int)$dt->format('m') === (int)$mes
                        && (int)$dt->format('Y') === (int)$anio;
                } catch (Exception $e) {
                    return false;
                }
            }));

            error_log("FavoritosModel::obtenerFavoritosFiltrados mes=$mes anio=$anio total=" . count($filtrados));
            return $filtrados;

        } catch (Exception $e) {
            error_log("FavoritosModel::obtenerFavoritosFiltrados ERROR: " . $e->getMessage());
            return [];
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Quitar de favoritos → vuelve al estado 'visitado'
    //  Solo aplica si el registro pertenece al usuario
    // ══════════════════════════════════════════════════════════════════════
    public function quitarFavorito($itinerario_id, $usuario_id) {
        try {
            $sql = "UPDATE itinerarios.itinerarios
                    SET estado = 'visitado', updated_at = NOW()
                    WHERE id = ? AND usuario_id = ? AND estado = 'favorito'";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$itinerario_id, $usuario_id]);

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("FavoritosModel::quitarFavorito error: " . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Contar favoritos totales del usuario (para stats / dashboard)
    // ══════════════════════════════════════════════════════════════════════
    public function contarFavoritos($usuario_id) {
        try {
            $sql = "SELECT COUNT(*) FROM itinerarios.itinerarios
                    WHERE usuario_id = ? AND estado = 'favorito'";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);

            return (int)$stmt->fetchColumn();

        } catch (PDOException $e) {
            error_log("FavoritosModel::contarFavoritos error: " . $e->getMessage());
            return 0;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Verificar si un itinerario específico ya es favorito
    // ══════════════════════════════════════════════════════════════════════
    public function esFavorito($itinerario_id, $usuario_id) {
        try {
            $sql = "SELECT COUNT(*) FROM itinerarios.itinerarios
                    WHERE id = ? AND usuario_id = ? AND estado = 'favorito'";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$itinerario_id, $usuario_id]);

            return (int)$stmt->fetchColumn() > 0;

        } catch (PDOException $e) {
            error_log("FavoritosModel::esFavorito error: " . $e->getMessage());
            return false;
        }
    }
}