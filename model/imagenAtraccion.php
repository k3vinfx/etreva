<?php
/*
 * model/imagenAtraccion.php
 * ─────────────────────────────────────────────────────────────────
 * Galería de fotos de cada atracción, sin enlaces caídos y con fotos adecuadas.
 *
 * Fotos propias: uploads/img_atracciones/{id}_{n}.jpg
 *   · Control de calidad: las imágenes en blanco/ilegibles se apartan a _descartadas/.
 *   · Las fotos chicas (< 500 px) se conservan pero van al final de la galería.
 *
 * Si faltan fotos buenas, se buscan en Wikimedia Commons (fotos libres, se guardan con autoría):
 *   1. Del LUGAR exacto: el título debe nombrarlo y la foto debe estar asociada a Bolivia/La Paz.
 *   2. Referenciales del TIPO de lugar ("Parrilla y Carnes", "Club Nocturno", "Vista Panorámica"…):
 *      la foto debe tratar de ese tema. Se marcan como referenciales en el detalle.
 *   3. Referenciales de la categoría (búsquedas curadas por categoría).
 * Siempre se descartan retratos, personas, mapas, logos, banderas, afiches y documentos,
 * y fotos de menos de 1000 px o verticales. Todo se busca una sola vez y queda guardado.
 * Último recurso: imagen generada con el nombre (nunca un enlace roto).
 */
class ImagenAtraccion
{
    public const DIR         = __DIR__ . '/../uploads/img_atracciones';
    private const CACHE_DIR  = self::DIR . '/_cache';        // metadatos de fotos propias descargadas
    private const REF_DIR    = self::DIR . '/_ref';          // fotos referenciales por tema
    private const DESCARTE   = self::DIR . '/_descartadas';  // originales defectuosas (no se borran)
    private const WEB        = 'uploads/img_atracciones';
    private const MAX_FOTOS  = 4;
    public const MAX_PROPIAS = 8;                             // fotos propias por atracción (subidas desde el admin)
    private const REF_MAX    = 8;
    private const ANCHO_MIN  = 1000;                          // tamaño mínimo para fotos de internet
    private const ANCHO_BUENO = 500;                          // fotos propias menores van al final
    private const UA         = 'EtrevaTravel/1.0 (tourism app; La Paz, Bolivia)';
    private const DIAS_REINTENTO = 7;
    private const HOSTS_IMAGEN   = ['upload.wikimedia.org', 'thumb.wikimedia.org'];
    private static int $fallosRed = 0;   // errores HTTP (p. ej. 429 por exceso de pedidos): no son "sin resultados"

    // Categoría principal = primer dígito del id: [nombre, color1, color2, ícono]
    private const CATEGORIAS = [
        1 => ['Trekking',        '#166534', '#22c55e', '🥾'],
        2 => ['Montañismo',      '#3730a3', '#6366f1', '🏔️'],
        3 => ['Cultura',         '#1e3a8a', '#3b82f6', '🏛️'],
        4 => ['Downhill',        '#7c2d12', '#f97316', '🚵'],
        5 => ['Vida nocturna',   '#581c87', '#a855f7', '🎶'],
        6 => ['Gastronomía',     '#9a3412', '#f59e0b', '🍽️'],
        7 => ['Íconos urbanos',  '#0f766e', '#14b8a6', '🏙️'],
    ];

    // Respaldo por categoría: [consultas, palabras que la foto debe mencionar]
    private const TEMA_CATEGORIA = [
        1 => [['Yungas trail Bolivia', 'Cordillera Real trekking'], ['trail','trek','camino','sendero','yungas','cordillera','choro','takesi','valle']],
        2 => [['Huayna Potosi', 'Cordillera Real Bolivia mountain', 'Illimani'], ['huayna','illimani','cordillera','nevado','mountain','glaciar','glacier','condoriri','pequeño alpamayo']],
        3 => [['museo La Paz Bolivia', 'La Paz Bolivia colonial'], ['museo','museum','colonial','iglesia','church','calle','teatro','cultural']],
        4 => [['Death Road Bolivia bike', 'Yungas road Bolivia'], ['bike','bici','camino','road','yungas','ruta','muerte','death']],
        5 => [['La Paz Bolivia night', 'Sopocachi La Paz Bolivia', 'La Paz Bolivia bar', 'Bolivia nightlife'], ['night','noche','bar','club','cocktail','coctel','disco','pub','music']],
        6 => [['Bolivian cuisine', 'comida boliviana', 'Bolivian food', 'restaurante La Paz Bolivia'], ['food','comida','dish','plato','cuisine','cocina','restaurant','restaurante','salteña','anticucho','sopa','meal']],
        7 => [['La Paz Bolivia street', 'La Paz Bolivia city'], ['la paz','calle','street','plaza','city','ciudad','teleferico','cholet','arquitectura']],
    ];

    // Sinónimos de temas frecuentes (los nombres traen "Parrilla y Carnes", "Pollo a la Brasa"…)
    private const SINONIMOS = [
        'parrilla' => ['parrilla','asado','grill','barbecue','churrasco','carne'], 'carnes' => ['carne','meat','steak','asado'],
        'pollo' => ['pollo','chicken','broaster','brasa'], 'pizza' => ['pizza'], 'cafe' => ['cafe','coffee','café','espresso'],
        'helados' => ['helado','ice cream','gelato'], 'postres' => ['postre','dessert','cake','torta','pastry'],
        'pasteleria' => ['pastel','pastry','cake','bakery','torta'], 'trucha' => ['trucha','trout','fish','pescado'],
        'burger' => ['burger','hamburguesa'], 'china' => ['chinese','china','chifa'], 'brunch' => ['brunch','breakfast','desayuno'],
        'comida' => ['comida','food','dish','plato'], 'tipica' => ['comida','food','dish','plato','traditional','tipica'],
        'club' => ['club','disco','dance','night','noche'], 'nocturno' => ['night','noche','club','bar'],
        'cocteleria' => ['cocktail','coctel','bar','drink'], 'cerveceria' => ['beer','cerveza','brewery','pub'],
        'karaoke' => ['karaoke','singing','microphone'], 'jazz' => ['jazz','concert','music','musico'],
        'mirador' => ['mirador','viewpoint','view','vista','panorama','panoramic'], 'vista' => ['view','vista','panorama','mirador'],
        'panoramica' => ['view','vista','panorama','mirador'], 'parque' => ['park','parque'], 'mercado' => ['market','mercado','feria'],
        'arquitectura' => ['arquitectura','architecture','building','edificio','cholet'], 'museo' => ['museum','museo'],
        'teatro' => ['theatre','theater','teatro'], 'galeria' => ['gallery','galeria','art','arte'], 'arte' => ['art','arte','mural','gallery'],
    ];

    // Lugares que confirman que la foto es de Bolivia. Solo términos inequívocos: "La Paz", "Copacabana",
    // "Miraflores" o "Titicaca" existen también en México, España, Brasil o Perú, así que por sí solos
    // NO bastan (p. ej. "Hotel Príncipe de la Paz" en Aranjuez, España). Sí cuentan términos propios
    // de La Paz / El Alto (Alasita, ekeko, paceña, Abaroa, Chuquiago…).
    private const CONTEXTO_BO = '/\b(bolivia|bolivian|bolivians|boliviana|bolivianas|boliviano|bolivianos|yungas|chacaltaya|illimani|huayna potosi|sopocachi|calacoto|achumani|mallasa|coroico|tiwanaku|tiahuanaco|chulumani|achocalla|condoriri|takesi|sorata|zongo|mecapaca|irpavi|cholet|cholets|mi teleferico|isla del sol|uyuni|el alto|alasita|alasitas|ekeko|ekekos|pacena|pacenas|paceno|pacenos|abaroa|chuquiago|illampu|mercado de las brujas|witches market)\b/u';

    // Otro país nombrado en el TÍTULO de la foto: se descarta aunque sus categorías mencionen Bolivia
    // (p. ej. "Vista de Puno y el Titicaca, Perú", categorizada en el lago Titicaca Bolivia–Perú).
    private const OTRO_PAIS = '/\b(peru|puno|chile|argentina|espana|spain|mexico|brasil|brazil|ecuador|colombia|paraguay|uruguay|venezuela|usa|united states|england|scotland|wales|uk|france|germany|deutschland|italia|italy|poland|polska|krakow|alicante|madrid|aranjuez|louisiana|new orleans|freiburg)\b/u';

    // Nunca: personas, mapas, logos, banderas, documentos…
    private const EXCLUIR = '/\b(retrato|portrait|portraits|nino|nina|ninos|ninas|child|children|kid|kids|girl|girls|boy|boys|mujer|mujeres|woman|women|hombre|hombres|man|men|persona|people|person|campesina|campesino|cholita|selfie|logo|logotipo|mapa|map|bandera|flag|escudo|coat of arms|diagrama|diagram|poster|afiche|cartel|documento|document|firma|signature|billete|banknote|moneda|coin|sello|stamp|plano|grafico|chart|screenshot|captura|politico|presidente|alcalde|wedding|boda|funeral|mhnt|specimen|especimen|dorsal|ventral|insect|insecto|moth|polilla|herbarium|herbario|fossil|fosil|skull|craneo|brachiopod|brachiopoda|braquiopodo|spirifer|graphic|graphics|infographic|infografia|stripes|temperature|temperatura|climate|warming)\b/u';

    /* ════════════════════════ API pública ═════════════════════════ */

    public static function archivo(int $id, int $n): string
    {
        return self::DIR . "/{$id}_{$n}.jpg";
    }

    /** Fotos propias válidas (las defectuosas se apartan), las de buen tamaño primero */
    public static function fotosLocales(int $id): array
    {
        $buenas = []; $chicas = [];
        for ($n = 1; $n <= self::MAX_PROPIAS; $n++) {
            $f = self::archivo($id, $n);
            if (!is_file($f)) continue;
            $ancho = self::anchoValido($f);
            if ($ancho === null) { self::descartar($f); continue; }
            $web = self::WEB . "/{$id}_{$n}.jpg";
            if ($ancho >= self::ANCHO_BUENO) $buenas[] = $web; else $chicas[] = $web;
        }
        return array_merge($buenas, $chicas);
    }

    /** Compatibilidad: lista de fotos para el carrusel */
    public static function fotosParaCarrusel(int $id, array $rutas = []): array
    {
        return self::galeria($id)['fotos'];
    }

    /**
     * Galería con al menos $min fotos:
     *   propias de buen tamaño → del lugar (Wikimedia) → referenciales del tema → de la categoría → propias chicas
     * ['fotos' => [rutas], 'creditos' => [...], 'referenciales' => n, 'grupo' => tema]
     */
    public static function galeria(int $id, int $min = self::MAX_FOTOS, bool $buscar = false): array
    {
        // $buscar = false (vistas): solo lo ya guardado, respuesta inmediata.
        // $buscar = true (precarga / foto faltante): puede consultar Wikimedia.
        $propias = self::fotosLocales($id);
        $buenas  = array_values(array_filter($propias, fn($f) => (self::anchoValido(__DIR__ . '/../' . $f) ?? 0) >= self::ANCHO_BUENO));
        $chicas  = array_values(array_diff($propias, $buenas));
        $creditos = self::creditos($id);

        // Si no hay suficientes fotos buenas, intentar fotos del lugar exacto (una sola vez)
        if ($buscar && count($buenas) < $min && $id > 0 && self::debeBuscar($id) && ($datos = self::datosAtraccion($id))) {
            self::buscarYGuardar($id, $datos, count($propias));
            $propias  = self::fotosLocales($id);
            $buenas   = array_values(array_filter($propias, fn($f) => (self::anchoValido(__DIR__ . '/../' . $f) ?? 0) >= self::ANCHO_BUENO));
            $chicas   = array_values(array_diff($propias, $buenas));
            $creditos = self::creditos($id);
        }

        $fotos = $buenas; $refs = 0; $tema = null;
        if (count($fotos) + count($chicas) < $min || count($fotos) < 2) {
            [$tema, $items] = self::referenciales($id, $buscar);
            $total = count($items);
            for ($i = 0; $i < $total && count($fotos) + count($chicas) < max($min, 2); $i++) {
                $item = $items[($id + $i) % $total];           // juego distinto por atracción
                $fotos[]    = self::WEB . '/_ref/' . $item['archivo'];
                $creditos[] = $item;
                $refs++;
            }
        }
        $fotos = array_slice(array_merge($fotos, $chicas), 0, max($min, count($fotos)));
        if (!$fotos) $fotos = [self::WEB . "/{$id}_1.jpg"]; // el servidor genera una imagen al pedirla

        return ['fotos' => $fotos, 'creditos' => $creditos, 'referenciales' => $refs, 'grupo' => $tema];
    }

    /** Créditos de fotos del lugar descargadas de Wikimedia */
    public static function creditos(int $id): array
    {
        $f = self::cacheFile($id);
        if (!is_file($f)) return [];
        $c = json_decode((string)file_get_contents($f), true) ?: [];
        return array_values(array_filter($c['fotos'] ?? [], fn($x) => is_file(self::archivo($id, (int)$x['n']))));
    }

    /** Lo llama imagen_atraccion.php cuando se pide {id}_{n}.jpg y no existe */
    public static function servir(int $id, int $n): void
    {
        $g = $id > 0 ? self::galeria($id, self::MAX_FOTOS, true) : ['fotos' => []];
        $ruta = $g['fotos'][max(0, $n - 1)] ?? ($g['fotos'][0] ?? null);
        if ($ruta && is_file(__DIR__ . '/../' . $ruta)) { self::enviarArchivo(__DIR__ . '/../' . $ruta); return; }
        self::enviarGenerada($id, $id > 0 ? self::datosAtraccion($id) : null);
    }

    /* ════════════════════════ Gestión desde el administrador ═════ */

    /** Estado completo de las fotos de una atracción para el panel de administración */
    public static function adminListar(int $id): array
    {
        $creditos = [];
        foreach (self::creditos($id) as $c) $creditos[(int)$c['n']] = $c;
        $propias = [];
        for ($n = 1; $n <= self::MAX_PROPIAS; $n++) {
            $f = self::archivo($id, $n);
            if (!is_file($f)) continue;
            $info = @getimagesize($f);
            $propias[] = ['n' => $n, 'url' => self::WEB . "/{$id}_{$n}.jpg?v=" . filemtime($f),
                'ancho' => $info[0] ?? 0, 'alto' => $info[1] ?? 0, 'kb' => (int)round(filesize($f) / 1024),
                'origen' => isset($creditos[$n]) ? 'Wikimedia Commons' : 'Subida / original',
                'credito' => $creditos[$n] ?? null];
        }
        $g = self::galeria($id);
        $refs = array_values(array_filter($g['fotos'], fn($r) => strpos($r, '/_ref/') !== false));
        $descartadas = [];
        foreach (glob(self::DESCARTE . "/{$id}_*.jpg") ?: [] as $f)
            $descartadas[] = ['archivo' => basename($f), 'fecha' => date('Y-m-d H:i', filemtime($f))];
        return ['propias' => $propias, 'referenciales' => $refs, 'tema' => $g['grupo'],
                'descartadas' => $descartadas, 'max' => self::MAX_PROPIAS];
    }

    /** Guarda una foto subida: valida el contenido, la reescala (máx. 1600 px) y la re-codifica a JPEG */
    public static function adminSubir(int $id, string $tmp): int
    {
        $info = @getimagesize($tmp);
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true))
            throw new RuntimeException('El archivo no es una imagen JPG, PNG, WEBP o GIF.');
        if ($info[0] < 400 || $info[1] < 250) throw new RuntimeException("La imagen es muy chica ({$info[0]}×{$info[1]}); mínimo 400×250 px.");
        $n = self::libre($id);
        if (!$n) throw new RuntimeException('Ya tiene ' . self::MAX_PROPIAS . ' fotos propias; quita alguna primero.');

        $img = @imagecreatefromstring((string)file_get_contents($tmp));
        if (!$img) throw new RuntimeException('No se pudo leer la imagen.');
        $w = imagesx($img); $h = imagesy($img);
        $esc = min(1, 1600 / max($w, $h));
        $nw = (int)round($w * $esc); $nh = (int)round($h * $esc);
        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // fondo blanco para PNG transparentes
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $destino = self::archivo($id, $n);
        imagejpeg($dst, $destino, 86);
        imagedestroy($img); imagedestroy($dst);
        if (self::analizarCalidad($destino) === null) {
            @unlink($destino);
            throw new RuntimeException('La imagen parece estar en blanco o ser de un solo color.');
        }
        return $n;
    }

    /** Aparta una foto propia a _descartadas (recuperable) y compacta la numeración */
    public static function adminQuitar(int $id, int $n): void
    {
        $f = self::archivo($id, $n);
        if (!is_file($f)) throw new RuntimeException('La foto no existe.');
        if (!is_dir(self::DESCARTE)) @mkdir(self::DESCARTE, 0775, true);
        rename($f, self::DESCARTE . "/{$id}_{$n}_" . date('YmdHis') . '.jpg');
        self::compactar($id);
    }

    /** Convierte la foto $n en la principal (posición 1) */
    public static function adminPrincipal(int $id, int $n): void
    {
        if ($n === 1) return;
        $a = self::archivo($id, 1); $b = self::archivo($id, $n);
        if (!is_file($b)) throw new RuntimeException('La foto no existe.');
        $tmp = self::DIR . "/{$id}_swap.tmp";
        if (is_file($a)) rename($a, $tmp);
        rename($b, $a);
        if (is_file($tmp)) rename($tmp, $b);
        self::renumerarCreditos($id, [1 => $n, $n => 1]);
        @touch($a); @touch($b);   // cambia ?v= para que el navegador no muestre la versión anterior
    }

    /** Recupera una foto apartada */
    public static function adminRestaurar(int $id, string $archivo): int
    {
        $archivo = basename($archivo);
        if (!preg_match('/^' . $id . '_[\w-]+\.jpg$/', $archivo) || !is_file(self::DESCARTE . '/' . $archivo))
            throw new RuntimeException('Archivo no encontrado.');
        $n = self::libre($id);
        if (!$n) throw new RuntimeException('Ya tiene ' . self::MAX_PROPIAS . ' fotos propias.');
        rename(self::DESCARTE . '/' . $archivo, self::archivo($id, $n));
        return $n;
    }

    /** Busca más fotos del lugar en Wikimedia Commons ahora mismo (sin esperar el reintento semanal) */
    public static function adminBuscar(int $id): int
    {
        $antes = count(self::fotosLocales($id));
        $datos = self::datosAtraccion($id);
        if (!$datos) throw new RuntimeException('Atracción no encontrada.');
        $c = self::cacheFile($id);
        if (is_file($c)) {   // olvidar la fecha de la última búsqueda, conservar créditos
            $j = json_decode((string)file_get_contents($c), true) ?: [];
            $j['buscado'] = 0;
            file_put_contents($c, json_encode($j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        self::buscarYGuardar($id, $datos, $antes);
        if (self::$fallosRed) throw new RuntimeException('Wikimedia no respondió; intenta en unos minutos.');
        return count(self::fotosLocales($id)) - $antes;
    }

    /** Quita una foto del banco de referenciales (deja de mostrarse en todas las atracciones que la usaban) */
    public static function adminQuitarReferencial(string $ruta): void
    {
        $archivo = basename($ruta);
        if (!preg_match('/^[a-z0-9-]+_\d+\.jpg$/', $archivo) || !is_file(self::REF_DIR . '/' . $archivo))
            throw new RuntimeException('Foto referencial no encontrada.');
        if (!is_dir(self::DESCARTE . '/_ref')) @mkdir(self::DESCARTE . '/_ref', 0775, true);
        rename(self::REF_DIR . '/' . $archivo, self::DESCARTE . '/_ref/' . $archivo);
    }

    private static function libre(int $id): ?int
    {
        for ($n = 1; $n <= self::MAX_PROPIAS; $n++) if (!is_file(self::archivo($id, $n))) return $n;
        return null;
    }

    /** Deja las fotos propias numeradas 1..k sin huecos ({id}_1.jpg es la principal en todo el sitio) */
    private static function compactar(int $id): void
    {
        $k = 1; $mapa = [];
        for ($n = 1; $n <= self::MAX_PROPIAS; $n++) {
            if (!is_file(self::archivo($id, $n))) continue;
            if ($n !== $k) { rename(self::archivo($id, $n), self::archivo($id, $k)); $mapa[$k] = $n; }
            $k++;
        }
        if ($mapa) self::renumerarCreditos($id, $mapa);
    }

    /** $mapa: nuevo n => n anterior */
    private static function renumerarCreditos(int $id, array $mapa): void
    {
        $c = self::cacheFile($id);
        if (!is_file($c)) return;
        $j = json_decode((string)file_get_contents($c), true) ?: [];
        $inverso = array_flip($mapa);   // n anterior => nuevo n
        foreach ($j['fotos'] ?? [] as &$x) if (isset($inverso[(int)$x['n']])) $x['n'] = $inverso[(int)$x['n']];
        unset($x);
        file_put_contents($c, json_encode($j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /* ════════════════════════ Calidad de fotos propias ═══════════ */

    /**
     * Ancho de una imagen válida; null si está en blanco, es uniforme o no se puede leer.
     * El resultado se guarda en _cache/calidad.json: cada foto se analiza una sola vez.
     */
    private static function anchoValido(string $f): ?int
    {
        static $indice = null, $sucio = false;
        $archivoIdx = self::CACHE_DIR . '/calidad.json';
        if ($indice === null) {
            $indice = is_file($archivoIdx) ? (json_decode((string)@file_get_contents($archivoIdx), true) ?: []) : [];
            register_shutdown_function(function () use (&$indice, &$sucio, $archivoIdx) {
                if (!$sucio) return;
                if (!is_dir(dirname($archivoIdx))) @mkdir(dirname($archivoIdx), 0775, true);
                // Fusionar con lo que otro proceso haya guardado mientras tanto
                $actual = is_file($archivoIdx) ? (json_decode((string)@file_get_contents($archivoIdx), true) ?: []) : [];
                @file_put_contents($archivoIdx, json_encode($indice + $actual), LOCK_EX);
            });
        }
        $clave = basename(dirname($f)) . '/' . basename($f) . '@' . @filemtime($f) . ':' . @filesize($f);
        if (array_key_exists($clave, $indice)) return $indice[$clave];
        $r = self::analizarCalidad($f);
        $indice[$clave] = $r; $sucio = true;
        return $r;
    }

    private static function analizarCalidad(string $f): ?int
    {
        $cache = []; $clave = 'x';

        $info = @getimagesize($f);
        $img  = $info ? @imagecreatefromstring((string)file_get_contents($f)) : null;
        if (!$img) return $cache[$clave] = null;

        $m = imagecreatetruecolor(24, 24);
        imagecopyresampled($m, $img, 0, 0, 0, 0, 24, 24, imagesx($img), imagesy($img));
        $v = [];
        for ($x = 0; $x < 24; $x++) for ($y = 0; $y < 24; $y++) {
            $c = imagecolorat($m, $x, $y);
            $v[] = .299 * (($c >> 16) & 255) + .587 * (($c >> 8) & 255) + .114 * ($c & 255);
        }
        imagedestroy($m); imagedestroy($img);
        $prom = array_sum($v) / count($v);
        $desv = sqrt(array_sum(array_map(fn($x) => ($x - $prom) ** 2, $v)) / count($v));
        return $cache[$clave] = ($desv < 10 ? null : (int)$info[0]);   // imagen casi uniforme = en blanco
    }

    private static function descartar(string $f): void
    {
        if (!is_dir(self::DESCARTE)) @mkdir(self::DESCARTE, 0775, true);
        @rename($f, self::DESCARTE . '/' . basename($f));
        error_log('ImagenAtraccion: foto defectuosa apartada → _descartadas/' . basename($f));
    }

    /* ════════════════════════ Fotos del lugar (Wikimedia) ════════ */

    private static function cacheFile(int $id): string
    {
        return self::CACHE_DIR . "/{$id}.json";
    }

    private static function debeBuscar(int $id): bool
    {
        $f = self::cacheFile($id);
        if (!is_file($f)) return true;
        $c = json_decode((string)file_get_contents($f), true) ?: [];
        if (($c['version'] ?? 1) < 2) return true;                       // búsqueda con el criterio anterior
        return (time() - (int)($c['buscado'] ?? 0)) > self::DIAS_REINTENTO * 86400 && empty($c['fotos']);
    }

    private static function buscarYGuardar(int $id, array $a, int $yaTiene): void
    {
        if (!is_dir(self::CACHE_DIR)) @mkdir(self::CACHE_DIR, 0775, true);
        $lock = fopen(self::CACHE_DIR . "/{$id}.lock", 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { if ($lock) fclose($lock); return; }
        try {
            if (!self::debeBuscar($id)) return;

            $nombre = trim(preg_replace('/\s*\([^)]*\)\s*/', ' ', $a['nombre']));
            $nombre = trim(preg_split('/\s+[–-]\s+/u', $nombre)[0] ?: $nombre);
            $claves = self::palabrasClave($nombre);
            $consultas = array_values(array_unique(["{$nombre} La Paz Bolivia", "{$nombre} Bolivia", $nombre]));

            $fallosAntes = self::$fallosRed;
            $candidatas = []; $vistas = [];
            foreach ($consultas as $q) {
                foreach (self::buscarCommons($q) as $img) {
                    if (isset($vistas[$img['url']])) continue;
                    $vistas[$img['url']] = true;
                    // El título debe nombrar el lugar Y la foto debe ser de Bolivia/La Paz
                    if (!self::coincide($img['titulo'], $claves)) continue;
                    if (!self::esDeBolivia($img['contexto'])) continue;
                    $candidatas[] = $img;
                }
                if (count($candidatas) >= self::MAX_FOTOS) break;
            }

            $guardadas = [];
            $n = 1;
            foreach ($candidatas as $img) {
                while ($n <= self::MAX_FOTOS + 1 && is_file(self::archivo($id, $n))) $n++;   // no pisar fotos propias
                if ($n > self::MAX_FOTOS + 1 || count($guardadas) + $yaTiene >= self::MAX_FOTOS) break;
                if (self::descargar($img['url'], self::archivo($id, $n))) {
                    unset($img['contexto']);
                    $guardadas[] = ['n' => $n] + $img;
                }
            }

            if (!$guardadas && self::$fallosRed > $fallosAntes) return;   // falló la red: reintentar la próxima vez
            file_put_contents(self::cacheFile($id), json_encode([
                'version' => 2, 'buscado' => time(), 'consulta' => $consultas,
                'fuente' => 'Wikimedia Commons', 'fotos' => $guardadas,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /* ════════════════════════ Fotos referenciales ═════════════════ */

    /** [tema, fotos] para el tipo de lugar; si no alcanza, completa con la categoría */
    private static function referenciales(int $id, bool $buscar = false): array
    {
        $cat = intdiv($id, 1000);
        $datos = self::datosAtraccion($id);
        $tema = self::temaDe($datos['nombre'] ?? '');
        $items = [];
        if ($tema) {
            $palabras = self::palabrasTema($tema);
            $items = self::grupo($tema, ["{$tema} Bolivia", "{$tema} La Paz", $tema], $palabras, $buscar);
        }
        if (count($items) < 3 && isset(self::TEMA_CATEGORIA[$cat])) {
            [$consultas, $palabras] = self::TEMA_CATEGORIA[$cat];
            $deCat = self::grupo('categoria-' . $cat, $consultas, $palabras, $buscar);
            $vistos = array_column($items, 'url');
            foreach ($deCat as $it) if (!in_array($it['url'] ?? '', $vistos, true)) $items[] = $it;
            $tema = $tema ?: (self::CATEGORIAS[$cat][0] ?? null);
        }
        return [$tema, $items];
    }

    /** "Vértigo (Club Nocturno - Zona Sur)" → "Club Nocturno"; sin paréntesis → null */
    private static function temaDe(string $nombre): ?string
    {
        if (!preg_match('/\(([^)]+)\)/u', $nombre, $m)) return null;
        $t = trim(preg_split('/\s+[–-]\s+/u', $m[1])[0]);
        if (preg_match('/^[\d.,]+\s*msnm$/iu', $t) || mb_strlen($t) < 4) return null;   // "6.088 msnm" no es un tema
        return $t;
    }

    private static function palabrasTema(string $tema): array
    {
        $out = [];
        foreach (explode(' ', self::normalizar($tema)) as $w) {
            if (strlen($w) < 4) continue;
            $out[] = $w;
            foreach (self::SINONIMOS[$w] ?? [] as $s) $out[] = self::normalizar($s);
        }
        return array_values(array_unique(array_map('trim', $out)));
    }

    /** Grupo de fotos de un tema: se busca una vez y queda guardado en _ref/ */
    private static function grupo(string $nombre, array $consultas, array $palabras, bool $buscar = false): array
    {
        if (!is_dir(self::REF_DIR)) @mkdir(self::REF_DIR, 0775, true);
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', self::normalizar($nombre)), '-') ?: 'general';
        $meta = self::REF_DIR . "/{$slug}.json";
        // Solo fotos verificadas como de Bolivia: nunca se muestran lugares de otros países
        $leer = function () use ($meta) {
            $c = is_file($meta) ? (json_decode((string)file_get_contents($meta), true) ?: []) : [];
            $c['fotos'] = array_values(array_filter($c['fotos'] ?? [],
                fn($f) => !empty($f['bolivia']) && is_file(self::REF_DIR . '/' . $f['archivo'])));
            return $c;
        };
        $c = $leer();
        if (!$buscar || (isset($c['buscado']) && ($c['fotos'] || time() - $c['buscado'] < self::DIAS_REINTENTO * 86400))) return $c['fotos'];

        // Sin espera: si otro proceso ya está buscando este tema, se usa lo que haya
        $lock = fopen(self::REF_DIR . "/{$slug}.lock", 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { if ($lock) fclose($lock); return $c['fotos']; }
        try {
            $c = $leer();
            if (isset($c['buscado']) && $c['fotos']) return $c['fotos'];

            $fallosAntes = self::$fallosRed;
            $candidatas = []; $vistas = [];
            foreach ($consultas as $q) {
                foreach (self::buscarCommons($q) as $img) {
                    if (isset($vistas[$img['url']])) continue;
                    $vistas[$img['url']] = true;
                    if (!self::mencionaAlguna($img['tema'], $palabras)) continue;       // el título o la descripción deben tratar del tema
                    if (!self::esDeBolivia($img['contexto'], $img['titulo'])) continue;                 // y ser de Bolivia
                    $img['bolivia'] = true;
                    unset($img['tema']);
                    $candidatas[] = $img;
                }
                if (count($candidatas) >= self::REF_MAX * 2) break;
            }

            $fotos = [];
            foreach ($candidatas as $img) {
                if (count($fotos) >= self::REF_MAX) break;
                $archivo = "{$slug}_" . (count($fotos) + 1) . '.jpg';
                if (self::descargar($img['url'], self::REF_DIR . '/' . $archivo)) {
                    unset($img['contexto']);
                    $fotos[] = ['archivo' => $archivo] + $img;
                }
            }
            if (!$fotos && self::$fallosRed > $fallosAntes) return [];   // falló la red: reintentar la próxima vez
            file_put_contents($meta, json_encode(['buscado' => time(), 'tema' => $nombre, 'consultas' => $consultas, 'fotos' => $fotos],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $fotos;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /* ════════════════════════ Wikimedia Commons ═══════════════════ */

    /** Fotos horizontales de ≥ 1000 px, sin personas/mapas/logos, con texto de contexto para filtrar */
    private static function buscarCommons(string $q): array
    {
        $url = 'https://commons.wikimedia.org/w/api.php?' . http_build_query([
            'action' => 'query', 'format' => 'json', 'formatversion' => 2,
            'generator' => 'search', 'gsrsearch' => $q . ' filetype:bitmap', 'gsrnamespace' => 6, 'gsrlimit' => 20,
            'prop' => 'imageinfo', 'iiprop' => 'url|mime|size|extmetadata', 'iiurlwidth' => 1600,
            'iiextmetadatafilter' => 'Artist|LicenseShortName|ImageDescription|Categories|ObjectName',
        ]);
        $pages = json_decode((string)self::http($url), true)['query']['pages'] ?? [];
        usort($pages, fn($x, $y) => ($x['index'] ?? 99) <=> ($y['index'] ?? 99));

        $out = [];
        foreach ($pages as $p) {
            $ii = $p['imageinfo'][0] ?? null;
            if (!$ii || !in_array($ii['mime'] ?? '', ['image/jpeg', 'image/png'], true)) continue;
            $w = (int)($ii['width'] ?? 0); $h = (int)($ii['height'] ?? 0);
            if ($w < self::ANCHO_MIN || $h > $w * 0.95 || $h < $w * 0.4) continue;   // buen tamaño, horizontal, no panorámica extrema
            $src = $ii['thumburl'] ?? $ii['url'] ?? '';
            if (!in_array(parse_url($src, PHP_URL_HOST), self::HOSTS_IMAGEN, true)) continue;

            $md = $ii['extmetadata'] ?? [];
            $contexto = self::normalizar(($p['title'] ?? '') . ' ' . strip_tags($md['ImageDescription']['value'] ?? '') . ' '
                       . ($md['Categories']['value'] ?? '') . ' ' . strip_tags($md['ObjectName']['value'] ?? ''));
            if (preg_match(self::EXCLUIR, $contexto)) continue;
            $tema = self::normalizar(($p['title'] ?? '') . ' ' . strip_tags($md['ImageDescription']['value'] ?? '') . ' '
                       . strip_tags($md['ObjectName']['value'] ?? ''));

            $out[] = [
                'url'      => $src,
                'pagina'   => $ii['descriptionurl'] ?? null,
                'titulo'   => $p['title'] ?? '',
                'autor'    => trim(strip_tags($md['Artist']['value'] ?? '')),
                'licencia' => $md['LicenseShortName']['value'] ?? null,
                'contexto' => $contexto,
                'tema'     => $tema,
            ];
        }
        return $out;
    }

    private static function descargar(string $url, string $destino): bool
    {
        if (!in_array(parse_url($url, PHP_URL_HOST), self::HOSTS_IMAGEN, true)) return false;
        $bin = self::http($url, 8 * 1024 * 1024);
        if (!$bin || !@getimagesizefromstring($bin)) return false;
        $img = @imagecreatefromstring($bin);
        if (!$img) return false;
        $ok = imagejpeg($img, $destino, 85);   // re-codificado: solo píxeles, nunca el archivo original
        imagedestroy($img);
        if ($ok && self::anchoValido($destino) === null) { @unlink($destino); return false; }
        return $ok && is_file($destino);
    }

    private static function http(string $url, int $max = 2 * 1024 * 1024): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_USERAGENT => self::UA,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        ]);
        usleep(250000);   // ritmo amable con Wikimedia
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code !== 200) { self::$fallosRed++; return null; }
        return strlen($body) > $max ? null : $body;
    }

    /* ════════════════════════ Utilidades ══════════════════════════ */

    private static function datosAtraccion(int $id): ?array
    {
        static $cache = [];
        if (array_key_exists($id, $cache)) return $cache[$id];
        try {
            require_once __DIR__ . '/database.php';
            $st = Database::Conectar()->prepare("SELECT id, nombre, ciudad, pais FROM catalogo.atracciones WHERE id = ?");
            $st->execute([$id]);
            return $cache[$id] = ($st->fetch(PDO::FETCH_ASSOC) ?: null);
        } catch (Throwable $e) {
            error_log('ImagenAtraccion datos: ' . $e->getMessage());
            return $cache[$id] = null;
        }
    }

    private static function normalizar(string $t): string
    {
        $t = mb_strtolower($t, 'UTF-8');
        $t = strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','ç'=>'c','à'=>'a','è'=>'e','ã'=>'a','õ'=>'o','â'=>'a','ê'=>'e','ô'=>'o']);
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $t));
    }

    /** Palabras distintivas del nombre (sin conectores ni términos genéricos) */
    private static function palabrasClave(string $nombre): array
    {
        static $genericas = ['del','las','los','para','con','por','una','que','paz','bolivia','boliviana','boliviano',
                             'ruta','tour','zona','centro','calle','avenida','plaza','parque','casa','mirador','museo',
                             'nacional','general','normal','completo','combinada','conexion','refugio','msnm','bar','pub','club',
                             'restaurante','restaurant','cafe','pizzeria','cocina','comida'];
        $out = [];
        foreach (explode(' ', self::normalizar($nombre)) as $w) {
            if (strlen($w) >= 4 && !in_array($w, $genericas, true) && !ctype_digit($w)) $out[] = $w;
        }
        if (!$out) foreach (explode(' ', self::normalizar($nombre)) as $w) if (strlen($w) >= 4) $out[] = $w;
        return array_values(array_unique($out));
    }

    private static function coincide(string $titulo, array $claves): bool
    {
        $t = ' ' . self::normalizar($titulo) . ' ';
        foreach ($claves as $c) if (str_contains($t, " {$c} ")) return true;
        return false;
    }

    /** El texto (título, descripción y categorías de Commons, normalizado) ubica la foto en Bolivia */
    private static function esDeBolivia(string $contexto, string $titulo = ''): bool
    {
        $t = self::normalizar($titulo);
        if ($t !== '' && preg_match(self::OTRO_PAIS, $t) && !preg_match(self::CONTEXTO_BO, $t)) return false;
        return (bool)preg_match(self::CONTEXTO_BO, self::normalizar($contexto));
    }

    /* ════════════════ Verificación de fotos ya descargadas ════════════════ */

    /**
     * Vuelve a consultar en Wikimedia el título, la descripción y las categorías de cada foto descargada
     * y conserva solo las de Bolivia:
     *   · referenciales (_ref/*.json): se marcan bolivia = true/false; la galería solo usa las true.
     *   · del lugar ({id}_{n}.jpg con crédito de Commons): las que no son de Bolivia se apartan a _descartadas/.
     * Las fotos subidas por el administrador no se tocan (no tienen crédito de Commons).
     * Devuelve un resumen. Pensado para la línea de comandos (herramientas/verificar_fotos_bolivia.php).
     */
    public static function verificarBolivia(?callable $log = null): array
    {
        $log = $log ?: function ($m) {};
        $res = ['ref_ok' => 0, 'ref_fuera' => 0, 'lugar_ok' => 0, 'lugar_apartadas' => 0, 'sin_respuesta' => 0];

        // 1. Reunir todos los títulos de Commons
        $refs = [];
        foreach (glob(self::REF_DIR . '/*.json') ?: [] as $f) $refs[$f] = json_decode((string)file_get_contents($f), true) ?: [];
        $lugares = [];
        foreach (glob(self::CACHE_DIR . '/*.json') ?: [] as $f) $lugares[$f] = json_decode((string)file_get_contents($f), true) ?: [];
        $titulos = [];
        foreach ([$refs, $lugares] as $grupo) foreach ($grupo as $c) foreach ($c['fotos'] ?? [] as $x) if (!empty($x['titulo'])) $titulos[$x['titulo']] = true;
        $titulos = array_keys($titulos);
        $log(count($titulos) . ' fotos de Wikimedia a verificar');

        // 2. Contexto de cada título (de a 50 por consulta)
        $contexto = [];
        foreach (array_chunk($titulos, 50) as $i => $lote) {
            $url = 'https://commons.wikimedia.org/w/api.php?' . http_build_query([
                'action' => 'query', 'format' => 'json', 'formatversion' => 2, 'titles' => implode('|', $lote),
                'prop' => 'imageinfo|categories', 'cllimit' => 'max', 'iiprop' => 'extmetadata',
                'iiextmetadatafilter' => 'ImageDescription|Categories|ObjectName',
            ]);
            $pages = json_decode((string)self::http($url), true)['query']['pages'] ?? null;
            if ($pages === null) { $log("lote $i sin respuesta"); continue; }
            foreach ($pages as $p) {
                $md = $p['imageinfo'][0]['extmetadata'] ?? [];
                $cats = implode(' ', array_map(fn($c) => $c['title'] ?? '', $p['categories'] ?? []));
                $contexto[$p['title']] = ($p['title'] ?? '') . ' ' . strip_tags($md['ImageDescription']['value'] ?? '') . ' '
                    . ($md['Categories']['value'] ?? '') . ' ' . strip_tags($md['ObjectName']['value'] ?? '') . ' ' . $cats;
            }
            $log('lote ' . ($i + 1) . ' de ' . ceil(count($titulos) / 50) . ': ' . count($pages) . ' páginas');
        }
        $log(count($contexto) . ' fotos con datos de Wikimedia');
        // La API puede normalizar títulos (espacios/guiones bajos): buscar también así
        $ctx = function (string $t) use ($contexto) {
            return $contexto[$t] ?? $contexto[str_replace('_', ' ', $t)] ?? null;
        };

        // 3. Referenciales: marcar
        foreach ($refs as $f => $c) {
            foreach ($c['fotos'] ?? [] as $k => $x) {
                $texto = $ctx($x['titulo'] ?? '');
                if ($texto === null) { $res['sin_respuesta']++; $c['fotos'][$k]['bolivia'] = false; continue; }
                $ok = self::esDeBolivia($texto, $x['titulo'] ?? '') && !preg_match(self::EXCLUIR, self::normalizar($texto));   // de Bolivia y sin gráficos, personas, fósiles…
                $c['fotos'][$k]['bolivia'] = $ok;
                $res[$ok ? 'ref_ok' : 'ref_fuera']++;
            }
            $c['verificado_bolivia'] = time();
            // Sin ninguna foto de Bolivia: se olvida la búsqueda anterior para volver a buscar (precargar_fotos.php)
            if (!array_filter($c['fotos'] ?? [], fn($x) => !empty($x['bolivia']))) unset($c['buscado']);
            file_put_contents($f, json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        // 4. Del lugar: apartar las que no son de Bolivia
        if (!is_dir(self::DESCARTE)) @mkdir(self::DESCARTE, 0775, true);
        foreach ($lugares as $f => $c) {
            $id = (int)basename($f, '.json');
            $quedan = [];
            foreach ($c['fotos'] ?? [] as $x) {
                $texto = $ctx($x['titulo'] ?? '');
                if ($texto !== null && !self::esDeBolivia($texto)) {
                    $img = self::archivo($id, (int)$x['n']);
                    if (is_file($img)) @rename($img, self::DESCARTE . '/' . basename($img, '.jpg') . '_no_bolivia_' . time() . '.jpg');
                    $res['lugar_apartadas']++;
                    $log("apartada {$id}_{$x['n']}: {$x['titulo']}");
                    continue;
                }
                if ($texto === null) $res['sin_respuesta']++; else $res['lugar_ok']++;
                $quedan[] = $x;
            }
            $c['fotos'] = $quedan;
            $c['verificado_bolivia'] = time();
            file_put_contents($f, json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $res;
    }

    private static function mencionaAlguna(string $contexto, array $palabras): bool
    {
        foreach ($palabras as $p) {
            if ($p !== '' && preg_match('/\b' . preg_quote($p, '/') . '(s|es)?\b/u', $contexto)) return true;
        }
        return false;
    }

    private static function enviarArchivo(string $ruta): void
    {
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . filesize($ruta));
        readfile($ruta);
    }

    /** Último recurso: imagen generada con el nombre (nunca un enlace roto) */
    private static function enviarGenerada(int $id, ?array $a): void
    {
        [$cat, $c1, $c2, $ico] = self::CATEGORIAS[intdiv($id, 1000)] ?? ['Atracción', '#1e293b', '#475569', '📍'];
        $nombre = htmlspecialchars($a['nombre'] ?? 'Etreva Travel', ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $cat    = htmlspecialchars($cat, ENT_XML1, 'UTF-8');
        $lineas = [];
        foreach (explode("\n", wordwrap(html_entity_decode($nombre, ENT_XML1, 'UTF-8'), 26, "\n", true)) as $l) {
            if (count($lineas) === 3) { $lineas[2] .= '…'; break; }
            $lineas[] = htmlspecialchars($l, ENT_XML1, 'UTF-8');
        }
        $y0 = 470 - (count($lineas) - 1) * 30;
        $texto = '';
        foreach ($lineas as $i => $l) {
            $texto .= '<text x="600" y="' . ($y0 + $i * 62) . '" text-anchor="middle" font-size="52" font-weight="700" fill="#fff">' . $l . '</text>';
        }
        header('Content-Type: image/svg+xml; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800" font-family="Segoe UI, Arial, sans-serif">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{$c1}"/><stop offset="1" stop-color="{$c2}"/></linearGradient>
    <pattern id="p" width="60" height="60" patternUnits="userSpaceOnUse"><path d="M0 60 L60 0" stroke="#fff" stroke-opacity=".06" stroke-width="2"/></pattern>
  </defs>
  <rect width="1200" height="800" fill="url(#g)"/>
  <rect width="1200" height="800" fill="url(#p)"/>
  <path d="M0 800 L260 520 L420 650 L640 380 L880 620 L1020 500 L1200 660 L1200 800 Z" fill="#000" fill-opacity=".18"/>
  <text x="600" y="290" text-anchor="middle" font-size="150">{$ico}</text>
  {$texto}
  <text x="600" y="700" text-anchor="middle" font-size="28" fill="#fff" fill-opacity=".8" letter-spacing="4">{$cat} · LA PAZ, BOLIVIA</text>
</svg>
SVG;
    }
}
