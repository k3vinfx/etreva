<?php
//Se incluye el modelo donde conectará el controlador.
require_once 'model/login.php';
require_once 'model/recomendaciones.php';
require_once 'model/restriccionEdad.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class RecomendacionesController{

    private $loginModel;
    private $recomendacionesModel;

    //Creación del modelo
    public function __CONSTRUCT(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->loginModel = new login();
        $this->recomendacionesModel = new Recomendaciones();
    }

    /**
     * Página principal de recomendaciones
     */
    public function Index() {
        $usuarioLogueado = AuthMiddleware::resguardar();
        
        if (!$usuarioLogueado) {
            header("Location: index.php?c=inicio&a=Index");
            exit();
        }
        
        require_once 'view/cliente/recomendaciones.php';
    }

    /**
     * API: Obtener datos del perfil del usuario
     */
    public function obtenerPerfil() {
        header('Content-Type: application/json');
        
        $usuarioLogueado = AuthMiddleware::resguardar();
        
        if (!$usuarioLogueado) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $id_usuario = is_array($usuarioLogueado) ? ($usuarioLogueado['id'] ?? null) : ($usuarioLogueado->id ?? null);
        
        if (!$id_usuario) {
            echo json_encode(['success' => false, 'message' => 'ID no encontrado']);
            return;
        }
        
        // Obtener datos del usuario
        $usuario = $this->loginModel->ObtenerUsuario($id_usuario);
        
        // Obtener preferencias de la tabla preferencias
        $preferencias = $this->loginModel->obtenerPreferencias($id_usuario);
        
        // Decodificar intereses_json si existe
        if ($preferencias && !empty($preferencias->intereses_json)) {
            if (is_string($preferencias->intereses_json)) {
                $preferencias->intereses = json_decode($preferencias->intereses_json);
            } else {
                $preferencias->intereses = $preferencias->intereses_json;
            }
        }
        
        echo json_encode([
            'success' => true,
            'usuario' => [
                'id' => $usuario->id ?? null,
                'nombre' => $usuario->nombre ?? null,
                'apellido' => $usuario->apellido ?? null,
                'email' => $usuario->email ?? null
            ],
            'preferencias' => $preferencias
        ]);
    }

    /**
     * Obtener recomendaciones para el usuario
     */
    public function obtenerRecomendaciones() {
        header('Content-Type: application/json');
        
        $usuarioLogueado = AuthMiddleware::resguardar();
        
        if (!$usuarioLogueado) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $id_usuario = is_array($usuarioLogueado) ? ($usuarioLogueado['id'] ?? null) : ($usuarioLogueado->id ?? null);
        
        if (!$id_usuario) {
            echo json_encode(['success' => false, 'message' => 'ID no encontrado']);
            return;
        }
        
        // Obtener recomendaciones del modelo
        $recomendaciones = $this->recomendacionesModel->obtenerRecomendacionesDelModelo($id_usuario);
        $recomendaciones = RestriccionEdad::filtrar((array)$recomendaciones, $id_usuario, 'output');
        
        echo json_encode([
            'success' => true,
            'recomendaciones' => $recomendaciones
        ]);
    }

    /**
     * API: Obtener detalles de una atracción específica
     */
    public function detalleAtraccion() {
        header('Content-Type: application/json');
        
        $usuarioLogueado = AuthMiddleware::resguardar();
        
        if (!$usuarioLogueado) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $atraccion_id = $_GET['id'] ?? null;
        
        if (!$atraccion_id) {
            echo json_encode(['success' => false, 'message' => 'ID de atracción no proporcionado']);
            return;
        }
        
        if (!RestriccionEdad::puedeVer(RestriccionEdad::idDe($usuarioLogueado), $atraccion_id)) {
            echo json_encode(['success' => false, 'restringida' => true, 'message' => Lang::get('legal.solo_mayores')]);
            return;
        }
        $detalle = $this->recomendacionesModel->obtenerDetalleAtraccion($atraccion_id);
        
        if ($detalle) {
            echo json_encode([
                'success' => true,
                'atraccion' => $detalle
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Atracción no encontrada'
            ]);
        }
    }

    /**
     * API: Guardar feedback de una recomendación
     */
    public function guardarFeedback() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $usuarioLogueado = AuthMiddleware::resguardar();
        
        if (!$usuarioLogueado) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['recomendacion_id']) || !isset($input['tipo_feedback'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }
        
        $resultado = $this->recomendacionesModel->guardarFeedback(
            $input['recomendacion_id'],
            $input['tipo_feedback']
        );
        
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Feedback guardado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar feedback']);
        }
    }

/**
 * Obtener recomendaciones basadas en modelos MR y preferencias detalladas del usuario
 */


public function ObtenerModeloMR() {
    header('Content-Type: application/json');
    ob_start();

    register_shutdown_function(function () {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Error fatal: ' . $err['message'],
                'linea'   => $err['file'] . ':' . $err['line']
            ]);
        }
    });

    try {

        // ── 1. AUTENTICACIÓN ─────────────────────────────────────────────────────
        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) { ob_clean(); echo json_encode(['success'=>false,'message'=>'No autorizado']); return; }

        $id_usuario = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        if (!$id_usuario) { ob_clean(); echo json_encode(['success'=>false,'message'=>'ID inválido']); return; }

        // ── 2. PREFERENCIAS DEL USUARIO ──────────────────────────────────────────
        $loginModel = new login();
        $pref       = $loginModel->obtenerPreferencias($id_usuario);

        if (!$pref) {
            ob_clean();
            echo json_encode(['success'=>false,'message'=>'No hay preferencias registradas']);
            return;
        }

        $intereses = [];
        if (!empty($pref->intereses_json) && is_string($pref->intereses_json)) {
            $intereses = json_decode($pref->intereses_json, true) ?: [];
        }

        // ── 3. EXTRAER CAMPOS ────────────────────────────────────────────────────
        $motivo_viaje    = $pref->motivo_viaje   ?? $intereses['motivo_viaje']   ?? null;
        $presupuesto_max = $pref->presupuesto_max ?? null;
        $duracion_dias   = isset($pref->duracion_dias) ? intval($pref->duracion_dias) : null;
        $compania        = $intereses['compania']    ?? null;
        $rango_edad      = $intereses['rango_edad']  ?? null;
        $actividades     = $intereses['actividades'] ?? [];
        $experiencias    = $intereses['experiencias'] ?? [];

        // Vida nocturna
        $vn             = $intereses['preferencias_vida_nocturna'] ?? [];
        $vn_ambiente    = $vn['ambiente']          ?? [];
        $vn_gen_fav     = $vn['generos_favoritos'] ?? [];
        $vn_gen_excluir = $vn['generos_excluidos'] ?? [];

        // Gastronomía
        $gastro         = $intereses['preferencias_gastronomicas'] ?? [];
        $gastro_fav     = $gastro['tipos_favoritos'] ?? [];
        $gastro_excluir = $gastro['tipos_excluidos'] ?? [];
        $gastro_precio  = $gastro['nivel_precio']    ?? null;

        // ── 4. MES ACTUAL → TEMPORADA ────────────────────────────────────────────
        // Bolivia: Dic-Mar = verano/lluvias | Jun-Ago = invierno/seco
        // Febrero = verano
        $mes_actual = (int) date('n'); // 1-12

        $temporada_actual = '';
        if (in_array($mes_actual, [12, 1, 2, 3]))       $temporada_actual = 'verano';
        elseif (in_array($mes_actual, [4, 5]))           $temporada_actual = 'otono';
        elseif (in_array($mes_actual, [6, 7, 8]))        $temporada_actual = 'invierno';
        else                                              $temporada_actual = 'primavera';

        // Meses de nombre para comparar etiquetas textuales
        $nombres_meses = [
            1=>'enero', 2=>'febrero', 3=>'marzo', 4=>'abril', 5=>'mayo',
            6=>'junio', 7=>'julio', 8=>'agosto', 9=>'septiembre',
            10=>'octubre', 11=>'noviembre', 12=>'diciembre'
        ];
        $mes_nombre = $nombres_meses[$mes_actual]; // "febrero"

        // ── 5. OBTENER MODELOS ACTIVOS ────────────────────────────────────────────
        $modelos = $this->recomendacionesModel->obtenerModelosActivos();

        if (empty($modelos)) {
            ob_clean();
            echo json_encode(['success'=>false,'message'=>'No hay modelos activos']);
            return;
        }

        // ── 6. PROCESAR CANDIDATOS ────────────────────────────────────────────────
        $candidatos     = [];
        $ids_procesados = [];

        foreach ($modelos as $modelo) {
            $metricas = json_decode($modelo->metricas_desempeno ?? '', true) ?: [];
            $config   = json_decode($modelo->configuracion_json  ?? '', true) ?: [];

            $dataset  = $metricas['dataset_entrenamiento']
                     ?? $metricas['dataset_base']
                     ?? $config['dataset_base']
                     ?? [];

            $mapeo = $metricas['mapeo_input'] ?? $config['mapeo_input'] ?? [];

            if (empty($dataset) || empty($mapeo)) continue;

            foreach ($dataset as $dato) {
                if (!isset($dato['input'], $dato['output'])) continue;

                $input     = $dato['input'];
                $output_id = is_array($dato['output']) ? ($dato['output'][0] ?? null) : $dato['output'];

                if (!$output_id || in_array($output_id, $ids_procesados)) continue;

                // ── Categoría y subcategoría ─────────────────────────────────────
                $cat_info   = $this->recomendacionesModel->obtenerCategoriaYSubcategoria($output_id);
                $categoria  = strtolower(str_replace(' ','_', $cat_info['categoria']  ?? 'otros'));
                $subcateg   = strtolower($cat_info['subcategoria'] ?? 'general');

                $categoria_original = $cat_info['categoria']  ?? 'Otros';
                $subcategoria_original = $cat_info['subcategoria'] ?? 'General';

                // ── FILTRO TEMPORAL: excluir si solo aplica a otro mes/temporada ──
                // Solo filtramos si el campo del modelo o subcategoría menciona explícitamente
                // meses que NO son el actual Y no menciona el actual tampoco.
                // Ejemplo: si subcateg contiene "diciembre" pero no "febrero" → skip
               // ── FILTRO TEMPORAL MEJORADO: usar categoría original y subcategoría ──
                $texto_busqueda = $categoria_original . ' ' . $subcategoria_original;
                $texto_busqueda = strtolower($texto_busqueda);

                    // Abreviaturas de meses en español (ENE, FEB, MAR, etc.)
                $abrev_meses = [
                    1=>'ene', 2=>'feb', 3=>'mar', 4=>'abr', 5=>'may',
                    6=>'jun', 7=>'jul', 8=>'ago', 9=>'sep', 10=>'oct',
                    11=>'nov', 12=>'dic'
                ];
                $abrev_actual = $abrev_meses[$mes_actual]; // "mar" si estamos en marzo

                $menciona_mes_excluido = false;
                $menciona_mes_actual   = false;

                foreach ($nombres_meses as $m) {
                    if (strpos($texto_busqueda, $m) !== false) {
                        if ($m === $mes_nombre) $menciona_mes_actual   = true;
                        else                    $menciona_mes_excluido = true;
                    }
                }

                // Comprobar abreviaturas (ENE, FEB, MAR…)
                foreach ($abrev_meses as $num => $abrev) {
                    if (strpos($texto_busqueda, $abrev) !== false) {
                        if ($num === $mes_actual) $menciona_mes_actual   = true;
                        else                      $menciona_mes_excluido = true;
                    }
                }

                // Si menciona explícitamente otro mes y NO el actual → saltar
                if ($menciona_mes_excluido && !$menciona_mes_actual) continue;
                // ── FILTROS DE EXCLUSIÓN DUROS ────────────────────────────────────
                if (!empty($gastro_excluir) && $categoria === 'gastronomia') {
                    $excluir = false;
                    foreach ($gastro_excluir as $te) {
                        if (strpos($subcateg, strtolower($te)) !== false) { $excluir = true; break; }
                    }
                    if ($excluir) continue;
                }

                if (!empty($vn_gen_excluir) && $categoria === 'vida_nocturna') {
                    $excluir = false;
                    foreach ($vn_gen_excluir as $ge) {
                        if (strpos($subcateg, strtolower($ge)) !== false) { $excluir = true; break; }
                    }
                    if ($excluir) continue;
                }

                // ── SISTEMA DE PUNTUACIÓN ────────────────────────────────────────
                $pts     = 0;
                $pts_max = 0;
                $detalle = [];

                // Helper: coincidencia en mapeo binario
                $coincide = function ($campo, $valor) use ($mapeo, $input) {
                    if (!isset($mapeo[$campo]['valores'], $mapeo[$campo]['indices'])) return false;
                    $pos = array_search($valor, $mapeo[$campo]['valores']);
                    if ($pos === false) return false;
                    $idx = $mapeo[$campo]['indices'][$pos] ?? null;
                    return ($idx !== null && isset($input[$idx]) && $input[$idx] == 1);
                };

                // 1. Motivo de viaje (25 pts)
                if ($motivo_viaje && isset($mapeo['motivo_viaje'])) {
                    $pts_max += 25;
                    if ($coincide('motivo_viaje', $motivo_viaje)) {
                        $pts += 25; $detalle['motivo_viaje'] = $motivo_viaje;
                    }
                }

                // 2. Compañía (20 pts)
                if ($compania && isset($mapeo['compania'])) {
                    $pts_max += 20;
                    if ($coincide('compania', $compania)) {
                        $pts += 20; $detalle['compania'] = $compania;
                    }
                }

                // 3. Rango de edad (20 pts)
                if ($rango_edad && isset($mapeo['rango_edad'])) {
                    $pts_max += 20;
                    if ($coincide('rango_edad', str_replace('-','_', $rango_edad))) {
                        $pts += 20; $detalle['rango_edad'] = $rango_edad;
                    }
                }

                // 4. Presupuesto (20 pts)
                if ($presupuesto_max && isset($mapeo['presupuesto'])) {
                    $pts_max += 20;
                    if ($coincide('presupuesto', $presupuesto_max)) {
                        $pts += 20; $detalle['presupuesto'] = $presupuesto_max;
                    }
                }

                // 5. Duración (15 pts)
                if ($duracion_dias && isset($mapeo['duracion'])) {
                    $pts_max += 15;
                    foreach ($mapeo['duracion']['valores'] ?? [] as $i => $rango_str) {
                        $p = explode('-', $rango_str);
                        if (count($p) === 2 && $duracion_dias >= intval($p[0]) && $duracion_dias <= intval($p[1])) {
                            $idx = $mapeo['duracion']['indices'][$i] ?? null;
                            if ($idx !== null && isset($input[$idx]) && $input[$idx] == 1) {
                                $pts += 15; $detalle['duracion'] = $rango_str;
                            }
                            break;
                        }
                    }
                }

                // 6. Actividades (10 pts c/u)
                if (!empty($actividades) && isset($mapeo['actividades'])) {
                    $act_match = [];
                    foreach ($actividades as $act) {
                        $pts_max += 10;
                        if ($coincide('actividades', $act)) { $pts += 10; $act_match[] = $act; }
                    }
                    if (!empty($act_match)) $detalle['actividades'] = $act_match;
                }

                // 7. Experiencias (8 pts c/u)
                if (!empty($experiencias) && isset($mapeo['experiencias'])) {
                    $exp_match = [];
                    foreach ($experiencias as $exp) {
                        $pts_max += 8;
                        if ($coincide('experiencias', $exp)) { $pts += 8; $exp_match[] = $exp; }
                    }
                    if (!empty($exp_match)) $detalle['experiencias'] = $exp_match;
                }

// 8. Temporada actual (10 pts) - bonus si la atracción menciona la temporada o mes actual
                if (strpos($texto_busqueda, $temporada_actual) !== false
                 || strpos($texto_busqueda, $mes_nombre) !== false) {
                    $pts     += 10;
                    $pts_max += 10;
                    $detalle['temporada'] = $temporada_actual;
                }

                // ── PUNTUACIÓN ESPECÍFICA VIDA NOCTURNA ──────────────────────────

                // ── PUNTUACIÓN ESPECÍFICA VIDA NOCTURNA ──────────────────────────
                $es_vida_nocturna = (strpos($categoria, 'vida_nocturna') !== false 
                                   || strpos($categoria, 'rock') !== false 
                                   || strpos($categoria, 'discoteca') !== false
                                   || strpos($categoria, 'cocteleria') !== false
                                   || strpos($categoria, 'cerveceria') !== false);
                $ambiente_ok = false;


            if ($categoria === 'vida_nocturna') {

            // Obtener nombre de la atracción para comparar
            $det_vn = $this->recomendacionesModel->obtenerDetalleAtraccion($output_id);
            $nombre_vn = strtolower($det_vn['nombre'] ?? '');

            // Mapa de alias MÁS AMPLIO - incluye nombres de lugares típicos
            $mapa_alias_ambiente = [
                'discoteca'     => ['discoteca','disco','club nocturno','night club','boliche','dancing'],
                'cocteleria'    => ['cocteleria','cocteles','coctelería','lounge','bar de cocteles','cocktail','mix'],
                'cerveceria'    => ['cerveceria','cervecería','cerveza','craft beer','artesanal','brew'],
                'karaoke'       => ['karaoke','kara'],
                'jazz_cultural' => ['jazz','blues','cultural','teatro','piano bar','live music','música en vivo'],
                'bar_rock'      => ['rock','metal','punk','alternativo','hard rock','grunge','heavy'],
            ];

            if (!empty($vn_ambiente)) {
                $pts_max += 15;
                foreach ($vn_ambiente as $amb_usuario) {
                    $alias = $mapa_alias_ambiente[strtolower($amb_usuario)] ?? [strtolower($amb_usuario)];
                    foreach ($alias as $a) {
                        // Buscar en subcategoría Y en nombre del lugar
                        if (strpos($subcateg, $a) !== false || strpos($nombre_vn, $a) !== false) {
                            $pts += 15;
                            $detalle['ambiente'] = $amb_usuario;
                            $ambiente_ok = true;
                            break 2;
                        }
                    }
                }
                // Si no coincidió el ambiente pero el usuario NO especificó géneros a excluir,
                // no penalizar con 0 - dar crédito parcial si es vida nocturna en general
                if (!$ambiente_ok && empty($vn_gen_excluir)) {
                    // Coincidencia parcial: es vida nocturna pero ambiente no específico
                    $pts += 5; // pequeño crédito por ser del tipo correcto
                }
            } else {
                // Usuario no especificó ambiente → no penalizar, marcar como ok
                $ambiente_ok = true;
            }

            // Géneros musicales favoritos (8 pts c/u)
            // Buscar en subcategoría Y nombre
            foreach ($vn_gen_fav as $gen) {
                $pts_max += 8;
                $gen_lower = strtolower($gen);
                if (strpos($subcateg, $gen_lower) !== false || strpos($nombre_vn, $gen_lower) !== false) {
                    $pts += 8;
                    $detalle['genero_musical'][] = $gen;
                }
            }
        }

                // ── PUNTUACIÓN ESPECÍFICA GASTRONOMÍA ────────────────────────────

      
                if ($categoria === 'gastronomia') {
                    // Comparar contra subcategoría Y contra nombre de la atracción
                    // así "Restaurante Vegetariano El Huerto" aparece aunque subcateg sea "restaurante"
                    $det_temp = $this->recomendacionesModel->obtenerDetalleAtraccion($output_id);
                    $nombre_atraccion = strtolower($det_temp['nombre'] ?? '');

                    foreach ($gastro_fav as $tipo) {
                        $tipo_lower = strtolower($tipo);
                        $pts_max += 10;

                        // Alias para cada tipo de comida
                        $alias_comida = [
                            'vegetariana' => ['vegetariana','vegetariano','veggie','sin carne','plant'],
                            'vegana'      => ['vegana','vegano','vegan','plant-based'],
                            'italiana'    => ['italiana','italiano','pasta','pizza','trattoria'],
                            'japonesa'    => ['japonesa','japonés','sushi','ramen','nikkei'],
                            'parrilla'    => ['parrilla','asado','grill','carnes','churrasco'],
                            'criolla'     => ['criolla','criollo','tradicional','boliviana','boliviano'],
                            'mariscos'    => ['mariscos','ceviche','pescado','seafood'],
                            'mexicana'    => ['mexicana','mexicano','tacos','burrito'],
                            'fastfood'    => ['burger','hamburgesa','fast food','pollo','kfc','mc'],
                            'cafe'        => ['cafe','café','cafetería','coffee','repostería'],
                            'china'       => ['china','chino','chifa','oriental'],
                        ];

                        $palabras_buscar = $alias_comida[$tipo_lower] ?? [$tipo_lower];
                        $encontrado = false;
                        foreach ($palabras_buscar as $palabra) {
                            if (strpos($subcateg, $palabra) !== false || strpos($nombre_atraccion, $palabra) !== false) {
                                $encontrado = true;
                                break;
                            }
                        }

                        if ($encontrado) {
                            $pts += 10;
                            $detalle['tipo_comida'][] = $tipo;
                        }
                    }

                    // Precio gastronómico (8 pts)
                    if ($gastro_precio) {
                        $pts_max += 8;
                        if (strpos($subcateg, strtolower($gastro_precio)) !== false
                        || $coincide('nivel_precio', $gastro_precio)) {
                            $pts += 8;
                            $detalle['nivel_precio'] = $gastro_precio;
                        }
                    }
                }

                // ── PORCENTAJE BASE ───────────────────────────────────────────────
                $pct_base = $pts_max > 0 ? round(($pts / $pts_max) * 100, 2) : 0;

                // ── AJUSTE FINAL ──────────────────────────────────────────────────
                $cat_en_motivo      = ($categoria === strtolower($motivo_viaje ?? ''));
                $cat_en_actividades = in_array($categoria, array_map('strtolower', $actividades));
                $tiene_motivo       = isset($detalle['motivo_viaje']);

                // Vida nocturna: penalizar solo si el usuario SÍ especificó ambiente y no coincide
                $penalizar_ambiente = (!$ambiente_ok && !empty($vn_ambiente) && $categoria === 'vida_nocturna');

                if ($penalizar_ambiente) {
                    $pct_final = min(40, $pct_base);

                } elseif ($cat_en_motivo && $tiene_motivo) {
                    $pct_final = max(82, $pct_base);

                } elseif ($cat_en_actividades && $categoria === 'vida_nocturna' && $ambiente_ok) {
                    $pct_final = max(76, $pct_base);

                } elseif ($cat_en_actividades) {
                    $pct_final = max(66, $pct_base);

                } elseif ($tiene_motivo) {
                    $pct_final = max(70, $pct_base);

                } else {
                    $pct_final = min(58, $pct_base);
                }

                $pct_final = (int) round($pct_final);

                $candidatos[] = [
                    'atraccion_id'     => $output_id,
                    'input'            => $input,   // ← necesario para brain.js
                    'modelo_id'        => $modelo->id,
                    'modelo_nombre'    => $modelo->nombre ?? 'Modelo ' . $modelo->id,
                    'categoria_raw'    => $cat_info['categoria'],
                    'subcategoria_raw' => $cat_info['subcategoria'],
                    'porcentaje_match' => $pct_final,
                    'coincidencias'    => $detalle,
                    '_debug' => [
                        'pts'          => $pts,
                        'pts_max'      => $pts_max,
                        'pct_base'     => $pct_base,
                        'cat'          => $categoria,
                        'sub'          => $subcateg,
                        'ambiente_ok'  => $ambiente_ok,
                        'temporada'    => $temporada_actual,
                        'mes'          => $mes_nombre,
                    ],
                ];

                $ids_procesados[] = $output_id;
            }
        }

        // Menores de 18: sin bares ni discotecas (Ley 259). Se filtra antes de elegir las 25.
        $candidatos = RestriccionEdad::filtrar($candidatos, $id_usuario, 'atraccion_id');

        if (empty($candidatos)) {
            ob_clean();
            echo json_encode(['success'=>false,'message'=>'No se encontraron recomendaciones']);
            return;
        }

        // ── 7. ORDENAR: primero por porcentaje, luego mezclar categorías ──────────
        usort($candidatos, fn($a,$b) => $b['porcentaje_match'] <=> $a['porcentaje_match']);

        // Intercalar categorías para que no salgan todos del mismo tipo seguidos
        $por_categoria = [];
        foreach ($candidatos as $c) {
            $por_categoria[$c['categoria_raw']][] = $c;
        }

        $intercalados = [];
        $max_por_grupo = 25;
        $ronda = 0;
        while (count($intercalados) < $max_por_grupo) {
            $agrego = false;
            foreach ($por_categoria as $cat => &$grupo) {
                if (isset($grupo[$ronda])) {
                    $intercalados[] = $grupo[$ronda];
                    $agrego = true;
                    if (count($intercalados) >= $max_por_grupo) break;
                }
            }
            unset($grupo);
            if (!$agrego) break;
            $ronda++;
        }

        // Rellenar si quedaron menos de 25 con los restantes ordenados
        if (count($intercalados) < $max_por_grupo) {
            foreach ($candidatos as $c) {
                if (!in_array($c['atraccion_id'], array_column($intercalados,'atraccion_id'))) {
                    $intercalados[] = $c;
                    if (count($intercalados) >= $max_por_grupo) break;
                }
            }
        }

        // ── 8. ENRIQUECER CON CATÁLOGO ───────────────────────────────────────────
        $resultado_final = [];
        foreach ($intercalados as $c) {
            $det = $this->recomendacionesModel->obtenerDetalleAtraccion($c['atraccion_id']);

            $resultado_final[] = [
                'output'           => $c['atraccion_id'],
                'input'            => $c['input'],   // ← brain.js lo necesita
                'modelo_id'        => $c['modelo_id'],
                'modelo_nombre'    => $c['modelo_nombre'],
                'nombre'           => $det['nombre']              ?? 'Atracción #' . $c['atraccion_id'],
                'descripcion'      => $det['descripcion']         ?? 'Sin descripción',
                'ciudad'           => $det['ciudad']              ?? 'La Paz',
                'pais'             => $det['pais']                ?? 'Bolivia',
                'puntuacion'       => $det['puntuacion_promedio'] ?? 4.5,
              //  'imagen'           => $det['imagen_url']          ?? null,
                'categoria'        => $this->recomendacionesModel->etiquetaCategoria($c['categoria_raw']),
                'subcategoria'     => $this->recomendacionesModel->etiquetaCategoria($c['subcategoria_raw']),
                'porcentaje_match' => $c['porcentaje_match'],
                'coincidencias'    => $c['coincidencias'],

                'horario_apertura' => $det['horario_apertura']    ?? null,
                'horario_cierre'   => $det['horario_cierre']      ?? null,
                'dias_operacion'   => isset($det['dias_operacion'])
                                    ? (is_string($det['dias_operacion'])
                                        ? json_decode($det['dias_operacion'], true)
                                        : $det['dias_operacion'])
                                    : null,
                'ubicacion'        => isset($det['ubicacion'])
                                        ? (is_string($det['ubicacion'])
                                            ? json_decode($det['ubicacion'], true)
                                            : $det['ubicacion'])
                                        : null,
                '_debug'           => $c['_debug'],
            ];
        }

        // ── 9. RESPUESTA ──────────────────────────────────────────────────────────
        $buffer = ob_get_clean();
        if (!empty($buffer)) {
            echo json_encode(['success'=>false,'message'=>'Output inesperado','raw'=>$buffer]);
            return;
        }

        echo json_encode([
            'success'          => true,
            'total'            => count($resultado_final),
            'mes_actual'       => $mes_nombre,
            'temporada'        => $temporada_actual,
            'recomendaciones'  => $resultado_final,
            'debug_preferencias' => [
                'motivo_viaje'  => $motivo_viaje,
                'actividades'   => $actividades,
                'vn_ambiente'   => $vn_ambiente,
                'gastro_fav'    => $gastro_fav,
                'gastro_excluir'=> $gastro_excluir,
            ],
        ]);

    } catch (Exception $e) {
        ob_clean();
        error_log('ObtenerModeloMR Error: ' . $e->getMessage());
        echo json_encode(['success'=>false,'message'=>'Error: ' . $e->getMessage()]);
    }
}





}