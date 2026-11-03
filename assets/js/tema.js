/* ============================================================
   assets/js/tema.js
   - cambiarTema(): guarda el tema (sesión + BD) y recarga la página.
   - Tema claro: convierte los colores fijos de las vistas (pensadas en oscuro):
       · fondos oscuros → claros (los botones oscuros → índigo con texto blanco)
       · texto claro sobre fondo claro → oscuro; bordes invisibles → gris suave
   - Ambos temas: garantiza contraste legible (WCAG AA: 4.5:1 texto normal,
     3:1 texto grande). Si un texto no llega, se oscurece (claro) o aclara
     (oscuro) manteniendo su tono. Respeta fotos, iconos y velos de modales.
   Procesa también el contenido que se carga después (MutationObserver).
   ============================================================ */
(function () {
    const html = document.documentElement;

    window.cambiarTema = function (tema) {
        const body = new URLSearchParams({ tema: tema });
        fetch('index.php?c=login&a=SetTema', { method: 'POST', body: body, credentials: 'same-origin' })
            .finally(() => window.location.reload());
    };

    const CLARO = html.classList.contains('tema-claro');

    // Nunca se tocan
    const MEDIOS = 'svg, img, video, canvas, iframe, picture, ion-icon, .leaflet-container, .gm-style';
    // Zonas con estilo propio en tema-claro.css (no se aclaran sus fondos)
    const PROPIOS = '.barra-lateral, .menu, #bloqOverlay, #rechazadoOverlay, #pendienteOverlay, ' +
                    '#pendienteVencidoOverlay, #soporteModal, #graciaToast, .swal2-container';
    const BOTONES = 'button, .btn, [class*="btn"], [role="button"], input[type="submit"], input[type="button"]';
    // Fondos de modales a pantalla completa (se atenúan, no se aclaran)
    const VELOS = '.modal-overlay, .modal-backdrop, .swal2-backdrop-show, [class*="overlay"]';
    const esVeloModal = (el, cs) => cs.position === 'fixed' && el.matches(VELOS);

    const FONDO_PAGINA = '#f3f5fa', SUPERFICIE = '#ffffff', SUPERFICIE_2 = '#eef1f7';
    const TEXTO = '#0f172a', TEXTO_SUAVE = '#556070', BORDE = 'rgba(15, 23, 42, .12)';
    const BOTON = '#4f46e5';

    /* ── Color ─────────────────────────────────────────────────── */
    function parse(c) {
        const m = c && c.match(/rgba?\(([^)]+)\)/);
        if (!m) return null;
        const p = m[1].split(/[\s,\/]+/).filter(Boolean).map(parseFloat);
        return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 };
    }
    function lum({ r, g, b }) {
        const f = v => { v /= 255; return v <= .03928 ? v / 12.92 : Math.pow((v + .055) / 1.055, 2.4); };
        return .2126 * f(r) + .7152 * f(g) + .0722 * f(b);
    }
    function sat({ r, g, b }) {
        const mx = Math.max(r, g, b), mn = Math.min(r, g, b);
        return mx === 0 ? 0 : (mx - mn) / mx;
    }
    const ratio = (a, b) => (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
    const mezclar = (c, d, t) => ({ r: c.r + (d.r - c.r) * t, g: c.g + (d.g - c.g) * t, b: c.b + (d.b - c.b) * t, a: 1 });
    const css = c => `rgb(${Math.round(c.r)}, ${Math.round(c.g)}, ${Math.round(c.b)})`;
    const NEGRO = { r: 15, g: 23, b: 42, a: 1 }, BLANCO = { r: 255, g: 255, b: 255, a: 1 };

    // Casi negro de cualquier tono (#0f172a, #1a1a2e…) o gris/azul oscuro poco saturado
    const oscuroNeutro = c => c && c.a > .35 && (lum(c) < .03 || (lum(c) < .09 && sat(c) < .62));
    const set = (el, prop, val) => el.style.setProperty(prop, val, 'important');

    // Fondo real detrás de un elemento. {foto:true} si el texto va sobre una imagen.
    // .origen = elemento que aporta el fondo opaco (para poder oscurecerlo si hace falta)
    function fondoEfectivo(el) {
        let capas = [], origen = null, degradado = false;
        for (let n = el; n && n.nodeType === 1; n = n.parentElement) {
            const cs = getComputedStyle(n);
            const img = cs.backgroundImage || '';
            if (img.includes('url(')) return { foto: true };
            const c = parse(cs.backgroundColor);
            if (img.includes('gradient')) { // promedio de los tramos opacos del degradado
                const s = (img.match(/rgba?\([^)]+\)/g) || []).map(parse).filter(x => x && x.a >= .5);
                if (s.length) {
                    capas.push({ r: s.reduce((t, x) => t + x.r, 0) / s.length, g: s.reduce((t, x) => t + x.g, 0) / s.length,
                                 b: s.reduce((t, x) => t + x.b, 0) / s.length, a: 1 });
                    origen = n; degradado = true;
                    break;
                }
            }
            if (c && c.a > 0) {
                capas.push(c);
                if (c.a >= .95) { origen = n; break; }
            }
        }
        // Componer capas translúcidas sobre el fondo de página
        let base = CLARO ? { r: 243, g: 245, b: 250, a: 1 } : { r: 17, g: 24, b: 39, a: 1 };
        for (let i = capas.length - 1; i >= 0; i--) base = mezclar(base, capas[i], capas[i].a);
        base.origen = origen; base.degradado = degradado;
        return base;
    }

    // Insignia/botón de color con texto claro: se oscurece el FONDO (se mantiene texto blanco)
    function oscurecerFondo(n, texto, objetivo) {
        if (!n || n.__tcf || n === document.body || n.tagName === 'MAIN') return false;
        const r = n.getBoundingClientRect();
        if (r.width * r.height > window.innerWidth * window.innerHeight * .2) return false; // solo piezas pequeñas
        const cs = getComputedStyle(n);
        const prom = fondoEfectivo(n);
        if (prom.foto || sat(prom) < .2) return false;                                     // solo fondos de color
        let t = 0;
        while (t < .7 && ratio(lum(texto), lum(mezclar(prom, NEGRO, t))) < objetivo) t += .05;
        if (t === 0) return true;
        n.__tcf = true;
        if (prom.degradado) {
            set(n, 'background-image', cs.backgroundImage.replace(/rgba?\([^)]+\)/g, m => {
                const c = parse(m); return c ? `rgba(${css(mezclar(c, NEGRO, t)).slice(4, -1)}, ${c.a})` : m;
            }));
        } else {
            set(n, 'background-color', css(mezclar(parse(cs.backgroundColor), NEGRO, t)));
        }
        return true;
    }

    /* ── 1) Tema claro: aclarar la vista ───────────────────────── */
    function aclarar(el, cs) {
        const bg = parse(cs.backgroundColor);
        const esBoton = el.matches(BOTONES);

        if (esVeloModal(el, cs)) {
            if (bg && bg.a > .3) set(el, 'background-color', 'rgba(15, 23, 42, .45)');
        } else if (oscuroNeutro(bg) && esBoton) {
            set(el, 'background-color', BOTON);           // botón oscuro → índigo, texto blanco
            set(el, 'color', '#ffffff');
        } else if (oscuroNeutro(bg)) {
            const esPagina = el === document.body || el.tagName === 'MAIN' || lum(bg) < .012 && el.offsetWidth > window.innerWidth * .6;
            set(el, 'background-color', esPagina ? FONDO_PAGINA : (lum(bg) > .03 ? SUPERFICIE_2 : SUPERFICIE));
        } else if (bg && bg.a > 0 && bg.a <= .35 && lum(bg) > .7 && !esBoton
                   && el.parentElement && !fondoEfectivo(el.parentElement).foto && lum(fondoEfectivo(el.parentElement)) > .45) {
            // velos blancos translúcidos sobre fondo claro → gris suave visible (no sobre fotos/cabeceras de color)
            set(el, 'background-color', 'rgba(15, 23, 42, ' + Math.min(.08, bg.a + .03).toFixed(3) + ')');
        }

        // Degradados: solo se aclaran los tramos muy oscuros; los velos sobre fotos se respetan
        const bgi = cs.backgroundImage;
        const esVeloFoto = bgi && /rgba\([^)]*,\s*0(\.0\d*)?\)|transparent/.test(bgi);
        if (bgi && bgi.includes('gradient') && !bgi.includes('url(') && !esVeloFoto && !esVeloModal(el, cs)) {
            let cambio = false;
            const nuevo = bgi.replace(/rgba?\([^)]+\)/g, m => {
                const c = parse(m);
                if (c && c.a >= .3 && lum(c) < .05) { cambio = true; return esBoton ? 'rgb(79, 70, 229)' : 'rgb(238, 241, 247)'; }
                return m;
            });
            if (cambio) set(el, 'background-image', nuevo);
        }

        // Bordes claros (invisibles sobre blanco)
        const bc = parse(cs.borderTopColor);
        if (bc && bc.a > 0 && lum(bc) > .6 && parseFloat(cs.borderTopWidth) > 0) set(el, 'border-color', BORDE);

        // Texto claro neutro sobre fondo claro → oscuro (mantiene jerarquía normal / atenuado)
        const col = parse(cs.color);
        if (col && lum(col) > .55 && sat(col) < .35) {
            const f = fondoEfectivo(el);
            if (!f.foto && lum(f) > .45 && tieneTexto(el)) { set(el, 'color', col.a < .8 ? TEXTO_SUAVE : TEXTO); el.__tcColor = true; }
        }
        // Títulos con texto degradado (background-clip:text) sobre fondo claro
        if ((cs.webkitTextFillColor || '').includes('0, 0, 0, 0') && cs.backgroundClip === 'text') {
            const f = fondoEfectivo(el.parentElement || el);
            if (!f.foto && lum(f) > .45) set(el, '-webkit-text-fill-color', TEXTO);
        }

        // Sombras negras pesadas → suaves
        if (cs.boxShadow && cs.boxShadow !== 'none' && /rgba\(0, 0, 0, 0\.[3-9]/.test(cs.boxShadow)) {
            set(el, 'box-shadow', '0 4px 18px rgba(15, 23, 42, .08)');
        }
    }

    /* ── 2) Ambos temas: contraste mínimo legible ──────────────── */
    // Solo texto legible: letras o números (no íconos sueltos como ★ ✓ → ni emojis)
    function tieneTexto(el) {
        for (const n of el.childNodes) if (n.nodeType === 3 && /[\p{L}\p{N}]/u.test(n.textContent)) return true;
        return el.matches('input:not([type=checkbox]):not([type=radio]):not([type=range]), textarea, select');
    }

    function asegurarContraste(el) {
        if (!tieneTexto(el)) return;
        const cs = getComputedStyle(el);
        if (cs.visibility === 'hidden') return;
        if ((cs.webkitTextFillColor || '').includes('0, 0, 0, 0')) return; // texto degradado: decorativo
        const col = parse(cs.color);
        if (!col) return;
        const fondo = fondoEfectivo(el);
        if (fondo.foto) return;                                            // texto sobre foto: lo resuelve su velo

        const efectivo = col.a < 1 ? mezclar(fondo, col, col.a) : col;
        const grande = parseFloat(cs.fontSize) >= 24 || (parseFloat(cs.fontSize) >= 18.6 && parseInt(cs.fontWeight, 10) >= 700);
        const objetivo = (grande ? 3 : 4.5) + .3;   // margen sobre el mínimo WCAG AA
        const lf = lum(fondo);
        if (ratio(lum(efectivo), lf) >= objetivo) return;

        // Texto claro sobre insignia/botón de color → oscurecer el fondo, no el texto
        if (lum(efectivo) > .5 && sat(fondo) >= .2 && oscurecerFondo(fondo.origen, efectivo, objetivo)) return;

        // Oscurecer sobre fondo claro / aclarar sobre fondo oscuro, conservando el tono
        const destino = lf > .18 ? NEGRO : BLANCO;
        for (let t = .1; t <= 1.001; t += .1) {
            const c = mezclar(efectivo, destino, t);
            if (ratio(lum(c), lf) >= objetivo || t >= 1) { set(el, 'color', css(c)); el.__tcColor = true; break; }
        }
    }

    /* ── Recorrido ─────────────────────────────────────────────── */
    function procesar(el) {
        if (el.nodeType !== 1 || el.__tc) return;
        el.__tc = true;
        if (el.closest(MEDIOS)) return;
        if (CLARO && !el.closest(PROPIOS)) aclarar(el, getComputedStyle(el));
    }

    // Recorre todo el documento; lo ya revisado se salta (marcas __tc / __tcc), así que repetirlo es barato.
    function recorrer() {
        const todos = [document.body, ...document.body.querySelectorAll('*')]; // padres antes que hijos
        todos.forEach(el => { try { procesar(el); } catch (e) { /* un elemento raro no corta el resto */ } });
        todos.forEach(el => {
            if (el.__tcc || el.closest(MEDIOS)) return;
            el.__tcc = true;
            try { asegurarContraste(el); } catch (e) { }
        });
    }

    let programado = false;
    function programar() {
        if (programado) return;
        programado = true;
        // setTimeout (no requestAnimationFrame): también corre en pestañas en segundo plano
        setTimeout(() => { programado = false; recorrer(); }, 60);
    }

    function iniciar() {
        recorrer();
        // Contenido nuevo (AJAX, plantillas, modales); si un elemento ya revisado recibe texto (p. ej. un contador que se llena
        // por JS después de cargar), se vuelve a evaluar: estando vacío no se le pudo ajustar el color
        new MutationObserver(muts => {
            muts.forEach(m => {
                const el = m.target.nodeType === 1 ? m.target : m.target.parentElement;
                if (el && el.__tc) { el.__tc = false; el.__tcc = false; }
            });
            programar();
        }).observe(document.body, { childList: true, characterData: true, subtree: true });

        // Cambios de estado (clase activa, seleccionada…): quitar el ajuste de color y reevaluar,
        // para no bloquear colores que dependen del estado (p. ej. estrellas de calificación)
        new MutationObserver(muts => {
            let hubo = false;
            muts.forEach(m => {
                const el = m.target;
                if (!el || el.nodeType !== 1) return;
                [el, ...el.querySelectorAll('*')].forEach(x => {
                    if (x.__tcColor) { x.style.removeProperty('color'); x.__tcColor = false; }
                    x.__tcc = false;
                });
                hubo = true;
            });
            // esperar a que terminen las transiciones CSS del cambio de estado antes de medir colores
            if (hubo) setTimeout(programar, 350);
        }).observe(document.body, { attributes: true, attributeFilter: ['class'], subtree: true });
        window.addEventListener('load', programar);
        let repasos = 0;
        const t = setInterval(() => { programar(); if (++repasos >= 8) clearInterval(t); }, 1500);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();

    /* ── Gráficos (Chart.js) ───────────────────────────────────────
       Se dibujan en <canvas>, fuera del alcance del ajuste de arriba: en modo claro,
       los textos y líneas de grilla claros de sus opciones se pasan a tonos oscuros. */
    if (!CLARO) return;
    const lienzo = document.createElement('canvas').getContext('2d');
    function claroRGBA(v) {
        if (typeof v !== 'string') return null;
        lienzo.fillStyle = '#000'; lienzo.fillStyle = v;
        const f = lienzo.fillStyle;   // '#rrggbb' o 'rgba(r, g, b, a)'
        let r, g, b, a = 1;
        if (f[0] === '#') { r = parseInt(f.substr(1, 2), 16); g = parseInt(f.substr(3, 2), 16); b = parseInt(f.substr(5, 2), 16); }
        else { const m = f.match(/[\d.]+/g); if (!m) return null; [r, g, b] = m.map(Number); a = m[3] !== undefined ? +m[3] : 1; }
        return (r + g + b) / 3 > 200 ? a : null;   // devuelve la opacidad si es un color claro
    }
    function oscurecerOpciones(o, clave) {
        if (!o || typeof o !== 'object') return;
        Object.keys(o).forEach(k => {
            const v = o[k];
            if (v && typeof v === 'object' && !Array.isArray(v) && !(v instanceof CanvasGradient)) { oscurecerOpciones(v, k); return; }
            if (k !== 'color' && k !== 'borderColor' && k !== 'tickColor') return;
            const a = claroRGBA(v);
            if (a === null) return;
            // grillas y bordes: línea suave; textos: gris oscuro legible
            o[k] = (clave === 'grid' || clave === 'border') ? 'rgba(15,23,42,.08)' : (a < .45 ? '#64748b' : '#334155');
        });
    }
    const pluginTema = {
        id: 'temaClaro',
        beforeInit(chart) { oscurecerOpciones(chart.config.options); },
        beforeUpdate(chart) { oscurecerOpciones(chart.config.options); }
    };
    let intentos = 0;
    (function registrarGraficos() {
        if (window.Chart && Chart.register) {
            Chart.defaults.color = '#334155';
            Chart.defaults.borderColor = 'rgba(15,23,42,.08)';
            Chart.register(pluginTema);
            Object.values(Chart.instances || {}).forEach(c => { oscurecerOpciones(c.config.options); c.update('none'); });
        } else if (++intentos < 60) {
            setTimeout(registrarGraficos, 250);
        }
    })();
})();
