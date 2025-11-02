<?php
class JWT {
    private $secret;
    private $algorithm = 'HS256';

    public function __construct() {
        // ใช้ secret key ที่แข็งแรงกว่า
        $this->secret = 'landslide-alert-secret-key-2024-secure-jwt-token';
    }

    /**
     * สร้าง JWT token
     * @param array $payload ข้อมูลที่ต้องการเก็บใน token
     * @param int $expire เวลาหมดอายุในวินาที (default 1 ชั่วโมง)
     * @return string JWT token
     */
    public function encode($payload, $expire = 3600 * 24 * 180) {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];
        
        $payload['iat'] = time(); // issued at
        $payload['exp'] = time() + $expire; // expiration time
        $payload['iss'] = 'landslide-alert-system'; // issuer

        $base64UrlHeader = $this->base64url_encode(json_encode($header));
        $base64UrlPayload = $this->base64url_encode(json_encode($payload));
        
        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $this->secret, true);
        $base64UrlSignature = $this->base64url_encode($signature);

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    /**
     * ตรวจสอบและ decode JWT token
     * @param string $token JWT token
     * @return array|false ข้อมูล payload หรือ false ถ้าไม่ถูกต้อง
     */
    public function decode($token) {
        if (!$token) {
            return false;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;
        
        $header = json_decode($this->base64url_decode($headerB64), true);
        $payload = json_decode($this->base64url_decode($payloadB64), true);
        
        if (!$header || !$payload) {
            return false;
        }

        // ตรวจสอบ signature
        $signatureCheck = $this->base64url_encode(
            hash_hmac('sha256', "$headerB64.$payloadB64", $this->secret, true)
        );

        if ($sigB64 !== $signatureCheck) {
            return false;
        }

        // ตรวจสอบเวลาหมดอายุ
        if (isset($payload['exp']) && time() >= $payload['exp']) {
            return false;
        }

        return $payload;
    }

    /**
     * สร้าง refresh token (อายุยาวกว่า access token)
     * @param array $payload ข้อมูลที่ต้องการเก็บใน token
     * @return string refresh token
     */
    public function generateRefreshToken($payload) {
        // refresh token อายุ 7 วัน
        return $this->encode($payload, 7 * 24 * 3600);
    }

    /**
     * ตรวจสอบว่า token หมดอายุหรือยัง
     * @param string $token JWT token
     * @return bool true ถ้าหมดอายุ
     */
    public function isExpired($token) {
        $payload = $this->decode($token);
        if (!$payload) {
            return true;
        }
        
        return isset($payload['exp']) && time() >= $payload['exp'];
    }

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
