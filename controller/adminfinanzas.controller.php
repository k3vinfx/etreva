<?php
/*
 * controller/AdminFinanzasController.php
 * ─────────────────────────────────────────────────────────────────────────────
 * ROUTER (agregar en index.php):
 *   case 'adminFinanzas':
 *       require_once 'controller/AdminFinanzasController.php';
 *       $c = new AdminfinanzasController(); break;
 *
 * ENDPOINTS  (todos soportan ?desde=YYYY-MM-DD&hasta=YYYY-MM-DD  o  ?mes=YYYY-MM)
 *   GET  ?c=adminFinanzas&a=Index
 *   GET  ?c=adminFinanzas&a=Kpis           [&desde=&hasta=] [&mes=]
 *   GET  ?c=adminFinanzas&a=GraficoDias    [&dias=30] [&desde=&hasta=]
 *   GET  ?c=adminFinanzas&a=GraficoMeses   [&meses=6] [&desde=&hasta=]
 *   GET  ?c=adminFinanzas&a=PorPlan        [&desde=&hasta=] [&mes=]
 *   GET  ?c=adminFinanzas&a=Pagos          [&page=] [&desde=&hasta=] [&mes=]
 *   GET  ?c=adminFinanzas&a=TopClientes    [&desde=&hasta=] [&mes=]
 *   GET  ?c=adminFinanzas&a=AniosDisponibles
 * ─────────────────────────────────────────────────────────────────────────────
 */

require_once 'model/adminFinanzas.php';
require_once 'controller/jwtHelper.php';

class AdminfinanzasController
{
    private $model;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AdminFinanzas();
        $this->soloAdmin();
    }

    // ── Guard ─────────────────────────────────────────────────────────────────
    private function soloAdmin()
    {
        $token  = isset($_SESSION['token']) ? $_SESSION['token'] : null;
        $rol    = (int)(isset($_SESSION['rol']) ? $_SESSION['rol'] : 0);
        $accion = isset($_GET['a']) ? $_GET['a'] : '';
        $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

        if (!$token || !JwtHelper::validateToken($token) || !in_array($rol, [2, 3], true)) {
            $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                   || (strpos($accept, 'application/json') !== false)
                   || (!empty($accion) && !in_array($accion, ['Index', '']));
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sesión expirada o sin permisos']);
                exit();
            }
            header('Location: index.php?c=login&a=Index');
            exit();
        }
    }

    private function json(array $data)
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ── Extrae el filtro de fecha del GET ─────────────────────────────────────
    private function filtro()
    {
        $f = array();
        if (!empty($_GET['mes']))   $f['mes']   = trim($_GET['mes']);
        if (!empty($_GET['desde'])) $f['desde'] = trim($_GET['desde']);
        if (!empty($_GET['hasta'])) $f['hasta'] = trim($_GET['hasta']);
        return $f;
    }

    // ── Vista ─────────────────────────────────────────────────────────────────
    public function Index()
    {
        require_once 'view/admin/finanzas.php';
    }

    // ── KPIs ──────────────────────────────────────────────────────────────────
    public function Kpis()
    {
        $this->json(array('success' => true, 'kpis' => $this->model->obtenerKpis($this->filtro())));
    }

    // ── Gráfico diario ────────────────────────────────────────────────────────
    public function GraficoDias()
    {
        $dias = max(7, min(366, (int)(isset($_GET['dias']) ? $_GET['dias'] : 30)));
        $this->json(array('success' => true, 'data' => $this->model->ingresosPorDia($dias, $this->filtro())));
    }

    // ── Gráfico mensual ───────────────────────────────────────────────────────
    public function GraficoMeses()
    {
        $meses = max(2, min(24, (int)(isset($_GET['meses']) ? $_GET['meses'] : 6)));
        $this->json(array('success' => true, 'data' => $this->model->ingresosPorMes($meses, $this->filtro())));
    }

    // ── Breakdown por plan ────────────────────────────────────────────────────
    public function PorPlan()
    {
        $this->json(array('success' => true, 'planes' => $this->model->ingresosPorPlan($this->filtro())));
    }

    // ── Tabla de pagos confirmados ────────────────────────────────────────────
    public function Pagos()
    {
        $page    = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
        $perPage = !empty($_GET['per_page']) ? min(9999, (int)$_GET['per_page']) : 25;
        $offset  = ($page - 1) * $perPage;

        $result = $this->model->listadoPagosConfirmados($perPage, $offset, $this->filtro());
        $pages  = max(1, (int)ceil($result['total'] / $perPage));

        $this->json(array(
            'success' => true,
            'pagos'   => $result['pagos'],
            'total'   => $result['total'],
            'page'    => $page,
            'pages'   => $pages,
        ));
    }

    // ── Top clientes ──────────────────────────────────────────────────────────
    public function TopClientes()
    {
        $this->json(array('success' => true, 'clientes' => $this->model->topClientes(10, $this->filtro())));
    }

    // ── Años disponibles (para combobox) ──────────────────────────────────────
    public function AniosDisponibles()
    {
        $this->json(array('success' => true, 'anios' => $this->model->aniosDisponibles()));
    }
}