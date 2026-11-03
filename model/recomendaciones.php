<?php
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/catalogoI18n.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class Recomendaciones
{
    private $pdo;

    // ── Tablas por idioma ──────────────────────────────────────────────────
    private $tbl_atracciones;          // catalogo.atracciones  ó  catalogo.atracciones_en
    private $tbl_atraccion_categorias; // catalogo.atraccion_categorias  ó  catalogo.atracciones_categorias_en

    public function __CONSTRUCT()
    {
        try {
            $this->pdo = Database::Conectar();
        } catch(Exception $e) {
            die($e->getMessage());
        }

        // ── Seleccionar tablas según idioma de sesión ──────────────────────
        // Textos de atracciones: en el idioma de la sesión (con respaldo en inglés/español).
        // Categorías: en inglés para la LÓGICA (detección de festividades y meses "Sep - Nov");
        // las etiquetas visibles se traducen al final con etiquetaCategoria().
        $lang = CatalogoI18n::idioma();
        $this->tbl_atracciones          = CatalogoI18n::atracciones();
        $this->tbl_atraccion_categorias = $lang === 'es' ? 'catalogo.atraccion_categorias' : 'catalogo.atracciones_categorias_en';
    }

    private ?array $mapaCategorias = null;

    /**
     * Perfil por atracción: [id => [motivo_viaje[], compania[], experiencias[], dificultad, duracion_horas]]
     * Una sola consulta sobre los modelos MR (570 alternativas) + duración del contexto de recomendaciones.
     */
    public function perfilesAtracciones(): array
    {
        $out = [];
        try {
            $sql = "SELECT (a->>'id')::int AS id,
                           a->'motivo_viaje' AS motivo, a->'compania' AS compania,
                           a->'experiencias' AS experiencias, a->>'dificultad' AS dificultad
                    FROM recomendaciones.modelos_mr m,
                         jsonb_array_elements(m.configuracion_json->'alternativas') a";
            foreach ($this->pdo->query($sql) as $r) {
                $out[(int)$r['id']] = [
                    'motivo_viaje' => json_decode($r['motivo'] ?? '[]', true) ?: [],
                    'compania'     => json_decode($r['compania'] ?? '[]', true) ?: [],
                    'experiencias' => json_decode($r['experiencias'] ?? '[]', true) ?: [],
                    'dificultad'   => $r['dificultad'] ?: null,
                    'duracion_horas' => null,
                ];
            }
            $dur = $this->pdo->query("SELECT atraccion_id, (contexto_recomendacion->>'duracion_horas')::numeric AS h
                                      FROM recomendaciones.recomendaciones WHERE contexto_recomendacion->>'duracion_horas' IS NOT NULL");
            foreach ($dur as $r) {
                $id = (int)$r['atraccion_id'];
                if (!isset($out[$id])) $out[$id] = ['motivo_viaje' => [], 'compania' => [], 'experiencias' => [], 'dificultad' => null];
                $out[$id]['duracion_horas'] = $r['h'] !== null ? (float)$r['h'] : null;
            }
        } catch (PDOException $e) {
            error_log('perfilesAtracciones: ' . $e->getMessage());
        }
        return $out;
    }

    /** Traduce un nombre de categoría/subcategoría inglés al idioma de la sesión (pt/fr/de) */
    public function etiquetaCategoria(?string $texto): ?string
    {
        $lang = CatalogoI18n::idioma();
        if ($texto === null || in_array($lang, ['es', 'en'], true)) return $texto;

        if ($this->mapaCategorias === null) {
            $this->mapaCategorias = [];
            try {
                $en = $this->pdo->query("SELECT id, categoria_rangos, subcategoria_rangos FROM catalogo.atracciones_categorias_en")
                                ->fetchAll(PDO::FETCH_ASSOC);
                $xx = $this->pdo->query("SELECT id, categoria_rangos, subcategoria_rangos FROM catalogo.atracciones_categorias_{$lang}")
                                ->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
                $sufijo = fn($t) => strpos($t, ' - ') !== false ? trim(explode(' - ', $t, 2)[1]) : $t;
                foreach ($en as $fila) {
                    $trad = $xx[$fila['id']] ?? null;
                    if (!$trad) continue;
                    foreach (['categoria_rangos' => 'categoria', 'subcategoria_rangos' => 'subcategoria'] as $col => $campo) {
                        $a = json_decode($fila[$col] ?? '[]', true) ?: [];
                        $b = json_decode($trad[$col] ?? '[]', true) ?: [];
                        foreach ($a as $i => $r) {
                            if (!isset($r[$campo], $b[$i][$campo])) continue;
                            $this->mapaCategorias[$r[$campo]] = $b[$i][$campo];
                            $this->mapaCategorias[$sufijo($r[$campo])] = $sufijo($b[$i][$campo]);
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log('etiquetaCategoria: ' . $e->getMessage());
            }
        }
        return $this->mapaCategorias[$texto] ?? $texto;
    }

    // ── Pequeño helper para reutilizar en todo el modelo ──────────────────
    private function tablaAtracciones(): string
    {
        return $this->tbl_atracciones;
    }

    private function tablaCategoria(): string
    {
        return $this->tbl_atraccion_categorias;
    }

    // ──────────────────────────────────────────────────────────────────────
    
    /**
     * Obtener recomendaciones filtradas según preferencias del usuario
     */
    public function obtenerRecomendacionesFiltradas($preferencias) {
        try {
            $intereses = $preferencias->intereses ?? null;
            
            if (!$intereses) {
                return $this->obtenerMejoresRecomendaciones();
            }
            
            $condiciones = [];
            $params = [];
            
            if (!empty($intereses->actividades)) {
                $condActividades = [];
                foreach ($intereses->actividades as $i => $actividad) {
                    $param = ":actividad$i";
                    $condActividades[] = "r.razon_recomendacion->'parametros'->'interes' ? $param";
                    $params[$param] = $actividad;
                }
                if (!empty($condActividades)) {
                    $condiciones[] = "(" . implode(" OR ", $condActividades) . ")";
                }
            }
            
            if (!empty($intereses->experiencias)) {
                $condExperiencias = [];
                foreach ($intereses->experiencias as $i => $exp) {
                    $param = ":exp$i";
                    $condExperiencias[] = "r.razon_recomendacion->'parametros'->'experiencias' ? $param";
                    $params[$param] = $exp;
                }
                if (!empty($condExperiencias)) {
                    $condiciones[] = "(" . implode(" OR ", $condExperiencias) . ")";
                }
            }
            
            if (!empty($preferencias->presupuesto_max)) {
                $condiciones[] = "r.razon_recomendacion->'parametros'->'presupuesto' ? :presupuesto";
                $params[':presupuesto'] = $preferencias->presupuesto_max;
            }

            // ── tabla dinámica ─────────────────────────────────────────────
            $tabla = $this->tablaAtracciones();

            $sql = "SELECT 
                        r.id,
                        r.atraccion_id,
                        r.modelo_mr_id,
                        r.score_relevancia,
                        r.razon_recomendacion,
                        r.contexto_recomendacion,
                        a.nombre   AS atraccion_nombre,
                        a.descripcion AS atraccion_descripcion,
                        a.ciudad,
                        a.pais
                    FROM recomendaciones.recomendaciones r
                    JOIN {$tabla} a ON r.atraccion_id = a.id
                    WHERE r.modelo_mr_id = 1";
            
            if (!empty($condiciones)) {
                $sql .= " AND " . implode(" AND ", $condiciones);
            }
            
            $sql .= " ORDER BY r.score_relevancia DESC LIMIT 10";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            $recomendaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['razon_recomendacion']    = json_decode($row['razon_recomendacion'], true);
                $row['contexto_recomendacion'] = json_decode($row['contexto_recomendacion'], true);
                $recomendaciones[] = $row;
            }
            
            return $recomendaciones;
            
        } catch (PDOException $e) {
            error_log("Error en obtenerRecomendacionesFiltradas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener las mejores recomendaciones (cuando no hay filtros)
     */
    private function obtenerMejoresRecomendaciones() {
        try {
            $tabla = $this->tablaAtracciones();

            $sql = "SELECT 
                        r.id,
                        r.atraccion_id,
                        r.modelo_mr_id,
                        r.score_relevancia,
                        r.razon_recomendacion,
                        r.contexto_recomendacion,
                        a.nombre   AS atraccion_nombre,
                        a.descripcion AS atraccion_descripcion,
                        a.ciudad,
                        a.pais
                    FROM recomendaciones.recomendaciones r
                    JOIN {$tabla} a ON r.atraccion_id = a.id
                    WHERE r.modelo_mr_id = 1
                    ORDER BY r.score_relevancia DESC
                    LIMIT 10";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            $recomendaciones = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['razon_recomendacion']    = json_decode($row['razon_recomendacion'], true);
                $row['contexto_recomendacion'] = json_decode($row['contexto_recomendacion'], true);
                $recomendaciones[] = $row;
            }
            
            return $recomendaciones;
            
        } catch (PDOException $e) {
            error_log("Error en obtenerMejoresRecomendaciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener detalles de una atracción específica
     */
    public function obtenerDetalleAtraccion($atraccion_id) {
        try {
            $tabla = $this->tablaAtracciones();
            $sql   = "SELECT * FROM {$tabla} WHERE id = ?";
            $stmt  = $this->pdo->prepare($sql);
            $stmt->execute([$atraccion_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerDetalleAtraccion: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Guardar feedback del usuario
     */
    public function guardarFeedback($recomendacion_id, $tipo_feedback) {
        try {
            $sql = "UPDATE recomendaciones.recomendaciones 
                    SET feedback_usuario = jsonb_set(
                        COALESCE(feedback_usuario, '{}'::jsonb),
                        '{tipo}',
                        :tipo::jsonb
                    )
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id'   => $recomendacion_id,
                ':tipo' => json_encode($tipo_feedback)
            ]);
            
        } catch (PDOException $e) {
            error_log("Error en guardarFeedback: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca modelos activos que correspondan a CUALQUIERA de las categorías dadas.
     */
    public function buscarModelosPorCategorias(array $categorias, $preferencias) {
        if (empty($categorias)) {
            return $this->buscarModeloPorPreferencias($preferencias);
        }

        if (!$this->pdo) {
            try {
                $this->pdo = Database::Conectar();
            } catch (Exception $e) {
                error_log("buscarModelosPorCategorias - no se pudo reconectar: " . $e->getMessage());
                return $this->buscarModeloPorPreferencias($preferencias);
            }
        }

        try {
            $placeholders = implode(',', array_fill(0, count($categorias), '?'));

            $sql = "SELECT * FROM recomendaciones.modelos_mr
                    WHERE estado = 'activo'
                      AND (
                          categoria       IN ($placeholders)
                          OR motivo_viaje IN ($placeholders)
                      )
                    ORDER BY id DESC
                    LIMIT 10";

            $params = array_merge(array_values($categorias), array_values($categorias));

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);

            return !empty($resultados) ? $resultados : $this->buscarModeloPorPreferencias($preferencias);

        } catch (PDOException $e) {
            error_log("buscarModelosPorCategorias - fallback activo: " . $e->getMessage());
            return $this->buscarModeloPorPreferencias($preferencias);
        }
    }

    public function obtenerModelosActivos() {
        try {
            if (!$this->pdo) {
                $this->pdo = Database::Conectar();
            }
            $stmt = $this->pdo->query(
                "SELECT * FROM recomendaciones.modelos_mr WHERE estado = 'activo' ORDER BY id DESC LIMIT 10"
            );
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log('obtenerModelosActivos error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar modelo MR relacionado a intereses
     */
    public function buscarModeloPorPreferencias($preferencias) {
        try {
            $sql  = "SELECT * FROM recomendaciones.modelos_mr WHERE estado = 'activo' ORDER BY id DESC LIMIT 10";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
            return !empty($resultados) ? $resultados : [];
        } catch (PDOException $e) {
            error_log("Error en buscarModeloPorPreferencias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener recomendaciones directamente del configuracion_json del modelo
     */
    public function obtenerRecomendacionesDelModelo($usuario_id) {
        try {
            $sql_usuario = "SELECT 
                                motivo_viaje,
                                presupuesto_max,
                                duracion_dias,
                                intereses_json
                            FROM usuarios.preferencias 
                            WHERE usuario_id = ?";
            
            $stmt = $this->pdo->prepare($sql_usuario);
            $stmt->execute([$usuario_id]);
            $preferencias = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$preferencias) {
                return [];
            }
            
            $intereses = json_decode($preferencias['intereses_json'], true);
            
            $sql_modelo = "SELECT configuracion_json FROM recomendaciones.modelos_mr 
                           WHERE id = 1 AND estado = 'activo'";
            
            $stmt = $this->pdo->prepare($sql_modelo);
            $stmt->execute();
            $modelo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$modelo) {
                return [];
            }
            
            $config       = json_decode($modelo['configuracion_json'], true);
            $alternativas = $config['alternativas'] ?? [];
            $recomendaciones = [];
            
            foreach ($alternativas as $alt) {
                $score        = 0;
                $coincidencias = [];
                
                if (!empty($intereses['motivo_viaje']) && in_array($intereses['motivo_viaje'], $alt['motivo_viaje'] ?? [])) {
                    $score += 25;
                    $coincidencias[] = 'motivo_viaje';
                }
                
                if (!empty($intereses['compania']) && in_array($intereses['compania'], $alt['compania'] ?? [])) {
                    $score += 15;
                    $coincidencias[] = 'compania';
                }
                
                $rango_usuario = str_replace('-', '_', $intereses['rango_edad'] ?? '');
                if (!empty($rango_usuario) && in_array($rango_usuario, $alt['rango_edad'] ?? [])) {
                    $score += 15;
                    $coincidencias[] = 'rango_edad';
                }
                
                $actividades_coincidentes = array_intersect($intereses['actividades'] ?? [], $alt['actividades'] ?? []);
                $score += count($actividades_coincidentes) * 8;
                if (!empty($actividades_coincidentes)) {
                    $coincidencias[] = 'actividades';
                }
                
                $experiencias_coincidentes = array_intersect($intereses['experiencias'] ?? [], $alt['experiencias'] ?? []);
                $score += count($experiencias_coincidentes) * 5;
                if (!empty($experiencias_coincidentes)) {
                    $coincidencias[] = 'experiencias';
                }
                
                if (!empty($preferencias['presupuesto_max']) && in_array($preferencias['presupuesto_max'], $alt['presupuesto'] ?? [])) {
                    $score += 10;
                    $coincidencias[] = 'presupuesto';
                }
                
                if ($score >= 30) {
                    $recomendaciones[] = [
                        'id'                  => $alt['id'],
                        'nombre'              => $alt['nombre'],
                        'descripcion'         => $alt['descripcion'],
                        'categoria'           => $alt['categoria'],
                        'subcategoria'        => $alt['subcategoria'],
                        'dificultad'          => $alt['dificultad'],
                        'duracion_horas'      => $alt['duracion_horas'],
                        'puntuacion'          => $alt['puntuacion'],
                        'ciudad'              => $config['metadata']['ciudad'] ?? 'La Paz',
                        'pais'                => $config['metadata']['pais'] ?? 'Bolivia',
                        'score'               => min(100, $score),
                        'coincidencias'       => $coincidencias,
                        'actividades_match'   => array_values($actividades_coincidentes),
                        'experiencias_match'  => array_values($experiencias_coincidentes)
                    ];
                }
            }
            
            usort($recomendaciones, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            return array_slice($recomendaciones, 0, 10);
            
        } catch (PDOException $e) {
            error_log("Error en obtenerRecomendacionesDelModelo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la categoría y subcategoría de una atracción según su ID
     * usando la tabla de categorías correcta según el idioma.
     */
    public function obtenerCategoriaYSubcategoria($id_atraccion)
    {
        try {
            $tabla = $this->tablaCategoria();

            $sql  = "SELECT categoria_rangos, subcategoria_rangos FROM {$tabla}";
            $stmt = $this->pdo->query($sql);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $categoria   = 'Otros';
            $subcategoria = 'General';

            foreach ($filas as $fila) {
                $categorias   = json_decode($fila['categoria_rangos'], true);
                $subcategorias = json_decode($fila['subcategoria_rangos'], true);

                $categoria_encontrada = false;

                if (is_array($categorias)) {
                    foreach ($categorias as $cat) {
                        if ($id_atraccion >= $cat['rango_inicio'] && $id_atraccion <= $cat['rango_fin']) {
                            $categoria = $cat['categoria'];
                            $categoria_encontrada = true;
                            break;
                        }
                    }
                }

                if ($categoria_encontrada && is_array($subcategorias)) {
                    foreach ($subcategorias as $sub) {
                        if ($id_atraccion >= $sub['rango_inicio'] && $id_atraccion <= $sub['rango_fin']) {
                            $nombre_sub = $sub['subcategoria'];
                            if (strpos($nombre_sub, ' - ') !== false) {
                                $partes     = explode(' - ', $nombre_sub, 2);
                                $nombre_sub = trim($partes[1]);
                            }
                            $subcategoria = $nombre_sub;
                            break;
                        }
                    }
                }

                if ($categoria_encontrada) {
                    break;
                }
            }

            return [
                'categoria'    => $categoria,
                'subcategoria' => $subcategoria
            ];

        } catch (PDOException $e) {
            error_log("Error en obtenerCategoriaYSubcategoria: " . $e->getMessage());
            return [
                'categoria'    => 'Otros',
                'subcategoria' => 'General'
            ];
        }
    }

    // ── Mapas de categoría ─────────────────────────────────────────────────

    private $CATEGORIA_ID_MAP = [
        1 => 'trekking',
        2 => 'montanismo',
        3 => 'cultura',
        4 => 'downhill',
        5 => 'vida_nocturna',
        6 => 'gastronomia',
        7 => 'iconos_urbanos',
    ];

    private $CATEGORIA_LABEL_MAP = [
        1 => 'Trekking',
        2 => 'Montañismo',
        3 => 'Cultura',
        4 => 'Downhill',
        5 => 'Vida Nocturna',
        6 => 'Gastronomía',
        7 => 'Iconos Urbanos',
    ];

    // ── Cuando el idioma es inglés los labels también se traducen ──────────

    private $CATEGORIA_LABEL_MAP_EN = [
        1 => 'Trekking',
        2 => 'Mountaineering',
        3 => 'Culture',
        4 => 'Downhill',
        5 => 'Nightlife',
        6 => 'Gastronomy',
        7 => 'Urban Icons',
    ];

    private function getCategoriaLabel(int $catId): string
    {
        $lang = $_SESSION['lang'] ?? 'es';

        // Etiqueta desde los archivos de idioma (es/en/pt/fr/de); los mapas fijos quedan de respaldo
        $clave = $this->CATEGORIA_ID_MAP[$catId] ?? null;
        if ($clave && class_exists('Lang')) {
            $txt = Lang::get('categorias.' . $clave);
            if ($txt !== 'categorias.' . $clave) return $txt;
        }

        if ($lang !== 'es') {
            return $this->CATEGORIA_LABEL_MAP_EN[$catId] ?? 'Others';
        }

        return $this->CATEGORIA_LABEL_MAP[$catId] ?? 'Otros';
    }

    // ──────────────────────────────────────────────────────────────────────

    private function slugify(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $from  = ['á','é','í','ó','ú','ä','ë','ï','ö','ü','ñ','ç','à','è','ì','ò','ù'];
        $to    = ['a','e','i','o','u','a','e','i','o','u','n','c','a','e','i','o','u'];
        $texto = str_replace($from, $to, $texto);
        $texto = preg_replace('/\(.*?\)/', '', $texto);
        $texto = preg_replace('/[^a-z0-9]+/', '_', trim($texto));
        return trim($texto, '_');
    }

    /**
     * Obtener TODAS las atracciones del catálogo con categoría y subcategoría.
     * Usa la tabla correcta según el idioma de sesión.
     */
    public function obtenerTodasAtracciones() {
        try {
            $tabla = $this->tablaAtracciones();

            $sql = "SELECT 
                        a.id,
                        a.nombre,
                        a.descripcion,
                        a.ciudad,
                        a.pais,
                        a.puntuacion_promedio,
                        a.horario_apertura,
                        a.horario_cierre,
                        a.dias_operacion,
                        a.ubicacion,
                        a.atraccion_categoria_id,
                        a.precio_entrada,
                        a.moneda,
                        a.tiempo_estimado
                    FROM {$tabla} a
                    WHERE a.estado = 'activo'
                    ORDER BY a.id ASC";

            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Detectar palabras clave de calendario según idioma
            $lang = $_SESSION['lang'] ?? 'es';
            $keyword_calendar = ($lang !== 'es') ? 'Cultural Calendar' : 'Calendario Cultural';

            foreach ($rows as &$row) {

                $catId = (int)$row['atraccion_categoria_id'];

                $row['mes_inicio']    = null;
                $row['mes_fin']       = null;
                $row['meses_evento']  = [];

                $catData = $this->obtenerCategoriaYSubcategoria($row['id']);

                $categoriaTexto    = $catData['categoria']    ?? '';
                $subcategoriaTexto = $catData['subcategoria'] ?? '';

                // ── 1. Calendario Cultural ─────────────────────────────────
                if (!empty($categoriaTexto)
                    && stripos($categoriaTexto, $keyword_calendar) !== false) {

                    $row['categoria']       = 'festividades';
                    $row['categoria_label'] = $this->etiquetaCategoria($categoriaTexto);

                    $textoBusqueda = $categoriaTexto . ' ' . $subcategoriaTexto;

                    $MESES = [
                        'ene'=>1,'jan'=>1,
                        'feb'=>2,
                        'mar'=>3,
                        'abr'=>4,'apr'=>4,
                        'may'=>5,
                        'jun'=>6,
                        'jul'=>7,
                        'ago'=>8,'aug'=>8,
                        'sep'=>9,
                        'oct'=>10,
                        'nov'=>11,
                        'dic'=>12,'dec'=>12
                    ];

                    if (preg_match('/(\w{3})\s*[-–]\s*(\w{3})/i', $textoBusqueda, $m)) {
                        $inicio = $MESES[strtolower($m[1])] ?? null;
                        $fin    = $MESES[strtolower($m[2])] ?? null;

                        if ($inicio && $fin) {
                            $row['mes_inicio'] = $inicio;
                            $row['mes_fin']    = $fin;

                            if ($inicio <= $fin) {
                                for ($i = $inicio; $i <= $fin; $i++) {
                                    $row['meses_evento'][] = $i;
                                }
                            } else {
                                for ($i = $inicio; $i <= 12; $i++) {
                                    $row['meses_evento'][] = $i;
                                }
                                for ($i = 1; $i <= $fin; $i++) {
                                    $row['meses_evento'][] = $i;
                                }
                            }
                        }

                    } elseif (preg_match('/\b(\w{3})\b/i', $textoBusqueda, $m)) {
                        $mes = $MESES[strtolower($m[1])] ?? null;
                        if ($mes) {
                            $row['mes_inicio'] = $mes;
                            $row['mes_fin']    = $mes;
                            $row['meses_evento'][] = $mes;
                        }
                    }

                // ── 2. Categorías normales ─────────────────────────────────
                } elseif ($catId > 0 && isset($this->CATEGORIA_ID_MAP[$catId])) {

                    $row['categoria']       = $this->CATEGORIA_ID_MAP[$catId];
                    $row['categoria_label'] = $this->getCategoriaLabel($catId);

                // ── 3. Fallback ────────────────────────────────────────────
                } else {

                    $row['categoria']       = 'otros';
                    $row['categoria_label'] = ($lang !== 'es') ? 'Others' : 'Otros';
                }

                // ── Tiempo estimado ────────────────────────────────────────
                if (!empty($row['tiempo_estimado']) && is_string($row['tiempo_estimado'])) {
                    $decoded = json_decode($row['tiempo_estimado'], true);
                    $row['tiempo_estimado'] = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                        ? $decoded
                        : ['visita' => '30 minutos', 'traslado' => '0 minutos'];
                } else {
                    $row['tiempo_estimado'] = ['visita' => '30 minutos', 'traslado' => '0 minutos'];
                }

                // ── Ubicación ──────────────────────────────────────────────
                if (!empty($row['ubicacion']) && is_string($row['ubicacion'])) {
                    $row['ubicacion'] = json_decode($row['ubicacion'], true) ?: [];
                }
            }

            unset($row);

            // Perfil de cada atracción (motivo, compañía, experiencias, dificultad, duración):
            // sale de las alternativas de los modelos MR y del contexto de recomendaciones.
            $perfiles = $this->perfilesAtracciones();
            foreach ($rows as &$row) {
                $row['perfil'] = $perfiles[(int)$row['id']] ?? null;
            }
            unset($row);

            return $rows;

        } catch (PDOException $e) {
            error_log('obtenerTodasAtracciones error: ' . $e->getMessage());
            return [];
        }
    }
}