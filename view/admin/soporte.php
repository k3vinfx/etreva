<?php include 'view/menu.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Soporte - Etreva Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#0f1117; --surf:#1c2235; --surf2:#222b42; --border:rgba(255,255,255,.07);
    --text:#e2e8f0; --muted:rgba(255,255,255,.45);
    --amber:#fbbf24; --blue:#38bdf8; --green:#4ade80; --red:#ef4444;
}
*{box-sizing:border-box}
html,body{background:var(--bg)!important;color:var(--text)!important;font-family:'DM Sans',sans-serif;min-height:100vh;margin:0}
main{margin-left:280px;min-height:100vh;transition:all .4s}
main.min-main{margin-left:85px}
@media(max-width:768px){main{margin-left:0!important}}

.hero{padding:40px 28px 24px;border-bottom:1px solid var(--border);background:linear-gradient(135deg,#0a0d1a 0%,#0d1526 60%,var(--bg) 100%)}
.hero h1{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;margin:0 0 6px;background:linear-gradient(135deg,#fff,#7dd3fc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{color:var(--muted);margin:0;font-size:.9rem}

.filtros{display:flex;gap:8px;padding:20px 28px 0;flex-wrap:wrap}
.filtro{background:var(--surf);border:1px solid var(--border);color:var(--muted);border-radius:50px;padding:7px 16px;font-size:.8rem;font-weight:700;cursor:pointer}
.filtro.activo{background:var(--blue);border-color:var(--blue);color:#0f172a}

.lista{padding:20px 28px 40px;display:grid;gap:14px;max-width:980px}
.vacio{color:var(--muted);text-align:center;padding:50px 0}
.sol{background:var(--surf);border:1px solid var(--border);border-radius:16px;padding:18px 20px}
.sol.pendiente{border-left:4px solid var(--amber)}
.sol.en_revision{border-left:4px solid var(--blue)}
.sol.resuelta{border-left:4px solid var(--green);opacity:.8}
.sol-top{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px}
.sol-user b{font-size:.95rem}
.sol-user small{display:block;color:var(--muted);font-size:.78rem}
.badge{display:inline-block;border-radius:20px;padding:3px 10px;font-size:.7rem;font-weight:800}
.badge.pendiente{background:rgba(251,191,36,.15);color:var(--amber)}
.badge.en_revision{background:rgba(56,189,248,.15);color:var(--blue)}
.badge.resuelta{background:rgba(74,222,128,.15);color:var(--green)}
.meta{display:flex;gap:14px;flex-wrap:wrap;font-size:.75rem;color:var(--muted);margin-bottom:10px}
.meta strong{color:var(--text);font-weight:600}
.msg{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:10px 12px;font-size:.85rem;white-space:pre-wrap;margin-bottom:10px}
.resp-prev{background:rgba(74,222,128,.06);border:1px solid rgba(74,222,128,.25);border-radius:10px;padding:10px 12px;font-size:.83rem;white-space:pre-wrap;margin-bottom:10px}
textarea.resp{width:100%;min-height:70px;border-radius:10px;padding:9px 11px;background:var(--surf2);border:1px solid rgba(255,255,255,.12);color:var(--text);font-family:inherit;font-size:.83rem;resize:vertical}
textarea.resp:focus{outline:none;border-color:var(--blue)}
.acciones{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.btn{border:none;border-radius:10px;padding:8px 14px;font-size:.78rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-azul{background:rgba(56,189,248,.15);color:var(--blue)}
.btn-verde{background:var(--green);color:#052e16}
.btn-gris{background:rgba(255,255,255,.06);color:var(--muted)}
.btn:disabled{opacity:.5;cursor:default}
.aviso{position:fixed;bottom:22px;right:22px;background:#16213e;border:1px solid rgba(56,189,248,.3);border-radius:12px;padding:10px 16px;font-size:.85rem;display:none;z-index:50}
</style>
</head>
<body>
<main>
    <div class="hero">
        <h1>Solicitudes de soporte</h1>
        <p>Mensajes que los clientes envían desde la app (por ejemplo, "ya pagué y mi plan no se activó"). Respóndeles aquí: lo ven en su propio modal.</p>
    </div>

    <div class="filtros">
        <button class="filtro activo" data-estado="abiertas">Abiertas</button>
        <button class="filtro" data-estado="pendiente">Pendientes</button>
        <button class="filtro" data-estado="en_revision">En revisión</button>
        <button class="filtro" data-estado="resuelta">Resueltas</button>
        <button class="filtro" data-estado="">Todas</button>
    </div>

    <div id="lista" class="lista"><div class="vacio">Cargando…</div></div>
</main>
<div id="aviso" class="aviso"></div>

<script>
const ESTADOS = { pendiente: 'Pendiente', en_revision: 'En revisión', resuelta: 'Resuelta' };
const TIPOS   = { pago_no_confirmado: 'Pagó y su plan no se activó', consulta_pago: 'Consulta por su pago', pago_rechazado: 'Reclamo por pago rechazado', otro: 'Otro' };
const PAGOS   = { pendiente_pago: 'Comprobante pendiente', activo_pagado: 'Plan activo', rechazado: 'Rechazado', pendiente_nuevo: 'Sin pagar' };
let filtro = 'abiertas';

const esc   = s => { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; };
const fecha = s => s ? new Date(String(s).replace(' ', 'T')).toLocaleString('es-BO', { dateStyle: 'medium', timeStyle: 'short' }) : '—';

function aviso(t) {
    const a = document.getElementById('aviso');
    a.textContent = t; a.style.display = 'block';
    setTimeout(() => a.style.display = 'none', 2500);
}

function cargar() {
    const estado = filtro === 'abiertas' ? '' : filtro;
    fetch('index.php?c=adminsoporte&a=Listar&estado=' + encodeURIComponent(estado), { credentials: 'same-origin' })
        .then(r => r.json())
        .then(res => {
            let data = res.data || [];
            if (filtro === 'abiertas') data = data.filter(s => s.estado !== 'resuelta');
            render(data);
        })
        .catch(() => document.getElementById('lista').innerHTML = '<div class="vacio">No se pudo cargar.</div>');
}

function render(data) {
    const lista = document.getElementById('lista');
    if (!data.length) { lista.innerHTML = '<div class="vacio">No hay solicitudes en esta vista. 🎉</div>'; return; }
    lista.innerHTML = data.map(s => `
        <div class="sol ${s.estado}" data-id="${s.id}">
            <div class="sol-top">
                <div class="sol-user"><b>${esc(s.nombre)} ${esc(s.apellido)}</b><small>${esc(s.email)}</small></div>
                <div class="badge ${s.estado}">${ESTADOS[s.estado] || s.estado}</div>
            </div>
            <div class="meta">
                <div>Motivo: <strong>${TIPOS[s.tipo] || s.tipo}</strong></div>
                <div>Plan: <strong>${esc(s.pago_plan || '—')}</strong> · ${PAGOS[s.pago_estado] || esc(s.pago_estado || '—')}</div>
                <div>Comprobante: <strong>${fecha(s.pago_fecha)}</strong></div>
                <div>Enviada: <strong>${fecha(s.created_at)}</strong></div>
            </div>
            <div class="msg">${esc(s.mensaje)}</div>
            ${s.respuesta ? `<div class="resp-prev"><b>Tu respuesta:</b>\n${esc(s.respuesta)}</div>` : ''}
            ${s.estado !== 'resuelta' ? `
                <textarea class="resp" placeholder="Respuesta para el cliente (la verá en su modal de soporte)…"></textarea>
                <div class="acciones">
                    ${s.estado === 'pendiente' ? `<button class="btn btn-azul" onclick="actualizar(${s.id}, 'en_revision', this)">👀 Marcar en revisión</button>` : ''}
                    <button class="btn btn-verde" onclick="actualizar(${s.id}, 'resuelta', this)">✅ Responder y resolver</button>
                    <a class="btn btn-gris" href="index.php?c=admin&a=Usuarios">💳 Ir a Clientes / Pagos</a>
                </div>` : ''}
        </div>`).join('');
}

function actualizar(id, estado, btn) {
    const card = btn.closest('.sol');
    const respuesta = card.querySelector('textarea.resp')?.value.trim() || '';
    if (estado === 'resuelta' && !respuesta) { aviso('Escribe una respuesta antes de resolver.'); return; }

    btn.disabled = true;
    fetch('index.php?c=adminsoporte&a=Actualizar', {
        method: 'POST', credentials: 'same-origin',
        body: new URLSearchParams({ id, estado, respuesta })
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) throw new Error(res.message || 'Error');
        aviso(estado === 'resuelta' ? 'Solicitud resuelta. El cliente ya ve tu respuesta.' : 'Marcada en revisión.');
        cargar();
    })
    .catch(e => { aviso(e.message); btn.disabled = false; });
}

document.querySelectorAll('.filtro').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('.filtro').forEach(x => x.classList.remove('activo'));
    b.classList.add('activo');
    filtro = b.dataset.estado;
    cargar();
}));

cargar();
</script>
</body>
</html>
