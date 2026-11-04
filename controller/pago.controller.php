<?php
require_once 'model/login.php';
require_once 'controller/middleware.php';
require_once 'lang/Lang.php';
class PagoController {

    private $model;

    // ─── Planes: única fuente de verdad ──────────────────────────────────────
    public static function PLANES(): array {
        return [
            'Explorador Urbano' => [
                'id'      => 'explorador_urbano',
                'nivel'   => 1,
                'precios' => ['weekly' => 8,  'monthly' => 10, 'yearly' => 40],
                'dias'    => ['weekly' => 7,  'monthly' => 30, 'yearly' => 365],
            ],
            'Aventura Andina' => [
                'id'      => 'aventura_andina',
                'nivel'   => 2,
                'precios' => ['weekly' => 10, 'monthly' => 20, 'yearly' => 60],
                'dias'    => ['weekly' => 7,  'monthly' => 30, 'yearly' => 365],
            ],
            'Premium Paceño' => [
                'id'      => 'premium_paceno',
                'nivel'   => 3,
                'precios' => ['weekly' => 15, 'monthly' => 30, 'yearly' => 90],
                'dias'    => ['weekly' => 7,  'monthly' => 30, 'yearly' => 365],
            ],
        ];
    }
     private static function NOMBRES_CANONICOS(): array {
        return [
            // Español (igual)
            'Explorador Urbano' => 'Explorador Urbano',
            'Aventura Andina'   => 'Aventura Andina',
            'Premium Paceño'    => 'Premium Paceño',
            // Inglés
            'Urban Explorer'    => 'Explorador Urbano',
            'Andean Adventure'  => 'Aventura Andina',
            'Premium Paceño'    => 'Premium Paceño', // mismo en ambos idiomas
        ];
    }

    private static function resolverNombrePlan(string $nombreRecibido): string {
        $mapa = self::NOMBRES_CANONICOS();
        return $mapa[$nombreRecibido] ?? $nombreRecibido;
    }



    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new login();
    }

    // ════════════════════════════════════════════════════════════════════════
    //  Vista principal: index.php?c=pago&a=Index
    // ════════════════════════════════════════════════════════════════════════
    public function Index() {
        $usuario = AuthMiddleware::resguardar();
        if (!$usuario) {
            header('Location: index.php?c=login&a=Index');
            exit();
        }

        $id_usuario = $_SESSION['user_id']
            ?? (is_array($usuario) ? $usuario['id'] : $usuario->id);

        $config        = $this->model->ObtenerConfiguracion($id_usuario);
        $registro_pago = (array)($config->registro_pago ?? []);
        $planes        = self::PLANES();

        // ── Evaluar plan activo ───────────────────────────────────────────
        $plan_activo        = false;
        $dias_restantes     = 0;
        $plan_actual_id     = 'explorador_gratis';
        $plan_actual_nombre = Lang::get('plan.explorador_gratis');
        $nivel_actual       = 0;
        $pct_restante       = 100;

        if (
            !empty($registro_pago['estado']) &&
            $registro_pago['estado'] === 'activo_pagado' &&
            !empty($registro_pago['fecha_vencimiento'])
        ) {
            $vence = new DateTime($registro_pago['fecha_vencimiento']);
            $ahora = new DateTime();

            if ($vence > $ahora) {
                $plan_activo    = true;
                $diff           = $ahora->diff($vence);
                $dias_restantes = $diff->days;

                // Porcentaje de vida restante
                $dias_totales_plan = (int)($registro_pago['dias_totales'] ?? 30);
                $pct_restante = $dias_totales_plan > 0
                    ? min(100, max(0, round(($dias_restantes / $dias_totales_plan) * 100)))
                    : 0;

                // Identificar plan
                foreach ($planes as $nombre => $info) {
                    if (
                        (!empty($registro_pago['identificador_plan']) && str_starts_with($registro_pago['identificador_plan'], $info['id'])) ||
                        (!empty($registro_pago['tipo_plan']) && $registro_pago['tipo_plan'] === $nombre)
                    ) {
                        $plan_actual_id     = $info['id'];
                        $plan_actual_nombre = $nombre;
                        $nivel_actual       = $info['nivel'];
                        break;
                    }
                }
            }
        }

        require_once 'view/cliente/planes.php';
    }


  
    /**
     * ============================================================
     *  REEMPLAZAR en controller/PagoController.php
     *  Método: ProcesarPago()
     * ============================================================
     *
     *  CAMBIOS:
     *  - Pasa correctamente $extras al modelo (incluyendo 'motivo')
     *  - Motivo: 'nuevo' | 'renovacion' | 'upgrade'
     *  - Devuelve 'motivo_pago' en el JSON de respuesta
     *    (para que el JS del modal lo muestre en el Swal)
     * ============================================================
     */
    public function ProcesarPago() {
        header('Content-Type: application/json');
        ob_start();

        $userId  = $_SESSION['user_id'] ?? null;
    
        $monto   = (float)($_POST['monto'] ?? 0);
        $periodo = trim($_POST['periodo'] ?? '');
        $file    = $_FILES['comprobante'] ?? null;

             // ── Resolver nombre del plan (puede venir en cualquier idioma) ────────
        $planRecibido = trim($_POST['plan'] ?? '');
        $plan         = self::resolverNombrePlan($planRecibido);



        // ── Validaciones básicas ──────────────────────────────────────────────
        if (!$userId) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Sesión expirada.']);
            return;
        }

        $planes = self::PLANES();

        if (!isset($planes[$plan])) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => "Plan inválido: {$plan}"]);
            return;
        }

        $precio_esperado = $planes[$plan]['precios'][$periodo] ?? null;
        if ($precio_esperado === null) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Período no válido']);
            return;
        }

        if ((float)$monto !== (float)$precio_esperado) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => "Monto incorrecto. Esperado: \${$precio_esperado}"]);
            return;
        }

        // ── Método de pago (config/pagos.php) ─────────────────────────────────
        // Yape / QR Simple (Bolivia), Binance Pay o USDT/USDC en TRC-20, BEP-20, Polygon o ERC-20.
        // El sistema no cobra en línea: registra el método y la prueba para que el administrador valide.
        require_once 'model/metodosPago.php';
        $metodo     = (string)($_POST['metodo_pago'] ?? 'yape');
        $referencia = trim(mb_substr((string)($_POST['referencia_pago'] ?? ''), 0, 80));
        if (!MetodosPago::valido($metodo)) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Método de pago no válido']);
            return;
        }
        $esCripto = MetodosPago::esCripto($metodo);
        if (!$esCripto) $referencia = '';   // Yape y Binance Pay se prueban solo con la captura
        if ($referencia !== '' && !MetodosPago::hashValido($metodo, $referencia)) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => Lang::get('pagos.hash_invalido')]);
            return;
        }

        // ── Prueba del pago: captura del comprobante (en cripto, o el hash de la transacción) ──
        $tieneArchivo = $file && $file['error'] === UPLOAD_ERR_OK;
        if (!$tieneArchivo && !($esCripto && $referencia !== '')) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => Lang::get($esCripto ? 'pagos.falta_prueba' : 'pagos.falta_captura')]);
            return;
        }
        $montoBs = $metodo === 'yape' ? round($monto * (float)MetodosPago::config()['tipo_cambio_bs'], 2) : null;

        // ── Validar y subir comprobante (si lo adjuntó) ───────────────────────
        $rutaImagen = null;
        if ($tieneArchivo) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif','webp']) || $file['size'] > 5 * 1024 * 1024) {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Archivo inválido (solo imágenes, máx 5MB)']);
                return;
            }

            $uploadDir  = 'uploads/comprobantes/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $rutaImagen = $uploadDir . "pago_{$userId}_" . time() . ".{$ext}";

            if (!move_uploaded_file($file['tmp_name'], $rutaImagen)) {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Error al guardar el comprobante']);
                return;
            }
        }

        // ── Leer estado actual del usuario ────────────────────────────────────
        $config        = $this->model->ObtenerConfiguracion($userId);
        $registro_pago = (array)($config->registro_pago ?? []);

        $dias_base        = $planes[$plan]['dias'][$periodo];
        $dias_carryover   = 0;
        $motivo_pago      = 'nuevo';       // default
        $motivo_carryover = '';

        $plan_previo_activo = (
            !empty($registro_pago['estado']) &&
            $registro_pago['estado'] === 'activo_pagado' &&
            !empty($registro_pago['fecha_vencimiento'])
        );

        if ($plan_previo_activo) {
            $vence = new DateTime($registro_pago['fecha_vencimiento']);
            $ahora = new DateTime();

            if ($vence > $ahora) {

                // Identificar nivel del plan actual
                $nivel_actual       = 0;
                $nombre_plan_actual = '';
                foreach ($planes as $nombre => $info) {
                    if (
                        (!empty($registro_pago['identificador_plan']) && str_starts_with($registro_pago['identificador_plan'], $info['id'])) ||
                        (!empty($registro_pago['tipo_plan'])           && $registro_pago['tipo_plan'] === $nombre)
                    ) {
                        $nivel_actual       = $info['nivel'];
                        $nombre_plan_actual = $nombre;
                        break;
                    }
                }

                $nivel_nuevo = $planes[$plan]['nivel'];

                // ── DOWNGRADE: bloquear ───────────────────────────────────────
                if ($nivel_nuevo < $nivel_actual) {
                    $dias_rest = (int)$ahora->diff($vence)->days;
                    ob_clean();
                    echo json_encode([
                        'success' => false,
                        'message' => "No podés bajar al plan \"{$plan}\" mientras \"{$nombre_plan_actual}\" está activo con {$dias_rest} días restantes.",
                    ]);
                    return;
                }

                // ── MISMO PLAN: renovación ────────────────────────────────────
                if ($nivel_nuevo === $nivel_actual) {
                    $dias_carryover   = (int)$ahora->diff($vence)->days;
                    $motivo_pago      = 'renovacion';
                    $motivo_carryover = "Renovación: se sumaron {$dias_carryover} días restantes.";
                }

                // ── UPGRADE ───────────────────────────────────────────────────
                if ($nivel_nuevo > $nivel_actual) {
                    $dias_carryover   = (int)$ahora->diff($vence)->days;
                    $motivo_pago      = 'upgrade';
                    $motivo_carryover = "Upgrade desde \"{$nombre_plan_actual}\": se sumaron {$dias_carryover} días.";
                }
            }
        }

        // ── Calcular totales ──────────────────────────────────────────────────
        $dias_totales     = $dias_base + $dias_carryover;
        $fechaPago        = date('Y-m-d H:i:s');
        $fechaVencimiento = date('Y-m-d H:i:s', strtotime("+{$dias_totales} days"));

        // ── Llamar al modelo con TODOS los extras ─────────────────────────────
        $ok = $this->model->actualizarPago(
            $userId,
            $plan,
            $monto,
            $periodo,
            $rutaImagen,
            [
                'dias_plan'                  => $dias_base,
                'dias_carryover'             => $dias_carryover,
                'dias_totales'               => $dias_totales,
                'fecha_vencimiento_override' => $fechaVencimiento,
                'motivo'                     => $motivo_pago,   // 'nuevo' | 'renovacion' | 'upgrade'
                'metodo_pago'                => $metodo,        // 'yape' | 'binance_pay' | 'usdt_trc20' | 'usdc_bep20' …
                'referencia_pago'            => $referencia !== '' ? $referencia : null,
                'monto_bs'                   => $montoBs,
            ]
        );

        ob_clean();

        if ($ok) {
            echo json_encode([
                'success'           => true,
                'plan'              => $plan,
                'periodo'           => $periodo,
                'dias_base'         => $dias_base,
                'dias_carryover'    => $dias_carryover,
                'dias_totales'      => $dias_totales,
                'fecha_vencimiento' => $fechaVencimiento,
                'motivo_pago'       => $motivo_pago,        // 'nuevo' | 'renovacion' | 'upgrade'
                'mensaje_extra'     => $motivo_carryover,
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    //  JSON estado: index.php?c=pago&a=EstadoPlan
    // ════════════════════════════════════════════════════════════════════════
    public function EstadoPlan() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $config        = $this->model->ObtenerConfiguracion($userId);
        $registro_pago = (array)($config->registro_pago ?? []);
        $activo        = false;
        $dias_rest     = 0;

        if (!empty($registro_pago['fecha_vencimiento']) && $registro_pago['estado'] === 'activo_pagado') {
            $vence = new DateTime($registro_pago['fecha_vencimiento']);
            $ahora = new DateTime();
            if ($vence > $ahora) {
                $activo    = true;
                $dias_rest = (int)$ahora->diff($vence)->days;
            }
        }

        echo json_encode([
            'success'        => true,
            'plan_activo'    => $activo,
            'dias_restantes' => $dias_rest,
            'registro_pago'  => $registro_pago,
        ]);
    }
}