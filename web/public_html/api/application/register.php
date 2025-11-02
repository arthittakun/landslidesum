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
    
    // รับข้อมูลจาก POST หรือ JSON
    $username = $input['username'] ?? $_POST['username'] ?? '';
    $email = $input['email'] ?? $_POST['email'] ?? '';
    $password = $input['password'] ?? $_POST['password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? $_POST['confirm_password'] ?? '';

    // สร้าง Auth instance และทำการลงทะเบียน
    $auth = new Auth();
    $result = $auth->register($username, $email, $password, $confirm_password);

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
