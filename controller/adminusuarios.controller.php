<?php
/*
 * controller/AdminUsuariosController.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Controlador de gestión de clientes — panel admin.
 * Guard: verifica $_SESSION['rol'] === 2 + JWT válido en cada request.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * ROUTER (agregar en index.php):
 *   case 'adminUsuarios':
 *       require_once 'controller/AdminUsuariosController.php';
 *       $c = new AdminUsuariosController(); break;
 */

require_once 'model/adminUsuarios.php';
require_once 'controller/jwtHelper.php';

class AdminUsuariosController {

    private AdminUsuarios $model;

    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new AdminUsuarios();
        $this->soloAdmin();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  GUARD — solo rol 2
    //  NOTA: fetch() no envía X-Requested-With por defecto, por eso se detecta
    //  también por la acción GET (cualquier acción != Index es una llamada AJAX).
    // ═══════════════════════════════════════════════════════════════════════════

    private function soloAdmin(): void {
        $token = $_SESSION['token'] ?? null;
        $rol   = (int)($_SESSION['rol'] ?? 0);

        if (!$token || !JwtHelper::validateToken($token) || !in_array($rol, [2, 3], true)) {
            $accion = $_GET['a'] ?? '';
            // Si la acción no es la vista principal, es una llamada AJAX/fetch
            $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                   || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
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

    private function adminId(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    private function json(array $data): void {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  VISTA PRINCIPAL — index.php?c=adminUsuarios&a=Index
    // ═══════════════════════════════════════════════════════════════════════════

    public function Index(): void {
        require_once 'view/admin/usuarios.php';
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  LISTAR CLIENTES (AJAX)
    //  GET ?c=adminUsuarios&a=Listar[&estado=&pago_estado=&q=&page=]
    // ═══════════════════════════════════════════════════════════════════════════

    public function Listar(): void {
        $estado     = trim($_GET['estado']      ?? '');
        $pagoEstado = trim($_GET['pago_estado'] ?? '');
        $busqueda   = trim($_GET['q']           ?? '');
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $perPage    = 20;
        $offset     = ($page - 1) * $perPage;

        $clientes = $this->model->listarClientes($estado, $pagoEstado, $busqueda, $perPage, $offset);
        $total    = $this->model->contarClientes($estado, $pagoEstado, $busqueda);
        $pages    = max(1, (int)ceil($total / $perPage));

        $this->json([
            'success'  => true,
            'clientes' => $clientes,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => $pages,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  VER DETALLE — GET ?c=adminUsuarios&a=Ver&id=X
    // ═══════════════════════════════════════════════════════════════════════════

    public function Ver(): void {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) $this->json(['success' => false, 'message' => 'ID inválido']);

        $cliente = $this->model->obtenerClientePorId($id);
        if (!$cliente) $this->json(['success' => false, 'message' => 'Usuario no encontrado']);

        $config = json_decode($cliente->configuracion_json ?? '{}', true) ?? [];
        $rp     = $config['registro_pago'] ?? [];

        $this->json([
            'success' => true,
            'cliente' => [
                'id'               => (int)$cliente->id,
                'nombre'           => $cliente->nombre,
                'apellido'         => $cliente->apellido,
                'email'            => $cliente->email,
                'telefono'         => $cliente->telefono,
                'estado'           => $cliente->estado,
                'pago_estado'      => $rp['estado']             ?? null,
                'pago_verificado'  => $rp['verificado']         ?? null,
                'pago_plan'        => $rp['tipo_plan']          ?? null,
                'pago_monto'       => $rp['monto']              ?? null,
                'pago_periodo'     => $rp['periodo']            ?? null,
                'pago_fecha'       => $rp['fecha_pago']         ?? null,
                'pago_vencimiento' => $rp['fecha_vencimiento']  ?? null,
                'pago_comprobante' => $rp['ruta_comprobante']   ?? null,
                'pago_motivo'      => $rp['motivo_pago']        ?? null,
                'pago_dias_totales'=> $rp['dias_totales']       ?? null,
                'pago_carryover'   => $rp['dias_carryover']     ?? null,
                'aprobado_por'     => $rp['aprobado_por']       ?? null,
                'fecha_aprobacion' => $rp['fecha_aprobacion']   ?? null,
                'motivo_rechazo'   => $rp['motivo_rechazo']     ?? null,
                'historial_estado' => $config['historial_estado']        ?? [],
                'emails_enviados'  => (int)($config['emails_oferta_enviados'] ?? 0),
                'ultimo_email'     => $config['ultimo_email_oferta'] ?? null,
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  APROBAR PAGO — POST ?c=adminUsuarios&a=AprobarPago
    //  Body JSON: { user_id: int }
    //  Lógica: pendiente_pago + verificado:false → activo_pagado + verificado:true
    // ═══════════════════════════════════════════════════════════════════════════

    public function AprobarPago(): void {
        $input  = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = (int)($input['user_id'] ?? $_POST['user_id'] ?? 0);

        if (!$userId) $this->json(['success' => false, 'message' => 'ID de usuario inválido']);

        $cliente = $this->model->obtenerClientePorId($userId);
        if (!$cliente) $this->json(['success' => false, 'message' => 'Usuario no encontrado']);

        // Leer estado actual del JSON
        $config = json_decode($cliente->configuracion_json ?? '{}', true) ?? [];
        $rp     = $config['registro_pago'] ?? [];

        if (($rp['estado'] ?? '') !== 'pendiente_pago') {
            $this->json(['success' => false, 'message' => 'El pago no está en estado pendiente']);
        }

        // Advertencia si ya pasaron 2 días (no bloquea)
        $alerta = '';
        if (!empty($rp['fecha_pago'])) {
            $tz        = new DateTimeZone('America/La_Paz');
            $dias      = (int)(new DateTime('now', $tz))->diff(new DateTime($rp['fecha_pago'], $tz))->days;
            if ($dias >= 2) $alerta = "Aprobación tardía: han pasado {$dias} días desde el pago.";
        }

        $ok = $this->model->aprobarPago($userId, $this->adminId());

        $this->json([
            'success' => $ok,
            'message' => $ok
                ? 'Pago aprobado. El cliente ya puede acceder al sistema.'
                : 'Error al aprobar el pago',
            'alerta'  => $alerta,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  RECHAZAR PAGO — POST ?c=adminUsuarios&a=RechazarPago
    //  Body JSON: { user_id: int, motivo: string }
    //  Lógica: pendiente_pago → rechazado + verificado:false (sin cambio)
    // ═══════════════════════════════════════════════════════════════════════════

    public function RechazarPago(): void {
        $input  = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = (int)($input['user_id'] ?? $_POST['user_id'] ?? 0);
        $motivo = trim($input['motivo']   ?? $_POST['motivo']  ?? '');

        if (!$userId) $this->json(['success' => false, 'message' => 'ID inválido']);
        if (!$motivo) $this->json(['success' => false, 'message' => 'El motivo de rechazo es obligatorio']);

        $ok = $this->model->rechazarPago($userId, $this->adminId(), $motivo);

        $this->json([
            'success' => $ok,
            'message' => $ok
                ? 'Pago rechazado. Se guardó el motivo en el registro del cliente.'
                : 'Error al rechazar el pago',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CAMBIAR ESTADO DE CUENTA — POST ?c=adminUsuarios&a=CambiarEstado
    //  Body JSON: { user_id: int, estado: 'activo'|'inactivo'|'suspendido', motivo: string }
    // ═══════════════════════════════════════════════════════════════════════════

    public function CambiarEstado(): void {
        $input       = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId      = (int)($input['user_id'] ?? $_POST['user_id'] ?? 0);
        $nuevoEstado = trim($input['estado']   ?? $_POST['estado']  ?? '');
        $motivo      = trim($input['motivo']   ?? $_POST['motivo']  ?? '');

        if (!$userId)      $this->json(['success' => false, 'message' => 'ID inválido']);
        if (!$nuevoEstado) $this->json(['success' => false, 'message' => 'Estado inválido']);

        if (!in_array($nuevoEstado, ['activo', 'inactivo', 'suspendido'])) {
            $this->json(['success' => false, 'message' => "Estado '{$nuevoEstado}' no válido"]);
        }

        if (in_array($nuevoEstado, ['inactivo', 'suspendido']) && !$motivo) {
            $this->json(['success' => false, 'message' => 'Debes indicar el motivo de la acción']);
        }

        $ok = $this->model->cambiarEstadoCuenta($userId, $nuevoEstado, $this->adminId(), $motivo);

        $labels = ['activo' => 'reactivada', 'inactivo' => 'desactivada', 'suspendido' => 'suspendida'];

        $this->json([
            'success' => $ok,
            'message' => $ok
                ? "Cuenta {$labels[$nuevoEstado]} correctamente."
                : 'Error al cambiar el estado de la cuenta',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  ENVIAR EMAIL OFERTA — POST ?c=adminUsuarios&a=EnviarOferta
    //  Body JSON: { user_id: int }
    // ═══════════════════════════════════════════════════════════════════════════

    public function EnviarOferta(): void {
        $input  = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = (int)($input['user_id'] ?? $_POST['user_id'] ?? 0);

        if (!$userId) $this->json(['success' => false, 'message' => 'ID inválido']);

        $resultado = $this->model->enviarEmailOferta($userId, $this->adminId());

        $this->json([
            'success' => $resultado['ok'],
            'message' => $resultado['msg'],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  PAGOS PENDIENTES — GET ?c=adminUsuarios&a=PagosPendientes
    //  Devuelve todos los pagos con estado 'pendiente_pago' + flag 'urgente' si >= 2 días
    // ═══════════════════════════════════════════════════════════════════════════

    public function PagosPendientes(): void {
        $pagos = $this->model->obtenerPagosPendientesAdmin();
        $this->json([
            'success' => true,
            'pagos'   => $pagos,
            'total'   => count($pagos),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  INACTIVOS — GET ?c=adminUsuarios&a=Inactivos
    // ═══════════════════════════════════════════════════════════════════════════

    public function Inactivos(): void {
        $clientes = $this->model->obtenerClientesInactivos(50);
        $this->json([
            'success'  => true,
            'clientes' => $clientes,
            'total'    => count($clientes),
        ]);
    }
}