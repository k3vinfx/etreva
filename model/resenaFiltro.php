<?php
/*
 * model/resenaFiltro.php
 * ─────────────────────────────────────────────────────────────────
 * Evita reseñas repetidas o clonadas (Reglamento, punto 9):
 *   · Un comentario idéntico a otro ya publicado (en cualquier atracción) se rechaza.
 *     La comparación ignora mayúsculas, acentos, signos y espacios.
 *   · Un comentario casi igual (≥ 85 % de similitud) a otro de la MISMA atracción también se rechaza.
 * Solo se compara contra reseñas de OTROS registros: editar la propia reseña siempre se permite.
 */
require_once __DIR__ . '/database.php';

class ResenaFiltro
{
    public const SIMILITUD_MAX = 85;   // %

    /** Texto normalizado para comparar: minúsculas, sin acentos ni signos, espacios simples */
    public static function normalizar(?string $t): string
    {
        $t = mb_strtolower(trim((string)$t), 'UTF-8');
        $t = strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','à'=>'a','è'=>'e','ç'=>'c','ã'=>'a','õ'=>'o','â'=>'a','ê'=>'e','ô'=>'o','ß'=>'ss','ä'=>'a','ö'=>'o']);
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $t));
    }

    /**
     * ¿El comentario repite otra reseña? Devuelve null si es original.
     * $excluirItinerario: itinerario de la reseña que se está editando (no se compara consigo misma).
     */
    public static function motivoDuplicado(?string $comentario, int $idAtraccion, ?int $excluirItinerario = null): ?string
    {
        $norm = self::normalizar($comentario);
        if ($norm === '') return null;   // calificar sin comentario siempre se permite
        try {
            $pdo = Database::Conectar();
            $sql = "SELECT bt.atraccion_id, bt.comentario_usuario
                      FROM itinerarios.back_testing_mr bt
                     WHERE bt.estado = 'completado' AND COALESCE(TRIM(bt.comentario_usuario), '') <> ''
                       AND bt.itinerario_id IS DISTINCT FROM ?
                       AND (bt.atraccion_id = ? OR LOWER(LEFT(TRIM(bt.comentario_usuario), 12)) = LOWER(LEFT(TRIM(?), 12))
                            OR LENGTH(TRIM(bt.comentario_usuario)) BETWEEN ? AND ?)";
            $len = mb_strlen(trim((string)$comentario));
            $st = $pdo->prepare($sql);
            $st->execute([$excluirItinerario, $idAtraccion, (string)$comentario, max(1, $len - 6), $len + 6]);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $otro = self::normalizar($r['comentario_usuario']);
                if ($otro === $norm) return 'identica';
                if ((int)$r['atraccion_id'] === $idAtraccion) {
                    similar_text($norm, $otro, $pct);
                    if ($pct >= self::SIMILITUD_MAX) return 'similar';
                }
            }
        } catch (Throwable $e) {
            error_log('ResenaFiltro::motivoDuplicado ' . $e->getMessage());
        }
        return null;
    }
}
