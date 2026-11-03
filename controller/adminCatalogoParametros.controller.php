<?php
require_once 'model/adminCatalogoParametros.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class AdmincatalogoparametrosController
{
    private adminCatalogoParametros $model;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new adminCatalogoParametros();
    }

    // ─── Auth ────────────────────────────────────────────────────────────────
    private function soloAdmin(): void
    {
        $token  = $_SESSION['token'] ?? null;
        $rol    = (int)($_SESSION['rol'] ?? 0);
        $accion = $_GET['a'] ?? '';

        if (!$token || !JwtHelper::validateToken($token) || $rol !== 2) {
            $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                   || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
                   || (!empty($accion) && !in_array($accion, ['Index', '']));

            if ($esAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
                exit();
            }
            header('Location: index.php?c=login&a=Index');
            exit();
        }
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function inputPost(): array
    {
        $raw     = file_get_contents('php://input');
        $decoded = $raw ? json_decode($raw, true) : null;
        return is_array($decoded) ? $decoded : $_POST;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?c=admincatalogoparametros&a=Index
    // ═══════════════════════════════════════════════════════════════════════
    public function Index(): void
    {
        $this->soloAdmin();
        $modelos = $this->model->obtenerTodosModelos();
        $grupos  = $this->model->obtenerGrupos();
        require_once 'view/admin/Catalogoparametros.php';
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Stats
    // ═══════════════════════════════════════════════════════════════════════
    public function Stats(): void
    {
        $this->soloAdmin();
        $this->json(['success' => true, 'stats' => $this->model->obtenerStats()]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Listar  [q, modelo_id, grupo, estado, sort, orden, page]
    // ═══════════════════════════════════════════════════════════════════════
    public function Listar(): void
    {
        $this->soloAdmin();

        $filtros = [
            'buscar'    => $_GET['q']          ?? '',
            'modelo_id' => $_GET['modelo_id']  ?? '',
            'grupo'     => $_GET['grupo']      ?? '',
            'estado'    => $_GET['estado']     ?? '',
            'sort'      => $_GET['sort']       ?? 'id',
            'orden'     => $_GET['orden']      ?? 'asc',
            'page'      => (int)($_GET['page'] ?? 1),
            'per_page'  => (int)($_GET['per_page'] ?? 20),
        ];

        $res = $this->model->listar($filtros);
        $this->json(['success' => true] + $res);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=ListarTodo  — para exportar sin paginación
    // ═══════════════════════════════════════════════════════════════════════
    public function ListarTodo(): void
    {
        $this->soloAdmin();

        $filtros = [
            'buscar'    => $_GET['q']         ?? '',
            'modelo_id' => $_GET['modelo_id'] ?? '',
            'grupo'     => $_GET['grupo']     ?? '',
            'estado'    => $_GET['estado']    ?? '',
        ];

        $datos = $this->model->listarTodo($filtros);
        $this->json(['success' => true, 'datos' => $datos]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Ver&id=X
    // ═══════════════════════════════════════════════════════════════════════
    public function Ver(): void
    {
        $this->soloAdmin();
        $id  = (int)($_GET['id'] ?? 0);
        $row = $this->model->obtenerPorId($id);
        if (!$row) { $this->json(['success' => false, 'message' => 'Registro no encontrado']); return; }
        $this->json(['success' => true, 'parametro' => $row]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Selectores
    // ═══════════════════════════════════════════════════════════════════════
    public function Selectores(): void
    {
        $this->soloAdmin();
        $this->json([
            'success' => true,
            'modelos' => $this->model->obtenerModelosActivos(),
            'grupos'  => $this->model->obtenerGrupos(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST  ?a=Crear
    // ═══════════════════════════════════════════════════════════════════════
    public function Crear(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d = $this->inputPost();

        if (empty($d['modelo_mr_id']))             { $this->json(['success' => false, 'message' => 'Seleccioná un modelo']); return; }
        if (empty(trim($d['grupo']  ?? '')))        { $this->json(['success' => false, 'message' => 'El grupo es requerido']); return; }
        if (empty(trim($d['valor']  ?? '')))        { $this->json(['success' => false, 'message' => 'El valor es requerido']); return; }

        $newId = $this->model->crear($d);
        if ($newId) {
            $this->json(['success' => true, 'id' => $newId, 'message' => 'Parámetro creado correctamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al crear — verificá que el modelo exista']);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST  ?a=Actualizar
    // ═══════════════════════════════════════════════════════════════════════
    public function Actualizar(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d  = $this->inputPost();
        $id = (int)($d['id'] ?? 0);

        if (!$id)                            { $this->json(['success' => false, 'message' => 'ID inválido']); return; }
        if (empty($d['modelo_mr_id']))        { $this->json(['success' => false, 'message' => 'Seleccioná un modelo']); return; }
        if (empty(trim($d['grupo']  ?? '')))  { $this->json(['success' => false, 'message' => 'El grupo es requerido']); return; }
        if (empty(trim($d['valor']  ?? '')))  { $this->json(['success' => false, 'message' => 'El valor es requerido']); return; }

        $ok = $this->model->actualizar($id, $d);
        $this->json([
            'success' => $ok,
            'message' => $ok ? 'Parámetro actualizado correctamente' : 'No se pudo actualizar',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST  ?a=CambiarEstado  { id }  — soft delete (toggle)
    // ═══════════════════════════════════════════════════════════════════════
    public function CambiarEstado(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d  = $this->inputPost();
        $id = (int)($d['id'] ?? 0);
        if (!$id) { $this->json(['success' => false, 'message' => 'ID inválido']); return; }

        $res = $this->model->cambiarEstado($id);
        $this->json([
            'success' => $res['ok'],
            'estado'  => $res['estado'],
            'message' => $res['ok']
                ? ($res['estado'] ? 'Parámetro activado' : 'Parámetro desactivado')
                : 'No se pudo cambiar el estado',
        ]);
    }
}