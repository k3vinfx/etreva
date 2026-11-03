<?php
/*
 * herramientas/traducir_atracciones.php  (solo línea de comandos)
 * ─────────────────────────────────────────────────────────────────
 * Llena catalogo.atracciones_traducciones para pt, fr y de traduciendo desde
 * el español original, y traduce los nombres de categorías/subcategorías.
 * Las traducciones marcadas 'manual' nunca se sobrescriben.
 *
 *   php herramientas/traducir_atracciones.php            → idiomas pt fr de
 *   php herramientas/traducir_atracciones.php fr         → solo francés
 *   php herramientas/traducir_atracciones.php fr --todo  → rehace también las ya traducidas
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

chdir(__DIR__ . '/..');
require 'model/database.php';
$pdo = Database::Conectar();

$args     = array_slice($argv, 1);
$rehacer  = in_array('--todo', $args, true);
$soloNom  = in_array('--nombres', $args, true);   // rehace solo los nombres (conservando nombres propios)
$idiomas  = array_values(array_intersect(array_diff($args, ['--todo', '--nombres']), ['pt', 'fr', 'de'])) ?: ['pt', 'fr', 'de'];
$destino  = ['pt' => 'pt-BR', 'fr' => 'fr', 'de' => 'de'];

/** Traduce una lista de textos (agrupados por petición). Devuelve el mismo orden. */
function traducir(array $textos, string $de, string $a): array
{
    $out = [];
    $lote = []; $largo = 0;
    $vaciar = function () use (&$lote, &$largo, &$out, $de, $a) {
        if (!$lote) return;
        $res = pedir(implode("\n", $lote), $de, $a);
        $lineas = $res === null ? [] : explode("\n", $res);
        if (count($lineas) === count($lote)) {
            foreach ($lineas as $l) $out[] = trim($l);
        } else {                       // si el lote no cuadra, uno por uno
            foreach ($lote as $t) $out[] = trim((string)(pedir($t, $de, $a) ?? $t));
        }
        $lote = []; $largo = 0;
    };
    foreach ($textos as $t) {
        $t = trim(preg_replace('/\s*\R\s*/u', ' ', (string)$t));
        if ($largo + strlen($t) > 3500) $vaciar();
        $lote[] = $t; $largo += strlen($t) + 1;
    }
    $vaciar();
    return $out;
}

function pedir(string $texto, string $de, string $a): ?string
{
    if (trim($texto) === '') return $texto;
    for ($intento = 1; $intento <= 4; $intento++) {
        $ch = curl_init('https://translate.googleapis.com/translate_a/single?' . http_build_query([
            'client' => 'gtx', 'sl' => $de, 'tl' => $a, 'dt' => 't', 'q' => $texto,
        ]));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_USERAGENT => 'Mozilla/5.0']);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code === 200 && ($j = json_decode((string)$body, true)) && is_array($j[0] ?? null)) {
            usleep(350000); // respetar el servicio
            return implode('', array_map(fn($s) => $s[0] ?? '', $j[0]));
        }
        sleep(3 * $intento);
    }
    fwrite(STDERR, "  ! no se pudo traducir un texto a {$a}\n");
    return null;
}

/**
 * Nombres: el nombre propio NO se traduce ("El Montículo", "Choro Takesi");
 * solo la parte descriptiva entre paréntesis: "El Montículo (Parque y Mirador - Sopocachi)".
 */
function traducirNombres(array $nombres, string $a): array
{
    $partes = [];
    foreach ($nombres as $i => $n) {
        if (preg_match('/^(.*?)\s*\((.+)\)\s*$/u', (string)$n, $m)) $partes[$i] = [$m[1], $m[2]];
    }
    $trad = $partes ? traducir(array_column($partes, 1), 'es', $a) : [];
    $out = []; $k = 0;
    foreach ($nombres as $i => $n) {
        $out[$i] = isset($partes[$i]) ? $partes[$i][0] . ' (' . ($trad[$k++] ?: $partes[$i][1]) . ')' : $n;
    }
    return $out;
}

if ($soloNom) {
    foreach ($idiomas as $lang) {
        $rows = $pdo->query("SELECT a.id, a.nombre FROM catalogo.atracciones a JOIN catalogo.atracciones_traducciones t
                             ON t.atraccion_id = a.id AND t.idioma = '{$lang}' AND t.origen <> 'manual' ORDER BY a.id")->fetchAll(PDO::FETCH_ASSOC);
        $upd = $pdo->prepare("UPDATE catalogo.atracciones_traducciones SET nombre = ?, updated_at = NOW() WHERE atraccion_id = ? AND idioma = ?");
        foreach (array_chunk($rows, 60) as $grupo) {
            $nuevos = traducirNombres(array_column($grupo, 'nombre'), $destino[$lang]);
            foreach ($grupo as $k => $r) $upd->execute([$nuevos[$k], $r['id'], $lang]);
        }
        echo "[$lang] nombres corregidos: " . count($rows) . "
";
    }
    exit;
}

/* ── Atracciones ─────────────────────────────────────────────── */
$base = $pdo->query("SELECT id, nombre, descripcion, direccion, tiempo_estimado, ubicacion FROM catalogo.atracciones ORDER BY id")
            ->fetchAll(PDO::FETCH_ASSOC);

foreach ($idiomas as $lang) {
    $ya = $pdo->prepare("SELECT atraccion_id, origen FROM catalogo.atracciones_traducciones WHERE idioma = ?");
    $ya->execute([$lang]);
    $existentes = $ya->fetchAll(PDO::FETCH_KEY_PAIR);

    $pendientes = array_values(array_filter($base, fn($r) =>
        ($existentes[$r['id']] ?? null) !== 'manual' && ($rehacer || !isset($existentes[$r['id']]))));
    echo "[$lang] atracciones a traducir: " . count($pendientes) . "\n";

    // Diccionario de frases cortas repetidas (tiempos, zonas) para no traducirlas 570 veces
    $frases = [];
    foreach ($pendientes as $r) {
        foreach ((json_decode($r['tiempo_estimado'] ?? '{}', true) ?: []) as $v) if (is_string($v)) $frases[$v] = true;
        $zona = (json_decode($r['ubicacion'] ?? '{}', true) ?: [])['zona'] ?? null;
        if (is_string($zona)) $frases[$zona] = true;
    }
    $frases = array_keys($frases);
    $dicc = $frases ? array_combine($frases, traducir($frases, 'es', $destino[$lang])) : [];

    $guardar = $pdo->prepare("
        INSERT INTO catalogo.atracciones_traducciones
               (atraccion_id, idioma, nombre, descripcion, direccion, tiempo_estimado, ubicacion, origen, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'automatica', NOW())
        ON CONFLICT (atraccion_id, idioma) DO UPDATE SET
            nombre = EXCLUDED.nombre, descripcion = EXCLUDED.descripcion, direccion = EXCLUDED.direccion,
            tiempo_estimado = EXCLUDED.tiempo_estimado, ubicacion = EXCLUDED.ubicacion, updated_at = NOW()
        WHERE catalogo.atracciones_traducciones.origen <> 'manual'");

    foreach (array_chunk($pendientes, 40) as $i => $grupo) {
        $nombres = traducirNombres(array_column($grupo, 'nombre'), $destino[$lang]);
        $descs   = traducir(array_column($grupo, 'descripcion'), 'es', $destino[$lang]);
        $dirs    = traducir(array_map(fn($r) => $r['direccion'] ?? '', $grupo), 'es', $destino[$lang]);

        $pdo->beginTransaction();
        foreach ($grupo as $k => $r) {
            $tiempo = json_decode($r['tiempo_estimado'] ?? 'null', true);
            if (is_array($tiempo)) foreach ($tiempo as $c => $v) if (is_string($v) && isset($dicc[$v])) $tiempo[$c] = $dicc[$v];
            $zona = (json_decode($r['ubicacion'] ?? '{}', true) ?: [])['zona'] ?? null;

            $guardar->execute([
                $r['id'], $lang,
                $nombres[$k] ?: $r['nombre'], $descs[$k] ?: $r['descripcion'], ($dirs[$k] ?? '') ?: $r['direccion'],
                $tiempo ? json_encode($tiempo, JSON_UNESCAPED_UNICODE) : null,
                is_string($zona) ? json_encode(['zona' => $dicc[$zona] ?? $zona], JSON_UNESCAPED_UNICODE) : null,
            ]);
        }
        $pdo->commit();
        echo "[$lang] " . min(($i + 1) * 40, count($pendientes)) . "/" . count($pendientes) . "\n";
    }

    /* ── Categorías y subcategorías (desde el inglés, que ya es manual) ─ */
    $cats = $pdo->query("SELECT id, categoria_rangos, subcategoria_rangos FROM catalogo.atracciones_categorias_en ORDER BY id")
                ->fetchAll(PDO::FETCH_ASSOC);
    $nombresCat = [];
    foreach ($cats as $c) {
        foreach (json_decode($c['categoria_rangos'] ?? '[]', true) ?: [] as $r) if (!empty($r['categoria'])) $nombresCat[$r['categoria']] = true;
        foreach (json_decode($c['subcategoria_rangos'] ?? '[]', true) ?: [] as $r) if (!empty($r['subcategoria'])) $nombresCat[$r['subcategoria']] = true;
    }
    $nombresCat = array_keys($nombresCat);
    $tradCat = $nombresCat ? array_combine($nombresCat, traducir($nombresCat, 'en', $destino[$lang])) : [];
    $upd = $pdo->prepare("UPDATE catalogo.atracciones_categorias_{$lang} SET categoria_rangos = ?, subcategoria_rangos = ? WHERE id = ?");
    foreach ($cats as $c) {
        $cr = json_decode($c['categoria_rangos'] ?? '[]', true) ?: [];
        $sr = json_decode($c['subcategoria_rangos'] ?? 'null', true);
        foreach ($cr as &$r) if (isset($r['categoria'], $tradCat[$r['categoria']])) $r['categoria'] = $tradCat[$r['categoria']];
        unset($r);
        if (is_array($sr)) { foreach ($sr as &$r) if (isset($r['subcategoria'], $tradCat[$r['subcategoria']])) $r['subcategoria'] = $tradCat[$r['subcategoria']]; unset($r); }
        $upd->execute([json_encode($cr, JSON_UNESCAPED_UNICODE), $sr === null ? null : json_encode($sr, JSON_UNESCAPED_UNICODE), $c['id']]);
    }
    echo "[$lang] categorías traducidas: " . count($tradCat) . "\n";
}
echo "Listo.\n";
