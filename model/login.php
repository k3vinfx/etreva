<?php
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class login
{
    private $pdo;

    public function __CONSTRUCT()
    {
        try {
            $this->pdo = Database::Conectar();
        } catch(Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Obtener preferencias del usuario desde la tabla usuarios.preferencias
     * @param int $usuario_id ID del usuario
     * @return object|null Objeto con las preferencias o null si no existe
     */
    public function obtenerPreferenciasDeTabla($usuario_id) {
        try {
            $sql = "SELECT 
                        id,
                        usuario_id,
                        motivo_viaje,
                        presupuesto_max,
                        duracion_dias,
                        intereses_json,
                        created_at,
                        updated_at
                    FROM usuarios.preferencias 
                    WHERE usuario_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            
            $resultado = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($resultado && !empty($resultado->intereses_json)) {
                // Decodificar el JSON de intereses
                $resultado->intereses_json = json_decode($resultado->intereses_json);
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            error_log("Error en obtenerPreferenciasDeTabla: " . $e->getMessage());
            return null;
        }
    }
    /* ==========================================
      OBTENER USUARIO + OBJETO JSON
    =========================================== */



     public function ObtenerUsuario($id) {
        try {
            $sql = "SELECT id, nombre, apellido, email, telefono, nacionalidad, fecha_nacimiento, genero 
                    FROM usuarios.usuarios WHERE id = ?";
            $stm = $this->pdo->prepare($sql);
            $stm->execute([$id]);
            return $stm->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario: " . $e->getMessage());
            return null;
        }
    }
    /* ==========================================
       REGISTRAR USUARIO + OBJETO TOKEN JSON
    =========================================== */
    public function Registrar(array $data)
    {
        try {

            if ($this->existeEmail($data['email'])) {
                return ["success" => false, "message" => "El correo ya está registrado."];
            }

            // 🔐 Generar token seguro
            $token = bin2hex(random_bytes(32));

            // 📦 Crear objeto token_verificacion_correo
            $configuracion_json = json_encode([
                "idioma" => $data['idioma'] ?? 'es',   // ← AGREGAR AQUÍ
                "token_verificacion_correo" => [
                    "token" => $token,
                    "estado" => "pendiente",
                    "fecha_creacion" => date("Y-m-d H:i:s"),
                    "fecha_verificacion" => null
                ],"registro_pago" => [
                    "tipo_plan" => $data['tipo_plan'] ?? 'no_seleccionado', // semanal, mensual, trimestral, anual
                    "estado" => "pendiente_nuevo",
                    "monto" => null,
                    "id_transaccion" => null,
                    "ruta_comprobante" => null,
                    "fecha_pago" => null,
                    "fecha_vencimiento" => null,
                    "ultima_actualizacion" => date("Y-m-d H:i:s")
                ]
            ]);

            $sql = "INSERT INTO usuarios.usuarios (
                        email,
                        password_hash,
                        nombre,
                        apellido,
                        fecha_nacimiento,
                        genero,
                        nacionalidad,
                        telefono,
                        rol,
                        estado,
                        configuracion_json,
                        fecha_registro
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $this->pdo->prepare($sql)->execute([
                $data['email'],
                $data['password_hash'],
                $data['nombre'],
                $data['apellido'],
                $data['fecha_nacimiento'],
                $data['genero'],
                $data['nacionalidad'],
                $data['telefono'],
                1,
                'pendiente_correo',
                $configuracion_json
            ]);

            // 📧 Enviar correo
            $this->enviarCorreoVerificacion($data['email'], $token);

            return [
                "success" => true,
                "message" => "Registro exitoso. Revisa tu correo para activar tu cuenta."
            ];

        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }


    /* ==========================================
        PUENTE PÚBLICO PARA REENVÍO
    =========================================== */
public function reenviarTokenVerificacion($id, $email)
{
    try {

        // 1️⃣ Generar nuevo token
        $nuevoToken = bin2hex(random_bytes(32));

        // 2️⃣ Obtener configuración actual desde BD (más seguro)
        $sql = "SELECT configuracion_json FROM usuarios.usuarios WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return false;
        }

        $config = json_decode($usuario['configuracion_json'], true);

        // 3️⃣ Actualizar SOLO la parte del token
        $config['token_verificacion_correo'] = [
            "token" => $nuevoToken,
            "estado" => "pendiente",
            "fecha_creacion" => date("Y-m-d H:i:s"),
            "fecha_verificacion" => null
        ];

        // 4️⃣ Guardar en PostgreSQL
        $update = "UPDATE usuarios.usuarios SET configuracion_json = ? WHERE id = ?";
        $guardado = $this->pdo->prepare($update)
                              ->execute([json_encode($config), $id]);

        if (!$guardado) {
            return false;
        }

        // 5️⃣ Enviar correo
        return $this->enviarCorreoVerificacion($email, $nuevoToken);

    } catch (Exception $e) {
        error_log("Error reenviarTokenVerificacion: " . $e->getMessage());
        return false;
    }
}
/*==================================
       ENVIAR CORREO
    =========================================== */
   

   private function enviarCorreoVerificacion($email, $token): bool
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom('k3vinfx@gmail.com', 'Etreva Travel');
        $mail->addAddress($email);

        $link = "http://localhost/etreva/index.php?c=usuarios&a=activar&token=" . $token;

        $mail->isHTML(true);
        $mail->Subject = 'Activa tu cuenta - Etreva Travel';
        $mail->Body = "
            <h3>Bienvenido a Etreva Travel</h3>
            <p>Haz clic en el siguiente enlace para activar tu cuenta:</p>
            <a href='$link'>$link</a>
        ";

        $mail->send();

        return true; // ✅ IMPORTANTE

    } catch (Exception $e) {
        error_log("Error enviando correo: " . $mail->ErrorInfo);
        return false; // ✅ IMPORTANTE
    }
}

 
  /* ==========================================
        Obtener Configuracion
    =========================================== */
    public function ObtenerConfiguracion($id) {
        try {
            // Usamos el nombre de la tabla con el esquema (ejemplo: public.usuarios)
            $sql = "SELECT configuracion_json FROM usuarios.usuarios WHERE id = ?";
            $stm = $this->pdo->prepare($sql);
            $stm->execute([$id]);
            
            $resultado = $stm->fetch(PDO::FETCH_OBJ);

            if ($resultado && !empty($resultado->configuracion_json)) {
                // PostgreSQL devuelve el jsonb a veces como string, lo decodificamos
                return json_decode($resultado->configuracion_json);
            }
            return null;
        } catch (PDOException $e) {
            // Esto te ayudará a ver el error real de SQL si persiste
            die("Error en SQL: " . $e->getMessage());
        }
    }
    /* ==========================================
       VALIDAR TOKEN Y ACTIVAR
    =========================================== */
    public function ActivarCuenta($token)
    {
        try {

            $sql = "SELECT * FROM usuarios.usuarios
                    WHERE JSON_UNQUOTE(JSON_EXTRACT(configuracion_json, '$.token_verificacion_correo.token')) = ?
                    AND estado = 'pendiente_correo'";

            $stm = $this->pdo->prepare($sql);
            $stm->execute([$token]);
            $usuario = $stm->fetch(PDO::FETCH_OBJ);

            if ($usuario) {

                $config = json_decode($usuario->configuracion_json, true);

                $config["token_verificacion_correo"]["estado"] = "verificado";
                
                $fechaActual = new DateTime("now", new DateTimeZone('America/La_Paz'));

                $config['token_verificacion_correo']['fecha_verificacion'] = $fechaActual->format('Y-m-d H:i:s');

                $nuevo_json = json_encode($config);

                $update = "UPDATE usuarios.usuarios
                           SET estado = 'activo',
                               configuracion_json = ?
                           WHERE id = ?";

                $this->pdo->prepare($update)->execute([$nuevo_json, $usuario->id]);

                return true;
            }

            return false;

        } catch (Exception $e) {
            return false;
        }
    }

    public function actualizarIdioma($usuario_id, $idioma) {
    try {
        $sql = "SELECT configuracion_json FROM usuarios.usuarios WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $cfg = json_decode($row['configuracion_json'] ?? '{}', true) ?: [];
        $cfg['idioma'] = $idioma;

        $update = "UPDATE usuarios.usuarios SET configuracion_json = ? WHERE id = ?";
        return $this->pdo->prepare($update)->execute([json_encode($cfg), $usuario_id]);

    } catch (PDOException $e) {
        error_log("Error actualizarIdioma: " . $e->getMessage());
        return false;
    }
}


    /* ==========================================
       LOGIN SOLO SI ACTIVO
    =========================================== */
public function Login($correo, $pass) {
    try {

        $sql = "SELECT 
                    id, 
                    nombre, 
                    apellido,
                    email,
                    password_hash,
                    rol, 
                    estado, 
                    configuracion_json 
                FROM usuarios.usuarios 
                WHERE email = ?";

        $stm = $this->pdo->prepare($sql);
        $stm->execute([$correo]);
        $user = $stm->fetch(PDO::FETCH_OBJ);

        if ($user && password_verify($pass, $user->password_hash)) {
              $cfg = json_decode($user->configuracion_json ?? '{}', true);
            $user->idioma = $cfg['idioma'] ?? 'es';   // ← AGREGAR
            return $user;
        }

        return false;

    } catch (Exception $e) {
        error_log("Error en Login Model: " . $e->getMessage());
        return false;
    }
}

    /* ==========================================
       LOGIN CON GOOGLE (solo cuentas existentes)
       Devuelve null si el correo no está registrado → el controlador
       manda al usuario a completar el registro (RegistrarGoogle).
       Google ya verificó el correo: si la cuenta estaba pendiente, se activa.
    =========================================== */
    public function LoginGoogle($email)
    {
        try {
            $sql = "SELECT id, nombre, apellido, email, password_hash, rol, estado, configuracion_json
                    FROM usuarios.usuarios WHERE email = ?";
            $stm = $this->pdo->prepare($sql);
            $stm->execute([$email]);
            $user = $stm->fetch(PDO::FETCH_OBJ);

            if (!$user) return null;

            if ($user->estado === 'pendiente_correo') {
                $cfg = json_decode($user->configuracion_json ?? '{}', true) ?: [];
                $cfg['token_verificacion_correo']['estado'] = 'verificado';
                $cfg['token_verificacion_correo']['fecha_verificacion'] = date("Y-m-d H:i:s");
                $user->configuracion_json = json_encode($cfg);
                $user->estado = 'activo';

                $update = "UPDATE usuarios.usuarios SET estado = 'activo', configuracion_json = ? WHERE id = ?";
                $this->pdo->prepare($update)->execute([$user->configuracion_json, $user->id]);
            }

            $cfg = json_decode($user->configuracion_json ?? '{}', true);
            $user->idioma = $cfg['idioma'] ?? 'es';
            return $user;

        } catch (Exception $e) {
            error_log("Error en LoginGoogle Model: " . $e->getMessage());
            return false;
        }
    }

    /* ==========================================
       REGISTRO CON GOOGLE (segundo camino de registro)
       Mismos datos que Registrar(), pero sin contraseña ni correo de
       activación: la cuenta nace activa porque Google verificó el email.
    =========================================== */
    public function RegistrarGoogle(array $data)
    {
        try {
            if ($this->existeEmail($data['email'])) {
                return ["success" => false, "message" => "El correo ya está registrado. Inicia sesión con Google."];
            }

            $ahora = date("Y-m-d H:i:s");
            $configuracion_json = json_encode([
                "idioma" => $data['idioma'] ?? 'es',
                "origen_registro" => "google",
                "token_verificacion_correo" => [
                    "token" => null,
                    "estado" => "verificado",
                    "fecha_creacion" => $ahora,
                    "fecha_verificacion" => $ahora
                ],
                "registro_pago" => [
                    "tipo_plan" => 'no_seleccionado',
                    "estado" => "pendiente_nuevo",
                    "monto" => null,
                    "id_transaccion" => null,
                    "ruta_comprobante" => null,
                    "fecha_pago" => null,
                    "fecha_vencimiento" => null,
                    "ultima_actualizacion" => $ahora
                ]
            ]);

            // Contraseña aleatoria: entra por Google (puede crear una con "¿Olvidaste tu contraseña?")
            $sql = "INSERT INTO usuarios.usuarios (
                        email, password_hash, nombre, apellido, fecha_nacimiento,
                        genero, nacionalidad, telefono, rol, estado, configuracion_json, fecha_registro
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'activo', ?, NOW())";

            $this->pdo->prepare($sql)->execute([
                $data['email'],
                password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                $data['nombre'],
                $data['apellido'],
                $data['fecha_nacimiento'],
                $data['genero'],
                $data['nacionalidad'],
                $data['telefono'],
                $configuracion_json
            ]);

            return ["success" => true, "message" => "Registro completado."];

        } catch (Exception $e) {
            error_log("Error en RegistrarGoogle Model: " . $e->getMessage());
            return ["success" => false, "message" => "No se pudo completar el registro."];
        }
    }

    /* ==========================================
       ÚLTIMO ACCESO de usuarios internos (rol 2/3)
       Actualiza solo la clave ultimo_acceso del JSONB, sin tocar el resto.
    =========================================== */
    public function registrarAccesoInterno($id): void
    {
        try {
            $this->pdo->prepare("
                UPDATE usuarios.usuarios
                SET configuracion_json = jsonb_set(COALESCE(configuracion_json, '{}'::jsonb),
                                                   '{ultimo_acceso}', to_jsonb(?::text), true)
                WHERE id = ? AND rol IN (2, 3)
            ")->execute([date('Y-m-d H:i:s'), $id]);
        } catch (Exception $e) {
            error_log("Error registrarAccesoInterno: " . $e->getMessage());
        }
    }

    /* ==========================================
       TEMA (oscuro / claro) en configuracion_json
       Guarda 'tema' y su equivalente 'dark_mode' (lo usa la página de perfil).
    =========================================== */
    public function actualizarTema($id, string $tema): bool
    {
        try {
            $patch = json_encode(['tema' => $tema, 'dark_mode' => $tema === 'oscuro']);
            return $this->pdo->prepare("
                UPDATE usuarios.usuarios
                SET configuracion_json = COALESCE(configuracion_json, '{}'::jsonb) || ?::jsonb
                WHERE id = ?
            ")->execute([$patch, $id]);
        } catch (Exception $e) {
            error_log("Error actualizarTema: " . $e->getMessage());
            return false;
        }
    }

    private function existeEmail($email)
    {
        $stm = $this->pdo->prepare("SELECT id FROM usuarios.usuarios WHERE email = ?");
        $stm->execute([$email]);
        return $stm->rowCount() > 0;
    }

    public function CrearSesion($usuario)
    {
        $_SESSION["logged_in"] = true;
        $_SESSION["id_usuario"] = $usuario->id;
        $_SESSION["nombre"]     = $usuario->nombre;
        $_SESSION["apellido"]   = $usuario->apellido;
        $_SESSION["email"]      = $usuario->email;
        $_SESSION["rol"]        = $usuario->rol;
    }

     /* ==========================================
        ACTUALIZAR CONFIGURACION (Genérico)
    =========================================== */
    public function actualizarConfiguracion($id_usuario, $config_json) {
        try {
            // Para PostgreSQL, podemos usar directamente el tipo JSON
            $sql = "UPDATE usuarios.usuarios SET configuracion_json = :config_json WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            
            // Asegurarnos que sea un JSON válido
            if (is_string($config_json)) {
                // Verificar que sea JSON válido
                json_decode($config_json);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("JSON inválido: " . json_last_error_msg());
                    return false;
                }
            }
            
            $stmt->bindParam(':config_json', $config_json);
            $stmt->bindParam(':id', $id_usuario);
            
            $resultado = $stmt->execute();
            
            if (!$resultado) {
                error_log("Error actualizando configuración: " . print_r($stmt->errorInfo(), true));
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            error_log("Error en actualizarConfiguracion: " . $e->getMessage());
            return false;
        }
    }

    /* ==========================================
        PREPARAR PARA RESTAURACIÓN (Específico)
    =========================================== */
    public function prepararRestauracion($id, $json)
    {
        // Este sí cambia el estado porque es para recuperar contraseña
        $sql = "UPDATE usuarios.usuarios SET configuracion_json = :json, estado = 'restauracion_cuenta' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':json', $json);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }


 public function actualizarDatosUsuario($id, $datos) {
    try {
        // Construir la consulta dinámicamente basada en los datos recibidos
        $campos = [];
        $parametros = [':id' => $id];
        
        // Mapeo de campos permitidos
        $campos_permitidos = [
            'nombre' => 'nombre',
            'apellido' => 'apellido',
            'telefono' => 'telefono',
            'nacionalidad' => 'nacionalidad',
            'fecha_nacimiento' => 'fecha_nacimiento',
            'genero' => 'genero'
        ];
        
        // Solo agregar campos que existen y no son null
        foreach ($campos_permitidos as $campo_form => $campo_bd) {
            if (isset($datos[$campo_form]) && $datos[$campo_form] !== null && $datos[$campo_form] !== '') {
                $campos[] = "$campo_bd = :$campo_form";
                $parametros[":$campo_form"] = $datos[$campo_form];
            }
        }
        
        // Si no hay campos para actualizar, retornar true (no hay error)
        if (empty($campos)) {
            error_log("No hay campos para actualizar para el usuario ID: $id");
            return true;
        }
        
        // Construir SQL dinámico
        $sql = "UPDATE usuarios.usuarios SET " . implode(", ", $campos) . " WHERE id = :id";
        
        error_log("SQL generado: " . $sql);
        error_log("Parámetros: " . print_r($parametros, true));
        
        $stmt = $this->pdo->prepare($sql);
        
        // Ejecutar con los parámetros
        $resultado = $stmt->execute($parametros);
        
        if (!$resultado) {
            error_log("Error en execute: " . print_r($stmt->errorInfo(), true));
        } else {
            error_log("Actualización exitosa para usuario ID: $id");
        }
        
        return $resultado;
        
    } catch (PDOException $e) {
        error_log("Error actualizando usuario: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}

/**
 * Verificar si existe registro de preferencias para el usuario
 */
public function existePreferencias($usuario_id) {
    try {
        $sql = "SELECT id FROM usuarios.preferencias WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetch() ? true : false;
    } catch (PDOException $e) {
        error_log("Error en existePreferencias: " . $e->getMessage());
        return false;
    }
}

/**
 * Actualizar preferencias existentes
 */


public function actualizarPreferencias($usuario_id, $datos) {
    // En tu modelo login.php, asegúrate que actualizarPreferencias incluya intereses_json:
    try {
        $campos = [];
        $parametros = [':usuario_id' => $usuario_id];
        
        $campos_permitidos = [
            'motivo_viaje' => 'motivo_viaje',
            'presupuesto_max' => 'presupuesto_max',
            'duracion_dias' => 'duracion_dias',
            'idioma_preferido' => 'idioma_preferido',
            'intereses_json' => 'intereses_json'  // ✅ ESTE CAMPO ES CRUCIAL
        ];
        
        foreach ($campos_permitidos as $campo_form => $campo_bd) {
            if (isset($datos[$campo_form])) {
                $campos[] = "$campo_bd = :$campo_form";
                $parametros[":$campo_form"] = $datos[$campo_form];
            }
        }
        
        if (empty($campos)) {
            return true;
        }
        
        $campos[] = "updated_at = CURRENT_TIMESTAMP";
        
        $sql = "UPDATE usuarios.preferencias SET " . implode(", ", $campos) . " WHERE usuario_id = :usuario_id";
        
        error_log("SQL actualizar preferencias: " . $sql);
        error_log("Parámetros: " . print_r($parametros, true));
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($parametros);
        
    } catch (PDOException $e) {
        error_log("Error en actualizarPreferencias: " . $e->getMessage());
        return false;
    }
}

public function crearPreferencias($usuario_id, $datos) {
    try {
        $campos = ['usuario_id'];
        $placeholders = [':usuario_id'];
        $parametros = [':usuario_id' => $usuario_id];
        
        $campos_permitidos = [
            'motivo_viaje' => 'motivo_viaje',
            'presupuesto_max' => 'presupuesto_max',
            'duracion_dias' => 'duracion_dias',
            'idioma_preferido' => 'idioma_preferido',
            'intereses_json' => 'intereses_json'  // ✅ ESTE CAMPO ES CRUCIAL
        ];
        
        foreach ($campos_permitidos as $campo_form => $campo_bd) {
            if (isset($datos[$campo_form])) {
                $campos[] = $campo_bd;
                $placeholders[] = ":$campo_form";
                $parametros[":$campo_form"] = $datos[$campo_form];
            }
        }
        
        $sql = "INSERT INTO usuarios.preferencias (" . implode(", ", $campos) . ") 
                VALUES (" . implode(", ", $placeholders) . ")";
        
        error_log("SQL crear preferencias: " . $sql);
        error_log("Parámetros: " . print_r($parametros, true));
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($parametros);
        
    } catch (PDOException $e) {
        error_log("Error en crearPreferencias: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener preferencias del usuario
 */
public function obtenerPreferencias($usuario_id) {
    try {
        $sql = "SELECT * FROM usuarios.preferencias WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        error_log("Error en obtenerPreferencias: " . $e->getMessage());
        return null;
    }
}



// ============================================================
//  model/login.php
//  Método: actualizarPago()
// ============================================================
//
//  CAMBIO: En upgrade/renovacion, el registro_pago anterior
//  se guarda en la COLUMNA historial_json (array JSON en BD),
//  NO dentro de configuracion_json.
//
//  Requiere que la columna exista en PostgreSQL:
//  ALTER TABLE usuarios.usuarios ADD COLUMN historial_json JSONB DEFAULT '[]';
// ============================================================

public function actualizarPago($userId, $plan, $monto, $periodo, $rutaImagen, array $extras = []) {

    // ── 1. Leer configuracion_json + historial_json actual ────────────────
    $sql  = "SELECT configuracion_json, historial_json FROM usuarios.usuarios WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$userId]);
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    $jsonExistente   = json_decode($row['configuracion_json'] ?? '{}', true) ?: [];
    $historialActual = json_decode($row['historial_json']     ?? '[]', true) ?: [];

    // ── 2. Calcular fechas / días ─────────────────────────────────────────
    $fechaPago = date("Y-m-d H:i:s");

    $diasBase = $extras['dias_plan'] ?? match($periodo) {
        'weekly'  => 7,
        'monthly' => 30,
        'yearly'  => 365,
        default   => 30,
    };

    $diasCarryover    = (int)($extras['dias_carryover'] ?? 0);
    $diasTotales      = (int)($extras['dias_totales']   ?? ($diasBase + $diasCarryover));
    $fechaVencimiento = $extras['fecha_vencimiento_override']
        ?? date("Y-m-d H:i:s", strtotime($fechaPago . " +{$diasTotales} days"));
    $motivo           = $extras['motivo'] ?? 'nuevo'; // 'nuevo' | 'renovacion' | 'upgrade'

    // ── 3. Archivar registro anterior en historial_json ───────────────────
    //    Solo si es upgrade o renovacion y existe registro previo activo
    if (
        in_array($motivo, ['upgrade', 'renovacion']) &&
        !empty($jsonExistente['registro_pago']['estado']) &&
        $jsonExistente['registro_pago']['estado'] === 'activo_pagado'
    ) {
        $historialActual[] = array_merge(
            $jsonExistente['registro_pago'],
            [
                '_archivado_en'    => $fechaPago,
                '_motivo_cambio'   => $motivo,
                '_reemplazado_por' => strtolower(str_replace(' ', '_', $plan)) . "_{$monto}_{$periodo}",
            ]
        );
    }

    // ── 4. Armar el nuevo registro_pago ───────────────────────────────────
    $jsonExistente['registro_pago'] = [
        'monto'                => $monto,
        'estado'               => 'pendiente_pago',
        'tipo_plan'            => $plan,
        'periodo'              => $periodo,
        'fecha_pago'           => $fechaPago,
        'fecha_vencimiento'    => $fechaVencimiento,
        'ruta_comprobante'     => $rutaImagen,
        'identificador_plan'   => strtolower(str_replace(' ', '_', $plan)) . "_{$monto}_{$periodo}",
        'ultima_actualizacion' => $fechaPago,
        'dias_plan'            => $diasBase,
        'dias_carryover'       => $diasCarryover,
        'dias_totales'         => $diasTotales,
        'motivo_pago'          => $motivo,
        // Método con el que pagó y su referencia (n.º de operación de Yape o hash de la transacción cripto)
        'metodo_pago'          => $extras['metodo_pago']     ?? 'yape',
        'referencia_pago'      => $extras['referencia_pago'] ?? null,
        'monto_bs'             => $extras['monto_bs']        ?? null,
    ];

    // ── 5. Asegurar preferencias ──────────────────────────────────────────
    if (empty($jsonExistente['preferencias'])) {
        $jsonExistente['preferencias'] = [
            "motivo_viaje"      => null,
            "presupuesto_max"   => null,
            "duracion_dias"     => null,
            "intereses"         => null,
            "zona_preferida"    => null,
            "idioma_preferido"  => null,
        ];
    }

    // ── 6. Persistir: configuracion_json + historial_json en un solo UPDATE
    $sqlUpdate = "UPDATE usuarios.usuarios 
                  SET configuracion_json = ?,
                      historial_json     = ?
                  WHERE id = ?";

    return $this->pdo->prepare($sqlUpdate)->execute([
        json_encode($jsonExistente),
        json_encode($historialActual),
        $userId,
    ]);
}



// ─────────────────────────────────────────────────────────────────────────────
//  STATS GENERALES PARA EL DASHBOARD ADMIN
// ─────────────────────────────────────────────────────────────────────────────
public function obtenerStatsAdmin() {
    $stats = new \stdClass();

    // Usuarios y pagos: configuracion_json es JSONB → objeto con '->', texto final con '->>'
    // (los admins, rol 2, no cuentan como clientes ni suscripciones)
    try {
        $u = $this->pdo->query("
            SELECT
                COUNT(*) FILTER (WHERE rol = 1)                                         AS total_usuarios,
                COUNT(*) FILTER (WHERE rol = 1 AND estado = 'activo')                   AS usuarios_activos,
                COUNT(*) FILTER (WHERE rol = 1 AND fecha_registro::date = CURRENT_DATE) AS nuevos_hoy,
                COUNT(*) FILTER (WHERE rol = 1
                    AND configuracion_json->'registro_pago'->>'estado' = 'activo_pagado'
                    AND NULLIF(configuracion_json->'registro_pago'->>'fecha_vencimiento', '')::timestamp >= NOW())
                                                                                        AS pagos_activos,
                COUNT(*) FILTER (WHERE rol = 1
                    AND configuracion_json->'registro_pago'->>'estado' = 'pendiente_pago') AS pagos_pendientes,
                COUNT(*) FILTER (WHERE rol = 1
                    AND configuracion_json->'registro_pago'->>'estado' = 'activo_pagado'
                    AND NULLIF(COALESCE(configuracion_json->'registro_pago'->>'fecha_aprobacion',
                                        configuracion_json->'registro_pago'->>'fecha_pago'), '')::timestamp
                        >= NOW() - INTERVAL '7 days')                                   AS pagos_semana
            FROM usuarios.usuarios
        ")->fetch(\PDO::FETCH_ASSOC);
        foreach ($u as $k => $v) $stats->$k = (int)$v;
    } catch (\PDOException $e) {
        error_log("Error obtenerStatsAdmin (usuarios): " . $e->getMessage());
    }

    // Catálogo y recomendaciones: cada bloque aparte para que un fallo no deje todo en 0
    try {
        $a = $this->pdo->query("
            SELECT COUNT(*) AS total, COUNT(*) FILTER (WHERE estado ILIKE 'activ%') AS activas
            FROM catalogo.atracciones
        ")->fetch(\PDO::FETCH_ASSOC);
        $stats->total_atracciones   = (int)$a['total'];
        $stats->atracciones_activas = (int)$a['activas'];
    } catch (\PDOException $e) {
        error_log("Error obtenerStatsAdmin (atracciones): " . $e->getMessage());
    }

    try {
        $r = $this->pdo->query("
            SELECT COUNT(*) AS total,
                   COUNT(*) FILTER (WHERE COALESCE(fecha_generacion, created_at) >= date_trunc('month', NOW())) AS mes
            FROM recomendaciones.recomendaciones
        ")->fetch(\PDO::FETCH_ASSOC);
        $stats->total_recomendaciones = (int)$r['total'];
        $stats->rec_este_mes          = (int)$r['mes'];
    } catch (\PDOException $e) {
        error_log("Error obtenerStatsAdmin (recomendaciones): " . $e->getMessage());
    }

    return $stats;
}

// ─────────────────────────────────────────────────────────────────────────────
//  MINI-STATS PARA SIDEBAR
// ─────────────────────────────────────────────────────────────────────────────
public function obtenerMiniStatsAdmin() {
    try {
        $activos = $this->pdo
            ->query("SELECT COUNT(*) FROM usuarios.usuarios WHERE rol = 1 AND estado = 'activo'")
            ->fetchColumn();

        $pendientes = $this->pdo
            ->query("SELECT COUNT(*) FROM usuarios.usuarios
                     WHERE rol = 1 AND configuracion_json->'registro_pago'->>'estado' = 'pendiente_pago'")
            ->fetchColumn();

        $obj = new \stdClass();
        $obj->usuarios_activos = (int)$activos;
        $obj->pagos_pendientes = (int)$pendientes;
        return $obj;

    } catch (\PDOException $e) {
        error_log("Error obtenerMiniStatsAdmin: " . $e->getMessage());
        return new \stdClass();
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  ÚLTIMOS N USUARIOS REGISTRADOS
// ─────────────────────────────────────────────────────────────────────────────
public function obtenerUsuariosRecientes(int $limite = 10): array {
    try {
        $sql = "SELECT id, nombre, apellido, email, rol, estado, fecha_registro, configuracion_json
                FROM usuarios.usuarios
                ORDER BY fecha_registro DESC
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    } catch (\PDOException $e) {
        error_log("Error obtenerUsuariosRecientes: " . $e->getMessage());
        return [];
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  PAGOS CON ESTADO PENDIENTE_PAGO
// ─────────────────────────────────────────────────────────────────────────────
public function obtenerPagosPendientes(): array {
    try {
        // Busca usuarios cuyo JSON tiene estado pendiente_pago
        $sql = "SELECT
                    u.id,
                    u.nombre,
                    u.apellido,
                    u.email,
                    u.configuracion_json,
                    -- Extraer datos del JSON para PostgreSQL
                    u.configuracion_json->'registro_pago'->>'monto'        AS monto,
                    u.configuracion_json->'registro_pago'->>'tipo_plan'     AS plan,
                    u.configuracion_json->'registro_pago'->>'ultima_actualizacion' AS fecha
                FROM usuarios.usuarios u
                WHERE u.rol = 1 AND u.configuracion_json->'registro_pago'->>'estado' = 'pendiente_pago'
                ORDER BY (u.configuracion_json->'registro_pago'->>'ultima_actualizacion') DESC
                LIMIT 20";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);

    } catch (\PDOException $e) {
        error_log("Error obtenerPagosPendientes: " . $e->getMessage());
        return [];
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  DISTRIBUCIÓN DE PLANES ACTIVOS
// ─────────────────────────────────────────────────────────────────────────────
public function obtenerDistribucionPlanes(): array {
    try {
        // Extrae el tipo_plan del JSON y agrupa
        $sql = "SELECT
                    COALESCE(
                        u.configuracion_json->'registro_pago'->>'tipo_plan',
                        'sin_plan'
                    ) AS plan,
                    COUNT(*) AS cantidad
                FROM usuarios.usuarios u
                WHERE u.rol = 1 AND u.configuracion_json->'registro_pago'->>'estado' = 'activo_pagado'
                GROUP BY plan
                ORDER BY cantidad DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);

    } catch (\PDOException $e) {
        error_log("Error obtenerDistribucionPlanes: " . $e->getMessage());
        return [];
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  ACTIVIDAD RECIENTE (si tienes tabla de logs, ajusta aquí)
//  Por ahora devuelve los últimos registros/cambios de estado como actividad
// ─────────────────────────────────────────────────────────────────────────────
public function obtenerActividadReciente(int $limite = 15): array {
    try {
        // Usar registros recientes de usuarios como proxy de actividad
        $sql = "SELECT
                    'registro' AS tipo,
                    CONCAT('Nuevo usuario: ', nombre, ' ', apellido) AS descripcion,
                    fecha_registro AS tiempo
                FROM usuarios.usuarios
                ORDER BY fecha_registro DESC
                LIMIT ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limite]);
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // Formatear tiempo relativo
        foreach ($rows as $row) {
            $diff = time() - strtotime($row->tiempo);
            if ($diff < 60)         $row->tiempo = 'Hace ' . $diff . 's';
            elseif ($diff < 3600)   $row->tiempo = 'Hace ' . floor($diff/60) . ' min';
            elseif ($diff < 86400)  $row->tiempo = 'Hace ' . floor($diff/3600) . 'h';
            else                    $row->tiempo = date('d/m/Y', strtotime($row->tiempo));
        }

        return $rows;

    } catch (\PDOException $e) {
        error_log("Error obtenerActividadReciente: " . $e->getMessage());
        return [];
    }
}

































}