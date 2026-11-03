<?php
include 'view/menu.php';

// ── Textos i18n para JS ─────────────────────────────────────────────────────
$t = [
    'confirm_quitar_titulo' => Lang::get('favoritos.confirm_quitar_titulo'),
    'confirm_quitar_texto'  => Lang::get('favoritos.confirm_quitar_texto'),
    'confirm_quitar_btn'    => Lang::get('favoritos.confirm_quitar_btn'),
    'confirm_cancelar'      => Lang::get('favoritos.confirm_cancelar'),
    'toast_quitado_ok'      => Lang::get('favoritos.toast_quitado_ok'),
    'toast_quitado_error'   => Lang::get('favoritos.toast_quitado_error'),
    'toast_conexion_error'  => Lang::get('favoritos.toast_conexion_error'),
    'modal_cargando'        => Lang::get('favoritos.modal_cargando'),
    'error_detalle'         => Lang::get('favoritos.error_detalle'),
];

// ── Helper para traducir categorías ────────────────────────────────────────
function traducirCategoria($cat) {
    $map = [
        'Trekking'      => 'categorias.trekking',
        'Montañismo'    => 'categorias.montanismo',
        'Cultura'       => 'categorias.cultura',
        'Downhill'      => 'categorias.downhill',
        'Vida Nocturna' => 'categorias.vida_nocturna',
        'Gastronomía'   => 'categorias.gastronomia',
        'Iconos Urbanos'=> 'categorias.iconos_urbanos',
    ];
    $key = $map[$cat] ?? 'categorias.default';
    return Lang::get($key);
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Lang::get('favoritos.titulo_pagina') ?></title>

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
    --gold2:    #fbbf24;
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
    background: linear-gradient(135deg, var(--bg-deep) 0%, #1a1208 50%, var(--bg) 100%);
    padding: 48px 24px 36px;
    border-bottom: 1px solid var(--border);
    position: relative; overflow: hidden;
}
.hero::before {
    content:''; position:absolute; top:-80px; right:-80px;
    width:420px; height:420px;
    background: radial-gradient(circle, rgba(245,158,11,.15) 0%, transparent 70%);
    pointer-events:none;
}
.hero::after {
    content:''; position:absolute; bottom:-60px; left:10%;
    width:300px; height:300px;
    background: radial-gradient(circle, rgba(251,191,36,.08) 0%, transparent 70%);
    pointer-events:none;
}
.hero h1 {
    font-family:'Syne',sans-serif; font-size:2.4rem; font-weight:800;
    background: linear-gradient(135deg, #fff 0%, #fde68a 100%);
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
.stat-box:hover { border-color:rgba(245,158,11,.3); }
.stat-ico {
    width:44px; height:44px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; flex-shrink:0;
}
.stat-ico.gold    { background:rgba(245,158,11,.12);  color:var(--gold); }
.stat-ico.purple  { background:rgba(102,126,234,.12); color:var(--primary); }
.stat-ico.green   { background:rgba(16,185,129,.12);  color:var(--green); }
.stat-ico.orange  { background:rgba(249,115,22,.12);  color:#f97316; }
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
.year-btn:hover { border-color:rgba(245,158,11,.4); color:var(--text); }
.year-btn.active {
    background:rgba(245,158,11,.12);
    border-color:rgba(245,158,11,.5);
    color:var(--gold);
}
.year-lbl {
    font-size:.72rem; text-transform:uppercase;
    letter-spacing:.8px; color:var(--muted);
    font-weight:700; margin-right:4px;
}

/* ── FILTRO CATEGORÍA ── */
.cat-tabs {
    max-width:1200px; margin:16px auto 0;
    display:flex; align-items:center; gap:8px;
    padding:0 16px; flex-wrap:wrap;
}
.cat-btn {
    padding:6px 16px; border-radius:50px;
    border:1px solid var(--border);
    background:var(--surf2); color:var(--muted);
    font-size:.78rem; font-weight:600;
    cursor:pointer; transition:all .2s; text-decoration:none;
}
.cat-btn:hover { border-color:rgba(245,158,11,.35); color:var(--text); }
.cat-btn.active {
    background:rgba(245,158,11,.1);
    border-color:rgba(245,158,11,.4);
    color:var(--gold2);
}
.cat-lbl {
    font-size:.72rem; text-transform:uppercase;
    letter-spacing:.8px; color:var(--muted);
    font-weight:700; margin-right:4px; white-space:nowrap;
}

/* ── CONTENIDO ── */
.fav-wrap {
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
    background:rgba(245,158,11,.1);
    border:1px solid rgba(245,158,11,.3);
    border-radius:50px; padding:5px 16px;
    font-family:'Syne',sans-serif; font-size:.82rem;
    font-weight:800; color:var(--gold);
    white-space:nowrap;
    display:flex; align-items:center; gap:7px;
}
.mes-pill i { font-size:.7rem; }
.mes-line {
    flex:1; height:1px;
    background:linear-gradient(to right, rgba(245,158,11,.25), transparent);
}
.mes-count {
    font-size:.72rem; color:var(--muted); white-space:nowrap;
}

/* ── GRID ── */
.fav-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:18px;
}

/* ── CARD ── */
.fav-card {
    background:var(--surf);
    border:1px solid rgba(245,158,11,.2);
    border-radius:var(--r); overflow:hidden;
    display:flex; flex-direction:column;
    transition:transform .25s, box-shadow .25s, border-color .25s;
    opacity:0; animation:cardIn .4s ease forwards;
    cursor:pointer;
    position:relative;
}
@keyframes cardIn {
    from { opacity:0; transform:translateY(16px); }
    to   { opacity:1; transform:translateY(0); }
}
.fav-card:hover {
    transform:translateY(-5px);
    box-shadow:0 18px 44px rgba(0,0,0,.5), 0 0 0 1px rgba(245,158,11,.3);
    border-color:rgba(245,158,11,.5);
}

/* Estrella animada al hacer hover */
.fav-card::after {
    content:'\f005';
    font-family:'Font Awesome 6 Free'; font-weight:900;
    position:absolute; top:12px; right:14px;
    font-size:1rem; color:var(--gold);
    opacity:.35; transition:opacity .2s, transform .2s;
    z-index:2;
}
.fav-card:hover::after { opacity:.8; transform:scale(1.2) rotate(10deg); }

/* Imagen */
.fav-img {
    height:165px; background-size:cover; background-position:center;
    position:relative;
}
.fav-img-ov {
    position:absolute; inset:0;
    background:linear-gradient(to top, rgba(13,15,26,.9) 0%, transparent 55%);
}
.fav-badges {
    position:absolute; top:10px; left:10px;
    display:flex; gap:6px; flex-wrap:wrap;
}
.fbc {
    font-size:.62rem; font-weight:800; text-transform:uppercase;
    letter-spacing:.5px; padding:4px 10px; border-radius:6px;
    backdrop-filter:blur(8px); color:#fff;
    background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.12);
}
.fbc-fav  { background:rgba(245,158,11,.85); border-color:transparent;
            animation:pulse-gold 2.5s infinite; }
.fbc-vis  { background:rgba(16,185,129,.75); border-color:transparent; }
@keyframes pulse-gold {
    0%,100%{ box-shadow:0 0 0 0 rgba(245,158,11,.4); }
    50%    { box-shadow:0 0 0 5px rgba(245,158,11,0); }
}

/* Fecha burbuja */
.fav-fecha {
    position:absolute; bottom:10px; left:12px;
}
.ffecha-bub {
    background:rgba(0,0,0,.75); backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,.12);
    border-radius:10px; padding:5px 11px; text-align:center;
}
.ffecha-bub .fd {
    font-family:'Syne',sans-serif; font-size:1.15rem;
    font-weight:800; color:#fff; line-height:1;
}
.ffecha-bub .fm {
    font-size:.56rem; color:rgba(255,255,255,.5);
    text-transform:uppercase; letter-spacing:.5px;
}

/* Rating badge */
.fav-rating {
    position:absolute; bottom:10px; right:12px;
    display:flex; align-items:center; gap:4px;
    background:rgba(0,0,0,.7); backdrop-filter:blur(8px);
    border:1px solid rgba(245,158,11,.3);
    border-radius:8px; padding:4px 9px;
}
.fav-rating i { color:var(--gold); font-size:.65rem; }
.fav-rating span { color:#fff; font-size:.72rem; font-weight:700; }

/* Stars display */
.fav-stars {
    display:flex; gap:2px; align-items:center;
}
.fav-stars i {
    font-size:.65rem;
    color: var(--gold);
}
.fav-stars i.empty { color:rgba(255,255,255,.2); }

/* Body */
.fav-body { padding:14px 16px 10px; flex:1; display:flex; flex-direction:column; }
.fav-name {
    font-family:'Syne',sans-serif; font-size:.93rem; font-weight:700;
    color:#fff; margin-bottom:5px; line-height:1.3;
    display:-webkit-box; -webkit-line-clamp:2;
    -webkit-box-orient:vertical; overflow:hidden;
}
.fav-desc {
    font-size:.75rem; color:var(--muted); line-height:1.5; flex:1;
    display:-webkit-box; -webkit-line-clamp:2;
    -webkit-box-orient:vertical; overflow:hidden;
    margin-bottom:10px;
}
/* Nota/comentario del usuario */
.fav-nota {
    background:rgba(245,158,11,.06);
    border-left:2px solid rgba(245,158,11,.4);
    border-radius:0 8px 8px 0;
    padding:7px 10px; margin-bottom:10px;
    font-size:.73rem; color:rgba(255,255,255,.6);
    font-style:italic;
}

/* Tags */
.fav-tags {
    display:flex; gap:5px; flex-wrap:wrap; margin-bottom:10px;
}
.fav-tag {
    background:rgba(245,158,11,.08);
    border:1px solid rgba(245,158,11,.2);
    color:rgba(245,158,11,.85);
    border-radius:50px; padding:2px 9px;
    font-size:.65rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.4px;
}

/* Footer */
.fav-foot {
    padding:10px 16px 13px;
    border-top:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between;
    gap:8px;
}
.fav-meta { display:flex; align-items:center; gap:12px; }
.fav-meta-item {
    display:flex; align-items:center; gap:4px;
    font-size:.7rem; color:var(--muted);
}
.fav-meta-item i { color:var(--gold); font-size:.6rem; }
.btn-detalle {
    background:rgba(245,158,11,.1);
    border:1px solid rgba(245,158,11,.25);
    color:var(--gold); border-radius:9px;
    padding:6px 13px; font-size:.73rem; font-weight:700;
    cursor:pointer; transition:all .2s; white-space:nowrap;
    display:flex; align-items:center; gap:5px;
}
.btn-detalle:hover {
    background:rgba(245,158,11,.2);
    border-color:rgba(245,158,11,.5);
    transform:translateY(-1px);
}
.btn-quitar {
    background:rgba(239,68,68,.08);
    border:1px solid rgba(239,68,68,.2);
    color:rgba(239,68,68,.7); border-radius:9px;
    padding:6px 10px; font-size:.73rem; font-weight:700;
    cursor:pointer; transition:all .2s; white-space:nowrap;
}
.btn-quitar:hover {
    background:rgba(239,68,68,.15);
    border-color:rgba(239,68,68,.45);
    color:#ef4444;
    transform:translateY(-1px);
}

/* ── VACÍO ── */
.fav-vacio {
    text-align:center; padding:80px 20px;
}
.fav-vacio .ico {
    width:80px; height:80px;
    background:var(--surf2); border:1px solid var(--border);
    border-radius:50%; display:flex; align-items:center; justify-content:center;
    margin:0 auto 20px;
}
.fav-vacio .ico i { font-size:2rem; color:var(--gold); opacity:.4; }
.fav-vacio h3 {
    font-family:'Syne',sans-serif; font-size:1.2rem;
    color:rgba(255,255,255,.5); margin-bottom:8px;
}
.fav-vacio p { font-size:.85rem; color:var(--muted); }
.btn-ir-historial {
    margin-top:20px; display:inline-flex; align-items:center; gap:8px;
    background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.25);
    color:var(--gold); border-radius:12px; padding:10px 22px;
    font-size:.85rem; font-weight:700; text-decoration:none;
    transition:all .2s;
}
.btn-ir-historial:hover {
    background:rgba(245,158,11,.18);
    border-color:rgba(245,158,11,.5);
    color:var(--gold2);
    transform:translateY(-2px);
}

::-webkit-scrollbar{ width:6px; }
::-webkit-scrollbar-track{ background:var(--bg); }
::-webkit-scrollbar-thumb{ background:var(--surf2); border-radius:3px; }
</style>
</head>

<body>
<main>

<?php
$tz = new DateTimeZone('America/La_Paz');

// Parámetros de filtro
$anioSel = (int)($_GET['anio'] ?? date('Y'));
$catSel  = $_GET['cat']  ?? 'todas';

// ── Procesar favoritos: agrupar por mes ──
$porMes       = [];
$totalFavs    = 0;
$categorias   = [];
$puntuaciones = [];
$aniosDisponibles = [];

if (!empty($favoritos)) {
    foreach ($favoritos as $item) {
        if (($item['estado'] ?? '') !== 'favorito') continue;

        $fechaRaw = $item['fecha_visita'] ?? null;
        if (!$fechaRaw) continue;

        try {
            $dt = new DateTime($fechaRaw);
            $dt->setTimezone($tz);
        } catch (Exception $e) { continue; }

        $anio = (int)$dt->format('Y');
        $mes  = (int)$dt->format('n');
        $cat  = $item['categoria'] ?? '';

        $aniosDisponibles[$anio] = true;

        if ($anio !== $anioSel) continue;
        if ($catSel !== 'todas' && strtolower($cat) !== strtolower($catSel)) continue;

        $porMes[$mes][] = array_merge($item, ['_dt' => $dt]);
        $totalFavs++;
        if ($cat) $categorias[$cat] = ($categorias[$cat] ?? 0) + 1;
        if (!empty($item['calificacion'])) $puntuaciones[] = (float)$item['calificacion'];
    }
    krsort($porMes);
}

// Stats globales (sin filtro de año/cat)
$totalGlobal = 0;
$catGlobal   = [];
if (!empty($favoritos)) {
    foreach ($favoritos as $item) {
        if (($item['estado'] ?? '') !== 'favorito') continue;
        $totalGlobal++;
        $c = $item['categoria'] ?? '';
        if ($c) $catGlobal[$c] = ($catGlobal[$c] ?? 0) + 1;
    }
}
arsort($catGlobal);
$catTop = array_key_first($catGlobal) ?? '—';

$promPunt = !empty($puntuaciones) ? round(array_sum($puntuaciones)/count($puntuaciones), 1) : null;

// Años para pestañas
krsort($aniosDisponibles);
if (empty($aniosDisponibles)) $aniosDisponibles[date('Y')] = true;

// Categorías únicas para filtro (del año seleccionado)
$catsAnio = [];
if (!empty($favoritos)) {
    foreach ($favoritos as $item) {
        if (($item['estado'] ?? '') !== 'favorito') continue;
        $fechaRaw = $item['fecha_visita'] ?? null;
        if (!$fechaRaw) continue;
        try {
            $dt2 = new DateTime($fechaRaw); $dt2->setTimezone($tz);
            if ((int)$dt2->format('Y') !== $anioSel) continue;
        } catch (Exception $e) { continue; }
        $c = $item['categoria'] ?? '';
        if ($c) $catsAnio[$c] = true;
    }
}
ksort($catsAnio);
?>

<!-- ── HERO ── -->
<div class="hero">
    <h1>
        <i class="fas fa-star me-3"
           style="color:var(--gold);font-size:1.8rem;vertical-align:middle;-webkit-text-fill-color:var(--gold);"></i>
        <?= Lang::get('favoritos.hero_titulo') ?>
    </h1>
    <p><?= Lang::get('favoritos.hero_subtitulo') ?></p>
</div>

<!-- ── STATS ── -->
<div class="stats-bar">
    <div class="stat-box">
        <div class="stat-ico gold"><i class="fas fa-star"></i></div>
        <div>
            <div class="stat-val"><?= $totalGlobal ?></div>
            <div class="stat-lbl"><?= Lang::get('favoritos.stats_total') ?></div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-ico purple"><i class="fas fa-calendar-star"></i></div>
        <div>
            <div class="stat-val"><?= count($porMes) ?></div>
            <div class="stat-lbl"><?= Lang::get('favoritos.stats_meses') ?></div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-ico green"><i class="fas fa-trophy"></i></div>
        <div>
            <div class="stat-val"><?= $promPunt ? $promPunt.'/5' : '—' ?></div>
            <div class="stat-lbl"><?= Lang::get('favoritos.stats_promedio') ?></div>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-ico orange"><i class="fas fa-tag"></i></div>
        <div>
            <div class="stat-val" style="font-size:1rem;padding-top:4px;">
                <?= traducirCategoria($catTop) ?>
            </div>
            <div class="stat-lbl"><?= Lang::get('favoritos.stats_categoria_top') ?></div>
        </div>
    </div>
</div>

<!-- ── FILTRO AÑO ── -->
<div class="year-tabs">
    <span class="year-lbl"><?= Lang::get('favoritos.year_label') ?></span>
    <?php foreach (array_keys($aniosDisponibles) as $a): ?>
        <a href="index.php?c=favoritos&a=Index&anio=<?= $a ?>&cat=<?= urlencode($catSel) ?>"
           class="year-btn <?= $a === $anioSel ? 'active' : '' ?>">
            <?= $a ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- ── FILTRO CATEGORÍA ── -->
<?php if (!empty($catsAnio)): ?>
<div class="cat-tabs">
    <span class="cat-lbl"><?= Lang::get('favoritos.cat_label') ?></span>
    <a href="index.php?c=favoritos&a=Index&anio=<?= $anioSel ?>&cat=todas"
       class="cat-btn <?= $catSel === 'todas' ? 'active' : '' ?>">
        <i class="fas fa-th me-1"></i><?= Lang::get('favoritos.todas') ?>
    </a>
    <?php foreach (array_keys($catsAnio) as $c): ?>
        <a href="index.php?c=favoritos&a=Index&anio=<?= $anioSel ?>&cat=<?= urlencode($c) ?>"
           class="cat-btn <?= strtolower($catSel) === strtolower($c) ? 'active' : '' ?>">
            <?= traducirCategoria($c) ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── CONTENIDO POR MES ── -->
<div class="fav-wrap">

<?php if (empty($porMes)): ?>
    <div class="fav-vacio">
        <div class="ico"><i class="fas fa-star"></i></div>
        <h3>
            <?php if ($catSel !== 'todas'): ?>
                <?= sprintf(Lang::get('favoritos.sin_favoritos_titulo_cat'), traducirCategoria($catSel), $anioSel) ?>
            <?php else: ?>
                <?= sprintf(Lang::get('favoritos.sin_favoritos_titulo_anio'), $anioSel) ?>
            <?php endif; ?>
        </h3>
        <p>
            <?php if ($totalGlobal > 0): ?>
                <?= Lang::get('favoritos.sin_favoritos_texto_probar') ?>
            <?php else: ?>
                <?= Lang::get('favoritos.sin_favoritos_texto_marcar') ?>
            <?php endif; ?>
        </p>
        <a href="index.php?c=itinerarios&a=Index" class="btn-ir-historial">
            <i class="fas fa-calendar-alt"></i> <?= Lang::get('favoritos.btn_ver_agenda') ?>
        </a>
    </div>

<?php else: ?>

    <?php foreach ($porMes as $mes => $items): ?>
        <?php
        $mesNombre = Lang::get("meses.$mes");
        $cantidad = count($items);
        $favoritoTexto = $cantidad == 1 ? Lang::get('favoritos.favorito_singular') : Lang::get('favoritos.favorito_plural');
        ?>

    <!-- Separador de mes -->
    <div class="mes-sep">
        <div class="mes-pill">
            <i class="fas fa-star"></i>
            <?= $mesNombre ?> <?= $anioSel ?>
        </div>
        <div class="mes-line"></div>
        <div class="mes-count">
            <?= $cantidad ?> <?= $favoritoTexto ?>
        </div>
    </div>

    <!-- Grid del mes -->
    <div class="fav-grid">
        <?php foreach ($items as $idx => $item):
            $dt       = $item['_dt'];
            $nombre   = $item['nombre']      ?? 'Atracción';
            $desc     = $item['descripcion'] ?? '';
            $descC    = mb_substr($desc, 0, 85) . (mb_strlen($desc) > 85 ? '…' : '');
            $imagen   = $item['imagen_url']  ?? 'assets/img/default.jpg';
            $categoria= $item['categoria']   ?? 'Atracción';
            $calif    = isset($item['calificacion']) ? (float)$item['calificacion'] : null;
            $comentario = trim($item['comentario'] ?? $item['nota'] ?? '');
            $tags     = !empty($item['tags']) ? (is_array($item['tags']) ? $item['tags'] : json_decode($item['tags'], true)) : [];
            $delay    = min($idx * 0.05, 0.35);
        ?>
        <div class="fav-card"
             style="animation-delay:<?= $delay ?>s"
             data-itin-id="<?= (int)$item['id'] ?>"
             data-atr-id="<?= (int)$item['atracciones_id'] ?>"
             data-nombre="<?= htmlspecialchars($nombre, ENT_QUOTES) ?>"
             onclick="verDetalle(<?= (int)$item['atracciones_id'] ?>)">

            <!-- Imagen -->
            <div class="fav-img" style="background-image:url('<?= htmlspecialchars($imagen) ?>');">
                <div class="fav-img-ov"></div>

                <div class="fav-badges">
                    <span class="fbc"><?= strtoupper(traducirCategoria($categoria)) ?></span>
                    <span class="fbc fbc-fav">
                        <i class="fas fa-star me-1"></i><?= Lang::get('favoritos.favorito_badge') ?>
                    </span>
                </div>

                <!-- Fecha -->
                <div class="fav-fecha">
                    <div class="ffecha-bub">
                        <div class="fd"><?= $dt->format('d') ?></div>
                        <div class="fm"><?= strtoupper($dt->format('M')) ?></div>
                    </div>
                </div>

                <!-- Rating -->
                <?php if ($calif): ?>
                <div class="fav-rating">
                    <?php
                    $estrellasLlenas = (int)round($calif);
                    for ($s = 1; $s <= 5; $s++):
                    ?>
                        <i class="fas fa-star<?= $s > $estrellasLlenas ? ' empty' : '' ?>"
                           style="<?= $s > $estrellasLlenas ? 'color:rgba(255,255,255,.2)' : '' ?>"></i>
                    <?php endfor; ?>
                    <span><?= number_format($calif, 1) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Body -->
            <div class="fav-body">
                <div class="fav-name"><?= htmlspecialchars($nombre) ?></div>
                <div class="fav-desc"><?= htmlspecialchars($descC) ?></div>

                <?php if ($comentario): ?>
                <div class="fav-nota">
                    <i class="fas fa-quote-left me-1" style="font-size:.6rem;color:var(--gold);"></i>
                    <?= htmlspecialchars(mb_substr($comentario, 0, 90)) . (mb_strlen($comentario) > 90 ? '…' : '') ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($tags)): ?>
                <div class="fav-tags">
                    <?php foreach (array_slice($tags, 0, 4) as $tag): ?>
                        <span class="fav-tag"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="fav-foot">
                <div class="fav-meta">
                    <div class="fav-meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?= $dt->format('H:i') ?> <?= Lang::get('favoritos.hora_abbr') ?></span>
                    </div>
                    <div class="fav-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($item['ciudad'] ?? 'La Paz') ?></span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn-quitar btn-quitar-fav"
                            title="<?= Lang::get('favoritos.btn_quitar') ?>"
                            onclick="event.stopPropagation();quitarFavorito(this)">
                        <i class="fas fa-heart-broken"></i>
                    </button>
                    <button class="btn-detalle"
                            onclick="event.stopPropagation();verDetalle(<?= (int)$item['atracciones_id'] ?>)">
                        <i class="fas fa-eye"></i> <?= Lang::get('favoritos.btn_ver_mas') ?>
                    </button>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    </div>

    <?php endforeach; ?>

<?php endif; ?>

</div><!-- /fav-wrap -->

<div id="contenedorModalDetalle"></div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── Textos i18n desde PHP ─────────────────────────────────────────────────────
const T = <?= json_encode($t, JSON_UNESCAPED_UNICODE) ?>;

// ── Ver detalle de atracción ──
function verDetalle(id) {
    const elAnt = document.getElementById('modalDetalleAtraccion');
    if (elAnt) {
        const inst = bootstrap.Modal.getInstance(elAnt);
        if (inst) inst.dispose();
        elAnt.remove();
    }
    $('#contenedorModalDetalle').html(`
        <div class="modal fade" id="modalDetalleAtraccion" data-bs-backdrop="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content p-5 text-center"
                     style="background:#1e2140;border:1px solid rgba(245,158,11,.2);border-radius:16px;">
                    <div class="spinner-border mx-auto mb-3"
                         style="color:#f59e0b;width:2.5rem;height:2.5rem"></div>
                    <p class="text-muted mb-0">${T.modal_cargando}</p>
                </div>
            </div>
        </div>`);
    const sp = new bootstrap.Modal(document.getElementById('modalDetalleAtraccion'));
    sp.show();
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

// ── Quitar de favoritos ──
function quitarFavorito(btn) {
    const card = $(btn).closest('.fav-card');
    const idItinerario = card.data('itin-id');
    const nombre = card.data('nombre');

    Swal.fire({
        title: T.confirm_quitar_titulo,
        text: T.confirm_quitar_texto.replace('%s', nombre),
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-heart-broken me-2"></i>' + T.confirm_quitar_btn,
        cancelButtonText: T.confirm_cancelar,
        background: '#1e2140',
        color: '#e2e8f0',
        backdrop: 'rgba(15, 22, 41, 0.8)',
        customClass: { popup: 'rounded-4' }
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.post('index.php?c=favoritos&a=QuitarFavorito',
            { id: idItinerario },
            function(res) {
                if (res.success) {
                    toastr.success(T.toast_quitado_ok);
                    card.css({ transition: 'all 0.4s ease', transform: 'scale(0.8)', opacity: '0' });
                    setTimeout(() => {
                        card.remove();
                        if ($('.fav-card').length === 0) location.reload();
                    }, 400);
                } else {
                    toastr.error(res.message || T.toast_quitado_error);
                }
            }, 'json'
        ).fail(() => toastr.error(T.toast_conexion_error));
    });
}

// ── Toastr config ──
toastr.options = {
    positionClass: 'toast-bottom-right',
    timeOut: 3000,
    closeButton: true,
};

// Sidebar sync (si el menu.php lo usa)
document.addEventListener('DOMContentLoaded', function() {
    const main = document.querySelector('main');
    if (document.body.classList.contains('sidebar-collapsed') && main) {
        main.classList.add('min-main');
    }
});
</script>
</body>
</html>