<?php
/*
 * herramientas/recalcular_reajustes.php  (solo línea de comandos)
 * Recalcula el reajuste del modelo (input_original → nuevo_input_vector) de cada calificación
 * guardada en itinerarios.back_testing_mr, con la misma lógica que usa la app al calificar
 * (ItinerariosModel::construirInputVector). Sirve tras cargar datos o cambiar los factores.
 *
 *   php herramientas/recalcular_reajustes.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
chdir(__DIR__ . '/..');
require_once 'model/database.php';
require_once 'model/itinerarios.php';

$pdo = Database::Conectar();
$it  = new ItinerariosModel();
$modelos = [];   // atraccion_id => info del modelo (input_original incluido)

$filas = $pdo->query("SELECT id, atraccion_id, calificacion_usuario, nueva_metricas_json_reajuste m FROM itinerarios.back_testing_mr")->fetchAll(PDO::FETCH_ASSOC);
$upd = $pdo->prepare("UPDATE itinerarios.back_testing_mr SET nueva_metricas_json_reajuste = ?::jsonb, modelo_mr_fk = ?::jsonb WHERE id = ?");

$res = ['aumento' => 0, 'reduccion' => 0, 'sin_cambio' => 0, 'sin_modelo' => 0];
$pdo->beginTransaction();
foreach ($filas as $f) {
    $aid = (int)$f['atraccion_id'];
    $nota = (int)$f['calificacion_usuario'];
    $modelos[$aid] ??= (@$it->obtenerModeloPorAtraccion($aid) ?: []);
    $mod = $modelos[$aid];
    $orig = $mod['input_original'] ?? [];
    if (!$orig) { $res['sin_modelo']++; continue; }

    $nuevo = @$it->construirInputVector($mod['mapeo'] ?? [], ['_input_original' => $orig], $nota);
    $m = json_decode($f['m'] ?? '{}', true) ?: [];
    $m['calificacion']       = $nota;
    $m['feedback_tipo']      = $nota >= 4 ? 'positivo' : ($nota === 3 ? 'neutral' : 'negativo');
    $m['input_original']     = $orig;
    $m['nuevo_input_vector'] = $nuevo;
    $m['modelo_origen']      = ['id' => $mod['id'] ?? null, 'nombre' => $mod['nombre'] ?? null, 'version' => $mod['version'] ?? null];
    $m['atraccion_id']       = $aid;

    $so = array_sum($orig); $sn = array_sum($nuevo);
    $res[$sn > $so ? 'aumento' : ($sn < $so ? 'reduccion' : 'sin_cambio')]++;
    $upd->execute([json_encode($m, JSON_UNESCAPED_UNICODE), json_encode(['modelo_id' => $mod['id'] ?? null]), $f['id']]);
}
$pdo->commit();
echo json_encode($res), "\n";
