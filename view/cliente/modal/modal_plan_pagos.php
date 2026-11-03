<?php
/**
 * ============================================================
 *  REEMPLAZAR: view/modal/modal_plan_pagos.php
 * ============================================================
 *
 *  CAMBIOS:
 *  - Muestra días bonificados en el modal antes de confirmar
 *  - Swal de éxito distingue entre nuevo / renovacion / upgrade
 *  - Al confirmar el Swal de éxito → cierra sesión (Logout)
 * ============================================================
 */
?>
<style>
.modal-custom {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background-color: rgba(44, 85, 48, 0.85);
    backdrop-filter: blur(6px);
}
.modal-content-pago {
    background: #16213e;
    margin: 5% auto;
    padding: 32px 28px 28px;
    border-radius: 20px;
    width: 90%;
    max-width: 460px;
    border: 1px solid rgba(255,255,255,0.1);
    color: #e2e8f0;
    position: relative;
}
.modal-content-pago .close-modal {
    position: absolute;
    top: 16px; right: 20px;
    cursor: pointer;
    font-size: 22px;
    color: rgba(255,255,255,0.4);
    line-height: 1;
    transition: color .2s;
}
.modal-content-pago .close-modal:hover { color: #fff; }

/* Título */
.mp-titulo {
    font-family: 'Syne', sans-serif;
    font-size: 1.2rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
}
.mp-sub {
    font-size: .78rem;
    color: rgba(255,255,255,0.4);
    margin-bottom: 20px;
}

/* Resumen */
.mp-resumen {
    background: rgba(102,126,234,.1);
    border: 1px solid rgba(102,126,234,.25);
    border-radius: 14px;
    padding: 16px 18px;
    margin-bottom: 16px;
}
.mp-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .85rem;
    padding: 5px 0;
}
.mp-row .lbl { color: rgba(255,255,255,0.45); }
.mp-row .val { color: #fff; font-weight: 700; }
.mp-row.carryover-row .val { color: #f59e0b; }
.mp-row.total-row {
    border-top: 1px solid rgba(255,255,255,.07);
    margin-top: 6px;
    padding-top: 12px;
}
.mp-row.total-row .val { font-size: 1.15rem; color: #10b981; }

/* Alerta carry-over */
.mp-carryover-alert {
    background: rgba(245,158,11,.1);
    border: 1px solid rgba(245,158,11,.25);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: .82rem;
    margin-bottom: 14px;
    display: none; /* JS lo activa */
}
.mp-carryover-alert i { color: #f59e0b; flex-shrink: 0; margin-top: 2px; }
.mp-carryover-alert p { margin: 0; color: rgba(255,255,255,0.55); line-height: 1.5; }
.mp-carryover-alert strong { color: #e2e8f0; }

/* QR hint */
.mp-qr-hint {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: .8rem;
    color: rgba(255,255,255,.45);
    margin-bottom: 14px;
    line-height: 1.5;
}
.mp-qr-hint i { font-size: 1.3rem; color: #667eea; flex-shrink: 0; }

/* Upload */
.mp-upload {
    border: 2px dashed rgba(102,126,234,.35);
    border-radius: 14px;
    padding: 22px;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    position: relative;
    margin-bottom: 18px;
}
.mp-upload:hover { border-color: rgba(102,126,234,.65); background: rgba(102,126,234,.05); }
.mp-upload input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.mp-upload .up-icon { font-size: 1.8rem; color: rgba(102,126,234,.5); display: block; margin-bottom: 6px; }
.mp-upload .up-txt  { font-size: .8rem; color: rgba(255,255,255,.4); margin: 0; }
.mp-upload .up-ok   { font-size: .82rem; color: #10b981; font-weight: 600; margin: 0; }

/* Botón activar */
.mp-btn-activar {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: .95rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.mp-btn-activar:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(102,126,234,.4);
}
.mp-btn-activar:disabled { opacity: .45; cursor: not-allowed; transform: none; }
.mp-terms {
    font-size: .68rem;
    color: rgba(255,255,255,.25);
    text-align: center;
    margin-top: 10px;
    margin-bottom: 0;
}
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&display=swap" rel="stylesheet">

<!-- ═══ MODAL PAGO ═══ -->
<div id="modalPago" class="modal-custom">
    <div class="modal-content-pago">
        <span class="close-modal" onclick="cerrarModalPago()">&times;</span>

        <div class="mp-titulo" id="mpTitulo">Activar plan</div>
        <div class="mp-sub"   id="mpSub">Subí tu comprobante para activar</div>

        <!-- Resumen -->
        <div class="mp-resumen">
            <div class="mp-row">
                <span class="lbl">Plan</span>
                <span class="val" id="mpPlan">—</span>
            </div>
            <div class="mp-row">
                <span class="lbl">Período</span>
                <span class="val" id="mpPeriodo">—</span>
            </div>
            <div class="mp-row">
                <span class="lbl">Días del plan</span>
                <span class="val" id="mpDiasBase">—</span>
            </div>
            <!-- Carry-over: solo visible si hay días restantes -->
            <div class="mp-row carryover-row" id="mpRowCarryover" style="display:none;">
                <span class="lbl"><i class="fas fa-gift me-1" style="color:#f59e0b;"></i>Días bonificados</span>
                <span class="val" id="mpCarryover">+0</span>
            </div>
            <div class="mp-row" id="mpRowTotalDias" style="display:none;">
                <span class="lbl">Total días</span>
                <span class="val" id="mpTotalDias" style="color:#10b981;"></span>
            </div>
            <div class="mp-row total-row">
                <span class="lbl" style="color:#e2e8f0;font-weight:700;">Total a pagar</span>
                <span class="val" id="mpTotal">$0</span>
            </div>
        </div>

        <!-- Alerta carry-over -->
        <div class="mp-carryover-alert" id="mpCarryoverAlert">
            <i class="fas fa-gift"></i>
            <p id="mpCarryoverTxt"></p>
        </div>

        <!-- QR hint -->
        <div class="mp-qr-hint">
            <i class="fas fa-qrcode"></i>
            <span>Realizá el pago por QR o transferencia y subí la captura del comprobante para activar tu plan.</span>
        </div>

        <!-- Upload -->
        <div class="mp-upload" id="mpUploadArea">
            <input type="file" id="mpFileInput" accept="image/*" onchange="mpOnFileChange(this)">
            <i class="fas fa-cloud-upload-alt up-icon" id="mpUpIcon"></i>
            <p class="up-txt" id="mpUpTxt">Clic para subir captura del comprobante</p>
            <p style="font-size:.68rem;color:rgba(255,255,255,.25);margin-top:4px;">JPG, PNG, WEBP · máx 5MB</p>
        </div>

        <!-- Botón -->
        <button class="mp-btn-activar" id="mpBtnActivar" onclick="mpProcesarPago()" disabled>
            <i class="fas fa-rocket"></i> Activar plan
        </button>
        <p class="mp-terms">Al confirmar aceptás los términos de Etreva Travel</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── Datos inyectados desde PHP (deben existir en la vista padre) ──────────────
// PRECIOS_MP, DIAS_MP, DIAS_RESTANTES_MP, PLAN_ACTIVO_MP, NIVEL_ACTUAL_MP
// Si la vista ya los define, no hace falta redefinirlos aquí.

const MP_PERIODOS = { weekly: 'Semanal', monthly: 'Mensual', yearly: 'Anual' };

let _mp_periodo    = 'weekly';
let _mp_planNombre = null;
let _mp_planNivel  = 0;

// ── Abrir modal ───────────────────────────────────────────────────────────────
function abrirModalPago(planNombre, planNivel, periodo) {
    _mp_planNombre = planNombre;
    _mp_planNivel  = planNivel;
    _mp_periodo    = periodo || _mp_periodo;

    // Reset upload
    document.getElementById('mpFileInput').value        = '';
    document.getElementById('mpUpIcon').className       = 'fas fa-cloud-upload-alt up-icon';
    document.getElementById('mpUpIcon').style.color     = '';
    document.getElementById('mpUpTxt').textContent      = 'Clic para subir captura del comprobante';
    document.getElementById('mpUpTxt').className        = 'up-txt';
    document.getElementById('mpBtnActivar').disabled    = true;

    _mpActualizarResumen();
    document.getElementById('modalPago').style.display = 'block';
}

function cerrarModalPago() {
    document.getElementById('modalPago').style.display = 'none';
}

// Cerrar al hacer clic fuera
window.addEventListener('click', e => {
    if (e.target === document.getElementById('modalPago')) cerrarModalPago();
});

// ── Actualizar resumen (se llama también al cambiar período) ──────────────────
function _mpActualizarResumen() {
    if (!_mp_planNombre) return;

    const precio    = (typeof PRECIOS_MP !== 'undefined') ? (PRECIOS_MP[_mp_planNombre]?.[_mp_periodo] ?? 0) : 0;
    const diasBase  = (typeof DIAS_MP    !== 'undefined') ? (DIAS_MP[_mp_planNombre]?.[_mp_periodo]    ?? 30) : 30;
    const diasRest  = (typeof DIAS_RESTANTES_MP !== 'undefined') ? DIAS_RESTANTES_MP : 0;
    const activo    = (typeof PLAN_ACTIVO_MP    !== 'undefined') ? PLAN_ACTIVO_MP    : false;
    const nivelAct  = (typeof NIVEL_ACTUAL_MP   !== 'undefined') ? NIVEL_ACTUAL_MP   : 0;

    // Carry-over aplica solo si: hay plan activo Y es mismo nivel o upgrade
    const esUpgradeOIgual = _mp_planNivel >= nivelAct;
    const carryover = (activo && esUpgradeOIgual) ? diasRest : 0;
    const totalDias = diasBase + carryover;

    // Motivo para el título del modal
    let subtitulo = 'Subí tu comprobante para activar';
    if (activo && _mp_planNivel === nivelAct) subtitulo = 'Renovación — tus días restantes se suman';
    if (activo && _mp_planNivel >  nivelAct) subtitulo = 'Upgrade — tus días restantes se transfieren';

    document.getElementById('mpTitulo').textContent  = _mp_planNombre;
    document.getElementById('mpSub').textContent     = subtitulo;
    document.getElementById('mpPlan').textContent    = _mp_planNombre;
    document.getElementById('mpPeriodo').textContent = MP_PERIODOS[_mp_periodo] ?? _mp_periodo;
    document.getElementById('mpDiasBase').textContent = diasBase + ' días';
    document.getElementById('mpTotal').textContent   = '$' + precio;

    // Carry-over rows
    if (carryover > 0) {
        document.getElementById('mpRowCarryover').style.display  = '';
        document.getElementById('mpRowTotalDias').style.display  = '';
        document.getElementById('mpCarryover').textContent       = '+' + carryover + ' días';
        document.getElementById('mpTotalDias').textContent       = totalDias + ' días en total';

        const alert = document.getElementById('mpCarryoverAlert');
        alert.style.display = 'flex';
        document.getElementById('mpCarryoverTxt').innerHTML =
            `<strong>¡Días bonificados!</strong> Tus <strong>${carryover} días</strong> restantes se sumarán automáticamente. Total: <strong>${totalDias} días</strong>.`;
    } else {
        document.getElementById('mpRowCarryover').style.display  = 'none';
        document.getElementById('mpRowTotalDias').style.display  = 'none';
        document.getElementById('mpCarryoverAlert').style.display = 'none';
    }
}

// Llamar cuando cambia el período desde la vista padre
function mpCambiarPeriodo(periodo) {
    _mp_periodo = periodo;
    _mpActualizarResumen();
}

// ── Cambio de archivo ─────────────────────────────────────────────────────────
function mpOnFileChange(input) {
    const archivo = input.files[0];
    if (!archivo) return;

    if (archivo.size > 5 * 1024 * 1024) {
        Swal.fire({
            icon: 'warning', title: 'Archivo muy grande',
            text: 'El comprobante no puede superar 5MB.',
            background: '#16213e', color: '#e2e8f0',
        });
        input.value = '';
        return;
    }

    document.getElementById('mpUpIcon').className   = 'fas fa-check-circle up-icon';
    document.getElementById('mpUpIcon').style.color = '#10b981';
    document.getElementById('mpUpTxt').textContent  = archivo.name;
    document.getElementById('mpUpTxt').className    = 'up-ok';
    document.getElementById('mpBtnActivar').disabled = false;
}

// ── Procesar pago ─────────────────────────────────────────────────────────────
async function mpProcesarPago() {
    const archivo = document.getElementById('mpFileInput').files[0];
    if (!archivo || !_mp_planNombre) return;

    const precio = (typeof PRECIOS_MP !== 'undefined') ? (PRECIOS_MP[_mp_planNombre]?.[_mp_periodo] ?? 0) : 0;

    cerrarModalPago();

    Swal.fire({
        title: 'Procesando pago...',
        text: 'Validando tu comprobante.',
        allowOutsideClick: false,
        background: '#16213e', color: '#e2e8f0',
        didOpen: () => Swal.showLoading(),
    });

    const fd = new FormData();
    fd.append('plan',        _mp_planNombre);
    fd.append('monto',       precio);
    fd.append('periodo',     _mp_periodo);
    fd.append('comprobante', archivo);

    try {
        const r      = await fetch('index.php?c=pago&a=ProcesarPago', { method: 'POST', body: fd });
        const result = await r.json();

        if (result.success) {

            // Título según motivo
            const titulos = {
                nuevo:      '¡Plan activado!',
                renovacion: '¡Plan renovado!',
                upgrade:    '¡Plan actualizado!',
            };
            const titulo = titulos[result.motivo_pago] ?? '¡Listo!';

            let html = `Tu plan <strong>${result.plan}</strong> fue activado.`;

            if (result.dias_carryover > 0) {
                html += `<br><small style="color:#f59e0b;">
                    <i class="fas fa-gift"></i>
                    +${result.dias_carryover} días bonificados →
                    ${result.dias_totales} días en total
                </small>`;
            }

            if (result.fecha_vencimiento) {
                const f = new Date(result.fecha_vencimiento.replace(' ', 'T'));
                html += `<br><small style="color:#94a3b8;">
                    Vence: ${f.toLocaleDateString('es-ES', { day:'2-digit', month:'long', year:'numeric' })}
                </small>`;
            }

            // ─── Swal éxito: al confirmar cierra sesión ───────────────────
            Swal.fire({
                icon: 'success',
                title: titulo,
                html,
                confirmButtonText: '<i class="fas fa-sign-in-alt"></i> Volver a iniciar sesión',
                confirmButtonColor: '#667eea',
                background: '#16213e', color: '#e2e8f0',
                allowOutsideClick: false,   // obliga a presionar el botón
                allowEscapeKey: false,
            }).then(() => {
                window.location.href = 'http://localhost/etreva/index.php?c=login&a=Logout';
            });

        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'No se pudo procesar el pago.',
                confirmButtonColor: '#ef4444',
                background: '#16213e', color: '#e2e8f0',
            });
        }

    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: 'error', title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor.',
            background: '#16213e', color: '#e2e8f0',
        });
    }
}
</script>