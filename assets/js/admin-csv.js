/* ============================================================
   assets/js/admin-csv.js
   Agrega "CSV" (exportar todo) e "Importar CSV" junto al botón Excel de cada pestaña
   del administrador. La importación primero valida (simulación) y muestra el resultado;
   solo guarda si el administrador confirma.
   ============================================================ */
(function () {
    const c = (new URLSearchParams(location.search).get('c') || '').toLowerCase();
    // pestaña → tablas que puede exportar/importar
    const MAPA = {
        admincatalogoparametros: [['parametros', 'Parámetros']],
        admincontextorelacion:   [['contexto', 'Contextos']],
        adminrecomendaciones:    [['recomendaciones', 'Recomendaciones']],
        admincategorias:         [['categorias', 'Categorías']],
        adminatracciones:        [['atracciones', 'Atracciones'], ['traducciones', 'Traducciones (pt/fr/de/en)']],
        adminbacktesting:        [['resenas', 'Reseñas', true]],
    };
    const css = document.createElement('style');
    css.textContent = `
        .btn-csv { background: rgba(14,165,233,.1); border: 1px solid rgba(14,165,233,.3); color: #0ea5e9; }
        .btn-csv-imp { background: rgba(168,85,247,.1); border: 1px solid rgba(168,85,247,.3); color: #a855f7; }
        .csv-menu { position: relative; display: inline-block; }
        .csv-lista { position: absolute; z-index: 50; top: calc(100% + 4px); right: 0; min-width: 220px; padding: 6px;
            background: var(--card, #1c1c32); border: 1px solid var(--border2, rgba(255,255,255,.1)); border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,.25); display: none; }
        .csv-menu.abierto .csv-lista { display: block; }
        .csv-lista button { display: block; width: 100%; text-align: left; background: none; border: 0; padding: 8px 10px;
            border-radius: 7px; color: inherit; font-size: .8rem; cursor: pointer; }
        .csv-lista button:hover { background: rgba(127,127,127,.12); }
        .csv-res { text-align: left; font-size: .85rem; }
        .csv-res .csv-n { display: flex; gap: 10px; justify-content: center; margin: 8px 0 12px; flex-wrap: wrap; }
        .csv-res .csv-n b { display: block; font-size: 1.4rem; }
        .csv-res .csv-n div { padding: 8px 14px; border-radius: 10px; background: rgba(127,127,127,.1); text-align: center; }
        .csv-res ul { max-height: 180px; overflow: auto; margin: 6px 0 0; padding-left: 18px; font-size: .78rem; }
        .csv-ayuda { font-size: .78rem; opacity: .8; text-align: left; margin-top: 8px; line-height: 1.45; }
        .csv-file { width: 100%; padding: 10px; border: 1.5px dashed rgba(127,127,127,.4); border-radius: 10px; }
    `;
    document.head.appendChild(css);

    const oscuro = !document.documentElement.classList.contains('tema-claro');
    const SW = oscuro ? { background: '#1c1c32', color: '#f0f0f5' } : {};
    const esc = s => String(s).replace(/[&<>"]/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch]));

    /* ── Un modelo MR completo en JSON (por fila en la tabla de Modelos MR) ── */
    function resumenModelo(d) {
        const err = d.errores || [];
        let h = `<div class="csv-res">
                 <ul style="max-height:none">${d.cambios.map(c => `<li>${esc(c)}</li>`).join('')}</ul>`;
        if (d.avisos.length) h += `<div style="margin-top:8px;color:#f59e0b"><b>Avisos</b><ul>${d.avisos.map(a => `<li>${esc(a)}</li>`).join('')}</ul></div>`;
        if (err.length) h += `<div style="margin-top:8px;color:#ef4444"><b>Errores (no se guardará nada)</b><ul>${err.map(e => `<li>${esc(e)}</li>`).join('')}</ul></div>`;
        return h + '</div>';
    }

    function enviarModelo(id, archivo, simular) {
        const fd = new FormData();
        fd.append('id', id); fd.append('archivo', archivo); fd.append('simular', simular ? '1' : '0');
        return fetch('index.php?c=adminmodelosmr&a=ImportarJson', { method: 'POST', body: fd, credentials: 'same-origin' }).then(r => r.json());
    }

    window.AdminCSV = {
        exportarModelo(id) { window.location.href = 'index.php?c=adminmodelosmr&a=ExportarJson&id=' + id; },
        importarModelo(id, nombre) {
            Swal.fire({
                ...SW,
                title: 'Importar modelo #' + id,
                html: `<div style="font-size:.85rem;margin-bottom:8px">${esc(nombre)}</div>
                       <input type="file" id="csvArchivo" class="csv-file" accept=".json,application/json">
                       <div class="csv-ayuda">
                         • Usa el archivo de <b>Exportar modelo (JSON)</b>: es el modelo completo (datos, alternativas, dataset de entrenamiento, configuración y métricas).<br>
                         • El archivo reemplaza al modelo: lo que no esté en él se quita.<br>
                         • Primero se valida y se muestran los cambios; nada se guarda hasta que confirmes.<br>
                         • Antes de guardar se deja una copia del modelo actual por seguridad.
                       </div>`,
                showCancelButton: true, confirmButtonText: 'Validar archivo', cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e55a2b', showLoaderOnConfirm: true,
                preConfirm: () => {
                    const f = document.getElementById('csvArchivo').files[0];
                    if (!f) { Swal.showValidationMessage('Elige un archivo .json'); return false; }
                    return enviarModelo(id, f, true).then(d => {
                        if (!d.success) { Swal.showValidationMessage(d.message || 'No se pudo leer el archivo'); return false; }
                        return { d, f };
                    }).catch(() => { Swal.showValidationMessage('Error de conexión'); return false; });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then(r => {
                if (!r.isConfirmed || !r.value) return;
                const { d, f } = r.value;
                const ok = !d.errores.length;
                Swal.fire({
                    ...SW, width: 640,
                    title: ok ? 'Archivo validado' : 'El archivo tiene errores',
                    html: resumenModelo(d), icon: ok ? (d.avisos.length ? 'warning' : 'success') : 'error',
                    showConfirmButton: ok, showCancelButton: true,
                    confirmButtonText: 'Guardar en el modelo', cancelButtonText: ok ? 'Cancelar' : 'Cerrar',
                    confirmButtonColor: '#e55a2b', showLoaderOnConfirm: true,
                    preConfirm: () => enviarModelo(id, f, false).then(x => {
                        if (!x.success || !x.guardado) { Swal.showValidationMessage(x.message || 'No se guardó'); return false; }
                        return x;
                    }),
                    allowOutsideClick: () => !Swal.isLoading()
                }).then(r2 => {
                    if (!r2.isConfirmed || !r2.value) return;
                    Swal.fire({ ...SW, title: 'Modelo actualizado', icon: 'success', confirmButtonColor: '#e55a2b' })
                        .then(() => location.reload());
                });
            });
        }
    };

    const tablas = MAPA[c];
    if (!tablas) return;



    function boton(clase, icono, texto, alClic) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'btn-export ' + clase;
        b.innerHTML = `<i class="fas ${icono}"></i> ${texto}`;
        b.addEventListener('click', alClic);
        return b;
    }
    // Con una sola tabla, botón directo; con varias, menú desplegable
    function control(clase, icono, texto, accion, lista) {
        if (lista.length === 1) return boton(clase, icono, texto, () => accion(lista[0]));
        const cont = document.createElement('div');
        cont.className = 'csv-menu';
        cont.appendChild(boton(clase, icono, texto + ' <i class="fas fa-caret-down"></i>', e => {
            e.stopPropagation();
            document.querySelectorAll('.csv-menu.abierto').forEach(m => m !== cont && m.classList.remove('abierto'));
            cont.classList.toggle('abierto');
        }));
        const ul = document.createElement('div');
        ul.className = 'csv-lista';
        lista.forEach(t => {
            const it = document.createElement('button');
            it.type = 'button';
            it.textContent = t[1];
            it.addEventListener('click', () => { cont.classList.remove('abierto'); accion(t); });
            ul.appendChild(it);
        });
        cont.appendChild(ul);
        return cont;
    }
    document.addEventListener('click', () => document.querySelectorAll('.csv-menu.abierto').forEach(m => m.classList.remove('abierto')));

    function exportar(t) {
        window.location.href = 'index.php?c=admincsv&a=Exportar&e=' + encodeURIComponent(t[0]);
    }

    function enviar(t, archivo, simular) {
        const fd = new FormData();
        fd.append('e', t[0]);
        fd.append('archivo', archivo);
        fd.append('simular', simular ? '1' : '0');
        return fetch('index.php?c=admincsv&a=Importar', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(r => r.json());
    }

    function resumen(d, simulado) {
        const err = d.errores || [];
        let h = `<div class="csv-res"><div class="csv-n">
            <div><b>${d.total}</b>filas</div>
            <div style="color:#22c55e"><b>${d.insertadas}</b>${simulado ? 'se crearán' : 'creadas'}</div>
            <div style="color:#0ea5e9"><b>${d.actualizadas}</b>${simulado ? 'se actualizarán' : 'actualizadas'}</div>
            <div style="color:${err.length ? '#ef4444' : 'inherit'}"><b>${err.length}</b>con error</div></div>`;
        if (d.ignoradas && d.ignoradas.length)
            h += `<div>Columnas ignoradas (no existen en la tabla): <code>${esc(d.ignoradas.join(', '))}</code></div>`;
        if (err.length)
            h += `<ul>${err.map(e => `<li>${e[0] ? 'Línea ' + e[0] + ': ' : ''}${esc(e[1])}</li>`).join('')}</ul>`;
        return h + '</div>';
    }

    function importar(t) {
        if (!window.Swal) { alert('No se pudo abrir el importador.'); return; }
        Swal.fire({
            ...SW,
            title: 'Importar ' + t[1],
            html: `<input type="file" id="csvArchivo" class="csv-file" accept=".csv,text/csv">
                   <div class="csv-ayuda">
                     • Usa los mismos encabezados que el CSV exportado (exporta primero para tener la plantilla).<br>
                     • Filas con <b>id</b> existente se <b>actualizan</b>; sin id o con id nuevo se <b>crean</b>.<br>
                     • Puedes incluir solo algunas columnas: las demás no se tocan.<br>
                     • Primero se valida el archivo; nada se guarda hasta que confirmes.
                   </div>`,
            showCancelButton: true,
            confirmButtonText: 'Validar archivo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#e55a2b',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const f = document.getElementById('csvArchivo').files[0];
                if (!f) { Swal.showValidationMessage('Elige un archivo .csv'); return false; }
                return enviar(t, f, true).then(d => {
                    if (!d.success) { Swal.showValidationMessage(d.message || 'No se pudo leer el archivo'); return false; }
                    return { d, f };
                }).catch(() => { Swal.showValidationMessage('Error de conexión'); return false; });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then(r => {
            if (!r.isConfirmed || !r.value) return;
            const { d, f } = r.value;
            const cambios = d.insertadas + d.actualizadas;
            Swal.fire({
                ...SW,
                title: cambios ? 'Archivo validado' : 'No hay filas válidas',
                html: resumen(d, true) + (cambios && d.errores.length ? '<p style="font-size:.8rem;margin-top:8px">Las filas con error se omitirán.</p>' : ''),
                icon: cambios ? (d.errores.length ? 'warning' : 'success') : 'error',
                showCancelButton: !!cambios,
                showConfirmButton: !!cambios,
                confirmButtonText: `Guardar ${cambios} fila(s)`,
                cancelButtonText: cambios ? 'Cancelar' : 'Cerrar',
                confirmButtonColor: '#e55a2b',
                showLoaderOnConfirm: true,
                preConfirm: () => enviar(t, f, false).then(x => {
                    if (!x.success) { Swal.showValidationMessage(x.message); return false; }
                    return x;
                }),
                allowOutsideClick: () => !Swal.isLoading()
            }).then(r2 => {
                if (!r2.isConfirmed || !r2.value) return;
                Swal.fire({ ...SW, title: 'Importación completa', html: resumen(r2.value, false), icon: 'success',
                    confirmButtonColor: '#e55a2b' }).then(() => location.reload());
            });
        });
    }

    function montar() {
        const excel = document.querySelector('.btn-excel');
        const ancla = excel || document.querySelector('.bt-header, .page-header, header, h1');
        if (!ancla) return;
        const exp = control('btn-csv', 'fa-file-csv', 'CSV', exportar, tablas);
        const importables = tablas.filter(t => !t[2]);
        const imp = importables.length ? control('btn-csv-imp', 'fa-file-import', 'Importar CSV', importar, importables) : null;
        if (excel) {
            excel.after(exp);
            if (imp) exp.after(imp);
        } else {
            const barra = document.createElement('div');
            barra.style.cssText = 'display:flex;gap:8px;justify-content:flex-end;margin:8px 0';
            barra.append(exp); if (imp) barra.append(imp);
            ancla.after(barra);
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', montar);
    else montar();
})();
