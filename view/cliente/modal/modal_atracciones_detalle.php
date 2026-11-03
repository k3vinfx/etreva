<?php
/**
 * view/atracciones/detalle.php
 * Modal emergente de detalle de atracción
 */

// Protección básica: si no hay datos, mostrar error
if (!isset($atraccion) || !$atraccion) {
    echo '<div class="alert alert-danger m-4"><i class="fas fa-exclamation-triangle"></i> ' . Lang::get('atracciones_detalle.error_no_encontrada') . '</div>';
    exit();
}

// Extraer y sanitizar datos
$nombre      = htmlspecialchars($atraccion['nombre']       ?? 'Sin nombre');
$descripcion = htmlspecialchars($atraccion['descripcion']  ?? 'Sin descripción');
$ciudad      = htmlspecialchars($atraccion['ciudad']       ?? 'La Paz');
$pais        = htmlspecialchars($atraccion['pais']         ?? 'Bolivia');
$puntuacion  = floatval($atraccion['puntuacion_promedio']  ?? 0);
$precio      = $atraccion['nivel_precio']                  ?? null;
$telefono    = htmlspecialchars($atraccion['telefono']     ?? '');
$email_at    = htmlspecialchars($atraccion['email']        ?? '');
$web         = htmlspecialchars($atraccion['sitio_web']    ?? '');
$direccion   = htmlspecialchars($atraccion['direccion']    ?? '');
$lat         = $atraccion['latitud']                       ?? null;
$lng         = $atraccion['longitud']                      ?? null;
$imagen_url  = $atraccion['imagen_url']                    ?? null;

$cat_nombre  = htmlspecialchars($categorias['categoria']    ?? 'Otros');
$subcat      = htmlspecialchars($categorias['subcategoria'] ?? 'General');

// Iconos por categoría
$iconos_cat = [
    'gastronomia'   => 'fa-utensils',
    'vida_nocturna' => 'fa-glass-cheers',
    'cultura'       => 'fa-landmark',
    'aventura'      => 'fa-hiking',
    'relax'         => 'fa-spa',
    'otros'         => 'fa-map-marker-alt',
];
$icon_cat = $iconos_cat[strtolower(str_replace(' ', '_', $cat_nombre))] ?? 'fa-map-marker-alt';

// Colores por categoría (versiones con opacidad para degradados)
$colores_cat = [
    'gastronomia'   => '#e67e22',
    'vida_nocturna' => '#8e44ad',
    'cultura'       => '#2980b9',
    'aventura'      => '#27ae60',
    'relax'         => '#16a085',
    'otros'         => '#2c3e50',
];
$color_cat = $colores_cat[strtolower(str_replace(' ', '_', $cat_nombre))] ?? '#2c3e50';

// Precio: etiqueta
$precio_label = ['economico' => '$', 'medio' => '$$', 'alto' => '$$$', 'premium' => '$$$$'];
$precio_tag   = $precio_label[$precio] ?? null;

// Imagen por defecto si no hay (usamos un placeholder con el nombre)
$default_image = 'https://ui-avatars.com/api/?name=' . urlencode($nombre) . '&background=' . str_replace('#', '', $color_cat) . '&color=fff&size=400&length=2&font-size=0.40&bold=true';
$imagen_fondo = $imagen_url ?: $default_image;

// ── FOTOS DEL CARRUSEL desde campo ubicacion ─────────────────────────────────
$ubicacion_data = $atraccion['ubicacion'] ?? null;
if (is_string($ubicacion_data)) {
    $ubicacion_data = json_decode($ubicacion_data, true);
}
// Solo fotos que existen (sin repetir). Si no hay ninguna, el servidor la obtiene de
// Wikimedia Commons o la genera al pedirla (ver model/imagenAtraccion.php).
require_once __DIR__ . '/../../../model/imagenAtraccion.php';
require_once __DIR__ . '/../../../model/avatar.php';
$galeria        = ImagenAtraccion::galeria((int)($atraccion['id'] ?? 0));
$fotos_lista    = $galeria['fotos'];
$fotos_creditos = $galeria['creditos'];
$foto_referencial = $galeria['referenciales'] ? ['grupo' => $galeria['grupo']] : null;

// Estrellas
function renderStars(float $rating): string {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $stars .= '<i class="fas fa-star" style="color:#f39c12"></i>';
        } elseif ($rating >= $i - 0.5) {
            $stars .= '<i class="fas fa-star-half-alt" style="color:#f39c12"></i>';
        } else {
            $stars .= '<i class="far fa-star" style="color:#bdc3c7"></i>';
        }
    }
    return $stars;
}

// Etiquetas/tags (chips)
function renderTags(array $items, string $color): string {
    if (empty($items)) return '<span class="atr-empty-tag">—</span>';
    $html = '';
    foreach ($items as $item) {
        $item = is_string($item) ? htmlspecialchars($item) : '';
        if ($item) {
            $html .= "<span class='atr-chip' style='border-color:{$color};color:{$color}'>{$item}</span> ";
        }
    }
    return $html ?: '<span class="atr-empty-tag">—</span>';
}

// Decodificar arrays JSON
$estilos    = is_array($atraccion['estilo_musical']    ?? null) ? $atraccion['estilo_musical']    : [];
$tipo_cocina = is_array($atraccion['tipo_cocina']      ?? null) ? $atraccion['tipo_cocina']       : [];
$servicios  = is_array($atraccion['servicios']         ?? null) ? $atraccion['servicios']          : [];
$idiomas    = is_array($atraccion['idiomas_disponibles'] ?? null) ? $atraccion['idiomas_disponibles'] : [];
$deportes   = is_array($atraccion['deportes_disponibles'] ?? null) ? $atraccion['deportes_disponibles'] : [];
$bienestar  = is_array($atraccion['servicios_bienestar'] ?? null) ? $atraccion['servicios_bienestar']  : [];
$cultura    = is_array($atraccion['tipos_cultura']     ?? null) ? $atraccion['tipos_cultura']      : [];
$certificaciones = is_array($atraccion['certificaciones'] ?? null) ? $atraccion['certificaciones'] : [];
$clima      = is_array($atraccion['clima_ideal']       ?? null) ? $atraccion['clima_ideal']        : [];
$temporada  = is_array($atraccion['temporada_ideal']   ?? null) ? $atraccion['temporada_ideal']    : [];

$pet_friendly = !empty($atraccion['pet_friendly']);
$apto_ninos   = !empty($atraccion['apto_ninos']);
$accesible    = !empty($atraccion['accesible_silla_ruedas']);
$sostenible   = !empty($atraccion['eco_sostenible']);

// ── Traducciones para JS ─────────────────────────────────────────────────────
$t_modal = [
    'selecciona_puntuacion' => Lang::get('atracciones_detalle.selecciona_puntuacion'),
    'resena_exitosa'        => Lang::get('atracciones_detalle.resena_exitosa'),
    'resena_error'          => Lang::get('atracciones_detalle.resena_error'),
    'resena_conexion'       => Lang::get('atracciones_detalle.resena_conexion'),
];
?>


<!-- ============================================================
     ESTILOS DEL MODAL DE DETALLE (MEJORADOS)
============================================================ -->
<style>
 @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap');

:root {
    --atr-accent: <?= $color_cat ?>;
    --atr-accent-light: <?= $color_cat ?>22;
    --atr-accent-mid:   <?= $color_cat ?>44;
    --atr-accent-soft:  <?= $color_cat ?>0d;
    --atr-radius: 20px;
    --atr-radius-sm: 10px;
    --atr-shadow: 0 20px 40px -10px rgba(0,0,0,0.3);
    --atr-shadow-sm: 0 4px 12px rgba(0,0,0,0.08);
    --atr-transition: all 0.25s ease;
}

/* ── Modal base ─────────────────────────────────────────── */
#modalDetalleAtraccion.modal {
    z-index: 10500 !important;
    padding-right: 0 !important; /* Evita desplazamiento */
}
#modalDetalleAtraccion .modal-backdrop {
    z-index: 10499 !important;
    background-color: rgba(0,0,0,0.6);
    backdrop-filter: blur(3px);
}
#modalDetalleAtraccion .modal-dialog {
    max-width: 900px;
    margin: 1.5rem auto;
}
#modalDetalleAtraccion .modal-content {
    border: none;
    border-radius: var(--atr-radius);
    overflow: hidden;
    box-shadow: var(--atr-shadow);
    font-family: 'DM Sans', sans-serif;
    background: #fff;
}

/* ── Hero / Carrusel ────────────────────────────────────── */
.atr-hero {
    position: relative;
    height: 280px;
    overflow: hidden;
    background: #000;
}
.atr-carousel-track {
    display: flex;
    height: 100%;
    transition: transform 0.45s cubic-bezier(0.4, 0, 0.2, 1);
}
.atr-slide {
    min-width: 100%;
    height: 100%;
    position: relative;
    flex-shrink: 0;
}
.atr-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.atr-slide-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.80) 25%, rgba(0,0,0,0.25) 65%, transparent);
}
/* Flechas del carrusel */
.atr-carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.40);
    border: 1px solid rgba(255,255,255,0.25);
    backdrop-filter: blur(6px);
    color: #fff;
    width: 40px; height: 40px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    z-index: 10;
    font-size: 1rem;
    transition: var(--atr-transition);
}
.atr-carousel-btn:hover { background: rgba(0,0,0,0.65); }
.atr-carousel-btn.prev { left: 14px; }
.atr-carousel-btn.next { right: 14px; }
/* Dots */
.atr-dots {
    position: absolute;
    bottom: 72px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
    z-index: 10;
}
.atr-dot {
    height: 8px;
    border-radius: 50px;
    background: rgba(255,255,255,0.45);
    cursor: pointer;
    transition: var(--atr-transition);
    width: 8px;
}
.atr-dot.activo {
    background: #fff;
    width: 22px;
}
/* Info sobre el carrusel */
.atr-hero-info {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 20px 28px 22px;
    z-index: 10;
    color: #fff;
}
.atr-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 100px;
    padding: 6px 16px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    margin-bottom: 12px;
    width: fit-content;
}
.atr-hero-info h2 {
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 8px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.4);
    line-height: 1.2;
    max-width: 80%;
}
.atr-hero-sub {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    font-size: 0.9rem;
    opacity: 0.95;
}
.atr-close-btn {
    position: absolute;
    top: 20px; right: 22px;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(8px);
    color: #fff;
    width: 42px; height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--atr-transition);
    font-size: 1.2rem;
    z-index: 20;
}
.atr-close-btn:hover {
    background: rgba(0,0,0,0.5);
    transform: scale(1.05);
}

/* ── Rating badge ───────────────────────────────────────── */
.atr-rating-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.98);
    color: #1a1a2e;
    border-radius: 100px;
    padding: 5px 15px;
    font-weight: 700;
    font-size: 0.9rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* ── Body ───────────────────────────────────────────────── */
.atr-body {
    padding: 32px 35px;
    max-height: 62vh;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--atr-accent-mid) #f0f0f0;
}
.atr-body::-webkit-scrollbar { width: 6px; }
.atr-body::-webkit-scrollbar-track { background: #f0f0f0; border-radius: 10px; }
.atr-body::-webkit-scrollbar-thumb {
    background: var(--atr-accent-mid);
    border-radius: 10px;
}
.atr-body::-webkit-scrollbar-thumb:hover { background: var(--atr-accent); }

/* ── Secciones ──────────────────────────────────────────── */
.atr-section {
    margin-bottom: 28px;
}
.atr-section-title {
    font-family: 'DM Sans', sans-serif;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--atr-accent);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.atr-section-title::after {
    content: '';
    flex: 1;
    height: 2px;
    background: var(--atr-accent-light);
}

/* ── Descripción ────────────────────────────────────────── */
.atr-desc {
    font-size: 0.95rem;
    color: #2c3e50;
    line-height: 1.7;
    background: #f9f9f9;
    padding: 18px 20px;
    border-radius: var(--atr-radius-sm);
    border-left: 3px solid var(--atr-accent);
}

/* ── Info grid ──────────────────────────────────────────── */
.atr-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}
.atr-info-item {
    background: #f8fafc;
    border-radius: var(--atr-radius-sm);
    padding: 14px 16px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
    transition: var(--atr-transition);
    border: 1px solid #edf2f7;
}
.atr-info-item:hover {
    background: #fff;
    border-color: var(--atr-accent-light);
    box-shadow: var(--atr-shadow-sm);
}
.atr-info-item i {
    color: var(--atr-accent);
    margin-top: 3px;
    width: 18px;
    flex-shrink: 0;
    font-size: 1.1rem;
}
.atr-info-item .atr-info-label {
    font-size: 0.7rem;
    color: #64748b;
    display: block;
    margin-bottom: 3px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.atr-info-item .atr-info-val {
    font-size: 0.9rem;
    color: #1e293b;
    font-weight: 500;
    word-break: break-word;
}
.atr-info-item a {
    color: var(--atr-accent);
    text-decoration: none;
    font-weight: 600;
}
.atr-info-item a:hover {
    text-decoration: underline;
}

/* ── Chips / Tags ───────────────────────────────────────── */
.atr-chip {
    display: inline-block;
    border: 1.5px solid;
    border-radius: 50px;
    padding: 4px 14px;
    font-size: 0.8rem;
    font-weight: 500;
    margin: 3px 4px 3px 0;
    background: transparent;
    transition: var(--atr-transition);
}
.atr-chip:hover {
    background: var(--atr-accent-light);
    border-color: var(--atr-accent);
}
.atr-empty-tag {
    font-size: 0.85rem;
    color: #94a3b8;
    font-style: italic;
}

/* ── Badges de características ──────────────────────────── */
.atr-features {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.atr-feature-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    border: 1.5px solid transparent;
    transition: var(--atr-transition);
}
.atr-feature-badge.activo {
    background: var(--atr-accent-light);
    border-color: var(--atr-accent-mid);
    color: var(--atr-accent);
}
.atr-feature-badge.inactivo {
    background: #f1f5f9;
    border-color: #e2e8f0;
    color: #64748b;
}

/* ── Fotos galería ──────────────────────────────────────── */
.atr-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
    margin-top: 10px;
}
.atr-gallery img {
    width: 100%;
    height: 128px;
    object-fit: cover;
    border-radius: var(--atr-radius-sm);
    cursor: pointer;
    transition: var(--atr-transition);
    border: 2px solid transparent;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.atr-gallery img:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border-color: var(--atr-accent);
}

/* ── Mapa ───────────────────────────────────────────────── */
.atr-map-container {
    width: 100%;
    height: 280px;
    border-radius: var(--atr-radius-sm);
    overflow: hidden;
    border: 1px solid #e2e8f0;
    box-shadow: var(--atr-shadow-sm);
    background: #e8eef5;
    position: relative;
    z-index: 0;            /* los paneles de Leaflet no quedan por encima del modal */
}
.atr-map-cargando {
    height: 100%; display: flex; align-items: center; justify-content: center;
    color: #64748b; font-size: 1.6rem;
}

/* ── Reseñas ────────────────────────────────────────────── */
.atr-resena {
    background: #ffffff;
    border-left: 3px solid var(--atr-accent);
    border-radius: 0 var(--atr-radius-sm) var(--atr-radius-sm) 0;
    padding: 16px 20px;
    margin-bottom: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    border: 1px solid #f1f5f9;
    border-left-width: 4px;
}
.atr-resena-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.atr-resena-author {
    font-weight: 700;
    font-size: 0.9rem;
    color: #0f172a;
}
.atr-resena-date {
    font-size: 0.75rem;
    color: #94a3b8;
}
.atr-resena-text {
    font-size: 0.9rem;
    color: #334155;
    line-height: 1.6;
    margin-top: 6px;
}

/* ── Relacionadas ───────────────────────────────────────── */
.atr-related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 12px;
    margin-top: 8px;
}
.atr-related-card {
    border-radius: var(--atr-radius-sm);
    overflow: hidden;
    cursor: pointer;
    border: 1px solid #e9eef2;
    transition: var(--atr-transition);
    text-decoration: none;
    color: inherit;
    display: block;
    background: #fff;
}
.atr-related-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    transform: translateY(-4px);
    border-color: var(--atr-accent-light);
}
.atr-related-img {
    width: 100%;
    height: 90px;
    object-fit: cover;
    background: var(--atr-accent-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--atr-accent);
}
.atr-related-info {
    padding: 12px 12px 10px;
}
.atr-related-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.atr-related-ciudad {
    font-size: 0.7rem;
    color: #64748b;
    margin-bottom: 4px;
}

/* ── Footer ─────────────────────────────────────────────── */
.atr-footer {
    padding: 18px 35px 22px;
    border-top: 1px solid #edf2f7;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #fafcff;
}
.atr-footer .btn {
    padding: 8px 24px;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 50px;
    transition: var(--atr-transition);
}
.atr-footer .btn-outline-secondary {
    border: 1.5px solid #cbd5e1;
    color: #334155;
}
.atr-footer .btn-outline-secondary:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
}
.atr-footer .btn-primary-custom {
    background: var(--atr-accent);
    color: #fff;
    border: none;
}
.atr-footer .btn-primary-custom:hover {
    background: <?= $color_cat ?>e0;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px <?= $color_cat ?>60;
}

/* ── Form reseña ─────────────────────────────────────────── */
.atr-star-input {
    display: flex;
    gap: 6px;
    margin-bottom: 15px;
}
.atr-star-input i {
    font-size: 1.8rem;
    cursor: pointer;
    color: #d1d5db;
    transition: color 0.15s, transform 0.1s;
}
.atr-star-input i.activa,
.atr-star-input i.hover {
    color: #fbbf24;
}
.atr-star-input i:hover {
    transform: scale(1.2);
}
#miComentario {
    border: 1.5px solid #e2e8f0;
    border-radius: var(--atr-radius-sm);
    font-size: 0.9rem;
    resize: vertical;
    padding: 10px 14px;
}
#miComentario:focus {
    border-color: var(--atr-accent);
    box-shadow: 0 0 0 3px var(--atr-accent-light);
    outline: none;
}
#btnEnviarResena {
    background: var(--atr-accent);
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 8px 28px;
    font-weight: 600;
    transition: var(--atr-transition);
}
#btnEnviarResena:hover {
    background: <?= $color_cat ?>e0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px <?= $color_cat ?>80;
}
#resenaFeedback {
    margin-top: 12px;
}

/* ── Precio badge ───────────────────────────────────────── */
.atr-precio-badge {
    display: inline-block;
    background: var(--atr-accent);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
    padding: 4px 14px;
    border-radius: 50px;
    letter-spacing: 0.05em;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

/* ── Animación entrada ───────────────────────────────────── */
@keyframes atrSlideIn {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}
#modalDetalleAtraccion .modal-content {
    animation: atrSlideIn 0.35s ease forwards;
}

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 640px) {
    #modalDetalleAtraccion .modal-dialog { margin: 0.5rem; }
    .atr-hero { height: 220px; }
    .atr-hero-info h2 { font-size: 1.6rem; max-width: 100%; }
    .atr-body { padding: 22px; }
    .atr-footer { padding: 16px 22px; flex-wrap: wrap; }
    .atr-footer .btn { width: 100%; }
    .atr-info-grid { grid-template-columns: 1fr; }
}


/* ===== DARK MODE PARA EL MODAL DETALLE ===== */
body.dark-mode #modalDetalleAtraccion .modal-content {
    background: #0f172a !important;
    color: #ffffff !important;
}

body.dark-mode .atr-body {
    background: #0f172a;
    scrollbar-color: var(--atr-accent) #0f172a;
}

body.dark-mode .atr-section-title {
    color: #60a5fa;
    filter: brightness(1.3);
}

body.dark-mode .atr-desc {
    background: #1e293b !important;
    color: #f8fafc !important;
    border-left: 4px solid var(--atr-accent);
}

body.dark-mode .atr-info-item {
    background: #1e293b !important;
    border: 1px solid #334155 !important;
}

body.dark-mode .atr-info-label {
    color: #94a3b8 !important;
}

body.dark-mode .atr-info-val, 
body.dark-mode .atr-info-val a {
    color: #ffffff !important;
}

body.dark-mode .atr-feature-badge.inactivo {
    background: #334155;
    color: #64748b;
    border-color: #1e293b;
}

body.dark-mode .atr-footer {
    background: #0a0f1d;
    border-top: 1px solid #334155;
}

body.dark-mode .atr-footer .btn-outline-secondary {
    color: #ffffff;
    border-color: #475569;
}


</style>

<!-- ============================================================
     MODAL DE DETALLE (HTML MEJORADO)
============================================================ -->
<div class="modal fade" id="modalDetalleAtraccion" 
     data-bs-backdrop="static" data-bs-keyboard="true" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <!-- ── HERO CON CARRUSEL ── -->
            <div class="atr-hero">

                <!-- Botón cerrar -->
                <button class="atr-close-btn" data-bs-dismiss="modal" aria-label="<?= Lang::get('atracciones_detalle.cerrar') ?>">
                    <i class="fas fa-times"></i>
                </button>

                <!-- Track de slides -->
                <div class="atr-carousel-track" id="atrCarouselTrack">
                    <?php foreach ($fotos_lista as $i => $ruta):
                        $src = htmlspecialchars(is_string($ruta) ? $ruta : '');
                    ?>
                    <div class="atr-slide">
                        <img src="<?= $src ?>"
                             alt="<?= $nombre ?> - foto <?= $i + 1 ?>"
                             loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                        <div class="atr-slide-overlay"></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Flechas (solo si hay más de 1 foto) -->
                <?php if (count($fotos_lista) > 1): ?>
                <button class="atr-carousel-btn prev" onclick="atrCarouselPrev()" aria-label="<?= Lang::get('atracciones_detalle.anterior') ?>">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="atr-carousel-btn next" onclick="atrCarouselNext()" aria-label="<?= Lang::get('atracciones_detalle.siguiente') ?>">
                    <i class="fas fa-chevron-right"></i>
                </button>

                <!-- Dots indicadores -->
                <div class="atr-dots" id="atrDots">
                    <?php foreach ($fotos_lista as $i => $_): ?>
                    <div class="atr-dot <?= $i === 0 ? 'activo' : '' ?>"
                         onclick="atrCarouselGoTo(<?= $i ?>)"
                         aria-label="<?= Lang::get('atracciones_detalle.foto') ?> <?= $i + 1 ?>"></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Info superpuesta (badge, título, rating) -->
                <div class="atr-hero-info">
                    <div class="atr-hero-badge">
                        <i class="fas <?= $icon_cat ?>"></i>
                        <?= $cat_nombre ?> · <?= $subcat ?>
                    </div>
                    <h2 id="modalDetalleLabel"><?= $nombre ?></h2>
                    <div class="atr-hero-sub">
                        <span><i class="fas fa-map-marker-alt"></i> <?= $ciudad ?>, <?= $pais ?></span>
                        <?php if ($puntuacion > 0): ?>
                            <span class="atr-rating-badge">
                                <?= renderStars($puntuacion) ?>
                                <strong><?= number_format($puntuacion, 1) ?></strong>
                            </span>
                        <?php endif; ?>
                        <?php if ($precio_tag): ?>
                            <span class="atr-precio-badge"><?= $precio_tag ?></span>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.atr-hero -->

            <!-- ── BODY ── -->
            <div class="atr-body">

                <!-- Descripción -->
                <?php if ($descripcion): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-align-left"></i> <?= Lang::get('atracciones_detalle.descripcion') ?>
                    </div>
                    <p class="atr-desc"><?= nl2br($descripcion) ?></p>
                </div>
                <?php endif; ?>

                <!-- Info de contacto y datos -->
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-info-circle"></i> <?= Lang::get('atracciones_detalle.informacion') ?>
                    </div>
                    <div class="atr-info-grid">
                        <?php if ($direccion): ?>
                        <div class="atr-info-item">
                            <i class="fas fa-map-pin"></i>
                            <div>
                                <span class="atr-info-label"><?= Lang::get('atracciones_detalle.direccion') ?></span>
                                <span class="atr-info-val"><?= $direccion ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($telefono): ?>
                        <div class="atr-info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <span class="atr-info-label"><?= Lang::get('atracciones_detalle.telefono') ?></span>
                                <span class="atr-info-val">
                                    <a href="tel:<?= $telefono ?>"><?= $telefono ?></a>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($email_at): ?>
                        <div class="atr-info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <span class="atr-info-label"><?= Lang::get('atracciones_detalle.email') ?></span>
                                <span class="atr-info-val">
                                    <a href="mailto:<?= $email_at ?>"><?= $email_at ?></a>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($web): ?>
                        <div class="atr-info-item">
                            <i class="fas fa-globe"></i>
                            <div>
                                <span class="atr-info-label"><?= Lang::get('atracciones_detalle.sitio_web') ?></span>
                                <span class="atr-info-val">
                                    <a href="<?= $web ?>" target="_blank" rel="noopener">
                                        <?= parse_url($web, PHP_URL_HOST) ?: $web ?>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($atraccion['horario_operacion']) && is_string($atraccion['horario_operacion'])): ?>
                        <div class="atr-info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <span class="atr-info-label"><?= Lang::get('atracciones_detalle.horario') ?></span>
                                <span class="atr-info-val"><?= htmlspecialchars($atraccion['horario_operacion']) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($atraccion['nivel_actividad'])): ?>
                        <div class="atr-info-item">
                            <i class="fas fa-dumbbell"></i>
                            <div>
                                <span class="atr-info-label"><?= Lang::get('atracciones_detalle.nivel_actividad') ?></span>
                                <span class="atr-info-val"><?= htmlspecialchars(ucfirst($atraccion['nivel_actividad'])) ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($atraccion['distancia_centro_km'])): ?>
                        <div class="atr-info-item">
                            <i class="fas fa-route"></i>
                            <div>
                                <span class="atr-info-label"><?= Lang::get('atracciones_detalle.distancia_centro') ?></span>
                                <span class="atr-info-val"><?= number_format($atraccion['distancia_centro_km'], 1) ?> <?= Lang::get('atracciones_detalle.km') ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ========== NUEVO: PRECIO DE ENTRADA ========== -->
                        <?php if (isset($atraccion['precio_entrada']) && floatval($atraccion['precio_entrada']) > 0): ?>
                        <div class="atr-info-item">
                            <i class="fas fa-ticket-alt"></i>
                            <div>
                                <span class="atr-info-label"><?= Lang::get('atracciones_detalle.precio_entrada') ?></span>
                                <span class="atr-info-val">
                                    <?= number_format(floatval($atraccion['precio_entrada']), 2) ?>
                                    <?= htmlspecialchars($atraccion['moneda'] ?? 'BOB') ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- ========== NUEVO: TIEMPO ESTIMADO ========== -->
                        <?php
                        $te = $atraccion['tiempo_estimado'] ?? null;
                        $visita = '';
                        $traslado = '';
                        if (is_array($te)) {
                            $visita   = $te['visita']   ?? '';
                            $traslado = $te['traslado'] ?? '';
                        } elseif (is_string($te)) {
                            $decoded = json_decode($te, true);
                            if (is_array($decoded)) {
                                $visita   = $decoded['visita']   ?? '';
                                $traslado = $decoded['traslado'] ?? '';
                            } else {
                                $visita = $te;
                            }
                        }
                        if ($visita || $traslado):
                        ?>
                        <div class="atr-info-item">
                            <i class="fas fa-hourglass-half"></i>
                            <div>
                                <span class="atr-info-label"><?= Lang::get('atracciones_detalle.tiempo_estimado') ?></span>
                                <span class="atr-info-val">
                                    <?php if ($visita): ?><?= Lang::get('atracciones_detalle.visita') ?>: <?= htmlspecialchars($visita) ?><?php endif; ?>
                                    <?php if ($visita && $traslado): ?><br><?php endif; ?>
                                    <?php if ($traslado): ?><?= Lang::get('atracciones_detalle.traslado') ?>: <?= htmlspecialchars($traslado) ?><?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Características rápidas -->
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-check-circle"></i> <?= Lang::get('atracciones_detalle.caracteristicas') ?>
                    </div>
                    <div class="atr-features">
                        <span class="atr-feature-badge <?= $pet_friendly ? 'activo' : 'inactivo' ?>">
                            <i class="fas fa-paw"></i> <?= Lang::get('atracciones_detalle.pet_friendly') ?>
                        </span>
                        <span class="atr-feature-badge <?= $apto_ninos ? 'activo' : 'inactivo' ?>">
                            <i class="fas fa-child"></i> <?= Lang::get('atracciones_detalle.apto_ninos') ?>
                        </span>
                        <span class="atr-feature-badge <?= $accesible ? 'activo' : 'inactivo' ?>">
                            <i class="fas fa-wheelchair"></i> <?= Lang::get('atracciones_detalle.accesible') ?>
                        </span>
                        <span class="atr-feature-badge <?= $sostenible ? 'activo' : 'inactivo' ?>">
                            <i class="fas fa-leaf"></i> <?= Lang::get('atracciones_detalle.eco_sostenible') ?>
                        </span>
                    </div>
                </div>

                <!-- Tags especializados según categoría (solo si hay datos) -->
                <?php if (!empty($estilos)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-music"></i> <?= Lang::get('atracciones_detalle.estilo_musical') ?>
                    </div>
                    <?= renderTags($estilos, $color_cat) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($tipo_cocina)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-utensils"></i> <?= Lang::get('atracciones_detalle.tipo_cocina') ?>
                    </div>
                    <?= renderTags($tipo_cocina, $color_cat) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($servicios)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-concierge-bell"></i> <?= Lang::get('atracciones_detalle.servicios') ?>
                    </div>
                    <?= renderTags($servicios, $color_cat) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($deportes)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-running"></i> <?= Lang::get('atracciones_detalle.deportes') ?>
                    </div>
                    <?= renderTags($deportes, $color_cat) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($cultura)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-theater-masks"></i> <?= Lang::get('atracciones_detalle.cultura') ?>
                    </div>
                    <?= renderTags($cultura, $color_cat) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($bienestar)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-spa"></i> <?= Lang::get('atracciones_detalle.bienestar') ?>
                    </div>
                    <?= renderTags($bienestar, $color_cat) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($certificaciones)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-certificate"></i> <?= Lang::get('atracciones_detalle.certificaciones') ?>
                    </div>
                    <?= renderTags($certificaciones, $color_cat) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($idiomas)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-language"></i> <?= Lang::get('atracciones_detalle.idiomas') ?>
                    </div>
                    <?= renderTags($idiomas, $color_cat) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($clima) || !empty($temporada)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-sun"></i> <?= Lang::get('atracciones_detalle.epoca_ideal') ?>
                    </div>
                    <?php if (!empty($clima)): ?>
                        <div class="mb-2"><small class="text-muted"><?= Lang::get('atracciones_detalle.clima') ?>:</small> <?= renderTags($clima, $color_cat) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($temporada)): ?>
                        <div><small class="text-muted"><?= Lang::get('atracciones_detalle.temporada') ?>:</small> <?= renderTags($temporada, $color_cat) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Galería de miniaturas (clicables al lightbox) -->
                <?php if (count($fotos_lista) > 1): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-images"></i> <?= Lang::get('atracciones_detalle.galeria') ?>
                    </div>
                    <div class="atr-gallery">
                        <?php foreach ($fotos_lista as $i => $ruta):
                            $src = htmlspecialchars(is_string($ruta) ? $ruta : '');
                        ?>
                            <img src="<?= $src ?>"
                                 alt="<?= $nombre ?> - foto <?= $i + 1 ?>"
                                 onclick="atrVerFoto('<?= $src ?>')"
                                 loading="lazy">
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($fotos_creditos): ?>
                <div class="atr-creditos" style="font-size:.72rem;opacity:.75;margin:-6px 0 16px;line-height:1.5;">
                    <i class="fas fa-camera"></i>
                    <?php foreach ($fotos_creditos as $i => $cr): ?>
                        <?= $i ? ' · ' : '' ?>
                        <a href="<?= htmlspecialchars($cr['pagina'] ?? '#') ?>" target="_blank" rel="noopener" style="color:inherit;">
                            <?= htmlspecialchars($cr['autor'] ?: 'Autor desconocido') ?></a><?= !empty($cr['licencia']) ? ', ' . htmlspecialchars($cr['licencia']) : '' ?>
                    <?php endforeach; ?>
                    — Wikimedia Commons
                    <?php if ($foto_referencial): ?>
                        <br><i class="fas fa-info-circle"></i> <?= Lang::get('atracciones_detalle.fotos_referenciales') ?> <?= htmlspecialchars((string)$foto_referencial['grupo']) ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <script>
                // Cierra el detalle actual y abre el de la atracción relacionada en la misma página
                function atrAbrirRelacionada(id, ev) {
                    if (ev) ev.preventDefault();
                    const el = document.getElementById('modalDetalleAtraccion');
                    const abrir = () => {
                        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.removeProperty('overflow');
                        document.body.style.removeProperty('padding-right');
                        if (typeof verDetalle === 'function') verDetalle(id);
                    };
                    const inst = el && window.bootstrap ? bootstrap.Modal.getInstance(el) : null;
                    if (inst && el.classList.contains('show')) {
                        el.addEventListener('hidden.bs.modal', abrir, { once: true });
                        inst.hide();
                    } else {
                        abrir();
                    }
                    return false;
                }
                </script>

                <!-- Mapa -->
                <?php if ($lat && $lng): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-map"></i> <?= Lang::get('atracciones_detalle.ubicacion') ?>
                    </div>
                    <!-- Mapa liviano (Leaflet + OpenStreetMap): se carga solo cuando se ve en pantalla.
                         Mucho más rápido que el iframe de Google Maps; "Cómo llegar" sigue abriendo Google Maps. -->
                    <div class="atr-map-container" id="atrMapa"
                         data-lat="<?= htmlspecialchars((string)$lat) ?>" data-lng="<?= htmlspecialchars((string)$lng) ?>"
                         data-nombre="<?= htmlspecialchars(strip_tags($nombre)) ?>"
                         role="img" aria-label="<?= Lang::get('atracciones_detalle.mapa_de') ?> <?= $nombre ?>">
                        <div class="atr-map-cargando"><i class="fas fa-map-marker-alt"></i></div>
                    </div>
                </div>
                <script>
                (function () {
                    const el = document.getElementById('atrMapa');
                    if (!el) return;

                    // Leaflet se descarga una sola vez y queda para todos los modales
                    function cargarLeaflet() {
                        if (window.L) return Promise.resolve();
                        if (window.__leafletCarga) return window.__leafletCarga;
                        const base = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/';
                        const css = document.createElement('link');
                        css.rel = 'stylesheet'; css.href = base + 'leaflet.min.css';
                        document.head.appendChild(css);
                        window.__leafletCarga = new Promise((ok, err) => {
                            const s = document.createElement('script');
                            s.src = base + 'leaflet.min.js'; s.onload = ok; s.onerror = err;
                            document.head.appendChild(s);
                        });
                        return window.__leafletCarga;
                    }

                    function pintar() {
                        cargarLeaflet().then(() => {
                            if (el.__mapa) return;
                            const lat = parseFloat(el.dataset.lat), lng = parseFloat(el.dataset.lng);
                            el.innerHTML = '';
                            L.Icon.Default.imagePath = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/';
                            const mapa = L.map(el, { scrollWheelZoom: false, zoomControl: true }).setView([lat, lng], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19, attribution: '&copy; OpenStreetMap'
                            }).addTo(mapa);
                            L.marker([lat, lng]).addTo(mapa).bindPopup(el.dataset.nombre);
                            el.__mapa = mapa;
                            setTimeout(() => mapa.invalidateSize(), 150); // por si el modal aún se estaba abriendo
                        }).catch(() => {
                            el.innerHTML = '<div class="atr-map-cargando">No se pudo cargar el mapa</div>';
                        });
                    }

                    // Precargar la librería ya, pero dibujar el mapa recién cuando se hace visible
                    cargarLeaflet().catch(() => {});
                    if ('IntersectionObserver' in window) {
                        const io = new IntersectionObserver(ents => {
                            if (ents.some(e => e.isIntersecting)) { io.disconnect(); pintar(); }
                        }, { rootMargin: '200px' });
                        io.observe(el);
                    } else {
                        pintar();
                    }
                })();
                </script>
                <?php endif; ?>

                <!-- Reseñas -->
                <?php if (!empty($resenas)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-comments"></i> <?= Lang::get('atracciones_detalle.opiniones') ?>
                    </div>
                    <?php foreach ($resenas as $resena): ?>
                    <div class="atr-resena">
                        <div class="atr-resena-header">
                            <span class="atr-resena-author" style="display:inline-flex;align-items:center;gap:8px;">
                                <?= Avatar::html($resena['usuario_avatar'] ?? null, $resena['nombre'] ?? '', $resena['apellido'] ?? '', 30) ?>
                                <?= htmlspecialchars(trim(($resena['nombre'] ?? 'Viajero') . ' ' . ($resena['apellido'] ?? ''))) ?>
                                <?php if (!empty($resena['usuario_contacto'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($resena['usuario_contacto']) ?>"
                                       title="<?= htmlspecialchars($resena['usuario_contacto']) ?>"
                                       style="font-size:.75rem;font-weight:600;text-decoration:none;color:var(--atr-accent);margin-left:4px;">
                                        <i class="fas fa-envelope"></i> <?= Lang::get('resenas.contactar') ?>
                                    </a>
                                <?php endif; ?>
                            </span>
                            <span class="atr-resena-date">
                                <?= !empty($resena['fecha_resena'])
                                    ? date('d M Y', strtotime($resena['fecha_resena']))
                                    : '' ?>
                            </span>
                        </div>
                        <div style="margin-bottom:6px">
                            <?= renderStars(floatval($resena['puntuacion'] ?? 0)) ?>
                        </div>
                        <?php if (!empty($resena['comentario'])): ?>
                            <p class="atr-resena-text"><?= htmlspecialchars($resena['comentario']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-comments"></i> <?= Lang::get('atracciones_detalle.opiniones') ?>
                    </div>
                    <p class="atr-empty-tag"><?= Lang::get('atracciones_detalle.sin_resenas') ?></p>
                </div>
                <?php endif; ?>

                <!-- Agregar reseña -->
                <div class="atr-section" id="seccionResena">
                    <div class="atr-section-title">
                        <i class="fas fa-star"></i> <?= Lang::get('atracciones_detalle.dejar_resena') ?>
                    </div>
                    <div class="atr-star-input" id="starInput">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <i class="far fa-star" data-val="<?= $s ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" id="miPuntuacion" value="0">
                    <textarea id="miComentario"
                              class="form-control mb-3"
                              rows="3"
                              placeholder="<?= sprintf(Lang::get('atracciones_detalle.comentario_placeholder'), $nombre) ?>"></textarea>
                    <button class="btn"
                            id="btnEnviarResena">
                        <i class="fas fa-paper-plane"></i> <?= Lang::get('atracciones_detalle.enviar_resena') ?>
                    </button>
                    <div id="resenaFeedback" style="display:none"></div>
                </div>

                <!-- Relacionadas -->
                <?php if (!empty($relacionadas)): ?>
                <div class="atr-section">
                    <div class="atr-section-title">
                        <i class="fas fa-th-large"></i> <?= Lang::get('atracciones_detalle.tambien_te_interesa') ?>
                    </div>
                    <div class="atr-related-grid">
                        <?php foreach ($relacionadas as $rel): ?>
                        <a class="atr-related-card" href="#"
                           onclick="return atrAbrirRelacionada(<?= intval($rel['id']) ?>, event);">
                            <?php $rel['imagen_url'] = ImagenAtraccion::galeria((int)$rel['id'], 1)['fotos'][0]; ?>
                            <?php if (!empty($rel['imagen_url'])): ?>
                                <img src="<?= htmlspecialchars($rel['imagen_url']) ?>"
                                     alt="<?= htmlspecialchars($rel['nombre'] ?? '') ?>"
                                     class="atr-related-img"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="atr-related-img">
                                    <i class="fas <?= $icon_cat ?>"></i>
                                </div>
                            <?php endif; ?>
                            <div class="atr-related-info">
                                <div class="atr-related-name"><?= htmlspecialchars($rel['nombre'] ?? '') ?></div>
                                <div class="atr-related-ciudad"><?= htmlspecialchars($rel['ciudad'] ?? '') ?></div>
                                <?php if (!empty($rel['puntuacion_promedio'])): ?>
                                    <div style="font-size:0.7rem;color:#f39c12;margin-top:4px">
                                        <?= renderStars(floatval($rel['puntuacion_promedio'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /.atr-body -->

            <!-- ── FOOTER ── -->
            <div class="atr-footer">
                <?php if ($web): ?>
                <a href="<?= $web ?>" target="_blank" rel="noopener"
                   class="btn btn-outline-secondary">
                    <i class="fas fa-external-link-alt"></i> <?= Lang::get('atracciones_detalle.sitio_web_btn') ?>
                </a>
                <?php endif; ?>

                <?php if ($lat && $lng): ?>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $lat ?>,<?= $lng ?>"
                   target="_blank" rel="noopener"
                   class="btn btn-primary-custom">
                    <i class="fas fa-directions"></i> <?= Lang::get('atracciones_detalle.como_llegar') ?>
                </a>
                <?php endif; ?>

                <!-- Botón Guardar en itinerario -->
                <button type="button" 
                    class="btn btn-outline-success"
                    onclick='abrirModalItinerario(<?= json_encode([ 
                        "id" => $atraccion["id"],
                        "nombre" => $nombre,
                        "ciudad" => $ciudad,
                        "descripcion" => $descripcion,
                        "precio_entrada" => isset($atraccion["precio_entrada"]) 
                            ? floatval($atraccion["precio_entrada"]) 
                            : null,
                        "moneda" => $atraccion["moneda"] ?? "BOB",
                        "tiempo_estimado" => $atraccion["tiempo_estimado"] ?? [
                            "visita" => "30 minutos",
                            "traslado" => "0 minutos"
                        ]
                    ], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                        <i class="fas fa-bookmark"></i> <?= Lang::get('atracciones_detalle.guardar_itinerario') ?>
                </button>

                <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">
                    <?= Lang::get('atracciones_detalle.cerrar') ?>
                </button>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Lightbox mejorado para fotos -->
<div id="atrLightbox"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); z-index:99999;
            align-items:center; justify-content:center; cursor:zoom-out; backdrop-filter: blur(5px);"
     onclick="this.style.display='none'">
    <img id="atrLightboxImg"
         style="max-width:90vw; max-height:90vh; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.5);"
         alt="">
</div>

<!-- ============================================================
     JS DEL MODAL (MEJORADO) CON TRADUCCIONES
============================================================ -->
<script>
// ── Traducciones para JS ─────────────────────────────────────────────────────
const T_MODAL = <?= json_encode($t_modal, JSON_UNESCAPED_UNICODE) ?>;

function abrirModalGuardarItinerario(atraccion) {
    var modalItinEl  = document.getElementById('modalGuardarItinerario');
    var modalDetalleEl = document.getElementById('modalDetalleAtraccion');

    if (!modalItinEl) {
        console.error('El modal #modalGuardarItinerario no existe en el DOM');
        return;
    }

    // Rellenar campos
    $('.atraccion-nombre').text(atraccion.nombre);
    $('#itin_nombre').val('Visita a ' + atraccion.nombre);
    $('#itin_descripcion').val(atraccion.descripcion || '');
    $('#modalGuardarItinerario').data('atraccion-id', atraccion.id);

    // 1️⃣ Ocultar el modal de detalle (sin destruirlo)
    var instanciaDetalle = bootstrap.Modal.getInstance(modalDetalleEl);
    if (instanciaDetalle) {
        // Cuando el detalle termine de ocultarse, abrir el de itinerario
        modalDetalleEl.addEventListener('hidden.bs.modal', function abrirItin() {
            modalDetalleEl.removeEventListener('hidden.bs.modal', abrirItin);
            new bootstrap.Modal(modalItinEl).show();
        });
        instanciaDetalle.hide();
    } else {
        new bootstrap.Modal(modalItinEl).show();
    }
}  

(function() {
    'use strict';

    const ATRACCION_ID  = <?= intval($atraccion['id'] ?? 0) ?>;
    const COLOR_ACCENT  = '<?= $color_cat ?>';
    const TOTAL_SLIDES  = <?= count($fotos_lista) ?>;

    // ── CARRUSEL ─────────────────────────────────────────────
    let slideActual = 0;
    let autoPlay    = null;

    function goTo(index) {
        slideActual = (index + TOTAL_SLIDES) % TOTAL_SLIDES;
        const track = document.getElementById('atrCarouselTrack');
        if (track) track.style.transform = 'translateX(-' + (slideActual * 100) + '%)';

        document.querySelectorAll('#modalDetalleAtraccion .atr-dot').forEach(function(d, i) {
            d.classList.toggle('activo', i === slideActual);
        });
    }

    window.atrCarouselGoTo = goTo;
    window.atrCarouselNext = function() { goTo(slideActual + 1); };
    window.atrCarouselPrev = function() { goTo(slideActual - 1); };

    // Auto-avance cada 4s (solo si hay más de 1 slide)
    if (TOTAL_SLIDES > 1) {
        autoPlay = setInterval(window.atrCarouselNext, 4000);
    }

    // Pausar al hover
    var track = document.getElementById('atrCarouselTrack');
    if (track) {
        track.addEventListener('mouseenter', function() { clearInterval(autoPlay); });
        track.addEventListener('mouseleave', function() {
            if (TOTAL_SLIDES > 1) autoPlay = setInterval(window.atrCarouselNext, 4000);
        });
    }

    // Swipe táctil
    var touchStartX = 0;
    var hero = document.querySelector('#modalDetalleAtraccion .atr-hero');
    if (hero) {
        hero.addEventListener('touchstart', function(e) { touchStartX = e.touches[0].clientX; }, { passive: true });
        hero.addEventListener('touchend', function(e) {
            var diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) {
                diff > 0 ? window.atrCarouselNext() : window.atrCarouselPrev();
            }
        }, { passive: true });
    }

    // Limpiar autoplay al cerrar el modal
    var modalEl = document.getElementById('modalDetalleAtraccion');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            clearInterval(autoPlay);
        });
    }

    // ── ESTRELLAS INTERACTIVAS ───────────────────────────────
    const $stars = $('#starInput i');
    $stars.on('mouseenter', function() {
        const val = $(this).data('val');
        $stars.each(function() {
            const v = $(this).data('val');
            $(this).removeClass('fas far').addClass(v <= val ? 'fas activa hover' : 'far')
                   .css('color', v <= val ? '#fbbf24' : '#d1d5db');
        });
    });
    $stars.on('mouseleave', function() {
        const seleccionada = parseInt($('#miPuntuacion').val());
        $stars.each(function() {
            const v = $(this).data('val');
            if (v <= seleccionada) {
                $(this).removeClass('far').addClass('fas activa').css('color', '#fbbf24');
            } else {
                $(this).removeClass('fas activa hover').addClass('far').css('color', '#d1d5db');
            }
        });
    });
    $stars.on('click', function() {
        const val = $(this).data('val');
        $('#miPuntuacion').val(val);
        $stars.each(function() {
            const v = $(this).data('val');
            $(this).removeClass('fas far activa hover')
                   .addClass(v <= val ? 'fas activa' : 'far')
                   .css('color', v <= val ? '#fbbf24' : '#d1d5db');
        });
    });

    // ── ENVIAR RESEÑA ────────────────────────────────────────
    $('#btnEnviarResena').on('click', function() {
        const puntuacion = parseInt($('#miPuntuacion').val());
        const comentario = $('#miComentario').val().trim();
        const $feedback = $('#resenaFeedback');

        if (puntuacion < 1) {
            $feedback.html('<div class="alert alert-warning py-2 mt-2">' + T_MODAL.selecciona_puntuacion + '</div>').show();
            return;
        }

        const $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

        $.ajax({
            url: 'index.php?c=atracciones&a=guardarResena',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                atraccion_id: ATRACCION_ID,
                puntuacion: puntuacion,
                comentario: comentario
            }),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $feedback.html(
                        '<div class="alert alert-success py-2 mt-2">' +
                        '<i class="fas fa-check-circle"></i> ' + T_MODAL.resena_exitosa +
                        '</div>'
                    ).show();
                    $('#miComentario').val('');
                    $('#miPuntuacion').val(0);
                    $stars.removeClass('fas activa').addClass('far').css('color', '#d1d5db');
                    setTimeout(() => $feedback.fadeOut(), 3000);
                } else {
                    $feedback.html('<div class="alert alert-danger py-2 mt-2">' + (res.message || T_MODAL.resena_error) + '</div>').show();
                }
            },
            error: function() {
                $feedback.html('<div class="alert alert-danger py-2 mt-2">' + T_MODAL.resena_conexion + '</div>').show();
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> <?= Lang::get('atracciones_detalle.enviar_resena') ?>');
            }
        });
    });

    // ── LIGHTBOX ─────────────────────────────────────────────
    window.atrVerFoto = function(src) {
        $('#atrLightboxImg').attr('src', src);
        $('#atrLightbox').css('display', 'flex');
    };

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#atrLightbox').is(':visible')) {
            $('#atrLightbox').hide();
        }
    });

})();
</script>