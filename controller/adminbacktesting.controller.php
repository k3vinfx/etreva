<?php
require_once 'model/adminBackTesting.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class AdminBacktestingController {

    private $model;

    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AdminBacktesting();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Verificar que el usuario sea administrador (rol 2)
    // ══════════════════════════════════════════════════════════════════════
    private function verificarAdmin() {
        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) return null;

        $rol = is_array($usuario) ? ($usuario['rol'] ?? null) : ($usuario->rol ?? null);
        if ((int)$rol !== 2) return null;

        return $usuario;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Vista principal del panel de administración
    //  GET index.php?c=admin_backtesting&a=Index
    // ══════════════════════════════════════════════════════════════════════
    public function Index() {
        $admin = $this->verificarAdmin();
        if (!$admin) {
            header("Location: index.php?c=inicio&a=Index");
            exit();
        }

        $kpis        = $this->model->obtenerKPIs();
        $kpisModelos = $this->model->kpisPorModelo();
        $evolucion   = $this->model->evolucionCalificaciones(30);
        $tagsNegativos = $this->model->tagsNegativosTop();
        $mejora      = $this->model->analisisMejora();

        // Feedbacks recientes con filtros
        $filtros = [
            'tipo'      => $_GET['tipo']      ?? '',
            'modelo_id' => $_GET['modelo_id'] ?? '',
            'calif_min' => $_GET['calif_min'] ?? '',
            'calif_max' => $_GET['calif_max'] ?? '',
            'ofensivo'  => $_GET['ofensivo']  ?? '',
            'estado'    => $_GET['estado']    ?? '',
            'buscar'    => $_GET['buscar']    ?? '',
            'page'      => $_GET['page']      ?? 1,
            'per_page'  => 15,
            'orden'     => $_GET['orden']     ?? 'desc',
        ];

        $feedbacks = $this->model->listarFeedbacks($filtros);
        $modelos   = $this->model->listarModelos();

        require_once 'view/admin/backtesting.php';
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Cambiar estado de un feedback
    //  POST index.php?c=admin_backtesting&a=CambiarEstado
    // ══════════════════════════════════════════════════════════════════════
    public function CambiarEstado() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success'=>false,'message'=>'Método no permitido']); return;
        }
        $admin = $this->verificarAdmin();
        if (!$admin) {
            echo json_encode(['success'=>false,'message'=>'No autorizado']); return;
        }

        $id     = (int)($_POST['id']     ?? 0);
        $estado = trim($_POST['estado']  ?? '');

        if (!$id || !$estado) {
            echo json_encode(['success'=>false,'message'=>'Datos inválidos']); return;
        }

        $ok = $this->model->cambiarEstado($id, $estado);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? "Estado actualizado a '$estado'" : 'No se pudo actualizar',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Eliminar feedback
    //  POST index.php?c=admin_backtesting&a=Eliminar
    // ══════════════════════════════════════════════════════════════════════
    public function Eliminar() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success'=>false,'message'=>'Método no permitido']); return;
        }
        $admin = $this->verificarAdmin();
        if (!$admin) {
            echo json_encode(['success'=>false,'message'=>'No autorizado']); return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID inválido']); return; }

        $ok = $this->model->eliminarFeedback($id);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Feedback eliminado' : 'No se pudo eliminar',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Cambiar estado de modelo ML
    //  POST index.php?c=admin_backtesting&a=CambiarEstadoModelo
    // ══════════════════════════════════════════════════════════════════════
    public function CambiarEstadoModelo() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success'=>false,'message'=>'Método no permitido']); return;
        }
        $admin = $this->verificarAdmin();
        if (!$admin) {
            echo json_encode(['success'=>false,'message'=>'No autorizado']); return;
        }

        $id     = (int)($_POST['id']     ?? 0);
        $estado = trim($_POST['estado']  ?? '');

        $ok = $this->model->cambiarEstadoModelo($id, $estado);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? "Modelo actualizado a '$estado'" : 'No se pudo actualizar',
        ]);
    }

   

    // ══════════════════════════════════════════════════════════════════════
    //  AJAX: KPIs generales del dashboard
    //  GET index.php?c=admin_backtesting&a=ObtenerKPIs
    // ══════════════════════════════════════════════════════════════════════
    public function ObtenerKPIs() {
        header('Content-Type: application/json');

        $admin = $this->verificarAdmin();
        if (!$admin) {
            echo json_encode(['success'=>false,'message'=>'No autorizado']); return;
        }

        $kpis        = $this->model->obtenerKPIs();
        $tagsNeg     = $this->model->tagsNegativosTop();
        $evolucion   = $this->model->evolucionCalificaciones(30);
        $mejora      = $this->model->analisisMejora();

        echo json_encode([
            'success'    => true,
            'kpis'       => $kpis,
            'tags_neg'   => $tagsNeg,
            'evolucion'  => $evolucion,
            'mejora'     => $mejora,
        ], JSON_UNESCAPED_UNICODE);
    }



    // ══════════════════════════════════════════════════════════════════════
    //  AJAX: Feedbacks con filtros y paginación
    //  GET index.php?c=admin_backtesting&a=ListarFeedbacks
    // ══════════════════════════════════════════════════════════════════════
    public function ListarFeedbacks() {
        header('Content-Type: application/json');
        $admin = $this->verificarAdmin();
        if (!$admin) { echo json_encode(['success'=>false,'message'=>'No autorizado']); return; }

        $filtros = [
            'tipo'      => $_GET['tipo']      ?? '',
            'modelo_id' => $_GET['modelo_id'] ?? '',
            'calif_min' => $_GET['calif_min'] ?? '',
            'calif_max' => $_GET['calif_max'] ?? '',
            'ofensivo'  => $_GET['ofensivo']  ?? '',
            'estado'    => $_GET['estado']    ?? '',
            'buscar'    => $_GET['buscar']    ?? '',
            'page'      => (int)($_GET['page']     ?? 1),
            'per_page'  => min(50, max(10, (int)($_GET['per_page'] ?? 20))),
            'orden'     => $_GET['orden']     ?? 'desc',
        ];

        $resultado = $this->model->listarFeedbacks($filtros);
        echo json_encode(['success' => true] + $resultado, JSON_UNESCAPED_UNICODE);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  AJAX: Modelos ML con KPIs por modelo
    //  GET index.php?c=admin_backtesting&a=ListarModelos
    // ══════════════════════════════════════════════════════════════════════
    public function ListarModelos() {
        header('Content-Type: application/json');
        $admin = $this->verificarAdmin();
        if (!$admin) { echo json_encode(['success'=>false,'message'=>'No autorizado']); return; }

        $modelos = $this->model->kpisPorModelo();
        echo json_encode([
            'success' => true,
            'modelos' => $modelos,
            'total'   => count($modelos),
        ], JSON_UNESCAPED_UNICODE);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  API JSON para datos de gráficas (AJAX)
    //  GET index.php?c=admin_backtesting&a=DatosGrafica&tipo=evolucion
    // ══════════════════════════════════════════════════════════════════════
    public function DatosGrafica() {
        header('Content-Type: application/json');

        $admin = $this->verificarAdmin();
        if (!$admin) {
            echo json_encode(['success'=>false,'message'=>'No autorizado']); return;
        }

        $tipo = $_GET['tipo'] ?? 'evolucion';

        switch ($tipo) {
            case 'evolucion':
                $datos = $this->model->evolucionCalificaciones((int)($_GET['dias'] ?? 30));
                break;
            case 'modelos':
                $datos = $this->model->kpisPorModelo();
                break;
            case 'tags':
                $datos = $this->model->tagsNegativosTop();
                break;
            case 'mejora':
                $datos = $this->model->analisisMejora();
                break;
            default:
                $datos = [];
        }

        echo json_encode(['success'=>true,'datos'=>$datos], JSON_UNESCAPED_UNICODE);
    }
}