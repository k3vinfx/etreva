<?php
require_once 'model/adminModelosMR.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';
require_once 'model/recomendaciones.php'; // Para obtener atracciones

class AdminmodelosmrController
{
    private AdminModelosMR $model;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AdminModelosMR();
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
    //  GET  ?c=adminmodelosmr&a=Index
    // ═══════════════════════════════════════════════════════════════════════
    public function Index(): void
    {
        $this->soloAdmin();
        require_once 'view/admin/modelosMR.php';
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
    //  GET  ?a=Listar  [q, estado, sort, orden, page, per_page]
    // ═══════════════════════════════════════════════════════════════════════
    public function Listar(): void
    {
        $this->soloAdmin();

        $filtros = [
            'buscar'   => $_GET['q']          ?? '',
            'estado'   => $_GET['estado']     ?? '',
            'sort'     => $_GET['sort']       ?? 'id',
            'orden'    => $_GET['orden']      ?? 'desc',
            'page'     => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 10),
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
            'buscar' => $_GET['q']      ?? '',
            'estado' => $_GET['estado'] ?? '',
        ];

        $datos = $this->model->listarTodo($filtros);
        $this->json(['success' => true, 'datos' => $datos]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Resumen&id=X  — metadata + métricas sin arrays pesados
    // ═══════════════════════════════════════════════════════════════════════
    public function Resumen(): void
    {
        $this->soloAdmin();
        $id  = (int)($_GET['id'] ?? 0);
        $row = $this->model->obtenerResumen($id);
        if (!$row) { $this->json(['success' => false, 'message' => 'Modelo no encontrado']); return; }
        $this->json(['success' => true, 'modelo' => $row]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Alternativas&id=X&page=1&per_page=10
    //  Devuelve alternativas paginadas desde configuracion_json
    // ═══════════════════════════════════════════════════════════════════════
    public function Alternativas(): void
    {
        $this->soloAdmin();
        $id      = (int)($_GET['id']       ?? 0);
        $page    = (int)($_GET['page']     ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 10);

        if (!$id) { $this->json(['success' => false, 'message' => 'ID inválido']); return; }

        $res = $this->model->obtenerAlternativas($id, $page, $perPage);
        $this->json(['success' => true] + $res);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Dataset&id=X&page=1&per_page=15
    //  Devuelve registros paginados de dataset_entrenamiento
    // ═══════════════════════════════════════════════════════════════════════
    public function Dataset(): void
    {
        $this->soloAdmin();
        $id      = (int)($_GET['id']       ?? 0);
        $page    = (int)($_GET['page']     ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 15);

        if (!$id) { $this->json(['success' => false, 'message' => 'ID inválido']); return; }

        $res = $this->model->obtenerDataset($id, $page, $perPage);
        $this->json(['success' => true] + $res);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=MapeoInput&id=X
    // ═══════════════════════════════════════════════════════════════════════
    public function MapeoInput(): void
    {
        $this->soloAdmin();
        $id  = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->json(['success' => false, 'message' => 'ID inválido']); return; }

        $mapeo = $this->model->obtenerMapeoInput($id);
        $this->json(['success' => true, 'mapeo_input' => $mapeo]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=StatsModelo&id=X
    // ═══════════════════════════════════════════════════════════════════════
    public function StatsModelo(): void
    {
        $this->soloAdmin();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { $this->json(['success' => false, 'message' => 'ID inválido']); return; }

        $stats = $this->model->obtenerStatsModelo($id);
        $this->json(['success' => true, 'stats' => $stats]);
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

        if (empty(trim($d['nombre']   ?? ''))) { $this->json(['success' => false, 'message' => 'El nombre es requerido']); return; }
        if (empty(trim($d['version']  ?? ''))) { $this->json(['success' => false, 'message' => 'La versión es requerida']); return; }

        $newId = $this->model->crear($d);
        if ($newId) {
            $this->json(['success' => true, 'id' => $newId, 'message' => 'Modelo creado correctamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al crear el modelo']);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST  ?a=Actualizar
    // ═══════════════════════════════════════════════════════════════════════
    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=ExportarJson&id=   → descarga el modelo completo en JSON
    //  POST ?a=ImportarJson (id, archivo, simular=1|0) → valida / reemplaza el modelo
    // ═══════════════════════════════════════════════════════════════════════
    public function ExportarJson(): void
    {
        $this->soloAdmin();
        require_once 'model/modeloJson.php';
        $id = (int)($_GET['id'] ?? 0);
        $m = new ModeloJson();
        try { $datos = $m->exportar($id); $nombre = $m->nombreArchivo($id); }
        catch (RuntimeException $e) { http_response_code(404); exit($e->getMessage()); }
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Cache-Control: no-store');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit();
    }

    public function ImportarJson(): void
    {
        $this->soloAdmin();
        require_once 'model/modeloJson.php';
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Método no permitido.');
            $f = $_FILES['archivo'] ?? null;
            if (!$f || $f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('No se recibió el archivo (máx. ' . ini_get('upload_max_filesize') . ').');
            if (!preg_match('/\.json$/i', $f['name'])) throw new RuntimeException('El archivo debe ser .json');
            $res = (new ModeloJson())->importar((int)($_POST['id'] ?? 0), (string)file_get_contents($f['tmp_name']), ($_POST['simular'] ?? '1') === '1');
            echo json_encode(['success' => true] + $res, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    public function Actualizar(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d  = $this->inputPost();
        $id = (int)($d['id'] ?? 0);

        if (!$id)                           { $this->json(['success' => false, 'message' => 'ID inválido']); return; }
        if (array_key_exists('nombre', $d) && trim((string)$d['nombre']) === '') { $this->json(['success' => false, 'message' => 'El nombre es requerido']); return; }

        $ok = $this->model->actualizar($id, $d);
        $this->json([
            'success' => $ok,
            'message' => $ok ? 'Modelo actualizado correctamente' : 'No se pudo actualizar',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST  ?a=CambiarEstado  { id, estado }
    // ═══════════════════════════════════════════════════════════════════════
    public function CambiarEstado(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d  = $this->inputPost();
        $id = (int)($d['id']     ?? 0);
        $st = trim($d['estado']  ?? '');

        if (!$id) { $this->json(['success' => false, 'message' => 'ID inválido']); return; }
        if (!in_array($st, ['activo','inactivo','entrenando'])) {
            $this->json(['success' => false, 'message' => 'Estado inválido (activo|inactivo|entrenando)']); return;
        }

        $res = $this->model->cambiarEstado($id, $st);
        $this->json([
            'success' => $res['ok'],
            'estado'  => $res['estado'],
            'message' => $res['ok'] ? "Modelo marcado como $st" : 'No se pudo cambiar el estado',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=ListarAtracciones  — para el selector de dataset
    // ═══════════════════════════════════════════════════════════════════════
    public function ListarAtracciones(): void
    {
        $this->soloAdmin();
        $buscar = $_GET['q'] ?? '';
        $page   = (int)($_GET['page'] ?? 1);
        $perPage = 20;

        $recoModel = new Recomendaciones();
        $data = $recoModel->obtenerAtraccionesPaginadas($buscar, $page, $perPage);
        $this->json(['success' => true, 'data' => $data]);
    }
}