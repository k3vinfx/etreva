<?php
/**
 * view/cliente/modal/modal_restringido.php
 * Se muestra en lugar del detalle cuando un menor de 18 años intenta abrir un bar o discoteca.
 * Usa el mismo id que el modal de detalle para que las vistas lo abran igual.
 */
?>
<div class="modal fade" id="modalDetalleAtraccion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px;border:none;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#7c3aed,#db2777);color:#fff;padding:26px 28px;text-align:center;">
                <div style="font-size:2.6rem;line-height:1;margin-bottom:8px;">🔞</div>
                <h5 style="margin:0;font-weight:800;"><?= Lang::get('legal.solo_mayores_titulo') ?></h5>
            </div>
            <div style="padding:22px 28px;font-size:0.92rem;line-height:1.55;color:#334155;">
                <p style="margin:0 0 10px;"><?= Lang::get('legal.solo_mayores') ?></p>
                <p style="margin:0;font-size:0.8rem;color:#64748b;"><?= Lang::get('legal.solo_mayores_ley') ?></p>
            </div>
            <div style="padding:0 28px 22px;text-align:right;">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="border-radius:10px;padding:8px 22px;"><?= Lang::get('legal.entendido') ?></button>
            </div>
        </div>
    </div>
</div>
