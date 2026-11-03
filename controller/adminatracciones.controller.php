<?php
require_once 'model/adminAtracciones.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class AdminatraccionesController
{
    private AdminAtracciones $model;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AdminAtracciones();
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
        $raw = file_get_contents('php://input');
        if (!$raw) return $_POST;
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : $_POST;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  index.php?c=adminatracciones&a=Index
    // ═══════════════════════════════════════════════════════════════════════
    public function Index(): void
    {
        $this->soloAdmin();
        $categorias = $this->model->obtenerCategorias();
        require_once 'view/admin/atracciones.php';
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  Fotos de una atracción
    //  GET  a=Fotos&id=            → estado de la galería
    //  POST a=FotoSubir (id, fotos[])  a=FotoQuitar (id, n)  a=FotoPrincipal (id, n)
    //       a=FotoRestaurar (id, archivo)  a=FotoBuscar (id)
    // ═══════════════════════════════════════════════════════════════════════
    public function Fotos(): void
    {
        $this->soloAdmin();
        require_once 'model/imagenAtraccion.php';
        $id = (int)($_GET['id'] ?? 0);
        $this->json($id > 0 ? ['success' => true] + ImagenAtraccion::adminListar($id) : ['success' => false, 'message' => 'ID inválido']);
    }

    public function FotoSubir(): void
    {
        $this->accionFoto(function (int $id) {
            $f = $_FILES['fotos'] ?? null;
            if (!$f) throw new RuntimeException('No se recibió ninguna imagen (máx. ' . ini_get('upload_max_filesize') . ' por archivo).');
            $subidas = 0; $errores = [];
            foreach ((array)$f['tmp_name'] as $i => $tmp) {
                $nombre = ((array)$f['name'])[$i] ?? 'imagen';
                if (((array)$f['error'])[$i] !== UPLOAD_ERR_OK) { $errores[] = "$nombre: no se pudo subir"; continue; }
                try { ImagenAtraccion::adminSubir($id, $tmp); $subidas++; }
                catch (RuntimeException $e) { $errores[] = "$nombre: " . $e->getMessage(); }
            }
            return ['subidas' => $subidas, 'errores' => $errores];
        });
    }

    public function FotoQuitar(): void
    {
        $this->accionFoto(fn(int $id, array $p) => ImagenAtraccion::adminQuitar($id, (int)($p['n'] ?? 0)));
    }

    public function FotoPrincipal(): void
    {
        $this->accionFoto(fn(int $id, array $p) => ImagenAtraccion::adminPrincipal($id, (int)($p['n'] ?? 0)));
    }

    public function FotoRestaurar(): void
    {
        $this->accionFoto(fn(int $id, array $p) => ['n' => ImagenAtraccion::adminRestaurar($id, (string)($p['archivo'] ?? ''))]);
    }

    public function FotoQuitarRef(): void
    {
        $this->accionFoto(fn(int $id, array $p) => ImagenAtraccion::adminQuitarReferencial((string)($p['archivo'] ?? '')));
    }

    public function FotoBuscar(): void
    {
        set_time_limit(120);
        $this->accionFoto(fn(int $id) => ['nuevas' => ImagenAtraccion::adminBuscar($id)]);
    }

    /** Valida sesión, método e id; ejecuta la acción y responde con el estado actualizado de la galería */
    private function accionFoto(callable $accion): void
    {
        $this->soloAdmin();
        require_once 'model/imagenAtraccion.php';
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Método no permitido.');
            $p  = $this->inputPost();
            $id = (int)($p['id'] ?? 0);
            if ($id <= 0) throw new RuntimeException('ID inválido.');
            $extra = $accion($id, $p);
            $this->json(['success' => true] + (is_array($extra) ? $extra : []) + ImagenAtraccion::adminListar($id));
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
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
    //  GET  ?a=Listar  [q, estado, categoria_id, ciudad, sort, orden, page, per_page]
    // ═══════════════════════════════════════════════════════════════════════
    public function Listar(): void
    {
        $this->soloAdmin();

        $filtros = [
            'buscar'       => $_GET['q']            ?? '',
            'estado'       => $_GET['estado']       ?? '',
            'categoria_id' => $_GET['categoria_id'] ?? '',
            'ciudad'       => $_GET['ciudad']       ?? '',
            'sort'         => $_GET['sort']         ?? 'id',
            'orden'        => $_GET['orden']        ?? 'asc',
            'page'         => (int)($_GET['page']   ?? 1),
            'per_page'     => (int)($_GET['per_page'] ?? 20),
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
        $this->json(['success' => true, 'atraccion' => $row]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  GET  ?a=Categorias
    // ═══════════════════════════════════════════════════════════════════════
    public function Categorias(): void
    {
        $this->soloAdmin();
        $this->json(['success' => true, 'categorias' => $this->model->obtenerCategorias()]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST ?a=Crear
    // ═══════════════════════════════════════════════════════════════════════
    public function Crear(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d = $this->inputPost();
        if (empty($d['nombre'])) {
            $this->json(['success' => false, 'message' => 'El nombre es obligatorio']); return;
        }

        $newId = $this->model->crear($d);
        if ($newId) {
            $this->json(['success' => true, 'id' => $newId, 'message' => 'Atracción creada correctamente']);
        } else {
            $this->json(['success' => false, 'message' => 'Error al crear la atracción']);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST ?a=Actualizar  { id, ...campos }
    // ═══════════════════════════════════════════════════════════════════════
    public function Actualizar(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d  = $this->inputPost();
        $id = (int)($d['id'] ?? 0);

        if (!$id)            { $this->json(['success' => false, 'message' => 'ID inválido']); return; }
        if (empty($d['nombre'])) { $this->json(['success' => false, 'message' => 'El nombre es obligatorio']); return; }

        $ok = $this->model->actualizar($id, $d);
        $this->json([
            'success' => $ok,
            'message' => $ok ? 'Atracción actualizada correctamente' : 'No se pudo actualizar',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST ?a=ToggleEstado  { id }
    // ═══════════════════════════════════════════════════════════════════════
    public function ToggleEstado(): void
    {
        $this->soloAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']); return;
        }

        $d  = $this->inputPost();
        $id = (int)($d['id'] ?? 0);
        if (!$id) { $this->json(['success' => false, 'message' => 'ID inválido']); return; }

        $res = $this->model->toggleEstado($id);
        $this->json([
            'success'      => $res['ok'],
            'nuevo_estado' => $res['nuevo_estado'] ?? '',
            'message'      => $res['ok']
                ? 'Estado cambiado a ' . ($res['nuevo_estado'] ?? '')
                : 'No se pudo cambiar el estado',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POST ?a=Eliminar  { id }
    //  Solo se puede eliminar si estado = 'inactivo'
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
            'message' => $ok
                ? 'Atracción eliminada definitivamente'
                : 'No se puede eliminar: primero desactivá la atracción',
        ]);
    }
}