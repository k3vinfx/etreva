-- Constancia de consentimientos del turista (versión 2026-11)
--   tipo 'reglamento': Reglamento de uso, privacidad y riesgos (primer ingreso / nueva versión)
--   tipo 'riesgo':     aviso de actividad de riesgo por categoría (trekking, montanismo, downhill)
-- CPE art. 21 y 130 · Ley 164 y DS 1793 · Ley 548 · Ley 259 · Ley 453 · Ley 292 · RGPD (UE)
-- model/consentimiento.php ejecuta este archivo si la tabla no existe. La última aceptación de cada
-- tipo también queda en usuarios.usuarios.configuracion_json -> 'consentimientos'.

CREATE TABLE IF NOT EXISTS usuarios.consentimientos (
    id                    SERIAL PRIMARY KEY,
    usuario_id            INTEGER NOT NULL REFERENCES usuarios.usuarios(id) ON DELETE CASCADE,
    tipo                  VARCHAR(20) NOT NULL CHECK (tipo IN ('reglamento', 'riesgo')),
    version               VARCHAR(20) NOT NULL,
    categoria             VARCHAR(30),
    atraccion_id          INTEGER,
    acepta_terminos       BOOLEAN,
    acepta_datos          BOOLEAN,
    acepta_perfil_publico BOOLEAN NOT NULL DEFAULT FALSE,
    acepta_riesgos        BOOLEAN NOT NULL DEFAULT FALSE,
    autorizacion_tutor    BOOLEAN,
    es_menor              BOOLEAN NOT NULL DEFAULT FALSE,
    ip                    VARCHAR(64),
    navegador             VARCHAR(255),
    aceptado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS consentimientos_usuario_idx ON usuarios.consentimientos (usuario_id, tipo, version);
CREATE INDEX IF NOT EXISTS consentimientos_fecha_idx   ON usuarios.consentimientos (aceptado_en DESC);
