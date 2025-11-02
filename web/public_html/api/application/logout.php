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
    // สร้าง Auth instance
    $auth = new Auth();
    
    // ตรวจสอบ authentication (จะ exit ถ้าไม่ผ่าน)
    $user = $auth->requireAuth();
    
    // ใน JWT, การ logout จริงๆ คือการบอกให้ client ลบ token
    // เพราะ JWT เป็น stateless ไม่สามารถ revoke token ได้จาก server side
    // หากต้องการ revoke token จริงๆ ต้องมี blacklist system
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful',
        'data' => [
            'message' => 'Please remove access token and refresh token from client',
            'logout_time' => date('Y-m-d H:i:s')
        ],
        'code' => 200
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'code' => 500
    ]);
}
