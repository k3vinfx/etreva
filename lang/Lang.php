<?php
// ============================================================
//  lang/Lang.php
//  Helper de internacionalización para el sistema Etreva
// ============================================================
class Lang {
    private static array $texts = [];

    // Idiomas soportados: código => [nombre nativo, código de bandera (flagcdn.com)]
    public const IDIOMAS = [
        'es' => ['Español',   'bo'],
        'en' => ['English',   'us'],
        'pt' => ['Português', 'br'],
        'fr' => ['Français',  'fr'],
        'de' => ['Deutsch',   'de'],
    ];

    public static function valido(?string $lang): bool {
        return $lang !== null && isset(self::IDIOMAS[$lang]);
    }

    // Normaliza a un idioma soportado (por defecto español)
    public static function normalizar(?string $lang, string $defecto = 'es'): string {
        return self::valido($lang) ? $lang : $defecto;
    }

    public static function load(string $lang = 'es'): void {
        $lang = self::normalizar($lang);
        $file = __DIR__ . "/{$lang}.php";
        $base = file_exists($file) ? require $file : [];

        // pt / fr / de: si falta alguna frase, se muestra en inglés (no la clave)
        if (!in_array($lang, ['es', 'en'], true)) {
            $base = array_replace_recursive(require __DIR__ . '/en.php', $base);
        }
        self::$texts = $base;
    }

    // Valores internos guardados en la BD → clave de traducción existente
    private const VALORES = [
        // motivo de viaje
        'aventura' => 'perfil.motivo_aventura', 'cultura' => 'perfil.motivo_cultura',
        'fiesta' => 'perfil.motivo_fiesta', 'gastronomia' => 'perfil.motivo_gastro', 'gastro' => 'perfil.motivo_gastro',
        'relax' => 'perfil.motivo_relax', 'negocios' => 'perfil.motivo_negocios',
        // experiencias
        'aventura_naturaleza' => 'perfil.exp_aventura', 'cultura_tradiciones' => 'perfil.exp_cultura',
        'vida_nocturna_ocio' => 'perfil.exp_nocturna', 'gastronomia_sabores' => 'perfil.exp_gastro',
        'relax_bienestar' => 'perfil.exp_relax', 'fotografia_paisajes' => 'perfil.exp_foto',
        // compañía
        'solo' => 'perfil.comp_solo', 'pareja' => 'perfil.comp_pareja', 'familia' => 'perfil.comp_familia',
        'amigos' => 'perfil.comp_amigos', 'grupo' => 'perfil.comp_grupo',
        // categorías principales
        'trekking' => 'categorias.trekking', 'montanismo' => 'categorias.montanismo', 'downhill' => 'categorias.downhill',
        'vida_nocturna' => 'categorias.vida_nocturna', 'iconos_urbanos' => 'categorias.iconos_urbanos',
        'festividades' => 'inicio.cat_festividades',
        'familiar' => 'buscar.v_familiar', 'deportes_extremos' => 'buscar.v_deportes_extremos',
        // dificultad
        'baja' => 'buscar.dif_baja', 'media' => 'buscar.dif_media', 'alta' => 'buscar.dif_alta', 'muy_alta' => 'buscar.dif_muy_alta',
    ];

    /** Texto visible para un valor interno (p. ej. "aventura_naturaleza"); si no se conoce, lo humaniza */
    public static function valor(?string $v): string
    {
        $k = mb_strtolower(trim((string)$v));
        if ($k === '') return '';
        if (isset(self::VALORES[$k])) {
            $t = self::get(self::VALORES[$k]);
            if ($t !== self::VALORES[$k]) return $t;
        }
        return mb_convert_case(str_replace('_', ' ', $k), MB_CASE_TITLE, 'UTF-8');
    }

    /** Mapa completo valor → texto, para usar en JS */
    public static function valoresJs(): string
    {
        $out = [];
        foreach (array_keys(self::VALORES) as $k) $out[$k] = self::valor($k);
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtiene un texto por clave con notación de punto.
     * Ejemplo: Lang::get('nav_cliente.inicio')
     */
    public static function get(string $key, string $default = ''): string {
        $keys = explode('.', $key);
        $val  = self::$texts;
        foreach ($keys as $k) {
            if (!isset($val[$k])) return $default ?: $key;
            $val = $val[$k];
        }
        return is_string($val) ? $val : ($default ?: $key);
    }
}
