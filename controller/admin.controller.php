

<?php
require_once 'model/login.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class AdminController {

    private $model;

    /* =============================================================================
       Constructor — verifica ROL 2, redirige si no es admin
    ============================================================================= */
    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', 3600);
            session_set_cookie_params(3600);
            session_start();
        }

        // Proteger TODAS las acciones: solo rol 2
        $this->verificarAdmin();
        $this->model = new login();
    }

    /* =============================================================================
       Verifica que el usuario sea administrador (rol 2)
    ============================================================================= */
    private function verificarAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Sin sesión → al login
        if (!isset($_SESSION['token'])) {
            header('Location: index.php?c=login&a=Index');
            exit();
        }

        // Token inválido → al login
        if (!JwtHelper::validateToken($_SESSION['token'])) {
            header('Location: index.php?c=login&a=Index');
            exit();
        }

        // Rol diferente a 2 → al inicio del cliente
        $rol = $_SESSION['rol'] ?? null;
        if ((int)$rol === 3 && !in_array($_GET['a'] ?? '', ['Usuarios', 'VerUsuario'], true)) {
            header('Location: index.php?c=adminFinanzas&a=Index');
            exit();
        }
        if (!in_array((int)$rol, [2, 3], true)) {
            header('Location: index.php?c=admin&a=Inicio');
            exit();
        }
    }

    /* =============================================================================
       Dashboard principal del administrador
    ============================================================================= */
    public function Inicio() {
        require_once 'view/admin/inicio.php';
    }

    /* =============================================================================
       Estadísticas generales para los KPI del dashboard
       GET: index.php?c=admin&a=Stats
    ============================================================================= */
    public function Stats() {
        header('Content-Type: application/json');

        try {
            $usuarioLogueado = AuthMiddleware::resguardar();
            if (!$usuarioLogueado) {
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                return;
            }

            $stats = $this->model->obtenerStatsAdmin();

            echo json_encode([
                'success'             => true,
                'total_usuarios'      => $stats->total_usuarios      ?? 0,
                'usuarios_activos'    => $stats->usuarios_activos    ?? 0,
                'nuevos_hoy'          => $stats->nuevos_hoy          ?? 0,
                'pagos_activos'       => $stats->pagos_activos       ?? 0,
                'pagos_pendientes'    => $stats->pagos_pendientes    ?? 0,
                'total_atracciones'   => $stats->total_atracciones   ?? 0,
                'atracciones_activas' => $stats->atracciones_activas ?? 0,
                'total_recomendaciones' => $stats->total_recomendaciones ?? 0,
                'rec_este_mes'        => $stats->rec_este_mes        ?? 0,
                'pagos_semana'        => $stats->pagos_semana        ?? 0,
            ]);

        } catch (Exception $e) {
            error_log("Error en AdminController::Stats: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno']);
        }
    }

    /* =============================================================================
       Mini-stats para el sidebar (usuarios activos + pagos pendientes)
       GET: index.php?c=admin&a=MiniStats
    ============================================================================= */
    public function MiniStats() {
        header('Content-Type: application/json');

        try {
            $stats = $this->model->obtenerMiniStatsAdmin();
            echo json_encode([
                'usuarios_activos' => $stats->usuarios_activos ?? 0,
                'pagos_pendientes' => $stats->pagos_pendientes ?? 0,
            ]);
        } catch (Exception $e) {
            echo json_encode(['usuarios_activos' => 0, 'pagos_pendientes' => 0]);
        }
    }

    /* =============================================================================
       Últimos usuarios registrados para la tabla del dashboard
       GET: index.php?c=admin&a=UsuariosRecientes
    ============================================================================= */
    public function UsuariosRecientes() {
        header('Content-Type: application/json');

        try {
            $usuarios = $this->model->obtenerUsuariosRecientes(10);

            echo json_encode([
                'success'  => true,
                'usuarios' => $usuarios ?? [],
            ]);
        } catch (Exception $e) {
            error_log("Error en AdminController::UsuariosRecientes: " . $e->getMessage());
            echo json_encode(['success' => false, 'usuarios' => []]);
        }
    }

    /* =============================================================================
       Lista de pagos con estado pendiente_pago
       GET: index.php?c=admin&a=PagosPendientes
    ============================================================================= */
    public function PagosPendientes() {
        header('Content-Type: application/json');

        try {
            $pagos = $this->model->obtenerPagosPendientes();

            echo json_encode([
                'success' => true,
                'pagos'   => $pagos ?? [],
            ]);
        } catch (Exception $e) {
            error_log("Error en AdminController::PagosPendientes: " . $e->getMessage());
            echo json_encode(['success' => false, 'pagos' => []]);
        }
    }

    /* =============================================================================
       Distribución de planes activos
       GET: index.php?c=admin&a=DistribucionPlanes
    ============================================================================= */
    public function DistribucionPlanes() {
        header('Content-Type: application/json');

        try {
            $planes = $this->model->obtenerDistribucionPlanes();

            echo json_encode([
                'success' => true,
                'planes'  => $planes ?? [],
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'planes' => []]);
        }
    }

    /* =============================================================================
       Actividad reciente del sistema (logs)
       GET: index.php?c=admin&a=ActividadReciente
    ============================================================================= */



    
    public function ActividadReciente() {
        header('Content-Type: application/json');

        try {
            $actividad = $this->model->obtenerActividadReciente(15);

            echo json_encode([
                'success'   => true,
                'actividad' => $actividad ?? [],
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'actividad' => []]);
        }
    }

    /* =============================================================================
       Vista: Gestión de Usuarios
    ============================================================================= */
    public function Usuarios() {
        require_once 'view/admin/usuarios.php';
    }

    /* =============================================================================
       Ver un usuario (clic en "Usuarios recientes" del dashboard)
       Clientes (rol 1) → Clientes / Pagos; internos (rol 2/3) → Gestión de Usuarios,
       con el detalle de ese usuario ya desplegado.
    ============================================================================= */
    public function VerUsuario() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirigir('index.php?c=admin&a=Usuarios');

        $stmt = Database::Conectar()->prepare("SELECT rol FROM usuarios.usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $rol = (int)$stmt->fetchColumn();

        $this->redirigir(in_array($rol, [2, 3], true)
            ? "index.php?c=admingestionusuarios&a=Index&ver={$id}"
            : "index.php?c=admin&a=Usuarios&ver={$id}");
    }

    /* =============================================================================
       Vista: Gestión de Pagos
    ============================================================================= */
    public function Pagos() {
        // Los pagos se gestionan en Clientes / Pagos
        $this->redirigir('index.php?c=admin&a=Usuarios');
    }

    /* =============================================================================
       Ver un pago pendiente (clic en "Pagos pendientes" del dashboard)
       Abre Clientes / Pagos con el detalle del cliente para aprobar o rechazar.
    ============================================================================= */
    public function VerPago() {
        $id = (int)($_GET['id'] ?? 0);
        $this->redirigir('index.php?c=admin&a=Usuarios' . ($id > 0 ? "&ver={$id}" : ''));
    }

    private function redirigir(string $url) {
        header('Location: ' . $url);
        exit();
    }

    /* =============================================================================
       Vista: Gestión de Atracciones
    ============================================================================= */
    public function Atracciones() {
        require_once 'view/admin/atracciones.php';
    }

    /* =============================================================================
       Vista: Festividades
    ============================================================================= */
    public function Festividades() {
        $this->redirigir('index.php?c=adminatracciones&a=Index');
    }

    /* =============================================================================
       Vista: Entrenamiento del Modelo RNN
    ============================================================================= */
    public function Modelo() {
        $this->redirigir('index.php?c=adminmodelosmr&a=Index');
    }

    /* =============================================================================
       Vista: Log de Recomendaciones
    ============================================================================= */
    public function Recomendaciones() {
        require_once 'view/admin/recomendaciones.php';
    }

    /* =============================================================================
       Vista: Reportes / Estadísticas
    ============================================================================= */
    public function Reportes() {
        $this->redirigir('index.php?c=adminFinanzas&a=Index');
    }

    /* =============================================================================
       Vista: Reseñas
    ============================================================================= */
    public function Resenas() {
        require_once 'view/admin/resenas.php';
    }

    /* =============================================================================
       Vista: Configuración del sistema
    ============================================================================= */
    public function Configuracion() {
        require_once 'view/admin/configuracion.php';
    }
}
?>