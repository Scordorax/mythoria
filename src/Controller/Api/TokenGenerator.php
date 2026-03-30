<?php

namespace App\Controller\Api;

class TokenGenerator
{
    private const SECRET_KEY = 'ThisIsASecretKeyForDev'; // change en production
    private const TTL = 3600; // durée de vie en secondes (1h)

    /**
     * Crée un token maison encodé
     */
    public static function createToken(array $user): string
    {
        $payload = [
            'sub' => (string) $user['id'],
            'email' => $user['email'],
            'username' => $user['username'],
            'roles' => $user['roles'],
            'iat' => time(),
            'exp' => time() + self::TTL
        ];

        $payloadBase64 = self::base64UrlEncode(json_encode($payload));

        // signature HMAC SHA256
        $signature = hash_hmac('sha256', $payloadBase64, self::SECRET_KEY);

        return $payloadBase64 . '.' . $signature;
    }

    /**
     * Vérifie si le token est valide (signature + expiration)
     */
    public static function isValid(string $token): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) return false;

        [$payloadBase64, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $payloadBase64, self::SECRET_KEY);

        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        // Vérification de l'expiration
        $payload = json_decode(self::base64UrlDecode($payloadBase64), true);
        if (!$payload || !isset($payload['exp'])) {
            return false;
        }

        return $payload['exp'] > time();
    }

    /**
     * Decode le payload du token
     */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) return null;

        [$payloadBase64] = $parts;
        $payloadJson = self::base64UrlDecode($payloadBase64);
        return json_decode($payloadJson, true);
    }

    /**
     * Encode base64 URL safe
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decode base64 URL safe
     */
    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}