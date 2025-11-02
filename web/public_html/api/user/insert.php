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

$username = $input['username'] ?? $_POST['username'] ?? '';
$email = $input['email'] ?? $_POST['email'] ?? '';
$password = $input['password'] ?? $_POST['password'] ?? '';
$role = (int)($input['role'] ?? $_POST['role'] ?? 0);

if (empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบทุกช่อง']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'รูปแบบอีเมลไม่ถูกต้อง']);
    exit;
}

try {
    $table = new Table_user();

    if ($table->usernameExists($username)) {
        http_response_code(409);
        echo json_encode(['error' => 'ชื่อผู้ใช้นี้มีอยู่แล้ว']);
        exit;
    }
    if ($table->emailExists($email)) {
        http_response_code(409);
        echo json_encode(['error' => 'อีเมลนี้มีอยู่แล้ว']);
        exit;
    }

    // Project uses md5 hashing (see Auth.php)
    $password_hash = md5($password);

    $ok = $table->createUser($username, $password_hash, $email, $role);

    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'เพิ่มผู้ใช้สำเร็จ']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'เพิ่มผู้ใช้ไม่สำเร็จ']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดบนเซิร์ฟเวอร์']);
}
