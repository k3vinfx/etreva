<?php
require_once 'model/recomendaciones.php';
require_once 'model/restriccionEdad.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';
require_once 'lang/Lang.php';

if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'es';
Lang::load($_SESSION['lang']);
class BuscarController {

    private $recomendacionesModel;

    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->recomendacionesModel = new Recomendaciones();
    }

    /**
     * Vista principal del explorador
     */
    public function Index() {
        $usuarioLogueado = AuthMiddleware::resguardar();
        if (!$usuarioLogueado) {
            header("Location: index.php?c=inicio&a=Index");
            exit();
        }
        require_once 'view/cliente/buscador.php';
    }

    /**
     * API: Devuelve TODAS las atracciones del catálogo con sus datos básicos.
     * El filtrado, la búsqueda y la paginación se hacen en el cliente (JS),
     * así el servidor hace solo una consulta y el frontend maneja el estado.
     */
    public function ObtenerTodas() {
        header('Content-Type: application/json');
        ob_start();

        try {
            $usuarioLogueado = AuthMiddleware::resguardar();
            if (!$usuarioLogueado) {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                return;
            }

            $atracciones = $this->recomendacionesModel->obtenerTodasAtracciones();
            // Menores de 18: sin bares ni discotecas (Ley 259)
            $atracciones = RestriccionEdad::filtrar($atracciones, RestriccionEdad::idDe($usuarioLogueado), 'id');

            $buffer = ob_get_clean();
            if (!empty($buffer)) {
                echo json_encode(['success' => false, 'message' => 'Output inesperado', 'raw' => $buffer]);
                return;
            }

            echo json_encode([
                'success'     => true,
                'total'       => count($atracciones),
                'atracciones' => $atracciones,
            ]);

        } catch (Exception $e) {
            ob_clean();
            error_log('BuscarController::ObtenerTodas - ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
    }
}