<?php
require_once 'model/perfil.php';
require_once 'controller/jwtHelper.php';
require_once 'controller/middleware.php';

class PerfilController
{
    private $perfilModel;

    public function __CONSTRUCT()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->perfilModel = new Perfil();
    }

    // ── Helpers ───────────────────────────────────────────────────────
    private function getIdUsuario()
    {
        $u = AuthMiddleware::resguardar();
        if (!$u) return null;
        return is_array($u) ? ($u['id'] ?? null) : ($u->id ?? null);
    }

    private function jsonHeader()  { header('Content-Type: application/json'); }
    private function noAuth()      { echo json_encode(['success'=>false,'message'=>'No autorizado']); }
    private function badMethod()   { echo json_encode(['success'=>false,'message'=>'Método no permitido']); }

    private function leerJson(): ?array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? null) : null;
    }

    // ══════════════════════════════════════════════════════════════════
    //  PÁGINA PRINCIPAL
    // ══════════════════════════════════════════════════════════════════
    public function Index()
    {
        $u = AuthMiddleware::resguardar();
        if (!$u) { header('Location: index.php?c=login&a=Index'); exit(); }
        require_once 'view/cliente/perfil.php';
    }

    // ══════════════════════════════════════════════════════════════════
    //  API: ObtenerPerfil
    //  GET index.php?c=perfil&a=ObtenerPerfil
    // ══════════════════════════════════════════════════════════════════
    public function ObtenerPerfil()
    {
        $this->jsonHeader();

        $id = $this->getIdUsuario();
        if (!$id) { $this->noAuth(); return; }

        $usuario = $this->perfilModel->obtenerUsuarioCompleto($id);

        if (!$usuario) {
            echo json_encode(['success'=>false,'message'=>'Usuario no encontrado','usuario'=>null,'configuracion'=>[]]);
            return;
        }

        // Separar campos de configuración del objeto usuario
        $config_keys = ['avatar','dark_mode','notificaciones','animaciones','menu_compacto','formato_fecha','idioma_ui','contacto_publico'];
        $configuracion = [];
        foreach ($config_keys as $k) {
            $configuracion[$k] = $usuario[$k] ?? null;
            unset($usuario[$k]);
        }

        // idioma_ui → idioma (para que la vista lo lea como 'idioma')
        $configuracion['idioma'] = $configuracion['idioma_ui'];
        unset($configuracion['idioma_ui']);

        echo json_encode([
            'success'       => true,
            'usuario'       => $usuario,
            'configuracion' => $configuracion,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  API: ActualizarDatos
    //  POST index.php?c=perfil&a=ActualizarDatos
    //  Body: { nombre, apellido, email, telefono, ciudad (→nacionalidad),
    //          pais (ignorado), bio (ignorado), idioma_preferido }
    //
    //  La tabla REAL no tiene ciudad/pais/bio.
    //  'ciudad' se guarda como 'nacionalidad' (campo más cercano disponible).
    //  'idioma_preferido' se guarda en configuracion_json->preferencias.
    // ══════════════════════════════════════════════════════════════════
    public function ActualizarDatos()
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->badMethod(); return; }

        $id = $this->getIdUsuario();
        if (!$id) { $this->noAuth(); return; }

        $datos = $this->leerJson();
        if (!$datos) {
            echo json_encode(['success'=>false,'message'=>'Body JSON inválido']);
            return;
        }

        // Validaciones mínimas
        $nombre = trim($datos['nombre'] ?? '');
        $email  = trim($datos['email']  ?? '');

        if (empty($nombre)) {
            echo json_encode(['success'=>false,'message'=>'El nombre es obligatorio']); return;
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success'=>false,'message'=>'Email inválido']); return;
        }

      
       // Fecha nacimiento (validar formato YYYY-MM-DD)
        $fecha_nacimiento = trim($datos['fecha_nacimiento'] ?? '');

        if ($fecha_nacimiento !== '') {
            // Validar formato fecha
            $d = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
            if (!$d || $d->format('Y-m-d') !== $fecha_nacimiento) {
                echo json_encode(['success'=>false,'message'=>'Fecha inválida']);
                return;
            }
        } else {
            $fecha_nacimiento = null;
        }

        $campos_tabla = [
            'nombre'           => $nombre,
            'apellido'         => trim($datos['apellido']  ?? ''),
            'email'            => $email,
            'telefono'         => trim($datos['telefono']  ?? ''),
            'nacionalidad'     => trim($datos['ciudad'] ?? $datos['nacionalidad'] ?? ''),
            'fecha_nacimiento' => $fecha_nacimiento,
        ];

        // Guardar en tabla usuarios
        $ok_tabla = $this->perfilModel->actualizarDatosPersonales($id, $campos_tabla);

        if (!$ok_tabla) {
            echo json_encode(['success'=>false,'message'=>'No se pudo actualizar. El email podría estar en uso.']);
            return;
        }

        // Si vino idioma_preferido → guardarlo en configuracion_json
        $idioma = trim($datos['idioma_preferido'] ?? '');
        if (Lang::valido($idioma)) {
            $this->perfilModel->actualizarConfiguracion($id, ['idioma' => $idioma]);
            $_SESSION['lang'] = $idioma;
        }

        // Si vino bio → guardarlo en configuracion_json también (campo extra)
        $bio = trim($datos['bio'] ?? '');
        if ($bio !== '') {
            $this->perfilModel->actualizarConfiguracion($id, ['bio' => $bio]);
        }

        echo json_encode(['success'=>true,'message'=>'Datos actualizados correctamente']);
    }

    // ══════════════════════════════════════════════════════════════════
    //  API: ActualizarAvatar (preset)
    //  POST index.php?c=perfil&a=ActualizarAvatar
    //  Body: { avatar: "preset_3" }
    // ══════════════════════════════════════════════════════════════════
    public function ActualizarAvatar()
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->badMethod(); return; }

        $id = $this->getIdUsuario();
        if (!$id) { $this->noAuth(); return; }

        $datos  = $this->leerJson();
        $avatar = trim($datos['avatar'] ?? '');

        if (empty($avatar)) {
            echo json_encode(['success'=>false,'message'=>'Avatar no especificado']); return;
        }

        // Validar: preset_0 … preset_11
        if (!preg_match('/^preset_([0-9]|1[01])$/', $avatar)) {
            echo json_encode(['success'=>false,'message'=>'Avatar inválido']); return;
        }

        $ok = $this->perfilModel->actualizarConfiguracion($id, ['avatar' => $avatar]);
        if ($ok) $_SESSION['avatar'] = $avatar;

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Avatar actualizado' : 'Error al actualizar avatar',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  API: SubirAvatar (archivo)
    //  POST multipart/form-data, campo: avatar
    // ══════════════════════════════════════════════════════════════════
    public function SubirAvatar()
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->badMethod(); return; }

        $id = $this->getIdUsuario();
        if (!$id) { $this->noAuth(); return; }

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success'=>false,'message'=>'No se recibió archivo o hubo un error']);
            return;
        }

        $ruta = $this->perfilModel->guardarArchivoAvatar($id, $_FILES['avatar']);

        if (!$ruta) {
            echo json_encode(['success'=>false,'message'=>'Formato no permitido o imagen > 3MB']);
            return;
        }

        $_SESSION['avatar'] = $ruta;
        echo json_encode(['success'=>true,'message'=>'Avatar subido','avatar'=>$ruta]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  API: ActualizarPreferencias
    //  POST index.php?c=perfil&a=ActualizarPreferencias
    // ══════════════════════════════════════════════════════════════════
    public function ActualizarPreferencias()
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->badMethod(); return; }

        $id = $this->getIdUsuario();
        if (!$id) { $this->noAuth(); return; }

        if (!empty($datos['actualizar_solo_rango'])) {

            $rango = $datos['rango_edad'] ?? '';

            if ($rango === '') {
                echo json_encode(['success' => false]);
                return;
            }

            // Obtener preferencias actuales
            $prefsActuales = $this->perfilModel->obtenerPreferencias($id);

            $intereses = $prefsActuales['intereses'] ?? [];

            $intereses['rango_edad'] = $rango;

            $this->modelo->actualizarSoloIntereses($id, $intereses);

            echo json_encode(['success' => true]);
            return;
        }


        $datos = $this->leerJson();
        if (!$datos) {
            echo json_encode(['success'=>false,'message'=>'Body JSON inválido']); return;
        }

        // Enriquecer intereses con campos raíz
        $intereses = $datos['intereses'] ?? [];
        $intereses['motivo_viaje'] = $datos['motivo_viaje'] ?? $intereses['motivo_viaje'] ?? null;
        $intereses['compania']     = $datos['compania']     ?? $intereses['compania']     ?? null;

        $payload = [
            'motivo_viaje'    => $datos['motivo_viaje']    ?? null,
            'presupuesto_max' => $datos['presupuesto_max'] ?? null,
            'duracion_dias'   => $datos['duracion_dias']   ?? null,
            'intereses'       => $intereses,
        ];

        $ok = $this->perfilModel->actualizarPreferencias($id, $payload);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Preferencias guardadas correctamente' : 'Error al guardar preferencias',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  API: ActualizarApariencia
    //  POST index.php?c=perfil&a=ActualizarApariencia
    // ══════════════════════════════════════════════════════════════════
    public function ActualizarApariencia()
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->badMethod(); return; }

        $id = $this->getIdUsuario();
        if (!$id) { $this->noAuth(); return; }

        $datos = $this->leerJson();
        if (!$datos) {
            echo json_encode(['success'=>false,'message'=>'Body JSON inválido']); return;
        }

        $idioma = Lang::normalizar($datos['idioma'] ?? null);
        $_SESSION['lang'] = $idioma;

        $config = [
            'dark_mode'     => (bool)($datos['dark_mode']      ?? false),
            'notificaciones'=> (bool)($datos['notificaciones']  ?? true),
            'animaciones'   => (bool)($datos['animaciones']     ?? true),
            'menu_compacto' => (bool)($datos['menu_compacto']   ?? false),
            'contacto_publico' => (bool)($datos['contacto_publico'] ?? false), // consentimiento para mostrar el correo en reseñas
            'idioma'        => $idioma,
            'formato_fecha' => trim($datos['formato_fecha']     ?? 'dd/mm/yyyy'),
        ];
        $config['tema']   = $config['dark_mode'] ? 'oscuro' : 'claro';
        $_SESSION['tema'] = $config['tema'];

        $ok = $this->perfilModel->actualizarConfiguracion($id, $config);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Apariencia guardada' : 'Error al guardar apariencia',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  API: CambiarPassword
    //  POST index.php?c=perfil&a=CambiarPassword
    //  Body: { password_actual, password_nueva }
    // ══════════════════════════════════════════════════════════════════
    public function CambiarPassword()
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->badMethod(); return; }

        $id = $this->getIdUsuario();
        if (!$id) { $this->noAuth(); return; }

        $datos = $this->leerJson();
        $actual = $datos['password_actual'] ?? '';
        $nueva  = $datos['password_nueva']  ?? '';

        if (empty($actual) || empty($nueva)) {
            echo json_encode(['success'=>false,'message'=>'Completá todos los campos']); return;
        }
        if (strlen($nueva) < 8) {
            echo json_encode(['success'=>false,'message'=>'Mínimo 8 caracteres']); return;
        }

        $resultado = $this->perfilModel->cambiarPassword($id, $actual, $nueva);

        switch ($resultado) {
            case 'ok':
                echo json_encode(['success'=>true,'message'=>'Contraseña actualizada correctamente']);
                break;
            case 'wrong_password':
                echo json_encode(['success'=>false,'message'=>'La contraseña actual es incorrecta']);
                break;
            default:
                echo json_encode(['success'=>false,'message'=>'Error al cambiar la contraseña']);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  API: EliminarCuenta
    //  POST index.php?c=perfil&a=EliminarCuenta
    // ══════════════════════════════════════════════════════════════════
    public function EliminarCuenta()
    {
        $this->jsonHeader();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->badMethod(); return; }

        $id = $this->getIdUsuario();
        if (!$id) { $this->noAuth(); return; }

        $ok = $this->perfilModel->eliminarCuenta($id);

        if ($ok) {
            session_unset();
            session_destroy();
            echo json_encode(['success'=>true,'message'=>'Cuenta eliminada correctamente']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Error al eliminar la cuenta']);
        }
    }
}