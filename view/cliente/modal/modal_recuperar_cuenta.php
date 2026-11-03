<!-- MODAL RESTAURAR CUENTA -->
<div class="modal fade" id="restoreAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Recuperar Cuenta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <form id="frm-restore">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Te mandaremos un Correo Electrónico</label>
                        <input type="email" class="form-control" name="email" id="restore_email" placeholder="tu@correo.com" required>
                        <div class="text-danger small mt-1 error-msg" id="err_restore_email"></div>
                    </div>
                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                        ENVIAR PARA RECUPERAR MI CUENTA
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){

    $('#frm-restore').on('submit', function(e){
        e.preventDefault();

        const emailInput = $('#restore_email');
        const email = emailInput.val().trim();

        if(email === ""){
            $('#err_restore_email').text('Este campo es obligatorio').show();
            emailInput.addClass('is-invalid');
            return;
        } else {
            $('#err_restore_email').text('').hide();
            emailInput.removeClass('is-invalid').addClass('is-valid');
        }

        Swal.fire({
            title: 'Procesando...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: 'index.php?c=usuarios&a=SolicitarRestauracion',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res){
                Swal.close();

                if(res.success){
                    var modalEl = document.getElementById('restoreAccountModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if(!modal) modal = new bootstrap.Modal(modalEl);
                    modal.hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Revisa tu correo 📩',
                        text: res.message,
                        confirmButtonColor: '#03A9F4'
                    });

                    $('#frm-restore')[0].reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message
                    });
                }
            },
            error: function(){
                Swal.fire('Error crítico', 'No se pudo conectar con el servidor', 'error');
            }
        });
    });

});
</script>