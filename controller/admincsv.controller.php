<?php
/**
 * controller/admincsv.controller.php
 * Exportar / importar CSV desde las pestañas del administrador.
 *   index.php?c=admincsv&a=Exportar&e=contexto
 *   POST index.php?c=admincsv&a=Importar  (e, archivo, simular=1|0)
 * Solo rol 2 (administrador).
 */
require_once 'controller/jwtHelper.php';
require_once 'model/csvAdmin.php';

class AdmincsvController
{
    private CsvAdmin $model;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $token = $_SESSION['token'] ?? null;
        if (!$token || !JwtHelper::validateToken($token) || (int)($_SESSION['rol'] ?? 0) !== 2) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sesión expirada o sin permisos']);
            exit();
        }
        $this->model = new CsvAdmin();
    }

    public function Index(): void { $this->Exportar(); }

    public function Exportar(): void
    {
        $e = (string)($_GET['e'] ?? '');
        $cfg = CsvAdmin::config($e);
        if (!$cfg) { http_response_code(404); exit('Tabla no válida'); }
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $cfg['titulo'] . '_' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-store');
        $this->model->exportar($e);
        exit();
    }

    public function Importar(): void
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Método no permitido.');
            $e = (string)($_POST['e'] ?? '');
            if (!CsvAdmin::config($e)) throw new RuntimeException('Tabla no válida.');
            $f = $_FILES['archivo'] ?? null;
            if (!$f || $f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('No se recibió el archivo (máx. ' . ini_get('upload_max_filesize') . ').');
            if (!preg_match('/\.(csv|txt)$/i', $f['name'])) throw new RuntimeException('El archivo debe ser .csv');

            $res = $this->model->importar($e, CsvAdmin::leer($f['tmp_name']), ($_POST['simular'] ?? '1') === '1');
            echo json_encode(['success' => true] + $res, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $ex) {
            echo json_encode(['success' => false, 'message' => $ex->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }
}
