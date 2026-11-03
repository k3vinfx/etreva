<?php
require_once __DIR__ . '/imagenAtraccion.php';
require_once __DIR__ . '/catalogoI18n.php';
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class ItinerariosModel {

    private $pdo;

    public function __CONSTRUCT() {
        try {
            $this->pdo = Database::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Obtener todos los itinerarios de un usuario con datos de la atracción
     */
public function obtenerItinerarios($usuario_id) {
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
                    a.nombre AS atraccion_nombre,
                    a.descripcion AS atraccion_descripcion,
                    a.ciudad,
                    a.pais,
                    a.latitud,
                    a.longitud,
                    a.direccion
                 FROM itinerarios.itinerarios i
        LEFT JOIN " . CatalogoI18n::atracciones() . " a ON i.atracciones_id = a.id
        WHERE i.usuario_id = ?
        AND i.estado NOT IN ('eliminado', 'resena')
        ORDER BY i.fecha_visita ASC, i.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Log para depuración
        error_log("Itinerarios encontrados para usuario $usuario_id: " . count($result));

        // Unificar nombres y asegurar imagen por defecto
        foreach ($result as &$row) {
            $row['nombre']      = $row['atraccion_nombre'] ?? $row['nombre'] ?? 'Atracción';
            $row['descripcion'] = $row['atraccion_descripcion'] ?? $row['descripcion'] ?? '';
            // Foto de la atracción (propia, de Wikimedia o referencial): nunca un enlace roto
            $row['imagen_url']  = !empty($row['atracciones_id'])
                ? ImagenAtraccion::galeria((int)$row['atracciones_id'], 1)['fotos'][0]
                : 'assets/img/default.jpg';
            unset($row['atraccion_nombre'], $row['atraccion_descripcion']);
        }
        return $result;
    } catch (PDOException $e) {
        error_log("ItinerariosModel::obtenerItinerarios error: " . $e->getMessage());
        return [];
    }
}

/**
     * Obtener itinerarios filtrados por un rango de fechas
     */
public function obtenerItinerariosFiltrados($usuario_id, $mes, $anio) {
    try {
        // Traer todos y filtrar en PHP con DateTime
        $todos = $this->obtenerItinerarios($usuario_id);

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

        error_log("PHP FILTER mes=$mes anio=$anio total=" . count($filtrados));
        return $filtrados;

    } catch (Exception $e) {
        error_log("obtenerItinerariosFiltrados ERROR: " . $e->getMessage());
        return [];
    }
}

    /**
     * Marcar un itinerario como visitado (solo si pertenece al usuario)
     */
    public function marcarVisitado($itinerario_id, $usuario_id) {
        try {
            $sql = "UPDATE itinerarios.itinerarios 
                    SET estado = 'visitado', updated_at = NOW()
                    WHERE id = ? AND usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$itinerario_id, $usuario_id]);
        } catch (PDOException $e) {
            error_log("ItinerariosModel::marcarVisitado error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un itinerario (solo si pertenece al usuario)
     */
    public function eliminarItinerario($itinerario_id, $usuario_id) {
        try {
            // Cambiamos DELETE por UPDATE para preservar el registro físicamente
            $sql = "UPDATE itinerarios.itinerarios 
                    SET estado = 'eliminado' 
                    WHERE id = ? AND usuario_id = ?";
                    
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$itinerario_id, $usuario_id]);
            
        } catch (PDOException $e) {
            error_log("ItinerariosModel::eliminarItinerario (Soft Delete) error: " . $e->getMessage());
            return false;
        }
    }

    
// ══════════════════════════════════════════════════════════════════════
//  MÉTODO 1: Obtener el modelo MR que contiene la atracción
// ══════════════════════════════════════════════════════════════════════
/**
 * Busca en modelos_mr cuál modelo tiene la atraccion_id en su dataset.
 * También devuelve la categoría de la atracción para armar el input.
 */

public function obtenerModeloPorAtraccion($atraccion_id) {
    try {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, version, metricas_desempeno, configuracion_json
            FROM recomendaciones.modelos_mr
            WHERE estado = 'activo'
            ORDER BY fecha_entrenamiento DESC
        ");
        $stmt->execute();
        $modelos = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($modelos as $modelo) {
            $metricas = json_decode($modelo->metricas_desempeno ?? '', true) ?: [];
            $config   = json_decode($modelo->configuracion_json  ?? '', true) ?: [];

            // Buscar mapeo en todos los lugares posibles
            $mapeo = $metricas['mapeo_input']
                  ?? $config['mapeo_input']
                  ?? $metricas['configuracion']['mapeo_input']
                  ?? $config['configuracion']['mapeo_input']
                  ?? [];

            error_log('Modelo ' . $modelo->id . ' mapeo keys: ' . json_encode(array_keys($mapeo)));

            $dataset = $metricas['dataset_entrenamiento']
                    ?? $metricas['dataset_base']
                    ?? $config['dataset_base']
                    ?? $config['dataset_entrenamiento']
                    ?? [];

            foreach ($dataset as $dato) {
                $output_id = is_array($dato['output'])
                           ? ($dato['output'][0] ?? null)
                           : ($dato['output'] ?? null);

                if ((int)$output_id === (int)$atraccion_id) {
                    error_log('Modelo encontrado id=' . $modelo->id . ' mapeo_count=' . count($mapeo));
                    return [
                        'id'             => $modelo->id,
                        'nombre'         => $modelo->nombre,
                        'version'        => $modelo->version,
                        'mapeo'          => $mapeo,
                        'input_original' => $dato['input'] ?? [],
                    ];
                }
            }
        }

        // Fallback: primer modelo activo
        if (!empty($modelos)) {
            $m       = $modelos[0];
            $metricas = json_decode($m->metricas_desempeno ?? '', true) ?: [];
            $config   = json_decode($m->configuracion_json  ?? '', true) ?: [];
            $mapeo    = $metricas['mapeo_input']
                     ?? $config['mapeo_input']
                     ?? $metricas['configuracion']['mapeo_input']
                     ?? $config['configuracion']['mapeo_input']
                     ?? [];

            return [
                'id'             => $m->id,
                'nombre'         => $m->nombre,
                'version'        => $m->version,
                'mapeo'          => $mapeo,
                'input_original' => [],
            ];
        }

        return null;

    } catch (Exception $e) {
        error_log('obtenerModeloPorAtraccion ERROR: ' . $e->getMessage());
        return null;
    }
}

public function obtenerModeloPorAtraccionOLD($atraccion_id) {
    try {
        // Obtener todos los modelos activos con sus métricas
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, version, metricas_desempeno, configuracion_json
            FROM recomendaciones.modelos_mr
            WHERE estado = 'activo'
            ORDER BY fecha_entrenamiento DESC
        ");
        $stmt->execute();
        $modelos = $stmt->fetchAll(PDO::FETCH_OBJ);

        foreach ($modelos as $modelo) {
            $metricas = json_decode($modelo->metricas_desempeno ?? '', true) ?: [];
            $config   = json_decode($modelo->configuracion_json  ?? '', true) ?: [];

            $dataset = $metricas['dataset_entrenamiento']
                    ?? $metricas['dataset_base']
                    ?? $config['dataset_base']
                    ?? [];

                $mapeo = $metricas['mapeo_input']
                  ?? $config['mapeo_input']
                  ?? $metricas['configuracion']['mapeo_input']
                  ?? $config['configuracion']['mapeo_input']
                  ?? [];

                error_log('Modelo ' . $modelo->id . ' mapeo keys: ' . json_encode(array_keys($mapeo)));

            foreach ($dataset as $dato) {
                $output_id = is_array($dato['output'])
                           ? ($dato['output'][0] ?? null)
                           : $dato['output'];

              if ((int)$output_id === (int)$atraccion_id) {
                    error_log('Modelo encontrado id=' . $modelo->id . ' mapeo_count=' . count($mapeo));
                    return [
                        'id'             => $modelo->id,
                        'nombre'         => $modelo->nombre,
                        'version'        => $modelo->version,
                        'mapeo'          => $mapeo,
                        'input_original' => $dato['input'] ?? [],
                    ];
                }
            }
        }

        // Si no está en ningún modelo, devolver el primero activo
        if (!empty($modelos)) {
            $m = $modelos[0];
            $metricas = json_decode($m->metricas_desempeno ?? '', true) ?: [];
            $config   = json_decode($m->configuracion_json  ?? '', true) ?: [];
            $mapeo    = $metricas['mapeo_input'] ?? $config['mapeo_input'] ?? [];

            return [
                'id'             => $m->id,
                'nombre'         => $m->nombre,
                'version'        => $m->version,
                'mapeo'          => $mapeo,
                'input_original' => [],
            ];
        }

        return null;

    } catch (Exception $e) {
        error_log('obtenerModeloPorAtraccion: ' . $e->getMessage());
        return null;
    }
}

// ══════════════════════════════════════════════════════════════════════
//  MÉTODO 2: Construir input vector desde preferencias del usuario
// ══════════════════════════════════════════════════════════════════════
/**
 * Dado el mapeo_input del modelo y las preferencias del usuario,
 * reconstruye el vector binario de entrada.
 * La calificación ajusta el peso de los campos relevantes.
 */
public function construirInputVector(array $mapeo, array $preferencias, int $calificacion): array {

    // Ajuste gradual: 4-5 estrellas refuerzan los pesos, 1-2 los reducen, 3 los deja igual
    $factores = [1 => 0.60, 2 => 0.80, 3 => 1.00, 4 => 1.10, 5 => 1.20];
    $factor   = $factores[$calificacion] ?? 1.00;

    $input_original = $preferencias['_input_original'] ?? [];

    if (empty($input_original)) {
        error_log('construirInputVector: sin input_original para calificacion=' . $calificacion);
        return [];
    }

    $nuevo = array_map(fn($v) => round($v * $factor, 4), $input_original);

    error_log('calificacion=' . $calificacion . ' factor=' . $factor);
    error_log('input_original:     ' . json_encode($input_original));
    error_log('nuevo_input_vector: ' . json_encode($nuevo));

    return $nuevo;
}

// ══════════════════════════════════════════════════════════════════════
//  MÉTODO 3: Guardar calificación en back_testing_mr
// ══════════════════════════════════════════════════════════════════════
public function guardarCalificacion(
    int    $itinerario_id,
    int    $atraccion_id,
    int    $calificacion,
    string $comentario,
    array  $tags,
    ?int   $modelo_id,
    array  $nueva_metricas
): bool {
    try {
        $stmt = $this->pdo->prepare("
            INSERT INTO itinerarios.back_testing_mr
                (itinerario_id, atraccion_id, calificacion_usuario,
                 comentario_usuario, modelo_mr_fk, nueva_metricas_json_reajuste,
                 estado, created_at, updated_at)
            VALUES
                (:itinerario_id, :atraccion_id, :calificacion,
                 :comentario, :modelo_mr_fk, :nueva_metricas,
                 'completado', NOW(), NOW())
        ");

        // modelo_mr_fk guarda un JSON con el ID del modelo (según tu schema es { })
        $modelo_fk_json = $modelo_id ? json_encode(['modelo_id' => $modelo_id]) : null;

        $stmt->execute([
            ':itinerario_id' => $itinerario_id,
            ':atraccion_id'  => $atraccion_id,
            ':calificacion'  => $calificacion,
            ':comentario'    => !empty($comentario)
                                ? $comentario . (!empty($tags) ? ' [Tags: ' . implode(', ', $tags) . ']' : '')
                                : (!empty($tags) ? implode(', ', $tags) : null),
            ':modelo_mr_fk'  => $modelo_fk_json,
            ':nueva_metricas'=> json_encode($nueva_metricas),
        ]);

        return true;

    } catch (Exception $e) {
        error_log('guardarCalificacion: ' . $e->getMessage());
        return false;
    }
}

// ══════════════════════════════════════════════════════════════════════
//  MÉTODO 4: Actualizar estado del itinerario a 'visitado'
// ══════════════════════════════════════════════════════════════════════
public function marcarComoVisitado(int $itinerario_id, int $usuario_id): bool {
    try {
        $stmt = $this->pdo->prepare("
            UPDATE itinerarios.itinerarios
            SET estado = 'visitado', fecha_visita = NOW(), updated_at = NOW()
            WHERE id = :id AND usuario_id = :uid
        ");
        $stmt->execute([':id' => $itinerario_id, ':uid' => $usuario_id]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        error_log('marcarComoVisitado: ' . $e->getMessage());
        return false;
    }
}

public function obtenerDatosAtraccionDeItinerario(int $itinerario_id): ?array {
    return $this->obtenerAtraccionDeItinerario($itinerario_id);
}


public function obtenerAtraccionDeItinerario(int $itinerario_id): ?array
{
    try {

        // 1️⃣ Obtener itinerario
        $stmt = $this->pdo->prepare("
            SELECT * 
            FROM itinerarios.itinerarios 
            WHERE id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $itinerario_id]);
        $itinerario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$itinerario) return null;

        $atr_id = (int)$itinerario['atracciones_id'];

        // 2️⃣ Obtener atracción
        $stmt2 = $this->pdo->prepare("
            SELECT id, nombre, atraccion_categoria_id
            FROM catalogo.atracciones
            WHERE id = :id
            LIMIT 1
        ");
        $stmt2->execute([':id' => $atr_id]);
        $atr = $stmt2->fetch(PDO::FETCH_ASSOC);

        if (!$atr) return null;

        // 3️⃣ Obtener categoría base
        $stmt3 = $this->pdo->prepare("
            SELECT categoria_rangos, subcategoria_rangos
            FROM catalogo.atraccion_categorias
            WHERE id = :id
            LIMIT 1
        ");
        $stmt3->execute([':id' => $atr['atraccion_categoria_id']]);
        $catRow = $stmt3->fetch(PDO::FETCH_ASSOC);

        if (!$catRow) return null;

        $categoriaFinal = '—';
        $subcategoriaFinal = '—';

        $categoriaRangos = json_decode($catRow['categoria_rangos'], true);
        $subcategoriaRangos = json_decode($catRow['subcategoria_rangos'], true);

        // 4️⃣ Buscar categoría por rango
         foreach ($categoriaRangos as $rango) {
            // ✅ Verifica ambas claves posibles antes de usarlas
            $inicio = $rango['rango_cliente'] ?? $rango['rango_inicio'] ?? $rango['desde'] ?? null;
            $fin    = $rango['rango_fin']     ?? $rango['hasta']        ?? null;

            if ($inicio === null || $fin === null) continue; // salta si falta el rango

            if ($atr_id >= (int)$inicio && $atr_id <= (int)$fin) {
                $categoriaFinal = $rango['categoria'];
                break;
            }
        }


        // 5️⃣ Buscar subcategoría exacta

        foreach ($subcategoriaRangos as $rango) {
            $inicio = $rango['rango_cliente'] ?? $rango['rango_inicio'] ?? $rango['desde'] ?? null;
            $fin    = $rango['rango_fin']     ?? $rango['hasta']        ?? null;

            if ($inicio === null || $fin === null) continue;

            if ($atr_id >= (int)$inicio && $atr_id <= (int)$fin) {
                $subcategoriaFinal = $rango['subcategoria'];
                break;
            }
        }

        return [
            'atracciones_id' => $atr_id,
            'nombre'         => $atr['nombre'],
            'categoria'      => $categoriaFinal,
            'subcategoria'   => $subcategoriaFinal
        ];

    } catch (Exception $e) {
        error_log('obtenerAtraccionDeItinerario error: ' . $e->getMessage());
        return null;
    }

    
}

/**
 * Cambia el estado de un itinerario de 'visitado' a 'favorito'
 */
public function marcarComoFavorito($itinerario_id, $usuario_id) {
    try {
        $sql = "UPDATE itinerarios.itinerarios 
                SET estado = 'favorito', updated_at = NOW()
                WHERE id = ? AND usuario_id = ? AND estado = 'visitado'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$itinerario_id, $usuario_id]);
    } catch (PDOException $e) {
        error_log("ItinerariosModel::marcarComoFavorito error: " . $e->getMessage());
        return false;
    }
}


















}