<?php
/*
 * model/gestionUsuarios.php
 * Gestión de usuarios internos: Administrador (rol 2) y Finanzas (rol 3)
 *
 * Estructura configuracion_json para roles 2 y 3:
 * {
 *   "token_verificacion_correo": { "token": "...", "estado": "verificado", "fecha_creacion": "...", "fecha_verificacion": "..." },
 *   "restauracion_cuenta": { "token": "...", "fecha_creacion": "...", "expira": "...", "estado": "pendiente|usado" },
 *   "perfil": { "avatar": "preset_0", "descripcion": "...", "departamento": "..." },
 *   "permisos_extra": [],
 *   "ultimo_acceso": "..."
 * }
 *
 * historial_json (array de eventos):
 * [
 *   { "evento": "creacion|cambio_estado|cambio_rol|restauracion|login", "descripcion": "...", "fecha": "...", "por_admin_id": null }
 * ]
 */

date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminGestionUsuarios
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
    //  LISTAR usuarios internos (rol 2 y 3)
    //  Soporta búsqueda, filtro por rol y paginación
    // ══════════════════════════════════════════════════════════════════
    public function listar(string $q = '', int $rol = 0, string $estado = '', int $page = 1, int $perPage = 15): array
    {
        try {
            $where  = ["u.rol IN (2, 3)"];
            $params = [];

            if (!empty($q)) {
                $where[]  = "(u.nombre ILIKE :q OR u.apellido ILIKE :q OR u.email ILIKE :q)";
                $params[':q'] = '%' . $q . '%';
            }
            if ($rol === 2 || $rol === 3) {
                $where[]      = "u.rol = :rol";
                $params[':rol'] = $rol;
            }
            if (!empty($estado)) {
                $where[]        = "u.estado = :estado";
                $params[':estado'] = $estado;
            }

            $whereSQL = implode(' AND ', $where);

            // Total
            $sqlCount = "SELECT COUNT(*) FROM usuarios.usuarios u WHERE $whereSQL";
            $stmCount = $this->pdo->prepare($sqlCount);
            $stmCount->execute($params);
            $total = (int)$stmCount->fetchColumn();

            $pages  = max(1, (int)ceil($total / $perPage));
            $offset = ($page - 1) * $perPage;

            $sql = "SELECT
                        u.id,
                        u.nombre,
                        u.apellido,
                        u.email,
                        u.telefono,
                        u.rol,
                        u.estado,
                        u.fecha_registro,
                        u.configuracion_json,
                        u.historial_json
                    FROM usuarios.usuarios u
                    WHERE $whereSQL
                    ORDER BY u.fecha_registro DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Enriquecer con datos del JSON
            foreach ($rows as &$row) {
                $cfg = !empty($row['configuracion_json'])
                    ? json_decode($row['configuracion_json'], true)
                    : [];

                $row['verificado']    = ($cfg['token_verificacion_correo']['estado'] ?? '') === 'verificado';
                $row['ultimo_acceso'] = $cfg['ultimo_acceso'] ?? null;
                $row['avatar']        = $cfg['perfil']['avatar'] ?? 'preset_0';
                $row['departamento']  = $cfg['perfil']['departamento'] ?? null;

                // Historial (últimos 5 eventos)
                $hist = !empty($row['historial_json'])
                    ? json_decode($row['historial_json'], true)
                    : [];
                $row['historial_reciente'] = array_slice(array_reverse($hist), 0, 5);

                $row['super_admin'] = (int)$row['rol'] === 2 && !empty($cfg['super_admin']);
                unset($row['configuracion_json'], $row['historial_json']);
            }

            return [
                'success'  => true,
                'usuarios' => $rows,
                'total'    => $total,
                'pages'    => $pages,
                'page'     => $page,
            ];

        } catch (\PDOException $e) {
            error_log("Error listar gestionUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al listar usuarios'];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  VER detalle de un usuario interno
    // ══════════════════════════════════════════════════════════════════
    public function ver(int $id): array
    {
        try {
            $sql = "SELECT id, nombre, apellido, email, telefono, genero,
                           nacionalidad, fecha_nacimiento, rol, estado,
                           fecha_registro, configuracion_json, historial_json
                    FROM usuarios.usuarios
                    WHERE id = ? AND rol IN (2, 3)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }

            $cfg  = !empty($row['configuracion_json']) ? json_decode($row['configuracion_json'], true) : [];
            $hist = !empty($row['historial_json'])     ? json_decode($row['historial_json'], true)     : [];

            $row['verificado']          = ($cfg['token_verificacion_correo']['estado'] ?? '') === 'verificado';
            $row['fecha_verificacion']  = $cfg['token_verificacion_correo']['fecha_verificacion'] ?? null;
            $row['ultimo_acceso']       = $cfg['ultimo_acceso'] ?? null;
            $row['avatar']              = $cfg['perfil']['avatar'] ?? 'preset_0';
            $row['departamento']        = $cfg['perfil']['departamento'] ?? null;
            $row['descripcion']         = $cfg['perfil']['descripcion'] ?? null;
            $row['permisos_extra']      = $cfg['permisos_extra'] ?? [];
            $row['super_admin']         = (int)$row['rol'] === 2 && !empty($cfg['super_admin']);
            $row['historial']           = array_reverse($hist);

            unset($row['configuracion_json'], $row['historial_json']);

            return ['success' => true, 'usuario' => $row];

        } catch (\PDOException $e) {
            error_log("Error ver gestionUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al obtener el usuario'];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  CREAR usuario interno (admin o finanzas)
    //  Genera token, envía correo de verificación
    // ══════════════════════════════════════════════════════════════════
    public function crear(array $data, int $adminId): array
    {
        try {
            if ((int)($data['rol'] ?? 0) === 2 && !$this->esSuper($adminId))
                return ['success' => false, 'message' => 'Solo un super administrador puede crear administradores.'];
            // Validar rol permitido
            $rol = (int)($data['rol'] ?? 0);
            if (!in_array($rol, [2, 3])) {
                return ['success' => false, 'message' => 'Rol no permitido'];
            }

            // Verificar email único
            if ($this->existeEmail($data['email'])) {
                return ['success' => false, 'message' => 'El correo ya está registrado'];
            }

            $token = bin2hex(random_bytes(32));
            $now   = date('Y-m-d H:i:s');

            $configJson = json_encode([
                'token_verificacion_correo' => [
                    'token'              => $token,
                    'estado'             => 'pendiente',
                    'fecha_creacion'     => $now,
                    'fecha_verificacion' => null,
                ],
                'restauracion_cuenta' => null,
                'perfil' => [
                    'avatar'       => 'preset_0',
                    'descripcion'  => $data['descripcion'] ?? null,
                    'departamento' => $data['departamento'] ?? ($rol === 2 ? 'Administración' : 'Finanzas'),
                ],
                'permisos_extra' => [],
                'ultimo_acceso'  => null,
            ]);

            $historialJson = json_encode([[
                'evento'      => 'creacion',
                'descripcion' => 'Cuenta creada por administrador',
                'fecha'       => $now,
                'por_admin_id'=> $adminId,
            ]]);

            $sql = "INSERT INTO usuarios.usuarios
                        (email, password_hash, nombre, apellido, fecha_nacimiento,
                         genero, nacionalidad, telefono, rol, estado,
                         configuracion_json, historial_json, fecha_registro)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente_correo', ?, ?, NOW())";

            $this->pdo->prepare($sql)->execute([
                trim($data['email']),
                password_hash($data['password'], PASSWORD_DEFAULT),
                trim($data['nombre']),
                trim($data['apellido']),
                $data['fecha_nacimiento'] ?? null,
                $data['genero']           ?? null,
                $data['nacionalidad']     ?? null,
                $data['telefono']         ?? null,
                $rol,
                $configJson,
                $historialJson,
            ]);

            $nuevoId = $this->pdo->lastInsertId();

            // Enviar correo de verificación
            $enviado = $this->enviarCorreoVerificacion($data['email'], $token, $data['nombre'], $rol);

            return [
                'success'        => true,
                'message'        => 'Usuario creado. ' . ($enviado ? 'Se envió correo de verificación.' : 'No se pudo enviar el correo.'),
                'id'             => $nuevoId,
                'correo_enviado' => $enviado,
            ];

        } catch (\Exception $e) {
            error_log("Error crear gestionUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear el usuario'];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  EDITAR datos básicos de un usuario interno
    // ══════════════════════════════════════════════════════════════════
    public function editar(int $id, array $data, int $adminId): array
    {
        try {
            if ($err = $this->autorizar($id, $adminId, 'editar')) return $err;
            $permitidos = ['nombre','apellido','telefono','nacionalidad','genero','fecha_nacimiento','departamento','descripcion'];
            $campos  = [];
            $params  = [':id' => $id];

            foreach ($permitidos as $campo) {
                if (!isset($data[$campo])) continue;

                // departamento y descripcion van al JSON, no a la tabla
                if (in_array($campo, ['departamento','descripcion'])) continue;

                $campos[]       = "$campo = :$campo";
                $params[":$campo"] = trim($data[$campo]);
            }

            if (!empty($campos)) {
                $sql  = "UPDATE usuarios.usuarios SET " . implode(', ', $campos) . " WHERE id = :id AND rol IN (2,3)";
                $stmt = $this->pdo->prepare($sql);
                if (!$stmt->execute($params)) {
                    return ['success' => false, 'message' => 'Error al actualizar datos'];
                }
            }

            // Actualizar perfil en el JSON
            if (isset($data['departamento']) || isset($data['descripcion'])) {
                $this->actualizarPerfil($id, [
                    'departamento' => $data['departamento'] ?? null,
                    'descripcion'  => $data['descripcion']  ?? null,
                ]);
            }

            $this->agregarHistorial($id, [
                'evento'       => 'edicion',
                'descripcion'  => 'Datos editados por administrador',
                'por_admin_id' => $adminId,
            ]);

            return ['success' => true, 'message' => 'Usuario actualizado correctamente'];

        } catch (\PDOException $e) {
            error_log("Error editar gestionUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el usuario'];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  SUPER ADMINISTRADOR Y PERMISOS
    //  - Super admin = administrador (rol 2) con configuracion_json.super_admin = true.
    //  - Solo un super admin puede crear administradores, cambiar roles y editar, resetear,
    //    suspender o desactivar a otro administrador; y dar o quitar el rango de super admin.
    //  - Cualquier administrador gestiona las cuentas de Finanzas.
    //  - Nadie puede suspenderse, desactivarse ni cambiarse el rol a sí mismo.
    //  - Un super admin solo lo gestiona otro super admin, y siempre queda al menos uno activo.
    // ══════════════════════════════════════════════════════════════════
    public function esSuper(int $id): bool
    {
        $st = $this->pdo->prepare("SELECT 1 FROM usuarios.usuarios
                                   WHERE id = ? AND rol = 2 AND (configuracion_json->>'super_admin') = 'true'");
        $st->execute([$id]);
        return (bool)$st->fetchColumn();
    }

    private function contarSupers(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM usuarios.usuarios
            WHERE rol = 2 AND estado = 'activo' AND (configuracion_json->>'super_admin') = 'true'")->fetchColumn();
    }

    /** Qué puede hacer $adminId sobre la cuenta interna $objetivo (con su rol y si es super) */
    public function permisos(int $objetivo, int $rolObjetivo, bool $objetivoSuper, int $adminId, ?bool $soySuper = null): array
    {
        $soySuper ??= $this->esSuper($adminId);
        $yo = $objetivo === $adminId;
        $sobreFinanzas = $rolObjetivo === 3;
        $gestiona = $soySuper ? true : $sobreFinanzas;     // admin común: solo Finanzas
        return [
            'editar'   => $yo || $gestiona,
            'password' => $yo || $gestiona,
            'rol'      => !$yo && $soySuper && !$objetivoSuper,
            'estado'   => !$yo && $gestiona && !$objetivoSuper,
            'eliminar' => !$yo && $gestiona && !$objetivoSuper,
            'super'    => !$yo && $soySuper && $rolObjetivo === 2,
        ];
    }

    /** Devuelve null si está permitido, o la respuesta de error */
    private function autorizar(int $objetivo, int $adminId, string $accion): ?array
    {
        $st = $this->pdo->prepare("SELECT rol, (configuracion_json->>'super_admin') = 'true' AS sup FROM usuarios.usuarios WHERE id = ? AND rol IN (2,3)");
        $st->execute([$objetivo]);
        $r = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$r) return ['success' => false, 'message' => 'Usuario no encontrado'];
        $p = $this->permisos($objetivo, (int)$r['rol'], (bool)$r['sup'], $adminId);
        if (!empty($p[$accion])) return null;
        $motivos = [
            'rol'      => 'Solo un super administrador puede cambiar roles (y no el propio).',
            'estado'   => $objetivo === $adminId ? 'No puedes suspender ni desactivar tu propia cuenta.'
                        : ((bool)$r['sup'] ? 'Primero quita el rango de super administrador a esta cuenta.' : 'Solo un super administrador puede suspender a otro administrador.'),
            'eliminar' => $objetivo === $adminId ? 'No puedes desactivar tu propia cuenta.'
                        : ((bool)$r['sup'] ? 'Primero quita el rango de super administrador a esta cuenta.' : 'Solo un super administrador puede desactivar a otro administrador.'),
            'super'    => 'Solo un super administrador puede dar o quitar ese rango (y no a sí mismo).',
        ];
        return ['success' => false, 'message' => $motivos[$accion] ?? 'Solo un super administrador puede modificar a otro administrador.'];
    }

    /** Da o quita el rango de super administrador */
    public function cambiarSuper(int $id, bool $super, int $adminId): array
    {
        if ($err = $this->autorizar($id, $adminId, 'super')) return $err;
        if (!$super && $this->esSuper($id) && $this->contarSupers() <= 1)
            return ['success' => false, 'message' => 'Debe quedar al menos un super administrador activo.'];
        $this->pdo->prepare("UPDATE usuarios.usuarios
            SET configuracion_json = jsonb_set(COALESCE(configuracion_json, '{}'::jsonb), '{super_admin}', ?::jsonb), updated_at = NOW()
            WHERE id = ? AND rol = 2")->execute([$super ? 'true' : 'false', $id]);
        $this->agregarHistorial($id, [
            'evento'       => $super ? 'super_admin_otorgado' : 'super_admin_retirado',
            'descripcion'  => $super ? 'Se le otorgó el rango de super administrador' : 'Se le retiró el rango de super administrador',
            'por_admin_id' => $adminId,
        ]);
        return ['success' => true, 'message' => $super ? 'Ahora es super administrador' : 'Ya no es super administrador'];
    }

    // ══════════════════════════════════════════════════════════════════
    //  CAMBIAR ROL (solo entre 2 y 3)
    // ══════════════════════════════════════════════════════════════════
    public function cambiarRol(int $id, int $nuevoRol, int $adminId): array
    {
        try {
            if ($err = $this->autorizar($id, $adminId, 'rol')) return $err;
            if (!in_array($nuevoRol, [2, 3])) {
                return ['success' => false, 'message' => 'Rol no permitido'];
            }

            // Obtener rol actual
            $stmt = $this->pdo->prepare("SELECT rol FROM usuarios.usuarios WHERE id = ? AND rol IN (2,3)");
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }

            if ((int)$row['rol'] === $nuevoRol) {
                return ['success' => false, 'message' => 'El usuario ya tiene ese rol'];
            }

            $this->pdo->prepare("UPDATE usuarios.usuarios SET rol = ? WHERE id = ?")->execute([$nuevoRol, $id]);

            $this->agregarHistorial($id, [
                'evento'       => 'cambio_rol',
                'descripcion'  => "Rol cambiado de {$row['rol']} a {$nuevoRol}",
                'por_admin_id' => $adminId,
            ]);

            return ['success' => true, 'message' => 'Rol actualizado correctamente'];

        } catch (\PDOException $e) {
            error_log("Error cambiarRol gestionUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el rol'];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  CAMBIAR ESTADO (activo, inactivo, suspendido)
    // ══════════════════════════════════════════════════════════════════
    public function cambiarEstado(int $id, string $nuevoEstado, string $motivo, int $adminId): array
    {
        try {
            if ($err = $this->autorizar($id, $adminId, 'estado')) return $err;
            $estadosValidos = ['activo', 'inactivo', 'suspendido'];
            if (!in_array($nuevoEstado, $estadosValidos)) {
                return ['success' => false, 'message' => 'Estado no válido'];
            }

            $stmt = $this->pdo->prepare(
                "UPDATE usuarios.usuarios SET estado = ? WHERE id = ? AND rol IN (2,3)"
            );
            $stmt->execute([$nuevoEstado, $id]);

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Usuario no encontrado o sin cambios'];
            }

            $this->agregarHistorial($id, [
                'evento'       => 'cambio_estado',
                'descripcion'  => "Estado cambiado a '{$nuevoEstado}'. Motivo: {$motivo}",
                'por_admin_id' => $adminId,
            ]);

            return ['success' => true, 'message' => "Estado actualizado a '{$nuevoEstado}'"];

        } catch (\PDOException $e) {
            error_log("Error cambiarEstado gestionUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el estado'];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  RESETEAR CONTRASEÑA (admin la cambia directamente o envía token)
    //  Modo 'directa': el admin establece nueva contraseña
    //  Modo 'correo':  se genera token y se envía al usuario
    // ══════════════════════════════════════════════════════════════════
    public function resetearPassword(int $id, string $modo, int $adminId, string $nuevaPass = ''): array
    {
        try {
            if ($err = $this->autorizar($id, $adminId, 'password')) return $err;
            $stmt = $this->pdo->prepare(
                "SELECT email, nombre, configuracion_json FROM usuarios.usuarios WHERE id = ? AND rol IN (2,3)"
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }

            if ($modo === 'directa') {
                if (strlen($nuevaPass) < 8) {
                    return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres'];
                }
                $hash = password_hash($nuevaPass, PASSWORD_DEFAULT);
                $this->pdo->prepare("UPDATE usuarios.usuarios SET password_hash = ? WHERE id = ?")->execute([$hash, $id]);

                $this->agregarHistorial($id, [
                    'evento'       => 'reset_password',
                    'descripcion'  => 'Contraseña reseteada directamente por administrador',
                    'por_admin_id' => $adminId,
                ]);

                return ['success' => true, 'message' => 'Contraseña actualizada correctamente'];
            }

            // Modo 'correo': generar token de restauración
            $token = bin2hex(random_bytes(32));
            $now   = date('Y-m-d H:i:s');
            $expira = date('Y-m-d H:i:s', strtotime('+2 hours'));

            $cfg = !empty($row['configuracion_json']) ? json_decode($row['configuracion_json'], true) : [];
            $cfg['restauracion_cuenta'] = [
                'token'        => $token,
                'fecha_creacion' => $now,
                'expira'       => $expira,
                'estado'       => 'pendiente',
            ];

            $this->pdo->prepare(
                "UPDATE usuarios.usuarios SET configuracion_json = ?, estado = 'restauracion_cuenta' WHERE id = ?"
            )->execute([json_encode($cfg), $id]);

            $enviado = $this->enviarCorreoRestauracion($row['email'], $token, $row['nombre']);

            $this->agregarHistorial($id, [
                'evento'       => 'restauracion',
                'descripcion'  => 'Solicitud de restauración de contraseña iniciada por administrador',
                'por_admin_id' => $adminId,
            ]);

            return [
                'success' => $enviado,
                'message' => $enviado
                    ? 'Se envió el enlace de restauración al correo del usuario'
                    : 'Error al enviar el correo de restauración',
            ];

        } catch (\Exception $e) {
            error_log("Error resetearPassword gestionUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al resetear contraseña'];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  REENVIAR correo de verificación
    // ══════════════════════════════════════════════════════════════════
    public function reenviarVerificacion(int $id, int $adminId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT email, nombre, configuracion_json FROM usuarios.usuarios WHERE id = ? AND rol IN (2,3)"
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }

            $token = bin2hex(random_bytes(32));
            $cfg   = !empty($row['configuracion_json']) ? json_decode($row['configuracion_json'], true) : [];

            $cfg['token_verificacion_correo'] = [
                'token'              => $token,
                'estado'             => 'pendiente',
                'fecha_creacion'     => date('Y-m-d H:i:s'),
                'fecha_verificacion' => null,
            ];

            $this->pdo->prepare(
                "UPDATE usuarios.usuarios SET configuracion_json = ?, estado = 'pendiente_correo' WHERE id = ?"
            )->execute([json_encode($cfg), $id]);

            $rol = $this->getRol($id);
            $enviado = $this->enviarCorreoVerificacion($row['email'], $token, $row['nombre'], $rol);

            $this->agregarHistorial($id, [
                'evento'       => 'reenvio_verificacion',
                'descripcion'  => 'Correo de verificación reenviado por administrador',
                'por_admin_id' => $adminId,
            ]);

            return [
                'success' => $enviado,
                'message' => $enviado
                    ? 'Correo de verificación reenviado'
                    : 'Error al enviar el correo',
            ];

        } catch (\Exception $e) {
            error_log("Error reenviarVerificacion gestionUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno'];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  ELIMINAR (soft delete → estado = 'eliminado') 
    //  Solo el superadmin debería poder hacerlo; el rol 2 puede hacerlo
    // ══════════════════════════════════════════════════════════════════
    public function eliminar(int $id, int $adminId): array
    {
        try {
            if ($err = $this->autorizar($id, $adminId, 'eliminar')) return $err;
            $stmt = $this->pdo->prepare(
                "SELECT id FROM usuarios.usuarios WHERE id = ? AND rol IN (2,3)"
            );
            $stmt->execute([$id]);

            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }

            // No puede eliminarse a sí mismo
            if ($id === $adminId) {
                return ['success' => false, 'message' => 'No puedes eliminar tu propia cuenta'];
            }

            $this->pdo->prepare(
                "UPDATE usuarios.usuarios SET estado = 'inactivo' WHERE id = ?"
            )->execute([$id]);

            $this->agregarHistorial($id, [
                'evento'       => 'desactivacion',
                'descripcion'  => 'Cuenta desactivada permanentemente por administrador',
                'por_admin_id' => $adminId,
            ]);

            return ['success' => true, 'message' => 'Usuario desactivado correctamente'];

        } catch (\PDOException $e) {
            error_log("Error eliminar gestionUsuarios: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar el usuario'];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  STATS para el panel de gestión
    // ══════════════════════════════════════════════════════════════════
    public function obtenerStats(): array
    {
        try {
            $total = (int)$this->pdo->query(
                "SELECT COUNT(*) FROM usuarios.usuarios WHERE rol IN (2,3)"
            )->fetchColumn();

            $admins = (int)$this->pdo->query(
                "SELECT COUNT(*) FROM usuarios.usuarios WHERE rol = 2"
            )->fetchColumn();

            $finanzas = (int)$this->pdo->query(
                "SELECT COUNT(*) FROM usuarios.usuarios WHERE rol = 3"
            )->fetchColumn();

            $activos = (int)$this->pdo->query(
                "SELECT COUNT(*) FROM usuarios.usuarios WHERE rol IN (2,3) AND estado = 'activo'"
            )->fetchColumn();

            $pendientes = (int)$this->pdo->query(
                "SELECT COUNT(*) FROM usuarios.usuarios WHERE rol IN (2,3) AND estado = 'pendiente_correo'"
            )->fetchColumn();

            return [
                'success'    => true,
                'total'      => $total,
                'admins'     => $admins,
                'finanzas'   => $finanzas,
                'activos'    => $activos,
                'pendientes' => $pendientes,
            ];

        } catch (\PDOException $e) {
            error_log("Error obtenerStats gestionUsuarios: " . $e->getMessage());
            return ['success' => false];
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  HELPERS PRIVADOS
    // ══════════════════════════════════════════════════════════════════
    private function existeEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios.usuarios WHERE email = ?");
        $stmt->execute([trim($email)]);
        return $stmt->rowCount() > 0;
    }

    private function getRol(int $id): int
    {
        $stmt = $this->pdo->prepare("SELECT rol FROM usuarios.usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return (int)($stmt->fetchColumn() ?: 0);
    }

    private function actualizarPerfil(int $id, array $datosPerfil): void
    {
        $stmt = $this->pdo->prepare(
            "SELECT configuracion_json FROM usuarios.usuarios WHERE id = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $cfg = !empty($row['configuracion_json']) ? json_decode($row['configuracion_json'], true) : [];
        if (!isset($cfg['perfil'])) $cfg['perfil'] = [];

        foreach ($datosPerfil as $k => $v) {
            if ($v !== null) $cfg['perfil'][$k] = $v;
        }

        $this->pdo->prepare(
            "UPDATE usuarios.usuarios SET configuracion_json = ? WHERE id = ?"
        )->execute([json_encode($cfg), $id]);
    }

    private function agregarHistorial(int $id, array $evento): void
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT historial_json FROM usuarios.usuarios WHERE id = ?"
            );
            $stmt->execute([$id]);
            $row  = $stmt->fetch(\PDO::FETCH_ASSOC);
            $hist = !empty($row['historial_json']) ? json_decode($row['historial_json'], true) : [];

            $hist[] = array_merge($evento, ['fecha' => date('Y-m-d H:i:s')]);

            $this->pdo->prepare(
                "UPDATE usuarios.usuarios SET historial_json = ? WHERE id = ?"
            )->execute([json_encode($hist), $id]);

        } catch (\PDOException $e) {
            error_log("Error agregarHistorial: " . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  CORREOS
    // ══════════════════════════════════════════════════════════════════
    private function enviarCorreoVerificacion(string $email, string $token, string $nombre, int $rol): bool
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;

            $mail->setFrom('k3vinfx@gmail.com', 'Etreva Travel');
            $mail->addAddress($email);

            $link     = "http://localhost/etreva/index.php?c=usuarios&a=activar&token=" . $token;
            $rolNombre = $rol === 2 ? 'Administrador' : 'Finanzas';

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Activa tu cuenta de ' . $rolNombre . ' — Etreva Travel';
            $mail->Body    = "
                <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;'>
                    <h2 style='color:#e55a2b;'>Bienvenido a Etreva Travel</h2>
                    <p>Hola <strong>{$nombre}</strong>, tu cuenta de <strong>{$rolNombre}</strong> ha sido creada.</p>
                    <p>Haz clic en el siguiente enlace para verificar tu correo y activar tu cuenta:</p>
                    <a href='{$link}' style='display:inline-block;background:#e55a2b;color:#fff;
                       padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                       Activar cuenta
                    </a>
                    <p style='margin-top:20px;font-size:12px;color:#888;'>
                        Si no esperabas este correo, puedes ignorarlo.
                    </p>
                </div>";

            $mail->send();
            return true;

        } catch (\Exception $e) {
            error_log("Error correo verificación gestionUsuarios: " . $e->getMessage());
            return false;
        }
    }

    private function enviarCorreoRestauracion(string $email, string $token, string $nombre): bool
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;

            $mail->setFrom('k3vinfx@gmail.com', 'Etreva Travel');
            $mail->addAddress($email);

            $link = "http://localhost/etreva/index.php?c=usuarios&a=restablecer&token=" . $token;

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Restablecer contraseña — Etreva Travel';
            $mail->Body    = "
                <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;'>
                    <h2 style='color:#e55a2b;'>Restablecer contraseña</h2>
                    <p>Hola <strong>{$nombre}</strong>, se solicitó el restablecimiento de tu contraseña.</p>
                    <p>Este enlace expira en <strong>2 horas</strong>:</p>
                    <a href='{$link}' style='display:inline-block;background:#e55a2b;color:#fff;
                       padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                       Restablecer contraseña
                    </a>
                    <p style='margin-top:20px;font-size:12px;color:#888;'>
                        Si no solicitaste esto, ignora este mensaje.
                    </p>
                </div>";

            $mail->send();
            return true;

        } catch (\Exception $e) {
            error_log("Error correo restauración gestionUsuarios: " . $e->getMessage());
            return false;
        }
    }
}