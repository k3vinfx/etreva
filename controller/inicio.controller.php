<?php
//Se incluye el modelo donde conectará el controlador.
require_once 'model/login.php';
require_once 'model/recomendaciones.php'; // Nuevo modelo para recomendaciones
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class inicioController{

    private $model;
    private $recomendacionesModel;

    //Creación del modelo
    public function __CONSTRUCT(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Fundamental para leer $_SESSION['token']
        }
        $this->model = new login();
        $this->recomendacionesModel = new Recomendaciones(); // Instanciamos el modelo de recomendaciones
    }

    //Llamado plantilla principal
    public function Index(){
   // Si el token existe y es válido, SALTAMOS el login
        if (isset($_SESSION['token'])) {
            if (JwtHelper::validateToken($_SESSION['token'])) {

            // Obtener rol de la sesión
            $rol = (int)($_SESSION['rol'] ?? 0);

            // Redirección según rol
            if ($rol === 1) {
                header('Location: index.php?c=inicio&a=Inicio');
                exit();
            }

            if ($rol === 2) {
                header('Location: index.php?c=admin&a=Inicio');
                exit();
            }
            if ($rol === 3) {
                header('Location: index.php?c=adminFinanzas&a=Index');
                exit();
            }
          
            }
        }
        require_once 'view/cliente/login.php';
    }

    //Llamado a la vista proveedor-nuevo
    public function Login(){
        $login = new login();

        //Llamado de las vistas.
         require_once 'view/cliente/login.php';
       
    }
       //Llamado a la vista proveedor-nuevo
    public function LoginError(){
        $login = new login();

        //Llamado de las vistas.
         require_once 'view/cliente/login.php';
       
    }

    public function Recomendaciones() {
        // Aquí va la lógica para mostrar las recomendaciones
        require_once 'view/cliente/recomendaciones.php';
    }

    // Esta es la acción que carga el Dashboard/Panel Principal
    public function inicio() {
        // 1. Obtener datos del usuario desde el Middleware
        $usuarioLogueado = AuthMiddleware::resguardar();
        
        if (!$usuarioLogueado) {
            
            // Obtener rol de la sesión
            $rol = (int)($_SESSION['rol'] ?? 0);

            // Redirección según rol
            if ($rol === 1) {
                header('Location: index.php?c=inicio&a=Inicio');
                exit();
            }

            if ($rol === 2) {
                header('Location: index.php?c=admin&a=Inicio');
                exit();
            }
            if ($rol === 3) {
                header('Location: index.php?c=adminFinanzas&a=Index');
                exit();
            }

            exit();
        }

        // --- AQUÍ ASIGNAMOS LAS VARIABLES PARA LA VISTA ---
        // Como tu JWT guarda 'id', 'email' y 'rol'
        $idUsuario = is_array($usuarioLogueado) ? $usuarioLogueado['id'] : $usuarioLogueado->id;
        $idEmail   = is_array($usuarioLogueado) ? $usuarioLogueado['email'] : $usuarioLogueado->email;
        $idMenu    = is_array($usuarioLogueado) ? $usuarioLogueado['rol'] : $usuarioLogueado->rol;
        $idNombre  = is_array($usuarioLogueado) ? ($usuarioLogueado['nombre'] ?? 'Usuario') : ($usuarioLogueado->nombre ?? 'Usuario');
        
        // 2. CORRECCIÓN DEL WARNING: Extraer ID de forma segura
        // Si $usuarioLogueado es array, usamos ['id']; si es objeto, ->id
        $id_busqueda = is_array($usuarioLogueado) ? ($usuarioLogueado['id'] ?? null) : ($usuarioLogueado->id ?? null);

        if (!$id_busqueda) {
            die("Error: No se pudo identificar al usuario en la sesión.");
        }
        $mostrar_modal_preferencias = false;
        // 3. Consultar la configuración en el esquema usuarios.usuarios
        $config = $this->model->ObtenerConfiguracion($id_busqueda);

        if ($config) {
            // Leer idioma: primero raíz, luego fallback a token_verificacion_correo
                $idioma_bd = $config->idioma
                    ?? $config->token_verificacion_correo->idioma
                    ?? null;

            if (Lang::valido($idioma_bd)) {
                // BD tiene idioma válido → usarlo y actualizar sesión
                $_SESSION['lang'] = $idioma_bd;
                Lang::load($idioma_bd);
            } else {
                // BD no tiene idioma → respetar lo que ya hay en sesión
                $lang_sesion = $_SESSION['lang'] ?? 'es';
                Lang::load($lang_sesion);
            }
                // ────────────────────────────────────────────

            // Acceder a la estructura interna del JSON que proporcionaste
            $registroPago = $config->registro_pago;
            $preferenciaUsuario = $config->preferencias ?? null;
          //  $verificacion = $config->token_verificacion_correo;

            // Lógica de redirección basada en el JSON
            if ($registroPago->estado === 'pendiente_nuevo') {
                
                require_once 'view/cliente/pago_pendiente.php';
            } else {
              
                  // Verificar si tiene intereses registrados
                $tieneIntereses = false;
                if ($preferenciaUsuario && isset($preferenciaUsuario->intereses)) {
                    $intereses = $preferenciaUsuario->intereses;
                    // Si es un objeto, convertir a array para verificar vacío
                    if (is_object($intereses)) {
                        $intereses = (array)$intereses;
                    }
                    $tieneIntereses = !empty($intereses);
                }

                if (!$tieneIntereses) {
                  require_once 'view/cliente/inicio.php'; // Cargar la página base
                    require_once 'view/cliente/modal/modal_registro_intereses.php'; // Cargar el modal
                    echo "<script>$(document).ready(function(){ $('#modalPreferencias').modal({backdrop:'static', keyboard:false, show:true}); });</script>";
                    exit(); // para que no siga ejecutando nada más
                } else {
                    require_once 'view/cliente/inicio.php';
                }
                            //extract($data);
               // require_once 'view/cliente/cliente.php';
                
            }
        } else {
            // Sin configuracion_json: si el usuario ya no existe (p. ej. fue eliminado con la
            // sesión abierta) se cierra la sesión; si existe, se trata como cliente nuevo.
            if (!$this->model->ObtenerUsuario($id_busqueda)) {
                header('Location: index.php?c=login&a=Logout');
                exit();
            }
            require_once 'view/cliente/pago_pendiente.php';
        }
    }
    
    public function SetLang() {
        header('Content-Type: application/json');
        
        $lang = $_POST['lang'] ?? 'es';
        $lang = Lang::normalizar($lang);
        
        // Guardar en sesión siempre
        $_SESSION['lang'] = $lang;

        // Si hay usuario logueado, persistir en BD también
        $userId = $_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? null;
        if ($userId) {
            $this->model->actualizarIdioma($userId, $lang);
        }

        echo json_encode(['success' => true]);
        exit;
    }
    //Registrate
     public function Registrate(){
        $login = new login();
             //Llamado de las vistas.
        require_once 'view/Registro/nuevo_usuario.php';
          
    }
    public function registrar() {
        // 1. Establecer el encabezado para responder JSON (obligatorio para AJAX/SWAL)
        header('Content-Type: application/json');

        try {
            // 2. Verificar que la petición sea POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido.");
            }

            // 3. Recolectar datos y Encriptar contraseña
            // Usamos password_hash para que coincida con password_verify del modelo
            $data = [
                'nombre'           => $_POST['nombre'],
                'apellido'         => $_POST['apellido'],
                'email'            => $_POST['email'],
                'telefono'         => $_POST['telefono'],
                'nacionalidad'     => $_POST['nacionalidad'],
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'genero'           => $_POST['genero'],
                'password_hash'    => password_hash($_POST['password_hash'], PASSWORD_DEFAULT),
                'rol'              => 1,        // Cliente
                'estado'           => 'activo',
                'idioma'           => $_POST['idioma'] ?? 'es',
            ];

            // 4. Llamar al método del modelo que ya corregimos
            $resultado = $this->model->Registrar($data);

            // 5. Retornar el resultado al JS
            echo json_encode($resultado);

        } catch (Exception $e) {
            // En caso de error fatal, enviar mensaje al SweetAlert
            echo json_encode([
                "success" => false, 
                "message" => "Error en el servidor: " . $e->getMessage()
            ]);
        }
    }
   private function evitarCache() {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
    }
    

    
}
