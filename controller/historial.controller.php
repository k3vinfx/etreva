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
}