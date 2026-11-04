<?php
/*
 * herramientas/limpiar_resenas_duplicadas.php  (solo línea de comandos)
 * Limpia reseñas con comentarios repetidos o clonados: de cada texto repetido se conserva la reseña
 * más antigua y en las demás se quita el comentario. La calificación (estrellas) se conserva, porque
 * el modelo de recomendación la usa para reajustarse.
 *
 *   php herramientas/limpiar_resenas_duplicadas.php            → solo muestra qué haría
 *   php herramientas/limpiar_resenas_duplicadas.php --aplicar  → respalda en sql_backups/ y limpia
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
chdir(__DIR__ . '/..');
require 'model/database.php';
require 'model/resenaFiltro.php';

$aplicar = in_array('--aplicar', $argv, true);
$pdo = Database::Conectar();
$filas = $pdo->query("SELECT id, itinerario_id, atraccion_id, comentario_usuario, created_at
                        FROM itinerarios.back_testing_mr
                       WHERE estado = 'completado' AND COALESCE(TRIM(comentario_usuario), '') <> ''
                       ORDER BY created_at, id")->fetchAll(PDO::FETCH_ASSOC);

$vistos = []; $quitar = [];
foreach ($filas as $f) {
    $k = ResenaFiltro::normalizar($f['comentario_usuario']);
    if (isset($vistos[$k])) $quitar[] = $f; else $vistos[$k] = $f['id'];
}
printf("Reseñas con comentario: %d · textos únicos: %d · comentarios repetidos a quitar: %d\n",
       count($filas), count($vistos), count($quitar));

if (!$aplicar) { echo "Simulación. Para aplicar: php herramientas/limpiar_resenas_duplicadas.php --aplicar\n"; exit; }
if (!$quitar) { echo "Nada que limpiar.\n"; exit; }

if (!is_dir('sql_backups')) mkdir('sql_backups', 0775, true);
$respaldo = 'sql_backups/resenas_duplicadas_' . date('Ymd_His') . '.json';
file_put_contents($respaldo, json_encode($quitar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Respaldo: $respaldo\n";

$pdo->beginTransaction();
$st = $pdo->prepare("UPDATE itinerarios.back_testing_mr SET comentario_usuario = NULL, updated_at = NOW() WHERE id = ?");
foreach ($quitar as $f) $st->execute([$f['id']]);
$pdo->commit();
echo 'Listo: ' . count($quitar) . " comentarios repetidos quitados (las calificaciones se conservan).\n";
