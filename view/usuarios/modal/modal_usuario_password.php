<!-- Modal para Cambiar Contraseña -->
<div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="frmCambiarPassword">
                    <input type="hidden" name="idUsuario" id="idUsuarioPass" value="<?php echo $r->id ?? ''; ?>">
                    
                    <div class="form-group">
                        <label for="currentPassword">Contraseña Actual</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-danger" id="errorCurrentPass"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="newPassword">Nueva Contraseña</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="newPassword" name="newPassword" required minlength="6">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Mínimo 6 caracteres</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirmar Nueva Contraseña</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <small class="text-danger" id="errorMatchPass"></small>
                    </div>
                    
                    <div class="progress mt-2 mb-3" style="height: 5px;">
                        <div id="passwordStrength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small id="passwordHelp" class="form-text text-muted"></small>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarCambio">Cambiar Contraseña</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Mostrar/ocultar contraseña
    $('.toggle-password').click(function() {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    });

    // Validar fortaleza de contraseña
    $('#newPassword').on('input', function() {
        const password = $(this).val();
        let strength = 0;
        let helpText = '';
        
        if (password.length >= 6) strength += 30;
        if (password.match(/[A-Z]/)) strength += 20;
        if (password.match(/[0-9]/)) strength += 20;
        if (password.match(/[^A-Za-z0-9]/)) strength += 30;
        
        $('#passwordStrength').css('width', strength + '%');
        
        if (strength < 40) {
            $('#passwordStrength').removeClass('bg-warning bg-success').addClass('bg-danger');
            helpText = 'Contraseña débil';
        } else if (strength < 70) {
            $('#passwordStrength').removeClass('bg-danger bg-success').addClass('bg-warning');
            helpText = 'Contraseña moderada';
        } else {
            $('#passwordStrength').removeClass('bg-danger bg-warning').addClass('bg-success');
            helpText = 'Contraseña fuerte';
        }
        
        $('#passwordHelp').text(helpText);
    });

    // Validar coincidencia de contraseñas
    $('#confirmPassword').on('input', function() {
        if ($(this).val() !== $('#newPassword').val()) {
            $('#errorMatchPass').text('Las contraseñas no coinciden');
        } else {
            $('#errorMatchPass').text('');
        }
    });

    // Confirmar cambio de contraseña
    $('#btnConfirmarCambio').click(function() {


        console.log("INGRESO CLICK");
        
        const currentPass = $('#currentPassword').val();
        const newPass = $('#newPassword').val();
        const confirmPass = $('#confirmPassword').val();
        const idUsuario = $('#idUsuarioPass').val(); // CORRECCIÓN AQUÍ
        // Validaciones básicas
        if (!currentPass) {
            $('#errorCurrentPass').text('Ingrese su contraseña actual');
            return;
        }
        
        if (newPass.length < 6) {
            $('#newPassword').focus();
            return;
        }
        
        if (newPass !== confirmPass) {
            $('#errorMatchPass').text('Las contraseñas no coinciden');
            return;
        }
        
        console.log("idUsuarioPass", idUsuario); // CORRECCIÓN AQUÍ
  
        // Verificar contraseña actual primero
        $.ajax({
            url: '?c=usuarios&a=verificarPassword',
            type: 'POST',
            data: {
                idUsuario: idUsuario,
                password: currentPass
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    // Si la contraseña actual es correcta, proceder con el cambio
                    cambiarPassword(newPass);
                } else {
                    $('#errorCurrentPass').text(res.message || 'Contraseña actual incorrecta');
                }
            }
        });
    });
    
    function cambiarPassword(newPassword) {
        const btn = $('#btnConfirmarCambio');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

        $.ajax({
            url: '?c=usuarios&a=cambiarPassword',
            type: 'POST',
            data: {
                idUsuario: $('#idUsuarioPass').val(),
                newPassword: newPassword
            },
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: res.message,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        $('#modalCambiarPassword').modal('hide');
                        $('#frmCambiarPassword')[0].reset();
                        $('#passwordStrength').css('width', '0%');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Error al cambiar la contraseña'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en AJAX:", status, error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo completar la solicitud'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html('Cambiar Contraseña');
            }
        });
    }
});
</script>