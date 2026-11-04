<?php
// ── Autoload de Composer (necesario para Dompdf) ──────────────────────────────
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    // Si no encuentra el autoload, dompdf no está instalado
    error_log('vendor/autoload.php no encontrado. Ejecuta: composer require dompdf/dompdf');
}

require_once 'model/itinerarios.php';
require_once 'model/restriccionEdad.php';
require_once 'model/resenaFiltro.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';
require_once 'model/login.php';

class ItinerariosController {

    private $itinerariosModel;

    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->itinerariosModel = new ItinerariosModel();
    }

    public function Index() {
        $usuarioLogueado = AuthMiddleware::resguardar();
        if (!$usuarioLogueado) {
            header("Location: index.php?c=inicio&a=Index");
            exit();
        }

        $id_usuario = is_array($usuarioLogueado)
            ? ($usuarioLogueado['id'] ?? null)
            : ($usuarioLogueado->id ?? null);

        $mes     = $_GET['mes']     ?? null;
        $anio    = $_GET['anio']    ?? date('Y');
        $filtro  = $_GET['f']       ?? 'todos';
        $formato = $_GET['formato'] ?? 'html';

        $itinerarios = [];

        if ($mes) {
            $mes  = (int)$mes;
            $anio = (int)$anio;
            $itinerarios = $this->itinerariosModel
                ->obtenerItinerariosFiltrados($id_usuario, $mes, $anio);

        } elseif ($filtro !== 'todos') {

            switch ($filtro) {
                case 'hoy':
                    $mes  = (int)date('m');
                    $anio = (int)date('Y');
                    $itinerarios = $this->itinerariosModel
                        ->obtenerItinerariosFiltrados($id_usuario, $mes, $anio);
                    $hoy = date('Y-m-d');
                    $itinerarios = array_values(array_filter($itinerarios,
                        fn($i) => date('Y-m-d', strtotime($i['fecha_visita'])) === $hoy
                    ));
                    break;

                case 'ayer':
                    $ayer = date('Y-m-d', strtotime('-1 day'));
                    $mes  = (int)date('m', strtotime('-1 day'));
                    $anio = (int)date('Y', strtotime('-1 day'));
                    $itinerarios = $this->itinerariosModel
                        ->obtenerItinerariosFiltrados($id_usuario, $mes, $anio);
                    $itinerarios = array_values(array_filter($itinerarios,
                        fn($i) => date('Y-m-d', strtotime($i['fecha_visita'])) === $ayer
                    ));
                    break;

                case 'semana':
                    $lunes   = date('Y-m-d', strtotime('monday this week'));
                    $domingo = date('Y-m-d', strtotime('sunday this week'));
                    $mes     = (int)date('m');
                    $anio    = (int)date('Y');
                    $itinerarios = $this->itinerariosModel
                        ->obtenerItinerariosFiltrados($id_usuario, $mes, $anio);
                    $itinerarios = array_values(array_filter($itinerarios,
                        function($i) use ($lunes, $domingo) {
                            $f = date('Y-m-d', strtotime($i['fecha_visita']));
                            return $f >= $lunes && $f <= $domingo;
                        }
                    ));
                    break;

                case 'mes':
                    $mes  = (int)date('m');
                    $anio = (int)date('Y');
                    $itinerarios = $this->itinerariosModel
                        ->obtenerItinerariosFiltrados($id_usuario, $mes, $anio);
                    break;
            }

        } else {
            $itinerarios = $this->itinerariosModel->obtenerItinerarios($id_usuario);
        }

        // Menores de 18: sin bares ni discotecas (Ley 259)
        $itinerarios = RestriccionEdad::filtrar((array)$itinerarios, $id_usuario, 'atracciones_id');

        if ($formato === 'json') {
            header('Content-Type: application/json');
            echo json_encode([
                'success'     => true,
                'filtro'      => ['mes' => $mes, 'anio' => $anio, 'f' => $filtro],
                'total'       => count($itinerarios),
                'itinerarios' => $itinerarios,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        require_once 'view/cliente/itinerario.php';
    }

    public function marcarVisitadoOLD() {
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
        $id_usuario    = is_array($usuarioLogueado) ? ($usuarioLogueado['id'] ?? null) : ($usuarioLogueado->id ?? null);
        $itinerario_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$itinerario_id) {
            echo json_encode(['success' => false, 'message' => 'ID de itinerario no válido']);
            return;
        }
        $resultado = $this->itinerariosModel->marcarVisitado($itinerario_id, $id_usuario);
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Itinerario marcado como visitado' : 'Error al actualizar'
        ]);
    }

    public function eliminar() {
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
        $id_usuario    = is_array($usuarioLogueado) ? ($usuarioLogueado['id'] ?? null) : ($usuarioLogueado->id ?? null);
        $itinerario_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if (!$itinerario_id) {
            echo json_encode(['success' => false, 'message' => 'ID de itinerario no válido']);
            return;
        }
        $resultado = $this->itinerariosModel->eliminarItinerario($itinerario_id, $id_usuario);
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Itinerario eliminado' : 'Error al eliminar'
        ]);
    }

    public function Favorito() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        $id_usuario    = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        $itinerario_id = intval($_POST['id'] ?? 0);
        if (!$itinerario_id || !$id_usuario) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos para la operación']);
            return;
        }
        $ok = $this->itinerariosModel->marcarComoFavorito($itinerario_id, $id_usuario);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Añadido a favoritos correctamente' : 'No se pudo marcar como favorito (verifique que esté visitado)'
        ]);
    }

    public function ObtenerModeloPorAtraccion() {
        header('Content-Type: application/json');
        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        $atraccion_id = intval($_GET['atraccion_id'] ?? 0);
        if (!$atraccion_id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $modelo = $this->itinerariosModel->obtenerModeloPorAtraccion($atraccion_id);
        if ($modelo) {
            echo json_encode([
                'success' => true,
                'modelo'  => ['id' => $modelo['id'], 'nombre' => $modelo['nombre'], 'version' => $modelo['version']]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Modelo no encontrado']);
        }
    }

    public function marcarVisitado() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        $id_usuario    = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        $itinerario_id = intval($_POST['id'] ?? 0);
        if (!$itinerario_id || !$id_usuario) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }
        $ok             = $this->itinerariosModel->marcarComoVisitado($itinerario_id, $id_usuario);
        $datosAtraccion = null;
        if ($ok) {
            $datosAtraccion = $this->itinerariosModel->obtenerDatosAtraccionDeItinerario($itinerario_id);
        }
        echo json_encode([
            'success'         => $ok,
            'message'         => $ok ? 'Marcado como visitado' : 'No se pudo actualizar',
            'datos_atraccion' => $datosAtraccion,
        ]);
    }

    public function GuardarCalificacion() {
        header('Content-Type: application/json');
        ob_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        $id_usuario = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Body inválido']);
            return;
        }

        $itinerario_id = intval($body['itinerario_id'] ?? 0);
        $atraccion_id  = intval($body['atraccion_id']  ?? 0);
        $calificacion  = intval($body['calificacion']  ?? 0);
        $comentario    = trim($body['comentario']       ?? '');
        $tags          = $body['tags']                  ?? [];
        $modelo_id     = !empty($body['modelo_id']) ? intval($body['modelo_id']) : null;

        if ($atraccion_id === 0 && $itinerario_id > 0) {
            $datos = $this->itinerariosModel->obtenerAtraccionDeItinerario($itinerario_id);
            $atraccion_id = intval($datos['atracciones_id'] ?? 0);
        }

        if (!$itinerario_id || !$atraccion_id || $calificacion < 1 || $calificacion > 5) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o puntuación fuera de rango']);
            return;
        }

        // Reseñas repetidas o clonadas: no se publican (Reglamento, punto 9)
        $dup = ResenaFiltro::motivoDuplicado($comentario, $atraccion_id, $itinerario_id);
        if ($dup) {
            ob_clean();
            echo json_encode(['success' => false, 'duplicada' => $dup, 'message' => Lang::get('legal.resena_' . $dup)]);
            return;
        }

        $loginModel = new login();
        $pref       = $loginModel->obtenerPreferencias($id_usuario);
        $intereses  = [];
        if ($pref && !empty($pref->intereses_json) && is_string($pref->intereses_json)) {
            $intereses = json_decode($pref->intereses_json, true) ?: [];
        }
        $preferencias_planas = [
            'rango_edad'     => $intereses['rango_edad']  ?? null,
            'compania'       => $intereses['compania']    ?? null,
            'motivo_viaje'   => $pref->motivo_viaje       ?? $intereses['motivo_viaje'] ?? null,
            'presupuesto_max'=> $pref->presupuesto_max    ?? null,
            'duracion_dias'  => isset($pref->duracion_dias) ? intval($pref->duracion_dias) : 0,
            'actividades'    => $intereses['actividades'] ?? [],
            'experiencias'   => $intereses['experiencias'] ?? [],
        ];

        $modelo_info = $this->itinerariosModel->obtenerModeloPorAtraccion($atraccion_id);
        $mapeo       = $modelo_info['mapeo'] ?? [];
        if (!$modelo_id && $modelo_info) { $modelo_id = $modelo_info['id']; }
        $preferencias_planas['_input_original'] = $modelo_info['input_original'] ?? [];

        $nuevo_input = [];
        if (!empty($mapeo)) {
            $nuevo_input = $this->itinerariosModel->construirInputVector($mapeo, $preferencias_planas, $calificacion);
        }

        $nueva_metricas = [
            'calificacion'        => $calificacion,
            'tags'                => $tags,
            'nuevo_input_vector'  => $nuevo_input,
            'atraccion_id'        => $atraccion_id,
            'modelo_origen'       => [
                'id'      => $modelo_info['id']      ?? null,
                'nombre'  => $modelo_info['nombre']  ?? null,
                'version' => $modelo_info['version'] ?? null,
            ],
            'preferencias_usadas' => array_filter(
                $preferencias_planas,
                fn($k) => $k !== '_input_original',
                ARRAY_FILTER_USE_KEY
            ),
            'input_original'  => $modelo_info['input_original'] ?? [],
            'fecha_reajuste'  => date('Y-m-d H:i:s'),
            'feedback_tipo'   => $calificacion >= 4 ? 'positivo' : ($calificacion >= 3 ? 'neutral' : 'negativo'),
        ];

        $ok     = $this->itinerariosModel->guardarCalificacion(
            $itinerario_id, $atraccion_id, $calificacion,
            $comentario, $tags, $modelo_id, $nueva_metricas
        );
        $buffer = ob_get_clean();
        if (!empty($buffer)) {
            echo json_encode(['success' => false, 'message' => 'Output inesperado', 'raw' => $buffer]);
            return;
        }
        echo json_encode($ok
            ? ['success' => true,  'message' => 'Calificación guardada correctamente',
               'calificacion' => $calificacion, 'feedback_tipo' => $nueva_metricas['feedback_tipo']]
            : ['success' => false, 'message' => 'Error al guardar en la base de datos']
        );
    }

    public function ObtenerAtraccionDeItinerario() {
        header('Content-Type: application/json');
        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $resultado = $this->itinerariosModel->obtenerAtraccionDeItinerario($id);
        if ($resultado) {
            echo json_encode([
                'success'      => true,
                'atraccion_id' => intval($resultado['atracciones_id']),
                'nombre'       => $resultado['nombre']    ?? 'Atracción',
                'categoria'    => $resultado['categoria'] ?? '—',
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No encontrado']);
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  ExportarPDF — genera PDF con itinerarios del mes + enlaces Maps
    //  URL: index.php?c=itinerarios&a=ExportarPDF&mes=03&anio=2026
    //  Requiere: composer require dompdf/dompdf
    // ══════════════════════════════════════════════════════════════════════
    public function ExportarPDF() {

        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            header("Location: index.php?c=inicio&a=Index");
            exit();
        }

        // Verificar que dompdf esté disponible
        if (!class_exists('\Dompdf\Dompdf')) {
            die('<p style="font-family:sans-serif;padding:20px;">
                <strong>Error:</strong> Dompdf no está instalado.<br>
                Ejecuta en la terminal: <code>composer require dompdf/dompdf</code>
            </p>');
        }

        $id_usuario = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        $mes        = intval($_GET['mes']  ?? date('m'));
        $anio       = intval($_GET['anio'] ?? date('Y'));

        $itinerarios = $this->itinerariosModel->obtenerItinerariosFiltrados($id_usuario, $mes, $anio);

        $tz = new DateTimeZone('America/La_Paz');

        $MESES = [
            1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril',
            5=>'Mayo',  6=>'Junio',   7=>'Julio', 8=>'Agosto',
            9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre',
        ];
        $DIAS = [
            'Monday'=>'Lunes', 'Tuesday'=>'Martes', 'Wednesday'=>'Miércoles',
            'Thursday'=>'Jueves', 'Friday'=>'Viernes',
            'Saturday'=>'Sábado', 'Sunday'=>'Domingo',
        ];

        $nombre_mes = $MESES[$mes] ?? $mes;
        $hoy        = (new DateTime('now', $tz))->format('d/m/Y H:i');
        $total      = count($itinerarios);
        $visitados  = count(array_filter($itinerarios, fn($i) => in_array($i['estado'], ['visitado','favorito'])));
        $pendientes = count(array_filter($itinerarios, fn($i) => $i['estado'] === 'pendiente'));
        $favoritos  = count(array_filter($itinerarios, fn($i) => $i['estado'] === 'favorito'));

        // ── Filas de la tabla ─────────────────────────────────────────
        $filas_html = '';
        foreach ($itinerarios as $idx => $item) {
            $fecha_fmt  = '—';
            $hora_fmt   = '—';
            $dia_semana = '';
            if (!empty($item['fecha_visita'])) {
                try {
                    $dt         = new DateTime($item['fecha_visita']);
                    $dt->setTimezone($tz);
                    $fecha_fmt  = $dt->format('d/m/Y');
                    $hora_fmt   = $dt->format('H:i') . ' hs';
                    $dia_semana = $DIAS[$dt->format('l')] ?? $dt->format('l');
                } catch (Exception $e) {}
            }

            $estado       = $item['estado'] ?? 'pendiente';
            $estado_color = match($estado) {
                'visitado' => '#10b981',
                'favorito' => '#f59e0b',
                'pendiente'=> '#667eea',
                default    => '#94a3b8',
            };
            $estado_label = match($estado) {
                'visitado' => 'Visitado',
                'favorito' => 'Favorito',
                'pendiente'=> 'Pendiente',
                default    => ucfirst($estado),
            };

            $lat      = $item['latitud']  ?? '';
            $lng      = $item['longitud'] ?? '';
            $maps_url = ($lat && $lng)
                ? "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}"
                : '';
            $maps_cell = $maps_url
                ? "<a href='{$maps_url}' style='color:#667eea;font-weight:700;font-size:10px;text-decoration:none;'>&#x1F4CD; Ver en Maps</a>"
                : "<span style='color:#94a3b8;font-size:10px;'>—</span>";

            $ciudad    = htmlspecialchars($item['ciudad']    ?? '');
            $ubicacion = $ciudad ?: (htmlspecialchars($item['direccion'] ?? '') ?: '—');
            $bg        = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc';
            $nombre_esc = htmlspecialchars($item['nombre'] ?? '—');

            $filas_html .= "
            <tr style='background:{$bg};'>
                <td style='padding:10px 12px;border-bottom:1px solid #e2e8f0;font-weight:700;font-size:11px;color:#1a1a2e;'>
                    {$nombre_esc}
                    <div style='font-weight:400;color:#718096;font-size:10px;margin-top:2px;'>{$dia_semana}</div>
                </td>
                <td style='padding:10px 12px;border-bottom:1px solid #e2e8f0;font-size:11px;color:#2d3748;text-align:center;'>
                    {$fecha_fmt}<br><span style='color:#667eea;font-weight:700;'>{$hora_fmt}</span>
                </td>
                <td style='padding:10px 12px;border-bottom:1px solid #e2e8f0;font-size:11px;color:#2d3748;text-align:center;'>{$ubicacion}</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e2e8f0;text-align:center;'>
                    <span style='background:{$estado_color};color:#fff;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;'>{$estado_label}</span>
                </td>
                <td style='padding:10px 12px;border-bottom:1px solid #e2e8f0;text-align:center;'>{$maps_cell}</td>
            </tr>";
        }

        if (!$filas_html) {
            $filas_html = "<tr><td colspan='5' style='padding:40px;text-align:center;color:#94a3b8;font-size:12px;'>Sin itinerarios para {$nombre_mes} {$anio}</td></tr>";
        }

        // ── Bloques Cómo Llegar ───────────────────────────────────────
        $como_llegar_html = '';
        $con_coords = array_filter($itinerarios, fn($i) => !empty($i['latitud']) && !empty($i['longitud']));

        if (!empty($con_coords)) {
            $como_llegar_html = "
            <div style='margin-top:32px;'>
                <div style='background:#1a1a2e;color:#fff;padding:14px 24px;border-radius:10px 10px 0 0;'>
                    <strong style='font-size:13px;'>&#x1F4CD; Cómo llegar a cada atracción</strong>
                    <span style='float:right;opacity:.5;font-size:10px;'>Abre el enlace en tu celular</span>
                </div>
                <div style='border:1px solid #e2e8f0;border-top:none;border-radius:0 0 10px 10px;padding:16px 20px;'>";

            foreach ($con_coords as $item) {
                $lat         = $item['latitud'];
                $lng         = $item['longitud'];
                $nombre      = htmlspecialchars($item['nombre'] ?? '—');
                $ciudad      = htmlspecialchars($item['ciudad'] ?? '');
                $dir         = htmlspecialchars($item['direccion'] ?? '');
                $fecha_corta = '—';
                if (!empty($item['fecha_visita'])) {
                    try {
                        $dt = new DateTime($item['fecha_visita']);
                        $dt->setTimezone($tz);
                        $fecha_corta = $dt->format('d/m H:i');
                    } catch (Exception $e) {}
                }
                $gmaps_url  = "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}";
                $gmaps_text = "maps.google.com/?q={$lat},{$lng}";

                $como_llegar_html .= "
                <div style='display:table;width:100%;border-bottom:1px solid #f1f5f9;padding:12px 0;'>
                    <div style='display:table-cell;vertical-align:middle;width:55%;'>
                        <strong style='font-size:12px;color:#1a1a2e;'>{$nombre}</strong><br>
                        <span style='font-size:10px;color:#718096;'>{$ciudad}" . ($dir ? " · {$dir}" : "") . " &nbsp;|&nbsp; <span style='color:#667eea;'>{$fecha_corta}</span></span><br>
                        <span style='font-size:10px;color:#94a3b8;'>Coords: {$lat}, {$lng}</span>
                    </div>
                    <div style='display:table-cell;vertical-align:middle;text-align:right;width:45%;padding-left:12px;'>
                        <div style='background:#f0f4ff;border:1px solid #c7d2fe;border-radius:8px;padding:8px 12px;display:inline-block;'>
                            <div style='font-size:9px;color:#4338ca;font-weight:700;margin-bottom:3px;'>ENLACE NAVEGACIÓN</div>
                            <a href='{$gmaps_url}' style='font-size:10px;color:#4338ca;word-break:break-all;'>{$gmaps_text}</a>
                        </div>
                    </div>
                </div>";
            }
            $como_llegar_html .= "</div></div>";
        }

        // ── HTML del PDF ──────────────────────────────────────────────
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1a1a2e; margin:0; padding:0; }
.pdf-header { background:#1a1a2e; color:#fff; padding:28px 32px 22px; }
.pdf-header h1 { margin:0 0 4px; font-size:22px; }
.pdf-header .sub { opacity:.5; font-size:11px; margin:0; }
.badge-mes { float:right; background:#667eea; color:#fff; padding:6px 18px; border-radius:20px; font-size:12px; font-weight:700; margin-top:4px; }
.stats-row { display:table; width:100%; background:#f8fafc; border-bottom:2px solid #e2e8f0; }
.stat-cell { display:table-cell; text-align:center; padding:14px 0; border-right:1px solid #e2e8f0; }
.stat-cell:last-child { border-right:none; }
.stat-num { font-size:22px; font-weight:700; line-height:1; }
.stat-lbl { font-size:10px; color:#718096; text-transform:uppercase; letter-spacing:.5px; margin-top:3px; }
.section-title { background:#1a1a2e; color:#fff; padding:10px 24px; font-size:12px; font-weight:700; margin:24px 0 0; border-radius:8px 8px 0 0; }
table { width:100%; border-collapse:collapse; }
thead th { background:#f1f5f9; color:#475569; padding:9px 12px; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #e2e8f0; }
thead th:nth-child(2), thead th:nth-child(3), thead th:nth-child(4), thead th:nth-child(5) { text-align:center; }
.pdf-footer { margin-top:28px; padding:12px 32px; border-top:2px solid #e2e8f0; color:#94a3b8; font-size:10px; text-align:center; }
a { color:#667eea; }
</style>
</head>
<body>
<div class="pdf-header">
    <span class="badge-mes">{$nombre_mes} {$anio}</span>
    <h1>Mis Itinerarios</h1>
    <p class="sub">Exportado el {$hoy} · Etreva Tourism Platform · La Paz, Bolivia</p>
</div>
<div class="stats-row">
    <div class="stat-cell"><div class="stat-num" style="color:#1a1a2e;">{$total}</div><div class="stat-lbl">Total</div></div>
    <div class="stat-cell"><div class="stat-num" style="color:#667eea;">{$pendientes}</div><div class="stat-lbl">Pendientes</div></div>
    <div class="stat-cell"><div class="stat-num" style="color:#10b981;">{$visitados}</div><div class="stat-lbl">Visitados</div></div>
    <div class="stat-cell"><div class="stat-num" style="color:#f59e0b;">{$favoritos}</div><div class="stat-lbl">Favoritos</div></div>
</div>
<div>
    <div class="section-title">Detalle de visitas — {$nombre_mes} {$anio}</div>
    <table>
        <thead>
            <tr>
                <th style="width:32%;">Atraccion</th>
                <th style="width:18%;">Fecha / Hora</th>
                <th style="width:20%;">Ubicacion</th>
                <th style="width:14%;">Estado</th>
                <th style="width:16%;">Como llegar</th>
            </tr>
        </thead>
        <tbody>{$filas_html}</tbody>
    </table>
</div>
{$como_llegar_html}
<div class="pdf-footer">Etreva Tourism Platform · La Paz, Bolivia · {$hoy} · {$total} visitas en {$nombre_mes} {$anio}</div>
</body>
</html>
HTML;

        // ── Generar PDF ───────────────────────────────────────────────
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("itinerario_{$nombre_mes}_{$anio}.pdf", ['Attachment' => true]);
        exit();
    }
}