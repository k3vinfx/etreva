<div class="modal fade register-modal" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Crear nueva cuenta en Etreva Travel</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Solo visible en el registro con Google -->
                <div id="googleRegistroAviso" class="alert alert-info d-none d-flex align-items-center gap-2 py-2" style="font-size:.9rem;">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20" alt="Google">
                    <span id="googleRegistroTexto"></span>
                </div>
                <form id="frm-register" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nombre</label>
                            <input type="text" class="form-control" name="nombre" id="reg_nombre" placeholder="Ej. Juan Carlos">
                            <div class="text-danger small mt-1 error-msg" id="err_nombre"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Apellido</label>
                            <input type="text" class="form-control" name="apellido" id="reg_apellido" placeholder="Ej. Pérez">
                            <div class="text-danger small mt-1 error-msg" id="err_apellido"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" id="reg_email" placeholder="viajero@ejemplo.com">
                            <div class="text-danger small mt-1 error-msg" id="err_email"></div>
                        </div>
                       <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" id="reg_telefono" placeholder="Ej. +591 70000000">
                            <div class="text-danger small mt-1 error-msg" id="err_telefono"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nacionalidad</label>
                            <select class="form-select" name="nacionalidad" id="select-nacionalidad">
                                <option value="" selected disabled>Cargando países...</option>
                            </select>
                            <div class="text-danger small mt-1 error-msg" id="err_nacionalidad"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" name="fecha_nacimiento" id="reg_fecha_nacimiento">
                            <div class="text-danger small mt-1 error-msg" id="err_fecha"></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-bold">Género</label>
                            <select class="form-select" name="genero" id="reg_genero"> <option value="" selected disabled>Seleccione Género</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <div class="text-danger small mt-1 error-msg" id="err_genero"></div> 
                        </div>
                        <div class="col-md-6 mb-3 campo-password">
                            <label class="form-label small fw-bold">Contraseña</label>
                            <input type="password" class="form-control" name="password_hash" id="password_hash">
                            <small id="password_strength" class="fw-bold"></small>
                            <div class="text-danger small mt-1 error-msg" id="err_pass"></div>
                        </div>
                        <div class="col-md-6 mb-3 campo-password">
                            <label class="form-label small fw-bold">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" placeholder="Repite tu contraseña">
                            <div class="text-danger small mt-1 error-msg" id="err_conf"></div>
                        </div>
                    </div>

                    <input type="hidden" name="rol" value="1"> 
                    <input type="hidden" name="estado" value="activo">
               
                    <button type="button" class="btn btn-success w-100 py-2 mt-3 fw-bold" onclick="registrarUsuario()">
                        REGISTRARSE COMO VIAJERO
                    </button>
                </form>
            </div>
        </div>
    </div>
</div><script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let reglasValidacion = {}; 

/* ===========================
   VALIDACIÓN DE EDAD (16 - 100 años)
=========================== */
function validarEdad() {
    const inputFecha = $('#reg_fecha_nacimiento');
    const fechaNacimiento = inputFecha.val();
    const errorDiv = '#err_fecha';

    if (!fechaNacimiento) {
        mostrarError(inputFecha, errorDiv, "La fecha es obligatoria");
        return false;
    }

    const hoy = new Date();
    const cumpleanos = new Date(fechaNacimiento);
    
    // Calcular edad
    let edad = hoy.getFullYear() - cumpleanos.getFullYear();
    const mes = hoy.getMonth() - cumpleanos.getMonth();
    
    // Ajuste si no ha cumplido años este año aún
    if (mes < 0 || (mes === 0 && hoy.getDate() < cumpleanos.getDate())) {
        edad--;
    }

    if (edad < 10) {
        mostrarError(inputFecha, errorDiv, `Debes ser mayor de 10 años (Tienes ${edad})`);
        return false;
    }

    if (edad > 100) {
        mostrarError(inputFecha, errorDiv, "La edad no puede ser mayor a 100 años");
        return false;
    }

    limpiarError(inputFecha, errorDiv);
    return true;
}

/* ===========================
   EVALUAR FUERZA DE CONTRASEÑA
=========================== */
function evaluarPasswordStrength(password) {

    let fuerza = 0;

    if (password.length >= 8) fuerza++;
    if (/[A-Z]/.test(password)) fuerza++;
    if (/[a-z]/.test(password)) fuerza++;
    if (/\d/.test(password)) fuerza++;
    if (/[@$!%*?&.#_-]/.test(password)) fuerza++;

    const strengthText = $('#password_strength');

    if (fuerza <= 2) {
        strengthText.text('Seguridad: Débil').css('color', 'red');
    } else if (fuerza === 3 || fuerza === 4) {
        strengthText.text('Seguridad: Media').css('color', 'orange');
    } else {
        strengthText.text('Seguridad: Fuerte').css('color', 'green');
    }
}

/* ===========================
   CARGAR REGLAS DESDE JSON
=========================== */
function cargarReglas() {
    return $.getJSON('assets/js/regex.json')
        .done(function(data) {
            reglasValidacion = {
                letras: new RegExp(data.letras),
                email: new RegExp(data.email),
                password: new RegExp(data.password),
                telefono: new RegExp(data.telefono)
            };
        })
        .fail(function() {
            console.error("No se pudo cargar regex.json");
        });
}

/* ===========================
   CARGAR PAISES
=========================== */
function cargarPaises() {
    const lang = sessionStorage.getItem('etreva_lang') || 'es';

    // Limpiar caché si tiene pocos países (era el backup viejo)
    if (window._paisesData && window._paisesData.length < 50) {
        window._paisesData = null;
    }

    if (window._paisesData) {
        llenarSelect(window._paisesData, lang);
        return;
    }

    // Detectar base automáticamente
    const base = window.location.pathname.split('/').slice(0, 2).join('/');
    const rutaJson = base + '/assets/js/paises.json';

    console.log('🌍 Intentando cargar:', rutaJson);

    $.getJSON(rutaJson)
        .done(function(data) {
            console.log('✅ Países cargados:', data.length);
            window._paisesData = data;
            llenarSelect(data, lang);
        })
        .fail(function(jqXHR) {
            console.error('❌ Falló con ruta:', rutaJson, '| Status:', jqXHR.status);

            // Intento 2 — ruta relativa simple
            $.getJSON('assets/js/paises.json')
                .done(function(data) {
                    console.log('✅ Cargado con ruta relativa:', data.length);
                    window._paisesData = data;
                    llenarSelect(data, lang);
                })
                .fail(function() {
                    // Intento 3 — ruta desde raíz absoluta
                    $.getJSON('/etreva/assets/js/paises.json')
                        .done(function(data) {
                            console.log('✅ Cargado con ruta absoluta:', data.length);
                            window._paisesData = data;
                            llenarSelect(data, lang);
                        })
                        .fail(function(xhr2) {
                            console.error('❌ Todos los intentos fallaron. Status:', xhr2.status);
                        });
                });
        });
}


function llenarSelect(paises, lang) {
    // Si no se pasa lang, leerlo del sessionStorage
    if (!lang) lang = sessionStorage.getItem('etreva_lang') || 'es';

    const select = $('#select-nacionalidad');
    const placeholder = (typeof i18n !== 'undefined' && i18n[lang]) ? i18n[lang].selNacionalidad : 'Seleccione Nacionalidad';

    select.empty().append(`<option value="" selected disabled>${placeholder}</option>`);

    // Nombre del país en el idioma activo: es/en vienen del JSON, pt/fr/de del navegador (Intl)
    let regiones = null;
    try { if (!['es', 'en'].includes(lang)) regiones = new Intl.DisplayNames([lang], { type: 'region' }); } catch (e) {}
    const nombrePais = p => {
        if (lang === 'es') return p.nombre_es || p.nombre;
        if (regiones && p.codigo) { try { return regiones.of(p.codigo) || p.nombre; } catch (e) {} }
        return p.nombre;
    };

    const sorted = [...paises].sort((a, b) => nombrePais(a).localeCompare(nombrePais(b), lang));

    sorted.forEach(p => {
        const value = p.nombre_es || p.nombre; // siempre en español en la BD
        select.append(`<option value="${value}">${nombrePais(p)}</option>`);
    });
}

/* ===========================
   VALIDACIÓN GENERAL
=========================== */

function validarCampo(input, regex, errorDiv, mensaje) {

    const valor = input.val().trim();

    if (valor === "") {
        mostrarError(input, errorDiv, "Este campo es obligatorio");
        return false;
    }

    if (!regex.test(valor)) {
        mostrarError(input, errorDiv, mensaje);
        return false;
    }

    limpiarError(input, errorDiv);
    return true;
}

function mostrarError(input, div, msg) {
    input.addClass('is-invalid').removeClass('is-valid');
    $(div).text(msg).show();
}

function limpiarError(input, div) {
    input.removeClass('is-invalid').addClass('is-valid');
    $(div).text('').hide();
}

/* ===========================
   INICIALIZACIÓN
=========================== */
$(document).ready(function() {

    cargarPaises();
    // Configurar límites dinámicos en el calendario HTML
    const hoy = new Date();
    const maxFecha = new Date(hoy.getFullYear() - 10, hoy.getMonth(), hoy.getDate()).toISOString().split("T")[0];
    const minFecha = new Date(hoy.getFullYear() - 100, hoy.getMonth(), hoy.getDate()).toISOString().split("T")[0];
    
    $('#reg_fecha_nacimiento').attr('max', maxFecha);
    $('#reg_fecha_nacimiento').attr('min', minFecha);
    $('#reg_genero').on('change blur', function() {
            validarGenero();
        });

    // Validación teléfono (reemplaza la que tenías)
    $('#reg_telefono').on('input blur', function() {
        validarTelefono();
    });

    // Validación nacionalidad
    $('#select-nacionalidad').on('change blur', function() {
        validarNacionalidad();
    });

    cargarReglas().done(function() {
        // ... (tus validaciones anteriores se mantienen igual)

        // Validación de fecha en tiempo real
        $('#reg_fecha_nacimiento').on('input blur', function() {
            validarEdad();
        });
    });


    // Esperar que carguen las reglas
    cargarReglas().done(function() {

        // Validación en tiempo real
        $('#reg_nombre').on('input blur', function() {
            validarCampo($(this), reglasValidacion.letras, '#err_nombre', 'Solo se permiten letras');
        });

        $('#reg_apellido').on('input blur', function() {
            validarCampo($(this), reglasValidacion.letras, '#err_apellido', 'Solo se permiten letras');
        });

        $('#reg_email').on('input blur', function() {
            validarCampo($(this), reglasValidacion.email, '#err_email', 'Correo electrónico inválido');
        });

         $('#password_hash').on('input blur', function() {
            const pass = $(this).val();
            evaluarPasswordStrength(pass);
            validarCampo(
                $(this), 
                reglasValidacion.password, 
                '#err_pass', 
                'Debe tener 8 caracteres, mayúscula, minúscula, número y símbolo'
            );
        });

        $('#confirm_password').on('input blur', function() {
            validarConfirmacion();
        });

        $('input[name="telefono"]').on('input blur', function() {
            validarCampo($(this), reglasValidacion.telefono, null, 'Teléfono inválido');
        });

    });
});

/* ===========================
   VALIDAR CONFIRMAR PASSWORD
=========================== */
function validarConfirmacion() {

    const p1 = $('#password_hash').val();
    const p2 = $('#confirm_password').val();

    if (p2.trim() === "") {
        mostrarError($('#confirm_password'), '#err_conf', 'Confirma tu contraseña');
        return false;
    }

    if (p1 !== p2) {
        mostrarError($('#confirm_password'), '#err_conf', 'Las contraseñas no coinciden');
        return false;
    }

    limpiarError($('#confirm_password'), '#err_conf');
    return true;
}

/* ===========================
   REGISTRAR USUARIO
=========================== */
/* ===========================
   DOS CAMINOS DE REGISTRO
   - 'normal': formulario completo con contraseña + correo de activación
   - 'google': datos de Google precargados, sin contraseña, cuenta activa al terminar
=========================== */
let modoRegistro = 'normal';

const textosRegistroGoogle = {
    es: { titulo: 'Completa tu registro', aviso: 'Te estás registrando con tu cuenta de Google <b>{email}</b>. Solo completa los datos que faltan.', boton: 'COMPLETAR REGISTRO Y ENTRAR' },
    en: { titulo: 'Complete your registration', aviso: 'You are signing up with your Google account <b>{email}</b>. Just fill in the missing details.', boton: 'COMPLETE SIGN UP AND ENTER' },
    pt: { titulo: 'Complete seu cadastro', aviso: 'Você está se cadastrando com sua conta Google <b>{email}</b>. Preencha apenas os dados que faltam.', boton: 'CONCLUIR CADASTRO E ENTRAR' },
    fr: { titulo: 'Finalisez votre inscription', aviso: 'Vous vous inscrivez avec votre compte Google <b>{email}</b>. Complétez simplement les informations manquantes.', boton: "TERMINER L'INSCRIPTION ET ENTRER" },
    de: { titulo: 'Registrierung abschließen', aviso: 'Sie registrieren sich mit Ihrem Google-Konto <b>{email}</b>. Ergänzen Sie nur die fehlenden Angaben.', boton: 'REGISTRIERUNG ABSCHLIESSEN UND EINTRETEN' }
};

function abrirRegistroGoogle(datos) {
    const lang = sessionStorage.getItem('etreva_lang') || 'es';
    const t = textosRegistroGoogle[lang] || textosRegistroGoogle.es;
    const modal = document.getElementById('registerModal');

    modoRegistro = 'google';
    $('#frm-register')[0].reset();
    $('#frm-register .is-invalid, #frm-register .is-valid').removeClass('is-invalid is-valid');
    $('#frm-register .error-msg').text('').hide();

    $('#reg_nombre').val(datos.nombre || '');
    $('#reg_apellido').val(datos.apellido || '');
    $('#reg_email').val(datos.email || '').prop('readonly', true);
    $('.campo-password').addClass('d-none');

    $('#googleRegistroTexto').html(t.aviso.replace('{email}', $('<span>').text(datos.email || '').html()));
    $('#googleRegistroAviso').removeClass('d-none');
    modal.querySelector('.modal-title').innerHTML = `<i class="fab fa-google me-2"></i>${t.titulo}`;
    modal.querySelector('[onclick="registrarUsuario()"]').textContent = t.boton;

    bootstrap.Modal.getOrCreateInstance(modal).show();
}

// Al cerrar el modal se vuelve al registro normal
document.getElementById('registerModal').addEventListener('hidden.bs.modal', function () {
    if (modoRegistro !== 'google') return;
    modoRegistro = 'normal';
    $('#reg_email').prop('readonly', false);
    $('.campo-password').removeClass('d-none');
    $('#googleRegistroAviso').addClass('d-none');
    $('#frm-register')[0].reset();
    if (typeof setLang === 'function') setLang(sessionStorage.getItem('etreva_lang') || 'es'); // restaura título y botón
});

function registrarUsuario() {
    const esGoogle = modoRegistro === 'google';
    const v1 = validarCampo($('#reg_nombre'), reglasValidacion.letras, '#err_nombre', 'Solo letras');
    const v2 = validarCampo($('#reg_apellido'), reglasValidacion.letras, '#err_apellido', 'Solo letras');
    const v3 = esGoogle || validarCampo($('#reg_email'), reglasValidacion.email, '#err_email', 'Email inválido');
    const v4 = esGoogle || validarCampo($('#password_hash'), reglasValidacion.password, '#err_pass', 'Contraseña no cumple requisitos');
    const v5 = esGoogle || validarConfirmacion();
    const v6 = validarEdad();
    const v7 = validarGenero();
    const v8 = validarNacionalidad(); // <--- Nueva
    const v9 = validarTelefono();     // <--- Nueva

    if (!v1 || !v2 || !v3 || !v4 || !v5 || !v6 || !v7 || !v8 || !v9) {
        Swal.fire('Atención', 'Por favor corrige los errores.', 'warning');
        return;
    }

    enviarFormulario();
}
function validarGenero() {
    const select = $('#reg_genero');
    const errorDiv = '#err_genero';
    
    if (select.val() === null || select.val() === "") {
        mostrarError(select, errorDiv, "Debes seleccionar un género");
        return false;
    }
    
    limpiarError(select, errorDiv);
    return true;
}

/* ===========================
   VALIDAR NACIONALIDAD
=========================== */
function validarNacionalidad() {
    const select = $('#select-nacionalidad');
    const errorDiv = '#err_nacionalidad';
    
    if (select.val() === null || select.val() === "") {
        mostrarError(select, errorDiv, "Selecciona tu país");
        return false;
    }
    limpiarError(select, errorDiv);
    return true;
}

/* ===========================
   VALIDAR TELÉFONO (Mejorado)
=========================== */
function validarTelefono() {
    return validarCampo(
        $('#reg_telefono'), 
        reglasValidacion.telefono, 
        '#err_telefono', 
        'Formato: +591 70000000'
    );
}

/* ===========================
   ENVIAR FORMULARIO
=========================== */
function enviarFormulario() {

    Swal.fire({
        title: 'Procesando...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const esGoogle = modoRegistro === 'google';
    const idioma = sessionStorage.getItem('etreva_lang') || 'es';

    $.ajax({
        url: esGoogle ? 'index.php?c=login&a=GoogleRegistro' : 'index.php?c=inicio&a=registrar',
        type: 'POST',
        data: $('#frm-register').serialize() + '&idioma=' + encodeURIComponent(idioma),
        dataType: 'json',

        success: function(res) {

            if (res.success && esGoogle) {
                // Cuenta creada y sesión iniciada → sigue el flujo normal (elegir plan)
                window.location.href = res.redirect || 'index.php';

            } else if (res.success) {

                Swal.fire('¡Éxito!', res.message, 'success')
                    .then(() => location.reload());

            } else {

                Swal.fire({
                    icon: 'error',
                    title: 'Error de Registro',
                    text: res.message
                });

            }
        },

        error: function() {

            Swal.fire(
                'Error crítico',
                'No se pudo conectar con el servidor.',
                'error'
            );

        }
    });
}
</script>