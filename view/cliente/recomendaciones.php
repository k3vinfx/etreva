<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Lang::get('recomendaciones.titulo_pagina') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="assets/css/recomendaciones.css" rel="stylesheet" type="text/css">
    <link href="assets/css/recomendaciones_cards.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>

<?php
include 'view/menu.php';
include 'modal/modal_guardar_itinerario.php';

// ── Textos i18n para JS (mismo patrón que buscar.php) ─────────────────────────
$t = [
    // Hero y estado
    'lang' => $_SESSION['lang'] ?? 'es',   // ← esta línea
    'hero_titulo'        => Lang::get('recomendaciones.hero_titulo'),
    'hero_subtitulo'     => Lang::get('recomendaciones.hero_subtitulo'),
    'analizando_perfil'  => Lang::get('recomendaciones.analizando_perfil'),
    'mostrando'          => Lang::get('recomendaciones.mostrando'),
    'de'                 => Lang::get('recomendaciones.de'),
    'recomendaciones_txt'=> Lang::get('recomendaciones.recomendaciones_txt'),
    'ordenadas_txt'      => Lang::get('recomendaciones.ordenadas_txt'),
    'titulo_grid'        => Lang::get('recomendaciones.titulo_grid'),
    'cargando_grid'      => Lang::get('recomendaciones.cargando_grid'),
    'sin_resultados'     => Lang::get('recomendaciones.sin_resultados'),
    'sin_mes'            => Lang::get('recomendaciones.sin_mes'),
    'error_cargar'       => Lang::get('recomendaciones.error_cargar'),
    'error_datos'        => Lang::get('recomendaciones.error_datos'),
    'error_conn'         => Lang::get('recomendaciones.error_conn'),

    // Perfil
    'perfil_motivo'      => Lang::get('recomendaciones.perfil_motivo'),
    'perfil_presupuesto' => Lang::get('recomendaciones.perfil_presupuesto'),
    'perfil_duracion'    => Lang::get('recomendaciones.perfil_duracion'),
    'perfil_nd'          => Lang::get('recomendaciones.perfil_nd'),
    'perfil_actividades' => Lang::get('recomendaciones.perfil_actividades'),
    'perfil_experiencias'=> Lang::get('recomendaciones.perfil_experiencias'),

    // Compañía (para el mapa en perfil)
    'compania_solo'      => Lang::get('recomendaciones.compania_solo'),
    'compania_pareja'    => Lang::get('recomendaciones.compania_pareja'),
    'compania_familia'   => Lang::get('recomendaciones.compania_familia'),
    'compania_amigos'    => Lang::get('recomendaciones.compania_amigos'),
    'compania_grupo'     => Lang::get('recomendaciones.compania_grupo'),

    // Cards
    'card_lugar'         => Lang::get('recomendaciones.card_lugar'),
    'card_desc'          => Lang::get('recomendaciones.card_desc'),
    'card_ciudad'        => Lang::get('recomendaciones.card_ciudad'),
    'card_pais'          => Lang::get('recomendaciones.card_pais'),
    'card_motivo'        => Lang::get('recomendaciones.card_motivo'),
    'card_compania'      => Lang::get('recomendaciones.card_compania'),
    'card_compania_nd'   => Lang::get('recomendaciones.card_compania_nd'),
    'card_exp'           => Lang::get('recomendaciones.card_exp'),
    'card_ver_detalle'   => Lang::get('recomendaciones.card_ver_detalle'),

    // Modal
    'modal_cargando'     => Lang::get('recomendaciones.modal_cargando'),
    'modal_error'        => Lang::get('recomendaciones.modal_error'),
    'modal_sesion'       => Lang::get('recomendaciones.modal_sesion'),
    'modal_cerrar'       => Lang::get('recomendaciones.modal_cerrar'),

    // Frases IA sin coincidencias
    'ia_cultura'         => Lang::get('recomendaciones.ia_cultura'),
    'ia_museos'          => Lang::get('recomendaciones.ia_museos'),
    'ia_historico'       => Lang::get('recomendaciones.ia_historico'),
    'ia_gastronomia'     => Lang::get('recomendaciones.ia_gastronomia'),
    'ia_restaurante'     => Lang::get('recomendaciones.ia_restaurante'),
    'ia_cafe'            => Lang::get('recomendaciones.ia_cafe'),
    'ia_vida_nocturna'   => Lang::get('recomendaciones.ia_vida_nocturna'),
    'ia_bar'             => Lang::get('recomendaciones.ia_bar'),
    'ia_discoteca'       => Lang::get('recomendaciones.ia_discoteca'),
    'ia_aventura'        => Lang::get('recomendaciones.ia_aventura'),
    'ia_trekking'        => Lang::get('recomendaciones.ia_trekking'),
    'ia_naturaleza'      => Lang::get('recomendaciones.ia_naturaleza'),
    'ia_montanismo'      => Lang::get('recomendaciones.ia_montanismo'),
    'ia_miradores'       => Lang::get('recomendaciones.ia_miradores'),
    'ia_relax'           => Lang::get('recomendaciones.ia_relax'),
    'ia_spa'             => Lang::get('recomendaciones.ia_spa'),
    'ia_bienestar'       => Lang::get('recomendaciones.ia_bienestar'),
    'ia_default'         => Lang::get('recomendaciones.ia_default'),

    // Frases IA con coincidencias
    'ia_cultura_c'       => Lang::get('recomendaciones.ia_cultura_c'),
    'ia_museos_c'        => Lang::get('recomendaciones.ia_museos_c'),
    'ia_gastronomia_c'   => Lang::get('recomendaciones.ia_gastronomia_c'),
    'ia_vida_nocturna_c' => Lang::get('recomendaciones.ia_vida_nocturna_c'),
    'ia_aventura_c'      => Lang::get('recomendaciones.ia_aventura_c'),
    'ia_trekking_c'      => Lang::get('recomendaciones.ia_trekking_c'),
    'ia_miradores_c'     => Lang::get('recomendaciones.ia_miradores_c'),
    'ia_relax_c'         => Lang::get('recomendaciones.ia_relax_c'),
    'ia_default_c'       => Lang::get('recomendaciones.ia_default_c'),
];

// (Opcional) Para depurar, descomenta la siguiente línea y mira la consola del navegador
// echo '<script>console.log(' . json_encode($t, JSON_UNESCAPED_UNICODE) . ');</script>';
?>

<body>
<main>

    <!-- HERO -->
    <div class="recomendaciones-hero">
        <h1><i class="fas fa-star"></i><?= Lang::get('recomendaciones.hero_titulo') ?></h1>
        <p><?= Lang::get('recomendaciones.hero_subtitulo') ?></p>

        <!-- PERFIL -->
        <div class="profile-card" id="perfil-container">
            <div class="text-center py-4">
                <div class="spinner-border" role="status" style="color:#667eea;"></div>
                <p class="mt-2"><?= Lang::get('recomendaciones.analizando_perfil') ?></p>
            </div>
        </div>
    </div>

    <!-- BARRA DE ESTADO -->
    <div class="barra-estado-rec">
        <div class="barra-estado-rec-txt">
            <?= Lang::get('recomendaciones.mostrando') ?>
            <span id="txtMostrando">—</span>
            <?= Lang::get('recomendaciones.de') ?>
            <span id="txtTotal">—</span>
            <?= Lang::get('recomendaciones.recomendaciones_txt') ?>
        </div>
        <div class="barra-estado-rec-txt">
            <i class="fas fa-robot" style="color:#667eea;margin-right:6px;"></i>
            <?= Lang::get('recomendaciones.ordenadas_txt') ?>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="dashboard">

        <!-- TÍTULO -->
        <h2 class="recomendaciones-title">
            <i class="fas fa-magic"></i>
            <?= Lang::get('recomendaciones.titulo_grid') ?>
        </h2>

        <!-- GRID -->
        <div class="recomendaciones-grid" id="recomendaciones-container">
            <div class="text-center py-5" style="grid-column:1/-1;">
                <div class="spinner-border" role="status" style="color:#667eea;width:2.5rem;height:2.5rem;"></div>
                <p style="color:rgba(255,255,255,0.4);margin-top:14px;font-size:0.85rem;"><?= Lang::get('recomendaciones.cargando_grid') ?></p>
            </div>
        </div>

        <!-- PAGINACIÓN -->
        <div class="paginacion-wrap" id="paginacionWrap"></div>

    </div>
</main>

<!-- Modal detalle -->
<div id="contenedorModalDetalle"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/brain/0.6.3/brain.min.js"></script>
<script src="assets/js/etreva-auth.js"></script>

<script>
// ── Textos i18n desde PHP ─────────────────────────────────────────────────────
const T = <?= json_encode($t, JSON_UNESCAPED_UNICODE) ?>;

// ─── CONFIG DE CATEGORÍAS (sin cambios) ───────────────────────────────────────
const CATEGORIAS_CONFIG = {
    'vida nocturna':      { icono: 'fa-glass-cheers',  color: '#7c3aed' },
    'vida_nocturna':      { icono: 'fa-glass-cheers',  color: '#7c3aed' },
    'discoteca':          { icono: 'fa-music',          color: '#7c3aed' },
    'bar':                { icono: 'fa-cocktail',       color: '#9333ea' },
    'cocteleria':         { icono: 'fa-cocktail',       color: '#a855f7' },
    'cerveceria':         { icono: 'fa-beer',           color: '#6d28d9' },
    'gastronomia':        { icono: 'fa-utensils',       color: '#ea580c' },
    'gastronomía':        { icono: 'fa-utensils',       color: '#ea580c' },
    'restaurante':        { icono: 'fa-utensils',       color: '#ea580c' },
    'vegetariana':        { icono: 'fa-leaf',           color: '#16a34a' },
    'vegana':             { icono: 'fa-seedling',       color: '#15803d' },
    'cafeteria':          { icono: 'fa-coffee',         color: '#92400e' },
    'café':               { icono: 'fa-coffee',         color: '#92400e' },
    'cafés y repostería': { icono: 'fa-coffee',         color: '#92400e' },
    'parrilla':           { icono: 'fa-fire',           color: '#b91c1c' },
    'italiana':           { icono: 'fa-pizza-slice',    color: '#dc2626' },
    'japonesa':           { icono: 'fa-fish',           color: '#0369a1' },
    'mariscos':           { icono: 'fa-fish',           color: '#0284c7' },
    'chifa':              { icono: 'fa-utensils',       color: '#b45309' },
    'aventura':           { icono: 'fa-hiking',         color: '#15803d' },
    'trekking':           { icono: 'fa-hiking',         color: '#166534' },
    'montañismo':         { icono: 'fa-mountain',       color: '#1e3a5f' },
    'montaña':            { icono: 'fa-mountain',       color: '#1e40af' },
    'downhill':           { icono: 'fa-bicycle',        color: '#0e7490' },
    'lagunas':            { icono: 'fa-water',          color: '#0369a1' },
    'valles':             { icono: 'fa-tree',           color: '#166534' },
    'miradores':          { icono: 'fa-binoculars',     color: '#1e3a5f' },
    'cultura':            { icono: 'fa-landmark',       color: '#92400e' },
    'museos':             { icono: 'fa-university',     color: '#78350f' },
    'histórico':          { icono: 'fa-landmark',       color: '#a16207' },
    'historia':           { icono: 'fa-scroll',         color: '#a16207' },
    'caminos':            { icono: 'fa-route',          color: '#c2410c' },
    'relax':              { icono: 'fa-spa',            color: '#0891b2' },
    'spa':                { icono: 'fa-spa',            color: '#0891b2' },
    'bienestar':          { icono: 'fa-heart',          color: '#db2777' },
    'default':            { icono: 'fa-map-marker-alt', color: '#667eea' },
};

function obtenerConfig(categoria, subcategoria) {
    const cat = (categoria || '').toLowerCase();
    const sub = (subcategoria || '').toLowerCase();
    for (const [key, val] of Object.entries(CATEGORIAS_CONFIG)) { if (sub.includes(key)) return val; }
    for (const [key, val] of Object.entries(CATEGORIAS_CONFIG)) { if (cat.includes(key)) return val; }
    return CATEGORIAS_CONFIG['default'];
}

function escalarMatch(matchReal) {
    let v;
    if      (matchReal >= 80) v = 90 + ((matchReal - 80) / 20) * 8;
    else if (matchReal >= 60) v = 78 + ((matchReal - 60) / 20) * 12;
    else if (matchReal >= 40) v = 65 + ((matchReal - 40) / 20) * 13;
    else                      v = 55 + (matchReal / 40) * 10;
    return Math.min(98, Math.max(55, Math.round(v)));
}

function formatearDias(dias) {
    if (!dias) return null;

    const claves = typeof dias === 'object' && !Array.isArray(dias) ? Object.keys(dias) : [];
    const porClaves = claves.some(k => ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'].includes(k));
    const esIngles = porClaves || T.lang !== 'es';

    if (esIngles) {
        const mapaES_EN  = { lunes:'Mon', martes:'Tue', miercoles:'Wed', jueves:'Thu', viernes:'Fri', sabado:'Sat', domingo:'Sun' };
        const mapaEN_EN  = { monday:'Mon', tuesday:'Tue', wednesday:'Wed', thursday:'Thu', friday:'Fri', saturday:'Sat', sunday:'Sun' };
        const ordenES    = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
        const ordenEN    = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

        if (typeof dias === 'object' && !Array.isArray(dias)) {
            // Claves inglesas primero
            const resultEN = ordenEN.filter(d => dias[d] === true || dias[d] === '1' || dias[d] === 1).map(d => mapaEN_EN[d]);
            if (resultEN.length > 0) return resultEN.join(' · ');
            // Fallback: claves españolas, mostrar en inglés
            return ordenES.filter(d => dias[d] === true || dias[d] === '1' || dias[d] === 1).map(d => mapaES_EN[d]).join(' · ');
        }
    }

    // Español
    const mapaCorto   = { lunes:'Lun', martes:'Mar', miercoles:'Mié', jueves:'Jue', viernes:'Vie', sabado:'Sáb', domingo:'Dom' };
    const ordenSemana = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];

    if (Array.isArray(dias)) {
        return ordenSemana.filter(d => dias.map(x => x.toLowerCase()).includes(d)).map(d => mapaCorto[d]).join(' · ');
    }
    if (typeof dias === 'object') {
        return ordenSemana.filter(d => dias[d] === true || dias[d] === '1' || dias[d] === 1).map(d => mapaCorto[d]).join(' · ');
    }
    return String(dias);
}



function formatearHora(hora) {
    if (!hora) return null;
    const partes = hora.split(':');
    let h = parseInt(partes[0]);
    const min = partes[1] || '00';
    const sufijo = h >= 12 ? 'PM' : 'AM';
    if (h > 12) h -= 12;
    if (h === 0) h = 12;
    return `${String(h).padStart(2,'0')}:${min} ${sufijo}`;
}

function obtenerImagenPrincipal(ubicacion) {
    if (!ubicacion) return null;
    const rutas = ubicacion.rutas ?? [];
    return rutas.length ? rutas[0] : null;
}

// ─── MENSAJE IA — usa T (i18n) ────────────────────────────────────────────────
function generarMensajeIA(rec, matchVisual, tieneCoincidencias = false) {
    const categoria = (rec.categoria || '').toLowerCase();
    const sub       = (rec.subcategoria || '').toLowerCase();

    const mapaFrases = [
        { claves: ['cultura'],                          key: 'ia_cultura'      },
        { claves: ['museos'],                           key: 'ia_museos'       },
        { claves: ['histórico', 'historico', 'historia'], key: 'ia_historico'  },
        { claves: ['gastronomia', 'gastronomía'],       key: 'ia_gastronomia'  },
        { claves: ['restaurante'],                      key: 'ia_restaurante'  },
        { claves: ['café', 'cafeteria', 'cafés'],       key: 'ia_cafe'         },
        { claves: ['vida nocturna', 'vida_nocturna'],   key: 'ia_vida_nocturna'},
        { claves: ['bar'],                              key: 'ia_bar'          },
        { claves: ['discoteca'],                        key: 'ia_discoteca'    },
        { claves: ['aventura'],                         key: 'ia_aventura'     },
        { claves: ['trekking'],                         key: 'ia_trekking'     },
        { claves: ['naturaleza'],                       key: 'ia_naturaleza'   },
        { claves: ['montañismo', 'montanismo', 'montaña'], key: 'ia_montanismo'},
        { claves: ['miradores'],                        key: 'ia_miradores'    },
        { claves: ['relax'],                            key: 'ia_relax'        },
        { claves: ['spa'],                              key: 'ia_spa'          },
        { claves: ['bienestar'],                        key: 'ia_bienestar'    },
    ];

    const sufijo = tieneCoincidencias ? '_c' : '';

    for (const entry of mapaFrases) {
        for (const clave of entry.claves) {
            if (sub.includes(clave) || categoria.includes(clave)) {
                const keyConSufijo = entry.key + sufijo;
                return T[keyConSufijo] || T[entry.key] || T['ia_default' + sufijo] || T['ia_default'];
            }
        }
    }

    return T['ia_default' + sufijo] || T['ia_default'];
}

// ════════════════════════════════════════════════════════════════
//  PAGINACIÓN
// ════════════════════════════════════════════════════════════════
const PAG = { pagina: 1, porPagina: 12, lista: [] };

function renderPagina() {
    const cliente  = (PAG.pagina - 1) * PAG.porPagina;
    const fin      = cliente + PAG.porPagina;
    const items    = PAG.lista.slice(cliente, fin);
    const total    = PAG.lista.length;
    const paginas  = Math.ceil(total / PAG.porPagina);

    $('#txtMostrando').text(items.length > 0 ? `${cliente+1}–${Math.min(fin, total)}` : '0');
    $('#txtTotal').text(total);

    let html = '';
    items.forEach(rec => { html += buildCard(rec); });
    $('#recomendaciones-container').html(
        html || `<div style="grid-column:1/-1;text-align:center;color:rgba(255,255,255,0.5);padding:60px;">${T.sin_resultados}</div>`
    );

    if (paginas <= 1) { $('#paginacionWrap').html(''); return; }

    let pag = '';
    pag += `<button class="pag-btn pag-btn-wide" id="pag-prev" ${PAG.pagina===1?'disabled':''}><i class="fas fa-chevron-left"></i></button>`;

    const ventana = 2;
    for (let i = 1; i <= paginas; i++) {
        if (i === 1 || i === paginas || (i >= PAG.pagina - ventana && i <= PAG.pagina + ventana)) {
            pag += `<button class="pag-btn ${i===PAG.pagina?'active':''}" data-pag="${i}">${i}</button>`;
        } else if (i === PAG.pagina - ventana - 1 || i === PAG.pagina + ventana + 1) {
            pag += `<span class="pag-info">…</span>`;
        }
    }

    pag += `<button class="pag-btn pag-btn-wide" id="pag-next" ${PAG.pagina===paginas?'disabled':''}><i class="fas fa-chevron-right"></i></button>`;
    $('#paginacionWrap').html(pag);

    $('#pag-prev').on('click', () => { if(PAG.pagina > 1) { PAG.pagina--; renderPagina(); scrollToGrid(); } });
    $('#pag-next').on('click', () => { if(PAG.pagina < paginas) { PAG.pagina++; renderPagina(); scrollToGrid(); } });
    $('[data-pag]').on('click', function() { PAG.pagina = parseInt($(this).data('pag')); renderPagina(); scrollToGrid(); });
}

function scrollToGrid() {
    const el = document.querySelector('.recomendaciones-title');
    if (el) window.scrollTo({ top: el.offsetTop - 20, behavior: 'smooth' });
}

// ─── BUILD CARD ───────────────────────────────────────────────────────────────

// Valores internos de la BD (aventura_naturaleza, solo, vida_nocturna…) → texto en el idioma activo
const VALORES_I18N = <?= Lang::valoresJs() ?>;
function tv(v) {
    if (v == null || v === '') return v;
    const lista = Array.isArray(v) ? v : String(v).split(/\s*[·,]\s*/);
    return lista.filter(Boolean).map(x => {
        const k = String(x).trim().toLowerCase();
        return VALORES_I18N[k] || k.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }).join(' · ');
}

function buildCard(rec) {
    const cfg              = obtenerConfig(rec.categoria, rec.subcategoria);
    const matchVisual      = escalarMatch(rec.porcentaje_match ?? 0);
    const nombreLimpio     = rec.nombre || T.card_lugar;
    const descLimpia       = rec.descripcion || T.card_desc;
    const ciudad           = rec.ciudad || T.card_ciudad;
    const pais             = rec.pais   || T.card_pais;
    const puntuacion       = rec.puntuacion ? parseFloat(rec.puntuacion).toFixed(1) : '4.5';
    const imagenRuta       = obtenerImagenPrincipal(rec.ubicacion);
    const horaAbre         = formatearHora(rec.horario_apertura);
    const horaCierra       = formatearHora(rec.horario_cierre);
    const diasFormateados  = formatearDias(rec.dias_operacion);

    const companiaTexto     = rec.coincidencias?.compania || rec.compania || null;
    const experienciasTexto = rec.coincidencias?.experiencias
        ? tv(rec.coincidencias.experiencias)
        : null;
    const motivoTexto        = tv(rec.coincidencias?.motivo_viaje) || null;
    const tieneCoincidencias = !!(motivoTexto || companiaTexto || experienciasTexto);

    const imgStyle = imagenRuta
        ? `background-image:url('${imagenRuta}');background-size:cover;background-position:center;`
        : `background:linear-gradient(135deg,${cfg.color}44,${cfg.color}22);`;

    let badgeColor = '#ef4444';
    if (matchVisual >= 85) badgeColor = '#16a34a';
    else if (matchVisual >= 70) badgeColor = '#d97706';

    const horarioRow = (horaAbre && horaCierra)
        ? `<div class="rc-info-row"><i class="fas fa-clock"></i><span>${horaAbre} – ${horaCierra}</span></div>` : '';
    const diasRow = diasFormateados
        ? `<div class="rc-info-row"><i class="fas fa-calendar-week"></i><span>${diasFormateados}</span></div>` : '';

    return `
    <div class="rc-card">
        <div class="rc-header" style="background:linear-gradient(135deg,${cfg.color},${cfg.color}cc);">
            <span class="rc-cat"><i class="fas ${cfg.icono}"></i> ${rec.categoria || T.card_lugar}</span>
            <span class="rc-cat-badge">${rec.subcategoria || ''}</span>
        </div>
        <div class="rc-img-wrap" style="${imgStyle}">
            <div class="rc-img-overlay">
                <div class="rc-overlay-title">
                    <span>${nombreLimpio}</span>
                    <span class="rc-badge" style="background:${badgeColor};">
                        <i class="fas fa-fire"></i> ${matchVisual}% match
                    </span>
                </div>
                <div class="rc-desc">${descLimpia}</div>
                <div class="rc-info-row"><i class="fas fa-map-marker-alt"></i><span>${ciudad}, ${pais}</span></div>
                <div class="rc-info-row"><i class="fas fa-star" style="color:#f59e0b;"></i><span>${puntuacion}/5</span></div>
                ${horarioRow}
                ${diasRow}
                <div class="rc-details">
                    ${motivoTexto
                        ? `<div class="rc-detail-line"><i class="fas fa-suitcase"></i><strong>${T.card_motivo}</strong> ${motivoTexto}</div>`
                        : ''}
                    ${companiaTexto
                        ? `<div class="rc-detail-line"><i class="fas fa-users"></i><strong>${T.card_compania}</strong> ${companiaTexto}</div>`
                        : `<div class="rc-detail-line rc-detail-muted"><i class="fas fa-users"></i><span>${T.card_compania_nd}</span></div>`}
                    ${experienciasTexto
                        ? `<div class="rc-detail-line"><i class="fas fa-star"></i><strong>${T.card_exp}</strong> ${experienciasTexto}</div>`
                        : ''}
                    <div class="rc-ia-tip ${tieneCoincidencias ? 'rc-ia-tip--complemento' : ''}">
                        <i class="fas fa-robot"></i>
                        <span>${generarMensajeIA(rec, matchVisual, tieneCoincidencias)}</span>
                    </div>
                </div>
                <div class="rc-btn-overlay">
                    <button class="rc-btn" onclick="verDetalle(${rec.output})">
                        ${T.card_ver_detalle} <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>`;
}

// ─── RENDER PRINCIPAL ─────────────────────────────────────────────────────────
function renderizarRecomendaciones(recomendaciones) {
    if (!recomendaciones || recomendaciones.length === 0) {
        $('#recomendaciones-container').html(
            `<div style="grid-column:1/-1;text-align:center;color:rgba(255,255,255,0.5);padding:60px 20px;">
                <i class="fas fa-star" style="font-size:3rem;opacity:0.2;display:block;margin-bottom:16px;"></i>
                ${T.sin_resultados}
            </div>`
        );
        $('#txtMostrando').text('0');
        $('#txtTotal').text('0');
        return;
    }
    PAG.lista  = [...recomendaciones].sort((a,b) => (b.porcentaje_match ?? 0) - (a.porcentaje_match ?? 0));
    PAG.pagina = 1;
    renderPagina();
}

// ─── BRAIN.JS ─────────────────────────────────────────────────────────────────
function entrenarConRecomendaciones(recomendaciones) {
    const validos = recomendaciones.filter(r => Array.isArray(r.input) && r.input.length > 0);
    if (validos.length < 3) return;
    const dataset = validos.map(r => ({
        input:  r.input,
        output: [Math.min(1, Math.max(0, (r.porcentaje_match ?? 50) / 100))]
    }));
    const net = new brain.NeuralNetwork({ hiddenLayers:[18,12], learningRate:0.05, activation:'relu' });
    net.train(dataset, { iterations:2000, errorThresh:0.005, shuffle:true, log:true, logPeriod:500 });
    window.redNeuronal = net;
}

// ─── CARGA ────────────────────────────────────────────────────────────────────
function cargarRecomendaciones() {
    $.ajax({
        url: 'index.php?c=recomendaciones&a=ObtenerModeloMR',
        type: 'GET', dataType: 'json',
        success: function(response) {
            if (response.success && response.recomendaciones?.length > 0) {

                const mesActual   = new Date().getMonth() + 1;
                const mesesAbrev  = {
                    1:'ene', 2:'feb', 3:'mar', 4:'abr', 5:'may', 6:'jun',
                    7:'jul', 8:'ago', 9:'sep', 10:'oct', 11:'nov', 12:'dic'
                };
                const abrevActual = mesesAbrev[mesActual];

                const filtradas = response.recomendaciones.filter(rec => {
                    const cat = (rec.categoria || '').toLowerCase();
                    if (!cat.includes('(')) return true;
                    const match = cat.match(/\(([^)]+)\)/);
                    if (!match) return true;
                    const rango = match[1];
                    return rango.includes(abrevActual);
                });

                if (!filtradas.length) {
                    $('#recomendaciones-container').html(
                        `<div style="grid-column:1/-1;text-align:center;color:rgba(255,255,255,0.5);padding:60px;">${T.sin_mes}</div>`
                    );
                    return;
                }

                entrenarConRecomendaciones(filtradas);
                renderizarRecomendaciones(filtradas);

            } else {
                $('#recomendaciones-container').html(
                    `<div style="grid-column:1/-1;text-align:center;color:rgba(255,255,255,0.5);padding:60px;">
                        ${response.message || T.sin_resultados}
                    </div>`
                );
            }
        },
        error: function() {
            $('#recomendaciones-container').html(
                `<div style="grid-column:1/-1;text-align:center;color:rgba(255,255,255,0.5);padding:60px;">${T.error_cargar}</div>`
            );
        }
    });
}

// ─── PERFIL ───────────────────────────────────────────────────────────────────
function cargarDatosUsuario() {
    $.ajax({
        url: 'index.php?c=login&a=VerPreferencias', type: 'GET', dataType: 'json',
        success: function(response) {
            if (response.success) renderizarPerfil(response);
            else $('#perfil-container').html(`<div class="alert alert-danger m-3">${T.error_datos}</div>`);
        },
        error: function() {
            $('#perfil-container').html(`<div class="alert alert-danger m-3">${T.error_conn}</div>`);
        }
    });
}

function renderizarPerfil(data) {
    const u    = data.usuario || {};
    const pref = data.preferencias_registro_tb || data.preferencias || {};
    const int  = pref.intereses || {};
    const iniciales   = ((u.nombre?.[0] || 'U') + (u.apellido?.[0] || '')).toUpperCase();

    // Mapa de traducción para los valores de compañía
    const companiaMap = {
        solo:    T.compania_solo,
        pareja:  T.compania_pareja,
        familia: T.compania_familia,
        amigos:  T.compania_amigos,
        grupo:   T.compania_grupo
    };

    let html = `
        <div class="profile-header">
            <div class="profile-avatar"><span>${iniciales}</span></div>
            <div class="profile-info">
                <h2>${u.nombre || 'Usuario'} ${u.apellido || ''}</h2>
                <p><i class="fas fa-envelope"></i> ${u.email || ''}</p>
            </div>
        </div>
        <div class="preferences-tags">
            <span class="pref-tag"><i class="fas fa-suitcase"></i> ${T.perfil_motivo} ${pref.motivo_viaje || int.motivo_viaje || T.perfil_nd}</span>
            <span class="pref-tag"><i class="fas fa-wallet"></i> ${T.perfil_presupuesto} ${pref.presupuesto_max || T.perfil_nd}</span>
            <span class="pref-tag"><i class="fas fa-clock"></i> ${pref.duracion_dias || 0} ${T.perfil_duracion}</span>
            ${int.compania   ? `<span class="pref-tag"><i class="fas fa-users"></i> ${companiaMap[int.compania] || int.compania}</span>` : ''}
            ${int.rango_edad ? `<span class="pref-tag"><i class="fas fa-calendar-alt"></i> ${int.rango_edad}</span>` : ''}
        </div>`;

    if (int.actividades?.length) {
        html += `<div style="margin-top:14px;">
                    <strong style="color:#667eea;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;">${T.perfil_actividades}</strong>
                    <div class="preferences-tags" style="margin-top:8px;">`;
        int.actividades.forEach(a => {
            const n = a.replace(/_/g,' ');
            html += `<span class="pref-tag"><i class="fas fa-tag"></i> ${n.charAt(0).toUpperCase()+n.slice(1)}</span>`;
        });
        html += '</div></div>';
    }

    if (int.experiencias?.length) {
        html += `<div style="margin-top:12px;">
                    <strong style="color:#667eea;font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;">${T.perfil_experiencias}</strong>
                    <div class="preferences-tags" style="margin-top:8px;">`;
        int.experiencias.forEach(e => {
            const n = e.replace(/_/g,' ');
            html += `<span class="pref-tag"><i class="fas fa-star"></i> ${n.charAt(0).toUpperCase()+n.slice(1)}</span>`;
        });
        html += '</div></div>';
    }

    $('#perfil-container').html(html);
}

// ─── DETALLE ──────────────────────────────────────────────────────────────────
function verDetalle(id) {
    const elAnt = document.getElementById('modalDetalleAtraccion');
    if (elAnt) { const i = bootstrap.Modal.getInstance(elAnt); if (i) i.dispose(); elAnt.remove(); }
    $('#contenedorModalDetalle').html(`
        <div class="modal fade" id="modalDetalleAtraccion" data-bs-backdrop="true" data-bs-keyboard="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content p-5 text-center" style="border-radius:16px">
                    <div class="spinner-border mx-auto mb-3" style="color:#2980b9;width:2.5rem;height:2.5rem"></div>
                    <p class="text-muted mb-0">${T.modal_cargando}</p>
                </div>
            </div>
        </div>`);
    const spinner = new bootstrap.Modal(document.getElementById('modalDetalleAtraccion'), { backdrop:true, keyboard:true });
    $.ajax({
        url: 'index.php?c=atracciones&a=detalle&id=' + id, type: 'GET',
        success: function(html) {
            spinner.hide();
            $('#contenedorModalDetalle').html(html);
            new bootstrap.Modal(document.getElementById('modalDetalleAtraccion')).show();
        },
        error: function(xhr) {
            spinner.hide();
            const msg = xhr.status === 401 ? T.modal_sesion : T.modal_error;
            $('#contenedorModalDetalle').html(`
                <div class="modal fade" id="modalDetalleAtraccion" data-bs-backdrop="true">
                    <div class="modal-dialog"><div class="modal-content p-4">
                        <div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle"></i> ${msg}</div>
                        <div class="text-end mt-3"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">${T.modal_cerrar}</button></div>
                    </div></div>
                </div>`);
            new bootstrap.Modal(document.getElementById('modalDetalleAtraccion')).show();
        }
    });
}

// ─── INIT ─────────────────────────────────────────────────────────────────────
$(document).ready(function() {
    console.log('Brain.js:', typeof brain !== 'undefined' ? '✅' : '❌');
    toastr.options = { closeButton:true, progressBar:true, positionClass:'toast-top-right', timeOut:'3000' };
    EtrevaAuth.verificarSesionInicial(function(autenticado) {
        if (autenticado) { cargarDatosUsuario(); cargarRecomendaciones(); }
    });
});
</script>

</body>
</html>