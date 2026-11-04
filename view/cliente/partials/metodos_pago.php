<?php
/**
 * view/cliente/partials/metodos_pago.php
 * Selector de método de pago. Lo usan el primer pago (pago_pendiente.php) y la renovación/mejora (planes.php).
 *   · Bolivia: Yape / QR Simple, en bolivianos            → se adjunta la captura del comprobante.
 *   · Cripto:  USDT / USDC en TRC-20, BEP-20, Polygon o ERC-20 → captura del comprobante o hash de la transacción.
 *   · Binance Pay (Pay ID)                                 → se adjunta la captura del comprobante.
 *
 * Deja en el formulario: <input name="metodo_pago"> y <input name="referencia_pago"> (hash, solo cripto).
 * Desde JS: EtrevaPago.setMonto(usd) · EtrevaPago.valido(tieneComprobante) · EtrevaPago.agregar(formData)
 *           EtrevaPago.alCambiar(fn) avisa cuando cambia el método o el hash (para habilitar el botón).
 */
require_once __DIR__ . '/../../../model/metodosPago.php';
$CFG_PAGO = MetodosPago::config();
$tc  = (float)$CFG_PAGO['tipo_cambio_bs'];
$cr  = $CFG_PAGO['cripto'];
$bp  = $CFG_PAGO['binance_pay'];
$bandera = '<svg class="bandera" viewBox="0 0 3 2" aria-label="Bolivia"><rect width="3" height="2" fill="#007934"/><rect width="3" height="1.333" fill="#f9e300"/><rect width="3" height=".667" fill="#d52b1e"/></svg>';
$redesJs = [];
foreach ($cr['redes'] as $k => $r) {
    $redesJs[$k] = ['nombre' => $r[0], 'cadena' => $r[1], 'tipo' => $r[2], 'comision' => $r[4], 'monedas' => $r[5],
                    'dir' => $r[2] === 'tron' ? $cr['billetera_tron'] : $cr['billetera_evm']];
}
?>
<style>
.mpago { margin: 4px 0 12px; }
.mpago-tabs { display:flex; gap:5px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:14px; padding:5px; margin-bottom:12px; }
.mpago-tab { flex:1; border:none; background:transparent; color:rgba(255,255,255,.6); border-radius:10px; padding:9px 6px; font-weight:700; font-size:.78rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s,color .2s; white-space:nowrap; }
.mpago-tab:hover { color:#fff; }
.mpago-tab.activo { background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; box-shadow:0 4px 12px rgba(102,126,234,.3); }
.mpago-tab .bandera { width:18px; height:12px; border-radius:2px; flex-shrink:0; box-shadow:0 0 0 1px rgba(0,0,0,.25); }
.mpago-panel { display:none; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:14px; padding:14px; }
.mpago-panel.activo { display:block; }
.mpago-fila { display:flex; gap:14px; align-items:center; }
.mpago-qr { width:112px; height:112px; border-radius:12px; background:#fff; padding:6px; flex-shrink:0; display:flex; align-items:center; justify-content:center; box-sizing:border-box; }
.mpago-qr img, .mpago-qr canvas { width:100% !important; height:100% !important; object-fit:contain; }
.mpago-info { flex:1; min-width:0; font-size:.8rem; color:rgba(255,255,255,.65); line-height:1.5; }
.mpago-info p { margin:0 0 6px; }
.mpago-monto { font-size:1.25rem; font-weight:800; color:#fff; margin:0 0 4px; }
.mpago-monto small { font-size:.72rem; font-weight:600; color:rgba(255,255,255,.5); }
.mpago-chip { display:inline-flex; align-items:center; gap:5px; font-size:.68rem; font-weight:800; padding:3px 9px; border-radius:50px; background:rgba(16,185,129,.15); color:#34d399; margin-bottom:6px; }
.mpago-chip.cripto { background:rgba(240,185,11,.15); color:#f0b90b; }
.mpago-sel { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:8px; }
.mpago-sel button { border:1px solid rgba(255,255,255,.15); background:transparent; color:rgba(255,255,255,.7); border-radius:8px; padding:4px 10px; font-size:.73rem; font-weight:700; cursor:pointer; }
.mpago-sel button.activo { border-color:#f0b90b; color:#f0b90b; background:rgba(240,185,11,.1); }
.mpago-sel button small { font-weight:600; opacity:.7; margin-left:3px; }
.mpago-etq { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:rgba(255,255,255,.4); margin:2px 0 4px; }
.mpago-dir { display:flex; gap:6px; align-items:center; background:rgba(0,0,0,.25); border:1px solid rgba(255,255,255,.08); border-radius:10px; padding:7px 8px 7px 10px; margin:4px 0 8px; }
.mpago-dir code { flex:1; min-width:0; font-size:.72rem; color:#e2e8f0; word-break:break-all; background:none; }
.mpago-dir.grande code { font-size:1.05rem; font-weight:800; letter-spacing:.06em; }
.mpago-copiar { border:none; background:rgba(255,255,255,.1); color:#fff; border-radius:8px; padding:5px 10px; font-size:.72rem; font-weight:700; cursor:pointer; white-space:nowrap; }
.mpago-copiar:hover { background:rgba(255,255,255,.2); }
.mpago-aviso { display:flex; gap:7px; align-items:flex-start; font-size:.72rem; line-height:1.45; color:#fbbf24; background:rgba(251,191,36,.08); border:1px solid rgba(251,191,36,.25); border-radius:10px; padding:7px 10px; margin-top:6px; }
.mpago-paso { display:flex; gap:7px; align-items:flex-start; font-size:.74rem; line-height:1.45; color:#a5b4fc; background:rgba(102,126,234,.1); border:1px solid rgba(102,126,234,.25); border-radius:10px; padding:7px 10px; margin-top:10px; }
.mpago-ref { margin-top:10px; }
.mpago-ref label { display:block; font-size:.74rem; font-weight:700; color:rgba(255,255,255,.6); margin-bottom:4px; }
.mpago-ref input { box-sizing:border-box; width:100%; background:rgba(0,0,0,.25); border:1px solid rgba(255,255,255,.12); color:#fff; border-radius:10px; padding:8px 10px; font-size:.78rem; outline:none; font-family:ui-monospace,Consolas,monospace; }
.mpago-ref input:focus { border-color:#667eea; }
.mpago-ref input.mal { border-color:#ef4444; }
.mpago-nota { font-size:.68rem; color:rgba(255,255,255,.4); margin:8px 2px 0; line-height:1.4; }
@media (max-width:480px) { .mpago-fila { flex-direction:column; align-items:stretch; } .mpago-qr { margin:0 auto; } .mpago-tab { font-size:.72rem; } }
</style>

<div class="mpago" id="mpago">
    <input type="hidden" name="metodo_pago" id="mpagoMetodo" value="yape">

    <div class="mpago-tabs" role="tablist">
        <?php if (!empty($CFG_PAGO['yape']['activo'])): ?>
        <button type="button" class="mpago-tab activo" data-metodo="yape" role="tab"><?= $bandera ?> <?= Lang::get('pagos.tab_yape') ?></button>
        <?php endif; ?>
        <?php if (!empty($cr['activo'])): ?>
        <button type="button" class="mpago-tab" data-metodo="cripto" role="tab"><i class="fas fa-coins"></i> <?= Lang::get('pagos.tab_cripto') ?></button>
        <?php endif; ?>
        <?php if (!empty($bp['activo'])): ?>
        <button type="button" class="mpago-tab" data-metodo="binance_pay" role="tab"><i class="fas fa-bolt"></i> Binance Pay</button>
        <?php endif; ?>
    </div>

    <!-- Bolivia: Yape / QR Simple -->
    <div class="mpago-panel activo" data-panel="yape">
        <div class="mpago-fila">
            <div class="mpago-qr"><img src="<?= htmlspecialchars($CFG_PAGO['yape']['qr']) ?>" alt="QR Yape"></div>
            <div class="mpago-info">
                <span class="mpago-chip"><i class="fas fa-qrcode"></i> Yape · QR Simple</span>
                <p class="mpago-monto"><span class="mpago-bs">Bs 0,00</span> <small class="mpago-usd-eq">≈ $0</small></p>
                <p><?= Lang::get('pagos.yape_instruccion') ?></p>
                <p style="margin:0;"><?= Lang::get('pagos.titular') ?>: <strong style="color:#fff;"><?= htmlspecialchars($CFG_PAGO['yape']['titular']) ?></strong></p>
            </div>
        </div>
        <p class="mpago-nota"><?= str_replace(['{tc}', '{fecha}'], [number_format($tc, 2, ',', '.'), date('d/m/Y', strtotime($CFG_PAGO['tipo_cambio_fecha'] ?? 'today'))], Lang::get('pagos.tipo_cambio')) ?></p>
        <div class="mpago-paso"><i class="fas fa-camera"></i><span><?= Lang::get('pagos.paso_captura') ?></span></div>
    </div>

    <!-- Cripto estable: USDT / USDC en varias redes -->
    <div class="mpago-panel" data-panel="cripto">
        <div class="mpago-etq"><?= Lang::get('pagos.moneda') ?></div>
        <div class="mpago-sel" id="mpagoMonedas">
            <button type="button" data-moneda="USDT" class="activo">USDT</button>
            <button type="button" data-moneda="USDC">USDC</button>
        </div>
        <div class="mpago-etq"><?= Lang::get('pagos.red') ?></div>
        <div class="mpago-sel" id="mpagoRedes"></div>
        <div class="mpago-fila" style="margin-top:6px;">
            <div class="mpago-qr" id="mpagoQrCripto"></div>
            <div class="mpago-info">
                <span class="mpago-chip cripto"><i class="fas fa-link"></i> <span id="mpagoRedNombre"></span></span>
                <p class="mpago-monto"><span class="mpago-cripto-monto">0</span> <span class="mpago-cripto-moneda">USDT</span></p>
                <p style="margin:0;"><?= Lang::get('pagos.cripto_instruccion') ?></p>
            </div>
        </div>
        <div class="mpago-dir">
            <code id="mpagoDir"></code>
            <button type="button" class="mpago-copiar" data-copiar="#mpagoDir"><i class="far fa-copy"></i> <?= Lang::get('pagos.copiar') ?></button>
        </div>
        <div class="mpago-aviso"><i class="fas fa-exclamation-triangle"></i><span id="mpagoAvisoRed"></span></div>
        <div class="mpago-ref">
            <label for="mpagoRef"><?= Lang::get('pagos.ref_cripto') ?></label>
            <input type="text" name="referencia_pago" id="mpagoRef" maxlength="80" autocomplete="off" spellcheck="false">
        </div>
        <div class="mpago-paso"><i class="fas fa-receipt"></i><span><?= Lang::get('pagos.paso_cripto') ?></span></div>
    </div>

    <!-- Binance Pay -->
    <div class="mpago-panel" data-panel="binance_pay">
        <span class="mpago-chip cripto"><i class="fas fa-bolt"></i> Binance Pay · <?= Lang::get('pagos.sin_comision') ?></span>
        <p class="mpago-monto"><span class="mpago-cripto-monto">0</span> USDT</p>
        <p style="font-size:.8rem;color:rgba(255,255,255,.65);margin:0 0 4px;"><?= Lang::get('pagos.binance_instruccion') ?></p>
        <div class="mpago-dir grande">
            <code id="mpagoPayId"><?= htmlspecialchars($bp['pay_id']) ?></code>
            <button type="button" class="mpago-copiar" data-copiar="#mpagoPayId"><i class="far fa-copy"></i> <?= Lang::get('pagos.copiar') ?></button>
        </div>
        <p style="font-size:.74rem;color:rgba(255,255,255,.5);margin:0;">Pay ID · <?= htmlspecialchars($bp['nombre']) ?></p>
        <div class="mpago-paso"><i class="fas fa-camera"></i><span><?= Lang::get('pagos.paso_captura') ?></span></div>
    </div>

    <p class="mpago-nota"><i class="fas fa-info-circle"></i> <?= Lang::get('pagos.nota_factura') ?></p>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
window.EtrevaPago = (function () {
    const TC = <?= json_encode($tc) ?>;
    const REDES = <?= json_encode($redesJs, JSON_UNESCAPED_UNICODE) ?>;
    const T = <?= json_encode([
        'copiado' => Lang::get('pagos.copiado'), 'aviso_red' => Lang::get('pagos.cripto_aviso'),
        'falta_prueba' => Lang::get('pagos.falta_prueba'), 'falta_captura' => Lang::get('pagos.falta_captura'),
        'hash_invalido' => Lang::get('pagos.hash_invalido'), 'comision' => Lang::get('pagos.comision'),
        'baja' => Lang::get('pagos.comision_baja'), 'alta' => Lang::get('pagos.comision_alta'),
        'ph_evm' => '0x… (66)', 'ph_tron' => Lang::get('pagos.ph_tron'),
    ], JSON_UNESCAPED_UNICODE) ?>;
    const raiz = document.getElementById('mpago');
    const $ = s => raiz.querySelector(s);
    let usd = 0, tab = 'yape', moneda = 'USDT', red = 'trc20', qr = null;
    const oyentes = [];
    const avisar = () => oyentes.forEach(fn => { try { fn(); } catch (e) {} });

    const bs = v => 'Bs ' + (v * TC).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const metodo = () => tab === 'cripto' ? moneda.toLowerCase() + '_' + red : tab;
    const hash = () => $('#mpagoRef').value.trim();
    const hashOk = () => REDES[red].tipo === 'tron' ? /^[0-9a-fA-F]{64}$/.test(hash()) : /^0x[0-9a-fA-F]{64}$/.test(hash());

    function pintarRedes() {
        // Solo las redes donde existe la moneda elegida (USDC no circula en TRC-20)
        if (!REDES[red].monedas.includes(moneda)) red = Object.keys(REDES).find(k => REDES[k].monedas.includes(moneda));
        $('#mpagoRedes').innerHTML = Object.entries(REDES).filter(([, r]) => r.monedas.includes(moneda)).map(([k, r]) =>
            `<button type="button" data-red="${k}" class="${k === red ? 'activo' : ''}">${r.nombre}<small>${r.cadena}</small></button>`).join('');
        $('#mpagoRedes').querySelectorAll('button').forEach(b => b.addEventListener('click', () => { red = b.dataset.red; pintar(); }));
    }

    function pintar() {
        pintarRedes();
        const r = REDES[red];
        raiz.querySelectorAll('.mpago-bs').forEach(e => e.textContent = bs(usd));
        raiz.querySelectorAll('.mpago-usd-eq').forEach(e => e.textContent = '≈ $' + usd);
        raiz.querySelectorAll('.mpago-cripto-monto').forEach(e => e.textContent = usd.toFixed(2));
        $('.mpago-cripto-moneda').textContent = moneda;
        $('#mpagoRedNombre').textContent = `${r.nombre} · ${r.cadena} · ${T.comision} ${T[r.comision]}`;
        $('#mpagoDir').textContent = r.dir;
        $('#mpagoAvisoRed').textContent = T.aviso_red.replace('{red}', `${r.nombre} (${r.cadena})`).replace('{moneda}', moneda);
        $('#mpagoRef').placeholder = r.tipo === 'tron' ? T.ph_tron : T.ph_evm;
        if (tab === 'cripto' && window.QRCode) {
            $('#mpagoQrCripto').innerHTML = '';
            qr = new QRCode($('#mpagoQrCripto'), { text: r.dir, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.M });
        }
        $('#mpagoMetodo').value = metodo();
        $('#mpagoRef').classList.remove('mal');
        avisar();
    }

    raiz.querySelectorAll('.mpago-tab').forEach(b => b.addEventListener('click', () => {
        tab = b.dataset.metodo;
        raiz.querySelectorAll('.mpago-tab').forEach(x => x.classList.toggle('activo', x === b));
        raiz.querySelectorAll('.mpago-panel').forEach(p => p.classList.toggle('activo', p.dataset.panel === tab));
        pintar();
    }));
    $('#mpagoMonedas').querySelectorAll('button').forEach(b => b.addEventListener('click', () => {
        moneda = b.dataset.moneda;
        $('#mpagoMonedas').querySelectorAll('button').forEach(x => x.classList.toggle('activo', x === b));
        pintar();
    }));
    $('#mpagoRef').addEventListener('input', () => { $('#mpagoRef').classList.toggle('mal', hash() !== '' && !hashOk()); avisar(); });
    raiz.querySelectorAll('[data-copiar]').forEach(btn => btn.addEventListener('click', function () {
        const el = $(btn.dataset.copiar), txt = el.textContent.trim(), antes = btn.innerHTML;
        (navigator.clipboard ? navigator.clipboard.writeText(txt) : Promise.reject()).catch(() => {
            const rg = document.createRange(); rg.selectNodeContents(el);
            const s = getSelection(); s.removeAllRanges(); s.addRange(rg); document.execCommand('copy');
        }).finally(() => { btn.innerHTML = '<i class="fas fa-check"></i> ' + T.copiado; setTimeout(() => btn.innerHTML = antes, 1600); });
    }));

    const alerta = txt => window.Swal
        ? Swal.fire({ icon: 'warning', text: txt, background: '#1a1a2e', color: '#f5f5f5', confirmButtonColor: '#667eea' })
        : alert(txt);

    pintar();
    return {
        setMonto(v) { usd = Number(v) || 0; pintar(); },
        metodo, referencia: () => (tab === 'cripto' ? hash() : ''),
        esCripto: () => tab === 'cripto',
        /** ¿Ya hay prueba suficiente? Yape y Binance Pay: captura. Cripto: captura o hash válido. */
        listo(tieneComprobante) {
            if (tab !== 'cripto') return !!tieneComprobante;
            if (hash() !== '' && !hashOk()) return false;
            return !!tieneComprobante || hashOk();
        },
        /** Igual que listo(), pero avisa al usuario qué falta */
        valido(tieneComprobante) {
            if (this.listo(tieneComprobante)) return true;
            if (tab === 'cripto' && hash() !== '' && !hashOk()) { $('#mpagoRef').classList.add('mal'); $('#mpagoRef').focus(); alerta(T.hash_invalido); }
            else alerta(tab === 'cripto' ? T.falta_prueba : T.falta_captura);
            return false;
        },
        agregar(fd) { fd.append('metodo_pago', metodo()); fd.append('referencia_pago', this.referencia()); return fd; },
        alCambiar(fn) { oyentes.push(fn); },
    };
})();
</script>
