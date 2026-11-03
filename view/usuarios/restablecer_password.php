<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña | Etreva</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body {
            height:100vh; display:flex; justify-content:center; align-items:center;
            background:#0f172a; color:#e2e8f0;
        }
        .wrapper {
            width:100%; max-width:420px; padding:40px;
            background:#111827; border:1px solid rgba(255,255,255,0.05);
            border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.6);
            text-align:center; animation:fadeIn 0.8s ease;
        }
        .icon {
            width:70px; height:70px; margin:0 auto 20px auto;
            background:linear-gradient(135deg,#3b82f6,#1d4ed8);
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            font-size:28px; color:white;
        }
        h1 { font-size:22px; margin-bottom:10px; font-weight:600; }
        p { font-size:14px; color:#94a3b8; margin-bottom:25px; }
        
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; margin-bottom: 8px; color: #94a3b8; }
        
        input {
            width: 100%; padding: 12px; background: #1f2937; border: 1px solid #374151;
            border-radius: 10px; color: white; outline: none; transition: 0.3s;
        }
        input:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,0.2); }

        .btn {
            width:100%; padding:12px; border:none; border-radius:10px;
            background:linear-gradient(135deg,#3b82f6,#1d4ed8);
            color:white; font-weight:600; cursor:pointer; transition:all 0.3s ease;
        }
        .btn:hover { transform:translateY(-2px); box-shadow:0 10px 30px rgba(59,130,246,0.4); }

        @keyframes fadeIn{ from{opacity:0; transform:translateY(20px);} to{opacity:1; transform:translateY(0);} }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="icon">🔑</div>
    <h1>Nueva Contraseña</h1>
    <p>Ingresa tu nueva clave de acceso para recuperar tu cuenta.</p>
    
    <form id="frm-restore-final">
        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
        <input type="hidden" name="token" value="<?php echo $token; ?>">

        <div class="form-group">
            <label>Nueva Contraseña</label>
            <input type="password" name="password" id="pass1" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label>Confirmar Contraseña</label>
            <input type="password" id="pass2" placeholder="••••••••" required>
        </div>
        
        <button type="submit" class="btn">Actualizar Contraseña</button>
    </form>
</div>

<script>
$('#frm-restore-final').on('submit', function(e) {
    e.preventDefault();
    
    if($('#pass1').val() !== $('#pass2').val()) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Las contraseñas no coinciden', background: '#111827', color: '#fff' });
        return;
    }

    $.ajax({
        url: 'index.php?c=usuarios&a=guardarNuevaPassword',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Listo!',
                    text: res.message,
                    background: '#111827', color: '#fff'
                }).then(() => { window.location.href = "index.php"; });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#111827', color: '#fff' });
            }
        }
    });
});
</script>

</body>
</html>