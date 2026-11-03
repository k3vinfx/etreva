-- ============================================================================
--  Atracciones multi-idioma (es · en · pt · fr · de)
--
--  catalogo.atracciones            → datos comunes + textos en ESPAÑOL (fuente)
--  catalogo.atracciones_traducciones → solo los textos traducibles por idioma
--  catalogo.atracciones_i18n(idioma) → atracciones con los textos en ese idioma.
--      Si falta una traducción usa inglés y luego español: nunca queda vacío.
--      Fotos, coordenadas, horarios y precios salen siempre de la tabla base,
--      así no se desincronizan entre idiomas.
--
--  Idempotente: se puede ejecutar varias veces.
-- ============================================================================
SET client_encoding = 'UTF8';

CREATE TABLE IF NOT EXISTS catalogo.atracciones_traducciones (
    atraccion_id    INTEGER     NOT NULL REFERENCES catalogo.atracciones(id) ON DELETE CASCADE,
    idioma          VARCHAR(5)  NOT NULL CHECK (idioma IN ('en', 'pt', 'fr', 'de')),
    nombre          TEXT,
    descripcion     TEXT,
    direccion       TEXT,
    tiempo_estimado JSONB,          -- {"visita": "1 hour", "traslado": "15 minutes"}
    ubicacion       JSONB,          -- textos extra de ubicación (p. ej. {"zona": "..."}); las rutas de fotos NO van aquí
    origen          VARCHAR(20) NOT NULL DEFAULT 'automatica', -- 'manual' | 'automatica'
    updated_at      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (atraccion_id, idioma)
);

-- Inglés: se migra la traducción manual que ya existía en catalogo.atracciones_en
INSERT INTO catalogo.atracciones_traducciones
       (atraccion_id, idioma, nombre, descripcion, direccion, tiempo_estimado, ubicacion, origen)
SELECT e.id, 'en', e.nombre, e.descripcion, e.direccion, e.tiempo_estimado,
       NULLIF(COALESCE(e.ubicacion, '{}'::jsonb) - 'rutas', '{}'::jsonb), 'manual'
FROM catalogo.atracciones_en e
JOIN catalogo.atracciones a ON a.id = e.id
ON CONFLICT (atraccion_id, idioma) DO NOTHING;

CREATE OR REPLACE FUNCTION catalogo.atracciones_i18n(p_idioma TEXT)
RETURNS SETOF catalogo.atracciones
LANGUAGE sql STABLE AS $$
    SELECT a.id,
           COALESCE(t.nombre,      en.nombre,      a.nombre),
           COALESCE(t.descripcion, en.descripcion, a.descripcion),
           COALESCE(a.ubicacion, '{}'::jsonb) || COALESCE(t.ubicacion, en.ubicacion, '{}'::jsonb),
           a.latitud, a.longitud,
           COALESCE(t.direccion,   en.direccion,   a.direccion),
           a.ciudad, a.pais, a.horario_apertura, a.horario_cierre, a.dias_operacion,
           a.precio_entrada, a.moneda,
           COALESCE(t.tiempo_estimado, en.tiempo_estimado, a.tiempo_estimado),
           a.puntuacion_promedio, a.estado, a.created_at, a.updated_at, a.atraccion_categoria_id
    FROM catalogo.atracciones a
    LEFT JOIN catalogo.atracciones_traducciones t
           ON t.atraccion_id = a.id AND t.idioma = p_idioma
    LEFT JOIN catalogo.atracciones_traducciones en
           ON en.atraccion_id = a.id AND en.idioma = 'en' AND p_idioma NOT IN ('es', 'en')
$$;

-- Categorías por idioma (misma estructura que atracciones_categorias_en).
-- Se crean como copia del inglés; el script de traducción reemplaza los nombres.
CREATE TABLE IF NOT EXISTS catalogo.atracciones_categorias_pt (LIKE catalogo.atracciones_categorias_en INCLUDING ALL);
CREATE TABLE IF NOT EXISTS catalogo.atracciones_categorias_fr (LIKE catalogo.atracciones_categorias_en INCLUDING ALL);
CREATE TABLE IF NOT EXISTS catalogo.atracciones_categorias_de (LIKE catalogo.atracciones_categorias_en INCLUDING ALL);
INSERT INTO catalogo.atracciones_categorias_pt SELECT * FROM catalogo.atracciones_categorias_en ON CONFLICT DO NOTHING;
INSERT INTO catalogo.atracciones_categorias_fr SELECT * FROM catalogo.atracciones_categorias_en ON CONFLICT DO NOTHING;
INSERT INTO catalogo.atracciones_categorias_de SELECT * FROM catalogo.atracciones_categorias_en ON CONFLICT DO NOTHING;
