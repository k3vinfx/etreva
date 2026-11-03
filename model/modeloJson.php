<?php
/**
 * model/modeloJson.php
 * Exporta / importa UN modelo MR completo en JSON, tal como está guardado:
 *   { formato, version_formato, exportado, modelo: {id, nombre, version, descripcion, estado, fecha_entrenamiento},
 *     configuracion_json: {...}, metricas_desempeno: {...} }
 *
 * Importar reemplaza el modelo por el contenido del archivo (es el modelo completo), después de validarlo
 * y mostrar los cambios. Antes de guardar se deja una copia en sql_backups/modelos/.
 */
class ModeloJson
{
    public const FORMATO = 'etreva-modelo-mr';
    private const CAMPOS = ['nombre', 'version', 'descripcion', 'estado', 'fecha_entrenamiento'];
    private const ESTADOS = ['activo', 'inactivo', 'entrenando', 'borrador'];

    private PDO $pdo;
    public function __construct() { $this->pdo = Database::Conectar(); }

    private function fila(int $id): array
    {
        $st = $this->pdo->prepare("SELECT * FROM recomendaciones.modelos_mr WHERE id = ?");
        $st->execute([$id]);
        $f = $st->fetch(PDO::FETCH_ASSOC);
        if (!$f) throw new RuntimeException('Modelo no encontrado.');
        return $f;
    }

    public function exportar(int $id): array
    {
        $f = $this->fila($id);
        $modelo = ['id' => (int)$f['id']];
        foreach (self::CAMPOS as $c) $modelo[$c] = $f[$c];
        return [
            'formato'            => self::FORMATO,
            'version_formato'    => 1,
            'exportado'          => date('c'),
            'modelo'             => $modelo,
            'configuracion_json' => json_decode($f['configuracion_json'] ?? '', true) ?: new stdClass(),
            'metricas_desempeno' => json_decode($f['metricas_desempeno'] ?? '', true) ?: new stdClass(),
        ];
    }

    public function nombreArchivo(int $id): string
    {
        $f = $this->fila($id);
        $slug = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $f['nombre']) ?: 'modelo')), '_');
        return "modelo_{$id}_{$slug}_" . date('Y-m-d') . '.json';
    }

    /** Dataset: puede estar en métricas o en configuración según el modelo */
    private static function dataset(array $cfg, array $met): array
    {
        return $met['dataset_entrenamiento'] ?? $cfg['dataset_entrenamiento'] ?? $cfg['dataset'] ?? [];
    }

    /**
     * Valida el archivo y describe los cambios; con $simular = false además guarda.
     * Devuelve ['cambios','avisos','errores','modelo','guardado']
     */
    public function importar(int $id, string $texto, bool $simular): array
    {
        $actual = $this->fila($id);
        if (strncmp($texto, "\xEF\xBB\xBF", 3) === 0) $texto = substr($texto, 3);
        $d = json_decode($texto, true);
        if (!is_array($d)) throw new RuntimeException('El archivo no es un JSON válido: ' . json_last_error_msg());

        // También se acepta el JSON "pelado" de configuración + métricas
        $cfg = $d['configuracion_json'] ?? null;
        $met = $d['metricas_desempeno'] ?? null;
        $mod = $d['modelo'] ?? [];
        if (!is_array($cfg) || !is_array($met))
            throw new RuntimeException('Faltan "configuracion_json" y "metricas_desempeno". Usa un archivo de "Exportar modelo (JSON)".');

        $errores = []; $avisos = [];
        if (($d['formato'] ?? self::FORMATO) !== self::FORMATO) $avisos[] = 'El archivo no indica el formato de Etreva; se importará igual si es válido.';
        if (isset($mod['id']) && (int)$mod['id'] !== $id) $avisos[] = "El archivo es del modelo #{$mod['id']}; se importará sobre el modelo #$id.";
        if (array_key_exists('nombre', $mod) && trim((string)$mod['nombre']) === '') $errores[] = 'El nombre del modelo no puede quedar vacío.';
        if (!empty($mod['estado']) && !in_array($mod['estado'], self::ESTADOS, true))
            $errores[] = "Estado inválido \"{$mod['estado']}\" (usa " . implode(', ', self::ESTADOS) . ').';

        // Alternativas
        $alts = $cfg['alternativas'] ?? null;
        if (!is_array($alts) || !array_is_list($alts)) { $errores[] = '"configuracion_json.alternativas" debe ser una lista.'; $alts = []; }
        $ids = [];
        foreach ($alts as $i => $a) {
            if (!is_array($a) || !isset($a['id']) || !is_numeric($a['id'])) { $errores[] = "Alternativa #" . ($i + 1) . ' sin "id" numérico.'; continue; }
            if (empty($a['nombre'])) $errores[] = "Alternativa {$a['id']} sin nombre.";
            $ids[] = (int)$a['id'];
        }
        foreach (array_count_values($ids) as $aid => $n) if ($n > 1) $errores[] = "La alternativa $aid está repetida.";

        // Dataset
        $ds = self::dataset($cfg, $met);
        if (!is_array($ds) || !array_is_list($ds)) { $errores[] = 'El dataset de entrenamiento debe ser una lista.'; $ds = []; }
        $largos = [];
        foreach ($ds as $i => $e) {
            if (!is_array($e) || !isset($e['input']) || !is_array($e['input'])) { $errores[] = 'Dataset #' . ($i + 1) . ': falta "input" (lista de números).'; continue; }
            foreach ($e['input'] as $v) if (!is_numeric($v)) { $errores[] = 'Dataset #' . ($i + 1) . ': "input" tiene valores no numéricos.'; break; }
            $largos[count($e['input'])] = true;
        }
        $feat = (int)($met['features_por_input'] ?? 0);
        if (count($largos) > 1) $errores[] = 'Los vectores "input" no tienen todos el mismo largo (' . implode(', ', array_keys($largos)) . ').';
        elseif ($largos && $feat && array_key_first($largos) !== $feat)
            $avisos[] = 'Los vectores tienen ' . array_key_first($largos) . " valores y features_por_input dice $feat.";
        $sinAlt = array_unique(array_diff(array_map(fn($e) => (int)(is_array($e['output'] ?? null) ? ($e['output'][0] ?? 0) : ($e['output'] ?? 0)), $ds), $ids));
        if ($ds && $sinAlt) $avisos[] = count($sinAlt) . ' ejemplo(s) del dataset apuntan a alternativas que no están: ' . implode(', ', array_slice($sinAlt, 0, 8));
        if (isset($met['total_alternativas']) && (int)$met['total_alternativas'] !== count($alts))
            $avisos[] = "total_alternativas dice {$met['total_alternativas']} pero hay " . count($alts) . ' alternativas (se corregirá al guardar).';

        // Resumen de cambios frente a lo guardado
        $cfgA = json_decode($actual['configuracion_json'] ?? '', true) ?: [];
        $metA = json_decode($actual['metricas_desempeno'] ?? '', true) ?: [];
        $antes = [];
        foreach ($cfgA['alternativas'] ?? [] as $a) if (isset($a['id'])) $antes[(int)$a['id']] = $a;
        $nuevas = 0; $mod_ = 0;
        foreach ($alts as $a) {
            if (!isset($a['id'])) continue;
            if (!isset($antes[(int)$a['id']])) $nuevas++;
            elseif (json_encode($antes[(int)$a['id']]) !== json_encode($a)) $mod_++;
        }
        $quit = count(array_diff(array_keys($antes), $ids));
        $cambios = [
            'Alternativas: ' . count($alts) . " en el archivo · $nuevas nuevas · $mod_ modificadas · $quit se quitan",
            'Dataset de entrenamiento: ' . count($ds) . ' ejemplos (ahora tiene ' . count(self::dataset($cfgA, $metA)) . ')',
        ];
        $difC = array_keys(array_filter($cfg, fn($v, $k) => $k !== 'alternativas' && json_encode($v) !== json_encode($cfgA[$k] ?? null), ARRAY_FILTER_USE_BOTH));
        $difM = array_keys(array_filter($met, fn($v, $k) => $k !== 'dataset_entrenamiento' && json_encode($v) !== json_encode($metA[$k] ?? null), ARRAY_FILTER_USE_BOTH));
        $faltan = array_merge(array_diff(array_keys($cfgA), array_keys($cfg)), array_diff(array_keys($metA), array_keys($met)));
        $cambios[] = 'Configuración y métricas: ' . (($difC || $difM) ? 'cambian ' . implode(', ', array_merge($difC, $difM)) : 'sin otros cambios');
        if ($faltan) $avisos[] = 'El archivo no trae: ' . implode(', ', $faltan) . ' (se quitarán del modelo).';
        $difMod = [];
        foreach (self::CAMPOS as $c) if (array_key_exists($c, $mod) && (string)$mod[$c] !== (string)$actual[$c]) $difMod[] = $c;
        $cambios[] = 'Datos del modelo: ' . ($difMod ? 'cambian ' . implode(', ', $difMod) : 'sin cambios');

        $res = ['cambios' => $cambios, 'avisos' => $avisos, 'errores' => $errores, 'modelo' => $actual['nombre'], 'guardado' => false];
        if ($simular || $errores) return $res;

        // Guardar (con copia previa)
        $met['total_alternativas'] = count($alts);
        if (isset($cfg['metadata']) && is_array($cfg['metadata'])) $cfg['metadata']['total_alternativas'] = count($alts);
        $dir = __DIR__ . '/../sql_backups/modelos';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents("$dir/modelo_{$id}_" . date('Ymd_His') . '_antes_importar.json', json_encode($actual, JSON_UNESCAPED_UNICODE));

        $set = ['configuracion_json = ?::jsonb', 'metricas_desempeno = ?::jsonb', 'updated_at = NOW()'];
        $params = [json_encode($cfg, JSON_UNESCAPED_UNICODE), json_encode($met, JSON_UNESCAPED_UNICODE)];
        foreach (self::CAMPOS as $c) if (array_key_exists($c, $mod)) {
            $set[] = "$c = ?";
            $params[] = ($mod[$c] === '' || $mod[$c] === null) && $c !== 'descripcion' ? null : $mod[$c];
        }
        $params[] = $id;
        $this->pdo->prepare('UPDATE recomendaciones.modelos_mr SET ' . implode(', ', $set) . ' WHERE id = ?')->execute($params);
        $res['guardado'] = true;
        return $res;
    }
}
