<?php include 'view/menu.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Consentimientos - Etreva Travel</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
:root{
    --bg:#0f1117; --surf:#1c2235; --surf2:#222b42; --border:rgba(255,255,255,.07);
    --text:#e2e8f0; --muted:rgba(255,255,255,.5);
    --amber:#fbbf24; --blue:#38bdf8; --green:#4ade80; --red:#f87171; --violet:#a78bfa; --orange:#fb923c;
}
*{box-sizing:border-box}
html,body{background:var(--bg)!important;color:var(--text)!important;font-family:'DM Sans',sans-serif;min-height:100vh;margin:0}
main{margin-left:280px;min-height:100vh;transition:all .4s}
main.min-main{margin-left:85px}
@media(max-width:768px){main{margin-left:0!important}}

.hero{padding:40px 28px 24px;border-bottom:1px solid var(--border);background:linear-gradient(135deg,#0a0d1a 0%,#0d1526 60%,var(--bg) 100%)}
.hero h1{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;margin:0 0 6px;background:linear-gradient(135deg,#fff,#a7f3d0);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{color:var(--muted);margin:0;font-size:.9rem;max-width:820px}

.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;padding:20px 28px 0;max-width:1200px}
.kpi{background:var(--surf);border:1px solid var(--border);border-radius:16px;padding:14px 16px}
.kpi b{display:block;font-family:'Syne',sans-serif;font-size:1.6rem;line-height:1.1}
.kpi span{color:var(--muted);font-size:.78rem}

.barra{display:flex;gap:8px;padding:20px 28px 0;flex-wrap:wrap;align-items:center;max-width:1200px}
.filtro{background:var(--surf);border:1px solid var(--border);color:var(--muted);border-radius:50px;padding:7px 16px;font-size:.8rem;font-weight:700;cursor:pointer}
.filtro.activo{background:var(--green);border-color:var(--green);color:#0f172a}
.buscar{flex:1;min-width:200px;background:var(--surf);border:1px solid var(--border);color:var(--text);border-radius:50px;padding:8px 16px;font-size:.85rem;outline:none}
.btn-csv{background:var(--surf2);border:1px solid var(--border);color:var(--text);border-radius:50px;padding:8px 16px;font-size:.8rem;font-weight:700;text-decoration:none;white-space:nowrap}
.btn-csv:hover{border-color:var(--green)}

.tabla-wrap{padding:16px 28px 40px;max-width:1200px;overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:.82rem;min-width:860px}
th{text-align:left;color:var(--muted);font-weight:700;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;padding:10px 10px;border-bottom:1px solid var(--border)}
td{padding:11px 10px;border-bottom:1px solid var(--border);vertical-align:top}
tr:hover td{background:rgba(255,255,255,.02)}
td small{display:block;color:var(--muted);font-size:.74rem}
.badge{display:inline-block;border-radius:20px;padding:3px 10px;font-size:.7rem;font-weight:800;white-space:nowrap}
.b-reglamento{background:rgba(74,222,128,.14);color:var(--green)}
.b-riesgo{background:rgba(251,146,60,.15);color:var(--orange)}
.b-menor{background:rgba(248,113,113,.15);color:var(--red);margin-left:4px}
.chips{display:flex;gap:4px;flex-wrap:wrap}
.chip{font-size:.68rem;border-radius:6px;padding:2px 7px;background:var(--surf2);color:var(--muted)}
.chip.si{background:rgba(74,222,128,.12);color:var(--green)}
.chip.no{background:rgba(248,113,113,.12);color:var(--red)}
.vacio{color:var(--muted);text-align:center;padding:50px 0}
.nota{color:var(--muted);font-size:.76rem;padding:0 28px 30px;max-width:1200px}
</style>
</head>
<body>
<main>
    <div class="hero">
        <h1>Consentimientos</h1>
        <p>Constancia de lo que cada turista aceptó: el Reglamento de uso y privacidad (Constitución Política del Estado Plurinacional de Bolivia, art. 21 y 130; Ley N.º 164 y DS N.º 1793 de Bolivia;
           Ley N.º 548 de Bolivia) y los avisos de riesgo de trekking, montañismo y downhill (Ley N.º 453 y Ley N.º 292 de Bolivia), con fecha, hora, IP y navegador.</p>
    </div>

    <div class="kpis" id="kpis"></div>

    <div class="barra">
        <button class="filtro activo" data-tipo="">Todos</button>
        <button class="filtro" data-tipo="reglamento">Reglamento</button>
        <button class="filtro" data-tipo="riesgo">Avisos de riesgo</button>
        <input type="search" class="buscar" id="buscar" placeholder="Buscar por nombre o correo…">
        <a class="btn-csv" id="btnCsv" href="index.php?c=adminconsentimientos&a=Exportar">⬇ Exportar CSV</a>
    </div>

    <div class="tabla-wrap">
        <table>
            <thead><tr><th>Fecha y hora</th><th>Turista</th><th>Tipo</th><th>Qué aceptó</th><th>Versión</th><th>Origen</th></tr></thead>
            <tbody id="filas"><tr><td colspan="6" class="vacio">Cargando…</td></tr></tbody>
        </table>
    </div>
    <p class="nota">Cada aceptación se guarda como un registro nuevo (nunca se sobrescribe) y la última de cada tipo también queda en
       <code>configuracion_json → consentimientos</code> del usuario.</p>
</main>

<script>
(function () {
    const ACT = { trekking: 'Trekking', montanismo: 'Montañismo', downhill: 'Downhill' };
    let tipo = '', texto = '', t = null;
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));
    const chip = (lbl, v) => v === null || v === undefined ? '' : `<span class="chip ${v ? 'si' : 'no'}">${v ? '✓' : '✗'} ${lbl}</span>`;
    const fecha = f => new Date(f).toLocaleString('es-BO', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
    const qs = () => `&tipo=${encodeURIComponent(tipo)}&texto=${encodeURIComponent(texto)}`;

    function cargar() {
        document.getElementById('btnCsv').href = 'index.php?c=adminconsentimientos&a=Exportar' + qs();
        fetch('index.php?c=adminconsentimientos&a=Listar' + qs(), { credentials: 'same-origin' })
            .then(r => r.json())
            .then(resp => {
                if (!resp.success) throw new Error(resp.message);
                const r = resp.resumen;
                document.getElementById('kpis').innerHTML = `
                    <div class="kpi"><b>${r.aceptaron} / ${r.clientes}</b><span>Turistas que aceptaron el reglamento ${esc(r.version)}</span></div>
                    <div class="kpi"><b style="color:var(--amber)">${r.pendientes}</b><span>Pendientes (se les pedirá al ingresar)</span></div>
                    <div class="kpi"><b style="color:var(--orange)">${(r.riesgo.trekking||0)} · ${(r.riesgo.montanismo||0)} · ${(r.riesgo.downhill||0)}</b><span>Avisos de riesgo: trekking · montañismo · downhill</span></div>
                    <div class="kpi"><b style="color:var(--red)">${r.menores}</b><span>Menores de edad con autorización de tutor</span></div>`;
                const filas = resp.data;
                document.getElementById('filas').innerHTML = !filas.length
                    ? '<tr><td colspan="6" class="vacio">Sin registros</td></tr>'
                    : filas.map(f => `<tr>
                        <td>${fecha(f.aceptado_en)}<small>#${f.id}</small></td>
                        <td><b>${esc(f.nombre)} ${esc(f.apellido)}</b><small>${esc(f.email)}</small></td>
                        <td>${f.tipo === 'reglamento' ? '<span class="badge b-reglamento">Reglamento</span>' : '<span class="badge b-riesgo">Riesgo · ' + esc(ACT[f.categoria] || f.categoria) + '</span>'}${f.es_menor ? '<span class="badge b-menor">Menor</span>' : ''}</td>
                        <td><div class="chips">${f.tipo === 'reglamento'
                            ? chip('Términos', f.acepta_terminos) + chip('Datos', f.acepta_datos) + chip('Riesgos', f.acepta_riesgos) + chip('Perfil público', f.acepta_perfil_publico) + (f.es_menor ? chip('Tutor', f.autorizacion_tutor) : '')
                            : chip('Bajo su propio riesgo', f.acepta_riesgos) + (f.atraccion ? `<span class="chip">${esc(f.atraccion)}</span>` : '<span class="chip">Desde el buscador</span>')}</div></td>
                        <td>${esc(f.version)}</td>
                        <td>${esc(f.ip)}<small title="${esc(f.navegador)}">${esc((f.navegador || '').slice(0, 42))}</small></td>
                    </tr>`).join('');
            })
            .catch(e => { document.getElementById('filas').innerHTML = `<tr><td colspan="6" class="vacio">${esc(e.message || 'Error al cargar')}</td></tr>`; });
    }

    document.querySelectorAll('.filtro').forEach(b => b.addEventListener('click', () => {
        document.querySelectorAll('.filtro').forEach(x => x.classList.remove('activo'));
        b.classList.add('activo'); tipo = b.dataset.tipo; cargar();
    }));
    document.getElementById('buscar').addEventListener('input', e => { clearTimeout(t); t = setTimeout(() => { texto = e.target.value.trim(); cargar(); }, 300); });
    cargar();
})();
</script>
</body>
</html>
