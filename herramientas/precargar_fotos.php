<?php
/*
 * herramientas/precargar_fotos.php  (solo línea de comandos)
 * Revisa y completa la galería de todas las atracciones (aparta fotos en blanco,
 * busca fotos del lugar y referenciales) para que ningún usuario espere la primera vez.
 *
 *   php herramientas/precargar_fotos.php            → todas
 *   php herramientas/precargar_fotos.php 0 4        → parte 0 de 4 (para correr 4 en paralelo)
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
chdir(__DIR__ . '/..');
require 'model/database.php';
require 'model/imagenAtraccion.php';

$parte = (int)($argv[1] ?? 0);
$total = max(1, (int)($argv[2] ?? 1));
$ids = Database::Conectar()->query("SELECT id FROM catalogo.atracciones ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

$resumen = ['propias' => 0, 'referenciales' => 0, 'atracciones' => 0];
foreach ($ids as $i => $id) {
    if ($i % $total !== $parte) continue;
    $g = ImagenAtraccion::galeria((int)$id, 4, true);
    $resumen['atracciones']++;
    $resumen['propias']       += count($g['fotos']) - $g['referenciales'];
    $resumen['referenciales'] += $g['referenciales'];
}
echo json_encode($resumen), "\n";
