<?php
include 'view/menu.php';
include 'modal/modal_guardar_itinerario.php';
include 'modal/modal_calificacion.php';

// ── Textos i18n para JS ─────────────────────────────────────────────────────
$t = [
    'eliminar_titulo'       => Lang::get('itinerarios.eliminar_titulo'),
    'eliminar_texto'        => Lang::get('itinerarios.eliminar_texto'),
    'btn_eliminar_si'       => Lang::get('itinerarios.btn_eliminar_si'),
    'btn_cancelar'          => Lang::get('itinerarios.btn_cancelar'),
    'toast_exito'           => Lang::get('itinerarios.toast_exito'),
    'toast_error'           => Lang::get('itinerarios.toast_error'),
    'toast_favorito_ok'     => Lang::get('itinerarios.toast_favorito_ok'),
    'toast_favorito_error'  => Lang::get('itinerarios.toast_favorito_error'),
    'toast_conexion_error'  => Lang::get('itinerarios.toast_conexion_error'),
    'en_favoritos'          => Lang::get('itinerarios.en_favoritos'),
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Lang::get('itinerarios.titulo_pagina') ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>
.btn-eliminar-itin {
    background: var(--red); /* Ahora es rojo sólido por defecto */
    border: 1px solid var(--red);
    color: #fff; /* Texto blanco */
    border-radius: 10px;
    padding: 8px 12px;
    font-size: .78rem;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}
.btn-eliminar-itin:hover {
     transform:translateY(-2px);
    box-shadow:0 6px 18px rgba(102,126,234,.4);
}

:root {
    --primary:  #667eea;
    --secondary:#764ba2;
    --accent:       #7274ee;
    --bg:       #1a1a2e;
    --bg-deep:  #0f1629;
    --surf:     #1e2140;
    --surf2:    #252848;
    --border:   rgba(255,255,255,0.07);
    --green:    #10b981;
    --red:      #ef4444;
    --gold:     #f59e0b;
    --text:     #e2e8f0;
    --muted:    rgba(255,255,255,0.4);
    --r:        18px;
    /* alias para que el resto del CSS no cambie */
    --accent-card: #667eea;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
    background: var(--bg) !important;
    color: var(--text) !important;
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
}

/* ── Layout con sidebar ── */
main {
    margin-left: 280px;
    padding: 0;
    background: var(--bg) !important;
    min-height: 100vh;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}
main.min-main { margin-left: 85px; }

@media (max-width: 768px) {
    main { margin-left: 0 !important; }
}

/* HERO */
.hero {
    background: linear-gradient(135deg, var(--bg-deep) 0%, #1a1040 50%, var(--bg) 100%);
    padding: 48px 24px 36px;
    border-bottom: 1px solid var(--border);
    position: relative; overflow: hidden;
}
.hero::before {
    content:''; position:absolute; top:-80px; right:-80px;
    width:400px; height:400px;
    background: radial-gradient(circle, rgba(102,126,234,.18) 0%, transparent 70%);
    pointer-events:none;
}
.hero::after {
    content:''; position:absolute; bottom:-60px; left:10%;
    width:300px; height:300px;
    background: radial-gradient(circle, rgba(118,75,162,.12) 0%, transparent 70%);
    pointer-events:none;
}
.hero h1 {
    font-family:'Syne',sans-serif; font-size:2.4rem; font-weight:800;
    background: linear-gradient(135deg,#fff 0%, #b8c3f8 100%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    background-clip:text; margin-bottom:6px;
}
.hero p { color:var(--muted); font-size:1rem; font-weight:300; }

/* FILTRO */
.filtro-wrap {
    max-width:1200px; margin:28px auto;
    background:var(--surf); border:1px solid var(--border);
    border-radius:var(--r); padding:20px 24px;
}
.filtro-wrap .form-label {
    font-size:.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.8px; color:var(--muted); margin-bottom:8px;
}
.filtro-wrap .form-select {
    background:var(--surf2); border:1px solid var(--border);
    color:var(--text); border-radius:12px; height:44px;
    font-size:.88rem; transition:border-color .2s;
}
.filtro-wrap .form-select:focus {
    background:var(--surf2); border-color:var(--accent);
    color:var(--text); box-shadow:0 0 0 3px rgba(108,99,255,.15);
}
.filtro-wrap .form-select option { background:#1c1f35; }
.btn-fil {
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    border:none; color:#fff; border-radius:12px;
    height:44px; width:100%; font-weight:700; font-size:.88rem;
    cursor:pointer; transition:all .2s;
}
.btn-fil:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(102,126,234,.4); color:#fff; }
.btn-lim {
    background:transparent; border:1px solid var(--border);
    color:var(--muted); border-radius:12px; height:44px; width:100%;
    font-size:.88rem; text-decoration:none;
    display:flex; align-items:center; justify-content:center;
    transition:all .2s;
}
.btn-lim:hover { border-color:rgba(255,255,255,.2); color:var(--text); }

/* RESULTADOS BAR */
.res-bar {
    max-width:1200px; margin:0 auto 20px;
    display:flex; align-items:center; justify-content:space-between;
    padding:0 4px;
}
.res-bar .cnt { font-size:.85rem; color:var(--muted); }
.res-bar .cnt strong { color:var(--primary); font-size:1rem; }
.fil-badge {
    background:rgba(102,126,234,.12); border:1px solid rgba(102,126,234,.25);
    color:#b8c3f8; font-size:.72rem; padding:4px 12px;
    border-radius:20px; font-weight:600;
}

/* GRID */
.itin-grid {
    max-width:1200px; margin:0 auto;
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(310px,1fr));
    gap:20px; padding-bottom:60px;
}

/* CARD */
.itin-card {
    background:var(--surf); border:1px solid var(--border);
    border-radius:var(--r); overflow:hidden;
    transition:transform .25s, box-shadow .25s, border-color .25s;
    display:flex; flex-direction:column;
    opacity:0; animation:cardIn .4s ease forwards;
}
@keyframes cardIn {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
.itin-card:nth-child(1){ animation-delay:.05s; }
.itin-card:nth-child(2){ animation-delay:.10s; }
.itin-card:nth-child(3){ animation-delay:.15s; }
.itin-card:nth-child(4){ animation-delay:.20s; }
.itin-card:nth-child(5){ animation-delay:.25s; }
.itin-card:nth-child(6){ animation-delay:.30s; }
.itin-card:hover {
    transform:translateY(-6px);
    box-shadow:0 20px 50px rgba(0,0,0,.5);
    border-color:rgba(102,126,234,.3);
}
.itin-card.v-card { border-color:rgba(16,185,129,.25); }
.itin-card.v-card:hover { border-color:rgba(16,185,129,.5); }
.itin-card.hoy-card {
    border-color:rgba(102,126,234,.4);
    box-shadow:0 0 0 1px rgba(102,126,234,.2);
}
.itin-card.manana-card {
    border-color:rgba(245,158,11,.35);
}
.itin-card.pmanana-card {
    border-color:rgba(14,116,144,.35);
}
.itin-card.semana-card {
    border-color:rgba(99,102,241,.25);
}

/* IMG */
.itin-img {
    height:175px; background-size:cover; background-position:center;
    position:relative; cursor:pointer;
}
.img-ov {
    position:absolute; inset:0;
    background:linear-gradient(to top,rgba(13,15,26,.9) 0%,transparent 55%);
}
.img-badges {
    position:absolute; top:12px; left:12px;
    display:flex; gap:6px; flex-wrap:wrap;
}
.bc {
    font-size:.63rem; font-weight:800; text-transform:uppercase;
    letter-spacing:.6px; padding:4px 10px; border-radius:6px;
    background:rgba(0,0,0,.6); backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,.15); color:#fff;
}
.bc-hoy    { background:rgba(102,126,234,.85); border-color:transparent;
             animation:pulse-b 2s infinite; }
.bc-ok     { background:rgba(16,185,129,.85); border-color:transparent; }
.bc-past   { background:rgba(239,68,68,.75);  border-color:transparent; }
.bc-manana { background:rgba(245,158,11,.85); border-color:transparent; }
.bc-pmanana{ background:rgba(14,116,144,.85); border-color:transparent; }
.bc-semana { background:rgba(99,102,241,.75); border-color:transparent; }
@keyframes pulse-b {
    0%,100%{ box-shadow:0 0 0 0 rgba(102,126,234,.4); }
    50%    { box-shadow:0 0 0 6px rgba(102,126,234,0); }
}

/* Fecha sobre imagen */
.img-fecha {
    position:absolute; bottom:12px; left:14px;
}
.fecha-bub {
    background:rgba(0,0,0,.75); backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,.12);
    border-radius:10px; padding:5px 11px; text-align:center;
}
.fecha-bub .fd {
    font-family:'Syne',sans-serif; font-size:1.2rem;
    font-weight:800; color:#fff; line-height:1;
}
.fecha-bub .fm {
    font-size:.58rem; color:rgba(255,255,255,.55);
    text-transform:uppercase; letter-spacing:.5px;
}

/* Body */
.itin-body { padding:16px 18px 12px; flex:1; display:flex; flex-direction:column; }
.itin-name {
    font-family:'Syne',sans-serif; font-size:.98rem; font-weight:700;
    color:#fff; margin-bottom:6px; line-height:1.3;
    display:-webkit-box; -webkit-line-clamp:2;
    -webkit-box-orient:vertical; overflow:hidden;
}
.itin-desc-txt {
    font-size:.77rem; color:var(--muted); line-height:1.5; flex:1;
    display:-webkit-box; -webkit-line-clamp:2;
    -webkit-box-orient:vertical; overflow:hidden;
}

/* Footer */
.itin-foot {
    padding:11px 18px 15px;
    border-top:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between; gap:10px;
}
.hora-lbl { display:flex; align-items:center; gap:5px; font-size:.72rem; color:var(--muted); }
.hora-lbl i { color:var(--primary); font-size:.65rem; }

.btn-marcar-visitado {
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    border:none; color:#fff; border-radius:10px;
    padding:8px 16px; font-size:.78rem; font-weight:700;
    cursor:pointer; transition:all .2s; white-space:nowrap;
}
.btn-marcar-visitado:hover {
    transform:translateY(-2px);
    box-shadow:0 6px 18px rgba(102,126,234,.4);
}
.btn-ya-vis {
    background:rgba(16,185,129,.1);
    border:1px solid rgba(16,185,129,.3);
    color:var(--green); border-radius:10px;
    padding:8px 16px; font-size:.78rem; font-weight:700;
    white-space:nowrap;
}

/* VACÍO */
.vacio {
    grid-column:1/-1; text-align:center; padding:80px 20px;
}
.vacio .ico {
    width:80px; height:80px;
    background:var(--surf2); border:1px solid var(--border);
    border-radius:50%; display:flex; align-items:center; justify-content:center;
    margin:0 auto 20px;
}
.vacio .ico i { font-size:2rem; color:var(--primary); opacity:.5; }
.vacio h3 {
    font-family:'Syne',sans-serif; font-size:1.2rem;
    font-weight:700; color:rgba(255,255,255,.6); margin-bottom:8px;
}
.vacio p { font-size:.85rem; color:var(--muted); }

::-webkit-scrollbar{ width:6px; }
::-webkit-scrollbar-track{ background:var(--bg); }
::-webkit-scrollbar-thumb{ background:var(--surf2); border-radius:3px; }

/* Clase para resaltar la hora cuando está bloqueada */
.hora-bloqueada {
    color: var(--gold) !important;
    font-weight: 600;
}
.hora-bloqueada i {
    color: var(--gold) !important;
}

/* Mejora visual para el botón de programado */
.btn-programado-placeholder {
    border-color: var(--gold); 
    color: var(--gold); 
    background: rgba(245, 158, 11, 0.1);
    border-radius: 10px;
    padding: 8px 16px; 
    font-size: .78rem; 
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-como-llegar {
    background: rgba(102,126,234,.12);
    border: 1px solid rgba(102,126,234,.3);
    color: var(--primary);
    border-radius: 10px;
    padding: 8px 10px;
    font-size: .78rem;
    cursor: pointer;
    transition: all .2s;
    line-height: 1;
}
.btn-como-llegar:hover {
    background: rgba(102,126,234,.25);
    border-color: var(--primary);
    transform: translateY(-2px);
}

</style>
</head>

 

<!-- MODAL CÓMO LLEGAR (solo una vez) -->
<div class="modal fade" id="modalComoLlegar" tabindex="-1"
     aria-labelledby="modalComoLlegarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content"
             style="background:#1e2140;border:1px solid rgba(255,255,255,0.1);
                    border-radius:18px;overflow:hidden;">
 
            <!-- Header -->
            <div class="modal-header"
                 style="background:linear-gradient(135deg,#1a1a2e,#1e2140);
                        border-bottom:1px solid rgba(255,255,255,0.07);
                        padding:16px 24px;">
                <div>
                    <h5 class="modal-title mb-0"
                        id="modalComoLlegarLabel"
                        style="color:#fff;font-family:'Syne',sans-serif;font-size:1rem;">
                        <i class="fas fa-directions me-2" style="color:var(--primary);"></i>
                        <span id="comoLlegarNombre">Cómo llegar</span>
                    </h5>
                    <small id="comoLlegarCiudad"
                           style="color:rgba(255,255,255,0.4);font-size:.78rem;"></small>
                </div>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
 
            <!-- Mapa -->
            <div style="position:relative;">
                <iframe id="comoLlegarIframe"
                        width="100%" height="340"
                        style="border:0;display:block;"
                        loading="lazy"
                        allowfullscreen
                        referrerpolicy="no-referrer-when-downgrade"
                        src="">
                </iframe>
                <!-- Overlay de carga -->
                <div id="mapaLoading"
                     style="position:absolute;inset:0;background:#1e2140;
                            display:flex;align-items:center;justify-content:center;
                            flex-direction:column;gap:10px;">
                    <div class="spinner-border" style="color:var(--primary);width:2rem;height:2rem;"></div>
                    <span style="color:rgba(255,255,255,0.4);font-size:.82rem;">Cargando mapa…</span>
                </div>
            </div>
 
            <!-- Footer -->
            <div class="modal-footer"
                 style="background:#1a1a2e;border-top:1px solid rgba(255,255,255,0.07);
                        padding:12px 20px;justify-content:space-between;align-items:center;">
 
                <div style="display:flex;flex-direction:column;gap:2px;">
                    <span style="color:rgba(255,255,255,0.4);font-size:.75rem;">
                        <i class="fas fa-map-marker-alt me-1" style="color:var(--primary);"></i>
                        <span id="comoLlegarCoords"></span>
                    </span>
                    <span style="color:rgba(255,255,255,0.25);font-size:.7rem;">
                        Toca el enlace para abrir navegación paso a paso
                    </span>
                </div>
 
                <div style="display:flex;gap:8px;">
                    <a id="btnWaze"
                       href="#" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;
                              background:rgba(0,210,110,0.15);
                              border:1px solid rgba(0,210,110,0.3);
                              color:#00d26a;border-radius:10px;
                              padding:8px 14px;font-size:.78rem;font-weight:700;
                              text-decoration:none;transition:all .2s;">
                        <i class="fas fa-car"></i> Waze
                    </a>
                    <a id="btnGmaps"
                       href="#" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;
                              background:linear-gradient(135deg,var(--primary),var(--secondary));
                              color:#fff;border-radius:10px;border:none;
                              padding:8px 18px;font-size:.78rem;font-weight:700;
                              text-decoration:none;transition:all .2s;white-space:nowrap;">
                        <i class="fas fa-map"></i> Google Maps
                    </a>
                </div>
            </div>
 
        </div>
    </div>
</div>
 

<body>
<main>

<div class="hero">
    <h1><i class="fas fa-calendar-alt me-3" style="color:var(--primary);font-size:1.8rem;vertical-align:middle;-webkit-text-fill-color:var(--primary);"></i><?= Lang::get('itinerarios.hero_titulo') ?></h1>
    <p><?= Lang::get('itinerarios.hero_subtitulo') ?></p>
</div>

<div style="max-width:1200px;margin:0 auto;padding:0 16px;">

<?php
$mesActual  = $_GET['mes']  ?? date('m');
$anioActual = $_GET['anio'] ?? date('Y');
// Los nombres de meses se obtienen del archivo de idioma
?>

<!-- FILTRO -->
<div class="filtro-wrap">
    <form method="GET" action="index.php" class="row g-3 align-items-end">
        <input type="hidden" name="c" value="itinerarios">
        <input type="hidden" name="a" value="Index">

        <div class="col-md-4">
            <label class="form-label"><?= Lang::get('itinerarios.mes') ?></label>
            <select name="mes" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>"
                        <?= (str_pad($m,2,'0',STR_PAD_LEFT) == $mesActual) ? 'selected' : '' ?>>
                        <?= Lang::get("meses.$m") ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label"><?= Lang::get('itinerarios.año') ?></label>
            <select name="anio" class="form-select">
                <?php for ($y = date('Y')-2; $y <= date('Y')+2; $y++): ?>
                    <option value="<?= $y ?>" <?= ($y == $anioActual) ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn-fil">
                <i class="fas fa-filter me-2"></i><?= Lang::get('itinerarios.btn_filtrar') ?>
            </button>
        </div>

        <div class="col-md-2">
            <a href="index.php?c=itinerarios&a=Index" class="btn-lim">
                <?= Lang::get('itinerarios.btn_limpiar') ?>
            </a>
        </div>
    </form>

    <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);
            display:flex;align-items:center;justify-content:flex-end;gap:10px;">
    <span style="color:var(--muted);font-size:.8rem;">
        <i class="fas fa-file-pdf me-1" style="color:#ef4444;"></i>
        <?= Lang::get('itinerarios.exportar_desc') ?>
    </span>
    <a href="index.php?c=itinerarios&a=ExportarPDF&mes=<?= $mesActual ?>&anio=<?= $anioActual ?>"
       target="_blank"
       id="btnExportarPDF"
       style="display:inline-flex;align-items:center;gap:8px;
              background:linear-gradient(135deg,#ef4444,#b91c1c);
              color:#fff;border-radius:10px;padding:9px 20px;
              font-size:.82rem;font-weight:700;text-decoration:none;
              border:none;cursor:pointer;transition:all .2s;white-space:nowrap;">
        <i class="fas fa-file-pdf"></i>
        <?= Lang::get('itinerarios.exportar_pdf') ?>
    </a>
</div>

</div>

<!-- BARRA RESULTADOS -->
<div class="res-bar">
    <div class="cnt">
        <?php
        $total = count($itinerarios);
        $resultado_texto = $total == 1 ? Lang::get('itinerarios.resultado_singular') : Lang::get('itinerarios.resultado_plural');
        ?>
        <strong><?= $total ?></strong> <?= $resultado_texto ?>
    </div>
    <?php if (!empty($_GET['mes'])): ?>
    <span class="fil-badge">
        <i class="fas fa-calendar me-1"></i>
        <?= Lang::get("meses." . ((int)$_GET['mes'])) ?> <?= htmlspecialchars($_GET['anio'] ?? '') ?>
    </span>
    <?php endif; ?>
</div>

<!-- GRID -->
<div class="itin-grid">

<?php if (empty($itinerarios)): ?>
    <div class="vacio">
        <div class="ico"><i class="fas fa-calendar-times"></i></div>
        <h3><?= Lang::get('itinerarios.sin_visitas_titulo') ?></h3>
        <p>
            <?php if (!empty($_GET['mes'])): ?>
                <?= sprintf(Lang::get('itinerarios.sin_visitas_texto'), Lang::get("meses." . ((int)$_GET['mes'])), htmlspecialchars($_GET['anio'] ?? date('Y'))) ?>
            <?php else: ?>
                <?= Lang::get('itinerarios.sin_visitas_general') ?>
            <?php endif; ?>
        </p>
    </div>

<?php else: ?>

        <?php
        $tz = new DateTimeZone('America/La_Paz');
        $ahora = new DateTime('now', $tz);
        $hoyStr = $ahora->format('Y-m-d');

        foreach ($itinerarios as $item):
            // 1. Datos básicos
            $nombre      = $item['nombre']      ?? 'Atracción';
            $descripcion = $item['descripcion'] ?? '';
            $descCorta   = mb_substr($descripcion, 0, 90) . (mb_strlen($descripcion) > 90 ? '…' : '');
            $imagen      = $item['imagen_url']  ?? 'assets/img/default.jpg';
            $estado      = $item['estado']      ?? 'pendiente';
            $esVisitado  = ($estado === 'visitado');
            $categoria   = $item['categoria']   ?? 'Atracción';

            // 2. PROCESAR FECHA (Debe ir antes de las comparaciones)
            $fechaRaw   = $item['fecha_visita'] ?? null;
            $fechaLocal = null;
            if ($fechaRaw) {
                try {
                    $dt = new DateTime($fechaRaw);
                    $dt->setTimezone($tz);
                    $fechaLocal = $dt;
                } catch (Exception $e) {}
            }

            // 3. LÓGICA DE TIEMPO
            // Si la fecha programada es menor o igual a "ahora", habilitamos el botón
            $yaEsHora = ($fechaLocal && $ahora >= $fechaLocal);

            // Clasificación visual de etiquetas
            $fechaDia = $fechaLocal ? $fechaLocal->format('Y-m-d') : null;
            $esHoy    = $fechaDia === $hoyStr;
            $yaPaso   = $fechaDia && $fechaDia < $hoyStr; // Fecha calendario anterior a hoy

            $diffDias = null;
            if ($fechaDia && $fechaDia > $hoyStr) {
                $dtBase   = new DateTime($hoyStr, $tz);
                $dtFut    = new DateTime($fechaDia, $tz);
                $diffDias = (int)$dtBase->diff($dtFut)->days;
            }

            $esManana       = $diffDias === 1;
            $esPasadoManana = $diffDias === 2;
            $esEstaSemana   = $diffDias !== null && $diffDias >= 3 && $diffDias <= 6;

            // Clases CSS para la tarjeta
            $cls = '';
            if ($esVisitado)      $cls .= ' v-card';
            if ($esHoy)           $cls .= ' hoy-card';
            if ($esManana)        $cls .= ' manana-card';
            if ($esPasadoManana)  $cls .= ' pmanana-card';
            if ($esEstaSemana)    $cls .= ' semana-card';
        ?>

    <div class="itin-card<?= $cls ?>"
         data-itin-id="<?= $item['id'] ?>"
         data-atr-id="<?= $item['atracciones_id'] ?>"
         data-nombre="<?= htmlspecialchars($nombre, ENT_QUOTES) ?>"
         data-categoria="<?= htmlspecialchars($categoria, ENT_QUOTES) ?>">

        <!-- Imagen -->
        <div class="itin-img" style="background-image:url('<?= htmlspecialchars($imagen) ?>');">
            <div class="img-ov"></div>

            <div class="img-badges">
                <span class="bc"><?= htmlspecialchars(strtoupper($categoria)) ?></span>
                
                <?php if ($estado === 'favorito'): ?>
                    <span class="bc bc-ok"><i class="fas fa-check-circle me-1"></i><?= Lang::get('itinerarios.visitado') ?></span>
                    <span class="bc" style="background: var(--gold); border-color: transparent; color: #fff;">
                        <i class="fas fa-star me-1"></i><?= Lang::get('itinerarios.favorito') ?>
                    </span>
                <?php elseif ($esVisitado): ?>
                    <span class="bc bc-ok"><i class="fas fa-check-circle me-1"></i><?= Lang::get('itinerarios.visitado') ?></span>
                <?php endif; ?>

                <?php if ($esHoy): ?>
                    <span class="bc bc-hoy"><i class="fas fa-bolt me-1"></i><?= Lang::get('itinerarios.hoy') ?></span>
                <?php elseif ($esManana): ?>
                    <span class="bc bc-manana"><i class="fas fa-sun me-1"></i><?= Lang::get('itinerarios.mañana') ?></span>
                <?php elseif ($esPasadoManana): ?>
                    <span class="bc bc-pmanana"><i class="fas fa-calendar-day me-1"></i><?= Lang::get('itinerarios.pasado_mañana') ?></span>
                <?php elseif ($esEstaSemana): ?>
                    <span class="bc bc-semana"><i class="fas fa-calendar-week me-1"></i><?= Lang::get('itinerarios.esta_semana') ?></span>
                <?php elseif ($yaPaso && !$esVisitado && $estado !== 'favorito'): ?>
                    <span class="bc bc-past"><?= Lang::get('itinerarios.finalizado') ?></span>
                <?php endif; ?>
            </div>

            <?php if ($fechaLocal): ?>
            <div class="img-fecha">
                <div class="fecha-bub">
                    <div class="fd"><?= $fechaLocal->format('d') ?></div>
                    <div class="fm"><?= strtoupper($fechaLocal->format('M')) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Body -->
        <div class="itin-body">
            <div class="itin-name"><?= htmlspecialchars($nombre) ?></div>
            <div class="itin-desc-txt"><?= htmlspecialchars($descCorta) ?></div>
        </div>

        <!-- Footer -->
    <div class="itin-foot">
    <div class="hora-lbl <?= (!$yaEsHora && $estado === 'pendiente') ? 'hora-bloqueada' : '' ?>">
        <i class="fas fa-clock"></i>
        <?= $fechaLocal ? $fechaLocal->format('H:i') . ' hs' : Lang::get('itinerarios.sin_hora') ?>
    </div>

        <div class="d-flex gap-2">
           <?php if ($estado === 'pendiente'): ?>
                <button class="btn-eliminar-itin btn-eliminar-itinerario" title="<?= Lang::get('itinerarios.eliminar_titulo') ?>">
                    <i class="fas fa-trash-alt"></i>
                </button>

                <?php if ($yaEsHora): ?>
                    <button class="btn-marcar-visitado">
                        <i class="fas fa-check me-1"></i><?= Lang::get('itinerarios.marcar_visitado') ?>
                    </button>
                <?php else: ?>
                    <span class="btn-ya-vis" style="border-color: var(--gold); color: var(--gold); background: rgba(245, 158, 11, 0.1);" title="<?= Lang::get('itinerarios.programado') ?>">
                        <i class="fas fa-lock me-1"></i><?= Lang::get('itinerarios.programado') ?>
                    </span>
                <?php endif; ?>
            
        <?php elseif ($estado === 'visitado'): ?>
            <button class="btn-add-fav"          
                    style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);
                        color:var(--gold);border-radius:10px;padding:8px 16px;
                        font-size:.78rem;font-weight:700;cursor:pointer;">
                <i class="far fa-star me-1"></i><?= Lang::get('itinerarios.agregar_favoritos') ?>
            </button>
            <span class="btn-ya-vis">
                <i class="fas fa-check-circle me-1"></i><?= Lang::get('itinerarios.visitado_etiqueta') ?>
            </span>

        <?php elseif ($estado === 'favorito'): ?>
            <span class="btn-ya-vis" style="border-color: var(--gold); color: var(--gold); background: rgba(245, 158, 11, 0.1);">
                <i class="fas fa-star me-1"></i><?= Lang::get('itinerarios.en_favoritos') ?>
            </span>
        <?php endif; ?>
        </div>
        <?php if ($item['latitud'] && $item['longitud']): ?>
<button class="btn-como-llegar"
        data-lat="<?= htmlspecialchars($item['latitud']) ?>"
        data-lng="<?= htmlspecialchars($item['longitud']) ?>"
        data-nombre="<?= htmlspecialchars($nombre, ENT_QUOTES) ?>"
        data-ciudad="<?= htmlspecialchars($item['ciudad'] ?? '', ENT_QUOTES) ?>"
        title="Cómo llegar">
    <i class="fas fa-directions"></i>
</button>
<?php endif; ?>
</div>

    </div>

    <?php endforeach; ?>
<?php endif; ?>

</div><!-- /itin-grid -->
</div><!-- /container -->

<div id="contenedorModalDetalle"></div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── Textos i18n desde PHP ─────────────────────────────────────────────────────
const T = <?= json_encode($t, JSON_UNESCAPED_UNICODE) ?>;

$(document).ready(function () {


// ── Cómo llegar ───────────────────────────────────────────────────────
      // ── Cómo llegar ───────────────────────────────────────────────────────
$(document).on('click', '.btn-como-llegar', function(e) {
    e.stopPropagation();

    const lat    = $(this).data('lat');
    const lng    = $(this).data('lng');
    const nombre = $(this).data('nombre');
    const ciudad = $(this).data('ciudad') || '';

    if (!lat || !lng) {
        toastr.warning('Coordenadas no disponibles para esta atracción.');
        return;
    }

    // URLs
    const gmapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
    const wazeUrl  = `https://waze.com/ul?ll=${lat},${lng}&navigate=yes`;
    const embedUrl = `https://maps.google.com/maps?q=${lat},${lng}&z=15&output=embed&hl=es`;

    // Rellenar modal
    $('#comoLlegarNombre').text(nombre);
    $('#comoLlegarCiudad').text(ciudad ? `📍 ${ciudad}` : '');
    $('#comoLlegarCoords').text(`${lat}, ${lng}`);
    $('#btnGmaps').attr('href', gmapsUrl);
    $('#btnWaze').attr('href', wazeUrl);

    // Mostrar overlay y cargar iframe con timeout por si falla
    $('#mapaLoading').show();
    const $iframe = $('#comoLlegarIframe');
    
    // Limpiar evento anterior y establecer uno nuevo
    $iframe.off('load').on('load', function() {
        $('#mapaLoading').fadeOut(300);
    });
    
    // Timeout por si el iframe no carga (ej. bloqueado)
    const timeout = setTimeout(() => {
        $('#mapaLoading').fadeOut(300);
        toastr.warning('El mapa no pudo cargarse. Puedes usar los enlaces de navegación.');
    }, 8000);

    // Cargar el iframe
    $iframe.attr('src', embedUrl);

    // Mostrar modal
    var modal = new bootstrap.Modal(document.getElementById('modalComoLlegar'));
    modal.show();

    // Limpiar timeout si el iframe carga antes
    $iframe.on('load', function() {
        clearTimeout(timeout);
    });
});



 
 
// ── Hover en botón PDF ────────────────────────────────────────────────
$('#btnExportarPDF')
    .on('mouseenter', function() {
        $(this).css({ 'transform':'translateY(-2px)',
                      'box-shadow':'0 6px 18px rgba(239,68,68,.4)' });
    })
    .on('mouseleave', function() {
        $(this).css({ 'transform':'', 'box-shadow':'' });
    });


    $(document).on('click', '.btn-eliminar-itinerario', function (e) {
        e.stopPropagation();
        const card = $(this).closest('.itin-card');
        const idItinerario = card.data('itin-id');
        const nombre = card.data('nombre');

        Swal.fire({
            title: T.eliminar_titulo,
            text: T.eliminar_texto.replace('%s', nombre),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#667eea', // var(--primary)
            cancelButtonColor: '#ef4444',  // var(--red)
            confirmButtonText: '<i class="fas fa-trash-alt me-2"></i>' + T.btn_eliminar_si,
            cancelButtonText: T.btn_cancelar,
            background: '#1e2140',         // var(--surf)
            color: '#e2e8f0',              // var(--text)
            backdrop: `rgba(15, 22, 41, 0.8)`, // var(--bg-deep) con opacidad
            customClass: {
                popup: 'rounded-4 border border-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('index.php?c=itinerarios&a=Eliminar', { id: idItinerario }, function (res) {
                    toastr.success(T.toast_exito);
                    
                    card.css('transition', 'all 0.5s ease');
                    card.css('transform', 'scale(0.8)');
                    card.css('opacity', '0');
                    
                    setTimeout(() => {
                        card.remove();
                        // Si el grid queda vacío, podrías recargar para mostrar el mensaje de "Sin visitas"
                        if ($('.itin-card').length === 0) {
                            location.reload();
                        }
                    }, 500);
                }).fail(function() {
                    toastr.error(T.toast_error);
                });
            }
        });
    });

    $(document).on('click', '.btn-marcar-visitado', function (e) {
        e.stopPropagation();
        const card = $(this).closest('.itin-card');
        if (typeof abrirModalCalificacion === "function") {
            abrirModalCalificacion(
                card.data('itin-id'),
                card.data('atr-id'),
                card.data('nombre'),
                card.data('categoria')
            );
        }
    });

    // Handler correcto para btn-add-fav
    $(document).on('click', '.btn-add-fav', function (e) {
        e.stopPropagation();
        const card = $(this).closest('.itin-card');
        const idItinerario = card.data('itin-id');
        const nombre = card.data('nombre');

        $.post('index.php?c=itinerarios&a=Favorito', { id: idItinerario }, function (res) {
            if (res.success) {
                toastr.success(T.toast_favorito_ok);
                // Actualizar la card visualmente sin recargar
                card.find('.btn-add-fav').replaceWith(
                    `<span class="btn-ya-vis" style="border-color:var(--gold);color:var(--gold);background:rgba(245,158,11,0.1);">
                        <i class="fas fa-star me-1"></i>${T.en_favoritos}
                    </span>`
                );
            } else {
                toastr.error(res.message || T.toast_favorito_error);
            }
        }, 'json').fail(() => toastr.error(T.toast_conexion_error));
    });

    $(document).on('click', '.itin-img', function () {
        const atrId = $(this).closest('.itin-card').data('atr-id');
        if (atrId && typeof verDetalle === "function") {
            verDetalle(atrId);
        }
    });
});

function verDetalle(id) {
    $.post('index.php?c=atracciones&a=ObtenerDetalle', { id: id }, function (res) {
        $('#contenedorModalDetalle').html(res);
        new bootstrap.Modal(document.getElementById('modalDetalleAtraccion')).show();
    });
}

// DEBUG
console.log('GET params:', <?= json_encode($_GET, JSON_UNESCAPED_UNICODE) ?>);
console.log('Total:', <?= count($itinerarios) ?>);
console.log('Items:', <?= json_encode(array_map(fn($i) => [
    'id'          => $i['id'],
    'nombre'      => $i['nombre'],
    'fecha_visita'=> $i['fecha_visita'],
    'estado'      => $i['estado'],
], $itinerarios), JSON_UNESCAPED_UNICODE) ?>);
</script>
</body>
</html>