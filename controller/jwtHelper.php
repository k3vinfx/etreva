<?php
class JwtHelper {
    private static $secret = 'ETREVA_SECRET_2026';

    // Genera el Token (El Activador)
    public static function createToken($data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(array_merge(['iat' => time(), 'exp' => time() + 3600], $data));

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret, true);
        $base64Signature = self::base64UrlEncode($signature);

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    // Valida el Token (El Escudo)
   public static function validateToken($jwt) {
    if (empty($jwt) || !is_string($jwt)) return false; // ← agregar esta línea

       $partes = explode('.', $jwt);
       if (count($partes) !== 3) return false;

        list($header, $payload, $signature) = $partes;
        $validSig = self::base64UrlEncode(hash_hmac('sha256', $header . "." . $payload, self::$secret, true));

        if ($signature !== $validSig) return false;

        $data = json_decode(base64_decode($payload), true);
        if ($data['exp'] < time()) return false; // Expirado

        return $data;
    }

    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}