<?php
include 'view/menu.php';
include 'modal/modal_guardar_itinerario.php';

// ── Textos para el clima (traducidos) ─────────────────────
$iconos_clima = [
    0  => ['emoji' => '☀️', 'texto' => Lang::get('inicio.clima_despejado')],
    1  => ['emoji' => '🌤', 'texto' => Lang::get('inicio.clima_mayormente_despejado')],
    2  => ['emoji' => '⛅', 'texto' => Lang::get('inicio.clima_parcialmente_nublado')],
    3  => ['emoji' => '☁️', 'texto' => Lang::get('inicio.clima_nublado')],
    45 => ['emoji' => '🌫', 'texto' => Lang::get('inicio.clima_neblina')],
    51 => ['emoji' => '🌦', 'texto' => Lang::get('inicio.clima_llovizna_leve')],
    61 => ['emoji' => '🌧', 'texto' => Lang::get('inicio.clima_lluvia_leve')],
    63 => ['emoji' => '🌧', 'texto' => Lang::get('inicio.clima_lluvia_moderada')],
    80 => ['emoji' => '🌦', 'texto' => Lang::get('inicio.clima_chubascos')],
    95 => ['emoji' => '⛈', 'texto' => Lang::get('inicio.clima_tormenta')],
];
$clima_default = ['emoji' => '🌡', 'texto' => Lang::get('inicio.clima_variable')];

// ── Textos varios para el clima ───────────────────────────
$t_clima = [
    'actualizado'  => Lang::get('inicio.clima_actualizado'),
    'sin_conexion' => Lang::get('inicio.clima_sin_conexion'),
    'viento'       => Lang::get('inicio.clima_viento'),
    'max'          => Lang::get('inicio.clima_max'),
    'min'          => Lang::get('inicio.clima_min'),
    'lluvia'       => Lang::get('inicio.clima_lluvia'),
];

// ── Meses traducidos ──────────────────────────────────────
$meses_traducidos = [];
for ($i = 1; $i <= 12; $i++) {
    $meses_traducidos[$i] = Lang::get("meses.$i");
}

// ── Frases del día traducidas ─────────────────────────────
$frases = [
    Lang::get('inicio.frase_1'),
    Lang::get('inicio.frase_2'),
    Lang::get('inicio.frase_3'),
    Lang::get('inicio.frase_4'),
    Lang::get('inicio.frase_5'),
    Lang::get('inicio.frase_6'),
    Lang::get('inicio.frase_7'),
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Lang::get('inicio.titulo_pagina') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/menu.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        /* ══════════════════════════════════════════
           DEFAULT — oscuro medio (sin clase dark-mode)
        ══════════════════════════════════════════ */
        :root {
            --bg-primary:    #111827;
            --bg-card:       rgba(255,255,255,0.05);
            --bg-card-solid: #1c2333;
            --text-primary:  #e2e8f0;
            --text-muted:    #94a3b8;
            --border-light:  rgba(255,255,255,0.09);
            --accent:        #6366f1;
            --accent2:       #38bdf8;
            --gold:          #facc15;
            --green:         #22c55e;
            --hero-gradient: linear-gradient(135deg, #0f172a 0%, #111827 60%, #0d1525 100%);
            --card-shadow:   0 12px 30px rgba(0,0,0,0.45);
            --overlay-bg:    rgba(17,24,39,0.82);
            --clima-grad:    linear-gradient(135deg, rgba(99,102,241,0.15), rgba(17,24,39,0.7));
            --track-bg:      rgba(255,255,255,0.1);
        }

        html, body {
            background: var(--bg-primary) !important;
            color: var(--text-primary) !important;
            margin: 0; padding: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        main {
            margin-left: 280px;
            min-height: 100vh;
            background: var(--bg-primary);
            transition: margin-left 0.3s ease;
        }
        main.min-main { margin-left: 85px; }

        /* ══════════════════════════════════════════
           DARK MODE — navy completo (buscar/recomendaciones)
        ══════════════════════════════════════════ */
        body.dark-mode {
            --bg-primary:    #0d0d1a;
            --bg-card:       rgba(255,255,255,0.04);
            --bg-card-solid: #1a1a2e;
            --text-primary:  #e2e8f0;
            --text-muted:    rgba(255,255,255,0.45);
            --border-light:  rgba(255,255,255,0.08);
            --hero-gradient: linear-gradient(135deg, #2b3b53 0%, #1c2646 100%);

            --card-shadow:   0 10px 40px rgba(0,0,0,0.5);
            --overlay-bg:    linear-gradient(to bottom, rgba(15,23,42,0.3), rgba(15,23,42,0.92));
            --clima-grad:    linear-gradient(135deg, rgba(102,126,234,0.18), rgba(15,52,96,0.55));
            --track-bg:      rgba(255,255,255,0.08);
        }

        body.dark-mode main { background: var(--bg-primary); }

        /* ══════════════════════════════════════════
           HERO
        ══════════════════════════════════════════ */
        .cliente-hero {
            background: var(--hero-gradient);
            padding: 40px 40px 32px;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--border-light);
        }

        .cliente-hero::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(99,102,241,0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .hero-saludo h1 {
            font-size: 1.9rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 6px;
            letter-spacing: -0.5px;
        }

        .hero-saludo h1 span { color: var(--accent); }

        .hero-saludo p {
            color: var(--text-muted);
            font-size: 0.92rem;
            margin: 0;
        }

        .hero-frase {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 14px 20px;
            max-width: 340px;
            text-align: center;
        }

        .hero-frase i { color: var(--gold); margin-bottom: 6px; display: block; font-size: 1.1rem; }
        .hero-frase p { color: rgba(255,255,255,0.7); font-size: 0.82rem; font-style: italic; margin: 0; line-height: 1.5; }

        /* KPIs */
        .hero-kpis {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            flex-wrap: wrap;
        }

        .hero-kpi {
            background: var(--bg-card);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 160px;
            flex: 1;
        }

        .hero-kpi-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
        }

        .hero-kpi-data h3 {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
            line-height: 1;
        }

        .hero-kpi-data p {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin: 3px 0 0;
        }

        /* ══════════════════════════════════════════
           BODY PRINCIPAL
        ══════════════════════════════════════════ */
        .cliente-body {
            padding: 28px 40px 60px;
            display: flex;
            flex-direction: column;
            gap: 28px;
            background: var(--bg-primary);
        }

        .sec-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sec-title i { color: var(--accent); }
        .sec-title span.muted {
            font-size: 0.75rem;
            font-weight: 400;
            color: var(--text-muted);
            margin-left: 4px;
        }

        /* ══════════════════════════════════════════
           ACCESOS RÁPIDOS
        ══════════════════════════════════════════ */
        .accesos-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
        }

        @media (max-width: 900px) { .accesos-grid { grid-template-columns: repeat(2, 1fr); } }

        .acceso-btn {
            background: var(--bg-card-solid);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 20px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .acceso-btn:hover {
            transform: translateY(-4px);
            border-color: rgba(99,102,241,0.4);
            background: rgba(99,102,241,0.1);
        }

        .acceso-btn i { font-size: 1.6rem; }
        .acceso-btn span { font-size: 0.82rem; font-weight: 600; color: var(--text-primary); }

        /* ══════════════════════════════════════════
           CLIMA
        ══════════════════════════════════════════ */
        .clima-card {
            background: var(--clima-grad);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 22px 28px;
            display: flex;
            align-items: center;
            gap: 28px;
            flex-wrap: wrap;
        }

        .clima-main { display: flex; align-items: center; gap: 16px; }

        .clima-temp {
            font-size: 3rem;
            font-weight: 800;
            color: #fff;
            line-height: 1;
        }
        .clima-temp sup { font-size: 1.2rem; font-weight: 400; }

        .clima-info h4 { font-size: 1rem; font-weight: 700; color: #fff; margin: 0 0 4px; }
        .clima-info p  { font-size: 0.8rem; color: var(--text-muted); margin: 0; }

        .clima-detalles { display: flex; gap: 20px; flex-wrap: wrap; margin-left: auto; }

        .clima-dato { text-align: center; }
        .clima-dato i       { color: var(--accent); font-size: 1.1rem; display: block; margin-bottom: 4px; }
        .clima-dato span    { font-size: 0.82rem; color: var(--text-muted); display: block; }
        .clima-dato strong  { font-size: 0.95rem; color: #fff; display: block; }

        /* ══════════════════════════════════════════
           DOS COLUMNAS
        ══════════════════════════════════════════ */
        .dos-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 1100px) { .dos-col { grid-template-columns: 1fr; } }

        /* ══════════════════════════════════════════
           RECOMENDACIÓN DEL DÍA
        ══════════════════════════════════════════ */
        .rec-dia-card {
            background: var(--bg-card-solid);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            min-height: 260px;
            display: flex;
            flex-direction: column;
            box-shadow: var(--card-shadow);
        }

        .rec-dia-img {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0.3;
        }

        .rec-dia-content {
            position: relative;
            padding: 24px;
            display: flex;
            flex-direction: column;
            height: 100%;
            flex: 1;
            /* NUNCA blanco — siempre oscuro */
            background: linear-gradient(to bottom, rgba(15,23,42,0.25), rgba(15,23,42,0.85));
        }

        .rec-dia-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 50px;
            margin-bottom: 12px;
            width: fit-content;
        }

        .rec-dia-nombre {
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 8px;
        }

        .rec-dia-desc {
            font-size: 0.83rem;
            color: rgba(255,255,255,0.65);
            margin: 0 0 16px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .rec-dia-meta {
            display: flex;
            gap: 14px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .rec-dia-meta span {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.55);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .rec-dia-meta i { color: var(--accent); }

        .rec-dia-btn {
            margin-top: auto;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            width: fit-content;
        }
        .rec-dia-btn:hover { background: #4f46e5; transform: translateY(-2px); }

        /* ══════════════════════════════════════════
           SKELETON — siempre oscuro
        ══════════════════════════════════════════ */
        .skeleton {
            background: linear-gradient(
                90deg,
                rgba(255,255,255,0.05) 0%,
                rgba(255,255,255,0.1) 50%,
                rgba(255,255,255,0.05) 100%
            );
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ══════════════════════════════════════════
           ESTE MES
        ══════════════════════════════════════════ */
        .este-mes-wrap { display: flex; flex-direction: column; gap: 10px; }

        .festividad-item {
            background: var(--bg-card-solid);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .festividad-item:hover {
            background: rgba(185,28,123,0.08);
            border-color: rgba(185,28,123,0.3);
            transform: translateX(4px);
        }

        .festividad-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #b91c7b;
            flex-shrink: 0;
            box-shadow: 0 0 8px rgba(185,28,123,0.5);
        }

        .festividad-info h5 { font-size: 0.88rem; font-weight: 700; color: var(--text-primary); margin: 0 0 2px; }
        .festividad-info p  { font-size: 0.75rem; color: var(--text-muted); margin: 0; }

        .festividad-badge {
            margin-left: auto;
            background: rgba(185,28,123,0.15);
            border: 1px solid rgba(185,28,123,0.35);
            color: #f0abcb;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 50px;
            white-space: nowrap;
        }

        /* ══════════════════════════════════════════
           ITINERARIOS
        ══════════════════════════════════════════ */
        .itinerarios-grid { display: flex; flex-direction: column; gap: 10px; }

        .itinerario-item {
            background: var(--bg-card-solid);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .itinerario-item:hover {
            background: rgba(99,102,241,0.08);
            border-color: rgba(99,102,241,0.3);
            transform: translateX(4px);
        }

        .itin-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .itin-info h5 { font-size: 0.88rem; font-weight: 700; color: var(--text-primary); margin: 0 0 3px; }
        .itin-info p  { font-size: 0.75rem; color: var(--text-muted); margin: 0; }

        .itin-meta { margin-left: auto; text-align: right; }
        .itin-meta span   { font-size: 0.72rem; color: var(--text-muted); display: block; }
        .itin-meta strong { font-size: 0.8rem; color: var(--accent); }

        /* ══════════════════════════════════════════
           CATEGORÍAS CON BARRAS
        ══════════════════════════════════════════ */
        .categorias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
        }

        .cat-bar-item {
            background: var(--bg-card-solid);
            border: 1px solid var(--border-light);
            border-radius: 14px;
            padding: 14px 16px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .cat-bar-item:hover {
            transform: translateY(-3px);
            border-color: rgba(99,102,241,0.3);
        }

        .cat-bar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .cat-bar-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .cat-bar-count { font-size: 1.1rem; font-weight: 800; color: var(--text-primary); }

        .cat-bar-track {
            height: 6px;
            background: var(--track-bg);
            border-radius: 50px;
            overflow: hidden;
        }

        .cat-bar-fill {
            height: 100%;
            border-radius: 50px;
            transition: width 1s ease;
        }

        /* ══════════════════════════════════════════
           PROGRESO
        ══════════════════════════════════════════ */
        .progreso-card {
            background: var(--bg-card-solid);
            border: 1px solid var(--border-light);
            border-radius: 20px;
            padding: 24px 28px;
            box-shadow: var(--card-shadow);
        }

        .progreso-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .progreso-nums { font-size: 0.85rem; color: var(--text-muted); }
        .progreso-nums strong { color: var(--text-primary); font-size: 1.1rem; }

        .progreso-track {
            height: 10px;
            background: var(--track-bg);
            border-radius: 50px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .progreso-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent), #a78bfa);
            border-radius: 50px;
            transition: width 1.5s ease;
            position: relative;
        }

        .progreso-fill::after {
            content: '';
            position: absolute;
            right: 0; top: 0;
            width: 14px; height: 100%;
            background: rgba(255,255,255,0.35);
            border-radius: 50px;
        }

        .badges-wrap { display: flex; gap: 12px; flex-wrap: wrap; }

        .badge-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .badge-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            transition: transform 0.2s;
        }

        .badge-icon:hover { transform: scale(1.1); }

        .badge-icon.bloqueado {
            background: rgba(255,255,255,0.04) !important;
            filter: grayscale(1);
            opacity: 0.35;
        }

        .badge-label {
            font-size: 0.65rem;
            color: var(--text-muted);
            text-align: center;
            max-width: 56px;
            line-height: 1.3;
        }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        @media (max-width: 768px) {
            main, main.min-main { margin-left: 0; }
            .cliente-hero  { padding: 24px 20px; }
            .cliente-body  { padding: 20px 16px 40px; }
            .hero-frase   { display: none; }
            .accesos-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>

<body>
<main>

    <!-- ══════════════════════════════════ HERO ══════════════════════════════════ -->
    <div class="cliente-hero">
        <div class="hero-top">
            <div class="hero-saludo">
                <h1><?= str_replace('{name}', '<span id="heroNombre">' . Lang::get('inicio.cargando') . '</span>', Lang::get('inicio.hero_saludo')) ?></h1>
                <p id="heroFecha"><?= str_replace('{fecha}', Lang::get('inicio.cargando'), Lang::get('inicio.hero_fecha')) ?></p>
            </div>
            <div class="hero-frase">
                <i class="fas fa-quote-left"></i>
                <p id="fraseDelDia"></p>
            </div>
        </div>

        <div class="hero-kpis">
            <div class="hero-kpi">
                <div class="hero-kpi-icon" style="background:rgba(99,102,241,0.2);">
                    <i class="fas fa-map-marked-alt" style="color:#6366f1;"></i>
                </div>
                <div class="hero-kpi-data">
                    <h3 id="kpiTotal">—</h3>
                    <p><?= Lang::get('inicio.kpi_atracciones') ?></p>
                </div>
            </div>
            <div class="hero-kpi">
                <div class="hero-kpi-icon" style="background:rgba(245,158,11,0.15);">
                    <i class="fas fa-robot" style="color:#f59e0b;"></i>
                </div>
                <div class="hero-kpi-data">
                    <h3 id="kpiMatch">—</h3>
                    <p><?= Lang::get('inicio.kpi_recomendaciones') ?></p>
                </div>
            </div>
            <div class="hero-kpi">
                <div class="hero-kpi-icon" style="background:rgba(34,197,94,0.15);">
                    <i class="fas fa-route" style="color:#22c55e;"></i>
                </div>
                <div class="hero-kpi-data">
                    <h3 id="kpiItinerarios">—</h3>
                    <p><?= Lang::get('inicio.kpi_itinerarios') ?></p>
                </div>
            </div>
            <div class="hero-kpi">
                <div class="hero-kpi-icon" style="background:rgba(185,28,123,0.15);">
                    <i class="fas fa-calendar-star" style="color:#e879ab;"></i>
                </div>
                <div class="hero-kpi-data">
                    <h3 id="kpiFestividades">—</h3>
                    <p><?= Lang::get('inicio.kpi_festividades') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════ BODY ══════════════════════════════════ -->
    <div class="cliente-body">

        <!-- ACCESOS RÁPIDOS -->
        <div>
            <div class="sec-title"><i class="fas fa-bolt"></i> <?= Lang::get('inicio.acceso_titulo') ?></div>
            <div class="accesos-grid">
                <a class="acceso-btn" href="index.php?c=buscar&a=index">
                    <i class="fas fa-compass" style="color:#6366f1;"></i>
                    <span><?= Lang::get('inicio.acceso_explorar') ?></span>
                </a>
                <a class="acceso-btn" href="index.php?c=recomendaciones&a=index">
                    <i class="fas fa-star" style="color:#f59e0b;"></i>
                    <span><?= Lang::get('inicio.acceso_recomendaciones') ?></span>
                </a>
                <a class="acceso-btn" href="index.php?c=itinerarios&a=index">
                    <i class="fas fa-route" style="color:#22c55e;"></i>
                    <span><?= Lang::get('inicio.acceso_itinerarios') ?></span>
                </a>
                <a class="acceso-btn" href="index.php?c=favoritos&a=index">
                    <i class="fas fa-heart" style="color:#ef4444;"></i>
                    <span><?= Lang::get('inicio.acceso_favoritos') ?></span>
                </a>
            </div>
        </div>
  <!-- CLIMA -->
        <div>
            <div class="sec-title"><i class="fas fa-cloud-sun"></i> <?= Lang::get('inicio.clima_titulo') ?> <span class="muted"><?= Lang::get('inicio.clima_subtitulo') ?></span></div>
            <div class="clima-card" id="climaCard">
                <div class="clima-main">
                    <div class="clima-icono skeleton" id="climaIcono" style="width:60px;height:60px;border-radius:12px;"></div>
                    <div>
                        <div class="clima-temp skeleton" id="climaTemp" style="width:100px;height:48px;"></div>
                    </div>
                </div>
                <div class="clima-info">
                    <h4 id="climaCondicion"><?= Lang::get('inicio.cargando') ?></h4>
                    <p><?= Lang::get('inicio.clima_altitud') ?></p>
                    <p id="climaHora" style="margin-top:4px;"></p>
                </div>
                <div class="clima-detalles">
                    <div class="clima-dato">
                        <i class="fas fa-wind"></i>
                        <strong id="climaViento">—</strong>
                          <span><?= Lang::get('inicio.clima_viento') ?></span>
                    </div>
                    <div class="clima-dato">
                        <i class="fas fa-thermometer-half"></i>
                        <strong id="climaMax">—</strong>
                         <span><?= Lang::get('inicio.clima_max') ?></span>
                    </div>
                    <div class="clima-dato">
                        <i class="fas fa-thermometer-empty"></i>
                        <strong id="climaMin">—</strong>
                       <span><?= Lang::get('inicio.clima_min') ?></span>
                    </div>
                    <div class="clima-dato">
                        <i class="fas fa-umbrella"></i>
                        <strong id="climaLluvia">—</strong>
                          <span><?= Lang::get('inicio.clima_lluvia') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- REC DEL DÍA + ESTE MES -->
        <div class="dos-col">

            <div>
                <div class="sec-title"><i class="fas fa-fire"></i> <?= Lang::get('inicio.rec_dia_titulo') ?> <span class="muted"><?= Lang::get('inicio.rec_dia_subtitulo') ?></span></div>
                <div class="rec-dia-card" id="recDiaCard">
                    <div class="rec-dia-img" id="recDiaImg"></div>
                    <div class="rec-dia-content">
                        <div class="rec-dia-badge skeleton" style="width:120px;height:22px;"></div>
                        <div style="font-size:1.4rem;font-weight:800;margin-bottom:10px;" class="skeleton" style="width:200px;height:28px;"></div>
                        <div class="skeleton" style="width:100%;height:36px;"></div>
                    </div>
                </div>
            </div>

            <div>
                <div class="sec-title"><i class="fas fa-calendar-star" style="color:#e879ab;"></i> <?= Lang::get('inicio.este_mes_titulo') ?> <span class="muted" id="mesNombre"></span></div>
                <div class="este-mes-wrap" id="esteMesWrap">
                    <div class="festividad-item skeleton" style="height:64px;"></div>
                    <div class="festividad-item skeleton" style="height:64px;"></div>
                    <div class="festividad-item skeleton" style="height:64px;"></div>
                </div>
            </div>

        </div>

        <!-- ITINERARIOS + PROGRESO -->
        <div class="dos-col">

            <div>
                <div class="sec-title"><i class="fas fa-route"></i> <?= Lang::get('inicio.itinerarios_titulo') ?></div>
                <div class="itinerarios-grid" id="itinerariosGrid">
                    <div class="itinerario-item skeleton" style="height:70px;"></div>
                    <div class="itinerario-item skeleton" style="height:70px;"></div>
                    <div class="itinerario-item skeleton" style="height:70px;"></div>
                </div>
            </div>

            <div>
                <div class="sec-title"><i class="fas fa-trophy"></i> <?= Lang::get('inicio.progreso_titulo') ?></div>
                <div class="progreso-card">
                    <div class="progreso-header">
                        <div>
                            <p style="color:var(--text-muted);font-size:0.82rem;margin:0 0 4px;"><?= Lang::get('inicio.progreso_atracciones') ?></p>
                            <div class="progreso-nums"><strong id="progresoVistas">0</strong> <?= Lang::get('inicio.progreso_de') ?> <strong id="progresoTotal">570</strong></div>
                        </div>
                        <div style="font-size:1.5rem;font-weight:800;color:var(--accent);" id="progresoPct">0%</div>
                    </div>
                    <div class="progreso-track">
                        <div class="progreso-fill" id="progresoFill" style="width:0%;"></div>
                    </div>
                    <div class="badges-wrap" id="badgesWrap"></div>
                </div>
            </div>

        </div>

        <!-- CATEGORÍAS -->
        <div>
            <div class="sec-title"><i class="fas fa-th"></i> <?= Lang::get('inicio.categorias_titulo') ?></div>
            <div class="categorias-grid" id="categoriasGrid"></div>
        </div>

    </div>
</main>

<div id="contenedorModalDetalle"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="assets/js/etreva-auth.js"></script>

<script>
// ── JavaScript ORIGINAL (funcional) ────────────────────────────────────────
// No se toca nada de esto, solo se añadieron las traducciones en el HTML





const MESES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
const MES_ACTUAL = new Date().getMonth() + 1;
const HOY = new Date();

// ── Frases y meses traducidos desde PHP ───────────────────────────────────
const FRASES = <?= json_encode($frases, JSON_UNESCAPED_UNICODE) ?>;
// Textos de la vista generados por JS (antes estaban fijos en español)
const TI = <?= json_encode(['ver_detalles' => Lang::get('inicio.ver_detalles'), 'match_contigo' => Lang::get('inicio.match_contigo'), 'sin_itinerarios' => Lang::get('inicio.sin_itinerarios'), 'error_cargar' => Lang::get('inicio.error_cargar'), 'sin_festividades' => Lang::get('inicio.sin_festividades'), 'festividad' => Lang::get('inicio.festividad'), 'evento_cultural' => Lang::get('inicio.evento_cultural'), 'itinerario_n' => Lang::get('inicio.itinerario_n'), 'destinos' => Lang::get('inicio.destinos'), 'cat_festividades' => Lang::get('inicio.cat_festividades'), 'ins_explorador' => Lang::get('inicio.ins_explorador'), 'ins_aventurero' => Lang::get('inicio.ins_aventurero'), 'ins_nochero' => Lang::get('inicio.ins_nochero'), 'ins_gourmet' => Lang::get('inicio.ins_gourmet'), 'ins_culturista' => Lang::get('inicio.ins_culturista'), 'ins_estrella' => Lang::get('inicio.ins_estrella'),
    'cat' => ['trekking' => Lang::get('categorias.trekking'), 'montanismo' => Lang::get('categorias.montanismo'),
              'downhill' => Lang::get('categorias.downhill'), 'cultura' => Lang::get('categorias.cultura'),
              'vida_nocturna' => Lang::get('categorias.vida_nocturna'), 'gastronomia' => Lang::get('categorias.gastronomia'),
              'iconos_urbanos' => Lang::get('categorias.iconos_urbanos'), 'festividades' => Lang::get('inicio.cat_festividades')]],
    JSON_UNESCAPED_UNICODE) ?>;
const LOCALE = <?= json_encode($_SESSION['lang'] ?? 'es') ?>;
const MESES_TRAD = <?= json_encode($meses_traducidos, JSON_UNESCAPED_UNICODE) ?>;


function initBasico() {
    const diaAno = Math.floor((HOY - new Date(HOY.getFullYear(), 0, 0)) / 86400000);
    $('#fraseDelDia').text(FRASES[diaAno % FRASES.length]);
    
    // Usar el locale de la sesión para formatear la fecha
    const locale = '<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>';
    const fechaStr = HOY.toLocaleDateString(locale, { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    $('#heroFecha').html(`<?= Lang::get('inicio.hero_fecha') ?>`.replace('{fecha}', fechaStr.charAt(0).toUpperCase() + fechaStr.slice(1)));
    
    // Usar meses traducidos
    $('#mesNombre').text(`— ${MESES_TRAD[MES_ACTUAL]}`);
}

// ── CLIMA ──────────────────────────────────────────────────────────────────────


// ── CLIMA ──────────────────────────────────────────────────────────────────────
const T_CLIMA = <?= json_encode($t_clima, JSON_UNESCAPED_UNICODE) ?>;
const ICONOS_CLIMA = <?= json_encode($iconos_clima, JSON_UNESCAPED_UNICODE) ?>;
const CLIMA_DEFAULT = <?= json_encode($clima_default, JSON_UNESCAPED_UNICODE) ?>;


function cargarClima() {
    fetch('https://api.open-meteo.com/v1/forecast?latitude=-16.5&longitude=-68.15&current_weather=true&daily=temperature_2m_max,temperature_2m_min,precipitation_sum&timezone=America%2FLa_Paz&forecast_days=1')
        .then(r => r.json())
        .then(data => {
            const cw   = data.current_weather;
            const info = ICONOS_CLIMA[cw.weathercode] || CLIMA_DEFAULT;
            const hora = new Date().toLocaleTimeString('es-ES', {hour:'2-digit',minute:'2-digit',timeZone:'America/La_Paz'});
            const tMax = data.daily?.temperature_2m_max?.[0] ? Math.round(data.daily.temperature_2m_max[0]) + '°C' : '—';
            const tMin = data.daily?.temperature_2m_min?.[0] ? Math.round(data.daily.temperature_2m_min[0]) + '°C' : '—';
            const ll   = (data.daily?.precipitation_sum?.[0] ?? 0).toFixed(1);
            $('#climaIcono').removeClass('skeleton').html(`<span style="font-size:2.5rem;line-height:1;">${info.emoji}</span>`);
            $('#climaTemp').removeClass('skeleton').html(`${Math.round(cw.temperature)}<sup>°C</sup>`);
            $('#climaCondicion').text(info.texto);
            // Línea traducida:
            $('#climaHora').text(T_CLIMA.actualizado.replace('{hora}', hora));
            $('#climaViento').text(Math.round(cw.windspeed));
            $('#climaMax').text(tMax); $('#climaMin').text(tMin); $('#climaLluvia').text(ll);
        })
        .catch(() => {
            $('#climaIcono').removeClass('skeleton').html('<span style="font-size:2.5rem;">🌡</span>');
            $('#climaTemp').removeClass('skeleton').html('—<sup>°C</sup>');
            // Línea traducida:
           $('#climaCondicion').text(T_CLIMA.sin_conexion);
        });
}

// ── RECOMENDACIÓN DEL DÍA ─────────────────────────────────────────────────────
function cargarRecDia() {
    $.ajax({ url:'index.php?c=recomendaciones&a=ObtenerModeloMR', type:'GET', dataType:'json',
        success: function(resp) {
            if (!resp.success || !resp.recomendaciones?.length) {
                $('#recDiaCard').html('<div style="padding:24px;color:var(--text-muted);text-align:center;">No hay recomendaciones</div>');
                $('#kpiMatch').text('0'); return;
            }

            const mesActual  = new Date().getMonth() + 1;
            const mesesAbrev = {
                1:'ene', 2:'feb', 3:'mar', 4:'abr', 5:'may', 6:'jun',
                7:'jul', 8:'ago', 9:'sep', 10:'oct', 11:'nov', 12:'dic'
            };
            const abrevActual = mesesAbrev[mesActual];

            const filtradas = resp.recomendaciones.filter(rec => {
                const cat = (rec.categoria || '').toLowerCase();
                if (!cat.includes('(')) return true;
                const match = cat.match(/\(([^)]+)\)/);
                if (!match) return true;
                return match[1].includes(abrevActual);
            });

            if (!filtradas.length) {
                $('#recDiaCard').html('<div style="padding:24px;color:var(--text-muted);text-align:center;">No hay recomendaciones para este mes</div>');
                $('#kpiMatch').text('0'); return;
            }

            const recs = filtradas.sort((a,b) => (b.porcentaje_match ?? 0) - (a.porcentaje_match ?? 0));
            $('#kpiMatch').text(recs.length);

            const rec = recs[0];
            let img = '';
            try { const ub = typeof rec.ubicacion === 'string' ? JSON.parse(rec.ubicacion) : rec.ubicacion; if (ub?.rutas?.[0]) img = ub.rutas[0]; } catch(e) {}
            let vv = rec.porcentaje_match ?? 0;
            vv = Math.min(98, Math.round(vv >= 80 ? 90 + ((vv-80)/20)*8 : vv >= 60 ? 78 + ((vv-60)/20)*12 : 65));
            const bc   = vv >= 85 ? '#16a34a' : '#d97706';
            const punt = parseFloat(rec.puntuacion || 4.5).toFixed(1);

            $('#recDiaCard').html(`
                <div class="rec-dia-img" style="${img ? `background-image:url('${img}');background-size:cover;background-position:center;` : `background:linear-gradient(135deg,#6366f144,#6366f122);`}"></div>
                <div class="rec-dia-content">
                    <div class="rec-dia-badge" style="background:${bc};"><i class="fas fa-fire"></i> ${TI.match_contigo.replace('{n}', vv)}</div>
                    <div class="rec-dia-nombre">${rec.nombre || 'Atracción'}</div>
                    <div class="rec-dia-desc">${rec.descripcion || 'Un lugar increíble en La Paz, Bolivia.'}</div>
                    <div class="rec-dia-meta">
                        <span><i class="fas fa-map-marker-alt"></i> ${rec.ciudad || 'La Paz'}, Bolivia</span>
                        <span><i class="fas fa-star" style="color:#f59e0b;"></i> ${punt}/5</span>
                        <span><i class="fas fa-tag"></i> ${rec.categoria || 'Lugar'}</span>
                    </div>
                    <button class="rec-dia-btn" onclick="verDetalle(${rec.output})">${TI.ver_detalles} <i class="fas fa-arrow-right"></i></button>
                </div>`);
        },
        error: () => { $('#recDiaCard').html('<div style="padding:24px;color:var(--text-muted);text-align:center;">' + TI.error_cargar + '</div>'); }
    });
}

// ── FESTIVIDADES + CATEGORÍAS + PROGRESO ──────────────────────────────────────
function cargarFestividades() {
    $.ajax({ url:'index.php?c=buscar&a=ObtenerTodas', type:'GET', dataType:'json',
        success: function(resp) {
            if (!resp.success || !resp.atracciones?.length) return;
            const todas = resp.atracciones;
            $('#kpiTotal').text(todas.length);
            const festiv = todas.filter(a => (a.meses_evento || []).includes(MES_ACTUAL)).slice(0, 5);
            $('#kpiFestividades').text(festiv.length);
            if (!festiv.length) {
                $('#esteMesWrap').html('<div style="color:var(--text-muted);font-size:0.85rem;padding:20px;text-align:center;">' + TI.sin_festividades + '</div>');
            } else {
              $('#esteMesWrap').html(festiv.map(f => `
                <div class="festividad-item" onclick="verDetalle(${f.id})" style="flex-wrap:wrap;gap:10px;cursor:pointer;">
                    <div class="festividad-dot" style="flex-shrink:0;"></div>
                    <div class="festividad-info" style="flex:1;min-width:0;">
                        <h5>${f.nombre || TI.festividad}</h5>
                        <p>${f.descripcion ? f.descripcion.slice(0,60) + '...' : TI.evento_cultural}</p>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                        <span class="festividad-badge">${MESES_TRAD[MES_ACTUAL]}</span>
                        <button onclick="event.stopPropagation(); verDetalle(${f.id})" 
                            style="background:#6366f1;color:#fff;border:none;border-radius:8px;
                                padding:5px 12px;font-size:0.75rem;font-weight:700;cursor:pointer;
                                display:flex;align-items:center;gap:5px;transition:all 0.2s;white-space:nowrap;"
                            onmouseover="this.style.background='#4f46e5'"
                            onmouseout="this.style.background='#6366f1'">
                            ${TI.ver_detalles} <i class="fas fa-arrow-right" style="font-size:0.65rem;"></i>
                        </button>
                    </div>
                </div>`).join(''));
            }
            renderCategorias(todas);
            renderProgreso(Math.floor(todas.length * 0.08), todas.length);
        }
    });
}

// ── CATEGORÍAS ────────────────────────────────────────────────────────────────
const CATS_VISUAL = {
    'trekking':       {icono:'fa-hiking',       color:'#166534'},
    'montanismo':     {icono:'fa-mountain',      color:'#1e3a5f'},
    'downhill':       {icono:'fa-bicycle',       color:'#0e7490'},
    'cultura':        {icono:'fa-landmark',      color:'#92400e'},
    'vida_nocturna':  {icono:'fa-glass-cheers',  color:'#7c3aed'},
    'gastronomia':    {icono:'fa-utensils',      color:'#ea580c'},
    'iconos_urbanos': {icono:'fa-city',          color:'#1e40af'},
    'festividades':   {icono:'fa-calendar-star', color:'#b91c7b'},
};

function renderCategorias(todas) {
    const conteo = {};
    todas.forEach(a => { const c = (a.categoria||'otros').toLowerCase().replace(' ','_'); conteo[c] = (conteo[c]||0)+1; });
    const max = Math.max(...Object.values(conteo));
    $('#categoriasGrid').html(Object.entries(CATS_VISUAL).map(([cat, cfg]) => {
        const count = conteo[cat] || 0;
        const pct   = max > 0 ? Math.round((count/max)*100) : 0;
        const label = TI.cat[cat] || cat.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase());
        return `<div class="cat-bar-item" onclick="window.location='index.php?c=buscar&a=index'">
            <div class="cat-bar-header">
                <div class="cat-bar-label"><i class="fas ${cfg.icono}" style="color:${cfg.color};"></i> ${label}</div>
                <div class="cat-bar-count">${count}</div>
            </div>
            <div class="cat-bar-track"><div class="cat-bar-fill" style="width:0%;background:${cfg.color};" data-pct="${pct}"></div></div>
        </div>`;
    }).join(''));
    setTimeout(() => { document.querySelectorAll('.cat-bar-fill').forEach(el => { el.style.width = el.dataset.pct + '%'; }); }, 300);
}

// ── PROGRESO ──────────────────────────────────────────────────────────────────
const BADGES_DEF = [
    {icono:'🗺️',label:TI.ins_explorador,color:'#667eea33',min:1},
    {icono:'🏔️',label:TI.ins_aventurero,color:'#16a34a33',min:5},
    {icono:'🌙',label:TI.ins_nochero,color:'#7c3aed33',min:10},
    {icono:'🍽️',label:TI.ins_gourmet,color:'#ea580c33',min:15},
    {icono:'🏛️',label:TI.ins_culturista,color:'#92400e33',min:20},
    {icono:'⭐',label:TI.ins_estrella,color:'#f59e0b33',min:30},
];

function renderProgreso(vistas, total) {
    $('#progresoVistas').text(vistas); $('#progresoTotal').text(total);
    const pct = total > 0 ? Math.round((vistas/total)*100) : 0;
    $('#progresoPct').text(pct + '%');
    setTimeout(() => { $('#progresoFill').css('width', pct + '%'); }, 400);
    $('#badgesWrap').html(BADGES_DEF.map(b => {
        const ok = vistas >= b.min;
        return `<div class="badge-item">
            <div class="badge-icon ${ok ? '' : 'bloqueado'}" style="background:${ok ? b.color : 'transparent'};" title="${ok ? b.label+' desbloqueado' : `Visitá ${b.min} atracciones`}">${b.icono}</div>
            <div class="badge-label" style="color:${ok ? 'var(--text-primary)' : 'var(--text-muted)'};">${b.label}</div>
        </div>`;
    }).join(''));
}

// ── ITINERARIOS ───────────────────────────────────────────────────────────────
function cargarItinerarios() {
    $.ajax({ url:'index.php?c=itinerarios&a=Index&formato=json', type:'GET', dataType:'json',
        success: function(resp) {
            const lista = resp.itinerarios || resp.data || [];
            $('#kpiItinerarios').text(lista.length);
            if (!lista.length) {
                $('#itinerariosGrid').html('<div style="padding:20px;color:var(--text-muted);text-align:center;font-size:0.85rem;"><i class="fas fa-route" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:10px;"></i>' + TI.sin_itinerarios + '</div>'); return;
            }
            $('#itinerariosGrid').html(lista.slice(0,3).map((it, i) => {
                const fecha = it.fecha_creacion ? new Date(it.fecha_creacion).toLocaleDateString(LOCALE,{day:'2-digit',month:'short'}) : '—';
                const dest  = it.cantidad_destinos || it.total_atracciones || '—';
                return `<div class="itinerario-item" onclick="window.location='index.php?c=itinerarios&a=index'">
                    <div class="itin-icon"><i class="fas fa-route"></i></div>
                    <div class="itin-info"><h5>${it.nombre||it.titulo||TI.itinerario_n.replace('{n}', i+1)}</h5><p>${it.descripcion ? it.descripcion.slice(0,50)+'...' : 'La Paz, Bolivia'}</p></div>
                    <div class="itin-meta"><span>${fecha}</span><strong>${dest} ${TI.destinos}</strong></div>
                </div>`;
            }).join(''));
        },
        error: () => { $('#kpiItinerarios').text('0'); $('#itinerariosGrid').html('<div style="padding:20px;color:var(--text-muted);text-align:center;font-size:0.85rem;"><i class="fas fa-route" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:10px;"></i>' + TI.sin_itinerarios + '</div>'); }
    });
}

// ── PERFIL ────────────────────────────────────────────────────────────────────
function cargarNombreUsuario() {
    $.ajax({ url:'index.php?c=login&a=VerPreferencias', type:'GET', dataType:'json',
        success: function(resp) { if (resp.success && resp.usuario?.nombre) $('#heroNombre').text(resp.usuario.nombre); }
    });
}

// ── VER DETALLE ───────────────────────────────────────────────────────────────
function verDetalle(id) {
    const elAnt = document.getElementById('modalDetalleAtraccion');
    if (elAnt) { const i = bootstrap.Modal.getInstance(elAnt); if(i) i.dispose(); elAnt.remove(); }
    $('#contenedorModalDetalle').html(`<div class="modal fade" id="modalDetalleAtraccion" data-bs-backdrop="true"><div class="modal-dialog modal-xl"><div class="modal-content p-5 text-center" style="border-radius:16px"><div class="spinner-border mx-auto mb-3" style="color:#6366f1;width:2.5rem;height:2.5rem"></div><p class="text-muted mb-0">Cargando...</p></div></div></div>`);
    const spinner = new bootstrap.Modal(document.getElementById('modalDetalleAtraccion'));
    $.ajax({ url:'index.php?c=atracciones&a=detalle&id='+id, type:'GET',
        success: function(html) { spinner.hide(); $('#contenedorModalDetalle').html(html); new bootstrap.Modal(document.getElementById('modalDetalleAtraccion')).show(); },
        error:   function()     { spinner.hide(); }
    });
}

// ── INIT ──────────────────────────────────────────────────────────────────────
$(document).ready(function() {
    toastr.options = { closeButton:true, progressBar:true, positionClass:'toast-top-right', timeOut:'3000' };
    initBasico();
    cargarClima();
    EtrevaAuth.verificarSesionInicial(function(autenticado) {
        if (autenticado) {
            cargarNombreUsuario();
            cargarRecDia();
            cargarFestividades();
            cargarItinerarios();
        }
    });
});
</script>

</body>
</html>