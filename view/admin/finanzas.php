<?php
/*
 * view/admin/finanzas.php
 * Dashboard de finanzas — solo rol 2.
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
<title>Etreva Admin — Finanzas</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/menu.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<style>
/* ─── PRINT ─── */
@media print {
    .barra-lateral,.topbar,.topbar-right,.pend-banner,.charts-row,
    .pag-btns,.btn-icon,.filtros-panel { display:none!important }
    main { margin-left:0!important }
    body,html { background:#fff!important; color:#000!important }
    .body { padding:10px!important; gap:12px!important }
    .kpi-strip { display:grid!important; grid-template-columns:repeat(4,1fr); gap:8px }
    .kpi-card  { border:1px solid #ddd!important; background:#f9f9f9!important; border-radius:8px; padding:12px; break-inside:avoid }
    .kpi-val   { color:#000!important; font-size:1.4rem!important }
    .kpi-lbl   { color:#555!important }
    .tabla-wrap { border:1px solid #ddd!important; background:#fff!important; border-radius:0!important }
    .section-header { background:#f0f0f0!important; border-bottom:1px solid #ddd!important }
    .section-title  { color:#000!important }
    .fin-table thead th { background:#e8e8e8!important; color:#333!important; border-bottom:2px solid #ccc!important }
    .fin-table tbody tr { border-bottom:1px solid #eee!important }
    .fin-table tbody td { color:#000!important; padding:7px 10px!important }
    .badge { border:1px solid #ccc!important; color:#333!important; background:#f0f0f0!important }
    .bottom-row { grid-template-columns:1fr!important }
    .print-header { display:block!important }
}
.print-header { display:none; text-align:center; margin-bottom:16px; padding-bottom:12px; border-bottom:2px solid #e55a2b }
.print-header h2 { margin:0; font-size:1.2rem; color:#000 }
.print-header p  { margin:4px 0 0; font-size:.8rem; color:#555 }

:root {
    --bg:#0f0f1a; --surface:#161625; --card:#1c1c32;
    --border:rgba(229,90,43,.15); --text:#f0f0f5; --muted:rgba(240,240,245,.45);
    --orange:#e55a2b; --amber:#f59e0b; --green:#22c55e;
    --blue:#38bdf8; --red:#ef4444; --purple:#a78bfa;
}
*{box-sizing:border-box}
html,body{background:var(--bg)!important;color:var(--text)!important;min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;margin:0;padding:0}
main{margin-left:280px;min-height:100vh;background:var(--bg);transition:margin-left .3s ease}
main.min-main{margin-left:85px}

/* TOPBAR */
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:14px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.topbar h1{font-size:1.1rem;font-weight:800;color:#fff;margin:0;display:flex;align-items:center;gap:10px}
.topbar h1 span{background:linear-gradient(135deg,var(--orange),#ff9a6c);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.topbar-right{display:flex;align-items:center;gap:10px}
.btn-icon{background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:9px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.9rem;cursor:pointer;text-decoration:none;transition:all .2s}
.btn-icon:hover{background:rgba(229,90,43,.12);color:var(--orange)}

/* ═══════════════════════════════════════════════════════════
   PANEL DE FILTROS DE FECHA
═══════════════════════════════════════════════════════════ */
.filtros-panel {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 13px 32px;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 14px;
}

/* Grupo label + control */
.fp-grp { display:flex; flex-direction:column; gap:4px }
.fp-lbl {
    font-size:.62rem; text-transform:uppercase; letter-spacing:.7px;
    color:var(--muted); font-weight:700;
}

/* Selects e inputs del filtro */
.fp-sel, .fp-inp {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    color: var(--text);
    border-radius: 9px;
    padding: 7px 11px;
    font-size: .78rem;
    outline: none;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    font-family: inherit;
}
.fp-sel:hover, .fp-inp:hover { border-color:rgba(229,90,43,.35) }
.fp-sel:focus, .fp-inp:focus { border-color:rgba(229,90,43,.55); background:rgba(255,255,255,.08) }
.fp-sel option { background:#1c1c32; color:#f0f0f5 }
input[type="date"].fp-inp::-webkit-calendar-picker-indicator { filter:invert(.5) }

/* Separador visual entre grupos */
.fp-sep { width:1px; height:34px; background:rgba(255,255,255,.07); align-self:flex-end; flex-shrink:0 }

/* Presets rápidos */
.fp-presets { display:flex; gap:5px; flex-wrap:wrap }
.fp-preset {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: .72rem;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
}
.fp-preset:hover { background:rgba(229,90,43,.1); color:var(--orange); border-color:rgba(229,90,43,.3) }
.fp-preset.activo { background:rgba(229,90,43,.15); color:var(--orange); border-color:rgba(229,90,43,.4); font-weight:700 }

/* Botones de acción */
.btn-aplicar {
    background: linear-gradient(135deg,var(--orange),#ff7a4a);
    border: none; border-radius: 9px;
    padding: 8px 20px;
    font-size: .78rem; font-weight: 800;
    color: #fff; cursor: pointer;
    transition: all .2s;
    display: flex; align-items: center; gap: 6px;
    box-shadow: 0 4px 14px rgba(229,90,43,.3);
    white-space: nowrap;
}
.btn-aplicar:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(229,90,43,.45) }
.btn-limpiar {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 9px; padding: 8px 13px;
    font-size: .78rem; font-weight: 600;
    color: var(--muted); cursor: pointer; transition: all .2s;
}
.btn-limpiar:hover { color:var(--text); border-color:rgba(255,255,255,.2) }

/* Pill que muestra el filtro activo */
.fp-pill {
    background: rgba(229,90,43,.1);
    border: 1px solid rgba(229,90,43,.28);
    border-radius: 50px;
    padding: 5px 13px;
    font-size: .72rem; font-weight: 700;
    color: var(--orange);
    display: flex; align-items: center; gap: 7px;
    animation: pillIn .22s ease;
}
@keyframes pillIn { from{opacity:0;transform:translateY(-5px)} to{opacity:1;transform:translateY(0)} }
.fp-pill-x { cursor:pointer; opacity:.65; transition:opacity .15s }
.fp-pill-x:hover { opacity:1 }

/* BODY */
.body{padding:24px 32px 60px;display:flex;flex-direction:column;gap:20px}

/* KPI STRIP */
.kpi-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.kpi-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:20px 18px;display:flex;flex-direction:column;gap:8px;transition:border-color .2s;position:relative;overflow:hidden}
.kpi-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,var(--kpi-color),transparent)}
.kpi-card:hover{border-color:rgba(229,90,43,.3)}
.kpi-top{display:flex;align-items:center;justify-content:space-between}
.kpi-ico{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.kpi-trend{font-size:.7rem;font-weight:700;padding:3px 8px;border-radius:20px}
.kpi-val{font-size:1.8rem;font-weight:900;color:#fff;line-height:1;letter-spacing:-.5px}
.kpi-val sup{font-size:.8rem;font-weight:600;vertical-align:super;margin-right:2px;color:var(--muted)}
.kpi-lbl{font-size:.73rem;color:var(--muted)}

/* GRÁFICOS */
.charts-row{display:grid;grid-template-columns:2fr 1fr;gap:16px}
.chart-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:20px 22px}
.chart-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.chart-title{font-size:.85rem;font-weight:800;color:#fff}
.chart-sub{font-size:.72rem;color:var(--muted);margin-top:2px}
.chart-tabs{display:flex;gap:4px}
.chart-tab{padding:5px 12px;border-radius:7px;font-size:.72rem;font-weight:600;cursor:pointer;border:1px solid rgba(255,255,255,.1);color:var(--muted);transition:all .2s;background:none}
.chart-tab.active{background:rgba(229,90,43,.15);color:var(--orange);border-color:rgba(229,90,43,.3)}
canvas{max-height:240px}

/* FILA INFERIOR */
.bottom-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}

/* TABLA */
.tabla-wrap{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden}
.section-header{padding:16px 20px 12px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;justify-content:space-between}
.section-title{font-size:.85rem;font-weight:800;color:#fff;display:flex;align-items:center;gap:8px}
.fin-table{width:100%;border-collapse:collapse;font-size:.8rem}
.fin-table thead th{background:rgba(255,255,255,.03);color:var(--muted);font-weight:600;font-size:.67rem;letter-spacing:1px;text-transform:uppercase;padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.06);white-space:nowrap}
.fin-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s}
.fin-table tbody tr:hover{background:rgba(229,90,43,.04)}
.fin-table tbody td{padding:10px 14px;vertical-align:middle}
.av{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--orange),#ff9a6c);display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;color:#fff;flex-shrink:0}

/* BADGES */
.badge{font-size:.62rem;font-weight:700;padding:2px 8px;border-radius:20px;white-space:nowrap}
.b-green  {background:rgba(34,197,94,.12);  color:var(--green)}
.b-amber  {background:rgba(245,158,11,.12); color:var(--amber)}
.b-blue   {background:rgba(56,189,248,.1);  color:var(--blue)}
.b-purple {background:rgba(167,139,250,.12);color:var(--purple)}
.b-muted  {background:rgba(255,255,255,.06);color:var(--muted)}

/* TOP CLIENTES */
.cliente-item{display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s}
.cliente-item:last-child{border-bottom:none}
.cliente-item:hover{background:rgba(229,90,43,.04)}
.cli-rank{font-size:.7rem;font-weight:900;color:var(--muted);width:18px;text-align:center;flex-shrink:0}
.cli-rank.top1{color:var(--amber)}.cli-rank.top2{color:#94a3b8}.cli-rank.top3{color:#cd7c4a}
.cli-info{flex:1;min-width:0}
.cli-name{font-size:.82rem;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cli-email{font-size:.68rem;color:var(--muted)}
.cli-right{text-align:right;flex-shrink:0}
.cli-total{font-size:.88rem;font-weight:900;color:var(--green)}
.cli-pagos{font-size:.65rem;color:var(--muted)}

/* PLAN BREAKDOWN */
.plan-item{padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.04)}
.plan-item:last-child{border-bottom:none}
.plan-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.plan-name{font-size:.8rem;font-weight:700;color:#fff}
.plan-bar-wrap{background:rgba(255,255,255,.06);border-radius:4px;height:6px;overflow:hidden}
.plan-bar{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--orange),#ff9a6c);transition:width .8s cubic-bezier(.4,0,.2,1)}

/* PAGINACIÓN */
.paginacion{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid rgba(255,255,255,.06);font-size:.75rem;color:var(--muted)}
.pag-btns{display:flex;gap:4px}
.pag-btn{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:7px;padding:4px 10px;color:var(--muted);cursor:pointer;font-size:.72rem;transition:all .2s}
.pag-btn:hover,.pag-btn.active{background:rgba(229,90,43,.15);color:var(--orange);border-color:rgba(229,90,43,.3)}
.pag-btn:disabled{opacity:.3;cursor:not-allowed}

/* SKELETON / EMPTY */
.skeleton{background:linear-gradient(90deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.09) 50%,rgba(255,255,255,.04) 100%);background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:8px}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.empty-fin{text-align:center;padding:36px 16px;color:var(--muted)}
.empty-fin i{font-size:2rem;margin-bottom:8px;display:block;opacity:.3}
.empty-fin p{font-size:.8rem}

/* BANNER PENDIENTE */
.pend-banner{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);border-radius:14px;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.pend-left{display:flex;align-items:center;gap:12px}
.pend-ico{width:40px;height:40px;border-radius:10px;background:rgba(245,158,11,.15);display:flex;align-items:center;justify-content:center;font-size:1rem;color:var(--amber);flex-shrink:0}
.pend-txt{font-size:.82rem;color:var(--text)}
.pend-txt strong{color:var(--amber)}
.pend-monto{font-size:1.4rem;font-weight:900;color:var(--amber)}
.btn-ver-pend{background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.3);border-radius:9px;padding:8px 16px;color:var(--amber);font-size:.78rem;font-weight:700;text-decoration:none;transition:all .2s}
.btn-ver-pend:hover{background:rgba(245,158,11,.25)}

@media(max-width:1100px){.charts-row,.bottom-row{grid-template-columns:1fr}}
@media(max-width:768px){
    main,main.min-main{margin-left:0}
    .body{padding:14px 12px 40px}
    .kpi-strip{grid-template-columns:repeat(2,1fr)}
    .filtros-panel{padding:11px 14px;gap:10px}
}
</style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main>

<!-- TOPBAR -->
<div class="topbar">
    <h1><i class="fas fa-chart-line" style="color:var(--orange)"></i> <span>Finanzas</span></h1>
    <div class="topbar-right">
        <button class="btn-icon" onclick="exportarExcel()" title="Exportar Excel">
            <i class="fas fa-file-excel" style="color:#22c55e"></i>
        </button>
        <button class="btn-icon" onclick="exportarPDF()" title="Imprimir / PDF">
            <i class="fas fa-file-pdf" style="color:#ef4444"></i>
        </button>
        <div class="btn-icon" id="btnRefresh" title="Actualizar"><i class="fas fa-rotate-right"></i></div>
        <a class="btn-icon" href="index.php?c=adminUsuarios&a=Index" title="Clientes"><i class="fas fa-users"></i></a>
        <a class="btn-icon" href="index.php?c=admin&a=Inicio"        title="Dashboard"><i class="fas fa-grid-2"></i></a>
        <a class="btn-icon" href="index.php?c=login&a=Logout"        title="Salir" style="color:#ef4444"><i class="fas fa-right-from-bracket"></i></a>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════
     PANEL DE FILTROS DE FECHA
════════════════════════════════════════════════════════════════════════ -->
<div class="filtros-panel">

    <!-- Presets rápidos -->
    <div class="fp-grp">
        <div class="fp-lbl">Período rápido</div>
        <div class="fp-presets">
            <button class="fp-preset" data-p="hoy"       onclick="preset(this)">Hoy</button>
            <button class="fp-preset" data-p="semana"    onclick="preset(this)">Esta semana</button>
            <button class="fp-preset activo" data-p="mes" onclick="preset(this)">Este mes</button>
            <button class="fp-preset" data-p="trimestre" onclick="preset(this)">Trimestre</button>
            <button class="fp-preset" data-p="anio"      onclick="preset(this)">Este año</button>
            <button class="fp-preset" data-p="todo"      onclick="preset(this)">Todo</button>
        </div>
    </div>

    <div class="fp-sep"></div>

    <!-- Selector Mes + Año -->
    <div class="fp-grp">
        <div class="fp-lbl">Mes</div>
        <select class="fp-sel" id="selMes" style="width:126px">
            <option value="">— mes —</option>
            <option value="01">Enero</option>   <option value="02">Febrero</option>
            <option value="03">Marzo</option>   <option value="04">Abril</option>
            <option value="05">Mayo</option>    <option value="06">Junio</option>
            <option value="07">Julio</option>   <option value="08">Agosto</option>
            <option value="09">Septiembre</option> <option value="10">Octubre</option>
            <option value="11">Noviembre</option>  <option value="12">Diciembre</option>
        </select>
    </div>
    <div class="fp-grp">
        <div class="fp-lbl">Año</div>
        <select class="fp-sel" id="selAnio" style="width:96px">
            <option value="">— año —</option>
            <!-- llenado dinámicamente desde AniosDisponibles -->
        </select>
    </div>

    <div class="fp-sep"></div>

    <!-- Rango personalizado desde–hasta -->
    <div class="fp-grp">
        <div class="fp-lbl">Desde</div>
        <input type="date" class="fp-inp" id="inpDesde" style="width:148px">
    </div>
    <div class="fp-grp">
        <div class="fp-lbl">Hasta</div>
        <input type="date" class="fp-inp" id="inpHasta" style="width:148px">
    </div>

    <div class="fp-sep"></div>

    <button class="btn-aplicar" onclick="aplicar()">
        <i class="fas fa-filter"></i> Aplicar
    </button>
    <button class="btn-limpiar" onclick="limpiar()">
        <i class="fas fa-rotate-left"></i>
    </button>

    <!-- Pill del filtro activo -->
    <div id="fpPill"></div>

</div>
<!-- /filtros-panel -->

<div class="body">

    <!-- BANNER PENDIENTE -->
    <div class="pend-banner" id="pendBanner" style="display:none">
        <div class="pend-left">
            <div class="pend-ico"><i class="fas fa-clock"></i></div>
            <div class="pend-txt">Hay <strong id="pendCount">0</strong> pagos esperando verificación por un total de</div>
        </div>
        <div class="pend-monto">Bs. <span id="pendMonto">0</span></div>
        <a class="btn-ver-pend" href="index.php?c=adminUsuarios&a=Index">
            <i class="fas fa-arrow-right"></i> Ver pagos
        </a>
    </div>

    <!-- KPI STRIP -->
    <div class="kpi-strip">
        <div class="kpi-card" style="--kpi-color:var(--green)">
            <div class="kpi-top">
                <div class="kpi-ico" style="background:rgba(34,197,94,.12)"><i class="fas fa-bolivar-sign" style="color:var(--green)"></i></div>
                <span class="kpi-trend" style="background:rgba(34,197,94,.12);color:var(--green)" id="trendTotal">—</span>
            </div>
            <div class="kpi-val"><sup>Bs.</sup><span id="kpiTotal">—</span></div>
            <div class="kpi-lbl" id="lblTotal">Ingresos confirmados totales</div>
        </div>
        <div class="kpi-card" style="--kpi-color:var(--orange)">
            <div class="kpi-top">
                <div class="kpi-ico" style="background:rgba(229,90,43,.12)"><i class="fas fa-calendar-check" style="color:var(--orange)"></i></div>
                <span class="kpi-trend" style="background:rgba(229,90,43,.12);color:var(--orange)" id="trendMes">este mes</span>
            </div>
            <div class="kpi-val"><sup>Bs.</sup><span id="kpiMes">—</span></div>
            <div class="kpi-lbl" id="lblMes">Ingresos del mes actual</div>
        </div>
        <div class="kpi-card" style="--kpi-color:var(--blue)">
            <div class="kpi-top">
                <div class="kpi-ico" style="background:rgba(56,189,248,.1)"><i class="fas fa-receipt" style="color:var(--blue)"></i></div>
                <span class="kpi-trend" style="background:rgba(56,189,248,.1);color:var(--blue)" id="trendPagos">—</span>
            </div>
            <div class="kpi-val"><span id="kpiPagos">—</span></div>
            <div class="kpi-lbl">Pagos confirmados</div>
        </div>
        <div class="kpi-card" style="--kpi-color:var(--amber)">
            <div class="kpi-top">
                <div class="kpi-ico" style="background:rgba(245,158,11,.12)"><i class="fas fa-sun" style="color:var(--amber)"></i></div>
                <span class="kpi-trend" style="background:rgba(245,158,11,.12);color:var(--amber)">hoy</span>
            </div>
            <div class="kpi-val"><sup>Bs.</sup><span id="kpiHoy">—</span></div>
            <div class="kpi-lbl">Ingresos de hoy</div>
        </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-header">
                <div>
                    <div class="chart-title">Evolución de ingresos</div>
                    <div class="chart-sub" id="chartSub">Últimos 30 días</div>
                </div>
                <div class="chart-tabs">
                    <button class="chart-tab active" data-rango="30"    onclick="cambiarRango(this,30)">30d</button>
                    <button class="chart-tab"        data-rango="60"    onclick="cambiarRango(this,60)">60d</button>
                    <button class="chart-tab"        data-rango="90"    onclick="cambiarRango(this,90)">90d</button>
                    <button class="chart-tab"        data-rango="meses" onclick="cambiarRango(this,'meses')">Meses</button>
                </div>
            </div>
            <canvas id="chartPrincipal"></canvas>
        </div>
        <div class="chart-card" style="padding:0;overflow:hidden">
            <div class="section-header">
                <div class="section-title"><i class="fas fa-layer-group" style="color:var(--orange)"></i> Ingresos por plan</div>
            </div>
            <div id="planList"></div>
        </div>
    </div>

    <!-- FILA INFERIOR -->
    <div class="bottom-row">
        <div class="tabla-wrap">
            <div class="section-header">
                <div class="section-title"><i class="fas fa-check-circle" style="color:var(--green)"></i> Pagos confirmados</div>
                <span id="totalPagosLbl" style="font-size:.72rem;color:var(--muted)"></span>
            </div>
            <table class="fin-table">
                <thead>
                    <tr><th>Cliente</th><th>Plan</th><th>Monto</th><th>Motivo</th><th>Fecha</th><th>Origen</th></tr>
                </thead>
                <tbody id="tbodyPagos">
                    <tr><td colspan="6" style="padding:24px"><div class="skeleton" style="height:16px"></div></td></tr>
                </tbody>
            </table>
            <div class="paginacion">
                <span id="pagInfoFin">Cargando…</span>
                <div class="pag-btns" id="pagBtnsFin"></div>
            </div>
        </div>
        <div class="tabla-wrap">
            <div class="section-header">
                <div class="section-title"><i class="fas fa-trophy" style="color:var(--amber)"></i> Top clientes por gasto</div>
            </div>
            <div id="topClientesList">
                <div style="padding:20px"><div class="skeleton" style="height:160px"></div></div>
            </div>
        </div>
    </div>

</div><!-- /body -->
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// ═══════════════════════════════════════════════════════════════════════════
//  ESTADO GLOBAL
// ═══════════════════════════════════════════════════════════════════════════
const pagFin = { page:1, total:0, pages:1 };
let filtro    = {};          // { desde:'YYYY-MM-DD', hasta:'YYYY-MM-DD' }
let rangoGraf = 30;          // 30 | 60 | 90 | 'meses'

// ═══════════════════════════════════════════════════════════════════════════
//  HELPERS
// ═══════════════════════════════════════════════════════════════════════════
const fmt2   = n => String(n).padStart(2,'0');
const hoy    = () => new Date();
const isoDay = d => d.toISOString().slice(0,10);

function bs(n)  { return parseFloat(n||0).toFixed(2) }
function ini(n,a){ return ((n?.[0]||'')+(a?.[0]||'')).toUpperCase()||'?' }
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') }
function fmtF(f){
    if(!f) return '<span style="color:var(--muted)">—</span>';
    const d=new Date(f);
    return d.toLocaleDateString('es-ES',{day:'2-digit',month:'short',year:'numeric'})+
           ' <span style="color:var(--muted);font-size:.7rem">'+
           d.toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit'})+'</span>';
}
function periodoBadge(p){
    const m={weekly:'b-blue',monthly:'b-green',yearly:'b-purple'};
    const l={weekly:'Semanal',monthly:'Mensual',yearly:'Anual'};
    return `<span class="badge ${m[p]||'b-muted'}">${l[p]||p||'—'}</span>`;
}
function motivoBadge(m){
    const c={nuevo:{cls:'b-green',lbl:'Nuevo'},renovacion:{cls:'b-blue',lbl:'Renovación'},upgrade:{cls:'b-purple',lbl:'Upgrade'}};
    const x=c[m]||{cls:'b-muted',lbl:m||'—'};
    return `<span class="badge ${x.cls}">${x.lbl}</span>`;
}

// Construir query-string con el filtro activo
function qsFin(extra){
    const p = {...filtro,...(extra||{})};
    const s = new URLSearchParams();
    if(p.desde) s.set('desde',p.desde);
    if(p.hasta) s.set('hasta',p.hasta);
    return s.toString() ? '&'+s.toString() : '';
}

// ═══════════════════════════════════════════════════════════════════════════
//  PANEL DE FILTROS — lógica
// ═══════════════════════════════════════════════════════════════════════════

// Cargar años disponibles desde BD
function cargarAnios(){
    fetch('index.php?c=adminFinanzas&a=AniosDisponibles')
    .then(r=>r.json())
    .then(d=>{
        const sel = document.getElementById('selAnio');
        const lista = (d.anios&&d.anios.length) ? d.anios : [(new Date()).getFullYear()];
        lista.forEach(a=>{
            const o = document.createElement('option');
            o.value = a; o.textContent = a;
            sel.appendChild(o);
        });
        // Seleccionar el año actual por defecto
        sel.value = String((new Date()).getFullYear());
    })
    .catch(()=>{
        // Fallback: últimos 4 años
        const sel = document.getElementById('selAnio');
        const y   = (new Date()).getFullYear();
        for(let i=0;i<4;i++){
            const o=document.createElement('option');
            o.value=y-i; o.textContent=y-i;
            sel.appendChild(o);
        }
        sel.value = String(y);
    });
}

// Quitar active de todos los presets
function limpiarPresets(){ document.querySelectorAll('.fp-preset').forEach(b=>b.classList.remove('activo')) }

// Presets rápidos
function preset(btn){
    limpiarPresets();
    btn.classList.add('activo');
    const p   = btn.dataset.p;
    const h   = hoy();
    let desde = null, hasta = isoDay(h);

    if     (p==='hoy')       { desde = isoDay(h) }
    else if(p==='semana')    { const l=new Date(h); l.setDate(h.getDate()-h.getDay()); desde=isoDay(l) }
    else if(p==='mes')       { desde = `${h.getFullYear()}-${fmt2(h.getMonth()+1)}-01` }
    else if(p==='trimestre') { const l=new Date(h); l.setMonth(h.getMonth()-2); l.setDate(1); desde=isoDay(l) }
    else if(p==='anio')      { desde = `${h.getFullYear()}-01-01` }
    else if(p==='todo')      { desde=null; hasta=null }

    document.getElementById('inpDesde').value = desde||'';
    document.getElementById('inpHasta').value = hasta||'';
    document.getElementById('selMes').value   = '';
    document.getElementById('selAnio').value  = '';

    filtro = desde ? {desde, hasta:hasta||''} : {};
    actualizarPill();
    pagFin.page=1;
    cargarTodo();
}

// Botón "Aplicar" — Lee mes+año O rango libre
function aplicar(){
    const mes   = document.getElementById('selMes').value;
    const anio  = document.getElementById('selAnio').value;
    const desde = document.getElementById('inpDesde').value;
    const hasta = document.getElementById('inpHasta').value;

    limpiarPresets();

    if(mes && anio){
        // Mes específico de un año
        const primerDia = `${anio}-${mes}-01`;
        // Último día del mes
        const ultimoDia = isoDay(new Date(parseInt(anio), parseInt(mes), 0));
        filtro = {desde:primerDia, hasta:ultimoDia};
        document.getElementById('inpDesde').value = primerDia;
        document.getElementById('inpHasta').value = ultimoDia;

    } else if(anio && !mes){
        // Año completo
        filtro = {desde:`${anio}-01-01`, hasta:`${anio}-12-31`};
        document.getElementById('inpDesde').value = filtro.desde;
        document.getElementById('inpHasta').value = filtro.hasta;

    } else if(desde || hasta){
        // Rango libre
        filtro = {};
        if(desde) filtro.desde = desde;
        if(hasta) filtro.hasta = hasta;
        document.getElementById('selMes').value  = '';
        document.getElementById('selAnio').value = '';

    } else {
        filtro = {};
    }

    actualizarPill();
    pagFin.page=1;
    cargarTodo();
}

// Limpiar → volver al mes actual (preset por defecto)
function limpiar(){
    document.getElementById('selMes').value  = '';
    document.getElementById('selAnio').value = '';
    document.getElementById('inpDesde').value = '';
    document.getElementById('inpHasta').value = '';
    limpiarPresets();
    // Activar preset "mes"
    const btnMes = document.querySelector('[data-p="mes"]');
    if(btnMes) preset(btnMes);
}

// Muestra u oculta la pill del filtro activo
function actualizarPill(){
    const pill = document.getElementById('fpPill');
    if(!filtro.desde && !filtro.hasta){ pill.innerHTML=''; return; }

    const fmtP = s => {
        if(!s) return '—';
        const [y,m,d] = s.split('-');
        const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        return `${parseInt(d)} ${MESES[parseInt(m)-1]} ${y}`;
    };
    const dLabel = fmtP(filtro.desde);
    const hLabel = fmtP(filtro.hasta);
    const txt    = (filtro.desde===filtro.hasta) ? dLabel : `${dLabel} — ${hLabel}`;

    pill.innerHTML = `<div class="fp-pill">
        <i class="fas fa-calendar-range" style="font-size:.7rem"></i>
        <span>${txt}</span>
        <i class="fas fa-xmark fp-pill-x" onclick="limpiar()" title="Quitar filtro"></i>
    </div>`;

    // Actualizar etiquetas dinámicas de KPIs
    document.getElementById('lblTotal').textContent = `Ingresos ${txt}`;
    document.getElementById('lblMes').textContent   = 'En el período';
}

// ═══════════════════════════════════════════════════════════════════════════
//  KPIs
// ═══════════════════════════════════════════════════════════════════════════
function cargarKpis(){
    fetch('index.php?c=adminFinanzas&a=Kpis'+qsFin())
    .then(r=>r.json())
    .then(d=>{
        if(!d.success) return;
        const k=d.kpis;
        document.getElementById('kpiTotal').textContent = bs(k.total_confirmado);
        document.getElementById('kpiMes').textContent   = bs(k.monto_mes);
        document.getElementById('kpiPagos').textContent = k.total_pagos;
        document.getElementById('kpiHoy').textContent   = bs(k.monto_hoy);
        document.getElementById('trendTotal').textContent = k.total_pagos+' pagos';
        document.getElementById('trendPagos').textContent = k.pagos_hoy+' hoy';

        // Restaurar etiquetas si no hay filtro activo
        if(!filtro.desde){
            document.getElementById('lblTotal').textContent = 'Ingresos confirmados totales';
            document.getElementById('lblMes').textContent   = 'Ingresos del mes actual';
        }

        if(k.pagos_pendientes>0){
            document.getElementById('pendCount').textContent = k.pagos_pendientes;
            document.getElementById('pendMonto').textContent = bs(k.total_pendiente);
            document.getElementById('pendBanner').style.display = 'flex';
        } else {
            document.getElementById('pendBanner').style.display = 'none';
        }
    }).catch(()=>{});
}

// ═══════════════════════════════════════════════════════════════════════════
//  GRÁFICO PRINCIPAL
// ═══════════════════════════════════════════════════════════════════════════
let chart = null;

function renderChart(labels, data, label){
    const ctx = document.getElementById('chartPrincipal').getContext('2d');
    if(chart) chart.destroy();
    const grad = ctx.createLinearGradient(0,0,0,220);
    grad.addColorStop(0,'rgba(229,90,43,.35)');
    grad.addColorStop(1,'rgba(229,90,43,0)');
    chart = new Chart(ctx,{
        type:'line',
        data:{labels,datasets:[{label,data,borderColor:'#e55a2b',backgroundColor:grad,
            borderWidth:2.5,pointRadius:3,pointBackgroundColor:'#e55a2b',tension:.4,fill:true}]},
        options:{
            responsive:true,maintainAspectRatio:true,
            plugins:{legend:{display:false},tooltip:{backgroundColor:'#1c1c32',
                borderColor:'rgba(229,90,43,.3)',borderWidth:1,
                titleColor:'#f0f0f5',bodyColor:'#f59e0b',
                callbacks:{label:c=>' Bs. '+bs(c.parsed.y)}}},
            scales:{
                x:{grid:{color:'rgba(255,255,255,.04)'},ticks:{color:'rgba(240,240,245,.4)',font:{size:10},maxTicksLimit:10}},
                y:{grid:{color:'rgba(255,255,255,.04)'},ticks:{color:'rgba(240,240,245,.4)',font:{size:10},callback:v=>'Bs. '+v}}
            }
        }
    });
}

function cargarGraficoDias(dias){
    const subTxt = filtro.desde
        ? `${filtro.desde} → ${filtro.hasta||'hoy'}`
        : `Últimos ${dias} días`;
    document.getElementById('chartSub').textContent = subTxt;
    fetch(`index.php?c=adminFinanzas&a=GraficoDias&dias=${dias}${qsFin()}`)
    .then(r=>r.json())
    .then(d=>{
        if(!d.success) return;
        const labels = d.data.map(r=>{
            const dt=new Date(r.fecha+'T00:00:00');
            return dt.toLocaleDateString('es-ES',{day:'2-digit',month:'short'});
        });
        renderChart(labels,d.data.map(r=>parseFloat(r.monto)),'Ingresos Bs.');
    }).catch(()=>{});
}

function cargarGraficoMeses(){
    document.getElementById('chartSub').textContent='Por meses';
    fetch('index.php?c=adminFinanzas&a=GraficoMeses'+qsFin())
    .then(r=>r.json())
    .then(d=>{
        if(!d.success) return;
        const MESES=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        const labels = d.data.map(r=>{
            const [y,m]=r.mes.split('-');
            return MESES[parseInt(m)-1]+' '+y.slice(2);
        });
        renderChart(labels,d.data.map(r=>parseFloat(r.monto)),'Ingresos Bs.');
    }).catch(()=>{});
}

function cambiarRango(btn, rango){
    document.querySelectorAll('.chart-tab').forEach(t=>t.classList.remove('active'));
    btn.classList.add('active');
    rangoGraf = rango;
    rango==='meses' ? cargarGraficoMeses() : cargarGraficoDias(rango);
}

// ═══════════════════════════════════════════════════════════════════════════
//  PLANES
// ═══════════════════════════════════════════════════════════════════════════
function cargarPlanes(){
    fetch('index.php?c=adminFinanzas&a=PorPlan'+qsFin())
    .then(r=>r.json())
    .then(d=>{
        if(!d.success||!d.planes.length){
            document.getElementById('planList').innerHTML=
                '<div class="empty-fin"><i class="fas fa-chart-pie"></i><p>Sin datos para el período</p></div>';
            return;
        }
        const max = Math.max(...d.planes.map(p=>p.monto));
        const pCls={weekly:'b-blue',monthly:'b-green',yearly:'b-purple'};
        const pLbl={weekly:'Semanal',monthly:'Mensual',yearly:'Anual'};
        document.getElementById('planList').innerHTML = d.planes.map(p=>{
            const pct = max>0 ? (p.monto/max*100).toFixed(1) : 0;
            return `<div class="plan-item">
                <div class="plan-top">
                    <div>
                        <span style="font-size:.8rem;font-weight:700;color:#fff">${esc(p.plan)||'—'}</span>
                        <span class="badge ${pCls[p.periodo]||'b-muted'}" style="margin-left:6px">${pLbl[p.periodo]||p.periodo||'—'}</span>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:.85rem;font-weight:900;color:var(--green)">Bs. ${bs(p.monto)}</div>
                        <div style="font-size:.65rem;color:var(--muted)">${p.cantidad} pago${p.cantidad!==1?'s':''}</div>
                    </div>
                </div>
                <div class="plan-bar-wrap"><div class="plan-bar" style="width:${pct}%"></div></div>
            </div>`;
        }).join('');
    }).catch(()=>{});
}

// ═══════════════════════════════════════════════════════════════════════════
//  TABLA PAGOS
// ═══════════════════════════════════════════════════════════════════════════
function cargarPagos(){
    document.getElementById('tbodyPagos').innerHTML=
        '<tr><td colspan="6" style="padding:22px"><div class="skeleton" style="height:14px"></div></td></tr>';
    fetch(`index.php?c=adminFinanzas&a=Pagos&page=${pagFin.page}${qsFin()}`)
    .then(r=>r.json())
    .then(d=>{
        if(!d.success||!d.pagos.length){
            document.getElementById('tbodyPagos').innerHTML=
                '<tr><td colspan="6"><div class="empty-fin"><i class="fas fa-receipt"></i><p>Sin pagos en este período</p></div></td></tr>';
            document.getElementById('totalPagosLbl').textContent='0 registros';
            document.getElementById('pagInfoFin').textContent='Sin resultados';
            document.getElementById('pagBtnsFin').innerHTML='';
            return;
        }
        pagFin.total=d.total; pagFin.pages=d.pages;
        document.getElementById('totalPagosLbl').textContent=d.total+' en total';
        document.getElementById('tbodyPagos').innerHTML=d.pagos.map(p=>`
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span class="av">${ini(p.nombre,p.apellido)}</span>
                        <div>
                            <div style="font-weight:700;font-size:.78rem">${esc(p.nombre)} ${esc(p.apellido)}</div>
                            <div style="font-size:.67rem;color:var(--muted)">${esc(p.email)}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-size:.78rem;font-weight:700;color:var(--orange)">${esc(p.plan)||'—'}</div>
                    <div style="margin-top:2px">${periodoBadge(p.periodo)}</div>
                </td>
                <td style="font-size:.92rem;font-weight:900;color:var(--green)">Bs. ${bs(p.monto)}</td>
                <td>${motivoBadge(p.motivo)}</td>
                <td style="font-size:.73rem">${fmtF(p.fecha_confirmacion)}</td>
                <td>
                    <span class="badge ${p.origen==='activo'?'b-green':'b-muted'}">
                        <i class="fas fa-${p.origen==='activo'?'circle-check':'clock-rotate-left'}"></i>
                        ${p.origen==='activo'?'Activo':'Historial'}
                    </span>
                </td>
            </tr>`).join('');
        renderPagFin();
    }).catch(()=>{});
}

function renderPagFin(){
    const {page,total,pages}=pagFin;
    document.getElementById('pagInfoFin').textContent=`Pág. ${page} de ${pages} · ${total} pago${total!==1?'s':''}`;
    let b=`<button class="pag-btn" onclick="irPag(${page-1})" ${page<=1?'disabled':''}>‹</button>`;
    for(let i=Math.max(1,page-2);i<=Math.min(pages,page+2);i++)
        b+=`<button class="pag-btn ${i===page?'active':''}" onclick="irPag(${i})">${i}</button>`;
    b+=`<button class="pag-btn" onclick="irPag(${page+1})" ${page>=pages?'disabled':''}>›</button>`;
    document.getElementById('pagBtnsFin').innerHTML=b;
}
function irPag(p){ if(p<1||p>pagFin.pages) return; pagFin.page=p; cargarPagos() }

// ═══════════════════════════════════════════════════════════════════════════
//  TOP CLIENTES
// ═══════════════════════════════════════════════════════════════════════════
function cargarTopClientes(){
    fetch('index.php?c=adminFinanzas&a=TopClientes'+qsFin())
    .then(r=>r.json())
    .then(d=>{
        if(!d.success||!d.clientes.length){
            document.getElementById('topClientesList').innerHTML=
                '<div class="empty-fin"><i class="fas fa-trophy"></i><p>Sin datos para este período</p></div>';
            return;
        }
        document.getElementById('topClientesList').innerHTML=d.clientes.map((c,i)=>{
            const rCls=i===0?'top1':i===1?'top2':i===2?'top3':'';
            const rIco=i===0?'🥇':i===1?'🥈':i===2?'🥉':(i+1);
            return `<div class="cliente-item">
                <div class="cli-rank ${rCls}">${rIco}</div>
                <span class="av">${ini(c.nombre,c.apellido)}</span>
                <div class="cli-info">
                    <div class="cli-name">${esc(c.nombre)} ${esc(c.apellido)}</div>
                    <div class="cli-email">${esc(c.email)}</div>
                </div>
                <div class="cli-right">
                    <div class="cli-total">Bs. ${bs(c.total)}</div>
                    <div class="cli-pagos">${c.pagos} pago${c.pagos!==1?'s':''}</div>
                </div>
            </div>`;
        }).join('');
    }).catch(()=>{});
}

// ═══════════════════════════════════════════════════════════════════════════
//  CARGAR TODO (usado en init + filtro + refresh)
// ═══════════════════════════════════════════════════════════════════════════
function cargarTodo(){
    cargarKpis();
    rangoGraf==='meses' ? cargarGraficoMeses() : cargarGraficoDias(rangoGraf);
    cargarPlanes();
    cargarPagos();
    cargarTopClientes();
}

document.getElementById('btnRefresh').addEventListener('click',()=>{ pagFin.page=1; cargarTodo() });

// ═══════════════════════════════════════════════════════════════════════════
//  EXPORTAR EXCEL (respeta el filtro activo)
// ═══════════════════════════════════════════════════════════════════════════
async function exportarExcel(){
    const btn=document.querySelector('[onclick="exportarExcel()"]');
    btn.innerHTML='<i class="fas fa-spinner fa-spin" style="color:#22c55e"></i>';
    btn.disabled=true;
    try{
        const res=await fetch(`index.php?c=adminFinanzas&a=Pagos&page=1&per_page=9999${qsFin()}`);
        const d=await res.json();
        if(!d.success||!d.pagos.length){alert('Sin datos para exportar');return;}
        const filas=d.pagos.map((p,i)=>({
            '#':i+1,'Nombre':(p.nombre||'')+' '+(p.apellido||''),
            'Email':p.email||'','Plan':p.plan||'','Período':p.periodo||'',
            'Monto (Bs.)':parseFloat(p.monto||0),'Motivo':p.motivo||'',
            'Fecha pago':p.fecha_pago||'','Confirmado el':p.fecha_confirmacion||'',
            'Aprobado por':p.aprobado_por?'Admin #'+p.aprobado_por:'',
            'Origen':p.origen==='activo'?'Pago activo':'Historial',
        }));
        const ws=XLSX.utils.json_to_sheet(filas);
        ws['!cols']=[{wch:4},{wch:22},{wch:28},{wch:20},{wch:10},{wch:12},{wch:12},{wch:20},{wch:20},{wch:14},{wch:12}];

        // Nombre de hoja con el rango
        const rangoNombre = filtro.desde
            ? `${filtro.desde} a ${filtro.hasta||'hoy'}`
            : 'Todo el historial';

        const wb=XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb,ws,`Pagos (${rangoNombre})`);

        // Segunda hoja: KPIs del período
        const kRes=await fetch('index.php?c=adminFinanzas&a=Kpis'+qsFin());
        const kD=await kRes.json();
        if(kD.success){
            const k=kD.kpis;
            const resumen=[
                {'KPI':'Período filtrado','Valor':rangoNombre},
                {'KPI':'Total confirmado (Bs.)','Valor':k.total_confirmado},
                {'KPI':'Ingresos del mes (Bs.)','Valor':k.monto_mes},
                {'KPI':'Ingresos de hoy (Bs.)','Valor':k.monto_hoy},
                {'KPI':'Total pagos confirmados','Valor':k.total_pagos},
                {'KPI':'Pagos pendientes','Valor':k.pagos_pendientes},
                {'KPI':'Monto pendiente (Bs.)','Valor':k.total_pendiente},
            ];
            const wsR=XLSX.utils.json_to_sheet(resumen);
            wsR['!cols']=[{wch:28},{wch:22}];
            XLSX.utils.book_append_sheet(wb,wsR,'KPIs del período');
        }

        const fecha=new Date().toISOString().slice(0,10);
        XLSX.writeFile(wb,`etreva_finanzas_${fecha}.xlsx`);
    }catch(e){console.error(e);alert('Error al exportar');}
    finally{btn.innerHTML='<i class="fas fa-file-excel" style="color:#22c55e"></i>';btn.disabled=false;}
}

// ═══════════════════════════════════════════════════════════════════════════
//  EXPORTAR PDF
// ═══════════════════════════════════════════════════════════════════════════
function exportarPDF(){
    let hdr=document.getElementById('printHeader');
    if(!hdr){
        hdr=document.createElement('div');
        hdr.id='printHeader'; hdr.className='print-header';
        const rangoTxt = filtro.desde
            ? ` · ${filtro.desde} → ${filtro.hasta||'hoy'}`
            : '';
        hdr.innerHTML=`<h2>🏔️ Etreva Travel — Reporte de Finanzas${rangoTxt}</h2>
            <p>Generado el ${new Date().toLocaleDateString('es-ES',{day:'2-digit',month:'long',year:'numeric'})}</p>`;
        document.querySelector('.body').prepend(hdr);
    }
    window.print();
}

// ═══════════════════════════════════════════════════════════════════════════
//  ARRANQUE — por defecto carga "este mes"
// ═══════════════════════════════════════════════════════════════════════════
(function init(){
    // Preseleccionar el mes y año actual en los selects
    const h = new Date();
    document.getElementById('selMes').value = fmt2(h.getMonth()+1);

    // Cargar años y después activar preset mes
    cargarAnios();

    // Aplicar preset "este mes" al arrancar
    const btnMes = document.querySelector('[data-p="mes"]');
    if(btnMes) preset(btnMes);

    // Sincronizar sidebar colapsado
    const main = document.querySelector('main');
    if(document.body.classList.contains('sidebar-collapsed') && main) main.classList.add('min-main');
})();
</script>
</body>
</html>