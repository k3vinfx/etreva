<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etreva Admin — Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/menu.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════
           TOKENS
        ═══════════════════════════════════════════ */
        :root {
            --adm-bg:       #0f0f1a;
            --adm-surface:  #161625;
            --adm-card:     #1c1c32;
            --adm-border:   rgba(229,90,43,0.14);
            --adm-text:     #f0f0f5;
            --adm-muted:    rgba(240,240,245,0.45);
            --adm-orange:   #e55a2b;
            --adm-amber:    #f59e0b;
            --adm-green:    #22c55e;
            --adm-blue:     #38bdf8;
            --adm-purple:   #a78bfa;
            --adm-red:      #ef4444;
            --adm-shadow:   0 12px 40px rgba(0,0,0,0.5);
        }

        html, body {
            background: var(--adm-bg) !important;
            color: var(--adm-text) !important;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0; padding: 0;
        }

        main {
            margin-left: 280px;
            min-height: 100vh;
            background: var(--adm-bg);
            transition: margin-left 0.3s ease;
        }
        main.min-main { margin-left: 85px; }

        /* ═══════════════════════════════════════════
           TOPBAR
        ═══════════════════════════════════════════ */
        .adm-topbar {
            background: var(--adm-surface);
            border-bottom: 1px solid var(--adm-border);
            padding: 16px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .adm-topbar-left h1 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .adm-topbar-left h1 span {
            background: linear-gradient(135deg, var(--adm-orange), #ff9a6c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .adm-topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .adm-fecha {
            font-size: 0.8rem;
            color: var(--adm-muted);
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--adm-border);
            padding: 6px 14px;
            border-radius: 8px;
        }

        .adm-alert-btn {
            position: relative;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--adm-border);
            border-radius: 10px;
            width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: var(--adm-muted);
            font-size: 1rem;
            transition: all 0.2s;
        }

        .adm-alert-btn:hover { background: rgba(229,90,43,0.12); color: var(--adm-orange); }

        .adm-badge-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: var(--adm-red);
            border-radius: 50%;
            border: 2px solid var(--adm-surface);
        }

        /* ═══════════════════════════════════════════
           BODY
        ═══════════════════════════════════════════ */
        .adm-body {
            padding: 28px 36px 60px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .sec-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--adm-text);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 0.3px;
        }

        .sec-title i { color: var(--adm-orange); }

        .sec-title .muted {
            font-size: 0.72rem;
            font-weight: 400;
            color: var(--adm-muted);
            margin-left: 4px;
        }

        /* ═══════════════════════════════════════════
           KPI CARDS
        ═══════════════════════════════════════════ */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        @media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px)  { .kpi-grid { grid-template-columns: 1fr; } }

        .kpi-card {
            background: var(--adm-card);
            border: 1px solid var(--adm-border);
            border-radius: 18px;
            padding: 22px 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: var(--adm-shadow);
            transition: transform 0.2s, border-color 0.2s;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 18px 18px 0 0;
        }

        .kpi-card:hover { transform: translateY(-3px); border-color: rgba(229,90,43,0.3); }

        .kpi-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .kpi-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }

        .kpi-trend {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .kpi-trend.up   { background: rgba(34,197,94,0.12);  color: var(--adm-green); }
        .kpi-trend.down { background: rgba(239,68,68,0.12);  color: var(--adm-red); }
        .kpi-trend.neu  { background: rgba(255,255,255,0.06); color: var(--adm-muted); }

        .kpi-num {
            font-size: 2rem;
            font-weight: 900;
            color: #fff;
            line-height: 1;
        }

        .kpi-label {
            font-size: 0.78rem;
            color: var(--adm-muted);
            margin-top: 2px;
        }

        .kpi-sub {
            font-size: 0.72rem;
            color: var(--adm-muted);
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        .kpi-sub strong { color: var(--adm-text); }

        /* ═══════════════════════════════════════════
           DOS COLUMNAS
        ═══════════════════════════════════════════ */
        .dos-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1000px) { .dos-col { grid-template-columns: 1fr; } }

        .tres-uno {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1000px) { .tres-uno { grid-template-columns: 1fr; } }

        /* ═══════════════════════════════════════════
           PANEL CARD
        ═══════════════════════════════════════════ */
        .panel-card {
            background: var(--adm-card);
            border: 1px solid var(--adm-border);
            border-radius: 18px;
            padding: 22px 24px;
            box-shadow: var(--adm-shadow);
        }

        /* ═══════════════════════════════════════════
           TABLA DE USUARIOS RECIENTES
        ═══════════════════════════════════════════ */
        .adm-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }

        .adm-table thead th {
            color: var(--adm-muted);
            font-weight: 600;
            font-size: 0.72rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 0 12px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        .adm-table tbody tr {
            border-bottom: 1px solid rgba(255,255,255,0.04);
            transition: background 0.15s;
            cursor: pointer;
        }

        .adm-table tbody tr:hover { background: rgba(229,90,43,0.06); }

        .adm-table tbody td {
            padding: 12px;
            color: var(--adm-text);
            vertical-align: middle;
        }

        .user-avatar-mini {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--adm-orange), #ff9a6c);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 800;
            color: #fff;
            margin-right: 8px;
            vertical-align: middle;
        }

        .estado-badge {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
        }

        .estado-activo         { background: rgba(34,197,94,0.12);  color: var(--adm-green); }
        .estado-pendiente      { background: rgba(245,158,11,0.12); color: var(--adm-amber); }
        .estado-inactivo       { background: rgba(239,68,68,0.10);  color: var(--adm-red); }
        .estado-pendiente-correo { background: rgba(56,189,248,0.10); color: var(--adm-blue); }

        /* ═══════════════════════════════════════════
           PAGOS PENDIENTES
        ═══════════════════════════════════════════ */
        .pago-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: background 0.15s;
            cursor: pointer;
            border-radius: 8px;
        }

        .pago-item:hover { background: rgba(229,90,43,0.07); padding-left: 8px; }
        .pago-item:last-child { border-bottom: none; }

        .pago-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: rgba(245,158,11,0.14);
            display: flex; align-items: center; justify-content: center;
            color: var(--adm-amber);
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .pago-info { flex: 1; min-width: 0; }
        .pago-info h6 { font-size: 0.82rem; font-weight: 700; color: var(--adm-text); margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pago-info p  { font-size: 0.72rem; color: var(--adm-muted); margin: 0; }

        .pago-monto { font-size: 0.85rem; font-weight: 800; color: var(--adm-amber); text-align: right; flex-shrink: 0; }

        /* ═══════════════════════════════════════════
           ACTIVIDAD RECIENTE
        ═══════════════════════════════════════════ */
        .actividad-list { display: flex; flex-direction: column; gap: 0; }

        .actividad-item {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            align-items: flex-start;
        }

        .act-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }

        .actividad-item .act-text { font-size: 0.8rem; color: var(--adm-text); line-height: 1.4; }
        .actividad-item .act-time { font-size: 0.7rem; color: var(--adm-muted); margin-top: 2px; }

        /* ═══════════════════════════════════════════
           BARRAS DE PROGRESO
        ═══════════════════════════════════════════ */
        .plan-bar-item { margin-bottom: 16px; }

        .plan-bar-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.78rem;
            color: var(--adm-muted);
            margin-bottom: 6px;
        }

        .plan-bar-header strong { color: var(--adm-text); }

        .plan-bar-track {
            height: 7px;
            background: rgba(255,255,255,0.07);
            border-radius: 50px;
            overflow: hidden;
        }

        .plan-bar-fill {
            height: 100%;
            border-radius: 50px;
            transition: width 1.2s ease;
        }

        /* ═══════════════════════════════════════════
           ACCIONES RÁPIDAS
        ═══════════════════════════════════════════ */
        .acciones-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        @media (max-width: 800px) { .acciones-grid { grid-template-columns: repeat(2, 1fr); } }

        .accion-btn {
            background: var(--adm-card);
            border: 1px solid var(--adm-border);
            border-radius: 14px;
            padding: 18px 14px;
            text-align: center;
            cursor: pointer;
            transition: all 0.22s;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .accion-btn:hover {
            transform: translateY(-3px);
            border-color: rgba(229,90,43,0.35);
            background: rgba(229,90,43,0.07);
        }

        .accion-btn i   { font-size: 1.4rem; }
        .accion-btn span { font-size: 0.78rem; font-weight: 600; color: var(--adm-text); }

        /* ═══════════════════════════════════════════
           SKELETON
        ═══════════════════════════════════════════ */
        .skeleton {
            background: linear-gradient(90deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.09) 50%, rgba(255,255,255,0.04) 100%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }

        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ═══════════════════════════════════════════
           ALERTAS SISTEMA
        ═══════════════════════════════════════════ */
        .sys-alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 10px;
            font-size: 0.8rem;
        }

        .sys-alert.warn  { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); color: var(--adm-amber); }
        .sys-alert.info  { background: rgba(56,189,248,0.08); border: 1px solid rgba(56,189,248,0.18); color: var(--adm-blue); }
        .sys-alert.error { background: rgba(239,68,68,0.09); border: 1px solid rgba(239,68,68,0.18); color: var(--adm-red); }

        @media (max-width: 768px) {
            main, main.min-main { margin-left: 0; }
            .adm-body { padding: 16px 14px 40px; }
            .adm-topbar { padding: 12px 16px; }
        }
    </style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main>

    <!-- ══════════════════════ TOPBAR ══════════════════════ -->
    <div class="adm-topbar">
        <div class="adm-topbar-left">
            <h1>
                <i class="fas fa-shield-halved" style="color:var(--adm-orange);"></i>
                Panel de <span>Administración</span>
            </h1>
        </div>
        <div class="adm-topbar-right">
            <div class="adm-fecha" id="adminFecha">Cargando...</div>
            <div class="adm-alert-btn" title="Notificaciones">
                <i class="fas fa-bell"></i>
                <span class="adm-badge-dot" id="alertDot" style="display:none;"></span>
            </div>
            <a href="index.php?c=login&a=Logout" class="adm-alert-btn" title="Cerrar sesión" style="color:#ef4444;">
                <i class="fas fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- ══════════════════════ BODY ══════════════════════ -->
    <div class="adm-body">

        <!-- ALERTAS DEL SISTEMA -->
        <div id="sysAlertsWrap"></div>

        <!-- KPIs -->
        <div>
            <div class="sec-title"><i class="fas fa-chart-line"></i> Resumen general <span class="muted">— Hoy</span></div>
            <div class="kpi-grid">

                <div class="kpi-card" style="--kpi-color:#e55a2b;">
                    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#e55a2b,#ff9a6c);border-radius:18px 18px 0 0;"></div>
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:rgba(229,90,43,0.15);">
                            <i class="fas fa-users" style="color:#e55a2b;"></i>
                        </div>
                        <span class="kpi-trend up" id="kpiUsuariosTrend">—</span>
                    </div>
                    <div>
                        <div class="kpi-num" id="kpiUsuarios"><span class="skeleton" style="display:inline-block;width:60px;height:32px;"></span></div>
                        <div class="kpi-label">Usuarios registrados</div>
                    </div>
                    <div class="kpi-sub">Activos hoy: <strong id="kpiUsuariosHoy">—</strong></div>
                </div>

                <div class="kpi-card">
                    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#22c55e,#86efac);border-radius:18px 18px 0 0;"></div>
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:rgba(34,197,94,0.12);">
                            <i class="fas fa-credit-card" style="color:#22c55e;"></i>
                        </div>
                        <span class="kpi-trend up" id="kpiPagosTrend">—</span>
                    </div>
                    <div>
                        <div class="kpi-num" id="kpiPagosActivos"><span class="skeleton" style="display:inline-block;width:60px;height:32px;"></span></div>
                        <div class="kpi-label">Suscripciones activas</div>
                    </div>
                    <div class="kpi-sub">Pendientes: <strong id="kpiPagosPendientes" style="color:#f59e0b;">—</strong></div>
                </div>

                <div class="kpi-card">
                    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#38bdf8,#7dd3fc);border-radius:18px 18px 0 0;"></div>
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:rgba(56,189,248,0.12);">
                            <i class="fas fa-map-marked-alt" style="color:#38bdf8;"></i>
                        </div>
                        <span class="kpi-trend neu">Total</span>
                    </div>
                    <div>
                        <div class="kpi-num" id="kpiAtracciones"><span class="skeleton" style="display:inline-block;width:60px;height:32px;"></span></div>
                        <div class="kpi-label">Atracciones cargadas</div>
                    </div>
                    <div class="kpi-sub">Activas en sistema: <strong id="kpiAtraccionesActivas">—</strong></div>
                </div>

                <div class="kpi-card">
                    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#a78bfa,#c4b5fd);border-radius:18px 18px 0 0;"></div>
                    <div class="kpi-card-header">
                        <div class="kpi-icon" style="background:rgba(167,139,250,0.12);">
                            <i class="fas fa-robot" style="color:#a78bfa;"></i>
                        </div>
                        <span class="kpi-trend up" id="kpiRecTrend">—</span>
                    </div>
                    <div>
                        <div class="kpi-num" id="kpiRecomendaciones"><span class="skeleton" style="display:inline-block;width:60px;height:32px;"></span></div>
                        <div class="kpi-label">Recomendaciones generadas</div>
                    </div>
                    <div class="kpi-sub">Este mes: <strong id="kpiRecMes">—</strong></div>
                </div>

            </div>
        </div>

        <!-- ACCIONES RÁPIDAS -->
         <div>
          <div class="sec-title"><i class="fas fa-bolt"></i> Accesos Directos de Gestión</div>
            <div class="acciones-grid">
                <a class="accion-btn" href="index.php?c=admin&a=Usuarios">
                    <ion-icon name="people-circle-outline" style="color:var(--adm-orange); font-size: 24px;"></ion-icon>
                    <span>Clientes / Pagos</span>
                </a>
                <a class="accion-btn" href="index.php?c=adminatracciones&a=Index">
                    <ion-icon name="location-outline" style="color:#38bdf8; font-size: 24px;"></ion-icon>
                    <span>Nueva Atracción</span>
                </a>
                <a class="accion-btn" href="index.php?c=admincontextorelacion&a=Index">
                    <ion-icon name="git-network-outline" style="color:#f59e0b; font-size: 24px;"></ion-icon>
                    <span>Mapear Contexto</span>
                </a>
                <a class="accion-btn" href="index.php?c=adminmodelosmr&a=Index">
                    <ion-icon name="hardware-chip-outline" style="color:#a78bfa; font-size: 24px;"></ion-icon>
                    <span>Entrenar Modelo</span>
                </a>
            </div>
        </div>

        <!-- USUARIOS RECIENTES + PAGOS PENDIENTES -->
        <div class="tres-uno">

            <!-- TABLA USUARIOS RECIENTES -->
            <div class="panel-card">
                <div class="sec-title" style="margin-bottom:18px;">
                    <i class="fas fa-users"></i> Usuarios recientes
                    <a href="index.php?c=admin&a=Usuarios" style="margin-left:auto;font-size:0.75rem;color:var(--adm-orange);text-decoration:none;font-weight:600;">Ver todos →</a>
                </div>
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Registro</th>
                            <th>Estado</th>
                            <th>Plan</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuarios">
                        <tr><td colspan="5" style="text-align:center;padding:20px;">
                            <div class="skeleton" style="height:20px;width:100%;"></div>
                        </td></tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGOS PENDIENTES -->
            <div class="panel-card">
                <div class="sec-title" style="margin-bottom:18px;">
                    <i class="fas fa-clock" style="color:#f59e0b;"></i> Pagos pendientes
                    <a href="index.php?c=admin&a=Pagos" style="margin-left:auto;font-size:0.75rem;color:var(--adm-orange);text-decoration:none;font-weight:600;">Gestionar →</a>
                </div>
                <div id="listaPagosPendientes">
                    <div class="pago-item skeleton" style="height:58px;"></div>
                    <div class="pago-item skeleton" style="height:58px;margin-top:8px;"></div>
                    <div class="pago-item skeleton" style="height:58px;margin-top:8px;"></div>
                </div>
            </div>

        </div>

        <!-- DISTRIBUCIÓN PLANES + ACTIVIDAD -->
        <div class="dos-col">

            <!-- DISTRIBUCIÓN DE PLANES -->
            <div class="panel-card">
                <div class="sec-title" style="margin-bottom:20px;">
                    <i class="fas fa-pie-chart" style="color:#22c55e;"></i> Distribución de planes
                </div>
                <div id="planesDistrib">
                    <div class="plan-bar-item">
                        <div class="plan-bar-header">
                            <span>Cargando...</span><strong>—</strong>
                        </div>
                        <div class="plan-bar-track"><div class="plan-bar-fill skeleton" style="width:100%;"></div></div>
                    </div>
                </div>
            </div>

            <!-- ACTIVIDAD RECIENTE DEL SISTEMA -->
            <div class="panel-card">
                <div class="sec-title" style="margin-bottom:16px;">
                    <i class="fas fa-activity" style="color:#38bdf8;"></i> Actividad del sistema
                    <span class="muted">— Últimas acciones</span>
                </div>
                <div class="actividad-list" id="actividadList">
                    <div style="height:14px;" class="skeleton" style="margin-bottom:12px;"></div>
                    <div style="height:14px;margin-top:10px;" class="skeleton"></div>
                    <div style="height:14px;margin-top:10px;" class="skeleton"></div>
                    <div style="height:14px;margin-top:10px;" class="skeleton"></div>
                </div>
            </div>

        </div>

    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── FECHA ──────────────────────────────────────────────────────────────────
(function() {
    const ahora = new Date();
    const opciones = { weekday:'short', year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' };
    document.getElementById('adminFecha').textContent = ahora.toLocaleDateString('es-ES', opciones);
})();

// ── HELPERS ────────────────────────────────────────────────────────────────
function estadoBadge(estado) {
    const map = {
        'activo':           'estado-activo',
        'pendiente_correo': 'estado-pendiente-correo',
        'pendiente':        'estado-pendiente',
        'inactivo':         'estado-inactivo',
    };
    const cls = map[estado] || 'estado-pendiente';
    const label = estado ? estado.replace(/_/g,' ') : '—';
    return `<span class="estado-badge ${cls}">${label}</span>`;
}

function planLabel(u) {
    // Usuarios internos: no tienen plan, se muestra su rol
    const rol = parseInt(u.rol, 10);
    if (rol === 2 || rol === 3) {
        const txt = rol === 2 ? 'Administrador' : 'Finanzas';
        return `<span style="font-size:0.7rem;font-weight:800;color:#e55a2b;background:rgba(229,90,43,0.12);padding:3px 10px;border-radius:20px;"><i class="fas fa-user-shield" style="margin-right:4px;"></i>${txt}</span>`;
    }
    try {
        const cfg  = typeof u.configuracion_json === 'string' ? JSON.parse(u.configuracion_json) : (u.configuracion_json || {});
        const rp   = cfg.registro_pago || {};
        const est  = rp.estado || '';
        const plan = rp.tipo_plan && rp.tipo_plan !== 'no_seleccionado' ? rp.tipo_plan : 'Sin plan';
        const vencido = est === 'activo_pagado' && rp.fecha_vencimiento && new Date(rp.fecha_vencimiento.replace(' ', 'T')) < new Date();
        let color = '#94a3b8', extra = '';
        if (est === 'activo_pagado' && !vencido) color = '#22c55e';
        else if (vencido)                        { color = '#ef4444'; extra = ' · vencido'; }
        else if (est === 'pendiente_pago')       { color = '#f59e0b'; extra = ' · por aprobar'; }
        else if (est === 'rechazado')            { color = '#ef4444'; extra = ' · rechazado'; }
        return `<span style="font-size:0.73rem;color:${color};font-weight:700;">${plan}${extra}</span>`;
    } catch(e) { return '<span style="color:var(--adm-muted);">—</span>'; }
}

function iniciales(nombre, apellido) {
    return ((nombre?.[0] || '') + (apellido?.[0] || '')).toUpperCase() || '??';
}

// ── CARGAR KPIs ────────────────────────────────────────────────────────────
function cargarKpis() {
    $.ajax({ url: 'index.php?c=admin&a=Stats', type: 'GET', dataType: 'json',
        success: function(d) {
            if (!d || !d.success) return;

            $('#kpiUsuarios').text(d.total_usuarios ?? '—');
            $('#kpiUsuariosHoy').text(d.usuarios_activos ?? '—');
            $('#kpiUsuariosTrend').text('+'+(d.nuevos_hoy ?? 0)+' hoy').addClass('up');

            $('#kpiPagosActivos').text(d.pagos_activos ?? '—');
            $('#kpiPagosPendientes').text(d.pagos_pendientes ?? '—');
            $('#kpiPagosTrend').text('+'+(d.pagos_semana ?? 0)+' sem').addClass('up');

            $('#kpiAtracciones').text(d.total_atracciones ?? '—');
            $('#kpiAtraccionesActivas').text(d.atracciones_activas ?? d.total_atracciones ?? '—');

            $('#kpiRecomendaciones').text(d.total_recomendaciones ?? '—');
            $('#kpiRecMes').text(d.rec_este_mes ?? '—');
            $('#kpiRecTrend').text('+'+(d.rec_este_mes ?? 0)+' mes').addClass('up');

            // Mini-stats sidebar
            document.getElementById('sbStatUsuarios').textContent = d.usuarios_activos ?? '—';
            document.getElementById('sbStatPagos').textContent    = d.pagos_pendientes ?? '0';

            if ((d.pagos_pendientes ?? 0) > 0) {
                document.getElementById('alertDot').style.display = 'block';
            }
        },
        error: function() {
            // Fallback: cargar datos básicos desde endpoints existentes
            cargarKpisFallback();
        }
    });
}

function cargarKpisFallback() {
    // Usar endpoint de atracciones existente para al menos un KPI
    $.ajax({ url: 'index.php?c=buscar&a=ObtenerTodas', type: 'GET', dataType: 'json',
        success: function(r) {
            if (r.success && r.atracciones) {
                $('#kpiAtracciones').text(r.atracciones.length);
                $('#kpiAtraccionesActivas').text(r.atracciones.length);
            }
        }
    });

    $.ajax({ url: 'index.php?c=recomendaciones&a=ObtenerModeloMR', type: 'GET', dataType: 'json',
        success: function(r) {
            if (r.success && r.recomendaciones) {
                $('#kpiRecomendaciones').text(r.recomendaciones.length);
                $('#kpiRecMes').text(r.recomendaciones.length);
            }
        }
    });
}

// ── USUARIOS RECIENTES ──────────────────────────────────────────────────────
function cargarUsuariosRecientes() {
    $.ajax({ url: 'index.php?c=admin&a=UsuariosRecientes', type: 'GET', dataType: 'json',
        success: function(d) {
            if (!d.success || !d.usuarios?.length) {
                $('#tablaUsuarios').html('<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--adm-muted);">Sin datos disponibles</td></tr>');
                return;
            }
            const rows = d.usuarios.map(u => `
                <tr onclick="window.location='index.php?c=admin&a=VerUsuario&id=${u.id}'">
                    <td>
                        <span class="user-avatar-mini">${iniciales(u.nombre, u.apellido)}</span>
                        ${u.nombre ?? ''} ${u.apellido ?? ''}
                    </td>
                    <td style="color:var(--adm-muted);">${u.email ?? '—'}</td>
                    <td style="color:var(--adm-muted);font-size:0.75rem;">${u.fecha_registro ? new Date(u.fecha_registro).toLocaleDateString('es-ES') : '—'}</td>
                    <td>${estadoBadge(u.estado)}</td>
                    <td>${planLabel(u)}</td>
                </tr>`).join('');
            $('#tablaUsuarios').html(rows);
        },
        error: function() {
            $('#tablaUsuarios').html('<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--adm-muted);font-size:0.8rem;"><i class="fas fa-info-circle" style="margin-right:6px;"></i>Configura el endpoint <code>admin&a=UsuariosRecientes</code></td></tr>');
        }
    });
}

// ── PAGOS PENDIENTES ────────────────────────────────────────────────────────
function cargarPagosPendientes() {
    $.ajax({ url: 'index.php?c=admin&a=PagosPendientes', type: 'GET', dataType: 'json',
        success: function(d) {
            if (!d.success || !d.pagos?.length) {
                $('#listaPagosPendientes').html('<div style="text-align:center;padding:20px;color:var(--adm-muted);font-size:0.82rem;"><i class="fas fa-check-circle" style="color:#22c55e;font-size:1.5rem;display:block;margin-bottom:8px;"></i>Sin pagos pendientes</div>');
                return;
            }
            $('#listaPagosPendientes').html(d.pagos.map(p => `
                <div class="pago-item" onclick="window.location='index.php?c=admin&a=VerPago&id=${p.id}'">
                    <div class="pago-icon"><i class="fas fa-file-invoice"></i></div>
                    <div class="pago-info">
                        <h6>${p.nombre ?? 'Usuario'} ${p.apellido ?? ''}</h6>
                        <p>${p.plan ?? 'Plan'} · ${p.fecha ? new Date(p.fecha).toLocaleDateString('es-ES') : '—'}</p>
                    </div>
                    <div class="pago-monto">Bs. ${p.monto ?? '—'}</div>
                </div>`).join(''));
        },
        error: function() {
            $('#listaPagosPendientes').html('<div style="text-align:center;padding:16px;color:var(--adm-muted);font-size:0.78rem;"><i class="fas fa-info-circle" style="margin-right:6px;"></i>Configura <code>admin&a=PagosPendientes</code></div>');
        }
    });
}

// ── DISTRIBUCIÓN PLANES ─────────────────────────────────────────────────────
function cargarPlanes() {
    $.ajax({ url: 'index.php?c=admin&a=DistribucionPlanes', type: 'GET', dataType: 'json',
        success: function(d) {
            if (!d.success || !d.planes?.length) {
                renderPlanesFallback(); return;
            }
            const max = Math.max(...d.planes.map(p => p.cantidad));
            const COLORES = ['#e55a2b','#22c55e','#38bdf8','#a78bfa','#f59e0b'];
            $('#planesDistrib').html(d.planes.map((p, i) => {
                const pct = max > 0 ? Math.round((p.cantidad/max)*100) : 0;
                return `<div class="plan-bar-item">
                    <div class="plan-bar-header">
                        <span>${p.plan}</span>
                        <strong>${p.cantidad} usuarios</strong>
                    </div>
                    <div class="plan-bar-track">
                        <div class="plan-bar-fill" style="width:0%;background:${COLORES[i%COLORES.length]};" data-pct="${pct}"></div>
                    </div>
                </div>`;
            }).join(''));
            setTimeout(() => { document.querySelectorAll('.plan-bar-fill').forEach(el => { el.style.width = el.dataset.pct + '%'; }); }, 300);
        },
        error: renderPlanesFallback
    });
}

function renderPlanesFallback() {
    const planes = [
        {plan:'Semanal',    cantidad:0, color:'#e55a2b'},
        {plan:'Mensual',    cantidad:0, color:'#22c55e'},
        {plan:'Trimestral', cantidad:0, color:'#38bdf8'},
        {plan:'Anual',      cantidad:0, color:'#a78bfa'},
    ];
    $('#planesDistrib').html(planes.map(p => `
        <div class="plan-bar-item">
            <div class="plan-bar-header">
                <span>${p.plan}</span><strong>${p.cantidad}</strong>
            </div>
            <div class="plan-bar-track">
                <div class="plan-bar-fill" style="width:${p.cantidad}%;background:${p.color};"></div>
            </div>
        </div>`).join('') + '<p style="font-size:0.72rem;color:var(--adm-muted);margin-top:12px;"><i class="fas fa-info-circle"></i> Configura el endpoint <code>admin&a=DistribucionPlanes</code></p>');
}

// ── ACTIVIDAD RECIENTE ──────────────────────────────────────────────────────
function cargarActividad() {
    $.ajax({ url: 'index.php?c=admin&a=ActividadReciente', type: 'GET', dataType: 'json',
        success: function(d) {
            if (!d.success || !d.actividad?.length) {
                renderActividadPlaceholder(); return;
            }
            const TIPOS = {
                'registro': {color:'#22c55e', icono:'fa-user-plus'},
                'pago':     {color:'#f59e0b', icono:'fa-credit-card'},
                'login':    {color:'#38bdf8', icono:'fa-right-to-bracket'},
                'itinerario': {color:'#a78bfa', icono:'fa-route'},
            };
            $('#actividadList').html(d.actividad.map(a => {
                const tipo = TIPOS[a.tipo] || {color:'#e55a2b', icono:'fa-circle-dot'};
                return `<div class="actividad-item">
                    <div class="act-dot" style="background:${tipo.color};"></div>
                    <div>
                        <div class="act-text">${a.descripcion}</div>
                        <div class="act-time">${a.tiempo ?? '—'}</div>
                    </div>
                </div>`;
            }).join(''));
        },
        error: renderActividadPlaceholder
    });
}

function renderActividadPlaceholder() {
    const items = [
        {color:'#22c55e', text:'Sistema inicializado correctamente', time:'Ahora'},
        {color:'#38bdf8', text:'Módulo de administración cargado',    time:'Hace 1 min'},
        {color:'#a78bfa', text:'Configura <code>admin&a=ActividadReciente</code> para ver logs reales', time:'Pendiente'},
    ];
    $('#actividadList').html(items.map(i => `
        <div class="actividad-item">
            <div class="act-dot" style="background:${i.color};"></div>
            <div>
                <div class="act-text">${i.text}</div>
                <div class="act-time">${i.time}</div>
            </div>
        </div>`).join(''));
}

// ── ALERTAS SISTEMA ──────────────────────────────────────────────────────────
function checkAlertas() {
    // Las alertas se generan con los datos que ya tenemos
    const alertas = [];
    const pendientes = parseInt($('#kpiPagosPendientes').text()) || 0;
    if (pendientes > 0) {
        alertas.push({ tipo:'warn', msg: `<strong>${pendientes} pagos pendientes</strong> requieren aprobación. <a href="index.php?c=admin&a=Pagos" style="color:inherit;text-decoration:underline;">Revisar ahora →</a>` });
    }

    if (alertas.length) {
        $('#sysAlertsWrap').html(alertas.map(a => `
            <div class="sys-alert ${a.tipo}">
                <i class="fas fa-triangle-exclamation"></i>
                <span>${a.msg}</span>
            </div>`).join(''));
    }
}

// ── INIT ─────────────────────────────────────────────────────────────────────
$(document).ready(function() {
    cargarKpis();
    cargarUsuariosRecientes();
    cargarPagosPendientes();
    cargarPlanes();
    cargarActividad();

    // Check alertas después de que los KPIs carguen
    setTimeout(checkAlertas, 2500);
});
</script>

</body>
</html>