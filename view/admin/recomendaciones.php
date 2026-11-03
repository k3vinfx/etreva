<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if ((int)($_SESSION['rol'] ?? 0) !== 2) {
    header('Location: index.php?c=login&a=Index'); exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Etreva Admin — Recomendaciones</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/menu.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<style>
:root{
    --bg:#0f0f1a; --surface:#161625; --card:#1c1c32;
    --border:rgba(229,90,43,.15); --text:#f0f0f5; --muted:rgba(240,240,245,.45);
    --orange:#e55a2b; --amber:#f59e0b; --green:#22c55e;
    --blue:#38bdf8; --red:#ef4444; --purple:#a78bfa; --teal:#2dd4bf;
}
*{box-sizing:border-box;}
html,body{background:var(--bg)!important;color:var(--text)!important;min-height:100vh;
    font-family:'Segoe UI',system-ui,sans-serif;margin:0;padding:0;}
main{margin-left:280px;min-height:100vh;background:var(--bg);transition:margin-left .3s;}
main.min-main{margin-left:85px;}

.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:14px 32px;
    display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.topbar h1{font-size:1.1rem;font-weight:800;color:#fff;margin:0;display:flex;align-items:center;gap:10px;}
.topbar h1 span{background:linear-gradient(135deg,var(--orange),#ff9a6c);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.topbar-right{display:flex;align-items:center;gap:10px;}
.btn-icon{background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:9px;
    width:36px;height:36px;display:flex;align-items:center;justify-content:center;
    color:var(--muted);font-size:.9rem;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-icon:hover{background:rgba(229,90,43,.12);color:var(--orange);}

.body{padding:24px 32px 60px;display:flex;flex-direction:column;gap:18px;}

/* STAT STRIP */
.stat-strip{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;}
.stat-pill{background:var(--card);border:1px solid var(--border);border-radius:14px;
    padding:14px 16px;display:flex;align-items:center;gap:12px;transition:border-color .2s;}
.stat-pill:hover{border-color:rgba(229,90,43,.35);}
.stat-pill-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;
    justify-content:center;font-size:.9rem;flex-shrink:0;}
.stat-pill-txt{font-size:1.3rem;font-weight:900;color:#fff;line-height:1;}
.stat-pill-lbl{font-size:.7rem;color:var(--muted);margin-top:2px;}

/* FILTROS */
.filtros-wrap{background:var(--card);border:1px solid var(--border);border-radius:14px;
    padding:14px 18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.filtros-wrap input[type=text],.filtros-wrap input[type=number],.filtros-wrap select{
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;
    padding:8px 12px;color:var(--text);font-size:.82rem;outline:none;transition:border-color .2s;}
.filtros-wrap input[type=text]{flex:1;min-width:200px;}
.filtros-wrap input[type=number]{width:110px;}
.filtros-wrap input:focus,.filtros-wrap select:focus{border-color:var(--orange);}
.filtros-wrap select option{background:#1c1c32;}
.btn-filtrar{background:linear-gradient(135deg,var(--orange),#ff9a6c);color:#fff;
    border:none;border-radius:9px;padding:8px 18px;font-size:.82rem;font-weight:700;
    cursor:pointer;transition:all .2s;}
.btn-filtrar:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(229,90,43,.35);}
.btn-nuevo{background:rgba(34,197,94,.14);color:var(--green);border:1px solid rgba(34,197,94,.3);
    border-radius:9px;padding:8px 16px;font-size:.82rem;font-weight:700;cursor:pointer;
    display:flex;align-items:center;gap:6px;transition:all .2s;white-space:nowrap;}
.btn-nuevo:hover{background:rgba(34,197,94,.25);transform:translateY(-1px);}
.btn-export{padding:8px 14px;border-radius:9px;border:none;font-size:.78rem;font-weight:700;
    cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s;white-space:nowrap;}
.btn-excel{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25);}
.btn-excel:hover{background:rgba(34,197,94,.22);transform:translateY(-1px);}
.export-sep{width:1px;height:28px;background:rgba(255,255,255,.08);margin:0 2px;}

/* TABLA */
.tabla-wrap{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;}
.adm-table{width:100%;border-collapse:collapse;font-size:.81rem;}
.adm-table thead th{background:rgba(255,255,255,.03);color:var(--muted);font-weight:600;
    font-size:.68rem;letter-spacing:1px;text-transform:uppercase;
    padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.07);white-space:nowrap;}
.adm-table thead th.sortable{cursor:pointer;user-select:none;}
.adm-table thead th.sortable:hover{color:var(--text);}
.adm-table thead th.sort-active{color:var(--orange);}
.adm-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;}
.adm-table tbody tr:hover{background:rgba(229,90,43,.04);cursor:pointer;}
.adm-table tbody td{padding:11px 14px;color:var(--text);vertical-align:middle;}

/* BADGES */
.badge{font-size:.63rem;font-weight:700;padding:3px 8px;border-radius:20px;white-space:nowrap;display:inline-flex;align-items:center;gap:4px;}
.b-positivo  {background:rgba(34,197,94,.12); color:var(--green);}
.b-negativo  {background:rgba(239,68,68,.1);  color:var(--red);}
.b-sin       {background:rgba(255,255,255,.07);color:var(--muted);}

/* SCORE BAR */
.score-bar-wrap{display:flex;align-items:center;gap:7px;}
.score-bar{flex:1;height:5px;background:rgba(255,255,255,.08);border-radius:3px;overflow:hidden;min-width:60px;}
.score-bar-fill{height:100%;border-radius:3px;}
.score-num{font-size:.7rem;font-weight:800;color:var(--text);white-space:nowrap;}

/* ACCIONES */
.acciones{display:flex;gap:5px;}
.btn-sm{padding:4px 9px;border-radius:7px;font-size:.7rem;font-weight:700;border:none;
    cursor:pointer;display:inline-flex;align-items:center;gap:3px;transition:all .18s;white-space:nowrap;}
.btn-edit   {background:rgba(56,189,248,.1); color:var(--blue);}
.btn-edit:hover{background:rgba(56,189,248,.22);transform:translateY(-1px);}
.btn-reset  {background:rgba(167,139,250,.1);color:var(--purple);}
.btn-reset:hover{background:rgba(167,139,250,.22);transform:translateY(-1px);}
.btn-del    {background:rgba(239,68,68,.08); color:var(--red);}
.btn-del:hover{background:rgba(239,68,68,.2);transform:translateY(-1px);}

/* PAGINACIÓN */
.paginacion{display:flex;align-items:center;justify-content:space-between;
    padding:12px 16px;border-top:1px solid rgba(255,255,255,.06);font-size:.77rem;color:var(--muted);}
.pag-btns{display:flex;gap:4px;}
.pag-btn{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:7px;
    padding:5px 10px;color:var(--muted);cursor:pointer;font-size:.73rem;transition:all .18s;}
.pag-btn:hover,.pag-btn.active{background:rgba(229,90,43,.15);color:var(--orange);border-color:rgba(229,90,43,.3);}
.pag-btn:disabled{opacity:.3;cursor:not-allowed;}

/* SKELETON */
.skeleton{background:linear-gradient(90deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.09) 50%,rgba(255,255,255,.04) 100%);
    background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:8px;}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.empty-state{text-align:center;padding:50px 20px;color:var(--muted);}
.empty-state i{font-size:2.5rem;margin-bottom:12px;display:block;opacity:.3;}

/* ─── MODAL ─── */
.modal-overlay{display:none;position:fixed;inset:0;z-index:1000;background:rgba(8,8,18,.88);
    backdrop-filter:blur(6px);align-items:flex-start;justify-content:center;padding:20px;overflow-y:auto;}
.modal-overlay.show{display:flex;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:20px;
    width:100%;max-width:720px;animation:popIn .22s ease;margin:auto;}
@keyframes popIn{from{transform:scale(.93) translateY(14px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
.modal-header{padding:18px 22px 14px;border-bottom:1px solid rgba(255,255,255,.07);
    display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;
    background:var(--card);z-index:1;border-radius:20px 20px 0 0;}
.modal-header h3{font-size:.98rem;font-weight:800;color:#fff;margin:0;display:flex;align-items:center;gap:8px;}
.modal-close{background:none;border:none;color:var(--muted);font-size:1rem;cursor:pointer;
    width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;transition:all .18s;}
.modal-close:hover{background:rgba(255,255,255,.08);color:#fff;}
.modal-body{padding:20px 22px;}
.modal-footer{padding:12px 22px 18px;border-top:1px solid rgba(255,255,255,.07);
    display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;}

/* FORM */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-group.full{grid-column:1/-1;}
.form-group label{font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;font-weight:700;}
.form-group input,.form-group select,.form-group textarea{
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;
    padding:9px 12px;color:var(--text);font-size:.83rem;outline:none;transition:border-color .2s;width:100%;}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--orange);}
.form-group textarea{resize:vertical;min-height:90px;font-family:inherit;}
.form-group select option{background:#1c1c32;}
.form-sep{font-size:.67rem;color:var(--orange);text-transform:uppercase;letter-spacing:1.5px;font-weight:700;
    margin:14px 0 6px;display:flex;align-items:center;gap:8px;grid-column:1/-1;}
.form-sep::after{content:'';flex:1;height:1px;background:rgba(229,90,43,.2);}

/* SEARCH INLINE */
.search-select-wrap{position:relative;width:100%;}
.search-select-wrap input{width:100%;}
.search-dropdown{position:absolute;top:calc(100% + 4px);left:0;right:0;
    background:var(--card);border:1px solid rgba(255,255,255,.12);border-radius:10px;
    max-height:200px;overflow-y:auto;z-index:200;display:none;box-shadow:0 8px 24px rgba(0,0,0,.4);}
.search-dropdown.show{display:block;}
.search-drop-item{padding:9px 12px;font-size:.8rem;cursor:pointer;transition:background .15s;
    display:flex;align-items:center;gap:8px;}
.search-drop-item:hover{background:rgba(229,90,43,.15);}
.search-drop-item .drop-id{color:var(--muted);font-size:.68rem;margin-left:auto;}

/* FEEDBACK MODAL */
.info-box{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);
    border-radius:10px;padding:10px 14px;font-size:.8rem;color:var(--muted);}
.info-box strong{color:var(--text);}

/* BOTONES MODAL */
.btn-guardar{background:linear-gradient(135deg,var(--orange),#ff9a6c);color:#fff;border:none;
    border-radius:9px;padding:9px 22px;font-size:.84rem;font-weight:700;cursor:pointer;transition:all .2s;
    display:flex;align-items:center;gap:7px;}
.btn-guardar:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(229,90,43,.4);}
.btn-cancelar{background:rgba(255,255,255,.06);color:var(--muted);border:1px solid rgba(255,255,255,.1);
    border-radius:9px;padding:9px 16px;font-size:.84rem;cursor:pointer;transition:all .2s;}
.btn-cancelar:hover{color:var(--text);background:rgba(255,255,255,.1);}

/* ─── CHIPS DINÁMICOS ─── */
.dyn-hint{background:rgba(245,158,11,.08);border:1px dashed rgba(245,158,11,.3);
    border-radius:10px;padding:12px 14px;font-size:.77rem;color:var(--amber);
    display:flex;align-items:center;gap:8px;}
.dyn-hint i{font-size:.9rem;flex-shrink:0;}
.dyn-hint.error{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.3);color:var(--red);}
.dyn-skel-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.dyn-skel-block{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);
    border-radius:12px;padding:12px 14px;}
.skel-bar{background:linear-gradient(90deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.09) 50%,rgba(255,255,255,.04) 100%);
    background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:6px;}
.groups-dyn-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.groups-dyn-grid .pg-fullwidth{grid-column:1/-1;}
.param-group{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);
    border-radius:12px;padding:12px 14px;transition:border-color .2s;}
.param-group:hover{border-color:rgba(255,255,255,.13);}
.param-group-label{font-size:.63rem;color:var(--muted);text-transform:uppercase;
    letter-spacing:1px;font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:6px;}
.param-group-label .pg-badge{margin-left:auto;font-size:.58rem;padding:1px 7px;border-radius:10px;
    background:rgba(255,255,255,.06);color:var(--muted);text-transform:none;letter-spacing:0;}
.chips-row{display:flex;flex-wrap:wrap;gap:6px;}
.chips-row-empty{font-size:.7rem;color:var(--muted);font-style:italic;padding:2px 0;}
.chip{padding:5px 11px;border-radius:20px;font-size:.72rem;font-weight:600;cursor:pointer;
    border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);
    color:rgba(240,240,245,.5);transition:all .18s;user-select:none;line-height:1.4;}
.chip:hover{border-color:rgba(229,90,43,.35);color:var(--text);background:rgba(229,90,43,.07);}
.chip:active{transform:scale(.95);}
.chip.sel-interes      {background:rgba(99,102,241,.2); color:#a5b4fc;border-color:rgba(99,102,241,.5);}
.chip.sel-dificultad   {background:rgba(239,68,68,.15); color:#fca5a5;border-color:rgba(239,68,68,.5);}
.chip.sel-duracion     {background:rgba(56,189,248,.15);color:#7dd3fc;border-color:rgba(56,189,248,.5);}
.chip.sel-presupuesto  {background:rgba(245,158,11,.15);color:#fcd34d;border-color:rgba(245,158,11,.5);}
.chip.sel-temporada    {background:rgba(45,212,191,.15);color:#5eead4;border-color:rgba(45,212,191,.5);}
.chip.sel-clasificacion{background:rgba(167,139,250,.2);color:#c4b5fd;border-color:rgba(167,139,250,.5);}
.chip.sel-custom       {background:rgba(229,90,43,.18); color:#ff9a6c;border-color:rgba(229,90,43,.5);}
.chip.sel-ctx          {background:rgba(34,197,94,.16); color:#86efac;border-color:rgba(34,197,94,.5);}
.json-preview-toggle{background:none;border:1px solid rgba(255,255,255,.1);border-radius:8px;
    padding:5px 12px;font-size:.67rem;color:var(--muted);cursor:pointer;
    display:inline-flex;align-items:center;gap:5px;transition:all .18s;margin-top:6px;}
.json-preview-toggle:hover{border-color:var(--orange);color:var(--orange);}
.json-preview-box{background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.08);
    border-radius:9px;padding:10px 12px;font-size:.67rem;font-family:'Courier New',monospace;
    color:var(--teal);white-space:pre-wrap;word-break:break-all;max-height:100px;overflow-y:auto;
    display:none;margin-top:5px;}
.json-preview-box.show{display:block;}

@media print{
    .topbar,.stat-strip,.filtros-wrap,.acciones,.pag-btns,#adminMenu,button{display:none!important;}
    main{margin-left:0!important;}
    body,html{background:#fff!important;color:#000!important;}
}
@media(max-width:900px){
    main,main.min-main{margin-left:0;}
    .body{padding:12px 12px 40px;}
    .stat-strip{grid-template-columns:repeat(2,1fr);}
    .form-grid{grid-template-columns:1fr;}
}
</style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main>

<div class="topbar">
    <h1>
        <i class="fas fa-robot" style="color:var(--orange);"></i>
        Gestión de <span>Recomendaciones</span>
    </h1>
    <div class="topbar-right">
        <div class="btn-icon" id="btnRefresh" title="Actualizar"><i class="fas fa-rotate-right"></i></div>
        <a class="btn-icon" href="index.php?c=login&a=Logout" title="Salir" style="color:#ef4444;">
            <i class="fas fa-right-from-bracket"></i>
        </a>
    </div>
</div>

<div class="body">

    <!-- STATS -->
    <div class="stat-strip">
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(229,90,43,.14);">
                <i class="fas fa-list" style="color:var(--orange);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-total">—</div><div class="stat-pill-lbl">Total</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(34,197,94,.12);">
                <i class="fas fa-thumbs-up" style="color:var(--green);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-pos">—</div><div class="stat-pill-lbl">Feedback positivo</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(239,68,68,.1);">
                <i class="fas fa-thumbs-down" style="color:var(--red);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-neg">—</div><div class="stat-pill-lbl">Feedback negativo</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(255,255,255,.06);">
                <i class="fas fa-circle-question" style="color:var(--muted);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-sin">—</div><div class="stat-pill-lbl">Sin feedback</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(56,189,248,.1);">
                <i class="fas fa-chart-line" style="color:var(--blue);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-score">—</div><div class="stat-pill-lbl">Score promedio</div></div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filtros-wrap">
        <input type="text" id="inputBusqueda" placeholder="🔍  Buscar por nombre de atracción o modelo…" oninput="debounceSearch()">
        <select id="selModelo" onchange="resetPage();cargar()">
            <option value="">Todos los modelos</option>
            <?php foreach ($modelos as $m): ?>
                <option value="<?= $m['id'] ?>">
                    <?= htmlspecialchars($m['nombre'] ?? 'Modelo '.$m['id']) ?> (v<?= htmlspecialchars($m['version'] ?? '?') ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <select id="selFeedback" onchange="resetPage();cargar()">
            <option value="">Todo feedback</option>
            <option value="positivo">👍 Positivos</option>
            <option value="negativo">👎 Negativos</option>
            <option value="sin_feedback">⬜ Sin feedback</option>
        </select>
        <input type="number" id="inputScoreMin" placeholder="Score mín." min="0" max="100" step="1" onchange="resetPage();cargar()">
        <button class="btn-filtrar" onclick="resetPage();cargar()"><i class="fas fa-filter"></i> Filtrar</button>
        <div class="export-sep"></div>
        <button class="btn-export btn-excel" onclick="exportarExcel()"><i class="fas fa-file-excel"></i> Excel</button>
        <button class="btn-export btn-excel" style="background:rgba(239,68,68,.1);color:var(--red);border-color:rgba(239,68,68,.25);" onclick="window.print()">
            <i class="fas fa-file-pdf"></i> PDF
        </button>
        <div class="export-sep"></div>
        <button class="btn-nuevo" onclick="abrirModalCrear()"><i class="fas fa-plus"></i> Nueva recomendación</button>
    </div>

    <!-- TABLA -->
    <div class="tabla-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th class="sortable sort-active" data-col="id" onclick="toggleSort('id')"># <i class="fas fa-sort-up" id="ico-id"></i></th>
                    <th class="sortable" data-col="atraccion" onclick="toggleSort('atraccion')">Atracción <i class="fas fa-sort" id="ico-atraccion"></i></th>
                    <th class="sortable" data-col="modelo" onclick="toggleSort('modelo')">Modelo MR <i class="fas fa-sort" id="ico-modelo"></i></th>
                    <th class="sortable" data-col="score" onclick="toggleSort('score')">Score <i class="fas fa-sort" id="ico-score"></i></th>
                    <th>Feedback</th>
                    <th class="sortable" data-col="generacion" onclick="toggleSort('generacion')">Generado <i class="fas fa-sort" id="ico-generacion"></i></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <tr><td colspan="7" style="padding:30px;">
                    <div class="skeleton" style="height:18px;"></div>
                </td></tr>
            </tbody>
        </table>
        <div class="paginacion">
            <span id="pagInfo">Cargando…</span>
            <div class="pag-btns" id="pagBtns"></div>
        </div>
    </div>

</div>
</main>

<!-- ─── MODAL ─── -->
<div class="modal-overlay" id="modalForm">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo">
                <i class="fas fa-plus" style="color:var(--green);"></i> Nueva Recomendación
            </h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="fId">

            <div class="form-grid">
                <div class="form-sep"><i class="fas fa-link"></i> Relaciones</div>

                <div class="form-group">
                    <label>Atracción *</label>
                    <div class="search-select-wrap">
                        <input type="text" id="fAtraccionSearch" placeholder="Buscar atracción…"
                               oninput="filtrarAtraccion(this.value)" autocomplete="off">
                        <input type="hidden" id="fAtraccionId">
                        <div class="search-dropdown" id="dropAtraccion"></div>
                    </div>
                    <div id="lblAtraccion" style="font-size:.68rem;color:var(--teal);margin-top:3px;"></div>
                </div>

                <div class="form-group">
                    <label>Modelo MR *</label>
                    <select id="fModeloId" onchange="onModeloChange()">
                        <option value="">— Seleccioná un modelo activo —</option>
                    </select>
                    <div id="lblModelo" style="font-size:.68rem;color:var(--teal);margin-top:3px;"></div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-sep"><i class="fas fa-chart-bar"></i> Métricas</div>
                <div class="form-group full">
                    <label>Score de relevancia (0 – 100)</label>
                    <input type="range" id="fScoreRange" min="0" max="100" step="1" value="50"
                           oninput="document.getElementById('fScoreNum').value=this.value"
                           style="accent-color:var(--orange);width:100%;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;">
                        <span style="font-size:.7rem;color:var(--muted);">0 — Sin relevancia</span>
                        <input type="number" id="fScoreNum" min="0" max="100" value="50"
                               oninput="document.getElementById('fScoreRange').value=this.value"
                               style="width:70px;text-align:center;">
                        <span style="font-size:.7rem;color:var(--muted);">100 — Máxima</span>
                    </div>
                </div>
            </div>

            <!-- RAZÓN — dinámica -->
            <div class="form-grid" style="margin-top:4px;">
                <div class="form-sep"><i class="fas fa-brain"></i> Razón de Recomendación</div>
                <div class="form-group full">
                    <div id="razonSection">
                        <div class="dyn-hint">
                            <i class="fas fa-circle-arrow-up"></i>
                            Seleccioná un modelo MR para ver los parámetros disponibles.
                        </div>
                    </div>
                    <button type="button" class="json-preview-toggle" id="btnPreviewRazon"
                            onclick="toggleJsonPreview('previewRazon')" style="display:none;">
                        <i class="fas fa-code"></i> Ver JSON generado
                    </button>
                    <div class="json-preview-box" id="previewRazon"></div>
                </div>
            </div>

            <!-- CONTEXTO — dinámico -->
            <div class="form-grid" style="margin-top:4px;">
                <div class="form-sep"><i class="fas fa-circle-info"></i> Contexto de Recomendación</div>
                <div class="form-group full">
                    <div id="contextoSection">
                        <div class="dyn-hint">
                            <i class="fas fa-circle-arrow-up"></i>
                            Seleccioná un modelo MR para ver los contextos disponibles.
                        </div>
                    </div>
                    <button type="button" class="json-preview-toggle" id="btnPreviewCtx"
                            onclick="toggleJsonPreview('previewCtx')" style="display:none;">
                        <i class="fas fa-code"></i> Ver JSON generado
                    </button>
                    <div class="json-preview-box" id="previewCtx"></div>
                </div>
            </div>

            <!-- Feedback solo lectura -->
            <div id="feedbackInfo" style="display:none;margin-top:6px;">
                <div class="form-sep"><i class="fas fa-comment"></i> Feedback (solo lectura)</div>
                <div class="info-box" id="feedbackBox">—</div>
            </div>

        </div>
        <div class="modal-footer">
            <button class="btn-cancelar" onclick="cerrarModal()"><i class="fas fa-xmark"></i> Cancelar</button>
            <button class="btn-guardar" id="btnGuardar" onclick="guardar()">
                <i class="fas fa-floppy-disk"></i> Guardar
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ═══════════════════════════════════════════════════════════════════════════
//  ESTADO TABLA
// ═══════════════════════════════════════════════════════════════════════════
const pag   = { page: 1, total: 0, pages: 1 };
let sortCol = 'id', sortOrd = 'desc';
let _dTimer = null;
let _atracciones = []; // para el search dropdown

function debounceSearch() {
    clearTimeout(_dTimer);
    _dTimer = setTimeout(() => { resetPage(); cargar(); }, 380);
}
function resetPage() { pag.page = 1; }

// ─── Escape HTML ─────────────────────────────────────────────────────────
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')
                        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
const escHtml = esc; // alias

// ═══════════════════════════════════════════════════════════════════════════
//  SORT
// ═══════════════════════════════════════════════════════════════════════════
function toggleSort(col) {
    if (sortCol === col) sortOrd = sortOrd === 'asc' ? 'desc' : 'asc';
    else { sortCol = col; sortOrd = 'asc'; }

    document.querySelectorAll('.sortable').forEach(th => {
        th.classList.remove('sort-active');
        const ico = document.getElementById('ico-' + th.dataset.col);
        if (ico) ico.className = 'fas fa-sort';
    });
    const active = document.querySelector(`[data-col="${col}"]`);
    if (active) active.classList.add('sort-active');
    const ico = document.getElementById('ico-' + col);
    if (ico) ico.className = sortOrd === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    resetPage(); cargar();
}

// ═══════════════════════════════════════════════════════════════════════════
//  CARGAR TABLA
// ═══════════════════════════════════════════════════════════════════════════
function cargar() {
    const q        = encodeURIComponent(document.getElementById('inputBusqueda').value.trim());
    const modeloId = document.getElementById('selModelo').value;
    const feedback = document.getElementById('selFeedback').value;
    const scoreMin = document.getElementById('inputScoreMin').value;

    document.getElementById('tbody').innerHTML =
        '<tr><td colspan="7" style="padding:28px;"><div class="skeleton" style="height:18px;"></div></td></tr>';

    const url = `index.php?c=adminrecomendaciones&a=Listar`
        + `&q=${q}&modelo_id=${modeloId}&feedback=${feedback}`
        + `&score_min=${scoreMin}&sort=${sortCol}&orden=${sortOrd}&page=${pag.page}`;

    fetch(url)
        .then(r => r.json())
        .then(d => {
            if (!d.success) { renderVacia(d.message || 'Error al cargar'); return; }
            pag.total = d.total;
            pag.pages = d.paginas;
            renderTabla(d.datos);
            renderPag();
        })
        .catch(() => renderVacia('Error de conexión'));
}

// ═══════════════════════════════════════════════════════════════════════════
//  RENDER TABLA
// ═══════════════════════════════════════════════════════════════════════════
function feedbackBadge(fb) {
    if (!fb || !Object.keys(fb).length) return '<span class="badge b-sin"><i class="fas fa-minus"></i> Sin feedback</span>';
    if (fb.tipo === 'positivo') return '<span class="badge b-positivo"><i class="fas fa-thumbs-up"></i> Positivo</span>';
    if (fb.tipo === 'negativo') return '<span class="badge b-negativo"><i class="fas fa-thumbs-down"></i> Negativo</span>';
    return '<span class="badge b-sin">—</span>';
}

function scoreColor(pct) {
    if (pct >= 70) return 'var(--green)';
    if (pct >= 40) return 'var(--amber)';
    return 'var(--red)';
}

function renderTabla(items) {
    if (!items || !items.length) { renderVacia('Sin recomendaciones con esos filtros'); return; }

    const rows = items.map(r => {
        const pct   = Math.round(parseFloat(r.score_relevancia || 0) * 100);
        const color = scoreColor(pct);
        const fecha = (r.fecha_generacion || r.created_at || '').slice(0, 10);

        return `<tr onclick="abrirModalEditar(${r.id})">
            <td style="color:var(--muted);font-size:.7rem;">${r.id}</td>
            <td>
                <div style="font-weight:700;font-size:.83rem;">${esc(r.atraccion_nombre||'—')}</div>
                <div style="font-size:.65rem;color:var(--muted);">${esc(r.atraccion_ciudad||'')}</div>
            </td>
            <td>
                <div style="font-size:.78rem;color:var(--purple);font-weight:600;">${esc(r.modelo_nombre||'—')}</div>
                <div style="font-size:.65rem;color:var(--muted);">v${esc(r.modelo_version||'?')}</div>
            </td>
            <td>
                <div class="score-bar-wrap">
                    <div class="score-bar">
                        <div class="score-bar-fill" style="width:${pct}%;background:${color};"></div>
                    </div>
                    <span class="score-num" style="color:${color};">${pct}%</span>
                </div>
            </td>
            <td>${feedbackBadge(r.feedback_usuario)}</td>
            <td style="font-size:.72rem;color:var(--muted);">${fecha}</td>
            <td onclick="event.stopPropagation();">
                <div class="acciones">
                    <button class="btn-sm btn-edit" onclick="abrirModalEditar(${r.id})" title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn-sm btn-reset" onclick="resetFeedback(${r.id})" title="Limpiar feedback">
                        <i class="fas fa-rotate-left"></i>
                    </button>
                    <button class="btn-sm btn-del" onclick="eliminar(${r.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');

    document.getElementById('tbody').innerHTML = rows;
}

function renderVacia(msg) {
    document.getElementById('tbody').innerHTML =
        `<tr><td colspan="7"><div class="empty-state"><i class="fas fa-robot"></i><p>${msg}</p></div></td></tr>`;
}

// ═══════════════════════════════════════════════════════════════════════════
//  PAGINACIÓN
// ═══════════════════════════════════════════════════════════════════════════
function renderPag() {
    const { page, total, pages } = pag;
    document.getElementById('pagInfo').textContent = `Página ${page} de ${pages} · ${total} registros`;
    let b = `<button class="pag-btn" onclick="irPag(${page-1})" ${page<=1?'disabled':''}>&lsaquo;</button>`;
    const s = Math.max(1, page-2), e = Math.min(pages, page+2);
    for (let i = s; i <= e; i++)
        b += `<button class="pag-btn ${i===page?'active':''}" onclick="irPag(${i})">${i}</button>`;
    b += `<button class="pag-btn" onclick="irPag(${page+1})" ${page>=pages?'disabled':''}>&rsaquo;</button>`;
    document.getElementById('pagBtns').innerHTML = b;
}
function irPag(p) { if (p < 1 || p > pag.pages) return; pag.page = p; cargar(); }

// ═══════════════════════════════════════════════════════════════════════════
//  STATS
// ═══════════════════════════════════════════════════════════════════════════
function cargarStats() {
    fetch('index.php?c=adminrecomendaciones&a=Stats')
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            const s = d.stats;
            document.getElementById('st-total').textContent = s.total       || 0;
            document.getElementById('st-pos').textContent   = s.positivos   || 0;
            document.getElementById('st-neg').textContent   = s.negativos   || 0;
            document.getElementById('st-sin').textContent   = s.sin_feedback|| 0;
            document.getElementById('st-score').textContent =
                s.score_promedio ? (parseFloat(s.score_promedio) * 100).toFixed(0) + '%' : '—';
        }).catch(()=>{});
}

// ═══════════════════════════════════════════════════════════════════════════
//  SELECTORES (atracciones + modelos para el modal)
// ═══════════════════════════════════════════════════════════════════════════
function cargarSelectores() {
    fetch('index.php?c=adminrecomendaciones&a=Selectores')
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            _atracciones = d.atracciones || [];

            // Llenar select modelos del modal
            const sel = document.getElementById('fModeloId');
            sel.innerHTML = '<option value="">— Seleccioná un modelo activo —</option>';
            (d.modelos || []).forEach(m => {
                const op = document.createElement('option');
                op.value       = m.id;
                op.textContent = `${m.nombre || 'Modelo '+m.id} (v${m.version||'?'})`;
                sel.appendChild(op);
            });
        }).catch(()=>{});
}

// ═══════════════════════════════════════════════════════════════════════════
//  SEARCH ATRACCIÓN (inline dropdown)
// ═══════════════════════════════════════════════════════════════════════════
function filtrarAtraccion(q) {
    const drop = document.getElementById('dropAtraccion');
    if (!q.trim()) { drop.classList.remove('show'); drop.innerHTML = ''; return; }

    const filtrados = _atracciones.filter(a =>
        a.nombre.toLowerCase().includes(q.toLowerCase()) ||
        (a.ciudad||'').toLowerCase().includes(q.toLowerCase())
    ).slice(0, 12);

    if (!filtrados.length) { drop.classList.remove('show'); return; }

    drop.innerHTML = filtrados.map(a =>
        `<div class="search-drop-item" onclick="seleccionarAtraccion(${a.id},'${esc(a.nombre)}','${esc(a.ciudad||'')}')">
            <div>
                <div style="font-weight:600;">${esc(a.nombre)}</div>
                <div style="font-size:.68rem;color:var(--muted);">${esc(a.ciudad||'')}</div>
            </div>
            <span class="drop-id">ID ${a.id}</span>
        </div>`
    ).join('');
    drop.classList.add('show');
}

function seleccionarAtraccion(id, nombre, ciudad) {
    document.getElementById('fAtraccionSearch').value  = nombre;
    document.getElementById('fAtraccionId').value      = id;
    document.getElementById('lblAtraccion').textContent = `✓ ID ${id} — ${ciudad}`;
    document.getElementById('dropAtraccion').classList.remove('show');
}

// Cerrar dropdown al click fuera
document.addEventListener('click', e => {
    if (!e.target.closest('.search-select-wrap'))
        document.getElementById('dropAtraccion').classList.remove('show');
});

// ═══════════════════════════════════════════════════════════════════════════
//  RESET FEEDBACK
// ═══════════════════════════════════════════════════════════════════════════
function resetFeedback(id) {
    Swal.fire({
        title: '¿Limpiar feedback?',
        text: 'El feedback del usuario quedará vacío.',
        icon: 'question',
        background:'#1c1c32', color:'#f0f0f5',
        confirmButtonColor:'#a78bfa', cancelButtonColor:'rgba(255,255,255,.08)',
        confirmButtonText: '<i class="fas fa-rotate-left"></i> Limpiar',
        cancelButtonText: 'Cancelar', showCancelButton: true,
    }).then(res => {
        if (!res.isConfirmed) return;
        fetch('index.php?c=adminrecomendaciones&a=LimpiarFeedback', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id }),
        })
        .then(r => r.json())
        .then(d => {
            Swal.fire({ title: d.success ? 'Limpiado' : 'Error', text: d.message,
                icon: d.success ? 'success' : 'error',
                background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
            if (d.success) cargar();
        });
    });
}

// ═══════════════════════════════════════════════════════════════════════════
//  ELIMINAR
// ═══════════════════════════════════════════════════════════════════════════
function eliminar(id) {
    Swal.fire({
        title: '¿Eliminar recomendación?',
        text: 'Esta acción es irreversible.',
        icon: 'warning',
        background:'#1c1c32', color:'#f0f0f5',
        confirmButtonColor:'#ef4444', cancelButtonColor:'rgba(255,255,255,.08)',
        confirmButtonText: '<i class="fas fa-trash"></i> Eliminar',
        cancelButtonText: 'Cancelar', showCancelButton: true,
    }).then(res => {
        if (!res.isConfirmed) return;
        fetch('index.php?c=adminrecomendaciones&a=Eliminar', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id }),
        })
        .then(r => r.json())
        .then(d => {
            Swal.fire({ title: d.success ? 'Eliminada' : 'Error', text: d.message,
                icon: d.success ? 'success' : 'error',
                background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
            if (d.success) { cargar(); cargarStats(); }
        });
    });
}

// ═══════════════════════════════════════════════════════════════════════════
//  EXPORTAR EXCEL
// ═══════════════════════════════════════════════════════════════════════════
function exportarExcel() {
    const q        = encodeURIComponent(document.getElementById('inputBusqueda').value.trim());
    const modeloId = document.getElementById('selModelo').value;
    const feedback = document.getElementById('selFeedback').value;

    Swal.fire({ title:'Generando Excel…', didOpen:()=>Swal.showLoading(),
        background:'#1c1c32', color:'#f0f0f5', allowOutsideClick:false });

    fetch(`index.php?c=adminrecomendaciones&a=Listar&q=${q}&modelo_id=${modeloId}&feedback=${feedback}&per_page=1000&page=1`)
        .then(r => r.json())
        .then(d => {
            Swal.close();
            if (!d.success || !d.datos.length) {
                Swal.fire({ title:'Sin datos', icon:'info', background:'#1c1c32', color:'#f0f0f5' }); return;
            }
            const filas = d.datos.map(r => ({
                'ID': r.id,
                'Atracción': r.atraccion_nombre,
                'Ciudad': r.atraccion_ciudad||'',
                'Modelo MR': r.modelo_nombre,
                'Versión': r.modelo_version,
                'Score (%)': Math.round(parseFloat(r.score_relevancia||0)*100),
                'Feedback': r.feedback_usuario?.tipo || '—',
                'Generado': (r.fecha_generacion||'').slice(0,10),
            }));
            const ws = XLSX.utils.json_to_sheet(filas);
            ws['!cols'] = [{wch:6},{wch:30},{wch:16},{wch:24},{wch:8},{wch:10},{wch:12},{wch:12}];
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Recomendaciones');
            XLSX.writeFile(wb, `etreva_recomendaciones_${new Date().toISOString().slice(0,10)}.xlsx`);
        })
        .catch(() => { Swal.close(); Swal.fire({title:'Error',icon:'error',background:'#1c1c32',color:'#f0f0f5'}); });
}

// ═══════════════════════════════════════════════════════════════════════════
//  MODAL — cerrar
// ═══════════════════════════════════════════════════════════════════════════
function cerrarModal() {
    document.getElementById('modalForm').classList.remove('show');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
document.getElementById('modalForm').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

// ═══════════════════════════════════════════════════════════════════════════
//  ────────────────────────────────────────────────────────────────────────
//  CHIPS DINÁMICOS POR MODELO
//  ────────────────────────────────────────────────────────────────────────
// ═══════════════════════════════════════════════════════════════════════════
let _dynParams    = {};
let _dynCtx       = {};
let _loadedModel  = null;
let _pendingRazon = null;
let _pendingCtx   = null;

const RADIO_GRUPOS = new Set(['dificultad','duracion','presupuesto','temporada']);

const GRUPO_META = {
    interes:       { cls:'sel-interes',       ico:'fa-heart',      color:'#a5b4fc' },
    dificultad:    { cls:'sel-dificultad',    ico:'fa-gauge-high', color:'#fca5a5' },
    duracion:      { cls:'sel-duracion',      ico:'fa-clock',      color:'#7dd3fc' },
    presupuesto:   { cls:'sel-presupuesto',   ico:'fa-coins',      color:'#fcd34d' },
    temporada:     { cls:'sel-temporada',     ico:'fa-sun',        color:'#5eead4' },
    clasificacion: { cls:'sel-clasificacion', ico:'fa-tags',       color:'#c4b5fd' },
    _default:      { cls:'sel-custom',        ico:'fa-circle-dot', color:'var(--muted)' },
};
function getGrupoMeta(g) { return GRUPO_META[g] || GRUPO_META._default; }

const CTX_META = {
    clima:     { ico:'fa-cloud-sun',    color:'var(--teal)'   },
    altitud:   { ico:'fa-mountain',     color:'var(--purple)' },
    ubicacion: { ico:'fa-location-dot', color:'var(--green)'  },
    ciudad:    { ico:'fa-city',         color:'var(--blue)'   },
    pais:      { ico:'fa-flag',         color:'var(--amber)'  },
    _default:  { ico:'fa-tag',          color:'var(--muted)'  },
};
function getCtxMeta(t) { return CTX_META[t] || CTX_META._default; }

// ─── onModeloChange ───────────────────────────────────────────────────────
function onModeloChange() {
    const id = parseInt(document.getElementById('fModeloId').value);
    if (!id) { resetDynSections(); return; }
    if (id === _loadedModel) return;
    fetchDatosPorModelo(id);
}

// ─── fetchDatosPorModelo ──────────────────────────────────────────────────
function fetchDatosPorModelo(modeloId) {
    const skel = `<div class="dyn-skel-grid">
        <div class="dyn-skel-block">
            <div class="skel-bar" style="height:9px;width:45%;margin-bottom:10px;"></div>
            <div style="display:flex;gap:6px;">
                <div class="skel-bar" style="height:26px;width:65px;border-radius:20px;"></div>
                <div class="skel-bar" style="height:26px;width:80px;border-radius:20px;"></div>
            </div>
        </div>
        <div class="dyn-skel-block">
            <div class="skel-bar" style="height:9px;width:35%;margin-bottom:10px;"></div>
            <div style="display:flex;gap:6px;">
                <div class="skel-bar" style="height:26px;width:55px;border-radius:20px;"></div>
                <div class="skel-bar" style="height:26px;width:70px;border-radius:20px;"></div>
            </div>
        </div>
    </div>`;
    document.getElementById('razonSection').innerHTML    = skel;
    document.getElementById('contextoSection').innerHTML = skel;
    document.getElementById('btnPreviewRazon').style.display = 'none';
    document.getElementById('btnPreviewCtx').style.display   = 'none';
    document.getElementById('previewRazon').classList.remove('show');
    document.getElementById('previewCtx').classList.remove('show');

    fetch(`index.php?c=adminrecomendaciones&a=DatosPorModelo&modelo_id=${modeloId}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                showDynError('razonSection',    d.message||'Error');
                showDynError('contextoSection', d.message||'Error');
                return;
            }
            _dynParams   = d.parametros || {};
            _dynCtx      = d.contexto   || {};
            _loadedModel = modeloId;

            renderRazonSection();
            renderCtxSection();

            document.getElementById('btnPreviewRazon').style.display = 'inline-flex';
            document.getElementById('btnPreviewCtx').style.display   = 'inline-flex';

            if (_pendingRazon) { cargarRazonDesdeJson(_pendingRazon); _pendingRazon = null; }
            if (_pendingCtx)   { cargarCtxDesdeJson(_pendingCtx);     _pendingCtx   = null; }
        })
        .catch(() => {
            showDynError('razonSection',    'Error de conexión');
            showDynError('contextoSection', 'Error de conexión');
        });
}

// ─── renderRazonSection ───────────────────────────────────────────────────
function renderRazonSection() {
    const sec    = document.getElementById('razonSection');
    const grupos = Object.keys(_dynParams);
    if (!grupos.length) {
        sec.innerHTML = '<div class="dyn-hint"><i class="fas fa-circle-info"></i> Sin parámetros activos para este modelo.</div>';
        return;
    }
    let html = '<div class="groups-dyn-grid">';
    grupos.forEach(grupo => {
        const items   = _dynParams[grupo];
        const meta    = getGrupoMeta(grupo);
        const isRadio = RADIO_GRUPOS.has(grupo);
        const fullCls = items.length > 5 ? 'pg-fullwidth' : '';
        const badge   = isRadio ? '<span class="pg-badge">elige uno</span>' : '<span class="pg-badge">varios</span>';
        const chips   = items.map(item => {
            const title   = item.descripcion ? ` title="${esc(item.descripcion)}"` : '';
            const onclick = isRadio
                ? `onclick="toggleChipRadio(this,'${grupo}','${meta.cls}')"`
                : `onclick="toggleChipMulti(this,'${meta.cls}')"`;
            return `<span class="chip" data-grupo="${grupo}" data-val="${esc(item.valor)}"${onclick}${title}>${esc(item.valor)}</span>`;
        }).join('');
        html += `<div class="param-group ${fullCls}">
            <div class="param-group-label"><i class="fas ${meta.ico}" style="color:${meta.color};"></i>${grupo}${badge}</div>
            <div class="chips-row">${chips||'<span class="chips-row-empty">Sin valores</span>'}</div>
        </div>`;
    });
    html += '</div>';
    sec.innerHTML = html;
}

// ─── renderCtxSection ─────────────────────────────────────────────────────
function renderCtxSection() {
    const sec   = document.getElementById('contextoSection');
    const tipos = Object.keys(_dynCtx);
    if (!tipos.length) {
        sec.innerHTML = '<div class="dyn-hint"><i class="fas fa-circle-info"></i> Sin contextos activos para este modelo.</div>';
        return;
    }
    let html = '<div class="groups-dyn-grid">';
    tipos.forEach(tipo => {
        const vals    = _dynCtx[tipo];
        const meta    = getCtxMeta(tipo);
        const fullCls = vals.length > 4 ? 'pg-fullwidth' : '';
        const chips   = vals.map(v =>
            `<span class="chip" data-ctx-tipo="${tipo}" data-val="${esc(v)}" onclick="toggleChipCtx(this,'${tipo}')">${esc(v)}</span>`
        ).join('');
        html += `<div class="param-group ${fullCls}">
            <div class="param-group-label"><i class="fas ${meta.ico}" style="color:${meta.color};"></i>${tipo}<span class="pg-badge">elige uno</span></div>
            <div class="chips-row">${chips}</div>
        </div>`;
    });
    html += '</div>';
    sec.innerHTML = html;
}

// ─── Toggles ──────────────────────────────────────────────────────────────
function toggleChipMulti(el, cls) {
    el.classList.toggle(cls);
    refreshJsonPreviews();
}
function toggleChipRadio(el, grupo, cls) {
    document.querySelectorAll(`.chip[data-grupo="${grupo}"]`).forEach(c => c.classList.remove(cls));
    el.classList.add(cls);
    refreshJsonPreviews();
}
function toggleChipCtx(el, tipo) {
    document.querySelectorAll(`.chip[data-ctx-tipo="${tipo}"]`).forEach(c => c.classList.remove('sel-ctx'));
    el.classList.add('sel-ctx');
    refreshJsonPreviews();
}

// ─── Build JSONs ──────────────────────────────────────────────────────────
function buildRazonJson() {
    const obj = { parametros: {} };
    Object.keys(_dynParams).forEach(grupo => {
        const cls  = getGrupoMeta(grupo).cls;
        const vals = [...document.querySelectorAll(`.chip[data-grupo="${grupo}"].${cls}`)].map(c => c.dataset.val);
        if (vals.length === 1) obj.parametros[grupo] = vals[0];
        else if (vals.length > 1) obj.parametros[grupo] = vals;
    });
    return obj;
}
function buildContextoJson() {
    const obj = {};
    document.querySelectorAll('.chip.sel-ctx').forEach(c => { obj[c.dataset.ctxTipo] = c.dataset.val; });
    return obj;
}

// ─── Cargar desde JSON (editar) ───────────────────────────────────────────
function cargarRazonDesdeJson(razon) {
    if (!razon || typeof razon !== 'object') return;
    Object.keys(_dynParams).forEach(g => {
        const cls = getGrupoMeta(g).cls;
        document.querySelectorAll(`.chip[data-grupo="${g}"]`).forEach(c => c.classList.remove(cls));
    });
    const params = razon.parametros || razon;
    for (const [grupo, val] of Object.entries(params)) {
        if (grupo === 'notas') continue;
        const cls = getGrupoMeta(grupo).cls;
        (Array.isArray(val) ? val : [val]).forEach(v => {
            const c = document.querySelector(`.chip[data-grupo="${grupo}"][data-val="${v}"]`);
            if (c) c.classList.add(cls);
        });
    }
    refreshJsonPreviews();
}
function cargarCtxDesdeJson(ctx) {
    if (!ctx || typeof ctx !== 'object') return;
    document.querySelectorAll('.chip[data-ctx-tipo]').forEach(c => c.classList.remove('sel-ctx'));
    for (const [tipo, val] of Object.entries(ctx)) {
        const c = document.querySelector(`.chip[data-ctx-tipo="${tipo}"][data-val="${val}"]`);
        if (c) c.classList.add('sel-ctx');
    }
    refreshJsonPreviews();
}

// ─── Reset ────────────────────────────────────────────────────────────────
function resetDynSections() {
    const hint = `<div class="dyn-hint"><i class="fas fa-circle-arrow-up"></i> Seleccioná un modelo MR para ver los datos.</div>`;
    document.getElementById('razonSection').innerHTML    = hint;
    document.getElementById('contextoSection').innerHTML = hint;
    document.getElementById('btnPreviewRazon').style.display = 'none';
    document.getElementById('btnPreviewCtx').style.display   = 'none';
    document.getElementById('previewRazon').classList.remove('show');
    document.getElementById('previewCtx').classList.remove('show');
}
function resetDynChips() {
    Object.keys(_dynParams).forEach(g => {
        const cls = getGrupoMeta(g).cls;
        document.querySelectorAll(`.chip[data-grupo="${g}"]`).forEach(c => c.classList.remove(cls));
    });
    document.querySelectorAll('.chip[data-ctx-tipo]').forEach(c => c.classList.remove('sel-ctx'));
}

// ─── Preview JSON ─────────────────────────────────────────────────────────
function toggleJsonPreview(boxId) {
    const box = document.getElementById(boxId);
    box.classList.toggle('show');
    if (box.classList.contains('show')) refreshJsonPreviews();
}
function refreshJsonPreviews() {
    const bR = document.getElementById('previewRazon');
    const bC = document.getElementById('previewCtx');
    if (bR?.classList.contains('show')) bR.textContent = JSON.stringify(buildRazonJson(), null, 2);
    if (bC?.classList.contains('show')) bC.textContent = JSON.stringify(buildContextoJson(), null, 2);
}
function showDynError(id, msg) {
    document.getElementById(id).innerHTML =
        `<div class="dyn-hint error"><i class="fas fa-circle-exclamation"></i> ${esc(msg)}</div>`;
}

// ═══════════════════════════════════════════════════════════════════════════
//  MODAL CREAR
// ═══════════════════════════════════════════════════════════════════════════
function abrirModalCrear() {
    document.getElementById('modalTitulo').innerHTML =
        '<i class="fas fa-plus" style="color:var(--green);"></i> Nueva Recomendación';
    document.getElementById('fId').value               = '';
    document.getElementById('fAtraccionSearch').value  = '';
    document.getElementById('fAtraccionId').value      = '';
    document.getElementById('lblAtraccion').textContent = '';
    document.getElementById('fModeloId').value         = '';
    document.getElementById('lblModelo').textContent   = '';
    document.getElementById('fScoreRange').value       = 50;
    document.getElementById('fScoreNum').value         = 50;
    document.getElementById('feedbackInfo').style.display = 'none';
    _pendingRazon = null;
    _pendingCtx   = null;
    resetDynSections();
    document.getElementById('modalForm').classList.add('show');
}

// ═══════════════════════════════════════════════════════════════════════════
//  MODAL EDITAR
// ═══════════════════════════════════════════════════════════════════════════
function abrirModalEditar(id) {
    fetch(`index.php?c=adminrecomendaciones&a=Ver&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                Swal.fire({ title:'Error', text:d.message, icon:'error',
                    background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
                return;
            }
            const rec = d.recomendacion;

            document.getElementById('modalTitulo').innerHTML =
                '<i class="fas fa-pen" style="color:var(--orange);"></i> Editar Recomendación';
            document.getElementById('fId').value              = rec.id;
            document.getElementById('fAtraccionSearch').value = rec.atraccion_nombre || '';
            document.getElementById('fAtraccionId').value     = rec.atraccion_id     || '';
            document.getElementById('lblAtraccion').textContent =
                `✓ ID ${rec.atraccion_id} — ${rec.atraccion_ciudad||''}`;

            const score = parseFloat(rec.score_relevancia || 0) * 100;
            document.getElementById('fScoreRange').value = score;
            document.getElementById('fScoreNum').value   = score.toFixed(0);

            const fb = rec.feedback_usuario || {};
            const fbSection = document.getElementById('feedbackInfo');
            if (fb && Object.keys(fb).length) {
                fbSection.style.display = 'block';
                document.getElementById('feedbackBox').innerHTML =
                    `<strong>Tipo:</strong> ${fb.tipo||'—'} &nbsp;|&nbsp; <strong>JSON:</strong> <code style="font-size:.72rem;">${esc(JSON.stringify(fb))}</code>`;
            } else {
                fbSection.style.display = 'none';
            }

            const modeloId = parseInt(rec.modelo_mr_id);
            document.getElementById('fModeloId').value       = modeloId;
            document.getElementById('lblModelo').textContent =
                `✓ ${rec.modelo_nombre||''} v${rec.modelo_version||''}`;

            if (modeloId === _loadedModel) {
                resetDynChips();
                cargarRazonDesdeJson(rec.razon_recomendacion    || {});
                cargarCtxDesdeJson(rec.contexto_recomendacion   || {});
                document.getElementById('btnPreviewRazon').style.display = 'inline-flex';
                document.getElementById('btnPreviewCtx').style.display   = 'inline-flex';
            } else {
                _pendingRazon = rec.razon_recomendacion    || {};
                _pendingCtx   = rec.contexto_recomendacion || {};
                _loadedModel  = null; // forzar re-fetch
                fetchDatosPorModelo(modeloId);
            }

            document.getElementById('modalForm').classList.add('show');
        })
        .catch(() => Swal.fire({ title:'Error de conexión', icon:'error',
            background:'#1c1c32', color:'#f0f0f5' }));
}

// ═══════════════════════════════════════════════════════════════════════════
//  GUARDAR
// ═══════════════════════════════════════════════════════════════════════════
function guardar() {
    const id          = document.getElementById('fId').value;
    const atraccionId = document.getElementById('fAtraccionId').value;
    const modeloId    = document.getElementById('fModeloId').value;
    const score       = document.getElementById('fScoreNum').value;

    if (!atraccionId) {
        Swal.fire({ title:'Requerido', text:'Seleccioná una atracción', icon:'warning',
            background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' }); return;
    }
    if (!modeloId) {
        Swal.fire({ title:'Requerido', text:'Seleccioná un modelo MR', icon:'warning',
            background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' }); return;
    }

    const payload = {
        atraccion_id:           parseInt(atraccionId),
        modelo_mr_id:           parseInt(modeloId),
        score_relevancia:       parseFloat(score) / 100,
        razon_recomendacion:    buildRazonJson(),
        contexto_recomendacion: buildContextoJson(),
    };
    if (id) payload.id = parseInt(id);

    const accion = id ? 'Actualizar' : 'Crear';
    const btn    = document.getElementById('btnGuardar');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';

    fetch(`index.php?c=adminrecomendaciones&a=${accion}`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Guardar';
        Swal.fire({
            title: d.success ? (id ? '¡Actualizada!' : '¡Creada!') : 'Error',
            text:  d.message, icon: d.success ? 'success' : 'error',
            background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b',
        });
        if (d.success) { cerrarModal(); cargar(); cargarStats(); }
    })
    .catch(() => {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Guardar';
        Swal.fire({ title:'Error de conexión', icon:'error', background:'#1c1c32', color:'#f0f0f5' });
    });
}

// ═══════════════════════════════════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════════════════════════════════
document.getElementById('btnRefresh').addEventListener('click', () => {
    resetPage(); cargar(); cargarStats();
});

cargarSelectores();
cargar();
cargarStats();
</script>
</body>
</html>