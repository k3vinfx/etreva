<?php
/*
 * model/avatar.php
 * Avatar de un usuario, igual en todo el sitio (menú, perfil, reseñas, favoritos…).
 * configuracion_json.avatar puede ser:
 *   "preset_N"                      → avatar predefinido (emoji sobre degradado)
 *   "assets/img/avatars/xxx.jpg"     → foto subida por el usuario
 *   vacío                            → iniciales
 * La misma lista de presets está en JS (Avatar.PRESETS, ver presetsJs()).
 */
class Avatar
{
    public const PRESETS = [
        ['linear-gradient(135deg,#6366f1,#818cf8)', '🧭'],
        ['linear-gradient(135deg,#16a34a,#22c55e)', '🏔️'],
        ['linear-gradient(135deg,#ea580c,#f97316)', '🌋'],
        ['linear-gradient(135deg,#b91c7b,#ec4899)', '🌸'],
        ['linear-gradient(135deg,#0e7490,#06b6d4)', '🌊'],
        ['linear-gradient(135deg,#d97706,#f59e0b)', '🌟'],
        ['linear-gradient(135deg,#7c3aed,#a78bfa)', '🎭'],
        ['linear-gradient(135deg,#0369a1,#38bdf8)', '🗺️'],
        ['linear-gradient(135deg,#166534,#4ade80)', '🌿'],
        ['linear-gradient(135deg,#9333ea,#c084fc)', '🦋'],
        ['linear-gradient(135deg,#dc2626,#f87171)', '🏕️'],
        ['linear-gradient(135deg,#0891b2,#67e8f9)', '✈️'],
    ];

    /** Avatar guardado en configuracion_json (string JSON o array) */
    public static function deConfig($config): ?string
    {
        if (is_string($config)) $config = json_decode($config, true);
        $a = is_array($config) ? ($config['avatar'] ?? null) : null;
        return is_string($a) && $a !== '' ? $a : null;
    }

    /** Solo se aceptan presets válidos o fotos de la carpeta de avatares */
    public static function esFoto(?string $a): bool
    {
        return $a !== null && preg_match('#^assets/img/avatars/[\w.-]+\.(jpe?g|png|webp|gif)$#i', $a) && is_file(__DIR__ . '/../' . $a);
    }

    /** HTML del avatar (círculo de $px píxeles) */
    public static function html(?string $avatar, string $nombre = '', string $apellido = '', int $px = 40, string $cls = ''): string
    {
        $base = "width:{$px}px;height:{$px}px;border-radius:50%;flex-shrink:0;display:inline-flex;align-items:center;"
              . "justify-content:center;overflow:hidden;color:#fff;font-weight:800;";
        $cls  = htmlspecialchars(trim('etv-avatar ' . $cls));

        if (self::esFoto($avatar)) {
            $src = htmlspecialchars($avatar) . '?v=' . @filemtime(__DIR__ . '/../' . $avatar);
            return "<span class=\"{$cls}\" style=\"{$base}\"><img src=\"{$src}\" alt=\"\" style=\"width:100%;height:100%;object-fit:cover;\"></span>";
        }
        if ($avatar && preg_match('/^preset_(\d+)$/', $avatar, $m) && isset(self::PRESETS[(int)$m[1]])) {
            [$bg, $emoji] = self::PRESETS[(int)$m[1]];
            $fs = round($px * .5);
            return "<span class=\"{$cls}\" style=\"{$base}background:{$bg};font-size:{$fs}px;\">{$emoji}</span>";
        }
        $ini = htmlspecialchars(mb_strtoupper(mb_substr(trim($nombre), 0, 1) . mb_substr(trim($apellido), 0, 1)) ?: '?');
        $fs  = round($px * .38);
        return "<span class=\"{$cls}\" style=\"{$base}background:linear-gradient(135deg,#6366f1,#a78bfa);font-size:{$fs}px;\">{$ini}</span>";
    }

    /**
     * Datos públicos del autor de una reseña.
     * El correo solo se expone si el usuario activó "contacto_publico" en su perfil (consentimiento).
     */
    public static function autorPublico(?string $email, $config): array
    {
        if (is_string($config)) $config = json_decode($config, true);
        $permite = is_array($config) && ($config['contacto_publico'] ?? false) === true;
        return [
            'avatar'   => self::deConfig($config),
            'contacto' => $permite && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null,
        ];
    }

    /** Lista de presets para JS: [{bg, emoji}, …] */
    public static function presetsJs(): string
    {
        return json_encode(array_map(fn($p) => ['bg' => $p[0], 'emoji' => $p[1]], self::PRESETS), JSON_UNESCAPED_UNICODE);
    }
}
