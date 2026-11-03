<?php
/*
 * controller/gestionUsuariosController.php
 * Gestión de usuarios internos: Administrador (rol 2) y Finanzas (rol 3)
 *
 * Rutas disponibles (index.php?c=gestionUsuarios&a=...):
 *
 *  GET  Listar         → lista paginada con filtros
 *  GET  Ver&id=X       → detalle de un usuario
 *  POST Crear          → crea usuario admin o finanzas
 *  POST Editar         → edita datos básicos
 *  POST CambiarRol     → cambia entre rol 2 y 3
 *  POST CambiarEstado  → activo | inactivo | suspendido
 *  POST ResetPassword  → resetea contraseña (directa o por correo)
 *  POST ReenviarVerificacion → reenvía correo de verificación
 *  POST Eliminar       → desactiva la cuenta (soft delete)
 *  GET  Stats          → estadísticas para el panel
 *  GET  Index          → carga la vista principal (solo rol 2)
 */

require_once 'model/adminGestionUsuarios.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class AdminGestionUsuariosController
{
    private AdminGestionUsuarios $model;
    private int             $adminId = 0;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new AdminGestionUsuarios();
    }

    // ══════════════════════════════════════════════════════════════════
    //  HELPERS INTERNOS
    // ══════════════════════════════════════════════════════════════════

    /** Responde JSON y termina la ejecución */
    private function json(array $data): void
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /** Valida que el usuario logueado sea rol 2 (admin). Devuelve el ID o null. */
    private function soloAdmin(): ?int
    {
        $u = AuthMiddleware::resguardar();
        if (!$u) return null;

        $rol = is_array($u) ? (int)($u['rol'] ?? 0) : (int)($u->rol ?? 0);
        if ($rol !== 2) return null;

        return is_array($u) ? (int)($u['id'] ?? 0) : (int)($u->id ?? 0);
    }

    /** Lee el body JSON de la petición */
    private function body(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : [];
    }

    // ══════════════════════════════════════════════════════════════════
    //  VISTA PRINCIPAL
    //  GET index.php?c=gestionUsuarios&a=Index
    //  Solo accesible para rol 2
    // ══════════════════════════════════════════════════════════════════
    public function Index(): void
    {
        $u = AuthMiddleware::resguardar();
        if (!$u) {
            header('Location: index.php?c=login&a=Index');
            exit();
        }

        $rol = is_array($u) ? (int)($u['rol'] ?? 0) : (int)($u->rol ?? 0);
        if ($rol !== 2) {
            header('Location: index.php?c=admin&a=Inicio');
            exit();
        }

        require_once 'view/admin/gestion_usuarios.php';
    }

    // ══════════════════════════════════════════════════════════════════
    //  LISTAR
    //  GET index.php?c=gestionUsuarios&a=Listar
    //  Params: q, rol (2|3), estado, page
    // ══════════════════════════════════════════════════════════════════
    public function Listar(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        $q      = trim($_GET['q']      ?? '');
        $rol    = (int)($_GET['rol']   ?? 0);
        $estado = trim($_GET['estado'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));

        $res = $this->model->listar($q, $rol, $estado, $page);
        $soySuper = $this->model->esSuper($adminId);
        if (!empty($res['usuarios'])) foreach ($res['usuarios'] as &$u)
            $u['permisos'] = $this->model->permisos((int)$u['id'], (int)$u['rol'], !empty($u['super_admin']), $adminId, $soySuper);
        unset($u);
        $res['yo'] = ['id' => $adminId, 'super_admin' => $soySuper];
        $this->json($res);
    }

    // ══════════════════════════════════════════════════════════════════
    //  VER DETALLE
    //  GET index.php?c=gestionUsuarios&a=Ver&id=X
    // ══════════════════════════════════════════════════════════════════
    public function Ver(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido']);
        }

        $res = $this->model->ver($id);
        if (!empty($res['success'])) {
            $u = $res['usuario'];
            $res['usuario']['permisos'] = $this->model->permisos((int)$u['id'], (int)$u['rol'], !empty($u['super_admin']), $adminId);
        }
        $this->json($res);
    }

    // POST a=CambiarSuper  { id, super: true|false }  — solo super administradores
    public function CambiarSuper(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) $this->json(['success' => false, 'message' => 'No autorizado']);
        $data = $this->body();
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) $this->json(['success' => false, 'message' => 'ID inválido']);
        $this->json($this->model->cambiarSuper($id, !empty($data['super']), $adminId));
    }

    // ══════════════════════════════════════════════════════════════════
    //  CREAR
    //  POST index.php?c=gestionUsuarios&a=Crear
    //  Body JSON: { nombre, apellido, email, password, rol (2|3),
    //               telefono, genero, fecha_nacimiento, nacionalidad,
    //               departamento, descripcion }
    // ══════════════════════════════════════════════════════════════════
    public function Crear(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $data = $this->body();

        // Validaciones básicas
        $errores = [];
        if (empty(trim($data['nombre'] ?? '')))   $errores[] = 'El nombre es obligatorio';
        if (empty(trim($data['apellido'] ?? ''))) $errores[] = 'El apellido es obligatorio';
        if (empty(trim($data['email'] ?? '')) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Email inválido';
        }
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres';
        }
        if (!in_array((int)($data['rol'] ?? 0), [2, 3])) {
            $errores[] = 'El rol debe ser 2 (Administrador) o 3 (Finanzas)';
        }

        if (!empty($errores)) {
            $this->json(['success' => false, 'message' => implode('. ', $errores)]);
        }

        $this->json($this->model->crear($data, $adminId));
    }

    // ══════════════════════════════════════════════════════════════════
    //  EDITAR
    //  POST index.php?c=gestionUsuarios&a=Editar
    //  Body JSON: { id, nombre, apellido, telefono, genero,
    //               fecha_nacimiento, nacionalidad, departamento, descripcion }
    // ══════════════════════════════════════════════════════════════════
    public function Editar(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $data = $this->body();
        $id   = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido']);
        }
        if (empty(trim($data['nombre'] ?? ''))) {
            $this->json(['success' => false, 'message' => 'El nombre es obligatorio']);
        }

        $this->json($this->model->editar($id, $data, $adminId));
    }

    // ══════════════════════════════════════════════════════════════════
    //  CAMBIAR ROL
    //  POST index.php?c=gestionUsuarios&a=CambiarRol
    //  Body JSON: { id, rol }
    // ══════════════════════════════════════════════════════════════════
    public function CambiarRol(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $data    = $this->body();
        $id      = (int)($data['id']  ?? 0);
        $nuevoRol = (int)($data['rol'] ?? 0);

        if ($id <= 0 || !in_array($nuevoRol, [2, 3])) {
            $this->json(['success' => false, 'message' => 'Datos inválidos']);
        }

        // No puede cambiar su propio rol
        if ($id === $adminId) {
            $this->json(['success' => false, 'message' => 'No puedes cambiar tu propio rol']);
        }

        $this->json($this->model->cambiarRol($id, $nuevoRol, $adminId));
    }

    // ══════════════════════════════════════════════════════════════════
    //  CAMBIAR ESTADO
    //  POST index.php?c=gestionUsuarios&a=CambiarEstado
    //  Body JSON: { id, estado, motivo }
    // ══════════════════════════════════════════════════════════════════
    public function CambiarEstado(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $data       = $this->body();
        $id         = (int)($data['id']     ?? 0);
        $nuevoEstado = trim($data['estado'] ?? '');
        $motivo     = trim($data['motivo']  ?? '');

        if ($id <= 0 || empty($nuevoEstado)) {
            $this->json(['success' => false, 'message' => 'Datos inválidos']);
        }
        if ($id === $adminId) {
            $this->json(['success' => false, 'message' => 'No puedes cambiar el estado de tu propia cuenta']);
        }
        if (in_array($nuevoEstado, ['inactivo','suspendido']) && empty($motivo)) {
            $this->json(['success' => false, 'message' => 'El motivo es obligatorio para este cambio de estado']);
        }

        $this->json($this->model->cambiarEstado($id, $nuevoEstado, $motivo, $adminId));
    }

    // ══════════════════════════════════════════════════════════════════
    //  RESET PASSWORD
    //  POST index.php?c=gestionUsuarios&a=ResetPassword
    //  Body JSON: { id, modo ('directa'|'correo'), password? }
    // ══════════════════════════════════════════════════════════════════
    public function ResetPassword(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $data      = $this->body();
        $id        = (int)($data['id']       ?? 0);
        $modo      = trim($data['modo']      ?? 'correo');
        $nuevaPass = trim($data['password']  ?? '');

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido']);
        }
        if (!in_array($modo, ['directa', 'correo'])) {
            $this->json(['success' => false, 'message' => 'Modo inválido (directa|correo)']);
        }
        if ($modo === 'directa' && strlen($nuevaPass) < 8) {
            $this->json(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres']);
        }

        $this->json($this->model->resetearPassword($id, $modo, $adminId, $nuevaPass));
    }

    // ══════════════════════════════════════════════════════════════════
    //  REENVIAR VERIFICACIÓN
    //  POST index.php?c=gestionUsuarios&a=ReenviarVerificacion
    //  Body JSON: { id }
    // ══════════════════════════════════════════════════════════════════
    public function ReenviarVerificacion(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $data = $this->body();
        $id   = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido']);
        }

        $this->json($this->model->reenviarVerificacion($id, $adminId));
    }

    // ══════════════════════════════════════════════════════════════════
    //  ELIMINAR (soft delete)
    //  POST index.php?c=gestionUsuarios&a=Eliminar
    //  Body JSON: { id }
    // ══════════════════════════════════════════════════════════════════
    public function Eliminar(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $data = $this->body();
        $id   = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido']);
        }

        $this->json($this->model->eliminar($id, $adminId));
    }

    // ══════════════════════════════════════════════════════════════════
    //  STATS
    //  GET index.php?c=gestionUsuarios&a=Stats
    // ══════════════════════════════════════════════════════════════════
    public function Stats(): void
    {
        $adminId = $this->soloAdmin();
        if (!$adminId) {
            $this->json(['success' => false, 'message' => 'No autorizado']);
        }

        $this->json($this->model->obtenerStats());
    }
}