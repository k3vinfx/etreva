<?php
require_once 'controller/jwtHelper.php';

class AuthMiddleware {
// Dentro de AuthMiddleware.php
public static function resguardar() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    // 1. Intentar obtener el token de la SESIÓN (para el navegador)
    $token = $_SESSION['token'] ?? null;

    // 2. Si no hay en sesión, intentar del HEADER (para Postman)
    if (!$token) {
        $headers = apache_request_headers();
        $token = $headers['Authorization'] ?? null;
    }

    if ($token) {
        $decoded = JwtHelper::validateToken($token);
        if ($decoded) return $decoded; // Sesión válida
    }

    // Si llegamos aquí, el token no sirve
    return false; 
}
}