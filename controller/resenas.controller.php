<?php
require_once 'model/resenas.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class ResenasController {

    private $resenasModel;

    public function __CONSTRUCT() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->resenasModel = new ResenasModel();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Vista principal de reseñas
    //  URL: index.php?c=resenas&a=Index
    // ══════════════════════════════════════════════════════════════════════
    public function Index() {

        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            header("Location: index.php?c=inicio&a=Index");
            exit();
        }

        $id_usuario = is_array($usuario)
            ? ($usuario['id'] ?? null)
            : ($usuario->id ?? null);

        require_once 'view/cliente/resenas.php';
    }

    // ══════════════════════════════════════════════════════════════════════
    //  API: obtener todas las reseñas con paginación (JSON)
    //  GET index.php?c=resenas&a=ObtenerTodas&pagina=1&por_pagina=12
    // ══════════════════════════════════════════════════════════════════════
    public function ObtenerTodas() {
        header('Content-Type: application/json');

        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $id_usuario  = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        $pagina      = max(1, intval($_GET['pagina']    ?? 1));
        $por_pagina  = min(50, max(6, intval($_GET['por_pagina'] ?? 12)));
        $busqueda    = trim($_GET['q']        ?? '');
        $solo_mias   = ($_GET['solo_mias']    ?? '0') === '1';
        $offset      = ($pagina - 1) * $por_pagina;

        // ── Obtener datos ─────────────────────────────────────────────────
        if ($busqueda !== '') {
            $resenas    = $this->resenasModel->buscarResenas(
                $busqueda,
                $solo_mias ? $id_usuario : null
            );
            $total      = count($resenas);
            // Paginar el resultado del filtro en PHP
            $resenas    = array_slice($resenas, $offset, $por_pagina);

        } elseif ($solo_mias) {
            $todas      = $this->resenasModel->obtenerResenasDeUsuario($id_usuario);
            $total      = count($todas);
            $resenas    = array_slice($todas, $offset, $por_pagina);

        } else {
            $total      = $this->resenasModel->contarTodasResenas();
            $resenas    = $this->resenasModel->obtenerTodasResenas($por_pagina, $offset);
        }

        // Marcar cuáles son del usuario logueado
        foreach ($resenas as &$r) {
            $r['es_mia'] = ((int)($r['usuario_id'] ?? 0) === (int)$id_usuario);
        }

        echo json_encode([
            'success'    => true,
            'total'      => $total,
            'pagina'     => $pagina,
            'por_pagina' => $por_pagina,
            'paginas'    => (int)ceil($total / $por_pagina),
            'resenas'    => $resenas,
        ], JSON_UNESCAPED_UNICODE);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  API: editar una reseña propia
    //  POST index.php?c=resenas&a=Editar
    //  Body JSON: { resena_id, calificacion, comentario }
    // ══════════════════════════════════════════════════════════════════════
    public function Editar() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $id_usuario = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);

        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            echo json_encode(['success' => false, 'message' => 'Body inválido']);
            return;
        }

        $resena_id   = intval($body['resena_id']   ?? 0);
        $calificacion= intval($body['calificacion'] ?? 0);
        $comentario  = trim($body['comentario']     ?? '');

        if (!$resena_id || $calificacion < 1 || $calificacion > 5) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }

        $ok = $this->resenasModel->editarResena($resena_id, $id_usuario, $calificacion, $comentario);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Reseña actualizada correctamente' : 'No se pudo actualizar la reseña',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  API: eliminar una reseña propia
    //  POST index.php?c=resenas&a=Eliminar
    //  Body: { id: resena_id }
    // ══════════════════════════════════════════════════════════════════════
    public function Eliminar() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $id_usuario = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        $resena_id  = intval($_POST['id'] ?? 0);

        if (!$resena_id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $ok = $this->resenasModel->eliminarResena($resena_id, $id_usuario);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Reseña eliminada' : 'No se pudo eliminar la reseña',
        ]);
    }
}