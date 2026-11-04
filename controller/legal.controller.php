<?php
// controller/legal.controller.php
// Reglamento de uso, privacidad y riesgos + registro del consentimiento del turista.

require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';
require_once 'model/consentimiento.php';
require_once 'model/restriccionEdad.php';

class LegalController {

    /** Página pública con el reglamento completo: index.php?c=legal&a=Reglamento */
    public function Reglamento() {
        require_once 'view/legal/reglamento.php';
    }

    /**
     * POST index.php?c=legal&a=Aceptar  (JSON)
     * { terminos, datos, riesgos, tutor, perfil_publico }
     * Términos, datos y riesgos son obligatorios; la autorización del tutor también si el usuario es menor.
     */
    public function Aceptar() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $id = RestriccionEdad::idDe(AuthMiddleware::resguardar());
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $c = json_decode(file_get_contents('php://input'), true) ?: [];
        $esMenor = RestriccionEdad::esMenor($id);
        if (empty($c['terminos']) || empty($c['datos']) || empty($c['riesgos']) || ($esMenor && empty($c['tutor']))) {
            echo json_encode(['success' => false, 'message' => Lang::get('legal.faltan')]);
            return;
        }

        try {
            Consentimiento::registrar($id, $c, $esMenor);
            $_SESSION['consentimiento_version'] = Consentimiento::VERSION;
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            error_log('LegalController::Aceptar ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => Lang::get('legal.error')]);
        }
    }

    /**
     * GET index.php?c=legal&a=ModalRiesgo&cat=trekking
     * HTML del aviso de riesgo para el buscador; vacío si ya lo aceptó o la categoría no tiene aviso.
     */
    public function ModalRiesgo() {
        $id  = RestriccionEdad::idDe(AuthMiddleware::resguardar());
        $cat = (string)($_GET['cat'] ?? '');
        if (!$id || !in_array($cat, Consentimiento::CATEGORIAS_RIESGO, true) || Consentimiento::riesgoAceptado($id, $cat)) return;
        $categoriaRiesgo = $cat; $idAtraccionRiesgo = null; $idModalRiesgo = 'modalAvisoRiesgo';
        require 'view/cliente/modal/modal_riesgo.php';
    }

    /**
     * POST index.php?c=legal&a=AceptarRiesgo  (JSON) { categoria, atraccion_id, acepto }
     * Registra que el turista practica la actividad bajo su propio riesgo.
     */
    public function AceptarRiesgo() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $id = RestriccionEdad::idDe(AuthMiddleware::resguardar());
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        $c   = json_decode(file_get_contents('php://input'), true) ?: [];
        $cat = (string)($c['categoria'] ?? '');
        $atr = !empty($c['atraccion_id']) ? (int)$c['atraccion_id'] : null;
        // Si viene una atracción, la categoría se toma de la BD (no de lo que mande el navegador)
        if ($atr) $cat = Consentimiento::categoriaRiesgo($atr) ?? $cat;
        if (empty($c['acepto']) || !in_array($cat, Consentimiento::CATEGORIAS_RIESGO, true)) {
            echo json_encode(['success' => false, 'message' => Lang::get('legal.faltan')]);
            return;
        }
        try {
            Consentimiento::registrarRiesgo($id, $cat, $atr);
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            error_log('LegalController::AceptarRiesgo ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => Lang::get('legal.error')]);
        }
    }
}
