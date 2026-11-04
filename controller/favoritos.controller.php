<?php
require_once 'model/favoritos.php';
require_once 'model/restriccionEdad.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class FavoritosController {

    private $favoritosModel;

    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->favoritosModel = new FavoritosModel();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Vista principal de favoritos
    //  URL: index.php?c=favoritos&a=Index
    // ══════════════════════════════════════════════════════════════════════
    public function Index() {

        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            header("Location: index.php?c=inicio&a=Index");
            exit();
        }

        $id_usuario = is_array($usuario)
            ? ($usuario['id'] ?? null)
            : ($usuario->id ?? null);

        $anio    = (int)($_GET['anio']   ?? date('Y'));
        $cat     = $_GET['cat']          ?? 'todas';
        $formato = $_GET['formato']      ?? 'html';

        // Obtener todos los favoritos del usuario
        $favoritos = RestriccionEdad::filtrar((array)$this->favoritosModel->obtenerFavoritos($id_usuario), $id_usuario, 'atracciones_id');

        // Respuesta JSON (para uso por API / debug)
        if ($formato === 'json') {
            header('Content-Type: application/json');
            echo json_encode([
                'success'   => true,
                'total'     => count($favoritos),
                'favoritos' => $favoritos,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            return;
        }

        require_once 'view/cliente/favoritos.php';
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Quitar un item de favoritos (vuelve a estado 'visitado')
    //  POST index.php?c=favoritos&a=QuitarFavorito
    // ══════════════════════════════════════════════════════════════════════
    public function QuitarFavorito() {
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

        $ok = $this->favoritosModel->quitarFavorito($itinerario_id, $id_usuario);

        echo json_encode([
            'success' => $ok,
            'message' => $ok
                ? 'Quitado de favoritos correctamente'
                : 'No se pudo quitar de favoritos',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Obtener favoritos como JSON puro (endpoint ligero para widgets)
    //  GET index.php?c=favoritos&a=Lista
    // ══════════════════════════════════════════════════════════════════════
    public function Lista() {
        header('Content-Type: application/json');

        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $id_usuario = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        $limite     = intval($_GET['limite'] ?? 50);

        $favoritos = RestriccionEdad::filtrar((array)$this->favoritosModel->obtenerFavoritos($id_usuario, $limite), $id_usuario, 'atracciones_id');

        echo json_encode([
            'success'   => true,
            'total'     => count($favoritos),
            'favoritos' => $favoritos,
        ], JSON_UNESCAPED_UNICODE);
    }
}