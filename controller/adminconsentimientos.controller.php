<?php
/*
 * controller/adminconsentimientos.controller.php — lado ADMINISTRACIÓN
 * Constancia de consentimientos de los turistas (reglamento y avisos de riesgo).
 *   GET ?c=adminconsentimientos&a=Index                    → vista
 *   GET ?c=adminconsentimientos&a=Listar[&tipo=][&texto=]  → JSON { resumen, data }
 *   GET ?c=adminconsentimientos&a=Exportar[&tipo=][&texto=] → CSV (respaldo / auditoría)
 */
require_once 'model/consentimiento.php';
require_once 'controller/jwtHelper.php';

class AdminconsentimientosController
{
    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $token = $_SESSION['token'] ?? null;
        if ($token && JwtHelper::validateToken($token) && (int)($_SESSION['rol'] ?? 0) === 2) return;
        if (($_GET['a'] ?? 'Index') !== 'Index') $this->json(['success' => false, 'message' => 'Sesión expirada o sin permisos']);
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

    private function filtros(): array
    {
        $tipo = in_array($_GET['tipo'] ?? '', ['reglamento', 'riesgo'], true) ? $_GET['tipo'] : null;
        return ['tipo' => $tipo, 'texto' => trim(mb_substr((string)($_GET['texto'] ?? ''), 0, 80))];
    }

    public function Index(): void
    {
        require_once 'view/admin/consentimientos.php';
    }

    public function Listar(): void
    {
        try {
            $this->json(['success' => true, 'resumen' => Consentimiento::resumen(), 'data' => Consentimiento::listar($this->filtros())]);
        } catch (Throwable $e) {
            error_log('AdminconsentimientosController::Listar ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'No se pudo cargar el listado']);
        }
    }

    public function Exportar(): void
    {
        $filas = Consentimiento::listar($this->filtros());
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="consentimientos_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");   // BOM para que Excel lea los acentos
        fputcsv($out, ['ID registro', 'Usuario ID', 'Nombre', 'Apellido', 'Correo', 'Tipo', 'Versión', 'Actividad', 'Atracción ID', 'Atracción',
                       'Acepta términos', 'Acepta datos', 'Perfil público', 'Acepta riesgos', 'Autorización tutor', 'Menor de edad',
                       'IP', 'Navegador', 'Fecha y hora']);
        $sn = fn($v) => $v === null ? '' : ($v ? 'Sí' : 'No');
        foreach ($filas as $r) {
            // Evita que un nombre que empiece con = + - @ se interprete como fórmula en Excel
            $txt = fn($v) => preg_match('/^[=+\-@]/', (string)$v) ? "'" . $v : (string)$v;
            fputcsv($out, [$r['id'], $r['usuario_id'], $txt($r['nombre']), $txt($r['apellido']), $txt($r['email']), $r['tipo'], $r['version'],
                           $r['categoria'], $r['atraccion_id'], $txt($r['atraccion']), $sn($r['acepta_terminos']), $sn($r['acepta_datos']),
                           $sn($r['acepta_perfil_publico']), $sn($r['acepta_riesgos']), $sn($r['autorizacion_tutor']), $sn($r['es_menor']),
                           $r['ip'], $txt($r['navegador']), $r['aceptado_en']]);
        }
        fclose($out);
        exit();
    }
}
