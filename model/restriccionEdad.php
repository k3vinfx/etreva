<?php
/*
 * model/restriccionEdad.php
 * ─────────────────────────────────────────────────────────────────
 * Protección de menores de edad (Bolivia):
 *   · Ley 259 de Control al Expendio y Consumo de Bebidas Alcohólicas: prohíbe vender o
 *     suministrar alcohol a menores de 18 años y su ingreso a locales de expendio.
 *   · Ley 548, Código Niña, Niño y Adolescente: protección integral de menores de 18 años.
 *
 * Un usuario es menor si NO cumplió 18 años según su fecha de nacimiento, o si en sus
 * preferencias eligió el rango de edad "menor_18". A los menores no se les muestran bares,
 * discotecas ni locales cuyo fin principal es el consumo de alcohol: no aparecen en búsquedas,
 * recomendaciones, relacionados, favoritos ni itinerarios, y no se pueden abrir ni guardar.
 */
require_once __DIR__ . '/database.php';

class RestriccionEdad
{
    public const EDAD_MINIMA = 18;
    private const CATEGORIA_VIDA_NOCTURNA = 5;

    // Fuera de Vida nocturna: locales cuyo fin principal es el alcohol (pubs, gastrobares, vinotecas, bodegas…).
    // "Brownie Bar" o "Bagel Bar" son cafeterías y no entran; "Vino Tinto" es una ruta ciclista; un restaurante
    // o café que además ofrece cocteles ("Alitas y Cocteles") es apto para familias y tampoco entra.
    private const PATRON_ALCOHOL = '\m(pub|gastrobar|shot bar|shots|discotecas?|karaoke|cervecer\w*|vinos|bodegas?|licor\w*|bar y comida)\M';

    private static array $menor = [];
    private static ?array $ids = null;

    /** El usuario no cumplió 18 años (fecha de nacimiento) o declaró ser menor en sus preferencias */
    public static function esMenor($idUsuario): bool
    {
        $id = (int)$idUsuario;
        if ($id <= 0) return false;
        if (array_key_exists($id, self::$menor)) return self::$menor[$id];
        $menor = false;
        try {
            $pdo = Database::Conectar();
            $st = $pdo->prepare("SELECT fecha_nacimiento FROM usuarios.usuarios WHERE id = ?");
            $st->execute([$id]);
            $fn = $st->fetchColumn();
            if ($fn) {
                $nac = new DateTime(substr((string)$fn, 0, 10));
                $menor = $nac->modify('+' . self::EDAD_MINIMA . ' years') > new DateTime('today');
            }
            if (!$menor) {
                $st = $pdo->prepare("SELECT intereses_json FROM usuarios.preferencias WHERE usuario_id = ?");
                $st->execute([$id]);
                $int = json_decode((string)$st->fetchColumn(), true) ?: [];
                $menor = str_replace('-', '_', (string)($int['rango_edad'] ?? '')) === 'menor_18';
            }
        } catch (Throwable $e) {
            error_log('RestriccionEdad::esMenor ' . $e->getMessage());
        }
        return self::$menor[$id] = $menor;
    }

    /** IDs de atracciones restringidas a mayores de 18 (bares, discotecas, locales de alcohol) */
    public static function idsRestringidos(): array
    {
        if (self::$ids !== null) return self::$ids;
        try {
            $st = Database::Conectar()->prepare(
                "SELECT id FROM catalogo.atracciones WHERE atraccion_categoria_id = ? OR nombre ~* ?");
            $st->execute([self::CATEGORIA_VIDA_NOCTURNA, self::PATRON_ALCOHOL]);
            self::$ids = array_fill_keys(array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN)), true);
        } catch (Throwable $e) {
            error_log('RestriccionEdad::idsRestringidos ' . $e->getMessage());
            self::$ids = [];
        }
        return self::$ids;
    }

    public static function esRestringida($idAtraccion): bool
    {
        return isset(self::idsRestringidos()[(int)$idAtraccion]);
    }

    /** ¿Este usuario puede ver esta atracción? */
    public static function puedeVer($idUsuario, $idAtraccion): bool
    {
        return !self::esRestringida($idAtraccion) || !self::esMenor($idUsuario);
    }

    /**
     * Quita de una lista las atracciones restringidas si el usuario es menor.
     * $campo: clave que guarda el id de la atracción en cada elemento (array u objeto).
     */
    public static function filtrar(array $lista, $idUsuario, string $campo = 'id'): array
    {
        if (!$lista || !self::esMenor($idUsuario)) return $lista;
        $ids = self::idsRestringidos();
        return array_values(array_filter($lista, function ($x) use ($ids, $campo) {
            $v = is_array($x) ? ($x[$campo] ?? null) : ($x->$campo ?? null);
            if (is_array($v)) $v = $v[0] ?? null;
            return !isset($ids[(int)$v]);
        }));
    }

    /** Id del usuario autenticado (el JWT puede venir como array u objeto) */
    public static function idDe($usuario): ?int
    {
        $id = is_array($usuario) ? ($usuario['id'] ?? null) : ($usuario->id ?? null);
        return $id ? (int)$id : null;
    }
}
