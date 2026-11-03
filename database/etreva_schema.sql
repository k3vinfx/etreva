--
-- PostgreSQL database dump
--

\restrict kZkgvEU3wEJGMZQ17qKdNlN3gFkeOW5l8mdNPzgX8XWIFf4rZIveE034VPn75ed

-- Dumped from database version 18.2
-- Dumped by pg_dump version 18.2

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: catalogo; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA catalogo;


--
-- Name: itinerarios; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA itinerarios;


--
-- Name: recomendaciones; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA recomendaciones;


--
-- Name: usuarios; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA usuarios;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: atracciones; Type: TABLE; Schema: catalogo; Owner: -
--

CREATE TABLE catalogo.atracciones (
    id integer NOT NULL,
    nombre character varying(255) NOT NULL,
    descripcion text,
    ubicacion jsonb,
    latitud numeric(10,8),
    longitud numeric(11,8),
    direccion text,
    ciudad character varying(100),
    pais character varying(100),
    horario_apertura time without time zone,
    horario_cierre time without time zone,
    dias_operacion jsonb,
    precio_entrada numeric(10,2),
    moneda character varying(3) DEFAULT 'USD'::character varying,
    tiempo_estimado jsonb,
    puntuacion_promedio numeric(3,2),
    estado character varying(50) DEFAULT 'activo'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    atraccion_categoria_id integer
);


--
-- Name: atracciones_i18n(text); Type: FUNCTION; Schema: catalogo; Owner: -
--

CREATE FUNCTION catalogo.atracciones_i18n(p_idioma text) RETURNS SETOF catalogo.atracciones
    LANGUAGE sql STABLE
    AS $$
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


--
-- Name: atraccion_categorias; Type: TABLE; Schema: catalogo; Owner: -
--

CREATE TABLE catalogo.atraccion_categorias (
    id integer NOT NULL,
    categoria_rangos json,
    subcategoria_rangos json,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: atraccion_categorias_id_seq; Type: SEQUENCE; Schema: catalogo; Owner: -
--

CREATE SEQUENCE catalogo.atraccion_categorias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: atraccion_categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: catalogo; Owner: -
--

ALTER SEQUENCE catalogo.atraccion_categorias_id_seq OWNED BY catalogo.atraccion_categorias.id;


--
-- Name: atracciones_categorias_en; Type: TABLE; Schema: catalogo; Owner: -
--

CREATE TABLE catalogo.atracciones_categorias_en (
    id integer NOT NULL,
    categoria_rangos jsonb NOT NULL,
    subcategoria_rangos jsonb NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


--
-- Name: atracciones_categorias_en_id_seq; Type: SEQUENCE; Schema: catalogo; Owner: -
--

CREATE SEQUENCE catalogo.atracciones_categorias_en_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: atracciones_categorias_en_id_seq; Type: SEQUENCE OWNED BY; Schema: catalogo; Owner: -
--

ALTER SEQUENCE catalogo.atracciones_categorias_en_id_seq OWNED BY catalogo.atracciones_categorias_en.id;


--
-- Name: atracciones_categorias_de; Type: TABLE; Schema: catalogo; Owner: -
--

CREATE TABLE catalogo.atracciones_categorias_de (
    id integer DEFAULT nextval('catalogo.atracciones_categorias_en_id_seq'::regclass) CONSTRAINT atracciones_categorias_en_id_not_null NOT NULL,
    categoria_rangos jsonb CONSTRAINT atracciones_categorias_en_categoria_rangos_not_null NOT NULL,
    subcategoria_rangos jsonb CONSTRAINT atracciones_categorias_en_subcategoria_rangos_not_null NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


--
-- Name: atracciones_categorias_fr; Type: TABLE; Schema: catalogo; Owner: -
--

CREATE TABLE catalogo.atracciones_categorias_fr (
    id integer DEFAULT nextval('catalogo.atracciones_categorias_en_id_seq'::regclass) CONSTRAINT atracciones_categorias_en_id_not_null NOT NULL,
    categoria_rangos jsonb CONSTRAINT atracciones_categorias_en_categoria_rangos_not_null NOT NULL,
    subcategoria_rangos jsonb CONSTRAINT atracciones_categorias_en_subcategoria_rangos_not_null NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


--
-- Name: atracciones_categorias_pt; Type: TABLE; Schema: catalogo; Owner: -
--

CREATE TABLE catalogo.atracciones_categorias_pt (
    id integer DEFAULT nextval('catalogo.atracciones_categorias_en_id_seq'::regclass) CONSTRAINT atracciones_categorias_en_id_not_null NOT NULL,
    categoria_rangos jsonb CONSTRAINT atracciones_categorias_en_categoria_rangos_not_null NOT NULL,
    subcategoria_rangos jsonb CONSTRAINT atracciones_categorias_en_subcategoria_rangos_not_null NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


--
-- Name: atracciones_en; Type: TABLE; Schema: catalogo; Owner: -
--

CREATE TABLE catalogo.atracciones_en (
    id integer NOT NULL,
    nombre text NOT NULL,
    descripcion text,
    ubicacion jsonb,
    latitud numeric(12,8),
    longitud numeric(12,8),
    direccion text,
    ciudad text,
    pais text,
    horario_apertura time without time zone,
    horario_cierre time without time zone,
    dias_operacion jsonb,
    precio_entrada numeric(10,2),
    moneda text,
    tiempo_estimado jsonb,
    puntuacion_promedio numeric(3,2),
    estado text,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    atraccion_categoria_id integer
);


--
-- Name: atracciones_id_seq; Type: SEQUENCE; Schema: catalogo; Owner: -
--

CREATE SEQUENCE catalogo.atracciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: atracciones_id_seq; Type: SEQUENCE OWNED BY; Schema: catalogo; Owner: -
--

ALTER SEQUENCE catalogo.atracciones_id_seq OWNED BY catalogo.atracciones.id;


--
-- Name: atracciones_traducciones; Type: TABLE; Schema: catalogo; Owner: -
--

CREATE TABLE catalogo.atracciones_traducciones (
    atraccion_id integer NOT NULL,
    idioma character varying(5) NOT NULL,
    nombre text,
    descripcion text,
    direccion text,
    tiempo_estimado jsonb,
    ubicacion jsonb,
    origen character varying(20) DEFAULT 'automatica'::character varying NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT atracciones_traducciones_idioma_check CHECK (((idioma)::text = ANY ((ARRAY['en'::character varying, 'pt'::character varying, 'fr'::character varying, 'de'::character varying])::text[])))
);


--
-- Name: back_testing_mr; Type: TABLE; Schema: itinerarios; Owner: -
--

CREATE TABLE itinerarios.back_testing_mr (
    id integer CONSTRAINT detalle_itinerario_id_not_null NOT NULL,
    itinerario_id integer CONSTRAINT detalle_itinerario_itinerario_id_not_null NOT NULL,
    atraccion_id integer CONSTRAINT detalle_itinerario_atraccion_id_not_null NOT NULL,
    calificacion_usuario integer,
    comentario_usuario text,
    modelo_mr_fk jsonb,
    nueva_metricas_json_reajuste jsonb,
    estado character varying(20) DEFAULT 'evaluado'::character varying,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    CONSTRAINT detalle_itinerario_calificacion_usuario_check CHECK (((calificacion_usuario >= 1) AND (calificacion_usuario <= 5)))
);


--
-- Name: detalle_itinerario_id_seq; Type: SEQUENCE; Schema: itinerarios; Owner: -
--

CREATE SEQUENCE itinerarios.detalle_itinerario_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: detalle_itinerario_id_seq; Type: SEQUENCE OWNED BY; Schema: itinerarios; Owner: -
--

ALTER SEQUENCE itinerarios.detalle_itinerario_id_seq OWNED BY itinerarios.back_testing_mr.id;


--
-- Name: itinerarios; Type: TABLE; Schema: itinerarios; Owner: -
--

CREATE TABLE itinerarios.itinerarios (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    nombre character varying(255) NOT NULL,
    descripcion text,
    atracciones_id integer,
    fecha_visita timestamp with time zone,
    estado character varying(50) DEFAULT 'borrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: itinerarios_id_seq; Type: SEQUENCE; Schema: itinerarios; Owner: -
--

CREATE SEQUENCE itinerarios.itinerarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: itinerarios_id_seq; Type: SEQUENCE OWNED BY; Schema: itinerarios; Owner: -
--

ALTER SEQUENCE itinerarios.itinerarios_id_seq OWNED BY itinerarios.itinerarios.id;


--
-- Name: atracciones_categorias_en; Type: TABLE; Schema: recomendaciones; Owner: -
--

CREATE TABLE recomendaciones.atracciones_categorias_en (
    id integer NOT NULL,
    categoria_rangos jsonb NOT NULL,
    subcategoria_rangos jsonb NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


--
-- Name: atracciones_categorias_en_id_seq; Type: SEQUENCE; Schema: recomendaciones; Owner: -
--

CREATE SEQUENCE recomendaciones.atracciones_categorias_en_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: atracciones_categorias_en_id_seq; Type: SEQUENCE OWNED BY; Schema: recomendaciones; Owner: -
--

ALTER SEQUENCE recomendaciones.atracciones_categorias_en_id_seq OWNED BY recomendaciones.atracciones_categorias_en.id;


--
-- Name: catalogo_parametros; Type: TABLE; Schema: recomendaciones; Owner: -
--

CREATE TABLE recomendaciones.catalogo_parametros (
    id integer NOT NULL,
    modelo_mr_id integer NOT NULL,
    grupo character varying(50) NOT NULL,
    valor character varying(50) NOT NULL,
    descripcion text NOT NULL,
    estado boolean DEFAULT true,
    fecha_creacion timestamp without time zone DEFAULT now()
);


--
-- Name: catalogo_parametros_id_seq; Type: SEQUENCE; Schema: recomendaciones; Owner: -
--

CREATE SEQUENCE recomendaciones.catalogo_parametros_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: catalogo_parametros_id_seq; Type: SEQUENCE OWNED BY; Schema: recomendaciones; Owner: -
--

ALTER SEQUENCE recomendaciones.catalogo_parametros_id_seq OWNED BY recomendaciones.catalogo_parametros.id;


--
-- Name: contexto_relacion; Type: TABLE; Schema: recomendaciones; Owner: -
--

CREATE TABLE recomendaciones.contexto_relacion (
    id integer NOT NULL,
    modelo_mr_id integer NOT NULL,
    tipo_contexto character varying(50) NOT NULL,
    valor character varying(200) NOT NULL,
    estado boolean DEFAULT true,
    fecha_creacion timestamp without time zone DEFAULT now()
);


--
-- Name: contexto_relacion_id_seq; Type: SEQUENCE; Schema: recomendaciones; Owner: -
--

CREATE SEQUENCE recomendaciones.contexto_relacion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: contexto_relacion_id_seq; Type: SEQUENCE OWNED BY; Schema: recomendaciones; Owner: -
--

ALTER SEQUENCE recomendaciones.contexto_relacion_id_seq OWNED BY recomendaciones.contexto_relacion.id;


--
-- Name: modelos_mr; Type: TABLE; Schema: recomendaciones; Owner: -
--

CREATE TABLE recomendaciones.modelos_mr (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    version character varying(50),
    descripcion text,
    configuracion_json jsonb,
    fecha_entrenamiento timestamp without time zone,
    metricas_desempeno jsonb,
    estado character varying(50) DEFAULT 'activo'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: modelos_mr_id_seq; Type: SEQUENCE; Schema: recomendaciones; Owner: -
--

CREATE SEQUENCE recomendaciones.modelos_mr_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: modelos_mr_id_seq; Type: SEQUENCE OWNED BY; Schema: recomendaciones; Owner: -
--

ALTER SEQUENCE recomendaciones.modelos_mr_id_seq OWNED BY recomendaciones.modelos_mr.id;


--
-- Name: recomendaciones; Type: TABLE; Schema: recomendaciones; Owner: -
--

CREATE TABLE recomendaciones.recomendaciones (
    id integer NOT NULL,
    atraccion_id integer,
    modelo_mr_id integer,
    score_relevancia numeric(5,2),
    razon_recomendacion jsonb DEFAULT '{}'::jsonb,
    contexto_recomendacion jsonb DEFAULT '{}'::jsonb,
    feedback_usuario jsonb DEFAULT '{}'::jsonb,
    fecha_generacion timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    fecha_mostrado timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: recomendaciones_id_seq; Type: SEQUENCE; Schema: recomendaciones; Owner: -
--

CREATE SEQUENCE recomendaciones.recomendaciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: recomendaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: recomendaciones; Owner: -
--

ALTER SEQUENCE recomendaciones.recomendaciones_id_seq OWNED BY recomendaciones.recomendaciones.id;


--
-- Name: preferencias; Type: TABLE; Schema: usuarios; Owner: -
--

CREATE TABLE usuarios.preferencias (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    motivo_viaje character varying(100),
    presupuesto_max character varying(8),
    duracion_dias integer,
    intereses_json jsonb,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: preferencias_id_seq; Type: SEQUENCE; Schema: usuarios; Owner: -
--

CREATE SEQUENCE usuarios.preferencias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: preferencias_id_seq; Type: SEQUENCE OWNED BY; Schema: usuarios; Owner: -
--

ALTER SEQUENCE usuarios.preferencias_id_seq OWNED BY usuarios.preferencias.id;


--
-- Name: solicitudes_soporte; Type: TABLE; Schema: usuarios; Owner: -
--

CREATE TABLE usuarios.solicitudes_soporte (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    tipo character varying(40) DEFAULT 'pago_no_confirmado'::character varying NOT NULL,
    mensaje text NOT NULL,
    estado character varying(20) DEFAULT 'pendiente'::character varying NOT NULL,
    respuesta text,
    atendida_por integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: solicitudes_soporte_id_seq; Type: SEQUENCE; Schema: usuarios; Owner: -
--

CREATE SEQUENCE usuarios.solicitudes_soporte_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: solicitudes_soporte_id_seq; Type: SEQUENCE OWNED BY; Schema: usuarios; Owner: -
--

ALTER SEQUENCE usuarios.solicitudes_soporte_id_seq OWNED BY usuarios.solicitudes_soporte.id;


--
-- Name: usuarios; Type: TABLE; Schema: usuarios; Owner: -
--

CREATE TABLE usuarios.usuarios (
    id integer NOT NULL,
    email character varying(255) NOT NULL,
    password_hash character varying(255) NOT NULL,
    nombre character varying(100),
    apellido character varying(100),
    fecha_nacimiento date,
    genero character varying(20),
    nacionalidad character varying(50),
    telefono character varying(20),
    rol integer DEFAULT 1,
    estado character varying(20) DEFAULT 'activo'::character varying,
    configuracion_json jsonb DEFAULT '{"preferencias": {"intereses": null}, "registro_pago": {"estado": "completado"}}'::jsonb,
    fecha_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    historial_json jsonb DEFAULT '[]'::jsonb
);


--
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: usuarios; Owner: -
--

CREATE SEQUENCE usuarios.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: usuarios; Owner: -
--

ALTER SEQUENCE usuarios.usuarios_id_seq OWNED BY usuarios.usuarios.id;


--
-- Name: atraccion_categorias id; Type: DEFAULT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atraccion_categorias ALTER COLUMN id SET DEFAULT nextval('catalogo.atraccion_categorias_id_seq'::regclass);


--
-- Name: atracciones id; Type: DEFAULT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones ALTER COLUMN id SET DEFAULT nextval('catalogo.atracciones_id_seq'::regclass);


--
-- Name: atracciones_categorias_en id; Type: DEFAULT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones_categorias_en ALTER COLUMN id SET DEFAULT nextval('catalogo.atracciones_categorias_en_id_seq'::regclass);


--
-- Name: back_testing_mr id; Type: DEFAULT; Schema: itinerarios; Owner: -
--

ALTER TABLE ONLY itinerarios.back_testing_mr ALTER COLUMN id SET DEFAULT nextval('itinerarios.detalle_itinerario_id_seq'::regclass);


--
-- Name: itinerarios id; Type: DEFAULT; Schema: itinerarios; Owner: -
--

ALTER TABLE ONLY itinerarios.itinerarios ALTER COLUMN id SET DEFAULT nextval('itinerarios.itinerarios_id_seq'::regclass);


--
-- Name: atracciones_categorias_en id; Type: DEFAULT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.atracciones_categorias_en ALTER COLUMN id SET DEFAULT nextval('recomendaciones.atracciones_categorias_en_id_seq'::regclass);


--
-- Name: catalogo_parametros id; Type: DEFAULT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.catalogo_parametros ALTER COLUMN id SET DEFAULT nextval('recomendaciones.catalogo_parametros_id_seq'::regclass);


--
-- Name: contexto_relacion id; Type: DEFAULT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.contexto_relacion ALTER COLUMN id SET DEFAULT nextval('recomendaciones.contexto_relacion_id_seq'::regclass);


--
-- Name: modelos_mr id; Type: DEFAULT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.modelos_mr ALTER COLUMN id SET DEFAULT nextval('recomendaciones.modelos_mr_id_seq'::regclass);


--
-- Name: recomendaciones id; Type: DEFAULT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.recomendaciones ALTER COLUMN id SET DEFAULT nextval('recomendaciones.recomendaciones_id_seq'::regclass);


--
-- Name: preferencias id; Type: DEFAULT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.preferencias ALTER COLUMN id SET DEFAULT nextval('usuarios.preferencias_id_seq'::regclass);


--
-- Name: solicitudes_soporte id; Type: DEFAULT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.solicitudes_soporte ALTER COLUMN id SET DEFAULT nextval('usuarios.solicitudes_soporte_id_seq'::regclass);


--
-- Name: usuarios id; Type: DEFAULT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.usuarios ALTER COLUMN id SET DEFAULT nextval('usuarios.usuarios_id_seq'::regclass);


--
-- Name: atraccion_categorias atraccion_categorias_pkey; Type: CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atraccion_categorias
    ADD CONSTRAINT atraccion_categorias_pkey PRIMARY KEY (id);


--
-- Name: atracciones_categorias_de atracciones_categorias_de_pkey; Type: CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones_categorias_de
    ADD CONSTRAINT atracciones_categorias_de_pkey PRIMARY KEY (id);


--
-- Name: atracciones_categorias_en atracciones_categorias_en_pkey; Type: CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones_categorias_en
    ADD CONSTRAINT atracciones_categorias_en_pkey PRIMARY KEY (id);


--
-- Name: atracciones_categorias_fr atracciones_categorias_fr_pkey; Type: CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones_categorias_fr
    ADD CONSTRAINT atracciones_categorias_fr_pkey PRIMARY KEY (id);


--
-- Name: atracciones_categorias_pt atracciones_categorias_pt_pkey; Type: CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones_categorias_pt
    ADD CONSTRAINT atracciones_categorias_pt_pkey PRIMARY KEY (id);


--
-- Name: atracciones_en atracciones_en_pkey; Type: CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones_en
    ADD CONSTRAINT atracciones_en_pkey PRIMARY KEY (id);


--
-- Name: atracciones atracciones_pkey; Type: CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones
    ADD CONSTRAINT atracciones_pkey PRIMARY KEY (id);


--
-- Name: atracciones_traducciones atracciones_traducciones_pkey; Type: CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones_traducciones
    ADD CONSTRAINT atracciones_traducciones_pkey PRIMARY KEY (atraccion_id, idioma);


--
-- Name: back_testing_mr detalle_itinerario_pkey; Type: CONSTRAINT; Schema: itinerarios; Owner: -
--

ALTER TABLE ONLY itinerarios.back_testing_mr
    ADD CONSTRAINT detalle_itinerario_pkey PRIMARY KEY (id);


--
-- Name: itinerarios itinerarios_pkey; Type: CONSTRAINT; Schema: itinerarios; Owner: -
--

ALTER TABLE ONLY itinerarios.itinerarios
    ADD CONSTRAINT itinerarios_pkey PRIMARY KEY (id);


--
-- Name: atracciones_categorias_en atracciones_categorias_en_pkey; Type: CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.atracciones_categorias_en
    ADD CONSTRAINT atracciones_categorias_en_pkey PRIMARY KEY (id);


--
-- Name: catalogo_parametros catalogo_parametros_pkey; Type: CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.catalogo_parametros
    ADD CONSTRAINT catalogo_parametros_pkey PRIMARY KEY (id);


--
-- Name: contexto_relacion contexto_relacion_modelo_mr_id_tipo_contexto_valor_key; Type: CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.contexto_relacion
    ADD CONSTRAINT contexto_relacion_modelo_mr_id_tipo_contexto_valor_key UNIQUE (modelo_mr_id, tipo_contexto, valor);


--
-- Name: contexto_relacion contexto_relacion_pkey; Type: CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.contexto_relacion
    ADD CONSTRAINT contexto_relacion_pkey PRIMARY KEY (id);


--
-- Name: modelos_mr modelos_mr_pkey; Type: CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.modelos_mr
    ADD CONSTRAINT modelos_mr_pkey PRIMARY KEY (id);


--
-- Name: recomendaciones recomendaciones_pkey; Type: CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.recomendaciones
    ADD CONSTRAINT recomendaciones_pkey PRIMARY KEY (id);


--
-- Name: preferencias preferencias_pkey; Type: CONSTRAINT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.preferencias
    ADD CONSTRAINT preferencias_pkey PRIMARY KEY (id);


--
-- Name: solicitudes_soporte solicitudes_soporte_pkey; Type: CONSTRAINT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.solicitudes_soporte
    ADD CONSTRAINT solicitudes_soporte_pkey PRIMARY KEY (id);


--
-- Name: usuarios usuarios_email_key; Type: CONSTRAINT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- Name: idx_atracciones_coords; Type: INDEX; Schema: catalogo; Owner: -
--

CREATE INDEX idx_atracciones_coords ON catalogo.atracciones USING btree (latitud, longitud);


--
-- Name: idx_atracciones_nombre; Type: INDEX; Schema: catalogo; Owner: -
--

CREATE INDEX idx_atracciones_nombre ON catalogo.atracciones USING btree (nombre);


--
-- Name: idx_contexto_modelo; Type: INDEX; Schema: recomendaciones; Owner: -
--

CREATE INDEX idx_contexto_modelo ON recomendaciones.contexto_relacion USING btree (modelo_mr_id);


--
-- Name: idx_contexto_tipo; Type: INDEX; Schema: recomendaciones; Owner: -
--

CREATE INDEX idx_contexto_tipo ON recomendaciones.contexto_relacion USING btree (tipo_contexto);


--
-- Name: idx_contexto_valor; Type: INDEX; Schema: recomendaciones; Owner: -
--

CREATE INDEX idx_contexto_valor ON recomendaciones.contexto_relacion USING btree (valor);


--
-- Name: idx_recom_feedback_jsonb; Type: INDEX; Schema: recomendaciones; Owner: -
--

CREATE INDEX idx_recom_feedback_jsonb ON recomendaciones.recomendaciones USING gin (feedback_usuario);


--
-- Name: idx_recomendaciones_atraccion; Type: INDEX; Schema: recomendaciones; Owner: -
--

CREATE INDEX idx_recomendaciones_atraccion ON recomendaciones.recomendaciones USING btree (atraccion_id);


--
-- Name: idx_recomendaciones_score; Type: INDEX; Schema: recomendaciones; Owner: -
--

CREATE INDEX idx_recomendaciones_score ON recomendaciones.recomendaciones USING btree (score_relevancia);


--
-- Name: idx_preferencias_usuario; Type: INDEX; Schema: usuarios; Owner: -
--

CREATE INDEX idx_preferencias_usuario ON usuarios.preferencias USING btree (usuario_id);


--
-- Name: atracciones_traducciones atracciones_traducciones_atraccion_id_fkey; Type: FK CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones_traducciones
    ADD CONSTRAINT atracciones_traducciones_atraccion_id_fkey FOREIGN KEY (atraccion_id) REFERENCES catalogo.atracciones(id) ON DELETE CASCADE;


--
-- Name: atracciones fk_atracciones_categoria; Type: FK CONSTRAINT; Schema: catalogo; Owner: -
--

ALTER TABLE ONLY catalogo.atracciones
    ADD CONSTRAINT fk_atracciones_categoria FOREIGN KEY (atraccion_categoria_id) REFERENCES catalogo.atraccion_categorias(id) ON DELETE CASCADE;


--
-- Name: back_testing_mr fk_detalle_itinerario_atraccion; Type: FK CONSTRAINT; Schema: itinerarios; Owner: -
--

ALTER TABLE ONLY itinerarios.back_testing_mr
    ADD CONSTRAINT fk_detalle_itinerario_atraccion FOREIGN KEY (atraccion_id) REFERENCES catalogo.atracciones(id) ON DELETE CASCADE;


--
-- Name: back_testing_mr fk_detalle_itinerario_itinerario; Type: FK CONSTRAINT; Schema: itinerarios; Owner: -
--

ALTER TABLE ONLY itinerarios.back_testing_mr
    ADD CONSTRAINT fk_detalle_itinerario_itinerario FOREIGN KEY (itinerario_id) REFERENCES itinerarios.itinerarios(id) ON DELETE CASCADE;


--
-- Name: itinerarios fk_itinerario_atraccion; Type: FK CONSTRAINT; Schema: itinerarios; Owner: -
--

ALTER TABLE ONLY itinerarios.itinerarios
    ADD CONSTRAINT fk_itinerario_atraccion FOREIGN KEY (atracciones_id) REFERENCES catalogo.atracciones(id) ON DELETE CASCADE;


--
-- Name: itinerarios fk_itinerarios_usuario; Type: FK CONSTRAINT; Schema: itinerarios; Owner: -
--

ALTER TABLE ONLY itinerarios.itinerarios
    ADD CONSTRAINT fk_itinerarios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios.usuarios(id) ON DELETE CASCADE;


--
-- Name: contexto_relacion contexto_relacion_modelo_mr_id_fkey; Type: FK CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.contexto_relacion
    ADD CONSTRAINT contexto_relacion_modelo_mr_id_fkey FOREIGN KEY (modelo_mr_id) REFERENCES recomendaciones.modelos_mr(id);


--
-- Name: catalogo_parametros fk_catalogo_parametros_modelo; Type: FK CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.catalogo_parametros
    ADD CONSTRAINT fk_catalogo_parametros_modelo FOREIGN KEY (modelo_mr_id) REFERENCES recomendaciones.modelos_mr(id) ON DELETE CASCADE;


--
-- Name: recomendaciones fk_recomendaciones_atraccion; Type: FK CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.recomendaciones
    ADD CONSTRAINT fk_recomendaciones_atraccion FOREIGN KEY (atraccion_id) REFERENCES catalogo.atracciones(id) ON DELETE CASCADE;


--
-- Name: recomendaciones fk_recomendaciones_modelo; Type: FK CONSTRAINT; Schema: recomendaciones; Owner: -
--

ALTER TABLE ONLY recomendaciones.recomendaciones
    ADD CONSTRAINT fk_recomendaciones_modelo FOREIGN KEY (modelo_mr_id) REFERENCES recomendaciones.modelos_mr(id) ON DELETE SET NULL;


--
-- Name: preferencias fk_preferencias_usuario; Type: FK CONSTRAINT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.preferencias
    ADD CONSTRAINT fk_preferencias_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios.usuarios(id) ON DELETE CASCADE;


--
-- Name: solicitudes_soporte solicitudes_soporte_atendida_por_fkey; Type: FK CONSTRAINT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.solicitudes_soporte
    ADD CONSTRAINT solicitudes_soporte_atendida_por_fkey FOREIGN KEY (atendida_por) REFERENCES usuarios.usuarios(id) ON DELETE SET NULL;


--
-- Name: solicitudes_soporte solicitudes_soporte_usuario_id_fkey; Type: FK CONSTRAINT; Schema: usuarios; Owner: -
--

ALTER TABLE ONLY usuarios.solicitudes_soporte
    ADD CONSTRAINT solicitudes_soporte_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES usuarios.usuarios(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict kZkgvEU3wEJGMZQ17qKdNlN3gFkeOW5l8mdNPzgX8XWIFf4rZIveE034VPn75ed

