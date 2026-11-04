<?php
// controller/historial.controller.php

require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';
require_once 'model/historial.php';

class HistorialController {

    private $model;

    public function __construct() {
        $this->model = new HistorialModel();
    }

    public function Index() {
        $usuarioLogueado = AuthMiddleware::resguardar();
        if (!$usuarioLogueado) {
            header('Location: index.php?c=inicio&a=Index');
            return;
        }

        $id_usuario = is_array($usuarioLogueado)
            ? ($usuarioLogueado['id'] ?? null)
            : ($usuarioLogueado->id ?? null);

        $itinerarios = $this->model->obtenerVisitados($id_usuario);

        require_once 'view/cliente/historial.php';
    }

    /**
     * JSON con las atracciones visitadas del usuario, para el "progreso viajero" del inicio.
     * GET index.php?c=historial&a=Visitadas
     */
    public function Visitadas() {
        header('Content-Type: application/json');
        $usuarioLogueado = AuthMiddleware::resguardar();
        $id_usuario = is_array($usuarioLogueado)
            ? ($usuarioLogueado['id'] ?? null)
            : ($usuarioLogueado->id ?? null);
        if (!$id_usuario) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        echo json_encode(['success' => true, 'ids' => $this->model->idsAtraccionesVisitadas((int)$id_usuario)]);
    }
}