<?php
/*
 * model/soporte.php
 * ─────────────────────────────────────────────────────────────────
 * Solicitudes de soporte internas (cliente → administración).
 * Estados: pendiente → en_revision → resuelta
 * La tabla se crea sola si no existe (ver también sql_solicitudes_soporte.sql).
 */
class Soporte
{
    private $pdo;

    public const TIPOS   = ['pago_no_confirmado', 'consulta_pago', 'pago_rechazado', 'otro'];
    public const ESTADOS = ['pendiente', 'en_revision', 'resuelta'];

    public function __CONSTRUCT()
    {
        $this->pdo = Database::Conectar();
        $this->asegurarTabla();
    }

    private function asegurarTabla(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios.solicitudes_soporte (
                id           SERIAL PRIMARY KEY,
                usuario_id   INTEGER NOT NULL REFERENCES usuarios.usuarios(id) ON DELETE CASCADE,
                tipo         VARCHAR(40)  NOT NULL DEFAULT 'pago_no_confirmado',
                mensaje      TEXT         NOT NULL,
                estado       VARCHAR(20)  NOT NULL DEFAULT 'pendiente',
                respuesta    TEXT,
                atendida_por INTEGER REFERENCES usuarios.usuarios(id) ON DELETE SET NULL,
                created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /* ── Cliente ─────────────────────────────────────────────────── */

    // Solicitud abierta (pendiente o en revisión) más reciente del usuario
    public function abiertaDeUsuario(int $usuarioId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM usuarios.solicitudes_soporte
            WHERE usuario_id = ? AND estado <> 'resuelta'
            ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function listarDeUsuario(int $usuarioId, int $limite = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, tipo, mensaje, estado, respuesta, created_at, updated_at
            FROM usuarios.solicitudes_soporte
            WHERE usuario_id = ?
            ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$usuarioId, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(int $usuarioId, string $tipo, string $mensaje): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios.solicitudes_soporte (usuario_id, tipo, mensaje)
            VALUES (?, ?, ?) RETURNING id");
        $stmt->execute([$usuarioId, $tipo, $mensaje]);
        return (int)$stmt->fetchColumn();
    }

    /* ── Administración ──────────────────────────────────────────── */

    public function listarAdmin(?string $estado = null): array
    {
        $sql = "SELECT s.*, u.nombre, u.apellido, u.email,
                       u.configuracion_json->'registro_pago'->>'estado'     AS pago_estado,
                       u.configuracion_json->'registro_pago'->>'tipo_plan'  AS pago_plan,
                       u.configuracion_json->'registro_pago'->>'fecha_pago' AS pago_fecha
                FROM usuarios.solicitudes_soporte s
                JOIN usuarios.usuarios u ON u.id = s.usuario_id";
        $params = [];
        if ($estado && in_array($estado, self::ESTADOS, true)) {
            $sql .= " WHERE s.estado = ?";
            $params[] = $estado;
        }
        $sql .= " ORDER BY (s.estado = 'resuelta'), s.created_at DESC LIMIT 200";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarAbiertas(): int
    {
        return (int)$this->pdo
            ->query("SELECT COUNT(*) FROM usuarios.solicitudes_soporte WHERE estado <> 'resuelta'")
            ->fetchColumn();
    }

    public function actualizar(int $id, string $estado, ?string $respuesta, int $adminId): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuarios.solicitudes_soporte
            SET estado = ?, respuesta = COALESCE(NULLIF(?, ''), respuesta),
                atendida_por = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        return $stmt->execute([$estado, $respuesta, $adminId, $id]);
    }
}
