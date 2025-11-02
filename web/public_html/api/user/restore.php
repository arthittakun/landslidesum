<?php
require_once __DIR__ . '/../../../database/table_user.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = (int)($_POST['id'] ?? (json_decode(file_get_contents('php://input'), true)['id'] ?? 0));

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณาระบุรหัสผู้ใช้']);
    exit;
}

try {
    $table = new Table_user();
    $ok = $table->restoreUser($id);

    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'กู้คืนผู้ใช้สำเร็จ']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'กู้คืนผู้ใช้ไม่สำเร็จ']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดบนเซิร์ฟเวอร์']);
}
