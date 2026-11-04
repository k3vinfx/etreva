<?php
/**
 * view/cliente/modal/modal_riesgo.php
 * Aviso de actividad de riesgo (trekking, montañismo, downhill). El turista debe confirmar que
 * practica la actividad bajo su propio riesgo; la aceptación queda registrada (Consentimiento::registrarRiesgo).
 *
 * Variables:
 *   $categoriaRiesgo  'trekking' | 'montanismo' | 'downhill'
 *   $idAtraccionRiesgo  id de la atracción (o null si viene del filtro del buscador)
 *   $idModalRiesgo      id del modal: 'modalDetalleAtraccion' cuando reemplaza al detalle (al aceptar se abre
 *                       el detalle), 'modalAvisoRiesgo' cuando lo pide el buscador (al aceptar avisa con un evento).
 */
$cat     = $categoriaRiesgo;
$idAtr   = $idAtraccionRiesgo ?? null;
$idModal = $idModalRiesgo ?? 'modalAvisoRiesgo';
$ICONOS  = ['trekking' => '🥾', 'montanismo' => '🏔️', 'downhill' => '🚵'];
?>
<div class="modal fade" id="<?= $idModal ?>" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:18px;border:none;overflow:hidden;">
      <div style="background:linear-gradient(135deg,#c2410c,#b91c1c);color:#fff;padding:20px 26px;">
        <div style="display:flex;align-items:center;gap:12px;">
          <span style="font-size:2.2rem;line-height:1;"><?= $ICONOS[$cat] ?? '⚠️' ?></span>
          <div>
            <h5 style="margin:0;font-weight:800;"><?= Lang::get('legal.riesgo_titulo') ?></h5>
            <div style="font-size:.86rem;opacity:.92;"><?= Lang::get('legal.riesgo_cat_' . $cat) ?></div>
          </div>
        </div>
      </div>
      <div class="modal-body" style="padding:18px 26px;font-size:.88rem;line-height:1.55;color:#334155;">
        <p style="margin:0 0 10px;"><strong><?= Lang::get('legal.riesgo_intro') ?></strong></p>
        <ul style="padding-left:18px;margin:0 0 12px;">
          <li><?= Lang::get('legal.riesgo_detalle_' . $cat) ?></li>
          <li><?= Lang::get('legal.riesgo_altura') ?></li>
          <li><?= Lang::get('legal.riesgo_no_operador') ?></li>
          <li><?= Lang::get('legal.riesgo_operador') ?></li>
          <li><?= Lang::get('legal.riesgo_salud') ?></li>
        </ul>
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:10px 14px;font-size:.8rem;color:#9a3412;">
          <i class="fas fa-balance-scale"></i> <?= Lang::get('legal.riesgo_ley') ?>
        </div>
        <label style="display:flex;gap:10px;align-items:flex-start;margin-top:14px;padding:10px 12px;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;">
          <input type="checkbox" class="riesgo-chk" style="margin-top:3px;width:18px;height:18px;flex-shrink:0;accent-color:#c2410c;">
          <span><strong><?= Lang::get('legal.riesgo_chk') ?></strong></span>
        </label>
        <div class="riesgo-error" style="display:none;color:#dc2626;font-size:.82rem;margin-top:8px;"></div>
      </div>
      <div class="modal-footer" style="border-top:1px solid #e2e8f0;padding:12px 26px;">
        <button type="button" class="btn btn-light riesgo-cancelar" data-bs-dismiss="modal" style="border-radius:10px;"><?= Lang::get('legal.riesgo_cancelar') ?></button>
        <button type="button" class="btn riesgo-aceptar" disabled style="background:#c2410c;color:#fff;border-radius:10px;font-weight:700;padding:8px 20px;"><?= Lang::get('legal.riesgo_aceptar') ?></button>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
    const el  = document.getElementById(<?= json_encode($idModal) ?>);
    const cat = <?= json_encode($cat) ?>, atr = <?= json_encode($idAtr) ?>;
    const chk = el.querySelector('.riesgo-chk'), btn = el.querySelector('.riesgo-aceptar'), err = el.querySelector('.riesgo-error');
    let aceptado = false;
    chk.addEventListener('change', () => { btn.disabled = !chk.checked; });

    btn.addEventListener('click', function () {
        btn.disabled = true; err.style.display = 'none';
        fetch('index.php?c=legal&a=AceptarRiesgo', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
            body: JSON.stringify({ categoria: cat, atraccion_id: atr, acepto: chk.checked })
        })
        .then(r => r.json())
        .then(resp => {
            if (!resp.success) throw new Error(resp.message || '');
            aceptado = true;
            document.dispatchEvent(new CustomEvent('etreva:riesgo-aceptado', { detail: { categoria: cat, atraccion_id: atr } }));
            bootstrap.Modal.getOrCreateInstance(el).hide();
        })
        .catch(e => { err.textContent = e.message || <?= json_encode(Lang::get('legal.error'), JSON_UNESCAPED_UNICODE) ?>; err.style.display = 'block'; btn.disabled = !chk.checked; });
    });

    el.addEventListener('hidden.bs.modal', function () {
        const cont = el.parentElement;
        if (!aceptado) {
            document.dispatchEvent(new CustomEvent('etreva:riesgo-cancelado', { detail: { categoria: cat } }));
            el.remove();
            return;
        }
        el.remove();
        // Venía del detalle: ahora sí se abre el detalle de la atracción
        if (<?= json_encode($idModal === 'modalDetalleAtraccion') ?> && atr && window.jQuery) {
            jQuery.get('index.php?c=atracciones&a=detalle&id=' + atr, function (html) {
                jQuery(cont).html(html);
                const m = document.getElementById('modalDetalleAtraccion');
                if (m) new bootstrap.Modal(m).show();
            });
        }
    });
})();
</script>
