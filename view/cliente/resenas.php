<?php
include 'view/menu.php';

// ── Textos i18n para JS ─────────────────────────────────────────────────────
$t = [
    // Modales y toasts
    'modal_titulo'          => Lang::get('resenas.modal_titulo'),
    'modal_calificacion'    => Lang::get('resenas.modal_calificacion'),
    'modal_comentario'      => Lang::get('resenas.modal_comentario'),
    'modal_placeholder'     => Lang::get('resenas.modal_placeholder'),
    'modal_cancelar'        => Lang::get('resenas.modal_cancelar'),
    'modal_guardar'         => Lang::get('resenas.modal_guardar'),
    'eliminar_titulo'       => Lang::get('resenas.eliminar_titulo'),
    'eliminar_texto'        => Lang::get('resenas.eliminar_texto'),
    'eliminar_confirmar'    => Lang::get('resenas.eliminar_confirmar'),
    'eliminar_cancelar'     => Lang::get('resenas.eliminar_cancelar'),
    'toast_actualizada'     => Lang::get('resenas.toast_actualizada'),
    'toast_error_guardar'   => Lang::get('resenas.toast_error_guardar'),
    'toast_conexion'        => Lang::get('resenas.toast_conexion'),
    'toast_eliminada'       => Lang::get('resenas.toast_eliminada'),
    'toast_error_eliminar'  => Lang::get('resenas.toast_error_eliminar'),
    'toast_selecciona_calif'=> Lang::get('resenas.toast_selecciona_calif'),

    // Textos de tarjetas
    'sin_comentario'        => Lang::get('resenas.sin_comentario'),
    'contactar'             => Lang::get('resenas.contactar'),
    'badge_mi_resena'       => Lang::get('resenas.badge_mi_resena'),
    'btn_editar'            => Lang::get('resenas.btn_editar'),
    'btn_eliminar'          => Lang::get('resenas.btn_eliminar'),
    'btn_ver_lugar'         => Lang::get('resenas.btn_ver_lugar'),
    'atraccion_default'     => Lang::get('resenas.atraccion_default'),
    'categoria_label'       => Lang::get('resenas.categoria_label'),
    'probar_otro'           => Lang::get('resenas.probar_otro'),
    'error_carga'           => Lang::get('resenas.error_carga'),
    'error_conexion'        => Lang::get('resenas.error_conexion'),
    'cargando'              => Lang::get('resenas.cargando'),
    'sin_resenas_mias'      => Lang::get('resenas.sin_resenas_mias'),
    'sin_resenas'           => Lang::get('resenas.sin_resenas'),
    'mostrando'             => Lang::get('resenas.mostrando'),
    'de'                    => Lang::get('resenas.de'),
    'resenas_txt'           => Lang::get('resenas.resenas_txt'),

   'tags' => [
        'buen_ambiente'      => Lang::get('modal_calificacion.tag_buen_ambiente'),
        'excelente_atencion' => Lang::get('modal_calificacion.tag_excelente_atencion'),
        'precio_justo'       => Lang::get('modal_calificacion.tag_precio_justo'),
        'muy_recomendado'    => Lang::get('modal_calificacion.tag_muy_recomendado'),
        'para_volver'        => Lang::get('modal_calificacion.tag_para_volver'),
        'decepcionante'      => Lang::get('modal_calificacion.tag_decepcionante'),
        'muy_lleno'          => Lang::get('modal_calificacion.tag_muy_lleno'),
        'dificil_acceso'     => Lang::get('modal_calificacion.tag_dificil_acceso'),
    ],

];

// ── Meses cortos para JavaScript ────────────────────────────────────────────
$mesesCortos = [
    1  => Lang::get('resenas.mes_corto_ene'),
    2  => Lang::get('resenas.mes_corto_feb'),
    3  => Lang::get('resenas.mes_corto_mar'),
    4  => Lang::get('resenas.mes_corto_abr'),
    5  => Lang::get('resenas.mes_corto_may'),
    6  => Lang::get('resenas.mes_corto_jun'),
    7  => Lang::get('resenas.mes_corto_jul'),
    8  => Lang::get('resenas.mes_corto_ago'),
    9  => Lang::get('resenas.mes_corto_sep'),
    10 => Lang::get('resenas.mes_corto_oct'),
    11 => Lang::get('resenas.mes_corto_nov'),
    12 => Lang::get('resenas.mes_corto_dic'),
];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= Lang::get('resenas.titulo_pagina') ?></title>

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
    --red:      #ef4444;
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
    padding: 40px 24px 0;
    border-bottom: 1px solid var(--border);
    position: relative; overflow: hidden;
}
.hero::before {
    content:''; position:absolute; top:-80px; right:-80px; width:420px; height:420px;
    background: radial-gradient(circle, rgba(102,126,234,.14) 0%, transparent 70%);
    pointer-events:none;
}
.hero::after {
    content:''; position:absolute; bottom:-60px; left:10%; width:300px; height:300px;
    background: radial-gradient(circle, rgba(245,158,11,.08) 0%, transparent 70%);
    pointer-events:none;
}
.hero-inner {
    max-width: 900px; margin: 0 auto; padding: 0 0 28px; text-align: center; position: relative; z-index: 1;
}
.hero h1 {
    font-family:'Syne',sans-serif; font-size:2.2rem; font-weight:800;
    background: linear-gradient(135deg, #fff 0%, #b8c3f8 100%);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    background-clip:text; margin-bottom:6px;
}
.hero p { color:var(--muted); font-size:.95rem; margin-bottom:22px; }

/* ── SEARCH BAR ── */
.search-wrap {
    position:relative; max-width:620px; margin:0 auto 16px;
}
.search-wrap input {
    width:100%; padding:15px 110px 15px 20px;
    border-radius:50px; border:2px solid rgba(255,255,255,.12);
    background:rgba(255,255,255,.07); color:#fff; font-size:.95rem;
    backdrop-filter:blur(8px); transition:all .3s; outline:none;
    font-family:'DM Sans',sans-serif;
}
.search-wrap input::placeholder { color:rgba(255,255,255,.35); }
.search-wrap input:focus {
    border-color:var(--primary);
    background:rgba(255,255,255,.1);
    box-shadow:0 0 0 4px rgba(102,126,234,.18);
}
.search-wrap .search-clear {
    position:absolute; right:52px; top:50%; transform:translateY(-50%);
    background:none; border:none; color:rgba(255,255,255,.4);
    cursor:pointer; font-size:.85rem; display:none; padding:4px;
}
.search-wrap .search-btn {
    position:absolute; right:8px; top:50%; transform:translateY(-50%);
    background:var(--primary); border:none; color:#fff;
    width:40px; height:40px; border-radius:50%;
    cursor:pointer; font-size:.88rem; transition:background .2s;
}
.search-wrap .search-btn:hover { background:#5a6fd6; }

/* ── FILTRO TAGS ── */
.tags-wrap {
    display:flex; gap:8px; flex-wrap:wrap; justify-content:center;
    padding-bottom:4px;
}
.tag-btn {
    padding:7px 18px; border-radius:50px;
    border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.06); color:rgba(255,255,255,.65);
    font-size:.78rem; font-weight:700; cursor:pointer;
    transition:all .2s; display:flex; align-items:center; gap:6px;
    white-space:nowrap;
}
.tag-btn:hover { background:rgba(255,255,255,.12); color:#fff; }
.tag-btn.active {
    border-color:transparent; color:#fff;
    box-shadow:0 4px 14px rgba(0,0,0,.3);
}
.tag-btn[data-tag="todas"].active  { background:var(--primary); }
.tag-btn[data-tag="mias"].active   { background:linear-gradient(135deg,var(--gold),#ea580c); }

/* ── BARRA DE ESTADO ── */
.barra-estado {
    background:rgba(255,255,255,.04);
    border-top:1px solid rgba(255,255,255,.06);
    padding:12px 24px;
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:10px;
}
.barra-estado-txt { color:var(--muted); font-size:.82rem; }
.barra-estado-txt span { color:#fff; font-weight:700; }
.sort-select {
    background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12);
    color:#fff; border-radius:8px; padding:6px 12px;
    font-size:.8rem; cursor:pointer; outline:none;
}
.sort-select option { background:#1a1a2e; color:#fff; }

/* ── GRID PRINCIPAL ── */
.resenas-wrap {
    max-width:1300px; margin:0 auto;
    padding:28px 20px 60px;
}
.resenas-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(310px,1fr));
    gap:20px;
}

/* ── CARD DE RESEÑA ── */
.res-card {
    background:var(--surf); border:1px solid var(--border);
    border-radius:var(--r); overflow:hidden;
    display:flex; flex-direction:column;
    transition:transform .25s, box-shadow .25s, border-color .25s;
    opacity:0; animation:cardIn .35s ease forwards;
}
@keyframes cardIn {
    from { opacity:0; transform:translateY(14px); }
    to   { opacity:1; transform:translateY(0); }
}
.res-card:hover {
    transform:translateY(-5px);
    box-shadow:0 18px 44px rgba(0,0,0,.5);
    border-color:rgba(102,126,234,.25);
}
.res-card.es-mia {
    border-color:rgba(245,158,11,.25);
}
.res-card.es-mia:hover {
    border-color:rgba(245,158,11,.5);
    box-shadow:0 18px 44px rgba(0,0,0,.5), 0 0 0 1px rgba(245,158,11,.15);
}

/* Imagen */
.res-img {
    height:155px; background-size:cover; background-position:center; position:relative;
}
.res-img-ov {
    position:absolute; inset:0;
    background:linear-gradient(to top, rgba(13,15,26,.92) 0%, transparent 55%);
}
.res-badges {
    position:absolute; top:10px; left:10px;
    display:flex; gap:6px; flex-wrap:wrap;
}
.rbc {
    font-size:.6rem; font-weight:800; text-transform:uppercase;
    letter-spacing:.5px; padding:4px 10px; border-radius:6px;
    backdrop-filter:blur(8px); color:#fff;
    background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.12);
}
.rbc-mia { background:rgba(245,158,11,.85); border-color:transparent; }

/* Badge "Mi reseña" en esquina superior derecha */
.badge-mia {
    position:absolute; top:10px; right:10px;
    background:rgba(245,158,11,.85); backdrop-filter:blur(6px);
    border-radius:8px; padding:4px 9px;
    font-size:.6rem; font-weight:800; color:#fff; text-transform:uppercase;
    letter-spacing:.5px;
}

/* Rating sobre imagen */
.res-rating-ov {
    position:absolute; bottom:10px; right:12px;
    display:flex; align-items:center; gap:3px;
    background:rgba(0,0,0,.7); backdrop-filter:blur(8px);
    border:1px solid rgba(245,158,11,.25); border-radius:8px; padding:4px 9px;
}
.res-rating-ov i { color:var(--gold); font-size:.62rem; }
.res-rating-ov span { color:#fff; font-size:.72rem; font-weight:700; }

/* Fecha sobre imagen */
.res-fecha-ov {
    position:absolute; bottom:10px; left:12px;
    background:rgba(0,0,0,.7); backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:4px 9px;
    font-size:.65rem; color:rgba(255,255,255,.6);
    display:flex; align-items:center; gap:5px;
}
.res-fecha-ov i { color:var(--primary); font-size:.6rem; }

/* Body */
.res-body { padding:14px 16px 10px; flex:1; display:flex; flex-direction:column; }

.res-atraccion {
    font-family:'Syne',sans-serif; font-size:.92rem; font-weight:700;
    color:#fff; margin-bottom:6px; line-height:1.3;
    display:-webkit-box; -webkit-line-clamp:1;
    -webkit-box-orient:vertical; overflow:hidden;
}

/* Estrellas interactivas (modo edición) y estáticas */
.stars-display { display:flex; gap:3px; margin-bottom:8px; }
.stars-display i { font-size:.82rem; color:var(--gold); }
.stars-display i.empty { color:rgba(255,255,255,.18); }

/* Estrellas editables */
.stars-editable { display:flex; gap:4px; margin-bottom:10px; cursor:pointer; }
.stars-editable i {
    font-size:1.1rem; color:rgba(255,255,255,.2);
    transition:color .15s, transform .15s;
}
.stars-editable i.lit { color:var(--gold); }
.stars-editable i:hover { transform:scale(1.2); }

.res-usuario {
    display:flex; align-items:center; gap:6px;
    margin-bottom:8px;
}
.res-avatar {
    width:26px; height:26px; border-radius:50%;
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    display:flex; align-items:center; justify-content:center;
    font-size:.65rem; font-weight:800; color:#fff; flex-shrink:0;
}
.res-usuario-nombre { font-size:.75rem; color:var(--muted); }
.res-contacto { margin-left:8px; font-size:.72rem; font-weight:700; color:var(--primary); text-decoration:none; white-space:nowrap; }
.res-contacto:hover { text-decoration:underline; }
.res-avatar { width:30px !important; height:30px !important; }
.res-usuario-nombre strong { color:rgba(255,255,255,.75); }

.res-comentario {
    font-size:.78rem; color:rgba(255,255,255,.55); line-height:1.55; flex:1;
    font-style:italic;
    display:-webkit-box; -webkit-line-clamp:3;
    -webkit-box-orient:vertical; overflow:hidden;
    margin-bottom:10px;
    min-height:38px;
}
.res-comentario.vacio { color:rgba(255,255,255,.25); font-style:normal; }

/* Tags */
.res-tags { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:8px; }
.res-tag {
    background:rgba(102,126,234,.08); border:1px solid rgba(102,126,234,.2);
    color:rgba(167,181,255,.8); border-radius:50px;
    padding:2px 9px; font-size:.62rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.4px;
}

/* Footer */
.res-foot {
    padding:10px 16px 13px;
    border-top:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between; gap:8px;
}
.res-cat {
    display:flex; align-items:center; gap:5px;
    font-size:.7rem; color:var(--muted);
}
.res-cat i { color:var(--primary); font-size:.62rem; }

.btn-edit {
    background:rgba(102,126,234,.1); border:1px solid rgba(102,126,234,.25);
    color:var(--primary); border-radius:9px;
    padding:6px 12px; font-size:.72rem; font-weight:700;
    cursor:pointer; transition:all .2s; display:flex; align-items:center; gap:5px;
}
.btn-edit:hover {
    background:rgba(102,126,234,.2); border-color:rgba(102,126,234,.5);
    transform:translateY(-1px);
}
.btn-del {
    background:rgba(239,68,68,.07); border:1px solid rgba(239,68,68,.18);
    color:rgba(239,68,68,.7); border-radius:9px;
    padding:6px 10px; font-size:.72rem; font-weight:700;
    cursor:pointer; transition:all .2s;
}
.btn-del:hover {
    background:rgba(239,68,68,.15); border-color:rgba(239,68,68,.4);
    color:var(--red); transform:translateY(-1px);
}
.btn-ver {
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
    color:var(--muted); border-radius:9px;
    padding:6px 12px; font-size:.72rem; font-weight:700;
    cursor:pointer; transition:all .2s; display:flex; align-items:center; gap:5px;
}
.btn-ver:hover { background:rgba(255,255,255,.1); color:var(--text); }

/* ── MODAL EDITAR ── */
.modal-edit-bg {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.7); backdrop-filter:blur(6px);
    z-index:9999; align-items:center; justify-content:center;
}
.modal-edit-bg.show { display:flex; }
.modal-edit-box {
    background:var(--surf); border:1px solid rgba(102,126,234,.3);
    border-radius:20px; padding:28px; width:100%; max-width:480px;
    margin:16px; animation:modalIn .25s ease;
}
@keyframes modalIn {
    from { opacity:0; transform:scale(.94) translateY(12px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
.modal-edit-box h4 {
    font-family:'Syne',sans-serif; font-size:1.05rem; font-weight:800;
    color:#fff; margin-bottom:18px;
    display:flex; align-items:center; gap:10px;
}
.modal-edit-box h4 i { color:var(--primary); }
.modal-label {
    font-size:.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.7px; color:var(--muted); margin-bottom:8px;
}
.modal-textarea {
    width:100%; background:var(--surf2); border:1px solid var(--border);
    color:var(--text); border-radius:12px; padding:12px 14px;
    font-size:.85rem; font-family:'DM Sans',sans-serif;
    resize:vertical; min-height:90px; outline:none;
    transition:border-color .2s;
}
.modal-textarea:focus { border-color:var(--primary); }
.modal-btns { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
.btn-modal-cancel {
    background:transparent; border:1px solid var(--border);
    color:var(--muted); border-radius:10px; padding:9px 18px;
    font-size:.82rem; cursor:pointer; transition:all .2s;
}
.btn-modal-cancel:hover { border-color:rgba(255,255,255,.2); color:var(--text); }
.btn-modal-save {
    background:linear-gradient(135deg,var(--primary),var(--secondary));
    border:none; color:#fff; border-radius:10px; padding:9px 22px;
    font-size:.82rem; font-weight:700; cursor:pointer; transition:all .2s;
}
.btn-modal-save:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(102,126,234,.4); }

/* ── PAGINACIÓN ── */
.pag-wrap {
    display:flex; align-items:center; justify-content:center;
    gap:7px; flex-wrap:wrap; padding:28px 0 10px;
}
.pag-btn {
    width:38px; height:38px; border-radius:10px;
    border:1px solid var(--border); background:var(--surf2);
    color:var(--muted); font-size:.82rem; font-weight:700;
    cursor:pointer; transition:all .2s;
    display:flex; align-items:center; justify-content:center;
}
.pag-btn:hover:not(:disabled) { border-color:rgba(102,126,234,.4); color:var(--text); }
.pag-btn.active { background:var(--primary); border-color:var(--primary); color:#fff; }
.pag-btn:disabled { opacity:.3; cursor:not-allowed; }
.pag-btn-wide { width:auto; padding:0 14px; }
.pag-dots { color:var(--muted); font-size:.8rem; padding:0 4px; }

/* ── LOADING / VACÍO ── */
.estado-loading, .estado-vacio {
    grid-column:1/-1; text-align:center; padding:70px 20px; color:var(--muted);
}
.estado-vacio .ico {
    width:70px; height:70px; background:var(--surf2); border:1px solid var(--border);
    border-radius:50%; display:flex; align-items:center; justify-content:center;
    margin:0 auto 18px;
}
.estado-vacio .ico i { font-size:1.8rem; color:var(--primary); opacity:.4; }
.estado-vacio h3 {
    font-family:'Syne',sans-serif; font-size:1.1rem;
    color:rgba(255,255,255,.45); margin-bottom:7px;
}

::-webkit-scrollbar { width:6px; }
::-webkit-scrollbar-track { background:var(--bg); }
::-webkit-scrollbar-thumb { background:var(--surf2); border-radius:3px; }
</style>
</head>

<body>
<main>

<!-- ── HERO + BUSCADOR ── -->
<div class="hero">
    <div class="hero-inner">
        <h1>
            <i class="fas fa-star me-2" style="color:var(--gold);-webkit-text-fill-color:var(--gold);font-size:1.8rem;vertical-align:middle;"></i>
            <?= Lang::get('resenas.hero_titulo') ?>
        </h1>
        <p><?= Lang::get('resenas.hero_subtitulo') ?></p>

        <!-- Buscador -->
        <div class="search-wrap">
            <input type="text" id="searchInput" placeholder="<?= Lang::get('resenas.search_placeholder') ?>" autocomplete="off">
            <button class="search-clear" id="searchClear"><i class="fas fa-times"></i></button>
            <button class="search-btn" id="searchBtn"><i class="fas fa-search"></i></button>
        </div>

        <!-- Tags de filtro -->
        <div class="tags-wrap">
            <button class="tag-btn active" data-tag="todas">
                <i class="fas fa-globe"></i> <?= Lang::get('resenas.filtro_todas') ?>
            </button>
            <button class="tag-btn" data-tag="mias">
                <i class="fas fa-user-edit"></i> <?= Lang::get('resenas.filtro_mias') ?>
            </button>
        </div>
    </div>

    <!-- Barra estado -->
    <div class="barra-estado">
        <div class="barra-estado-txt">
            <?= Lang::get('resenas.mostrando') ?> <span id="txtMostrando">—</span> <?= Lang::get('resenas.de') ?> <span id="txtTotal">—</span> <?= Lang::get('resenas.resenas_txt') ?>
        </div>
        <select class="sort-select" id="sortSelect">
            <option value="recientes" selected><?= Lang::get('resenas.orden_recientes') ?></option>
            <option value="mejor_calificacion"><?= Lang::get('resenas.orden_mejor') ?></option>
            <option value="peor_calificacion"><?= Lang::get('resenas.orden_peor') ?></option>
        </select>
    </div>
</div>

<!-- ── GRID DE RESEÑAS ── -->
<div class="resenas-wrap">
    <div class="resenas-grid" id="resenasGrid">
        <div class="estado-loading">
            <div class="spinner-border mx-auto d-block mb-3" style="color:var(--primary);width:2.5rem;height:2.5rem;"></div>
            <p><?= Lang::get('resenas.cargando') ?></p>
        </div>
    </div>

    <!-- Paginación -->
    <div class="pag-wrap" id="pagWrap"></div>
</div>

<!-- ── MODAL EDITAR RESEÑA ── -->
<div class="modal-edit-bg" id="modalEditarBg">
    <div class="modal-edit-box">
        <h4><i class="fas fa-edit"></i> <?= Lang::get('resenas.modal_titulo') ?></h4>

        <input type="hidden" id="editResenaId">

        <div class="modal-label"><?= Lang::get('resenas.modal_calificacion') ?></div>
        <div class="stars-editable mb-3" id="editStars">
            <i class="fas fa-star" data-val="1"></i>
            <i class="fas fa-star" data-val="2"></i>
            <i class="fas fa-star" data-val="3"></i>
            <i class="fas fa-star" data-val="4"></i>
            <i class="fas fa-star" data-val="5"></i>
        </div>

        <div class="modal-label"><?= Lang::get('resenas.modal_comentario') ?></div>
        <textarea class="modal-textarea" id="editComentario" placeholder="<?= Lang::get('resenas.modal_placeholder') ?>"></textarea>

        <div class="modal-btns">
            <button class="btn-modal-cancel" id="btnCancelEdit"><?= Lang::get('resenas.modal_cancelar') ?></button>
            <button class="btn-modal-save" id="btnSaveEdit">
                <i class="fas fa-save me-2"></i><?= Lang::get('resenas.modal_guardar') ?>
            </button>
        </div>
    </div>
</div>

<div id="contenedorModalDetalle"></div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ════════════════════════════════════════════════════════
//  TEXTOS i18n DESDE PHP
// ════════════════════════════════════════════════════════
const T = <?= json_encode($t, JSON_UNESCAPED_UNICODE) ?>;
const MESES_CORTOS = <?= json_encode($mesesCortos, JSON_UNESCAPED_UNICODE) ?>;

// ════════════════════════════════════════════════════════
//  ESTADO GLOBAL
// ════════════════════════════════════════════════════════
const RS = {
    pagina:     1,
    porPagina:  12,
    total:      0,
    paginas:    1,
    tag:        'todas',   // 'todas' | 'mias'
    busqueda:   '',
    orden:      'recientes',
    todas:      [],        // cache de la página actual
};

// ════════════════════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════════════════════
function estrellasHtml(n, editable = false) {
    const llenas = Math.round(parseFloat(n) || 0);
    let s = '';
    for (let i = 1; i <= 5; i++) {
        s += `<i class="fas fa-star${i > llenas ? ' empty' : ''}" ${editable ? `data-val="${i}"` : ''}></i>`;
    }
    return s;
}

function formatFecha(str) {
    if (!str) return '—';
    const d = new Date(str);
    return `${d.getDate()} ${MESES_CORTOS[d.getMonth()+1]} ${d.getFullYear()}`;
}

function inicialAvatar(nombre) {
    return (nombre || 'U').charAt(0).toUpperCase();
}

// Avatar del autor: foto subida, avatar predefinido o inicial (misma lógica que model/avatar.php)
const AVATAR_PRESETS = <?php require_once __DIR__ . '/../../model/avatar.php'; echo Avatar::presetsJs(); ?>;
function avatarAutor(r, nombre) {
    const a = r.usuario_avatar || '';
    if (/^assets\/img\/avatars\/[\w.-]+\.(jpe?g|png|webp|gif)$/i.test(a)) {
        return `<div class="res-avatar" style="background:none;overflow:hidden;"><img src="${a}" alt="" style="width:100%;height:100%;object-fit:cover;"></div>`;
    }
    const m = a.match(/^preset_(\d+)$/);
    if (m && AVATAR_PRESETS[+m[1]]) {
        const p = AVATAR_PRESETS[+m[1]];
        return `<div class="res-avatar" style="background:${p.bg};font-size:.85rem;">${p.emoji}</div>`;
    }
    return `<div class="res-avatar">${inicialAvatar(nombre)}</div>`;
}
function escAttr(t) { const d = document.createElement('div'); d.textContent = t ?? ''; return d.innerHTML.replace(/"/g, '&quot;'); }

// ════════════════════════════════════════════════════════
//  RENDER DE UNA CARD
// ════════════════════════════════════════════════════════
function renderCard(r, delay) {
    const esMia     = r.es_mia;
    const calif     = parseFloat(r.calificacion || 0);
    
    // Limpiar comentario: eliminar cualquier "[Tags: ...]" del texto
    let comentarioOriginal = r.comentario || '';
    let comentarioLimpio = comentarioOriginal.replace(/\s*\[Tags:.*?\]/g, '').trim();
    
    const nombre    = r.atraccion_nombre || T.atraccion_default;
    const imagen    = r.imagen_url || 'assets/img/default.jpg';
    const categoria = r.categoria || T.atraccion_default;
    const ciudad    = r.ciudad || 'La Paz';
    
    // Asegurar que tags sea un array
    let tags = [];
    if (Array.isArray(r.tags)) {
        tags = r.tags.slice(0, 3);
    } else if (typeof r.tags === 'string') {
        try {
            tags = JSON.parse(r.tags).slice(0, 3);
        } catch(e) {
            tags = r.tags.split(',').map(t => t.trim()).filter(t => t).slice(0, 3);
        }
    }
    
    const fecha     = formatFecha(r.created_at);
    const usuario   = r.usuario_display || 'Usuario';
    const atrId     = parseInt(r.atraccion_id || 0);

    // Estrellas
    const estrellas = estrellasHtml(calif);

    // Tags html (traducidos)
    const tagsHtml = tags.map(t => {
        const tagText = T.tags && T.tags[t] ? T.tags[t] : t;
        return `<span class="res-tag">${tagText}</span>`;
    }).join('');

    // Rating overlay
    const ratingStars = estrellasHtml(calif);

    return `
    <div class="res-card ${esMia ? 'es-mia' : ''}"
         style="animation-delay:${delay}s"
         data-resena-id="${r.resena_id}"
         data-calificacion="${calif}"
         data-comentario="${(comentarioLimpio).replace(/"/g,'&quot;')}"
         data-es-mia="${esMia ? '1' : '0'}">

        <!-- Imagen -->
        <div class="res-img" style="background-image:url('${imagen}');">
            <div class="res-img-ov"></div>

            <div class="res-badges">
                <span class="rbc">${categoria.toUpperCase()}</span>
            </div>

            ${esMia ? `<span class="badge-mia"><i class="fas fa-user me-1"></i>${T.badge_mi_resena}</span>` : ''}

            <div class="res-rating-ov">
                ${ratingStars}
                <span>${calif.toFixed(1)}</span>
            </div>

            <div class="res-fecha-ov">
                <i class="fas fa-calendar-alt"></i>${fecha}
            </div>
        </div>

        <!-- Body -->
        <div class="res-body">
            <div class="res-atraccion">${nombre}</div>

            <div class="stars-display">${estrellas}</div>

            <div class="res-usuario">
                ${avatarAutor(r, usuario)}
                <div class="res-usuario-nombre">
                    <strong>${usuario}</strong>
                    <span style="margin-left:4px;color:rgba(255,255,255,.3);">·</span>
                    <span style="margin-left:4px;">${ciudad}</span>
                    ${r.usuario_contacto && !esMia ? `
                    <a class="res-contacto" href="mailto:${escAttr(r.usuario_contacto)}" title="${escAttr(r.usuario_contacto)}">
                        <i class="fas fa-envelope"></i> ${T.contactar}
                    </a>` : ''}
                </div>
            </div>

            <div class="res-comentario ${!comentarioLimpio ? 'vacio' : ''}">
                ${comentarioLimpio
                    ? `<i class="fas fa-quote-left me-1" style="font-size:.6rem;color:var(--primary);opacity:.6;"></i>${comentarioLimpio}`
                    : T.sin_comentario}
            </div>

            ${tagsHtml ? `<div class="res-tags">${tagsHtml}</div>` : ''}
        </div>

        <!-- Footer -->
        <div class="res-foot">
            <div class="res-cat">
                <i class="fas fa-tag"></i>${categoria}
            </div>
            <div class="d-flex gap-2">
                ${esMia ? `
                    <button class="btn-del" onclick="eliminarResena(this)" title="${T.btn_eliminar}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button class="btn-edit" onclick="abrirEditar(this)" title="${T.btn_editar}">
                        <i class="fas fa-edit"></i> ${T.btn_editar}
                    </button>
                ` : `
                    <button class="btn-ver" onclick="verDetalle(${atrId})">
                        <i class="fas fa-eye"></i> ${T.btn_ver_lugar}
                    </button>
                `}
            </div>
        </div>
    </div>`;
}
// ════════════════════════════════════════════════════════
//  CARGAR RESEÑAS DEL SERVIDOR
// ════════════════════════════════════════════════════════
function cargarResenas() {
    $('#resenasGrid').html(`
        <div class="estado-loading">
            <div class="spinner-border mx-auto d-block mb-3"
                 style="color:var(--primary);width:2.5rem;height:2.5rem;"></div>
            <p>${T.cargando}</p>
        </div>`);
    $('#pagWrap').html('');

    $.ajax({
        url: 'index.php?c=resenas&a=ObtenerTodas',
        type: 'GET',
        dataType: 'json',
        data: {
            pagina:    RS.pagina,
            por_pagina:RS.porPagina,
            q:         RS.busqueda,
            solo_mias: RS.tag === 'mias' ? '1' : '0',
        },
        success: function(res) {
            if (!res.success) {
                mostrarVacio(T.error_carga);
                return;
            }

            RS.total   = res.total;
            RS.paginas = res.paginas;
            RS.todas   = res.resenas;

            let lista = [...RS.todas];

            // Ordenar en el cliente
            if (RS.orden === 'mejor_calificacion') {
                lista.sort((a,b) => parseFloat(b.calificacion||0) - parseFloat(a.calificacion||0));
            } else if (RS.orden === 'peor_calificacion') {
                lista.sort((a,b) => parseFloat(a.calificacion||0) - parseFloat(b.calificacion||0));
            }

            if (lista.length === 0) {
                mostrarVacio(RS.tag === 'mias' ? T.sin_resenas_mias : T.sin_resenas);
                actualizarBarra(0, 0);
                return;
            }

            $('#resenasGrid').html(
                lista.map((r, i) => renderCard(r, Math.min(i * 0.04, 0.36))).join('')
            );

            actualizarBarra(lista.length);
            renderPaginacion();
        },
        error: function() {
            mostrarVacio(T.error_conexion);
        }
    });
}

function mostrarVacio(msg) {
    $('#resenasGrid').html(`
        <div class="estado-vacio">
            <div class="ico"><i class="fas fa-star-half-alt"></i></div>
            <h3>${msg}</h3>
            <p style="font-size:.82rem;">${T.probar_otro}</p>
        </div>`);
}

function actualizarBarra(mostrando) {
    const ini = (RS.pagina - 1) * RS.porPagina + 1;
    const fin = ini + mostrando - 1;
    $('#txtMostrando').text(mostrando > 0 ? `${ini}–${fin}` : '0');
    $('#txtTotal').text(RS.total);
}

// ════════════════════════════════════════════════════════
//  PAGINACIÓN
// ════════════════════════════════════════════════════════
function renderPaginacion() {
    const { paginas, pagina } = RS;
    if (paginas <= 1) { $('#pagWrap').html(''); return; }

    let html = `<button class="pag-btn pag-btn-wide" id="pagPrev" ${pagina===1?'disabled':''}><i class="fas fa-chevron-left"></i></button>`;
    const vent = 2;
    for (let i = 1; i <= paginas; i++) {
        if (i === 1 || i === paginas || (i >= pagina - vent && i <= pagina + vent)) {
            html += `<button class="pag-btn ${i===pagina?'active':''}" data-pag="${i}">${i}</button>`;
        } else if (i === pagina - vent - 1 || i === pagina + vent + 1) {
            html += `<span class="pag-dots">…</span>`;
        }
    }
    html += `<button class="pag-btn pag-btn-wide" id="pagNext" ${pagina===paginas?'disabled':''}><i class="fas fa-chevron-right"></i></button>`;
    $('#pagWrap').html(html);

    $('#pagPrev').on('click', () => { if(RS.pagina>1){ RS.pagina--; cargarResenas(); scrollUp(); } });
    $('#pagNext').on('click', () => { if(RS.pagina<RS.paginas){ RS.pagina++; cargarResenas(); scrollUp(); } });
    $('[data-pag]').on('click', function(){ RS.pagina = parseInt($(this).data('pag')); cargarResenas(); scrollUp(); });
}

function scrollUp() {
    window.scrollTo({ top: document.querySelector('.hero').offsetHeight - 60, behavior: 'smooth' });
}

// ════════════════════════════════════════════════════════
//  MODAL EDITAR
// ════════════════════════════════════════════════════════
let editCalifActual = 0;

function abrirEditar(btn) {
    const card = $(btn).closest('.res-card');
    const id   = card.data('resena-id');
    const calif= parseFloat(card.data('calificacion') || 0);
    const com  = card.data('comentario') || '';

    $('#editResenaId').val(id);
    $('#editComentario').val(com);
    editCalifActual = Math.round(calif);
    actualizarEditStars(editCalifActual);
    $('#modalEditarBg').addClass('show');
}

function cerrarEditar() {
    $('#modalEditarBg').removeClass('show');
    editCalifActual = 0;
}

function actualizarEditStars(val) {
    $('#editStars i').each(function() {
        const v = parseInt($(this).data('val'));
        $(this).toggleClass('lit', v <= val);
    });
}

// Estrellas editables hover / click
$('#editStars').on('mouseover', 'i', function() {
    const v = parseInt($(this).data('val'));
    actualizarEditStars(v);
}).on('mouseleave', function() {
    actualizarEditStars(editCalifActual);
}).on('click', 'i', function() {
    editCalifActual = parseInt($(this).data('val'));
    actualizarEditStars(editCalifActual);
});

$('#btnCancelEdit').on('click', cerrarEditar);
$('#modalEditarBg').on('click', function(e) {
    if ($(e.target).is('#modalEditarBg')) cerrarEditar();
});

$('#btnSaveEdit').on('click', function() {
    const resenaId   = parseInt($('#editResenaId').val());
    const comentario = $('#editComentario').val().trim();

    if (!editCalifActual) {
        toastr.warning(T.toast_selecciona_calif);
        return;
    }

    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>' + T.modal_guardar + '…');

    $.ajax({
        url: 'index.php?c=resenas&a=Editar',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            resena_id:   resenaId,
            calificacion:editCalifActual,
            comentario:  comentario,
        }),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toastr.success(T.toast_actualizada);
                cerrarEditar();
                cargarResenas();
            } else {
                toastr.error(res.message || T.toast_error_guardar);
            }
        },
        error: function() { toastr.error(T.toast_conexion); },
        complete: function() {
            $('#btnSaveEdit').prop('disabled', false).html('<i class="fas fa-save me-2"></i>' + T.modal_guardar);
        }
    });
});

// ════════════════════════════════════════════════════════
//  ELIMINAR
// ════════════════════════════════════════════════════════
function eliminarResena(btn) {
    const card = $(btn).closest('.res-card');
    const id   = card.data('resena-id');
    const nombre = card.find('.res-atraccion').text().trim();

    Swal.fire({
        title: T.eliminar_titulo,
        text: T.eliminar_texto.replace('%s', nombre),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash-alt me-2"></i>' + T.eliminar_confirmar,
        cancelButtonText: T.eliminar_cancelar,
        background: '#1e2140',
        color: '#e2e8f0',
        backdrop: 'rgba(15,22,41,.8)',
        customClass: { popup: 'rounded-4' }
    }).then(result => {
        if (!result.isConfirmed) return;

        $.post('index.php?c=resenas&a=Eliminar', { id }, function(res) {
            if (res.success) {
                toastr.success(T.toast_eliminada);
                card.css({ transition:'all .4s', transform:'scale(.85)', opacity:'0' });
                setTimeout(() => { card.remove(); if (!$('.res-card').length) cargarResenas(); }, 400);
            } else {
                toastr.error(res.message || T.toast_error_eliminar);
            }
        }, 'json').fail(() => toastr.error(T.toast_conexion));
    });
}

// ════════════════════════════════════════════════════════
//  VER DETALLE DE ATRACCIÓN
// ════════════════════════════════════════════════════════
function verDetalle(id) {
    const elAnt = document.getElementById('modalDetalleAtraccion');
    if (elAnt) { const i = bootstrap.Modal.getInstance(elAnt); if(i) i.dispose(); elAnt.remove(); }

    $('#contenedorModalDetalle').html(`
        <div class="modal fade" id="modalDetalleAtraccion" data-bs-backdrop="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content p-5 text-center"
                     style="background:#1e2140;border:1px solid rgba(102,126,234,.2);border-radius:16px;">
                    <div class="spinner-border mx-auto mb-3"
                         style="color:var(--primary);width:2.5rem;height:2.5rem;"></div>
                    <p class="text-muted mb-0">${T.cargando}</p>
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
        error: function() { sp.hide(); toastr.error(T.error_conexion); }
    });
}

// ════════════════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════════════════
$(document).ready(function() {

    toastr.options = {
        positionClass: 'toast-bottom-right',
        timeOut: 3000, closeButton: true,
    };

    // Tags
    $('.tag-btn').on('click', function() {
        $('.tag-btn').removeClass('active');
        $(this).addClass('active');
        RS.tag    = $(this).data('tag');
        RS.pagina = 1;
        cargarResenas();
    });

    // Buscador
    let timerSearch;
    $('#searchInput').on('input', function() {
        const val = $(this).val();
        $('#searchClear').toggle(val.length > 0);
        clearTimeout(timerSearch);
        timerSearch = setTimeout(() => {
            RS.busqueda = val;
            RS.pagina   = 1;
            cargarResenas();
        }, 350);
    });

    $('#searchClear').on('click', function() {
        $('#searchInput').val('').trigger('input');
    });

    $('#searchBtn').on('click', function() {
        RS.busqueda = $('#searchInput').val();
        RS.pagina   = 1;
        cargarResenas();
    });

    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) { RS.busqueda = $(this).val(); RS.pagina = 1; cargarResenas(); }
    });

    // Ordenamiento
    $('#sortSelect').on('change', function() {
        RS.orden  = $(this).val();
        RS.pagina = 1;
        cargarResenas();
    });

    // Carga inicial
    cargarResenas();
});
</script>
</body>
</html>