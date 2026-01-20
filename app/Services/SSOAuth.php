<?php

namespace App\Services;

class SSOAuth
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function discovery(): array
    {
        if (empty($this->config['discovery_url'])) {
            throw new \RuntimeException('Discovery URL missing');
        }
        $data = file_get_contents($this->config['discovery_url']);
        if ($data === false) {
            throw new \RuntimeException('Failed to fetch discovery');
        }
        return json_decode($data, true) ?: [];
    }

    public function authUrl(): string
    {
        $discovery = $this->discovery();
        $authEndpoint = $discovery['authorization_endpoint'] ?? '';
        $state = bin2hex(random_bytes(16));
        $_SESSION['oidc_state'] = $state;
        $params = http_build_query([
            'client_id' => $this->config['client_id'],
            'response_type' => 'code',
            'scope' => $this->config['scopes'],
            'redirect_uri' => $this->config['redirect_uri'],
            'state' => $state,
        ]);
        return $authEndpoint . '?' . $params;
    }

    public function handleCallback(string $code, string $state): array
    {
        if (!isset($_SESSION['oidc_state']) || $_SESSION['oidc_state'] !== $state) {
            throw new \RuntimeException('Invalid state');
        }
        $discovery = $this->discovery();
        $tokenEndpoint = $discovery['token_endpoint'] ?? '';
        $payload = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->config['redirect_uri'],
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
        ]);
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded",
                'content' => $payload,
            ],
        ];
        $response = file_get_contents($tokenEndpoint, false, stream_context_create($opts));
        if ($response === false) {
            throw new \RuntimeException('Token exchange failed');
        }
        $data = json_decode($response, true) ?: [];
        $idToken = $data['id_token'] ?? '';
        if (!$idToken) {
            throw new \RuntimeException('Missing id_token');
        }
        $claims = $this->validateIdToken($idToken);
        return ['tokens' => $data, 'claims' => $claims];
    }

    private function validateIdToken(string $jwt): array
    {
        [$headerB64, $payloadB64, $signatureB64] = explode('.', $jwt);
        $header = json_decode(base64_decode(strtr($headerB64, '-_', '+/')), true) ?: [];
        $payload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true) ?: [];
        $discovery = $this->discovery();
        $jwksUri = $discovery['jwks_uri'] ?? '';
        $jwks = json_decode(file_get_contents($jwksUri), true) ?: [];
        $kid = $header['kid'] ?? '';
        $keys = $jwks['keys'] ?? [];
        $key = null;
        foreach ($keys as $candidate) {
            if (($candidate['kid'] ?? '') === $kid) {
                $key = $candidate;
                break;
            }
        }
        if (!$key) {
            throw new \RuntimeException('JWKS key not found');
        }
        $publicKey = $this->jwkToPem($key);
        $signature = base64_decode(strtr($signatureB64, '-_', '+/'));
        $valid = openssl_verify($headerB64 . '.' . $payloadB64, $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if ($valid !== 1) {
            throw new \RuntimeException('Invalid token signature');
        }
        if ($this->config['issuer'] && ($payload['iss'] ?? '') !== $this->config['issuer']) {
            throw new \RuntimeException('Invalid issuer');
        }
        return $payload;
    }

    private function jwkToPem(array $jwk): string
    {
        $modulus = base64_decode(strtr($jwk['n'], '-_', '+/'));
        $exponent = base64_decode(strtr($jwk['e'], '-_', '+/'));
        $modulus = ltrim($modulus, "\x00");
        $components = [
            'modulus' => $modulus,
            'exponent' => $exponent,
        ];
        $rsa = $this->rsaKey($components);
        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($rsa), 64, "\n") . "-----END PUBLIC KEY-----\n";
        return $pem;
    }

    private function rsaKey(array $components): string
    {
        $modulus = $this->encodeInteger($components['modulus']);
        $exponent = $this->encodeInteger($components['exponent']);
        $sequence = $this->encodeSequence($modulus . $exponent);
        $bitstring = "\x03" . $this->encodeLength(strlen($sequence) + 1) . "\x00" . $sequence;
        $algorithm = "\x30\x0D\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01\x05\x00";
        return $this->encodeSequence($algorithm . $bitstring);
    }

    private function encodeInteger(string $value): string
    {
        if (ord($value[0]) > 0x7f) {
            $value = "\x00" . $value;
        }
        return "\x02" . $this->encodeLength(strlen($value)) . $value;
    }

    private function encodeSequence(string $value): string
    {
        return "\x30" . $this->encodeLength(strlen($value)) . $value;
    }

    private function encodeLength(int $length): string
    {
        if ($length <= 0x7f) {
            return chr($length);
        }
        $temp = ltrim(pack('N', $length), "\x00");
        return chr(0x80 | strlen($temp)) . $temp;
    }
}
