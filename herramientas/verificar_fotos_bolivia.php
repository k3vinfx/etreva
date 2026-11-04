<?php
/*
 * herramientas/verificar_fotos_bolivia.php  (solo línea de comandos)
 * Revisa en Wikimedia Commons todas las fotos ya descargadas y deja solo las que son de Bolivia.
 * Las referenciales de otros países dejan de mostrarse; las "del lugar" de otros países se apartan
 * a uploads/img_atracciones/_descartadas/ (no se borran). Las fotos subidas por el admin no se tocan.
 *
 *   php herramientas/verificar_fotos_bolivia.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
chdir(__DIR__ . '/..');
require 'model/database.php';
require 'model/imagenAtraccion.php';

$res = ImagenAtraccion::verificarBolivia(function ($m) { echo $m, "\n"; });
echo json_encode($res, JSON_PRETTY_PRINT), "\n";
