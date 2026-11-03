<?php include 'view/menu.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin · Back-Testing & Modelos ML</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
:root {
    --bg:      #0e1120;
    --bg2:     #13172b;
    --surf:    #1a1f38;
    --surf2:   #222845;
    --border:  rgba(255,255,255,0.07);
    --red:     #ef4444;
    --amber:   #f59e0b;
    --green:   #10b981;
    --blue:    #6366f1;
    --text:    #e2e8f0;
    --muted:   rgba(255,255,255,0.4);
    --r:       14px;
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{background:var(--bg)!important;color:var(--text)!important;font-family:'DM Sans',sans-serif;min-height:100vh}
main{margin-left:280px;padding:0;background:var(--bg)!important;min-height:100vh;transition:all .4s}
main.min-main{margin-left:85px}
@media(max-width:768px){main{margin-left:0!important}}

/* ─── HEADER ─── */
.adm-header{
    background:linear-gradient(135deg,#0a0d1a 0%,#1a1040 60%,var(--bg) 100%);
    padding:40px 28px 28px;
    border-bottom:1px solid var(--border);
    position:relative;overflow:hidden;
}
.adm-header::before{
    content:'';position:absolute;top:-100px;right:-100px;
    width:500px;height:500px;
    background:radial-gradient(circle,rgba(99,102,241,.12) 0%,transparent 70%);
    pointer-events:none;
}
.adm-header h1{
    font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;
    background:linear-gradient(135deg,#fff 0%,#a5b4fc 100%);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    background-clip:text;margin-bottom:4px;
}
.adm-header p{color:var(--muted);font-size:.9rem}
.adm-header .badge-rol{
    display:inline-flex;align-items:center;gap:6px;
    background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);
    color:#fca5a5;border-radius:50px;padding:4px 14px;font-size:.72rem;font-weight:700;
}

/* ─── TABS ─── */
.adm-tabs{
    display:flex;gap:4px;padding:20px 28px 0;
    border-bottom:1px solid var(--border);background:var(--bg2);
}
.adm-tab{
    padding:10px 20px;border-radius:10px 10px 0 0;
    background:transparent;border:none;color:var(--muted);
    font-size:.82rem;font-weight:700;cursor:pointer;
    transition:all .2s;display:flex;align-items:center;gap:7px;
}
.adm-tab:hover{color:var(--text);background:rgba(255,255,255,.04)}
.adm-tab.active{
    background:var(--surf);color:var(--text);
    border-top:2px solid var(--blue);
}
.adm-tab .badge-cnt{
    background:rgba(239,68,68,.85);color:#fff;
    border-radius:50px;padding:1px 7px;font-size:.62rem;
}

/* ─── CONTENIDO ─── */
.adm-body{padding:24px 28px 60px}
.tab-pane{display:none}
.tab-pane.active{display:block}

/* ─── KPI GRID ─── */
.kpi-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(170px,1fr));
    gap:14px;margin-bottom:28px;
}
.kpi-card{
    background:var(--surf);border:1px solid var(--border);
    border-radius:var(--r);padding:18px 20px;
    transition:border-color .2s,transform .2s;
}
.kpi-card:hover{border-color:rgba(99,102,241,.3);transform:translateY(-2px)}
.kpi-card .ico{
    width:40px;height:40px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-size:.95rem;margin-bottom:12px;
}
.ico-blue{background:rgba(99,102,241,.12);color:#818cf8}
.ico-green{background:rgba(16,185,129,.12);color:var(--green)}
.ico-red{background:rgba(239,68,68,.12);color:var(--red)}
.ico-amber{background:rgba(245,158,11,.12);color:var(--amber)}
.ico-purple{background:rgba(168,85,247,.12);color:#c084fc}
.ico-cyan{background:rgba(6,182,212,.12);color:#22d3ee}
.kpi-val{
    font-family:'Syne',sans-serif;font-size:1.75rem;font-weight:800;
    color:#fff;line-height:1;margin-bottom:4px;
}
.kpi-lbl{font-size:.72rem;color:var(--muted)}
.kpi-trend{font-size:.7rem;margin-top:5px}
.trend-up{color:var(--green)} .trend-dn{color:var(--red)}

/* ─── FILTRO BAR ─── */
.filter-bar{
    background:var(--surf);border:1px solid var(--border);
    border-radius:var(--r);padding:16px 20px;
    display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;
    margin-bottom:20px;
}
.filter-bar label{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;font-weight:700;display:block;margin-bottom:4px}
.filter-bar select,.filter-bar input[type=text],.filter-bar input[type=number]{
    background:var(--bg2);border:1px solid var(--border);color:var(--text);
    border-radius:8px;padding:7px 12px;font-size:.78rem;
    outline:none;transition:border-color .2s;
}
.filter-bar select:focus,.filter-bar input:focus{border-color:rgba(99,102,241,.5)}
.btn-filtrar{
    background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.3);
    color:#818cf8;border-radius:8px;padding:7px 16px;font-size:.78rem;
    font-weight:700;cursor:pointer;transition:all .2s;
    display:flex;align-items:center;gap:6px;
}
.btn-filtrar:hover{background:rgba(99,102,241,.25);border-color:rgba(99,102,241,.5)}
.btn-reset{
    background:rgba(255,255,255,.04);border:1px solid var(--border);
    color:var(--muted);border-radius:8px;padding:7px 12px;font-size:.78rem;
    cursor:pointer;transition:all .2s;
}
.btn-reset:hover{color:var(--text);border-color:rgba(255,255,255,.15)}

/* ─── TABLA FEEDBACKS ─── */
.tabla-wrap{
    background:var(--surf);border:1px solid var(--border);
    border-radius:var(--r);overflow:hidden;margin-bottom:20px;
}
.tabla-wrap table{width:100%;border-collapse:collapse}
.tabla-wrap thead tr{background:var(--surf2)}
.tabla-wrap thead th{
    padding:11px 14px;font-size:.68rem;text-transform:uppercase;
    letter-spacing:.6px;color:var(--muted);font-weight:700;
    border-bottom:1px solid var(--border);white-space:nowrap;
}
.tabla-wrap tbody tr{
    border-bottom:1px solid rgba(255,255,255,.04);
    transition:background .15s;
}
.tabla-wrap tbody tr:last-child{border-bottom:none}
.tabla-wrap tbody tr:hover{background:rgba(255,255,255,.025)}
.tabla-wrap tbody td{padding:10px 14px;font-size:.78rem;vertical-align:middle}

/* Tags y badges */
.tag-pill{
    display:inline-flex;align-items:center;
    background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);
    color:#a5b4fc;border-radius:50px;padding:2px 8px;
    font-size:.62rem;font-weight:700;text-transform:uppercase;margin:1px;
}
.tag-pill.rojo{background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.2);color:#fca5a5}
.badge-tipo{
    padding:3px 10px;border-radius:6px;font-size:.62rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.4px;
}
.tipo-pos{background:rgba(16,185,129,.12);color:#34d399;border:1px solid rgba(16,185,129,.2)}
.tipo-neg{background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.2)}
.tipo-neu{background:rgba(245,158,11,.12);color:#f59e0b;border:1px solid rgba(245,158,11,.25)}
.badge-estado{
    padding:3px 9px;border-radius:6px;font-size:.62rem;font-weight:700;
    text-transform:uppercase;
}
.est-completado{background:rgba(16,185,129,.1);color:#34d399}
.est-revisado{background:rgba(6,182,212,.1);color:#22d3ee}
.est-bloqueado{background:rgba(239,68,68,.1);color:#f87171}
.est-pendiente{background:rgba(245,158,11,.1);color:#fbbf24}

/* Ofensivo */
.fila-ofensiva td:first-child{border-left:3px solid var(--red)}
.icono-ofensivo{color:var(--red);font-size:.75rem}

/* Stars mini */
.stars-mini{display:flex;gap:1px;align-items:center}
.stars-mini i{font-size:.55rem;color:var(--amber)}
.stars-mini i.empty{color:rgba(255,255,255,.15)}
.stars-mini span{font-size:.7rem;color:#fff;font-weight:700;margin-left:4px}

/* Botones acción */
.btn-accion{
    padding:4px 9px;border-radius:6px;font-size:.65rem;font-weight:700;
    cursor:pointer;transition:all .15s;border:none;display:inline-flex;align-items:center;gap:4px;
}
.btn-revisar{background:rgba(6,182,212,.1);color:#22d3ee;border:1px solid rgba(6,182,212,.2)}
.btn-bloquear{background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.2)}
.btn-completar{background:rgba(16,185,129,.1);color:#34d399;border:1px solid rgba(16,185,129,.2)}
.btn-eliminar{background:rgba(239,68,68,.08);color:#ef4444;border:1px solid rgba(239,68,68,.15)}
.btn-accion:hover{filter:brightness(1.2);transform:translateY(-1px)}

/* ─── COMENTARIO EXPANDIBLE ─── */
.comentario-txt{
    max-width:300px;white-space:nowrap;overflow:hidden;
    text-overflow:ellipsis;cursor:pointer;color:var(--text);
    transition:color .15s;
}
.comentario-txt:hover{color:#fff;text-decoration:underline}

/* ─── PAGINACIÓN ─── */
.paginacion{
    display:flex;align-items:center;justify-content:space-between;
    padding:12px 16px;font-size:.75rem;color:var(--muted);
}
.page-btns{display:flex;gap:4px}
.page-btn{
    padding:5px 11px;background:var(--surf2);border:1px solid var(--border);
    color:var(--muted);border-radius:7px;cursor:pointer;transition:all .15s;
    font-size:.73rem;text-decoration:none;
}
.page-btn:hover,.page-btn.active{background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.3);color:#818cf8}
.page-btn.active{font-weight:700}

/* ─── MODELOS TABLA ─── */
.modelo-row{background:var(--surf2);border-radius:10px;padding:16px 18px;margin-bottom:10px;border:1px solid var(--border)}
.modelo-row:hover{border-color:rgba(99,102,241,.25)}
.modelo-header{display:flex;align-items:center;gap:14px;margin-bottom:10px}
.modelo-ico{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;background:rgba(99,102,241,.12);color:#818cf8;font-size:.9rem;flex-shrink:0}
.modelo-nombre{font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;color:#fff}
.modelo-ver{font-size:.7rem;color:var(--muted)}
.modelo-stats{display:flex;gap:18px;flex-wrap:wrap}
.mstat{text-align:center}
.mstat-val{font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;color:#fff}
.mstat-lbl{font-size:.63rem;color:var(--muted)}

/* Barra de tasa positiva */
.tasa-bar{height:5px;border-radius:3px;background:rgba(255,255,255,.06);overflow:hidden;margin-top:8px}
.tasa-fill{height:100%;border-radius:3px;background:linear-gradient(to right,var(--red),var(--amber),var(--green));transition:width .6s ease}

/* ─── GRÁFICAS ─── */
.graficas-grid{
    display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:24px;
}
@media(max-width:900px){.graficas-grid{grid-template-columns:1fr}}
.chart-box{
    background:var(--surf);border:1px solid var(--border);
    border-radius:var(--r);padding:20px;
}
.chart-box h4{
    font-family:'Syne',sans-serif;font-size:.85rem;font-weight:700;
    color:#fff;margin-bottom:16px;
    display:flex;align-items:center;gap:8px;
}
.chart-box h4 i{color:var(--blue);font-size:.8rem}
.chart-container{position:relative;height:220px}

/* ─── MEJORA BACKTESTING ─── */
.mejora-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px}
.mejora-card{
    background:var(--surf2);border-radius:10px;padding:14px 16px;
    text-align:center;border:1px solid var(--border);
}
.mejora-val{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;color:#fff}
.mejora-lbl{font-size:.68rem;color:var(--muted);margin-top:3px}

/* ─── MODAL DETALLE ─── */
.modal-content{background:var(--surf)!important;border:1px solid rgba(99,102,241,.25)!important;border-radius:16px!important}
.modal-header{border-bottom:1px solid var(--border)!important;padding:18px 22px!important}
.modal-footer{border-top:1px solid var(--border)!important}
.modal-title{font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;color:#fff!important}
.json-block{
    background:var(--bg);border:1px solid var(--border);
    border-radius:8px;padding:12px;font-size:.72rem;
    color:#a5f3fc;font-family:monospace;white-space:pre-wrap;
    max-height:180px;overflow-y:auto;
}
.vector-grid{
    display:grid;grid-template-columns:repeat(auto-fill,minmax(38px,1fr));gap:4px;
}
.vec-cell{
    background:var(--bg2);border-radius:5px;padding:4px 2px;
    text-align:center;font-size:.65rem;color:var(--muted);border:1px solid var(--border);
}
.vec-cell.activo{background:rgba(99,102,241,.15);color:#818cf8;border-color:rgba(99,102,241,.3);font-weight:700}
.vec-cell.reducido{background:rgba(239,68,68,.1);color:#fca5a5}

/* Scrollbar */
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:var(--bg)}
::-webkit-scrollbar-thumb{background:var(--surf2);border-radius:3px}
</style>
</head>

<body>
<main id="mainContent">

<?php
$tz = new DateTimeZone('America/La_Paz');
$ofensivoCount = $kpis['comentarios_ofensivos'] ?? 0;
?>

<!-- ─── HEADER ─── -->
<div class="adm-header">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h1>
                <i class="fas fa-brain me-2" style="font-size:1.5rem;color:#818cf8;-webkit-text-fill-color:#818cf8"></i>
                Back-Testing &amp; Modelos ML
            </h1>
            <p>Panel de administración · Feedback de usuarios · Rendimiento de modelos de recomendación</p>
        </div>
        <span class="badge-rol"><i class="fas fa-shield-alt"></i> Administrador</span>
    </div>
</div>

<!-- ─── TABS ─── -->
<div class="adm-tabs">
    <button class="adm-tab active" onclick="switchTab('dashboard',this)">
        <i class="fas fa-chart-line"></i> Dashboard
    </button>
    <button class="adm-tab" onclick="switchTab('feedbacks',this)">
        <i class="fas fa-comments"></i> Feedbacks
        <?php if ($ofensivoCount > 0): ?>
            <span class="badge-cnt"><?= $ofensivoCount ?></span>
        <?php endif; ?>
    </button>
    <button class="adm-tab" onclick="switchTab('modelos',this)">
        <i class="fas fa-robot"></i> Modelos ML
    </button>
    <button class="adm-tab" onclick="switchTab('mejora',this)">
        <i class="fas fa-arrow-trend-up"></i> Análisis Mejora
    </button>
</div>

<!-- ─── BODY ─── -->
<div class="adm-body">

<!-- ══════════ TAB: DASHBOARD ══════════ -->
<div class="tab-pane active" id="tab-dashboard">

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="ico ico-blue"><i class="fas fa-database"></i></div>
            <div class="kpi-val"><?= number_format($kpis['total_feedbacks'] ?? 0) ?></div>
            <div class="kpi-lbl">Total feedbacks</div>
        </div>
        <div class="kpi-card">
            <div class="ico ico-green"><i class="fas fa-thumbs-up"></i></div>
            <div class="kpi-val"><?= $kpis['positivos'] ?? 0 ?></div>
            <div class="kpi-lbl">Positivos</div>
        </div>
        <div class="kpi-card">
            <div class="ico ico-red"><i class="fas fa-thumbs-down"></i></div>
            <div class="kpi-val"><?= $kpis['negativos'] ?? 0 ?></div>
            <div class="kpi-lbl">Negativos</div>
        </div>
        <div class="kpi-card">
            <div class="ico ico-amber"><i class="fas fa-star-half-alt"></i></div>
            <div class="kpi-val"><?= $kpis['promedio_calif'] ?? '—' ?></div>
            <div class="kpi-lbl">Calificación promedio</div>
        </div>
        <div class="kpi-card" style="border-color:<?= $ofensivoCount>0?'rgba(239,68,68,.3)':'var(--border)' ?>">
            <div class="ico ico-red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="kpi-val" style="color:<?= $ofensivoCount>0?'#f87171':'#fff' ?>"><?= $ofensivoCount ?></div>
            <div class="kpi-lbl">Comentarios ofensivos</div>
        </div>
        <div class="kpi-card">
            <div class="ico ico-cyan"><i class="fas fa-calendar-day"></i></div>
            <div class="kpi-val"><?= $kpis['feedbacks_hoy'] ?? 0 ?></div>
            <div class="kpi-lbl">Feedbacks hoy</div>
        </div>
        <div class="kpi-card">
            <div class="ico ico-purple"><i class="fas fa-microchip"></i></div>
            <div class="kpi-val"><?= $kpis['modelos_activos'] ?? 0 ?></div>
            <div class="kpi-lbl">Modelos activos</div>
        </div>
        <div class="kpi-card">
            <div class="ico ico-green"><i class="fas fa-retweet"></i></div>
            <div class="kpi-val"><?= $kpis['con_ajuste'] ?? 0 ?></div>
            <div class="kpi-lbl">Con re-ajuste aplicado</div>
        </div>
    </div>

    <!-- Gráficas -->
    <div class="graficas-grid">
        <div class="chart-box" style="grid-column:1/-1">
            <h4><i class="fas fa-chart-area"></i> Evolución de calificaciones (30 días)</h4>
            <div class="chart-container"><canvas id="chartEvolucion"></canvas></div>
        </div>
        <div class="chart-box">
            <h4><i class="fas fa-chart-pie"></i> Distribución positivos / negativos</h4>
            <div class="chart-container"><canvas id="chartDonut"></canvas></div>
        </div>
        <div class="chart-box">
            <h4><i class="fas fa-tags"></i> Tags negativos más frecuentes</h4>
            <div class="chart-container"><canvas id="chartTags"></canvas></div>
        </div>
    </div>

</div><!-- /tab-dashboard -->

<!-- ══════════ TAB: FEEDBACKS ══════════ -->
<div class="tab-pane" id="tab-feedbacks">

    <!-- Filtros -->
    <form method="GET" action="index.php" id="formFiltros">
        <input type="hidden" name="c" value="adminbacktesting">
        <input type="hidden" name="a" value="Index">
        <input type="hidden" name="tab" value="feedbacks">
        <div class="filter-bar">
            <div>
                <label>Tipo</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="positivo" <?= ($_GET['tipo']??'')==='positivo'?'selected':'' ?>>Positivo</option>
                    <option value="negativo" <?= ($_GET['tipo']??'')==='negativo'?'selected':'' ?>>Negativo</option>
                </select>
            </div>
            <div>
                <label>Modelo</label>
                <select name="modelo_id">
                    <option value="">Todos</option>
                    <?php foreach ($modelos as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= ($_GET['modelo_id']??'')==$m['id']?'selected':'' ?>>
                            <?= htmlspecialchars($m['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="completado" <?= ($_GET['estado']??'')==='completado'?'selected':'' ?>>Completado</option>
                    <option value="revisado"   <?= ($_GET['estado']??'')==='revisado'?'selected':'' ?>>Revisado</option>
                    <option value="bloqueado"  <?= ($_GET['estado']??'')==='bloqueado'?'selected':'' ?>>Bloqueado</option>
                    <option value="pendiente"  <?= ($_GET['estado']??'')==='pendiente'?'selected':'' ?>>Pendiente</option>
                </select>
            </div>
            <div>
                <label>Calif. mín</label>
                <input type="number" name="calif_min" min="1" max="5" step="1"
                       style="width:70px" value="<?= htmlspecialchars($_GET['calif_min']??'') ?>">
            </div>
            <div>
                <label>Calif. máx</label>
                <input type="number" name="calif_max" min="1" max="5" step="1"
                       style="width:70px" value="<?= htmlspecialchars($_GET['calif_max']??'') ?>">
            </div>
            <div>
                <label>Solo ofensivos</label>
                <select name="ofensivo">
                    <option value="">No</option>
                    <option value="1" <?= !empty($_GET['ofensivo'])?'selected':'' ?>>Sí</option>
                </select>
            </div>
            <div style="flex:1;min-width:140px">
                <label>Buscar comentario</label>
                <input type="text" name="buscar" placeholder="Texto..." style="width:100%"
                       value="<?= htmlspecialchars($_GET['buscar']??'') ?>">
            </div>
            <button type="submit" class="btn-filtrar"><i class="fas fa-filter"></i> Filtrar</button>
            <a href="index.php?c=adminbacktesting&a=Index&tab=feedbacks" class="btn-reset"><i class="fas fa-times"></i></a>
        </div>
    </form>

    <!-- Tabla -->
    <div class="tabla-wrap">
        <table>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Atracción</th>
                    <th>Modelo</th>
                    <th>Calificación</th>
                    <th>Tipo</th>
                    <th>Comentario</th>
                    <th>Tags</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($feedbacks['datos'])): ?>
                <tr><td colspan="10" style="text-align:center;padding:40px;color:var(--muted)">
                    <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.3"></i>
                    Sin feedbacks con estos filtros
                </td></tr>
            <?php else: ?>
                <?php foreach ($feedbacks['datos'] as $fb):
                    $metricas   = $fb['metricas'] ?? [];
                    $tags       = $metricas['tags'] ?? [];
                    $tipo       = $metricas['feedback_tipo'] ?? 'n/a';
                    $es_ofensivo = $fb['es_ofensivo'] ?? false;
                    $dtFb = new DateTime($fb['created_at']);
                    $dtFb->setTimezone($tz);
                ?>
                <tr class="<?= $es_ofensivo ? 'fila-ofensiva' : '' ?>">
                    <td style="color:var(--muted);font-size:.7rem">#<?= $fb['id'] ?></td>
                    <td>
                        <div style="font-size:.75rem;color:#fff;font-weight:600">
                            <?= htmlspecialchars(mb_substr($fb['atraccion_nombre'] ?? 'ID '.$fb['atraccion_id'], 0, 30)) ?>
                        </div>
                        <div style="font-size:.65rem;color:var(--muted)">id:<?= $fb['atraccion_id'] ?></div>
                    </td>
                    <td>
                        <div style="font-size:.7rem;color:#a5b4fc">
                            <?= htmlspecialchars(mb_substr($fb['modelo_nombre'] ?? '—', 0, 25)) ?>
                        </div>
                    </td>
                    <td>
                        <div class="stars-mini">
                            <?php $c = (int)round((float)$fb['calificacion_usuario']); for ($s=1;$s<=5;$s++): ?>
                                <i class="fas fa-star <?= $s>$c?'empty':'' ?>"></i>
                            <?php endfor; ?>
                            <span><?= number_format((float)$fb['calificacion_usuario'],1) ?></span>
                        </div>
                    </td>
                    <td><span class="badge-tipo <?= $tipo==='positivo'?'tipo-pos':($tipo==='neutral'?'tipo-neu':'tipo-neg') ?>"><?= $tipo ?></span></td>
                    <td>
                        <?php if ($es_ofensivo): ?>
                            <i class="fas fa-flag icono-ofensivo me-1"></i>
                        <?php endif; ?>
                        <span class="comentario-txt"
                              title="<?= htmlspecialchars($fb['comentario_usuario'] ?? '') ?>"
                              onclick="verComentario(<?= $fb['id'] ?>,<?= htmlspecialchars(json_encode($fb), ENT_QUOTES) ?>)">
                            <?= htmlspecialchars(mb_substr($fb['comentario_usuario'] ?? '—', 0, 50)) ?>…
                        </span>
                    </td>
                    <td>
                        <?php foreach (array_slice($tags,0,3) as $tag): ?>
                            <span class="tag-pill <?= in_array($tag,['muy_lleno','dificil_acceso'])?'rojo':'' ?>">
                                <?= htmlspecialchars($tag) ?>
                            </span>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <span class="badge-estado est-<?= htmlspecialchars($fb['estado']??'pendiente') ?>">
                            <?= htmlspecialchars($fb['estado']??'pendiente') ?>
                        </span>
                    </td>
                    <td style="font-size:.7rem;color:var(--muted);white-space:nowrap">
                        <?= $dtFb->format('d/m/y H:i') ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap">
                            <button class="btn-accion btn-revisar"
                                    onclick="cambiarEstado(<?= $fb['id'] ?>,'revisado',this)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-accion btn-bloquear"
                                    onclick="cambiarEstado(<?= $fb['id'] ?>,'bloqueado',this)">
                                <i class="fas fa-ban"></i>
                            </button>
                            <button class="btn-accion btn-eliminar"
                                    onclick="eliminarFeedback(<?= $fb['id'] ?>,this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <div class="paginacion">
            <span>Mostrando <?= count($feedbacks['datos']) ?> de <?= $feedbacks['total'] ?> registros</span>
            <div class="page-btns">
                <?php
                $pag = $feedbacks['pagina'];
                $total_pags = $feedbacks['paginas'];
                $qs = $_GET; unset($qs['c'], $qs['a'], $qs['page'], $qs['tab']);
                $baseUrl = 'index.php?c=adminbacktesting&a=Index&' . http_build_query($qs + ['tab' => 'feedbacks']) . '&page=';
                if ($pag > 1): ?>
                    <a class="page-btn" href="<?= $baseUrl . ($pag-1) ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif;
                $start = max(1, $pag-2); $end = min($total_pags, $pag+2);
                for ($p=$start; $p<=$end; $p++): ?>
                    <a class="page-btn <?= $p===$pag?'active':'' ?>"
                       href="<?= $baseUrl . $p ?>"><?= $p ?></a>
                <?php endfor;
                if ($pag < $total_pags): ?>
                    <a class="page-btn" href="<?= $baseUrl . ($pag+1) ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div><!-- /tab-feedbacks -->

<!-- ══════════ TAB: MODELOS ML ══════════ -->
<div class="tab-pane" id="tab-modelos">

    <div style="margin-bottom:16px">
        <p style="font-size:.83rem;color:var(--muted)">
            <i class="fas fa-info-circle me-1" style="color:var(--blue)"></i>
            Ranking de modelos por calificación promedio. Los re-ajustes del backtesting modifican el vector de entrada de cada modelo para mejorar futuras recomendaciones.
        </p>
    </div>

    <?php foreach ($kpisModelos as $idx => $km):
        $total  = (int)$km['total_feedbacks'];
        $tasaP  = (float)($km['tasa_positiva'] ?? 0);
        $prom   = (float)($km['promedio_calif'] ?? 0);
        $estadoBadge = match($km['estado']) {
            'activo'    => 'tipo-pos',
            'inactivo'  => 'tipo-neg',
            'entrenando'=> 'est-pendiente',
            default     => 'est-revisado',
        };
    ?>
    <div class="modelo-row">
        <div class="modelo-header">
            <div class="modelo-ico">
                <i class="fas fa-robot"></i>
            </div>
            <div style="flex:1">
                <div class="modelo-nombre">
                    #<?= $idx+1 ?> · <?= htmlspecialchars($km['nombre']) ?>
                </div>
                <div class="modelo-ver">
                    v<?= htmlspecialchars($km['version']) ?> ·
                    <span class="badge-tipo <?= $estadoBadge ?>"><?= htmlspecialchars($km['estado']) ?></span>
                </div>
            </div>
            <div style="display:flex;gap:6px">
                <button class="btn-accion btn-completar"
                        onclick="cambiarEstadoModelo(<?= $km['id'] ?>,'activo')">
                    <i class="fas fa-play"></i> Activar
                </button>
                <button class="btn-accion btn-bloquear"
                        onclick="cambiarEstadoModelo(<?= $km['id'] ?>,'inactivo')">
                    <i class="fas fa-pause"></i> Pausar
                </button>
            </div>
        </div>

        <div class="modelo-stats">
            <div class="mstat">
                <div class="mstat-val"><?= $total ?></div>
                <div class="mstat-lbl">Feedbacks</div>
            </div>
            <div class="mstat">
                <div class="mstat-val" style="color:<?= $prom>=4?'#34d399':($prom>=3?'#fbbf24':'#f87171') ?>">
                    <?= $total>0 ? number_format($prom,1) : '—' ?>
                </div>
                <div class="mstat-lbl">Prom. calificación</div>
            </div>
            <div class="mstat">
                <div class="mstat-val" style="color:#34d399"><?= $km['positivos'] ?? 0 ?></div>
                <div class="mstat-lbl">Positivos</div>
            </div>
            <div class="mstat">
                <div class="mstat-val" style="color:#f87171"><?= $km['negativos'] ?? 0 ?></div>
                <div class="mstat-lbl">Negativos</div>
            </div>
            <div class="mstat" style="flex:1;min-width:200px">
                <div class="mstat-lbl" style="text-align:left;margin-bottom:4px">
                    Tasa positiva: <strong style="color:#fff"><?= $tasaP ?>%</strong>
                </div>
                <div class="tasa-bar">
                    <div class="tasa-fill" style="width:<?= $tasaP ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($kpisModelos)): ?>
        <div style="text-align:center;padding:60px;color:var(--muted)">
            <i class="fas fa-robot" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.3"></i>
            Sin modelos registrados
        </div>
    <?php endif; ?>

</div><!-- /tab-modelos -->

<!-- ══════════ TAB: ANÁLISIS DE MEJORA ══════════ -->
<div class="tab-pane" id="tab-mejora">

    <div style="margin-bottom:16px">
        <p style="font-size:.83rem;color:var(--muted)">
            <i class="fas fa-info-circle me-1" style="color:var(--blue)"></i>
            Compara el <strong style="color:#fff">input_original</strong> con el <strong style="color:#fff">nuevo_input_vector</strong> de cada feedback.
            Cuando un feedback negativo llega, el modelo reduce los pesos de ese input. Un "reset" indica vector en ceros.
        </p>
    </div>

    <?php
    $totalMejora = $mejora['total'] ?? 0;
    $mej = $mejora['mejoras']   ?? 0;
    $ig  = $mejora['igual']     ?? 0;
    $deg = $mejora['degradado'] ?? 0;
    ?>

    <div class="mejora-grid">
        <div class="mejora-card" style="border-color:rgba(16,185,129,.25)">
            <div class="mejora-val" style="color:#34d399"><?= $mej ?></div>
            <div class="mejora-lbl"><i class="fas fa-arrow-up me-1"></i>Aumento de pesos</div>
        </div>
        <div class="mejora-card">
            <div class="mejora-val"><?= $ig ?></div>
            <div class="mejora-lbl"><i class="fas fa-equals me-1"></i>Sin cambio</div>
        </div>
        <div class="mejora-card" style="border-color:rgba(239,68,68,.25)">
            <div class="mejora-val" style="color:#f87171"><?= $deg ?></div>
            <div class="mejora-lbl"><i class="fas fa-arrow-down me-1"></i>Reducción / Reset</div>
        </div>
    </div>

    <!-- Tabla de últimos ajustes -->
    <div class="tabla-wrap">
        <table>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Atracción</th>
                    <th>Calificación</th>
                    <th>Tipo</th>
                    <th>Modelo</th>
                    <th>Cambio vector</th>
                    <th>Suma original</th>
                    <th>Suma nueva</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($mejora['detalle'] ?? [], 0, 50) as $dm):
                $orig    = json_decode($dm['input_original'] ?? '[]', true) ?? [];
                $nuevo   = json_decode($dm['nuevo_vector']   ?? '[]', true) ?? [];
                $sumO    = array_sum($orig);
                $sumN    = array_sum($nuevo);
                $cambio  = $dm['cambio'] ?? 'sin_cambio';
                $cambioColor = match($cambio) {
                    'aumento'  => '#34d399',
                    'reset','reduccion' => '#f87171',
                    default    => 'var(--muted)',
                };
                $cambioIco = match($cambio) {
                    'aumento'  => 'fa-arrow-up',
                    'reset'    => 'fa-rotate-left',
                    'reduccion'=> 'fa-arrow-down',
                    default    => 'fa-equals',
                };
            ?>
            <tr>
                <td style="color:var(--muted);font-size:.7rem">#<?= $dm['id'] ?></td>
                <td style="font-size:.75rem">
                    <div style="font-weight:600"><?= htmlspecialchars($dm['atraccion_nombre'] ?? 'Atracción eliminada') ?></div>
                    <div style="font-size:.65rem;color:var(--muted)">id:<?= $dm['atraccion_id'] ?></div>
                </td>
                <td>
                    <div class="stars-mini">
                        <?php $cv = (int)round((float)$dm['calificacion_usuario']); for ($s=1;$s<=5;$s++): ?>
                            <i class="fas fa-star <?= $s>$cv?'empty':'' ?>"></i>
                        <?php endfor; ?>
                        <span><?= number_format((float)$dm['calificacion_usuario'],1) ?></span>
                    </div>
                </td>
                <td>
                    <span class="badge-tipo <?= ($dm['tipo']??'')==='positivo'?'tipo-pos':(($dm['tipo']??'')==='neutral'?'tipo-neu':'tipo-neg') ?>">
                        <?= htmlspecialchars($dm['tipo'] ?? '—') ?>
                    </span>
                </td>
                <td style="font-size:.7rem;color:#a5b4fc" title="<?= htmlspecialchars($dm['modelo_nombre'] ?? '') ?>"><?= htmlspecialchars(preg_replace('/\s+La Paz.*$/u', '', $dm['modelo_nombre'] ?? ('m:' . $dm['modelo_id']))) ?></td>
                <td>
                    <span style="color:<?= $cambioColor ?>;font-size:.75rem;font-weight:700">
                        <i class="fas <?= $cambioIco ?> me-1"></i><?= $cambio ?>
                    </span>
                </td>
                <td style="font-size:.75rem;color:var(--muted)"><?= round($sumO, 2) ?></td>
                <td style="font-size:.75rem;color:<?= $sumN<$sumO?'#f87171':($sumN>$sumO?'#34d399':'var(--muted)') ?>">
                    <?= round($sumN, 2) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Sugerencias de mejora -->
    <div class="chart-box" style="margin-top:20px">
        <h4><i class="fas fa-lightbulb"></i> Sugerencias para mejorar el backtesting</h4>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;font-size:.8rem;color:var(--muted)">
            <?php
            $sugerencias = [
                ['ico'=>'fa-percent','color'=>'#818cf8','titulo'=>'Ajuste gradual (0.1–0.3)',
                 'desc'=>'En lugar de resetear a 0, reducir los pesos de forma gradual evita que el modelo "olvide" preferencias válidas por un feedback aislado.'],
                ['ico'=>'fa-filter','color'=>'#34d399','titulo'=>'Filtrar comentarios ofensivos',
                 'desc'=>"Se detectaron $ofensivoCount comentarios potencialmente ofensivos. Estos no deberían alterar el modelo. Marcarlos como 'bloqueado' los excluye del reajuste."],
                ['ico'=>'fa-scale-balanced','color'=>'#fbbf24','titulo'=>'Ponderar por volumen',
                 'desc'=>'Un solo feedback negativo no debería contraponerse a 10 positivos. Implementar un umbral mínimo de N feedbacks antes de aplicar cambios al vector.'],
                ['ico'=>'fa-clock-rotate-left','color'=>'#22d3ee','titulo'=>'Historial de versiones',
                 'desc'=>'Guardar snapshots del vector antes de cada reajuste permite hacer rollback si el rendimiento del modelo empeora tras un cambio.'],
            ];
            foreach ($sugerencias as $s): ?>
            <div style="background:var(--surf2);border-radius:10px;padding:14px;border:1px solid var(--border)">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                    <i class="fas <?= $s['ico'] ?>" style="color:<?= $s['color'] ?>"></i>
                    <strong style="color:#fff;font-size:.8rem"><?= $s['titulo'] ?></strong>
                </div>
                <p style="margin:0;line-height:1.5"><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div><!-- /tab-mejora -->

</div><!-- /adm-body -->

<!-- ─── MODAL DETALLE FEEDBACK ─── -->
<div class="modal fade" id="modalFeedback" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-comment-dots me-2" style="color:#818cf8"></i>Detalle del Feedback</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalFeedbackBody" style="padding:20px">
                <!-- Inyectado por JS -->
            </div>
            <div class="modal-footer" style="gap:8px">
                <button class="btn-accion btn-revisar" id="modalBtnRevisar">
                    <i class="fas fa-eye"></i> Marcar revisado
                </button>
                <button class="btn-accion btn-bloquear" id="modalBtnBloquear">
                    <i class="fas fa-ban"></i> Bloquear
                </button>
                <button class="btn-accion btn-eliminar" id="modalBtnEliminar">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<div id="contenedorModal"></div>

</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ─── Datos PHP → JS ───
const evolucionData = <?= json_encode($evolucion, JSON_UNESCAPED_UNICODE) ?>;
const tagsData      = <?= json_encode($tagsNegativos, JSON_UNESCAPED_UNICODE) ?>;
const kpiPos        = <?= $kpis['positivos'] ?? 0 ?>;
const kpiNeg        = <?= $kpis['negativos'] ?? 0 ?>;

// ─── Tab switcher ───
function switchTab(id, btn) {
    document.querySelectorAll('.tab-pane').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.adm-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
}
// Al paginar o filtrar se vuelve a la misma pestaña (?tab=feedbacks)
(function () {
    const tab = new URLSearchParams(location.search).get('tab');
    const btn = tab && document.querySelector(`.adm-tab[onclick*="'${tab}'"]`);
    if (btn && document.getElementById('tab-' + tab)) switchTab(tab, btn);
})();

// ─── Gráfica evolución ───
(function() {
    const ctx = document.getElementById('chartEvolucion');
    if (!ctx || !evolucionData.length) return;

    const labels   = evolucionData.map(d => d.fecha);
    const promedios = evolucionData.map(d => parseFloat(d.promedio));
    const positivos = evolucionData.map(d => parseInt(d.positivos));
    const negativos = evolucionData.map(d => parseInt(d.negativos));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Calificación promedio',
                    data: promedios,
                    borderColor: '#818cf8',
                    backgroundColor: 'rgba(99,102,241,.08)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y',
                    pointBackgroundColor: '#818cf8',
                    pointRadius: 4,
                },
                {
                    label: 'Positivos',
                    data: positivos,
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(16,185,129,.06)',
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y2',
                    borderDash: [4,3],
                    pointRadius: 3,
                },
                {
                    label: 'Negativos',
                    data: negativos,
                    borderColor: '#f87171',
                    backgroundColor: 'rgba(239,68,68,.06)',
                    fill: false,
                    tension: 0.3,
                    yAxisID: 'y2',
                    borderDash: [4,3],
                    pointRadius: 3,
                },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { color: 'rgba(255,255,255,.5)', font: { size: 11 } } } },
            scales: {
                x:  { ticks: { color: 'rgba(255,255,255,.35)', font:{size:10} }, grid: { color: 'rgba(255,255,255,.04)' } },
                y:  { ticks: { color: '#818cf8', font:{size:10} }, grid: { color: 'rgba(255,255,255,.04)' }, min: 0, max: 5, position: 'left' },
                y2: { ticks: { color: 'rgba(255,255,255,.35)', font:{size:10} }, grid: { display:false }, position: 'right' },
            }
        }
    });
})();

// ─── Gráfica donut ───
(function() {
    const ctx = document.getElementById('chartDonut');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Positivos', 'Negativos'],
            datasets: [{ data: [kpiPos, kpiNeg], backgroundColor: ['rgba(16,185,129,.7)','rgba(239,68,68,.7)'], borderWidth:0 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: 'rgba(255,255,255,.5)', font:{size:11} } }
            },
            cutout: '65%'
        }
    });
})();

// ─── Gráfica tags negativos ───
(function() {
    const ctx = document.getElementById('chartTags');
    if (!ctx || !tagsData.length) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: tagsData.map(t => t.tag),
            datasets: [{
                label: 'Frecuencia',
                data: tagsData.map(t => parseInt(t.frecuencia)),
                backgroundColor: 'rgba(239,68,68,.5)',
                borderColor: 'rgba(239,68,68,.8)',
                borderWidth: 1,
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: 'rgba(255,255,255,.35)', font:{size:10} }, grid: { color:'rgba(255,255,255,.04)' } },
                y: { ticks: { color: 'rgba(255,255,255,.5)', font:{size:10} }, grid: { display:false } }
            }
        }
    });
})();

// ─── Cambiar estado feedback ───
function cambiarEstado(id, estado, btn) {
    $.post('index.php?c=adminbacktesting&a=CambiarEstado',
        { id, estado },
        function(res) {
            if (res.success) {
                toastr.success(res.message);
                // Actualizar badge en la fila
                const fila = $(btn).closest('tr');
                const badge = fila.find('.badge-estado');
                badge.attr('class', 'badge-estado est-' + estado).text(estado);
            } else {
                toastr.error(res.message || 'Error al actualizar');
            }
        }, 'json'
    ).fail(() => toastr.error('Error de conexión'));
}

// ─── Eliminar feedback ───
function eliminarFeedback(id, btn) {
    Swal.fire({
        title: '¿Eliminar este feedback?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#1a1f38', color: '#e2e8f0',
        backdrop: 'rgba(10,13,26,.8)',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post('index.php?c=adminbacktesting&a=Eliminar', { id },
            function(res) {
                if (res.success) {
                    toastr.success('Feedback eliminado');
                    $(btn).closest('tr').fadeOut(300, function(){ $(this).remove(); });
                } else {
                    toastr.error(res.message || 'No se pudo eliminar');
                }
            }, 'json'
        ).fail(() => toastr.error('Error de conexión'));
    });
}

// ─── Cambiar estado modelo ───
function cambiarEstadoModelo(id, estado) {
    $.post('index.php?c=adminbacktesting&a=CambiarEstadoModelo', { id, estado },
        function(res) {
            if (res.success) { toastr.success(res.message); }
            else { toastr.error(res.message || 'Error al actualizar'); }
        }, 'json'
    ).fail(() => toastr.error('Error de conexión'));
}

// ─── Ver comentario completo ───
let modalFbId = null;
function verComentario(id, fb) {
    modalFbId = id;
    const metricas    = fb.metricas || {};
    const tags        = (metricas.tags || []).join(', ') || '—';
    const tipo        = metricas.feedback_tipo || '—';
    const inputOrig   = JSON.stringify(metricas.input_original || [], null, 2);
    const nuevoVec    = metricas.nuevo_input_vector || [];
    const origVec     = metricas.input_original     || [];

    // Generar grid de vectores
    let vecHtml = '<div class="vector-grid">';
    for (let i=0; i<nuevoVec.length; i++) {
        const o = origVec[i] ?? 0;
        const n = nuevoVec[i] ?? 0;
        const cls = n > 0 ? 'activo' : (o > 0 && n === 0 ? 'reducido' : '');
        vecHtml += `<div class="vec-cell ${cls}" title="Idx ${i}: orig=${o} nuevo=${n}">${n}</div>`;
    }
    vecHtml += '</div>';

    document.getElementById('modalFeedbackBody').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <div style="font-size:.65rem;text-transform:uppercase;color:var(--muted);letter-spacing:.5px;margin-bottom:6px">Comentario completo</div>
                <div style="background:var(--bg2);border:1px solid var(--border);border-radius:8px;padding:12px;font-size:.8rem;line-height:1.6;color:#fff">
                    ${fb.es_ofensivo ? '<span style="color:#f87171;font-size:.7rem"><i class="fas fa-flag me-1"></i>Comentario ofensivo detectado</span><br>' : ''}
                    ${(fb.comentario_usuario || '—').replace(/</g,'&lt;')}
                </div>
            </div>
            <div>
                <div style="font-size:.65rem;text-transform:uppercase;color:var(--muted);letter-spacing:.5px;margin-bottom:6px">Metadatos</div>
                <div style="font-size:.78rem;display:grid;gap:6px">
                    <div>Tipo: <strong style="color:${tipo==='positivo'?'#34d399':'#f87171'}">${tipo}</strong></div>
                    <div>Tags: <span style="color:#a5b4fc">${tags}</span></div>
                    <div>Atracción ID: <strong style="color:#fff">${fb.atraccion_id}</strong></div>
                    <div>Modelo ID: <strong style="color:#fff">${(fb.modelo_mr_fk||{}).modelo_id || '—'}</strong></div>
                    <div>Calificación: <strong style="color:${fb.calificacion_usuario>=4?'#34d399':fb.calificacion_usuario>=3?'#fbbf24':'#f87171'}">${fb.calificacion_usuario}/5</strong></div>
                </div>
            </div>
        </div>
        <div style="font-size:.65rem;text-transform:uppercase;color:var(--muted);letter-spacing:.5px;margin-bottom:6px">
            Nuevo vector de entrada (<?= '<?= ' ?>nuevoVec.length?> features)
            <span style="color:#818cf8;margin-left:8px">azul=activo · rojo=reducido</span>
        </div>
        ${vecHtml}
    `;

    // Bind botones modal
    document.getElementById('modalBtnRevisar').onclick  = () => { cambiarEstado(id,'revisado',null); };
    document.getElementById('modalBtnBloquear').onclick = () => { cambiarEstado(id,'bloqueado',null); };
    document.getElementById('modalBtnEliminar').onclick = () => {
        bootstrap.Modal.getInstance(document.getElementById('modalFeedback')).hide();
        eliminarFeedback(id, null);
    };

    new bootstrap.Modal(document.getElementById('modalFeedback')).show();
}

// ─── Toastr ───
toastr.options = { positionClass:'toast-bottom-right', timeOut:3000, closeButton:true };

// ─── Sidebar sync ───
document.addEventListener('DOMContentLoaded', function() {
    const main = document.querySelector('main');
    if (document.body.classList.contains('sidebar-collapsed') && main) {
        main.classList.add('min-main');
    }
});
</script>
</body>
</html>