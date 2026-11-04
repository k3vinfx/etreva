<?php
/*
 * model/consentimiento.php
 * ─────────────────────────────────────────────────────────────────
 * Constancia del consentimiento informado del turista:
 *   · 'reglamento': Reglamento de uso, privacidad y riesgos (al primer ingreso y en cada nueva versión).
 *   · 'riesgo':     aviso de actividades de riesgo (trekking, montañismo, downhill), una vez por categoría.
 *
 * Base legal (Bolivia):
 *   · Constitución Política del Estado, art. 21 (privacidad) y art. 130 (acción de protección de privacidad).
 *   · Ley 164 General de Telecomunicaciones y TIC y su reglamento, DS 1793 (consentimiento para datos personales).
 *   · Ley 548 (menores de edad), Ley 259 (alcohol), Ley 453 (derechos del consumidor), Ley 292 (turismo).
 *   · Turistas de la Unión Europea: RGPD, art. 6 y 7.
 *
 * Cada aceptación se guarda dos veces:
 *   1. Fila en usuarios.consentimientos (historial completo, nunca se sobrescribe): lo lista el administrador.
 *   2. En usuarios.usuarios.configuracion_json → "consentimientos": la constancia en el propio registro del usuario.
 */
require_once __DIR__ . '/database.php';

class Consentimiento
{
    public const VERSION = '2026-11';

    // Actividades que requieren aceptar el aviso de riesgo (id de categoría => clave)
    public const CATEGORIAS_RIESGO = [1 => 'trekking', 2 => 'montanismo', 4 => 'downhill'];

    private static bool $tablaLista = false;

    /** Crea la tabla si no existe (migración: database/migraciones/2026_11_consentimiento.sql) */
    public static function asegurarTabla(): void
    {
        if (self::$tablaLista) return;
        Database::Conectar()->exec(file_get_contents(__DIR__ . '/../database/migraciones/2026_11_consentimiento.sql'));
        self::$tablaLista = true;
    }

    /* ═══════════════════════ Reglamento ═══════════════════════ */

    /** ¿El usuario aceptó la versión vigente del reglamento? */
    public static function vigente($idUsuario): bool
    {
        $id = (int)$idUsuario;
        if ($id <= 0) return false;
        try {
            self::asegurarTabla();
            $st = Database::Conectar()->prepare(
                "SELECT 1 FROM usuarios.consentimientos
                  WHERE usuario_id = ? AND tipo = 'reglamento' AND version = ?
                    AND acepta_terminos AND acepta_datos AND acepta_riesgos LIMIT 1");
            $st->execute([$id, self::VERSION]);
            return (bool)$st->fetchColumn();
        } catch (Throwable $e) {
            error_log('Consentimiento::vigente ' . $e->getMessage());
            return true;   // ante un error de BD no se bloquea al usuario
        }
    }

    /** Guarda la aceptación del reglamento. Devuelve el id del registro. */
    public static function registrar(int $idUsuario, array $c, bool $esMenor): int
    {
        $datos = [
            'terminos'       => !empty($c['terminos']),
            'datos'          => !empty($c['datos']),
            'perfil_publico' => !empty($c['perfil_publico']),
            'riesgos'        => !empty($c['riesgos']),
            'tutor'          => $esMenor ? !empty($c['tutor']) : null,
            'es_menor'       => $esMenor,
        ];
        return self::guardar($idUsuario, 'reglamento', null, null, $datos);
    }

    /* ═══════════════════════ Aviso de riesgo ═══════════════════════ */

    /** Clave de riesgo de una atracción ('trekking', 'montanismo', 'downhill') o null si no aplica */
    public static function categoriaRiesgo($idAtraccion): ?string
    {
        try {
            $st = Database::Conectar()->prepare("SELECT atraccion_categoria_id FROM catalogo.atracciones WHERE id = ?");
            $st->execute([(int)$idAtraccion]);
            $cat = (int)$st->fetchColumn();
        } catch (Throwable $e) {
            $cat = intdiv((int)$idAtraccion, 1000);
        }
        return self::CATEGORIAS_RIESGO[$cat] ?? null;
    }

    /** ¿Aceptó el aviso de riesgo de esa categoría en la versión vigente? */
    public static function riesgoAceptado($idUsuario, string $categoria): bool
    {
        try {
            self::asegurarTabla();
            $st = Database::Conectar()->prepare(
                "SELECT 1 FROM usuarios.consentimientos
                  WHERE usuario_id = ? AND tipo = 'riesgo' AND categoria = ? AND version = ? AND acepta_riesgos LIMIT 1");
            $st->execute([(int)$idUsuario, $categoria, self::VERSION]);
            return (bool)$st->fetchColumn();
        } catch (Throwable $e) {
            error_log('Consentimiento::riesgoAceptado ' . $e->getMessage());
            return true;
        }
    }

    /** Categorías de riesgo ya aceptadas por el usuario (para el buscador) */
    public static function riesgosAceptados($idUsuario): array
    {
        try {
            self::asegurarTabla();
            $st = Database::Conectar()->prepare(
                "SELECT DISTINCT categoria FROM usuarios.consentimientos
                  WHERE usuario_id = ? AND tipo = 'riesgo' AND version = ? AND acepta_riesgos");
            $st->execute([(int)$idUsuario, self::VERSION]);
            return $st->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            return array_values(self::CATEGORIAS_RIESGO);
        }
    }

    public static function registrarRiesgo(int $idUsuario, string $categoria, ?int $idAtraccion): int
    {
        if (!in_array($categoria, self::CATEGORIAS_RIESGO, true)) throw new InvalidArgumentException('Categoría sin aviso de riesgo');
        return self::guardar($idUsuario, 'riesgo', $categoria, $idAtraccion, ['riesgos' => true]);
    }

    /* ═══════════════════════ Administrador ═══════════════════════ */

    /** Listado para el administrador, con filtros opcionales: tipo, texto (nombre/correo) */
    public static function listar(array $f = []): array
    {
        self::asegurarTabla();
        $where = ['1=1']; $p = [];
        if (!empty($f['tipo']))  { $where[] = 'c.tipo = ?'; $p[] = $f['tipo']; }
        if (!empty($f['texto'])) {
            $where[] = "(u.nombre ILIKE ? OR u.apellido ILIKE ? OR u.email ILIKE ?)";
            $t = '%' . $f['texto'] . '%'; array_push($p, $t, $t, $t);
        }
        $st = Database::Conectar()->prepare("
            SELECT c.id, c.usuario_id, u.nombre, u.apellido, u.email, c.tipo, c.version, c.categoria,
                   c.atraccion_id, a.nombre AS atraccion, c.acepta_terminos, c.acepta_datos, c.acepta_perfil_publico,
                   c.acepta_riesgos, c.autorizacion_tutor, c.es_menor, c.ip, c.navegador, c.aceptado_en
              FROM usuarios.consentimientos c
              JOIN usuarios.usuarios u ON u.id = c.usuario_id
              LEFT JOIN catalogo.atracciones a ON a.id = c.atraccion_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY c.aceptado_en DESC
             LIMIT 1000");
        $st->execute($p);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Totales para las tarjetas del administrador */
    public static function resumen(): array
    {
        self::asegurarTabla();
        $pdo = Database::Conectar();
        $clientes = (int)$pdo->query("SELECT COUNT(*) FROM usuarios.usuarios WHERE rol = 1")->fetchColumn();
        $st = $pdo->prepare("SELECT COUNT(DISTINCT usuario_id) FROM usuarios.consentimientos WHERE tipo = 'reglamento' AND version = ?");
        $st->execute([self::VERSION]);
        $aceptaron = (int)$st->fetchColumn();
        $riesgo = $pdo->query("SELECT categoria, COUNT(DISTINCT usuario_id) n FROM usuarios.consentimientos WHERE tipo = 'riesgo' GROUP BY categoria")
                      ->fetchAll(PDO::FETCH_KEY_PAIR);
        $menores = (int)$pdo->query("SELECT COUNT(DISTINCT usuario_id) FROM usuarios.consentimientos WHERE tipo = 'reglamento' AND es_menor")->fetchColumn();
        return ['clientes' => $clientes, 'aceptaron' => $aceptaron, 'pendientes' => max(0, $clientes - $aceptaron),
                'riesgo' => $riesgo, 'menores' => $menores, 'version' => self::VERSION];
    }

    /** ¿El usuario permitió que otros turistas vean su nombre en reseñas? (última aceptación del reglamento) */
    public static function perfilPublico($idUsuario): bool
    {
        try {
            self::asegurarTabla();
            $st = Database::Conectar()->prepare(
                "SELECT acepta_perfil_publico FROM usuarios.consentimientos
                  WHERE usuario_id = ? AND tipo = 'reglamento' ORDER BY aceptado_en DESC LIMIT 1");
            $st->execute([(int)$idUsuario]);
            return (bool)$st->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    /* ═══════════════════════ Interno ═══════════════════════ */

    /** Guarda en la tabla (historial) y en configuracion_json del usuario (constancia), en una transacción */
    private static function guardar(int $idUsuario, string $tipo, ?string $categoria, ?int $idAtraccion, array $d): int
    {
        self::asegurarTabla();
        $pdo = Database::Conectar();
        $ip  = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64);
        $nav = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
        $b = fn($v) => $v === null ? null : ($v ? 't' : 'f');

        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare("
                INSERT INTO usuarios.consentimientos
                    (usuario_id, tipo, version, categoria, atraccion_id, acepta_terminos, acepta_datos,
                     acepta_perfil_publico, acepta_riesgos, autorizacion_tutor, es_menor, ip, navegador)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id, aceptado_en");
            $st->execute([
                $idUsuario, $tipo, self::VERSION, $categoria, $idAtraccion,
                $b($d['terminos'] ?? null), $b($d['datos'] ?? null), $b($d['perfil_publico'] ?? false) ?? 'f',
                $b($d['riesgos'] ?? false), $b($d['tutor'] ?? null), $b($d['es_menor'] ?? false), $ip, $nav,
            ]);
            $fila = $st->fetch(PDO::FETCH_ASSOC);

            // Constancia en el JSON del usuario
            $registro = array_filter([
                'id_registro' => (int)$fila['id'], 'version' => self::VERSION, 'fecha' => $fila['aceptado_en'],
                'categoria' => $categoria, 'atraccion_id' => $idAtraccion, 'ip' => $ip,
            ] + $d, fn($v) => $v !== null);
            $ruta = $tipo === 'reglamento' ? '{consentimientos,reglamento}' : '{consentimientos,riesgos,' . $categoria . '}';
            $st = $pdo->prepare("
                UPDATE usuarios.usuarios SET configuracion_json =
                    jsonb_set(
                        jsonb_set(
                            jsonb_set(COALESCE(configuracion_json, '{}'::jsonb), '{consentimientos}',
                                      COALESCE(configuracion_json->'consentimientos', '{}'::jsonb), true),
                            '{consentimientos,riesgos}',
                            COALESCE(configuracion_json->'consentimientos'->'riesgos', '{}'::jsonb), true),
                        ?::text[], ?::jsonb, true)
                 WHERE id = ?");
            $st->execute([$ruta, json_encode($registro, JSON_UNESCAPED_UNICODE), $idUsuario]);

            $pdo->commit();
            return (int)$fila['id'];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
