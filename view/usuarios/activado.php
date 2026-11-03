<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cuenta Activada | Etreva</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter',sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#0f172a;
    color:#e2e8f0;
}

.wrapper{
    width:100%;
    max-width:420px;
    padding:40px;
    background:#111827;
    border:1px solid rgba(255,255,255,0.05);
    border-radius:16px;
    box-shadow:0 20px 60px rgba(0,0,0,0.6);
    text-align:center;
    animation:fadeIn 0.8s ease;
}

.icon{
    width:70px;
    height:70px;
    margin:0 auto 20px auto;
    background:linear-gradient(135deg,#22d3ee,#3b82f6);
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:30px;
    font-weight:700;
    color:white;
}

h1{
    font-size:22px;
    margin-bottom:10px;
    font-weight:600;
}

p{
    font-size:14px;
    color:#94a3b8;
    margin-bottom:25px;
}

.btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:linear-gradient(135deg,#22d3ee,#3b82f6);
    color:white;
    font-weight:600;
    cursor:pointer;
    transition:all 0.3s ease;
}

.btn:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 30px rgba(59,130,246,0.4);
}

.footer{
    margin-top:20px;
    font-size:12px;
    color:#64748b;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}
</style>
</head>
<body>

<div class="wrapper">
    <div class="icon">✓</div>
    <h1>Cuenta activada</h1>
    <p>Tu correo fue verificado correctamente.  
    Ya puedes iniciar sesión en Etreva.</p>
    
    <button class="btn" onclick="redirigir()">Ir al Login</button>

    <div class="footer">
        Redirigiendo automáticamente en 10 segundos...
    </div>
</div>

<script>
function redirigir(){
    window.location.href = "index.php";
}

setTimeout(() => {
    redirigir();
}, 10000);
</script>

</body>
</html>