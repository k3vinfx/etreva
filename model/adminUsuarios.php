<?php
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class AdminUsuarios
{
    private $pdo;

    public function __CONSTRUCT()
    {
        try {
            $this->pdo = Database::Conectar();
        } catch(Exception $e) {
            die($e->getMessage());
        }
    }
    

/*
 * model/adminUsuarios.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Modelo de gestión de clientes para el panel de administración.
 *
 * FLUJO DE VERIFICACIÓN DE PAGO:
 *   ProcesarPago  → estado: 'pendiente_pago',  verificado: false
 *   AprobarPago   → estado: 'activo_pagado',   verificado: true,  aprobado_por: adminId
 *   RechazarPago  → estado: 'rechazado',       verificado: false  (sin cambio)
 * ─────────────────────────────────────────────────────────────────────────────
 */








    // ═══════════════════════════════════════════════════════════════════════════
    //  LISTAR CLIENTES (paginado)
    // ═══════════════════════════════════════════════════════════════════════════

    public function listarClientes(
        string $filtroEstado    = '',
        string $filtroEstadoPago = '',
        string $busqueda        = '',
        int    $limit           = 20,
        int    $offset          = 0
    ): array {
        $where  = ["u.rol = 1"];
        $params = [];

        if ($filtroEstado !== '') {
            $where[] = "u.estado = :estado";
            $params[':estado'] = $filtroEstado;
        }
        if ($busqueda !== '') {
            $where[] = "(u.nombre ILIKE :bus OR u.apellido ILIKE :bus OR u.email ILIKE :bus)";
            $params[':bus'] = "%{$busqueda}%";
        }

        $filtroJson = '';
        if ($filtroEstadoPago === 'pendiente_pago') {
            $filtroJson = "AND u.configuracion_json->'registro_pago'->>'estado' = 'pendiente_pago'";
        } elseif ($filtroEstadoPago === 'activo_pagado') {
            $filtroJson = "AND u.configuracion_json->'registro_pago'->>'estado' = 'activo_pagado'";
        } elseif ($filtroEstadoPago === 'sin_plan') {
            $filtroJson = "AND (
                u.configuracion_json IS NULL
                OR u.configuracion_json->'registro_pago' IS NULL
                OR u.configuracion_json->>'registro_pago' = 'null'
                OR u.configuracion_json->'registro_pago'->>'estado' NOT IN ('pendiente_pago','activo_pagado','rechazado')
            )";
        }

        $sql = "
            SELECT
                u.id,
                u.nombre,
                u.apellido,
                u.email,
                u.telefono,
                u.estado,
                u.configuracion_json->'registro_pago'->>'estado'            AS pago_estado,
                u.configuracion_json->'registro_pago'->>'verificado'        AS pago_verificado,
                u.configuracion_json->'registro_pago'->>'tipo_plan'         AS pago_plan,
                u.configuracion_json->'registro_pago'->>'monto'             AS pago_monto,
                u.configuracion_json->'registro_pago'->>'periodo'           AS pago_periodo,
                u.configuracion_json->'registro_pago'->>'fecha_pago'        AS pago_fecha,
                u.configuracion_json->'registro_pago'->>'fecha_vencimiento' AS pago_vencimiento,
                u.configuracion_json->'registro_pago'->>'ruta_comprobante'  AS pago_comprobante,
                u.configuracion_json->'registro_pago'->>'motivo_pago'       AS pago_motivo,
                u.configuracion_json->'registro_pago'->>'dias_totales'      AS pago_dias_totales,
                u.configuracion_json->'registro_pago'->>'dias_carryover'    AS pago_carryover
            FROM usuarios.usuarios u
            WHERE " . implode(' AND ', $where) . "
            {$filtroJson}
            ORDER BY
                CASE WHEN u.configuracion_json->'registro_pago'->>'estado' = 'pendiente_pago' THEN 0 ELSE 1 END,
                u.id DESC
            LIMIT :lim OFFSET :off
        ";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CONTAR CLIENTES (para paginación)
    // ═══════════════════════════════════════════════════════════════════════════

    public function contarClientes(
        string $filtroEstado    = '',
        string $filtroEstadoPago = '',
        string $busqueda        = ''
    ): int {
        $where  = ["rol = 1"];
        $params = [];

        if ($filtroEstado !== '') {
            $where[] = "estado = :estado";
            $params[':estado'] = $filtroEstado;
        }
        if ($busqueda !== '') {
            $where[] = "(nombre ILIKE :bus OR apellido ILIKE :bus OR email ILIKE :bus)";
            $params[':bus'] = "%{$busqueda}%";
        }

        $filtroJson = '';
        if ($filtroEstadoPago === 'pendiente_pago') {
            $filtroJson = "AND configuracion_json->'registro_pago'->>'estado' = 'pendiente_pago'";
        } elseif ($filtroEstadoPago === 'activo_pagado') {
            $filtroJson = "AND configuracion_json->'registro_pago'->>'estado' = 'activo_pagado'";
        } elseif ($filtroEstadoPago === 'sin_plan') {
            $filtroJson = "AND (configuracion_json IS NULL
                            OR configuracion_json->'registro_pago' IS NULL
                            OR configuracion_json->>'registro_pago' = 'null')";
        }

        $sql = "SELECT COUNT(*) FROM usuarios.usuarios
                WHERE " . implode(' AND ', $where) . " {$filtroJson}";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  OBTENER CLIENTE POR ID
    // ═══════════════════════════════════════════════════════════════════════════

    public function obtenerClientePorId(int $id): ?object {
        $sql = "
            SELECT u.*,
                u.configuracion_json->'registro_pago'->>'estado'            AS pago_estado,
                u.configuracion_json->'registro_pago'->>'verificado'        AS pago_verificado,
                u.configuracion_json->'registro_pago'->>'tipo_plan'         AS pago_plan,
                u.configuracion_json->'registro_pago'->>'monto'             AS pago_monto,
                u.configuracion_json->'registro_pago'->>'periodo'           AS pago_periodo,
                u.configuracion_json->'registro_pago'->>'fecha_pago'        AS pago_fecha,
                u.configuracion_json->'registro_pago'->>'fecha_vencimiento' AS pago_vencimiento,
                u.configuracion_json->'registro_pago'->>'ruta_comprobante'  AS pago_comprobante,
                u.configuracion_json->'registro_pago'->>'motivo_pago'       AS pago_motivo,
                u.configuracion_json->'registro_pago'->>'dias_totales'      AS pago_dias_totales,
                u.configuracion_json->'registro_pago'->>'dias_carryover'    AS pago_carryover
            FROM usuarios.usuarios u
            WHERE u.id = :id AND u.rol = 1
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch(PDO::FETCH_OBJ);
        return $r ?: null;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  APROBAR PAGO
    //  pendiente_pago + verificado:false  →  activo_pagado + verificado:true
    // ═══════════════════════════════════════════════════════════════════════════

    public function aprobarPago(int $userId, int $adminId): bool {
        $usuario = $this->obtenerClientePorId($userId);
        if (!$usuario) return false;

        $config = json_decode($usuario->configuracion_json, true) ?? [];
        $rp     = $config['registro_pago'] ?? [];

        if (empty($rp) || ($rp['estado'] ?? '') !== 'pendiente_pago') return false;

        $tz    = new DateTimeZone('America/La_Paz');
        $ahora = new DateTime('now', $tz);

        $rp['estado']           = 'activo_pagado';
        $rp['verificado']       = true;           // ← APROBADO
        $rp['aprobado_por']     = $adminId;
        $rp['fecha_aprobacion'] = $ahora->format('Y-m-d H:i:s');

        $config['registro_pago'] = $rp;

        $stmt = $this->pdo->prepare(
            "UPDATE usuarios.usuarios
             SET configuracion_json = :json, estado = 'activo'
             WHERE id = :id AND rol = 1"
        );
        return $stmt->execute([':json' => json_encode($config), ':id' => $userId]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  RECHAZAR PAGO
    //  pendiente_pago  →  rechazado  (verificado sigue false)
    // ═══════════════════════════════════════════════════════════════════════════

    public function rechazarPago(int $userId, int $adminId, string $motivo = ''): bool {
        $usuario = $this->obtenerClientePorId($userId);
        if (!$usuario) return false;

        $config = json_decode($usuario->configuracion_json, true) ?? [];
        $rp     = $config['registro_pago'] ?? [];

        if (empty($rp) || ($rp['estado'] ?? '') !== 'pendiente_pago') return false;

        $tz    = new DateTimeZone('America/La_Paz');
        $ahora = new DateTime('now', $tz);

        $rp['estado']         = 'rechazado';
        $rp['verificado']     = false;            // ← RECHAZADO, sigue false
        $rp['rechazado_por']  = $adminId;
        $rp['fecha_rechazo']  = $ahora->format('Y-m-d H:i:s');
        $rp['motivo_rechazo'] = $motivo;

        $config['registro_pago'] = $rp;

        $stmt = $this->pdo->prepare(
            "UPDATE usuarios.usuarios SET configuracion_json = :json WHERE id = :id AND rol = 1"
        );
        return $stmt->execute([':json' => json_encode($config), ':id' => $userId]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CAMBIAR ESTADO DE CUENTA
    //  estados válidos: 'activo' | 'inactivo' | 'suspendido'
    //  Registra historial en configuracion_json['historial_estado'][]
    // ═══════════════════════════════════════════════════════════════════════════

    public function cambiarEstadoCuenta(int $userId, string $nuevoEstado, int $adminId, string $motivo = ''): bool {
        // Leer estado actual desde BD
        $stmt = $this->pdo->prepare(
            "SELECT estado, configuracion_json FROM usuarios.usuarios WHERE id = :id AND rol = 1"
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;

        $estadoAnterior = $row['estado'];
        $config = json_decode($row['configuracion_json'] ?? '{}', true) ?? [];

        // Agregar entrada al historial
        $tz    = new DateTimeZone('America/La_Paz');
        $ahora = new DateTime('now', $tz);

        $config['historial_estado'][] = [
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => $nuevoEstado,
            'admin_id'        => $adminId,
            'motivo'          => $motivo,
            'fecha'           => $ahora->format('Y-m-d H:i:s'),
        ];

        $stmt2 = $this->pdo->prepare(
            "UPDATE usuarios.usuarios
             SET estado = :estado, configuracion_json = :json
             WHERE id = :id AND rol = 1"
        );
        return $stmt2->execute([
            ':estado' => $nuevoEstado,
            ':json'   => json_encode($config),
            ':id'     => $userId,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  ENVIAR EMAIL OFERTA (máx 1/día por usuario)
    // ═══════════════════════════════════════════════════════════════════════════

    public function enviarEmailOferta(int $userId, int $adminId): array {
        $stmt = $this->pdo->prepare(
            "SELECT nombre, apellido, email, configuracion_json FROM usuarios.usuarios WHERE id = :id AND rol = 1"
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return ['ok' => false, 'msg' => 'Usuario no encontrado'];

        $config = json_decode($row['configuracion_json'] ?? '{}', true) ?? [];
        $nombre = $row['nombre'] . ' ' . $row['apellido'];
        $email  = $row['email'];

        // Control de 1 email/día
        $tz    = new DateTimeZone('America/La_Paz');
        $ahora = new DateTime('now', $tz);
        if (!empty($config['ultimo_email_oferta'])) {
            $ultimoEmail = new DateTime($config['ultimo_email_oferta'], $tz);
            if ($ahora->diff($ultimoEmail)->days < 1) {
                return ['ok' => false, 'msg' => 'Ya se envió un email hoy a este usuario'];
            }
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom('k3vinfx@gmail.com', 'Etreva Travel');
            $mail->addAddress($email, $nombre);
            $mail->isHTML(true);
            $mail->Subject = '¡Te extrañamos! Vuelve a explorar La Paz 🌄';

            $link = 'http://localhost/etreva/index.php?c=pago&a=Index';
            $mail->Body = "
            <div style='max-width:520px;margin:0 auto;background:#161625;border-radius:16px;overflow:hidden;font-family:Segoe UI,sans-serif;'>
              <div style='background:linear-gradient(135deg,#e55a2b,#ff9a6c);padding:28px 24px;text-align:center;'>
                <h1 style='color:#fff;margin:0;font-size:1.5rem;font-weight:900;'>Etreva Travel 🏔️</h1>
              </div>
              <div style='padding:28px 24px;'>
                <p style='color:#f0f0f5;'>Hola, <strong>{$nombre}</strong> 👋</p>
                <p style='color:rgba(240,240,245,.65);line-height:1.7;'>Tu acceso a Etreva Travel está inactivo. ¡Renová y seguí descubriendo La Paz!</p>
                <a href='{$link}' style='display:block;text-align:center;background:linear-gradient(135deg,#e55a2b,#ff9a6c);color:#fff;text-decoration:none;padding:13px 24px;border-radius:10px;font-weight:700;margin-top:20px;'>
                  Ver planes 🚀
                </a>
              </div>
            </div>";
            $mail->AltBody = "Hola {$nombre}, renová tu plan en: {$link}";
            $mail->send();

            // Registrar
            $config['ultimo_email_oferta']    = $ahora->format('Y-m-d H:i:s');
            $config['emails_oferta_enviados'] = ($config['emails_oferta_enviados'] ?? 0) + 1;
            $config['ultimo_email_admin']     = $adminId;

            $this->pdo->prepare(
                "UPDATE usuarios.usuarios SET configuracion_json = :json WHERE id = :id"
            )->execute([':json' => json_encode($config), ':id' => $userId]);

            return ['ok' => true, 'msg' => "Email enviado a {$email}"];

        } catch (Exception $e) {
            error_log("enviarEmailOferta error: " . $e->getMessage());
            return ['ok' => false, 'msg' => 'Error al enviar: ' . $e->getMessage()];
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  PAGOS PENDIENTES (para stats y dashboard)
    //  Marca urgente:true si han pasado >= 2 días desde fecha_pago
    // ═══════════════════════════════════════════════════════════════════════════

    public function obtenerPagosPendientesAdmin(): array {
        $sql = "
            SELECT
                u.id,
                u.nombre,
                u.apellido,
                u.email,
                u.estado,
                u.configuracion_json->'registro_pago'->>'tipo_plan'        AS plan,
                u.configuracion_json->'registro_pago'->>'monto'            AS monto,
                u.configuracion_json->'registro_pago'->>'periodo'          AS periodo,
                u.configuracion_json->'registro_pago'->>'fecha_pago'       AS fecha_pago,
                u.configuracion_json->'registro_pago'->>'ruta_comprobante' AS comprobante
            FROM usuarios.usuarios u
            WHERE u.rol = 1
              AND u.configuracion_json->'registro_pago'->>'estado' = 'pendiente_pago'
            ORDER BY u.configuracion_json->'registro_pago'->>'fecha_pago' ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tz    = new DateTimeZone('America/La_Paz');
        $ahora = new DateTime('now', $tz);

        foreach ($rows as &$row) {
            if (!empty($row['fecha_pago'])) {
                $dias = (int)$ahora->diff(new DateTime($row['fecha_pago'], $tz))->days;
                $row['dias_pendiente'] = $dias;
                $row['urgente']        = $dias >= 2;
            } else {
                $row['dias_pendiente'] = 0;
                $row['urgente']        = false;
            }
        }

        return $rows;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  CLIENTES INACTIVOS / SUSPENDIDOS
    // ═══════════════════════════════════════════════════════════════════════════

    public function obtenerClientesInactivos(int $limit = 50): array {
        $sql = "
            SELECT
                u.id, u.nombre, u.apellido, u.email, u.estado,
                u.configuracion_json->'registro_pago'->>'tipo_plan'         AS ultimo_plan,
                u.configuracion_json->'registro_pago'->>'fecha_vencimiento' AS vencimiento,
                u.configuracion_json->>'ultimo_email_oferta'                AS ultimo_email,
                u.configuracion_json->>'emails_oferta_enviados'             AS emails_enviados
            FROM usuarios.usuarios u
            WHERE u.rol = 1 AND u.estado IN ('inactivo','suspendido')
            ORDER BY u.id DESC
            LIMIT :lim
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  STATS GENERALES (KPIs)
    // ═══════════════════════════════════════════════════════════════════════════

    public function obtenerStatsAdmin(): object {
        $sql = "
            SELECT
                COUNT(*)                                                        AS total_usuarios,
                COUNT(*) FILTER (WHERE estado = 'activo')                       AS usuarios_activos,
                COUNT(*) FILTER (WHERE estado IN ('inactivo','suspendido'))     AS usuarios_inactivos,
                COUNT(*) FILTER (
                    WHERE configuracion_json->'registro_pago'->>'estado' = 'pendiente_pago'
                )                                                               AS pagos_pendientes,
                COUNT(*) FILTER (
                    WHERE configuracion_json->'registro_pago'->>'estado' = 'activo_pagado'
                )                                                               AS pagos_activos
            FROM usuarios.usuarios WHERE rol = 1
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}