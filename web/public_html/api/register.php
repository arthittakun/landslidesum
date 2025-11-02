<?php 

require_once __DIR__ . '/../../database/toble_login.php';
$login = new Table_get();

header('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate input data
if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบทุกช่อง']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'รูปแบบอีเมลไม่ถูกต้อง']);
    exit;
}

// Validate password match
if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสผ่านไม่ตรงกัน']);
    exit;
}

// Validate password strength
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร']);
    exit;
}

// Hash the password
$hashed_password = md5($password);

// Check if the user already exists
$user_exists = $login->checkUserExists($username, $email);
if ($user_exists) {
    http_response_code(409); // Conflict
    echo json_encode(['error' => 'ชื่อผู้ใช้หรืออีเมลนี้มีในระบบแล้ว']);
    exit;
}

// Register the user
$result = $login->registerUser($username, $email, $hashed_password);

if ($result) {
    http_response_code(201); // Created
    echo json_encode(['success' => true, 'message' => 'ลงทะเบียนสำเร็จ', 'test' => $result]);
    exit;
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการลงทะเบียน กรุณาลองใหม่อีกครั้ง']);
    exit;
}
  
// Validate password strength
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร']);
    exit;
}
