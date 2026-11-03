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
<title>Etreva Admin — Atracciones</title>
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
main{margin-left:280px;min-height:100vh;background:var(--bg);transition:margin-left .3s;}
main.min-main{margin-left:85px;}

/* TOPBAR */
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:14px 32px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.topbar h1{font-size:1.1rem;font-weight:800;color:#fff;margin:0;display:flex;align-items:center;gap:10px;}
.topbar h1 span{background:linear-gradient(135deg,var(--orange),#ff9a6c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.topbar-right{display:flex;align-items:center;gap:10px;}
.btn-icon{background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:9px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.9rem;cursor:pointer;text-decoration:none;transition:all .2s;}
.btn-icon:hover{background:rgba(229,90,43,.12);color:var(--orange);}

.body{padding:24px 32px 60px;display:flex;flex-direction:column;gap:18px;}

/* STAT STRIP */
.stat-strip{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;}
.stat-pill{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:14px 16px;display:flex;align-items:center;gap:12px;transition:border-color .2s;}
.stat-pill:hover{border-color:rgba(229,90,43,.35);}
.stat-pill-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;}
.stat-pill-txt{font-size:1.3rem;font-weight:900;color:#fff;line-height:1;}
.stat-pill-lbl{font-size:.7rem;color:var(--muted);margin-top:2px;}

/* FILTROS */
.filtros-wrap{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:14px 18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.filtros-wrap input[type=text],.filtros-wrap select{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;padding:8px 12px;color:var(--text);font-size:.82rem;outline:none;transition:border-color .2s;}
.filtros-wrap input[type=text]{flex:1;min-width:200px;}
.filtros-wrap input[type=text]:focus,.filtros-wrap select:focus{border-color:var(--orange);}
.filtros-wrap select option{background:#1c1c32;}
.btn-filtrar{background:linear-gradient(135deg,var(--orange),#ff9a6c);color:#fff;border:none;border-radius:9px;padding:8px 18px;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .2s;}
.btn-filtrar:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(229,90,43,.35);}
.btn-nuevo{background:rgba(34,197,94,.14);color:var(--green);border:1px solid rgba(34,197,94,.3);border-radius:9px;padding:8px 16px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s;white-space:nowrap;}
.btn-nuevo:hover{background:rgba(34,197,94,.25);transform:translateY(-1px);}
.btn-export{padding:8px 14px;border-radius:9px;border:none;font-size:.78rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s;white-space:nowrap;}
.btn-excel{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25);}
.btn-excel:hover{background:rgba(34,197,94,.22);transform:translateY(-1px);}
.export-sep{width:1px;height:28px;background:rgba(255,255,255,.08);margin:0 2px;}

/* TABLA */
.tabla-wrap{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;}
.adm-table{width:100%;border-collapse:collapse;font-size:.81rem;}
.adm-table thead th{background:rgba(255,255,255,.03);color:var(--muted);font-weight:600;font-size:.68rem;letter-spacing:1px;text-transform:uppercase;padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.07);white-space:nowrap;}
.adm-table thead th.sortable{cursor:pointer;user-select:none;}
.adm-table thead th.sortable:hover{color:var(--text);}
.adm-table thead th.sort-active{color:var(--orange);}
.adm-table tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;}
.adm-table tbody tr:hover{background:rgba(229,90,43,.04);cursor:pointer;}
.adm-table tbody td{padding:11px 14px;color:var(--text);vertical-align:middle;}
.av{width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--orange),#ff9a6c);display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;color:#fff;flex-shrink:0;}

/* BADGES */
.badge{font-size:.63rem;font-weight:700;padding:3px 8px;border-radius:20px;white-space:nowrap;}
.b-activo  {background:rgba(34,197,94,.12); color:var(--green);}
.b-inactivo{background:rgba(239,68,68,.1);  color:var(--red);}
.b-gratis  {background:rgba(56,189,248,.1); color:var(--blue);}
.b-pago    {background:rgba(245,158,11,.1); color:var(--amber);}

/* ESTRELLAS */
.stars{display:inline-flex;gap:1px;align-items:center;}
.stars i{font-size:.55rem;}
.stars .f{color:var(--amber);}
.stars .e{color:rgba(255,255,255,.15);}

/* ACCIONES */
.acciones{display:flex;gap:5px;flex-wrap:nowrap;}
.btn-fotos{background:rgba(14,165,233,.12);color:#0ea5e9;border:1px solid rgba(14,165,233,.3);}
.av-foto{position:relative;overflow:hidden;cursor:pointer;}
.av-foto img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;}
.fg-wrap{text-align:left;font-size:.8rem;}
.fg-sec{margin:14px 0 6px;font-weight:700;font-size:.78rem;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;}
.fg-sec small{font-weight:400;opacity:.7;}
.fg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;}
.fg-item{position:relative;border-radius:10px;overflow:hidden;border:1px solid rgba(127,127,127,.25);background:rgba(127,127,127,.08);}
.fg-item img{display:block;width:100%;height:105px;object-fit:cover;}
.fg-item.principal{outline:2px solid #f59e0b;outline-offset:-2px;}
.fg-tag{position:absolute;top:6px;left:6px;background:#f59e0b;color:#1a1a1a;font-size:.6rem;font-weight:800;padding:2px 7px;border-radius:20px;}
.fg-meta{padding:5px 8px;font-size:.65rem;opacity:.8;line-height:1.35;}
.fg-acc{display:flex;gap:4px;padding:0 8px 8px;}
.fg-acc button{flex:1;border:1px solid rgba(127,127,127,.3);background:transparent;color:inherit;border-radius:6px;font-size:.65rem;padding:4px;cursor:pointer;}
.fg-acc button:hover{background:rgba(127,127,127,.15);}
.fg-acc .fg-del{color:#ef4444;border-color:rgba(239,68,68,.35);}
.fg-drop{border:1.5px dashed rgba(127,127,127,.45);border-radius:10px;padding:14px;text-align:center;cursor:pointer;}
.fg-drop.sobre{border-color:#0ea5e9;background:rgba(14,165,233,.08);}
.fg-vacio{opacity:.7;font-size:.75rem;padding:8px 0;}
.fg-btn{border:1px solid rgba(14,165,233,.35);background:rgba(14,165,233,.1);color:#0ea5e9;border-radius:8px;padding:5px 10px;font-size:.72rem;cursor:pointer;}
.fg-ref img{filter:saturate(.85);}
.btn-sm{padding:4px 9px;border-radius:7px;font-size:.7rem;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:3px;transition:all .18s;white-space:nowrap;}
.btn-edit    {background:rgba(56,189,248,.1); color:var(--blue);}
.btn-edit:hover{background:rgba(56,189,248,.22);transform:translateY(-1px);}
.btn-toggle-on {background:rgba(34,197,94,.12); color:var(--green);}
.btn-toggle-on:hover{background:rgba(34,197,94,.25);transform:translateY(-1px);}
.btn-toggle-off{background:rgba(167,139,250,.1);color:var(--purple);}
.btn-toggle-off:hover{background:rgba(167,139,250,.22);transform:translateY(-1px);}
.btn-del   {background:rgba(239,68,68,.08); color:var(--red);}
.btn-del:hover{background:rgba(239,68,68,.2);transform:translateY(-1px);}

/* PAGINACIÓN */
.paginacion{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid rgba(255,255,255,.06);font-size:.77rem;color:var(--muted);}
.pag-btns{display:flex;gap:4px;}
.pag-btn{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:7px;padding:5px 10px;color:var(--muted);cursor:pointer;font-size:.73rem;transition:all .18s;}
.pag-btn:hover,.pag-btn.active{background:rgba(229,90,43,.15);color:var(--orange);border-color:rgba(229,90,43,.3);}
.pag-btn:disabled{opacity:.3;cursor:not-allowed;}

/* SKELETON */
.skeleton{background:linear-gradient(90deg,rgba(255,255,255,.04) 0%,rgba(255,255,255,.09) 50%,rgba(255,255,255,.04) 100%);background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:8px;}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.empty-state{text-align:center;padding:50px 20px;color:var(--muted);}
.empty-state i{font-size:2.5rem;margin-bottom:12px;display:block;opacity:.3;}

/* ─── MODAL ─── */
.modal-overlay{display:none;position:fixed;inset:0;z-index:1000;background:rgba(8,8,18,.88);backdrop-filter:blur(6px);align-items:flex-start;justify-content:center;padding:20px;overflow-y:auto;}
.modal-overlay.show{display:flex;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:20px;width:100%;max-width:760px;animation:popIn .22s ease;margin:auto;}
@keyframes popIn{from{transform:scale(.93) translateY(14px);opacity:0}to{transform:scale(1) translateY(0);opacity:1}}
.modal-header{padding:18px 22px 14px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--card);z-index:1;border-radius:20px 20px 0 0;}
.modal-header h3{font-size:.98rem;font-weight:800;color:#fff;margin:0;display:flex;align-items:center;gap:8px;}
.modal-close{background:none;border:none;color:var(--muted);font-size:1rem;cursor:pointer;width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;transition:all .18s;}
.modal-close:hover{background:rgba(255,255,255,.08);color:#fff;}
.modal-body{padding:20px 22px;}
.modal-footer{padding:12px 22px 18px;border-top:1px solid rgba(255,255,255,.07);display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;}

/* FORM */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-grid.triple{grid-template-columns:1fr 1fr 1fr;}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-group.full{grid-column:1/-1;}
.form-group label{font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;font-weight:700;}
.form-group input,.form-group select,.form-group textarea{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;padding:9px 12px;color:var(--text);font-size:.83rem;outline:none;transition:border-color .2s;width:100%;}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--orange);}
.form-group textarea{resize:vertical;min-height:80px;}
.form-group select option{background:#1c1c32;}
.form-sep{font-size:.67rem;color:var(--orange);text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin:14px 0 6px;display:flex;align-items:center;gap:8px;grid-column:1/-1;}
.form-sep::after{content:'';flex:1;height:1px;background:rgba(229,90,43,.2);}

/* DÍAS CHECKBOXES */
.dias-grid{display:flex;gap:6px;flex-wrap:wrap;}
.dia-check{display:none;}
.dia-label{padding:5px 11px;border-radius:8px;font-size:.72rem;font-weight:700;cursor:pointer;border:1px solid rgba(255,255,255,.1);color:var(--muted);transition:all .18s;user-select:none;}
.dia-check:checked + .dia-label{background:rgba(229,90,43,.2);border-color:var(--orange);color:var(--orange);}
.dia-label:hover{border-color:rgba(229,90,43,.4);color:var(--text);}

/* BOTONES MODAL */
.btn-guardar{background:linear-gradient(135deg,var(--orange),#ff9a6c);color:#fff;border:none;border-radius:9px;padding:9px 22px;font-size:.84rem;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:7px;}
.btn-guardar:hover{transform:translateY(-1px);box-shadow:0 4px 16px rgba(229,90,43,.4);}
.btn-cancelar{background:rgba(255,255,255,.06);color:var(--muted);border:1px solid rgba(255,255,255,.1);border-radius:9px;padding:9px 16px;font-size:.84rem;cursor:pointer;transition:all .2s;}
.btn-cancelar:hover{color:var(--text);background:rgba(255,255,255,.1);}

/* ROW INACTIVA */
tr.row-inactiva td{opacity:.55;}

/* PRINT */
@media print{
    .topbar,.stat-strip,.filtros-wrap,.acciones,.pag-btns,#adminMenu,button{display:none!important;}
    main{margin-left:0!important;}
    body,html{background:#fff!important;color:#000!important;}
    .tabla-wrap{border:1px solid #ccc!important;border-radius:0!important;}
    .adm-table thead th{background:#f5f5f5!important;color:#333!important;}
    .adm-table tbody td{color:#222!important;}
    .paginacion{display:none!important;}
}

@media(max-width:900px){
    main,main.min-main{margin-left:0;}
    .body{padding:12px 12px 40px;}
    .stat-strip{grid-template-columns:repeat(2,1fr);}
    .form-grid,.form-grid.triple{grid-template-columns:1fr;}
}
</style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main>

<div class="topbar">
    <h1><i class="fas fa-map-marked-alt" style="color:var(--orange);"></i> Gestión de <span>Atracciones</span></h1>
    <div class="topbar-right">
        <div class="btn-icon" id="btnRefresh" title="Actualizar"><i class="fas fa-rotate-right"></i></div>
        <a class="btn-icon" href="index.php?c=login&a=Logout" title="Salir" style="color:#ef4444;"><i class="fas fa-right-from-bracket"></i></a>
    </div>
</div>

<div class="body">

    <!-- STATS -->
    <div class="stat-strip">
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(229,90,43,.14);"><i class="fas fa-map-marked-alt" style="color:var(--orange);"></i></div>
            <div><div class="stat-pill-txt" id="st-total">—</div><div class="stat-pill-lbl">Total</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(34,197,94,.12);"><i class="fas fa-circle-check" style="color:var(--green);"></i></div>
            <div><div class="stat-pill-txt" id="st-activas">—</div><div class="stat-pill-lbl">Activas</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(239,68,68,.1);"><i class="fas fa-circle-xmark" style="color:var(--red);"></i></div>
            <div><div class="stat-pill-txt" id="st-inactivas">—</div><div class="stat-pill-lbl">Inactivas</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(56,189,248,.1);"><i class="fas fa-ticket" style="color:var(--blue);"></i></div>
            <div><div class="stat-pill-txt" id="st-gratuitas">—</div><div class="stat-pill-lbl">Gratuitas</div></div>
        </div>
        <div class="stat-pill">
            <div class="stat-pill-ico" style="background:rgba(245,158,11,.1);"><i class="fas fa-star" style="color:var(--amber);"></i></div>
            <div><div class="stat-pill-txt" id="st-promedio">—</div><div class="stat-pill-lbl">Puntaje promedio</div></div>
        </div>
    </div>

    <!-- FILTROS -->
    <div class="filtros-wrap">
        <input type="text" id="inputBusqueda" placeholder="🔍  Buscar nombre, descripción o ciudad…" oninput="debounceSearch()">
        <select id="selEstado" onchange="resetPage();cargar()">
            <option value="">Todos los estados</option>
            <option value="activo">Activas</option>
            <option value="inactivo">Inactivas</option>
        </select>
        <select id="selCategoria" onchange="resetPage();cargar()">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre'] ?? 'Grupo '.$cat['id']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn-filtrar" onclick="resetPage();cargar()"><i class="fas fa-filter"></i> Filtrar</button>
        <div class="export-sep"></div>
        <button class="btn-export btn-excel" onclick="exportarExcel()"><i class="fas fa-file-excel"></i> Excel</button>
        <button class="btn-export btn-excel" style="background:rgba(239,68,68,.1);color:var(--red);border-color:rgba(239,68,68,.25);" onclick="window.print()"><i class="fas fa-file-pdf"></i> PDF</button>
        <div class="export-sep"></div>
        <button class="btn-nuevo" onclick="abrirModal()"><i class="fas fa-plus"></i> Nueva atracción</button>
    </div>

    <!-- TABLA -->
    <div class="tabla-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th class="sortable sort-active" data-col="id" onclick="toggleSort('id')">#ID <i class="fas fa-sort-up" id="ico-id"></i></th>
                    <th>Atracción</th>
                    <th class="sortable" data-col="categoria" onclick="toggleSort('categoria')">Categoría</th>
                    <th>Horario</th>
                    <th class="sortable" data-col="precio" onclick="toggleSort('precio')">Precio</th>
                    <th class="sortable" data-col="puntuacion" onclick="toggleSort('puntuacion')">Puntaje</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody">
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

<!-- ─── MODAL CREAR / EDITAR ─── -->
<div class="modal-overlay" id="modalForm">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitulo"><i class="fas fa-map-pin" style="color:var(--orange);"></i> Nueva Atracción</h3>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="fId">

            <!-- Información principal -->
            <div class="form-grid">
                <div class="form-sep"><i class="fas fa-info-circle"></i> Información principal</div>
                <div class="form-group full">
                    <label>Nombre *</label>
                    <input type="text" id="fNombre" placeholder="Ej: Mirador Killi Killi">
                </div>
                <div class="form-group full">
                    <label>Descripción</label>
                    <textarea id="fDescripcion" placeholder="Descripción del lugar…"></textarea>
                </div>
                <div class="form-group">
                    <label>Categoría (grupo)</label>
                    <select id="fCategoria">
                        <option value="">Sin categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre'] ?? 'Grupo '.$cat['id']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="fEstado">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>

            <!-- Ubicación -->
            <div class="form-grid">
                <div class="form-sep"><i class="fas fa-map-marker-alt"></i> Ubicación</div>
                <div class="form-group full">
                    <label>Dirección / Zona</label>
                    <input type="text" id="fDireccion" placeholder="Ej: Calle Comercio esq. Mercado, Centro">
                </div>
                <div class="form-group">
                    <label>Zona / Barrio</label>
                    <input type="text" id="fZona" placeholder="Ej: Centro Histórico">
                </div>
                <div class="form-group">
                    <label>Ciudad</label>
                    <input type="text" id="fCiudad" value="La Paz">
                </div>
                <div class="form-group">
                    <label>País</label>
                    <input type="text" id="fPais" value="Bolivia">
                </div>
                <div class="form-group">
                    <label>Latitud</label>
                    <input type="number" id="fLat" step="0.000001" placeholder="-16.5000">
                </div>
                <div class="form-group">
                    <label>Longitud</label>
                    <input type="number" id="fLng" step="0.000001" placeholder="-68.1500">
                </div>
            </div>

            <!-- Horario -->
            <div class="form-grid">
                <div class="form-sep"><i class="fas fa-clock"></i> Horario y días</div>
                <div class="form-group">
                    <label>Apertura</label>
                    <input type="time" id="fApertura">
                </div>
                <div class="form-group">
                    <label>Cierre</label>
                    <input type="time" id="fCierre">
                </div>
                <div class="form-group full">
                    <label>Días de operación</label>
                    <div class="dias-grid" id="diasGrid">
                        <?php
                        $diasMap = ['lunes'=>'Lun','martes'=>'Mar','miercoles'=>'Mié','jueves'=>'Jue','viernes'=>'Vie','sabado'=>'Sáb','domingo'=>'Dom'];
                        foreach ($diasMap as $val => $label):
                        ?>
                        <input type="checkbox" class="dia-check" id="dia_<?= $val ?>" value="<?= $val ?>">
                        <label class="dia-label" for="dia_<?= $val ?>"><?= $label ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tiempo estimado -->
            <div class="form-grid">
                <div class="form-sep"><i class="fas fa-hourglass-half"></i> Tiempo estimado</div>
                <div class="form-group">
                    <label>Tiempo visita</label>
                    <input type="text" id="fVisita" placeholder="Ej: 2 horas, 45 minutos, 3 dias">
                </div>
                <div class="form-group">
                    <label>Tiempo traslado</label>
                    <input type="text" id="fTraslado" placeholder="Ej: 15 minutos, 1 hora">
                </div>
            </div>

            <!-- Precio -->
            <div class="form-grid triple">
                <div class="form-sep"><i class="fas fa-tag"></i> Precio</div>
                <div class="form-group">
                    <label>Precio de entrada</label>
                    <input type="number" id="fPrecio" min="0" step="0.5" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Moneda</label>
                    <select id="fMoneda">
                        <option value="BOB">BOB</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Puntaje actual</label>
                    <input type="number" id="fPuntaje" min="0" max="5" step="0.1" placeholder="0.0" readonly style="opacity:.6;cursor:not-allowed;">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ═══════════════════════════════════════════════════════════════════════════
//  ESTADO
// ═══════════════════════════════════════════════════════════════════════════
const pag   = { page: 1, total: 0, pages: 1 };
let sortCol = 'id', sortOrd = 'asc';
let _dTimer = null;
let _cache  = [];

function debounceSearch() {
    clearTimeout(_dTimer);
    _dTimer = setTimeout(() => { resetPage(); cargar(); }, 380);
}
function resetPage() { pag.page = 1; }

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
    const activeTh = document.querySelector(`[data-col="${col}"]`);
    if (activeTh) activeTh.classList.add('sort-active');
    const icoActive = document.getElementById('ico-' + col);
    if (icoActive) icoActive.className = sortOrd === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
    resetPage(); cargar();
}

// ═══════════════════════════════════════════════════════════════════════════
//  CARGAR TABLA
// ═══════════════════════════════════════════════════════════════════════════
function cargar() {
    const q    = encodeURIComponent(document.getElementById('inputBusqueda').value.trim());
    const est  = document.getElementById('selEstado').value;
    const cat  = document.getElementById('selCategoria').value;

    document.getElementById('tbody').innerHTML =
        '<tr><td colspan="8" style="padding:28px;"><div class="skeleton" style="height:18px;"></div></td></tr>';

    const url = `index.php?c=adminatracciones&a=Listar&q=${q}&estado=${est}&categoria_id=${cat}&sort=${sortCol}&orden=${sortOrd}&page=${pag.page}`;

    fetch(url)
        .then(r => r.json())
        .then(d => {
            if (!d.success) { renderVacia(d.message || 'Error'); return; }
            pag.total  = d.total;
            pag.pages  = d.paginas;
            _cache     = d.datos;
            renderTabla(d.datos);
            renderPag();
        })
        .catch(() => renderVacia('Error de conexión'));
}

// ═══════════════════════════════════════════════════════════════════════════
//  RENDER
// ═══════════════════════════════════════════════════════════════════════════
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function renderTabla(items) {
    if (!items.length) { renderVacia('Sin atracciones con esos filtros'); return; }

    const rows = items.map(a => {
        const inactiva = a.estado === 'inactivo';
        const punt = parseFloat(a.puntuacion_promedio || 0);
        let stars = '';
        for (let i = 1; i <= 5; i++)
            stars += `<i class="fas fa-star ${i <= Math.round(punt) ? 'f' : 'e'}"></i>`;

        const horario = (() => {
            if (!a.horario_apertura) return '<span style="color:var(--muted)">—</span>';
            const ap = a.horario_apertura.slice(0,5);
            const ci = a.horario_cierre  ? a.horario_cierre.slice(0,5) : '';
            return `<span style="font-size:.75rem;">${ap}${ci?' – '+ci:''}</span>`;
        })();

        const precio = parseFloat(a.precio_entrada || 0) === 0
            ? '<span class="badge b-gratis">Gratis</span>'
            : `<span class="badge b-pago">${parseFloat(a.precio_entrada).toFixed(0)} ${esc(a.moneda||'BOB')}</span>`;

        const catNom = esc(a.categoria_nombre || '—');
        const ini    = (a.nombre||'?')[0].toUpperCase();

        const toggleBtn = inactiva
            ? `<button class="btn-sm btn-toggle-on" onclick="toggleEstado(${a.id},this)" title="Activar"><i class="fas fa-eye"></i></button>`
            : `<button class="btn-sm btn-toggle-off" onclick="toggleEstado(${a.id},this)" title="Desactivar"><i class="fas fa-eye-slash"></i></button>`;

        const delBtn = inactiva
            ? `<button class="btn-sm btn-del" onclick="eliminar(${a.id},'${esc(a.nombre||'')}')" title="Eliminar"><i class="fas fa-trash"></i></button>`
            : `<button class="btn-sm btn-del" style="opacity:.25;cursor:not-allowed;" title="Solo se pueden eliminar las inactivas"><i class="fas fa-trash"></i></button>`;

        return `<tr class="${inactiva ? 'row-inactiva' : ''}" onclick="abrirModalEditar(${a.id})">
            <td style="color:var(--muted);font-size:.7rem;">${a.id}</td>
            <td>
                <div style="display:flex;align-items:center;gap:9px;">
                    <span class="av av-foto" onclick="event.stopPropagation();abrirFotos(${a.id},'${esc(a.nombre||'').replace(/'/g, "\\'")}')" title="Gestionar fotos">
                        <img src="uploads/img_atracciones/${a.id}_1.jpg" alt="" loading="lazy" onerror="this.remove()">${ini}
                    </span>
                    <div>
                        <div style="font-weight:700;font-size:.82rem;">${esc(a.nombre)}</div>
                        <div style="font-size:.68rem;color:var(--muted);">${esc(a.ciudad||'')}, ${esc(a.pais||'')}</div>
                    </div>
                </div>
            </td>
            <td style="font-size:.75rem;color:var(--purple);">${catNom}</td>
            <td>${horario}</td>
            <td>${precio}</td>
            <td>
                <div class="stars">${stars}</div>
                <div style="font-size:.65rem;color:var(--muted);margin-top:1px;">${punt.toFixed(1)}/5</div>
            </td>
            <td><span class="badge b-${a.estado}">${a.estado}</span></td>
            <td onclick="event.stopPropagation();">
                <div class="acciones">
                    <button class="btn-sm btn-edit" onclick="abrirModalEditar(${a.id})" title="Editar"><i class="fas fa-pen"></i></button>
                    <button class="btn-sm btn-fotos" onclick="abrirFotos(${a.id},'${esc(a.nombre||'').replace(/'/g, "\\'")}')" title="Fotos"><i class="fas fa-images"></i></button>
                    ${toggleBtn}
                    ${delBtn}
                </div>
            </td>
        </tr>`;
    }).join('');

    document.getElementById('tbody').innerHTML = rows;
}

// ═══════════════════════════════════════════════════════════════════════════
//  GESTIÓN DE FOTOS
//  Propias (subidas o de Wikimedia): subir, elegir principal, quitar (recuperable).
//  Si tiene menos de 4, la galería del sitio completa con fotos referenciales del tipo de lugar.
// ═══════════════════════════════════════════════════════════════════════════
let fotosAtr = { id: 0, nombre: '' };
const SW_FOTOS = document.documentElement.classList.contains('tema-claro') ? {} : { background: '#1c1c32', color: '#f0f0f5' };

function abrirFotos(id, nombre) {
    fotosAtr = { id, nombre };
    Swal.fire({
        ...SW_FOTOS,
        title: `<i class="fas fa-images" style="color:#0ea5e9"></i> Fotos — ${esc(nombre)}`,
        html: '<div id="fgCont" class="fg-wrap"><div class="fg-vacio">Cargando…</div></div>',
        width: 900,
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: () => cargarFotos()
    }).then(() => cargarDatos && cargarDatos());   // refresca miniaturas de la tabla
}

function cargarFotos() {
    fetch(`index.php?c=adminatracciones&a=Fotos&id=${fotosAtr.id}`)
        .then(r => r.json())
        .then(pintarFotos)
        .catch(() => { document.getElementById('fgCont').innerHTML = '<div class="fg-vacio">Error de conexión</div>'; });
}

function pintarFotos(d) {
    const c = document.getElementById('fgCont');
    if (!c) return;
    if (!d.success) { c.innerHTML = `<div class="fg-vacio">${esc(d.message || 'Error')}</div>`; return; }
    const prop = d.propias || [];
    const libres = d.max - prop.length;

    let h = `<div class="fg-sec">Fotos propias <small>${prop.length} de ${d.max} · la primera es la principal en todo el sitio</small>
             <button class="fg-btn" onclick="buscarFotosWeb()"><i class="fas fa-globe"></i> Buscar más en Wikimedia</button></div>`;
    h += prop.length ? '<div class="fg-grid">' + prop.map(f => `
        <div class="fg-item ${f.n === 1 ? 'principal' : ''}">
            ${f.n === 1 ? '<span class="fg-tag">PRINCIPAL</span>' : ''}
            <a href="${f.url}" target="_blank"><img src="${f.url}" alt=""></a>
            <div class="fg-meta">${f.ancho}×${f.alto} · ${f.kb} KB<br>${esc(f.origen)}${f.credito && f.credito.autor ? ' · ' + esc(f.credito.autor) : ''}</div>
            <div class="fg-acc">
                ${f.n !== 1 ? `<button onclick="accionFoto('FotoPrincipal',{n:${f.n}})" title="Usar como principal"><i class="fas fa-star"></i> Principal</button>` : ''}
                <button class="fg-del" onclick="quitarFoto(${f.n})" title="Quitar (se puede recuperar)"><i class="fas fa-trash"></i> Quitar</button>
            </div>
        </div>`).join('') + '</div>'
        : '<div class="fg-vacio">Esta atracción no tiene fotos propias todavía.</div>';

    h += libres > 0
        ? `<label class="fg-drop" id="fgDrop" style="display:block;margin-top:10px">
             <input type="file" id="fgFile" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden onchange="subirFotos(this.files)">
             <i class="fas fa-cloud-upload-alt" style="font-size:1.3rem;color:#0ea5e9"></i><br>
             Arrastra imágenes aquí o haz clic para elegir (hasta ${libres}) · JPG, PNG o WEBP · mínimo 400×250 px
           </label>`
        : '<div class="fg-vacio">Llegó al máximo de fotos propias; quita alguna para subir otra.</div>';

    const refs = d.referenciales || [];
    if (refs.length) {
        h += `<div class="fg-sec">Fotos referenciales que completan la galería <small>${esc(d.tema || '')} · se dejan de usar al tener 4 fotos propias</small></div>
              <div class="fg-grid">${refs.map(r => `<div class="fg-item fg-ref"><img src="${esc(r)}" alt="">
                <div class="fg-acc" style="padding-top:8px"><button class="fg-del" onclick="quitarReferencial('${esc(r)}')" title="No relacionada: deja de usarse en todas las atracciones"><i class="fas fa-ban"></i> No corresponde</button></div>
              </div>`).join('')}</div>`;
    }
    const desc = d.descartadas || [];
    if (desc.length) {
        h += `<div class="fg-sec">Fotos quitadas <small>se pueden recuperar</small></div>
              <div class="fg-grid">${desc.map(x => `
                <div class="fg-item"><div class="fg-meta" style="padding:10px 8px">${esc(x.archivo)}<br>${esc(x.fecha)}</div>
                  <div class="fg-acc"><button onclick="accionFoto('FotoRestaurar',{archivo:'${esc(x.archivo)}'})"><i class="fas fa-undo"></i> Recuperar</button></div>
                </div>`).join('')}</div>`;
    }
    c.innerHTML = h;

    const drop = document.getElementById('fgDrop');
    if (drop) {
        ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('sobre'); }));
        ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('sobre'); }));
        drop.addEventListener('drop', e => subirFotos(e.dataTransfer.files));
    }
}

function avisoFotos(msg, ok = true) {
    const c = document.getElementById('fgCont');
    if (!c) return;
    const n = document.createElement('div');
    n.style.cssText = `margin-bottom:8px;padding:8px 10px;border-radius:8px;font-size:.75rem;background:${ok ? 'rgba(34,197,94,.12)' : 'rgba(239,68,68,.12)'};color:${ok ? '#16a34a' : '#ef4444'}`;
    n.innerHTML = msg;
    c.prepend(n);
}

function postFotos(accion, datos) {
    const fd = datos instanceof FormData ? datos : new FormData();
    if (!(datos instanceof FormData)) Object.entries(datos).forEach(([k, v]) => fd.append(k, v));
    fd.append('id', fotosAtr.id);
    return fetch(`index.php?c=adminatracciones&a=${accion}`, { method: 'POST', body: fd }).then(r => r.json());
}

function accionFoto(accion, datos, mensaje) {
    document.getElementById('fgCont').style.opacity = .5;
    return postFotos(accion, datos).then(d => {
        document.getElementById('fgCont').style.opacity = 1;
        if (!d.success) { avisoFotos(esc(d.message || 'Error'), false); return d; }
        pintarFotos(d);
        if (mensaje) avisoFotos(mensaje(d));
        return d;
    }).catch(() => { document.getElementById('fgCont').style.opacity = 1; avisoFotos('Error de conexión', false); });
}

function quitarFoto(n) {
    if (!confirm('¿Quitar esta foto? Quedará en "Fotos quitadas" y la podrás recuperar.')) return;
    accionFoto('FotoQuitar', { n });
}

function quitarReferencial(ruta) {
    if (!confirm('¿Quitar esta foto del grupo de referenciales? Dejará de mostrarse en todas las atracciones que la usaban y se reemplaza por otra del mismo tipo.')) return;
    accionFoto('FotoQuitarRef', { archivo: ruta });
}

function subirFotos(files) {
    if (!files || !files.length) return;
    const fd = new FormData();
    [...files].forEach(f => fd.append('fotos[]', f));
    accionFoto('FotoSubir', fd, d =>
        `${d.subidas} foto(s) subida(s).` + (d.errores && d.errores.length ? '<br>' + d.errores.map(esc).join('<br>') : ''));
}

function buscarFotosWeb() {
    avisoFotos('<i class="fas fa-spinner fa-spin"></i> Buscando fotos de este lugar en Wikimedia Commons… puede tardar hasta un minuto.');
    accionFoto('FotoBuscar', {}, d => d.nuevas > 0
        ? `Se agregaron ${d.nuevas} foto(s) del lugar.`
        : 'No se encontraron fotos nuevas que cumplan los criterios (del lugar, horizontales, ≥1000 px, sin personas). Puedes subirlas tú.');
}

function renderVacia(msg) {
    document.getElementById('tbody').innerHTML =
        `<tr><td colspan="8"><div class="empty-state"><i class="fas fa-map"></i><p>${msg}</p></div></td></tr>`;
}

// ═══════════════════════════════════════════════════════════════════════════
//  PAGINACIÓN
// ═══════════════════════════════════════════════════════════════════════════
function renderPag() {
    const { page, total, pages } = pag;
    document.getElementById('pagInfo').textContent = `Página ${page} de ${pages} · ${total} atracciones`;

    let b = `<button class="pag-btn" onclick="irPag(${page-1})" ${page<=1?'disabled':''}>‹</button>`;
    const s = Math.max(1, page-2), e = Math.min(pages, page+2);
    for (let i = s; i <= e; i++)
        b += `<button class="pag-btn ${i===page?'active':''}" onclick="irPag(${i})">${i}</button>`;
    b += `<button class="pag-btn" onclick="irPag(${page+1})" ${page>=pages?'disabled':''}>›</button>`;
    document.getElementById('pagBtns').innerHTML = b;
}
function irPag(p) { if (p < 1 || p > pag.pages) return; pag.page = p; cargar(); }

// ═══════════════════════════════════════════════════════════════════════════
//  STATS
// ═══════════════════════════════════════════════════════════════════════════
function cargarStats() {
    fetch('index.php?c=adminatracciones&a=Stats')
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;
            const s = d.stats;
            document.getElementById('st-total').textContent    = s.total     || 0;
            document.getElementById('st-activas').textContent  = s.activas   || 0;
            document.getElementById('st-inactivas').textContent= s.inactivas || 0;
            document.getElementById('st-gratuitas').textContent= s.gratuitas || 0;
            document.getElementById('st-promedio').textContent = s.promedio_puntaje || '—';
        }).catch(()=>{});
}

// ═══════════════════════════════════════════════════════════════════════════
//  MODAL
// ═══════════════════════════════════════════════════════════════════════════
const DIAS_LISTA = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];

function abrirModal(datos = null) {
    const edicion = !!datos;
    document.getElementById('modalTitulo').innerHTML = edicion
        ? '<i class="fas fa-pen" style="color:var(--orange);"></i> Editar Atracción'
        : '<i class="fas fa-plus" style="color:var(--green);"></i> Nueva Atracción';

    // Limpiar / rellenar
    document.getElementById('fId').value         = datos?.id           || '';
    document.getElementById('fNombre').value      = datos?.nombre       || '';
    document.getElementById('fDescripcion').value = datos?.descripcion  || '';
    document.getElementById('fZona').value        = datos?.ubicacion?.zona || '';
    document.getElementById('fDireccion').value   = datos?.direccion    || '';
    document.getElementById('fCiudad').value      = datos?.ciudad       || 'La Paz';
    document.getElementById('fPais').value        = datos?.pais         || 'Bolivia';
    document.getElementById('fLat').value         = datos?.latitud      || '';
    document.getElementById('fLng').value         = datos?.longitud     || '';
    document.getElementById('fApertura').value    = (datos?.horario_apertura || '').slice(0,5);
    document.getElementById('fCierre').value      = (datos?.horario_cierre   || '').slice(0,5);
    document.getElementById('fPrecio').value      = datos?.precio_entrada    ?? '';
    document.getElementById('fMoneda').value      = datos?.moneda       || 'BOB';
    document.getElementById('fPuntaje').value     = datos?.puntuacion_promedio || '0';
    document.getElementById('fCategoria').value   = datos?.atraccion_categoria_id || '';
    document.getElementById('fEstado').value      = datos?.estado       || 'activo';

    // Tiempo estimado
    const te = datos?.tiempo_estimado || {};
    document.getElementById('fVisita').value   = te.visita   || '';
    document.getElementById('fTraslado').value = te.traslado || '';

    // Días
    const diasOp = datos?.dias_operacion || {};
    DIAS_LISTA.forEach(d => {
        const cb = document.getElementById('dia_' + d);
        if (cb) cb.checked = diasOp[d] === true || diasOp[d] === 1;
    });

    document.getElementById('modalForm').classList.add('show');
}

function abrirModalEditar(id) {
    fetch(`index.php?c=adminatracciones&a=Ver&id=${id}`)
        .then(r => r.json())
        .then(d => {
            if (d.success) abrirModal(d.atraccion);
            else Swal.fire({ title:'Error', text: d.message, icon:'error', background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
        })
        .catch(() => Swal.fire({ title:'Error de conexión', icon:'error', background:'#1c1c32', color:'#f0f0f5' }));
}

function cerrarModal() { document.getElementById('modalForm').classList.remove('show'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
document.getElementById('modalForm').addEventListener('click', function(e) { if (e.target === this) cerrarModal(); });

// ═══════════════════════════════════════════════════════════════════════════
//  GUARDAR (crear o editar)
// ═══════════════════════════════════════════════════════════════════════════
function guardar() {
    const id     = document.getElementById('fId').value;
    const nombre = document.getElementById('fNombre').value.trim();
    if (!nombre) {
        Swal.fire({ title:'Campo requerido', text:'El nombre es obligatorio', icon:'warning', background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
        return;
    }

    const diasSel = DIAS_LISTA.filter(d => document.getElementById('dia_' + d)?.checked);

    const payload = {
        id:                     id || undefined,
        nombre,
        descripcion:            document.getElementById('fDescripcion').value.trim(),
        zona:                   document.getElementById('fZona').value.trim(),
        direccion:              document.getElementById('fDireccion').value.trim(),
        ciudad:                 document.getElementById('fCiudad').value.trim(),
        pais:                   document.getElementById('fPais').value.trim(),
        latitud:                document.getElementById('fLat').value || null,
        longitud:               document.getElementById('fLng').value || null,
        horario_apertura:       document.getElementById('fApertura').value || null,
        horario_cierre:         document.getElementById('fCierre').value || null,
        dias_operacion:         diasSel,
        precio_entrada:         document.getElementById('fPrecio').value || 0,
        moneda:                 document.getElementById('fMoneda').value,
        visita:                 document.getElementById('fVisita').value.trim(),
        traslado:               document.getElementById('fTraslado').value.trim(),
        estado:                 document.getElementById('fEstado').value,
        atraccion_categoria_id: document.getElementById('fCategoria').value || null,
    };

    const accion = id ? 'Actualizar' : 'Crear';
    const btn    = document.getElementById('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';

    fetch(`index.php?c=adminatracciones&a=${accion}`, {
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
            background: '#1c1c32', color: '#f0f0f5', confirmButtonColor: '#e55a2b',
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
//  TOGGLE ESTADO
// ═══════════════════════════════════════════════════════════════════════════
function toggleEstado(id, btn) {
    fetch('index.php?c=adminatracciones&a=ToggleEstado', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            const nuevo = d.nuevo_estado;
            const fila  = btn.closest('tr');
            // Actualizar badge
            const badge = fila.querySelector('.badge');
            if (badge) { badge.className = 'badge b-' + nuevo; badge.textContent = nuevo; }
            // Cambiar opacidad fila
            fila.classList.toggle('row-inactiva', nuevo === 'inactivo');
            // Cambiar botón toggle
            if (nuevo === 'inactivo') {
                btn.className = 'btn-sm btn-toggle-on';
                btn.title = 'Activar';
                btn.innerHTML = '<i class="fas fa-eye"></i>';
                // Habilitar botón eliminar
                const delBtn = fila.querySelector('.btn-del');
                if (delBtn) { delBtn.style.opacity='1'; delBtn.style.cursor='pointer'; }
            } else {
                btn.className = 'btn-sm btn-toggle-off';
                btn.title = 'Desactivar';
                btn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                // Deshabilitar botón eliminar
                const delBtn = fila.querySelector('.btn-del');
                if (delBtn) { delBtn.style.opacity='.25'; delBtn.style.cursor='not-allowed'; }
            }
            cargarStats();
        } else {
            Swal.fire({ title:'Error', text: d.message, icon:'error', background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
        }
    })
    .catch(() => Swal.fire({ title:'Error de conexión', icon:'error', background:'#1c1c32', color:'#f0f0f5' }));
}

// ═══════════════════════════════════════════════════════════════════════════
//  ELIMINAR
// ═══════════════════════════════════════════════════════════════════════════
function eliminar(id, nombre) {
    Swal.fire({
        title: '¿Eliminar atracción?',
        html: `<strong>${nombre}</strong><br><span style="font-size:.8rem;color:rgba(240,240,245,.5);">Esta acción es irreversible. Solo se pueden eliminar atracciones inactivas.</span>`,
        icon: 'warning',
        background: '#1c1c32', color: '#f0f0f5',
        confirmButtonColor: '#ef4444', cancelButtonColor: 'rgba(255,255,255,.08)',
        confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar', showCancelButton: true,
    }).then(res => {
        if (!res.isConfirmed) return;
        fetch('index.php?c=adminatracciones&a=Eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        })
        .then(r => r.json())
        .then(d => {
            Swal.fire({
                title: d.success ? 'Eliminada' : 'Error', text: d.message,
                icon: d.success ? 'success' : 'error',
                background: '#1c1c32', color: '#f0f0f5', confirmButtonColor: '#e55a2b',
            });
            if (d.success) { cargar(); cargarStats(); }
        })
        .catch(() => Swal.fire({ title:'Error de conexión', icon:'error', background:'#1c1c32', color:'#f0f0f5' }));
    });
}

// ═══════════════════════════════════════════════════════════════════════════
//  EXPORTAR EXCEL
// ═══════════════════════════════════════════════════════════════════════════
function exportarExcel() {
    if (!_cache.length) {
        Swal.fire({ title:'Sin datos', text:'Cargá la tabla primero.', icon:'info', background:'#1c1c32', color:'#f0f0f5', confirmButtonColor:'#e55a2b' });
        return;
    }
    const filas = _cache.map(a => ({
        'ID':          a.id,
        'Nombre':      a.nombre,
        'Descripción': a.descripcion,
        'Ciudad':      a.ciudad,
        'País':        a.pais,
        'Dirección':   a.direccion,
        'Categoría':   a.categoria_nombre || '—',
        'Horario':     `${(a.horario_apertura||'').slice(0,5)} – ${(a.horario_cierre||'').slice(0,5)}`,
        'Precio':      a.precio_entrada,
        'Moneda':      a.moneda,
        'Visita':      a.tiempo_estimado?.visita   || '',
        'Traslado':    a.tiempo_estimado?.traslado || '',
        'Puntaje':     a.puntuacion_promedio,
        'Estado':      a.estado,
        'Actualizado': a.updated_at?.slice(0,10) || '',
    }));

    const ws = XLSX.utils.json_to_sheet(filas);
    ws['!cols'] = [{wch:6},{wch:40},{wch:45},{wch:14},{wch:12},{wch:30},{wch:28},{wch:14},{wch:8},{wch:7},{wch:16},{wch:16},{wch:8},{wch:10},{wch:12}];
    const wb   = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Atracciones');
    XLSX.writeFile(wb, `etreva_atracciones_${new Date().toISOString().slice(0,10)}.xlsx`);
}

// ═══════════════════════════════════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════════════════════════════════
document.getElementById('btnRefresh').addEventListener('click', () => { resetPage(); cargar(); cargarStats(); });
cargar();
cargarStats();
</script>
</body>
</html>