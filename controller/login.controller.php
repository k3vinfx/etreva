<?php
require_once 'model/login.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php'; // Asegúrate de tener este archivo
require_once 'config/google.php';
require_once 'model/avatar.php';

class LoginController{

    private $model;

   /* =============================================================================
       Creacion del Controller y Configuración de sesión para 1 hora y creación del modelo
    ============================================================================= */

    public function __CONSTRUCT(){
   // Configuramos la duración de la sesión a 1 hora
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', 3600);
            session_set_cookie_params(3600);
            session_start();
        }
        $this->model = new login();
    }

   /*  =============================================================================
       Index redirigir al Dashboard si el token es válido, evitando mostrar el login
       ============================================================================= */
    public function Index() {
        // 1. Asegurar que la sesión esté iniciada para leer el token
         if (session_status() === PHP_SESSION_NONE) session_start();
        // 2. Si existe el token, validarlo inmediatamente
        // ← Verificar que el token exista antes de validarlo
        if (!empty($_SESSION['token']) && JwtHelper::validateToken($_SESSION['token'])) {

            $rol = (int)($_SESSION['rol'] ?? 0);

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
            require_once 'view/cliente/login.php';
            require_once 'view/footerx.php';
        }
   /* =======================================================================================================
      Login con validación de estado pendiente_correo y rol, además de respuesta JSON para manejo en frontend
      ======================================================================================================= */

    public function LoginError(){
        $login = new login();
        require_once 'view/cliente/login.php';
        require_once 'view/footerx.php';

      //  $alert = '<div class="alert alert-danger" role="alert">
       // Ingrese su usuario y su clave
       // </div>';
    
        ?>
        <script>
            alert("Error de CorreoElectronico o Contraseña.");
        </script><?php 
    }   
   /* =============================================================
      Guardar preferencias completas (datos usuario + preferencias)
      ============================================================= */

    public function GuardarPreferencias() {
    header('Content-Type: application/json');
    
    try {
        // Verificar método
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        // Obtener usuario logueado
        $usuarioLogueado = AuthMiddleware::resguardar();
        if (!$usuarioLogueado) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $id_usuario = is_array($usuarioLogueado) ? ($usuarioLogueado['id'] ?? null) : ($usuarioLogueado->id ?? null);
        if (!$id_usuario) {
            echo json_encode(['success' => false, 'message' => 'ID no encontrado']);
            return;
        }
        
        // Leer JSON del body
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos o JSON mal formado']);
            return;
        }
        
        $datos_usuario = $input['actualizar_usuario'] ?? [];
        $preferencias = $input['preferencias'] ?? [];
        
        // Log para depuración
        error_log("=== GuardarPreferencias ===");
        error_log("ID Usuario: $id_usuario");
        error_log("Datos usuario recibidos: " . print_r($datos_usuario, true));
        error_log("Preferencias recibidas: " . print_r($preferencias, true));
        
        // 1. ACTUALIZAR TABLA USUARIOS (datos básicos)
        $update_usuario = true;
        if (!empty($datos_usuario)) {
            $update_usuario = $this->model->actualizarDatosUsuario($id_usuario, $datos_usuario);
            error_log("Resultado actualizar usuario: " . ($update_usuario ? "true" : "false"));
        }
        
        // 2. ACTUALIZAR TABLA PREFERENCIAS (columnas específicas)
        $update_preferencias_tabla = true;
        $preferencias_tabla = [];
        
        // Mapeo de campos que van a la tabla preferencias
        if (isset($preferencias['motivo_viaje'])) {
            $preferencias_tabla['motivo_viaje'] = $preferencias['motivo_viaje'];
        }
        if (isset($preferencias['presupuesto_max'])) {
            $preferencias_tabla['presupuesto_max'] = $preferencias['presupuesto_max'];
        }
        if (isset($preferencias['duracion_dias'])) {
            $preferencias_tabla['duracion_dias'] = $preferencias['duracion_dias'];
        }
        if (isset($preferencias['idioma_preferido'])) {
            $preferencias_tabla['idioma_preferido'] = $preferencias['idioma_preferido'];
        }
        
        // ============= CORRECCIÓN IMPORTANTE =============
        // Procesar intereses para guardar en intereses_json de la tabla preferencias
        if (isset($preferencias['intereses'])) {
                $intereses = $preferencias['intereses'];
                
                // Según tu imagen, la estructura es:
                // "intereses": {
                //   "compania": "solo",
                //   "rango_edad": "menor_18"
                // }
                
                if (is_array($intereses)) {
                    // Guardar el objeto completo como JSON en intereses_json
                    $preferencias_tabla['intereses_json'] = json_encode($intereses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    error_log("✅ INTERESES GUARDADOS EN intereses_json: " . $preferencias_tabla['intereses_json']);
                } else if ($intereses === null) {
                    $preferencias_tabla['intereses_json'] = json_encode([]);
                    error_log("⚠️ INTERESES NULL, guardando array vacío");
                } else {
                    // Si viene como string u otro formato
                    $preferencias_tabla['intereses_json'] = json_encode([$intereses]);
                    error_log("⚠️ INTERESES EN OTRO FORMATO: " . print_r($intereses, true));
                }
            } else {
                error_log("⚠️ NO SE RECIBIÓ EL CAMPO 'intereses' en preferencias");
            }
            // ==================================================
            
            // Actualizar tabla preferencias si hay datos
            if (!empty($preferencias_tabla)) {
                error_log("Datos a guardar en tabla preferencias: " . print_r($preferencias_tabla, true));
                
                if ($this->model->existePreferencias($id_usuario)) {
                    $update_preferencias_tabla = $this->model->actualizarPreferencias($id_usuario, $preferencias_tabla);
                } else {
                    $update_preferencias_tabla = $this->model->crearPreferencias($id_usuario, $preferencias_tabla);
                }
                error_log("Resultado actualizar tabla preferencias: " . ($update_preferencias_tabla ? "true" : "false"));
            } else {
                error_log("No hay datos para guardar en tabla preferencias");
            }
            
            // 3. ACTUALIZAR CONFIGURACION_JSON (para mantener la estructura que VerPreferencias espera)
            $config = $this->model->ObtenerConfiguracion($id_usuario);
            
            if (!$config) {
                $config = new stdClass();
                $config->preferencias = new stdClass();
                $config->registro_pago = new stdClass();
            }
            
            if (!isset($config->preferencias)) {
                $config->preferencias = new stdClass();
            }
            
            // Actualizar preferencias en el JSON
            foreach ($preferencias as $key => $value) {
                if ($key === 'interese') {
                    // Para 'interese', si es null poner true como pediste
                    $config->preferencias->$key = ($value === null || $value === '') ? true : $value;
                    error_log("interese en JSON: " . ($config->preferencias->$key ? 'true' : 'false'));
                } else if ($value !== null && $value !== '') {
                    $config->preferencias->$key = $value;
                }
            }
            
            // Asegurar campos requeridos
            $campos_requeridos = ['motivo_viaje', 'duracion_dia', 'zona_preferida', 'presupuesto_max', 'idioma_preferido', 'interese'];
            foreach ($campos_requeridos as $campo) {
                if (!isset($config->preferencias->$campo)) {
                    $config->preferencias->$campo = ($campo === 'interese') ? true : null;
                }
            }
            
            $json_config = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            error_log("JSON config a guardar: " . $json_config);
            
            $update_config_json = $this->model->actualizarConfiguracion($id_usuario, $json_config);
            error_log("Resultado actualizar config JSON: " . ($update_config_json ? "true" : "false"));
            
            // Verificar resultados
            if ($update_usuario && $update_preferencias_tabla && $update_config_json) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Datos actualizados correctamente'
                ]);
            } else {
                $errores = [];
                if (!$update_usuario) $errores[] = 'Error en datos de usuario';
                if (!$update_preferencias_tabla) $errores[] = 'Error en preferencias de tabla';
                if (!$update_config_json) $errores[] = 'Error en configuración JSON';
                
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al actualizar: ' . implode(', ', $errores)
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Excepción en GuardarPreferencias: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Error interno del servidor'
            ]);
        }
    }
   /* =======================================================================================================
      Cambiar idioma al vuelo (login y sidebar): guarda en sesión y, si hay usuario logueado, en la BD.
      No requiere volver a iniciar sesión: el JS solo recarga la página.
      ======================================================================================================= */

    public function SetLang(): void {
        header('Content-Type: application/json');

        $lang = $_POST['lang'] ?? 'es';
        $lang = Lang::normalizar($lang);

        $_SESSION['lang'] = $lang;

        $userId = $_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? null;
        if ($userId) {
            $this->model->actualizarIdioma($userId, $lang);
        }

        echo json_encode(['success' => true, 'lang' => $lang]);
        exit;
    }

   /* =======================================================================================================
      Cambiar tema (oscuro / claro): guarda en sesión y, si hay usuario logueado, en configuracion_json.
      ======================================================================================================= */

    public function SetTema(): void {
        header('Content-Type: application/json');

        $tema = ($_POST['tema'] ?? 'oscuro') === 'claro' ? 'claro' : 'oscuro';
        $_SESSION['tema'] = $tema;

        $userId = $_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? null;
        if ($userId) {
            $this->model->actualizarTema($userId, $tema);
        }

        echo json_encode(['success' => true, 'tema' => $tema]);
        exit;
    }

    // Tema guardado del usuario: 'tema' (nuevo) o 'dark_mode' (perfil); por defecto oscuro
    private function temaDe($usuario): string {
        $cfg = json_decode($usuario->configuracion_json ?? '{}', true) ?: [];
        if (isset($cfg['tema'])) return $cfg['tema'] === 'claro' ? 'claro' : 'oscuro';
        if (array_key_exists('dark_mode', $cfg) && $cfg['dark_mode'] === false) return 'claro';
        return 'oscuro';
    }

   /* ================
      Eliminar Session
      ================ */

    public function Logout() {
            // Iniciar sesión si no está iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Destruir todas las variables de sesión
            $_SESSION = array();

            // Si se desea destruir la sesión completamente, borra también la cookie de sesión
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(), 
                    '', 
                    time() - 42000,
                    $params["path"], 
                    $params["domain"],
                    $params["secure"], 
                    $params["httponly"]
                );
            }

            // Finalmente, destruir la sesión
            session_destroy();

            // Redirigir al login
            header("Location: index.php?c=inicio&a=Index");
            exit();
    }

   /* =========================================================
      Ver preferencias completas (datos usuario + preferencias) 
      ========================================================= */

    public function VerPreferencias() {
        header('Content-Type: application/json');
        
        $usuarioLogueado = AuthMiddleware::resguardar();
        
        if (!$usuarioLogueado) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }
        
        $id_busqueda = is_array($usuarioLogueado) ? ($usuarioLogueado['id'] ?? null) : ($usuarioLogueado->id ?? null);
        
        if (!$id_busqueda) {
            echo json_encode(['success' => false, 'message' => 'ID no encontrado']);
            return;
        }
        
        $config = $this->model->ObtenerConfiguracion($id_busqueda);
        $usuario = $this->model->ObtenerUsuario($id_busqueda);
        $preferencias = $this->model->obtenerPreferenciasDeTabla($id_busqueda);
        $respuesta = [
            'success' => true,
            'usuario' => [
                'id' => $usuario->id ?? null,
                'nombre' => $usuario->nombre ?? null,
                'apellido' => $usuario->apellido ?? null,
                'email' => $usuario->email ?? null,
                'telefono' => $usuario->telefono ?? null,
                'nacionalidad' => $usuario->nacionalidad ?? null,
                'fecha_nacimiento' => $usuario->fecha_nacimiento ?? null,
                'genero' => $usuario->genero ?? null
            ],
            'preferencias' => $config->preferencias ?? null,
            'registro_pago' => $config->registro_pago ?? null,
            'preferencias_registro_tb' => [
                'id' => $preferencias->id ?? null,
                'usuario_id' => $preferencias->usuario_id ?? null,
                'motivo_viaje' => $preferencias->motivo_viaje ?? null,
                'presupuesto_max' => $preferencias->presupuesto_max ?? null,
                'duracion_dias' => $preferencias->duracion_dias ?? null,
                'intereses' => $preferencias->intereses_json ?? null, // Ya viene decodificado
                'created_at' => $preferencias->created_at ?? null,
                'updated_at' => $preferencias->updated_at ?? null
            ]
        ];
        
        echo json_encode($respuesta);
    }


   
   /* =======================================================================================================
      Login con validación de estado pendiente_correo y rol, además de respuesta JSON para manejo en frontend 
      ======================================================================================================= */

     public function Login(): void {

        ob_start();

        $jsonInput = file_get_contents('php://input');
        $inputData = json_decode($jsonInput, true);

        $correo = $inputData['CorreoElectronico'] ?? $_POST['CorreoElectronico'] ?? null;
        $pass   = $inputData['Contrasena']        ?? $_POST['Contrasena']        ?? null;

        if (!$correo || !$pass) {
            $this->enviarRespuestaJSON(false, "Credenciales incorrectas.");
            return;
        }

        $usuario = $this->model->Login($correo, $pass);

        if (!$usuario) {
            $this->enviarRespuestaJSON(false, "Credenciales incorrectas.");
            return;
        }

        $this->finalizarLogin($usuario);
    }

   /* =======================================================================================================
      Login con Google: valida el ID token (JWT) contra Google, crea/activa la cuenta y abre sesión
      ======================================================================================================= */

    public function GoogleLogin(): void {

        ob_start();

        $idToken = $_POST['id_token'] ?? null;
        if (!$idToken) {
            $this->enviarRespuestaJSON(false, "Token de Google no recibido.");
            return;
        }

        $payload = $this->verificarTokenGoogle($idToken);
        if (!$payload) {
            $this->enviarRespuestaJSON(false, "No se pudo validar tu cuenta de Google.");
            return;
        }

        $usuario = $this->model->LoginGoogle($payload['email']);

        if ($usuario === false) {
            $this->enviarRespuestaJSON(false, "Error al iniciar sesión con Google.");
            return;
        }

        // ── Primera vez con Google → es un REGISTRO: guardar los datos verificados
        //    y pedir al frontend que abra el formulario para completar el perfil.
        if ($usuario === null) {
            $_SESSION['google_registro'] = [
                'email'    => $payload['email'],
                'nombre'   => $payload['given_name']  ?? ($payload['name'] ?? ''),
                'apellido' => $payload['family_name'] ?? '',
                'expira'   => time() + 1800,
            ];

            $this->enviarRespuestaJSON(
                false,
                "Completa tu registro para continuar.",
                "google_registro",
                null,
                ['datos' => [
                    'email'    => $_SESSION['google_registro']['email'],
                    'nombre'   => $_SESSION['google_registro']['nombre'],
                    'apellido' => $_SESSION['google_registro']['apellido'],
                ]]
            );
            return;
        }

        $this->finalizarLogin($usuario);
    }

   /* =======================================================================================================
      Registro con Google: completa el perfil del usuario cuyo correo ya validó Google en GoogleLogin.
      El email sale de la sesión (no del formulario) para que no se pueda registrar otro correo.
      ======================================================================================================= */

    public function GoogleRegistro(): void {

        ob_start();

        $g = $_SESSION['google_registro'] ?? null;
        if (!$g || ($g['expira'] ?? 0) < time()) {
            unset($_SESSION['google_registro']);
            $this->enviarRespuestaJSON(false, "Tu sesión de Google expiró. Vuelve a presionar \"Iniciar sesión con Google\".");
            return;
        }

        $data = [
            'email'            => $g['email'],
            'nombre'           => trim($_POST['nombre']           ?? ''),
            'apellido'         => trim($_POST['apellido']         ?? ''),
            'telefono'         => trim($_POST['telefono']         ?? ''),
            'nacionalidad'     => trim($_POST['nacionalidad']     ?? ''),
            'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? ''),
            'genero'           => trim($_POST['genero']           ?? ''),
            'idioma'           => Lang::normalizar($_POST['idioma'] ?? ($_SESSION['lang'] ?? 'es')),
        ];

        foreach (['nombre', 'apellido', 'telefono', 'nacionalidad', 'fecha_nacimiento', 'genero'] as $campo) {
            if ($data[$campo] === '') {
                $this->enviarRespuestaJSON(false, "Completa todos los campos del formulario.");
                return;
            }
        }

        $resultado = $this->model->RegistrarGoogle($data);
        if (!$resultado['success']) {
            $this->enviarRespuestaJSON(false, $resultado['message']);
            return;
        }

        unset($_SESSION['google_registro']);

        $usuario = $this->model->LoginGoogle($data['email']);
        if (!$usuario) {
            $this->enviarRespuestaJSON(false, "La cuenta se creó, pero no se pudo iniciar sesión. Intenta de nuevo con Google.");
            return;
        }

        $this->finalizarLogin($usuario);
    }

    private function verificarTokenGoogle(string $idToken): ?array {
        $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $code !== 200) {
            error_log("GoogleLogin: tokeninfo HTTP $code $err $body");
            return null;
        }

        $p = json_decode($body, true);
        if (!is_array($p)) return null;

        $issValido = in_array($p['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true);
        $emailOk   = in_array($p['email_verified'] ?? '', ['true', true], true);

        if (($p['aud'] ?? '') !== GOOGLE_CLIENT_ID || !$issValido || !$emailOk
            || (int)($p['exp'] ?? 0) < time() || empty($p['email'])) {
            error_log("GoogleLogin: token rechazado " . $body);
            return null;
        }

        return $p;
    }

   /* =======================================================================================================
      Pasos comunes después de autenticar (contraseña o Google): estado, rol, plan, sesión y respuesta JSON
      ======================================================================================================= */

    private function finalizarLogin($usuario): void {

        // ── 1. PENDIENTE DE VERIFICACIÓN ─────────────────────────────────────
        if ($usuario->estado === 'pendiente_correo') {
            $enviado = $this->model->reenviarTokenVerificacion(
                $usuario->id,
                $usuario->email
            );
            $this->enviarRespuestaJSON(
                false,
                $enviado
                    ? "Cuenta pendiente. Se reenvió el enlace de activación."
                    : "Error al enviar el correo. Contactá a soporte.",
                "not_verified"
            );
            return;
        }

        // ── 2. CUENTA INACTIVA ───────────────────────────────────────────────
        if ($usuario->estado !== 'activo') {
            $this->enviarRespuestaJSON(false, "Cuenta inactiva.");
            return;
        }

        // ── 3. VALIDAR ROL — acepta rol 1 (cliente) y rol 2 (admin) ────────────
        $rol = (int)$usuario->rol;
        if (!in_array($rol, [1, 2, 3])) {
            $this->enviarRespuestaJSON(false, "Rol no autorizado.");
            return;
        }

        if ($rol === 2 || $rol === 3) {
            $_SESSION['token'] = JwtHelper::createToken([
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'email'  => $usuario->email,
                'rol'    => $usuario->rol,
                'exp'    => time() + 3600,
            ]);
            $_SESSION['user_id']         = $usuario->id;
            $_SESSION['session_usuario'] = $usuario->nombre;
        $_SESSION['session_apellido'] = $usuario->apellido ?? '';
        $_SESSION['avatar']           = Avatar::deConfig($usuario->configuracion_json ?? null);
            $_SESSION['rol']             = $rol;
            $_SESSION['pago_activo']     = true;   // personal interno no tiene restricciones de plan
            $_SESSION['plan_estado']     = null;   // el menú admin ignora esto
            $_SESSION['lang']            = Lang::normalizar($usuario->idioma ?? 'es');
            $_SESSION['tema']            = $this->temaDe($usuario);

            // Registra ultimo_acceso en configuracion_json (se ve en Gestión de Usuarios)
            $this->model->registrarAccesoInterno($usuario->id);

            $this->enviarRespuestaJSON(
                true,
                $rol === 2 ? "¡Bienvenido, administrador!" : "¡Bienvenido, equipo de Finanzas!",
                null,
                $rol === 2 ? 'index.php?c=admin&a=Inicio' : 'index.php?c=adminFinanzas&a=Index'
            );
            return;
        }



        // ── 4. CALCULAR ESTADO DEL PLAN ──────────────────────────────────────
        //    Una sola vez aquí; el menú solo leerá la sesión
        $config        = !empty($usuario->configuracion_json)
            ? json_decode($usuario->configuracion_json, true)
            : [];
        $registro_pago = $config['registro_pago'] ?? [];

        $planEstado = $this->calcularEstadoPlan($registro_pago);

        // ── 5. CREAR SESIÓN ──────────────────────────────────────────────────
        $_SESSION['token'] = JwtHelper::createToken([
            'id'     => $usuario->id,
            'nombre' => $usuario->nombre,
            'email'  => $usuario->email,
            'rol'    => $usuario->rol,
            'exp'    => time() + 3600,
        ]);

        $_SESSION['user_id']         = $usuario->id;
        $_SESSION['session_usuario'] = $usuario->nombre;
        $_SESSION['session_apellido'] = $usuario->apellido ?? '';
        $_SESSION['avatar']           = Avatar::deConfig($usuario->configuracion_json ?? null);
        $_SESSION['rol']             = $usuario->rol;
        $_SESSION['pago_activo']     = ($planEstado['estado'] === 'activo');
        $_SESSION['plan_estado']     = $planEstado;   // el menú y las vistas solo leen esto

        $_SESSION['tema'] = $this->temaDe($usuario);

        $idioma = $usuario->idioma ?? 'es';
        $idioma = Lang::normalizar($idioma);
        $_SESSION['lang'] = $idioma;
        Lang::load($idioma);


        // ← NUEVO: guardar TODO el estado del plan en sesión
        $_SESSION['plan_estado'] = $planEstado;
       $rol             = (int)($_SESSION['rol']             ?? 0);
       $redirect = '';
        // Redirección según rol
            if ($rol === 1) {
              
           switch ($planEstado['estado']) {
            case 'activo':
                $mensaje    = "¡Bienvenido de nuevo!";
                $error_type = null;
                $redirect = 'index.php?c=inicio&a=Inicio';
                break;

            case 'gracia':
                $mensaje    = "Bienvenido. Tu plan venció hace {$planEstado['dias_vencido']} día(s). Tenés {$planEstado['dias_gracia_restantes']} día(s) de acceso gratuito.";
                $error_type = "plan_gracia";   // el JS del login puede mostrar aviso
                break;

            case 'bloqueado':
                // Redirigir directo a pago si el plan está bloqueado
                $mensaje    = "Tu plan venció hace {$planEstado['dias_vencido']} días. Renovalo para continuar.";
                $error_type = "plan_bloqueado";
                $redirect   = 'index.php?c=pago&a=Index';
                break;


           case 'pendiente':
                // Redirigir directo a pago si el plan está bloqueado
                $mensaje    = "Bienvenido. Activá tu plan para acceder a todas las funciones.";
                $error_type = "plan_bloqueado";
                $redirect   = 'index.php?c=Inicio&a=Inicio';
                break;
            default: // sin_plan / pendiente_pago
                $mensaje    = "Bienvenido. Activá tu plan para acceder a todas las funciones.";
                $error_type = "pago_pendiente";
                $redirect   = 'index.php?c=Inicio&a=Inicio';
                break;
        }

           
            }

           
          

        

        // ── 6. RESPUESTA AL FRONTEND ─────────────────────────────────────────


      
        $this->enviarRespuestaJSON(true, $mensaje, $error_type, $redirect);
    }

    // ════════════════════════════════════════════════════════════════
    //  CALCULAR ESTADO DEL PLAN
    //  Devuelve array con todo lo necesario para el menú y las vistas
    // ════════════════════════════════════════════════════════════════ 
    // 
 

       private function calcularEstadoPlan(array $rp): array {

        // Campos comunes que SIEMPRE están en la sesión (incluido plan_estado_raw)
        $planNombre    = $rp['tipo_plan']       ?? 'Sin plan';
        $pagoRaw       = $rp['estado']          ?? null;
        $motivoRechazo = $rp['motivo_rechazo']  ?? null;

        // Helper para construir el array con campos extra siempre presentes
        $mk = function(array $d) use ($planNombre, $pagoRaw, $motivoRechazo): array {
            return array_merge([
                'plan_nombre'           => $planNombre,
                'pago_estado_raw'       => $pagoRaw,
                'motivo_rechazo'        => $motivoRechazo,
                'pago_plan'             => $planNombre,
                'dias_vencido'          => 0,
                'dias_gracia_restantes' => 0,
                'dias_restantes'        => 0,
                'es_gracia'             => false,
                'es_bloqueado'          => false,
                'puede_navegar'         => false,
                'pendiente_vencido'       => false,
                'horas_esperando'         => 0,
                'horas_gracia_pago'       => 72,
                'horas_restantes_pago'    => 72,
            ], $d);
        };

        // ── SIN REGISTRO DE PAGO — usuario recién registrado ─────────────────
        if (empty($pagoRaw)) {
            return $mk(['estado' => 'sin_plan', 'pago_estado_raw' => 'sin_plan']);
        }

        // ── PAGO PENDIENTE — comprobante subido, esperando admin ──────────────
        //    Puede navegar (tiene acceso limitado hasta confirmación)
        // ── PAGO PENDIENTE — comprobante subido, esperando admin ──────────────
        if ($pagoRaw === 'pendiente_pago') {
            
            // Calcular cuántas horas lleva esperando verificación
            $tz          = new DateTimeZone('America/La_Paz');
            $ahora       = new DateTime('now', $tz);
            $fechaPago   = !empty($rp['fecha_pago'])
                            ? new DateTime($rp['fecha_pago'], $tz)
                            : $ahora;  // fallback: si no hay fecha, no bloquear aún
            
            $horas_esperando = (int)(($ahora->getTimestamp() - $fechaPago->getTimestamp()) / 3600);
            $horas_gracia_pago = 72; // 3 días para que el admin valide
            
            $puede_navegar_pendiente = ($horas_esperando < $horas_gracia_pago);
            
            return $mk([
                'estado'                  => 'pendiente_pago',
                'puede_navegar'           => $puede_navegar_pendiente,
                'pendiente_vencido'       => !$puede_navegar_pendiente,
                'horas_esperando'         => $horas_esperando,
                'horas_gracia_pago'       => $horas_gracia_pago,
                'horas_restantes_pago'    => max(0, $horas_gracia_pago - $horas_esperando),
            ]);
        }

        // ── PAGO RECHAZADO — admin rechazó el comprobante ────────────────────
        //    NO puede navegar. Solo ve el overlay con el motivo.
        if ($pagoRaw === 'rechazado') {
            return $mk([
                'estado'        => 'sin_plan',
                'puede_navegar' => false,  // ← bloqueado hasta subir nuevo comprobante
                'es_bloqueado'  => false,  // ← NO activa el overlay de "vencido"
            ]);
        }

        // ── CUALQUIER OTRO ESTADO QUE NO SEA activo_pagado ───────────────────
        if ($pagoRaw !== 'activo_pagado') {
            return $mk(['estado' => 'sin_plan']);
        }

        // Sin fecha de vencimiento — dato corrupto
        if (empty($rp['fecha_vencimiento'])) {
            return $mk(['estado' => 'sin_plan']);
        }

            // ── SIN REGISTRO DE PAGO — usuario recién registrado ─────────────────
    if (empty($pagoRaw)) {
        return $mk(['estado' => 'sin_plan', 'pago_estado_raw' => 'sin_plan']);
    }

    // ── PENDIENTE NUEVO — usuario registrado que nunca hizo pago ──────────
    //    Puede navegar libremente (igual que pendiente_pago),
    //    pero el inicioController lo redirige a pago_pendiente.php
    if ($pagoRaw === 'pendiente_nuevo') {
        return $mk([
            'estado'        => 'sin_plan',
            'pago_estado_raw' => 'pendiente_nuevo',  // ← valor propio para no confundir
            'puede_navegar' => false,   // ← no puede navegar, debe pagar primero
            'es_bloqueado'  => false,   // ← NO activa overlay de plan vencido
        ]);
    }

        // ── PLAN ACTIVO_PAGADO: calcular vigencia ─────────────────────────────
        $tz    = new DateTimeZone('America/La_Paz');
        $ahora = new DateTime('now', $tz);
        $vence = new DateTime($rp['fecha_vencimiento'], $tz);
        $dias  = (int)$ahora->diff($vence)->days;

        // ACTIVO — no vencido todavía
        if ($vence > $ahora) {
            return $mk([
                'estado'          => 'activo',
                'puede_navegar'   => true,
                'dias_restantes'  => $dias,
            ]);
        }

        // GRACIA — vencido entre 1 y 3 días
        $diasGracia = 3;
        if ($dias <= $diasGracia) {
            return $mk([
                'estado'                => 'gracia',
                'puede_navegar'         => true,
                'es_gracia'             => true,
                'dias_vencido'          => $dias,
                'dias_gracia_restantes' => $diasGracia - $dias,
            ]);
        }

        // BLOQUEADO — más de 3 días vencido
        return $mk([
            'estado'        => 'bloqueado',
            'puede_navegar' => false,
            'es_bloqueado'  => true,
            'dias_vencido'  => $dias,
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    //  REFRESCAR plan_estado en sesión (llamar en cada página
    //  si querés que sea en tiempo real y no solo al login)
    // ════════════════════════════════════════════════════════════════
  public function RefrescarPlanSesion(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId || (int)($_SESSION['rol'] ?? 0) !== 1) return;

        $config = $this->model->ObtenerConfiguracion($userId);

        // ObtenerConfiguracion puede devolver objeto o array
        if (is_object($config)) {
            $rp = (array)($config->registro_pago ?? []);
        } elseif (is_array($config)) {
            $rp = $config['registro_pago'] ?? [];
        } else {
            $rp = [];
        }

        $planEstado = $this->calcularEstadoPlan($rp);

        $_SESSION['plan_estado'] = $planEstado;
        $_SESSION['pago_activo'] = ($planEstado['estado'] === 'activo');
    }


   /* =======================================================================================================
      Enviar respuesta JSON estandarizada para el frontend (éxito, mensaje, tipo de error)
      ======================================================================================================= */

        private function enviarRespuestaJSON($success, $message, $errorType = null, $redirect = null, array $extra = []) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');

            echo json_encode(array_merge([
                "success" => $success,
                "message" => $message,
                "error_type" => $errorType,
                "redirect" => $redirect
            ], $extra));

    exit();
}




}
  

?>