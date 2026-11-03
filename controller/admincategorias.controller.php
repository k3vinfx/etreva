<?php
/*
 * controller/AdminCategoriasController.php
 * Compatible PHP 7.0+
 *
 * ROUTER (agregar en index.php):
 *   case 'adminCategorias':
 *       require_once 'controller/AdminCategoriasController.php';
 *       $c = new AdmincategoriasController(); break;
 *
 * ENDPOINTS:
 *   GET  ?c=adminCategorias&a=Index
 *   GET  ?c=adminCategorias&a=Listar
 *   GET  ?c=adminCategorias&a=Stats
 *   POST ?c=adminCategorias&a=ToggleCategoria    {grupo_id, nombre}
 *   POST ?c=adminCategorias&a=ToggleSubcategoria {grupo_id, key}
 *   POST ?c=adminCategorias&a=AgregarCategoria   {grupo_id, nombre, rango_inicio, rango_fin}
 *   POST ?c=adminCategorias&a=AgregarSubcategoria{grupo_id, key, rango_inicio, rango_fin}
 *   POST ?c=adminCategorias&a=EditarCategoria    {grupo_id, nombre, rango_inicio, rango_fin}
 *   POST ?c=adminCategorias&a=EditarSubcategoria {grupo_id, key, rango_inicio, rango_fin}
 */

require_once 'model/adminCategorias.php';
require_once 'controller/jwtHelper.php';

class AdmincategoriasController
{
    private $model;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AdminCategorias();
        $this->soloAdmin();
    }

    private function soloAdmin()
    {
        $token  = isset($_SESSION['token']) ? $_SESSION['token'] : null;
        $rol    = (int)(isset($_SESSION['rol']) ? $_SESSION['rol'] : 0);
        $accion = isset($_GET['a']) ? $_GET['a'] : '';
        $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

        if (!$token || !JwtHelper::validateToken($token) || $rol !== 2) {
            $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                   || (strpos($accept, 'application/json') !== false)
                   || (!empty($accion) && !in_array($accion, array('Index', '')));
            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(array('success' => false, 'message' => 'Sesión expirada'));
                exit();
            }
            header('Location: index.php?c=login&a=Index');
            exit();
        }
    }

    private function json($data)
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    private function input()
    {
        $raw = file_get_contents('php://input');
        $dec = json_decode($raw, true);
        return is_array($dec) ? $dec : array();
    }

    // ── Vista ────────────────────────────────────────────────────────────────
    public function Index()
    {
        require_once 'view/admin/categorias.php';
    }

    // ── Listar todos los grupos ───────────────────────────────────────────────
    public function Listar()
    {
        $grupos  = $this->model->obtenerTodos();
        $conteos = $this->model->contarAtraccionesPorGrupo();

        foreach ($grupos as &$g) {
            $g['total_atracciones'] = isset($conteos[$g['id']]) ? $conteos[$g['id']] : 0;
        }
        $this->json(array('success' => true, 'grupos' => $grupos));
    }

    // ── Stats KPIs ────────────────────────────────────────────────────────────
    public function Stats()
    {
        $stats = $this->model->obtenerStats();
        $this->json(array('success' => true, 'stats' => $stats));
    }

    // ── Toggle estado CATEGORÍA ───────────────────────────────────────────────
    public function ToggleCategoria()
    {
        $in      = $this->input();
        $grupoId = (int)(isset($in['grupo_id']) ? $in['grupo_id'] : 0);
        $nombre  = trim(isset($in['nombre']) ? $in['nombre'] : '');

        if (!$grupoId || !$nombre)
            $this->json(array('success' => false, 'message' => 'Datos incompletos'));

        $res = $this->model->toggleEstadoCategoria($grupoId, $nombre);
        $this->json(array(
            'success'      => $res['ok'],
            'message'      => $res['msg'],
            'nuevo_estado' => isset($res['nuevo_estado']) ? $res['nuevo_estado'] : '',
        ));
    }

    // ── Toggle estado SUBCATEGORÍA ────────────────────────────────────────────
    public function ToggleSubcategoria()
    {
        $in      = $this->input();
        $grupoId = (int)(isset($in['grupo_id']) ? $in['grupo_id'] : 0);
        $key     = trim(isset($in['key']) ? $in['key'] : '');

        if (!$grupoId || !$key)
            $this->json(array('success' => false, 'message' => 'Datos incompletos'));

        $res = $this->model->toggleEstadoSubcategoria($grupoId, $key);
        $this->json(array(
            'success'      => $res['ok'],
            'message'      => $res['msg'],
            'nuevo_estado' => isset($res['nuevo_estado']) ? $res['nuevo_estado'] : '',
        ));
    }

    // ── Agregar CATEGORÍA ─────────────────────────────────────────────────────
    public function AgregarCategoria()
    {
        $in      = $this->input();
        $grupoId = (int)(isset($in['grupo_id'])     ? $in['grupo_id']     : 0);
        $nombre  = trim(isset($in['nombre'])        ? $in['nombre']        : '');
        $ranIni  = (int)(isset($in['rango_inicio']) ? $in['rango_inicio'] : 0);
        $ranFin  = (int)(isset($in['rango_fin'])    ? $in['rango_fin']    : 0);

        if (!$grupoId || !$nombre)
            $this->json(array('success' => false, 'message' => 'Datos incompletos'));

        $res = $this->model->agregarCategoria($grupoId, $nombre, $ranIni, $ranFin);
        $this->json(array('success' => $res['ok'], 'message' => $res['msg']));
    }

    // ── Agregar SUBCATEGORÍA ──────────────────────────────────────────────────
    public function AgregarSubcategoria()
    {
        $in      = $this->input();
        $grupoId = (int)(isset($in['grupo_id'])     ? $in['grupo_id']     : 0);
        $key     = trim(isset($in['key'])           ? $in['key']           : '');
        $ranIni  = (int)(isset($in['rango_inicio']) ? $in['rango_inicio'] : 0);
        $ranFin  = (int)(isset($in['rango_fin'])    ? $in['rango_fin']    : 0);

        if (!$grupoId || !$key)
            $this->json(array('success' => false, 'message' => 'Datos incompletos'));

        $res = $this->model->agregarSubcategoria($grupoId, $key, $ranIni, $ranFin);
        $this->json(array('success' => $res['ok'], 'message' => $res['msg']));
    }

    // ── Editar CATEGORÍA (rangos) ─────────────────────────────────────────────
    public function EditarCategoria()
    {
        $in      = $this->input();
        $grupoId = (int)(isset($in['grupo_id'])     ? $in['grupo_id']     : 0);
        $nombre  = trim(isset($in['nombre'])        ? $in['nombre']        : '');
        $ranIni  = (int)(isset($in['rango_inicio']) ? $in['rango_inicio'] : 0);
        $ranFin  = (int)(isset($in['rango_fin'])    ? $in['rango_fin']    : 0);

        if (!$grupoId || !$nombre)
            $this->json(array('success' => false, 'message' => 'Datos incompletos'));

        $res = $this->model->editarCategoria($grupoId, $nombre, $ranIni, $ranFin);
        $this->json(array('success' => $res['ok'], 'message' => $res['msg']));
    }

    // ── Editar SUBCATEGORÍA (rangos) ──────────────────────────────────────────
    public function EditarSubcategoria()
    {
        $in      = $this->input();
        $grupoId = (int)(isset($in['grupo_id'])     ? $in['grupo_id']     : 0);
        $key     = trim(isset($in['key'])           ? $in['key']           : '');
        $ranIni  = (int)(isset($in['rango_inicio']) ? $in['rango_inicio'] : 0);
        $ranFin  = (int)(isset($in['rango_fin'])    ? $in['rango_fin']    : 0);

        if (!$grupoId || !$key)
            $this->json(array('success' => false, 'message' => 'Datos incompletos'));

        $res = $this->model->editarSubcategoria($grupoId, $key, $ranIni, $ranFin);
        $this->json(array('success' => $res['ok'], 'message' => $res['msg']));
    }
}