<?php
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Perfil
{
    private $pdo;

    public function __CONSTRUCT()
    {
        try {
            $this->pdo = Database::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  OBTENER DATOS COMPLETOS DEL USUARIO
    //  Columnas REALES: id, nombre, apellido, email, telefono,
    //                   nacionalidad, fecha_nacimiento, genero,
    //                   estado, fecha_registro, configuracion_json
    // ══════════════════════════════════════════════════════════════════
    public function obtenerUsuarioCompleto(int $id_usuario)
    {
        try {
            $sql = "SELECT
                        id,
                        nombre,
                        apellido,
                        email,
                        telefono,
                        nacionalidad,
                        fecha_nacimiento,
                        genero,
                        estado,
                        fecha_registro,
                        configuracion_json
                    FROM usuarios.usuarios
                    WHERE id = :id
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id_usuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return null;

            // Parsear configuracion_json
            $config = [];
            if (!empty($row['configuracion_json'])) {
                $config = is_string($row['configuracion_json'])
                    ? (json_decode($row['configuracion_json'], true) ?? [])
                    : (array)$row['configuracion_json'];
            }

            // idioma_preferido viene de preferencias{} dentro del JSON
            $prefs_json              = $config['preferencias'] ?? [];
            $row['idioma_preferido'] = $prefs_json['idioma_preferido'] ?? null;

            // Campos de apariencia / config
            $row['avatar']         = $config['avatar']         ?? null;
            $row['dark_mode']      = $config['dark_mode']      ?? false;
            $row['notificaciones'] = $config['notificaciones'] ?? true;
            $row['animaciones']    = $config['animaciones']    ?? true;
            $row['menu_compacto']  = $config['menu_compacto']  ?? false;
            $row['contacto_publico'] = ($config['contacto_publico'] ?? false) === true;
            $row['formato_fecha']  = $config['formato_fecha']  ?? 'dd/mm/yyyy';
            $row['idioma_ui']      = $config['idioma']         ?? $row['idioma_preferido'] ?? 'es';

            unset($row['configuracion_json']); // no exponer raw JSON

            return $row;

        } catch (PDOException $e) {
            error_log('Perfil::obtenerUsuarioCompleto → ' . $e->getMessage());
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  OBTENER PREFERENCIAS (tabla usuarios.preferencias)
    // ══════════════════════════════════════════════════════════════════
    public function obtenerPreferencias(int $id_usuario)
    {
        try {
            $sql = "SELECT
                        id,
                        usuario_id,
                        motivo_viaje,
                        presupuesto_max,
                        duracion_dias,
                        intereses_json
                    FROM usuarios.preferencias
                    WHERE usuario_id = :id
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id_usuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return null;

            $row['intereses'] = [];
            if (!empty($row['intereses_json'])) {
                $row['intereses'] = is_string($row['intereses_json'])
                    ? (json_decode($row['intereses_json'], true) ?? [])
                    : (array)$row['intereses_json'];
            }
            unset($row['intereses_json']);

            return $row;

        } catch (PDOException $e) {
            error_log('Perfil::obtenerPreferencias → ' . $e->getMessage());
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  ACTUALIZAR DATOS PERSONALES
    //  Whitelist EXACTA de columnas reales de la tabla
    // ══════════════════════════════════════════════════════════════════
    public function actualizarDatosPersonales(int $id_usuario, array $datos): bool
    {
        try {
            // Solo estas columnas existen en la tabla
            $campos_reales = [
                'nombre', 'apellido', 'email',
                'telefono', 'nacionalidad',
                'fecha_nacimiento', 'genero',
            ];

            $sets   = [];
            $params = [':id' => $id_usuario];

            foreach ($campos_reales as $campo) {
                if (array_key_exists($campo, $datos) && $datos[$campo] !== null && $datos[$campo] !== '') {
                    $sets[]              = "{$campo} = :{$campo}";
                    $params[":{$campo}"] = $datos[$campo];
                }
            }

            if (empty($sets)) return true; // nada que actualizar

            // Evitar email duplicado
            if (isset($datos['email'])) {
                $check = $this->pdo->prepare(
                    "SELECT id FROM usuarios.usuarios WHERE email = :email AND id <> :id LIMIT 1"
                );
                $check->execute([':email' => $datos['email'], ':id' => $id_usuario]);
                if ($check->fetch()) return false;
            }

            $sql  = "UPDATE usuarios.usuarios SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log('Perfil::actualizarDatosPersonales → ' . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  ACTUALIZAR CONFIGURACION_JSON (merge parcial)
    //  Gestiona: avatar, dark_mode, idioma, formato_fecha, etc.
    // ══════════════════════════════════════════════════════════════════
    public function actualizarConfiguracion(int $id_usuario, array $nuevos_campos): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT configuracion_json FROM usuarios.usuarios WHERE id = :id"
            );
            $stmt->execute([':id' => $id_usuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $config = [];
            if ($row && !empty($row['configuracion_json'])) {
                $config = json_decode($row['configuracion_json'], true) ?? [];
            }

            // Merge en raíz
            foreach ($nuevos_campos as $k => $v) {
                $config[$k] = $v;
            }

            // Sincronizar idioma dentro de preferencias{} también
            if (isset($nuevos_campos['idioma'])) {
                if (!isset($config['preferencias'])) $config['preferencias'] = [];
                $config['preferencias']['idioma_preferido'] = $nuevos_campos['idioma'];
            }

            $json   = json_encode($config, JSON_UNESCAPED_UNICODE);
            $update = $this->pdo->prepare(
                "UPDATE usuarios.usuarios SET configuracion_json = :json WHERE id = :id"
            );
            return $update->execute([':json' => $json, ':id' => $id_usuario]);

        } catch (PDOException $e) {
            error_log('Perfil::actualizarConfiguracion → ' . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  ACTUALIZAR PREFERENCIAS DE VIAJE (UPSERT)
    // ══════════════════════════════════════════════════════════════════
    public function actualizarPreferencias(int $id_usuario, array $datos): bool
    {
        try {
            $motivo_viaje   = $datos['motivo_viaje']    ?? null;
            $presupuesto    = $datos['presupuesto_max'] ?? null;
            $duracion       = $datos['duracion_dias']   ?? null;
            $intereses      = $datos['intereses']       ?? [];
            $intereses_json = json_encode($intereses, JSON_UNESCAPED_UNICODE);

            $check = $this->pdo->prepare(
                "SELECT id FROM usuarios.preferencias WHERE usuario_id = :uid LIMIT 1"
            );
            $check->execute([':uid' => $id_usuario]);

            if ($check->fetch()) {
                $sql = "UPDATE usuarios.preferencias
                        SET motivo_viaje    = :motivo,
                            presupuesto_max = :presupuesto,
                            duracion_dias   = :duracion,
                            intereses_json  = :intereses,
                            updated_at      = CURRENT_TIMESTAMP
                        WHERE usuario_id = :uid";
            } else {
                $sql = "INSERT INTO usuarios.preferencias
                            (usuario_id, motivo_viaje, presupuesto_max, duracion_dias, intereses_json)
                        VALUES (:uid, :motivo, :presupuesto, :duracion, :intereses)";
            }

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':uid'         => $id_usuario,
                ':motivo'      => $motivo_viaje,
                ':presupuesto' => $presupuesto,
                ':duracion'    => $duracion,
                ':intereses'   => $intereses_json,
            ]);

        } catch (PDOException $e) {
            error_log('Perfil::actualizarPreferencias → ' . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  CAMBIAR CONTRASEÑA → 'ok' | 'wrong_password' | 'error'
    // ══════════════════════════════════════════════════════════════════
    public function cambiarPassword(int $id_usuario, string $pwd_actual, string $pwd_nueva): string
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT password_hash FROM usuarios.usuarios WHERE id = :id"
            );
            $stmt->execute([':id' => $id_usuario]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return 'error';
            if (!password_verify($pwd_actual, $row['password_hash'])) return 'wrong_password';

            $nuevo_hash = password_hash($pwd_nueva, PASSWORD_BCRYPT, ['cost' => 12]);
            $update = $this->pdo->prepare(
                "UPDATE usuarios.usuarios SET password_hash = :hash WHERE id = :id"
            );
            return $update->execute([':hash' => $nuevo_hash, ':id' => $id_usuario]) ? 'ok' : 'error';

        } catch (PDOException $e) {
            error_log('Perfil::cambiarPassword → ' . $e->getMessage());
            return 'error';
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  SUBIR AVATAR (archivo físico)
    // ══════════════════════════════════════════════════════════════════
    public function guardarArchivoAvatar(int $id_usuario, array $file)
    {
        try {
            if ($file['size'] > 3 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) return false;

            // Se valida el CONTENIDO (no el tipo que declara el navegador ni la extensión del nombre)
            $info = @getimagesize($file['tmp_name']);
            if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) return false;
            $img = @imagecreatefromstring((string)file_get_contents($file['tmp_name']));
            if (!$img) return false;

            // Re-codificado a JPEG cuadrado de 400px: solo se guardan píxeles, nunca el archivo original
            $lado = min(imagesx($img), imagesy($img));
            $cuad = imagecreatetruecolor(400, 400);
            imagefill($cuad, 0, 0, imagecolorallocate($cuad, 255, 255, 255));
            imagecopyresampled($cuad, $img, 0, 0, (int)((imagesx($img) - $lado) / 2), (int)((imagesy($img) - $lado) / 2), 400, 400, $lado, $lado);

            $carpeta = 'assets/img/avatars/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

            $nombre    = 'avatar_' . $id_usuario . '_' . time() . '.jpg';
            $ruta_dest = $carpeta . $nombre;

            $guardado = imagejpeg($cuad, $ruta_dest, 88);
            imagedestroy($img); imagedestroy($cuad);
            if (!$guardado) return false;

            // Borrar avatar anterior si era archivo subido
            $s = $this->pdo->prepare("SELECT configuracion_json FROM usuarios.usuarios WHERE id = :id");
            $s->execute([':id' => $id_usuario]);
            $r = $s->fetch(PDO::FETCH_ASSOC);
            if ($r) {
                $cfg = json_decode($r['configuracion_json'], true) ?? [];
                $ant = $cfg['avatar'] ?? null;
                if ($ant && strpos($ant, 'assets/img/avatars/') !== false && file_exists($ant)) {
                    @unlink($ant);
                }
            }

            $ok = $this->actualizarConfiguracion($id_usuario, ['avatar' => $ruta_dest]);
            return $ok ? $ruta_dest : false;

        } catch (Exception $e) {
            error_log('Perfil::guardarArchivoAvatar → ' . $e->getMessage());
            return false;
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  ELIMINAR CUENTA (soft delete)
    // ══════════════════════════════════════════════════════════════════
    public function eliminarCuenta(int $id_usuario): bool
    {
        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare(
                "DELETE FROM usuarios.preferencias WHERE usuario_id = :id"
            )->execute([':id' => $id_usuario]);

            $this->pdo->prepare(
                "UPDATE usuarios.usuarios
                 SET estado = 'eliminado',
                     email  = CONCAT(email, '_deleted_', EXTRACT(EPOCH FROM NOW())::bigint)
                 WHERE id = :id"
            )->execute([':id' => $id_usuario]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('Perfil::eliminarCuenta → ' . $e->getMessage());
            return false;
        }
    }
}