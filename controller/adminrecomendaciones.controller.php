<?php
require_once 'model/adminRecomendaciones.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class AdminrecomendacionesController
{
    private AdminRecomendaciones $model;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AdminRecomendaciones();
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
    //  GET  ?c=adminrecomendaciones&a=Index
    // ═══════════════════════════════════════════════════════════════════════
    public function Index(): void
    {
        $this->soloAdmin();
        $modelos     = $this->model->obtenerTodosModelos();
        $atracciones = $this->model->obtenerAtraccionesActivas();
        require_once 'view/admin/recomendaciones.php';
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
    //  GET  ?a=Listar
    // ═══════════════════════════════════════════════════════════════════════
    public function Listar(): void
    {
        $this->soloAdmin();

        $filtros = [
            'buscar'    => $_GET['q']          ?? '',
            'modelo_id' => $_GET['modelo_id']  ?? '',
            'feedback'  => $_GET['feedback']   ?? '',
            'score_min' => $_GET['score_min']  ?? '',
            'sort'      => $_GET['sort']       ?? 'id',
            'orden'     => $_GET['orden']      ?? 'desc',
            'page'      => (int)($_GET['page'] ?? 1),
            'per_page'  => (int)($_GET['per_page'] ?? 20),
        ];

        $res = $this->model->listar($filtros);
        $this->json(['success' => true] + $res);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Ver&id=X
    // ═══════════════════════════════════════════════════════════════════════
    public function Ver(): void
    {
        $this->soloAdmin();
        $id  = (int)($_GET['id'] ?? 0);
        $row = $this->model->obtenerPorId($id);
        if (!$row) { $this->json(['success' => false, 'message' => 'No encontrada']); return; }
        $this->json(['success' => true, 'recomendacion' => $row]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Selectores
    // ═══════════════════════════════════════════════════════════════════════
    public function Selectores(): void
    {
        $this->soloAdmin();
        $this->json([
            'success'     => true,
            'atracciones' => $this->model->obtenerAtraccionesActivas(),
            'modelos'     => $this->model->obtenerModelosActivos(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  ★ NUEVO — GET  ?a=DatosPorModelo&modelo_id=X
    //  Devuelve parámetros (razón) y contexto agrupados para el modelo dado.
    //  Se llama desde el modal al cambiar el selector de modelo.
    // ═══════════════════════════════════════════════════════════════════════
    public function DatosPorModelo(): void
    {
        $this->soloAdmin();

        $modeloId = (int)($_GET['modelo_id'] ?? 0);
        if (!$modeloId) {
            $this->json(['success' => false, 'message' => 'modelo_id requerido']);
            return;
        }

        $this->json([
            'success'    => true,
            'parametros' => $this->model->obtenerParametrosPorModelo($modeloId),
            'contexto'   => $this->model->obtenerContextoPorModelo($modeloId),
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

        if (empty($d['atraccion_id'])) {
            $this->json(['success' => false, 'message' => 'Seleccioná una atracción']); return;
        }
        if (empty($d['modelo_mr_id'])) {
            $this->json(['success' => false, 'message' => 'Seleccioná un modelo']); return;
        }

        $d['razon_recomendacion']    = $this->parseJsonField($d['razon_recomendacion']    ?? '{}');
        $d['contexto_recomendacion'] = $this->parseJsonField($d['contexto_recomendacion'] ?? '{}');

        $newId = $this->model->crear($d);
        if ($newId) {
            $this->json(['success' => true, 'id' => $newId, 'message' => 'Recomendación creada correctamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al crear — verificá que la atracción y el modelo existan']);
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

        if (!$id)                      { $this->json(['success' => false, 'message' => 'ID inválido']); return; }
        if (empty($d['atraccion_id'])) { $this->json(['success' => false, 'message' => 'Seleccioná una atracción']); return; }
        if (empty($d['modelo_mr_id'])) { $this->json(['success' => false, 'message' => 'Seleccioná un modelo']); return; }

        $d['razon_recomendacion']    = $this->parseJsonField($d['razon_recomendacion']    ?? '{}');
        $d['contexto_recomendacion'] = $this->parseJsonField($d['contexto_recomendacion'] ?? '{}');

        $ok = $this->model->actualizar($id, $d);
        $this->json([
            'success' => $ok,
            'message' => $ok
                ? 'Recomendación actualizada correctamente'
                : 'No se pudo actualizar — verificá que la atracción y el modelo existan',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST  ?a=Eliminar
    // ═══════════════════════════════════════════════════════════════════════
    public function Eliminar(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d  = $this->inputPost();
        $id = (int)($d['id'] ?? 0);
        if (!$id) { $this->json(['success' => false, 'message' => 'ID inválido']); return; }

        $ok = $this->model->eliminar($id);
        $this->json([
            'success' => $ok,
            'message' => $ok ? 'Recomendación eliminada' : 'No se pudo eliminar',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST  ?a=LimpiarFeedback
    // ═══════════════════════════════════════════════════════════════════════
    public function LimpiarFeedback(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d  = $this->inputPost();
        $id = (int)($d['id'] ?? 0);
        if (!$id) { $this->json(['success' => false, 'message' => 'ID inválido']); return; }

        $ok = $this->model->limpiarFeedback($id);
        $this->json([
            'success' => $ok,
            'message' => $ok ? 'Feedback reseteado' : 'No se pudo resetear',
        ]);
    }

    // ─── Helper ──────────────────────────────────────────────────────────────
    private function parseJsonField($val): array
    {
        if (is_array($val))  return $val;
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) return $decoded;
        }
        return [];
    }
}