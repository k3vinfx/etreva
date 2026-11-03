<?php
require_once 'model/atracciones.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class AtraccionesController {

    private $atraccionesModel;

    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->atraccionesModel = new AtraccionesModel();
    }

    /**
     * Vista modal de detalle de una atracción
     * URL: index.php?c=atracciones&a=detalle&id=7112
     */


     /**
     * Alias de detalle() para llamadas AJAX desde itinerarios
     * URL: index.php?c=atracciones&a=ObtenerDetalle&id=123
     */
    public function ObtenerDetalle() {
        // Acepta id tanto por GET como por POST
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        $usuarioLogueado = AuthMiddleware::resguardar();
        if (!$usuarioLogueado) {
            header("Location: index.php?c=inicio&a=Index");
            exit();
        }

        $atraccion    = $this->atraccionesModel->obtenerDetalle($id);
        $categorias   = $this->atraccionesModel->obtenerCategoria($id);
        $fotos        = $atraccion['fotos_rutas'] ?? [];
        $resenas      = $this->atraccionesModel->obtenerResenas($id);
        $relacionadas = $this->atraccionesModel->obtenerRelacionadas(
            $categorias['categoria'] ?? 'Otros',
            $id
        );
        $categorias   = $this->atraccionesModel->obtenerCategoria($id, true); // para mostrar, en el idioma activo

        require_once 'view/cliente/modal/modal_atracciones_detalle.php';
    }
    public function detalle() {
        $usuarioLogueado = AuthMiddleware::resguardar();

        if (!$usuarioLogueado) {
            header("Location: index.php?c=inicio&a=Index");
            exit();
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;

        if (!$id) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        $atraccion    = $this->atraccionesModel->obtenerDetalle($id);
        $categorias   = $this->atraccionesModel->obtenerCategoria($id);
        $fotos        = $atraccion['fotos_rutas'] ?? [];
        $resenas      = $this->atraccionesModel->obtenerResenas($id);
        $relacionadas = $this->atraccionesModel->obtenerRelacionadas(
            $categorias['categoria'] ?? 'Otros',
            $id
        );
        $categorias   = $this->atraccionesModel->obtenerCategoria($id, true); // para mostrar, en el idioma activo

        require_once 'view/cliente/modal/modal_atracciones_detalle.php';
    }

    /**
     * API JSON: retorna detalle de atracción para llamadas AJAX
     * URL: index.php?c=atracciones&a=detalleJson&id=7112
     */
    public function detalleJson() {
        header('Content-Type: application/json');

        $usuarioLogueado = AuthMiddleware::resguardar();
        if (!$usuarioLogueado) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $atraccion  = $this->atraccionesModel->obtenerDetalle($id);
        $categorias = $this->atraccionesModel->obtenerCategoria($id);
        $fotos      = $this->atraccionesModel->obtenerFotos($id);
        $resenas    = $this->atraccionesModel->obtenerResenas($id);

        if (!$atraccion) {
            echo json_encode(['success' => false, 'message' => 'Atracción no encontrada']);
            return;
        }

        echo json_encode([
            'success'    => true,
            'atraccion'  => $atraccion,
            'categorias' => $categorias,
            'fotos'      => $fotos,
            'resenas'    => $resenas,
        ]);
    }

    /**
     * Guardar reseña del usuario sobre una atracción
     */
    public function guardarResena() {
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

        $id_usuario = is_array($usuarioLogueado)
            ? ($usuarioLogueado['id'] ?? null)
            : ($usuarioLogueado->id ?? null);

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['atraccion_id']) || !isset($input['puntuacion'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $resultado = $this->atraccionesModel->guardarResena(
            $id_usuario,
            intval($input['atraccion_id']),
            floatval($input['puntuacion']),
            $input['comentario'] ?? ''
        );

        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Reseña guardada' : 'Error al guardar reseña'
        ]);
    }

    /**
     * Guardar atracción en itinerario del usuario
     * POST index.php?c=atracciones&a=guardarEnItinerario
     *
     * Regla de negocio:
     *   Si el usuario ya tiene itinerarios el mismo día, el nuevo horario debe
     *   comenzar DESPUÉS de que termine la visita anterior.
     *   El tiempo de visita se toma de atracciones.tiempo_estimado; si no existe,
     *   se usa 30 minutos como mínimo por defecto.
     */
    public function guardarEnItinerario() {
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

        $id_usuario = is_array($usuarioLogueado)
            ? ($usuarioLogueado['id'] ?? null)
            : ($usuarioLogueado->id ?? null);

        $input = json_decode(file_get_contents('php://input'), true);

        /* ── Validaciones básicas ── */
        if (!$input || empty($input['nombre'])) {
            echo json_encode(['success' => false, 'message' => 'El nombre del itinerario es requerido']);
            return;
        }

        $atraccion_id = intval($input['atraccion_id'] ?? 0);
        if (!$atraccion_id) {
            echo json_encode(['success' => false, 'message' => 'ID de atracción no válido']);
            return;
        }

        $fecha_visita = trim($input['fecha_visita'] ?? '');
        if (!$fecha_visita) {
            echo json_encode(['success' => false, 'message' => 'La fecha y hora de visita son requeridas']);
            return;
        }

        /* Normalizar formato: el input datetime-local llega como "YYYY-MM-DDTHH:MM" */
        $fecha_visita = str_replace('T', ' ', $fecha_visita);  // → "YYYY-MM-DD HH:MM"
        if (!strpos($fecha_visita, ':')) {
            echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido']);
            return;
        }

        /* ── Validar que la fecha no sea pasada ── */
        $ahora = new DateTime('now', new DateTimeZone('America/La_Paz'));
        $dt_visita = DateTime::createFromFormat('Y-m-d H:i', $fecha_visita,
                        new DateTimeZone('America/La_Paz'));

        if (!$dt_visita) {
            echo json_encode(['success' => false, 'message' => 'Fecha de visita inválida']);
            return;
        }
        // if ($dt_visita < $ahora) {
        //     echo json_encode(['success' => false, 'message' => 'La fecha de visita no puede ser en el pasado']);
        //     return;
        // }

        /* ── Validar conflicto de horario con visitas del mismo día ── */
        $conflicto = $this->atraccionesModel->verificarConflictoHorario(
            $id_usuario,
            $atraccion_id,
            $dt_visita
        );

        if ($conflicto['hay_conflicto']) {
            echo json_encode([
                'success'  => false,
                'conflict' => true,
                'message'  => $conflicto['mensaje'],
            ]);
            return;
        }

        /* ── Guardar ── */
        $resultado = $this->atraccionesModel->guardarEnItinerario(
            $id_usuario,
            $atraccion_id,
            trim($input['nombre']),
            trim($input['descripcion'] ?? ''),
            $fecha_visita
        );

        echo json_encode([
            'success'       => (bool) $resultado,
            'itinerario_id' => is_int($resultado) ? $resultado : null,
            'message'       => $resultado
                ? '¡Atracción guardada en tu itinerario!<br><small class="text-muted">Visita programada para: <strong>'
                    . date('d/m/Y H:i', strtotime($fecha_visita)) . '</strong></small>'
                : 'Error al guardar en el itinerario',
        ]);
    }

}