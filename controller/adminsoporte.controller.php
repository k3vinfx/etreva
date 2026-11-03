<?php
/*
 * controller/adminsoporte.controller.php — lado ADMINISTRACIÓN
 *   GET  ?c=adminsoporte&a=Index               → vista bandeja de solicitudes
 *   GET  ?c=adminsoporte&a=Listar[&estado=]    → JSON
 *   GET  ?c=adminsoporte&a=Contar              → JSON { abiertas } (badge del menú)
 *   POST ?c=adminsoporte&a=Actualizar          (id, estado, respuesta)
 */
require_once 'model/soporte.php';
require_once 'controller/jwtHelper.php';

class AdminsoporteController
{
    private $model;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->soloAdmin();
        $this->model = new Soporte();
    }

    private function soloAdmin(): void
    {
        $token = $_SESSION['token'] ?? null;
        $rol   = (int)($_SESSION['rol'] ?? 0);
        if ($token && JwtHelper::validateToken($token) && $rol === 2) return;

        if (($_GET['a'] ?? 'Index') !== 'Index') {
            $this->json(['success' => false, 'message' => 'Sesión expirada o sin permisos']);
        }
        header('Location: index.php?c=login&a=Index');
        exit();
    }

    private function json(array $data): void
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function Index(): void
    {
        require_once 'view/admin/soporte.php';
    }

    public function Listar(): void
    {
        $this->json(['success' => true, 'data' => $this->model->listarAdmin($_GET['estado'] ?? null)]);
    }

    public function Contar(): void
    {
        $this->json(['success' => true, 'abiertas' => $this->model->contarAbiertas()]);
    }

    public function Actualizar(): void
    {
        $id        = (int)($_POST['id'] ?? 0);
        $estado    = $_POST['estado'] ?? '';
        $respuesta = trim($_POST['respuesta'] ?? '');

        if (!$id || !in_array($estado, Soporte::ESTADOS, true)) {
            $this->json(['success' => false, 'message' => 'Datos inválidos']);
        }
        if ($estado === 'resuelta' && $respuesta === '') {
            $this->json(['success' => false, 'message' => 'Escribe una respuesta para el cliente antes de resolver.']);
        }

        $ok = $this->model->actualizar($id, $estado, mb_substr($respuesta, 0, 2000), (int)($_SESSION['user_id'] ?? 0));
        $this->json(['success' => $ok]);
    }
}
