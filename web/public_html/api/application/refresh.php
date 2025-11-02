<?php
require_once __DIR__ . '/../../../auth/Auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// เฉพาะ POST method เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'code' => 405
    ]);
    exit;
}

try {
    // รับข้อมูลจาก request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    // รับ refresh token
    $refresh_token = $input['refresh_token'] ?? $_POST['refresh_token'] ?? '';
    
    if (empty($refresh_token)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Refresh token is required',
            'code' => 400
        ]);
        exit;
    }

    // สร้าง Auth instance และทำการรีเฟรช token
    $auth = new Auth();
    $result = $auth->refreshToken($refresh_token);

    // ส่งผลลัพธ์
    http_response_code($result['code']);
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'code' => 500
    ]);
}
