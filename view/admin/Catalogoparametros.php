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
<title>Etreva Admin — Catálogo de Parámetros</title>
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
    --indigo:#6366f1;
}
*{box-sizing:border-box;}
html,body{background:var(--bg)!important;color:var(--text)!important;min-height:100vh;
    font-family:'Segoe UI',system-ui,sans-serif;margin:0;padding:0;}
main{margin-left:280px;min-height:100vh;background:var(--bg);transition:margin-left .3s;}
main.min-main{margin-left:85px;}

/* TOPBAR */
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
.filtros-wrap input[type=text],.filtros-wrap select{
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;
    padding:8px 12px;color:var(--text);font-size:.82rem;outline:none;transition:border-color .2s;}
.filtros-wrap input[type=text]{flex:1;min-width:200px;}
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
.btn-pdf{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.25);}
.btn-pdf:hover{background:rgba(239,68,68,.2);transform:translateY(-1px);}
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
.badge{font-size:.63rem;font-weight:700;padding:3px 8px;border-radius:20px;
    white-space:nowrap;display:inline-flex;align-items:center;gap:4px;}
.b-activo   {background:rgba(34,197,94,.12); color:var(--green);}
.b-inactivo {background:rgba(239,68,68,.1);  color:var(--red);}

/* Grupo badges */
.b-interes     {background:rgba(99,102,241,.14);  color:var(--indigo);}
.b-dificultad  {background:rgba(239,68,68,.12);   color:var(--red);}
.b-duracion    {background:rgba(56,189,248,.12);  color:var(--blue);}
.b-presupuesto {background:rgba(245,158,11,.12);  color:var(--amber);}
.b-temporada   {background:rgba(45,212,191,.12);  color:var(--teal);}
.b-clasificacion{background:rgba(167,139,250,.12);color:var(--purple);}
.b-grupo-gen   {background:rgba(255,255,255,.07); color:var(--muted);}

/* ACCIONES */
.acciones{display:flex;gap:5px;}
.btn-sm{padding:4px 9px;border-radius:7px;font-size:.7rem;font-weight:700;border:none;
    cursor:pointer;display:inline-flex;align-items:center;gap:3px;transition:all .18s;white-space:nowrap;}
.btn-edit      {background:rgba(56,189,248,.1);  color:var(--blue);}
.btn-edit:hover{background:rgba(56,189,248,.22); transform:translateY(-1px);}
.btn-toggle-on {background:rgba(239,68,68,.08);  color:var(--red);}
.btn-toggle-on:hover{background:rgba(239,68,68,.2);transform:translateY(-1px);}
.btn-toggle-off{background:rgba(34,197,94,.1);   color:var(--green);}
.btn-toggle-off:hover{background:rgba(34,197,94,.22);transform:translateY(-1px);}

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
    width:100%;max-width:600px;animation:popIn .22s ease;margin:auto;}
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
.form-group select option{background:#1c1c32;}
.form-group textarea{resize:vertical;min-height:70px;font-family:inherit;}
.form-sep{font-size:.67rem;color:var(--orange);text-transform:uppercase;letter-spacing:1.5px;font-weight:700;
    margin:14px 0 6px;display:flex;align-items:center;gap:8px;grid-column:1/-1;}
.form-sep::after{content:'';flex:1;height:1px;background:rgba(229,90,43,.2);}

/* Toggle estado */
.toggle-wrap{display:flex;align-items:center;gap:10px;padding:8px 0;}
.toggle-switch{position:relative;display:inline-block;width:44px;height:24px;}
.toggle-switch input{opacity:0;width:0;height:0;}
.toggle-slider{position:absolute;cursor:pointer;inset:0;background:rgba(255,255,255,.1);
    border-radius:24px;transition:.3s;}
.toggle-slider:before{content:'';position:absolute;height:18px;width:18px;left:3px;bottom:3px;
    background:#fff;border-radius:50%;transition:.3s;}
.toggle-switch input:checked+.toggle-slider{background:var(--green);}
.toggle-switch input:checked+.toggle-slider:before{transform:translateX(20px);}
.toggle-label{font-size:.83rem;color:var(--text);}

/* Botones modal */
.btn-guardar{background:linear-gradient(135deg,var(--orange),#ff9a6c);color:#fff;border:none;
    border-radius:9px;padding:9px 22px;font-size:.84rem;font-weight:700;cursor:pointer;transition:all .2s;
    display:flex;align-items:center;gap:7px;}
.btn-guardar:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(229,90,43,.4);}
.btn-cancelar{background:rgba(255,255,255,.06);color:var(--muted);border:1px solid rgba(255,255,255,.1);
    border-radius:9px;padding:9px 16px;font-size:.84rem;cursor:pointer;transition:all .2s;}
.btn-cancelar:hover{color:var(--text);background:rgba(255,255,255,.1);}

/* ─── PRINT / PDF ─── */
@media print{
    .topbar,.stat-strip,.filtros-wrap,.acciones,.pag-btns,
    #adminMenu,.btn-icon,.modal-overlay,button{display:none!important;}
    main{margin-left:0!important;}
    body,html{background:#fff!important;color:#000!important;}
    .tabla-wrap{border:1px solid #ccc!important;border-radius:0!important;}
    .adm-table thead th{background:#f5f5f5!important;color:#333!important;
        border-bottom:2px solid #ccc!important;font-size:.72rem!important;}
    .adm-table tbody tr{border-bottom:1px solid #eee!important;}
    .adm-table tbody td{color:#111!important;padding:7px 10px!important;font-size:.78rem!important;}
    .badge{border:1px solid #bbb!important;background:transparent!important;color:#333!important;}
    .print-header{display:block!important;}
    .paginacion{display:none!important;}
}
.print-header{display:none;text-align:center;margin-bottom:20px;
    padding-bottom:14px;border-bottom:2px solid #ccc;}
.print-header h2{margin:0 0 4px;font-size:1.3rem;}
.print-header p{margin:0;font-size:.8rem;color:#666;}

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
        <i class="fas fa-sliders" style="color:var(--orange);"></i>
        Catálogo de <span>Parámetros</span>
    </h1>
    <div class="topbar-right">
        <div class="btn-icon" id="btnRefresh" title="Actualizar"><i class="fas fa-rotate-right"></i></div>
        <a class="btn-icon" href="index.php?c=login&a=Logout" title="Salir" style="color:#ef4444;">
            <i class="fas fa-right-from-bracket"></i>
        </a>
    </div>
</div>

<div class="body">

    <!-- PRINT HEADER -->
    <div class="print-header">
        <h2>Reporte — Catálogo de Parámetros</h2>
        <p>Generado el <span id="printFecha"></span> · Etreva Admin</p>
    </div>

    <!-- STATS -->
    <div class="stat-strip">
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(229,90,43,.14);">
                <i class="fas fa-list-check" style="color:var(--orange);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-total">—</div><div class="stat-pill-lbl">Total parámetros</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(34,197,94,.12);">
                <i class="fas fa-circle-check" style="color:var(--green);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-activos">—</div><div class="stat-pill-lbl">Activos</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(239,68,68,.1);">
                <i class="fas fa-circle-xmark" style="color:var(--red);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-inactivos">—</div><div class="stat-pill-lbl">Inactivos</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(167,139,250,.1);">
                <i class="fas fa-robot" style="color:var(--purple);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-modelos">—</div><div class="stat-pill-lbl">Modelos usados</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(99,102,241,.12);">
                <i class="fas fa-layer-group" style="color:var(--indigo);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-grupos">—</div><div class="stat-pill-lbl">Grupos distintos</div></div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filtros-wrap">
        <input type="text" id="inputBusqueda"
               placeholder="🔍  Buscar por valor, descripción, grupo o modelo…"
               oninput="debounceSearch()">

        <select id="selModelo" onchange="resetPage();cargar()">
            <option value="">Todos los modelos</option>
            <?php foreach ($modelos as $m): ?>
                <option value="<?= $m['id'] ?>">
                    <?= htmlspecialchars($m['nombre'] ?? 'Modelo '.$m['id']) ?>
                    (v<?= htmlspecialchars($m['version'] ?? '?') ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <select id="selGrupo" onchange="resetPage();cargar()">
            <option value="">Todos los grupos</option>
            <?php foreach ($grupos as $g): ?>
                <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="selEstado" onchange="resetPage();cargar()">
            <option value="">Todos los estados</option>
            <option value="activo">✅ Activos</option>
            <option value="inactivo">❌ Inactivos</option>
        </select>

        <button class="btn-filtrar" onclick="resetPage();cargar()">
            <i class="fas fa-filter"></i> Filtrar
        </button>
        <div class="export-sep"></div>
        <button class="btn-export btn-excel" onclick="exportarExcel()">
            <i class="fas fa-file-excel"></i> Excel
        </button>
        <button class="btn-export btn-pdf" onclick="imprimirPDF()">
            <i class="fas fa-file-pdf"></i> PDF
        </button>
        <div class="export-sep"></div>
        <button class="btn-nuevo" onclick="abrirModalCrear()">
            <i class="fas fa-plus"></i> Nuevo parámetro
        </button>
    </div>

    <!-- TABLA -->
    <div class="tabla-wrap">
        <table class="adm-table" id="tablaParametros">
            <thead>
                <tr>
                    <th class="sortable sort-active" data-col="id" onclick="toggleSort('id')"># <i class="fas fa-sort-up" id="ico-id"></i></th>
                    <th class="sortable" data-col="modelo" onclick="toggleSort('modelo')">Modelo MR <i class="fas fa-sort" id="ico-modelo"></i></th>
                    <th class="sortable" data-col="grupo" onclick="toggleSort('grupo')">Grupo <i class="fas fa-sort" id="ico-grupo"></i></th>
                    <th class="sortable" data-col="valor" onclick="toggleSort('valor')">Valor <i class="fas fa-sort" id="ico-valor"></i></th>
                    <th class="sortable" data-col="descripcion" onclick="toggleSort('descripcion')">Descripción <i class="fas fa-sort" id="ico-descripcion"></i></th>
                    <th class="sortable" data-col="estado" onclick="toggleSort('estado')">Estado <i class="fas fa-sort" id="ico-estado"></i></th>
                    <th class="sortable" data-col="fecha" onclick="toggleSort('fecha')">Creado <i class="fas fa-sort" id="ico-fecha"></i></th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <tr><td colspan="8" style="padding:30px;">
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

<!-- ─── MODAL CREAR / EDITAR ─── -->
<div class="modal-overlay" id="modalForm">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo">
                <i class="fas fa-plus" style="color:var(--green);"></i> Nuevo Parámetro
            </h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="fId">

            <div class="form-grid">
                <div class="form-sep"><i class="fas fa-link"></i> Datos del parámetro</div>

                <!-- Modelo MR -->
                <div class="form-group full">
                    <label>Modelo MR *</label>
                    <select id="fModeloId">
                        <option value="">— Seleccioná un modelo activo —</option>
                    </select>
                </div>

                <!-- Grupo -->
                <div class="form-group">
                    <label>Grupo *</label>
                    <select id="fGrupo" onchange="sincronizarGrupoTexto()">
                        <option value="">— Seleccioná un grupo —</option>
                        <option value="interes">interes</option>
                        <option value="dificultad">dificultad</option>
                        <option value="duracion">duracion</option>
                        <option value="presupuesto">presupuesto</option>
                        <option value="temporada">temporada</option>
                        <option value="clasificacion">clasificacion</option>
                        <option value="__custom__">+ Otro grupo…</option>
                    </select>
                </div>

                <!-- Grupo personalizado -->
                <div class="form-group" id="grupoCustomWrap" style="display:none;">
                    <label>Nombre del grupo *</label>
                    <input type="text" id="fGrupoCustom" placeholder="ej: tipo_acceso">
                </div>

                <!-- Valor -->
                <div class="form-group" id="valorWrap">
                    <label>Valor *</label>
                    <input type="text" id="fValor" placeholder="ej: aventura, alta, media…">
                </div>

                <!-- Descripción -->
                <div class="form-group full">
                    <label>Descripción</label>
                    <textarea id="fDescripcion" placeholder="Descripción opcional del parámetro…"></textarea>
                </div>

                <!-- Estado -->
                <div class="form-group full">
                    <label>Estado</label>
                    <div class="toggle-wrap">
                        <label class="toggle-switch">
                            <input type="checkbox" id="fEstado" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label" id="toggleLabel">Activo</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancelar" onclick="cerrarModal()">
                <i class="fas fa-xmark"></i> Cancelar
            </button>
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
//  ESTADO
// ═══════════════════════════════════════════════════════════════════════════
const pag    = { page: 1, total: 0, pages: 1 };
let sortCol  = 'id', sortOrd = 'asc';
let _dTimer  = null;
let _cache   = [];
let _modelos = [];

function debounceSearch() {
    clearTimeout(_dTimer);
    _dTimer = setTimeout(() => { resetPage(); cargar(); }, 380);
}
function resetPage() { pag.page = 1; }

// Fecha print header
document.getElementById('printFecha').textContent =
    new Date().toLocaleString('es-BO', { dateStyle:'long', timeStyle:'short' });

// Toggle label
document.getElementById('fEstado').addEventListener('change', function(){
    document.getElementById('toggleLabel').textContent = this.checked ? 'Activo' : 'Inactivo';
});

// Grupo custom toggle
function sincronizarGrupoTexto() {
    const val = document.getElementById('fGrupo').value;
    const wrap = document.getElementById('grupoCustomWrap');
    const vWrap = document.getElementById('valorWrap');

    if (val === '__custom__') {
        wrap.style.display = 'flex';
        vWrap.style.gridColumn = '1/-1';
    } else {
        wrap.style.display = 'none';
        vWrap.style.gridColumn = '';
    }
}

function getGrupoFinal() {
    const sel = document.getElementById('fGrupo').value;
    if (sel === '__custom__') return document.getElementById('fGrupoCustom').value.trim();
    return sel;
}

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
//  BADGE GRUPO
// ═══════════════════════════════════════════════════════════════════════════
function grupoBadge(grupo) {
    const map = {
        interes:       { cls:'b-interes',      ico:'fa-heart'       },
        dificultad:    { cls:'b-dificultad',   ico:'fa-gauge-high'  },
        duracion:      { cls:'b-duracion',     ico:'fa-clock'       },
        presupuesto:   { cls:'b-presupuesto',  ico:'fa-coins'       },
        temporada:     { cls:'b-temporada',    ico:'fa-sun'         },
        clasificacion: { cls:'b-clasificacion',ico:'fa-tags'        },
    };
    const g = map[grupo] || { cls:'b-grupo-gen', ico:'fa-circle-dot' };
    return `<span class="badge ${g.cls}"><i class="fas ${g.ico}"></i> ${grupo}</span>`;
}

function estadoBadge(estado) {
    return estado
        ? '<span class="badge b-activo"><i class="fas fa-circle-check"></i> Activo</span>'
        : '<span class="badge b-inactivo"><i class="fas fa-circle-xmark"></i> Inactivo</span>';
}

function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')
                        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ═══════════════════════════════════════════════════════════════════════════
//  CARGAR TABLA
// ═══════════════════════════════════════════════════════════════════════════
function cargar() {
    const q   = encodeURIComponent(document.getElementById('inputBusqueda').value.trim());
    const mod = document.getElementById('selModelo').value;
    const grp = encodeURIComponent(document.getElementById('selGrupo').value);
    const est = document.getElementById('selEstado').value;

    document.getElementById('tbody').innerHTML =
        '<tr><td colspan="8" style="padding:28px;"><div class="skeleton" style="height:18px;"></div></td></tr>';

    fetch(`index.php?c=admincatalogoparametros&a=Listar&q=${q}&modelo_id=${mod}&grupo=${grp}&estado=${est}&sort=${sortCol}&orden=${sortOrd}&page=${pag.page}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) { renderVacia(d.message || 'Error'); return; }
            pag.total = d.total;
            pag.pages = d.paginas;
            _cache    = d.datos;
            renderTabla(d.datos);
            renderPag();
        })
        .catch(() => renderVacia('Error de conexión'));
}

// ═══════════════════════════════════════════════════════════════════════════
//  RENDER TABLA
// ═══════════════════════════════════════════════════════════════════════════
function renderTabla(items) {
    if (!items.length) { renderVacia('Sin parámetros con esos filtros'); return; }

    const rows = items.map(r => {
        const fecha = (r.fecha_creacion || '').slice(0, 10);
        const desc  = r.descripcion ? esc(r.descripcion) : '<span style="color:var(--muted);font-size:.7rem;">—</span>';

        // ✅ Normalizar estado a booleano JS real
        const estadoVal = r.estado === true || r.estado === 't' || r.estado === '1' || r.estado === 1;

        const btnToggle = estadoVal
            ? `<button class="btn-sm btn-toggle-on"  onclick="cambiarEstado(${r.id}, true)"  title="Desactivar"><i class="fas fa-ban"></i></button>`
            : `<button class="btn-sm btn-toggle-off" onclick="cambiarEstado(${r.id}, false)" title="Activar"><i class="fas fa-circle-check"></i></button>`;

        return `<tr onclick="abrirModalEditar(${r.id})">
            <td style="color:var(--muted);font-size:.7rem;">${r.id}</td>
            <td>
                <div style="font-size:.78rem;color:var(--purple);font-weight:600;">${esc(r.modelo_nombre||'—')}</div>
                <div style="font-size:.65rem;color:var(--muted);">v${esc(r.modelo_version||'?')}</div>
            </td>
            <td>${grupoBadge(r.grupo)}</td>
            <td style="font-weight:700;font-size:.84rem;">${esc(r.valor)}</td>
            <td style="font-size:.77rem;color:var(--muted);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${desc}</td>
            <td>${estadoBadge(estadoVal)}</td>
            <td style="font-size:.72rem;color:var(--muted);">${fecha}</td>
            <td onclick="event.stopPropagation();">
                <div class="acciones">
                    <button class="btn-sm btn-edit" onclick="abrirModalEditar(${r.id})" title="Editar">
                        <i class="fas fa-pen"></i>
                    </button>
                    ${btnToggle}
                </div>
            </td>
        </tr>`;
    }).join('');

    document.getElementById('tbody').innerHTML = rows;
}

function renderVacia(msg) {
    document.getElementById('tbody').innerHTML =
        `<tr><td colspan="8"><div class="empty-state"><i class="fas fa-sliders"></i><p>${msg}</p></div></td></tr>`;
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
    fetch('index.php?c=admincatalogoparametros&a=Stats')
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            const s = d.stats;
            document.getElementById('st-total').textContent    = s.total           || 0;
            document.getElementById('st-activos').textContent  = s.activos         || 0;
            document.getElementById('st-inactivos').textContent= s.inactivos       || 0;
            document.getElementById('st-modelos').textContent  = s.modelos_usados  || 0;
            document.getElementById('st-grupos').textContent   = s.grupos_distintos|| 0;
        }).catch(()=>{});
}

// ═══════════════════════════════════════════════════════════════════════════
//  CARGAR SELECTORES
// ═══════════════════════════════════════════════════════════════════════════
function cargarSelectores() {
    fetch('index.php?c=admincatalogoparametros&a=Selectores')
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            _modelos = d.modelos || [];
            const sel = document.getElementById('fModeloId');
            sel.innerHTML = '<option value="">— Seleccioná un modelo activo —</option>';
            _modelos.forEach(m => {
                const op = document.createElement('option');
                op.value       = m.id;
                op.textContent = `${m.nombre || 'Modelo '+m.id} (v${m.version || '?'})`;
                sel.appendChild(op);
            });

            // Agregar grupos dinámicos al select del filtro (si hay nuevos)
            const selGrpFiltro = document.getElementById('selGrupo');
            const existentes = [...selGrpFiltro.options].map(o => o.value);
            (d.grupos || []).forEach(g => {
                if (!existentes.includes(g)) {
                    const op = document.createElement('option');
                    op.value = g; op.textContent = g;
                    selGrpFiltro.appendChild(op);
                }
            });
        }).catch(()=>{});
}

// ═══════════════════════════════════════════════════════════════════════════
//  MODAL CREAR
// ═══════════════════════════════════════════════════════════════════════════
function abrirModalCrear() {
    document.getElementById('modalTitulo').innerHTML =
        '<i class="fas fa-plus" style="color:var(--green);"></i> Nuevo Parámetro';
    document.getElementById('fId').value           = '';
    document.getElementById('fModeloId').value     = '';
    document.getElementById('fGrupo').value        = '';
    document.getElementById('fGrupoCustom').value  = '';
    document.getElementById('fValor').value        = '';
    document.getElementById('fDescripcion').value  = '';
    document.getElementById('fEstado').checked     = true;
    document.getElementById('toggleLabel').textContent = 'Activo';
    document.getElementById('grupoCustomWrap').style.display = 'none';
    document.getElementById('valorWrap').style.gridColumn    = '';
    document.getElementById('modalForm').classList.add('show');
}

// ═══════════════════════════════════════════════════════════════════════════
//  MODAL EDITAR
// ═══════════════════════════════════════════════════════════════════════════
function abrirModalEditar(id) {
    fetch(`index.php?c=admincatalogoparametros&a=Ver&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                Swal.fire({ title:'Error', text:d.message, icon:'error',
                    background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
                return;
            }
            const p = d.parametro;

            document.getElementById('modalTitulo').innerHTML =
                '<i class="fas fa-pen" style="color:var(--orange);"></i> Editar Parámetro';

            document.getElementById('fId').value          = p.id;
            document.getElementById('fModeloId').value    = p.modelo_mr_id;
            document.getElementById('fDescripcion').value = p.descripcion || '';
            document.getElementById('fValor').value       = p.valor;

            // Grupo: si es de los conocidos cargarlo en select, si no en custom
            const gruposConocidos = ['interes','dificultad','duracion','presupuesto','temporada','clasificacion'];
            const selGrupo = document.getElementById('fGrupo');
            if (gruposConocidos.includes(p.grupo)) {
                selGrupo.value = p.grupo;
                document.getElementById('grupoCustomWrap').style.display = 'none';
                document.getElementById('valorWrap').style.gridColumn    = '';
            } else {
                selGrupo.value = '__custom__';
                document.getElementById('fGrupoCustom').value = p.grupo;
                document.getElementById('grupoCustomWrap').style.display = 'flex';
                document.getElementById('valorWrap').style.gridColumn    = '1/-1';
            }

            const activo = p.estado === true || p.estado === 't' || p.estado === '1';
            document.getElementById('fEstado').checked = activo;
            document.getElementById('toggleLabel').textContent = activo ? 'Activo' : 'Inactivo';

            document.getElementById('modalForm').classList.add('show');
        })
        .catch(() => Swal.fire({ title:'Error de conexión', icon:'error',
            background:'#1c1c32', color:'#f0f0f5' }));
}

function cerrarModal() {
    document.getElementById('modalForm').classList.remove('show');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
document.getElementById('modalForm').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

// ═══════════════════════════════════════════════════════════════════════════
//  GUARDAR
// ═══════════════════════════════════════════════════════════════════════════
function guardar() {
    const id    = document.getElementById('fId').value;
    const modId = document.getElementById('fModeloId').value;
    const grupo = getGrupoFinal();
    const valor = document.getElementById('fValor').value.trim();
    const desc  = document.getElementById('fDescripcion').value.trim();
    const est   = document.getElementById('fEstado').checked;

    if (!modId) { Swal.fire({ title:'Requerido', text:'Seleccioná un modelo', icon:'warning', background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' }); return; }
    if (!grupo) { Swal.fire({ title:'Requerido', text:'Seleccioná o ingresá un grupo', icon:'warning', background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' }); return; }
    if (!valor) { Swal.fire({ title:'Requerido', text:'Ingresá un valor', icon:'warning', background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' }); return; }

    const payload = {
        id:           id || undefined,
        modelo_mr_id: parseInt(modId),
        grupo:        grupo,
        valor:        valor,
        descripcion:  desc,
        estado:       est,
    };

    const accion = id ? 'Actualizar' : 'Crear';
    const btn    = document.getElementById('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';

    fetch(`index.php?c=admincatalogoparametros&a=${accion}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(d => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Guardar';
        Swal.fire({
            title: d.success ? (id ? '¡Actualizado!' : '¡Creado!') : 'Error',
            text:  d.message,
            icon:  d.success ? 'success' : 'error',
            background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b',
        });
        if (d.success) { cerrarModal(); cargar(); cargarStats(); }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Guardar';
        Swal.fire({ title:'Error de conexión', icon:'error', background:'#1c1c32', color:'#f0f0f5' });
    });
}

// ═══════════════════════════════════════════════════════════════════════════
//  CAMBIAR ESTADO — soft delete toggle
// ═══════════════════════════════════════════════════════════════════════════
function cambiarEstado(id, estadoActual) {
    const accion = estadoActual ? 'desactivar' : 'activar';
    const color  = estadoActual ? '#ef4444'    : '#22c55e';

    Swal.fire({
        title: `¿${accion.charAt(0).toUpperCase()+accion.slice(1)} parámetro?`,
        text:  `El registro quedará ${estadoActual ? 'inactivo' : 'activo'}.`,
        icon:  estadoActual ? 'warning' : 'question',
        background:'#1c1c32', color:'#f0f0f5',
        confirmButtonColor: color,
        cancelButtonColor: 'rgba(255,255,255,.08)',
        confirmButtonText: `<i class="fas fa-${estadoActual?'ban':'circle-check'}"></i> Sí, ${accion}`,
        cancelButtonText: 'Cancelar', showCancelButton: true,
    }).then(res => {
        if (!res.isConfirmed) return;
        fetch('index.php?c=admincatalogoparametros&a=CambiarEstado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        })
        .then(r => r.json())
        .then(d => {
            Swal.fire({
                title: d.success ? 'Listo' : 'Error', text: d.message,
                icon:  d.success ? 'success' : 'error',
                background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b',
            });
            if (d.success) { cargar(); cargarStats(); }
        })
        .catch(() => Swal.fire({ title:'Error de conexión', icon:'error',
            background:'#1c1c32', color:'#f0f0f5' }));
    });
}

// ═══════════════════════════════════════════════════════════════════════════
//  EXPORTAR EXCEL
// ═══════════════════════════════════════════════════════════════════════════
function exportarExcel() {
    const q   = encodeURIComponent(document.getElementById('inputBusqueda').value.trim());
    const mod = document.getElementById('selModelo').value;
    const grp = encodeURIComponent(document.getElementById('selGrupo').value);
    const est = document.getElementById('selEstado').value;

    Swal.fire({ title:'Generando Excel…', text:'Cargando todos los registros filtrados.',
        didOpen: () => Swal.showLoading(),
        background:'#1c1c32', color:'#f0f0f5', allowOutsideClick: false });

    fetch(`index.php?c=admincatalogoparametros&a=ListarTodo&q=${q}&modelo_id=${mod}&grupo=${grp}&estado=${est}`)
        .then(r => r.json())
        .then(d => {
            Swal.close();
            if (!d.success || !d.datos.length) {
                Swal.fire({ title:'Sin datos', text:'No hay registros para exportar.',
                    icon:'info', background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
                return;
            }
            const filas = d.datos.map(r => ({
                'ID':           r.id,
                'Modelo MR':    r.modelo_nombre,
                'Versión':      r.modelo_version,
                'Grupo':        r.grupo,
                'Valor':        r.valor,
                'Descripción':  r.descripcion || '',
                'Estado':       r.estado ? 'Activo' : 'Inactivo',
                'Fecha Creación': (r.fecha_creacion || '').slice(0, 10),
            }));

            const ws = XLSX.utils.json_to_sheet(filas);
            ws['!cols'] = [{wch:6},{wch:30},{wch:8},{wch:14},{wch:20},{wch:40},{wch:10},{wch:14}];
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'CatalogoParametros');
            XLSX.writeFile(wb, `etreva_catalogo_parametros_${new Date().toISOString().slice(0,10)}.xlsx`);
        })
        .catch(() => {
            Swal.close();
            Swal.fire({ title:'Error de conexión', icon:'error', background:'#1c1c32', color:'#f0f0f5' });
        });
}

// ═══════════════════════════════════════════════════════════════════════════
//  IMPRIMIR PDF
// ═══════════════════════════════════════════════════════════════════════════
function imprimirPDF() {
    const q   = encodeURIComponent(document.getElementById('inputBusqueda').value.trim());
    const mod = document.getElementById('selModelo').value;
    const grp = encodeURIComponent(document.getElementById('selGrupo').value);
    const est = document.getElementById('selEstado').value;

    Swal.fire({ title:'Preparando PDF…', text:'Cargando todos los registros.',
        didOpen: () => Swal.showLoading(),
        background:'#1c1c32', color:'#f0f0f5', allowOutsideClick: false });

    fetch(`index.php?c=admincatalogoparametros&a=ListarTodo&q=${q}&modelo_id=${mod}&grupo=${grp}&estado=${est}`)
        .then(r => r.json())
        .then(d => {
            Swal.close();
            if (!d.success || !d.datos.length) {
                Swal.fire({ title:'Sin datos', text:'No hay registros para imprimir.',
                    icon:'info', background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
                return;
            }
            const rows = d.datos.map(r => `<tr>
                <td>${r.id}</td>
                <td>${r.modelo_nombre||'—'} v${r.modelo_version||'?'}</td>
                <td>${r.grupo}</td>
                <td>${r.valor}</td>
                <td>${r.descripcion||'—'}</td>
                <td>${r.estado ? 'Activo' : 'Inactivo'}</td>
                <td>${(r.fecha_creacion||'').slice(0,10)}</td>
                <td>—</td>
            </tr>`).join('');
            document.getElementById('tbody').innerHTML = rows;
            document.getElementById('pagInfo').textContent =
                `${d.datos.length} registros (reporte completo)`;
            document.getElementById('pagBtns').innerHTML = '';
            setTimeout(() => window.print(), 300);
        })
        .catch(() => {
            Swal.close();
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