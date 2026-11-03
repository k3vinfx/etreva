<?php
require_once 'model/adminContextoRelacion.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class AdmincontextorelacionController
{
    private AdminContextoRelacion $model;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AdminContextoRelacion();
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
    //  GET  ?c=admincontextorelacion&a=Index
    // ═══════════════════════════════════════════════════════════════════════
    public function Index(): void
    {
        $this->soloAdmin();
        $modelos = $this->model->obtenerTodosModelos();
        $tipos   = $this->model->obtenerTipos();
        require_once 'view/admin/contextoRelacion.php';
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
    //  GET  ?a=Listar  [q, modelo_id, tipo_contexto, estado, sort, orden, page]
    // ═══════════════════════════════════════════════════════════════════════
    public function Listar(): void
    {
        $this->soloAdmin();

        $filtros = [
            'buscar'        => $_GET['q']             ?? '',
            'modelo_id'     => $_GET['modelo_id']     ?? '',
            'tipo_contexto' => $_GET['tipo_contexto'] ?? '',
            'estado'        => $_GET['estado']        ?? '',
            'sort'          => $_GET['sort']          ?? 'id',
            'orden'         => $_GET['orden']         ?? 'asc',
            'page'          => (int)($_GET['page']    ?? 1),
            'per_page'      => (int)($_GET['per_page'] ?? 20),
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
            'buscar'        => $_GET['q']             ?? '',
            'modelo_id'     => $_GET['modelo_id']     ?? '',
            'tipo_contexto' => $_GET['tipo_contexto'] ?? '',
            'estado'        => $_GET['estado']        ?? '',
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
        $this->json(['success' => true, 'contexto' => $row]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Selectores  — dropdowns para modal
    // ═══════════════════════════════════════════════════════════════════════
    public function Selectores(): void
    {
        $this->soloAdmin();
        $this->json([
            'success' => true,
            'modelos' => $this->model->obtenerModelosActivos(),
            'tipos'   => $this->model->obtenerTipos(),
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

        if (empty($d['modelo_mr_id'])) {
            $this->json(['success' => false, 'message' => 'Seleccioná un modelo']); return;
        }
        if (empty(trim($d['tipo_contexto'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'El tipo de contexto es requerido']); return;
        }
        if (empty(trim($d['valor'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'El valor es requerido']); return;
        }

        $newId = $this->model->crear($d);
        if ($newId) {
            $this->json(['success' => true, 'id' => $newId, 'message' => 'Contexto creado correctamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al crear — verificá que el modelo exista o que no sea un duplicado']);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST  ?a=Actualizar  { id, ...campos }
    // ═══════════════════════════════════════════════════════════════════════
    public function Actualizar(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d  = $this->inputPost();
        $id = (int)($d['id'] ?? 0);

        if (!$id)                                 { $this->json(['success' => false, 'message' => 'ID inválido']); return; }
        if (empty($d['modelo_mr_id']))             { $this->json(['success' => false, 'message' => 'Seleccioná un modelo']); return; }
        if (empty(trim($d['tipo_contexto'] ?? ''))) { $this->json(['success' => false, 'message' => 'El tipo de contexto es requerido']); return; }
        if (empty(trim($d['valor'] ?? '')))         { $this->json(['success' => false, 'message' => 'El valor es requerido']); return; }

        $ok = $this->model->actualizar($id, $d);
        $this->json([
            'success' => $ok,
            'message' => $ok ? 'Contexto actualizado correctamente' : 'No se pudo actualizar',
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
                ? ($res['estado'] ? 'Contexto activado' : 'Contexto desactivado')
                : 'No se pudo cambiar el estado',
        ]);
    }
}