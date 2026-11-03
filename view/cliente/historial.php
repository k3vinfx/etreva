<?php
include 'view/menu.php';

// ── Textos i18n para JS ─────────────────────────────────────────────────────
$t = [
    'modal_cargando' => Lang::get('historial.modal_cargando'),
    'error_detalle'  => Lang::get('historial.error_detalle'),
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Lang::get('historial.titulo_pagina') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        /* (mismo CSS que tenías, sin cambios) */
        :root {
            --primary:  #667eea;
            --secondary:#764ba2;
            --bg:       #1a1a2e;
            --bg-deep:  #0f1629;
            --surf:     #1e2140;
            --surf2:    #252848;
            --border:   rgba(255,255,255,0.07);
            --green:    #10b981;
            --gold:     #f59e0b;
            --text:     #e2e8f0;
            --muted:    rgba(255,255,255,0.4);
            --r:        18px;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        html, body {
            background: var(--bg) !important;
            color: var(--text) !important;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
        }
        main {
            margin-left: 280px;
            padding: 0;
            background: var(--bg) !important;
            min-height: 100vh;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        main.min-main { margin-left: 85px; }
        @media (max-width: 768px) { main { margin-left: 0 !important; } }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, var(--bg-deep) 0%, #0d1a2e 50%, var(--bg) 100%);
            padding: 48px 24px 36px;
            border-bottom: 1px solid var(--border);
            position: relative; overflow: hidden;
        }
        .hero::before {
            content:''; position:absolute; top:-80px; right:-80px;
            width:400px; height:400px;
            background: radial-gradient(circle, rgba(16,185,129,.12) 0%, transparent 70%);
            pointer-events:none;
        }
        .hero::after {
            content:''; position:absolute; bottom:-60px; left:10%;
            width:300px; height:300px;
            background: radial-gradient(circle, rgba(102,126,234,.1) 0%, transparent 70%);
            pointer-events:none;
        }
        .hero h1 {
            font-family:'Syne',sans-serif; font-size:2.4rem; font-weight:800;
            background: linear-gradient(135deg, #fff 0%, #a7f3d0 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text; margin-bottom:6px;
        }
        .hero p { color:var(--muted); font-size:1rem; font-weight:300; }

        /* ── STATS BAR ── */
        .stats-bar {
            max-width:1200px; margin:28px auto 0;
            display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
            gap:14px; padding:0 16px;
        }
        .stat-box {
            background:var(--surf); border:1px solid var(--border);
            border-radius:var(--r); padding:18px 20px;
            display:flex; align-items:center; gap:14px;
            transition: border-color .2s;
        }
        .stat-box:hover { border-color:rgba(16,185,129,.3); }
        .stat-ico {
            width:44px; height:44px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-size:1.1rem; flex-shrink:0;
        }
        .stat-ico.green  { background:rgba(16,185,129,.12); color:var(--green); }
        .stat-ico.purple { background:rgba(102,126,234,.12); color:var(--primary); }
        .stat-ico.gold   { background:rgba(245,158,11,.12); color:var(--gold); }
        .stat-ico.teal   { background:rgba(14,116,144,.12); color:#0e7490; }
        .stat-val {
            font-family:'Syne',sans-serif; font-size:1.6rem;
            font-weight:800; color:#fff; line-height:1;
        }
        .stat-lbl { font-size:.72rem; color:var(--muted); margin-top:3px; }

        /* ── FILTRO AÑO ── */
        .year-tabs {
            max-width:1200px; margin:28px auto 0;
            display:flex; align-items:center; gap:8px;
            padding:0 16px; flex-wrap:wrap;
        }
        .year-btn {
            padding:7px 18px; border-radius:50px;
            border:1px solid var(--border);
            background:var(--surf2); color:var(--muted);
            font-size:.82rem; font-weight:700;
            cursor:pointer; transition:all .2s; text-decoration:none;
        }
        .year-btn:hover { border-color:rgba(16,185,129,.4); color:var(--text); }
        .year-btn.active {
            background:rgba(16,185,129,.15);
            border-color:rgba(16,185,129,.5);
            color:var(--green);
        }
        .year-lbl {
            font-size:.72rem; text-transform:uppercase;
            letter-spacing:.8px; color:var(--muted);
            font-weight:700; margin-right:4px;
        }

        /* ── CONTENIDO ── */
        .hist-wrap {
            max-width:1200px; margin:32px auto 60px;
            padding:0 16px;
        }

        /* ── SEPARADOR DE MES ── */
        .mes-sep {
            display:flex; align-items:center; gap:14px;
            margin-bottom:18px; margin-top:8px;
        }
        .mes-sep:not(:first-child) { margin-top:44px; }
        .mes-pill {
            background:rgba(16,185,129,.12);
            border:1px solid rgba(16,185,129,.25);
            border-radius:50px; padding:5px 16px;
            font-family:'Syne',sans-serif; font-size:.82rem;
            font-weight:800; color:var(--green);
            white-space:nowrap;
            display:flex; align-items:center; gap:7px;
        }
        .mes-pill i { font-size:.7rem; }
        .mes-line {
            flex:1; height:1px;
            background:linear-gradient(to right, rgba(16,185,129,.2), transparent);
        }
        .mes-count {
            font-size:.72rem; color:var(--muted); white-space:nowrap;
        }

        /* ── GRID ── */
        .hist-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
            gap:18px;
        }

        /* ── CARD ── */
        .hist-card {
            background:var(--surf);
            border:1px solid rgba(16,185,129,.2);
            border-radius:var(--r); overflow:hidden;
            display:flex; flex-direction:column;
            transition:transform .25s, box-shadow .25s, border-color .25s;
            opacity:0; animation:cardIn .4s ease forwards;
            cursor:pointer;
        }
        @keyframes cardIn {
            from { opacity:0; transform:translateY(16px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .hist-card:hover {
            transform:translateY(-5px);
            box-shadow:0 18px 44px rgba(0,0,0,.5);
            border-color:rgba(16,185,129,.5);
        }

        /* Imagen */
        .hist-img {
            height:165px; background-size:cover; background-position:center;
            position:relative;
        }
        .hist-img-ov {
            position:absolute; inset:0;
            background:linear-gradient(to top, rgba(13,15,26,.9) 0%, transparent 55%);
        }
        .hist-badges {
            position:absolute; top:10px; left:10px;
            display:flex; gap:6px; flex-wrap:wrap;
        }
        .hbc {
            font-size:.62rem; font-weight:800; text-transform:uppercase;
            letter-spacing:.5px; padding:4px 10px; border-radius:6px;
            backdrop-filter:blur(8px); color:#fff;
            background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.12);
        }
        .hbc-ok { background:rgba(16,185,129,.8); border-color:transparent; }

        /* Fecha burbuja */
        .hist-fecha {
            position:absolute; bottom:10px; left:12px;
        }
        .hfecha-bub {
            background:rgba(0,0,0,.75); backdrop-filter:blur(8px);
            border:1px solid rgba(255,255,255,.12);
            border-radius:10px; padding:5px 11px; text-align:center;
        }
        .hfecha-bub .fd {
            font-family:'Syne',sans-serif; font-size:1.15rem;
            font-weight:800; color:#fff; line-height:1;
        }
        .hfecha-bub .fm {
            font-size:.56rem; color:rgba(255,255,255,.5);
            text-transform:uppercase; letter-spacing:.5px;
        }

        /* Rating badge */
        .hist-rating {
            position:absolute; bottom:10px; right:12px;
            display:flex; align-items:center; gap:4px;
            background:rgba(0,0,0,.7); backdrop-filter:blur(8px);
            border:1px solid rgba(245,158,11,.25);
            border-radius:8px; padding:4px 9px;
        }
        .hist-rating i { color:var(--gold); font-size:.65rem; }
        .hist-rating span { color:#fff; font-size:.72rem; font-weight:700; }

        /* Body */
        .hist-body { padding:14px 16px 10px; flex:1; display:flex; flex-direction:column; }
        .hist-name {
            font-family:'Syne',sans-serif; font-size:.92rem; font-weight:700;
            color:#fff; margin-bottom:5px; line-height:1.3;
            display:-webkit-box; -webkit-line-clamp:2;
            -webkit-box-orient:vertical; overflow:hidden;
        }
        .hist-desc {
            font-size:.75rem; color:var(--muted); line-height:1.5; flex:1;
            display:-webkit-box; -webkit-line-clamp:2;
            -webkit-box-orient:vertical; overflow:hidden;
            margin-bottom:10px;
        }
        /* Nota del usuario */
        .hist-nota {
            background:rgba(16,185,129,.06);
            border-left:2px solid rgba(16,185,129,.4);
            border-radius:0 8px 8px 0;
            padding:7px 10px; margin-bottom:10px;
            font-size:.73rem; color:rgba(255,255,255,.6);
            font-style:italic;
            display:none;
        }
        .hist-nota.tiene-nota { display:block; }

        /* Footer */
        .hist-foot {
            padding:10px 16px 13px;
            border-top:1px solid var(--border);
            display:flex; align-items:center; justify-content:space-between;
        }
        .hist-meta { display:flex; align-items:center; gap:12px; }
        .hist-meta-item {
            display:flex; align-items:center; gap:4px;
            font-size:.7rem; color:var(--muted);
        }
        .hist-meta-item i { color:var(--green); font-size:.6rem; }
        .btn-detalle {
            background:rgba(16,185,129,.1);
            border:1px solid rgba(16,185,129,.25);
            color:var(--green); border-radius:9px;
            padding:6px 13px; font-size:.73rem; font-weight:700;
            cursor:pointer; transition:all .2s; white-space:nowrap;
        }
        .btn-detalle:hover {
            background:rgba(16,185,129,.2);
            border-color:rgba(16,185,129,.5);
            transform:translateY(-1px);
        }

        /* ── VACÍO ── */
        .hist-vacio {
            text-align:center; padding:80px 20px;
        }
        .hist-vacio .ico {
            width:80px; height:80px;
            background:var(--surf2); border:1px solid var(--border);
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            margin:0 auto 20px;
        }
        .hist-vacio .ico i { font-size:2rem; color:var(--green); opacity:.4; }
        .hist-vacio h3 {
            font-family:'Syne',sans-serif; font-size:1.2rem;
            color:rgba(255,255,255,.5); margin-bottom:8px;
        }
        .hist-vacio p { font-size:.85rem; color:var(--muted); }

        ::-webkit-scrollbar{ width:6px; }
        ::-webkit-scrollbar-track{ background:var(--bg); }
        ::-webkit-scrollbar-thumb{ background:var(--surf2); border-radius:3px; }
    </style>
</head>

<body>
<main>

<?php
$tz = new DateTimeZone('America/La_Paz');

// Año seleccionado (default: año actual)
$anioSel = (int)($_GET['anio'] ?? date('Y'));

// ── Agrupar visitados por mes ──
$porMes = [];
$totalVisitas = 0;
$categorias   = [];
$puntuaciones = [];

if (!empty($itinerarios)) {
    foreach ($itinerarios as $item) {
        if (($item['estado'] ?? '') !== 'visitado') continue;

        $fechaRaw = $item['fecha_visita'] ?? null;
        if (!$fechaRaw) continue;

        try {
            $dt = new DateTime($fechaRaw);
            $dt->setTimezone($tz);
        } catch (Exception $e) { continue; }

        $anio = (int)$dt->format('Y');
        $mes  = (int)$dt->format('n');

        if ($anio !== $anioSel) continue;

        $porMes[$mes][] = array_merge($item, ['_dt' => $dt]);
        $totalVisitas++;
        $cat = $item['categoria'] ?? '';
        if ($cat) $categorias[$cat] = ($categorias[$cat] ?? 0) + 1;
        if (!empty($item['calificacion'])) $puntuaciones[] = (float)$item['calificacion'];
    }
    krsort($porMes); // más reciente primero
}

// Stats
arsort($categorias);
$catTop   = array_key_first($categorias) ?? '—';
$promPunt = !empty($puntuaciones) ? round(array_sum($puntuaciones)/count($puntuaciones),1) : null;

// Años disponibles (para las pestañas)
$aniosDisponibles = [];
if (!empty($itinerarios)) {
    foreach ($itinerarios as $item) {
        if (($item['estado'] ?? '') !== 'visitado') continue;
        $raw = $item['fecha_visita'] ?? null;
        if (!$raw) continue;
        try {
            $dt = new DateTime($raw); $dt->setTimezone($tz);
            $aniosDisponibles[(int)$dt->format('Y')] = true;
        } catch (Exception $e) {}
    }
}
krsort($aniosDisponibles);
if (empty($aniosDisponibles)) $aniosDisponibles[date('Y')] = true;
?>

<!-- HERO -->
<div class="hero">
    <h1>
        <i class="fas fa-route me-3"
           style="color:var(--green);font-size:1.8rem;vertical-align:middle;-webkit-text-fill-color:var(--green);"></i>
        <?= Lang::get('historial.hero_titulo') ?>
    </h1>
    <p><?= Lang::get('historial.hero_subtitulo') ?></p>
</div>

<!-- STATS -->
<div class="stats-bar">
    <div class="stat-box">
        <div class="stat-ico green"><i class="fas fa-map-marker-check"></i></div>
        <div>
            <div class="stat-val"><?= $totalVisitas ?></div>
            <div class="stat-lbl"><?= Lang::get('historial.stats_visitados') ?></div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-ico purple"><i class="fas fa-calendar-check"></i></div>
        <div>
            <div class="stat-val"><?= count($porMes) ?></div>
            <div class="stat-lbl"><?= Lang::get('historial.stats_meses') ?></div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-ico gold"><i class="fas fa-star"></i></div>
        <div>
            <div class="stat-val"><?= $promPunt ? $promPunt.'/5' : '—' ?></div>
            <div class="stat-lbl"><?= Lang::get('historial.stats_promedio') ?></div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-ico teal"><i class="fas fa-tag"></i></div>
        <div>
            <div class="stat-val" style="font-size:1rem;padding-top:4px;">
                <?= ucfirst(str_replace('_',' ', $catTop)) ?>
            </div>
            <div class="stat-lbl"><?= Lang::get('historial.stats_favorita') ?></div>
        </div>
    </div>
</div>

<!-- TABS DE AÑO -->
<div class="year-tabs">
    <span class="year-lbl"><?= Lang::get('historial.year_label') ?></span>
    <?php foreach (array_keys($aniosDisponibles) as $a): ?>
        <a href="index.php?c=historial&a=Index&anio=<?= $a ?>"
           class="year-btn <?= $a === $anioSel ? 'active' : '' ?>">
            <?= $a ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- CONTENIDO POR MES -->
<div class="hist-wrap">

<?php if (empty($porMes)): ?>
    <div class="hist-vacio">
        <div class="ico"><i class="fas fa-map-marked-alt"></i></div>
        <h3><?= sprintf(Lang::get('historial.sin_visitas_titulo'), $anioSel) ?></h3>
        <p><?= Lang::get('historial.sin_visitas_texto') ?></p>
    </div>

<?php else: ?>

    <?php foreach ($porMes as $mes => $items): ?>
        <?php
        $mesNombre = Lang::get("meses.$mes");
        $cantidad = count($items);
        $lugarTexto = $cantidad == 1 ? Lang::get('historial.lugar_singular') : Lang::get('historial.lugar_plural');
        ?>

    <!-- Separador de mes -->
    <div class="mes-sep">
        <div class="mes-pill">
            <i class="fas fa-calendar-alt"></i>
            <?= $mesNombre ?> <?= $anioSel ?>
        </div>
        <div class="mes-line"></div>
        <div class="mes-count">
            <?= $cantidad ?> <?= $lugarTexto ?>
        </div>
    </div>

    <!-- Grid del mes -->
    <div class="hist-grid">
        <?php foreach ($items as $idx => $item):
            $dt       = $item['_dt'];
            $nombre   = $item['nombre']      ?? 'Atracción';
            $desc     = $item['descripcion'] ?? '';
            $descC    = mb_substr($desc, 0, 85) . (mb_strlen($desc) > 85 ? '…' : '');
            $imagen   = $item['imagen_url']  ?? 'assets/img/default.jpg';
            $categoria= $item['categoria']   ?? 'Atracción';
            $nota     = $item['descripcion_usuario'] ?? $item['nota'] ?? '';
            $calif    = $item['calificacion'] ?? null;
            $delay    = min($idx * 0.05, 0.3);
        ?>
        <div class="hist-card"
             style="animation-delay:<?= $delay ?>s"
             onclick="verDetalle(<?= (int)$item['atracciones_id'] ?>)">

            <!-- Imagen -->
            <div class="hist-img" style="background-image:url('<?= htmlspecialchars($imagen) ?>');">
                <div class="hist-img-ov"></div>

                <div class="hist-badges">
                    <span class="hbc"><?= htmlspecialchars(strtoupper($categoria)) ?></span>
                    <span class="hbc hbc-ok"><i class="fas fa-check-circle me-1"></i><?= Lang::get('historial.badge_visitado') ?></span>
                </div>

                <!-- Fecha -->
                <div class="hist-fecha">
                    <div class="hfecha-bub">
                        <div class="fd"><?= $dt->format('d') ?></div>
                        <div class="fm"><?= strtoupper($dt->format('M')) ?></div>
                    </div>
                </div>

                <!-- Rating si existe -->
                <?php if ($calif): ?>
                <div class="hist-rating">
                    <i class="fas fa-star"></i>
                    <span><?= number_format((float)$calif, 1) ?>/5</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Body -->
            <div class="hist-body">
                <div class="hist-name"><?= htmlspecialchars($nombre) ?></div>
                <div class="hist-desc"><?= htmlspecialchars($descC) ?></div>

                <?php if ($nota): ?>
                <div class="hist-nota tiene-nota">
                    <i class="fas fa-quote-left me-1" style="font-size:.6rem;color:var(--green);"></i>
                    <?= htmlspecialchars(mb_substr($nota, 0, 80)) . (mb_strlen($nota) > 80 ? '…' : '') ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="hist-foot">
                <div class="hist-meta">
                    <div class="hist-meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?= $dt->format('H:i') ?> <?= Lang::get('historial.hora_abbr') ?></span>
                    </div>
                    <div class="hist-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($item['ciudad'] ?? 'La Paz') ?></span>
                    </div>
                </div>
                <button class="btn-detalle" onclick="event.stopPropagation();verDetalle(<?= (int)$item['atracciones_id'] ?>)">
                    <i class="fas fa-eye me-1"></i><?= Lang::get('historial.btn_ver_mas') ?>
                </button>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

    <?php endforeach; ?>

<?php endif; ?>

</div><!-- /hist-wrap -->

<div id="contenedorModalDetalle"></div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
// ── Textos i18n desde PHP ─────────────────────────────────────────────────────
const T = <?= json_encode($t, JSON_UNESCAPED_UNICODE) ?>;

function verDetalle(id) {
    const elAnt = document.getElementById('modalDetalleAtraccion');
    if (elAnt) {
        const i = bootstrap.Modal.getInstance(elAnt);
        if (i) i.dispose();
        elAnt.remove();
    }
    $('#contenedorModalDetalle').html(`
        <div class="modal fade" id="modalDetalleAtraccion" data-bs-backdrop="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content p-5 text-center" style="border-radius:16px">
                    <div class="spinner-border mx-auto mb-3" style="color:#10b981;width:2.5rem;height:2.5rem"></div>
                    <p class="text-muted mb-0">${T.modal_cargando}</p>
                </div>
            </div>
        </div>`);
    const sp = new bootstrap.Modal(document.getElementById('modalDetalleAtraccion'));
    $.ajax({
        url: 'index.php?c=atracciones&a=detalle&id=' + id,
        type: 'GET',
        success: function(html) {
            sp.hide();
            $('#contenedorModalDetalle').html(html);
            new bootstrap.Modal(document.getElementById('modalDetalleAtraccion')).show();
        },
        error: function() {
            sp.hide();
            toastr.error(T.error_detalle);
        }
    });
}
</script>
</body>
</html>