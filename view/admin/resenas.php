<?php include 'view/menu.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reseñas - Etreva Travel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

<style>
:root {
    --bg:      #0f1117;
    --bg2:     #161b2e;
    --surf:    #1c2235;
    --surf2:   #222b42;
    --border:  rgba(255,255,255,0.07);
    --gold:    #f59e0b;
    --gold2:   #fbbf24;
    --green:   #10b981;
    --red:     #ef4444;
    --blue:    #6366f1;
    --text:    #e2e8f0;
    --muted:   rgba(255,255,255,0.4);
    --r:       16px;
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{background:var(--bg)!important;color:var(--text)!important;font-family:'DM Sans',sans-serif;min-height:100vh}
main{margin-left:280px;padding:0;background:var(--bg)!important;min-height:100vh;transition:all .4s}
main.min-main{margin-left:85px}
@media(max-width:768px){main{margin-left:0!important}}

/* ─── HERO ─── */
.hero{
    background:linear-gradient(135deg,#0a0d1a 0%,#0d1a12 50%,var(--bg) 100%);
    padding:46px 28px 32px;border-bottom:1px solid var(--border);
    position:relative;overflow:hidden;
}
.hero::before{
    content:'';position:absolute;top:-80px;right:-80px;
    width:420px;height:420px;
    background:radial-gradient(circle,rgba(16,185,129,.1) 0%,transparent 70%);
    pointer-events:none;
}
.hero h1{
    font-family:'Syne',sans-serif;font-size:2.2rem;font-weight:800;
    background:linear-gradient(135deg,#fff 0%,#6ee7b7 100%);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    background-clip:text;margin-bottom:6px;
}
.hero p{color:var(--muted);font-size:.92rem;font-weight:300}

/* ─── STATS ─── */
.stats-bar{
    display:flex;gap:12px;padding:20px 28px 0;flex-wrap:wrap;
}
.stat-chip{
    background:var(--surf);border:1px solid var(--border);border-radius:50px;
    padding:8px 18px;display:flex;align-items:center;gap:8px;font-size:.78rem;
}
.stat-chip i{font-size:.72rem}
.stat-chip .val{font-family:'Syne',sans-serif;font-weight:800;color:#fff;font-size:.9rem}

/* ─── FILTROS ─── */
.filtros-wrap{
    padding:20px 28px;background:var(--bg2);border-bottom:1px solid var(--border);
    display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;
}
.filtros-wrap label{font-size:.67rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);font-weight:700;display:block;margin-bottom:3px}
.filtros-wrap select,.filtros-wrap input{
    background:var(--surf2);border:1px solid var(--border);color:var(--text);
    border-radius:9px;padding:7px 12px;font-size:.78rem;outline:none;
    transition:border-color .2s;min-width:120px;
}
.filtros-wrap select:focus,.filtros-wrap input:focus{border-color:rgba(16,185,129,.4)}
.filtros-wrap .search-wrap{flex:1;min-width:180px;position:relative}
.filtros-wrap .search-wrap input{width:100%;padding-left:34px}
.filtros-wrap .search-wrap i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.72rem}
.btn-buscar{
    background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);
    color:#34d399;border-radius:9px;padding:7px 16px;font-size:.78rem;
    font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:6px;
}
.btn-buscar:hover{background:rgba(16,185,129,.2);border-color:rgba(16,185,129,.5)}
.btn-reset{
    background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--muted);
    border-radius:9px;padding:7px 12px;font-size:.78rem;cursor:pointer;transition:all .2s;
}
.btn-reset:hover{color:var(--text);border-color:rgba(255,255,255,.15)}
.toggle-mias{
    display:flex;align-items:center;gap:8px;cursor:pointer;
    background:var(--surf2);border:1px solid var(--border);border-radius:9px;
    padding:7px 14px;font-size:.78rem;color:var(--muted);transition:all .2s;
    user-select:none;
}
.toggle-mias.active{
    background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.3);color:#a5b4fc;
}
.toggle-mias i{font-size:.75rem}

/* ─── GRILLA ─── */
.resenas-wrap{padding:24px 28px 60px}
.resenas-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:16px;
}

/* ─── CARD RESEÑA ─── */
.resena-card{
    background:var(--surf);border:1px solid var(--border);
    border-radius:var(--r);display:flex;flex-direction:column;
    transition:transform .2s,box-shadow .2s,border-color .2s;
    animation:fadeUp .35s ease both;
}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.resena-card:hover{
    transform:translateY(-4px);
    box-shadow:0 14px 36px rgba(0,0,0,.45);
    border-color:rgba(16,185,129,.2);
}

/* Imagen */
.rc-img{
    height:130px;background-size:cover;background-position:center;
    position:relative;border-radius:var(--r) var(--r) 0 0;overflow:hidden;
}
.rc-img-ov{
    position:absolute;inset:0;
    background:linear-gradient(to top,rgba(10,13,22,.95) 0%,transparent 55%);
}
.rc-badges{position:absolute;top:9px;left:10px;display:flex;gap:5px;flex-wrap:wrap}
.rc-badge{
    font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.4px;
    padding:3px 9px;border-radius:5px;backdrop-filter:blur(6px);color:#fff;
    background:rgba(0,0,0,.5);border:1px solid rgba(255,255,255,.1);
}
.rc-badge-pos{background:rgba(16,185,129,.8);border-color:transparent}
.rc-badge-neg{background:rgba(239,68,68,.8);border-color:transparent}
.rc-badge-mia{background:rgba(99,102,241,.8);border-color:transparent}

/* Estrellas en imagen */
.rc-stars{
    position:absolute;bottom:9px;left:10px;
    display:flex;align-items:center;gap:2px;
    background:rgba(0,0,0,.6);backdrop-filter:blur(6px);
    border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:4px 8px;
}
.rc-stars i{font-size:.55rem;color:var(--gold)}
.rc-stars i.empty{color:rgba(255,255,255,.15)}
.rc-stars span{color:#fff;font-size:.7rem;font-weight:700;margin-left:4px}

/* Fecha en imagen */
.rc-fecha{
    position:absolute;bottom:9px;right:10px;
    background:rgba(0,0,0,.6);backdrop-filter:blur(6px);
    border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:4px 9px;
    font-size:.65rem;color:rgba(255,255,255,.6);
}

/* Body */
.rc-body{padding:13px 15px 10px;flex:1;display:flex;flex-direction:column}
.rc-atraccion{
    font-family:'Syne',sans-serif;font-size:.88rem;font-weight:700;
    color:#fff;margin-bottom:4px;line-height:1.3;
    display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;
}
.rc-usuario{
    font-size:.7rem;color:var(--muted);margin-bottom:8px;
    display:flex;align-items:center;gap:5px;
}
.rc-usuario i{font-size:.6rem;color:var(--green)}
.rc-comentario{
    font-size:.78rem;color:rgba(255,255,255,.65);line-height:1.55;
    flex:1;margin-bottom:10px;
    display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    cursor:pointer;
}
.rc-comentario:hover{color:rgba(255,255,255,.9)}

/* Tags */
.rc-tags{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:10px}
.rc-tag{
    background:rgba(16,185,129,.07);border:1px solid rgba(16,185,129,.18);
    color:rgba(16,185,129,.85);border-radius:50px;
    padding:2px 8px;font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;
}
.rc-tag.mal{background:rgba(239,68,68,.07);border-color:rgba(239,68,68,.18);color:rgba(239,68,68,.85)}

/* Footer */
.rc-foot{
    padding:9px 15px 12px;border-top:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;gap:8px;
}
.rc-cat{font-size:.65rem;color:var(--muted);display:flex;align-items:center;gap:4px}
.rc-cat i{font-size:.6rem;color:var(--green)}
.rc-actions{display:flex;gap:5px}
.btn-edit{
    background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);
    color:#a5b4fc;border-radius:7px;padding:5px 10px;font-size:.65rem;
    font-weight:700;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:4px;
}
.btn-edit:hover{background:rgba(99,102,241,.2);border-color:rgba(99,102,241,.4)}
.btn-del{
    background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.18);
    color:rgba(239,68,68,.8);border-radius:7px;padding:5px 10px;font-size:.65rem;
    font-weight:700;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:4px;
}
.btn-del:hover{background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.4);color:var(--red)}
.btn-expand{
    background:rgba(255,255,255,.04);border:1px solid var(--border);
    color:var(--muted);border-radius:7px;padding:5px 9px;font-size:.65rem;
    cursor:pointer;transition:all .15s;
}
.btn-expand:hover{color:var(--text);border-color:rgba(255,255,255,.15)}

/* ─── VACÍO ─── */
.res-vacio{text-align:center;padding:80px 20px}
.res-vacio .ico{
    width:72px;height:72px;background:var(--surf2);border:1px solid var(--border);
    border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;
}
.res-vacio .ico i{font-size:1.8rem;color:var(--green);opacity:.35}
.res-vacio h3{font-family:'Syne',sans-serif;font-size:1.1rem;color:rgba(255,255,255,.4);margin-bottom:6px}
.res-vacio p{font-size:.82rem;color:var(--muted)}

/* ─── PAGINACIÓN ─── */
.paginacion{
    display:flex;align-items:center;justify-content:space-between;
    padding:20px 28px;border-top:1px solid var(--border);font-size:.75rem;color:var(--muted);
}
.page-btns{display:flex;gap:4px}
.page-btn{
    padding:6px 12px;background:var(--surf2);border:1px solid var(--border);
    color:var(--muted);border-radius:8px;cursor:pointer;transition:all .15s;
    font-size:.73rem;background:none;
}
.page-btn:hover,.page-btn.active{background:rgba(16,185,129,.12);border-color:rgba(16,185,129,.3);color:#34d399}
.page-btn.active{font-weight:700}
.page-btn:disabled{opacity:.3;cursor:not-allowed}

/* ─── SPINNER ─── */
.spinner-grid{
    display:flex;justify-content:center;align-items:center;padding:80px;
    grid-column:1/-1;
}
.spinner-grid .spinner-border{color:var(--green);width:2.5rem;height:2.5rem}

/* ─── MODAL ─── */
.modal-content{background:var(--surf)!important;border:1px solid rgba(16,185,129,.2)!important;border-radius:16px!important}
.modal-header{border-bottom:1px solid var(--border)!important;padding:18px 22px!important}
.modal-footer{border-top:1px solid var(--border)!important}
.modal-title{font-family:'Syne',sans-serif;font-size:.95rem;font-weight:700;color:#fff!important}
.star-selector{display:flex;gap:6px;margin-bottom:12px}
.star-selector i{font-size:1.4rem;color:rgba(255,255,255,.2);cursor:pointer;transition:color .15s}
.star-selector i.active,.star-selector i:hover~i~i~i~i:not(.active),
.star-selector i.active~i.active{color:var(--gold)}
.star-selector i.hover-on{color:var(--gold)}
textarea.edit-txt{
    background:var(--bg2);border:1px solid var(--border);color:var(--text);
    border-radius:10px;padding:10px 12px;width:100%;font-size:.82rem;resize:vertical;
    min-height:90px;outline:none;transition:border-color .2s;
}
textarea.edit-txt:focus{border-color:rgba(16,185,129,.4)}

::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:var(--bg)}
::-webkit-scrollbar-thumb{background:var(--surf2);border-radius:3px}
</style>
</head>

<body>
<main id="mainContent">

<!-- ─── HERO ─── -->
<div class="hero">
    <h1>
        <i class="fas fa-comments me-2"
           style="font-size:1.6rem;color:#34d399;-webkit-text-fill-color:#34d399"></i>
        Reseñas de la comunidad
    </h1>
    <p>Opiniones reales de viajeros que visitaron lugares en La Paz</p>
    <div class="stats-bar" id="statsBar" style="padding:16px 0 0">
        <div class="stat-chip"><i class="fas fa-comments" style="color:#34d399"></i><span class="val" id="statTotal">—</span> <span style="color:var(--muted)">reseñas</span></div>
        <div class="stat-chip"><i class="fas fa-star" style="color:var(--gold)"></i><span class="val" id="statProm">—</span> <span style="color:var(--muted)">promedio</span></div>
        <div class="stat-chip"><i class="fas fa-thumbs-up" style="color:#34d399"></i><span class="val" id="statPos">—</span> <span style="color:var(--muted)">positivas</span></div>
        <div class="stat-chip"><i class="fas fa-thumbs-down" style="color:var(--red)"></i><span class="val" id="statNeg">—</span> <span style="color:var(--muted)">negativas</span></div>
    </div>
</div>

<!-- ─── FILTROS ─── -->
<div class="filtros-wrap">
    <div class="search-wrap">
        <label>Buscar</label>
        <i class="fas fa-search"></i>
        <input type="text" id="inpBuscar" placeholder="Lugar o comentario..." autocomplete="off">
    </div>
    <div>
        <label>Calificación</label>
        <select id="selCalif">
            <option value="">Todas</option>
            <option value="5-5">★★★★★ Solo 5 estrellas</option>
            <option value="4-5">★★★★+ 4 o más</option>
            <option value="3-5">★★★+ 3 o más</option>
            <option value="1-2">★★ 2 o menos</option>
            <option value="1-1">★ Solo 1 estrella</option>
        </select>
    </div>
    <div>
        <label>Tipo</label>
        <select id="selTipo">
            <option value="">Todos</option>
            <option value="pos">Solo positivos</option>
            <option value="neg">Solo negativos</option>
        </select>
    </div>
    <div>
        <label>Mostrar por página</label>
        <select id="selPorPagina">
            <option value="12">12</option>
            <option value="24">24</option>
            <option value="48">48</option>
        </select>
    </div>
    <div class="toggle-mias" id="btnSoloMias" onclick="toggleMias()">
        <i class="fas fa-user-circle"></i> Mis reseñas
    </div>
    <button class="btn-buscar" onclick="buscar()"><i class="fas fa-filter"></i> Filtrar</button>
    <button class="btn-reset"  onclick="resetFiltros()"><i class="fas fa-times"></i></button>
</div>

<!-- ─── CONTENIDO ─── -->
<div class="resenas-wrap">
    <div class="resenas-grid" id="resenasGrid">
        <!-- Cards inyectadas por JS -->
        <div class="spinner-grid">
            <div class="spinner-border"></div>
        </div>
    </div>
</div>

<!-- Paginación -->
<div class="paginacion" id="paginacion" style="display:none">
    <span id="paginfoTxt"></span>
    <div class="page-btns" id="pagBtns"></div>
</div>

<!-- ─── MODAL EXPANDIR COMENTARIO ─── -->
<div class="modal fade" id="modalVer" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerTitulo">Reseña completa</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalVerBody" style="padding:20px"></div>
        </div>
    </div>
</div>

<!-- ─── MODAL EDITAR ─── -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2" style="color:#818cf8"></i>Editar reseña</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:20px">
                <input type="hidden" id="editId">
                <div style="margin-bottom:14px">
                    <label style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);font-weight:700;display:block;margin-bottom:8px">Calificación</label>
                    <div class="star-selector" id="starSelector">
                        <i class="fas fa-star" data-val="1"></i>
                        <i class="fas fa-star" data-val="2"></i>
                        <i class="fas fa-star" data-val="3"></i>
                        <i class="fas fa-star" data-val="4"></i>
                        <i class="fas fa-star" data-val="5"></i>
                    </div>
                    <input type="hidden" id="editCalif" value="0">
                </div>
                <div>
                    <label style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);font-weight:700;display:block;margin-bottom:6px">Comentario</label>
                    <textarea class="edit-txt" id="editComentario" rows="4" placeholder="Contá tu experiencia..."></textarea>
                </div>
            </div>
            <div class="modal-footer" style="gap:8px">
                <button class="btn-reset" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn-buscar" onclick="guardarEdicion()">
                    <i class="fas fa-check"></i> Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>

<div id="contenedorModalDetalle"></div>

</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ─── Estado global ───
let estadoActual = {
    pagina: 1,
    por_pagina: 12,
    q: '',
    solo_mias: false,
    calif_min: null,
    calif_max: null,
    tipo: '',
    total: 0,
    paginas: 0,
};

let statsCalculados = false;

// ─── Categorías → iconos ───
const CAT_ICON = {
    'Trekking':        'fa-hiking',
    'Montañismo':      'fa-mountain',
    'Cultura':         'fa-landmark',
    'Downhill':        'fa-bicycle',
    'Vida Nocturna':   'fa-moon',
    'Gastronomía':     'fa-utensils',
    'Iconos Urbanos':  'fa-city',
    'Atracción':       'fa-map-marker-alt',
};

// Tags negativos (resaltar en rojo)
const TAGS_MALOS = ['muy_lleno','dificil_acceso','mal_servicio','caro','sucio','peligroso','cerrado','ruidoso'];

// ─── Cargar reseñas ───
function cargarResenas() {
    const grid = document.getElementById('resenasGrid');
    grid.innerHTML = `<div class="spinner-grid"><div class="spinner-border"></div></div>`;

    const params = new URLSearchParams({
        c: 'resenas',
        a: 'ObtenerTodas',
        pagina:    estadoActual.pagina,
        por_pagina: estadoActual.por_pagina,
        q:         estadoActual.q,
        solo_mias: estadoActual.solo_mias ? '1' : '0',
    });

    if (estadoActual.calif_min !== null) params.set('calif_min', estadoActual.calif_min);
    if (estadoActual.calif_max !== null) params.set('calif_max', estadoActual.calif_max);

    fetch('index.php?' + params.toString())
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                grid.innerHTML = `<div class="res-vacio" style="grid-column:1/-1">
                    <div class="ico"><i class="fas fa-exclamation-circle"></i></div>
                    <h3>Error al cargar</h3>
                    <p>${data.message || 'Intenta de nuevo'}</p>
                </div>`;
                return;
            }

            estadoActual.total   = data.total;
            estadoActual.paginas = data.paginas;

            renderResenas(data.resenas);
            renderPaginacion();
            calcularStats(data.resenas, data.total);
        })
        .catch(err => {
            console.error(err);
            grid.innerHTML = `<div class="res-vacio" style="grid-column:1/-1">
                <div class="ico"><i class="fas fa-wifi"></i></div>
                <h3>Error de conexión</h3><p>Verificá tu conexión a internet</p>
            </div>`;
        });
}

// ─── Render cards ───
function renderResenas(resenas) {
    const grid = document.getElementById('resenasGrid');
    document.getElementById('paginacion').style.display = estadoActual.total > 0 ? 'flex' : 'none';

    if (!resenas || resenas.length === 0) {
        grid.innerHTML = `<div class="res-vacio" style="grid-column:1/-1">
            <div class="ico"><i class="fas fa-comments"></i></div>
            <h3>Sin reseñas</h3>
            <p>No se encontraron reseñas con estos filtros.</p>
        </div>`;
        return;
    }

    // Filtro tipo en JS si se eligió
    let lista = resenas;
    if (estadoActual.tipo === 'pos') lista = resenas.filter(r => r.feedback_tipo === 'positivo');
    if (estadoActual.tipo === 'neg') lista = resenas.filter(r => r.feedback_tipo === 'negativo');

    if (lista.length === 0) {
        grid.innerHTML = `<div class="res-vacio" style="grid-column:1/-1">
            <div class="ico"><i class="fas fa-filter"></i></div>
            <h3>Sin resultados</h3><p>Probá cambiando los filtros</p>
        </div>`;
        return;
    }

    grid.innerHTML = lista.map((r, i) => buildCard(r, i)).join('');
}

function buildCard(r, idx) {
    const delay  = Math.min(idx * 0.04, 0.3);
    const estrs  = Math.round(parseFloat(r.calificacion || 0));
    const img    = r.imagen_url || 'assets/img/default.jpg';
    const tipo   = r.feedback_tipo || 'n/a';
    const esMia  = r.es_mia;
    const cat    = r.categoria || 'Atracción';
    const ico    = CAT_ICON[cat] || 'fa-map-marker-alt';
    const tags   = r.tags || [];
    const fecha  = r.created_at ? new Date(r.created_at).toLocaleDateString('es-BO', {day:'2-digit',month:'short',year:'2-digit'}) : '';
    const coment = r.comentario || 'Sin comentario';

    let starsHtml = '';
    for (let s=1;s<=5;s++) starsHtml += `<i class="fas fa-star${s>estrs?' empty':''}"></i>`;

    let badgesHtml = `<span class="rc-badge">${escHtml(cat.toUpperCase())}</span>`;
    if (tipo === 'positivo') badgesHtml += `<span class="rc-badge rc-badge-pos">✓ Positivo</span>`;
    if (tipo === 'negativo') badgesHtml += `<span class="rc-badge rc-badge-neg">✗ Negativo</span>`;
    if (esMia) badgesHtml += `<span class="rc-badge rc-badge-mia">✎ Mi reseña</span>`;

    let tagsHtml = '';
    tags.slice(0,4).forEach(t => {
        const esMal = TAGS_MALOS.includes(t.toLowerCase());
        tagsHtml += `<span class="rc-tag${esMal?' mal':''}">${escHtml(t.replace(/_/g,' '))}</span>`;
    });

    const accionesHtml = esMia ? `
        <button class="btn-edit" onclick="abrirEditar(${r.resena_id},${estrs},${JSON.stringify(coment).replace(/</g,'&lt;')})">
            <i class="fas fa-pen"></i> Editar
        </button>
        <button class="btn-del" onclick="eliminarResena(${r.resena_id})">
            <i class="fas fa-trash"></i>
        </button>` : '';

    return `<div class="resena-card" style="animation-delay:${delay}s" data-id="${r.resena_id}">
        <div class="rc-img" style="background-image:url('${escHtml(img)}')">
            <div class="rc-img-ov"></div>
            <div class="rc-badges">${badgesHtml}</div>
            <div class="rc-stars">${starsHtml}<span>${parseFloat(r.calificacion||0).toFixed(1)}</span></div>
            <div class="rc-fecha"><i class="fas fa-clock me-1"></i>${fecha}</div>
        </div>
        <div class="rc-body">
            <div class="rc-atraccion">${escHtml(r.atraccion_nombre || 'Atracción')}</div>
            <div class="rc-usuario">
                <i class="fas fa-user-circle"></i>
                ${escHtml(r.usuario_display || 'Usuario')}
                <span style="margin-left:4px;color:rgba(255,255,255,.2)">·</span>
                <span>${escHtml(r.ciudad || 'La Paz')}</span>
            </div>
            <div class="rc-comentario" onclick="verComentario(${JSON.stringify(r).replace(/</g,'&lt;')})">
                "${escHtml(coment)}"
            </div>
            ${tagsHtml ? `<div class="rc-tags">${tagsHtml}</div>` : ''}
        </div>
        <div class="rc-foot">
            <div class="rc-cat">
                <i class="fas ${ico}"></i>
                ${escHtml(cat)}
            </div>
            <div class="rc-actions">
                <button class="btn-expand" onclick="verComentario(${JSON.stringify(r).replace(/</g,'&lt;')})">
                    <i class="fas fa-expand-alt"></i>
                </button>
                ${accionesHtml}
            </div>
        </div>
    </div>`;
}

// ─── Render paginación ───
function renderPaginacion() {
    const total   = estadoActual.total;
    const pag     = estadoActual.pagina;
    const paginas = estadoActual.paginas;
    const ppag    = estadoActual.por_pagina;
    const desde   = ((pag-1)*ppag)+1;
    const hasta   = Math.min(pag*ppag, total);

    document.getElementById('paginfoTxt').textContent = `Mostrando ${desde}–${hasta} de ${total} reseñas`;

    let btns = '';
    if (pag > 1) btns += `<button class="page-btn" onclick="irPag(${pag-1})"><i class="fas fa-chevron-left"></i></button>`;
    const ini = Math.max(1, pag-2);
    const fin = Math.min(paginas, pag+2);
    for (let p=ini; p<=fin; p++) {
        btns += `<button class="page-btn${p===pag?' active':''}" onclick="irPag(${p})">${p}</button>`;
    }
    if (pag < paginas) btns += `<button class="page-btn" onclick="irPag(${pag+1})"><i class="fas fa-chevron-right"></i></button>`;
    document.getElementById('pagBtns').innerHTML = btns;
    document.getElementById('paginacion').style.display = 'flex';
}

function irPag(p) {
    estadoActual.pagina = p;
    cargarResenas();
    window.scrollTo({top: 0, behavior:'smooth'});
}

// ─── Stats rápidos de la página visible ───
function calcularStats(resenas, total) {
    document.getElementById('statTotal').textContent = total;
    if (!resenas.length) return;
    const califs = resenas.map(r => parseFloat(r.calificacion||0)).filter(c=>c>0);
    const prom   = califs.length ? (califs.reduce((a,b)=>a+b,0)/califs.length).toFixed(1) : '—';
    const pos    = resenas.filter(r => r.feedback_tipo==='positivo').length;
    const neg    = resenas.filter(r => r.feedback_tipo==='negativo').length;
    document.getElementById('statProm').textContent = prom;
    document.getElementById('statPos').textContent  = pos;
    document.getElementById('statNeg').textContent  = neg;
}

// ─── Filtros ───
function buscar() {
    estadoActual.pagina    = 1;
    estadoActual.q         = document.getElementById('inpBuscar').value.trim();
    estadoActual.tipo      = document.getElementById('selTipo').value;
    estadoActual.por_pagina = parseInt(document.getElementById('selPorPagina').value);

    const cv = document.getElementById('selCalif').value;
    if (cv) {
        const [mn, mx] = cv.split('-');
        estadoActual.calif_min = parseInt(mn);
        estadoActual.calif_max = parseInt(mx);
    } else {
        estadoActual.calif_min = null;
        estadoActual.calif_max = null;
    }
    cargarResenas();
}

function resetFiltros() {
    document.getElementById('inpBuscar').value = '';
    document.getElementById('selCalif').value  = '';
    document.getElementById('selTipo').value   = '';
    document.getElementById('selPorPagina').value = '12';
    document.getElementById('btnSoloMias').classList.remove('active');
    estadoActual = { ...estadoActual, pagina:1, q:'', solo_mias:false, calif_min:null, calif_max:null, tipo:'' };
    cargarResenas();
}

function toggleMias() {
    estadoActual.solo_mias = !estadoActual.solo_mias;
    estadoActual.pagina    = 1;
    document.getElementById('btnSoloMias').classList.toggle('active', estadoActual.solo_mias);
    cargarResenas();
}

// Enter en buscador
document.getElementById('inpBuscar').addEventListener('keydown', e => { if (e.key==='Enter') buscar(); });

// ─── Ver comentario completo ───
function verComentario(r) {
    if (typeof r === 'string') r = JSON.parse(r);
    const tags = (r.tags||[]).map(t => {
        const mal = TAGS_MALOS.includes(t.toLowerCase());
        return `<span class="rc-tag${mal?' mal':''}">${escHtml(t.replace(/_/g,' '))}</span>`;
    }).join('');

    const estrs = Math.round(parseFloat(r.calificacion||0));
    let stars = '';
    for (let s=1;s<=5;s++) stars += `<i class="fas fa-star${s>estrs?' empty':''}" style="color:${s<=estrs?'var(--gold)':'rgba(255,255,255,.15)'}"></i>`;

    document.getElementById('modalVerTitulo').textContent = r.atraccion_nombre || 'Reseña';
    document.getElementById('modalVerBody').innerHTML = `
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
            ${stars}
            <span style="color:#fff;font-weight:700;font-size:.9rem">${parseFloat(r.calificacion||0).toFixed(1)}/5</span>
            <span style="color:var(--muted);font-size:.75rem;margin-left:4px">${r.feedback_tipo==='positivo'?'<span style="color:#34d399">✓ Positivo</span>':'<span style="color:#f87171">✗ Negativo</span>'}</span>
        </div>
        <div style="background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:14px;font-size:.82rem;line-height:1.65;color:rgba(255,255,255,.8)">
            "${escHtml(r.comentario||'Sin comentario')}"
        </div>
        <div style="display:flex;gap:12px;font-size:.75rem;color:var(--muted);margin-bottom:12px;flex-wrap:wrap">
            <span><i class="fas fa-user-circle me-1" style="color:#34d399"></i>${escHtml(r.usuario_display||'Usuario')}</span>
            <span><i class="fas fa-map-marker-alt me-1" style="color:#34d399"></i>${escHtml(r.ciudad||'La Paz')}</span>
            <span><i class="fas fa-calendar me-1" style="color:#34d399"></i>${r.created_at ? new Date(r.created_at).toLocaleDateString('es-BO') : '—'}</span>
        </div>
        ${tags ? `<div class="rc-tags">${tags}</div>` : ''}
    `;
    new bootstrap.Modal(document.getElementById('modalVer')).show();
}

// ─── Editar reseña ───
function abrirEditar(id, calif, comentario) {
    document.getElementById('editId').value         = id;
    document.getElementById('editComentario').value = comentario;
    document.getElementById('editCalif').value      = calif;
    actualizarEstrellas(calif);
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}

function actualizarEstrellas(val) {
    document.querySelectorAll('#starSelector i').forEach((el, i) => {
        el.classList.toggle('active', i < val);
        el.style.color = i < val ? 'var(--gold)' : 'rgba(255,255,255,.2)';
    });
}

document.querySelectorAll('#starSelector i').forEach((el, i) => {
    el.addEventListener('click', () => {
        document.getElementById('editCalif').value = i+1;
        actualizarEstrellas(i+1);
    });
    el.addEventListener('mouseenter', () => {
        document.querySelectorAll('#starSelector i').forEach((e2, j) => {
            e2.style.color = j<=i ? 'var(--gold)' : 'rgba(255,255,255,.2)';
        });
    });
    el.addEventListener('mouseleave', () => {
        const v = parseInt(document.getElementById('editCalif').value||0);
        actualizarEstrellas(v);
    });
});

function guardarEdicion() {
    const id      = parseInt(document.getElementById('editId').value||0);
    const calif   = parseInt(document.getElementById('editCalif').value||0);
    const coment  = document.getElementById('editComentario').value.trim();

    if (!id || calif<1||calif>5) { toastr.warning('Seleccioná una calificación'); return; }

    fetch('index.php?c=resenas&a=Editar', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({resena_id:id, calificacion:calif, comentario:coment})
    })
    .then(r=>r.json())
    .then(res=>{
        if (res.success) {
            toastr.success('Reseña actualizada');
            bootstrap.Modal.getInstance(document.getElementById('modalEditar')).hide();
            cargarResenas();
        } else {
            toastr.error(res.message||'No se pudo actualizar');
        }
    })
    .catch(()=>toastr.error('Error de conexión'));
}

// ─── Eliminar reseña ───
function eliminarResena(id) {
    Swal.fire({
        title: '¿Eliminar reseña?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#1c2235', color: '#e2e8f0',
        backdrop: 'rgba(10,13,22,.85)',
    }).then(r => {
        if (!r.isConfirmed) return;
        $.post('index.php?c=resenas&a=Eliminar', {id}, function(res) {
            if (res.success) {
                toastr.success('Reseña eliminada');
                document.querySelector(`[data-id="${id}"]`)?.remove();
                estadoActual.total--;
                if (document.querySelectorAll('.resena-card').length === 0) cargarResenas();
            } else {
                toastr.error(res.message||'No se pudo eliminar');
            }
        }, 'json').fail(()=>toastr.error('Error de conexión'));
    });
}

// ─── Helper escape HTML ───
function escHtml(str) {
    return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ─── Toastr config ───
toastr.options = {positionClass:'toast-bottom-right',timeOut:3000,closeButton:true};

// ─── Sidebar sync ───
document.addEventListener('DOMContentLoaded', function() {
    const main = document.querySelector('main');
    if (document.body.classList.contains('sidebar-collapsed') && main) {
        main.classList.add('min-main');
    }
    cargarResenas();
});
</script>
</body>
</html>