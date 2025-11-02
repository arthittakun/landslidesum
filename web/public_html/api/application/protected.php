<?php
require_once __DIR__ . '/../../../auth/Auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // สร้าง Auth instance
    $auth = new Auth();
    
    // ตรวจสอบ authentication (จะ exit ถ้าไม่ผ่าน)
    $user = $auth->requireAuth();
    
    // ถ้าผ่านการตรวจสอบแล้ว จะมาถึงบรรทัดนี้
    echo json_encode([
        'success' => true,
        'message' => 'Data accessed successfully',
        'data' => [
            'user_info' => $user,
            'protected_data' => 'This is protected data that requires authentication',
            'timestamp' => date('Y-m-d H:i:s')
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
