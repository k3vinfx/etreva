    <style>
     .bg-primary {
        background-color: rgb(44, 62, 80) !important;
    }
    .icon-adjust {
    margin-top: 10px;  /* Ajusta este valor según necesites */
    display: inline-block; /* Para que funcione margin-top */
    }
    /* Elimina o modifica estos estilos */
    #modalVenta {
        position: fixed;
        z-index: 10;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
    }

    /* En su lugar usa */
    .modal {
    
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1050;
    }

    .modal-dialog {
        position: relative;
            left: 0%;
        margin: 5% auto;
        max-width: 800px;
    }
    /* Estilos para los botones de filtro */
.btn-outline-artistic {
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.btn-outline-artistic:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-outline-artistic.active {
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

/* Estilo para el badge de estado */
.badge {
    font-size: 0.85em;
    padding: 5px 10px;
    border-radius: 10px;
    min-width: 80px;
    display: inline-block;
    text-align: center;
}
/* Oculta el campo de búsqueda de DataTables */
.dataTables_filter {
    display: none;
}

    /* Manteniendo tus estilos originales */
    .bg-primary {
        background-color: rgb(44, 62, 80) !important;
    }
    .icon-adjust {
        margin-top: 10px;
        display: inline-block;
    }
    .badge {
        font-size: 0.85em;
        padding: 5px 10px;
        border-radius: 10px;
        min-width: 80px;
        display: inline-block;
        text-align: center;
    }
    
    /* Nuevos estilos para el perfil */
    .profile-card {
        max-width: 600px;
        margin: 0 auto;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .profile-header {
        background-color: #2c3e50;
        color: white;
        padding: 20px;
        text-align: center;
        border-radius: 5px 5px 0 0 !important;
    }
    .profile-header h4 {
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    .profile-body {
        padding: 30px;
    }
    .profile-field {
        display: flex;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .profile-field:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .field-label {
        flex: 0 0 200px;
        font-weight: 600;
        color: #555;
    }
    .field-value {
        flex: 1;
        color: #333;
    }
    .btn-change-password {
        margin-top: 20px;
        padding: 10px 25px;
        font-weight: 500;
    }

    </style>
   

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    
    <!-- Bootstrap CSS -->    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <link href="assets/css/body.css" rel="stylesheet" type="text/css">
</head>

  
<main>


<div class="container">
    &nbsp; <ion-icon name="people-outline" class="icon-adjust" style="font-size: 2em;">&nbsp;</ion-icon>  
         <h2 class="page-title">
                      
        &nbsp;Mi Perfil&nbsp;
    </h2>
      
               


<br>

  <div class="card profile-card">
        <div class="profile-header">
            <h4 class="m-0"><i class="fas fa-user-circle mr-2"></i>Información del Usuario</h4>
        </div>
        
        <div class="card-body profile-body">
            <?php 
            $usuario = $this->model->Listado();
            if (!empty($usuario)): 
                $r = $usuario[0];
            ?>
            <div class="profile-field">
                <div class="field-label">ID de Usuario</div>
                <div class="field-value" id="idUsuarioPassX"><?php echo $r->id; ?></div>
            </div>
            
            <div class="profile-field">
                <div class="field-label">Nombre Completo</div>
                <div class="field-value"><?php echo $r->nombre; ?></div>
            </div>
            
            <div class="profile-field">
                <div class="field-label">Correo Electrónico</div>
                <div class="field-value"><?php echo $r->email; ?></div>
            </div>
            
            <div class="profile-field">
                <div class="field-label">Rol</div>
                <div class="field-value">
                    <?php 
                        $clase = '';
                        $texto = '';
                        $icono = '';
                        
                        switch($r->rol_id) {
                            case 2: // Dueño/Administrador
                                $clase = 'primary';
                                $texto = 'Administrador';
                                $icono = 'fas fa-crown';
                                break;
                            case 1: // Médico estándar
                                $clase = 'success';
                                $texto = 'Vetarinario';
                                $icono = 'fas fa-user-md';
                                break;
                            case 0: // Inactivo
                                $clase = 'danger';
                                $texto = 'Inactivo';
                                $icono = 'fas fa-user-slash';
                                break;
                            default:
                                $clase = 'secondary';
                                $texto = 'Desconocido';
                                $icono = 'fas fa-question-circle';
                        }
                    ?>
                    <span class="badge badge-<?php echo $clase; ?>">
                        <i class="<?php echo $icono; ?>"></i> <?php echo $texto; ?>
                    </span>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button class="btn btn-primary btn-change-password" id="btnCambiarPassword" data-toggle="modal" data-target="#modalCambiarPassword">
                    <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                </button>
            </div>
            
            <?php else: ?>
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>No se encontraron datos del usuario
            </div>
            <?php endif; ?>
        </div>




</script>
</main>


    <?php 
   
    // Incluir el modal desde archivo externo
    include 'modal\modal_usuario_password.php'; 
    ?>
<!-- Modal para nueva Venta - Versión Mejorada -->


</html>

<script>



</script>

