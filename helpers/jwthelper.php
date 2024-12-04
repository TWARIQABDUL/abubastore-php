<?php

class JwtHelper {
    private static $secretKey = "Hp12Hl24Kn123!"; // Use a strong, unique secret key.

    public static function encode(array $payload) {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode($payload);

        // Base64Url encode header and payload
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);

        // Create signature
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", self::$secretKey, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        // Combine all parts into a JWT
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    public static function decode($jwt) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new Exception("Invalid JWT structure");
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        // Decode header and payload
        $header = json_decode(self::base64UrlDecode($base64UrlHeader), true);
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);

        // Verify signature
        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", self::$secretKey, true);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new Exception("Invalid JWT signature");
        }

        // Check token expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception("Token has expired");
        }

        return $payload;
    }

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
