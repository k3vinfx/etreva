<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'model/usuarios.php';

class usuariosController {

    private $model;

    public function __CONSTRUCT() {
        $this->model = new usuarios();
    }

    // --- MÉTODOS PRIVADOS DE UTILIDAD ---

    private function verificarSesion() {
        if (!isset($_SESSION['session_usuario']) || empty($_SESSION['session_usuario'])) {
            header("Location: index.php");
            exit();
        }
    }

    private function evitarCache() {
        header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    // --- MÉTODOS DE VISTA ---




    // --- MÉTODOS DE ACTIVACIÓN Y CUENTA ---

    public function activar() {
        if (!isset($_GET['token']) || empty($_GET['token'])) {
            require_once 'view/usuarios/error_activacion.php';
            return;
        }

        $token = trim($_GET['token']);
        $resultado = $this->model->ActivarCuenta($token);

        if ($resultado) {
            require_once 'view/usuarios/activado.php';
        } else {
            require_once 'view/usuarios/error_activacion.php';
        }
    }

    // --- FLUJO DE RESTAURACIÓN DE CONTRASEÑA ---

    public function SolicitarRestauracion() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if (!isset($_POST['email']) || empty(trim($_POST['email']))) {
                echo json_encode(["success" => false, "message" => "El correo es obligatorio."]);
                return;
            }

            $correoIngresado = trim($_POST['email']);
            $usuario = $this->model->buscarPorCorreo($correoIngresado);

            if (!$usuario) {
                echo json_encode(["success" => true, "message" => "Si el correo está registrado, recibirás un enlace de recuperación."]);
                return;
            }

            // 1. PERSISTENCIA: Decodificar el JSON actual para no borrar 'token_verificacion_correo'
            $config = !empty($usuario['configuracion_json']) ? json_decode($usuario['configuracion_json'], true) : [];
            if (!is_array($config)) $config = [];

            // 2. Generar Token
            $token = bin2hex(random_bytes(32));

            // 3. FUSIÓN: Actualizar solo la llave de restauración manteniendo lo demás
           // 1. Crear el objeto de tiempo explícito
            $tz = new DateTimeZone('America/La_Paz');
            $ahora = new DateTime("now", $tz);
            $fechaBolivia = $ahora->format('Y-m-d H:i:s');

            // 2. FUSIÓN: Actualizar con la fecha garantizada
            $config['restauracion_cuenta'] = [
                "token" => $token,
                "estado" => "pendiente",
                "fecha_creacion" => $fechaBolivia, // <--- Usamos la variable segura
                "fecha_uso" => null
            ];

            $actualizado = $this->model->actualizarConfiguracion($usuario['id'], json_encode($config));

            if ($actualizado) {
                $this->model->enviarCorreoRestauracion($correoIngresado, $token);
                echo json_encode(["success" => true, "message" => "Si el correo está registrado, recibirás un enlace de recuperación."]);
            } else {
                throw new Exception("Error al actualizar la base de datos.");
            }

        } catch (Exception $e) {
            error_log("Error en SolicitarRestauracion: " . $e->getMessage());
            echo json_encode(["success" => false, "message" => "Ocurrió un error inesperado."]);
        }
        exit;
    }

    public function restablecer() {
        if (!isset($_GET['token']) || empty($_GET['token'])) {
            header("Location: index.php");
            exit();
        }

        $token = trim($_GET['token']);
        $usuario = $this->model->buscarPorTokenRestauracion($token);

        if ($usuario) {
            $config = json_decode($usuario['configuracion_json'], true);
            
            // VALIDACIÓN DE TIEMPO (1 HORA = 3600 SEGUNDOS)
            $fechaCreacion = strtotime($config['restauracion_cuenta']['fecha_creacion']);
            $tiempoTranscurrido = time() - $fechaCreacion;

            if ($tiempoTranscurrido > 3600 || $config['restauracion_cuenta']['estado'] !== 'pendiente') {
                require_once 'view/usuarios/error_token.php';
                return;
            }

            require_once 'view/usuarios/restablecer_password.php';
        } else {
            require_once 'view/usuarios/error_token.php';
        }
    }

    public function guardarNuevaPassword() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? null;
        $token = $_POST['token'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$id || !$token || !$password) {
            echo json_encode(["success" => false, "message" => "Datos incompletos."]);
            return;
        }

        try {
            $usuario = $this->model->buscarPorTokenRestauracion($token);

            if ($usuario && $usuario['id'] == $id) {
                $config = json_decode($usuario['configuracion_json'], true);

                // RE-VALIDACIÓN DE SEGURIDAD (TIEMPO)
                $fechaCreacion = strtotime($config['restauracion_cuenta']['fecha_creacion']);
                if ((time() - $fechaCreacion) > 3600) {
                    echo json_encode(["success" => false, "message" => "El enlace ha expirado."]);
                    return;
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                // ACTUALIZACIÓN DE LOG: No usamos unset para mantener el historial
                $config['restauracion_cuenta']['estado'] = 'completado';

                $fechaActual = new DateTime("now", new DateTimeZone('America/La_Paz'));

                $config['token_verificacion_correo']['fecha_verificacion'] = $fechaActual->format('Y-m-d H:i:s');
                // Invalidamos el token para que no se re-use
                $config['restauracion_cuenta']['token'] = 'usado_' . date('Ymd_His');

                $resultado = $this->model->finalizarRestauracion($id, $hash, json_encode($config));

                if ($resultado) {
                    echo json_encode(["success" => true, "message" => "Contraseña actualizada correctamente."]);
                } else {
                    echo json_encode(["success" => false, "message" => "Error al guardar cambios."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Sesión de recuperación inválida."]);
            }
        } catch (Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
        exit;
    }

   



}