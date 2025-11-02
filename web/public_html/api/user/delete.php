<?php
require_once __DIR__ . '/../../../database/table_user.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$id = '';
$hard = false;

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if ($input) {
        $id = $input['id'] ?? '';
        $hard = (bool)($input['hard_delete'] ?? false);
    } else {
        parse_str(file_get_contents('php://input'), $parsed);
        $id = $parsed['id'] ?? $_GET['id'] ?? '';
        $hard = (bool)($parsed['hard_delete'] ?? $_GET['hard_delete'] ?? false);
    }
} else {
    $id = $_POST['id'] ?? ($input['id'] ?? '');
    $hard = (bool)($_POST['hard_delete'] ?? ($input['hard_delete'] ?? false));
}

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณาระบุรหัสผู้ใช้']);
    exit;
}

try {
    $table = new Table_user();

    if (!$table->userExistsById((int)$id)) {
        http_response_code(404);
        echo json_encode(['error' => 'ไม่พบผู้ใช้ที่ระบุ']);
        exit;
    }

    if ($hard) {
        $ok = $table->hardDeleteUser((int)$id);
        $msg = 'ลบผู้ใช้ถาวรแล้ว';
    } else {
        $ok = $table->deleteUser((int)$id);
        $msg = 'ลบผู้ใช้เรียบร้อย';
    }

    if ($ok) {
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'ลบผู้ใช้ไม่สำเร็จ']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดบนเซิร์ฟเวอร์']);
}
