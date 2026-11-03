<?php
/*
 * controller/soporte.controller.php — lado CLIENTE
 *   GET  ?c=soporte&a=MisSolicitudes   → { abierta, historial }
 *   POST ?c=soporte&a=Enviar           (tipo, mensaje) → crea la solicitud si no hay otra abierta
 */
require_once 'model/soporte.php';
require_once 'controller/middleware.php';

class SoporteController
{
    private $model;
    private $usuarioId;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $usuario = AuthMiddleware::resguardar();
        $this->usuarioId = (int)($_SESSION['user_id'] ?? 0);
        if (!$usuario || !$this->usuarioId) {
            $this->json(['success' => false, 'message' => 'Sesión expirada. Vuelve a iniciar sesión.']);
        }
        $this->model = new Soporte();
    }

    private function json(array $data): void
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function MisSolicitudes(): void
    {
        $this->json([
            'success'   => true,
            'abierta'   => $this->model->abiertaDeUsuario($this->usuarioId),
            'historial' => $this->model->listarDeUsuario($this->usuarioId),
        ]);
    }

    public function Enviar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método no permitido']);
        }

        $tipo    = $_POST['tipo'] ?? 'pago_no_confirmado';
        $mensaje = trim($_POST['mensaje'] ?? '');
        if (!in_array($tipo, Soporte::TIPOS, true)) $tipo = 'otro';

        if (mb_strlen($mensaje) < 5) {
            $this->json(['success' => false, 'message' => Lang::get('soporte.err_mensaje')]);
        }
        $mensaje = mb_substr($mensaje, 0, 2000);

        // Una sola solicitud abierta a la vez: evita duplicados y mantiene el seguimiento claro
        $abierta = $this->model->abiertaDeUsuario($this->usuarioId);
        if ($abierta) {
            $this->json(['success' => true, 'ya_existia' => true, 'abierta' => $abierta]);
        }

        $this->model->crear($this->usuarioId, $tipo, $mensaje);
        $this->json(['success' => true, 'abierta' => $this->model->abiertaDeUsuario($this->usuarioId)]);
    }
}
