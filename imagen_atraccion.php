<?php
/*
 * imagen_atraccion.php
 * Se ejecuta solo cuando se pide una foto que NO existe en uploads/img_atracciones
 * (ver uploads/img_atracciones/.htaccess). Busca o genera la imagen; nunca devuelve 404.
 */
require_once __DIR__ . '/model/imagenAtraccion.php';

$id = (int)($_GET['id'] ?? 0);
$n  = (int)($_GET['n']  ?? 1);

ImagenAtraccion::servir($id, $n);
