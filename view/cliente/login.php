<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="E-commerce ISSIS">
  <meta name="author" content="">

  <title>e-treva</title>

  <!-- Fuentes y estilos -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  <style>
    :root {
      --primary-color: #189561;
      --secondary-color: #03A9F4;
      --dark-color: #1a1a2e;
      --text-light:rgb(247, 247, 247);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    html, body {
      width: 100%;
      height: 100%;
      overflow: hidden;
    }

    /* Contenedor principal con grid */
    .main-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      height: 100vh;
    }

    /* Sección izquierda con carrusel */
    .left-section {
      position: relative;
      overflow: hidden;
    }

    /* Carrusel de imágenes */
    .bg-carousel {
      position: absolute;
      width: 100%;
      height: 100%;
    }

    .bg-carousel img {
      position: absolute;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0;
      transition: opacity 2.5s ease-in-out;
      z-index: 0;
    }

    .bg-carousel img.active {
      opacity: 1;
    }

    /* Texto sobre imágenes */
    .carousel-text {
      position: absolute;
      bottom: 20%;
      left: 10%;
      color: var(--text-light);
      z-index: 2;
      max-width: 80%;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .carousel-text h2 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
      font-weight: 700;
    }

    .carousel-text p {
      font-size: 1.2rem;
      line-height: 1.6;
    }

    /* Sección derecha con login */
    .right-section {
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: rgba(39, 163, 23, 0.6);
      backdrop-filter: blur(5px);
      position: relative;
    }

    /* Efecto de partículas */
    #particles-js {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: 1;
            pointer-events: none;
    }

    /* Contenedor de autenticación */
    .auth-container {
      width: 100%;
      max-width: 380px;
      min-height: 500px;
      padding: 40px 30px;
      background-color: rgba(13, 110, 61, 0.52);
      border-radius: 20px;
      box-shadow: 13px 13px 20px rgba(0,0,0,0.2), -13px -13px 20px rgba(255,255,255,0.1);
      z-index: 4;
      transition: all 0.3s ease;
      margin-right: 50px;
    }

    .auth-container:hover {
      box-shadow: 15px 15px 25px rgba(0,0,0,0.3), -15px -15px 25px rgba(255,255,255,0.2);
    }


    /* Texto sobre imágenes - Versión mejorada */
  .carousel-text {
    position: absolute;
    bottom: 5%;
    left: 10%;
    color: var(--text-light);
    z-index: 2;
    max-width: 80%;
    padding: 2rem;
    background-color: rgba(0, 0, 0, 0.1); /* Fondo semitransparente */
    border-radius: 15px;
    backdrop-filter: blur(5px); /* Efecto vidrio */
    border: 1px solid rgba(255, 255, 255, 0.1); /* Borde sutil */
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-shadow: 
      2px 2px 4px rgba(0,0,0,0.7), /* Sombra principal */
      0 0 8px rgba(0,0,0,0.5); /* Sombra difuminada extra */
  }

  .carousel-text h2 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
    text-shadow: 
      2px 2px 4px rgba(0,0,0,0.8),
      0 0 10px rgba(0,0,0,0.6);
  }

  .carousel-text p {
    font-size: 1.2rem;
    line-height: 1.6;
    text-shadow: 
      1px 1px 3px rgba(0,0,0,0.8),
      0 0 8px rgba(0,0,0,0.5);
  }

  /* Ajustes para móviles */
  @media (max-width: 768px) {
    .carousel-text {
      padding: 1.5rem;
      bottom: 15%;
      left: 5%;
      max-width: 90%;
    }
    
    .carousel-text h2 {
      font-size: 2rem;
    }
    
    .carousel-text p {
      font-size: 1rem;
    }
  }
    /* Logo */
    .logo {
      width: 100px;
      margin: 0 auto 20px;
      transition: transform 0.3s ease;
      animation: pulse 3.5s infinite;
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.35); }
      100% { transform: scale(1); }
    }

    .logo img {
      width: 100%;
      height: 100px;
      object-fit: cover;
      border-radius: 50%;
      box-shadow: 0px 0px 10px rgba(0,0,0,0.3);
    }

    /* Texto */
    .auth-title {
      font-weight: 600;
      font-size: 1.8rem;
      letter-spacing: 1.5px;
      color: var(--text-light);
      text-align: center;
      margin-bottom: 30px;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }

    /* Campos de formulario */
    .form-field {
      position: relative;
      margin-bottom: 25px;
      border-radius: 20px;
      background: rgba(255,255,255,0.8);
      box-shadow: inset 5px 5px 10px rgba(0,0,0,0.1), inset -5px -5px 10px rgba(255,255,255,0.5);
    }

    .form-field i {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #555;
    }

    .form-field input {
      width: 100%;
      border: none;
      outline: none;
      background: none;
      font-size: 1rem;
      color: #333;
      padding: 15px 15px 15px 50px;
    }

    /* Botones */
    .auth-btn {
      width: 100%;
      height: 45px;
      background-color: var(--secondary-color);
      color: #fff;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      letter-spacing: 1.3px;
      box-shadow: 3px 3px 8px rgba(0,0,0,0.2), -3px -3px 8px rgba(255,255,255,0.1);
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .auth-btn:hover {
      background-color: #0388c7;
      transform: translateY(-2px);
      box-shadow: 5px 5px 10px rgba(0,0,0,0.2), -5px -5px 10px rgba(255,255,255,0.1);
    }

    /* Enlaces */
    .auth-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: var(--text-light);
      font-size: 1rem;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .auth-link:hover {
      color: #fff;
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 992px) {
      .main-container {
        grid-template-columns: 1fr;
      }
      
      .left-section {
        display: none;
      }
      
      .right-section {
        background: linear-gradient(135deg, rgba(24, 149, 97, 0.9) 0%, rgba(13, 110, 61, 0.9) 100%);
      }
      
      .auth-container {
        margin-right: 0;
      }
    }

    @media (max-width: 576px) {
      .auth-container {
        width: 90%;
        padding: 30px 20px;
      }
    }
    .separator-line {
      display: flex;
      align-items: center;
      text-align: center;
      color: rgba(255,255,255,0.7);
      margin: 15px 0;
    }
    .separator-line::before, .separator-line::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid rgba(255,255,255,0.3);
    }
    .separator-line:not(:empty)::before { margin-right: .5em; }
    .separator-line:not(:empty)::after { margin-left: .5em; }

    /* ── Language Switcher ── */
    .lang-switcher {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      gap: 6px;
      margin-bottom: 14px;
    }

    .lang-btn {
      display: flex;
      align-items: center;
      gap: 5px;
      background: rgba(255,255,255,0.15);
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 20px;
      color: #fff;
      font-size: 0.78rem;
      font-weight: 600;
      padding: 4px 10px;
      cursor: pointer;
      transition: all 0.2s ease;
      letter-spacing: 0.5px;
    }

    .lang-btn:hover {
      background: rgba(255,255,255,0.28);
    }

    .lang-btn.active {
      background: rgba(255,255,255,0.35);
      border-color: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .lang-btn img {
      width: 18px;
      height: 13px;
      object-fit: cover;
      border-radius: 2px;
    }

    .lang-sep {
      color: rgba(255,255,255,0.4);
      font-size: 0.8rem;
    }

  </style>
  

</head>
    <?php 
    // Incluir el modal desde archivo externo
    include 'modal\modal_cliente_registro.php'; 
    include 'modal\modal_recuperar_cuenta.php'; 

    ?>
<main></main>
<body>
  <div class="main-container">
          <div id="particles-js"></div>
    <!-- Sección izquierda con carrusel de imágenes -->
    <div class="left-section">
      <div class="bg-carousel">
        <!-- Imágenes de fondo (reemplaza con tus propias imágenes) -->
        <img src="assets/img/intro_1.jpg" alt="E-commerce 1" class="active">
        <img src="assets/img/intro_2.jpg" alt="E-commerce 2">
        <img src="assets/img/intro_3.jpg" alt="E-commerce 3">
        <img src="assets/img/intro_4.jpg" alt="E-commerce 4">
        <img src="assets/img/intro_5.jpg" alt="E-commerce 5">
      </div>
      
      <!-- Texto descriptivo -->
        <div class="carousel-text">
          <h2>Bienvenido a Etreva Travel 1.0</h2>
          <p>Tu guía inteligente en La Paz. Descubre destinos que se adaptan perfectamente a tus gustos y perfil.</p>
        </div>
    </div>
    
    <!-- Sección derecha con formulario de login -->
    <div class="right-section">
      <!-- Efecto de partículas -->

      
      <!-- Contenedor de autenticación -->
      <div class="auth-container">
        <div class="logo">
          <img src="assets/img/etreva_logo.jpg" alt="Logo">
        </div>
        <h1 class="auth-title">Iniciar Sesión</h1>
        
        <form id="frm-login" action="?c=login&a=Login" method="post" enctype="multipart/form-data">
          <?php echo isset($alert) ? $alert : ""; ?>
          
          <div class="form-field">
            <i class="fas fa-envelope"></i>
            <input type="email" class="form-control" placeholder="Correo Electrónico" name="CorreoElectronico" required>
          </div>
          
          <div class="form-field">
            <i class="fas fa-lock"></i>
            <input type="password" class="form-control" placeholder="Contraseña" name="Contrasena" required>
          </div>
          
          <button type="submit" class="auth-btn">Iniciar Sesión</button>
          

          <div class="text-center my-3" style="color: white; font-size: 0.9rem;">
            <span>O ingresa con</span>
          </div>

          <div class="d-flex justify-content-center mb-4">
            <div id="g_id_onload"
                data-client_id="<?php require_once __DIR__ . '/../../config/google.php'; echo GOOGLE_CLIENT_ID; ?>"
                data-context="signin"
                data-ux_mode="popup"
                data-callback="handleCredentialResponse"
                data-auto_prompt="false">
            </div>

            <div class="g_id_signin"
                data-type="standard"
                data-shape="pill"
                data-theme="outline"
                data-text="signin_with"
                data-size="large"
                data-logo_alignment="left">
            </div>
          </div>

                      <!-- SELECTOR DE IDIOMA — pegar justo después del <div class="logo"> -->
            <div class="lang-switcher" id="langSwitcher">
              <button class="lang-btn active" data-lang="es" onclick="setLang('es')">
                <img src="https://flagcdn.com/w20/bo.png" alt="ES"> ES
              </button>
              <span class="lang-sep">|</span>
              <button class="lang-btn" data-lang="en" onclick="setLang('en')">
                <img src="https://flagcdn.com/w20/us.png" alt="EN"> EN
              </button>
              <span class="lang-sep">|</span>
              <button class="lang-btn" data-lang="pt" onclick="setLang('pt')">
                <img src="https://flagcdn.com/w20/br.png" alt="PT"> PT
              </button>
              <span class="lang-sep">|</span>
              <button class="lang-btn" data-lang="fr" onclick="setLang('fr')">
                <img src="https://flagcdn.com/w20/fr.png" alt="FR"> FR
              </button>
              <span class="lang-sep">|</span>
              <button class="lang-btn" data-lang="de" onclick="setLang('de')">
                <img src="https://flagcdn.com/w20/de.png" alt="DE"> DE
              </button>
            </div>

          <a href="#" class="auth-link" data-bs-toggle="modal" data-bs-target="#registerModal">
            ¿No tienes cuenta? Regístrate aquí
          </a>

           <a href="#" class="auth-link" data-bs-toggle="modal" data-bs-target="#restoreAccountModal">
           ¿Olvidaste tu contraseña?
          </a>

        </form>
      </div>
    </div>
  </div>




  <!-- Scripts -->

  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="lang/i18n.js"></script>
  <script src="assets/js/helper.js"></script>
  <script>
    
function handleCredentialResponse(response) {
    // 'response.credential' es el ID token (JWT); el backend lo valida contra Google
    Swal.fire({ title: 'Ingresando con Google', html: 'Por favor espere...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    $.ajax({
        url: '?c=login&a=GoogleLogin',
        type: 'POST',
        data: { id_token: response.credential },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                window.location.href = res.redirect || 'index.php';
            } else if (res.error_type === 'google_registro') {
                // Primera vez con Google = registro: completar el perfil en el modal
                Swal.close();
                abrirRegistroGoogle(res.datos || {});
            } else {
                Swal.fire({ icon: 'error', title: 'Fallo al ingresar', text: res.message, confirmButtonColor: '#d33' });
            }
        },
        error: function() {
            Swal.fire('Error', 'No se pudo iniciar sesión con Google', 'error');
        }
    });
}

    $(document).ready(function() {
    $('#btnRegistrar').click(function(e) {
          e.preventDefault();
        
        // Validación básica antes de enviar
        if ($('#clave').val() !== $('#confirm_clave').val()) {
            Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
            return false;
        }

        // Mostrar loader
        Swal.fire({
            title: 'Procesando registro',
            html: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Capturar todos los datos del formulario
        const formData = $('#frm-register').serialize();
        
        $.ajax({
            url: '?c=inicio&a=registrar',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                Swal.close();
                
                if(response.success) {
                    // Cerrar el modal
                    $('#registerModal').modal('hide');
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Registro exitoso!',
                        text: response.message,
                        confirmButtonColor: '#03A9F4'
                    }).then(() => {
                        // Limpiar el formulario
                        $('#frm-register')[0].reset();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                
                let errorMsg = 'Error al procesar el registro';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    // Si el servidor devuelve HTML en lugar de JSON
                    errorMsg = 'Error en el servidor. Por favor intente nuevamente.';
                    console.error("Error del servidor:", xhr.responseText);
                }
                
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });
     });
    // Carrusel de imágenes de fondo
    document.addEventListener('DOMContentLoaded', function() {

    // Manejador del Formulario de Login
    $('#frm-login').on('submit', function(e) {
        e.preventDefault(); // Evitamos que la página se recargue

        const formData = {
            CorreoElectronico: $(this).find('input[name="CorreoElectronico"]').val(),
            Contrasena: $(this).find('input[name="Contrasena"]').val()
        };

        $.ajax({
            url: '?c=login&a=Login',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    window.location.href = response.redirect;
                } else {
                    if (response.error_type === 'not_verified') {
                        // AQUÍ SE MUESTRA EL SWAL QUE SOLICITASTE
                        Swal.fire({
                            icon: 'warning',
                            title: 'Verificación requerida',
                            text: response.message,
                            confirmButtonColor: '#189561',
                            confirmButtonText: 'Revisar mi correo'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Fallo al ingresar',
                            text: response.message,
                            confirmButtonColor: '#d33'
                        });
                    }
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
            }
        });
    });
    
      const images = document.querySelectorAll('.bg-carousel img');
     let currentIndex = 0;
     window.currentCarouselIndex = 0; 

      const textContainer = document.querySelector('.carousel-text');

function changeImage() {
    images[currentIndex].classList.remove('active');
    currentIndex = (currentIndex + 1) % images.length;
    window.currentCarouselIndex = currentIndex;
    images[currentIndex].classList.add('active');

    // Usa window.texts (que helper.js actualiza al cambiar idioma)
    if (window.texts && window.texts[currentIndex]) {
        textContainer.querySelector('h2').textContent = window.texts[currentIndex].title;
        textContainer.querySelector('p').textContent  = window.texts[currentIndex].desc;
    }
}

      // Cambiar imagen cada 5 segundos
      setInterval(changeImage, 5000);

      // Configuración de partículas
      particlesJS('particles-js', {

        "particles": {
          "number": {
            "value": 120,
            "density": {
              "enable": true,
              "value_area": 1000
            }
          },
          "color": {
            "value": "#ffffff"
          },
          "shape": {
            "type": "circle",
            "stroke": {
              "width": 0,
              "color": "#000000"
            }
          },
          "opacity": {
            "value": 0.7,
            "random": true,
            "anim": {
              "enable": true,
              "speed": 1,
              "opacity_min": 0.1,
              "sync": false
            }
          },
          "size": {
            "value": 4,
            "random": true,
            "anim": {
              "enable": true,
              "speed": 4,
              "size_min": 0.3,
              "sync": false
            }
          },
          "line_linked": {
            "enable": true,
            "distance": 180,
            "color": "#ffffff",
            "opacity": 0.4,
            "width": 1.5
          },
          "move": {
            "enable": true,
            "speed": 3,
            "direction": "none",
            "random": true,
            "straight": false,
            "out_mode": "out",
            "bounce": false,
            "attract": {
              "enable": true,
              "rotateX": 600,
              "rotateY": 1200
            }
          }
        },
        "interactivity": {
          "detect_on": "canvas",
          "events": {
            "onhover": {
              "enable": true,
              "mode": "bubble"
            },
            "onclick": {
              "enable": false,
              "mode": "push"
            },
            "resize": true
          },
          "modes": {
            "bubble": {
              "distance": 200,
              "size": 6,
              "duration": 2,
              "opacity": 0.8,
              "speed": 3
            },
            "push": {
              "particles_nb": 6
            }
          }
        },
        "retina_detect": true
      });

      // Mostrar SweetAlert si hay error de login
      <?php if (isset($_SESSION["login_error"])) : ?>
        Swal.fire({
          icon: 'error',
          title: 'Error de autenticación',
          text: '<?php echo $_SESSION["login_error"]; ?>',
          confirmButtonColor: '#03A9F4'
        });
        <?php unset($_SESSION["login_error"]); ?>
      <?php endif; ?>




      
    
    // Mostrar mensajes de éxito/error
    <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '<?php echo $_SESSION['success']; ?>',
            confirmButtonColor: '#03A9F4'
        });
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo $_SESSION['error']; ?>',
            confirmButtonColor: '#03A9F4'
        });
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    });
  </script>
</body>
</html>