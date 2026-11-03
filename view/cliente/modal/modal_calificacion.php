<?php
// ── Textos para JavaScript ───────────────────────────────────────────────────
$t_cal = [
    'star_muy_malo'  => Lang::get('modal_calificacion.star_muy_malo'),
    'star_regular'   => Lang::get('modal_calificacion.star_regular'),
    'star_bueno'     => Lang::get('modal_calificacion.star_bueno'),
    'star_muy_bueno' => Lang::get('modal_calificacion.star_muy_bueno'),
    'star_excelente' => Lang::get('modal_calificacion.star_excelente'),

    'js_selecciona_estrella' => Lang::get('modal_calificacion.js_selecciona_estrella'),
    'js_guardando'           => Lang::get('modal_calificacion.js_guardando'),
    'js_error_guardar'       => Lang::get('modal_calificacion.js_error_guardar'),
    'js_exito'               => Lang::get('modal_calificacion.js_exito'),
    'js_calificacion_ok_marcado_fallo' => Lang::get('modal_calificacion.js_calificacion_ok_marcado_fallo'),
    'js_calificacion_ok_conexion_fallo'=> Lang::get('modal_calificacion.js_calificacion_ok_conexion_fallo'),
    'js_error_conexion'      => Lang::get('modal_calificacion.js_error_conexion'),
];
?>
<!-- ═══════════════════════════════════════════════
     modal/modal_calificacion.php
     Modal de calificación — se abre al marcar visitado
════════════════════════════════════════════════ -->
<style>
/* ── MODAL CALIFICACIÓN ─────────────────────────── */
#modalCalificacion .modal-content {
    background: #1a1a2e;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 20px;
    color: #e2e8f0;
}
#modalCalificacion .modal-header {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border-radius: 18px 18px 0 0;
    border: none;
    padding: 20px 24px;
}
#modalCalificacion .modal-header .modal-title {
    font-weight: 800;
    font-size: 1.1rem;
    color: #fff;
}
#modalCalificacion .modal-body { padding: 28px 24px; }
#modalCalificacion .modal-footer {
    border-top: 1px solid rgba(255,255,255,0.08);
    padding: 16px 24px;
    gap: 10px;
}

/* Info de la atracción */
.cal-atraccion-info {
    background: rgba(99,102,241,0.1);
    border: 1px solid rgba(99,102,241,0.25);
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.cal-atraccion-info i { color: #6366f1; font-size: 1.4rem; flex-shrink: 0; }
.cal-atraccion-info h6 { margin: 0 0 3px; font-weight: 700; color: #fff; font-size: 0.95rem; }
.cal-atraccion-info p  { margin: 0; font-size: 0.78rem; color: rgba(255,255,255,0.5); }

/* Estrellas */
.cal-label {
    font-size: 0.8rem;
    font-weight: 700;
    color: rgba(255,255,255,0.6);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}
.stars-wrap {
    display: flex;
    gap: 8px;
    margin-bottom: 6px;
}
.star-btn {
    font-size: 2rem;
    cursor: pointer;
    color: rgba(255,255,255,0.2);
    transition: color 0.15s, transform 0.15s;
    background: none;
    border: none;
    padding: 0;
    line-height: 1;
}
.star-btn:hover,
.star-btn.activa { color: #f59e0b; transform: scale(1.15); }
.star-btn.hover-activa { color: #f59e0b; }

.cal-valor-texto {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.45);
    margin-bottom: 20px;
    min-height: 18px;
}

/* Comentario */
#calComentario {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    color: #e2e8f0;
    resize: vertical;
    min-height: 90px;
    font-size: 0.88rem;
    padding: 12px 14px;
    width: 100%;
    transition: border-color 0.2s;
}
#calComentario:focus {
    outline: none;
    border-color: rgba(99,102,241,0.5);
    background: rgba(99,102,241,0.07);
}
#calComentario::placeholder { color: rgba(255,255,255,0.25); }

/* Tags de criterios rápidos */
.cal-tags { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0 22px; }
.cal-tag {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 50px;
    padding: 5px 14px;
    font-size: 0.76rem;
    color: rgba(255,255,255,0.55);
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
}
.cal-tag:hover { border-color: rgba(99,102,241,0.4); color: #a5b4fc; }
.cal-tag.selected {
    background: rgba(99,102,241,0.2);
    border-color: #6366f1;
    color: #a5b4fc;
    font-weight: 700;
}

/* Botones footer */
#btnGuardarCalificacion {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 10px 24px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}
#btnGuardarCalificacion:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
#btnGuardarCalificacion:disabled { opacity: 0.5; cursor: not-allowed; }

#btnCancelarCal {
    background: rgba(255,255,255,0.07);
    color: rgba(255,255,255,0.6);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 10px 20px;
    font-size: 0.88rem;
    cursor: pointer;
    transition: all 0.2s;
}
#btnCancelarCal:hover { background: rgba(255,255,255,0.12); }

/* Info modelo */
.cal-modelo-info {
    background: rgba(245,158,11,0.08);
    border: 1px solid rgba(245,158,11,0.2);
    border-radius: 10px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 18px;
    font-size: 0.76rem;
    color: rgba(255,255,255,0.5);
}
.cal-modelo-info i { color: #f59e0b; }
.cal-modelo-info span strong { color: #fcd34d; }
</style>

<!-- ══ MODAL ══════════════════════════════════════ -->
<div class="modal fade" id="modalCalificacion" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false"
     aria-labelledby="modalCalificacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalCalificacionLabel">
                    <i class="fas fa-star" style="margin-right:8px;color:#fcd34d;"></i>
                    <?= Lang::get('modal_calificacion.titulo') ?>
                </h5>
            </div>

            <div class="modal-body">

                <!-- Info de la atracción visitada -->
                <div class="cal-atraccion-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h6 id="calNombreAtraccion"><?= Lang::get('modal_calificacion.cargando') ?></h6>
                        <p id="calCategoriaAtraccion">—</p>
                    </div>
                </div>

                <!-- ESTRELLAS -->
                <div class="cal-label"><?= Lang::get('modal_calificacion.tu_puntuacion') ?></div>
                <div class="stars-wrap" id="starsWrap">
                    <button class="star-btn" data-val="1">★</button>
                    <button class="star-btn" data-val="2">★</button>
                    <button class="star-btn" data-val="3">★</button>
                    <button class="star-btn" data-val="4">★</button>
                    <button class="star-btn" data-val="5">★</button>
                </div>
                <div class="cal-valor-texto" id="calValorTexto"><?= Lang::get('modal_calificacion.selecciona_puntuacion') ?></div>

                <!-- TAGS RÁPIDOS -->
                <div class="cal-label">
                    <?= Lang::get('modal_calificacion.que_destacarias') ?>
                    <span style="font-weight:400;text-transform:none;"><?= Lang::get('modal_calificacion.opcional') ?></span>
                </div>
                <div class="cal-tags" id="calTagsWrap">
                    <span class="cal-tag" data-tag="buen_ambiente"><?= Lang::get('modal_calificacion.tag_buen_ambiente') ?></span>
                    <span class="cal-tag" data-tag="excelente_atencion"><?= Lang::get('modal_calificacion.tag_excelente_atencion') ?></span>
                    <span class="cal-tag" data-tag="precio_justo"><?= Lang::get('modal_calificacion.tag_precio_justo') ?></span>
                    <span class="cal-tag" data-tag="muy_recomendado"><?= Lang::get('modal_calificacion.tag_muy_recomendado') ?></span>
                    <span class="cal-tag" data-tag="para_volver"><?= Lang::get('modal_calificacion.tag_para_volver') ?></span>
                    <span class="cal-tag" data-tag="decepcionante"><?= Lang::get('modal_calificacion.tag_decepcionante') ?></span>
                    <span class="cal-tag" data-tag="muy_lleno"><?= Lang::get('modal_calificacion.tag_muy_lleno') ?></span>
                    <span class="cal-tag" data-tag="dificil_acceso"><?= Lang::get('modal_calificacion.tag_dificil_acceso') ?></span>
                </div>

                <!-- COMENTARIO -->
                <div class="cal-label">
                    <?= Lang::get('modal_calificacion.comentario') ?>
                    <span style="font-weight:400;text-transform:none;"><?= Lang::get('modal_calificacion.opcional') ?></span>
                </div>
                <textarea id="calComentario" placeholder="<?= Lang::get('modal_calificacion.comentario_placeholder') ?>"></textarea>

                <!-- Info del modelo que generó la recomendación -->
                <div class="cal-modelo-info" id="calModeloInfo" style="display:none;">
                    <i class="fas fa-robot"></i>
                    <span>
                        <?= Lang::get('modal_calificacion.recomendado_por') ?>
                        <strong id="calModeloNombre">—</strong>
                        <span id="calModeloVersion" style="opacity:0.6;"></span>
                    </span>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" id="btnCancelarCal"
                        data-bs-dismiss="modal"><?= Lang::get('modal_calificacion.cancelar') ?></button>
                <button type="button" id="btnGuardarCalificacion" disabled>
                    <i class="fas fa-save"></i> <?= Lang::get('modal_calificacion.guardar') ?>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// ── Traducciones desde PHP ───────────────────────────────────────────────────
const T_CAL = <?= json_encode($t_cal, JSON_UNESCAPED_UNICODE) ?>;

// ── ESTADO INTERNO DEL MODAL ───────────────────────────────────────────────────
const CAL = {
    itinerarioId : null,
    atraccionId  : null,
    puntuacion   : 0,
    tagsSeleccionados: [],
    modeloId     : null,
};

const TEXTOS_STAR = [
    '',
    T_CAL.star_muy_malo,
    T_CAL.star_regular,
    T_CAL.star_bueno,
    T_CAL.star_muy_bueno,
    T_CAL.star_excelente
];

// ── ABRIR MODAL ────────────────────────────────────────────────────────────────
function abrirModalCalificacion(itinerarioId, atraccionId, nombreAtraccion, categoriaAtraccion) {
    CAL.itinerarioId      = itinerarioId;
    CAL.atraccionId       = atraccionId;  // puede ser 0
    CAL.puntuacion        = 0;
    CAL.tagsSeleccionados = [];
    CAL.modeloId          = null;

    $('#calNombreAtraccion').text(nombreAtraccion || '<?= Lang::get('modal_calificacion.atraccion_default') ?>');
    $('#calCategoriaAtraccion').text(categoriaAtraccion || '—');
    $('#calComentario').val('');
    $('#calValorTexto').text('<?= Lang::get('modal_calificacion.selecciona_puntuacion') ?>');
    $('.star-btn').removeClass('activa hover-activa');
    $('.cal-tag').removeClass('selected');
    $('#btnGuardarCalificacion').prop('disabled', true);
    $('#calModeloInfo').hide();

    new bootstrap.Modal(document.getElementById('modalCalificacion')).show();

    // Si atraccion_id llegó en 0 → buscarlo desde el servidor
    if (!atraccionId || atraccionId === 0) {
        $('#calNombreAtraccion').html('<?= Lang::get('modal_calificacion.cargando') ?> <i class="fas fa-spinner fa-spin" style="font-size:0.8rem;"></i>');

        $.ajax({
            url: 'index.php?c=itinerarios&a=ObtenerAtraccionDeItinerario&id=' + itinerarioId,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.atraccion_id) {
                    CAL.atraccionId = res.atraccion_id;
                    $('#calNombreAtraccion').text(res.nombre || nombreAtraccion || '<?= Lang::get('modal_calificacion.atraccion_default') ?>');
                    $('#calCategoriaAtraccion').text(res.categoria || categoriaAtraccion || '—');
                    buscarModelo(CAL.atraccionId);
                } else {
                    $('#calNombreAtraccion').text(nombreAtraccion || '<?= Lang::get('modal_calificacion.atraccion_default') ?>');
                    toastr.warning('<?= Lang::get('modal_calificacion.js_error_cargar_id') ?>');
                }
            },
            error: function() {
                $('#calNombreAtraccion').text(nombreAtraccion || '<?= Lang::get('modal_calificacion.atraccion_default') ?>');
            }
        });
    } else {
        buscarModelo(atraccionId);
    }
}

function buscarModelo(atrId) {
    if (!atrId) return;
    $.ajax({
        url: 'index.php?c=itinerarios&a=ObtenerModeloPorAtraccion&atraccion_id=' + atrId,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.success && res.modelo) {
                CAL.modeloId = res.modelo.id;
                $('#calModeloNombre').text(res.modelo.nombre || '<?= Lang::get('modal_calificacion.modelo_default') ?>');
                $('#calModeloVersion').text('v' + (res.modelo.version || '1.0'));
                $('#calModeloInfo').show();
            }
        }
    });
}

// ── ESTRELLAS ──────────────────────────────────────────────────────────────────
$(document).on('mouseover', '.star-btn', function() {
    const val = parseInt($(this).data('val'));
    $('.star-btn').each(function() {
        $(this).toggleClass('hover-activa', parseInt($(this).data('val')) <= val);
    });
}).on('mouseout', '.star-btn', function() {
    $('.star-btn').removeClass('hover-activa');
    // Restaurar las marcadas
    $('.star-btn').each(function() {
        $(this).toggleClass('activa', parseInt($(this).data('val')) <= CAL.puntuacion);
    });
}).on('click', '.star-btn', function() {
    CAL.puntuacion = parseInt($(this).data('val'));
    $('.star-btn').each(function() {
        $(this).toggleClass('activa', parseInt($(this).data('val')) <= CAL.puntuacion);
    });
    $('#calValorTexto').text(TEXTOS_STAR[CAL.puntuacion]);
    $('#btnGuardarCalificacion').prop('disabled', CAL.puntuacion === 0);
});

// ── TAGS ───────────────────────────────────────────────────────────────────────
$(document).on('click', '.cal-tag', function() {
    const tag = $(this).data('tag');
    $(this).toggleClass('selected');
    if ($(this).hasClass('selected')) {
        CAL.tagsSeleccionados.push(tag);
    } else {
        CAL.tagsSeleccionados = CAL.tagsSeleccionados.filter(t => t !== tag);
    }
});

// ── GUARDAR ────────────────────────────────────────────────────────────────────
$(document).on('click', '#btnGuardarCalificacion', function() {
    if (CAL.puntuacion === 0) {
        toastr.warning(T_CAL.js_selecciona_estrella);
        return;
    }

    const btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + T_CAL.js_guardando);

    // ── PASO 1: Guardar calificación PRIMERO ──────────────────────────
    $.ajax({
        url : 'index.php?c=itinerarios&a=GuardarCalificacion',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            itinerario_id : CAL.itinerarioId,
            atraccion_id  : CAL.atraccionId,
            calificacion  : CAL.puntuacion,
            comentario    : $('#calComentario').val().trim(),
            tags          : CAL.tagsSeleccionados,
            modelo_id     : CAL.modeloId,
        }),
        dataType: 'json',
        success: function(res) {
            if (!res.success) {
                toastr.error(res.message || T_CAL.js_error_guardar);
                btn.prop('disabled', false)
                   .html('<i class="fas fa-save"></i> <?= Lang::get('modal_calificacion.guardar') ?>');
                return;
            }

            // ── PASO 2: Recién ahora marcar como visitado ─────────────
            $.ajax({
                url : 'index.php?c=itinerarios&a=marcarVisitado',
                type: 'POST',
                data: { id: CAL.itinerarioId },
                dataType: 'json',
                success: function(resVisitado) {
                    bootstrap.Modal.getInstance(
                        document.getElementById('modalCalificacion')
                    ).hide();

                    if (resVisitado.success) {
                        toastr.success(T_CAL.js_exito);
                    } else {
                        toastr.warning(T_CAL.js_calificacion_ok_marcado_fallo);
                    }

                    // Actualizar card en el DOM
                    actualizarCardVisitado(CAL.itinerarioId);
                },
                error: function() {
                    bootstrap.Modal.getInstance(
                        document.getElementById('modalCalificacion')
                    ).hide();
                    toastr.warning(T_CAL.js_calificacion_ok_conexion_fallo);
                    actualizarCardVisitado(CAL.itinerarioId);
                }
            });
        },
        error: function() {
            toastr.error(T_CAL.js_error_conexion);
            btn.prop('disabled', false)
               .html('<i class="fas fa-save"></i> <?= Lang::get('modal_calificacion.guardar') ?>');
        }
    });
});

// ── ACTUALIZAR CARD SIN RECARGAR ───────────────────────────────────────────────
function actualizarCardVisitado(itinerarioId) {
    const card = $('[data-itin-id="' + itinerarioId + '"]');
    if (!card.length) { location.reload(); return; }

    // Cambiar badge del header (los textos "Visitado" y "Ya visitado" deben venir de itinerarios.php)
    card.find('.badge-estado-header')
        .removeClass('badge-pendiente')
        .addClass('badge-visitado')
        .html('<i class="fas fa-check-circle"></i> <?= Lang::get('itinerarios.visitado') ?>');

    // Cambiar botón
    card.find('.btn-marcar-visitado')
        .replaceWith('<button class="rc-btn" style="background:#374151;border-color:#4b5563;" disabled>' +
                     '<i class="fas fa-check-circle"></i> <?= Lang::get('itinerarios.visitado_etiqueta') ?></button>');
}
</script>