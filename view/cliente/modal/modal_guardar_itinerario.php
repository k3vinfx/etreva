<?php
// ── Textos i18n para JS ─────────────────────────────────────────────────────
$t_modal_itinerario = [
    'campo_requerido'        => Lang::get('modal_itinerario.campo_requerido'),
    'error_no_atraccion'     => Lang::get('modal_itinerario.error_no_atraccion'),
    'error_fecha_requerida'  => Lang::get('modal_itinerario.error_fecha_requerida'),
    'hora_no_disponible'     => Lang::get('modal_itinerario.hora_no_disponible'),
    'hora_no_disponible_msg' => Lang::get('modal_itinerario.hora_no_disponible_msg'),
    'exito_titulo'           => Lang::get('modal_itinerario.exito_titulo'),
    'exito_mensaje'          => Lang::get('modal_itinerario.exito_mensaje'),
    'exito_badge'            => Lang::get('modal_itinerario.exito_badge'),
    'ver_itinerarios'        => Lang::get('modal_itinerario.ver_itinerarios'),
    'cerrar'                 => Lang::get('modal_itinerario.cerrar'),
    'conflicto_dias_titulo'  => Lang::get('modal_itinerario.conflicto_dias_titulo'),
    'conflicto_horas_titulo' => Lang::get('modal_itinerario.conflicto_horas_titulo'),
    'ajustar'                => Lang::get('modal_itinerario.ajustar'),
    'ajustar_cancelar'       => Lang::get('modal_itinerario.ajustar_cancelar'),
    'hora_ajustada'          => Lang::get('modal_itinerario.hora_ajustada'),
    'hora_ajustada_msg'      => Lang::get('modal_itinerario.hora_ajustada_msg'),
    'error_conexion'         => Lang::get('modal_itinerario.error_conexion'),
    'error_conexion_msg'     => Lang::get('modal_itinerario.error_conexion_msg'),
];
?>

<!-- ── MINI-MODAL: GUARDAR EN ITINERARIO ── -->
<div class="modal fade" id="modalGuardarItinerario" tabindex="-1" style="z-index:100001">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; overflow:hidden">

            <div class="modal-header" style="background:linear-gradient(135deg,#16a34a,#15803d); color:#fff; border:none">
                <h5 class="modal-title">
                    <i class="fas fa-bookmark me-2"></i> <?= Lang::get('modal_itinerario.titulo') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                <!-- ══ BLOQUE REFERENCIAL ══ -->
                <div class="mb-4 p-3 rounded-3" style="background:#f0fdf4; border:1px solid #bbf7d0">

                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">

                        <span id="itinAtraccionCiudad"
                              class="d-none align-items-center gap-1 px-2 py-1 rounded-pill"
                              style="background:#dcfce7; color:#15803d; font-size:.72rem;
                                     border:1px solid #86efac; cursor:default">
                            <i class="fas fa-map-marker-alt" style="font-size:.65rem"></i>
                            <span class="tag-label"></span>
                        </span>

                        <span id="itinAtraccionTiempo"
                              class="d-inline-flex align-items-center gap-1 px-2 py-1 rounded-pill"
                              style="background:#e0f2fe; color:#075985; font-size:.72rem;
                                     border:1px solid #bae6fd; cursor:default">
                            <i class="fas fa-clock" style="font-size:.65rem"></i>
                            <span class="tag-label"></span>
                        </span>

                        <span id="itinAtraccionPrecio"
                              class="d-none align-items-center gap-1 px-2 py-1 rounded-pill"
                              style="background:#fef9c3; color:#854d0e; font-size:.72rem;
                                     border:1px solid #fde68a; cursor:default">
                            <i class="fas fa-tag" style="font-size:.65rem"></i>
                            <span class="tag-label"></span>
                        </span>

                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold mb-1" style="font-size:.78rem; color:#15803d">
                            <i class="fas fa-map-marked-alt me-1"></i> <?= Lang::get('modal_itinerario.lugar') ?>
                        </label>
                        <input type="text" id="itinAtraccionNombre"
                               class="form-control form-control-sm" readonly tabindex="-1"
                               style="background:#e9f7ef; border:1px solid #bbf7d0;
                                      color:#166534; font-weight:600; cursor:default;
                                      font-size:.85rem; pointer-events:none">
                    </div>

                    <div id="itinDescWrapper">
                        <label class="form-label fw-semibold mb-1" style="font-size:.78rem; color:#6b7280">
                            <i class="fas fa-align-left me-1"></i> <?= Lang::get('modal_itinerario.descripcion') ?>
                        </label>
                        <textarea id="itinAtraccionDesc"
                                  class="form-control form-control-sm" rows="2"
                                  readonly tabindex="-1"
                                  style="background:#e9f7ef; border:1px solid #bbf7d0;
                                         color:#4b5563; cursor:default; resize:none;
                                         font-size:.8rem; line-height:1.4;
                                         pointer-events:none"></textarea>
                    </div>
                </div>

                <!-- Fecha y hora — step=600 fuerza intervalos de 10 min en el picker -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <?= Lang::get('modal_itinerario.fecha_hora') ?> <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" id="itin_fecha_visita"
                           class="form-control" step="600">
                    <div class="form-text text-muted mt-1">
                        <i class="fas fa-info-circle me-1"></i>
                        <?= Lang::get('modal_itinerario.fecha_hora_ayuda') ?>
                    </div>
                </div>

                <!-- Nota personal -->
                <div class="mb-1">
                    <label class="form-label fw-semibold">
                        <?= Lang::get('modal_itinerario.nota_personal') ?> <small class="text-muted"><?= Lang::get('modal_itinerario.nota_opcional') ?></small>
                    </label>
                    <textarea id="itin_nota" class="form-control" rows="2"
                              placeholder="<?= Lang::get('modal_itinerario.nota_placeholder') ?>"></textarea>
                </div>

            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= Lang::get('modal_itinerario.cancelar') ?></button>
                <button type="button" class="btn btn-success" id="btnConfirmarItinerario">
                    <i class="fas fa-save me-1"></i> <?= Lang::get('modal_itinerario.guardar') ?>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ── DEPENDENCIAS ── (ya están en las vistas, pero las dejamos por si acaso) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/etreva-auth.js"></script>

<style>
.swal2-cancel.swal-dark-cancel {
    background: rgba(255,255,255,.08) !important;
    color: rgba(255,255,255,.65) !important;
    border: 1px solid rgba(255,255,255,.15) !important;
}
.swal2-cancel.swal-dark-cancel:hover {
    background: rgba(255,255,255,.16) !important;
    color: #fff !important;
}
</style>

<script>
// ── Textos traducidos para JavaScript ────────────────────────────────────────
const T_MODAL_ITIN = <?= json_encode($t_modal_itinerario, JSON_UNESCAPED_UNICODE) ?>;

$(document).ready(function () {

    /* ══════════════════════════════════════════════════════
       próximo intervalo de 10 minutos desde ahora
       Ej: 12:23 → 12:30 | 12:30 → 12:40 (siempre adelante)
    ══════════════════════════════════════════════════════ */
    function proximoSlot() {
        var d    = new Date();
        var mins = d.getMinutes();
        var resto = mins % 10;
        // avanzar al próximo múltiplo de 10 y agregar 10 min de margen
        d.setMinutes(mins + (resto === 0 ? 10 : 10 - resto), 0, 0);

        var pad = function(n){ return String(n).padStart(2,'0'); };
        return d.getFullYear()
             + '-' + pad(d.getMonth()+1)
             + '-' + pad(d.getDate())
             + 'T' + pad(d.getHours())
             + ':' + pad(d.getMinutes());
    }

    function fijarMinFecha() {
        var min = proximoSlot();
        var $f  = $('#itin_fecha_visita');
        $f.attr('min', min);
        // Si el valor elegido ya quedó en el pasado → corregirlo
        if ($f.val() && $f.val() < min) $f.val(min);
    }

    /* ── Parser tiempo estimado ── */
    function _parsearTE(te) {
        var DEF = { tipo:'minutos', valor:30, label:'Aprox. 30 min', restriccion:30 };
        if (!te) return DEF;
        if (typeof te === 'string') { try { te = JSON.parse(te); } catch(e){ return DEF; } }
        if (typeof te === 'number') {
            var v = Math.max(30, te);
            return { tipo:'minutos', valor:v, label:v+' min', restriccion: v>240?60:v };
        }
        if (typeof te !== 'object' || te === null) return DEF;
        var visita = (te.visita||'').toString().toLowerCase().trim();
        if (!visita) return DEF;
        var dm = visita.match(/(\d+)\s*d[ií]a/);
        if (dm) { var nd=parseInt(dm[1]); return {tipo:'dias',valor:nd,label:nd+(nd===1?' día':' días'),restriccion:0}; }
        var mins=0, hm=visita.match(/(\d+)\s*hora/), mm=visita.match(/(\d+)\s*minuto/);
        if (hm) mins += parseInt(hm[1])*60;
        if (mm) mins += parseInt(mm[1]);
        if (mins===0) mins=30;
        var lbl = mins>=60 ? Math.floor(mins/60)+'h'+(mins%60?' '+(mins%60)+'min':'') : mins+' min';
        return { tipo:'minutos', valor:mins, label:lbl, restriccion: mins>240?60:mins };
    }

    /* ── Abrir modal ── */
    window.abrirModalItinerario = function (datos) {
        if (!datos || !datos.id) return;

        var $modal = $('#modalGuardarItinerario');
        $modal.data('atraccion-id', datos.id);
        $modal.data('tiempo-parsed', _parsearTE(datos.tiempo_estimado));

        $('#itinAtraccionNombre').val(datos.nombre || '—');
        if (datos.descripcion) {
            $('#itinAtraccionDesc').val(datos.descripcion);
            $('#itinDescWrapper').show();
        } else {
            $('#itinAtraccionDesc').val('');
            $('#itinDescWrapper').hide();
        }

        // Tag ciudad
        var $ciu = $('#itinAtraccionCiudad');
        if (datos.ciudad) {
            $ciu.find('.tag-label').text(datos.ciudad);
            $ciu.removeClass('d-none').addClass('d-inline-flex');
        } else { $ciu.addClass('d-none'); }

        // Tag tiempo
        var p = _parsearTE(datos.tiempo_estimado);
        var tLabel = p.tipo==='dias' ? 'Actividad de '+p.label : 'Visita aprox. '+p.label;
        if (p.tipo==='minutos' && p.valor>240) tLabel += ' (restricción: 1h)';
        $('#itinAtraccionTiempo .tag-label').text(tLabel);

        // Tag precio
        var $pre = $('#itinAtraccionPrecio');
        var pNum = parseFloat(datos.precio_entrada);
        if (!isNaN(pNum) && pNum >= 0) {
            var pLabel = pNum===0 ? 'Entrada libre' : pNum.toFixed(2)+' '+(datos.moneda||'').trim();
            $pre.find('.tag-label').text(pLabel);
            $pre.removeClass('d-none').addClass('d-inline-flex');
        } else { $pre.addClass('d-none'); }

        $('#itin_nota').val('');
        $('#itin_fecha_visita').val('');
        fijarMinFecha();

        new bootstrap.Modal(document.getElementById('modalGuardarItinerario')).show();
    };

    // Refrescar min cada vez que el modal se muestra (por si cambió la hora)
    document.getElementById('modalGuardarItinerario')
        ?.addEventListener('shown.bs.modal', fijarMinFecha);

    // Si el usuario escribe/elige una hora pasada → corregir
    $('#itin_fecha_visita').on('change', function(){
        var min = proximoSlot();
        if ($(this).val() && $(this).val() < min) {
            $(this).val(min);
            if (typeof toastr !== 'undefined')
                toastr.warning('<?= Lang::get('modal_itinerario.hora_no_disponible_msg') ?>');
        }
    });

    /* ── Estado del modal ── */
    var guardadoExitoso = false;
    var modalItinEl    = document.getElementById('modalGuardarItinerario');
    var modalDetalleEl = document.getElementById('modalDetalleAtraccion');

    if (modalItinEl) {
        modalItinEl.addEventListener('hidden.bs.modal', function () {
            if (!guardadoExitoso && modalDetalleEl) {
                var inst = bootstrap.Modal.getInstance(modalDetalleEl)
                        || new bootstrap.Modal(modalDetalleEl);
                inst.show();
            }
            guardadoExitoso = false;
        });
    }

    /* ── Guardar ── */
    $('#btnConfirmarItinerario').on('click', function () {
        var $modal       = $('#modalGuardarItinerario');
        var atraccionId  = $modal.data('atraccion-id');
        var fecha_visita = $('#itin_fecha_visita').val();

        if (!atraccionId) {
            Swal.fire({ 
                icon:'error', 
                title:'<?= Lang::get('modal_itinerario.campo_requerido') ?>', 
                text:T_MODAL_ITIN.error_no_atraccion,
                background:'#1e2140', 
                color:'#e2e8f0',
                confirmButtonColor:'#667eea' 
            });
            return;
        }
        if (!fecha_visita) {
            Swal.fire({ 
                icon:'warning', 
                title:'<?= Lang::get('modal_itinerario.campo_requerido') ?>',
                text:T_MODAL_ITIN.error_fecha_requerida,
                background:'#1e2140', 
                color:'#e2e8f0',
                confirmButtonColor:'#667eea' 
            });
            return;
        }

        // Validación client-side: bloquear horas pasadas
        var minSlot = proximoSlot();
        if (fecha_visita < minSlot) {
            $('#itin_fecha_visita').val(minSlot);
            Swal.fire({ 
                icon:'warning', 
                title:T_MODAL_ITIN.hora_no_disponible,
                text:T_MODAL_ITIN.hora_no_disponible_msg,
                background:'#1e2140', 
                color:'#e2e8f0',
                confirmButtonColor:'#667eea' 
            });
            return;
        }

        var $btn = $(this).prop('disabled', true)
                         .html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url        : 'index.php?c=atracciones&a=guardarEnItinerario',
            type       : 'POST',
            contentType: 'application/json',
            data       : JSON.stringify({
                atraccion_id : atraccionId,
                nombre       : $('#itinAtraccionNombre').val().trim(),
                descripcion  : $('#itin_nota').val().trim(),
                fecha_visita : fecha_visita,
            }),
            dataType: 'json',

            success: function (res) {

                /* ── ÉXITO ── */
                if (res.success) {
                    guardadoExitoso = true;

                    // Cerrar modal itinerario
                    bootstrap.Modal.getInstance(
                        document.getElementById('modalGuardarItinerario')
                    ).hide();

                    // Cerrar modal detalle si sigue abierto
                    var elD = document.getElementById('modalDetalleAtraccion');
                    if (elD) { var iD = bootstrap.Modal.getInstance(elD); if (iD) iD.hide(); }

                    // Formatear fecha para mostrar
                    var fechaMostrar = fecha_visita.replace('T',' ');

                    Swal.fire({
                        icon: 'success',
                        title: T_MODAL_ITIN.exito_titulo,
                        html: `
                            <p style="color:#94a3b8;margin-bottom:14px;font-size:.92rem;">
                                ${T_MODAL_ITIN.exito_mensaje}
                            </p>
                            <div style="
                                background:rgba(102,126,234,.1);
                                border:1px solid rgba(102,126,234,.3);
                                border-radius:12px; padding:11px 16px;
                                color:#b8c3f8; font-size:.85rem; margin-bottom:4px;
                            ">
                                <i class="fas fa-calendar-check me-2" style="color:#667eea;"></i>
                                <strong>${fechaMostrar}</strong>
                            </div>
                            <p style="color:rgba(255,255,255,.35);font-size:.78rem;margin-top:12px;">
                                ${T_MODAL_ITIN.exito_badge}
                            </p>`,
                        background: '#1e2140',
                        color: '#e2e8f0',
                        confirmButtonColor: '#667eea',
                        confirmButtonText: '<i class="fas fa-calendar-alt me-1"></i> ' + T_MODAL_ITIN.ver_itinerarios,
                        showCancelButton: true,
                        cancelButtonText: T_MODAL_ITIN.cerrar,
                        reverseButtons: true,
                        customClass: { cancelButton: 'swal-dark-cancel' },
                    }).then(function(result) {
                        if (result.isConfirmed)
                            window.location.href = 'index.php?c=itinerarios&a=Index';
                    });

                /* ── CONFLICTO DÍAS ── */
                } else if (res.conflict && res.conflict_type === 'dias') {
                    Swal.fire({
                        icon:'info', 
                        title:T_MODAL_ITIN.conflicto_dias_titulo,
                        html: res.message,
                        background:'#1e2140', 
                        color:'#e2e8f0',
                        confirmButtonColor:'#667eea', 
                        confirmButtonText:'Entendido',
                    });

                /* ── CONFLICTO HORAS ── */
                } else if (res.conflict && res.conflict_type === 'horas') {
                    Swal.fire({
                        icon:'warning', 
                        title:T_MODAL_ITIN.conflicto_horas_titulo,
                        html: res.message + '<br><br>¿Querés ajustar automáticamente a la hora disponible?',
                        background:'#1e2140', 
                        color:'#e2e8f0',
                        showCancelButton:true,
                        confirmButtonText:T_MODAL_ITIN.ajustar,
                        cancelButtonText:T_MODAL_ITIN.ajustar_cancelar,
                        confirmButtonColor:'#667eea',
                        customClass:{ cancelButton:'swal-dark-cancel' },
                    }).then(function(result){
                        if (result.isConfirmed && res.disponible_desde) {
                            var fecha  = $('#itin_fecha_visita').val().split('T')[0];
                            $('#itin_fecha_visita').val(fecha + 'T' + res.disponible_desde);
                            Swal.fire({
                                icon:'info', 
                                title:T_MODAL_ITIN.hora_ajustada,
                                text: T_MODAL_ITIN.hora_ajustada_msg.replace('{hora}', res.disponible_desde),
                                background:'#1e2140', 
                                color:'#e2e8f0',
                                timer:2000, 
                                showConfirmButton:false,
                            });
                        }
                    });

                /* ── ERROR GENÉRICO ── */
                } else {
                    Swal.fire({
                        icon:'error', 
                        title:'<?= Lang::get('modal_itinerario.campo_requerido') ?>',
                        html: res.message || 'Ocurrió un error inesperado',
                        background:'#1e2140', 
                        color:'#e2e8f0',
                        confirmButtonColor:'#667eea',
                    });
                }
            },

            error: function () {
                Swal.fire({
                    icon:'error', 
                    title:T_MODAL_ITIN.error_conexion,
                    text:T_MODAL_ITIN.error_conexion_msg,
                    background:'#1e2140', 
                    color:'#e2e8f0',
                    confirmButtonColor:'#667eea',
                });
            },

            complete: function () {
                $btn.prop('disabled', false)
                    .html('<i class="fas fa-save me-1"></i> <?= Lang::get('modal_itinerario.guardar') ?>');
            }
        });
    });

});
</script>