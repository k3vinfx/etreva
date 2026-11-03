<?php
/**
 * model/csvAdmin.php
 * Exportación e importación CSV genérica para las tablas que gestiona el administrador.
 *
 * - Exportar: todas las filas, encabezados = nombres de columna (así el mismo archivo se puede reimportar).
 * - Importar: por cada fila, si trae la clave y existe → UPDATE de las columnas presentes; si no → INSERT.
 *   Cada fila va en su propio SAVEPOINT: una fila con error no tumba el resto, y se informa línea y motivo.
 *   Con $simular = true todo se revierte al final (sirve para validar antes de guardar).
 */
class CsvAdmin
{
    /** entidad => tabla, clave(s), columnas que no se importan, si admite importación */
    public const ENTIDADES = [
        'parametros'      => ['tabla' => 'recomendaciones.catalogo_parametros', 'clave' => ['id'], 'titulo' => 'catalogo_parametros'],
        'contexto'        => ['tabla' => 'recomendaciones.contexto_relacion',   'clave' => ['id'], 'titulo' => 'contexto_relacion'],
        'recomendaciones' => ['tabla' => 'recomendaciones.recomendaciones',     'clave' => ['id'], 'titulo' => 'recomendaciones'],
        'categorias'      => ['tabla' => 'catalogo.atraccion_categorias',       'clave' => ['id'], 'titulo' => 'categorias'],
        'atracciones'     => ['tabla' => 'catalogo.atracciones',                'clave' => ['id'], 'titulo' => 'atracciones'],
        'traducciones'    => ['tabla' => 'catalogo.atracciones_traducciones',   'clave' => ['atraccion_id', 'idioma'], 'titulo' => 'atracciones_traducciones'],
        'resenas'         => ['tabla' => 'itinerarios.back_testing_mr',         'clave' => ['id'], 'titulo' => 'resenas_backtesting', 'soloExportar' => true],
    ];
    private const AUTOMATICAS = ['created_at', 'updated_at', 'fecha_creacion'];
    public const MAX_FILAS = 20000;

    private PDO $pdo;
    public function __construct() { $this->pdo = Database::Conectar(); }

    public static function config(string $entidad): ?array { return self::ENTIDADES[$entidad] ?? null; }

    /** Columnas de la tabla: nombre => ['tipo' => udt, 'default' => bool, 'nulo' => bool] */
    public function columnas(string $tabla): array
    {
        [$esq, $tab] = explode('.', $tabla);
        $st = $this->pdo->prepare("SELECT column_name, udt_name, column_default IS NOT NULL d, is_nullable = 'YES' n
                                   FROM information_schema.columns WHERE table_schema = ? AND table_name = ? ORDER BY ordinal_position");
        $st->execute([$esq, $tab]);
        $c = [];
        foreach ($st as $r) $c[$r['column_name']] = ['tipo' => $r['udt_name'], 'default' => (bool)$r['d'], 'nulo' => (bool)$r['n']];
        return $c;
    }

    /** Escribe el CSV completo en la salida (BOM + ';' para que Excel en español lo abra en columnas) */
    public function exportar(string $entidad): void
    {
        $cfg  = self::config($entidad);
        $cols = array_keys($this->columnas($cfg['tabla']));
        $orden = implode(', ', array_map(fn($k) => '"' . $k . '"', $cfg['clave']));
        $st = $this->pdo->query('SELECT ' . implode(', ', array_map(fn($c) => '"' . $c . '"', $cols)) . " FROM {$cfg['tabla']} ORDER BY $orden");

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $cols, ';');
        while ($r = $st->fetch(PDO::FETCH_NUM)) {
            foreach ($r as &$v) if (is_bool($v)) $v = $v ? 'true' : 'false';
            fputcsv($out, $r, ';');
        }
        fclose($out);
    }

    /** Lee el archivo subido: detecta separador (; , o tabulación) y quita el BOM */
    public static function leer(string $ruta): array
    {
        $txt = file_get_contents($ruta);
        if ($txt === false || trim($txt) === '') throw new RuntimeException('El archivo está vacío.');
        if (strncmp($txt, "\xEF\xBB\xBF", 3) === 0) $txt = substr($txt, 3);
        if (!mb_check_encoding($txt, 'UTF-8')) $txt = mb_convert_encoding($txt, 'UTF-8', 'Windows-1252');

        $primera = strtok($txt, "\n");
        $sep = ';';
        $max = -1;
        foreach ([';', ',', "\t"] as $s) {
            $n = substr_count($primera, $s);
            if ($n > $max) { $max = $n; $sep = $s; }
        }
        $h = fopen('php://temp', 'r+');
        fwrite($h, $txt); rewind($h);
        $filas = [];
        while (($r = fgetcsv($h, 0, $sep)) !== false) {
            if ($r === [null] || (count($r) === 1 && trim((string)$r[0]) === '')) continue;
            $filas[] = $r;
            if (count($filas) > self::MAX_FILAS + 1) throw new RuntimeException('Máximo ' . self::MAX_FILAS . ' filas por archivo.');
        }
        fclose($h);
        if (count($filas) < 2) throw new RuntimeException('El archivo no tiene filas de datos.');
        return $filas;
    }

    /**
     * Importa las filas. Devuelve ['insertadas','actualizadas','errores' => [[linea, mensaje]], 'ignoradas' => [col]]
     */
    public function importar(string $entidad, array $filas, bool $simular): array
    {
        $cfg = self::config($entidad);
        if (!empty($cfg['soloExportar'])) throw new RuntimeException('Esta tabla solo se puede exportar.');
        $cols = $this->columnas($cfg['tabla']);

        $enc = array_map(fn($h) => strtolower(trim((string)$h)), array_shift($filas));
        $usar = []; $ignoradas = [];
        foreach ($enc as $i => $h) {
            if (isset($cols[$h]) && !in_array($h, self::AUTOMATICAS, true)) $usar[$i] = $h;
            elseif ($h !== '') $ignoradas[] = $h;
        }
        if (!array_diff($usar, $cfg['clave'])) throw new RuntimeException('Ningún encabezado coincide con las columnas de la tabla. Exporta primero el CSV para ver el formato.');
        foreach ($cfg['clave'] as $k) {
            if (!$cols[$k]['default'] && !in_array($k, $usar, true))
                throw new RuntimeException("Falta la columna obligatoria \"$k\".");
        }
        $tieneUpdated = isset($cols['updated_at']);

        $res = ['insertadas' => 0, 'actualizadas' => 0, 'errores' => [], 'ignoradas' => $ignoradas, 'total' => count($filas)];
        $this->pdo->beginTransaction();
        try {
            foreach ($filas as $n => $fila) {
                $linea = $n + 2;
                $datos = [];
                try {
                    foreach ($usar as $i => $col) $datos[$col] = $this->valor($fila[$i] ?? '', $cols[$col], $col);
                } catch (InvalidArgumentException $e) {
                    $res['errores'][] = [$linea, $e->getMessage()];
                    continue;
                }

                $this->pdo->exec('SAVEPOINT fila');
                try {
                    $claveCompleta = true;
                    foreach ($cfg['clave'] as $k) if (!isset($datos[$k]) || $datos[$k] === null) $claveCompleta = false;

                    $actualizado = false;
                    if ($claveCompleta) {
                        $set = array_diff_key($datos, array_flip($cfg['clave']));
                        $where = implode(' AND ', array_map(fn($k) => "\"$k\" = :k_$k", $cfg['clave']));
                        $params = [];
                        foreach ($cfg['clave'] as $k) $params["k_$k"] = $datos[$k];
                        if ($set) {
                            $sets = [];
                            foreach ($set as $c => $v) { $sets[] = "\"$c\" = :v_$c"; $params["v_$c"] = $v; }
                            if ($tieneUpdated) $sets[] = 'updated_at = NOW()';
                            $st = $this->pdo->prepare("UPDATE {$cfg['tabla']} SET " . implode(', ', $sets) . " WHERE $where");
                        } else {
                            $st = $this->pdo->prepare("SELECT 1 FROM {$cfg['tabla']} WHERE $where");
                        }
                        $st->execute($params);
                        $actualizado = $st->rowCount() > 0;
                    }
                    if (!$actualizado) {
                        $ins = array_filter($datos, fn($v, $c) => !($v === null && $cols[$c]['default']), ARRAY_FILTER_USE_BOTH);
                        foreach ($cols as $c => $m) {
                            if (!$m['nulo'] && !$m['default'] && !array_key_exists($c, $ins))
                                throw new InvalidArgumentException("falta el valor de \"$c\"");
                        }
                        $st = $this->pdo->prepare("INSERT INTO {$cfg['tabla']} (" . implode(', ', array_map(fn($c) => "\"$c\"", array_keys($ins))) . ')
                                                   VALUES (' . implode(', ', array_map(fn($c) => ":v_$c", array_keys($ins))) . ')');
                        $st->execute(array_combine(array_map(fn($c) => "v_$c", array_keys($ins)), array_values($ins)));
                        $res['insertadas']++;
                    } else {
                        $res['actualizadas']++;
                    }
                    $this->pdo->exec('RELEASE SAVEPOINT fila');
                } catch (Throwable $e) {
                    $this->pdo->exec('ROLLBACK TO SAVEPOINT fila');
                    $res['errores'][] = [$linea, self::mensaje($e)];
                }
                if (count($res['errores']) >= 200) { $res['errores'][] = [0, 'Demasiados errores; se detuvo la revisión.']; break; }
            }

            // Si se insertaron ids a mano, adelantar la secuencia para que los nuevos registros no choquen
            if (!$simular && isset($cols['id']) && $cols['id']['default']) {
                $this->pdo->query("SELECT setval(pg_get_serial_sequence('{$cfg['tabla']}', 'id'), GREATEST((SELECT COALESCE(MAX(id), 1) FROM {$cfg['tabla']}), 1))");
            }
            $simular ? $this->pdo->rollBack() : $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
        return $res;
    }

    /** Convierte el texto de la celda al tipo de la columna; lanza InvalidArgumentException si no es válido */
    private function valor(string $v, array $meta, string $col)
    {
        $v = trim($v);
        if ($v === '' || strcasecmp($v, 'null') === 0) return null;
        switch ($meta['tipo']) {
            case 'bool':
                $l = mb_strtolower($v);
                if (in_array($l, ['true', '1', 't', 'si', 'sí', 'yes', 'activo', 'verdadero'], true)) return 'true';
                if (in_array($l, ['false', '0', 'f', 'no', 'inactivo', 'falso'], true)) return 'false';
                throw new InvalidArgumentException("\"$col\": \"$v\" no es sí/no");
            case 'int2': case 'int4': case 'int8':
                if (!preg_match('/^-?\d+$/', $v)) throw new InvalidArgumentException("\"$col\": \"$v\" no es un número entero");
                return $v;
            case 'numeric': case 'float4': case 'float8':
                $n = str_replace(',', '.', $v);
                if (!is_numeric($n)) throw new InvalidArgumentException("\"$col\": \"$v\" no es un número");
                return $n;
            case 'json': case 'jsonb':
                json_decode($v);
                if (json_last_error() !== JSON_ERROR_NONE) throw new InvalidArgumentException("\"$col\": JSON inválido (" . json_last_error_msg() . ')');
                return $v;
            default:
                return $v;
        }
    }

    private static function mensaje(Throwable $e): string
    {
        $m = $e->getMessage();
        if (preg_match('/ERROR:\s+(.+?)(\n|DETAIL|$)/s', $m, $x)) $m = $x[1];
        if (preg_match('/DETAIL:\s+(.+?)(\n|$)/s', $e->getMessage(), $d)) $m .= ' — ' . $d[1];
        return mb_substr(trim($m), 0, 220);
    }
}
