<!DOCTYPE html>

<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Lang::get('buscar.titulo_pagina') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="assets/css/recomendaciones_cards.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        html, body {
            margin: 0; padding: 0; min-height: 100vh;
            background: radial-gradient(circle at top right, #1a1a3a 0%, #0f172a 100%) !important;
            background-attachment: fixed !important;
            color: #181313;
            font-family: 'DM Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative; overflow-x: hidden;
        }
        body::before {
            content: "";
            position: fixed; top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle at center, rgba(102,126,234,0.05) 0%, transparent 50%);
            animation: moveGlow 15s linear infinite;
            z-index: -1; pointer-events: none;
        }
        :root {
            --primary:    #667eea;
            --secondary:  #764ba2;
            --bg:         #1a1a2e;
            --bg-deep:    #0f1629;
            --border:     rgba(255,255,255,0.07);
            --muted:      rgba(255,255,255,0.4);
            --star-filled: #f59e0b;
            --star-empty:  rgba(0,0,0,0.15);
            --text-overlay: #2d3748;
            --text-muted:   #718096;
            --ia-bg:    linear-gradient(135deg, rgba(102,126,234,0.12), rgba(167,139,250,0.08));
            --ia-border: #667eea;
            --ia-text:   #4a5568;
        }
        body.dark-mode {
            --star-empty: rgba(255,255,255,0.2);
            --text-muted: #94a3b8;
            --ia-bg: linear-gradient(135deg, rgba(102,126,234,0.2), rgba(167,139,250,0.1));
            --ia-border: #a78bfa;
            --ia-text: rgba(255,255,255,0.85);
        }
        main { margin-left: 280px; transition: margin-left 0.3s; min-height: 100vh; background: #1a1a2e !important; }
        main.min-main { margin-left: 85px; }
        @media (max-width: 768px) { main { margin-left: 0 !important; } }
        .buscar-hero {
            background: linear-gradient(135deg, var(--bg-deep) 0%, #1a1040 50%, var(--bg) 100%);
            padding: 48px 24px 0; margin-bottom: 0;
            border-bottom: 1px solid var(--border);
            position: relative; overflow: hidden;
        }
        .buscar-hero::before {
            content: ''; position: absolute; top: -80px; right: -80px;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(102,126,234,.18) 0%, transparent 70%);
            pointer-events: none;
        }
        .buscar-hero::after {
            content: ''; position: absolute; bottom: -60px; left: 10%;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(118,75,162,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .buscar-hero-inner { max-width: 900px; margin: 0 auto; padding: 0 20px 30px; text-align: center; position: relative; z-index: 1; }
        .buscar-hero-inner h1 {
            font-family: 'Syne', sans-serif; font-size: 2.4rem; font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #b8c3f8 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            margin-bottom: 6px; letter-spacing: -0.5px;
        }
        .buscar-hero-inner h1 i { -webkit-text-fill-color: var(--primary); color: var(--primary); font-size: 1.8rem; vertical-align: middle; margin-right: 10px; }
        .buscar-hero-inner p { color: var(--muted); font-size: 1rem; font-weight: 300; margin-bottom: 24px; }
        .search-wrap { position: relative; max-width: 640px; margin: 0 auto 20px; }
        .search-wrap input {
            width: 100%; padding: 15px 110px 15px 20px;
            border-radius: 50px; border: 2px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.07); color: #fff; font-size: .95rem;
            backdrop-filter: blur(8px); transition: all .3s; outline: none;
            font-family: 'DM Sans', sans-serif;
        }
        .search-wrap input::placeholder { color: rgba(255,255,255,0.35); }
        .search-wrap input:focus { border-color: var(--primary); background: rgba(255,255,255,0.1); box-shadow: 0 0 0 4px rgba(102,126,234,.18); }
        .search-wrap .search-clear { position: absolute; right: 52px; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255,255,255,.4); cursor: pointer; font-size: .85rem; display: none; padding: 4px; }
        .search-wrap .search-btn { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: var(--primary); border: none; color: #fff; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: .88rem; transition: background .2s; }
        .search-wrap .search-btn:hover { background: #5a6fd6; }
        .filtros-wrap { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; padding-bottom: 4px; }
        .filtro-btn { padding: 8px 16px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.75); font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.2s; white-space: nowrap; display: flex; align-items: center; gap: 6px; letter-spacing: 0.3px; }
        .filtro-btn:hover { background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.4); }
        .filtro-btn.active { color: #fff; border-color: transparent; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .filtro-btn[data-cat="todos"].active        { background: #667eea; }
        .filtro-btn[data-cat="trekking"].active     { background: #166534; }
        .filtro-btn[data-cat="montanismo"].active   { background: #1e3a5f; }
        .filtro-btn[data-cat="downhill"].active     { background: #0e7490; }
        .filtro-btn[data-cat="cultura"].active      { background: #92400e; }
        .filtro-btn[data-cat="vida_nocturna"].active{ background: #7c3aed; }
        .filtro-btn[data-cat="gastronomia"].active  { background: #ea580c; }
        .filtro-btn[data-cat="iconos_urbanos"].active{ background: #1e40af; }
        .filtro-btn[data-cat="festividades"].active { background: #b91c7b; }
        /* ── Filtros avanzados ── */
        .filtros-avanzados { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; align-items:flex-end; max-width:1100px; margin:16px auto 0; }
        .fa-campo { display:flex; flex-direction:column; gap:4px; font-size:.7rem; font-weight:700; letter-spacing:.3px; color:rgba(255,255,255,.75); text-transform:uppercase; }
        .fa-campo span i { margin-right:4px; opacity:.8; }
        .fa-campo select { min-width:150px; padding:8px 30px 8px 12px; border-radius:12px; border:1px solid rgba(255,255,255,.22);
            background:rgba(255,255,255,.08) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23cbd5e1'/%3E%3C/svg%3E") no-repeat right 12px center;
            color:#fff; font-size:.8rem; font-weight:600; text-transform:none; appearance:none; cursor:pointer; }
        .fa-campo select:focus { outline:none; border-color:#818cf8; box-shadow:0 0 0 3px rgba(129,140,248,.25); }
        .fa-campo select.activo { background-color:rgba(102,126,234,.35); border-color:#818cf8; }
        .fa-campo select option { color:#0f172a; background:#fff; }
        .fa-limpiar { padding:8px 14px; border-radius:12px; border:1px solid rgba(248,113,113,.5); background:rgba(248,113,113,.12); color:#fecaca; font-size:.78rem; font-weight:700; cursor:pointer; }
        .fa-limpiar:hover { background:rgba(248,113,113,.25); color:#fff; }
        @media (max-width:576px) { .fa-campo { flex:1 1 45%; } .fa-campo select { min-width:0; width:100%; } }
        .meses-wrap { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center; padding: 12px 0 4px; animation: fadeInDown 0.25s ease; max-width: 820px; margin: 0 auto; }
        @keyframes fadeInDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
        .mes-btn { padding: 5px 12px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.18); background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.65); font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.18s; position: relative; letter-spacing: 0.3px; }
        .mes-btn:hover { background: rgba(185,28,123,0.3); border-color: #b91c7b; color: #fff; }
        .mes-btn.active { background: #b91c7b; border-color: #b91c7b; color: #fff; box-shadow: 0 3px 10px rgba(185,28,123,0.4); }
        .mes-btn .mes-now-dot { position: absolute; top: -3px; right: -3px; width: 8px; height: 8px; background: #f59e0b; border-radius: 50%; border: 1px solid #0f3460; animation: pulse-dot 1.5s infinite; }
        @keyframes pulse-dot { 0%,100% { transform:scale(1); opacity:1; } 50% { transform:scale(1.4); opacity:0.7; } }
        .badge-este-mes { display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #f59e0b, #ef4444); color: #fff; font-size: 0.65rem; font-weight: 800; padding: 3px 8px; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.5px; animation: pulse-badge 2s infinite; }
        @keyframes pulse-badge { 0%,100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.5); } 50% { box-shadow: 0 0 0 6px rgba(245,158,11,0); } }
        .pill-mes { display: inline-flex; align-items: center; gap: 4px; background: rgba(185,28,123,0.25); border: 1px solid rgba(185,28,123,0.5); color: #f9a8d4; font-size: 0.7rem; font-weight: 700; padding: 3px 10px; border-radius: 50px; margin-right: 4px; }
        .barra-estado { background: rgba(255,255,255,0.04); border-top: 1px solid rgba(255,255,255,0.06); padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .barra-estado-txt { color: rgba(255,255,255,0.55); font-size: 0.82rem; }
        .barra-estado-txt span { color: #fff; font-weight: 700; }
        .sort-select { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); color: #fff; border-radius: 8px; padding: 6px 12px; font-size: 0.8rem; cursor: pointer; outline: none; }
        .sort-select option { background: #1a1a2e; color: #fff; }
        .buscar-grid { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 20px; padding: 0 15px; grid-auto-flow: dense; align-items: stretch; }
        .estado-vacio, .estado-loading { grid-column: 1/-1; text-align: center; padding: 80px 20px; color: rgba(255,255,255,0.5); }
        .estado-loading .spinner-border { width: 3rem; height: 3rem; color: #667eea; margin-bottom: 16px; }
        .paginacion-wrap { max-width: 1400px; margin: 0 auto; padding: 0 20px 60px; display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap; }
        .pag-btn { width: 40px; height: 40px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
        .pag-btn:hover:not(:disabled) { background: rgba(102,126,234,0.3); border-color: #667eea; color: #fff; }
        .pag-btn.active { background: #667eea; border-color: #667eea; color: #fff; box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
        .pag-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .pag-btn-wide { width: auto; padding: 0 16px; }
        .pag-info { color: rgba(255,255,255,0.4); font-size: 0.8rem; padding: 0 8px; }
        .stars-inline { display: inline-flex; align-items: center; gap: 2px; font-size: 0.72rem; }
        .stars-inline .s  { color: var(--star-filled); }
        .stars-inline .se { color: var(--star-empty); }
        body.dark-mode .stars-inline .s  { color: #fbbf24; text-shadow: 0 0 4px rgba(251,191,36,.45); }
        body.dark-mode .stars-inline .se { color: rgba(255,255,255,0.15) !important; }
        .rc-rating-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 50px; font-size: 0.78rem; font-weight: 800; color: #fff; white-space: nowrap; box-shadow: 0 3px 10px rgba(0,0,0,0.35); }
        .rc-rating-badge.excelente { background: linear-gradient(135deg,#16a34a,#15803d); }
        .rc-rating-badge.bueno     { background: linear-gradient(135deg,#d97706,#b45309); }
        .rc-rating-badge.regular   { background: linear-gradient(135deg,#ef4444,#b91c1c); }
        .rc-rating-num { font-size: 0.72rem; margin-left: 4px; color: #2d3748; font-weight: 700; }
        body.dark-mode .rc-rating-num { color: #e2e8f0; }
        .rc-cat-badge { font-size: 0.66rem; padding: 3px 9px; border-radius: 50px; background: rgba(255,255,255,0.15); color: #fff; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; max-width: 120px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .rc-info-row { color: var(--text-overlay); }
        .rc-info-row span { color: #2d3748; }
        body.dark-mode .rc-info-row span { color: #e2e8f0; }
        body.dark-mode .rc-info-row i { color: #667eea; }
        .rc-desc { color: var(--text-overlay); }
        .rc-detail-line { color: var(--text-overlay); }
        .rc-detail-line strong { color: var(--text-overlay); }
        .rc-ia-tip { background: var(--ia-bg); border-left-color: var(--ia-border); color: var(--ia-text); }
        .rc-ia-tip i { color: var(--ia-border); }
        body.dark-mode .rc-img-overlay { background: rgba(15,23,42,0.65); backdrop-filter: blur(1px) saturate(160%); border-color: rgba(255,255,255,0.1); }
        body.dark-mode .rc-cat-badge { background: rgba(255,255,255,0.2); color: #fff; }
        body.dark-mode .rc-badge { background: rgba(0,0,0,0.5); border-color: rgba(255,255,255,0.2); }
        @keyframes cardIn { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
        .rc-card { animation: cardIn 0.35s ease both; cursor: default; transition: transform 0.2s; }
        .rc-card:hover { transform: translateY(-4px); }
        .rc-card:nth-child(1)  { animation-delay: 0.00s; }
        .rc-card:nth-child(2)  { animation-delay: 0.04s; }
        .rc-card:nth-child(3)  { animation-delay: 0.08s; }
        .rc-card:nth-child(4)  { animation-delay: 0.12s; }
        .rc-card:nth-child(5)  { animation-delay: 0.16s; }
        .rc-card:nth-child(6)  { animation-delay: 0.20s; }
        .rc-card:nth-child(7)  { animation-delay: 0.24s; }
        .rc-card:nth-child(8)  { animation-delay: 0.28s; }
        .rc-card:nth-child(9)  { animation-delay: 0.32s; }
        .rc-card:nth-child(10) { animation-delay: 0.36s; }
        .rc-card:nth-child(11) { animation-delay: 0.40s; }
        .rc-card:nth-child(12) { animation-delay: 0.44s; }
        .no-results-icon { font-size: 4rem; color: rgba(255,255,255,0.15); margin-bottom: 16px; }
        @media (max-width: 576px) { .buscar-hero-inner h1 { font-size: 1.6rem; } .filtro-btn { font-size: 0.72rem; padding: 6px 12px; } .buscar-grid { grid-template-columns: 1fr; } }
    </style>
</head>

<?php
require_once 'model/consentimiento.php';
require_once 'model/restriccionEdad.php';
include 'view/menu.php';
include 'modal/modal_guardar_itinerario.php';


// Pasar textos JS al frontend via PHP para que Lang funcione en JS también
$t = [
    'lang' => $_SESSION['lang'] ?? 'es',  // ← agrega esta línea
    'cargando'        => Lang::get('buscar.cargando'),
    'sin_res_t'       => Lang::get('buscar.sin_resultados_t'),
    'sin_res_s'       => Lang::get('buscar.sin_resultados_s'),
    'error_conexion'  => Lang::get('buscar.error_conexion'),
    'reintentar'      => Lang::get('buscar.reintentar'),
    'modal_cargando'  => Lang::get('buscar.modal_cargando'),
    'modal_error'     => Lang::get('buscar.modal_error'),
    'modal_sesion'    => Lang::get('buscar.modal_sesion'),
    'modal_cerrar'    => Lang::get('buscar.modal_cerrar'),
    'card_motivo'     => Lang::get('buscar.card_motivo'),
    'card_motivo_nd'  => Lang::get('buscar.card_motivo_nd'),
    'card_compania'   => Lang::get('buscar.card_compania'),
    'card_compania_nd'=> Lang::get('buscar.card_compania_nd'),
    'card_exp'        => Lang::get('buscar.card_experiencias'),
    'card_exp_nd'     => Lang::get('buscar.card_exp_nd'),
    'card_detalle'    => Lang::get('buscar.card_ver_detalle'),
    'card_este_mes'   => Lang::get('buscar.card_este_mes'),
    'card_ciudad'     => Lang::get('buscar.card_ciudad_default'),
    'card_pais'       => Lang::get('buscar.card_pais_default'),
    'card_desc'       => Lang::get('buscar.card_desc_default'),
    'card_lugar'      => Lang::get('buscar.card_lugar_default'),
    // frases IA sin complemento
    'ia_trekking'        => Lang::get('buscar.ia_trekking'),
    'ia_montanismo'      => Lang::get('buscar.ia_montanismo'),
    'ia_downhill'        => Lang::get('buscar.ia_downhill'),
    'ia_cultura'         => Lang::get('buscar.ia_cultura'),
    'ia_vida_nocturna'   => Lang::get('buscar.ia_vida_nocturna'),
    'ia_gastronomia'     => Lang::get('buscar.ia_gastronomia'),
    'ia_iconos_urbanos'  => Lang::get('buscar.ia_iconos_urbanos'),
    'ia_festividades'    => Lang::get('buscar.ia_festividades'),
    'ia_miradores'       => Lang::get('buscar.ia_miradores'),
    'ia_default'         => Lang::get('buscar.ia_default'),
    // frases IA con complemento
    'ia_trekking_c'      => Lang::get('buscar.ia_trekking_c'),
    'ia_montanismo_c'    => Lang::get('buscar.ia_montanismo_c'),
    'ia_downhill_c'      => Lang::get('buscar.ia_downhill_c'),
    'ia_cultura_c'       => Lang::get('buscar.ia_cultura_c'),
    'ia_vida_nocturna_c' => Lang::get('buscar.ia_vida_nocturna_c'),
    'ia_gastronomia_c'   => Lang::get('buscar.ia_gastronomia_c'),
    'ia_iconos_urbanos_c'=> Lang::get('buscar.ia_iconos_urbanos_c'),
    'ia_festividades_c'  => Lang::get('buscar.ia_festividades_c'),
    'ia_miradores_c'     => Lang::get('buscar.ia_miradores_c'),
    'ia_default_c'       => Lang::get('buscar.ia_default_c'),
];
?>

<body>
<main>

    <!-- ══════════════════════════════════
         HERO + BUSCADOR
    ══════════════════════════════════ -->
    <div class="buscar-hero">
        <div class="buscar-hero-inner">
            <h1>
                <i class="fas fa-compass"></i><?= Lang::get('buscar.hero_titulo') ?>
            </h1>
            <p><?= Lang::get('buscar.hero_subtitulo') ?></p>

            <!-- Search -->
            <div class="search-wrap">
                <input type="text" id="searchInput"
                       placeholder="<?= Lang::get('buscar.search_placeholder') ?>"
                       autocomplete="off">
                <button class="search-clear" id="searchClear"
                        title="<?= Lang::get('buscar.search_limpiar') ?>">
                    <i class="fas fa-times"></i>
                </button>
                <button class="search-btn" id="searchBtn"
                        title="<?= Lang::get('buscar.search_buscar') ?>">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- Filtros de categoría -->
            <div class="filtros-wrap">
                <button class="filtro-btn active" data-cat="todos">
                    <i class="fas fa-th-large"></i> <?= Lang::get('buscar.cat_todos') ?>
                </button>
                <button class="filtro-btn" data-cat="trekking">
                    <i class="fas fa-hiking"></i> <?= Lang::get('buscar.cat_trekking') ?>
                </button>
                <button class="filtro-btn" data-cat="montanismo">
                    <i class="fas fa-mountain"></i> <?= Lang::get('buscar.cat_montanismo') ?>
                </button>
                <button class="filtro-btn" data-cat="downhill">
                    <i class="fas fa-bicycle"></i> <?= Lang::get('buscar.cat_downhill') ?>
                </button>
                <button class="filtro-btn" data-cat="cultura">
                    <i class="fas fa-landmark"></i> <?= Lang::get('buscar.cat_cultura') ?>
                </button>
                <?php if (!RestriccionEdad::esMenor($_SESSION['user_id'] ?? 0)): // menores de 18: sin bares ni discotecas ?>
                <button class="filtro-btn" data-cat="vida_nocturna">
                    <i class="fas fa-glass-cheers"></i> <?= Lang::get('buscar.cat_vida_nocturna') ?>
                </button>
                <?php endif; ?>
                <button class="filtro-btn" data-cat="gastronomia">
                    <i class="fas fa-utensils"></i> <?= Lang::get('buscar.cat_gastronomia') ?>
                </button>
                <button class="filtro-btn" data-cat="iconos_urbanos">
                    <i class="fas fa-city"></i> <?= Lang::get('buscar.cat_iconos_urbanos') ?>
                </button>
                <button class="filtro-btn" data-cat="festividades">
                    <i class="fas fa-calendar-star"></i> <?= Lang::get('buscar.cat_festividades') ?>
                </button>
            </div>

            <!-- Sub-filtro de meses -->
            <div class="meses-wrap" id="mesesWrap" style="display:none;">
                <button class="mes-btn active" data-mes="0"><?= Lang::get('buscar.mes_todos') ?></button>
                <button class="mes-btn" data-mes="1"><?= Lang::get('buscar.mes_ene') ?></button>
                <button class="mes-btn" data-mes="2"><?= Lang::get('buscar.mes_feb') ?></button>
                <button class="mes-btn" data-mes="3"><?= Lang::get('buscar.mes_mar') ?></button>
                <button class="mes-btn" data-mes="4"><?= Lang::get('buscar.mes_abr') ?></button>
                <button class="mes-btn" data-mes="5"><?= Lang::get('buscar.mes_may') ?></button>
                <button class="mes-btn" data-mes="6"><?= Lang::get('buscar.mes_jun') ?></button>
                <button class="mes-btn" data-mes="7"><?= Lang::get('buscar.mes_jul') ?></button>
                <button class="mes-btn" data-mes="8"><?= Lang::get('buscar.mes_ago') ?></button>
                <button class="mes-btn" data-mes="9"><?= Lang::get('buscar.mes_sep') ?></button>
                <button class="mes-btn" data-mes="10"><?= Lang::get('buscar.mes_oct') ?></button>
                <button class="mes-btn" data-mes="11"><?= Lang::get('buscar.mes_nov') ?></button>
                <button class="mes-btn" data-mes="12"><?= Lang::get('buscar.mes_dic') ?></button>
            </div>
            <!-- Filtros avanzados: motivo, compañía, día, horario, duración y dificultad -->
            <div class="filtros-avanzados" id="filtrosAvanzados">
                <label class="fa-campo"><span><i class="fas fa-suitcase"></i> <?= Lang::get('buscar.f_motivo') ?></span>
                    <select id="fMotivo"><option value=""><?= Lang::get('buscar.f_cualquiera') ?></option>
                        <option value="aventura"><?= Lang::valor('aventura') ?></option>
                        <option value="cultura"><?= Lang::valor('cultura') ?></option>
                        <option value="gastronomia"><?= Lang::valor('gastronomia') ?></option>
                        <option value="fiesta"><?= Lang::valor('fiesta') ?></option>
                        <option value="relax"><?= Lang::valor('relax') ?></option>
                        <option value="familiar"><?= Lang::valor('familiar') ?></option>
                    </select></label>
                <label class="fa-campo"><span><i class="fas fa-users"></i> <?= Lang::get('buscar.f_compania') ?></span>
                    <select id="fCompania"><option value=""><?= Lang::get('buscar.f_cualquiera') ?></option>
                        <option value="solo"><?= Lang::valor('solo') ?></option>
                        <option value="pareja"><?= Lang::valor('pareja') ?></option>
                        <option value="familia"><?= Lang::valor('familia') ?></option>
                        <option value="amigos"><?= Lang::valor('amigos') ?></option>
                        <option value="grupo"><?= Lang::valor('grupo') ?></option>
                    </select></label>
                <label class="fa-campo"><span><i class="fas fa-calendar-day"></i> <?= Lang::get('buscar.f_dia') ?></span>
                    <select id="fDia"><option value=""><?= Lang::get('buscar.f_cualquiera') ?></option>
                        <option value="hoy"><?= Lang::get('buscar.f_hoy') ?></option>
                        <option value="lunes"><?= Lang::get('buscar.dia_lunes') ?></option>
                        <option value="martes"><?= Lang::get('buscar.dia_martes') ?></option>
                        <option value="miercoles"><?= Lang::get('buscar.dia_miercoles') ?></option>
                        <option value="jueves"><?= Lang::get('buscar.dia_jueves') ?></option>
                        <option value="viernes"><?= Lang::get('buscar.dia_viernes') ?></option>
                        <option value="sabado"><?= Lang::get('buscar.dia_sabado') ?></option>
                        <option value="domingo"><?= Lang::get('buscar.dia_domingo') ?></option>
                    </select></label>
                <label class="fa-campo"><span><i class="fas fa-clock"></i> <?= Lang::get('buscar.f_horario') ?></span>
                    <select id="fHorario"><option value=""><?= Lang::get('buscar.f_cualquiera') ?></option>
                        <option value="ahora"><?= Lang::get('buscar.h_ahora') ?></option>
                        <option value="manana"><?= Lang::get('buscar.h_manana') ?></option>
                        <option value="tarde"><?= Lang::get('buscar.h_tarde') ?></option>
                        <option value="noche"><?= Lang::get('buscar.h_noche') ?></option>
                    </select></label>
                <label class="fa-campo"><span><i class="fas fa-hourglass-half"></i> <?= Lang::get('buscar.f_duracion') ?></span>
                    <select id="fDuracion"><option value=""><?= Lang::get('buscar.f_cualquiera') ?></option>
                        <option value="corta"><?= Lang::get('buscar.d_corta') ?></option>
                        <option value="media"><?= Lang::get('buscar.d_media') ?></option>
                        <option value="dia"><?= Lang::get('buscar.d_dia') ?></option>
                        <option value="varios"><?= Lang::get('buscar.d_varios') ?></option>
                    </select></label>
                <label class="fa-campo"><span><i class="fas fa-signal"></i> <?= Lang::get('buscar.f_dificultad') ?></span>
                    <select id="fDificultad"><option value=""><?= Lang::get('buscar.f_cualquiera') ?></option>
                        <option value="baja"><?= Lang::valor('baja') ?></option>
                        <option value="media"><?= Lang::valor('media') ?></option>
                        <option value="alta"><?= Lang::valor('alta') ?></option>
                        <option value="muy_alta"><?= Lang::valor('muy_alta') ?></option>
                    </select></label>
                <button type="button" class="fa-limpiar" id="fLimpiar" hidden><i class="fas fa-times"></i> <?= Lang::get('buscar.f_limpiar') ?></button>
            </div>
        </div>

        <!-- Barra de estado -->
        <div class="barra-estado">
            <div class="barra-estado-txt">
                <?= Lang::get('buscar.mostrando') ?>
                <span id="txtMostrando">—</span>
                <?= Lang::get('buscar.de') ?>
                <span id="txtTotal">—</span>
                <?= Lang::get('buscar.atracciones_txt') ?>
            </div>
            <select class="sort-select" id="sortSelect">
                <option value="nombre_asc"><?= Lang::get('buscar.orden_nombre_az') ?></option>
                <option value="nombre_desc"><?= Lang::get('buscar.orden_nombre_za') ?></option>
                <option value="puntuacion_desc" selected><?= Lang::get('buscar.orden_mejor_cal') ?></option>
                <option value="id_asc"><?= Lang::get('buscar.orden_categoria') ?></option>
            </select>
        </div>
    </div>

    <!-- ══════════════════════════════════
         GRID DE CARDS
    ══════════════════════════════════ -->
    <div style="background:#1a1a2e; padding: 28px 20px 20px;">
        <div class="buscar-grid" id="buscarGrid">
            <div class="estado-loading">
                <div class="spinner-border"></div>
                <p><?= Lang::get('buscar.cargando') ?></p>
            </div>
        </div>
    </div>

    <div style="background:#1a1a2e; padding-bottom: 20px;">
        <div class="paginacion-wrap" id="paginacionWrap"></div>
    </div>

</main>

<div id="contenedorModalDetalle"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="assets/js/etreva-auth.js"></script>

<script>
// ── Textos desde PHP (i18n para JS) ──────────────────────────────────────────
const T = <?= json_encode($t, JSON_UNESCAPED_UNICODE) ?>;

// ════════════════════════════════════════════════════════
//  CONFIG DE CATEGORÍAS
// ════════════════════════════════════════════════════════
const CATS_CONFIG = {
    'vida nocturna':  { icono:'fa-glass-cheers', color:'#7c3aed' },
    'vida_nocturna':  { icono:'fa-glass-cheers', color:'#7c3aed' },
    'discoteca':      { icono:'fa-music',         color:'#7c3aed' },
    'bar':            { icono:'fa-cocktail',      color:'#9333ea' },
    'cocteleria':     { icono:'fa-cocktail',      color:'#a855f7' },
    'cerveceria':     { icono:'fa-beer',          color:'#6d28d9' },
    'gastronomia':    { icono:'fa-utensils',      color:'#ea580c' },
    'gastronomía':    { icono:'fa-utensils',      color:'#ea580c' },
    'restaurante':    { icono:'fa-utensils',      color:'#ea580c' },
    'vegetariana':    { icono:'fa-leaf',          color:'#16a34a' },
    'vegana':         { icono:'fa-seedling',      color:'#15803d' },
    'cafeteria':      { icono:'fa-coffee',        color:'#92400e' },
    'café':           { icono:'fa-coffee',        color:'#92400e' },
    'parrilla':       { icono:'fa-fire',          color:'#b91c1c' },
    'italiana':       { icono:'fa-pizza-slice',   color:'#dc2626' },
    'japonesa':       { icono:'fa-fish',          color:'#0369a1' },
    'mariscos':       { icono:'fa-fish',          color:'#0284c7' },
    'aventura':       { icono:'fa-hiking',        color:'#15803d' },
    'trekking':       { icono:'fa-hiking',        color:'#166534' },
    'montañismo':     { icono:'fa-mountain',      color:'#1e3a5f' },
    'montaña':        { icono:'fa-mountain',      color:'#1e40af' },
    'downhill':       { icono:'fa-bicycle',       color:'#0e7490' },
    'lagunas':        { icono:'fa-water',         color:'#0369a1' },
    'miradores':      { icono:'fa-binoculars',    color:'#1e3a5f' },
    'cultura':        { icono:'fa-landmark',      color:'#92400e' },
    'museos':         { icono:'fa-university',    color:'#78350f' },
    'histórico':      { icono:'fa-landmark',      color:'#a16207' },
    'iconos urbanos': { icono:'fa-city',          color:'#1e40af' },
    'iconos_urbanos': { icono:'fa-city',          color:'#1e40af' },
    'festividades':   { icono:'fa-calendar-star', color:'#b91c7b' },
    'relax':          { icono:'fa-spa',           color:'#0891b2' },
    'default':        { icono:'fa-map-marker-alt',color:'#667eea' },
};

function obtenerCfg(categoria, subcategoria) {
    const cat = (categoria   || '').toLowerCase();
    const sub = (subcategoria|| '').toLowerCase();
    for (const [k,v] of Object.entries(CATS_CONFIG)) { if (sub.includes(k)) return v; }
    for (const [k,v] of Object.entries(CATS_CONFIG)) { if (cat.includes(k)) return v; }
    return CATS_CONFIG['default'];
}

// ════════════════════════════════════════════════════════
//  ESTADO GLOBAL
// ════════════════════════════════════════════════════════
// Avisos de riesgo ya aceptados por el turista y categorías que lo requieren
const CATS_RIESGO = <?= json_encode(array_values(Consentimiento::CATEGORIAS_RIESGO)) ?>;
const RIESGOS_OK  = <?= json_encode(Consentimiento::riesgosAceptados($_SESSION['user_id'] ?? 0)) ?>;

const ESTADO = {
    pagina: 1, porPagina: 18, totalItems: 0,
    categoria: 'todos', busqueda: '', orden: 'puntuacion_desc', mes: 0,
    filtros: { motivo: '', compania: '', dia: '', horario: '', duracion: '', dificultad: '' },
    todas: [], filtradas: [], matchMap: {}, coincidenciasMap: {},
};

const MES_ACTUAL = new Date().getMonth() + 1;
const MESES_NOMBRES = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

// ════════════════════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════════════════════
function formatHora(hora) {
    if (!hora) return null;
    const p = hora.split(':');
    let h = parseInt(p[0]);
    const m = p[1] || '00';
    const suf = h >= 12 ? 'PM' : 'AM';
    if (h > 12) h -= 12;
    if (h === 0) h = 12;
    return `${String(h).padStart(2,'0')}:${m} ${suf}`;
}


function formatDias(dias) {
    if (!dias) return null;

    // Detectar por claves del objeto
    const claves = typeof dias === 'object' && !Array.isArray(dias) ? Object.keys(dias) : [];
    const porClaves = claves.some(k => ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'].includes(k));

    // Idioma de sesión como fallback (por si las claves son ambiguas o españolas)
    const esIngles = porClaves || T.lang !== 'es';

    if (esIngles) {
        // Si los datos vienen con claves españolas pero el usuario está en inglés,
        // construimos el mapa cruzado
        const mapaES_EN = { lunes:'Mon', martes:'Tue', miercoles:'Wed', jueves:'Thu', viernes:'Fri', sabado:'Sat', domingo:'Sun' };
        const mapaEN_EN = { monday:'Mon', tuesday:'Tue', wednesday:'Wed', thursday:'Thu', friday:'Fri', saturday:'Sat', sunday:'Sun' };
        const ordenES   = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
        const ordenEN   = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

        if (typeof dias === 'object' && !Array.isArray(dias)) {
            // Intentar con claves inglesas primero, luego españolas
            const resultEN = ordenEN.filter(d => dias[d] === true || dias[d] === '1' || dias[d] === 1).map(d => mapaEN_EN[d]);
            if (resultEN.length > 0) return resultEN.join(' · ');
            // Fallback: claves españolas pero mostrar en inglés
            return ordenES.filter(d => dias[d] === true || dias[d] === '1' || dias[d] === 1).map(d => mapaES_EN[d]).join(' · ');
        }
    }

    // Español
    const mapa  = { lunes:'Lun', martes:'Mar', miercoles:'Mié', jueves:'Jue', viernes:'Vie', sabado:'Sáb', domingo:'Dom' };
    const orden = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
    if (Array.isArray(dias)) {
        return orden.filter(d => dias.map(x => x.toLowerCase()).includes(d)).map(d => mapa[d]).join(' · ');
    }
    if (typeof dias === 'object') {
        return orden.filter(d => dias[d] === true || dias[d] === '1' || dias[d] === 1).map(d => mapa[d]).join(' · ');
    }
    return String(dias);
}
   

function escalarMatch(matchReal) {
    let v;
    if      (matchReal >= 80) v = 90 + ((matchReal-80)/20)*8;
    else if (matchReal >= 60) v = 78 + ((matchReal-60)/20)*12;
    else if (matchReal >= 40) v = 65 + ((matchReal-40)/20)*13;
    else                      v = 55 + (matchReal/40)*10;
    return Math.min(98, Math.max(55, Math.round(v)));
}

function estrellasInline(n) {
    const llenas = Math.floor(parseFloat(n) || 0);
    let s = '';
    for (let i=0;i<llenas;i++) s += '<i class="fas fa-star s"></i>';
    for (let i=0;i<5-llenas;i++) s += '<i class="fas fa-star se"></i>';
    return `<span class="stars-inline">${s}</span>`;
}

function ratingBadge(punt) {
    const n   = parseFloat(punt) || 0;
    const cls = n >= 4.5 ? 'excelente' : n >= 3.5 ? 'bueno' : 'regular';
    const ico = n >= 4.5 ? 'fa-award' : 'fa-star';
    return `<span class="rc-rating-badge ${cls}"><i class="fas ${ico}"></i> ${n.toFixed(1)}/5</span>`;
}

function mensajeIA(categoria, subcategoria, complemento) {
    const cat = (categoria   ||'').toLowerCase();
    const sub = (subcategoria||'').toLowerCase();
    const sufijo = complemento ? '_c' : '';
    const claves = ['trekking','montanismo','downhill','cultura','vida_nocturna','gastronomia','iconos_urbanos','festividades','miradores'];
    for (const k of claves) {
        if (sub.includes(k.replace('_',' ')) || sub.includes(k) || cat.includes(k.replace('_',' ')) || cat.includes(k)) {
            return T['ia_' + k + sufijo] || T['ia_default' + sufijo];
        }
    }
    return T['ia_default' + sufijo];
}

// ════════════════════════════════════════════════════════
//  RENDER CARD
// ════════════════════════════════════════════════════════

// Valores internos de la BD (aventura_naturaleza, solo, vida_nocturna…) → texto en el idioma activo
const VALORES_I18N = <?= Lang::valoresJs() ?>;
function tv(v) {
    if (v == null || v === '') return v;
    const lista = Array.isArray(v) ? v : String(v).split(/\s*[·,]\s*/);
    return lista.filter(Boolean).map(x => {
        const k = String(x).trim().toLowerCase();
        return VALORES_I18N[k] || k.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }).join(' · ');
}

function renderCard(atr) {
    const cfg      = obtenerCfg(atr.categoria, atr.subcategoria);
    const nombre   = atr.nombre || T.card_lugar;
    const desc     = atr.descripcion || T.card_desc;
    const ciudad   = atr.ciudad || T.card_ciudad;
    const pais     = atr.pais   || T.card_pais;
    const punt     = parseFloat(atr.puntuacion_promedio || 4.5).toFixed(1);
    const catLabel = atr.categoria_label || (atr.categoria || T.card_lugar).replace(/_/g,' ');
    const subLabel = atr.subcategoria || '';

    const matchReal  = ESTADO.matchMap[atr.id] ?? null;
    const tieneMatch = matchReal !== null;
    const coinc      = ESTADO.coincidenciasMap[atr.id] ?? {};

    let badgePrincipal = '';
    if (tieneMatch) {
        const mv = escalarMatch(matchReal);
        let bg = mv >= 85 ? '#16a34a' : mv >= 70 ? '#d97706' : '#ef4444';
        badgePrincipal = `<span class="rc-badge" style="background:${bg};"><i class="fas fa-fire"></i> ${mv}% match</span>`;
    } else {
        badgePrincipal = ratingBadge(punt);
    }

    // Coincidencias con tu perfil (si el modelo te la recomienda); si no, el perfil de la atracción
    const perfil        = atr.perfil || {};
    const motivoTexto   = tv(coinc.motivo_viaje || perfil.motivo_viaje) || null;
    const companiaTexto = tv(coinc.compania || perfil.compania) || null;
    const expTexto      = tv(coinc.experiencias || perfil.experiencias) || null;

    const tieneCoincidencias = !!(motivoTexto || companiaTexto || expTexto);

    const motivoLine      = `<div class="rc-detail-line"><i class="fas fa-suitcase"></i><strong>${T.card_motivo}</strong> ${motivoTexto || T.card_motivo_nd}</div>`;
    const companiaLine    = `<div class="rc-detail-line"><i class="fas fa-users"></i><strong>${T.card_compania}</strong> ${companiaTexto || T.card_compania_nd}</div>`;
    const experienciasLine= `<div class="rc-detail-line"><i class="fas fa-star"></i><strong>${T.card_exp}</strong> ${expTexto || T.card_exp_nd}</div>`;

    const iaBlock = `<div class="rc-ia-tip ${tieneCoincidencias ? 'rc-ia-tip--complemento' : ''}">
        <i class="fas fa-robot"></i><span>${mensajeIA(atr.categoria, atr.subcategoria, tieneCoincidencias)}</span>
    </div>`;

    const esFestiv  = atr.categoria === 'festividades';
    const mesIni    = atr.mes_inicio ?? null;
    const mesFin    = atr.mes_fin   ?? null;
    const esEsteMes = esFestiv && mesIni && mesFin && MES_ACTUAL >= mesIni && MES_ACTUAL <= mesFin;
    const badgeEsteMes = esEsteMes ? `<span class="badge-este-mes"><i class="fas fa-fire"></i> ${T.card_este_mes}</span>` : '';
    const pillsMeses   = (esFestiv && mesIni && mesFin)
        ? `<span class="pill-mes" style="${esEsteMes?'background:rgba(185,28,123,0.5);border-color:#b91c7b;':''}">
            <i class="fas fa-calendar-day" style="font-size:.6rem;"></i>
            ${MESES_NOMBRES[mesIni]}${mesIni!==mesFin?' – '+MESES_NOMBRES[mesFin]:''}
        </span>` : '';

    const hA = formatHora(atr.horario_apertura);
    const hC = formatHora(atr.horario_cierre);
    const horarioRow = (hA && hC) ? `<div class="rc-info-row"><i class="fas fa-clock"></i><span>${hA} – ${hC}</span></div>` : '';
    const diasFmt    = formatDias(atr.dias_operacion ? (typeof atr.dias_operacion==='string' ? JSON.parse(atr.dias_operacion) : atr.dias_operacion) : null);
    const diasRow    = diasFmt ? `<div class="rc-info-row"><i class="fas fa-calendar-week"></i><span>${diasFmt}</span></div>` : '';

    let imgStyle = `background:linear-gradient(135deg,${cfg.color}55,${cfg.color}22);`;
    try {
        const ub = typeof atr.ubicacion==='string' ? JSON.parse(atr.ubicacion) : atr.ubicacion;
        if (ub?.rutas?.[0]) imgStyle = `background-image:url('${ub.rutas[0]}');background-size:cover;background-position:center;`;
    } catch(e) {}

    return `
    <div class="rc-card">
        <div class="rc-header" style="background:linear-gradient(135deg,${cfg.color},${cfg.color}cc);">
            <span class="rc-cat"><i class="fas ${cfg.icono}"></i> ${catLabel}</span>
            <span style="display:flex;align-items:center;gap:5px;">
                ${badgeEsteMes}
                <span class="rc-cat-badge">${subLabel}</span>
            </span>
        </div>
        <div class="rc-img-wrap" style="${imgStyle}">
            <div class="rc-img-overlay">
                <div class="rc-overlay-title">
                    <span>${nombre}</span>
                    ${badgePrincipal}
                </div>
                <div class="rc-desc">${desc}</div>
                <div class="rc-info-row"><i class="fas fa-map-marker-alt"></i><span>${ciudad}, ${pais}</span></div>
                <div class="rc-info-row">${estrellasInline(punt)}<span class="rc-rating-num">${punt}/5</span></div>
                ${horarioRow}${diasRow}
                <div class="rc-details">
                    ${motivoLine}${companiaLine}${experienciasLine}${iaBlock}
                </div>
                <div class="rc-btn-overlay">
                    <button class="rc-btn" onclick="verDetalle(${atr.id})">
                        ${T.card_detalle} <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>`;
}


// ── Filtros avanzados ─────────────────────────────────────────────────────
const DIAS_SEMANA = ['domingo','lunes','martes','miercoles','jueves','viernes','sabado'];
function horaANum(h) {             // "18:30:00" → 18.5
    if (!h) return null;
    const p = String(h).split(':'); return parseInt(p[0], 10) + (parseInt(p[1] || 0, 10) / 60);
}
// Tramo abierto [ini, fin] en horas; si cierra pasada la medianoche, fin > 24
function tramoAbierto(a) {
    const ini = horaANum(a.horario_apertura), fin0 = horaANum(a.horario_cierre);
    if (ini === null || fin0 === null) return null;
    let fin = fin0 <= ini ? fin0 + 24 : fin0;
    return [ini, fin];
}
function abreEnDia(a, dia) {
    let d = a.dias_operacion;
    if (typeof d === 'string') { try { d = JSON.parse(d); } catch (e) { d = null; } }
    if (!d || typeof d !== 'object') return true;           // sin datos: no se descarta
    return d[dia] === true;
}
function cumpleHorario(a, filtro) {
    const t = tramoAbierto(a);
    if (!t) return true;
    const [ini, fin] = t;
    const solapa = (x, y) => ini < y && fin > x;
    if (filtro === 'manana') return solapa(6, 12);
    if (filtro === 'tarde')  return solapa(12, 19);
    if (filtro === 'noche')  return solapa(19, 30);
    if (filtro === 'ahora') {
        const ahora = new Date(new Date().toLocaleString('en-US', { timeZone: 'America/La_Paz' }));
        let h = ahora.getHours() + ahora.getMinutes() / 60;
        if (!abreEnDia(a, DIAS_SEMANA[ahora.getDay()])) return false;
        return (h >= ini && h < fin) || (h + 24 >= ini && h + 24 < fin);
    }
    return true;
}
function cumpleDuracion(a, filtro) {
    const h = a.perfil?.duracion_horas;
    if (h === null || h === undefined) return true;
    if (filtro === 'corta')  return h <= 2;
    if (filtro === 'media')  return h > 2 && h <= 5;
    if (filtro === 'dia')    return h > 5 && h <= 12;
    if (filtro === 'varios') return h > 12;
    return true;
}
function aplicarFiltrosAvanzados(lista) {
    const f = ESTADO.filtros, p = a => a.perfil || {};
    if (f.motivo)     lista = lista.filter(a => (p(a).motivo_viaje || []).includes(f.motivo));
    if (f.compania)   lista = lista.filter(a => (p(a).compania || []).includes(f.compania));
    if (f.dificultad) lista = lista.filter(a => p(a).dificultad === f.dificultad);
    if (f.dia) {
        const dia = f.dia === 'hoy'
            ? DIAS_SEMANA[new Date(new Date().toLocaleString('en-US', { timeZone: 'America/La_Paz' })).getDay()]
            : f.dia;
        lista = lista.filter(a => abreEnDia(a, dia));
    }
    if (f.horario)  lista = lista.filter(a => cumpleHorario(a, f.horario));
    if (f.duracion) lista = lista.filter(a => cumpleDuracion(a, f.duracion));
    return lista;
}

// ════════════════════════════════════════════════════════
//  FILTRADO + ORDENAMIENTO
// ════════════════════════════════════════════════════════
function aplicarFiltros() {
    const busq = ESTADO.busqueda.toLowerCase().trim();
    const cat  = ESTADO.categoria;
    const mes  = ESTADO.mes;
    let lista  = [...ESTADO.todas];

    if (cat !== 'todos') {
        lista = lista.filter(a => {
            const bd = (a.categoria||'').toString().trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g,"");
            const fi = cat.toString().trim().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g,"");
            return bd === fi;
        });
    }

    if (cat === 'festividades') {
        if (mes === 0) lista = lista.filter(a => a.categoria === 'festividades');
        else lista = lista.filter(a => a.mes_inicio && a.mes_fin && mes >= a.mes_inicio && mes <= a.mes_fin);
    }

    if (busq) {
        lista = lista.filter(a =>
            (a.nombre||'').toLowerCase().includes(busq) ||
            (a.descripcion||'').toLowerCase().includes(busq) ||
            (a.subcategoria||'').toLowerCase().includes(busq)
        );
    }

    lista = aplicarFiltrosAvanzados(lista);

    if (cat === 'festividades' && ESTADO.orden === 'puntuacion_desc') {
        lista.sort((a,b) => { const mA=a.mes_inicio??99, mB=b.mes_inicio??99; return mA!==mB ? mA-mB : (a.id||0)-(b.id||0); });
    } else {
        switch (ESTADO.orden) {
            case 'nombre_asc':      lista.sort((a,b) => (a.nombre||'').localeCompare(b.nombre||'')); break;
            case 'nombre_desc':     lista.sort((a,b) => (b.nombre||'').localeCompare(a.nombre||'')); break;
            case 'puntuacion_desc': lista.sort((a,b) => parseFloat(b.puntuacion_promedio||0)-parseFloat(a.puntuacion_promedio||0)); break;
            case 'id_asc':          lista.sort((a,b) => (a.id||0)-(b.id||0)); break;
        }
    }

    ESTADO.filtradas = lista; ESTADO.totalItems = lista.length; ESTADO.pagina = 1;
    renderPagina();
}

// ════════════════════════════════════════════════════════
//  RENDER PÁGINA
// ════════════════════════════════════════════════════════
function renderPagina() {
    const inicio = (ESTADO.pagina-1)*ESTADO.porPagina;
    const fin    = inicio + ESTADO.porPagina;
    const items  = ESTADO.filtradas.slice(inicio, fin);

    if (items.length === 0) {
        $('#buscarGrid').html(`<div class="estado-vacio">
            <div class="no-results-icon"><i class="fas fa-search"></i></div>
            <p style="font-size:1.1rem;margin-bottom:6px;">${T.sin_res_t}</p>
            <p style="font-size:0.85rem;">${T.sin_res_s}</p>
        </div>`);
    } else {
        $('#buscarGrid').html(items.map(renderCard).join(''));
    }

    $('#txtMostrando').text(items.length > 0 ? `${inicio+1}–${Math.min(fin, ESTADO.totalItems)}` : '0');
    $('#txtTotal').text(ESTADO.totalItems);
    renderPaginacion();
}

function renderPaginacion() {
    const paginas = Math.ceil(ESTADO.totalItems / ESTADO.porPagina);
    const actual  = ESTADO.pagina;
    if (paginas <= 1) { $('#paginacionWrap').html(''); return; }

    let html = `<button class="pag-btn pag-btn-wide" id="pag-prev" ${actual===1?'disabled':''}><i class="fas fa-chevron-left"></i></button>`;
    const ventana = 2;
    for (let i=1; i<=paginas; i++) {
        if (i===1||i===paginas||(i>=actual-ventana&&i<=actual+ventana))
            html += `<button class="pag-btn ${i===actual?'active':''}" data-pag="${i}">${i}</button>`;
        else if (i===actual-ventana-1||i===actual+ventana+1)
            html += `<span class="pag-info">…</span>`;
    }
    html += `<button class="pag-btn pag-btn-wide" id="pag-next" ${actual===paginas?'disabled':''}><i class="fas fa-chevron-right"></i></button>`;
    $('#paginacionWrap').html(html);

    $('#pag-prev').on('click', ()=>{ if(ESTADO.pagina>1){ESTADO.pagina--;renderPagina();scrollTop();} });
    $('#pag-next').on('click', ()=>{ if(ESTADO.pagina<paginas){ESTADO.pagina++;renderPagina();scrollTop();} });
    $('[data-pag]').on('click', function(){ ESTADO.pagina=parseInt($(this).data('pag'));renderPagina();scrollTop(); });
}

function scrollTop() { window.scrollTo({ top: document.querySelector('.buscar-hero').offsetHeight - 60, behavior:'smooth' }); }

// ════════════════════════════════════════════════════════
//  CARGAR DATOS
// ════════════════════════════════════════════════════════
function cargarTodasAtracciones() {
    $('#buscarGrid').html(`<div class="estado-loading"><div class="spinner-border"></div><p>${T.cargando}</p></div>`);

    const promesaCatalogo = $.ajax({ url:'index.php?c=buscar&a=ObtenerTodas', type:'GET', dataType:'json' });
    const promesaMatch    = $.ajax({ url:'index.php?c=recomendaciones&a=ObtenerModeloMR', type:'GET', dataType:'json' });

    $.when(promesaCatalogo, promesaMatch)
        .done(function(resCatalogo, resMatch) {
            const respCat   = resCatalogo[0];
            const respMatch = resMatch[0];

            ESTADO.matchMap = {}; ESTADO.coincidenciasMap = {};
            if (respMatch?.success && respMatch.recomendaciones?.length > 0) {
                respMatch.recomendaciones.forEach(rec => {
                    if (rec.output) {
                        ESTADO.matchMap[rec.output]         = rec.porcentaje_match ?? 0;
                        ESTADO.coincidenciasMap[rec.output] = rec.coincidencias    ?? {};
                    }
                });
            }

            if (respCat?.success && respCat.atracciones?.length > 0) {
                ESTADO.todas = respCat.atracciones;
                aplicarFiltros();
            } else {
                $('#buscarGrid').html(`<div class="estado-vacio"><p>${respCat?.message || T.sin_res_t}</p></div>`);
            }
        })
        .fail(function() {
            promesaCatalogo.done(function(resp) {
                if (resp?.success && resp.atracciones?.length > 0) { ESTADO.todas = resp.atracciones; aplicarFiltros(); }
            }).fail(function() {
                $('#buscarGrid').html(`<div class="estado-vacio"><i class="fas fa-wifi no-results-icon"></i>
                    <p>${T.error_conexion}</p>
                    <button class="rc-btn" style="margin-top:16px;" onclick="cargarTodasAtracciones()">
                        <i class="fas fa-redo"></i> ${T.reintentar}</button></div>`);
            });
        });
}

// ════════════════════════════════════════════════════════
//  VER DETALLE
// ════════════════════════════════════════════════════════
function verDetalle(id) {
    const elAnt = document.getElementById('modalDetalleAtraccion');
    if (elAnt) { const i = bootstrap.Modal.getInstance(elAnt); if(i) i.dispose(); elAnt.remove(); }
    $('#contenedorModalDetalle').html(`
        <div class="modal fade" id="modalDetalleAtraccion" data-bs-backdrop="true" data-bs-keyboard="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content p-5 text-center" style="border-radius:16px">
                    <div class="spinner-border mx-auto mb-3" style="color:#2980b9;width:2.5rem;height:2.5rem"></div>
                    <p class="text-muted mb-0">${T.modal_cargando}</p>
                </div>
            </div>
        </div>`);
    const sp = new bootstrap.Modal(document.getElementById('modalDetalleAtraccion'), {backdrop:true,keyboard:true});
    $.ajax({
        url: 'index.php?c=atracciones&a=detalle&id='+id, type:'GET',
        success: function(html) { sp.hide(); $('#contenedorModalDetalle').html(html); new bootstrap.Modal(document.getElementById('modalDetalleAtraccion')).show(); },
        error: function(xhr) {
            sp.hide();
            const msg = xhr.status===401 ? T.modal_sesion : T.modal_error;
            $('#contenedorModalDetalle').html(`
                <div class="modal fade" id="modalDetalleAtraccion" data-bs-backdrop="true">
                    <div class="modal-dialog"><div class="modal-content p-4">
                        <div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle"></i> ${msg}</div>
                        <div class="text-end mt-3"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">${T.modal_cerrar}</button></div>
                    </div></div>
                </div>`);
            new bootstrap.Modal(document.getElementById('modalDetalleAtraccion')).show();
        }
    });
}

// ════════════════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════════════════
$(document).ready(function() {
    toastr.options = { closeButton:true, progressBar:true, positionClass:'toast-top-right', timeOut:'3000' };

    $(`.mes-btn[data-mes="${MES_ACTUAL}"]`).append('<span class="mes-now-dot"></span>');

    // Trekking, montañismo y downhill: antes de mostrarlos se pide aceptar el aviso de riesgo (una vez por categoría)
    $('.filtro-btn').on('click', function() {
        const btn = this, cat = $(btn).data('cat');
        if (!CATS_RIESGO.includes(cat) || RIESGOS_OK.includes(cat)) return seleccionarCategoria(btn);
        $.get('index.php?c=legal&a=ModalRiesgo&cat=' + encodeURIComponent(cat), function (html) {
            if (!$.trim(html)) { RIESGOS_OK.push(cat); return seleccionarCategoria(btn); }
            $('#modalAvisoRiesgo').remove();
            $('body').append(html);
            const ok = e => { if (e.detail.categoria !== cat) return; RIESGOS_OK.push(cat); seleccionarCategoria(btn); fin(); };
            const fin = () => { document.removeEventListener('etreva:riesgo-aceptado', ok); document.removeEventListener('etreva:riesgo-cancelado', fin); };
            document.addEventListener('etreva:riesgo-aceptado', ok);
            document.addEventListener('etreva:riesgo-cancelado', fin);
            new bootstrap.Modal(document.getElementById('modalAvisoRiesgo')).show();
        });
    });

    function seleccionarCategoria(btn) {
        $('.filtro-btn').removeClass('active');
        $(btn).addClass('active');
        ESTADO.categoria = $(btn).data('cat');
        ESTADO.mes = 0;
        if (ESTADO.categoria === 'festividades') {
            ESTADO.mes = MES_ACTUAL;
            $('.mes-btn').removeClass('active');
            $(`.mes-btn[data-mes="${MES_ACTUAL}"]`).addClass('active');
            $('#mesesWrap').slideDown(200);
        } else {
            $('#mesesWrap').slideUp(150);
        }
        aplicarFiltros();
    }

    // Filtros avanzados
    const SEL_FILTROS = { fMotivo:'motivo', fCompania:'compania', fDia:'dia', fHorario:'horario', fDuracion:'duracion', fDificultad:'dificultad' };
    function sincronizarFiltros() {
        let activos = 0;
        Object.entries(SEL_FILTROS).forEach(([id, k]) => {
            const el = document.getElementById(id);
            ESTADO.filtros[k] = el.value;
            el.classList.toggle('activo', !!el.value);
            if (el.value) activos++;
        });
        document.getElementById('fLimpiar').hidden = activos === 0;
        aplicarFiltros();
    }
    Object.keys(SEL_FILTROS).forEach(id => document.getElementById(id).addEventListener('change', sincronizarFiltros));
    $('#fLimpiar').on('click', function() {
        Object.keys(SEL_FILTROS).forEach(id => { document.getElementById(id).value = ''; });
        sincronizarFiltros();
    });

    $('.mes-btn').on('click', function() {
        $('.mes-btn').removeClass('active');
        $(this).addClass('active');
        ESTADO.mes = parseInt($(this).data('mes'));
        aplicarFiltros();
    });

    let timerSearch;
    $('#searchInput').on('input', function() {
        const val = $(this).val();
        $('#searchClear').toggle(val.length > 0);
        clearTimeout(timerSearch);
        timerSearch = setTimeout(() => { ESTADO.busqueda = val; aplicarFiltros(); }, 300);
    });

    $('#searchClear').on('click', function() { $('#searchInput').val(''); $(this).hide(); ESTADO.busqueda = ''; aplicarFiltros(); });
    $('#searchBtn').on('click', function() { ESTADO.busqueda = $('#searchInput').val(); aplicarFiltros(); });
    $('#searchInput').on('keypress', function(e) { if(e.which===13){ ESTADO.busqueda=$(this).val(); aplicarFiltros(); } });
    $('#sortSelect').on('change', function() { ESTADO.orden = $(this).val(); aplicarFiltros(); });

    EtrevaAuth.verificarSesionInicial(function(autenticado) {
        if (autenticado) cargarTodasAtracciones();
    });
});
</script>

</body>
</html>