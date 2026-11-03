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
<title>Etreva Admin — Modelos MR</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/menu.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<style>
:root{
    --bg:#0f0f1a;--surface:#161625;--card:#1c1c32;
    --border:rgba(229,90,43,.15);--text:#f0f0f5;--muted:rgba(240,240,245,.45);
    --orange:#e55a2b;--amber:#f59e0b;--green:#22c55e;
    --blue:#38bdf8;--red:#ef4444;--purple:#a78bfa;--teal:#2dd4bf;
    --indigo:#6366f1;--pink:#ec4899;
}
*{box-sizing:border-box;}
html,body{background:var(--bg)!important;color:var(--text)!important;
    min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;margin:0;padding:0;}
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

.stat-strip{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;}
.stat-pill{background:var(--card);border:1px solid var(--border);border-radius:14px;
    padding:12px 14px;display:flex;align-items:center;gap:10px;transition:border-color .2s;}
.stat-pill:hover{border-color:rgba(229,90,43,.35);}
.stat-pill-ico{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;
    justify-content:center;font-size:.85rem;flex-shrink:0;}
.stat-pill-txt{font-size:1.2rem;font-weight:900;color:#fff;line-height:1;}
.stat-pill-lbl{font-size:.65rem;color:var(--muted);margin-top:2px;}

.filtros-wrap{background:var(--card);border:1px solid var(--border);border-radius:14px;
    padding:12px 16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.filtros-wrap input[type=text],.filtros-wrap select{
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;
    padding:8px 12px;color:var(--text);font-size:.82rem;outline:none;transition:border-color .2s;}
.filtros-wrap input[type=text]{flex:1;min-width:200px;}
.filtros-wrap input:focus,.filtros-wrap select:focus{border-color:var(--orange);}
.filtros-wrap select option{background:#1c1c32;}
.btn-filtrar{background:linear-gradient(135deg,var(--orange),#ff9a6c);color:#fff;
    border:none;border-radius:9px;padding:8px 16px;font-size:.82rem;font-weight:700;cursor:pointer;}
.btn-nuevo{background:rgba(34,197,94,.14);color:var(--green);border:1px solid rgba(34,197,94,.3);
    border-radius:9px;padding:8px 14px;font-size:.82rem;font-weight:700;cursor:pointer;
    display:flex;align-items:center;gap:6px;transition:all .2s;white-space:nowrap;}
.btn-nuevo:hover{background:rgba(34,197,94,.25);}
.btn-export{padding:8px 12px;border-radius:9px;border:none;font-size:.76rem;font-weight:700;
    cursor:pointer;display:flex;align-items:center;gap:5px;transition:all .2s;white-space:nowrap;}
.btn-excel{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25);}
.btn-excel:hover{background:rgba(34,197,94,.22);}
.btn-pdf{background:rgba(239,68,68,.1);color:var(--red);border:1px solid rgba(239,68,68,.25);}
.btn-pdf:hover{background:rgba(239,68,68,.2);}
.export-sep{width:1px;height:26px;background:rgba(255,255,255,.08);margin:0 2px;}

.main-layout{display:grid;grid-template-columns:1fr 420px;gap:16px;align-items:start;}
.main-layout.sin-panel{grid-template-columns:1fr;}

.tabla-wrap{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;}
.adm-table{width:100%;border-collapse:collapse;font-size:.79rem;}
.adm-table thead th{background:rgba(255,255,255,.03);color:var(--muted);font-weight:600;
    font-size:.66rem;letter-spacing:1px;text-transform:uppercase;
    padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.07);white-space:nowrap;}
.adm-table thead th.sortable{cursor:pointer;user-select:none;}
.adm-table thead th.sortable:hover{color:var(--text);}
.adm-table thead th.sort-active{color:var(--orange);}
.adm-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;}
.adm-table tbody tr:hover{background:rgba(229,90,43,.04);cursor:pointer;}
.adm-table tbody tr.row-selected{background:rgba(229,90,43,.08);border-left:3px solid var(--orange);}
.adm-table tbody td{padding:10px 12px;color:var(--text);vertical-align:middle;}

.badge{font-size:.6rem;font-weight:700;padding:3px 8px;border-radius:20px;
    white-space:nowrap;display:inline-flex;align-items:center;gap:4px;}
.b-activo     {background:rgba(34,197,94,.12); color:var(--green);}
.b-inactivo   {background:rgba(239,68,68,.1);  color:var(--red);}
.b-entrenando {background:rgba(245,158,11,.12);color:var(--amber);}
.b-cat        {background:rgba(167,139,250,.12);color:var(--purple);}

.mini-bar-wrap{display:flex;flex-direction:column;gap:3px;}
.mini-bar-lbl{font-size:.6rem;color:var(--muted);display:flex;justify-content:space-between;}
.mini-bar{height:4px;border-radius:4px;background:rgba(255,255,255,.07);overflow:hidden;}
.mini-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--orange),#ff9a6c);}

.acciones{display:flex;gap:4px;flex-wrap:wrap;}
.btn-sm{padding:4px 8px;border-radius:6px;font-size:.68rem;font-weight:700;border:none;
    cursor:pointer;display:inline-flex;align-items:center;gap:3px;transition:all .18s;white-space:nowrap;}
.btn-view    {background:rgba(167,139,250,.1);  color:var(--purple);}
.btn-view:hover{background:rgba(167,139,250,.22);}
.btn-edit    {background:rgba(56,189,248,.1);   color:var(--blue);}
.btn-edit:hover{background:rgba(56,189,248,.22);}
.btn-activo  {background:rgba(34,197,94,.1);    color:var(--green);}
.btn-activo:hover{background:rgba(34,197,94,.22);}
.btn-inactivo{background:rgba(239,68,68,.08);   color:var(--red);}
.btn-inactivo:hover{background:rgba(239,68,68,.2);}
.btn-train   {background:rgba(245,158,11,.1);   color:var(--amber);}
.btn-train:hover{background:rgba(245,158,11,.22);}
.btn-add-alt {background:rgba(34,197,94,.1);    color:var(--green);border:1px solid rgba(34,197,94,.25);}
.btn-add-alt:hover{background:rgba(34,197,94,.22);}

/* PAGINACIÓN COMPACTA */
.paginacion{display:flex;align-items:center;justify-content:space-between;
    padding:10px 14px;border-top:1px solid rgba(255,255,255,.06);font-size:.75rem;color:var(--muted);}
.pag-btns{display:flex;gap:4px;}
.pag-btn{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:7px;
    padding:4px 9px;color:var(--muted);cursor:pointer;font-size:.71rem;transition:all .18s;}
.pag-btn:hover,.pag-btn.active{background:rgba(229,90,43,.15);color:var(--orange);border-color:rgba(229,90,43,.3);}
.pag-btn:disabled{opacity:.3;cursor:not-allowed;}

/* PAGINACIÓN INLINE PANEL — compacta sin botonera enorme */
.pag-compact{display:flex;align-items:center;gap:8px;justify-content:center;
    padding:8px 0;flex-wrap:wrap;}
.pag-compact-info{font-size:.68rem;color:var(--muted);white-space:nowrap;}
.pag-compact-btns{display:flex;gap:3px;align-items:center;}
.pag-compact-btns .pag-btn{font-size:.65rem;padding:3px 7px;}
.pag-jump{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:7px;
    width:44px;text-align:center;color:var(--text);font-size:.68rem;padding:2px 4px;outline:none;}
.pag-jump:focus{border-color:var(--orange);}

.panel-detalle{background:var(--card);border:1px solid var(--border);border-radius:16px;
    overflow:hidden;position:sticky;top:80px;max-height:calc(100vh - 100px);
    display:flex;flex-direction:column;}
.panel-header{padding:14px 16px 10px;border-bottom:1px solid rgba(255,255,255,.07);
    display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.panel-header h3{font-size:.88rem;font-weight:800;color:#fff;margin:0;}
.panel-close{background:none;border:none;color:var(--muted);cursor:pointer;
    width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;}
.panel-close:hover{background:rgba(255,255,255,.08);color:#fff;}
.panel-body{overflow-y:auto;padding:14px 16px;flex:1;display:flex;flex-direction:column;gap:14px;}
.panel-body::-webkit-scrollbar{width:4px;}
.panel-body::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px;}
.panel-tabs{display:flex;gap:4px;flex-wrap:wrap;border-bottom:1px solid rgba(255,255,255,.07);
    padding:0 16px;flex-shrink:0;}
.panel-tab{padding:8px 12px;font-size:.72rem;font-weight:700;color:var(--muted);cursor:pointer;
    border:none;background:none;border-bottom:2px solid transparent;transition:all .2s;white-space:nowrap;}
.panel-tab:hover{color:var(--text);}
.panel-tab.active{color:var(--orange);border-bottom-color:var(--orange);}
.sec-title{font-size:.64rem;color:var(--orange);text-transform:uppercase;letter-spacing:1.5px;
    font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:6px;}
.sec-title::after{content:'';flex:1;height:1px;background:rgba(229,90,43,.18);}
.kv-row{display:flex;justify-content:space-between;align-items:flex-start;
    padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:.75rem;gap:8px;}
.kv-row:last-child{border-bottom:none;}
.kv-key{color:var(--muted);flex-shrink:0;min-width:110px;}
.kv-val{color:var(--text);font-weight:600;text-align:right;word-break:break-all;}
.mapeo-group{margin-bottom:10px;}
.mapeo-group-title{font-size:.65rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;
    margin-bottom:5px;display:flex;align-items:center;justify-content:space-between;}
.mapeo-chips{display:flex;flex-wrap:wrap;gap:4px;}
.mapeo-chip{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);
    border-radius:20px;padding:2px 8px;font-size:.62rem;color:var(--text);
    display:flex;align-items:center;gap:3px;}
.mapeo-chip-idx{color:var(--orange);font-weight:700;font-size:.58rem;}
.alt-table{width:100%;border-collapse:collapse;font-size:.72rem;}
.alt-table th{color:var(--muted);font-size:.6rem;text-transform:uppercase;letter-spacing:.8px;
    padding:5px 6px;border-bottom:1px solid rgba(255,255,255,.07);text-align:left;}
.alt-table td{padding:5px 6px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:top;}
.alt-table tr:hover td{background:rgba(255,255,255,.03);}
.diff-badge{padding:2px 6px;border-radius:10px;font-size:.56rem;font-weight:700;}
.diff-baja     {background:rgba(34,197,94,.12);  color:var(--green);}
.diff-media    {background:rgba(245,158,11,.12); color:var(--amber);}
.diff-alta     {background:rgba(239,68,68,.1);   color:var(--red);}
.diff-muy_alta {background:rgba(167,139,250,.12);color:var(--purple);}
.ds-row{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);
    border-radius:8px;padding:8px 10px;margin-bottom:6px;font-size:.7rem;}
.ds-row-id{font-size:.62rem;color:var(--muted);margin-bottom:4px;}
.ds-input-grid{display:flex;flex-wrap:wrap;gap:3px;}
.ds-bit{width:14px;height:14px;border-radius:3px;display:inline-flex;align-items:center;
    justify-content:center;font-size:.5rem;font-weight:700;}
.ds-bit-1{background:rgba(229,90,43,.4);color:#fff;}
.ds-bit-0{background:rgba(255,255,255,.05);color:rgba(255,255,255,.2);}
.stats-grid-panel{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.stat-mini{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);
    border-radius:10px;padding:10px;text-align:center;}
.stat-mini-num{font-size:1.3rem;font-weight:900;color:#fff;}
.stat-mini-lbl{font-size:.62rem;color:var(--muted);margin-top:2px;}

/* ── MODAL BASE ── */
.modal-overlay{display:none;position:fixed;inset:0;z-index:1000;background:rgba(8,8,18,.88);
    backdrop-filter:blur(6px);align-items:flex-start;justify-content:center;padding:20px;overflow-y:auto;}
.modal-overlay.show{display:flex;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:20px;
    width:100%;max-width:760px;animation:popIn .22s ease;margin:auto;}
.modal-box.wide{max-width:1000px;}
@keyframes popIn{from{transform:scale(.93) translateY(14px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
.modal-header{padding:16px 20px 12px;border-bottom:1px solid rgba(255,255,255,.07);
    display:flex;align-items:center;justify-content:space-between;
    background:var(--card);z-index:1;border-radius:20px 20px 0 0;}
.modal-header h3{font-size:.96rem;font-weight:800;color:#fff;margin:0;display:flex;align-items:center;gap:8px;}
.modal-close{background:none;border:none;color:var(--muted);font-size:1rem;cursor:pointer;
    width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;transition:all .18s;}
.modal-close:hover{background:rgba(255,255,255,.08);color:#fff;}
.modal-body{padding:18px 20px;}
.modal-footer{padding:10px 20px 16px;border-top:1px solid rgba(255,255,255,.07);
    display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.form-group{display:flex;flex-direction:column;gap:4px;}
.form-group.full{grid-column:1/-1;}
.form-group label{font-size:.66rem;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;font-weight:700;}
.form-group input,.form-group select,.form-group textarea{
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;
    padding:8px 11px;color:var(--text);font-size:.82rem;outline:none;transition:border-color .2s;width:100%;}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--orange);}
.form-group select option{background:#1c1c32;}
.form-group textarea{resize:vertical;min-height:65px;font-family:inherit;}
.form-sep{font-size:.64rem;color:var(--orange);text-transform:uppercase;letter-spacing:1.5px;font-weight:700;
    margin:12px 0 4px;display:flex;align-items:center;gap:8px;grid-column:1/-1;}
.form-sep::after{content:'';flex:1;height:1px;background:rgba(229,90,43,.2);}
.btn-guardar{background:linear-gradient(135deg,var(--orange),#ff9a6c);color:#fff;border:none;
    border-radius:9px;padding:8px 20px;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .2s;
    display:flex;align-items:center;gap:6px;}
.btn-guardar:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(229,90,43,.4);}
.btn-cancelar{background:rgba(255,255,255,.06);color:var(--muted);border:1px solid rgba(255,255,255,.1);
    border-radius:9px;padding:8px 14px;font-size:.82rem;cursor:pointer;transition:all .2s;}
.btn-cancelar:hover{color:var(--text);background:rgba(255,255,255,.1);}

/* ── MODAL ALTERNATIVAS FULL ── */
.alt-modal-search{display:flex;gap:8px;margin-bottom:12px;align-items:center;}
.alt-modal-search input{flex:1;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
    border-radius:9px;padding:7px 12px;color:var(--text);font-size:.82rem;outline:none;}
.alt-modal-search input:focus{border-color:var(--orange);}
.alt-modal-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:8px;
    max-height:55vh;overflow-y:auto;padding-right:4px;}
.alt-modal-grid::-webkit-scrollbar{width:4px;}
.alt-modal-grid::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px;}
.alt-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:10px;
    padding:10px 12px;display:flex;flex-direction:column;gap:4px;position:relative;transition:border-color .18s;}
.alt-card:hover{border-color:rgba(229,90,43,.3);}
.alt-card-id{font-size:.58rem;color:var(--muted);font-weight:700;}
.alt-card-nombre{font-size:.78rem;font-weight:700;color:#fff;line-height:1.2;}
.alt-card-sub{font-size:.62rem;color:var(--blue);}
.alt-card-row{display:flex;gap:6px;align-items:center;margin-top:2px;}
.alt-card-pts{font-size:.85rem;font-weight:900;color:var(--orange);}
.alt-card-actions{position:absolute;top:8px;right:8px;display:flex;gap:4px;}
.alt-modal-pag{display:flex;align-items:center;gap:8px;justify-content:space-between;
    margin-top:12px;padding-top:10px;border-top:1px solid rgba(255,255,255,.06);}

/* ── MODAL MAPEO ── */
.mapeo-modal-body{max-height:65vh;overflow-y:auto;padding-right:4px;}
.mapeo-modal-body::-webkit-scrollbar{width:4px;}
.mapeo-modal-body::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px;}
.mapeo-section{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);
    border-radius:12px;padding:12px 14px;margin-bottom:10px;}
.mapeo-section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.mapeo-section-title{font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;
    display:flex;align-items:center;gap:7px;}
.mapeo-section-meta{font-size:.62rem;color:var(--muted);}
.mapeo-chips-row{display:flex;flex-wrap:wrap;gap:5px;}
.mapeo-chip-big{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);
    border-radius:20px;padding:4px 11px;font-size:.7rem;color:var(--text);
    display:inline-flex;align-items:center;gap:5px;transition:border-color .15s;}
.mapeo-chip-big:hover{border-color:rgba(229,90,43,.3);}
.mapeo-chip-big .idx{color:var(--orange);font-weight:800;font-size:.62rem;
    background:rgba(229,90,43,.12);border-radius:10px;padding:1px 5px;}
/* Editor mapeo */
.mapeo-edit-group{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.06);
    border-radius:12px;padding:10px 12px;margin-bottom:10px;}
.mapeo-edit-group-title{font-size:.66rem;text-transform:uppercase;letter-spacing:1px;font-weight:700;
    color:var(--muted);margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;}
.mapeo-edit-row{display:grid;grid-template-columns:1fr 72px 28px;gap:5px;margin-bottom:5px;align-items:center;}
.mapeo-edit-input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:7px;
    padding:5px 9px;color:var(--text);font-size:.76rem;outline:none;width:100%;}
.mapeo-edit-input:focus{border-color:var(--orange);}
.mapeo-edit-idx{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:7px;
    padding:5px 6px;color:var(--orange);font-size:.76rem;font-weight:700;outline:none;text-align:center;width:100%;}
.mapeo-edit-idx:focus{border-color:var(--orange);}

/* ── MODAL DATASET ── */
.ds-modal-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px;}
.ds-modal-info{font-size:.72rem;color:var(--muted);}
.ds-modal-legend{font-size:.64rem;display:flex;align-items:center;gap:6px;}
.ds-view-scroll{max-height:60vh;overflow-y:auto;padding-right:4px;}
.ds-view-scroll::-webkit-scrollbar{width:4px;}
.ds-view-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px;}
.ds-view-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);
    border-radius:9px;padding:10px 12px;margin-bottom:7px;}
.ds-view-card-header{display:flex;align-items:center;gap:10px;margin-bottom:6px;}
.ds-view-output{background:rgba(167,139,250,.15);color:var(--purple);font-size:.78rem;font-weight:800;
    border-radius:8px;padding:3px 10px;border:1px solid rgba(167,139,250,.3);}
.ds-view-feats{font-size:.62rem;color:var(--muted);}
.ds-bits-grid{display:flex;flex-wrap:wrap;gap:2px;}
.ds-bit-v{width:16px;height:16px;border-radius:3px;display:inline-flex;align-items:center;
    justify-content:center;font-size:.52rem;font-weight:700;cursor:default;}
.ds-bit-v.on{background:rgba(229,90,43,.45);color:#fff;border:1px solid rgba(229,90,43,.5);}
.ds-bit-v.off{background:rgba(255,255,255,.05);color:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.06);}
/* Dataset edit table */
.ds-edit-table{width:100%;border-collapse:collapse;font-size:.74rem;}
.ds-edit-table th{color:var(--muted);font-size:.6rem;text-transform:uppercase;letter-spacing:.8px;
    padding:6px 8px;border-bottom:1px solid rgba(255,255,255,.07);text-align:left;white-space:nowrap;}
.ds-edit-table td{padding:4px 5px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle;}
.ds-edit-table tbody tr:hover td{background:rgba(255,255,255,.02);}
.ds-edit-inp{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:6px;
    padding:4px 7px;color:var(--text);font-size:.72rem;outline:none;width:100%;}
.ds-edit-inp:focus{border-color:var(--orange);}
.ds-edit-out{width:76px;}
.ds-edit-scroll{max-height:52vh;overflow-y:auto;}
.ds-edit-scroll::-webkit-scrollbar{width:4px;}
.ds-edit-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.1);border-radius:4px;}

/* ── TOGGLE MODO MODAL (ver / editar) ── */
.mode-toggle{display:flex;gap:0;border:1px solid rgba(255,255,255,.1);border-radius:9px;overflow:hidden;}
.mode-btn{padding:5px 13px;font-size:.72rem;font-weight:700;cursor:pointer;border:none;
    background:rgba(255,255,255,.04);color:var(--muted);transition:all .18s;}
.mode-btn.active{background:rgba(229,90,43,.2);color:var(--orange);}
.mode-btn:hover:not(.active){background:rgba(255,255,255,.08);color:var(--text);}

/* ── VISUAL FIELDS ── */
.visual-block{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);
    border-radius:12px;padding:14px 16px;display:flex;flex-direction:column;gap:10px;}
.visual-block-label{font-size:.62rem;color:var(--muted);text-transform:uppercase;
    letter-spacing:1px;font-weight:700;margin-bottom:2px;display:flex;align-items:center;gap:6px;}
.v-field{display:flex;flex-direction:column;gap:4px;}
.v-field label{font-size:.61rem;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;
    font-weight:700;display:flex;align-items:center;gap:5px;}
.v-field input,.v-field select{
    background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;
    padding:7px 11px;color:var(--text);font-size:.81rem;outline:none;transition:border-color .2s;width:100%;}
.v-field input:focus,.v-field select:focus{border-color:var(--orange);}
.v-field select option{background:#1c1c32;}
.v-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.v-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;}
.tipo-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;}
.tipo-chip{padding:5px 12px;border-radius:20px;font-size:.73rem;font-weight:600;cursor:pointer;
    border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);
    color:var(--muted);transition:all .18s;user-select:none;}
.tipo-chip:hover{border-color:rgba(229,90,43,.4);color:var(--text);}
.tipo-chip.sel-tipo{background:rgba(56,189,248,.15);color:#7dd3fc;border-color:rgba(56,189,248,.4);}
.etrain-chip{padding:5px 12px;border-radius:20px;font-size:.73rem;font-weight:600;cursor:pointer;
    border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);
    color:var(--muted);transition:all .18s;user-select:none;}
.etrain-chip:hover{border-color:rgba(229,90,43,.4);color:var(--text);}
.etrain-chip.sel-pendiente   {background:rgba(245,158,11,.15);color:#fcd34d;border-color:rgba(245,158,11,.4);}
.etrain-chip.sel-entrenando  {background:rgba(56,189,248,.15);color:#7dd3fc;border-color:rgba(56,189,248,.4);}
.etrain-chip.sel-completado  {background:rgba(34,197,94,.15);color:#86efac;border-color:rgba(34,197,94,.4);}
.etrain-chip.sel-error       {background:rgba(239,68,68,.15);color:#f87171;border-color:rgba(239,68,68,.4);}
.cat-chip{padding:5px 12px;border-radius:20px;font-size:.73rem;font-weight:600;cursor:pointer;
    border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);
    color:var(--muted);transition:all .18s;user-select:none;}
.cat-chip:hover{border-color:rgba(229,90,43,.4);color:var(--text);}
.cat-chip.sel-cat{background:rgba(167,139,250,.15);color:#c4b5fd;border-color:rgba(167,139,250,.4);}
.json-preview-toggle{background:none;border:1px solid rgba(255,255,255,.08);border-radius:8px;
    padding:4px 11px;font-size:.66rem;color:var(--muted);cursor:pointer;
    display:inline-flex;align-items:center;gap:5px;transition:all .18s;}
.json-preview-toggle:hover{border-color:var(--orange);color:var(--orange);}
.json-preview-box{background:rgba(0,0,0,.35);border:1px solid rgba(255,255,255,.07);
    border-radius:9px;padding:10px 12px;font-size:.67rem;font-family:'Courier New',monospace;
    color:var(--teal);white-space:pre-wrap;word-break:break-all;max-height:90px;overflow-y:auto;
    display:none;margin-top:5px;}
.json-preview-box.show{display:block;}
.num-stepper{display:flex;align-items:center;gap:6px;}
.num-stepper input[type=number]{width:80px;text-align:center;}
.num-stepper button{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
    border-radius:7px;width:26px;height:26px;color:var(--muted);cursor:pointer;
    display:flex;align-items:center;justify-content:center;font-size:.85rem;transition:all .18s;}
.num-stepper button:hover{background:rgba(229,90,43,.15);color:var(--orange);}

.skeleton{background:linear-gradient(90deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.09) 50%,rgba(255,255,255,.04) 100%);
    background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:8px;}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.empty-state{text-align:center;padding:40px 20px;color:var(--muted);}
.empty-state i{font-size:2.2rem;margin-bottom:10px;display:block;opacity:.3;}

/* Print */
@media print{
    .topbar,.stat-strip,.filtros-wrap,.acciones,.pag-btns,
    #adminMenu,.btn-icon,.modal-overlay,.panel-detalle,button{display:none!important;}
    main{margin-left:0!important;}
    body,html{background:#fff!important;color:#000!important;}
    .tabla-wrap{border:1px solid #ccc!important;border-radius:0!important;}
    .adm-table thead th{background:#f5f5f5!important;color:#333!important;
        border-bottom:2px solid #ccc!important;font-size:.7rem!important;}
    .adm-table tbody td{color:#111!important;font-size:.75rem!important;}
    .main-layout{grid-template-columns:1fr!important;}
    .print-header{display:block!important;}
}
.print-header{display:none;text-align:center;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #ccc;}
.print-header h2{margin:0 0 4px;font-size:1.2rem;}
.print-header p{margin:0;font-size:.78rem;color:#666;}

@media(max-width:1100px){
    .main-layout{grid-template-columns:1fr;}
    .stat-strip{grid-template-columns:repeat(3,1fr);}
}
@media(max-width:700px){
    main,main.min-main{margin-left:0;}
    .body{padding:10px 10px 40px;}
    .stat-strip{grid-template-columns:repeat(2,1fr);}
    .form-grid{grid-template-columns:1fr;}
    .v-grid-2,.v-grid-3{grid-template-columns:1fr;}
}
</style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main>

<div class="topbar">
    <h1>
        <i class="fas fa-brain" style="color:var(--orange);"></i>
        Modelos <span>MR</span> — Redes Neuronales
    </h1>
    <div class="topbar-right">
        <div class="btn-icon" id="btnRefresh" title="Actualizar"><i class="fas fa-rotate-right"></i></div>
        <a class="btn-icon" href="index.php?c=login&a=Logout" title="Salir" style="color:#ef4444;">
            <i class="fas fa-right-from-bracket"></i>
        </a>
    </div>
</div>

<div class="body">

    <div class="print-header">
        <h2>Reporte — Modelos MR</h2>
        <p>Generado el <span id="printFecha"></span> · Etreva Admin</p>
    </div>

    <!-- STATS -->
    <div class="stat-strip">
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(229,90,43,.14);">
                <i class="fas fa-brain" style="color:var(--orange);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-total">—</div><div class="stat-pill-lbl">Total modelos</div></div>
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
            <div class="stat-pill-ico" style="background:rgba(245,158,11,.12);">
                <i class="fas fa-spinner" style="color:var(--amber);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-entrenando">—</div><div class="stat-pill-lbl">Entrenando</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(99,102,241,.12);">
                <i class="fas fa-map-pin" style="color:var(--indigo);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-alt">—</div><div class="stat-pill-lbl">Alternativas totales</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(45,212,191,.1);">
                <i class="fas fa-database" style="color:var(--teal);"></i>
            </div>
            <div><div class="stat-pill-txt" id="st-ds">—</div><div class="stat-pill-lbl">Registros dataset</div></div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filtros-wrap">
        <input type="text" id="inputBusqueda" placeholder="🔍  Buscar modelo por nombre, versión o descripción…" oninput="debounceSearch()">
        <select id="selEstado" onchange="resetPage();cargar()">
            <option value="">Todos los estados</option>
            <option value="activo">✅ Activos</option>
            <option value="inactivo">❌ Inactivos</option>
            <option value="entrenando">⏳ Entrenando</option>
        </select>
        <button class="btn-filtrar" onclick="resetPage();cargar()"><i class="fas fa-filter"></i> Filtrar</button>
        <div class="export-sep"></div>
        <button class="btn-export btn-excel" onclick="exportarExcel()"><i class="fas fa-file-excel"></i> Excel</button>
        <button class="btn-export btn-pdf"   onclick="imprimirPDF()"><i class="fas fa-file-pdf"></i> PDF</button>
        <div class="export-sep"></div>
        <button class="btn-nuevo" onclick="abrirModalCrear()"><i class="fas fa-plus"></i> Nuevo modelo</button>
    </div>

    <!-- MAIN LAYOUT -->
    <div class="main-layout sin-panel" id="mainLayout">
        <div class="tabla-wrap">
            <table class="adm-table" id="tablaModelos">
                <thead>
                    <tr>
                        <th class="sortable sort-active" data-col="id" onclick="toggleSort('id')"># <i class="fas fa-sort-down" id="ico-id"></i></th>
                        <th class="sortable" data-col="nombre" onclick="toggleSort('nombre')">Nombre / Versión <i class="fas fa-sort" id="ico-nombre"></i></th>
                        <th>Categoría</th>
                        <th>Tipo Modelo</th>
                        <th class="sortable" data-col="estado" onclick="toggleSort('estado')">Estado <i class="fas fa-sort" id="ico-estado"></i></th>
                        <th>Alternativas</th>
                        <th>Dataset</th>
                        <th>Features</th>
                        <th class="sortable" data-col="updated" onclick="toggleSort('updated')">Actualizado <i class="fas fa-sort" id="ico-updated"></i></th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody">
                    <tr><td colspan="10" style="padding:28px;">
                        <div class="skeleton" style="height:18px;"></div>
                    </td></tr>
                </tbody>
            </table>
            <div class="paginacion">
                <span id="pagInfo">Cargando…</span>
                <div class="pag-btns" id="pagBtns"></div>
            </div>
        </div>

        <!-- PANEL LATERAL -->
        <div class="panel-detalle" id="panelDetalle" style="display:none;">
            <div class="panel-header">
                <h3 id="panelTitulo"><i class="fas fa-brain" style="color:var(--orange);"></i> Detalle</h3>
                <div style="display:flex;gap:4px;">
                    <button class="btn-icon" id="btnEditPanel" title="Editar sección" style="display:none;"><i class="fas fa-pen"></i></button>
                    <button class="panel-close" onclick="cerrarPanel()"><i class="fas fa-xmark"></i></button>
                </div>
            </div>
            <div class="panel-tabs">
                <button class="panel-tab active" data-tab="resumen"   onclick="switchTab('resumen')">📊 Resumen</button>
                <button class="panel-tab"        data-tab="mapeo"     onclick="switchTab('mapeo')">🗺️ Mapeo</button>
                <button class="panel-tab"        data-tab="alts"      onclick="switchTab('alts')">📍 Alts</button>
                <button class="panel-tab"        data-tab="dataset"   onclick="switchTab('dataset')">🧠 Dataset</button>
                <button class="panel-tab"        data-tab="stats"     onclick="switchTab('stats')">📈 Stats</button>
            </div>
            <div class="panel-body" id="panelBody">
                <div class="empty-state"><i class="fas fa-brain"></i><p>Seleccioná un modelo</p></div>
            </div>
        </div>
    </div>

</div>
</main>

<!-- ═══════════════════════════════════════════════════
     MODAL PRINCIPAL CREAR / EDITAR MODELO
═══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalForm">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo"><i class="fas fa-plus" style="color:var(--green);"></i> Nuevo Modelo</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="fId">
            <div class="form-grid">
                <div class="form-sep"><i class="fas fa-info-circle"></i> Información básica</div>
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" id="fNombre" placeholder="ej: Trekking La Paz 25 Alternativas">
                </div>
                <div class="form-group">
                    <label>Versión *</label>
                    <input type="text" id="fVersion" placeholder="ej: 1.0.0">
                </div>
                <div class="form-group full">
                    <label>Descripción</label>
                    <textarea id="fDescripcion" placeholder="Descripción del modelo y sus alternativas…"></textarea>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="fEstado">
                        <option value="inactivo">Inactivo</option>
                        <option value="activo">Activo</option>
                        <option value="entrenando">Entrenando</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha de entrenamiento</label>
                    <input type="datetime-local" id="fFechaEntrenamiento">
                </div>
            </div>
            <div class="form-grid" style="margin-top:6px;">
                <div class="form-sep"><i class="fas fa-map-pin"></i> Metadata (configuracion_json)</div>
                <div class="form-group full">
                    <div class="visual-block">
                        <div>
                            <div class="visual-block-label"><i class="fas fa-tag" style="color:var(--purple);"></i> Categoría principal</div>
                            <div class="tipo-chips" id="chips-categoria">
                                <span class="cat-chip" data-val="trekking"    onclick="selCat(this)">🥾 Trekking</span>
                                <span class="cat-chip" data-val="cultural"    onclick="selCat(this)">🏛️ Cultural</span>
                                <span class="cat-chip" data-val="gastronomia" onclick="selCat(this)">🍽️ Gastronomía</span>
                                <span class="cat-chip" data-val="aventura"    onclick="selCat(this)">🧗 Aventura</span>
                                <span class="cat-chip" data-val="naturaleza"  onclick="selCat(this)">🌿 Naturaleza</span>
                                <span class="cat-chip" data-val="historico"   onclick="selCat(this)">🏰 Histórico</span>
                                <span class="cat-chip" data-val="urbano"      onclick="selCat(this)">🏙️ Urbano</span>
                                <span class="cat-chip" data-val="general"     onclick="selCat(this)">⭐ General</span>
                            </div>
                            <input type="text" id="fCategoria" placeholder="O escribí una categoría personalizada…"
                                style="margin-top:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
                                border-radius:9px;padding:7px 11px;color:var(--text);font-size:.81rem;outline:none;
                                transition:border-color .2s;width:100%;"
                                onfocus="this.style.borderColor='var(--orange)'"
                                onblur="this.style.borderColor='rgba(255,255,255,.1)'"
                                oninput="limpiarSelCat();actualizarPreviewConfig()">
                        </div>
                        <div class="v-grid-3">
                            <div class="v-field">
                                <label><i class="fas fa-city" style="color:var(--blue);"></i> Ciudad</label>
                                <input type="text" id="fCiudad" placeholder="ej: La Paz" oninput="actualizarPreviewConfig()">
                            </div>
                            <div class="v-field">
                                <label><i class="fas fa-flag" style="color:var(--amber);"></i> País</label>
                                <input type="text" id="fPais" placeholder="ej: Bolivia" list="lista-paises" oninput="actualizarPreviewConfig()">
                                <datalist id="lista-paises">
                                    <option value="Bolivia"><option value="Perú"><option value="Argentina">
                                    <option value="Chile"><option value="Brasil"><option value="Colombia">
                                </datalist>
                            </div>
                            <div class="v-field">
                                <label><i class="fas fa-calendar" style="color:var(--teal);"></i> Fecha generación</label>
                                <input type="date" id="fFechaGen" oninput="actualizarPreviewConfig()">
                            </div>
                        </div>
                        <div class="v-field">
                            <label><i class="fas fa-map-pin" style="color:var(--orange);"></i> Total alternativas en config</label>
                            <div class="num-stepper">
                                <button type="button" onclick="stepNum('fTotalAltConfig',-1)"><i class="fas fa-minus"></i></button>
                                <input type="number" id="fTotalAltConfig" value="0" min="0" max="500" oninput="actualizarPreviewConfig()">
                                <button type="button" onclick="stepNum('fTotalAltConfig',1)"><i class="fas fa-plus"></i></button>
                                <span style="font-size:.7rem;color:var(--muted);">alternativas definidas</span>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;">
                            <button type="button" class="json-preview-toggle" onclick="togglePreview('previewConfig')">
                                <i class="fas fa-code"></i> Ver configuracion_json
                            </button>
                        </div>
                        <div class="json-preview-box" id="previewConfig"></div>
                    </div>
                </div>
            </div>
            <div class="form-grid" style="margin-top:6px;">
                <div class="form-sep"><i class="fas fa-chart-line"></i> Métricas (metricas_desempeno)</div>
                <div class="form-group full">
                    <div class="visual-block">
                        <div>
                            <div class="visual-block-label"><i class="fas fa-microchip" style="color:var(--blue);"></i> Tipo de modelo</div>
                            <div class="tipo-chips" id="chips-tipo-modelo">
                                <span class="tipo-chip" data-val="recomendacion_brainjs"       onclick="selTipoModelo(this)">🧠 BrainJS</span>
                            </div>
                            <input type="text" id="fTipoModelo" readonly title="Los modelos se entrenan con BrainJS"
                                style="margin-top:7px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);
                                border-radius:9px;padding:7px 11px;color:var(--text);font-size:.81rem;outline:none;
                                transition:border-color .2s;width:100%;"
                                onfocus="this.style.borderColor='var(--orange)'"
                                onblur="this.style.borderColor='rgba(255,255,255,.1)'"
                                oninput="limpiarSelTipo();actualizarPreviewMetricas()">
                        </div>
                        <div class="v-grid-3">
                            <div class="v-field">
                                <label><i class="fas fa-layer-group" style="color:var(--purple);"></i> Features por input</label>
                                <div class="num-stepper">
                                    <button type="button" onclick="stepNum('fFeaturesInput',-1)"><i class="fas fa-minus"></i></button>
                                    <input type="number" id="fFeaturesInput" value="0" min="0" max="9999" oninput="actualizarPreviewMetricas()">
                                    <button type="button" onclick="stepNum('fFeaturesInput',1)"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="v-field">
                                <label><i class="fas fa-map-pin" style="color:var(--orange);"></i> Total alternativas</label>
                                <div class="num-stepper">
                                    <button type="button" onclick="stepNum('fTotalAltMetricas',-1)"><i class="fas fa-minus"></i></button>
                                    <input type="number" id="fTotalAltMetricas" value="0" min="0" max="500" oninput="actualizarPreviewMetricas()">
                                    <button type="button" onclick="stepNum('fTotalAltMetricas',1)"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="v-field">
                                <label><i class="fas fa-repeat" style="color:var(--teal);"></i> Epochs (total_e)</label>
                                <div class="num-stepper">
                                    <button type="button" onclick="stepNum('fTotalEpochs',-1)"><i class="fas fa-minus"></i></button>
                                    <input type="number" id="fTotalEpochs" value="0" min="0" max="999999" oninput="actualizarPreviewMetricas()">
                                    <button type="button" onclick="stepNum('fTotalEpochs',1)"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="visual-block-label"><i class="fas fa-spinner" style="color:var(--amber);"></i> Estado entrenamiento</div>
                            <div class="tipo-chips" id="chips-etrain">
                                <span class="etrain-chip" data-val="pendiente"  onclick="selEtrain(this)">⏳ Pendiente</span>
                                <span class="etrain-chip" data-val="entrenando" onclick="selEtrain(this)">🔄 Entrenando</span>
                                <span class="etrain-chip" data-val="completado" onclick="selEtrain(this)">✅ Completado</span>
                                <span class="etrain-chip" data-val="error"      onclick="selEtrain(this)">❌ Error</span>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:flex-end;">
                            <button type="button" class="json-preview-toggle" onclick="togglePreview('previewMetricas')">
                                <i class="fas fa-code"></i> Ver metricas_desempeno
                            </button>
                        </div>
                        <div class="json-preview-box" id="previewMetricas"></div>
                    </div>
                </div>
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

<!-- ═══════════════════════════════════════════════════
     MODAL ALTERNATIVAS COMPLETO (ver todas / crear / editar)
═══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalAlternativas">
    <div class="modal-box wide">
        <div class="modal-header">
            <h3 id="altModalTitulo"><i class="fas fa-map-pin" style="color:var(--orange);"></i> Alternativas</h3>
            <div style="display:flex;gap:6px;align-items:center;">
                <button class="btn-nuevo" id="btnNuevaAlt" onclick="abrirFormAlt(null)" style="font-size:.72rem;padding:6px 10px;">
                    <i class="fas fa-plus"></i> Nueva alternativa
                </button>
                <button class="modal-close" onclick="cerrarModalAlts()"><i class="fas fa-xmark"></i></button>
            </div>
        </div>
        <div class="modal-body" style="padding:14px 20px;">
            <!-- buscador + info -->
            <div class="alt-modal-search">
                <input type="text" id="altBuscar" placeholder="🔍 Buscar por nombre o subcategoría…" oninput="filtrarAltsModal()">
                <span id="altModalInfo" style="font-size:.7rem;color:var(--muted);white-space:nowrap;">— alternativas</span>
            </div>
            <!-- grid de cards -->
            <div class="alt-modal-grid" id="altModalGrid">
                <div style="text-align:center;padding:30px;color:var(--muted);">
                    <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;"></i>
                </div>
            </div>
            <!-- paginación compacta -->
            <div class="alt-modal-pag" id="altModalPag" style="display:none;"></div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════
     MODAL FORM ALTERNATIVA (crear/editar una)
═══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalFormAlt">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="altFormTitulo"><i class="fas fa-plus" style="color:var(--green);"></i> Nueva Alternativa</h3>
            <button class="modal-close" onclick="cerrarFormAlt()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="afId">
            <input type="hidden" id="afModeloId">
            <div class="form-grid">
                <div class="form-group">
                    <label>ID Atracción *</label>
                    <input type="number" id="afIdAtraccion" placeholder="ej: 7101" min="1">
                </div>
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" id="afNombre" placeholder="ej: Calle Jaén">
                </div>
                <div class="form-group">
                    <label>Subcategoría</label>
                    <input type="text" id="afSubcategoria" placeholder="ej: calle_historica">
                </div>
                <div class="form-group">
                    <label>Dificultad</label>
                    <select id="afDificultad">
                        <option value="">— Sin dificultad —</option>
                        <option value="baja">Baja</option>
                        <option value="media">Media</option>
                        <option value="alta">Alta</option>
                        <option value="muy_alta">Muy alta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Puntuación</label>
                    <input type="number" id="afPuntuacion" placeholder="ej: 4.7" step="0.1" min="0" max="5">
                </div>
                <div class="form-group">
                    <label>Presupuesto (separado por comas)</label>
                    <input type="text" id="afPresupuesto" placeholder="ej: 100-300,300-600">
                </div>
                <div class="form-group full">
                    <label>Descripción</label>
                    <textarea id="afDescripcion" placeholder="Descripción breve de la alternativa…" style="min-height:60px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Índice de output (posición en el modelo)</label>
                    <input type="number" id="afOutputIdx" placeholder="ej: 0" min="0">
                </div>
                <div class="form-group">
                    <label>Duración estimada (min)</label>
                    <input type="number" id="afDuracion" placeholder="ej: 90" min="0">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancelar" onclick="cerrarFormAlt()"><i class="fas fa-xmark"></i> Cancelar</button>
            <button class="btn-guardar" id="btnGuardarAlt" onclick="guardarAlt()">
                <i class="fas fa-floppy-disk"></i> Guardar alternativa
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════
     MODAL MAPEO INPUT
═══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalMapeo">
    <div class="modal-box wide">
        <div class="modal-header">
            <h3 id="mapeoModalTitulo"><i class="fas fa-sitemap" style="color:var(--blue);"></i> Mapeo Input</h3>
            <div style="display:flex;gap:8px;align-items:center;">
                <div class="mode-toggle">
                    <button class="mode-btn active" id="mapeoBtnVer"  onclick="setMapeoMode('ver')"><i class="fas fa-eye"></i> Ver</button>
                    <button class="mode-btn"        id="mapeoBtnEdit" onclick="setMapeoMode('edit')"><i class="fas fa-pen"></i> Editar</button>
                </div>
                <button class="modal-close" onclick="cerrarModalMapeo()"><i class="fas fa-xmark"></i></button>
            </div>
        </div>
        <div class="modal-body" style="padding:14px 20px;">
            <div id="mapeoModalContent">
                <div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="color:var(--orange);font-size:1.5rem;"></i></div>
            </div>
        </div>
        <div class="modal-footer" id="mapeoModalFooter" style="display:none;">
            <button class="btn-cancelar" onclick="setMapeoMode('ver')"><i class="fas fa-xmark"></i> Cancelar</button>
            <button class="btn-guardar" id="btnGuardarMapeo" onclick="guardarMapeoModal()">
                <i class="fas fa-floppy-disk"></i> Guardar cambios
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════
     MODAL DATASET
═══════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalDataset">
    <div class="modal-box wide">
        <div class="modal-header">
            <h3 id="dsModalTitulo"><i class="fas fa-database" style="color:var(--teal);"></i> Dataset de entrenamiento</h3>
            <div style="display:flex;gap:8px;align-items:center;">
                <div class="mode-toggle">
                    <button class="mode-btn active" id="dsBtnVer"  onclick="setDsMode('ver')"><i class="fas fa-eye"></i> Ver</button>
                    <button class="mode-btn"        id="dsBtnEdit" onclick="setDsMode('edit')"><i class="fas fa-pen"></i> Editar</button>
                </div>
                <button class="modal-close" onclick="cerrarModalDataset()"><i class="fas fa-xmark"></i></button>
            </div>
        </div>
        <div class="modal-body" style="padding:14px 20px;">
            <div id="dsModalContent">
                <div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="color:var(--teal);font-size:1.5rem;"></i></div>
            </div>
        </div>
        <div class="modal-footer" id="dsModalFooter" style="display:none;">
            <button class="btn-cancelar" onclick="setDsMode('ver')"><i class="fas fa-xmark"></i> Cancelar</button>
            <button class="btn-guardar" id="btnGuardarDs" onclick="guardarDatasetModal()">
                <i class="fas fa-floppy-disk"></i> Guardar dataset
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ═══════════════════════════════════════════════════════════════════
    //  ESTADO GLOBAL
    // ═══════════════════════════════════════════════════════════════════
    const pag      = { page: 1, total: 0, pages: 1 };
    let sortCol    = 'id', sortOrd = 'desc';
    let _dTimer    = null;
    let _modeloSel = null;
    let _tabActiva = 'resumen';
    let _pagAlt    = 1, _pagDs = 1;
    let _editMode  = false;

    // Estado modal alternativas
    let _altsPagina = 1, _altsTotal = 0, _altsPaginas = 1;
    let _altsCache  = [];   // cache de todas las alts cargadas
    let _altsBuscar = '';

    document.getElementById('printFecha').textContent =
        new Date().toLocaleString('es-BO', { dateStyle:'long', timeStyle:'short' });

    const btnEditPanel = document.getElementById('btnEditPanel');

    // ═══════════════════════════════════════════════════════════════════
    //  HELPERS GLOBALES
    // ═══════════════════════════════════════════════════════════════════
    window.debounceSearch = function() {
        clearTimeout(_dTimer);
        _dTimer = setTimeout(() => { resetPage(); cargar(); }, 380);
    };
    window.resetPage = function() { pag.page = 1; };

    window.toggleSort = function(col) {
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
    };

    window.estadoBadge = function(e) {
        const m = { activo:'b-activo', inactivo:'b-inactivo', entrenando:'b-entrenando' };
        const i = { activo:'fa-circle-check', inactivo:'fa-circle-xmark', entrenando:'fa-spinner fa-spin' };
        return `<span class="badge ${m[e]||'b-inactivo'}"><i class="fas ${i[e]||'fa-circle-xmark'}"></i> ${e||'inactivo'}</span>`;
    };
    window.catBadge = function(c) {
        if (!c) return '<span class="badge b-cat">—</span>';
        return `<span class="badge b-cat"><i class="fas fa-tag"></i> ${c}</span>`;
    };
    window.diffBadge = function(d) {
        if (!d) return '—';
        return `<span class="diff-badge diff-${d}">${d}</span>`;
    };
    window.esc = function(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')
                            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    };

    // ═══════════════════════════════════════════════════════════════════
    //  TABLA PRINCIPAL
    // ═══════════════════════════════════════════════════════════════════
    window.cargar = function() {
        const q  = encodeURIComponent(document.getElementById('inputBusqueda').value.trim());
        const st = document.getElementById('selEstado').value;
        document.getElementById('tbody').innerHTML =
            '<tr><td colspan="10" style="padding:26px;"><div class="skeleton" style="height:18px;"></div></td></tr>';
        fetch(`index.php?c=adminmodelosmr&a=Listar&q=${q}&estado=${st}&sort=${sortCol}&orden=${sortOrd}&page=${pag.page}`)
            .then(r => r.json())
            .then(d => {
                if (!d.success) { renderVacia(d.message || 'Error'); return; }
                pag.total = d.total; pag.pages = d.paginas;
                renderTabla(d.datos); renderPag();
            })
            .catch(() => renderVacia('Error de conexión'));
    };

    function renderTabla(items) {
        if (!items.length) { renderVacia('Sin modelos con esos filtros'); return; }
        const rows = items.map(r => {
            const updated = (r.updated_at || '').slice(0, 16).replace('T',' ');
            const altPct  = Math.min(100, Math.round((+r.cant_alternativas / 30) * 100));
            const btnEstado = r.estado === 'activo'
                ? `<button class="btn-sm btn-inactivo" onclick="cambiarEstado(${r.id},'inactivo')" title="Desactivar"><i class="fas fa-ban"></i></button>`
                : `<button class="btn-sm btn-activo"  onclick="cambiarEstado(${r.id},'activo')"   title="Activar"><i class="fas fa-circle-check"></i></button>`;
            const btnTrain = r.estado !== 'entrenando'
                ? `<button class="btn-sm btn-train" onclick="cambiarEstado(${r.id},'entrenando')" title="Marcar entrenando"><i class="fas fa-spinner"></i></button>` : '';
            return `<tr onclick="seleccionarModelo(${r.id})" id="row-${r.id}">
                <td style="color:var(--muted);font-size:.68rem;">${r.id}</td>
                <td>
                    <div style="font-weight:700;font-size:.82rem;color:#fff;">${esc(r.nombre)}</div>
                    <div style="font-size:.64rem;color:var(--muted);">v${esc(r.version)}</div>
                </td>
                <td>${catBadge(r.categoria_principal)}</td>
                <td>
                    <div style="font-size:.72rem;color:var(--blue);">${esc(r.tipo_modelo||'—')}</div>
                    <div style="font-size:.62rem;color:var(--muted);">${esc(r.estado_entrenamiento||'—')}</div>
                </td>
                <td>${estadoBadge(r.estado)}</td>
                <td>
                    <div class="mini-bar-wrap">
                        <div class="mini-bar-lbl">
                            <span>${r.cant_alternativas||0}</span>
                            <span style="color:var(--orange);">${r.total_alt_config||0} cfg</span>
                        </div>
                        <div class="mini-bar"><div class="mini-bar-fill" style="width:${altPct}%;"></div></div>
                    </div>
                </td>
                <td>
                    <span style="font-size:.78rem;color:var(--teal);font-weight:700;">${r.cant_dataset||0}</span>
                    <span style="font-size:.62rem;color:var(--muted);"> regs</span>
                </td>
                <td style="font-size:.75rem;color:var(--purple);font-weight:700;">${r.features_por_input||'—'}</td>
                <td style="font-size:.68rem;color:var(--muted);">${updated}</td>
                <td onclick="event.stopPropagation();">
                    <div class="acciones">
                        <button class="btn-sm btn-view"    onclick="seleccionarModelo(${r.id})"  title="Ver detalle"><i class="fas fa-eye"></i></button>
                        <button class="btn-sm btn-edit"    onclick="abrirModalEditar(${r.id})"   title="Editar"><i class="fas fa-pen"></i></button>
                        <button class="btn-sm btn-csv"     onclick="AdminCSV.exportarModelo(${r.id})" title="Exportar este modelo completo (JSON)"><i class="fas fa-file-code"></i></button>
                        <button class="btn-sm btn-csv-imp" onclick="AdminCSV.importarModelo(${r.id}, ${JSON.stringify(r.nombre||'').replace(/"/g,'&quot;')})" title="Importar JSON sobre este modelo"><i class="fas fa-file-import"></i></button>
                        ${btnEstado}
                        ${btnTrain}
                    </div>
                </td>
            </tr>`;
        }).join('');
        document.getElementById('tbody').innerHTML = rows;
        if (_modeloSel) {
            const row = document.getElementById(`row-${_modeloSel}`);
            if (row) row.classList.add('row-selected');
        }
    }

    function renderVacia(msg) {
        document.getElementById('tbody').innerHTML =
            `<tr><td colspan="10"><div class="empty-state"><i class="fas fa-brain"></i><p>${msg}</p></div></td></tr>`;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PAGINACIÓN TABLA
    // ═══════════════════════════════════════════════════════════════════
    function renderPag() {
        const { page, total, pages } = pag;
        document.getElementById('pagInfo').textContent = `Página ${page} de ${pages} · ${total} modelos`;
        let b = `<button class="pag-btn" onclick="irPag(${page-1})" ${page<=1?'disabled':''}>&lsaquo;</button>`;
        const s = Math.max(1,page-2), e = Math.min(pages,page+2);
        for (let i=s;i<=e;i++) b += `<button class="pag-btn ${i===page?'active':''}" onclick="irPag(${i})">${i}</button>`;
        b += `<button class="pag-btn" onclick="irPag(${page+1})" ${page>=pages?'disabled':''}>&rsaquo;</button>`;
        document.getElementById('pagBtns').innerHTML = b;
    }
    window.irPag = function(p) { if(p<1||p>pag.pages) return; pag.page=p; cargar(); };

    // ═══════════════════════════════════════════════════════════════════
    //  STATS
    // ═══════════════════════════════════════════════════════════════════
    function cargarStats() {
        fetch('index.php?c=adminmodelosmr&a=Stats')
            .then(r => r.json())
            .then(d => {
                if (!d.success) return;
                const s = d.stats;
                document.getElementById('st-total').textContent      = s.total                   || 0;
                document.getElementById('st-activos').textContent    = s.activos                 || 0;
                document.getElementById('st-inactivos').textContent  = s.inactivos               || 0;
                document.getElementById('st-entrenando').textContent = s.entrenando              || 0;
                document.getElementById('st-alt').textContent        = s.total_alternativas      || 0;
                document.getElementById('st-ds').textContent         = s.total_registros_dataset || 0;
            }).catch(()=>{});
    }

    // ═══════════════════════════════════════════════════════════════════
    //  HELPER PAGINACIÓN COMPACTA (para panel y modales)
    // ═══════════════════════════════════════════════════════════════════
    function pagCompacta(page, pages, total, callbackName) {
        if (pages <= 1) return '';
        // Mostramos prev / "Pág X de Y" / next + salto directo
        return `<div class="pag-compact">
            <div class="pag-compact-btns">
                <button class="pag-btn" onclick="${callbackName}(${page-1})" ${page<=1?'disabled':''}>&lsaquo;</button>
                ${pagesChunk(page, pages, callbackName)}
                <button class="pag-btn" onclick="${callbackName}(${page+1})" ${page>=pages?'disabled':''}>&rsaquo;</button>
            </div>
            <span class="pag-compact-info">${page}/${pages} · ${total}</span>
        </div>`;
    }

    function pagesChunk(page, pages, cb) {
        // Mostrar máx 5 números alrededor de la página actual
        const s = Math.max(1, page-2), e = Math.min(pages, page+2);
        let out = '';
        if (s > 1) out += `<button class="pag-btn" onclick="${cb}(1)">1</button>${s>2?'<span style="color:var(--muted);font-size:.65rem;">…</span>':''}`;
        for (let i=s;i<=e;i++) out += `<button class="pag-btn ${i===page?'active':''}" onclick="${cb}(${i})">${i}</button>`;
        if (e < pages) out += `${e<pages-1?'<span style="color:var(--muted);font-size:.65rem;">…</span>':''}<button class="pag-btn" onclick="${cb}(${pages})">${pages}</button>`;
        return out;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PANEL LATERAL
    // ═══════════════════════════════════════════════════════════════════
    window.seleccionarModelo = function(id) {
        document.querySelectorAll('.adm-table tbody tr').forEach(r => r.classList.remove('row-selected'));
        const row = document.getElementById(`row-${id}`);
        if (row) row.classList.add('row-selected');
        _modeloSel = id; _pagAlt = 1; _pagDs = 1;
        _editMode = false;
        btnEditPanel.style.display = 'none';
        btnEditPanel.innerHTML = '<i class="fas fa-pen"></i>';
        document.getElementById('panelDetalle').style.display = 'flex';
        document.getElementById('mainLayout').classList.remove('sin-panel');
        switchTab(_tabActiva, true);
    };

    window.cerrarPanel = function() {
        _modeloSel = null;
        document.getElementById('panelDetalle').style.display = 'none';
        document.getElementById('mainLayout').classList.add('sin-panel');
        document.querySelectorAll('.adm-table tbody tr').forEach(r => r.classList.remove('row-selected'));
    };

    window.switchTab = function(tab, force = false) {
        if (_tabActiva === tab && !force) return;
        _tabActiva = tab;
        document.querySelectorAll('.panel-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
        if (!_modeloSel) return;

        if (tab === 'mapeo' || tab === 'dataset') {
            btnEditPanel.style.display = 'none'; // ya no usamos el botón del panel
            _editMode = false;
            btnEditPanel.innerHTML = '<i class="fas fa-pen"></i>';
        } else {
            btnEditPanel.style.display = 'none';
            _editMode = false;
            btnEditPanel.innerHTML = '<i class="fas fa-pen"></i>';
        }

        const body = document.getElementById('panelBody');
        body.innerHTML = '<div style="text-align:center;padding:30px;"><i class="fas fa-spinner fa-spin" style="color:var(--orange);font-size:1.5rem;"></i></div>';
        switch(tab) {
            case 'resumen': cargarResumen();    break;
            case 'mapeo':   cargarMapeo();      break;
            case 'alts':    cargarAlts();       break;
            case 'dataset': cargarDataset();    break;
            case 'stats':   cargarStatsModelo();break;
        }
    };

    // ─── EDITAR EN PANEL ────────────────────────────────────────────────
    btnEditPanel.addEventListener('click', function() {
        if (!_modeloSel) return;
        if (_tabActiva === 'mapeo') {
            if (_editMode) {
                _editMode = false;
                btnEditPanel.innerHTML = '<i class="fas fa-pen"></i>';
                cargarMapeo();
            } else {
                _editMode = true;
                btnEditPanel.innerHTML = '<i class="fas fa-times" style="color:var(--red);"></i>';
                cargarMapeoEditor();
            }
        } else if (_tabActiva === 'dataset') {
            if (_editMode) {
                _editMode = false;
                btnEditPanel.innerHTML = '<i class="fas fa-pen"></i>';
                cargarDataset();
            } else {
                _editMode = true;
                btnEditPanel.innerHTML = '<i class="fas fa-times" style="color:var(--red);"></i>';
                cargarDatasetEditor();
            }
        }
    });

    // ═══════════════════════════════════════════════════════════════════
    //  PANEL — RESUMEN
    // ═══════════════════════════════════════════════════════════════════
    function cargarResumen() {
        fetch(`index.php?c=adminmodelosmr&a=Resumen&id=${_modeloSel}`)
            .then(r => r.json())
            .then(d => {
                if (!d.success) { panelError(d.message); return; }
                const m = d.modelo, mt = m.metricas||{}, md = m.metadata||{}, ic = m.indices_clasificacion||{};
                document.getElementById('panelTitulo').innerHTML =
                    `<i class="fas fa-brain" style="color:var(--orange);"></i> ${esc(m.nombre)}`;
                let html = `
                <div>
                    <div class="sec-title"><i class="fas fa-info-circle"></i> General</div>
                    <div class="kv-row"><span class="kv-key">ID</span><span class="kv-val">#${m.id}</span></div>
                    <div class="kv-row"><span class="kv-key">Nombre</span><span class="kv-val">${esc(m.nombre)}</span></div>
                    <div class="kv-row"><span class="kv-key">Versión</span><span class="kv-val">v${esc(m.version)}</span></div>
                    <div class="kv-row"><span class="kv-key">Estado</span><span class="kv-val">${estadoBadge(m.estado)}</span></div>
                    <div class="kv-row"><span class="kv-key">Descripción</span><span class="kv-val" style="font-size:.7rem;font-weight:400;">${esc(m.descripcion||'—')}</span></div>
                    <div class="kv-row"><span class="kv-key">Entrenado</span><span class="kv-val">${(m.fecha_entrenamiento||'—').slice(0,10)}</span></div>
                    <div class="kv-row"><span class="kv-key">Actualizado</span><span class="kv-val">${(m.updated_at||'—').slice(0,16).replace('T',' ')}</span></div>
                </div>
                <div>
                    <div class="sec-title"><i class="fas fa-cog"></i> Métricas</div>
                    <div class="kv-row"><span class="kv-key">Tipo</span><span class="kv-val" style="color:var(--blue);">${esc(mt.tipo_modelo||'—')}</span></div>
                    <div class="kv-row"><span class="kv-key">Features/input</span><span class="kv-val" style="color:var(--purple);">${mt.features_por_input||'—'}</span></div>
                    <div class="kv-row"><span class="kv-key">Total epochs</span><span class="kv-val">${mt.total_e||'—'}</span></div>
                    <div class="kv-row"><span class="kv-key">Alternativas</span><span class="kv-val" style="color:var(--orange);">${mt.total_alternativas||m.cant_alternativas||'—'}</span></div>
                    <div class="kv-row"><span class="kv-key">Estado train</span><span class="kv-val" style="color:var(--green);">${esc(mt.estado_entrenamiento||'—')}</span></div>
                    <div class="kv-row"><span class="kv-key">Dataset rows</span><span class="kv-val" style="color:var(--teal);">${m.cant_dataset||0}</span></div>
                </div>
                <div>
                    <div class="sec-title"><i class="fas fa-map-pin"></i> Metadata</div>
                    <div class="kv-row"><span class="kv-key">Categoría</span><span class="kv-val">${catBadge(md.categoria_principal||md.categoria)}</span></div>
                    <div class="kv-row"><span class="kv-key">Ciudad</span><span class="kv-val">${esc(md.ciudad||'—')}</span></div>
                    <div class="kv-row"><span class="kv-key">País</span><span class="kv-val">${esc(md.pais||'—')}</span></div>
                    <div class="kv-row"><span class="kv-key">Fecha config</span><span class="kv-val">${esc(md.fecha_generacion||'—')}</span></div>
                    <div class="kv-row"><span class="kv-key">Total alt. config</span><span class="kv-val">${esc(String(md.total_alternativas||'—'))}</span></div>
                </div>`;
                const clases = md.clasificaciones||{};
                if (Object.keys(clases).length) {
                    html += `<div><div class="sec-title"><i class="fas fa-layer-group"></i> Clasificaciones</div>`;
                    Object.entries(clases).forEach(([k,v]) => {
                        html += `<div class="kv-row">
                            <span class="kv-key" style="font-size:.68rem;">${k}</span>
                            <span class="kv-val"><span style="color:var(--orange);">${v.cantidad||0}</span>
                            <span style="font-size:.62rem;color:var(--muted);margin-left:4px;">${v.rango_ids||''}</span></span></div>`;
                    });
                    html += `</div>`;
                }
                // Botón acceso rápido alternativas
                html += `<div style="padding-top:4px;">
                    <button class="btn-nuevo" style="width:100%;justify-content:center;" onclick="abrirModalAlternativas()">
                        <i class="fas fa-map-pin"></i> Ver / Gestionar Alternativas
                    </button>
                </div>`;
                document.getElementById('panelBody').innerHTML = html;
            })
            .catch(() => panelError('Error de conexión'));
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PANEL — MAPEO (resumen + botón abrir modal)
    // ═══════════════════════════════════════════════════════════════════
    function cargarMapeo() {
        fetch(`index.php?c=adminmodelosmr&a=MapeoInput&id=${_modeloSel}`)
            .then(r => r.json())
            .then(d => {
                if (!d.success) { panelError(d.message); return; }
                const mapeo = d.mapeo_input||{};
                const keys  = Object.keys(mapeo);
                if (!keys.length) {
                    document.getElementById('panelBody').innerHTML = `
                        <div class="empty-state"><i class="fas fa-map"></i><p>Sin mapeo_input</p></div>
                        <button class="btn-nuevo" style="width:100%;justify-content:center;" onclick="abrirModalMapeo()">
                            <i class="fas fa-plus"></i> Configurar mapeo
                        </button>`;
                    return;
                }
                const colores = {rango_edad:'var(--blue)',compania:'var(--green)',motivo_viaje:'var(--orange)',
                    presupuesto:'var(--amber)',duracion:'var(--teal)',actividades:'var(--purple)',experiencias:'var(--pink)'};

                // Resumen compacto en el panel
                let html = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:.68rem;color:var(--muted);">${keys.length} campos mapeados</span>
                    <button class="btn-nuevo" style="font-size:.68rem;padding:5px 10px;" onclick="abrirModalMapeo()">
                        <i class="fas fa-expand"></i> Ver / Editar
                    </button>
                </div>`;
                keys.forEach(k => {
                    const g = mapeo[k], vals = g.valores||[], idxs = g.indices||[];
                    const c = colores[k]||'var(--muted)';
                    html += `<div style="margin-bottom:8px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                            <span style="font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:${c};">
                                <i class="fas fa-tag" style="font-size:.55rem;"></i> ${k}
                            </span>
                            <span style="font-size:.58rem;color:var(--muted);">${vals.length} vals · [${idxs[0]??''}–${idxs[idxs.length-1]??''}]</span>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:3px;">`;
                    vals.slice(0,6).forEach((v,i) => {
                        html += `<span style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
                            border-radius:14px;padding:2px 7px;font-size:.6rem;color:var(--text);">
                            <span style="color:${c};font-weight:700;font-size:.55rem;">[${idxs[i]??'?'}]</span> ${esc(String(v))}
                        </span>`;
                    });
                    if (vals.length > 6) html += `<span style="font-size:.6rem;color:var(--muted);padding:2px 4px;">+${vals.length-6} más</span>`;
                    html += `</div></div>`;
                });
                document.getElementById('panelBody').innerHTML = html;
            })
            .catch(() => panelError('Error de conexión'));
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PANEL — MAPEO EDITOR
    // ═══════════════════════════════════════════════════════════════════
    function cargarMapeoEditor() {
        fetch(`index.php?c=adminmodelosmr&a=MapeoInput&id=${_modeloSel}`)
            .then(r => r.json())
            .then(d => {
                if (!d.success) { panelError(d.message); return; }
                const mapeo = d.mapeo_input||{};
                let html = '<div id="formMapeoWrap">';
                for (let [cat, datos] of Object.entries(mapeo)) {
                    html += `<div class="mapeo-group" data-cat="${esc(cat)}" style="margin-bottom:12px;">
                        <div class="mapeo-group-title" style="margin-bottom:6px;">
                            <span style="font-weight:700;color:var(--orange);">${esc(cat)}</span>
                            <button type="button" class="btn-sm btn-activo" onclick="agregarValorMapeo('${esc(cat)}')">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>`;
                    datos.valores.forEach((val, idx) => {
                        html += mapeoFila(val, datos.indices[idx]);
                    });
                    html += `</div>`;
                }
                html += `</div>
                <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
                    <button type="button" class="btn-guardar" onclick="guardarMapeo()">
                        <i class="fas fa-floppy-disk"></i> Guardar cambios
                    </button>
                    <button type="button" class="btn-cancelar" onclick="cancelarEdicion()">Cancelar</button>
                </div>`;
                document.getElementById('panelBody').innerHTML = html;
            })
            .catch(() => panelError('Error de conexión'));
    }

    function mapeoFila(val, idx) {
        return `<div class="mapeo-row" style="display:flex;gap:4px;margin-bottom:4px;">
            <input type="text" class="mapeo-valor" value="${esc(String(val||''))}" placeholder="valor"
                style="flex:1;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:4px 8px;color:var(--text);">
            <input type="number" class="mapeo-indice" value="${idx??0}" min="0" max="999"
                style="width:70px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:4px;color:var(--text);">
            <button type="button" class="btn-sm btn-inactivo" onclick="this.closest('.mapeo-row').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>`;
    }

    window.agregarValorMapeo = function(cat) {
        const group = document.querySelector(`[data-cat="${cat}"]`);
        if (!group) return;
        const div = document.createElement('div');
        div.innerHTML = mapeoFila('', 0);
        group.insertBefore(div.firstElementChild, group.querySelector('div[style*="flex"]') || null);
        group.appendChild(div.firstElementChild || div);
    };

    window.guardarMapeo = function() {
        const grupos = document.querySelectorAll('#formMapeoWrap .mapeo-group');
        let nuevoMapeo = {};
        grupos.forEach(g => {
            const cat   = g.dataset.cat;
            const filas = g.querySelectorAll('.mapeo-row');
            let valores = [], indices = [];
            filas.forEach(f => {
                const v = f.querySelector('.mapeo-valor').value.trim();
                const i = parseInt(f.querySelector('.mapeo-indice').value);
                if (v !== '') { valores.push(v); indices.push(isNaN(i)?0:i); }
            });
            if (valores.length) nuevoMapeo[cat] = { valores, indices };
        });
        fetch('index.php?c=adminmodelosmr&a=Actualizar', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id:_modeloSel, metricas_desempeno:{ mapeo_input: nuevoMapeo } })
        })
        .then(r=>r.json())
        .then(d => {
            if (d.success) {
                Swal.fire({title:'¡Guardado!',text:d.message,icon:'success',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});
                cancelarEdicion(); cargarMapeo();
            } else {
                Swal.fire({title:'Error',text:d.message,icon:'error',background:'#1c1c32',color:'#f0f0f5'});
            }
        })
        .catch(()=>Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'}));
    };

    function cancelarEdicion() {
        _editMode = false;
        btnEditPanel.innerHTML = '<i class="fas fa-pen"></i>';
    }

    // ═══════════════════════════════════════════════════════════════════
    //  PANEL — ALTERNATIVAS (MINI vista + botón abrir modal completo)
    // ═══════════════════════════════════════════════════════════════════
    window.cargarAlts = function(page) {          // ← WINDOW: llamado desde onclick dinámico
        if (page === undefined) page = _pagAlt;
        _pagAlt = page;
        fetch(`index.php?c=adminmodelosmr&a=Alternativas&id=${_modeloSel}&page=${page}&per_page=8`)
            .then(r => r.json())
            .then(d => {
                if (!d.success) { panelError(d.message); return; }
                const alts = d.datos||[];
                if (!alts.length) {
                    document.getElementById('panelBody').innerHTML =
                        `<div class="empty-state"><i class="fas fa-map-pin"></i><p>Sin alternativas</p></div>
                         <div style="padding-top:8px;">
                             <button class="btn-nuevo" style="width:100%;justify-content:center;" onclick="abrirModalAlternativas()">
                                 <i class="fas fa-plus"></i> Agregar alternativas
                             </button>
                         </div>`;
                    return;
                }
                let html = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:.68rem;color:var(--muted);">${d.total} alternativas</span>
                    <button class="btn-nuevo" style="font-size:.68rem;padding:5px 10px;" onclick="abrirModalAlternativas()">
                        <i class="fas fa-expand"></i> Ver todas
                    </button>
                </div>
                <table class="alt-table">
                    <thead><tr><th>ID</th><th>Nombre</th><th>Dif.</th><th>Pts</th></tr></thead>
                    <tbody>`;
                alts.forEach(a => {
                    html += `<tr>
                        <td style="color:var(--muted);font-size:.62rem;">${a.id||'—'}</td>
                        <td><div style="font-weight:600;font-size:.72rem;">${esc(a.nombre||'—')}</div>
                            <div style="font-size:.6rem;color:var(--muted);">${esc(a.subcategoria||'')}</div></td>
                        <td>${diffBadge(a.dificultad)}</td>
                        <td style="color:var(--orange);font-weight:700;">${a.puntuacion||'—'}</td>
                    </tr>`;
                });
                html += `</tbody></table>`;
                html += pagCompacta(d.pagina, d.paginas, d.total, 'cargarAlts');
                document.getElementById('panelBody').innerHTML = html;
            })
            .catch(() => panelError('Error de conexión'));
    };

    // ═══════════════════════════════════════════════════════════════════
    //  PANEL — DATASET (resumen + botón abrir modal)
    // ═══════════════════════════════════════════════════════════════════
    window.cargarDataset = function(page) {
        if (page === undefined) page = _pagDs;
        _pagDs = page;
        fetch(`index.php?c=adminmodelosmr&a=Dataset&id=${_modeloSel}&page=1&per_page=5`)
            .then(r => r.json())
            .then(d => {
                if (!d.success) { panelError(d.message); return; }
                const rows = d.datos||[];
                if (!rows.length) {
                    document.getElementById('panelBody').innerHTML = `
                        <div class="empty-state"><i class="fas fa-database"></i><p>Sin registros de dataset</p></div>
                        <button class="btn-nuevo" style="width:100%;justify-content:center;margin-top:8px;" onclick="abrirModalDataset()">
                            <i class="fas fa-plus"></i> Agregar registros
                        </button>`;
                    return;
                }
                let html = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <div>
                        <span style="font-size:.9rem;font-weight:900;color:var(--teal);">${d.total}</span>
                        <span style="font-size:.68rem;color:var(--muted);margin-left:4px;">registros de entrenamiento</span>
                    </div>
                    <button class="btn-nuevo" style="font-size:.68rem;padding:5px 10px;" onclick="abrirModalDataset()">
                        <i class="fas fa-expand"></i> Ver / Editar
                    </button>
                </div>
                <div style="font-size:.62rem;color:var(--muted);margin-bottom:8px;">
                    <i class="fas fa-circle-info" style="color:var(--orange);"></i>
                    Muestra de los primeros 5 registros:
                </div>`;
                rows.forEach(row => {
                    const input  = row.input||[];
                    const output = Array.isArray(row.output) ? row.output[0] : row.output;
                    const activos = input.filter(b=>b===1||b==='1').length;
                    html += `<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05);
                        border-radius:8px;padding:7px 10px;margin-bottom:5px;display:flex;align-items:center;gap:10px;">
                        <span style="background:rgba(167,139,250,.15);color:var(--purple);font-size:.72rem;font-weight:800;
                            border-radius:7px;padding:2px 8px;border:1px solid rgba(167,139,250,.25);white-space:nowrap;">
                            → #${output}
                        </span>
                        <div style="flex:1;">
                            <div style="display:flex;flex-wrap:wrap;gap:1px;">`;
                    input.slice(0,30).forEach(b => {
                        html += `<div style="width:10px;height:10px;border-radius:2px;
                            background:${b?'rgba(229,90,43,.5)':'rgba(255,255,255,.05)'};"></div>`;
                    });
                    if (input.length > 30) html += `<span style="font-size:.55rem;color:var(--muted);margin-left:2px;">+${input.length-30}</span>`;
                    html += `</div>
                            <div style="font-size:.58rem;color:var(--muted);margin-top:2px;">${input.length} features · ${activos} activos</div>
                        </div>
                    </div>`;
                });
                if (d.total > 5) {
                    html += `<div style="text-align:center;padding:6px 0;">
                        <span style="font-size:.64rem;color:var(--muted);">y ${d.total - 5} registros más…</span>
                    </div>`;
                }
                document.getElementById('panelBody').innerHTML = html;
            })
            .catch(() => panelError('Error de conexión'));
    };

    // ═══════════════════════════════════════════════════════════════════
    //  PANEL — DATASET EDITOR (legacy - ahora usa modal)
    // ═══════════════════════════════════════════════════════════════════
    window.cargarDatasetEditor = function(page) {
        // Redirigir al modal
        abrirModalDataset();
        setDsMode('edit');
    };

    // ═══════════════════════════════════════════════════════════════════
    //  PANEL — STATS MODELO
    // ═══════════════════════════════════════════════════════════════════
    window.cargarStatsModelo = function() {       // ← WINDOW por consistencia
        fetch(`index.php?c=adminmodelosmr&a=StatsModelo&id=${_modeloSel}`)
            .then(r=>r.json())
            .then(d => {
                if (!d.success) { panelError(d.message); return; }
                const s = d.stats;
                const score  = s.score_promedio ? (+s.score_promedio*100).toFixed(1)+'%' : '—';
                const grupos = Array.isArray(s.grupos_lista) ? s.grupos_lista.join(', ') : (s.grupos_lista||'—');
                const tipos  = Array.isArray(s.tipos_lista)  ? s.tipos_lista.join(', ')  : (s.tipos_lista ||'—');
                document.getElementById('panelBody').innerHTML = `
                <div>
                    <div class="sec-title"><i class="fas fa-star"></i> Recomendaciones</div>
                    <div class="stats-grid-panel">
                        <div class="stat-mini"><div class="stat-mini-num" style="color:var(--orange);">${s.total_recomendaciones||0}</div><div class="stat-mini-lbl">Total generadas</div></div>
                        <div class="stat-mini"><div class="stat-mini-num" style="color:var(--teal);">${score}</div><div class="stat-mini-lbl">Score promedio</div></div>
                        <div class="stat-mini"><div class="stat-mini-num" style="color:var(--green);">${s.feedback_positivos||0}</div><div class="stat-mini-lbl">Feedback +</div></div>
                        <div class="stat-mini"><div class="stat-mini-num" style="color:var(--red);">${s.feedback_negativos||0}</div><div class="stat-mini-lbl">Feedback -</div></div>
                    </div>
                    <div class="kv-row" style="margin-top:8px;"><span class="kv-key">Atracciones distintas</span><span class="kv-val">${s.atracciones_distintas||0}</span></div>
                </div>
                <div>
                    <div class="sec-title"><i class="fas fa-sliders"></i> Parámetros</div>
                    <div class="kv-row"><span class="kv-key">Total</span><span class="kv-val">${s.total_parametros||0}</span></div>
                    <div class="kv-row"><span class="kv-key">Activos</span><span class="kv-val" style="color:var(--green);">${s.parametros_activos||0}</span></div>
                    <div class="kv-row"><span class="kv-key">Grupos</span><span class="kv-val" style="font-size:.62rem;color:var(--muted);">${esc(grupos)}</span></div>
                </div>
                <div>
                    <div class="sec-title"><i class="fas fa-link"></i> Contextos</div>
                    <div class="kv-row"><span class="kv-key">Total</span><span class="kv-val">${s.total_contextos||0}</span></div>
                    <div class="kv-row"><span class="kv-key">Activos</span><span class="kv-val" style="color:var(--green);">${s.contextos_activos||0}</span></div>
                    <div class="kv-row"><span class="kv-key">Tipos</span><span class="kv-val" style="font-size:.62rem;color:var(--muted);">${esc(tipos)}</span></div>
                </div>`;
            })
            .catch(()=>panelError('Error de conexión'));
    };

    function panelError(msg) {
        document.getElementById('panelBody').innerHTML =
            `<div class="empty-state"><i class="fas fa-triangle-exclamation"></i><p>${esc(msg)}</p></div>`;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  MODAL ALTERNATIVAS — COMPLETO
    // ═══════════════════════════════════════════════════════════════════
    window.abrirModalAlternativas = function() {
        if (!_modeloSel) return;
        document.getElementById('altModalTitulo').innerHTML =
            `<i class="fas fa-map-pin" style="color:var(--orange);"></i> Alternativas — Modelo #${_modeloSel}`;
        document.getElementById('altBuscar').value = '';
        _altsBuscar = '';
        document.getElementById('modalAlternativas').classList.add('show');
        cargarAltsModal(1);
    };

    window.cerrarModalAlts = function() {
        document.getElementById('modalAlternativas').classList.remove('show');
        // recargar panel si estaba en pestaña alts
        if (_tabActiva === 'alts') cargarAlts();
    };

    document.getElementById('modalAlternativas').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalAlts();
    });

    window.cargarAltsModal = function(page) {     // ← WINDOW: onclick dinámico
        _altsPagina = page||1;
        const body = document.getElementById('altModalGrid');
        body.innerHTML = '<div style="text-align:center;padding:30px;"><i class="fas fa-spinner fa-spin" style="color:var(--orange);font-size:1.5rem;"></i></div>';
        fetch(`index.php?c=adminmodelosmr&a=Alternativas&id=${_modeloSel}&page=${_altsPagina}&per_page=30`)
            .then(r=>r.json())
            .then(d => {
                if (!d.success) {
                    body.innerHTML = `<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>${esc(d.message)}</p></div>`;
                    return;
                }
                _altsCache   = d.datos||[];
                _altsTotal   = d.total;
                _altsPaginas = d.paginas;
                document.getElementById('altModalInfo').textContent = `${d.total} alternativas`;
                renderAltsModalGrid(_altsCache);
                renderAltsModalPag(d.pagina, d.paginas, d.total);
            })
            .catch(()=>{
                body.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error de conexión</p></div>';
            });
    };

    function renderAltsModalGrid(alts) {
        const q = _altsBuscar.toLowerCase();
        const filtradas = q ? alts.filter(a => (a.nombre||'').toLowerCase().includes(q) || (a.subcategoria||'').toLowerCase().includes(q)) : alts;
        const body = document.getElementById('altModalGrid');
        if (!filtradas.length) {
            body.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-search"></i><p>Sin resultados</p></div>';
            return;
        }
        body.innerHTML = filtradas.map(a => {
            const pres = Array.isArray(a.presupuesto) ? a.presupuesto.join(', ') : (a.presupuesto||'—');
            return `<div class="alt-card">
                <div class="alt-card-actions">
                    <button class="btn-sm btn-edit" onclick="abrirFormAlt(${JSON.stringify(a).replace(/"/g,'&quot;')})" title="Editar"><i class="fas fa-pen"></i></button>
                    <button class="btn-sm btn-inactivo" onclick="eliminarAlt(${a.id||'null'},'${esc(a.nombre||'')}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                </div>
                <div class="alt-card-id">#${a.id||'—'}</div>
                <div class="alt-card-nombre">${esc(a.nombre||'—')}</div>
                <div class="alt-card-sub">${esc(a.subcategoria||'')}</div>
                <div class="alt-card-row">
                    ${diffBadge(a.dificultad)}
                    <span style="font-size:.6rem;color:var(--amber);margin-left:4px;">${pres}</span>
                </div>
                <div class="alt-card-row">
                    <span class="alt-card-pts">${a.puntuacion||'—'}</span>
                    <span style="font-size:.6rem;color:var(--muted);margin-left:4px;">pts</span>
                </div>
            </div>`;
        }).join('');
    }

    function renderAltsModalPag(page, pages, total) {
        const pag = document.getElementById('altModalPag');
        if (pages <= 1) { pag.style.display='none'; return; }
        pag.style.display = 'flex';
        pag.innerHTML = `
            <span style="font-size:.72rem;color:var(--muted);">${total} alternativas en total</span>
            <div style="display:flex;gap:4px;align-items:center;">
                <button class="pag-btn" onclick="cargarAltsModal(${page-1})" ${page<=1?'disabled':''}>&lsaquo;</button>
                ${pagesChunk(page, pages, 'cargarAltsModal')}
                <button class="pag-btn" onclick="cargarAltsModal(${page+1})" ${page>=pages?'disabled':''}>&rsaquo;</button>
                <span style="font-size:.68rem;color:var(--muted);margin-left:4px;">${page}/${pages}</span>
            </div>`;
    }

    window.filtrarAltsModal = function() {
        _altsBuscar = document.getElementById('altBuscar').value.trim();
        renderAltsModalGrid(_altsCache);
    };

    // ═══════════════════════════════════════════════════════════════════
    //  MODAL FORM ALTERNATIVA
    // ═══════════════════════════════════════════════════════════════════
    window.abrirFormAlt = function(altData) {
        document.getElementById('afModeloId').value = _modeloSel;
        if (!altData) {
            // Crear nueva
            document.getElementById('altFormTitulo').innerHTML =
                '<i class="fas fa-plus" style="color:var(--green);"></i> Nueva Alternativa';
            ['afId','afIdAtraccion','afNombre','afSubcategoria','afDescripcion','afOutputIdx','afDuracion'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            document.getElementById('afDificultad').value  = '';
            document.getElementById('afPuntuacion').value  = '';
            document.getElementById('afPresupuesto').value = '';
        } else {
            // Editar existente
            const a = typeof altData === 'string' ? JSON.parse(altData) : altData;
            document.getElementById('altFormTitulo').innerHTML =
                `<i class="fas fa-pen" style="color:var(--orange);"></i> Editar: ${esc(a.nombre||'')}`;
            document.getElementById('afId').value          = a.id||'';
            document.getElementById('afIdAtraccion').value = a.id_atraccion||a.id||'';
            document.getElementById('afNombre').value      = a.nombre||'';
            document.getElementById('afSubcategoria').value= a.subcategoria||'';
            document.getElementById('afDificultad').value  = a.dificultad||'';
            document.getElementById('afPuntuacion').value  = a.puntuacion||'';
            const pres = Array.isArray(a.presupuesto) ? a.presupuesto.join(',') : (a.presupuesto||'');
            document.getElementById('afPresupuesto').value = pres;
            document.getElementById('afDescripcion').value = a.descripcion||'';
            document.getElementById('afOutputIdx').value   = a.output_idx??'';
            document.getElementById('afDuracion').value    = a.duracion||'';
        }
        document.getElementById('modalFormAlt').classList.add('show');
    };

    window.cerrarFormAlt = function() {
        document.getElementById('modalFormAlt').classList.remove('show');
    };

    document.getElementById('modalFormAlt').addEventListener('click', function(e) {
        if (e.target === this) cerrarFormAlt();
    });

    window.guardarAlt = function() {
        const id         = document.getElementById('afId').value;
        const modeloId   = document.getElementById('afModeloId').value;
        const nombre     = document.getElementById('afNombre').value.trim();
        const idAtr      = document.getElementById('afIdAtraccion').value.trim();
        const sub        = document.getElementById('afSubcategoria').value.trim();
        const dif        = document.getElementById('afDificultad').value;
        const pts        = document.getElementById('afPuntuacion').value;
        const presStr    = document.getElementById('afPresupuesto').value.trim();
        const desc       = document.getElementById('afDescripcion').value.trim();
        const outIdx     = document.getElementById('afOutputIdx').value;
        const dur        = document.getElementById('afDuracion').value;

        if (!nombre) {
            Swal.fire({title:'Requerido',text:'El nombre es obligatorio.',icon:'warning',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});
            return;
        }

        const presupuesto = presStr ? presStr.split(',').map(p=>p.trim()).filter(Boolean) : [];
        const payload = {
            modelo_id: parseInt(modeloId),
            nombre, subcategoria: sub, dificultad: dif||null,
            puntuacion: pts ? parseFloat(pts) : null,
            presupuesto, descripcion: desc,
            output_idx: outIdx !== '' ? parseInt(outIdx) : null,
            duracion: dur ? parseInt(dur) : null,
        };
        if (id) payload.id = parseInt(id);
        if (idAtr) payload.id_atraccion = parseInt(idAtr);

        const accion = id ? 'ActualizarAlternativa' : 'CrearAlternativa';
        const btn = document.getElementById('btnGuardarAlt');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';

        fetch(`index.php?c=adminmodelosmr&a=${accion}`, {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify(payload),
        })
        .then(r=>r.json())
        .then(d => {
            btn.disabled=false; btn.innerHTML='<i class="fas fa-floppy-disk"></i> Guardar alternativa';
            if (d.success) {
                Swal.fire({title:id?'¡Actualizado!':'¡Creado!',text:d.message,icon:'success',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});
                cerrarFormAlt();
                cargarAltsModal(_altsPagina); // refrescar el modal de alternativas
                cargarStats();
            } else {
                Swal.fire({title:'Error',text:d.message,icon:'error',background:'#1c1c32',color:'#f0f0f5'});
            }
        })
        .catch(()=>{
            btn.disabled=false; btn.innerHTML='<i class="fas fa-floppy-disk"></i> Guardar alternativa';
            Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'});
        });
    };

    window.eliminarAlt = function(id, nombre) {
        if (!id) { Swal.fire({title:'Error',text:'ID no disponible para eliminar.',icon:'error',background:'#1c1c32',color:'#f0f0f5'}); return; }
        Swal.fire({
            title:`¿Eliminar "${nombre}"?`,
            text:'Esta acción no se puede deshacer.',
            icon:'warning', background:'#1c1c32', color:'#f0f0f5',
            confirmButtonColor:'#ef4444', cancelButtonColor:'rgba(255,255,255,.08)',
            confirmButtonText:'<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText:'Cancelar', showCancelButton:true,
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch('index.php?c=adminmodelosmr&a=EliminarAlternativa', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({ id }),
            })
            .then(r=>r.json())
            .then(d => {
                Swal.fire({title:d.success?'Eliminado':'Error',text:d.message,
                    icon:d.success?'success':'error',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});
                if (d.success) { cargarAltsModal(_altsPagina); cargarStats(); }
            })
            .catch(()=>Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'}));
        });
    };

    // ═══════════════════════════════════════════════════════════════════
    //  MODAL MAPEO INPUT
    // ═══════════════════════════════════════════════════════════════════
    let _mapeoData    = {};
    let _mapeoMode    = 'ver'; // 'ver' | 'edit'

    const MAPEO_COLORES = {
        rango_edad:'var(--blue)', compania:'var(--green)', motivo_viaje:'var(--orange)',
        presupuesto:'var(--amber)', duracion:'var(--teal)', actividades:'var(--purple)',
        experiencias:'var(--pink)'
    };

    window.abrirModalMapeo = function() {
        if (!_modeloSel) return;
        document.getElementById('modalMapeo').classList.add('show');
        _mapeoMode = 'ver';
        actualizarMapeoModeUI();
        cargarMapeoModal();
    };

    window.cerrarModalMapeo = function() {
        document.getElementById('modalMapeo').classList.remove('show');
    };
    document.getElementById('modalMapeo').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalMapeo();
    });

    function cargarMapeoModal() {
        const content = document.getElementById('mapeoModalContent');
        content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="color:var(--blue);font-size:1.5rem;"></i></div>';
        fetch(`index.php?c=adminmodelosmr&a=MapeoInput&id=${_modeloSel}`)
            .then(r=>r.json())
            .then(d=>{
                if (!d.success) { content.innerHTML=`<div class="empty-state"><p>${esc(d.message)}</p></div>`; return; }
                _mapeoData = d.mapeo_input||{};
                // Actualizar título con nombre del modelo
                const nombre = document.getElementById('panelTitulo')?.textContent?.replace(/[^a-zA-Z0-9 áéíóúñÁÉÍÓÚÑ#]/g,'').trim();
                document.getElementById('mapeoModalTitulo').innerHTML =
                    `<i class="fas fa-sitemap" style="color:var(--blue);"></i> Mapeo Input <span style="font-size:.72rem;color:var(--muted);margin-left:6px;">Modelo #${_modeloSel}</span>`;
                renderMapeoContent();
            })
            .catch(()=>{ content.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error de conexión</p></div>'; });
    }

    window.setMapeoMode = function(mode) {
        _mapeoMode = mode;
        actualizarMapeoModeUI();
        renderMapeoContent();
    };

    function actualizarMapeoModeUI() {
        document.getElementById('mapeoBtnVer' ).classList.toggle('active', _mapeoMode==='ver');
        document.getElementById('mapeoBtnEdit').classList.toggle('active', _mapeoMode==='edit');
        document.getElementById('mapeoModalFooter').style.display = _mapeoMode==='edit' ? 'flex' : 'none';
    }

    function renderMapeoContent() {
        const content = document.getElementById('mapeoModalContent');
        const keys = Object.keys(_mapeoData);
        if (!keys.length && _mapeoMode === 'ver') {
            content.innerHTML = `<div class="empty-state"><i class="fas fa-map"></i><p>Sin mapeo_input configurado</p>
                <button class="btn-nuevo" style="margin:10px auto;display:inline-flex;" onclick="setMapeoMode('edit')">
                    <i class="fas fa-plus"></i> Configurar ahora
                </button></div>`;
            return;
        }
        if (_mapeoMode === 'ver') {
            renderMapeoVer(keys);
        } else {
            renderMapeoEdit(keys);
        }
    }

    function renderMapeoVer(keys) {
        const content = document.getElementById('mapeoModalContent');
        // Calcular totales
        let totalVals = 0, minIdx = Infinity, maxIdx = -Infinity;
        keys.forEach(k => {
            const g = _mapeoData[k];
            totalVals += (g.valores||[]).length;
            (g.indices||[]).forEach(i => { if(i<minIdx)minIdx=i; if(i>maxIdx)maxIdx=i; });
        });

        let html = `
        <div style="font-size:.68rem;color:var(--muted);margin-bottom:12px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-circle-info" style="color:var(--orange);"></i>
            Índices del vector input &bull;
            <span style="color:var(--text);">${keys.length} campos</span> &bull;
            <span style="color:var(--text);">${totalVals} valores totales</span> &bull;
            Rango índices: <span style="color:var(--orange);">[${minIdx===Infinity?'—':minIdx}–${maxIdx===-Infinity?'—':maxIdx}]</span>
        </div>
        <div class="mapeo-modal-body">`;

        keys.forEach(k => {
            const g = _mapeoData[k], vals = g.valores||[], idxs = g.indices||[];
            const c = MAPEO_COLORES[k]||'var(--muted)';
            const rMin = idxs[0]??'?', rMax = idxs[idxs.length-1]??'?';
            html += `<div class="mapeo-section">
                <div class="mapeo-section-header">
                    <div class="mapeo-section-title" style="color:${c};">
                        <i class="fas fa-tag" style="font-size:.7rem;"></i>
                        ${k.toUpperCase()}
                    </div>
                    <span class="mapeo-section-meta">${vals.length} VALS &middot; IDX [${rMin}–${rMax}]</span>
                </div>
                <div class="mapeo-chips-row">`;
            vals.forEach((v,i) => {
                html += `<div class="mapeo-chip-big">
                    <span class="idx">[${idxs[i]??'?'}]</span>
                    <span>${esc(String(v))}</span>
                </div>`;
            });
            html += `</div></div>`;
        });
        html += `</div>`;
        content.innerHTML = html;
    }

    function renderMapeoEdit(keys) {
        const content = document.getElementById('mapeoModalContent');
        // Si no hay keys, ofrecer campos predefinidos
        const defaultCampos = ['compania','duracion','rango_edad','actividades','presupuesto','experiencias','motivo_viaje'];
        const camposEditar  = keys.length ? keys : defaultCampos;

        let html = `
        <div style="font-size:.68rem;color:var(--muted);margin-bottom:12px;">
            <i class="fas fa-pencil" style="color:var(--orange);"></i>
            Editá los valores e índices de cada campo del vector input.
        </div>
        <div class="mapeo-modal-body" id="mapeoEditWrap">`;

        camposEditar.forEach(k => {
            const g    = _mapeoData[k]||{ valores:[], indices:[] };
            const vals = g.valores||[], idxs = g.indices||[];
            const c    = MAPEO_COLORES[k]||'var(--muted)';
            html += `<div class="mapeo-edit-group" data-cat="${esc(k)}">
                <div class="mapeo-edit-group-title">
                    <span style="color:${c};"><i class="fas fa-tag" style="font-size:.55rem;"></i> ${k.toUpperCase()}</span>
                    <button class="btn-sm btn-activo" style="font-size:.62rem;padding:3px 8px;"
                        onclick="mapeoAgregarFila('${esc(k)}')"><i class="fas fa-plus"></i> Agregar valor</button>
                </div>
                <div style="display:grid;grid-template-columns:auto 28px;gap:3px;margin-bottom:4px;padding:0 2px;">
                    <div style="display:grid;grid-template-columns:1fr 72px;gap:5px;font-size:.58rem;color:var(--muted);padding:0 2px;">
                        <span>VALOR</span><span>ÍNDICE</span>
                    </div>
                    <span></span>
                </div>`;
            if (!vals.length) {
                html += `<div class="mapeo-edit-row" data-row>
                    <input class="mapeo-edit-input mapeo-valor" placeholder="ej: solo" value="">
                    <input class="mapeo-edit-idx mapeo-indice" type="number" placeholder="0" value="0" min="0" max="999">
                    <button class="btn-sm btn-inactivo" style="padding:3px 6px;" onclick="this.closest('[data-row]').remove()"><i class="fas fa-times"></i></button>
                </div>`;
            } else {
                vals.forEach((v,i) => {
                    html += `<div class="mapeo-edit-row" data-row>
                        <input class="mapeo-edit-input mapeo-valor" value="${esc(String(v))}" placeholder="valor">
                        <input class="mapeo-edit-idx mapeo-indice" type="number" value="${idxs[i]??0}" min="0" max="999">
                        <button class="btn-sm btn-inactivo" style="padding:3px 6px;" onclick="this.closest('[data-row]').remove()"><i class="fas fa-times"></i></button>
                    </div>`;
                });
            }
            html += `</div>`;
        });
        html += `</div>`;
        content.innerHTML = html;
    }

    window.mapeoAgregarFila = function(cat) {
        const group = document.querySelector(`#mapeoEditWrap [data-cat="${cat}"]`);
        if (!group) return;
        const div = document.createElement('div');
        div.innerHTML = `<div class="mapeo-edit-row" data-row>
            <input class="mapeo-edit-input mapeo-valor" placeholder="ej: valor" value="">
            <input class="mapeo-edit-idx mapeo-indice" type="number" placeholder="0" value="0" min="0" max="999">
            <button class="btn-sm btn-inactivo" style="padding:3px 6px;" onclick="this.closest('[data-row]').remove()"><i class="fas fa-times"></i></button>
        </div>`;
        group.appendChild(div.firstElementChild);
    };

    window.guardarMapeoModal = function() {
        const grupos = document.querySelectorAll('#mapeoEditWrap .mapeo-edit-group');
        let nuevoMapeo = {};
        grupos.forEach(g => {
            const cat  = g.dataset.cat;
            const rows = g.querySelectorAll('[data-row]');
            let valores=[], indices=[];
            rows.forEach(r => {
                const v = r.querySelector('.mapeo-valor').value.trim();
                const i = parseInt(r.querySelector('.mapeo-indice').value);
                if (v !== '') { valores.push(v); indices.push(isNaN(i)?0:i); }
            });
            if (valores.length) nuevoMapeo[cat] = { valores, indices };
        });

        const btn = document.getElementById('btnGuardarMapeo');
        btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Guardando…';

        fetch('index.php?c=adminmodelosmr&a=Actualizar', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id:_modeloSel, metricas_desempeno:{ mapeo_input: nuevoMapeo } })
        })
        .then(r=>r.json())
        .then(d => {
            btn.disabled=false; btn.innerHTML='<i class="fas fa-floppy-disk"></i> Guardar cambios';
            if (d.success) {
                Swal.fire({title:'¡Guardado!',text:d.message,icon:'success',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b',timer:1800,showConfirmButton:false});
                _mapeoData = nuevoMapeo;
                setMapeoMode('ver');
                cargarMapeo(); // actualizar panel
            } else {
                Swal.fire({title:'Error',text:d.message,icon:'error',background:'#1c1c32',color:'#f0f0f5'});
            }
        })
        .catch(()=>{
            btn.disabled=false; btn.innerHTML='<i class="fas fa-floppy-disk"></i> Guardar cambios';
            Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'});
        });
    };

    // ═══════════════════════════════════════════════════════════════════
    //  MODAL DATASET
    // ═══════════════════════════════════════════════════════════════════
    let _dsData    = [];
    let _dsMeta    = {};
    let _dsMode    = 'ver';
    let _dsPag     = 1;
    let _dsPags    = 1;
    let _dsTotal   = 0;

    window.abrirModalDataset = function() {
        if (!_modeloSel) return;
        document.getElementById('modalDataset').classList.add('show');
        _dsMode = 'ver';
        _dsPag  = 1;
        actualizarDsModeUI();
        cargarDsModal(1);
    };

    window.cerrarModalDataset = function() {
        document.getElementById('modalDataset').classList.remove('show');
    };
    document.getElementById('modalDataset').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalDataset();
    });

    function cargarDsModal(page) {
        _dsPag = page||1;
        const content = document.getElementById('dsModalContent');
        content.innerHTML = '<div style="text-align:center;padding:40px;"><i class="fas fa-spinner fa-spin" style="color:var(--teal);font-size:1.5rem;"></i></div>';
        fetch(`index.php?c=adminmodelosmr&a=Dataset&id=${_modeloSel}&page=${_dsPag}&per_page=20`)
            .then(r=>r.json())
            .then(d=>{
                if (!d.success) { content.innerHTML=`<div class="empty-state"><p>${esc(d.message)}</p></div>`; return; }
                _dsData  = d.datos||[];
                _dsMeta  = { pagina:d.pagina, paginas:d.paginas, total:d.total };
                _dsPags  = d.paginas;
                _dsTotal = d.total;
                document.getElementById('dsModalTitulo').innerHTML =
                    `<i class="fas fa-database" style="color:var(--teal);"></i> Dataset <span style="font-size:.72rem;color:var(--muted);margin-left:6px;">Modelo #${_modeloSel} &bull; ${d.total} registros</span>`;
                renderDsContent();
            })
            .catch(()=>{ content.innerHTML='<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error de conexión</p></div>'; });
    }

    window.setDsMode = function(mode) {
        _dsMode = mode;
        actualizarDsModeUI();
        if (mode === 'edit') {
            renderDsEdit();
        } else {
            renderDsVer();
        }
    };

    function actualizarDsModeUI() {
        document.getElementById('dsBtnVer' ).classList.toggle('active', _dsMode==='ver');
        document.getElementById('dsBtnEdit').classList.toggle('active', _dsMode==='edit');
        document.getElementById('dsModalFooter').style.display = _dsMode==='edit' ? 'flex' : 'none';
    }

    function renderDsContent() {
        if (_dsMode==='ver') renderDsVer();
        else renderDsEdit();
    }

    function renderDsVer() {
        const content = document.getElementById('dsModalContent');
        if (!_dsData.length) {
            content.innerHTML = `<div class="empty-state">
                <i class="fas fa-database"></i><p>Sin registros de dataset</p>
                <button class="btn-nuevo" style="margin:10px auto;display:inline-flex;" onclick="setDsMode('edit')">
                    <i class="fas fa-plus"></i> Agregar registros
                </button></div>`;
            return;
        }
        const features = _dsData[0]?.input?.length||0;
        let html = `
        <div class="ds-modal-toolbar">
            <div class="ds-modal-info">
                <span style="color:var(--teal);font-weight:700;">${_dsTotal}</span> registros &bull;
                <span style="color:var(--muted);">${features} features por registro</span> &bull;
                pág ${_dsPag}/${_dsPags}
            </div>
            <div class="ds-modal-legend">
                <div style="width:12px;height:12px;border-radius:2px;background:rgba(229,90,43,.5);"></div>
                <span style="color:var(--muted);">= bit activo (1)</span>
                <div style="width:12px;height:12px;border-radius:2px;background:rgba(255,255,255,.05);margin-left:8px;"></div>
                <span style="color:var(--muted);">= inactivo (0)</span>
            </div>
        </div>
        <div class="ds-view-scroll">`;

        _dsData.forEach((row, rowIdx) => {
            const input  = row.input||[];
            const output = Array.isArray(row.output) ? row.output[0] : row.output;
            const activos = input.filter(b=>b===1||b==='1'||b===true).length;
            const num = (_dsPag-1)*20 + rowIdx + 1;
            const bits = input.map((b,idx) =>
                `<div class="ds-bit-v ${b?'on':'off'}" title="[${idx}]: ${b}">${b?'1':'0'}</div>`
            ).join('');
            html += `<div class="ds-view-card">
                <div class="ds-view-card-header">
                    <span style="font-size:.6rem;color:var(--muted);">#${num}</span>
                    <div class="ds-view-output">→ Alt #${output}</div>
                    <span class="ds-view-feats">${input.length} features &bull; ${activos} activos</span>
                </div>
                <div class="ds-bits-grid">${bits}</div>
            </div>`;
        });
        html += `</div>`;

        // Paginación
        if (_dsPags > 1) {
            html += `<div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:10px;border-top:1px solid rgba(255,255,255,.06);">
                <span style="font-size:.7rem;color:var(--muted);">${_dsTotal} registros en total</span>
                <div style="display:flex;gap:4px;align-items:center;">
                    <button class="pag-btn" onclick="cargarDsModalPag(${_dsPag-1})" ${_dsPag<=1?'disabled':''}>&lsaquo;</button>
                    ${pagesChunk(_dsPag, _dsPags, 'cargarDsModalPag')}
                    <button class="pag-btn" onclick="cargarDsModalPag(${_dsPag+1})" ${_dsPag>=_dsPags?'disabled':''}>&rsaquo;</button>
                    <span style="font-size:.68rem;color:var(--muted);margin-left:4px;">${_dsPag}/${_dsPags}</span>
                </div>
            </div>`;
        }
        content.innerHTML = html;
    }

    window.cargarDsModalPag = function(page) {
        if (page < 1 || page > _dsPags) return;
        cargarDsModal(page);
    };

    function renderDsEdit() {
        const content = document.getElementById('dsModalContent');
        let html = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
            <span style="font-size:.68rem;color:var(--muted);">
                <i class="fas fa-circle-info" style="color:var(--orange);"></i>
                Output = índice de alternativa &bull; Input = array de bits separados por comas
            </span>
            <button class="btn-nuevo" style="font-size:.68rem;padding:5px 10px;" onclick="dsAgregarFila()">
                <i class="fas fa-plus"></i> Nueva fila
            </button>
        </div>
        <div class="ds-edit-scroll">
            <table class="ds-edit-table">
                <thead>
                    <tr>
                        <th style="width:26px;">#</th>
                        <th style="width:90px;">Output (Alt #)</th>
                        <th>Input (bits separados por coma)</th>
                        <th style="width:36px;"></th>
                    </tr>
                </thead>
                <tbody id="dsEditTbody">`;

        if (_dsData.length) {
            _dsData.forEach((row, i) => {
                const out = Array.isArray(row.output) ? row.output[0] : row.output;
                const inp = (row.input||[]).join(',');
                const num = (_dsPag-1)*20 + i + 1;
                html += dsEditFila(num, out, inp);
            });
        } else {
            html += dsEditFila(1, 0, '');
        }
        html += `</tbody></table>
        </div>`;

        if (_dsPags > 1) {
            html += `<div style="margin-top:8px;padding:6px 0;border-top:1px solid rgba(255,255,255,.06);">
                <span style="font-size:.64rem;color:var(--amber);"><i class="fas fa-triangle-exclamation"></i>
                Editando pág ${_dsPag}/${_dsPags}. Guardar reemplaza solo los registros de esta página.
                Para editar otra página, cancela, navega y vuelve a modo editar.</span>
            </div>`;
        }
        content.innerHTML = html;
    }

    function dsEditFila(num, out, inp) {
        return `<tr>
            <td style="color:var(--muted);font-size:.6rem;">${num}</td>
            <td><input class="ds-edit-inp ds-edit-out" type="number" value="${esc(String(out??0))}" placeholder="0" min="0"></td>
            <td><input class="ds-edit-inp ds-edit-in" value="${esc(inp||'')}" placeholder="0,1,0,1,0…"></td>
            <td><button class="btn-sm btn-inactivo" style="padding:3px 6px;" onclick="this.closest('tr').remove()"><i class="fas fa-times"></i></button></td>
        </tr>`;
    }

    window.dsAgregarFila = function() {
        const tbody = document.getElementById('dsEditTbody');
        if (!tbody) return;
        const count = tbody.querySelectorAll('tr').length + 1 + (_dsPag-1)*20;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td style="color:var(--muted);font-size:.6rem;">${count}</td>
            <td><input class="ds-edit-inp ds-edit-out" type="number" value="0" placeholder="0" min="0"></td>
            <td><input class="ds-edit-inp ds-edit-in" value="" placeholder="0,1,0,1,0…"></td>
            <td><button class="btn-sm btn-inactivo" style="padding:3px 6px;" onclick="this.closest('tr').remove()"><i class="fas fa-times"></i></button></td>`;
        tbody.appendChild(tr);
        tr.scrollIntoView({ behavior:'smooth', block:'nearest' });
    };

    window.guardarDatasetModal = function() {
        const filas = document.querySelectorAll('#dsEditTbody tr');
        let dataset = [];
        filas.forEach(f => {
            const outEl = f.querySelector('.ds-edit-out');
            const inpEl = f.querySelector('.ds-edit-in');
            if (!outEl || !inpEl) return;
            const out = parseInt(outEl.value);
            const inpStr = inpEl.value.trim();
            if (inpStr !== '' && !isNaN(out)) {
                const inputArr = inpStr.split(',').map(v=>parseInt(v.trim())).filter(v=>!isNaN(v));
                if (inputArr.length) dataset.push({ input: inputArr, output: [out] });
            }
        });

        if (!dataset.length) {
            Swal.fire({title:'Sin datos',text:'Agrega al menos un registro válido.',icon:'warning',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});
            return;
        }

        const btn = document.getElementById('btnGuardarDs');
        btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Guardando…';

        fetch('index.php?c=adminmodelosmr&a=Actualizar', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ id:_modeloSel, metricas_desempeno:{ dataset_entrenamiento: dataset } })
        })
        .then(r=>r.json())
        .then(d=>{
            btn.disabled=false; btn.innerHTML='<i class="fas fa-floppy-disk"></i> Guardar dataset';
            if (d.success) {
                Swal.fire({title:'¡Guardado!',text:d.message,icon:'success',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b',timer:1800,showConfirmButton:false});
                setDsMode('ver');
                cargarDsModal(_dsPag);
                cargarDataset(); // actualizar panel
                cargarStats();
            } else {
                Swal.fire({title:'Error',text:d.message,icon:'error',background:'#1c1c32',color:'#f0f0f5'});
            }
        })
        .catch(()=>{
            btn.disabled=false; btn.innerHTML='<i class="fas fa-floppy-disk"></i> Guardar dataset';
            Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'});
        });
    };

    // ═══════════════════════════════════════════════════════════════════
    //  MODAL CREAR / EDITAR MODELO (helpers visuales)
    // ═══════════════════════════════════════════════════════════════════
    window.selCat = function(el) {
        document.querySelectorAll('#chips-categoria .cat-chip').forEach(c=>c.classList.remove('sel-cat'));
        el.classList.add('sel-cat');
        document.getElementById('fCategoria').value = el.dataset.val;
        actualizarPreviewConfig();
    };
    window.limpiarSelCat = function() {
        document.querySelectorAll('#chips-categoria .cat-chip').forEach(c=>c.classList.remove('sel-cat'));
    };
    window.selTipoModelo = function(el) {
        document.querySelectorAll('#chips-tipo-modelo .tipo-chip').forEach(c=>c.classList.remove('sel-tipo'));
        el.classList.add('sel-tipo');
        document.getElementById('fTipoModelo').value = el.dataset.val;
        actualizarPreviewMetricas();
    };
    window.limpiarSelTipo = function() {
        document.querySelectorAll('#chips-tipo-modelo .tipo-chip').forEach(c=>c.classList.remove('sel-tipo'));
    };
    window.selEtrain = function(el) {
        document.querySelectorAll('#chips-etrain .etrain-chip').forEach(c=>{
            c.classList.remove('sel-pendiente','sel-entrenando','sel-completado','sel-error');
        });
        el.classList.add('sel-'+el.dataset.val);
        actualizarPreviewMetricas();
    };
    window.stepNum = function(id, delta) {
        const el = document.getElementById(id);
        if (!el) return;
        el.value = Math.max(parseInt(el.min||'0'), parseInt(el.value||'0') + delta);
        actualizarPreviewConfig(); actualizarPreviewMetricas();
    };
    window.buildConfigJson = function() {
        const cat=document.getElementById('fCategoria').value.trim();
        const city=document.getElementById('fCiudad').value.trim();
        const pais=document.getElementById('fPais').value.trim();
        const fgen=document.getElementById('fFechaGen').value;
        const total=parseInt(document.getElementById('fTotalAltConfig').value||'0');
        const md={};
        if(cat)   md.categoria_principal=cat;
        if(city)  md.ciudad=city;
        if(pais)  md.pais=pais;
        if(fgen)  md.fecha_generacion=fgen;
        if(total) md.total_alternativas=total;
        return {metadata:md};
    };
    window.buildMetricasJson = function() {
        const tipo=document.getElementById('fTipoModelo').value.trim();
        const feat=parseInt(document.getElementById('fFeaturesInput').value||'0');
        const alt=parseInt(document.getElementById('fTotalAltMetricas').value||'0');
        const ep=parseInt(document.getElementById('fTotalEpochs').value||'0');
        const etChip=document.querySelector('#chips-etrain .etrain-chip[class*="sel-"]');
        const et=etChip?etChip.dataset.val:'';
        const obj={};
        if(tipo) obj.tipo_modelo=tipo;
        if(feat) obj.features_por_input=feat;
        if(alt)  obj.total_alternativas=alt;
        if(ep)   obj.total_e=ep;
        if(et)   obj.estado_entrenamiento=et;
        return obj;
    };
    window.actualizarPreviewConfig = function() {
        const box=document.getElementById('previewConfig');
        if(box.classList.contains('show')) box.textContent=JSON.stringify(buildConfigJson(),null,2);
    };
    window.actualizarPreviewMetricas = function() {
        const box=document.getElementById('previewMetricas');
        if(box.classList.contains('show')) box.textContent=JSON.stringify(buildMetricasJson(),null,2);
    };
    window.togglePreview = function(id) {
        const box=document.getElementById(id);
        box.classList.toggle('show');
        if(box.classList.contains('show')){
            if(id==='previewConfig')   box.textContent=JSON.stringify(buildConfigJson(),null,2);
            if(id==='previewMetricas') box.textContent=JSON.stringify(buildMetricasJson(),null,2);
        }
    };
    window.resetCamposVisuales = function() {
        document.querySelectorAll('#chips-categoria .cat-chip').forEach(c=>c.classList.remove('sel-cat'));
        document.getElementById('fCategoria').value='';
        ['fCiudad','fPais','fFechaGen'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
        ['fTotalAltConfig','fFeaturesInput','fTotalAltMetricas','fTotalEpochs'].forEach(id=>{const el=document.getElementById(id);if(el)el.value=0;});
        document.querySelectorAll('#chips-tipo-modelo .tipo-chip').forEach(c=>c.classList.remove('sel-tipo'));
        document.getElementById('fTipoModelo').value='recomendacion_brainjs';
        document.querySelectorAll('#chips-etrain .etrain-chip').forEach(c=>{
            c.classList.remove('sel-pendiente','sel-entrenando','sel-completado','sel-error');
        });
        document.getElementById('previewConfig').classList.remove('show');
        document.getElementById('previewMetricas').classList.remove('show');
    };
    window.cargarConfigDesdeJson = function(cj) {
        const md=(cj&&cj.metadata)?cj.metadata:(cj||{});
        const cat=md.categoria_principal||md.categoria||'';
        if(cat){
            const chip=document.querySelector(`#chips-categoria .cat-chip[data-val="${cat}"]`);
            if(chip) chip.classList.add('sel-cat');
            document.getElementById('fCategoria').value=cat;
        }
        document.getElementById('fCiudad').value        =md.ciudad||'';
        document.getElementById('fPais').value          =md.pais||'';
        document.getElementById('fFechaGen').value      =(md.fecha_generacion||'').slice(0,10);
        document.getElementById('fTotalAltConfig').value=md.total_alternativas||0;
    };
    window.cargarMetricasDesdeJson = function(mt) {
        if(!mt||typeof mt!=='object') return;
        const tipo=mt.tipo_modelo||'';
        if(tipo){
            const chip=document.querySelector(`#chips-tipo-modelo .tipo-chip[data-val="${tipo}"]`);
            if(chip) selTipoModelo(chip);
            else document.getElementById('fTipoModelo').value=tipo;
        }
        document.getElementById('fFeaturesInput').value   =mt.features_por_input||0;
        document.getElementById('fTotalAltMetricas').value=mt.total_alternativas||0;
        document.getElementById('fTotalEpochs').value     =mt.total_e||0;
        const et=mt.estado_entrenamiento||'';
        if(et){
            const chip=document.querySelector(`#chips-etrain .etrain-chip[data-val="${et}"]`);
            if(chip) selEtrain(chip);
        }
    };

    // ═══════════════════════════════════════════════════════════════════
    //  MODAL CREAR / EDITAR MODELO
    // ═══════════════════════════════════════════════════════════════════
    window.abrirModalCrear = function() {
        document.getElementById('modalTitulo').innerHTML =
            '<i class="fas fa-plus" style="color:var(--green);"></i> Nuevo Modelo MR';
        ['fId','fNombre','fDescripcion','fFechaEntrenamiento'].forEach(id=>document.getElementById(id).value='');
        document.getElementById('fVersion').value = '1.0.0';
        document.getElementById('fEstado').value  = 'inactivo';
        resetCamposVisuales();
        document.getElementById('modalForm').classList.add('show');
    };

    window.abrirModalEditar = function(id) {
        fetch(`index.php?c=adminmodelosmr&a=Resumen&id=${id}`)
            .then(r=>r.json())
            .then(d => {
                if(!d.success){Swal.fire({title:'Error',text:d.message,icon:'error',background:'#1c1c32',color:'#f0f0f5'});return;}
                const m=d.modelo;
                document.getElementById('modalTitulo').innerHTML =
                    '<i class="fas fa-pen" style="color:var(--orange);"></i> Editar Modelo MR';
                document.getElementById('fId').value         =m.id;
                document.getElementById('fNombre').value     =m.nombre||'';
                document.getElementById('fVersion').value    =m.version||'';
                document.getElementById('fDescripcion').value=m.descripcion||'';
                document.getElementById('fEstado').value     =m.estado||'inactivo';
                const fe=m.fecha_entrenamiento||'';
                document.getElementById('fFechaEntrenamiento').value=fe?fe.slice(0,16):'';
                resetCamposVisuales();
                cargarConfigDesdeJson({metadata:m.metadata||{}});
                cargarMetricasDesdeJson(m.metricas||{});
                document.getElementById('modalForm').classList.add('show');
            })
            .catch(()=>Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'}));
    };

    window.cerrarModal = function() {
        document.getElementById('modalForm').classList.remove('show');
    };
    document.getElementById('modalForm').addEventListener('click', function(e) {
        if(e.target===this) cerrarModal();
    });

    window.guardar = function() {
        const id     =document.getElementById('fId').value;
        const nombre =document.getElementById('fNombre').value.trim();
        const version=document.getElementById('fVersion').value.trim();
        const desc   =document.getElementById('fDescripcion').value.trim();
        const estado =document.getElementById('fEstado').value;
        const fecha  =document.getElementById('fFechaEntrenamiento').value;
        if(!nombre)  {Swal.fire({title:'Requerido',text:'El nombre es obligatorio.',icon:'warning',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});return;}
        if(!version) {Swal.fire({title:'Requerido',text:'La versión es obligatoria.',icon:'warning',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});return;}
        const payload={id:id||undefined,nombre,version,descripcion:desc,estado,
            fecha_entrenamiento:fecha||null,
            configuracion_json:buildConfigJson(),metricas_desempeno:buildMetricasJson()};
        const accion=id?'Actualizar':'Crear';
        const btn=document.getElementById('btnGuardar');
        btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Guardando…';
        fetch(`index.php?c=adminmodelosmr&a=${accion}`,{
            method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
        .then(r=>r.json())
        .then(d=>{
            btn.disabled=false; btn.innerHTML='<i class="fas fa-floppy-disk"></i> Guardar';
            Swal.fire({title:d.success?(id?'¡Actualizado!':'¡Creado!'):'Error',text:d.message,
                icon:d.success?'success':'error',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});
            if(d.success){cerrarModal();cargar();cargarStats();}
        })
        .catch(()=>{
            btn.disabled=false; btn.innerHTML='<i class="fas fa-floppy-disk"></i> Guardar';
            Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'});
        });
    };

    // ═══════════════════════════════════════════════════════════════════
    //  CAMBIAR ESTADO
    // ═══════════════════════════════════════════════════════════════════
    window.cambiarEstado = function(id, nuevoEstado) {
        const textos={
            activo:    {txt:'activar',   ico:'fa-circle-check',col:'#22c55e'},
            inactivo:  {txt:'desactivar',ico:'fa-ban',         col:'#ef4444'},
            entrenando:{txt:'marcar como entrenando',ico:'fa-spinner',col:'#f59e0b'},
        };
        const cfg=textos[nuevoEstado]||textos.inactivo;
        Swal.fire({
            title:`¿${cfg.txt.charAt(0).toUpperCase()+cfg.txt.slice(1)} modelo?`,
            text:`El modelo quedará en estado "${nuevoEstado}".`,
            icon:'question',background:'#1c1c32',color:'#f0f0f5',
            confirmButtonColor:cfg.col,cancelButtonColor:'rgba(255,255,255,.08)',
            confirmButtonText:`<i class="fas ${cfg.ico}"></i> Sí, ${cfg.txt}`,
            cancelButtonText:'Cancelar',showCancelButton:true,
        }).then(res=>{
            if(!res.isConfirmed) return;
            fetch('index.php?c=adminmodelosmr&a=CambiarEstado',{
                method:'POST',headers:{'Content-Type':'application/json'},
                body:JSON.stringify({id,estado:nuevoEstado})})
            .then(r=>r.json())
            .then(d=>{
                Swal.fire({title:d.success?'Listo':'Error',text:d.message,
                    icon:d.success?'success':'error',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});
                if(d.success){cargar();cargarStats();if(_modeloSel===id)switchTab(_tabActiva,true);}
            })
            .catch(()=>Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'}));
        });
    };

    // ═══════════════════════════════════════════════════════════════════
    //  EXPORTAR EXCEL / PDF
    // ═══════════════════════════════════════════════════════════════════
    window.exportarExcel = function() {
        const q=encodeURIComponent(document.getElementById('inputBusqueda').value.trim());
        const st=document.getElementById('selEstado').value;
        Swal.fire({title:'Generando Excel…',didOpen:()=>Swal.showLoading(),background:'#1c1c32',color:'#f0f0f5',allowOutsideClick:false});
        fetch(`index.php?c=adminmodelosmr&a=ListarTodo&q=${q}&estado=${st}`)
        .then(r=>r.json())
        .then(d=>{
            Swal.close();
            if(!d.success||!d.datos.length){Swal.fire({title:'Sin datos',icon:'info',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});return;}
            const filas=d.datos.map(r=>({
                'ID':r.id,'Nombre':r.nombre,'Versión':r.version,'Descripción':r.descripcion||'',
                'Categoría':r.categoria_principal||'','Ciudad':r.ciudad||'',
                'Tipo Modelo':r.tipo_modelo||'','Features/Input':r.features_por_input||'',
                'Total Alternativas':r.total_alternativas||0,'Dataset Rows':r.cant_dataset||0,
                'Estado':r.estado,'Estado Entrenamiento':r.estado_entrenamiento||'',
                'Epochs':r.total_epochs||'','Fecha Entrenamiento':(r.fecha_entrenamiento||'').slice(0,10),
                'Actualizado':(r.updated_at||'').slice(0,10),
            }));
            const ws=XLSX.utils.json_to_sheet(filas);
            ws['!cols']=[{wch:5},{wch:40},{wch:8},{wch:40},{wch:14},{wch:10},{wch:22},{wch:13},{wch:16},{wch:12},{wch:10},{wch:18},{wch:8},{wch:18},{wch:12}];
            const wb=XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb,ws,'ModelosMR');
            XLSX.writeFile(wb,`etreva_modelos_mr_${new Date().toISOString().slice(0,10)}.xlsx`);
        })
        .catch(()=>{Swal.close();Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'});});
    };

    window.imprimirPDF = function() {
        const q=encodeURIComponent(document.getElementById('inputBusqueda').value.trim());
        const st=document.getElementById('selEstado').value;
        Swal.fire({title:'Preparando PDF…',didOpen:()=>Swal.showLoading(),background:'#1c1c32',color:'#f0f0f5',allowOutsideClick:false});
        fetch(`index.php?c=adminmodelosmr&a=ListarTodo&q=${q}&estado=${st}`)
        .then(r=>r.json())
        .then(d=>{
            Swal.close();
            if(!d.success||!d.datos.length){Swal.fire({title:'Sin datos',icon:'info',background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b'});return;}
            const rows=d.datos.map(r=>`<tr>
                <td>${r.id}</td><td>${r.nombre}</td><td>v${r.version}</td>
                <td>${r.categoria_principal||'—'}</td><td>${r.tipo_modelo||'—'}</td>
                <td>${r.estado}</td><td>${r.total_alternativas||0}</td>
                <td>${r.cant_dataset||0}</td><td>${(r.updated_at||'').slice(0,10)}</td>
            </tr>`).join('');
            document.getElementById('tbody').innerHTML=rows;
            document.getElementById('pagInfo').textContent=`${d.datos.length} modelos (reporte completo)`;
            document.getElementById('pagBtns').innerHTML='';
            setTimeout(()=>window.print(),300);
        })
        .catch(()=>{Swal.close();Swal.fire({title:'Error de conexión',icon:'error',background:'#1c1c32',color:'#f0f0f5'});});
    };

    // ═══════════════════════════════════════════════════════════════════
    //  INIT
    // ═══════════════════════════════════════════════════════════════════
    document.getElementById('btnRefresh').addEventListener('click', () => {
        resetPage(); cargar(); cargarStats();
        if (_modeloSel) switchTab(_tabActiva, true);
    });

    cargar();
    cargarStats();
});
</script>
</body>
</html>
