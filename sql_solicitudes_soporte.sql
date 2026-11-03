-- Solicitudes de soporte internas (cliente → administración).
-- model/soporte.php la crea automáticamente si no existe; este script es para crearla a mano.
CREATE TABLE IF NOT EXISTS usuarios.solicitudes_soporte (
    id           SERIAL PRIMARY KEY,
    usuario_id   INTEGER NOT NULL REFERENCES usuarios.usuarios(id) ON DELETE CASCADE,
    tipo         VARCHAR(40)  NOT NULL DEFAULT 'pago_no_confirmado',  -- pago_no_confirmado | consulta_pago | pago_rechazado | otro
    mensaje      TEXT         NOT NULL,
    estado       VARCHAR(20)  NOT NULL DEFAULT 'pendiente',           -- pendiente | en_revision | resuelta
    respuesta    TEXT,
    atendida_por INTEGER REFERENCES usuarios.usuarios(id) ON DELETE SET NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);
