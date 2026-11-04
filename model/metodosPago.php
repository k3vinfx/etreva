<?php
/*
 * model/metodosPago.php
 * ─────────────────────────────────────────────────────────────────
 * Métodos de pago aceptados (config/pagos.php) y sus reglas, compartidos por la pantalla de pago,
 * PagoController::ProcesarPago y el panel del administrador.
 *
 * Claves de método:
 *   'yape'            Yape / QR Simple (Bolivia, en bolivianos)        → captura del comprobante obligatoria
 *   'binance_pay'     Binance Pay (USDT, sin red)                       → captura del comprobante obligatoria
 *   '{moneda}_{red}'  p. ej. 'usdt_trc20', 'usdc_bep20'                → captura del comprobante O hash de la transacción
 */
class MetodosPago
{
    private static ?array $cfg = null;

    public static function config(): array
    {
        return self::$cfg ??= require __DIR__ . '/../config/pagos.php';
    }

    /** Todas las claves válidas */
    public static function claves(): array
    {
        $c = self::config();
        $out = [];
        if (!empty($c['yape']['activo']))        $out[] = 'yape';
        if (!empty($c['binance_pay']['activo'])) $out[] = 'binance_pay';
        if (!empty($c['cripto']['activo'])) {
            foreach ($c['cripto']['redes'] as $red => $r) foreach ($r[5] as $m) $out[] = strtolower($m) . '_' . $red;
        }
        return $out;
    }

    public static function valido(string $metodo): bool
    {
        return in_array($metodo, self::claves(), true);
    }

    /** Datos de la red de un método cripto, o null si no es cripto */
    public static function red(string $metodo): ?array
    {
        if (!preg_match('/^(usdt|usdc)_([a-z0-9]+)$/', $metodo, $m)) return null;
        $r = self::config()['cripto']['redes'][$m[2]] ?? null;
        return $r ? ['clave' => $m[2], 'moneda' => strtoupper($m[1]), 'nombre' => $r[0], 'cadena' => $r[1],
                     'tipo' => $r[2], 'explorador' => $r[3]] : null;
    }

    public static function esCripto(string $metodo): bool
    {
        return self::red($metodo) !== null;
    }

    /** Formato del hash según la red: EVM = 0x + 64 hex; Tron = 64 hex */
    public static function hashValido(string $metodo, string $hash): bool
    {
        $red = self::red($metodo);
        if (!$red) return false;
        return $red['tipo'] === 'tron'
            ? (bool)preg_match('/^[0-9a-fA-F]{64}$/', $hash)
            : (bool)preg_match('/^0x[0-9a-fA-F]{64}$/', $hash);
    }

    /** Enlace al explorador de bloques para que el administrador verifique la transacción */
    public static function enlaceExplorador(string $metodo, ?string $hash): ?string
    {
        $red = self::red($metodo);
        return ($red && $hash && self::hashValido($metodo, $hash)) ? $red['explorador'] . $hash : null;
    }

    /** Nombre legible: "USDT · TRC-20 (Tron)", "Yape · QR (Bolivia)"… */
    public static function etiqueta(?string $metodo): string
    {
        if (!$metodo || $metodo === 'yape') return 'Yape · QR (Bolivia)';
        if ($metodo === 'binance_pay') return 'Binance Pay';
        $red = self::red($metodo);
        return $red ? "{$red['moneda']} · {$red['nombre']} ({$red['cadena']})" : $metodo;
    }
}
