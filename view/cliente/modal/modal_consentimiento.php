<?php
/**
 * view/cliente/modal/modal_consentimiento.php
 * Se muestra al turista la primera vez que entra (y cada vez que cambia la versión del reglamento).
 * No se puede cerrar sin aceptar: es el consentimiento previo, expreso e informado (Ley 164 / DS 1793).
 * Requiere $consentimientoMenor (bool). No depende de Bootstrap.
 */
$esMenorCons = !empty($consentimientoMenor);
?>
<style>
#consOverlay { position:fixed; inset:0; z-index:20000; background:rgba(15,23,42,.72); backdrop-filter:blur(4px);
    display:flex; align-items:center; justify-content:center; padding:16px; font-family:inherit; }
#consOverlay .cons-card { background:#fff; color:#1e293b; width:100%; max-width:760px; max-height:94vh; border-radius:20px;
    display:flex; flex-direction:column; overflow:hidden; box-shadow:0 24px 60px rgba(0,0,0,.35); }
#consOverlay .cons-head { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; padding:20px 26px; }
#consOverlay .cons-head h3 { margin:0 0 4px; font-size:1.25rem; font-weight:800; }
#consOverlay .cons-head p { margin:0; font-size:.85rem; opacity:.9; }
#consOverlay .cons-cuerpo { padding:16px 26px; overflow-y:auto; flex:1; min-height:0; }
#consOverlay .cons-aviso { display:flex; gap:10px; align-items:flex-start; border-radius:12px; padding:11px 14px; font-size:.84rem; margin-bottom:10px; line-height:1.45; }
#consOverlay .cons-aviso.menor  { background:#fdf2f8; border:1px solid #fbcfe8; color:#9d174d; }
#consOverlay .cons-aviso.riesgo { background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; }
#consOverlay .cons-aviso .ico { font-size:1.3rem; line-height:1; }
#consOverlay .reglamento-caja { border:1px solid #e2e8f0; border-radius:12px; padding:4px 16px; max-height:34vh; overflow-y:auto; background:#f8fafc; font-size:.82rem; line-height:1.55; }
#consOverlay .reglamento-texto h4 { font-size:.9rem; margin:14px 0 6px; color:#312e81; }
#consOverlay .reglamento-texto ul { padding-left:18px; margin:0 0 8px; }
#consOverlay .reglamento-texto p { margin:0 0 8px; }
#consOverlay .reg-meta { color:#64748b; font-size:.75rem; margin-top:10px !important; }
#consOverlay .cons-checks { margin-top:12px; display:flex; flex-direction:column; gap:8px; }
#consOverlay .cons-check { display:flex; gap:10px; align-items:flex-start; font-size:.86rem; line-height:1.4; cursor:pointer; padding:8px 10px; border-radius:10px; border:1px solid #e2e8f0; }
#consOverlay .cons-check:hover { background:#f8fafc; }
#consOverlay .cons-check input { margin-top:3px; width:17px; height:17px; flex-shrink:0; accent-color:#4f46e5; cursor:pointer; }
#consOverlay .cons-check .req { color:#dc2626; font-weight:700; }
#consOverlay .cons-check .opc { color:#64748b; font-size:.76rem; }
#consOverlay .cons-pie { padding:14px 26px; border-top:1px solid #e2e8f0; display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap; }
#consOverlay .cons-pie small { color:#64748b; font-size:.76rem; }
#consOverlay .cons-btn { background:#4f46e5; color:#fff; border:none; border-radius:12px; padding:11px 22px; font-weight:700; cursor:pointer; font-size:.9rem; }
#consOverlay .cons-btn:disabled { background:#a5b4fc; cursor:not-allowed; }
#consOverlay .cons-salir { color:#64748b; font-size:.82rem; text-decoration:none; margin-right:auto; }
#consOverlay .cons-error { color:#dc2626; font-size:.82rem; width:100%; display:none; }
</style>

<div id="consOverlay" role="dialog" aria-modal="true" aria-labelledby="consTitulo">
  <div class="cons-card">
    <div class="cons-head">
        <h3 id="consTitulo"><?= Lang::get('legal.titulo') ?></h3>
        <p><?= Lang::get('legal.subtitulo') ?></p>
    </div>

    <div class="cons-cuerpo">
        <?php if ($esMenorCons): ?>
        <div class="cons-aviso menor"><span class="ico">🔞</span><div><?= Lang::get('legal.aviso_menor') ?></div></div>
        <?php endif; ?>
        <div class="cons-aviso riesgo"><span class="ico">⚠️</span><div><?= Lang::get('legal.aviso_riesgo') ?></div></div>

        <div class="reglamento-caja" tabindex="0"><?php include __DIR__ . '/../../legal/reglamento_texto.php'; ?></div>
        <p style="font-size:.74rem;color:#64748b;margin:6px 2px 0;"><?= Lang::get('legal.rige_espanol') ?>
            <a href="index.php?c=legal&a=Reglamento" target="_blank" rel="noopener"><?= Lang::get('legal.ver_completo') ?></a></p>

        <div class="cons-checks">
            <label class="cons-check"><input type="checkbox" id="consTerminos" class="cons-req">
                <span><span class="req">*</span> <?= Lang::get('legal.chk_terminos') ?></span></label>
            <label class="cons-check"><input type="checkbox" id="consDatos" class="cons-req">
                <span><span class="req">*</span> <?= Lang::get('legal.chk_datos') ?></span></label>
            <label class="cons-check"><input type="checkbox" id="consRiesgos" class="cons-req">
                <span><span class="req">*</span> <?= Lang::get('legal.chk_riesgos') ?></span></label>
            <?php if ($esMenorCons): ?>
            <label class="cons-check"><input type="checkbox" id="consTutor" class="cons-req">
                <span><span class="req">*</span> <?= Lang::get('legal.chk_tutor') ?></span></label>
            <?php endif; ?>
            <label class="cons-check"><input type="checkbox" id="consPerfil">
                <span><?= Lang::get('legal.chk_perfil') ?> <span class="opc">(<?= Lang::get('legal.opcional') ?>)</span></span></label>
        </div>
    </div>

    <div class="cons-pie">
        <a class="cons-salir" href="index.php?c=login&a=Logout"><?= Lang::get('legal.no_acepto') ?></a>
        <small><span style="color:#dc2626;font-weight:700;">*</span> <?= Lang::get('legal.obligatorio') ?></small>
        <button type="button" class="cons-btn" id="consAceptar" disabled><?= Lang::get('legal.aceptar') ?></button>
        <div class="cons-error" id="consError"></div>
    </div>
  </div>
</div>

<script>
(function () {
    const req = [...document.querySelectorAll('#consOverlay .cons-req')];
    const btn = document.getElementById('consAceptar');
    const err = document.getElementById('consError');
    const marcado = id => !!(document.getElementById(id) || {}).checked;
    req.forEach(c => c.addEventListener('change', () => { btn.disabled = !req.every(x => x.checked); }));
    document.documentElement.style.overflow = 'hidden';

    btn.addEventListener('click', function () {
        btn.disabled = true; err.style.display = 'none';
        fetch('index.php?c=legal&a=Aceptar', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
            body: JSON.stringify({ terminos: marcado('consTerminos'), datos: marcado('consDatos'), riesgos: marcado('consRiesgos'),
                                   tutor: marcado('consTutor'), perfil_publico: marcado('consPerfil') })
        })
        .then(r => r.json())
        .then(resp => {
            if (!resp.success) throw new Error(resp.message || '');
            document.getElementById('consOverlay').remove();
            document.documentElement.style.overflow = '';
        })
        .catch(e => {
            err.textContent = e.message || <?= json_encode(Lang::get('legal.error'), JSON_UNESCAPED_UNICODE) ?>;
            err.style.display = 'block';
            btn.disabled = !req.every(x => x.checked);
        });
    });
})();
</script>
