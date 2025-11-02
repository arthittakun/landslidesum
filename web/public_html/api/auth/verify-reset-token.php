<?php
require_once __DIR__ . '/../../../database/table_password_reset.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// เฉพาะ GET method เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'code' => 405
    ]);
    exit;
}

try {
    // รับ token จาก query parameter
    $token = $_GET['token'] ?? '';
    
    if (empty($token)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Token is required',
            'code' => 400
        ]);
        exit;
    }

    // สร้าง instance ของ Table_password_reset
    $passwordReset = new Table_password_reset();
    
    // ตรวจสอบ reset token
    $resetData = $passwordReset->verifyResetToken($token);
    
    if (!$resetData) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired reset token',
            'valid' => false,
            'code' => 400
        ]);
        exit;
    }

    // คำนวณเวลาที่เหลือ
    $now = new DateTime();
    $expiresAt = new DateTime($resetData['expires_at']);
    $timeLeft = $expiresAt->getTimestamp() - $now->getTimestamp();
    
    if ($timeLeft <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Reset token has expired',
            'valid' => false,
            'code' => 400
        ]);
        exit;
    }

    // Token ถูกต้องและยังไม่หมดอายุ
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Token is valid',
        'valid' => true,
        'data' => [
            'email' => $resetData['email'],
            'expires_at' => $resetData['expires_at'],
            'time_left_seconds' => $timeLeft,
            'created_at' => $resetData['created_at']
        ],
        'code' => 200
    ]);

} catch (Exception $e) {
    error_log("Verify reset token error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'valid' => false,
        'error' => $e->getMessage(),
        'code' => 500
    ]);
}
