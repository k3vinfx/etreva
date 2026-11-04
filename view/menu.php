<?php
/*
 * view/menu.php
 * ─────────────────────────────────────────────────────────────────
 * Solo lee $_SESSION['plan_estado'] que calculó LoginController.
 * Sin consultas a BD. Sin require de modelos.
 * Bloqueos SOLO para rol = 1 (clientes).
 *
 * ESTADOS POSIBLES (pago_estado_raw):
 *   pendiente_nuevo  → recién registrado, sin plan
 *   pendiente_pago   → subió comprobante, esperando admin (72hs gracia)
 *   activo_pagado    → admin aprobó, plan vigente
 *   rechazado        → admin rechazó comprobante
 *   gracia           → plan vencido, dentro de 3 días de gracia
 *   bloqueado        → plan vencido, pasaron los 3 días de gracia
 */
if (session_status() === PHP_SESSION_NONE) session_start();

// ── Cargar sistema de idiomas ────────────────────────────────────────────────
require_once __DIR__ . '/../lang/Lang.php';
if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'es';
Lang::load($_SESSION['lang']);

$rol             = (int)($_SESSION['rol']             ?? 0);
$session_usuario =       $_SESSION['session_usuario'] ?? 'Usuario';

// ── Estado del plan desde sesión ────────────────────────────────────────────
$pe = $_SESSION['plan_estado'] ?? null;

// Fallback: sesiones viejas que no tienen plan_estado aún
if (!$pe) {
    $pago_activo_ses = (bool)($_SESSION['pago_activo'] ?? false);
    $pe = [
        'estado'                => $pago_activo_ses ? 'activo' : 'sin_plan',
        'dias_vencido'          => 0,
        'dias_gracia_restantes' => 0,
        'dias_restantes'        => 0,
        'plan_nombre'           => 'Plan',
        'puede_navegar'         => $pago_activo_ses,
        'es_gracia'             => false,
        'es_bloqueado'          => !$pago_activo_ses,
        'pago_estado_raw'       => null,
        'pendiente_vencido'     => false,
        'horas_esperando'       => 0,
        'horas_restantes_pago'  => 72,
    ];
}

// ── Extraer todos los campos necesarios ─────────────────────────────────────
$aplica  = ($rol === 1);
$estado  = $pe['estado']       ?? 'sin_plan';
$pago_raw = $pe['pago_estado_raw'] ?? null;

// Campos de gracia de plan (plan activo que venció)
$gracia   = $aplica && (bool)($pe['es_gracia']    ?? false);
$bloq     = $aplica && (bool)($pe['es_bloqueado'] ?? false);
$d_venc   = (int)($pe['dias_vencido']          ?? 0);
$d_gracia = (int)($pe['dias_gracia_restantes'] ?? 0);
$nombre   = htmlspecialchars($pe['plan_nombre'] ?? 'Plan');

// Campos de pago pendiente (comprobante subido, esperando admin)
$es_rechazado       = $aplica && ($pago_raw === 'rechazado');
$es_pendiente       = $aplica && ($pago_raw === 'pendiente_pago');
$pendiente_vencido  = $aplica && $es_pendiente && !empty($pe['pendiente_vencido']);
$horas_esperando    = (int)($pe['horas_esperando']     ?? 0);
$horas_restantes    = (int)($pe['horas_restantes_pago'] ?? 72);

$motivo_rechazo = htmlspecialchars($pe['motivo_rechazo'] ?? '');
$pago_plan      = htmlspecialchars($pe['pago_plan']      ?? 'tu plan');

// ── Calcular $navegar con prioridad correcta ─────────────────────────────────
// ORDEN IMPORTA: cada caso sobreescribe al anterior con mayor especificidad
if (!$aplica) {
    // Admins y otros roles: siempre pueden navegar
    $navegar = true;

} elseif ($bloq) {
    // Plan vencido y pasó el período de gracia → BLOQUEADO
    $navegar = false;

} elseif ($es_rechazado) {
    // Pago rechazado por admin → NO puede navegar, debe subir nuevo comprobante
    $navegar = false;

} elseif ($es_pendiente && $pendiente_vencido) {
    // Pago pendiente pero ya pasaron 72hs → bloquear hasta que admin actúe
    $navegar = false;

} elseif ($es_pendiente && !$pendiente_vencido) {
    // Pago pendiente dentro de las 72hs → puede navegar normalmente
    $navegar = true;

} elseif ($gracia) {
    // Plan en período de gracia (vencido hace ≤3 días) → puede navegar con aviso
    $navegar = true;

} elseif ($estado === 'activo') {
    // Plan activo y vigente → navega sin restricciones
    $navegar = true;

} else {
    // Sin plan / pendiente_nuevo / cualquier otro estado desconocido
    $navegar = false;
}

// ── Chip de estado visual ────────────────────────────────────────────────────
$chip_cls = match(true) {
    $estado === 'activo'                    => 'activo',
    $gracia                                 => 'gracia',
    $bloq                                   => 'bloqueado',
    $es_rechazado                           => 'rechazado',
    $es_pendiente && $pendiente_vencido     => 'pendiente_vencido',
    $es_pendiente                           => 'pendiente_pago',
    default                                 => 'sin_plan',
};

// Helper: genera href o bloqueo
function navHref(bool $puede, string $url): string {
    return $puede
        ? "href=\"{$url}\""
        : 'href="#" onclick="bloqClick(event)"';
}

// Helper: texto plural simple
function _p(string $singular, string $plural, int $n): string {
    return sprintf($n === 1 ? $singular : $plural, $n);
}

// Helper: selector de idioma con banderas (sidebar y overlays). Cambia sin cerrar sesión.
function langSwitch(string $extraCls = ''): string {
    $actual = $_SESSION['lang'] ?? 'es';
    $html = '';
    foreach (Lang::IDIOMAS as $code => [$nombre, $flag]) {
        $cls  = $code === $actual ? 'sb-lang-btn active' : 'sb-lang-btn';
        $html .= "<button type=\"button\" class=\"{$cls}\" title=\"{$nombre}\" onclick=\"cambiarIdioma('{$code}')\">"
               . "<img src=\"https://flagcdn.com/w20/{$flag}.png\" alt=\"\"> " . strtoupper($code) . "</button>";
    }
    return "<div class=\"sb-lang {$extraCls}\">{$html}</div>";
}

$dias_esperando = intdiv($horas_esperando, 24);

// Tema guardado en sesión (se carga desde configuracion_json al iniciar sesión)
$tema = ($_SESSION['tema'] ?? 'oscuro') === 'claro' ? 'claro' : 'oscuro';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>" class="tema-<?= $tema ?>">
<head>
<meta charset="UTF-8">
<link href="assets/css/menu.css" rel="stylesheet">
<link href="assets/css/tema-claro.css?v=<?= @filemtime(__DIR__ . '/../assets/css/tema-claro.css') ?>" rel="stylesheet">
<script src="assets/js/tema.js?v=<?= @filemtime(__DIR__ . '/../assets/js/tema.js') ?>"></script>
<style>
    :root {
        --primary: #667eea;
        --secondary: #764ba2;
        --accent:     #7274ee;
        --text-primary: #f5f5f5;
        --text-muted: rgba(255,255,255,0.55);
        --card-shadow: 0 10px 40px rgba(0,0,0,0.35);
        --bg-base: #1a1a2e;
        --bg-deep: #0f1629;
    }

    html, body {
        background: var(--bg-base) !important;
        color: #e2e8f0 !important;
        min-height: 100vh;
        font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
    }

    main {
        margin-left: 280px;
        padding: 0;
        background: var(--bg-base) !important;
        min-height: 100vh;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    main.min-main { margin-left: 85px; }

    /* ── Chips de estado del plan ─────────────────────────────────────── */
    .plan-chip {
        display:flex; align-items:center; gap:8px;
        margin:6px 12px; padding:8px 12px;
        border-radius:10px; font-size:.72rem; font-weight:700;
        text-decoration:none; cursor:pointer;
    }
    .plan-chip.activo          { background:rgba(34,197,94,.12);  color:#22c55e; border:1px solid rgba(34,197,94,.25); }
    .plan-chip.gracia          { background:rgba(245,158,11,.12); color:#f59e0b; border:1px solid rgba(245,158,11,.28); animation:blink 2s infinite; }
    .plan-chip.bloqueado       { background:rgba(239,68,68,.12);  color:#ef4444; border:1px solid rgba(239,68,68,.25);  animation:blink 1.5s infinite; }
    .plan-chip.sin_plan        { background:rgba(100,116,139,.1); color:#94a3b8; border:1px solid rgba(100,116,139,.2); }
    .plan-chip.rechazado       { background:rgba(239,68,68,.12);  color:#ef4444; border:1px solid rgba(239,68,68,.25);  animation:blink 1.8s infinite; }
    .plan-chip.pendiente_pago  { background:rgba(56,189,248,.1);  color:#38bdf8; border:1px solid rgba(56,189,248,.2);  animation:blink 2.5s infinite; }
    .plan-chip.pendiente_vencido { background:rgba(239,68,68,.12); color:#ef4444; border:1px solid rgba(239,68,68,.25); animation:blink 1.8s infinite; }

    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.55} }

    .chip-txt { flex:1; line-height:1.3; }
    .chip-t   { display:block; font-weight:700; }
    .chip-s   { display:block; font-size:.65rem; opacity:.7; margin-top:1px; }

    /* ── Nav bloqueado ────────────────────────────────────────────────── */
    .nav-bloq > a { opacity:.28 !important; cursor:not-allowed !important; pointer-events:none; }

    /* ── Overlays ─────────────────────────────────────────────────────── */
    #bloqOverlay,
    #rechazadoOverlay,
    #pendienteOverlay,
    #pendienteVencidoOverlay {
        display:none; position:fixed; inset:0; z-index:9999;
        background:rgba(8,8,18,.94); backdrop-filter:blur(8px);
        align-items:center; justify-content:center;
    }
    #bloqOverlay.show,
    #rechazadoOverlay.show,
    #pendienteOverlay.show,
    #pendienteVencidoOverlay.show { display:flex; }

    .bloq-card {
        background:#16213e; border:1px solid rgba(239,68,68,.28);
        border-radius:22px; padding:40px 34px; max-width:420px; width:92%;
        text-align:center; position:relative; animation:popIn .25s ease;
    }
    .bloq-card.azul  { border-color:rgba(56,189,248,.28); }
    .bloq-card.naranja { border-color:rgba(245,158,11,.28); }

    @keyframes popIn { from{transform:scale(.9) translateY(14px);opacity:0} to{transform:scale(1) translateY(0);opacity:1} }

    .bloq-card::before {
        content:''; position:absolute; top:0; left:0; right:0; height:3px;
        background:linear-gradient(90deg,#ef4444,#f97316); border-radius:22px 22px 0 0;
    }
    .bloq-card.azul::before    { background:linear-gradient(90deg,#38bdf8,#818cf8); }
    .bloq-card.naranja::before { background:linear-gradient(90deg,#f59e0b,#f97316); }

    .bloq-ico {
        width:66px; height:66px; border-radius:16px;
        background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.2);
        display:flex; align-items:center; justify-content:center;
        font-size:1.65rem; margin:0 auto 16px;
    }
    .bloq-ico.azul    { background:rgba(56,189,248,.1);  border-color:rgba(56,189,248,.2); }
    .bloq-ico.naranja { background:rgba(245,158,11,.1);  border-color:rgba(245,158,11,.2); }

    .bloq-card h2  { font-size:1.18rem; font-weight:800; color:#fff; margin:0 0 10px; }
    .bloq-card p   { font-size:.84rem; color:rgba(255,255,255,.5); line-height:1.65; margin:0 0 22px; }
    .bloq-card p strong { color:#ef4444; }
    .bloq-card.azul p strong    { color:#38bdf8; }
    .bloq-card.naranja p strong { color:#f59e0b; }

    .motivo-box {
        background:rgba(239,68,68,.06); border:1px solid rgba(239,68,68,.2);
        border-radius:10px; padding:12px 14px; margin:0 0 20px; text-align:left;
    }
    .motivo-box.azul   { background:rgba(56,189,248,.06);  border-color:rgba(56,189,248,.2); }
    .motivo-box.naranja{ background:rgba(245,158,11,.06);  border-color:rgba(245,158,11,.2); }
    .motivo-box .motivo-lbl {
        font-size:.68rem; color:rgba(239,68,68,.7); font-weight:700;
        text-transform:uppercase; letter-spacing:.8px; margin-bottom:4px;
    }
    .motivo-box.azul .motivo-lbl    { color:rgba(56,189,248,.8); }
    .motivo-box.naranja .motivo-lbl { color:rgba(245,158,11,.8); }
    .motivo-box .motivo-txt { font-size:.82rem; color:rgba(255,255,255,.7); line-height:1.5; }

    .btn-renovar {
        display:flex; align-items:center; justify-content:center; gap:8px;
        background:linear-gradient(135deg,#ef4444,#f97316); color:#fff;
        border:none; border-radius:12px; padding:13px; font-size:.9rem;
        font-weight:700; cursor:pointer; text-decoration:none; width:100%; transition:all .2s;
    }
    .btn-renovar:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(239,68,68,.4); color:#fff; }
    .btn-renovar.azul    { background:linear-gradient(135deg,#38bdf8,#818cf8); }
    .btn-renovar.azul:hover    { box-shadow:0 8px 22px rgba(56,189,248,.35); }
    .btn-renovar.naranja { background:linear-gradient(135deg,#f59e0b,#d97706); }
    .btn-renovar.naranja:hover { box-shadow:0 8px 22px rgba(245,158,11,.35); }

    .btn-contacto {
        display:flex; align-items:center; justify-content:center; gap:8px;
        background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12);
        color:rgba(255,255,255,.65); border-radius:12px; padding:11px; font-size:.85rem;
        font-weight:600; cursor:pointer; text-decoration:none; width:100%;
        transition:all .2s; margin-top:8px;
    }
    .btn-contacto:hover { background:rgba(255,255,255,.1); color:#fff; border-color:rgba(255,255,255,.22); }

    .bloq-links { display:flex; gap:8px; margin-top:10px; }
    .bloq-lnk {
        flex:1; padding:9px 6px; border-radius:10px;
        border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.04);
        color:rgba(255,255,255,.45); font-size:.75rem; font-weight:600;
        text-decoration:none; display:flex; align-items:center; justify-content:center;
        gap:5px; transition:all .2s;
    }
    .bloq-lnk:hover { background:rgba(255,255,255,.09); color:#fff; }

    /* ── Steps (overlay pendiente normal) ────────────────────────────── */
    .steps-wrap { display:flex; flex-direction:column; gap:8px; margin:0 0 20px; text-align:left; }
    .step-item  { display:flex; align-items:center; gap:10px; font-size:.8rem; color:rgba(255,255,255,.55); }
    .step-num {
        width:22px; height:22px; border-radius:50%;
        background:rgba(56,189,248,.15); border:1px solid rgba(56,189,248,.3);
        color:#38bdf8; font-size:.68rem; font-weight:800;
        display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .step-num.done { background:rgba(34,197,94,.15); border-color:rgba(34,197,94,.3); color:#22c55e; }

    /* ── Barra de progreso de horas ───────────────────────────────────── */
    .horas-barra-wrap { margin:0 0 20px; text-align:left; }
    .horas-barra-label {
        font-size:.72rem; color:rgba(255,255,255,.45);
        display:flex; justify-content:space-between; margin-bottom:6px;
    }
    .horas-barra-label strong { color:#f59e0b; }
    .horas-track {
        height:7px; background:rgba(255,255,255,.08);
        border-radius:50px; overflow:hidden;
    }
    .horas-fill {
        height:100%; border-radius:50px;
        background:linear-gradient(90deg,#f59e0b,#ef4444);
        transition:width .6s ease;
    }

    /* ── Toast gracia ─────────────────────────────────────────────────── */
    #graciaToast { display:none; position:fixed; bottom:26px; left:50%; transform:translateX(-50%); z-index:8000; width:92%; max-width:450px; }
    #graciaToast.show { display:block; }
    .gracia-inner {
        background:#1c1c32; border:1px solid rgba(245,158,11,.3);
        border-radius:16px; padding:18px 20px; display:flex; gap:14px;
        align-items:flex-start; position:relative;
        animation:slideUp .3s cubic-bezier(.22,1,.36,1);
    }
    @keyframes slideUp { from{transform:translateY(28px);opacity:0} to{transform:translateY(0);opacity:1} }
    .gracia-inner::before {
        content:''; position:absolute; top:0; left:0; right:0; height:3px;
        background:linear-gradient(90deg,#f59e0b,#fbbf24); border-radius:16px 16px 0 0;
    }
    .gracia-ico { width:40px; height:40px; border-radius:10px; background:rgba(245,158,11,.14); display:flex; align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; }
    .gracia-body { flex:1; }
    .gracia-body h4 { font-size:.87rem; font-weight:800; color:#fff; margin:0 0 4px; }
    .gracia-body p  { font-size:.77rem; color:rgba(255,255,255,.5); margin:0 0 12px; line-height:1.5; }
    .gracia-btns { display:flex; gap:8px; }
    .btn-g-pagar {
        background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; border:none;
        border-radius:8px; padding:8px 15px; font-size:.79rem; font-weight:700;
        cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:5px; transition:all .2s;
    }
    .btn-g-pagar:hover { color:#fff; transform:translateY(-1px); }
    .btn-g-cerrar {
        background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.09);
        color:rgba(255,255,255,.4); border-radius:8px; padding:8px 12px; font-size:.77rem;
        font-weight:600; cursor:pointer; transition:all .2s;
    }
    .btn-g-cerrar:hover { background:rgba(255,255,255,.1); color:#fff; }
    .toast-x { background:none; border:none; color:rgba(255,255,255,.18); font-size:.9rem; cursor:pointer; flex-shrink:0; transition:color .2s; }
    .toast-x:hover { color:rgba(255,255,255,.6); }

    /* ── Selector de idioma (sidebar + overlays) ─────────────────────── */
    .sb-lang-wrap { display:flex; flex-direction:column; align-items:flex-start; gap:6px; padding:6px 20px 4px; color:inherit; font-size:.85rem; }
    .sb-lang-wrap .info { display:flex; align-items:center; gap:10px; }
    .sb-lang { display:flex; flex-wrap:wrap; gap:5px; }
    .sb-lang-btn {
        display:flex; align-items:center; gap:4px; padding:4px 9px; border-radius:20px;
        border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.05);
        color:rgba(255,255,255,.65); font-size:.7rem; font-weight:700; cursor:pointer; transition:all .2s;
    }
    .sb-lang-btn img { width:16px; border-radius:2px; }
    .sb-lang-btn:hover { background:rgba(255,255,255,.12); color:#fff; }
    .sb-lang-btn.active { background:#4fc3f7; border-color:#4fc3f7; color:#0f172a; }
    .barra-lateral.mini-barra-lateral .sb-lang-wrap .info span,
    .barra-lateral.mini-barra-lateral .sb-lang-btn img { display:none; }
    .barra-lateral.mini-barra-lateral .sb-lang-wrap { flex-direction:column; gap:6px; padding:6px 0; }
    .barra-lateral.mini-barra-lateral .sb-lang { flex-direction:column; }
    .bloq-card .sb-lang { justify-content:center; margin:-14px 0 16px; }
    .bloq-card .sb-lang-btn { padding:3px 7px; font-size:.65rem; }

    /* ── Overlay: lista "qué puedo hacer" ────────────────────────────── */
    .opciones-wrap { text-align:left; margin:0 0 20px; }
    .opciones-wrap .opciones-lbl { font-size:.68rem; color:rgba(255,255,255,.45); font-weight:700; text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px; }
    .opcion { display:flex; gap:10px; align-items:flex-start; font-size:.8rem; color:rgba(255,255,255,.7); line-height:1.5; margin-bottom:8px; }
    .opcion .step-num { margin-top:1px; }

    /* ── Modal de soporte (encima de los overlays) ───────────────────── */
    #soporteModal {
        display:none; position:fixed; inset:0; z-index:10001;
        background:rgba(8,8,18,.7); backdrop-filter:blur(4px);
        align-items:center; justify-content:center; padding:16px;
    }
    #soporteModal.show { display:flex; }
    .sop-card {
        background:#16213e; border:1px solid rgba(56,189,248,.28); border-radius:20px;
        width:100%; max-width:460px; max-height:92vh; overflow-y:auto;
        padding:26px 24px 20px; position:relative; color:#fff; animation:popIn .2s ease;
    }
    .sop-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#38bdf8,#818cf8); border-radius:20px 20px 0 0; }
    .sop-x { position:absolute; top:12px; right:14px; background:none; border:none; color:rgba(255,255,255,.4); font-size:1.1rem; cursor:pointer; }
    .sop-x:hover { color:#fff; }
    .sop-card h3 { font-size:1.05rem; font-weight:800; margin:0 0 4px; padding-right:24px; }
    .sop-sub { font-size:.8rem; color:rgba(255,255,255,.5); margin:0 0 16px; line-height:1.5; }
    .sop-lbl { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:rgba(56,189,248,.85); margin:0 0 6px; }
    .sop-txt {
        width:100%; min-height:110px; resize:vertical; border-radius:12px; padding:10px 12px;
        background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.14); color:#fff; font-size:.85rem; font-family:inherit;
    }
    .sop-txt:focus { outline:none; border-color:#38bdf8; }
    .sop-err { color:#f87171; font-size:.75rem; min-height:1em; margin:6px 0 0; }
    .sop-ok  { background:rgba(34,197,94,.1); border:1px solid rgba(34,197,94,.3); color:#86efac; border-radius:10px; padding:8px 12px; font-size:.8rem; margin:0 0 14px; }
    .sop-estado { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:800; margin:0 0 14px; }
    .sop-estado.pendiente   { background:rgba(245,158,11,.15); color:#fbbf24; }
    .sop-estado.en_revision { background:rgba(56,189,248,.15); color:#38bdf8; }
    .sop-estado.resuelta    { background:rgba(34,197,94,.15);  color:#4ade80; }
    .sop-timeline { display:flex; justify-content:space-between; position:relative; margin:0 6px 18px; }
    .sop-timeline::before { content:''; position:absolute; top:11px; left:12px; right:12px; height:2px; background:rgba(255,255,255,.12); }
    .sop-paso { position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; gap:5px; font-size:.68rem; color:rgba(255,255,255,.45); width:80px; text-align:center; }
    .sop-paso i { width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-style:normal; font-weight:800; font-size:.7rem; background:#16213e; border:2px solid rgba(255,255,255,.2); }
    .sop-paso.hecho { color:#fff; }
    .sop-paso.hecho i { background:#38bdf8; border-color:#38bdf8; color:#0f172a; }
    .sop-caja { background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1); border-radius:12px; padding:10px 12px; margin:0 0 12px; font-size:.82rem; line-height:1.5; color:rgba(255,255,255,.8); white-space:pre-wrap; }
    .sop-caja.resp { border-color:rgba(34,197,94,.3); background:rgba(34,197,94,.06); }
    .sop-fecha { font-size:.7rem; color:rgba(255,255,255,.4); margin:-6px 0 12px; }
    .sop-correo { border-top:1px solid rgba(255,255,255,.08); margin-top:16px; padding-top:12px; font-size:.75rem; color:rgba(255,255,255,.5); text-align:center; }
    .sop-correo a { color:#38bdf8; }
    .sop-link { background:none; border:none; color:#38bdf8; font-size:.78rem; cursor:pointer; padding:0; text-decoration:underline; }
    .sop-badge { margin-left:auto; min-width:20px; padding:1px 6px; border-radius:20px; background:#ef4444; color:#fff; font-size:.68rem; text-align:center; }
    .btn-soporte { font-family:inherit; }

    /* ── Avatar del usuario en el menú ──────────────────────────────── */
    .usuario-avatar { display:inline-flex; margin-right:12px; text-decoration:none; border-radius:50%;
                      box-shadow:0 0 0 2px rgba(255,255,255,.25); transition:transform .2s; }
    .usuario-avatar:hover { transform:scale(1.06); }
    .barra-lateral.mini-barra-lateral .usuario-avatar { margin:0 auto; }

    /* ── Nav activo ───────────────────────────────────────────────────── */
    .navegacion a.active {
        background: #3a5f7f;
        border-left: 4px solid #4fc3f7;
    }
</style>
</head>
<body>

<div class="menu">
    <ion-icon name="menu-outline"></ion-icon>
    <ion-icon name="close-outline"></ion-icon>
</div>

<div class="barra-lateral">
<div>
    <div class="nombre-pagina">
        <ion-icon id="logo" name="logo-firefox"></ion-icon>
        <span><?= Lang::get('app.nombre') ?></span>
    </div>


<!-- ══════════ ROL 1: CLIENTE ══════════════════════════════════ -->
<?php if ($rol === 1): ?>
</div><!-- cierre div interno -->

<!-- ── Chip de estado del plan ──────────────────────────────────────────── -->
<a class="plan-chip <?= $chip_cls ?>" href="index.php?c=pago&a=Index">
    <span>
        <?php
        if ($bloq)                              echo '🔒';
        elseif ($gracia)                        echo '⚠️';
        elseif ($es_rechazado)                  echo '❌';
        elseif ($es_pendiente && $pendiente_vencido) echo '💳';
        elseif ($es_pendiente)                  echo '🕐';
        else                                    echo '✅';
        ?>
    </span>
    <span class="chip-txt">
        <span class="chip-t">
            <?php if ($bloq): ?>
                <?= Lang::get('plan.vencido_renovar') ?>
            <?php elseif ($gracia): ?>
                <?= _p(Lang::get('plan.gracia_dias'), Lang::get('plan.gracia_dias_plural'), $d_gracia) ?>
            <?php elseif ($es_rechazado): ?>
                <?= Lang::get('plan.pago_rechazado') ?>
            <?php elseif ($es_pendiente && $pendiente_vencido): ?>
                <?= Lang::get('plan.verificacion_demorada') ?>
            <?php elseif ($es_pendiente): ?>
                <?= Lang::get('plan.pago_revision') ?>
            <?php else: ?>
                <?= $nombre ?>
            <?php endif; ?>
        </span>
        <span class="chip-s">
            <?php if ($bloq): ?>
                <?= sprintf(Lang::get('plan.hace_dias_vencido'), $d_venc) ?>
            <?php elseif ($gracia): ?>
                <?= _p(Lang::get('plan.vencido_hace_dia'), Lang::get('plan.vencido_hace_dias'), $d_venc) ?>
            <?php elseif ($es_rechazado): ?>
                <?= Lang::get('plan.ver_motivo') ?>
            <?php elseif ($es_pendiente && $pendiente_vencido): ?>
                <?= Lang::get('plan.pago_sin_confirmar') ?>
            <?php elseif ($es_pendiente): ?>
                <?= sprintf(Lang::get('plan.horas_restantes_verificacion'), $horas_restantes) ?>
            <?php else: ?>
                <?= Lang::get('plan.plan_activo') ?>
            <?php endif; ?>
        </span>
    </span>
</a>

<nav class="navegacion">
<ul>
    <!-- Inicio — siempre libre -->
    <li>
        <a href="index.php?c=inicio&a=Inicio">
            <ion-icon name="home-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.inicio') ?></span>
        </a>
    </li>

    <!-- Buscar — bloqueado si no puede navegar -->
    <li <?= !$navegar ? 'class="nav-bloq"' : '' ?>>
        <a <?= navHref($navegar, 'index.php?c=buscar&a=Index') ?>>
            <ion-icon name="search-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.buscar') ?></span>
        </a>
    </li>

    <!-- Recomendaciones IA — bloqueado si no puede navegar -->
    <li <?= !$navegar ? 'class="nav-bloq"' : '' ?>>
        <a <?= navHref($navegar, 'index.php?c=recomendaciones&a=index') ?>>
            <ion-icon name="sparkles-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.recomendaciones') ?></span>
        </a>
    </li>

    <!-- Historial — SIEMPRE libre -->
    <li>
        <a href="index.php?c=historial&a=Index">
            <ion-icon name="time-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.historial') ?></span>
        </a>
    </li>

    <!-- Itinerarios — bloqueado si no puede navegar -->
    <li <?= !$navegar ? 'class="nav-bloq"' : '' ?>>
        <a <?= navHref($navegar, 'index.php?c=itinerarios&a=Index') ?>>
            <ion-icon name="calendar-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.itinerarios') ?></span>
        </a>
    </li>

    <!-- Favoritos — SIEMPRE libre -->
    <li>
        <a href="index.php?c=favoritos&a=Index">
            <ion-icon name="heart-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.favoritos') ?></span>
        </a>
    </li>

    <!-- Reseñas — bloqueado si no puede navegar -->
    <li <?= !$navegar ? 'class="nav-bloq"' : '' ?>>
        <a <?= navHref($navegar, 'index.php?c=resenas&a=Index') ?>>
            <ion-icon name="star-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.resenas') ?></span>
        </a>
    </li>

    <!-- Mi Perfil — bloqueado si no puede navegar -->
    <li <?= !$navegar ? 'class="nav-bloq"' : '' ?>>
        <a <?= navHref($navegar, 'index.php?c=perfil&a=Index') ?>>
            <ion-icon name="person-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.perfil') ?></span>
        </a>
    </li>

    <!-- Mis Planes — SIEMPRE libre -->
    <li>
        <a href="index.php?c=pago&a=Index">
            <ion-icon name="card-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.planes') ?></span>
        </a>
    </li>

    <!-- Cerrar sesión -->
    <li>
        <a href="index.php?c=login&a=Logout" class="logout-btn">
            <ion-icon name="log-out-outline"></ion-icon>
            <span><?= Lang::get('nav_cliente.cerrar_sesion') ?></span>
        </a>
    </li>
</ul>
</nav>
<?php endif; /* fin rol 1 */ ?>


<!-- ══════════ ROL 2: ADMIN ════════════════════════════════════ -->
<?php if ($rol === 2 || $rol === 3): ?>

</div><!-- cierre div interno -->
<nav class="navegacion">
<ul>
    <?php if ($rol === 2): ?>
    <li><a href="index.php?c=admin&a=Inicio">
        <ion-icon name="apps-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.dashboard') ?></span></a>
    </li>

    <?php endif; ?>
    <li><a href="index.php?c=admin&a=Usuarios">
        <ion-icon name="people-circle-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.clientes_pagos') ?></span></a>
    </li>
    <?php if ($rol === 2): ?>

    <li><a href="index.php?c=adminsoporte&a=Index">
        <ion-icon name="chatbubbles-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.soporte') ?></span>
        <b id="sopBadge" class="sop-badge" hidden></b></a>
    </li>

    <li><a href="index.php?c=adminconsentimientos&a=Index">
        <ion-icon name="shield-checkmark-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.consentimientos') ?></span></a>
    </li>

    <?php endif; ?>
    <li><a href="index.php?c=adminFinanzas&a=Index">
        <ion-icon name="cash-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.finanzas') ?></span></a>
    </li>
    <?php if ($rol === 2): ?>

    <li><a href="index.php?c=admincategorias&a=Index">
        <ion-icon name="folder-open-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.categorias') ?></span></a>
    </li>

    <li><a href="index.php?c=adminatracciones&a=Index">
        <ion-icon name="location-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.atracciones') ?></span></a>
    </li>

    <li><a href="index.php?c=admincontextorelacion&a=Index">
        <ion-icon name="git-network-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.contexto') ?></span></a>
    </li>

    <li><a href="index.php?c=adminCatalogoParametros&a=Index">
        <ion-icon name="settings-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.catalogo') ?></span></a>
    </li>

    <li><a href="index.php?c=adminrecomendaciones&a=Index">
        <ion-icon name="sparkles-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.recomendaciones') ?></span></a>
    </li>

    <li><a href="index.php?c=adminbacktesting&a=Index">
        <ion-icon name="flask-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.backtesting') ?></span></a>
    </li>

    <li><a href="index.php?c=adminmodelosmr&a=Index">
        <ion-icon name="hardware-chip-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.modelo_mr') ?></span></a>
    </li>

    <li><a href="index.php?c=admingestionusuarios&a=Index">
        <ion-icon name="shield-checkmark-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.gestion_usuarios') ?></span></a>
    </li>
    <?php endif; ?>

    <li><a href="index.php?c=login&a=Logout" class="logout-btn">
        <ion-icon name="log-out-outline"></ion-icon>
        <span><?= Lang::get('nav_admin.cerrar_sesion') ?></span></a>
    </li>
</ul>
</nav>

<?php endif; ?>


<!-- Parte inferior (común) -->
<div>
    <div class="linea"></div>
    <div class="sb-lang-wrap">
        <div class="info">
            <ion-icon name="language-outline"></ion-icon>
            <span><?= Lang::get('sidebar.idioma') ?></span>
        </div>
        <?= langSwitch() ?>
    </div>
    <div class="modo-oscuro">
        <div class="info">
            <ion-icon name="moon-outline"></ion-icon>
            <span><?= Lang::get('sidebar.dark_mode') ?></span>
        </div>
        <div class="switch" role="switch" aria-checked="<?= $tema === 'oscuro' ? 'true' : 'false' ?>"
             title="<?= Lang::get('sidebar.dark_mode') ?>"><div class="base"><div class="circulo<?= $tema === 'oscuro' ? ' prendido' : '' ?>"></div></div></div>
    </div>
    <div class="usuario">
        <?php
        require_once __DIR__ . '/../model/avatar.php';
        // Sesiones abiertas antes de existir el avatar en sesión: se lee una sola vez de la BD
        if (!array_key_exists('avatar', $_SESSION) && !empty($_SESSION['user_id']) && class_exists('Database')) {
            try {
                $st = Database::Conectar()->prepare("SELECT apellido, configuracion_json FROM usuarios.usuarios WHERE id = ?");
                $st->execute([(int)$_SESSION['user_id']]);
                if ($fila = $st->fetch(PDO::FETCH_ASSOC)) {
                    $_SESSION['avatar']           = Avatar::deConfig($fila['configuracion_json']);
                    $_SESSION['session_apellido'] = $fila['apellido'] ?? '';
                }
            } catch (Throwable $e) { $_SESSION['avatar'] = null; }
        }
        ?>
        <a href="<?= $rol === 1 ? 'index.php?c=perfil&a=Index' : ($rol === 3 ? 'index.php?c=adminFinanzas&a=Index' : 'index.php?c=admingestionusuarios&a=Index') ?>" class="usuario-avatar" title="<?= htmlspecialchars($session_usuario) ?>">
            <?= Avatar::html($_SESSION['avatar'] ?? null, $session_usuario, $_SESSION['session_apellido'] ?? '', 45) ?>
        </a>
        <div class="info-usuario">
            <div class="nombre-email">
                <span class="nombre"><?= htmlspecialchars($session_usuario) ?></span>
                <span class="email">
                    <?php if ($rol === 2): ?>
                        <?= Lang::get('sidebar.administrador') ?>
                    <?php elseif ($rol === 3): ?>
                        <?= Lang::get('sidebar.finanzas') ?>
                    <?php elseif ($bloq): ?>
                        <?= Lang::get('sidebar.plan_vencido') ?>
                    <?php elseif ($gracia): ?>
                        <?= sprintf(Lang::get('sidebar.dias_gracia'), $d_gracia) ?>
                    <?php elseif ($es_rechazado): ?>
                        <?= Lang::get('sidebar.pago_rechazado') ?>
                    <?php elseif ($es_pendiente && $pendiente_vencido): ?>
                        <?= Lang::get('sidebar.verificacion_demorada') ?>
                    <?php elseif ($es_pendiente): ?>
                        <?= Lang::get('sidebar.pago_revision') ?>
                    <?php elseif ($estado === 'activo'): ?>
                        <?= $nombre ?>
                    <?php else: ?>
                        <?= Lang::get('sidebar.sin_plan') ?>
                    <?php endif; ?>
                </span>
            </div>
            <ion-icon name="ellipsis-vertical-outline"></ion-icon>
        </div>
    </div>
</div>

</div><!-- fin .barra-lateral -->


<!-- ═══════════════════════════════════════════════════════════════════
     OVERLAY 1 — BLOQUEO TOTAL (plan vencido > 3 días sin pagar)
════════════════════════════════════════════════════════════════════ -->
<?php if ($aplica && $bloq): ?>
<div id="bloqOverlay">
    <div class="bloq-card">
        <div class="bloq-ico">🔒</div>
        <h2><?= Lang::get('overlay_bloq.titulo') ?></h2>
        <p><?= sprintf(Lang::get('overlay_bloq.descripcion'), $nombre, $d_venc) ?></p>
        <a href="index.php?c=pago&a=Index" class="btn-renovar">
            <?= Lang::get('overlay_bloq.btn_renovar') ?>
        </a>
        <div class="bloq-links">
            <a href="index.php?c=historial&a=Index" class="bloq-lnk"><?= Lang::get('overlay_bloq.link_historial') ?></a>
            <a href="index.php?c=favoritos&a=Index" class="bloq-lnk"><?= Lang::get('overlay_bloq.link_favoritos') ?></a>
            <a href="index.php?c=login&a=Logout"    class="bloq-lnk"><?= Lang::get('overlay_bloq.link_salir') ?></a>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════════════
     OVERLAY 2 — PAGO RECHAZADO
════════════════════════════════════════════════════════════════════ -->
<?php if ($aplica && $es_rechazado): ?>
<div id="rechazadoOverlay">
    <div class="bloq-card">
        <div class="bloq-ico">❌</div>
        <h2><?= Lang::get('overlay_rechazado.titulo') ?></h2>
        <p><?= sprintf(Lang::get('overlay_rechazado.descripcion'), $pago_plan) ?></p>

        <div class="motivo-box">
            <div class="motivo-lbl"><?= Lang::get('overlay_rechazado.motivo_lbl') ?></div>
            <div class="motivo-txt">
                <?= $motivo_rechazo ?: Lang::get('overlay_rechazado.sin_motivo') ?>
            </div>
        </div>

        <a href="index.php?c=pago&a=Index" class="btn-renovar">
            <?= Lang::get('overlay_rechazado.btn_comprobante') ?>
        </a>
        <button type="button" class="btn-contacto btn-soporte" onclick="abrirSoporte('pago_rechazado')">
            <?= Lang::get('overlay_rechazado.btn_contacto') ?>
        </button>
        <div class="bloq-links" style="margin-top:12px;">
            <a href="index.php?c=login&a=Logout" class="bloq-lnk"><?= Lang::get('overlay_rechazado.link_salir') ?></a>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════════════
     OVERLAY 3 — PAGO PENDIENTE NORMAL (dentro de las 72hs)
     Solo se muestra si el usuario intenta acceder a rutas bloqueadas,
     NO al entrar al inicio. El JS controla cuándo mostrarlo.
════════════════════════════════════════════════════════════════════ -->
<?php if ($aplica && $es_pendiente && !$pendiente_vencido): ?>
<div id="pendienteOverlay">
    <div class="bloq-card azul">
        <div class="bloq-ico azul">🕐</div>
        <h2><?= Lang::get('overlay_pendiente.titulo') ?></h2>
        <p><?= sprintf(Lang::get('overlay_pendiente.descripcion'), $pago_plan) ?></p>

        <div class="steps-wrap">
            <div class="step-item">
                <div class="step-num done">✓</div>
                <span><?= Lang::get('overlay_pendiente.step1') ?></span>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <span>
                    <?= Lang::get('overlay_pendiente.step2') ?>
                    <span style="color:#38bdf8;font-weight:700;">
                        <?= Lang::get('overlay_pendiente.step2_estado') ?>
                    </span>
                </span>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <span><?= Lang::get('overlay_pendiente.step3') ?></span>
            </div>
        </div>

        <!-- Barra de tiempo restante -->
        <?php
            $pct_horas = $horas_restantes > 0
                ? min(100, round(($horas_restantes / 72) * 100))
                : 0;
        ?>
        <div class="horas-barra-wrap">
            <div class="horas-barra-label">
                <span><?= Lang::get('overlay_pendiente.tiempo_restante') ?></span>
                <strong><?= $horas_restantes ?>h <?= Lang::get('overlay_pendiente.restantes') ?></strong>
            </div>
            <div class="horas-track">
                <div class="horas-fill" style="width:<?= $pct_horas ?>%;"></div>
            </div>
        </div>

        <a href="index.php?c=pago&a=Index" class="btn-renovar azul">
            <?= Lang::get('overlay_pendiente.btn_ver_pago') ?>
        </a>
        <button type="button" class="btn-contacto btn-soporte" onclick="abrirSoporte('consulta_pago')">
            <?= Lang::get('overlay_pendiente.btn_consultar') ?>
        </button>
        <div class="bloq-links" style="margin-top:12px;">
            <a href="index.php?c=login&a=Logout" class="bloq-lnk"><?= Lang::get('overlay_pendiente.link_salir') ?></a>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════════════
     OVERLAY 4 — PAGO PENDIENTE VENCIDO (pasaron las 72hs sin validar)
     El admin no respondió a tiempo. El usuario queda bloqueado.
════════════════════════════════════════════════════════════════════ -->
<?php if ($aplica && $es_pendiente && $pendiente_vencido): ?>
<div id="pendienteVencidoOverlay">
    <div class="bloq-card naranja">
        <?= langSwitch() ?>
        <div class="bloq-ico naranja">💳</div>
        <h2><?= Lang::get('overlay_pendiente_vencido.titulo') ?></h2>
        <p><?= sprintf(Lang::get('overlay_pendiente_vencido.descripcion'), $pago_plan) ?></p>

        <div class="motivo-box naranja">
            <div class="motivo-lbl"><?= Lang::get('overlay_pendiente_vencido.lbl_por_que') ?></div>
            <div class="motivo-txt">
                <?= _p(Lang::get('overlay_pendiente_vencido.por_que_dia'), Lang::get('overlay_pendiente_vencido.por_que_dias'), $dias_esperando) ?>
            </div>
        </div>

        <div class="opciones-wrap">
            <div class="opciones-lbl"><?= Lang::get('overlay_pendiente_vencido.lbl_que_hacer') ?></div>
            <div class="opcion"><div class="step-num">1</div><span><?= Lang::get('overlay_pendiente_vencido.opcion_pagar') ?></span></div>
            <div class="opcion"><div class="step-num">2</div><span><?= Lang::get('overlay_pendiente_vencido.opcion_ya_pague') ?></span></div>
        </div>

        <a href="index.php?c=pago&a=Index" class="btn-renovar naranja">
            <?= Lang::get('overlay_pendiente_vencido.btn_ver_pago') ?>
        </a>
        <button type="button" class="btn-contacto btn-soporte" onclick="abrirSoporte('pago_no_confirmado')">
            <?= Lang::get('overlay_pendiente_vencido.btn_contactar') ?>
        </button>
        <div class="bloq-links" style="margin-top:12px;">
            <a href="index.php?c=login&a=Logout" class="bloq-lnk"><?= Lang::get('overlay_pendiente_vencido.link_salir') ?></a>
        </div>
    </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════════════
     TOAST DE GRACIA (plan vencido, dentro de los 3 días de gracia)
════════════════════════════════════════════════════════════════════ -->
<?php if ($aplica && $gracia): ?>
<div id="graciaToast">
    <div class="gracia-inner">
        <div class="gracia-ico">⚠️</div>
        <div class="gracia-body">
            <h4><?= _p(Lang::get('toast_gracia.titulo'), Lang::get('toast_gracia.titulo_plural'), $d_venc) ?></h4>
            <p><?= _p(Lang::get('toast_gracia.descripcion'), Lang::get('toast_gracia.descripcion_plural'), $d_gracia) ?></p>
            <div class="gracia-btns">
                <a href="index.php?c=pago&a=Index" class="btn-g-pagar">
                    <?= Lang::get('toast_gracia.btn_renovar') ?>
                </a>
                <button class="btn-g-cerrar" onclick="cerrarGracia()">
                    <?= Lang::get('toast_gracia.btn_seguir') ?>
                </button>
            </div>
        </div>
        <button class="toast-x" onclick="cerrarGracia()">✕</button>
    </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════════════
     MODAL DE SOPORTE (cliente) — la solicitud llega a Administración → Soporte.
     Si ya hay una abierta, muestra su seguimiento en vez del formulario.
════════════════════════════════════════════════════════════════════ -->
<?php if ($aplica):
    $sopKeys = ['titulo','subtitulo','lbl_mensaje','msg_pago_no_confirmado','msg_consulta_pago','msg_pago_rechazado','msg_otro',
                'btn_enviar','enviando','o_correo','estado_pendiente','estado_en_revision','estado_resuelta',
                'paso_enviada','paso_revision','paso_resuelta','tu_mensaje','respuesta_admin','sin_respuesta',
                'enviada_el','ya_existia','enviada_ok','ultima_resuelta','nueva_solicitud','btn_ver','err_mensaje','err_conexion'];
    $SOP_T = [];
    foreach ($sopKeys as $k) $SOP_T[$k] = Lang::get("soporte.$k");
?>
<div id="soporteModal" onclick="if (event.target === this) cerrarSoporte()">
    <div class="sop-card" role="dialog" aria-modal="true" aria-labelledby="sopTitulo">
        <button type="button" class="sop-x" onclick="cerrarSoporte()" aria-label="Cerrar">✕</button>
        <h3 id="sopTitulo"><?= Lang::get('soporte.titulo') ?></h3>
        <div id="sopBody"></div>
        <div class="sop-correo">
            <?= Lang::get('soporte.o_correo') ?>
            <a href="mailto:k3vinfx@gmail.com?subject=<?= rawurlencode('Soporte Etreva — ' . ($_SESSION['session_usuario'] ?? '')) ?>">k3vinfx@gmail.com</a>
        </div>
    </div>
</div>
<?php endif; ?>

<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule  src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<?php if ($rol === 2): ?>
<script src="assets/js/admin-csv.js?v=<?= @filemtime(__DIR__ . '/../assets/js/admin-csv.js') ?>" defer></script>
<?php endif; ?>
<script>
// ── Variables de estado (calculadas en PHP, exportadas al JS) ──────────────
const ES_BLOQUEADO          = <?= json_encode($bloq)               ?>;
const ES_GRACIA             = <?= json_encode($gracia)             ?>;
const ES_RECHAZADO          = <?= json_encode($es_rechazado)       ?>;
const ES_PENDIENTE          = <?= json_encode($es_pendiente)       ?>;
const PENDIENTE_VENCIDO     = <?= json_encode($pendiente_vencido)  ?>;

// Rutas que SIEMPRE están libres para cualquier estado
const RUTAS_LIBRES_SIEMPRE  = ['pago', 'historial', 'favoritos', 'login'];
// Rutas libres cuando el pago está pendiente dentro de las 72hs
const RUTAS_LIBRES_PENDIENTE = ['pago', 'login', 'inicio', 'historial', 'favoritos'];

function controlador() {
    return (new URLSearchParams(window.location.search).get('c') || '').toLowerCase();
}

// Cambia el idioma en sesión + BD y recarga la misma página (sin cerrar sesión)
function cambiarIdioma(lang) {
    try { sessionStorage.setItem('etreva_lang', lang); } catch (e) {}
    const body = new URLSearchParams({ lang: lang });
    fetch('index.php?c=login&a=SetLang', { method: 'POST', body: body, credentials: 'same-origin' })
        .finally(() => window.location.reload());
}

function mostrarOverlay(id) {
    const o = document.getElementById(id);
    if (o) o.classList.add('show');
}

// Llamar desde los items del menú bloqueados
function bloqClick(e) {
    e.preventDefault();
    if (PENDIENTE_VENCIDO) {
        mostrarOverlay('pendienteVencidoOverlay');
    } else if (ES_PENDIENTE) {
        mostrarOverlay('pendienteOverlay');
    } else {
        mostrarOverlay('bloqOverlay');
    }
}

// ── Lógica principal de overlays al cargar la página ──────────────────────
(function () {
    const ruta = controlador();

    // 1. RECHAZADO — bloqueo fuerte, solo puede ir a pago o logout
    if (ES_RECHAZADO) {
        if (!['pago', 'login'].includes(ruta)) {
            mostrarOverlay('rechazadoOverlay');
        }
        return;
    }

    // 2. PENDIENTE VENCIDO — pasaron 72hs sin que el admin valide
    //    Bloquea todo excepto pago, historial, favoritos y logout
    if (PENDIENTE_VENCIDO) {
        if (!RUTAS_LIBRES_SIEMPRE.includes(ruta)) {
            mostrarOverlay('pendienteVencidoOverlay');
        }
        return;
    }

    // 3. PENDIENTE NORMAL — dentro de las 72hs, puede navegar
    //    Solo muestra el overlay si intenta ir a rutas no permitidas
    //    (en este caso, todas están permitidas, el overlay es informativo)
    if (ES_PENDIENTE) {
        // Con pendiente normal puede navegar libremente, no bloqueamos
        return;
    }

    // 4. BLOQUEADO — plan vencido hace más de 3 días
    if (ES_BLOQUEADO) {
        if (!RUTAS_LIBRES_SIEMPRE.includes(ruta)) {
            mostrarOverlay('bloqOverlay');
        }
        return;
    }

    // 5. GRACIA — el toast se maneja por separado abajo

})();

// ── Soporte interno (cliente) ──────────────────────────────────────────────
<?php if ($aplica): ?>
const SOP_T    = <?= json_encode($SOP_T, JSON_UNESCAPED_UNICODE) ?>;
const SOP_LANG = <?= json_encode($_SESSION['lang'] ?? 'es') ?>;
let sopTipo = 'pago_no_confirmado';

function sopEsc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }
function sopFecha(s) {
    if (!s) return '';
    const d = new Date(String(s).replace(' ', 'T'));
    const loc = { es: 'es-BO', en: 'en-US', pt: 'pt-BR', fr: 'fr-FR', de: 'de-DE' }[SOP_LANG] || 'es-BO';
    return isNaN(d) ? s : d.toLocaleString(loc, { dateStyle: 'medium', timeStyle: 'short' });
}

function abrirSoporte(tipo) {
    sopTipo = tipo || 'pago_no_confirmado';
    document.getElementById('soporteModal').classList.add('show');
    document.getElementById('sopBody').innerHTML = '<p class="sop-sub">…</p>';
    fetch('index.php?c=soporte&a=MisSolicitudes', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.message);
            if (res.abierta) return sopRenderSeguimiento(res.abierta);
            const ultima = (res.historial || []).find(s => s.estado === 'resuelta');
            sopRenderFormulario(ultima);
        })
        .catch(() => { document.getElementById('sopBody').innerHTML = `<p class="sop-err">${SOP_T.err_conexion}</p>`; });
}

function cerrarSoporte() { document.getElementById('soporteModal').classList.remove('show'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarSoporte(); });

function sopRenderFormulario(ultimaResuelta) {
    const previo = ultimaResuelta ? `
        <div class="sop-lbl">${SOP_T.ultima_resuelta}</div>
        <div class="sop-caja">${sopEsc(ultimaResuelta.mensaje)}</div>
        <div class="sop-caja resp"><b>${SOP_T.respuesta_admin}:</b>\n${sopEsc(ultimaResuelta.respuesta)}</div>` : '';
    document.getElementById('sopBody').innerHTML = `
        <p class="sop-sub">${SOP_T.subtitulo}</p>
        ${previo}
        <div class="sop-lbl">${SOP_T.lbl_mensaje}</div>
        <textarea id="sopMensaje" class="sop-txt" maxlength="2000">${sopEsc(SOP_T['msg_' + sopTipo] || SOP_T.msg_otro)}</textarea>
        <p id="sopErr" class="sop-err"></p>
        <button type="button" id="sopEnviar" class="btn-renovar azul" onclick="enviarSoporte()">${SOP_T.btn_enviar}</button>`;
}

function sopRenderSeguimiento(s, aviso) {
    const orden = ['pendiente', 'en_revision', 'resuelta'];
    const n = orden.indexOf(s.estado);
    const paso = (i, txt) => `<div class="sop-paso ${n >= i ? 'hecho' : ''}"><i>${n >= i ? '✓' : i + 1}</i>${txt}</div>`;
    document.getElementById('sopBody').innerHTML = `
        ${aviso ? `<div class="sop-ok">${aviso}</div>` : ''}
        <div class="sop-estado ${s.estado}">${SOP_T['estado_' + s.estado] || s.estado}</div>
        <div class="sop-timeline">${paso(0, SOP_T.paso_enviada)}${paso(1, SOP_T.paso_revision)}${paso(2, SOP_T.paso_resuelta)}</div>
        <div class="sop-lbl">${SOP_T.tu_mensaje}</div>
        <div class="sop-caja">${sopEsc(s.mensaje)}</div>
        <div class="sop-fecha">${SOP_T.enviada_el.replace('%s', sopFecha(s.created_at))}</div>
        <div class="sop-lbl">${SOP_T.respuesta_admin}</div>
        ${s.respuesta
            ? `<div class="sop-caja resp">${sopEsc(s.respuesta)}</div>`
            : `<p class="sop-sub">${SOP_T.sin_respuesta}</p>`}
        ${s.estado === 'resuelta' ? `<button type="button" class="sop-link" onclick="sopRenderFormulario()">${SOP_T.nueva_solicitud}</button>` : ''}`;
    sopActualizarBotones(s);
}

function enviarSoporte() {
    const msg = document.getElementById('sopMensaje').value.trim();
    const err = document.getElementById('sopErr');
    const btn = document.getElementById('sopEnviar');
    if (msg.length < 5) { err.textContent = SOP_T.err_mensaje; return; }

    btn.disabled = true; btn.textContent = SOP_T.enviando; err.textContent = '';
    fetch('index.php?c=soporte&a=Enviar', {
        method: 'POST', credentials: 'same-origin',
        body: new URLSearchParams({ tipo: sopTipo, mensaje: msg })
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) throw new Error(res.message || SOP_T.err_conexion);
        sopRenderSeguimiento(res.abierta, res.ya_existia ? SOP_T.ya_existia : SOP_T.enviada_ok);
    })
    .catch(e => { err.textContent = e.message || SOP_T.err_conexion; btn.disabled = false; btn.textContent = SOP_T.btn_enviar; });
}

// Si ya hay una solicitud abierta, los botones de contacto lo dicen ("Ver mi solicitud · En revisión")
function sopActualizarBotones(s) {
    if (!s || s.estado === 'resuelta') return;
    document.querySelectorAll('.btn-soporte').forEach(b => {
        b.textContent = `${SOP_T.btn_ver} · ${SOP_T['estado_' + s.estado] || ''}`;
    });
}
if (document.querySelector('.btn-soporte')) {
    fetch('index.php?c=soporte&a=MisSolicitudes', { credentials: 'same-origin' })
        .then(r => r.json()).then(res => { if (res.success) sopActualizarBotones(res.abierta); })
        .catch(() => {});
}
<?php endif; ?>

// ── Badge de solicitudes abiertas (admin) ──────────────────────────────────
<?php if ($rol === 2): ?>
fetch('index.php?c=adminsoporte&a=Contar', { credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
        const b = document.getElementById('sopBadge');
        if (b && res.success && res.abiertas > 0) { b.textContent = res.abiertas; b.hidden = false; }
    })
    .catch(() => {});
<?php endif; ?>

// ── Toast de gracia ────────────────────────────────────────────────────────
function cerrarGracia() {
    const t = document.getElementById('graciaToast');
    if (t) t.classList.remove('show');
    try { sessionStorage.setItem('gracia_visto', '1'); } catch(e) {}
}

(function () {
    if (!ES_GRACIA) return;
    try { if (sessionStorage.getItem('gracia_visto')) return; } catch(e) {}
    setTimeout(() => {
        const t = document.getElementById('graciaToast');
        if (t) t.classList.add('show');
    }, 800);
})();

// ── Sidebar ────────────────────────────────────────────────────────────────
const logoEl  = document.getElementById('logo');
const barra   = document.querySelector('.barra-lateral');
const spans   = document.querySelectorAll('span');
const palanca = document.querySelector('.switch');
const circulo = document.querySelector('.circulo');
const menuBtn = document.querySelector('.menu');
const mainEl  = document.querySelector('main');

menuBtn.addEventListener('click', () => {
    barra.classList.toggle('max-barra-lateral');
    const max = barra.classList.contains('max-barra-lateral');
    menuBtn.children[0].style.display = max ? 'none'  : 'block';
    menuBtn.children[1].style.display = max ? 'block' : 'none';
    if (window.innerWidth <= 320) {
        barra.classList.add('mini-barra-lateral');
        if (mainEl) mainEl.classList.add('min-main');
        spans.forEach(s => s.classList.add('oculto'));
    }
});

// Modo oscuro / claro: se guarda en sesión + BD y aplica en todas las páginas
palanca.addEventListener('click', () => {
    circulo.classList.toggle('prendido');
    cambiarTema(document.documentElement.classList.contains('tema-claro') ? 'oscuro' : 'claro');
});

logoEl.addEventListener('click', () => {
    barra.classList.toggle('mini-barra-lateral');
    if (mainEl) mainEl.classList.toggle('min-main');
    spans.forEach(s => s.classList.toggle('oculto'));
});

// ── Marcar ítem activo ─────────────────────────────────────────────────────
const params = new URLSearchParams(window.location.search);
const c = params.get('c');
const a = params.get('a');

document.querySelectorAll('.navegacion a').forEach(link => {
    const url = new URL(link.href);
    const p = new URLSearchParams(url.search);
    if (p.get('c') === c && p.get('a') === a) {
        link.classList.add('active');
    }
});
</script>

<?php
// ── Reglamento y consentimiento (primer ingreso o nueva versión) — solo turistas ──
// Se consulta una vez por sesión; al aceptar, LegalController::Aceptar guarda la versión en la sesión.
if ($aplica && !empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../model/consentimiento.php';
    if (($_SESSION['consentimiento_version'] ?? null) !== Consentimiento::VERSION) {
        if (Consentimiento::vigente($_SESSION['user_id'])) {
            $_SESSION['consentimiento_version'] = Consentimiento::VERSION;
        } else {
            require_once __DIR__ . '/../model/restriccionEdad.php';
            $consentimientoMenor = RestriccionEdad::esMenor($_SESSION['user_id']);
            include __DIR__ . '/cliente/modal/modal_consentimiento.php';
        }
    }
}
?>
</body>
</html>