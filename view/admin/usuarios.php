<?php
/*
 * view/admin/usuarios.php
 * Panel de gestión de clientes — solo rol 2.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
if (!in_array((int)($_SESSION['rol'] ?? 0), [2, 3], true)) {
    header('Location: index.php?c=login&a=Index'); exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Etreva Admin — Clientes</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/menu.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<style>
:root {
    --bg:      #0f0f1a;
    --surface: #161625;
    --card:    #1c1c32;
    --border:  rgba(229,90,43,.15);
    --text:    #f0f0f5;
    --muted:   rgba(240,240,245,.45);
    --orange:  #e55a2b;
    --amber:   #f59e0b;
    --green:   #22c55e;
    --blue:    #38bdf8;
    --red:     #ef4444;
    --purple:  #a78bfa;
}
*{box-sizing:border-box;}
html,body{background:var(--bg)!important;color:var(--text)!important;min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;margin:0;padding:0;}

main{margin-left:280px;min-height:100vh;background:var(--bg);transition:margin-left .3s ease;}
main.min-main{margin-left:85px;}

/* TOPBAR */
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:14px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.topbar h1{font-size:1.1rem;font-weight:800;color:#fff;margin:0;display:flex;align-items:center;gap:10px;}
.topbar h1 span{background:linear-gradient(135deg,var(--orange),#ff9a6c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.topbar-right{display:flex;align-items:center;gap:10px;}
.btn-icon{background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:9px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.9rem;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-icon:hover{background:rgba(229,90,43,.12);color:var(--orange);}

/* BODY */
.body{padding:24px 32px 60px;display:flex;flex-direction:column;gap:20px;}

/* STAT STRIP */
.stat-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;}
.stat-pill{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:14px;cursor:default;transition:border-color .2s;}
.stat-pill:hover{border-color:rgba(229,90,43,.35);}
.stat-pill-ico{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.stat-pill-txt{font-size:1.35rem;font-weight:900;color:#fff;line-height:1;}
.stat-pill-lbl{font-size:.73rem;color:var(--muted);margin-top:2px;}

/* FILTROS */
.filtros-wrap{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:14px 18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.filtros-wrap input[type=text],
.filtros-wrap select{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;padding:8px 12px;color:var(--text);font-size:.82rem;outline:none;transition:border-color .2s;}
.filtros-wrap input[type=text]{flex:1;min-width:180px;}
.filtros-wrap input[type=text]:focus,.filtros-wrap select:focus{border-color:var(--orange);}
.filtros-wrap select option{background:#1c1c32;}
.btn-filtrar{background:linear-gradient(135deg,var(--orange),#ff9a6c);color:#fff;border:none;border-radius:9px;padding:8px 18px;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .2s;}
.btn-filtrar:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(229,90,43,.35);}

/* EXPORT BUTTONS */
.btn-export{padding:8px 14px;border-radius:9px;border:none;font-size:.78rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s;white-space:nowrap;}
.btn-excel{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25);}
.btn-excel:hover{background:rgba(34,197,94,.22);transform:translateY(-1px);}
.btn-pdf{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.2);}
.btn-pdf:hover{background:rgba(239,68,68,.2);transform:translateY(-1px);}
.export-sep{width:1px;height:28px;background:rgba(255,255,255,.08);margin:0 2px;}

/* TABS */
.tabs{display:flex;gap:4px;border-bottom:1px solid rgba(255,255,255,.07);}
.tab{padding:9px 18px;font-size:.82rem;font-weight:600;color:var(--muted);border-bottom:2px solid transparent;cursor:pointer;transition:all .2s;margin-bottom:-1px;border-radius:6px 6px 0 0;}
.tab:hover{color:var(--text);}
.tab.active{color:var(--orange);border-bottom-color:var(--orange);background:rgba(229,90,43,.06);}
.tab-badge{display:none;align-items:center;justify-content:center;background:var(--red);color:#fff;border-radius:10px;font-size:.6rem;font-weight:800;min-width:16px;height:16px;padding:0 4px;margin-left:6px;}

/* TABLA */
.tabla-wrap{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;}
.adm-table{width:100%;border-collapse:collapse;font-size:.82rem;}
.adm-table thead th{background:rgba(255,255,255,.03);color:var(--muted);font-weight:600;font-size:.7rem;letter-spacing:1px;text-transform:uppercase;padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.07);white-space:nowrap;}
.adm-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;}
.adm-table tbody tr:hover{background:rgba(229,90,43,.05);cursor:pointer;}
.adm-table tbody td{padding:12px 14px;color:var(--text);vertical-align:middle;}
.av{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--orange),#ff9a6c);display:inline-flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:800;color:#fff;flex-shrink:0;}

/* BADGES */
.badge{font-size:.65rem;font-weight:700;padding:3px 9px;border-radius:20px;white-space:nowrap;}
.b-activo            {background:rgba(34,197,94,.12); color:var(--green);}
.b-inactivo          {background:rgba(239,68,68,.1);  color:var(--red);}
.b-suspendido        {background:rgba(167,139,250,.12);color:var(--purple);}
.b-pendiente         {background:rgba(245,158,11,.12); color:var(--amber);}
.b-pendiente-correo  {background:rgba(56,189,248,.1);  color:var(--blue);}
.b-pago-pendiente    {background:rgba(245,158,11,.18); color:var(--amber);border:1px solid rgba(245,158,11,.3);animation:pulse 2s infinite;}
.b-pago-activo       {background:rgba(34,197,94,.1);   color:var(--green);}
.b-pago-rechazado    {background:rgba(239,68,68,.1);   color:var(--red);}
.b-sin-plan          {background:rgba(255,255,255,.06);color:var(--muted);}
.b-verificado        {background:rgba(34,197,94,.12);  color:var(--green);}
.b-no-verificado     {background:rgba(245,158,11,.12); color:var(--amber);}

@keyframes pulse{0%,100%{opacity:1}50%{opacity:.6}}

/* ACCIONES */
.acciones{display:flex;gap:6px;flex-wrap:wrap;}
.btn-sm{padding:5px 10px;border-radius:7px;font-size:.72rem;font-weight:700;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;transition:all .2s;white-space:nowrap;}
.btn-aprobar  {background:rgba(34,197,94,.15); color:var(--green);}
.btn-aprobar:hover  {background:rgba(34,197,94,.28); transform:translateY(-1px);}
.btn-rechazar {background:rgba(239,68,68,.12); color:var(--red);}
.btn-rechazar:hover {background:rgba(239,68,68,.25); transform:translateY(-1px);}
.btn-ver      {background:rgba(56,189,248,.1);  color:var(--blue);}
.btn-ver:hover {background:rgba(56,189,248,.22); transform:translateY(-1px);}
.btn-email    {background:rgba(245,158,11,.1);  color:var(--amber);}
.btn-email:hover {background:rgba(245,158,11,.22); transform:translateY(-1px);}
.btn-desact   {background:rgba(167,139,250,.1); color:var(--purple);}
.btn-desact:hover {background:rgba(167,139,250,.22); transform:translateY(-1px);}
.btn-reactivar{background:rgba(34,197,94,.12);  color:var(--green);}
.btn-reactivar:hover{background:rgba(34,197,94,.25); transform:translateY(-1px);}

/* CHIPS */
.dias-chip{font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:6px;}
.dias-urgente{background:rgba(239,68,68,.2);  color:var(--red);}
.dias-ok     {background:rgba(34,197,94,.1);  color:var(--green);}

/* PAGINACIÓN */
.paginacion{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-top:1px solid rgba(255,255,255,.06);font-size:.78rem;color:var(--muted);}
.pag-btns{display:flex;gap:4px;}
.pag-btn{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:7px;padding:5px 11px;color:var(--muted);cursor:pointer;font-size:.75rem;transition:all .2s;}
.pag-btn:hover,.pag-btn.active{background:rgba(229,90,43,.15);color:var(--orange);border-color:rgba(229,90,43,.3);}
.pag-btn:disabled{opacity:.3;cursor:not-allowed;}

/* MODAL */
.modal-overlay{display:none;position:fixed;inset:0;z-index:1000;background:rgba(8,8,18,.85);backdrop-filter:blur(6px);align-items:center;justify-content:center;}
.modal-overlay.show{display:flex;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:20px;width:92%;max-width:640px;max-height:90vh;overflow-y:auto;animation:popIn .22s ease;}
@keyframes popIn{from{transform:scale(.92) translateY(12px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
.modal-header{padding:20px 24px 16px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--card);z-index:1;}
.modal-header h3{font-size:1rem;font-weight:800;color:#fff;margin:0;}
.modal-close{background:none;border:none;color:var(--muted);font-size:1.1rem;cursor:pointer;width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;transition:all .2s;}
.modal-close:hover{background:rgba(255,255,255,.08);color:#fff;}
.modal-body{padding:20px 24px;}
.modal-footer{padding:14px 24px 20px;border-top:1px solid rgba(255,255,255,.07);display:flex;gap:8px;flex-wrap:wrap;}

/* DETALLE GRID */
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;}
.detail-field label{display:block;font-size:.67rem;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px;}
.detail-field span{font-size:.85rem;color:var(--text);font-weight:500;}
.section-sep{font-size:.67rem;color:var(--orange);text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin:16px 0 10px;display:flex;align-items:center;gap:8px;}
.section-sep::after{content:'';flex:1;height:1px;background:rgba(229,90,43,.2);}

/* COMPROBANTE */
.comprobante-wrap{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:14px;text-align:center;margin-bottom:12px;}
.comprobante-wrap img{max-width:100%;max-height:320px;border-radius:8px;object-fit:contain;}
.no-comp{color:var(--muted);font-size:.82rem;padding:20px 0;}
.btn-ver-comp{display:inline-flex;align-items:center;gap:6px;background:rgba(56,189,248,.1);color:var(--blue);border:none;border-radius:8px;padding:7px 14px;font-size:.78rem;font-weight:600;cursor:pointer;text-decoration:none;margin-top:8px;transition:all .2s;}
.btn-ver-comp:hover{background:rgba(56,189,248,.22);}

/* HISTORIAL */
.historial-item{display:flex;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:.78rem;}
.historial-item:last-child{border-bottom:none;}
.hist-dot{width:7px;height:7px;border-radius:50%;margin-top:4px;flex-shrink:0;}

/* SKELETON */
.skeleton{background:linear-gradient(90deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.09) 50%,rgba(255,255,255,.04) 100%);background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:8px;}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

/* EMPTY */
.empty-state{text-align:center;padding:50px 20px;color:var(--muted);}
.empty-state i{font-size:2.5rem;margin-bottom:12px;display:block;opacity:.3;}
.empty-state p{font-size:.85rem;}

/* URGENTE ROW */
tr.row-urgente td{background:rgba(239,68,68,.04);}

/* PRINT */
@media print {
    .topbar,.stat-strip,.filtros-wrap,.tabs,.acciones,.pag-btns,
    #adminMenu,.modal-overlay,button,.btn-sm,.btn-export{display:none!important;}
    main{margin-left:0!important;}
    body,html{background:#fff!important;color:#000!important;}
    .tabla-wrap{border:1px solid #ccc!important;border-radius:0!important;}
    .adm-table thead th{background:#f5f5f5!important;color:#333!important;border-bottom:1px solid #ccc!important;}
    .adm-table tbody tr{border-bottom:1px solid #eee!important;}
    .adm-table tbody td{color:#222!important;}
    .badge{border:1px solid #ccc!important;color:#333!important;background:#f9f9f9!important;}
    .print-header{display:block!important;}
    .paginacion{display:none!important;}
}
.print-header{display:none;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #e55a2b;}
.print-header h2{font-size:1.2rem;font-weight:800;color:#e55a2b;margin:0 0 4px;}
.print-header p{font-size:.75rem;color:#666;margin:0;}

@media(max-width:768px){
    main,main.min-main{margin-left:0;}
    .body{padding:14px 12px 40px;}
    .stat-strip{grid-template-columns:repeat(2,1fr);}
    .detail-grid{grid-template-columns:1fr;}
}
</style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main>

<!-- TOPBAR -->
<div class="topbar">
    <h1><i class="fas fa-users" style="color:var(--orange);"></i> Gestión de <span>Clientes</span></h1>
    <div class="topbar-right">
        <div class="btn-icon" id="btnRefresh" title="Actualizar"><i class="fas fa-rotate-right"></i></div>
        <a class="btn-icon" href="index.php?c=admin&a=Inicio" title="Dashboard"><i class="fas fa-grid-2"></i></a>
        <a class="btn-icon" href="index.php?c=login&a=Logout" title="Salir" style="color:#ef4444;"><i class="fas fa-right-from-bracket"></i></a>
    </div>
</div>

<div class="body">

    <!-- STAT STRIP -->
    <div class="stat-strip">
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(229,90,43,.14);"><i class="fas fa-users" style="color:var(--orange);"></i></div>
            <div><div class="stat-pill-txt" id="st-total">—</div><div class="stat-pill-lbl">Total clientes</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(34,197,94,.12);"><i class="fas fa-user-check" style="color:var(--green);"></i></div>
            <div><div class="stat-pill-txt" id="st-activos">—</div><div class="stat-pill-lbl">Cuentas activas</div></div>
        </div>
        <div class="stat-pill" style="cursor:pointer;" onclick="setTab('pendientes')">
            <div class="stat-pill-ico" style="background:rgba(245,158,11,.12);"><i class="fas fa-clock" style="color:var(--amber);"></i></div>
            <div><div class="stat-pill-txt" id="st-pendientes">—</div><div class="stat-pill-lbl">Pagos pendientes</div></div>
        </div>
        <div class="stat-pill" style="cursor:pointer;" onclick="setTab('inactivos')">
            <div class="stat-pill-ico" style="background:rgba(239,68,68,.1);"><i class="fas fa-user-slash" style="color:var(--red);"></i></div>
            <div><div class="stat-pill-txt" id="st-inactivos">—</div><div class="stat-pill-lbl">Cuentas inactivas</div></div>
        </div>
    </div>

    <!-- FILTROS + EXPORT -->
    <div class="filtros-wrap">
        <input type="text" id="inputBusqueda" placeholder="🔍  Buscar nombre, apellido o email…" oninput="debounceSearch()">
        <select id="selEstado" onchange="resetPage(); cargarTabla()">
            <option value="">Todos los estados</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
            <option value="suspendido">Suspendidos</option>
            <option value="pendiente_correo">Pendiente correo</option>
            <option value="inactivo_suspendido" style="display:none">Inactivos y Suspendidos</option>
        </select>
        <select id="selPagoEstado" onchange="resetPage(); cargarTabla()">
            <option value="">Todos los pagos</option>
            <option value="pendiente_pago">Pago pendiente</option>
            <option value="activo_pagado">Pago activo</option>
            <option value="sin_plan">Sin plan</option>
        </select>
        <button class="btn-filtrar" onclick="resetPage(); cargarTabla()"><i class="fas fa-filter"></i> Filtrar</button>
        <div class="export-sep"></div>
        <button class="btn-export btn-excel" onclick="exportarExcel()" title="Exportar a Excel">
            <i class="fas fa-file-excel"></i> Excel
        </button>
        <button class="btn-export btn-pdf" onclick="exportarPDF()" title="Imprimir / PDF">
            <i class="fas fa-file-pdf"></i> PDF
        </button>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <div class="tab active" data-tab="todos"      onclick="setTab('todos')">Todos</div>
        <div class="tab"        data-tab="pendientes" onclick="setTab('pendientes')">
            Pagos por aprobar <span class="tab-badge" id="tabBadgePend">0</span>
        </div>
        <div class="tab"        data-tab="inactivos"  onclick="setTab('inactivos')">Inactivos / Suspendidos</div>
    </div>

    <!-- TABLA -->
    <div class="tabla-wrap" id="tablaWrap">
        <!-- Print header (hidden on screen) -->
        <div class="print-header" id="printHeader">
            <h2>Etreva — Gestión de Clientes</h2>
            <p id="printSubtitle">Generado el <?= date('d/m/Y H:i') ?></p>
        </div>
        <table class="adm-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>Estado cuenta</th>
                    <th>Pago / Verificado</th>
                    <th>Plan / Monto</th>
                    <th>Vencimiento</th>
                    <th class="no-print">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyClientes">
                <tr><td colspan="8" style="padding:30px;"><div class="skeleton" style="height:18px;"></div></td></tr>
            </tbody>
        </table>
        <div class="paginacion">
            <span id="pagInfo">Cargando…</span>
            <div class="pag-btns" id="pagBtns"></div>
        </div>
    </div>

</div>
</main>

<!-- MODAL DETALLE -->
<div class="modal-overlay" id="modalDetalle">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo">Detalle del cliente</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body"   id="modalBody"><div class="skeleton" style="height:200px;"></div></div>
        <div class="modal-footer" id="modalFooter"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ═══════════════════════════════════════════════════════════════════════════
//  ESTADO
// ═══════════════════════════════════════════════════════════════════════════
const pag = { page: 1, total: 0, pages: 1 };
let tabDoble = false;
let _dTimer = null;

// Cache de todos los datos visibles para export
let _exportCache = [];

function debounceSearch() {
    clearTimeout(_dTimer);
    _dTimer = setTimeout(() => { resetPage(); cargarTabla(); }, 400);
}
function resetPage() { pag.page = 1; }

// ═══════════════════════════════════════════════════════════════════════════
//  TABS
// ═══════════════════════════════════════════════════════════════════════════
function setTab(tab) {
    resetPage();
    tabDoble = false;
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`[data-tab="${tab}"]`)?.classList.add('active');

    const selE = document.getElementById('selEstado');
    const selP = document.getElementById('selPagoEstado');

    if (tab === 'pendientes') {
        selP.value = 'pendiente_pago';
        selE.value = '';
    } else if (tab === 'inactivos') {
        tabDoble = true;
        selE.value = '';
        selP.value = '';
    } else {
        selE.value = '';
        selP.value = '';
    }
    cargarTabla();
}

// ═══════════════════════════════════════════════════════════════════════════
//  CARGAR TABLA
// ═══════════════════════════════════════════════════════════════════════════
function cargarTabla() {
    const q       = document.getElementById('inputBusqueda').value.trim();
    const est     = document.getElementById('selEstado').value;
    const pag_est = document.getElementById('selPagoEstado').value;

    document.getElementById('tbodyClientes').innerHTML =
        '<tr><td colspan="8" style="padding:28px;"><div class="skeleton" style="height:18px;"></div></td></tr>';

    if (tabDoble) {
        const urlInact = `index.php?c=adminUsuarios&a=Listar&q=${encodeURIComponent(q)}&estado=inactivo&pago_estado=${pag_est}&page=${pag.page}`;
        const urlSusp  = `index.php?c=adminUsuarios&a=Listar&q=${encodeURIComponent(q)}&estado=suspendido&pago_estado=${pag_est}&page=${pag.page}`;

        Promise.all([fetch(urlInact), fetch(urlSusp)])
            .then(rs => Promise.all(rs.map(r => r.json())))
            .then(([dI, dS]) => {
                const clientes = [
                    ...(dI.success ? dI.clientes : []),
                    ...(dS.success ? dS.clientes : []),
                ];
                const total = (dI.success ? dI.total : 0) + (dS.success ? dS.total : 0);
                pag.total = total;
                pag.pages = Math.max(dI.pages || 1, dS.pages || 1);
                _exportCache = clientes;
                if (!clientes.length) { renderVacia('Sin clientes con esos filtros'); renderPag(); return; }
                renderTabla(clientes);
                renderPag();
            })
            .catch(err => { console.error(err); renderVacia('Error de conexión'); });
        return;
    }

    const url = `index.php?c=adminUsuarios&a=Listar&q=${encodeURIComponent(q)}&estado=${est}&pago_estado=${pag_est}&page=${pag.page}`;
    fetch(url)
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(d => {
            if (!d.success) { renderVacia(d.message || 'Error al cargar'); return; }
            pag.total  = d.total;
            pag.pages  = d.pages;
            _exportCache = d.clientes;
            renderTabla(d.clientes);
            renderPag();
        })
        .catch(err => { console.error(err); renderVacia('Error de conexión — verificá que estés logueado como admin'); });
}

// ═══════════════════════════════════════════════════════════════════════════
//  RENDER TABLA
// ═══════════════════════════════════════════════════════════════════════════
function ini(n, a) { return ((n?.[0]||'')+(a?.[0]||'')).toUpperCase() || '?'; }

function estadoBadge(est) {
    const m = {activo:'b-activo',inactivo:'b-inactivo',suspendido:'b-suspendido',
               pendiente_correo:'b-pendiente-correo',pendiente:'b-pendiente'};
    return `<span class="badge ${m[est]||'b-sin-plan'}">${(est||'—').replace(/_/g,' ')}</span>`;
}
function pagoBadge(est) {
    if (!est) return '<span class="badge b-sin-plan">Sin plan</span>';
    const m = {pendiente_pago:'b-pago-pendiente',activo_pagado:'b-pago-activo',rechazado:'b-pago-rechazado'};
    return `<span class="badge ${m[est]||'b-sin-plan'}">${est.replace(/_/g,' ')}</span>`;
}
function verificadoBadge(v, estado) {
    if (estado === 'activo_pagado' && (v === true || v === 'true' || v === '1')) {
        return '<span class="badge b-verificado"><i class="fas fa-check-circle"></i> Verificado</span>';
    }
    if (estado === 'pendiente_pago') {
        return '<span class="badge b-no-verificado"><i class="fas fa-clock"></i> Pendiente</span>';
    }
    if (estado === 'rechazado') {
        return '<span class="badge b-pago-rechazado"><i class="fas fa-xmark-circle"></i> Rechazado</span>';
    }
    return '<span class="badge b-sin-plan">—</span>';
}
function fmtFecha(f) {
    if (!f) return '<span style="color:var(--muted);">—</span>';
    return new Date(f).toLocaleDateString('es-ES', {day:'2-digit',month:'short',year:'numeric'});
}
function fmtFechaPlain(f) {
    if (!f) return '—';
    return new Date(f).toLocaleDateString('es-ES', {day:'2-digit',month:'short',year:'numeric'});
}
function diasRestantes(venc, estado) {
    if (!venc || estado !== 'activo_pagado') return '';
    const diff = Math.ceil((new Date(venc) - new Date()) / 86400000);
    if (diff < 0) return `<span class="dias-chip dias-urgente">${Math.abs(diff)}d vencido</span>`;
    return `<span class="dias-chip dias-ok">${diff}d</span>`;
}
function calcDias(fecha) {
    if (!fecha) return 0;
    return Math.floor((Date.now() - new Date(fecha).getTime()) / 86400000);
}
function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function renderTabla(clientes) {
    if (!clientes || !clientes.length) { renderVacia('Sin clientes con esos filtros'); return; }

    const rows = clientes.map(c => {
        const urgente  = c.pago_estado === 'pendiente_pago' && calcDias(c.pago_fecha) >= 2;
        const diasChip = c.pago_estado === 'pendiente_pago'
            ? (urgente
                ? `<span class="dias-chip dias-urgente">⚡ ${calcDias(c.pago_fecha)}d — Urgente</span>`
                : `<span class="dias-chip dias-ok">${calcDias(c.pago_fecha)}d pendiente</span>`)
            : '';

        return `<tr class="${urgente?'row-urgente':''}" onclick="verCliente(${c.id})">
            <td style="color:var(--muted);font-size:.73rem;">${c.id}</td>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="av">${ini(c.nombre, c.apellido)}</span>
                    <div>
                        <div style="font-weight:700;">${escHtml(c.nombre)||''} ${escHtml(c.apellido)||''}</div>
                        <div style="font-size:.7rem;margin-top:2px;">${diasChip}</div>
                    </div>
                </div>
            </td>
            <td style="color:var(--muted);font-size:.78rem;">${escHtml(c.email)||'—'}</td>
            <td>${estadoBadge(c.estado)}</td>
            <td>
                ${pagoBadge(c.pago_estado)}
                <div style="margin-top:4px;">${verificadoBadge(c.pago_verificado, c.pago_estado)}</div>
            </td>
            <td>
                <div style="font-size:.8rem;font-weight:700;color:var(--orange);">${escHtml(c.pago_plan)||'—'}</div>
                <div style="font-size:.7rem;color:var(--amber);">${c.pago_monto ? 'Bs. '+c.pago_monto : ''}</div>
            </td>
            <td style="font-size:.75rem;">
                ${fmtFecha(c.pago_vencimiento)}
                <div style="margin-top:3px;">${diasRestantes(c.pago_vencimiento, c.pago_estado)}</div>
            </td>
            <td onclick="event.stopPropagation();">
                <div class="acciones">${construirAcciones(c)}</div>
            </td>
        </tr>`;
    }).join('');

    document.getElementById('tbodyClientes').innerHTML = rows;
}

function construirAcciones(c) {
    let b = `<button class="btn-sm btn-ver" onclick="verCliente(${c.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>`;

    if (c.pago_estado === 'pendiente_pago') {
        const nm = escHtml((c.nombre||'')+' '+(c.apellido||''));
        b += `<button class="btn-sm btn-aprobar" onclick="aprobarPago(${c.id},'${nm}')" title="Aprobar pago">
                  <i class="fas fa-check"></i> Aprobar
              </button>`;
        b += `<button class="btn-sm btn-rechazar" onclick="rechazarPago(${c.id},'${nm}')" title="Rechazar pago">
                  <i class="fas fa-xmark"></i>
              </button>`;
    }
    if (c.estado === 'inactivo' || c.estado === 'suspendido') {
        b += `<button class="btn-sm btn-email" onclick="enviarOferta(${c.id},'${escHtml(c.nombre||'')}')" title="Email oferta">
                  <i class="fas fa-envelope"></i>
              </button>`;
        b += `<button class="btn-sm btn-reactivar" onclick="cambiarEstado(${c.id},'activo','${escHtml((c.nombre||'')+' '+(c.apellido||''))}')" title="Reactivar cuenta">
                  <i class="fas fa-rotate-left"></i>
              </button>`;
    }
    if (c.estado === 'activo') {
        b += `<button class="btn-sm btn-desact" onclick="cambiarEstado(${c.id},'suspendido','${escHtml((c.nombre||'')+' '+(c.apellido||''))}')" title="Suspender">
                  <i class="fas fa-ban"></i>
              </button>`;
    }
    return b;
}

function renderVacia(msg) {
    document.getElementById('tbodyClientes').innerHTML =
        `<tr><td colspan="8"><div class="empty-state">
            <i class="fas fa-users-slash"></i><p>${msg}</p>
        </div></td></tr>`;
}

// ═══════════════════════════════════════════════════════════════════════════
//  PAGINACIÓN
// ═══════════════════════════════════════════════════════════════════════════
function renderPag() {
    const {page, total, pages} = pag;
    document.getElementById('pagInfo').textContent =
        `Página ${page} de ${pages} · ${total} cliente${total !== 1 ? 's' : ''}`;

    let b = `<button class="pag-btn" onclick="irPag(${page-1})" ${page<=1?'disabled':''}>‹</button>`;
    const s = Math.max(1, page-2), e = Math.min(pages, page+2);
    for (let i = s; i <= e; i++)
        b += `<button class="pag-btn ${i===page?'active':''}" onclick="irPag(${i})">${i}</button>`;
    b += `<button class="pag-btn" onclick="irPag(${page+1})" ${page>=pages?'disabled':''}>›</button>`;
    document.getElementById('pagBtns').innerHTML = b;
}
function irPag(p) {
    if (p < 1 || p > pag.pages) return;
    pag.page = p;
    cargarTabla();
}

// ═══════════════════════════════════════════════════════════════════════════
//  STATS STRIP
// ═══════════════════════════════════════════════════════════════════════════
function actualizarStats() {
    fetch('index.php?c=adminUsuarios&a=Listar&page=1')
        .then(r => r.json())
        .then(d => { if (d.success) document.getElementById('st-total').textContent = d.total; })
        .catch(()=>{});

    fetch('index.php?c=adminUsuarios&a=Listar&estado=activo&page=1')
        .then(r => r.json())
        .then(d => { if (d.success) document.getElementById('st-activos').textContent = d.total; })
        .catch(()=>{});

    fetch('index.php?c=adminUsuarios&a=PagosPendientes')
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            const n = d.total;
            document.getElementById('st-pendientes').textContent = n;
            const badge = document.getElementById('tabBadgePend');
            badge.textContent = n;
            badge.style.display = n > 0 ? 'inline-flex' : 'none';
        })
        .catch(()=>{});

    fetch('index.php?c=adminUsuarios&a=Inactivos')
        .then(r => r.json())
        .then(d => {
            if (d.success) document.getElementById('st-inactivos').textContent = d.total ?? d.clientes?.length ?? 0;
        })
        .catch(()=>{});
}

// ═══════════════════════════════════════════════════════════════════════════
//  EXPORT EXCEL — usa datos en caché de la vista actual
// ═══════════════════════════════════════════════════════════════════════════
function exportarExcel() {
    if (!_exportCache.length) {
        Swal.fire({title:'Sin datos',text:'Cargá primero la tabla.',icon:'info',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});
        return;
    }

    // Determinar nombre de pestaña activa
    const tabActiva = document.querySelector('.tab.active')?.dataset?.tab || 'clientes';
    const tabNombre = {todos:'Todos',pendientes:'Pagos Pendientes',inactivos:'Inactivos-Suspendidos'}[tabActiva] || 'Clientes';

    // Filtros activos para subtítulo
    const q   = document.getElementById('inputBusqueda').value.trim();
    const est = document.getElementById('selEstado').value;
    const pst = document.getElementById('selPagoEstado').value;
    const filtroDesc = [q && `Búsqueda: "${q}"`, est && `Estado: ${est}`, pst && `Pago: ${pst}`].filter(Boolean).join(' | ') || 'Sin filtros';

    // Filas de datos
    const filas = _exportCache.map(c => ({
        'ID':              c.id,
        'Nombre':          ((c.nombre||'') + ' ' + (c.apellido||'')).trim(),
        'Email':           c.email || '—',
        'Estado cuenta':   (c.estado || '—').replace(/_/g,' '),
        'Estado pago':     (c.pago_estado || 'Sin plan').replace(/_/g,' '),
        'Verificado':      (c.pago_verificado === true || c.pago_verificado === 'true' || c.pago_verificado === '1') ? 'Sí' : 'No',
        'Plan':            c.pago_plan || '—',
        'Monto (Bs.)':     c.pago_monto || '',
        'Período':         c.pago_periodo || '—',
        'Fecha pago':      fmtFechaPlain(c.pago_fecha),
        'Vencimiento':     fmtFechaPlain(c.pago_vencimiento),
    }));

    // Hoja clientes
    const ws = XLSX.utils.json_to_sheet(filas);
    ws['!cols'] = [
        {wch:6},{wch:28},{wch:30},{wch:18},{wch:18},{wch:12},{wch:22},{wch:12},{wch:12},{wch:16},{wch:16}
    ];

    // Hoja resumen
    const resumen = [
        ['Reporte Etreva — Clientes', ''],
        ['Sección:', tabNombre],
        ['Filtros:', filtroDesc],
        ['Total registros:', _exportCache.length],
        ['Generado:', new Date().toLocaleString('es-ES')],
        ['', ''],
        ['Estado',        'Cantidad'],
        ['Activos',       _exportCache.filter(c=>c.estado==='activo').length],
        ['Inactivos',     _exportCache.filter(c=>c.estado==='inactivo').length],
        ['Suspendidos',   _exportCache.filter(c=>c.estado==='suspendido').length],
        ['Pago pendiente',_exportCache.filter(c=>c.pago_estado==='pendiente_pago').length],
        ['Pago activo',   _exportCache.filter(c=>c.pago_estado==='activo_pagado').length],
    ];
    const wsRes = XLSX.utils.aoa_to_sheet(resumen);
    wsRes['!cols'] = [{wch:22},{wch:36}];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Clientes');
    XLSX.utils.book_append_sheet(wb, wsRes, 'Resumen');

    const fecha = new Date().toISOString().slice(0,10);
    XLSX.writeFile(wb, `etreva_clientes_${tabActiva}_${fecha}.xlsx`);
}

// ═══════════════════════════════════════════════════════════════════════════
//  EXPORT PDF — window.print() con @media print
// ═══════════════════════════════════════════════════════════════════════════
function exportarPDF() {
    // Actualizar subtítulo del print header
    const tabActiva = document.querySelector('.tab.active')?.dataset?.tab || 'clientes';
    const tabNombre = {todos:'Todos los clientes',pendientes:'Pagos pendientes',inactivos:'Inactivos / Suspendidos'}[tabActiva] || 'Clientes';
    const q   = document.getElementById('inputBusqueda').value.trim();
    const est = document.getElementById('selEstado').value;
    const pst = document.getElementById('selPagoEstado').value;
    const filtros = [q && `"${q}"`, est, pst].filter(Boolean).join(', ');

    document.getElementById('printSubtitle').textContent =
        `Sección: ${tabNombre}${filtros ? ' · Filtros: '+filtros : ''} · Generado: ${new Date().toLocaleString('es-ES')} · Total: ${pag.total} clientes`;

    window.print();
}

// ═══════════════════════════════════════════════════════════════════════════
//  MODAL DETALLE
// ═══════════════════════════════════════════════════════════════════════════
function verCliente(id) {
    document.getElementById('modalDetalle').classList.add('show');
    document.getElementById('modalBody').innerHTML = '<div class="skeleton" style="height:260px;"></div>';
    document.getElementById('modalFooter').innerHTML = '';
    document.getElementById('modalTitulo').textContent = 'Cargando…';

    fetch(`index.php?c=adminUsuarios&a=Ver&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                document.getElementById('modalBody').innerHTML = `<p style="color:var(--red);">${d.message}</p>`;
                return;
            }
            renderModalDetalle(d.cliente);
        })
        .catch(() => {
            document.getElementById('modalBody').innerHTML = '<p style="color:var(--red);">Error de conexión</p>';
        });
}

function renderModalDetalle(c) {
    document.getElementById('modalTitulo').textContent =
        `${c.nombre||''} ${c.apellido||''} — #${c.id}`;

    let compHtml;
    if (c.pago_comprobante) {
        compHtml = `<div class="comprobante-wrap">
            <img src="${escHtml(c.pago_comprobante)}" alt="Comprobante"
                 onerror="this.outerHTML='<div class=\\'no-comp\\'>⚠️ No se pudo cargar la imagen</div>'">
            <br>
            <a class="btn-ver-comp" href="${escHtml(c.pago_comprobante)}" target="_blank" rel="noopener">
                <i class="fas fa-external-link-alt"></i> Abrir comprobante
            </a>
        </div>`;
    } else {
        compHtml = `<div class="comprobante-wrap"><div class="no-comp">
            <i class="fas fa-file-slash" style="font-size:1.8rem;display:block;margin-bottom:8px;opacity:.3;"></i>
            Sin comprobante adjunto
        </div></div>`;
    }

    let histHtml = '';
    if (c.historial_estado?.length) {
        histHtml = c.historial_estado.map(h => `
            <div class="historial-item">
                <div class="hist-dot" style="background:${h.estado_nuevo==='activo'?'#22c55e':h.estado_nuevo==='suspendido'?'#a78bfa':'#ef4444'};"></div>
                <div>
                    <span style="font-weight:600;">${(h.estado_anterior||'?').replace(/_/g,' ')} → ${(h.estado_nuevo||'?').replace(/_/g,' ')}</span>
                    ${h.motivo ? `<span style="color:var(--muted);"> · ${escHtml(h.motivo)}</span>` : ''}
                    <div style="color:var(--muted);font-size:.68rem;">${h.fecha||''} · Admin #${h.admin_id}</div>
                </div>
            </div>`).join('');
    } else {
        histHtml = '<div style="color:var(--muted);font-size:.78rem;">Sin cambios de estado registrados</div>';
    }

    let alertaHtml = '';
    if (c.pago_estado === 'pendiente_pago' && c.pago_fecha) {
        const d = calcDias(c.pago_fecha);
        if (d >= 2) alertaHtml = `
            <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);
                        border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:.8rem;color:var(--red);">
                <i class="fas fa-triangle-exclamation"></i>
                <strong>Atención:</strong> Han pasado <strong>${d} días</strong> sin aprobar este comprobante.
            </div>`;
    }

    const verBadge = verificadoBadge(c.pago_verificado, c.pago_estado);

    document.getElementById('modalBody').innerHTML = `
        ${alertaHtml}
        <div class="detail-grid">
            <div class="detail-field"><label>Nombre</label><span>${escHtml((c.nombre||'')+(c.apellido?' '+c.apellido:''))}</span></div>
            <div class="detail-field"><label>Email</label><span>${escHtml(c.email||'—')}</span></div>
            <div class="detail-field"><label>Teléfono</label><span>${escHtml(c.telefono||'—')}</span></div>
            <div class="detail-field"><label>Estado cuenta</label><span>${estadoBadge(c.estado)}</span></div>
        </div>
        <div class="section-sep"><i class="fas fa-credit-card"></i> Información de pago</div>
        <div class="detail-grid">
            <div class="detail-field"><label>Estado pago</label><span>${pagoBadge(c.pago_estado)}</span></div>
            <div class="detail-field"><label>Verificado</label><span>${verBadge}</span></div>
            <div class="detail-field"><label>Plan</label><span style="color:var(--orange);font-weight:700;">${escHtml(c.pago_plan||'—')}</span></div>
            <div class="detail-field"><label>Monto</label><span style="color:var(--amber);font-weight:700;">${c.pago_monto?'Bs. '+c.pago_monto:'—'}</span></div>
            <div class="detail-field"><label>Período</label><span>${escHtml(c.pago_periodo||'—')}</span></div>
            <div class="detail-field"><label>Motivo pago</label><span>${escHtml((c.pago_motivo||'—').replace(/_/g,' '))}</span></div>
            <div class="detail-field"><label>Fecha de pago</label><span>${fmtFecha(c.pago_fecha)}</span></div>
            <div class="detail-field"><label>Vencimiento</label><span>${fmtFecha(c.pago_vencimiento)}</span></div>
            <div class="detail-field"><label>Días totales</label><span>${c.pago_dias_totales||'—'}</span></div>
            <div class="detail-field"><label>Carryover (días)</label><span>${c.pago_carryover||0}</span></div>
            ${c.aprobado_por ? `<div class="detail-field"><label>Aprobado por</label><span>Admin #${c.aprobado_por} · ${fmtFecha(c.fecha_aprobacion)}</span></div>` : ''}
            ${c.motivo_rechazo ? `<div class="detail-field" style="grid-column:span 2;"><label>Motivo de rechazo</label><span style="color:var(--red);">${escHtml(c.motivo_rechazo)}</span></div>` : ''}
        </div>
        <div class="section-sep"><i class="fas fa-receipt"></i> Comprobante de pago</div>
        ${compHtml}
        <div class="section-sep"><i class="fas fa-clock-rotate-left"></i> Historial de estado</div>
        <div>${histHtml}</div>
        ${c.emails_enviados > 0 ? `
        <div style="margin-top:12px;font-size:.75rem;color:var(--muted);">
            <i class="fas fa-envelope"></i> Emails de oferta enviados: <strong>${c.emails_enviados}</strong>
            ${c.ultimo_email ? ` · Último: ${fmtFecha(c.ultimo_email)}` : ''}
        </div>` : ''}
    `;

    let fbtns = '';
    if (c.pago_estado === 'pendiente_pago') {
        const nm = escHtml((c.nombre||'')+' '+(c.apellido||''));
        fbtns += `<button class="btn-sm btn-aprobar" style="padding:9px 18px;font-size:.82rem;" onclick="aprobarPago(${c.id},'${nm}')"><i class="fas fa-check-circle"></i> Aprobar pago</button>`;
        fbtns += `<button class="btn-sm btn-rechazar" style="padding:9px 18px;font-size:.82rem;" onclick="rechazarPago(${c.id},'${nm}')"><i class="fas fa-xmark-circle"></i> Rechazar</button>`;
    }
    if (c.estado === 'inactivo' || c.estado === 'suspendido') {
        fbtns += `<button class="btn-sm btn-email" style="padding:9px 18px;font-size:.82rem;" onclick="enviarOferta(${c.id},'${escHtml(c.nombre||'')}')"><i class="fas fa-envelope"></i> Enviar oferta</button>`;
        fbtns += `<button class="btn-sm btn-reactivar" style="padding:9px 18px;font-size:.82rem;" onclick="cambiarEstado(${c.id},'activo','${escHtml((c.nombre||'')+' '+(c.apellido||''))}')"><i class="fas fa-rotate-left"></i> Reactivar</button>`;
    }
    if (c.estado === 'activo') {
        fbtns += `<button class="btn-sm btn-desact" style="padding:9px 14px;font-size:.78rem;" onclick="cambiarEstado(${c.id},'suspendido','${escHtml((c.nombre||'')+' '+(c.apellido||''))}')"><i class="fas fa-ban"></i> Suspender</button>`;
        fbtns += `<button class="btn-sm btn-rechazar" style="padding:9px 14px;font-size:.78rem;" onclick="cambiarEstado(${c.id},'inactivo','${escHtml((c.nombre||'')+' '+(c.apellido||''))}')"><i class="fas fa-user-slash"></i> Desactivar</button>`;
    }
    fbtns += `<button class="btn-sm" style="background:rgba(255,255,255,.06);color:var(--muted);padding:9px 14px;" onclick="cerrarModal()"><i class="fas fa-xmark"></i> Cerrar</button>`;
    document.getElementById('modalFooter').innerHTML = fbtns;
}

function cerrarModal() { document.getElementById('modalDetalle').classList.remove('show'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
document.getElementById('modalDetalle').addEventListener('click', function(e) { if (e.target === this) cerrarModal(); });

// ═══════════════════════════════════════════════════════════════════════════
//  ACCIONES
// ═══════════════════════════════════════════════════════════════════════════
function aprobarPago(userId, nombre) {
    Swal.fire({
        title: '¿Aprobar pago?',
        html: `Vas a verificar el comprobante de <strong>${nombre}</strong>.<br>
               El cliente obtendrá acceso completo.`,
        icon: 'question',
        background: '#1c1c32', color: '#f0f0f5',
        confirmButtonColor: '#22c55e', cancelButtonColor: 'rgba(255,255,255,0.1)',
        confirmButtonText: '<i class="fas fa-check-circle"></i> Sí, aprobar',
        cancelButtonText: 'Cancelar', showCancelButton: true,
    }).then(res => {
        if (!res.isConfirmed) return;
        fetch('index.php?c=adminUsuarios&a=AprobarPago', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({user_id: userId}),
        })
        .then(r => r.json())
        .then(d => {
            Swal.fire({title: d.success ? '¡Aprobado!' : 'Error', text: d.message + (d.alerta ? '\n\n⚠️ ' + d.alerta : ''),
                icon: d.success ? 'success' : 'error', background: '#1c1c32', color: '#f0f0f5', confirmButtonColor: '#e55a2b'});
            if (d.success) { cerrarModal(); cargarTabla(); actualizarStats(); }
        })
        .catch(() => Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'}));
    });
}

function rechazarPago(userId, nombre) {
    Swal.fire({
        title: 'Rechazar pago',
        html: `<div style="text-align:left;color:#f0f0f5;font-size:.85rem;margin-bottom:10px;">Rechazando comprobante de <strong>${nombre}</strong>.</div>
               <textarea id="motivoRechazo" placeholder="Motivo del rechazo (obligatorio)…"
                 style="width:100%;background:#0f0f1a;border:1px solid rgba(229,90,43,.3);border-radius:8px;color:#f0f0f5;padding:10px;font-size:.83rem;min-height:90px;resize:vertical;outline:none;"></textarea>`,
        background: '#1c1c32', color: '#f0f0f5',
        confirmButtonColor: '#ef4444', cancelButtonColor: 'rgba(255,255,255,0.08)',
        confirmButtonText: '<i class="fas fa-xmark"></i> Rechazar',
        cancelButtonText: 'Cancelar', showCancelButton: true,
        preConfirm: () => {
            const m = document.getElementById('motivoRechazo').value.trim();
            if (!m) { Swal.showValidationMessage('El motivo es obligatorio'); return false; }
            return m;
        },
    }).then(res => {
        if (!res.isConfirmed) return;
        fetch('index.php?c=adminUsuarios&a=RechazarPago', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({user_id: userId, motivo: res.value}),
        })
        .then(r => r.json())
        .then(d => {
            Swal.fire({title: d.success ? 'Rechazado' : 'Error', text: d.message,
                icon: d.success ? 'info' : 'error', background: '#1c1c32', color: '#f0f0f5', confirmButtonColor: '#e55a2b'});
            if (d.success) { cerrarModal(); cargarTabla(); actualizarStats(); }
        })
        .catch(() => Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'}));
    });
}

function cambiarEstado(userId, nuevoEstado, nombre) {
    const cfg = {
        activo:     {title:'¿Reactivar cuenta?',  icon:'question',color:'#22c55e',txt:'<i class="fas fa-rotate-left"></i> Reactivar',  need:false},
        inactivo:   {title:'¿Desactivar cuenta?', icon:'warning', color:'#ef4444',txt:'<i class="fas fa-user-slash"></i> Desactivar', need:true},
        suspendido: {title:'¿Suspender cuenta?',  icon:'warning', color:'#a78bfa',txt:'<i class="fas fa-ban"></i> Suspender',         need:true},
    }[nuevoEstado];

    Swal.fire({
        title: cfg.title,
        html: `<div style="color:#f0f0f5;font-size:.85rem;margin-bottom:${cfg.need?'10px':'0'};">Cliente: <strong>${nombre}</strong></div>
               ${cfg.need?`<textarea id="motivoEstado" placeholder="Motivo (obligatorio)…"
                 style="width:100%;background:#0f0f1a;border:1px solid rgba(229,90,43,.3);border-radius:8px;color:#f0f0f5;padding:10px;font-size:.83rem;min-height:80px;resize:vertical;outline:none;"></textarea>`:''}`,
        icon: cfg.icon, background: '#1c1c32', color: '#f0f0f5',
        confirmButtonColor: cfg.color, cancelButtonColor: 'rgba(255,255,255,0.08)',
        confirmButtonText: cfg.txt, cancelButtonText: 'Cancelar', showCancelButton: true,
        preConfirm: () => {
            if (!cfg.need) return '';
            const m = document.getElementById('motivoEstado')?.value.trim()||'';
            if (!m) { Swal.showValidationMessage('El motivo es obligatorio'); return false; }
            return m;
        },
    }).then(res => {
        if (!res.isConfirmed) return;
        fetch('index.php?c=adminUsuarios&a=CambiarEstado', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({user_id: userId, estado: nuevoEstado, motivo: res.value}),
        })
        .then(r => r.json())
        .then(d => {
            Swal.fire({title: d.success ? '¡Listo!' : 'Error', text: d.message,
                icon: d.success ? 'success' : 'error', background: '#1c1c32', color: '#f0f0f5', confirmButtonColor: '#e55a2b'});
            if (d.success) { cerrarModal(); cargarTabla(); actualizarStats(); }
        })
        .catch(() => Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'}));
    });
}

function enviarOferta(userId, nombre) {
    Swal.fire({
        title: '¿Enviar email de oferta?',
        html: `Email de reactivación para <strong>${nombre}</strong>.<br>
               <span style="font-size:.78rem;color:rgba(240,240,245,.5);">Máx. 1 email por día por usuario.</span>`,
        icon: 'info', background: '#1c1c32', color: '#f0f0f5',
        confirmButtonColor: '#f59e0b', cancelButtonColor: 'rgba(255,255,255,0.08)',
        confirmButtonText: '<i class="fas fa-envelope"></i> Enviar',
        cancelButtonText: 'Cancelar', showCancelButton: true,
    }).then(res => {
        if (!res.isConfirmed) return;
        fetch('index.php?c=adminUsuarios&a=EnviarOferta', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({user_id: userId}),
        })
        .then(r => r.json())
        .then(d => Swal.fire({
            title: d.success ? '¡Email enviado!' : 'No se pudo enviar', text: d.message,
            icon: d.success ? 'success' : 'warning', background: '#1c1c32', color: '#f0f0f5', confirmButtonColor: '#e55a2b',
        }))
        .catch(() => Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'}));
    });
}

// ═══════════════════════════════════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════════════════════════════════
document.getElementById('btnRefresh').addEventListener('click', () => {
    resetPage(); cargarTabla(); actualizarStats();
});

cargarTabla();
actualizarStats();

// Abrir el detalle directo si se llega desde el dashboard (?ver=ID)
(function () {
    const verId = parseInt(new URLSearchParams(location.search).get('ver'), 10);
    if (verId > 0) verCliente(verId);
})();
</script>
</body>
</html>