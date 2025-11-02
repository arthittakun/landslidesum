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

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$id = (int)($input['id'] ?? $_POST['id'] ?? 0);
$username = $input['username'] ?? $_POST['username'] ?? '';
$email = $input['email'] ?? $_POST['email'] ?? '';
$role = (int)($input['role'] ?? $_POST['role'] ?? 0);
$password = $input['password'] ?? $_POST['password'] ?? '';

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ต้องระบุ id สำหรับแก้ไข']);
    exit;
}
if (empty($username) || empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบ']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'อีเมลไม่ถูกต้อง']);
    exit;
}

try {
    $table = new Table_user();

    if ($table->usernameExists($username, $id)) {
        http_response_code(409);
        echo json_encode(['error' => 'ชื่อผู้ใช้นี้มีอยู่แล้ว']);
        exit;
    }
    if ($table->emailExists($email, $id)) {
        http_response_code(409);
        echo json_encode(['error' => 'อีเมลนี้มีอยู่แล้ว']);
        exit;
    }

    $password_hash = null;
    if (!empty($password)) {
        $password_hash = md5($password);
    }

    $ok = $table->updateUser($id, $username, $email, $role, $password_hash);

    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'อัปเดตผู้ใช้สำเร็จ']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'อัปเดตผู้ใช้ไม่สำเร็จ']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดบนเซิร์ฟเวอร์']);
}
