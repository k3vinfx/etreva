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
<title>Etreva Admin — Categorías</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/menu.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<!-- ════════════════════════════════════════════════════════════════
     PRINT STYLES — reporte elegante
════════════════════════════════════════════════════════════════ -->
<style>
@media print {
    /* Ocultar todo lo de la app */
    .barra-lateral, .topbar-right, .toolbar,
    .btn-icon, .reporte-panel, .tab-pills,
    #btnRefresh, .no-print { display: none !important; }

    main { margin-left: 0 !important; background: #fff !important; }
    body, html { background: #fff !important; color: #000 !important; font-family: 'Georgia', serif !important; }

    .body { padding: 0 !important; gap: 0 !important; }

    /* Cabecera del reporte */
    .print-header { display: flex !important; }

    /* KPIs en print */
    .kpi-strip { display: grid !important; grid-template-columns: repeat(4,1fr); gap: 8px; margin-bottom: 20px; }
    .kpi-card  { border: 1.5px solid #e0e0e0 !important; background: #fafafa !important;
                 border-radius: 8px; padding: 12px 14px; }
    .kpi-val   { color: #1a1a1a !important; font-size: 1.5rem !important; font-weight: 900 !important; }
    .kpi-lbl   { color: #666 !important; font-size: .65rem !important; }
    .kpi-ico   { display: none !important; }

    /* Tabla de reporte */
    #tablaReporte { display: table !important; width: 100%; border-collapse: collapse; font-size: .78rem; }
    .rep-header { display: none !important; }
    .rpt-wrap { border: none !important; background: transparent !important; padding: 0 !important; }
    .rpt-table thead th {
        background: #1a1a2e !important; color: #fff !important;
        padding: 8px 10px; text-align: left; font-size: .68rem;
        letter-spacing: .8px; text-transform: uppercase;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .rpt-table tbody tr:nth-child(even) td {
        background: #f8f8f8 !important;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .rpt-table tbody td { padding: 7px 10px; border-bottom: 1px solid #e8e8e8; color: #1a1a1a !important; }
    .badge-print-act  { color: #15803d !important; font-weight: 700; }
    .badge-print-inac { color: #b91c1c !important; font-weight: 700; }

    /* Ocultar la vista de grupos, mostrar solo tabla */
    #gruposWrap { display: none !important; }
    #rptSection { display: block !important; }
    .page-break { page-break-before: always; }
}
</style>

<style>
:root {
    --bg:      #0f0f1a;
    --surface: #161625;
    --card:    #1c1c32;
    --border:  rgba(229,90,43,.13);
    --border2: rgba(255,255,255,.06);
    --text:    #f0f0f5;
    --muted:   rgba(240,240,245,.42);
    --orange:  #e55a2b;
    --green:   #22c55e;
    --red:     #ef4444;
    --blue:    #38bdf8;
    --purple:  #a78bfa;
    --amber:   #f59e0b;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { background: var(--bg) !important; color: var(--text) !important; font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; }
main { margin-left: 280px; min-height: 100vh; background: var(--bg); transition: margin-left .3s; }
main.min-main { margin-left: 85px; }

/* TOPBAR */
.topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
.topbar-title { display: flex; align-items: center; gap: 12px; }
.ico-wrap { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, var(--orange), #ff9a6c); display: flex; align-items: center; justify-content: center; font-size: .95rem; color: #fff; flex-shrink: 0; }
.topbar-title h1 { font-size: 1.05rem; font-weight: 800; color: #fff; line-height: 1.1; }
.topbar-title h1 small { display: block; font-size: .68rem; font-weight: 400; color: var(--muted); margin-top: 1px; }
.topbar-right { display: flex; align-items: center; gap: 8px; }
.btn-icon { background: rgba(255,255,255,.05); border: 1px solid var(--border2); border-radius: 9px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: .88rem; cursor: pointer; text-decoration: none; transition: all .2s; }
.btn-icon:hover { background: rgba(229,90,43,.1); color: var(--orange); border-color: rgba(229,90,43,.25); }

/* BODY */
.body { padding: 24px 32px 80px; display: flex; flex-direction: column; gap: 18px; }

/* PRINT HEADER — oculto en pantalla */
.print-header {
    display: none;
    align-items: flex-start;
    justify-content: space-between;
    padding: 0 0 16px;
    border-bottom: 3px solid #e55a2b;
    margin-bottom: 20px;
}
.ph-logo { font-size: 1.4rem; font-weight: 900; color: #1a1a1a; letter-spacing: -1px; }
.ph-logo span { color: #e55a2b; }
.ph-info { text-align: right; font-size: .72rem; color: #555; line-height: 1.6; }
.ph-info strong { color: #1a1a1a; display: block; font-size: .9rem; margin-bottom: 2px; }

/* KPI STRIP */
.kpi-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.kpi-card { background: var(--card); border: 1px solid var(--border2); border-radius: 14px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; transition: border-color .2s, transform .15s; }
.kpi-card:hover { border-color: rgba(229,90,43,.25); transform: translateY(-1px); }
.kpi-ico { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
.kpi-val { font-size: 1.55rem; font-weight: 900; color: #fff; line-height: 1; }
.kpi-lbl { font-size: .68rem; color: var(--muted); margin-top: 3px; }
.kpi-sub { font-size: .6rem; margin-top: 2px; color: var(--muted); }

/* ─── PANEL DE REPORTES ──────────────────────────────────────── */
.reporte-panel {
    background: var(--card);
    border: 1px solid rgba(229,90,43,.22);
    border-radius: 16px;
    overflow: hidden;
}
.rep-header {
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border2);
    cursor: pointer;
    user-select: none;
}
.rep-header:hover { background: rgba(229,90,43,.04); }
.rep-title { display: flex; align-items: center; gap: 10px; font-size: .85rem; font-weight: 800; color: #fff; }
.rep-title i { color: var(--orange); }
.rep-badge { font-size: .62rem; font-weight: 700; padding: 2px 9px; border-radius: 20px; background: rgba(229,90,43,.14); color: var(--orange); }
.rep-actions { display: flex; align-items: center; gap: 8px; }

/* Botones de exportar */
.btn-export {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: 9px; font-size: .76rem; font-weight: 700;
    border: 1px solid; cursor: pointer; transition: all .2s; white-space: nowrap;
}
.btn-export:hover { filter: brightness(1.15); transform: translateY(-1px); }
.btn-excel { background: rgba(34,197,94,.1); border-color: rgba(34,197,94,.25); color: var(--green); }
.btn-print { background: rgba(229,90,43,.1); border-color: rgba(229,90,43,.25); color: var(--orange); }

/* Cuerpo del panel colapsable */
.rep-body { display: none; }
.rep-body.open { display: block; }

/* Filtros del reporte */
.rep-filtros {
    padding: 16px 20px;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 10px;
    align-items: end;
    border-bottom: 1px solid var(--border2);
}
.flt-grp { display: flex; flex-direction: column; gap: 5px; }
.flt-lbl { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--muted); }
.flt-sel, .flt-inp {
    background: rgba(255,255,255,.05); border: 1px solid var(--border2);
    border-radius: 9px; padding: 8px 12px; color: var(--text); font-size: .8rem; outline: none;
    transition: border-color .2s; width: 100%;
}
.flt-sel option { background: var(--card); }
.flt-sel:focus, .flt-inp:focus { border-color: rgba(229,90,43,.4); }
.btn-aplicar {
    background: linear-gradient(135deg, var(--orange), #ff9a6c);
    border: none; border-radius: 9px; padding: 8px 18px;
    color: #fff; font-size: .8rem; font-weight: 800; cursor: pointer;
    transition: all .2s; white-space: nowrap;
}
.btn-aplicar:hover { filter: brightness(1.1); transform: translateY(-1px); }

/* Resumen del reporte (bajo filtros) */
.rep-resumen {
    padding: 10px 20px;
    display: flex; align-items: center; gap: 14px;
    border-bottom: 1px solid var(--border2);
    font-size: .74rem; color: var(--muted);
    flex-wrap: wrap;
}
.res-chip { display: flex; align-items: center; gap: 5px; }
.res-chip strong { color: var(--text); }
.res-sep { width: 1px; height: 14px; background: var(--border2); }

/* ─── TABLA DEL REPORTE ──────────────────────────────────────── */
.rpt-wrap { overflow-x: auto; }
.rpt-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.rpt-table thead th {
    background: rgba(229,90,43,.08);
    color: var(--muted); font-size: .65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .9px;
    padding: 10px 14px; border-bottom: 1px solid var(--border2);
    white-space: nowrap; cursor: pointer; user-select: none;
    transition: color .15s;
}
.rpt-table thead th:hover { color: var(--orange); }
.rpt-table thead th .sort-ico { margin-left: 4px; opacity: .4; font-size: .6rem; }
.rpt-table thead th.sorted { color: var(--orange); }
.rpt-table thead th.sorted .sort-ico { opacity: 1; }
.rpt-table tbody tr { border-bottom: 1px solid rgba(255,255,255,.035); transition: background .12s; }
.rpt-table tbody tr:hover { background: rgba(229,90,43,.04); }
.rpt-table tbody td { padding: 10px 14px; vertical-align: middle; }
.td-grupo { display: flex; align-items: center; gap: 8px; }
.gn { width: 26px; height: 26px; border-radius: 7px; background: linear-gradient(135deg,var(--orange),#ff9a6c); display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:900;color:#fff;flex-shrink:0; }
.td-tipo { display:flex;align-items:center;gap:5px; }
.badge-tipo { font-size:.64rem; font-weight:700; padding:2px 8px; border-radius:20px; white-space:nowrap; }
.b-cat { background:rgba(56,189,248,.1); color:var(--blue); }
.b-sub { background:rgba(167,139,250,.1); color:var(--purple); }
.badge-est { font-size:.64rem; font-weight:800; padding:2px 9px; border-radius:20px; display:inline-flex; align-items:center; gap:4px; }
.b-act  { background:rgba(34,197,94,.1);  color:var(--green); }
.b-inac { background:rgba(239,68,68,.1);  color:var(--red); }
.rango-chip { background:rgba(255,255,255,.06); border-radius:5px; padding:2px 7px; font-size:.68rem; font-weight:600; color:var(--text); }
.rango-chip.vacio { color:var(--muted); font-style:italic; }
.td-nombre { font-weight:700; color:#fff; }
.td-catref { font-size:.66rem; color:var(--muted); margin-top:2px; }

/* Paginación */
.rpt-footer { padding: 12px 16px; display:flex; align-items:center; justify-content:space-between; border-top:1px solid var(--border2); font-size:.73rem; color:var(--muted); }
.pag-btns { display:flex; gap:4px; }
.pag-btn { background:rgba(255,255,255,.05); border:1px solid var(--border2); border-radius:7px; padding:4px 9px; color:var(--muted); cursor:pointer; font-size:.7rem; transition:all .18s; }
.pag-btn:hover, .pag-btn.active { background:rgba(229,90,43,.15); color:var(--orange); border-color:rgba(229,90,43,.3); }
.pag-btn:disabled { opacity:.3; cursor:not-allowed; }

/* Tabla vacía */
.rpt-empty { text-align:center; padding:40px 16px; color:var(--muted); }
.rpt-empty i { font-size:2rem; opacity:.2; display:block; margin-bottom:10px; }

/* TOOLBAR */
.toolbar { background: var(--card); border: 1px solid var(--border2); border-radius: 13px; padding: 12px 16px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.search-wrap { flex: 1; min-width: 200px; position: relative; }
.search-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: .78rem; pointer-events: none; }
.search-wrap input { width: 100%; background: rgba(255,255,255,.05); border: 1px solid var(--border2); border-radius: 9px; padding: 8px 12px 8px 32px; color: var(--text); font-size: .82rem; outline: none; transition: border-color .2s; }
.search-wrap input:focus { border-color: rgba(229,90,43,.4); }
.sel-filter { background: rgba(255,255,255,.05); border: 1px solid var(--border2); border-radius: 9px; padding: 8px 12px; color: var(--text); font-size: .8rem; outline: none; cursor: pointer; }
.sel-filter option { background: var(--card); }
.tab-pills { display: flex; gap: 4px; background: rgba(255,255,255,.04); border-radius: 9px; padding: 3px; }
.tab-pill { padding: 6px 13px; border-radius: 7px; font-size: .74rem; font-weight: 700; color: var(--muted); cursor: pointer; border: none; background: none; transition: all .2s; white-space: nowrap; }
.tab-pill.active { background: var(--card); color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.35); }
.tab-pill.active.t-todo  { color: var(--orange); }
.tab-pill.active.t-cats  { color: var(--blue); }
.tab-pill.active.t-subs  { color: var(--purple); }
.tab-pill.active.t-inact { color: var(--red); }

/* SKELETONS, GROUPS, etc — mismos que antes */
.estado-panel { background: var(--card); border: 1px solid var(--border2); border-radius: 14px; padding: 60px; text-align: center; color: var(--muted); }
.estado-panel i { font-size: 2.2rem; opacity: .2; display: block; margin-bottom: 14px; }
.estado-panel p { font-size: .84rem; }
.skel { background: linear-gradient(90deg, rgba(255,255,255,.04) 0%, rgba(255,255,255,.08) 50%, rgba(255,255,255,.04) 100%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 14px; }
@keyframes shimmer { 0% { background-position: 200% 0 } 100% { background-position: -200% 0 } }
.grupo-card { background: var(--card); border: 1px solid var(--border2); border-radius: 16px; overflow: hidden; transition: border-color .2s; }
.grupo-card:hover { border-color: rgba(229,90,43,.2); }
.grupo-header { padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; background: rgba(255,255,255,.02); transition: background .15s; }
.grupo-header:hover { background: rgba(229,90,43,.04); }
.gh-left { display: flex; align-items: center; gap: 12px; }
.grupo-num { width: 34px; height: 34px; border-radius: 9px; background: linear-gradient(135deg, var(--orange), #ff9a6c); display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 900; color: #fff; flex-shrink: 0; }
.grupo-info-title { font-size: .88rem; font-weight: 800; color: #fff; }
.grupo-info-meta  { font-size: .68rem; color: var(--muted); margin-top: 2px; }
.gh-right { display: flex; align-items: center; gap: 10px; }
.gpills { display: flex; gap: 5px; }
.gpill { font-size: .64rem; font-weight: 800; padding: 3px 9px; border-radius: 20px; display: flex; align-items: center; gap: 4px; }
.gpill-cat   { background: rgba(56,189,248,.1);  color: var(--blue);   }
.gpill-sub   { background: rgba(167,139,250,.1); color: var(--purple); }
.gpill-atr   { background: rgba(34,197,94,.1);   color: var(--green);  }
.gpill-inact { background: rgba(239,68,68,.1);   color: var(--red);    }
.chevron-ico { color: var(--muted); font-size: .82rem; transition: transform .25s; }
.chevron-ico.open { transform: rotate(180deg); }
.grupo-body { display: none; border-top: 1px solid rgba(255,255,255,.05); }
.grupo-body.open { display: block; }
.seccion { padding: 16px 20px; }
.sec-sep  { height: 1px; background: rgba(255,255,255,.05); margin: 0 20px; }
.sec-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 13px; }
.sec-title { display: flex; align-items: center; gap: 8px; font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.1px; }
.sec-title::before { content: ''; display: block; width: 3px; height: 14px; border-radius: 2px; }
.sec-title.t-cat { color: var(--blue);   } .sec-title.t-cat::before { background: var(--blue); }
.sec-title.t-sub { color: var(--purple); } .sec-title.t-sub::before { background: var(--purple); }
.sec-count { font-size: .62rem; color: var(--muted); font-weight: 400; margin-left: 2px; }
.btn-agregar { background: rgba(229,90,43,.1); border: 1px solid rgba(229,90,43,.22); border-radius: 7px; color: var(--orange); font-size: .7rem; font-weight: 700; padding: 5px 11px; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all .2s; }
.btn-agregar:hover { background: rgba(229,90,43,.2); }
.items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 9px; }
.item-card { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07); border-radius: 12px; padding: 12px 13px; position: relative; transition: all .2s; }
.item-card:hover { background: rgba(255,255,255,.055); border-color: rgba(229,90,43,.18); }
.item-card.inactivo { opacity: .52; }
.item-card.inactivo .item-nombre { text-decoration: line-through; color: var(--muted); }
.est-dot { position: absolute; top: 11px; right: 11px; width: 7px; height: 7px; border-radius: 50%; }
.est-dot.activo   { background: var(--green); box-shadow: 0 0 6px rgba(34,197,94,.5); }
.est-dot.inactivo { background: var(--red); }
.item-nombre { font-size: .81rem; font-weight: 700; color: #fff; margin-bottom: 4px; padding-right: 16px; word-break: break-word; }
.item-sub-cat { font-size: .63rem; color: var(--muted); margin-bottom: 5px; }
.item-rangos { display: flex; align-items: center; gap: 6px; font-size: .67rem; margin-bottom: 9px; }
.rchip { background: rgba(255,255,255,.06); border-radius: 5px; padding: 2px 7px; font-size: .65rem; font-weight: 600; color: var(--text); }
.rchip.null { color: var(--muted); font-style: italic; font-weight: 400; }
.rarrow { font-size: .52rem; color: var(--muted); }
.item-actions { display: flex; gap: 5px; flex-wrap: wrap; }
.btn-xs { padding: 4px 9px; border-radius: 6px; font-size: .67rem; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 3px; transition: all .18s; white-space: nowrap; }
.btn-xs:hover { filter: brightness(1.25); transform: translateY(-1px); }
.btn-xs:disabled { opacity: .5; cursor: wait; transform: none; filter: none; }
.btn-on   { background: rgba(34,197,94,.1);  color: var(--green);  }
.btn-off  { background: rgba(239,68,68,.1);  color: var(--red);    }
.btn-edit { background: rgba(56,189,248,.1); color: var(--blue);   }
.sec-empty { text-align: center; padding: 24px; color: var(--muted); font-size: .78rem; }
.sec-empty i { font-size: 1.5rem; opacity: .2; display: block; margin-bottom: 8px; }
mark.hl { background: rgba(229,90,43,.28); border-radius: 2px; padding: 0 1px; color: inherit; }
.spin { display: inline-block; width: 13px; height: 13px; border: 2px solid rgba(255,255,255,.1); border-top-color: currentColor; border-radius: 50%; animation: rot .6s linear infinite; }
@keyframes rot { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
    main, main.min-main { margin-left: 0; }
    .body { padding: 12px 12px 60px; }
    .kpi-strip { grid-template-columns: repeat(2, 1fr); }
    .items-grid { grid-template-columns: 1fr; }
    .rep-filtros { grid-template-columns: 1fr 1fr; }
}
</style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main id="mainContent">

<!-- PRINT HEADER (visible solo al imprimir) -->
<div class="print-header" id="printHdr">
    <div>
        <div class="ph-logo">🏔️ etreva<span>.</span></div>
        <div style="font-size:.78rem;color:#555;margin-top:4px;">Sistema de Gestión — Panel Admin</div>
    </div>
    <div class="ph-info">
        <strong>Reporte de Categorías de Atracciones</strong>
        <span id="phFiltroTxt">Todos los registros</span><br>
        <span id="phFecha"></span>
    </div>
</div>

<div class="topbar">
    <div class="topbar-title">
        <div class="ico-wrap"><i class="fas fa-layer-group"></i></div>
        <h1>Categorías de Atracciones<small>Gestión de rangos, estados y reportes</small></h1>
    </div>
    <div class="topbar-right">
        <div class="btn-icon" id="btnRefresh" title="Actualizar"><i class="fas fa-rotate-right"></i></div>
        <a class="btn-icon" href="index.php?c=adminFinanzas&a=Index" title="Finanzas"><i class="fas fa-chart-line"></i></a>
        <a class="btn-icon" href="index.php?c=adminUsuarios&a=Index" title="Usuarios"><i class="fas fa-users"></i></a>
        <a class="btn-icon" href="index.php?c=admin&a=Inicio" title="Dashboard"><i class="fas fa-grid-2"></i></a>
        <a class="btn-icon" href="index.php?c=login&a=Logout" title="Salir" style="color:var(--red);"><i class="fas fa-right-from-bracket"></i></a>
    </div>
</div>

<div class="body">

    <!-- KPI STRIP -->
    <div class="kpi-strip">
        <div class="kpi-card">
            <div class="kpi-ico" style="background:rgba(229,90,43,.12);"><i class="fas fa-folder-tree" style="color:var(--orange);"></i></div>
            <div><div class="kpi-val" id="kGrupos"><span class="spin" style="border-top-color:var(--orange);"></span></div><div class="kpi-lbl">Grupos</div></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-ico" style="background:rgba(56,189,248,.1);"><i class="fas fa-tag" style="color:var(--blue);"></i></div>
            <div><div class="kpi-val" id="kCats"><span class="spin" style="border-top-color:var(--blue);"></span></div><div class="kpi-lbl">Categorías activas</div><div class="kpi-sub" id="kCatsTot"></div></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-ico" style="background:rgba(167,139,250,.1);"><i class="fas fa-tags" style="color:var(--purple);"></i></div>
            <div><div class="kpi-val" id="kSubs"><span class="spin" style="border-top-color:var(--purple);"></span></div><div class="kpi-lbl">Subcategorías activas</div><div class="kpi-sub" id="kSubsTot"></div></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-ico" style="background:rgba(239,68,68,.1);"><i class="fas fa-ban" style="color:var(--red);"></i></div>
            <div><div class="kpi-val" id="kInact"><span class="spin" style="border-top-color:var(--red);"></span></div><div class="kpi-lbl">Total desactivadas</div></div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         PANEL DE REPORTES
    ═══════════════════════════════════════════════════════════ -->
    <div class="reporte-panel">
        <!-- Header siempre visible -->
        <div class="rep-header" onclick="toggleReporte()">
            <div class="rep-title">
                <i class="fas fa-chart-bar"></i>
                Reporte de Categorías
                <span class="rep-badge" id="repBadge">0 registros</span>
            </div>
            <div class="rep-actions" onclick="event.stopPropagation()">
                <button class="btn-export btn-excel" onclick="exportarExcel()">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn-export btn-print" onclick="imprimirReporte()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
                <div class="btn-icon" style="margin-left:4px;" title="Colapsar/Expandir" onclick="toggleReporte()">
                    <i class="fas fa-chevron-down" id="repChevron" style="transition:transform .25s;"></i>
                </div>
            </div>
        </div>

        <!-- Cuerpo colapsable -->
        <div class="rep-body" id="repBody">
            <!-- FILTROS -->
            <div class="rep-filtros">
                <div class="flt-grp">
                    <label class="flt-lbl"><i class="fas fa-folder-tree" style="margin-right:4px;"></i>Grupo / Modelo</label>
                    <select class="flt-sel" id="fltGrupo">
                        <option value="">Todos los grupos</option>
                    </select>
                </div>
                <div class="flt-grp">
                    <label class="flt-lbl"><i class="fas fa-tag" style="margin-right:4px;"></i>Tipo</label>
                    <select class="flt-sel" id="fltTipo">
                        <option value="">Categorías y subcategorías</option>
                        <option value="cat">Solo categorías</option>
                        <option value="sub">Solo subcategorías</option>
                    </select>
                </div>
                <div class="flt-grp">
                    <label class="flt-lbl"><i class="fas fa-circle-dot" style="margin-right:4px;"></i>Estado</label>
                    <select class="flt-sel" id="fltEstRep">
                        <option value="">Todos los estados</option>
                        <option value="activo">Solo activas</option>
                        <option value="inactivo">Solo inactivas</option>
                    </select>
                </div>
                <div class="flt-grp">
                    <label class="flt-lbl"><i class="fas fa-ruler" style="margin-right:4px;"></i>Rangos</label>
                    <select class="flt-sel" id="fltRangos">
                        <option value="">Con y sin rangos</option>
                        <option value="con">Con rangos definidos</option>
                        <option value="sin">Sin rangos (vacíos)</option>
                    </select>
                </div>
                <button class="btn-aplicar" onclick="aplicarFiltrosRep()">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </div>

            <!-- RESUMEN RÁPIDO -->
            <div class="rep-resumen" id="repResumen" style="display:none;">
                <div class="res-chip"><strong id="rsTotalReg">0</strong> registros</div>
                <div class="res-sep"></div>
                <div class="res-chip"><i class="fas fa-tag" style="color:var(--blue);font-size:.7rem;"></i> <strong id="rsCats">0</strong> categorías</div>
                <div class="res-sep"></div>
                <div class="res-chip"><i class="fas fa-tags" style="color:var(--purple);font-size:.7rem;"></i> <strong id="rsSubs">0</strong> subcategorías</div>
                <div class="res-sep"></div>
                <div class="res-chip"><i class="fas fa-check-circle" style="color:var(--green);font-size:.7rem;"></i> <strong id="rsActivas">0</strong> activas</div>
                <div class="res-sep"></div>
                <div class="res-chip"><i class="fas fa-ban" style="color:var(--red);font-size:.7rem;"></i> <strong id="rsInact">0</strong> inactivas</div>
            </div>

            <!-- TABLA -->
            <div class="rpt-wrap">
                <table class="rpt-table" id="tablaReporte">
                    <thead>
                        <tr>
                            <th onclick="sortRep('grupo')" data-col="grupo">#G <span class="sort-ico fas fa-sort"></span></th>
                            <th onclick="sortRep('nombre')" data-col="nombre">Nombre <span class="sort-ico fas fa-sort"></span></th>
                            <th onclick="sortRep('tipo')" data-col="tipo">Tipo <span class="sort-ico fas fa-sort"></span></th>
                            <th onclick="sortRep('estado')" data-col="estado">Estado <span class="sort-ico fas fa-sort"></span></th>
                            <th onclick="sortRep('rango_inicio')" data-col="rango_inicio">Rango inicio <span class="sort-ico fas fa-sort"></span></th>
                            <th onclick="sortRep('rango_fin')" data-col="rango_fin">Rango fin <span class="sort-ico fas fa-sort"></span></th>
                            <th>Atracciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyRep">
                        <tr><td colspan="7" style="padding:30px;"><div class="skel" style="height:14px;"></div></td></tr>
                    </tbody>
                </table>
            </div>

            <!-- PIE DE TABLA: paginación -->
            <div class="rpt-footer">
                <span id="rptInfoPag">Cargando…</span>
                <div class="pag-btns" id="rptPagBtns"></div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         TOOLBAR Y VISTA DE GRUPOS (igual que antes)
    ═══════════════════════════════════════════════════════════ -->
    <div class="toolbar">
        <div class="search-wrap">
            <i class="fas fa-magnifying-glass"></i>
            <input type="text" id="txtBuscar" placeholder="Buscar categoría o subcategoría…" oninput="renderGrupos()">
        </div>
        <select class="sel-filter" id="selEstado" onchange="renderGrupos()">
            <option value="">Todos los estados</option>
            <option value="activo">Solo activas</option>
            <option value="inactivo">Solo inactivas</option>
        </select>
        <div class="tab-pills">
            <button class="tab-pill active t-todo"  onclick="setTab('todo',  this)"><i class="fas fa-border-all" style="font-size:.7rem;"></i> Todo</button>
            <button class="tab-pill t-cats"          onclick="setTab('cats',  this)"><i class="fas fa-tag"       style="font-size:.7rem;"></i> Categorías</button>
            <button class="tab-pill t-subs"          onclick="setTab('subs',  this)"><i class="fas fa-tags"      style="font-size:.7rem;"></i> Subcategorías</button>
            <button class="tab-pill t-inact"         onclick="setTab('inact', this)"><i class="fas fa-ban"       style="font-size:.7rem;"></i> Inactivas</button>
        </div>
    </div>

    <div id="gruposWrap" style="display:flex;flex-direction:column;gap:12px;">
        <div class="skel" style="height:66px;"></div>
        <div class="skel" style="height:66px;opacity:.6;"></div>
        <div class="skel" style="height:66px;opacity:.3;"></div>
    </div>

</div><!-- /.body -->
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
'use strict';

// ════════════════════════════════════════════════════════════════
//  ESTADO GLOBAL
// ════════════════════════════════════════════════════════════════
let _grupos   = [];
let _tab      = 'todo';
let _repDatos = [];        // datos flat del reporte (después de filtrar)
let _repSort  = { col: 'grupo', dir: 1 };
let _repPag   = { page: 1, perPage: 20 };
let _conteos  = {};        // { grupo_id: total_atracciones }

// ════════════════════════════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════════════════════════════
const esc  = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
const escA = s => String(s ?? '').replace(/\\/g,'\\\\').replace(/'/g,"\\'");

function hl(txt, q) {
    if (!q) return esc(txt);
    const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
    return esc(txt).replace(re, '<mark class="hl">$1</mark>');
}

// ════════════════════════════════════════════════════════════════
//  CARGA INICIAL
// ════════════════════════════════════════════════════════════════
function cargarTodo() {
    document.getElementById('gruposWrap').innerHTML = `
        <div class="skel" style="height:66px;"></div>
        <div class="skel" style="height:66px;opacity:.6;"></div>
        <div class="skel" style="height:66px;opacity:.3;"></div>`;
    document.getElementById('tbodyRep').innerHTML =
        '<tr><td colspan="7" style="padding:28px;"><div class="skel" style="height:14px;"></div></td></tr>';

    Promise.all([
        fetch('index.php?c=adminCategorias&a=Listar').then(r => r.json()),
        fetch('index.php?c=adminCategorias&a=Stats').then(r => r.json()),
    ]).then(([dG, dS]) => {
        if (dG.success) {
            _grupos  = dG.grupos;
            _conteos = {};
            _grupos.forEach(g => { _conteos[g.id] = g.total_atracciones || 0; });
            renderGrupos();
            poblarSelectGrupo();
            aplicarFiltrosRep();
        } else {
            document.getElementById('gruposWrap').innerHTML =
                '<div class="estado-panel"><i class="fas fa-circle-exclamation"></i><p>Error al cargar grupos</p></div>';
        }
        if (dS.success) actualizarKpis(dS.stats);
    }).catch(() => {
        document.getElementById('gruposWrap').innerHTML =
            '<div class="estado-panel"><i class="fas fa-wifi-slash"></i><p>Error de conexión</p></div>';
    });
}

function soloStats() {
    fetch('index.php?c=adminCategorias&a=Stats').then(r => r.json())
        .then(d => { if (d.success) actualizarKpis(d.stats); }).catch(() => {});
}

function actualizarKpis(s) {
    document.getElementById('kGrupos').textContent  = s.total_grupos;
    document.getElementById('kCats').textContent    = s.activas_cat;
    document.getElementById('kCatsTot').textContent = `de ${s.total_categorias} totales`;
    document.getElementById('kSubs').textContent    = s.activas_sub;
    document.getElementById('kSubsTot').textContent = `de ${s.total_sub} totales`;
    document.getElementById('kInact').textContent   = (s.inactivas_cat + s.inactivas_sub);
}

// Poblar el select de grupos en el panel de reportes
function poblarSelectGrupo() {
    const sel = document.getElementById('fltGrupo');
    sel.innerHTML = '<option value="">Todos los grupos</option>';
    _grupos.forEach(g => {
        const lbl = g.modelo_nombre ? `Grupo ${g.id} — ${g.modelo_nombre}` : `Grupo ${g.id}`;
        sel.innerHTML += `<option value="${g.id}">${esc(lbl)}</option>`;
    });
}

// ════════════════════════════════════════════════════════════════
//  REPORTE — PANEL COLAPSABLE
// ════════════════════════════════════════════════════════════════
function toggleReporte() {
    const b = document.getElementById('repBody');
    const c = document.getElementById('repChevron');
    b.classList.toggle('open');
    c.style.transform = b.classList.contains('open') ? 'rotate(0deg)' : 'rotate(-90deg)';
}

// ── Construir datos flat del reporte según filtros ────────────────
function construirDatosFlat(fGrupo, fTipo, fEst, fRangos) {
    const filas = [];

    _grupos.forEach(g => {
        if (fGrupo && g.id !== parseInt(fGrupo)) return;
        const nombre_g = g.modelo_nombre || `Grupo ${g.id}`;
        const atracciones = _conteos[g.id] || 0;

        if (fTipo !== 'sub') {
            (g.categoria_rangos || []).forEach(c => {
                const est = c.estado ?? 'activo';
                if (fEst && est !== fEst) return;
                const tieneRango = c.rango_inicio > 0 || c.rango_fin > 0;
                if (fRangos === 'con' && !tieneRango) return;
                if (fRangos === 'sin' && tieneRango)  return;
                filas.push({
                    grupo: g.id, grupo_nombre: nombre_g,
                    nombre: c.categoria, catref: '',
                    tipo: 'cat', estado: est,
                    rango_inicio: c.rango_inicio || 0,
                    rango_fin:    c.rango_fin    || 0,
                    atracciones,
                });
            });
        }

        if (fTipo !== 'cat') {
            (g.subcategoria_rangos || []).forEach(s => {
                const est = s.estado ?? 'activo';
                if (fEst && est !== fEst) return;
                const tieneRango = s.rango_inicio > 0 || s.rango_fin > 0;
                if (fRangos === 'con' && !tieneRango) return;
                if (fRangos === 'sin' && tieneRango)  return;
                const partes = (s.subcategoria || '').split(' - ');
                const label  = partes[1] || partes[0];
                const catref = partes.length > 1 ? partes[0] : '';
                filas.push({
                    grupo: g.id, grupo_nombre: nombre_g,
                    nombre: label, catref,
                    tipo: 'sub', estado: est,
                    rango_inicio: s.rango_inicio || 0,
                    rango_fin:    s.rango_fin    || 0,
                    atracciones,
                });
            });
        }
    });

    return filas;
}

function aplicarFiltrosRep() {
    const fGrupo  = document.getElementById('fltGrupo').value;
    const fTipo   = document.getElementById('fltTipo').value;
    const fEst    = document.getElementById('fltEstRep').value;
    const fRangos = document.getElementById('fltRangos').value;

    _repDatos = construirDatosFlat(fGrupo, fTipo, fEst, fRangos);
    _repPag.page = 1;
    sortRepDatos();
    renderTablaRep();
    actualizarResumenRep();
    actualizarFiltroDescripcion(fGrupo, fTipo, fEst, fRangos);
}

function actualizarResumenRep() {
    const d = _repDatos;
    const cats    = d.filter(r => r.tipo === 'cat').length;
    const subs    = d.filter(r => r.tipo === 'sub').length;
    const activas = d.filter(r => r.estado === 'activo').length;
    const inact   = d.filter(r => r.estado === 'inactivo').length;

    document.getElementById('rsTotalReg').textContent = d.length;
    document.getElementById('rsCats').textContent     = cats;
    document.getElementById('rsSubs').textContent     = subs;
    document.getElementById('rsActivas').textContent  = activas;
    document.getElementById('rsInact').textContent    = inact;
    document.getElementById('repBadge').textContent   = d.length + ' registros';
    document.getElementById('repResumen').style.display = d.length ? 'flex' : 'none';
}

function actualizarFiltroDescripcion(fGrupo, fTipo, fEst, fRangos) {
    const partes = [];
    if (fGrupo) {
        const g = _grupos.find(x => x.id === parseInt(fGrupo));
        partes.push(g ? `Grupo: ${g.modelo_nombre || '#'+g.id}` : '');
    }
    if (fTipo   === 'cat') partes.push('Solo categorías');
    if (fTipo   === 'sub') partes.push('Solo subcategorías');
    if (fEst    === 'activo')   partes.push('Estado: activo');
    if (fEst    === 'inactivo') partes.push('Estado: inactivo');
    if (fRangos === 'con') partes.push('Con rangos');
    if (fRangos === 'sin') partes.push('Sin rangos');

    const txt = partes.length ? partes.join(' · ') : 'Todos los registros';
    document.getElementById('phFiltroTxt').textContent = txt;
    document.getElementById('phFecha').textContent = 'Generado el ' +
        new Date().toLocaleDateString('es-ES', {day:'2-digit',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'});
}

// ── Sort ──────────────────────────────────────────────────────────
function sortRep(col) {
    if (_repSort.col === col) _repSort.dir *= -1;
    else { _repSort.col = col; _repSort.dir = 1; }

    document.querySelectorAll('.rpt-table thead th').forEach(th => {
        th.classList.remove('sorted');
        const ico = th.querySelector('.sort-ico');
        if (ico) { ico.className = 'sort-ico fas fa-sort'; }
    });
    const th = document.querySelector(`[data-col="${col}"]`);
    if (th) {
        th.classList.add('sorted');
        const ico = th.querySelector('.sort-ico');
        if (ico) ico.className = 'sort-ico fas fa-sort-' + (_repSort.dir === 1 ? 'up' : 'down');
    }
    sortRepDatos();
    renderTablaRep();
}

function sortRepDatos() {
    const { col, dir } = _repSort;
    _repDatos.sort((a, b) => {
        let va = a[col] ?? '', vb = b[col] ?? '';
        if (typeof va === 'string') va = va.toLowerCase();
        if (typeof vb === 'string') vb = vb.toLowerCase();
        if (va < vb) return -1 * dir;
        if (va > vb) return  1 * dir;
        return 0;
    });
}

// ── Render tabla ──────────────────────────────────────────────────
function renderTablaRep() {
    const { page, perPage } = _repPag;
    const total  = _repDatos.length;
    const pages  = Math.max(1, Math.ceil(total / perPage));
    const start  = (page - 1) * perPage;
    const slice  = _repDatos.slice(start, start + perPage);

    const tbody = document.getElementById('tbodyRep');

    if (!slice.length) {
        tbody.innerHTML = `<tr><td colspan="7"><div class="rpt-empty">
            <i class="fas fa-filter"></i>Sin registros para los filtros aplicados</div></td></tr>`;
        document.getElementById('rptInfoPag').textContent = '0 registros';
        document.getElementById('rptPagBtns').innerHTML = '';
        return;
    }

    tbody.innerHTML = slice.map(r => {
        const badgeTipo = r.tipo === 'cat'
            ? '<span class="badge-tipo b-cat"><i class="fas fa-tag"></i> Categoría</span>'
            : '<span class="badge-tipo b-sub"><i class="fas fa-tags"></i> Subcategoría</span>';
        const badgeEst = r.estado === 'activo'
            ? '<span class="badge-est b-act"><i class="fas fa-circle" style="font-size:.45rem;"></i> Activa</span>'
            : '<span class="badge-est b-inac"><i class="fas fa-ban" style="font-size:.55rem;"></i> Inactiva</span>';
        const ri = r.rango_inicio > 0
            ? `<span class="rango-chip">${r.rango_inicio}</span>`
            : `<span class="rango-chip vacio">—</span>`;
        const rf = r.rango_fin > 0
            ? `<span class="rango-chip">${r.rango_fin}</span>`
            : `<span class="rango-chip vacio">—</span>`;
        const atrs = r.atracciones > 0
            ? `<span style="color:var(--green);font-weight:700;">${r.atracciones}</span>`
            : `<span style="color:var(--muted);">—</span>`;

        return `<tr>
            <td>
                <div class="td-grupo">
                    <div class="gn">${r.grupo}</div>
                    <div style="font-size:.72rem;color:var(--muted);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                         title="${esc(r.grupo_nombre)}">${esc(r.grupo_nombre)}</div>
                </div>
            </td>
            <td>
                <div class="td-nombre">${esc(r.nombre)}</div>
                ${r.catref ? `<div class="td-catref"><i class="fas fa-folder" style="font-size:.5rem;margin-right:3px;"></i>${esc(r.catref)}</div>` : ''}
            </td>
            <td>${badgeTipo}</td>
            <td>${badgeEst}</td>
            <td>${ri}</td>
            <td>${rf}</td>
            <td style="text-align:center;">${atrs}</td>
        </tr>`;
    }).join('');

    // Paginación
    document.getElementById('rptInfoPag').textContent =
        `Mostrando ${start+1}–${Math.min(start+perPage, total)} de ${total} registros`;

    let bHtml = `<button class="pag-btn" onclick="irPagRep(${page-1})" ${page<=1?'disabled':''}>‹</button>`;
    const s = Math.max(1, page-2), e = Math.min(pages, page+2);
    for (let i = s; i <= e; i++) {
        bHtml += `<button class="pag-btn ${i===page?'active':''}" onclick="irPagRep(${i})">${i}</button>`;
    }
    bHtml += `<button class="pag-btn" onclick="irPagRep(${page+1})" ${page>=pages?'disabled':''}>›</button>`;
    document.getElementById('rptPagBtns').innerHTML = bHtml;
}

function irPagRep(p) {
    const pages = Math.ceil(_repDatos.length / _repPag.perPage);
    if (p < 1 || p > pages) return;
    _repPag.page = p;
    renderTablaRep();
}

// ════════════════════════════════════════════════════════════════
//  EXPORTAR EXCEL
// ════════════════════════════════════════════════════════════════
function exportarExcel() {
    if (!_repDatos.length) {
        Swal.fire({ title: 'Sin datos', text: 'Aplica filtros y asegúrate de tener registros', icon: 'info',
            background: '#1c1c32', color: '#f0f0f5', confirmButtonColor: '#e55a2b' });
        return;
    }

    // Hoja 1: datos del reporte
    const filas = _repDatos.map((r, i) => ({
        '#':            i + 1,
        'Grupo ID':     r.grupo,
        'Modelo':       r.grupo_nombre,
        'Nombre':       r.nombre,
        'Categoría ref':r.catref || '',
        'Tipo':         r.tipo === 'cat' ? 'Categoría' : 'Subcategoría',
        'Estado':       r.estado === 'activo' ? 'Activa' : 'Inactiva',
        'Rango inicio': r.rango_inicio || '',
        'Rango fin':    r.rango_fin    || '',
        'Atracciones':  r.atracciones  || 0,
    }));

    const ws = XLSX.utils.json_to_sheet(filas);
    ws['!cols'] = [
        {wch:4},{wch:10},{wch:22},{wch:28},{wch:20},
        {wch:14},{wch:10},{wch:14},{wch:12},{wch:13}
    ];

    // Encabezado superior con el filtro activo
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Categorías');

    // Hoja 2: resumen KPIs
    const resumen = [
        { 'KPI': 'Total registros en reporte', 'Valor': _repDatos.length },
        { 'KPI': 'Categorías', 'Valor': _repDatos.filter(r => r.tipo==='cat').length },
        { 'KPI': 'Subcategorías', 'Valor': _repDatos.filter(r => r.tipo==='sub').length },
        { 'KPI': 'Activas', 'Valor': _repDatos.filter(r => r.estado==='activo').length },
        { 'KPI': 'Inactivas', 'Valor': _repDatos.filter(r => r.estado==='inactivo').length },
        { 'KPI': 'Con rangos', 'Valor': _repDatos.filter(r => r.rango_inicio > 0 || r.rango_fin > 0).length },
        { 'KPI': 'Sin rangos', 'Valor': _repDatos.filter(r => !r.rango_inicio && !r.rango_fin).length },
        { 'KPI': 'Generado el', 'Valor': new Date().toLocaleString('es-ES') },
        { 'KPI': 'Filtros aplicados', 'Valor': document.getElementById('phFiltroTxt').textContent },
    ];
    const wsR = XLSX.utils.json_to_sheet(resumen);
    wsR['!cols'] = [{wch:28},{wch:30}];
    XLSX.utils.book_append_sheet(wb, wsR, 'Resumen');

    // Hoja 3: por grupo
    const porGrupo = [];
    _grupos.forEach(g => {
        const cats = (g.categoria_rangos    || []).length;
        const subs = (g.subcategoria_rangos || []).length;
        const cAct = (g.categoria_rangos    || []).filter(c => (c.estado||'activo')==='activo').length;
        const sAct = (g.subcategoria_rangos || []).filter(s => (s.estado||'activo')==='activo').length;
        porGrupo.push({
            'ID':             g.id,
            'Modelo':         g.modelo_nombre || `Grupo ${g.id}`,
            'Versión':        g.modelo_version || '',
            'Categorías':     cats,
            'Cats. activas':  cAct,
            'Subcategorías':  subs,
            'Subs. activas':  sAct,
            'Atracciones':    _conteos[g.id] || 0,
        });
    });
    const wsG = XLSX.utils.json_to_sheet(porGrupo);
    wsG['!cols'] = [{wch:6},{wch:22},{wch:10},{wch:13},{wch:13},{wch:14},{wch:13},{wch:12}];
    XLSX.utils.book_append_sheet(wb, wsG, 'Por grupo');

    const fecha = new Date().toISOString().slice(0,10);
    XLSX.writeFile(wb, `etreva_categorias_${fecha}.xlsx`);
}

// ════════════════════════════════════════════════════════════════
//  IMPRIMIR REPORTE
// ════════════════════════════════════════════════════════════════
function imprimirReporte() {
    if (!_repDatos.length) {
        Swal.fire({ title: 'Sin datos', text: 'No hay registros para imprimir con los filtros actuales',
            icon: 'info', background: '#1c1c32', color: '#f0f0f5', confirmButtonColor: '#e55a2b' });
        return;
    }

    // Asegurarse de que la tabla tenga TODOS los datos (sin paginación) para imprimir
    const backupPage    = _repPag.page;
    const backupPerPage = _repPag.perPage;
    _repPag.perPage = 9999;
    _repPag.page    = 1;
    renderTablaRep();

    // Actualizar encabezado de print
    document.getElementById('phFecha').textContent = 'Generado el ' +
        new Date().toLocaleDateString('es-ES',{day:'2-digit',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'});

    window.print();

    // Restaurar paginación
    _repPag.perPage = backupPerPage;
    _repPag.page    = backupPage;
    renderTablaRep();
}

// ════════════════════════════════════════════════════════════════
//  TABS Y VISTA DE GRUPOS (sin cambios)
// ════════════════════════════════════════════════════════════════
function setTab(tab, btn) {
    _tab = tab;
    document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderGrupos();
}

function renderGrupos() {
    const q   = document.getElementById('txtBuscar').value.trim().toLowerCase();
    const est = document.getElementById('selEstado').value;
    const wrap = document.getElementById('gruposWrap');

    if (!_grupos.length) {
        wrap.innerHTML = '<div class="estado-panel"><i class="fas fa-box-open"></i><p>No hay grupos disponibles</p></div>';
        return;
    }
    const html = _grupos.map(g => buildGrupo(g, q, est)).filter(Boolean).join('');
    wrap.innerHTML = html ||
        '<div class="estado-panel"><i class="fas fa-filter"></i><p>Sin resultados para los filtros aplicados</p></div>';
    if (q || est || _tab !== 'todo') _grupos.forEach(g => expandir(g.id));
}

function buildGrupo(g, q, est) {
    let cats = [...(g.categoria_rangos    || [])];
    let subs = [...(g.subcategoria_rangos || [])];

    if (_tab === 'inact') {
        cats = cats.filter(c => (c.estado ?? 'activo') === 'inactivo');
        subs = subs.filter(s => (s.estado ?? 'activo') === 'inactivo');
        if (!cats.length && !subs.length) return '';
    }
    if (est) {
        cats = cats.filter(c => (c.estado ?? 'activo') === est);
        subs = subs.filter(s => (s.estado ?? 'activo') === est);
    }
    if (q) {
        cats = cats.filter(c => (c.categoria    ?? '').toLowerCase().includes(q));
        subs = subs.filter(s => (s.subcategoria ?? '').toLowerCase().includes(q));
    }
    if ((q || est) && !cats.length && !subs.length) return '';

    const allC  = g.categoria_rangos    || [];
    const allS  = g.subcategoria_rangos || [];
    const actC  = allC.filter(c => (c.estado ?? 'activo') === 'activo').length;
    const actS  = allS.filter(s => (s.estado ?? 'activo') === 'activo').length;
    const inact = (allC.length - actC) + (allS.length - actS);
    const mostrarCats = _tab !== 'subs';
    const mostrarSubs = _tab !== 'cats';

    return `<div class="grupo-card" id="gc-${g.id}">
        <div class="grupo-header" onclick="toggleGrupo(${g.id})">
            <div class="gh-left">
                <div class="grupo-num">${g.id}</div>
                <div>
                    <div class="grupo-info-title">${g.modelo_nombre ? esc(g.modelo_nombre) : 'Grupo de categorías #'+g.id}</div>
                    <div class="grupo-info-meta">
                        ${g.modelo_version ? `<span style="color:rgba(240,240,245,.3);font-size:.62rem;">v${esc(g.modelo_version)}</span> &nbsp;·&nbsp;` : ''}
                        ${actC}/${allC.length} categorías &nbsp;·&nbsp; ${actS}/${allS.length} subcategorías activas
                        ${g.total_atracciones > 0 ? `&nbsp;·&nbsp;<span style="color:var(--green)">${g.total_atracciones} atracciones</span>` : ''}
                    </div>
                </div>
            </div>
            <div class="gh-right">
                <div class="gpills">
                    <span class="gpill gpill-cat"><i class="fas fa-tag"></i>${allC.length}</span>
                    <span class="gpill gpill-sub"><i class="fas fa-tags"></i>${allS.length}</span>
                    ${g.total_atracciones > 0 ? `<span class="gpill gpill-atr"><i class="fas fa-map-pin"></i>${g.total_atracciones}</span>` : ''}
                    ${inact > 0 ? `<span class="gpill gpill-inact"><i class="fas fa-ban"></i>${inact}</span>` : ''}
                </div>
                <i class="fas fa-chevron-down chevron-ico" id="ico-${g.id}"></i>
            </div>
        </div>
        <div class="grupo-body" id="gb-${g.id}">
            ${mostrarCats ? seccionCats(g, cats, q) : ''}
            ${mostrarCats && mostrarSubs ? '<div class="sec-sep"></div>' : ''}
            ${mostrarSubs ? seccionSubs(g, subs, q) : ''}
        </div>
    </div>`;
}

function seccionCats(g, cats, q) {
    return `<div class="seccion">
        <div class="sec-header">
            <div class="sec-title t-cat"><i class="fas fa-tag"></i> Categorías <span class="sec-count">(${cats.length})</span></div>
            <button class="btn-agregar" onclick="modalAgregarCat(${g.id})"><i class="fas fa-plus"></i> Agregar</button>
        </div>
        ${cats.length
            ? `<div class="items-grid">${cats.map(c => itemCat(g.id, c, q)).join('')}</div>`
            : `<div class="sec-empty"><i class="fas fa-tag"></i>Sin categorías${q ? ' para esta búsqueda' : ''}</div>`}
    </div>`;
}

function itemCat(gid, c, q) {
    const est = c.estado ?? 'activo';
    const on  = est === 'activo';
    return `<div class="item-card ${on ? '' : 'inactivo'}">
        <div class="est-dot ${est}"></div>
        <div class="item-nombre">${hl(c.categoria, q)}</div>
        <div class="item-rangos">
            <span class="rchip ${!c.rango_inicio ? 'null' : ''}">${c.rango_inicio || 'sin rango'}</span>
            <i class="fas fa-arrow-right rarrow"></i>
            <span class="rchip ${!c.rango_fin ? 'null' : ''}">${c.rango_fin || 'sin rango'}</span>
        </div>
        <div class="item-actions">
            <button class="btn-xs ${on ? 'btn-on' : 'btn-off'}" onclick="toggleCat(${gid}, '${escA(c.categoria)}', this)">
                <i class="fas fa-${on ? 'toggle-on' : 'toggle-off'}"></i> ${on ? 'Activa' : 'Inactiva'}
            </button>
            <button class="btn-xs btn-edit" onclick="modalEditarCat(${gid}, '${escA(c.categoria)}', ${c.rango_inicio ?? 0}, ${c.rango_fin ?? 0})">
                <i class="fas fa-pen-to-square"></i> Rangos
            </button>
        </div>
    </div>`;
}

function seccionSubs(g, subs, q) {
    return `<div class="seccion">
        <div class="sec-header">
            <div class="sec-title t-sub"><i class="fas fa-tags"></i> Subcategorías <span class="sec-count">(${subs.length})</span></div>
            <button class="btn-agregar" onclick="modalAgregarSub(${g.id})"><i class="fas fa-plus"></i> Agregar</button>
        </div>
        ${subs.length
            ? `<div class="items-grid">${subs.map(s => itemSub(g.id, s, q)).join('')}</div>`
            : `<div class="sec-empty"><i class="fas fa-tags"></i>Sin subcategorías${q ? ' para esta búsqueda' : ''}</div>`}
    </div>`;
}

function itemSub(gid, s, q) {
    const est    = s.estado ?? 'activo';
    const on     = est === 'activo';
    const partes = (s.subcategoria ?? '').split(' - ');
    const label  = partes[1] || partes[0];
    const catRef = partes.length > 1 ? partes[0] : '';
    return `<div class="item-card ${on ? '' : 'inactivo'}">
        <div class="est-dot ${est}"></div>
        <div class="item-nombre">${hl(label, q)}</div>
        ${catRef ? `<div class="item-sub-cat"><i class="fas fa-folder" style="font-size:.55rem;margin-right:3px;opacity:.5;"></i>${hl(catRef, q)}</div>` : ''}
        <div class="item-rangos">
            <span class="rchip ${!s.rango_inicio ? 'null' : ''}">${s.rango_inicio || 'sin rango'}</span>
            <i class="fas fa-arrow-right rarrow"></i>
            <span class="rchip ${!s.rango_fin ? 'null' : ''}">${s.rango_fin || 'sin rango'}</span>
        </div>
        <div class="item-actions">
            <button class="btn-xs ${on ? 'btn-on' : 'btn-off'}" onclick="toggleSub(${gid}, '${escA(s.subcategoria)}', this)">
                <i class="fas fa-${on ? 'toggle-on' : 'toggle-off'}"></i> ${on ? 'Activa' : 'Inactiva'}
            </button>
            <button class="btn-xs btn-edit" onclick="modalEditarSub(${gid}, '${escA(s.subcategoria)}', ${s.rango_inicio ?? 0}, ${s.rango_fin ?? 0})">
                <i class="fas fa-pen-to-square"></i> Rangos
            </button>
        </div>
    </div>`;
}

function toggleGrupo(id) {
    document.getElementById(`gb-${id}`)?.classList.toggle('open');
    document.getElementById(`ico-${id}`)?.classList.toggle('open');
}
function expandir(id) {
    document.getElementById(`gb-${id}`)?.classList.add('open');
    document.getElementById(`ico-${id}`)?.classList.add('open');
}

// ════════════════════════════════════════════════════════════════
//  TOGGLE ACCIONES (igual que antes)
// ════════════════════════════════════════════════════════════════
function toggleCat(gid, nombre, btn) {
    btn.disabled = true; btn.innerHTML = '<span class="spin"></span>';
    fetch('index.php?c=adminCategorias&a=ToggleCategoria', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({grupo_id:gid, nombre}),
    }).then(r=>r.json()).then(d=>{
        if (!d.success) { toastErr(d.message); btn.disabled=false; return; }
        const g = _grupos.find(x=>x.id===gid);
        if (g) { const c = g.categoria_rangos.find(x=>x.categoria===nombre); if(c) c.estado=d.nuevo_estado; }
        renderGrupos(); if (_tab!=='subs') expandir(gid);
        aplicarFiltrosRep(); soloStats();
    }).catch(()=>{ toastErr('Error de conexión'); btn.disabled=false; });
}

function toggleSub(gid, key, btn) {
    btn.disabled = true; btn.innerHTML = '<span class="spin"></span>';
    fetch('index.php?c=adminCategorias&a=ToggleSubcategoria', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({grupo_id:gid, key}),
    }).then(r=>r.json()).then(d=>{
        if (!d.success) { toastErr(d.message); btn.disabled=false; return; }
        const g = _grupos.find(x=>x.id===gid);
        if (g) { const s = g.subcategoria_rangos.find(x=>x.subcategoria===key); if(s) s.estado=d.nuevo_estado; }
        renderGrupos(); if (_tab!=='cats') expandir(gid);
        aplicarFiltrosRep(); soloStats();
    }).catch(()=>{ toastErr('Error de conexión'); btn.disabled=false; });
}

// ════════════════════════════════════════════════════════════════
//  MODALES SWAL (igual que antes)
// ════════════════════════════════════════════════════════════════
const swBase = {
    background:'#1c1c32', color:'#f0f0f5',
    confirmButtonColor:'#e55a2b', cancelButtonColor:'rgba(255,255,255,.07)',
    cancelButtonText:'Cancelar', showCancelButton:true,
};
const IS = 'width:100%;background:#0f0f1a;border:1px solid rgba(229,90,43,.28);border-radius:8px;color:#f0f0f5;padding:9px 12px;font-size:.84rem;outline:none;margin-top:4px;';
const LS = 'font-size:.67rem;color:rgba(240,240,245,.4);text-transform:uppercase;letter-spacing:.8px;display:block;margin-top:10px;';

function rangoHtml(ri='',rf=''){
    return `<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:2px;">
        <div><label style="${LS}">Rango inicio</label><input id="sw-ri" type="number" value="${ri}" placeholder="Ej: 6101" style="${IS}"></div>
        <div><label style="${LS}">Rango fin</label><input id="sw-rf" type="number" value="${rf}" placeholder="Ej: 6150" style="${IS}"></div>
    </div>`;
}
const getRangos = () => ({
    rango_inicio: parseInt(document.getElementById('sw-ri').value)||0,
    rango_fin:    parseInt(document.getElementById('sw-rf').value)||0,
});
const postJSON = (a,b) => fetch(`index.php?c=adminCategorias&a=${a}`,{
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(b),
}).then(r=>r.json());

function modalAgregarCat(gid){
    Swal.fire({...swBase,
        title:'<i class="fas fa-tag" style="color:#38bdf8;margin-right:8px;"></i>Agregar Categoría',
        html:`<label style="${LS}">Nombre</label><input id="sw-nombre" placeholder="Ej: Criolla Tradicional" style="${IS}">${rangoHtml()}
              <div style="font-size:.6rem;color:rgba(240,240,245,.25);margin-top:8px;">Los rangos pueden quedar en 0 si no hay atracciones aún.</div>`,
        confirmButtonText:'<i class="fas fa-plus"></i> Agregar',
        preConfirm:()=>{
            const n=document.getElementById('sw-nombre').value.trim();
            if(!n){Swal.showValidationMessage('El nombre es obligatorio');return false;}
            return {nombre:n,...getRangos()};
        },
    }).then(r=>{
        if(!r.isConfirmed)return;
        postJSON('AgregarCategoria',{grupo_id:gid,...r.value}).then(d=>{swRes(d);if(d.success){cargarTodo();}});
    });
}

function modalAgregarSub(gid){
    Swal.fire({...swBase,
        title:'<i class="fas fa-tags" style="color:#a78bfa;margin-right:8px;"></i>Agregar Subcategoría',
        html:`<label style="${LS}">Clave (formato: categoria - nombre)</label>
              <input id="sw-key" placeholder="Ej: criolla_tradicional - parrilla" style="${IS}">
              <div style="font-size:.6rem;color:rgba(240,240,245,.25);margin-top:4px;">Guion bajo para espacios, todo minúsculas.</div>
              ${rangoHtml()}`,
        confirmButtonText:'<i class="fas fa-plus"></i> Agregar',
        preConfirm:()=>{
            const k=document.getElementById('sw-key').value.trim();
            if(!k){Swal.showValidationMessage('La clave es obligatoria');return false;}
            return {key:k,...getRangos()};
        },
    }).then(r=>{
        if(!r.isConfirmed)return;
        postJSON('AgregarSubcategoria',{grupo_id:gid,...r.value}).then(d=>{swRes(d);if(d.success){cargarTodo();}});
    });
}

function modalEditarCat(gid,nombre,ri,rf){
    Swal.fire({...swBase,
        title:'<i class="fas fa-pen-to-square" style="color:#38bdf8;margin-right:8px;"></i>Editar Rangos',
        html:`<div style="background:rgba(56,189,248,.07);border:1px solid rgba(56,189,248,.18);border-radius:8px;padding:9px 13px;margin-bottom:6px;font-size:.78rem;text-align:left;">
              <i class="fas fa-tag" style="color:#38bdf8;margin-right:6px;"></i><strong style="color:#fff;">${esc(nombre)}</strong></div>${rangoHtml(ri,rf)}`,
        confirmButtonText:'<i class="fas fa-floppy-disk"></i> Guardar', confirmButtonColor:'#38bdf8',
        preConfirm:()=>getRangos(),
    }).then(r=>{
        if(!r.isConfirmed)return;
        postJSON('EditarCategoria',{grupo_id:gid,nombre,...r.value}).then(d=>{swRes(d);if(d.success){cargarTodo();}});
    });
}

function modalEditarSub(gid,key,ri,rf){
    const partes=key.split(' - '); const label=partes[1]||partes[0];
    Swal.fire({...swBase,
        title:'<i class="fas fa-pen-to-square" style="color:#a78bfa;margin-right:8px;"></i>Editar Rangos',
        html:`<div style="background:rgba(167,139,250,.07);border:1px solid rgba(167,139,250,.18);border-radius:8px;padding:9px 13px;margin-bottom:6px;font-size:.78rem;text-align:left;">
              <i class="fas fa-tags" style="color:#a78bfa;margin-right:6px;"></i>
              <strong style="color:#fff;">${esc(label)}</strong>
              ${partes.length>1?`<span style="color:rgba(240,240,245,.35);font-size:.68rem;margin-left:6px;">${esc(partes[0])}</span>`:''}
              </div>${rangoHtml(ri,rf)}`,
        confirmButtonText:'<i class="fas fa-floppy-disk"></i> Guardar', confirmButtonColor:'#a78bfa',
        preConfirm:()=>getRangos(),
    }).then(r=>{
        if(!r.isConfirmed)return;
        postJSON('EditarSubcategoria',{grupo_id:gid,key,...r.value}).then(d=>{swRes(d);if(d.success){cargarTodo();}});
    });
}

function swRes(d){
    Swal.fire({title:d.success?'¡Listo!':'Error',text:d.message,icon:d.success?'success':'error',
        background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b',
        timer:d.success?1800:undefined,timerProgressBar:d.success});
}
function toastErr(msg){
    Swal.fire({toast:true,position:'bottom-end',icon:'error',title:msg,
        showConfirmButton:false,timer:3000,background:'#1c1c32',color:'#f0f0f5'});
}

// ════════════════════════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════════════════════════
document.getElementById('btnRefresh').addEventListener('click', cargarTodo);
document.addEventListener('DOMContentLoaded', cargarTodo);
</script>
</body>
</html>