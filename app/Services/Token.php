<?php

namespace App\Services;

use Exception;

class Token
{
    private const TOKEN_SEPARATOR = '.';
    private const MESSAGE_SEPARATOR = '|';
    private const TOKEN_VERSION = 'v1';

    private string $pathPrivateKey;
    private string $pathPublicKey;
    private ?\OpenSSLAsymmetricKey $cachedPrivateKey = null;
    private ?\OpenSSLAsymmetricKey $cachedPublicKey = null;

    public function __construct() {
        $this->pathPrivateKey = 'file://' . storage_path('keys') . '/private_key.pem';
        $this->pathPublicKey = 'file://' . storage_path('keys') . '/public_key.pem';
    }

    public function createShortHash(string $sid, ?int $horasExpiracion = null): string {
        $privateKey = $this->getPrivateKey();

        $tiempoVida = $horasExpiracion ?? intval(config('jwt.expired_token'));
        $expires = time() + ($tiempoVida * 3600);

        $encodedSid = $this->base64UrlEncode($sid);
        $version = self::TOKEN_VERSION;
        $message = "{$version}" . self::MESSAGE_SEPARATOR . "{$encodedSid}" . self::MESSAGE_SEPARATOR . "{$expires}";

        $signature = '';
        if (!openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new Exception('No se pudo firmar el token: ' . openssl_error_string());
        }

        $base64Signature = $this->base64UrlEncode($signature);

        $sep = self::TOKEN_SEPARATOR;
        return "{$version}{$sep}{$encodedSid}{$sep}{$expires}{$sep}{$base64Signature}";
    }

    public function verifyShortHash(string $hash): array {
        $parts = explode(self::TOKEN_SEPARATOR, $hash, 4);

        if (count($parts) !== 4) {
            throw new Exception('Token con formato inválido.');
        }

        [$version, $encodedSid, $expires, $signatureB64] = $parts;

        if ($version !== self::TOKEN_VERSION) {
            throw new Exception('Versión de token no soportada.');
        }

        if (time() > (int) $expires) {
            throw new Exception('El código QR ha expirado.');
        }

        $publicKey = $this->getPublicKey();

        $message = "{$version}" . self::MESSAGE_SEPARATOR . "{$encodedSid}" . self::MESSAGE_SEPARATOR . "{$expires}";
        $signature = $this->base64UrlDecode($signatureB64);

        $isValid = openssl_verify($message, $signature, $publicKey, OPENSSL_ALGO_SHA256);

        if ($isValid !== 1) {
            throw new Exception('Firma de token inválida.', 401);
        }

        return [
            'sid'     => $this->base64UrlDecode($encodedSid),
            'expires' => (int) $expires,
        ];
    }

    private function getPrivateKey(): \OpenSSLAsymmetricKey {
        if ($this->cachedPrivateKey !== null) {
            return $this->cachedPrivateKey;
        }

        $key = openssl_pkey_get_private($this->pathPrivateKey, config('jwt.secret'));

        if (!$key) {
            throw new Exception('No se pudo cargar la clave privada: ' . openssl_error_string());
        }

        return $this->cachedPrivateKey = $key;
    }

    private function getPublicKey(): \OpenSSLAsymmetricKey {
        if ($this->cachedPublicKey !== null) {
            return $this->cachedPublicKey;
        }

        $key = openssl_pkey_get_public($this->pathPublicKey);

        if (!$key) {
            throw new Exception('No se pudo cargar la clave pública: ' . openssl_error_string());
        }

        return $this->cachedPublicKey = $key;
    }

    private function base64UrlEncode(string $data): string {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private function base64UrlDecode(string $data): string {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}