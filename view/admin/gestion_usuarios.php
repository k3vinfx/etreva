<?php
/*
 * view/admin/gestion_usuarios.php
 * Gestión de usuarios internos: Administrador (rol 2) y Finanzas (rol 3)
 * Acceso exclusivo rol 2
 */
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
<title>Etreva Admin — Equipo Interno</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/menu.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
/* ─── VARIABLES ─────────────────────────────────────────────────── */
:root {
    --bg:       #0b0b16;
    --surface:  #13131f;
    --card:     #18182a;
    --card2:    #1e1e32;
    --border:   rgba(229,90,43,.14);
    --border2:  rgba(255,255,255,.07);
    --text:     #ededf5;
    --muted:    rgba(237,237,245,.42);
    --orange:   #e55a2b;
    --orange2:  #ff8c5a;
    --amber:    #f59e0b;
    --green:    #22c55e;
    --blue:     #38bdf8;
    --red:      #ef4444;
    --purple:   #a78bfa;
    --teal:     #2dd4bf;
}

*   { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
    background: var(--bg) !important;
    color: var(--text) !important;
    min-height: 100vh;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

/* ─── LAYOUT ────────────────────────────────────────────────────── */
main { margin-left: 280px; min-height: 100vh; transition: margin-left .3s; }
main.min-main { margin-left: 85px; }

/* ─── TOPBAR ────────────────────────────────────────────────────── */
.topbar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 14px 32px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 100;
}
.topbar-left { display: flex; align-items: center; gap: 14px; }
.topbar-left h1 { font-size: 1.05rem; font-weight: 800; color: #fff; }
.topbar-left h1 span {
    background: linear-gradient(135deg, var(--orange), var(--orange2));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.breadcrumb { font-size: .72rem; color: var(--muted); display: flex; align-items: center; gap: 6px; }
.breadcrumb a { color: var(--muted); text-decoration: none; transition: color .2s; }
.breadcrumb a:hover { color: var(--orange); }
.topbar-right { display: flex; align-items: center; gap: 8px; }
.btn-icon {
    background: rgba(255,255,255,.05); border: 1px solid var(--border2);
    border-radius: 9px; width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    color: var(--muted); font-size: .9rem; cursor: pointer;
    text-decoration: none; transition: all .2s;
}
.btn-icon:hover { background: rgba(229,90,43,.12); color: var(--orange); border-color: rgba(229,90,43,.3); }

/* ─── BODY ──────────────────────────────────────────────────────── */
.body { padding: 26px 32px 70px; display: flex; flex-direction: column; gap: 22px; }

/* ─── STAT STRIP ────────────────────────────────────────────────── */
.stat-strip { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
.stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px; padding: 18px 20px;
    display: flex; align-items: center; gap: 14px;
    cursor: default; transition: border-color .2s, transform .2s;
    position: relative; overflow: hidden;
}
.stat-card::before {
    content: ''; position: absolute; inset: 0;
    background: var(--stat-glow, transparent);
    opacity: 0; transition: opacity .3s;
}
.stat-card:hover { border-color: rgba(229,90,43,.35); transform: translateY(-2px); }
.stat-card:hover::before { opacity: 1; }
.stat-ico {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; flex-shrink: 0;
}
.stat-val { font-size: 1.5rem; font-weight: 900; color: #fff; line-height: 1; }
.stat-lbl { font-size: .7rem; color: var(--muted); margin-top: 3px; font-weight: 500; }

/* ─── TOOLBAR (filtros + nuevo) ─────────────────────────────────── */
.toolbar {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px; padding: 14px 18px;
    display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
}
.toolbar input[type=text],
.toolbar select {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 9px; padding: 9px 13px;
    color: var(--text); font-size: .82rem; outline: none;
    transition: border-color .2s;
}
.toolbar input[type=text] { flex: 1; min-width: 200px; }
.toolbar input[type=text]:focus,
.toolbar select:focus { border-color: var(--orange); }
.toolbar select option { background: #1c1c32; }

.btn-primary {
    background: linear-gradient(135deg, var(--orange), var(--orange2));
    color: #fff; border: none; border-radius: 10px;
    padding: 9px 20px; font-size: .82rem; font-weight: 700;
    cursor: pointer; display: flex; align-items: center; gap: 7px;
    transition: all .2s; white-space: nowrap; margin-left: auto;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(229,90,43,.35); }

.btn-filtrar {
    background: rgba(229,90,43,.12); color: var(--orange);
    border: 1px solid rgba(229,90,43,.25);
    border-radius: 9px; padding: 9px 16px;
    font-size: .82rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; gap: 6px; transition: all .2s;
}
.btn-filtrar:hover { background: rgba(229,90,43,.22); }

/* ─── TABS ──────────────────────────────────────────────────────── */
.tabs { display: flex; gap: 4px; border-bottom: 1px solid var(--border2); }
.tab {
    padding: 10px 20px; font-size: .82rem; font-weight: 600;
    color: var(--muted); border-bottom: 2px solid transparent;
    cursor: pointer; transition: all .2s; margin-bottom: -1px;
    border-radius: 6px 6px 0 0; display: flex; align-items: center; gap: 7px;
}
.tab:hover { color: var(--text); background: rgba(255,255,255,.03); }
.tab.active { color: var(--orange); border-bottom-color: var(--orange); background: rgba(229,90,43,.06); }
.tab-badge {
    background: var(--orange); color: #fff;
    border-radius: 10px; font-size: .62rem; font-weight: 800;
    min-width: 17px; height: 17px; padding: 0 5px;
    display: inline-flex; align-items: center; justify-content: center;
}

/* ─── TABLA ─────────────────────────────────────────────────────── */
.tabla-wrap {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px; overflow: hidden;
}
.adm-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.adm-table thead th {
    background: rgba(255,255,255,.025); color: var(--muted);
    font-weight: 600; font-size: .68rem; letter-spacing: 1px;
    text-transform: uppercase; padding: 11px 16px;
    border-bottom: 1px solid var(--border2); white-space: nowrap;
}
.adm-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,.04);
    transition: background .15s;
}
.adm-table tbody tr:hover { background: rgba(229,90,43,.04); cursor: pointer; }
.adm-table tbody td { padding: 13px 16px; vertical-align: middle; }

/* Avatar letras */
.av {
    width: 36px; height: 36px; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: .78rem; font-weight: 800; color: #fff; flex-shrink: 0;
}
.av-admin   { background: linear-gradient(135deg, var(--orange), var(--orange2)); }
.av-finanzas{ background: linear-gradient(135deg, var(--teal), #0891b2); }

/* Rol pill */
.rol-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .68rem; font-weight: 700; padding: 3px 10px;
    border-radius: 20px; white-space: nowrap;
}
.rol-admin    { background: rgba(229,90,43,.14); color: var(--orange); border: 1px solid rgba(229,90,43,.25); }
.rol-finanzas { background: rgba(45,212,191,.12); color: var(--teal);   border: 1px solid rgba(45,212,191,.22); }

/* Estado badge */
.badge { font-size: .65rem; font-weight: 700; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
.b-activo          { background: rgba(34,197,94,.12);  color: var(--green); }
.b-inactivo        { background: rgba(239,68,68,.1);   color: var(--red); }
.b-suspendido      { background: rgba(167,139,250,.12);color: var(--purple); }
.b-pendiente-correo{ background: rgba(56,189,248,.1);  color: var(--blue); }
.b-verificado      { background: rgba(34,197,94,.12);  color: var(--green); }
.b-no-verificado   { background: rgba(245,158,11,.12); color: var(--amber); }

/* Acciones fila */
.acciones { display: flex; gap: 5px; flex-wrap: wrap; }
.btn-sm {
    padding: 5px 10px; border-radius: 7px;
    font-size: .71rem; font-weight: 700; border: none;
    cursor: pointer; display: flex; align-items: center; gap: 4px;
    transition: all .2s; white-space: nowrap;
}
.btn-ver    { background: rgba(56,189,248,.1);  color: var(--blue); }
.btn-ver:hover    { background: rgba(56,189,248,.22); transform: translateY(-1px); }
.btn-editar { background: rgba(245,158,11,.1);  color: var(--amber); }
.btn-editar:hover { background: rgba(245,158,11,.22); transform: translateY(-1px); }
.btn-pass   { background: rgba(167,139,250,.1); color: var(--purple); }
.btn-pass:hover   { background: rgba(167,139,250,.22);transform: translateY(-1px); }
.btn-desact { background: rgba(239,68,68,.1);   color: var(--red); }
.btn-desact:hover { background: rgba(239,68,68,.22);  transform: translateY(-1px); }
.btn-activa { background: rgba(34,197,94,.1);   color: var(--green); }
.btn-activa:hover { background: rgba(34,197,94,.22);  transform: translateY(-1px); }

/* Último acceso */
.last-access { font-size: .7rem; color: var(--muted); }

/* ─── PAGINACIÓN ─────────────────────────────────────────────────── */
.paginacion {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; border-top: 1px solid var(--border2);
    font-size: .78rem; color: var(--muted);
}
.pag-btns { display: flex; gap: 4px; }
.pag-btn {
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
    border-radius: 7px; padding: 5px 12px; color: var(--muted);
    cursor: pointer; font-size: .75rem; transition: all .2s;
}
.pag-btn:hover, .pag-btn.active {
    background: rgba(229,90,43,.15); color: var(--orange); border-color: rgba(229,90,43,.3);
}
.pag-btn:disabled { opacity: .3; cursor: not-allowed; }

/* ─── SKELETON ──────────────────────────────────────────────────── */
.skeleton {
    background: linear-gradient(90deg,
        rgba(255,255,255,.04) 0%, rgba(255,255,255,.09) 50%, rgba(255,255,255,.04) 100%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite; border-radius: 8px;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ─── EMPTY STATE ───────────────────────────────────────────────── */
.empty-state { text-align: center; padding: 56px 20px; color: var(--muted); }
.empty-state i { font-size: 2.8rem; display: block; margin-bottom: 14px; opacity: .25; }
.empty-state p { font-size: .85rem; }

/* ─── MODAL BASE ─────────────────────────────────────────────────── */
.modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 1000;
    background: rgba(6,6,14,.88); backdrop-filter: blur(8px);
    align-items: center; justify-content: center;
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: var(--card2); border: 1px solid var(--border);
    border-radius: 22px; width: 94%; max-width: 680px;
    max-height: 92vh; overflow-y: auto;
    animation: popIn .22s cubic-bezier(.34,1.56,.64,1);
}
.modal-box.modal-sm { max-width: 480px; }
@keyframes popIn { from{transform:scale(.9) translateY(16px);opacity:0} to{transform:scale(1) translateY(0);opacity:1} }

.modal-header {
    padding: 22px 26px 18px; border-bottom: 1px solid var(--border2);
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; background: var(--card2); z-index: 2;
}
.modal-header h3 { font-size: 1rem; font-weight: 800; color: #fff; }
.modal-close {
    background: none; border: none; color: var(--muted);
    font-size: 1.1rem; cursor: pointer; width: 32px; height: 32px;
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
    transition: all .2s;
}
.modal-close:hover { background: rgba(255,255,255,.08); color: #fff; }
.modal-body   { padding: 22px 26px; }
.modal-footer {
    padding: 16px 26px 22px; border-top: 1px solid var(--border2);
    display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;
}

/* ─── FORM FIELDS ────────────────────────────────────────────────── */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-grid.full { grid-template-columns: 1fr; }
.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-group.span2 { grid-column: span 2; }
.form-group label { font-size: .72rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .7px; }
.form-group input,
.form-group select,
.form-group textarea {
    background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
    border-radius: 10px; padding: 10px 13px; color: var(--text);
    font-size: .83rem; outline: none; transition: border-color .2s;
    font-family: inherit;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color: var(--orange); }
.form-group select option { background: #1c1c32; }
.form-group textarea { resize: vertical; min-height: 72px; }
.form-error { font-size: .7rem; color: var(--red); margin-top: 2px; display: none; }

/* Separador de sección */
.sec-sep {
    font-size: .67rem; color: var(--orange); text-transform: uppercase;
    letter-spacing: 1.4px; font-weight: 700; margin: 18px 0 12px;
    display: flex; align-items: center; gap: 8px;
}
.sec-sep::after { content:''; flex: 1; height: 1px; background: rgba(229,90,43,.2); }

/* ─── DETALLE MODAL ──────────────────────────────────────────────── */
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
.detail-field label { display: block; font-size: .67rem; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
.detail-field span  { font-size: .85rem; color: var(--text); font-weight: 500; }

/* Historial */
.hist-item { display: flex; gap: 10px; padding: 9px 0; border-bottom: 1px solid rgba(255,255,255,.04); font-size: .77rem; }
.hist-item:last-child { border-bottom: none; }
.hist-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
.hist-desc { font-weight: 600; color: var(--text); }
.hist-meta { font-size: .68rem; color: var(--muted); margin-top: 2px; }

/* Botones modal footer */
.btn-modal {
    padding: 9px 20px; border-radius: 10px; border: none;
    font-size: .82rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; gap: 7px; transition: all .2s;
}
.btn-success { background: rgba(34,197,94,.15);  color: var(--green); border: 1px solid rgba(34,197,94,.25); }
.btn-success:hover { background: rgba(34,197,94,.28); transform: translateY(-1px); }
.btn-danger  { background: rgba(239,68,68,.12);  color: var(--red);   border: 1px solid rgba(239,68,68,.22); }
.btn-danger:hover  { background: rgba(239,68,68,.25); transform: translateY(-1px); }
.btn-warn    { background: rgba(245,158,11,.12); color: var(--amber); border: 1px solid rgba(245,158,11,.22); }
.btn-warn:hover    { background: rgba(245,158,11,.25); transform: translateY(-1px); }
.btn-neutral { background: rgba(255,255,255,.07); color: var(--muted); border: 1px solid rgba(255,255,255,.1); }
.btn-neutral:hover { background: rgba(255,255,255,.12); color: var(--text); }
.btn-purple  { background: rgba(167,139,250,.12); color: var(--purple); border: 1px solid rgba(167,139,250,.22); }
.btn-purple:hover  { background: rgba(167,139,250,.25); transform: translateY(-1px); }

/* Toggle switch (para cambio de rol rápido) */
.rol-toggle { display: flex; gap: 8px; }
.rol-btn {
    flex: 1; padding: 9px; border-radius: 10px; border: 1px solid var(--border2);
    font-size: .78rem; font-weight: 700; cursor: pointer; text-align: center;
    transition: all .2s; background: rgba(255,255,255,.04); color: var(--muted);
}
.rol-btn.selected-admin    { background: rgba(229,90,43,.18); color: var(--orange); border-color: rgba(229,90,43,.4); }
.rol-btn.selected-finanzas { background: rgba(45,212,191,.14); color: var(--teal);  border-color: rgba(45,212,191,.35); }
.rol-btn:hover { border-color: rgba(255,255,255,.2); color: var(--text); }

/* Indicador de fortaleza de contraseña */
.pass-strength { height: 4px; border-radius: 4px; margin-top: 6px; transition: all .3s; background: rgba(255,255,255,.08); }
.pass-strength-bar { height: 100%; border-radius: 4px; transition: all .3s; width: 0; }

/* ─── RESPONSIVE ─────────────────────────────────────────────────── */
@media (max-width: 900px) {
    main, main.min-main { margin-left: 0; }
    .body { padding: 14px 12px 50px; }
    .stat-strip { grid-template-columns: repeat(2, 1fr); }
    .form-grid { grid-template-columns: 1fr; }
    .form-group.span2 { grid-column: span 1; }
    .detail-grid { grid-template-columns: 1fr; }
}
</style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main id="mainContent">

<!-- ══════════════════ TOPBAR ══════════════════ -->
<div class="topbar">
    <div class="topbar-left">
        <div>
            <h1><i class="fas fa-user-shield" style="color:var(--orange);margin-right:8px;"></i>Equipo <span>Interno</span></h1>
            <div class="breadcrumb">
                <a href="index.php?c=admin&a=Inicio"><i class="fas fa-house"></i> Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size:.55rem;"></i>
                <span>Gestión de Administradores y Finanzas</span>
            </div>
        </div>
    </div>
    <div class="topbar-right">
        <div class="btn-icon" id="btnRefresh" title="Actualizar tabla"><i class="fas fa-rotate-right"></i></div>
        <a class="btn-icon" href="index.php?c=admin&a=Inicio" title="Dashboard"><i class="fas fa-grid-2"></i></a>
        <a class="btn-icon" href="index.php?c=login&a=Logout" title="Salir" style="color:var(--red);"><i class="fas fa-right-from-bracket"></i></a>
    </div>
</div>

<!-- ══════════════════ BODY ══════════════════ -->
<div class="body">

    <!-- STAT STRIP -->
    <div class="stat-strip">
        <div class="stat-card" style="--stat-glow:radial-gradient(ellipse at 0 0,rgba(229,90,43,.08),transparent 70%);">
            <div class="stat-ico" style="background:rgba(229,90,43,.14);">
                <i class="fas fa-users-gear" style="color:var(--orange);"></i>
            </div>
            <div>
                <div class="stat-val" id="st-total">—</div>
                <div class="stat-lbl">Total equipo</div>
            </div>
        </div>
        <div class="stat-card" style="cursor:pointer;" onclick="setTab('admins')">
            <div class="stat-ico" style="background:rgba(229,90,43,.12);">
                <i class="fas fa-user-shield" style="color:var(--orange);"></i>
            </div>
            <div>
                <div class="stat-val" id="st-admins">—</div>
                <div class="stat-lbl">Administradores</div>
            </div>
        </div>
        <div class="stat-card" style="cursor:pointer;" onclick="setTab('finanzas')">
            <div class="stat-ico" style="background:rgba(45,212,191,.1);">
                <i class="fas fa-coins" style="color:var(--teal);"></i>
            </div>
            <div>
                <div class="stat-val" id="st-finanzas">—</div>
                <div class="stat-lbl">Finanzas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-ico" style="background:rgba(34,197,94,.1);">
                <i class="fas fa-circle-check" style="color:var(--green);"></i>
            </div>
            <div>
                <div class="stat-val" id="st-activos">—</div>
                <div class="stat-lbl">Cuentas activas</div>
            </div>
        </div>
        <div class="stat-card" style="cursor:pointer;" onclick="setTab('pendientes')">
            <div class="stat-ico" style="background:rgba(56,189,248,.1);">
                <i class="fas fa-envelope-circle-check" style="color:var(--blue);"></i>
            </div>
            <div>
                <div class="stat-val" id="st-pendientes">—</div>
                <div class="stat-lbl">Pendiente verificar</div>
            </div>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <input type="text" id="inputBusqueda" placeholder="🔍  Buscar nombre, apellido o email…" oninput="debounceSearch()">
        <select id="selEstado" onchange="resetPage(); cargarTabla()">
            <option value="">Todos los estados</option>
            <option value="activo">Activos</option>
            <option value="inactivo">Inactivos</option>
            <option value="suspendido">Suspendidos</option>
            <option value="pendiente_correo">Pendiente correo</option>
        </select>
        <button class="btn-filtrar" onclick="resetPage(); cargarTabla()">
            <i class="fas fa-filter"></i> Filtrar
        </button>
        <button class="btn-primary" onclick="abrirModalCrear()">
            <i class="fas fa-user-plus"></i> Nuevo usuario
        </button>
    </div>

    <!-- TABS -->
    <div class="tabs">
        <div class="tab active" data-tab="todos"     onclick="setTab('todos')">
            <i class="fas fa-users"></i> Todos
        </div>
        <div class="tab" data-tab="admins"    onclick="setTab('admins')">
            <i class="fas fa-user-shield"></i> Administradores
        </div>
        <div class="tab" data-tab="finanzas"  onclick="setTab('finanzas')">
            <i class="fas fa-coins"></i> Finanzas
        </div>
        <div class="tab" data-tab="pendientes" onclick="setTab('pendientes')">
            <i class="fas fa-clock"></i> Pendientes
            <span class="tab-badge" id="tabBadge" style="display:none;">0</span>
        </div>
    </div>

    <!-- TABLA -->
    <div class="tabla-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Verificado</th>
                    <th>Departamento</th>
                    <th>Último acceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyUsuarios">
                <tr><td colspan="9" style="padding:32px;">
                    <div class="skeleton" style="height:18px;"></div>
                </td></tr>
            </tbody>
        </table>
        <div class="paginacion">
            <span id="pagInfo">Cargando…</span>
            <div class="pag-btns" id="pagBtns"></div>
        </div>
    </div>

</div><!-- /body -->
</main>

<!-- ══════════════════ MODAL VER DETALLE ══════════════════ -->
<div class="modal-overlay" id="modalVer">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="verTitulo">Detalle del usuario</h3>
            <button class="modal-close" onclick="cerrarModal('modalVer')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="verBody">
            <div class="skeleton" style="height:260px;"></div>
        </div>
        <div class="modal-footer" id="verFooter"></div>
    </div>
</div>

<!-- ══════════════════ MODAL CREAR ══════════════════ -->
<div class="modal-overlay" id="modalCrear">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus" style="color:var(--orange);margin-right:8px;"></i>Nuevo usuario interno</h3>
            <button class="modal-close" onclick="cerrarModal('modalCrear')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="sec-sep"><i class="fas fa-id-card"></i> Datos personales</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" id="c_nombre" placeholder="Ej. María">
                    <span class="form-error" id="ce_nombre"></span>
                </div>
                <div class="form-group">
                    <label>Apellido *</label>
                    <input type="text" id="c_apellido" placeholder="Ej. González">
                    <span class="form-error" id="ce_apellido"></span>
                </div>
                <div class="form-group">
                    <label>Correo electrónico *</label>
                    <input type="email" id="c_email" placeholder="usuario@etreva.com">
                    <span class="form-error" id="ce_email"></span>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" id="c_telefono" placeholder="+591 70000000">
                </div>
                <div class="form-group">
                    <label>Género</label>
                    <select id="c_genero">
                        <option value="">Seleccionar…</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha de nacimiento</label>
                    <input type="date" id="c_fecha_nacimiento">
                </div>
                <div class="form-group">
                    <label>Nacionalidad</label>
                    <input type="text" id="c_nacionalidad" placeholder="Ej. Boliviana">
                </div>
                <div class="form-group">
                    <label>Departamento</label>
                    <input type="text" id="c_departamento" placeholder="Ej. Administración">
                </div>
                <div class="form-group span2">
                    <label>Descripción / Notas internas</label>
                    <textarea id="c_descripcion" placeholder="Notas opcionales sobre el usuario…"></textarea>
                </div>
            </div>

            <div class="sec-sep"><i class="fas fa-shield-halved"></i> Rol y acceso</div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>Rol *</label>
                <div class="rol-toggle">
                    <div class="rol-btn selected-admin" id="rolBtn2" onclick="selectRol(2)">
                        <i class="fas fa-user-shield"></i> Administrador
                        <div style="font-size:.65rem;font-weight:400;margin-top:2px;opacity:.7;">Acceso total al panel</div>
                    </div>
                    <div class="rol-btn" id="rolBtn3" onclick="selectRol(3)">
                        <i class="fas fa-coins"></i> Finanzas
                        <div style="font-size:.65rem;font-weight:400;margin-top:2px;opacity:.7;">Solo pagos y atracciones</div>
                    </div>
                </div>
                <input type="hidden" id="c_rol" value="2">
                <span class="form-error" id="ce_rol"></span>
            </div>

            <div class="sec-sep"><i class="fas fa-lock"></i> Contraseña inicial</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Contraseña *</label>
                    <input type="password" id="c_password" placeholder="Mínimo 8 caracteres" oninput="checkPass()">
                    <div class="pass-strength"><div class="pass-strength-bar" id="passBar"></div></div>
                    <span class="form-error" id="ce_password"></span>
                </div>
                <div class="form-group">
                    <label>Confirmar contraseña *</label>
                    <input type="password" id="c_password2" placeholder="Repite la contraseña">
                    <span class="form-error" id="ce_password2"></span>
                </div>
            </div>
            <div style="margin-top:10px;font-size:.74rem;color:var(--muted);background:rgba(56,189,248,.06);border:1px solid rgba(56,189,248,.15);border-radius:8px;padding:10px 14px;">
                <i class="fas fa-circle-info" style="color:var(--blue);"></i>
                Se enviará un correo de verificación al usuario para activar su cuenta.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal btn-neutral" onclick="cerrarModal('modalCrear')">
                <i class="fas fa-xmark"></i> Cancelar
            </button>
            <button class="btn-modal btn-primary" style="border:none;" onclick="crearUsuario()">
                <i class="fas fa-user-plus"></i> Crear usuario
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════ MODAL EDITAR ══════════════════ -->
<div class="modal-overlay" id="modalEditar">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="fas fa-pen" style="color:var(--amber);margin-right:8px;"></i>Editar usuario</h3>
            <button class="modal-close" onclick="cerrarModal('modalEditar')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="e_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" id="e_nombre">
                </div>
                <div class="form-group">
                    <label>Apellido *</label>
                    <input type="text" id="e_apellido">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" id="e_telefono">
                </div>
                <div class="form-group">
                    <label>Género</label>
                    <select id="e_genero">
                        <option value="">Seleccionar…</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha de nacimiento</label>
                    <input type="date" id="e_fecha_nacimiento">
                </div>
                <div class="form-group">
                    <label>Nacionalidad</label>
                    <input type="text" id="e_nacionalidad">
                </div>
                <div class="form-group">
                    <label>Departamento</label>
                    <input type="text" id="e_departamento">
                </div>
                <div class="form-group span2">
                    <label>Descripción</label>
                    <textarea id="e_descripcion"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal btn-neutral" onclick="cerrarModal('modalEditar')">Cancelar</button>
            <button class="btn-modal btn-warn" onclick="guardarEdicion()">
                <i class="fas fa-floppy-disk"></i> Guardar cambios
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════ MODAL RESET PASSWORD ══════════════════ -->
<div class="modal-overlay" id="modalPass">
    <div class="modal-box modal-sm">
        <div class="modal-header">
            <h3><i class="fas fa-key" style="color:var(--purple);margin-right:8px;"></i>Resetear contraseña</h3>
            <button class="modal-close" onclick="cerrarModal('modalPass')"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="p_id">
            <p style="font-size:.82rem;color:var(--muted);margin-bottom:16px;">
                Usuario: <strong id="p_nombre" style="color:var(--text);"></strong>
            </p>

            <!-- Modo -->
            <div class="form-group" style="margin-bottom:16px;">
                <label>Método de reseteo</label>
                <div style="display:flex;gap:8px;margin-top:4px;">
                    <label style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 12px;
                        border:1px solid var(--border2);border-radius:10px;cursor:pointer;font-size:.8rem;
                        background:rgba(255,255,255,.04);text-transform:none;letter-spacing:0;">
                        <input type="radio" name="pass_modo" value="correo" checked onchange="togglePassModo()">
                        <span><i class="fas fa-envelope" style="color:var(--blue);"></i> Enviar enlace por correo</span>
                    </label>
                    <label style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 12px;
                        border:1px solid var(--border2);border-radius:10px;cursor:pointer;font-size:.8rem;
                        background:rgba(255,255,255,.04);text-transform:none;letter-spacing:0;">
                        <input type="radio" name="pass_modo" value="directa" onchange="togglePassModo()">
                        <span><i class="fas fa-pen" style="color:var(--amber);"></i> Establecer directamente</span>
                    </label>
                </div>
            </div>

            <div id="passDirectaWrap" style="display:none;">
                <div class="form-group">
                    <label>Nueva contraseña (mínimo 8 caracteres)</label>
                    <input type="password" id="p_password" placeholder="Nueva contraseña">
                </div>
            </div>

            <div id="pasCorreoInfo" style="font-size:.78rem;color:var(--muted);background:rgba(56,189,248,.06);
                border:1px solid rgba(56,189,248,.15);border-radius:8px;padding:10px 14px;">
                <i class="fas fa-circle-info" style="color:var(--blue);"></i>
                Se enviará un enlace de restablecimiento con validez de <strong style="color:var(--text);">2 horas</strong>.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-modal btn-neutral" onclick="cerrarModal('modalPass')">Cancelar</button>
            <button class="btn-modal btn-purple" onclick="ejecutarReset()">
                <i class="fas fa-key"></i> Resetear contraseña
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     JAVASCRIPT
═══════════════════════════════════════════════════════════ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ── ESTADO ──────────────────────────────────────────────────────────
const pag = { page: 1, total: 0, pages: 1 };
let _filtroRol = 0;
let _dTimer    = null;
const BASE     = 'index.php?c=adminGestionUsuarios&a=';

// ── HELPERS ─────────────────────────────────────────────────────────
const esc  = s => !s ? '' : String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
const ini  = (n,a) => ((n?.[0]||'')+(a?.[0]||'')).toUpperCase()||'?';
const fmt  = f => f ? new Date(f).toLocaleDateString('es-ES',{day:'2-digit',month:'short',year:'numeric'}) : '—';
const fmtH = f => {
    if (!f) return '—';
    const d = Math.floor((Date.now() - new Date(f))/86400000);
    if (d === 0) return 'Hoy';
    if (d === 1) return 'Ayer';
    return `Hace ${d}d`;
};

function debounceSearch() { clearTimeout(_dTimer); _dTimer = setTimeout(()=>{resetPage();cargarTabla();}, 380); }
function resetPage() { pag.page = 1; }

const swal = cfg => Swal.fire({background:'#1c1c32',color:'#f0f0f5',confirmButtonColor:'#e55a2b',...cfg});

// ── TABS ────────────────────────────────────────────────────────────
function setTab(tab) {
    resetPage();
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`[data-tab="${tab}"]`)?.classList.add('active');

    _filtroRol = 0;
    const selE = document.getElementById('selEstado');

    if (tab === 'admins')    { _filtroRol = 2; selE.value = ''; }
    else if (tab === 'finanzas')  { _filtroRol = 3; selE.value = ''; }
    else if (tab === 'pendientes'){ _filtroRol = 0; selE.value = 'pendiente_correo'; }
    else { _filtroRol = 0; selE.value = ''; }

    cargarTabla();
}

// ── CARGAR TABLA ────────────────────────────────────────────────────
function cargarTabla() {
    const q   = document.getElementById('inputBusqueda').value.trim();
    const est = document.getElementById('selEstado').value;

    document.getElementById('tbodyUsuarios').innerHTML =
        '<tr><td colspan="9" style="padding:30px;"><div class="skeleton" style="height:18px;"></div></td></tr>';

    const url = `${BASE}Listar&q=${encodeURIComponent(q)}&rol=${_filtroRol}&estado=${est}&page=${pag.page}`;
    fetch(url)
        .then(r => r.json())
        .then(d => {
            if (!d.success) { renderVacio(d.message||'Error al cargar'); return; }
            pag.total = d.total; pag.pages = d.pages;
            if (d.yo) { YO = d.yo; ajustarAltaSegunRango(); }
            if (!d.usuarios?.length) { renderVacio('Sin usuarios con esos filtros'); renderPag(); return; }
            renderTabla(d.usuarios);
            renderPag();
        })
        .catch(() => renderVacio('Error de conexión'));
}

// ── RENDER TABLA ────────────────────────────────────────────────────
let YO = { id: 0, super_admin: false };
// Solo un super admin puede crear administradores: si no lo es, el alta queda en Finanzas
function ajustarAltaSegunRango() {
    const b2 = document.getElementById('rolBtn2');
    if (!b2) return;
    b2.style.display = YO.super_admin ? '' : 'none';
    if (!YO.super_admin && typeof selectRol === 'function') selectRol(3);
}
function rolPill(rol, sup) {
    if (+rol === 2 && sup) return '<span class="rol-pill rol-admin" style="background:rgba(245,158,11,.14);color:#f59e0b;border-color:rgba(245,158,11,.35)"><i class="fas fa-crown"></i> Super admin</span>';
    if (+rol === 2) return '<span class="rol-pill rol-admin"><i class="fas fa-user-shield"></i> Administrador</span>';
    if (+rol === 3) return '<span class="rol-pill rol-finanzas"><i class="fas fa-coins"></i> Finanzas</span>';
    return '<span class="rol-pill">—</span>';
}
function estadoBadge(est) {
    const m = {activo:'b-activo',inactivo:'b-inactivo',suspendido:'b-suspendido',pendiente_correo:'b-pendiente-correo'};
    return `<span class="badge ${m[est]||''}">${(est||'—').replace(/_/g,' ')}</span>`;
}
function avClass(rol) { return +rol === 3 ? 'av-finanzas' : 'av-admin'; }

function renderTabla(usuarios) {
    const rows = usuarios.map(u => `
        <tr onclick="verUsuario(${u.id})">
            <td style="color:var(--muted);font-size:.72rem;">${u.id}</td>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="av ${avClass(u.rol)}">${ini(u.nombre,u.apellido)}</span>
                    <div>
                        <div style="font-weight:700;">${esc(u.nombre)||''} ${esc(u.apellido)||''}</div>
                        <div style="font-size:.7rem;color:var(--muted);margin-top:2px;">${esc(u.departamento)||'Sin departamento'}</div>
                    </div>
                </div>
            </td>
            <td style="color:var(--muted);font-size:.78rem;">${esc(u.email)||'—'}</td>
            <td>${rolPill(u.rol, u.super_admin)}</td>
            <td>${estadoBadge(u.estado)}</td>
            <td>
                ${u.verificado
                    ? '<span class="badge b-verificado"><i class="fas fa-check"></i> Verificado</span>'
                    : '<span class="badge b-no-verificado"><i class="fas fa-clock"></i> Pendiente</span>'}
            </td>
            <td style="font-size:.78rem;">${esc(u.departamento)||'—'}</td>
            <td class="last-access">${fmtH(u.ultimo_acceso)}</td>
            <td onclick="event.stopPropagation();">
                <div class="acciones">
                    <button class="btn-sm btn-ver"    onclick="verUsuario(${u.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                    ${(u.permisos||{}).editar ? `<button class="btn-sm btn-editar" onclick="abrirModalEditar(${u.id})" title="Editar"><i class="fas fa-pen"></i></button>` : ''}
                    ${(u.permisos||{}).password ? `<button class="btn-sm btn-pass"   onclick="abrirModalPass(${u.id},'${esc(u.nombre)} ${esc(u.apellido)}')" title="Resetear contraseña"><i class="fas fa-key"></i></button>` : ''}
                    ${!(u.permisos||{}).estado ? `<span title="${+u.id === +YO.id ? 'Tu propia cuenta' : 'Solo un super administrador puede gestionar esta cuenta'}" style="opacity:.45;padding:0 4px"><i class="fas fa-lock"></i></span>`
                      : u.estado === 'activo'
                        ? `<button class="btn-sm btn-desact" onclick="cambiarEstado(${u.id},'suspendido','${esc(u.nombre)} ${esc(u.apellido)}')" title="Suspender"><i class="fas fa-ban"></i></button>`
                        : `<button class="btn-sm btn-activa" onclick="cambiarEstado(${u.id},'activo','${esc(u.nombre)} ${esc(u.apellido)}')" title="Reactivar"><i class="fas fa-rotate-left"></i></button>`}
                </div>
            </td>
        </tr>`).join('');

    document.getElementById('tbodyUsuarios').innerHTML = rows;
}

function renderVacio(msg) {
    document.getElementById('tbodyUsuarios').innerHTML =
        `<tr><td colspan="9"><div class="empty-state">
            <i class="fas fa-user-slash"></i><p>${msg}</p>
        </div></td></tr>`;
}

// ── PAGINACIÓN ──────────────────────────────────────────────────────
function renderPag() {
    const {page,total,pages} = pag;
    document.getElementById('pagInfo').textContent =
        `Página ${page} de ${pages} · ${total} usuario${total!==1?'s':''}`;
    let b = `<button class="pag-btn" onclick="irPag(${page-1})" ${page<=1?'disabled':''}>‹</button>`;
    const s = Math.max(1,page-2), e = Math.min(pages,page+2);
    for (let i=s;i<=e;i++) b += `<button class="pag-btn ${i===page?'active':''}" onclick="irPag(${i})">${i}</button>`;
    b += `<button class="pag-btn" onclick="irPag(${page+1})" ${page>=pages?'disabled':''}>›</button>`;
    document.getElementById('pagBtns').innerHTML = b;
}
function irPag(p) { if(p<1||p>pag.pages)return; pag.page=p; cargarTabla(); }

// ── STATS ───────────────────────────────────────────────────────────
function cargarStats() {
    fetch(BASE + 'Stats')
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            document.getElementById('st-total').textContent    = d.total;
            document.getElementById('st-admins').textContent   = d.admins;
            document.getElementById('st-finanzas').textContent = d.finanzas;
            document.getElementById('st-activos').textContent  = d.activos;
            document.getElementById('st-pendientes').textContent = d.pendientes;
            const badge = document.getElementById('tabBadge');
            badge.textContent = d.pendientes;
            badge.style.display = d.pendientes > 0 ? 'inline-flex' : 'none';
        })
        .catch(()=>{});
}

// ── MODALES HELPERS ─────────────────────────────────────────────────
function abrirModal(id)  { document.getElementById(id).classList.add('show'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('show'); }
document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.modal-overlay.show').forEach(m=>m.classList.remove('show')); });
document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click', e => { if(e.target===m) m.classList.remove('show'); }));

// ── MODAL VER DETALLE ───────────────────────────────────────────────
function verUsuario(id) {
    document.getElementById('verTitulo').textContent = 'Cargando…';
    document.getElementById('verBody').innerHTML   = '<div class="skeleton" style="height:280px;"></div>';
    document.getElementById('verFooter').innerHTML = '';
    abrirModal('modalVer');

    fetch(`${BASE}Ver&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                document.getElementById('verBody').innerHTML = `<p style="color:var(--red);">${d.message}</p>`;
                return;
            }
            renderDetalle(d.usuario);
        })
        .catch(() => {
            document.getElementById('verBody').innerHTML = '<p style="color:var(--red);">Error de conexión</p>';
        });
}

function renderDetalle(u) {
    document.getElementById('verTitulo').textContent =
        `${u.nombre||''} ${u.apellido||''} — #${u.id}`;

    // Historial
    let histHtml = '';
    if (u.historial?.length) {
        const colores = {
            creacion:'#22c55e', edicion:'#f59e0b', cambio_estado:'#a78bfa',
            cambio_rol:'#38bdf8', reset_password:'#ef4444', restauracion:'#f59e0b',
            reenvio_verificacion:'#38bdf8', desactivacion:'#ef4444',
        };
        histHtml = u.historial.slice(0,8).map(h => `
            <div class="hist-item">
                <div class="hist-dot" style="background:${colores[h.evento]||'#888'};"></div>
                <div>
                    <div class="hist-desc">${esc(h.descripcion||h.evento)}</div>
                    <div class="hist-meta">${fmt(h.fecha)} · Admin #${h.por_admin_id||'—'}</div>
                </div>
            </div>`).join('');
    } else {
        histHtml = '<div style="color:var(--muted);font-size:.78rem;">Sin eventos registrados</div>';
    }

    document.getElementById('verBody').innerHTML = `
        <div class="detail-grid">
            <div class="detail-field"><label>Nombre completo</label><span>${esc((u.nombre||'')+' '+(u.apellido||''))}</span></div>
            <div class="detail-field"><label>Email</label><span>${esc(u.email||'—')}</span></div>
            <div class="detail-field"><label>Teléfono</label><span>${esc(u.telefono||'—')}</span></div>
            <div class="detail-field"><label>Género</label><span>${esc(u.genero||'—')}</span></div>
            <div class="detail-field"><label>Nacionalidad</label><span>${esc(u.nacionalidad||'—')}</span></div>
            <div class="detail-field"><label>Fecha nacimiento</label><span>${fmt(u.fecha_nacimiento)}</span></div>
        </div>
        <div class="sec-sep"><i class="fas fa-shield-halved"></i> Rol y estado</div>
        <div class="detail-grid">
            <div class="detail-field"><label>Rol</label><span>${rolPill(u.rol, u.super_admin)}</span></div>
            <div class="detail-field"><label>Estado</label><span>${estadoBadge(u.estado)}</span></div>
            <div class="detail-field"><label>Verificado</label><span>
                ${u.verificado
                    ? `<span class="badge b-verificado"><i class="fas fa-check"></i> Verificado</span>`
                    : `<span class="badge b-no-verificado"><i class="fas fa-clock"></i> Pendiente</span>`}
            </span></div>
            <div class="detail-field"><label>Fecha verificación</label><span>${fmt(u.fecha_verificacion)}</span></div>
            <div class="detail-field"><label>Departamento</label><span>${esc(u.departamento||'—')}</span></div>
            <div class="detail-field"><label>Último acceso</label><span>${fmt(u.ultimo_acceso)}</span></div>
        </div>
        ${u.descripcion ? `
        <div class="sec-sep"><i class="fas fa-note-sticky"></i> Notas internas</div>
        <div style="font-size:.82rem;color:var(--muted);background:rgba(255,255,255,.03);border-radius:10px;padding:12px 14px;">
            ${esc(u.descripcion)}
        </div>` : ''}
        <div class="sec-sep"><i class="fas fa-clock-rotate-left"></i> Historial de actividad</div>
        <div>${histHtml}</div>
    `;

    // Footer con acciones contextuales
    const P = u.permisos || {};
    let fbtns = '';
    if (P.editar) fbtns += `<button class="btn-modal btn-warn" onclick="abrirModalEditar(${u.id}); cerrarModal('modalVer');">
        <i class="fas fa-pen"></i> Editar
    </button>`;
    if (P.password) fbtns += `<button class="btn-modal btn-purple" onclick="abrirModalPass(${u.id},'${esc(u.nombre)} ${esc(u.apellido)}'); cerrarModal('modalVer');">
        <i class="fas fa-key"></i> Resetear pass
    </button>`;
    if (P.super) fbtns += `<button class="btn-modal" style="background:rgba(245,158,11,.12);color:#f59e0b;border:1px solid rgba(245,158,11,.3);"
        onclick="cambiarSuper(${u.id}, ${u.super_admin ? 'false' : 'true'}, '${esc(u.nombre)} ${esc(u.apellido)}')">
        <i class="fas fa-crown"></i> ${u.super_admin ? 'Quitar super admin' : 'Hacer super admin'}
    </button>`;
    if (!P.estado && !P.rol) fbtns += `<span style="font-size:.72rem;opacity:.7;align-self:center;margin-right:auto">
        <i class="fas fa-lock"></i> ${+u.id === +YO.id ? 'Es tu cuenta: no puedes suspenderla ni cambiar su rol.'
          : u.super_admin ? 'Cuenta de super administrador: solo otro super admin la gestiona.' : 'Solo un super administrador puede gestionar a otro administrador.'}
    </span>`;

    if (u.estado === 'pendiente_correo') {
        fbtns += `<button class="btn-modal" style="background:rgba(56,189,248,.12);color:var(--blue);border:1px solid rgba(56,189,248,.22);"
            onclick="reenviarVerificacion(${u.id})">
            <i class="fas fa-envelope"></i> Reenviar verificación
        </button>`;
    }

    if (!P.rol) {
        // sin permiso para cambiar el rol
    } else if (+u.rol === 2) {
        fbtns += `<button class="btn-modal" style="background:rgba(45,212,191,.1);color:var(--teal);border:1px solid rgba(45,212,191,.2);"
            onclick="cambiarRol(${u.id},3,'${esc(u.nombre)} ${esc(u.apellido)}')">
            <i class="fas fa-arrows-rotate"></i> Cambiar a Finanzas
        </button>`;
    } else {
        fbtns += `<button class="btn-modal" style="background:rgba(229,90,43,.12);color:var(--orange);border:1px solid rgba(229,90,43,.22);"
            onclick="cambiarRol(${u.id},2,'${esc(u.nombre)} ${esc(u.apellido)}')">
            <i class="fas fa-arrows-rotate"></i> Cambiar a Admin
        </button>`;
    }

    if (!P.estado) {
        // sin permiso para suspender / reactivar
    } else if (u.estado === 'activo') {
        fbtns += `<button class="btn-modal btn-danger" onclick="cambiarEstado(${u.id},'suspendido','${esc(u.nombre)} ${esc(u.apellido)}')">
            <i class="fas fa-ban"></i> Suspender
        </button>`;
    } else {
        fbtns += `<button class="btn-modal btn-success" onclick="cambiarEstado(${u.id},'activo','${esc(u.nombre)} ${esc(u.apellido)}')">
            <i class="fas fa-rotate-left"></i> Reactivar
        </button>`;
    }

    fbtns += `<button class="btn-modal btn-neutral" onclick="cerrarModal('modalVer')">
        <i class="fas fa-xmark"></i> Cerrar
    </button>`;

    document.getElementById('verFooter').innerHTML = fbtns;
}

// ── MODAL CREAR ─────────────────────────────────────────────────────
function abrirModalCrear() {
    // Limpiar campos
    ['c_nombre','c_apellido','c_email','c_telefono','c_departamento','c_descripcion','c_password','c_password2','c_nacionalidad'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('c_genero').value = '';
    document.getElementById('c_fecha_nacimiento').value = '';
    document.getElementById('c_rol').value = '2';
    document.getElementById('passBar').style.width = '0';
    document.getElementById('passBar').style.background = 'transparent';
    selectRol(2);
    document.querySelectorAll('.form-error').forEach(e => { e.style.display='none'; e.textContent=''; });
    abrirModal('modalCrear');
}

function selectRol(rol) {
    document.getElementById('c_rol').value = rol;
    document.getElementById('rolBtn2').className = 'rol-btn' + (rol===2?' selected-admin':'');
    document.getElementById('rolBtn3').className = 'rol-btn' + (rol===3?' selected-finanzas':'');
}

function checkPass() {
    const v = document.getElementById('c_password').value;
    let f = 0;
    if (v.length >= 8) f++;
    if (/[A-Z]/.test(v)) f++;
    if (/[a-z]/.test(v)) f++;
    if (/\d/.test(v))    f++;
    if (/[@$!%*?&.#_-]/.test(v)) f++;
    const bar = document.getElementById('passBar');
    const w = [0, 20, 40, 65, 82, 100][f] || 0;
    const c = f<=2 ? '#ef4444' : f<=3 ? '#f59e0b' : '#22c55e';
    bar.style.width = w + '%';
    bar.style.background = c;
}

function crearUsuario() {
    let ok = true;
    const campo = (id, errId, test, msg) => {
        const v = document.getElementById(id)?.value?.trim()||'';
        const err = document.getElementById(errId);
        if (!test(v)) {
            if (err) { err.textContent = msg; err.style.display='block'; }
            ok = false;
        } else {
            if (err) { err.style.display='none'; }
        }
        return v;
    };

    const nombre   = campo('c_nombre',   'ce_nombre',   v=>v.length>0,                'Obligatorio');
    const apellido = campo('c_apellido', 'ce_apellido', v=>v.length>0,                'Obligatorio');
    const email    = campo('c_email',    'ce_email',    v=>/^[^@]+@[^@]+\.[^@]+$/.test(v), 'Email inválido');
    const pass     = campo('c_password', 'ce_password', v=>v.length>=8,               'Mínimo 8 caracteres');
    const pass2    = campo('c_password2','ce_password2',v=>v===document.getElementById('c_password').value,'Las contraseñas no coinciden');
    const rol      = +document.getElementById('c_rol').value;

    if (!ok) return;

    const payload = {
        nombre, apellido, email,
        password:        document.getElementById('c_password').value,
        rol,
        telefono:        document.getElementById('c_telefono').value.trim(),
        genero:          document.getElementById('c_genero').value,
        fecha_nacimiento:document.getElementById('c_fecha_nacimiento').value,
        nacionalidad:    document.getElementById('c_nacionalidad').value.trim(),
        departamento:    document.getElementById('c_departamento').value.trim(),
        descripcion:     document.getElementById('c_descripcion').value.trim(),
    };

    swal({title:'Creando usuario…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});

    fetch(BASE+'Crear',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
        .then(r=>r.json())
        .then(d=>{
            if (d.success) {
                swal({title:'¡Usuario creado!',text:d.message,icon:'success'});
                cerrarModal('modalCrear');
                cargarTabla(); cargarStats();
            } else {
                swal({title:'Error',text:d.message,icon:'error'});
            }
        })
        .catch(()=>swal({title:'Error de conexión',icon:'error'}));
}

// ── MODAL EDITAR ─────────────────────────────────────────────────────
function abrirModalEditar(id) {
    abrirModal('modalEditar');
    fetch(`${BASE}Ver&id=${id}`)
        .then(r=>r.json())
        .then(d=>{
            if (!d.success) return;
            const u = d.usuario;
            document.getElementById('e_id').value               = u.id;
            document.getElementById('e_nombre').value           = u.nombre||'';
            document.getElementById('e_apellido').value         = u.apellido||'';
            document.getElementById('e_telefono').value         = u.telefono||'';
            document.getElementById('e_genero').value           = u.genero||'';
            document.getElementById('e_fecha_nacimiento').value = u.fecha_nacimiento ? u.fecha_nacimiento.split('T')[0] : '';
            document.getElementById('e_nacionalidad').value     = u.nacionalidad||'';
            document.getElementById('e_departamento').value     = u.departamento||'';
            document.getElementById('e_descripcion').value      = u.descripcion||'';
        });
}

function guardarEdicion() {
    const id = +document.getElementById('e_id').value;
    if (!id) return;
    const nombre = document.getElementById('e_nombre').value.trim();
    if (!nombre) { swal({title:'El nombre es obligatorio',icon:'warning'}); return; }

    const payload = {
        id,
        nombre,
        apellido:        document.getElementById('e_apellido').value.trim(),
        telefono:        document.getElementById('e_telefono').value.trim(),
        genero:          document.getElementById('e_genero').value,
        fecha_nacimiento:document.getElementById('e_fecha_nacimiento').value,
        nacionalidad:    document.getElementById('e_nacionalidad').value.trim(),
        departamento:    document.getElementById('e_departamento').value.trim(),
        descripcion:     document.getElementById('e_descripcion').value.trim(),
    };

    fetch(BASE+'Editar',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)})
        .then(r=>r.json())
        .then(d=>{
            swal({title:d.success?'¡Guardado!':'Error',text:d.message,icon:d.success?'success':'error'});
            if(d.success){cerrarModal('modalEditar');cargarTabla();}
        })
        .catch(()=>swal({title:'Error de conexión',icon:'error'}));
}

// ── CAMBIAR ESTADO ────────────────────────────────────────────────────
function cambiarEstado(id, nuevoEstado, nombre) {
    const cfgs = {
        activo:     {title:'¿Reactivar cuenta?',  icon:'question',color:'#22c55e',txt:'Reactivar', need:false},
        inactivo:   {title:'¿Desactivar cuenta?', icon:'warning', color:'#ef4444',txt:'Desactivar',need:true},
        suspendido: {title:'¿Suspender cuenta?',  icon:'warning', color:'#a78bfa',txt:'Suspender', need:true},
    };
    const cfg = cfgs[nuevoEstado] || {};

    Swal.fire({
        title:cfg.title,
        html:`<div style="font-size:.85rem;color:#f0f0f5;margin-bottom:${cfg.need?'10px':'0'};">
              Usuario: <strong>${nombre}</strong></div>
              ${cfg.need?`<textarea id="motivoEstado" placeholder="Motivo obligatorio…"
                style="width:100%;background:#0f0f1a;border:1px solid rgba(229,90,43,.3);
                border-radius:8px;color:#f0f0f5;padding:10px;font-size:.83rem;min-height:70px;
                resize:vertical;outline:none;"></textarea>`:''}`,
        icon:cfg.icon, background:'#1c1c32', color:'#f0f0f5',
        confirmButtonColor:cfg.color, cancelButtonColor:'rgba(255,255,255,0.08)',
        confirmButtonText:cfg.txt, cancelButtonText:'Cancelar', showCancelButton:true,
        preConfirm:()=>{
            if (!cfg.need) return '';
            const m = document.getElementById('motivoEstado')?.value.trim()||'';
            if (!m) { Swal.showValidationMessage('El motivo es obligatorio'); return false; }
            return m;
        },
    }).then(res=>{
        if (!res.isConfirmed) return;
        fetch(BASE+'CambiarEstado',{
            method:'POST',headers:{'Content-Type':'application/json'},
            body:JSON.stringify({id,estado:nuevoEstado,motivo:res.value}),
        }).then(r=>r.json()).then(d=>{
            swal({title:d.success?'¡Listo!':'Error',text:d.message,icon:d.success?'success':'error'});
            if(d.success){cerrarModal('modalVer');cargarTabla();cargarStats();}
        }).catch(()=>swal({title:'Error de conexión',icon:'error'}));
    });
}

// ── CAMBIAR ROL ───────────────────────────────────────────────────────
function cambiarSuper(id, hacer, nombre) {
    Swal.fire({
        title: hacer ? '¿Hacer super administrador?' : '¿Quitar super administrador?',
        html: `<strong>${nombre}</strong><br><span style="font-size:.8rem;opacity:.75">${hacer
            ? 'Podrá crear y gestionar administradores, cambiar roles y dar este rango.'
            : 'Seguirá siendo administrador, pero sin poder gestionar a otros administradores.'}</span>`,
        icon: 'question', showCancelButton: true, confirmButtonText: hacer ? 'Sí, hacer super admin' : 'Sí, quitar',
        cancelButtonText: 'Cancelar', confirmButtonColor: '#f59e0b'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(`${BASE}CambiarSuper`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, super: hacer }) })
            .then(x => x.json())
            .then(d => {
                Swal.fire({ title: d.success ? 'Listo' : 'No permitido', text: d.message, icon: d.success ? 'success' : 'error', confirmButtonColor: '#e55a2b' });
                if (d.success) { cerrarModal('modalVer'); cargarTabla(); }
            });
    });
}

function cambiarRol(id, nuevoRol, nombre) {
    const rolNombre = nuevoRol===2 ? 'Administrador' : 'Finanzas';
    swal({
        title:`¿Cambiar a ${rolNombre}?`,
        html:`<div style="font-size:.85rem;color:#f0f0f5;">Usuario: <strong>${nombre}</strong></div>`,
        icon:'question',
        showCancelButton:true, cancelButtonText:'Cancelar',
        confirmButtonText:`Cambiar a ${rolNombre}`,
    }).then(res=>{
        if(!res.isConfirmed) return;
        fetch(BASE+'CambiarRol',{
            method:'POST',headers:{'Content-Type':'application/json'},
            body:JSON.stringify({id,rol:nuevoRol}),
        }).then(r=>r.json()).then(d=>{
            swal({title:d.success?'¡Rol actualizado!':'Error',text:d.message,icon:d.success?'success':'error'});
            if(d.success){cerrarModal('modalVer');cargarTabla();cargarStats();}
        }).catch(()=>swal({title:'Error de conexión',icon:'error'}));
    });
}

// ── REENVIAR VERIFICACIÓN ─────────────────────────────────────────────
function reenviarVerificacion(id) {
    swal({title:'¿Reenviar correo de verificación?',icon:'question',
        showCancelButton:true,cancelButtonText:'Cancelar',confirmButtonText:'Reenviar'
    }).then(res=>{
        if(!res.isConfirmed) return;
        fetch(BASE+'ReenviarVerificacion',{
            method:'POST',headers:{'Content-Type':'application/json'},
            body:JSON.stringify({id}),
        }).then(r=>r.json()).then(d=>{
            swal({title:d.success?'¡Enviado!':'Error',text:d.message,icon:d.success?'success':'error'});
            if(d.success){cerrarModal('modalVer');cargarTabla();}
        }).catch(()=>swal({title:'Error de conexión',icon:'error'}));
    });
}

// ── MODAL RESET PASSWORD ──────────────────────────────────────────────
function abrirModalPass(id, nombre) {
    document.getElementById('p_id').value = id;
    document.getElementById('p_nombre').textContent = nombre;
    document.getElementById('p_password').value = '';
    document.querySelector('input[name="pass_modo"][value="correo"]').checked = true;
    togglePassModo();
    abrirModal('modalPass');
}

function togglePassModo() {
    const modo = document.querySelector('input[name="pass_modo"]:checked')?.value||'correo';
    document.getElementById('passDirectaWrap').style.display = modo==='directa' ? 'block' : 'none';
    document.getElementById('pasCorreoInfo').style.display   = modo==='correo'  ? 'block' : 'none';
}

function ejecutarReset() {
    const id   = +document.getElementById('p_id').value;
    const modo = document.querySelector('input[name="pass_modo"]:checked')?.value||'correo';
    const pass = document.getElementById('p_password').value;

    if (modo==='directa' && pass.length<8) {
        swal({title:'Contraseña muy corta',text:'Mínimo 8 caracteres.',icon:'warning'});
        return;
    }

    fetch(BASE+'ResetPassword',{
        method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({id, modo, password:pass}),
    }).then(r=>r.json()).then(d=>{
        swal({title:d.success?'¡Listo!':'Error',text:d.message,icon:d.success?'success':'error'});
        if(d.success) cerrarModal('modalPass');
    }).catch(()=>swal({title:'Error de conexión',icon:'error'}));
}

// ── INIT ──────────────────────────────────────────────────────────────
document.getElementById('btnRefresh').addEventListener('click',()=>{
    cargarTabla(); cargarStats();
});

cargarTabla();
cargarStats();

// Abrir el detalle directo si se llega desde otra pantalla (?ver=ID, p. ej. el dashboard)
(function () {
    const verId = parseInt(new URLSearchParams(location.search).get('ver'), 10);
    if (verId > 0) verUsuario(verId);
})();
</script>
</body>
</html>