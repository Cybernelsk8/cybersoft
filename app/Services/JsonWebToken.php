<?php

namespace App\Services;

use Exception;

class JsonWebToken
{
    private string $pathPrivateKey;
    private string $pathPublicKey;

    public function __construct() {
        $this->pathPrivateKey = 'file://' . storage_path('keys') . '/private_key.pem';
        $this->pathPublicKey = 'file://' . storage_path('keys') . '/public_key.pem';
    }

    public function createJWT(array $payloads, string $aud, ?int $horasExpiracion = null): string {
        $privateKey = openssl_pkey_get_private($this->pathPrivateKey, config("jwt.secret"));

        if (!$privateKey) {
            throw new \Exception("No se pudo cargar la clave privada");
        }
        
        $tiempoVida = $horasExpiracion ?? intval(config("jwt.expired_token"));
        $expiracion = time() + ($tiempoVida * 3600);

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $payload = [
            'iss' => env('APP_URL'),
            'aud' => $aud,
            'iat' => time(),
            'exp' => $expiracion,
            'sub' => $payloads['sub'] ?? null,
            'data' => $payloads['data'] ?? null 
        ];
        
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        $signature = '';

        if (!openssl_sign("$base64UrlHeader.$base64UrlPayload", $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \Error("No se pudo firmar el JWT");
        }
        
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    public function verifyJWT(string $jwt): bool {

        try {

            if (substr_count($jwt, '.') !== 2) {
                throw new \Exception($jwt);
            }

            list($header, $payload, $signature) = explode('.', $jwt);

            $decodedPayload = $this->decodeJWT($jwt);

            $receivers = config('jwt.receivers');
            $currentTimestamp = time();
        
            if (isset($decodedPayload['aud']) && !in_array($decodedPayload['aud'], $receivers)) {
                throw new \Exception('Audience inválido', 401);
            }
        
            if (isset($decodedPayload['exp']) && $currentTimestamp > $decodedPayload['exp']) {
                throw new \Exception('La sesión ha expirado.', 419);
            }

            if (isset($decodedPayload['iat']) && $decodedPayload['iat'] > ($currentTimestamp + 60)) {
                throw new \Exception('El token aún no es válido (emitido en el futuro).', 401);
            }
        
            $publicKey = openssl_pkey_get_public(file_get_contents($this->pathPublicKey));
            
            if (!$publicKey) {
                throw new \Exception("No se pudo cargar la clave pública");
            }
        
            $base64UrlSignature = str_replace(['-', '_'], ['+', '/'], $signature);
            $signature = base64_decode($base64UrlSignature);
        
            // Verificación de la firma después de las claims
            $isValid = openssl_verify("$header.$payload", $signature, $publicKey, OPENSSL_ALGO_SHA256);
            
            if ($isValid !== 1) {
                throw new \Exception('Firma de token inválida', 401);
            }
            
            return true;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

       
    }

    public function verifyQrHash(string $hash): array {
        try {

            $this->verifyJWT($hash);

            $payload = $this->decodeJWT($hash);

            if ($payload['aud'] !== 'qr_solicitante') {
                throw new \Exception('Este token no es un código QR válido.');
            }

            return [
                'valido' => true,
                'id_solicitante' => $payload['sub'],
                'datos_extra' => $payload['data'] ?? []
            ];

        } catch (\Exception $e) {
            throw new \Exception("Error al validar QR: " . $e->getMessage());
        }
    }

    public function decodeJWT(string $jwt): array {
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        
        return $payload;
    }
    
    private function base64UrlEncode(string $data): string {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private function base64UrlDecode(string $data): string {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}