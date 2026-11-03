

// Variable global para el estado de autenticación
let autenticacion = {
    token: null,
    usuario: null,
    verificado: false
};

/**
 * Verificar errores de autenticación en respuestas AJAX
 * @param {object} xhr - Objeto XMLHttpRequest
 * @param {object} response - Respuesta JSON parseada (opcional)
 * @returns {boolean} - True si redirige al logout
 */
function verificarErrorAuth(xhr, response) {
    // Si tenemos la respuesta directamente
    if (response && response.success === false) {
        if (response.message === 'No autorizado' || 
            response.message?.includes('token') || 
            response.message?.includes('sesión')) {
            
            mostrarMensajeExpiracion();
            return true;
        }
    }
    
    // Si tenemos xhr y existe
    if (xhr && typeof xhr === 'object') {
        // Verificar por código HTTP
        if (xhr.status === 401 || xhr.status === 403) {
            mostrarMensajeExpiracion();
            return true;
        }
        
        // Verificar texto de respuesta
        if (xhr.responseText) {
            try {
                const errorResponse = JSON.parse(xhr.responseText);
                if (errorResponse && errorResponse.success === false && 
                    (errorResponse.message === 'No autorizado' || errorResponse.message?.includes('token'))) {
                    
                    mostrarMensajeExpiracion();
                    return true;
                }
            } catch (e) {}
        }
    }
    
    return false;
}

/**
 * Mostrar mensaje de sesión expirada y redirigir
 */
function mostrarMensajeExpiracion() {
    // Usar toastr si está disponible
    if (typeof toastr !== 'undefined') {
        toastr.warning('Tu sesión ha expirado. Redirigiendo al login...');
    } else {
        alert('Tu sesión ha expirado. Serás redirigido al login.');
    }
    
    setTimeout(() => {
        window.location.href = 'index.php?c=login&a=Logout';
    }, 2000);
}

/**
 * Verificar sesión al cargar la página
 * @param {function} callback - Función a ejecutar si la sesión es válida
 */
function verificarSesionInicial(callback) {
    $.ajax({
        url: 'index.php?c=login&a=VerPreferencias',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                autenticacion.verificado = true;
                autenticacion.usuario = response.usuario;
                if (callback) callback(true, response);
            } else {
                verificarErrorAuth(null, response);
            }
        },
        error: function(xhr) {
            if (!verificarErrorAuth(xhr)) {
                console.error('Error verificando sesión:', xhr);
            }
        }
    });
}

/**
 * Obtener datos del usuario actual
 * @returns {object} - Datos del usuario
 */
function getUsuarioActual() {
    return autenticacion.usuario;
}

/**
 * Verificar si el usuario está autenticado
 * @returns {boolean}
 */
function isAutenticado() {
    return autenticacion.verificado;
}

// Exportar funciones al ámbito global
window.EtrevaAuth = {
    verificarError: verificarErrorAuth,
    verificarSesionInicial: verificarSesionInicial,
    getUsuario: getUsuarioActual,
    isAutenticado: isAutenticado
};