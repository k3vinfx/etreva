<?php
// Variables desde PagoController::Index():
// $registro_pago, $plan_activo, $planes, $dias_restantes
// $plan_actual_id, $plan_actual_nombre, $nivel_actual, $pct_restante
$periodoLabels = [
    'weekly'  => Lang::get('planes.semanal'),
    'monthly' => Lang::get('planes.mensual'),
    'yearly'  => Lang::get('planes.anual'),
];
$lang = $_SESSION['lang'] ?? 'es';

// ── Nombres de planes traducidos ──
$nombreExplorador = Lang::get('planes.explorador_nombre');
$nombreAventura   = Lang::get('planes.aventura_nombre');
$nombrePremium    = Lang::get('planes.premium_nombre');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Lang::get('planes.titulo_pagina') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/menu.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* [STYLES UNCHANGED] */
:root {
    --primary:  #667eea; --secondary: #764ba2;
    --bg:       #1a1a2e; --bg-deep:   #0f1629;
    --surf:     #1e2140; --surf2:     #252848;
    --border:   rgba(255,255,255,0.07);
    --green:    #10b981; --gold: #f59e0b;
    --text:     #e2e8f0; --muted: rgba(255,255,255,0.4);
}
*{box-sizing:border-box;}
html,body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;min-height:100vh;}
main{margin-left:280px;min-height:100vh;background:var(--bg);transition:all .4s;}
main.min-main{margin-left:85px;}
@media(max-width:768px){main{margin-left:0!important;}}
.hero{background:linear-gradient(135deg,var(--bg-deep) 0%,#1a1040 50%,var(--bg) 100%);padding:48px 40px 40px;border-bottom:1px solid var(--border);position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;top:-80px;right:-80px;width:400px;height:400px;background:radial-gradient(circle,rgba(102,126,234,.18) 0%,transparent 70%);pointer-events:none;}
.hero h1{font-family:'Syne',sans-serif;font-size:2.4rem;font-weight:800;background:linear-gradient(135deg,#fff 0%,#b8c3f8 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:6px;position:relative;z-index:1;}
.hero p{color:var(--muted);font-size:1rem;font-weight:300;position:relative;z-index:1;}
.plan-actual-card{background:linear-gradient(135deg,rgba(102,126,234,.1) 0%,rgba(118,75,162,.07) 100%);border:1px solid rgba(102,126,234,.3);border-radius:20px;padding:28px 32px;margin:32px 40px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;position:relative;overflow:hidden;}
.plan-actual-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),var(--secondary));}
.plan-icono-grande{width:64px;height:64px;border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;}
.plan-actual-info{flex:1;min-width:180px;}
.plan-actual-info h2{font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;color:#fff;margin:0 0 8px;}
.vigencia-wrap{flex:1;min-width:200px;max-width:300px;}
.vigencia-labels{display:flex;justify-content:space-between;font-size:.74rem;color:var(--muted);margin-bottom:6px;}
.vigencia-labels span:last-child{color:var(--text);font-weight:700;}
.vigencia-track{height:10px;background:rgba(255,255,255,.08);border-radius:50px;overflow:hidden;}
.vigencia-fill{height:100%;border-radius:50px;transition:width 1.2s ease;}
.badge-estado{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:50px;font-size:.75rem;font-weight:700;}
.badge-activo{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#10b981;}
.badge-vencido{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#ef4444;}
.badge-gratis{background:rgba(100,116,139,.15);border:1px solid rgba(100,116,139,.3);color:#94a3b8;}
.carryover-info{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);border-radius:14px;padding:14px 18px;margin:0 40px 16px;display:flex;align-items:center;gap:12px;font-size:.85rem;}
.carryover-info i{color:var(--gold);font-size:1.2rem;flex-shrink:0;}
.carryover-info p{margin:0;color:var(--muted);}
.carryover-info strong{color:var(--text);}
.periodo-wrap{display:flex;justify-content:center;gap:4px;background:var(--surf);border:1px solid var(--border);border-radius:50px;padding:5px;width:fit-content;margin:28px auto 20px;}
.periodo-btn{padding:8px 22px;border-radius:50px;border:none;background:transparent;color:var(--muted);font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s;}
.periodo-btn.activo{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;box-shadow:0 4px 12px rgba(102,126,234,.3);}
.ahorro-badge{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#10b981;font-size:.65rem;font-weight:800;padding:2px 7px;border-radius:50px;margin-left:5px;}
.planes-wrap{max-width:1100px;margin:0 auto;padding:0 40px 60px;}
.planes-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;}
.plan-card{background:var(--surf);border:1px solid var(--border);border-radius:20px;padding:28px 24px;position:relative;transition:all .25s;display:flex;flex-direction:column;}
.plan-card:hover{transform:translateY(-6px);box-shadow:0 20px 50px rgba(0,0,0,.4);}
.plan-card.destacado{border-color:rgba(102,126,234,.4);}
.plan-card.es-actual{border-color:rgba(16,185,129,.35);}
.tag-card{position:absolute;top:-12px;left:50%;transform:translateX(-50%);font-size:.68rem;font-weight:800;padding:4px 16px;border-radius:50px;white-space:nowrap;color:#fff;}
.tag-popular{background:linear-gradient(135deg,var(--primary),var(--secondary));}
.tag-actual{background:rgba(16,185,129,.9);}
.plan-card-icono{width:50px;height:50px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin-bottom:16px;}
.plan-nombre{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;color:#fff;margin-bottom:4px;}
.plan-desc{font-size:.8rem;color:var(--muted);margin-bottom:18px;line-height:1.5;}
.plan-precio{margin-bottom:18px;}
.precio-num{font-family:'Syne',sans-serif;font-size:2.4rem;font-weight:800;color:#fff;line-height:1;}
.precio-num sup{font-size:1rem;vertical-align:top;margin-top:6px;}
.precio-per{font-size:.75rem;color:var(--muted);margin-top:4px;}
.precio-item{display:none;}
.precio-item.visible{display:block;}
.plan-features{list-style:none;padding:0;margin:0 0 20px;flex:1;}
.plan-features li{display:flex;align-items:center;gap:10px;font-size:.82rem;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);color:var(--muted);}
.plan-features li:last-child{border-bottom:none;}
.plan-features li.ok{color:var(--text);}
.plan-features li i{font-size:.7rem;width:16px;text-align:center;flex-shrink:0;}
.plan-features li.ok i{color:#10b981;}
.plan-features li:not(.ok) i{color:rgba(255,255,255,.15);}
.btn-plan{width:100%;padding:13px;border-radius:12px;border:none;font-size:.9rem;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-elegir{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;}
.btn-elegir:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(102,126,234,.4);}
.btn-upgrade{background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;}
.btn-upgrade:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(245,158,11,.4);}
.btn-actual{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);color:#10b981;cursor:default;}
.btn-renovar{background:rgba(102,126,234,.12);border:1px solid rgba(102,126,234,.3);color:#818cf8;}
.btn-renovar:hover{background:rgba(102,126,234,.2);}
.btn-dis{background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--muted);cursor:not-allowed;opacity:.5;}
.comp-card{background:var(--surf2);border:1px solid var(--border);border-radius:14px;padding:14px 18px;display:flex;align-items:center;gap:14px;}
.comp-fecha{font-size:.75rem;color:var(--muted);}
.comp-plan-badge{font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:50px;background:rgba(102,126,234,.15);border:1px solid rgba(102,126,234,.25);color:#b8c3f8;}
.mp-overlay{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,22,41,.88);backdrop-filter:blur(6px);align-items:center;justify-content:center;}
.mp-overlay.show{display:flex;}
.mp-box{background:#16213e;border:1px solid rgba(255,255,255,.1);border-radius:20px;width:90%;max-width:460px;color:var(--text);position:relative;overflow:hidden;animation:mpIn .25s ease;}
@keyframes mpIn{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
.mp-box::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),var(--secondary));}
.mp-head{padding:22px 24px 16px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;justify-content:space-between;align-items:flex-start;}
.mp-head-txt h3{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;color:#fff;margin:0 0 3px;}
.mp-head-txt p{font-size:.75rem;color:var(--muted);margin:0;}
.mp-close{background:none;border:none;color:rgba(255,255,255,.35);font-size:1.2rem;cursor:pointer;padding:4px;line-height:1;transition:color .2s;}
.mp-close:hover{color:#fff;}
.mp-body{padding:22px 24px;}
.mp-resumen{background:rgba(102,126,234,.1);border:1px solid rgba(102,126,234,.25);border-radius:14px;padding:16px 18px;margin-bottom:14px;}
.mp-row{display:flex;justify-content:space-between;align-items:center;font-size:.85rem;padding:5px 0;}
.mp-row .lbl{color:var(--muted);}
.mp-row .val{color:#fff;font-weight:700;}
.mp-row.co-row .val{color:var(--gold);}
.mp-row.total-row{border-top:1px solid rgba(255,255,255,.07);margin-top:6px;padding-top:12px;}
.mp-row.total-row .val{font-size:1.15rem;color:#10b981;}
.mp-co-alert{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);border-radius:12px;padding:12px 14px;display:none;align-items:flex-start;gap:10px;font-size:.82rem;margin-bottom:12px;}
.mp-co-alert.show{display:flex;}
.mp-co-alert i{color:var(--gold);flex-shrink:0;margin-top:2px;}
.mp-co-alert p{margin:0;color:var(--muted);line-height:1.5;}
.mp-co-alert strong{color:var(--text);}
.mp-qr{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:12px 14px;display:flex;align-items:center;gap:12px;font-size:.8rem;color:var(--muted);margin-bottom:12px;line-height:1.5;}
.mp-qr i{font-size:1.3rem;color:var(--primary);flex-shrink:0;}
.mp-upload{border:2px dashed rgba(102,126,234,.3);border-radius:14px;padding:22px;text-align:center;cursor:pointer;transition:all .2s;position:relative;margin-bottom:16px;}
.mp-upload:hover{border-color:rgba(102,126,234,.65);background:rgba(102,126,234,.05);}
.mp-upload input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.mp-upload .up-icon{font-size:1.8rem;color:rgba(102,126,234,.5);display:block;margin-bottom:6px;}
.mp-upload .up-txt{font-size:.8rem;color:var(--muted);margin:0;}
.mp-upload .up-ok{font-size:.82rem;color:#10b981;font-weight:600;margin:0;}
.mp-btn{width:100%;padding:14px;background:linear-gradient(135deg,var(--primary),var(--secondary));border:none;border-radius:12px;color:#fff;font-size:.95rem;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;}
.mp-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 24px rgba(102,126,234,.4);}
.mp-btn:disabled{opacity:.45;cursor:not-allowed;transform:none;}
.mp-terms{font-size:.68rem;color:rgba(255,255,255,.2);text-align:center;margin-top:10px;margin-bottom:0;}
@media(max-width:768px){.hero,.planes-wrap{padding-left:20px;padding-right:20px;}.plan-actual-card,.carryover-info{margin-left:20px;margin-right:20px;}.planes-grid{grid-template-columns:1fr;}}
</style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main>

<!-- ═══ HERO ═══ -->
<div class="hero">
    <h1>
        <i class="fas fa-crown" style="-webkit-text-fill-color:#f59e0b;font-size:1.8rem;vertical-align:middle;margin-right:12px;"></i>
        <?= Lang::get('planes.hero_titulo') ?>
    </h1>
    <p><?= Lang::get('planes.hero_subtitulo') ?></p>
</div>

<!-- ═══ PLAN ACTUAL ═══ -->
<?php
$planConfig = [
    'explorador_gratis' => ['color' => '#64748b', 'icono' => 'fa-map'],
    'explorador_urbano' => ['color' => '#22c55e', 'icono' => 'fa-city'],
    'aventura_andina'   => ['color' => '#667eea', 'icono' => 'fa-mountain'],
    'premium_paceno'    => ['color' => '#f59e0b', 'icono' => 'fa-crown'],
];
$cfg       = $planConfig[$plan_actual_id] ?? $planConfig['explorador_gratis'];
$color     = $cfg['color'];
$bar_color = $pct_restante > 50 ? '#10b981' : ($pct_restante > 20 ? '#f59e0b' : '#ef4444');
?>

<div class="plan-actual-card" style="border-color:<?= $color ?>40;background:linear-gradient(135deg,<?= $color ?>10 0%,rgba(118,75,162,.06) 100%);">
    <div class="plan-icono-grande" style="background:<?= $color ?>22;color:<?= $color ?>;">
        <i class="fas <?= $cfg['icono'] ?>"></i>
    </div>

    <div class="plan-actual-info">
        <h2><?= htmlspecialchars($plan_actual_nombre) ?></h2>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <?php if ($plan_activo): ?>
                <span class="badge-estado badge-activo">
                    <i class="fas fa-circle" style="font-size:.5rem;"></i>
                    <?= Lang::get('planes.badge_activo') ?>
                </span>
                <span style="font-size:.78rem;color:var(--muted);">
                    <?= htmlspecialchars($periodoLabels[$registro_pago['periodo'] ?? ''] ?? '') ?>
                    · $<?= htmlspecialchars($registro_pago['monto'] ?? '0') ?>
                </span>
            <?php elseif (!empty($registro_pago['estado']) && !$plan_activo): ?>
                <span class="badge-estado badge-vencido">
                    <i class="fas fa-exclamation-circle" style="font-size:.5rem;"></i>
                    <?= Lang::get('planes.badge_vencido') ?>
                </span>
            <?php else: ?>
                <span class="badge-estado badge-gratis">
                    <i class="fas fa-circle" style="font-size:.5rem;"></i>
                    <?= Lang::get('planes.badge_sin_plan') ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($plan_activo && !empty($registro_pago['fecha_vencimiento'])): ?>
    <div class="vigencia-wrap">
        <div class="vigencia-labels">
            <span><?= Lang::get('planes.vigencia') ?></span>
            <span>
                <?php
                if ($dias_restantes > 1)     echo $dias_restantes . ' ' . Lang::get('planes.dias_restantes');
                elseif ($dias_restantes === 1) echo Lang::get('planes.vence_manana');
                else                          echo Lang::get('planes.vence_hoy');
                ?>
            </span>
        </div>
        <div class="vigencia-track">
            <div class="vigencia-fill" id="vigenciaFill"
                 style="width:0%;background:<?= $bar_color ?>;"
                 data-pct="<?= $pct_restante ?>"></div>
        </div>
        <div style="font-size:.7rem;color:var(--muted);margin-top:5px;display:flex;justify-content:space-between;">
            <span><?= Lang::get('planes.pagado') ?>: <?= date('d/m/Y', strtotime($registro_pago['fecha_pago'] ?? 'now')) ?></span>
            <span><?= Lang::get('planes.vence') ?>: <?= date('d/m/Y', strtotime($registro_pago['fecha_vencimiento'])) ?></span>
        </div>
    </div>

    <div style="text-align:center;">
        <div style="font-family:'Syne',sans-serif;font-size:2.2rem;font-weight:800;color:<?= $bar_color ?>;">
            <?= $dias_restantes ?>
        </div>
        <div style="font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;">
            <?= Lang::get('planes.dias_restantes') ?>
        </div>
        <?php if (!empty($registro_pago['dias_carryover']) && $registro_pago['dias_carryover'] > 0): ?>
        <div style="font-size:.68rem;color:var(--gold);margin-top:4px;">
            <i class="fas fa-gift me-1"></i><?= $registro_pago['dias_carryover'] ?> <?= Lang::get('planes.dias_bonificados') ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ═══ ALERTA CARRY-OVER ═══ -->
<?php if ($plan_activo && $dias_restantes > 0): ?>
<div class="carryover-info">
    <i class="fas fa-info-circle"></i>
    <p>
        <?= sprintf(Lang::get('planes.carryover_info'), $dias_restantes) ?>
        <?= $nivel_actual >= 3 ? Lang::get('planes.carryover_max') : Lang::get('planes.carryover_upgrade') ?>
    </p>
</div>
<?php endif; ?>

<!-- ═══ SELECTOR PERIODO ═══ -->
<div style="text-align:center;padding:0 40px;">
    <p style="color:var(--muted);font-size:.87rem;margin-bottom:10px;"><?= Lang::get('planes.elegir_periodo') ?></p>
    <div class="periodo-wrap">
        <button class="periodo-btn activo" data-periodo="weekly"  onclick="cambiarPeriodo('weekly',this)"><?= Lang::get('planes.semanal') ?></button>
        <button class="periodo-btn"        data-periodo="monthly" onclick="cambiarPeriodo('monthly',this)"><?= Lang::get('planes.mensual') ?></button>
        <button class="periodo-btn"        data-periodo="yearly"  onclick="cambiarPeriodo('yearly',this)">
            <?= Lang::get('planes.anual') ?> <span class="ahorro-badge"><?= Lang::get('planes.ahorro') ?></span>
        </button>
    </div>
</div>

<!-- ═══ PLANES ═══ -->
<?php
$planesData = [
    $nombreExplorador => [
        'id'        => 'explorador_urbano',
        'nivel'     => 1,
        'icono'     => 'fa-city',
        'color'     => '#22c55e',
        'desc'      => Lang::get('planes.explorador_desc'),
        'destacado' => false,
        'precios'   => ['weekly' => 8,  'monthly' => 10, 'yearly' => 40],
        'features'  => [
            [true,  Lang::get('planes.f_5rec')],
            [true,  Lang::get('planes.f_base_datos')],
            [true,  Lang::get('planes.f_perfil_basico')],
            [true,  Lang::get('planes.f_soporte_email')],
            [false, Lang::get('planes.f_historial')],
            [false, Lang::get('planes.f_ia_premium')],
        ],
    ],
    $nombreAventura => [
        'id'        => 'aventura_andina',
        'nivel'     => 2,
        'icono'     => 'fa-mountain',
        'color'     => '#667eea',
        'desc'      => Lang::get('planes.aventura_desc'),
        'destacado' => true,
        'precios'   => ['weekly' => 10, 'monthly' => 20, 'yearly' => 60],
        'features'  => [
            [true,  Lang::get('planes.f_dinamicas')],
            [true,  Lang::get('planes.f_historial')],
            [true,  Lang::get('planes.f_ajuste_auto')],
            [true,  Lang::get('planes.f_analisis')],
            [true,  Lang::get('planes.f_soporte_prio')],
            [false, Lang::get('planes.f_ia_247')],
        ],
    ],
    $nombrePremium => [
        'id'        => 'premium_paceno',
        'nivel'     => 3,
        'icono'     => 'fa-crown',
        'color'     => '#f59e0b',
        'desc'      => Lang::get('planes.premium_desc'),
        'destacado' => false,
        'precios'   => ['weekly' => 15, 'monthly' => 30, 'yearly' => 90],
        'features'  => [
            [true, Lang::get('planes.f_todo_aventura')],
            [true, Lang::get('planes.f_evolutivo')],
            [true, Lang::get('planes.f_predictivo')],
            [true, Lang::get('planes.f_tiempo_real')],
            [true, Lang::get('planes.f_asistente')],
            [true, Lang::get('planes.f_vip')],
        ],
    ],
];
?>

<div class="planes-wrap">
    <div class="planes-grid">

    <?php foreach ($planesData as $nombre => $p):
        $es_actual  = ($p['id'] === $plan_actual_id) && $plan_activo;
        $es_upgrade = $plan_activo && ($p['nivel'] > $nivel_actual);
        $es_down    = $plan_activo && ($p['nivel'] < $nivel_actual);
    ?>
    <div class="plan-card <?= $p['destacado'] ? 'destacado' : '' ?> <?= $es_actual ? 'es-actual' : '' ?>">

        <?php if ($es_actual): ?>
            <div class="tag-card tag-actual"><i class="fas fa-check me-1"></i><?= Lang::get('planes.tag_actual') ?></div>
        <?php elseif ($p['destacado'] && !$es_actual): ?>
            <div class="tag-card tag-popular"><?= Lang::get('planes.tag_popular') ?></div>
        <?php endif; ?>

        <div class="plan-card-icono" style="background:<?= $p['color'] ?>22;color:<?= $p['color'] ?>;">
            <i class="fas <?= $p['icono'] ?>"></i>
        </div>

        <div class="plan-nombre"><?= $nombre ?></div>
        <div class="plan-desc"><?= $p['desc'] ?></div>

        <!-- Precios por período -->
        <div class="plan-precio">
            <?php foreach (['weekly','monthly','yearly'] as $per):
                $x = $per === 'weekly' ? 7 : ($per === 'monthly' ? 30 : 365);
            ?>
            <div class="precio-item <?= $per === 'weekly' ? 'visible' : '' ?>" data-periodo="<?= $per ?>">
                <div class="precio-num"><sup>$</sup><?= $p['precios'][$per] ?></div>
                <div class="precio-per">
                    / <?= $periodoLabels[$per] ?>
                    <span style="color:var(--green);font-size:.7rem;margin-left:4px;">
                        (≈ $<?= round($p['precios'][$per] / $x, 2) ?><?= Lang::get('planes.por_dia') ?>)
                    </span>
                </div>
                <?php if ($plan_activo && $dias_restantes > 0): ?>
                    <?php if ($es_actual): ?>
                    <div style="font-size:.72rem;color:var(--gold);margin-top:6px;">
                        <i class="fas fa-gift me-1"></i><?= sprintf(Lang::get('planes.hint_renovar'), $dias_restantes) ?>
                    </div>
                    <?php elseif ($es_upgrade): ?>
                    <div style="font-size:.72rem;color:var(--gold);margin-top:6px;">
                        <i class="fas fa-arrow-up me-1"></i><?= sprintf(Lang::get('planes.hint_upgrade'), $dias_restantes) ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <ul class="plan-features">
            <?php foreach ($p['features'] as [$ok, $txt]): ?>
            <li class="<?= $ok ? 'ok' : '' ?>">
                <i class="fas <?= $ok ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                <?= $txt ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Botones -->
        <?php if ($es_down): ?>
            <button class="btn-plan btn-dis" disabled>
                <i class="fas fa-lock me-2"></i><?= Lang::get('planes.no_disponible') ?>
            </button>

        <?php elseif ($es_actual): ?>
            <div style="display:flex;gap:8px;flex-direction:column;">
                <button class="btn-plan btn-actual">
                    <i class="fas fa-check-circle me-2"></i><?= Lang::get('planes.plan_activo_btn') ?>
                </button>
                <button class="btn-plan btn-renovar"
                    onclick="abrirModal('<?= $nombre ?>', <?= $p['nivel'] ?>)">
                    <i class="fas fa-sync me-2"></i><?= sprintf(Lang::get('planes.renovar_btn'), $dias_restantes) ?>
                </button>
            </div>

        <?php elseif ($es_upgrade): ?>
            <button class="btn-plan btn-upgrade"
                onclick="abrirModal('<?= $nombre ?>', <?= $p['nivel'] ?>)">
                <i class="fas fa-arrow-up me-2"></i><?= Lang::get('planes.upgrade_btn') ?>
            </button>

        <?php else: ?>
            <button class="btn-plan btn-elegir"
                onclick="abrirModal('<?= $nombre ?>', <?= $p['nivel'] ?>)">
                <i class="fas fa-rocket me-2"></i><?= Lang::get('planes.elegir_btn') ?>
            </button>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>
    </div>

    <!-- Historial último pago -->
    <?php if (!empty($registro_pago) && !empty($registro_pago['estado'])): ?>
    <div style="margin-top:40px;">
        <div style="font-size:1rem;font-weight:700;color:var(--text);margin-bottom:14px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-receipt" style="color:var(--primary);"></i><?= Lang::get('planes.ultimo_comprobante') ?>
        </div>
        <div class="comp-card">
            <i class="fas fa-file-image" style="font-size:1.4rem;color:var(--primary);flex-shrink:0;"></i>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.88rem;"><?= htmlspecialchars($registro_pago['tipo_plan'] ?? '—') ?></div>
                <div class="comp-fecha">
                    <?= htmlspecialchars($registro_pago['fecha_pago'] ?? '—') ?>
                    · $<?= htmlspecialchars($registro_pago['monto'] ?? '0') ?>
                    · <?= htmlspecialchars($periodoLabels[$registro_pago['periodo'] ?? ''] ?? ($registro_pago['periodo'] ?? '')) ?>
                    <?php if (!empty($registro_pago['motivo_pago']) && $registro_pago['motivo_pago'] !== 'nuevo'): ?>
                        · <span style="color:var(--gold);">
                            <?= $registro_pago['motivo_pago'] === 'renovacion'
                                ? Lang::get('planes.renovacion')
                                : Lang::get('planes.upgrade') ?>
                          </span>
                    <?php endif; ?>
                    <?php if (!empty($registro_pago['dias_carryover']) && $registro_pago['dias_carryover'] > 0): ?>
                        · <span style="color:var(--gold);">+<?= $registro_pago['dias_carryover'] ?> <?= Lang::get('planes.dias_bonificados') ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <span class="comp-plan-badge"><?= htmlspecialchars($registro_pago['estado'] ?? '') ?></span>
            <?php if (!empty($registro_pago['ruta_comprobante'])): ?>
            <a href="<?= htmlspecialchars($registro_pago['ruta_comprobante']) ?>" target="_blank"
               style="color:var(--primary);font-size:.78rem;font-weight:600;text-decoration:none;">
                <i class="fas fa-external-link-alt me-1"></i><?= $lang !== 'es' ? 'View' : 'Ver' ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

</main>

<!-- ═══ MODAL PAGO ═══ -->
<div class="mp-overlay" id="mpOverlay">
    <div class="mp-box">
        <div class="mp-head">
            <div class="mp-head-txt">
                <h3 id="mpTitulo"><?= Lang::get('planes.modal_activar') ?></h3>
                <p  id="mpSub"><?= Lang::get('planes.modal_sub') ?></p>
            </div>
            <button class="mp-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="mp-body">
            <div class="mp-resumen">
                <div class="mp-row"><span class="lbl"><?= Lang::get('planes.modal_plan') ?></span><span class="val" id="mpPlan">—</span></div>
                <div class="mp-row"><span class="lbl"><?= Lang::get('planes.modal_periodo') ?></span><span class="val" id="mpPeriodo">—</span></div>
                <div class="mp-row"><span class="lbl"><?= Lang::get('planes.modal_dias_base') ?></span><span class="val" id="mpDiasBase">—</span></div>
                <div class="mp-row co-row" id="mpRowCarryover" style="display:none;">
                    <span class="lbl"><i class="fas fa-gift me-1" style="color:var(--gold);"></i><?= Lang::get('planes.modal_bonificados') ?></span>
                    <span class="val" id="mpCarryover">+0</span>
                </div>
                <div class="mp-row" id="mpRowTotal" style="display:none;">
                    <span class="lbl"><?= Lang::get('planes.modal_total_dias') ?></span>
                    <span class="val" id="mpTotalDias" style="color:#10b981;"></span>
                </div>
                <div class="mp-row total-row">
                    <span class="lbl" style="color:var(--text);font-weight:700;"><?= Lang::get('planes.modal_total_pagar') ?></span>
                    <span class="val" id="mpTotal">$0</span>
                </div>
            </div>
            <div class="mp-co-alert" id="mpCoAlert">
                <i class="fas fa-gift"></i>
                <p id="mpCoAlertTxt"></p>
            </div>
            <!-- Método de pago: Yape · QR (Bolivia) o USDT/USDC BEP-20 -->
            <?php include __DIR__ . '/partials/metodos_pago.php'; ?>
            <div class="mp-upload">
                <input type="file" id="mpFile" accept="image/*" onchange="mpOnFile(this)">
                <i class="fas fa-cloud-upload-alt up-icon" id="mpUpIcon"></i>
                <p class="up-txt" id="mpUpTxt"><?= Lang::get('planes.modal_upload') ?></p>
                <p style="font-size:.68rem;color:rgba(255,255,255,.2);margin-top:4px;">JPG, PNG, WEBP · <?= $lang !== 'es' ? 'max 5MB' : 'máx 5MB' ?></p>
            </div>
            <button class="mp-btn" id="mpBtn" onclick="mpProcesar()" disabled>
                <i class="fas fa-rocket"></i> <?= Lang::get('planes.modal_activar') ?>
            </button>
            <p class="mp-terms"><?= Lang::get('planes.modal_terminos') ?></p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/etreva-auth.js"></script>

<script>
// ── Textos JS desde PHP (para SweetAlert y modal dinámico) ──
const LANG_PLANES = {
    modal_sub:        '<?= Lang::get('planes.modal_sub') ?>',
    modal_renovacion: '<?= Lang::get('planes.modal_renovacion') ?>',
    modal_upgrade:    '<?= Lang::get('planes.modal_upgrade') ?>',
    dias_txt:         '<?= $lang !== 'es' ? 'days' : 'días' ?>',
    dias_total:       '<?= $lang !== 'es' ? 'days total' : 'días en total' ?>',
    carryover_txt_en: <?= json_encode(Lang::get('planes.modal_carryover_txt')) ?>,
    titulo_nuevo:     '<?= $lang !== 'es' ? 'All set!' : '¡Todo listo!' ?>',
    titulo_renovacion:'<?= $lang !== 'es' ? 'Renewal complete!' : '¡Renovación completada!' ?>',
    titulo_upgrade:   '<?= $lang !== 'es' ? 'Plan upgraded!' : '¡Tu plan ha subido de nivel!' ?>',
    plan_activo_msg:  '<?= $lang !== 'es' ? 'Your plan <strong>%s</strong> is now active.' : 'Tu plan <strong>%s</strong> ya está activo.' ?>',
    relogin_msg:      '<?= $lang !== 'es' ? 'To apply changes, <strong>please log in again</strong>.' : 'Para aplicar los cambios, <strong>volvé a iniciar sesión</strong>.' ?>',
    dias_regalo:      '<?= $lang !== 'es' ? 'You have +%d bonus days' : 'Tenés +%d días extra de regalo' ?>',
    nueva_venc:       '<?= $lang !== 'es' ? 'New expiry date:' : 'Nueva fecha de vencimiento:' ?>',
    confirmar_btn:    '<?= $lang !== 'es' ? 'Got it, log back in' : 'Entendido, volver a entrar' ?>',
    procesando:       '<?= $lang !== 'es' ? 'Processing payment...' : 'Procesando pago...' ?>',
    validando:        '<?= $lang !== 'es' ? 'Validating your receipt.' : 'Validando tu comprobante.' ?>',
    error_titulo:     '<?= $lang !== 'es' ? 'Error' : 'Error' ?>',
    error_conn:       '<?= $lang !== 'es' ? 'Connection error' : 'Error de conexión' ?>',
    no_conectar:      '<?= $lang !== 'es' ? 'Could not connect.' : 'No se pudo conectar.' ?>',
    archivo_grande:   '<?= $lang !== 'es' ? 'File too large' : 'Archivo muy grande' ?>',
    max_5mb:          '<?= $lang !== 'es' ? 'Maximum 5MB' : 'Máximo 5MB' ?>',
    upload_txt:       '<?= Lang::get('planes.modal_upload') ?>',
};

const PRECIOS = {
    '<?= $nombreExplorador ?>': { weekly: 8,  monthly: 10, yearly: 40  },
    '<?= $nombreAventura ?>':   { weekly: 10, monthly: 20, yearly: 60  },
    '<?= $nombrePremium ?>':    { weekly: 15, monthly: 30, yearly: 90  },
};
const DIAS_PLAN = {
    '<?= $nombreExplorador ?>': { weekly: 7, monthly: 30, yearly: 365 },
    '<?= $nombreAventura ?>':   { weekly: 7, monthly: 30, yearly: 365 },
    '<?= $nombrePremium ?>':    { weekly: 7, monthly: 30, yearly: 365 },
};
const PERIODOS = {
    weekly:  '<?= Lang::get('planes.semanal') ?>',
    monthly: '<?= Lang::get('planes.mensual') ?>',
    yearly:  '<?= Lang::get('planes.anual') ?>',
};
const DIAS_RESTANTES = <?= (int)$dias_restantes ?>;
const PLAN_ACTIVO    = <?= $plan_activo ? 'true' : 'false' ?>;
const NIVEL_ACTUAL   = <?= (int)$nivel_actual ?>;

let _periodo    = 'weekly';
let _planNombre = null;
let _planNivel  = 0;

window.addEventListener('load', () => {
    const f = document.getElementById('vigenciaFill');
    if (f) setTimeout(() => f.style.width = f.dataset.pct + '%', 400);
});

function cambiarPeriodo(periodo, btn) {
    _periodo = periodo;
    document.querySelectorAll('.periodo-btn').forEach(b => b.classList.remove('activo'));
    btn.classList.add('activo');
    document.querySelectorAll('.precio-item').forEach(el => {
        el.classList.toggle('visible', el.dataset.periodo === periodo);
    });
    if (_planNombre) _mpResumen();
}

function abrirModal(planNombre, planNivel) {
    _planNombre = planNombre;
    _planNivel  = planNivel;
    document.getElementById('mpFile').value         = '';
    document.getElementById('mpUpIcon').className   = 'fas fa-cloud-upload-alt up-icon';
    document.getElementById('mpUpIcon').style.color = '';
    document.getElementById('mpUpTxt').textContent  = LANG_PLANES.upload_txt;
    document.getElementById('mpUpTxt').className    = 'up-txt';
    document.getElementById('mpBtn').disabled       = true;
    _mpResumen();
    document.getElementById('mpOverlay').classList.add('show');
}

function cerrarModal() {
    document.getElementById('mpOverlay').classList.remove('show');
}

document.getElementById('mpOverlay').addEventListener('click', e => {
    if (e.target === document.getElementById('mpOverlay')) cerrarModal();
});

function _mpResumen() {
    const precio   = PRECIOS[_planNombre]?.[_periodo]  ?? 0;
    const diasBase = DIAS_PLAN[_planNombre]?.[_periodo] ?? 30;
    const esIgualOSuperior = _planNivel >= NIVEL_ACTUAL;
    const carryover = (PLAN_ACTIVO && esIgualOSuperior) ? DIAS_RESTANTES : 0;
    const totalDias = diasBase + carryover;

    let subtitulo = LANG_PLANES.modal_sub;
    if (PLAN_ACTIVO && _planNivel === NIVEL_ACTUAL) subtitulo = LANG_PLANES.modal_renovacion;
    if (PLAN_ACTIVO && _planNivel >  NIVEL_ACTUAL) subtitulo = LANG_PLANES.modal_upgrade;

    document.getElementById('mpTitulo').textContent   = _planNombre;
    document.getElementById('mpSub').textContent      = subtitulo;
    document.getElementById('mpPlan').textContent     = _planNombre;
    document.getElementById('mpPeriodo').textContent  = PERIODOS[_periodo];
    document.getElementById('mpDiasBase').textContent = diasBase + ' ' + LANG_PLANES.dias_txt;
    document.getElementById('mpTotal').textContent    = '$' + precio;
    EtrevaPago.setMonto(precio);

    if (carryover > 0) {
        document.getElementById('mpRowCarryover').style.display = '';
        document.getElementById('mpRowTotal').style.display     = '';
        document.getElementById('mpCarryover').textContent      = '+' + carryover + ' ' + LANG_PLANES.dias_txt;
        document.getElementById('mpTotalDias').textContent      = totalDias + ' ' + LANG_PLANES.dias_total;
        document.getElementById('mpCoAlert').classList.add('show');
        document.getElementById('mpCoAlertTxt').innerHTML =
            LANG_PLANES.carryover_txt_en
                .replace('%d', carryover)
                .replace('%d', carryover)
                .replace('%d', totalDias);
    } else {
        document.getElementById('mpRowCarryover').style.display = 'none';
        document.getElementById('mpRowTotal').style.display     = 'none';
        document.getElementById('mpCoAlert').classList.remove('show');
    }
}

// El botón se habilita con la captura, o en cripto también con un hash válido
EtrevaPago.alCambiar(() => {
    const b = document.getElementById('mpBtn');
    if (b) b.disabled = !EtrevaPago.listo(!!document.getElementById('mpFile').files[0]);
});

function mpOnFile(input) {
    const f = input.files[0];
    if (!f) return;
    if (f.size > 5 * 1024 * 1024) {
        Swal.fire({ icon:'warning', title: LANG_PLANES.archivo_grande, text: LANG_PLANES.max_5mb, background:'#16213e', color:'#e2e8f0' });
        input.value = '';
        return;
    }
    document.getElementById('mpUpIcon').className   = 'fas fa-check-circle up-icon';
    document.getElementById('mpUpIcon').style.color = '#10b981';
    document.getElementById('mpUpTxt').textContent  = f.name;
    document.getElementById('mpUpTxt').className    = 'up-ok';
    document.getElementById('mpBtn').disabled       = false;
}

async function mpProcesar() {
    const archivo = document.getElementById('mpFile').files[0];
    if (!_planNombre) return;
    // Yape y Binance Pay: captura del comprobante. Cripto: captura o hash de la transacción.
    if (!EtrevaPago.valido(!!archivo)) return;

    const precio = PRECIOS[_planNombre]?.[_periodo] ?? 0;
    cerrarModal();

    Swal.fire({
        title: LANG_PLANES.procesando,
        text: LANG_PLANES.validando,
        allowOutsideClick: false, background: '#16213e', color: '#e2e8f0',
        didOpen: () => Swal.showLoading(),
    });

    const fd = new FormData();
    fd.append('plan',        _planNombre);
    fd.append('monto',       precio);
    fd.append('periodo',     _periodo);
    if (archivo) fd.append('comprobante', archivo);
    EtrevaPago.agregar(fd);

    try {
        const r      = await fetch('index.php?c=pago&a=ProcesarPago', { method:'POST', body:fd });
        const result = await r.json();

        if (result.success) {
            const titulos = {
                nuevo:     LANG_PLANES.titulo_nuevo,
                renovacion:LANG_PLANES.titulo_renovacion,
                upgrade:   LANG_PLANES.titulo_upgrade,
            };

            let html = LANG_PLANES.plan_activo_msg.replace('%s', result.plan) + '<br><br>';
            html += `<div style="background:rgba(96,165,250,.1);padding:10px;border-radius:8px;border:1px solid #60a5fa;">`;
            html += `<span style="color:#60a5fa;">${LANG_PLANES.relogin_msg}</span></div>`;

            if (result.dias_carryover > 0) {
                html += `<br><small style="color:#f59e0b;"><i class="fas fa-gift"></i> ${LANG_PLANES.dias_regalo.replace('%d', result.dias_carryover)}</small>`;
            }

            if (result.fecha_vencimiento) {
                const fv = new Date(result.fecha_vencimiento.replace(' ', 'T'));
                const locale = '<?= $lang !== 'es' ? 'en-US' : 'es-ES' ?>';
                html += `<br><small style="color:#94a3b8;">${LANG_PLANES.nueva_venc} ${fv.toLocaleDateString(locale, {day:'2-digit',month:'long',year:'numeric'})}</small>`;
            }

            Swal.fire({
                icon: 'success',
                title: titulos[result.motivo_pago] ?? LANG_PLANES.titulo_nuevo,
                html,
                confirmButtonText: LANG_PLANES.confirmar_btn,
                confirmButtonColor: '#667eea',
                background: '#16213e', color: '#e2e8f0',
                allowOutsideClick: false, allowEscapeKey: false
            }).then(() => {
                window.location.href = 'index.php?c=login&a=Logout';
            });

        } else {
            Swal.fire({
                icon:'error', title: LANG_PLANES.error_titulo,
                text: result.message || LANG_PLANES.no_conectar,
                confirmButtonColor:'#ef4444', background:'#16213e', color:'#e2e8f0',
            });
        }
    } catch (err) {
        Swal.fire({ icon:'error', title: LANG_PLANES.error_conn, text: LANG_PLANES.no_conectar, background:'#16213e', color:'#e2e8f0' });
    }
}
</script>
</body>
</html>