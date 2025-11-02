<?php
require_once __DIR__ . '/../../../database/table_user.php';
session_start();

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

try {
  if (!isset($_SESSION['username']) && !isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
  }

  $old = $_POST['old_password'] ?? '';
  $new = $_POST['new_password'] ?? '';

  if (strlen($new) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสผ่านใหม่อย่างน้อย 8 ตัวอักษร']);
    exit;
  }

  $table = new Table_user();
  $current = null;
  if (!empty($_SESSION['username'])) {
    $current = $table->getUserByUsername($_SESSION['username']);
  }
  if (!$current && !empty($_SESSION['email'])) {
    $current = $table->getUserByEmail($_SESSION['email']);
  }
  if (!$current) {
    http_response_code(404);
    echo json_encode(['error' => 'ไม่พบผู้ใช้']);
    exit;
  }

  // Verify old password matches (md5)
  if (!isset($current['password']) || md5($old) !== $current['password']) {
    http_response_code(401);
    echo json_encode(['error' => 'รหัสผ่านเดิมไม่ถูกต้อง']);
    exit;
  }

  $id = (int)$current['id'];
  $role = isset($current['role']) ? (int)$current['role'] : 0;
  $newHash = md5($new);
  $ok = $table->updateUser($id, $current['username'], $current['email'], $role, $newHash);

  if ($ok) {
    echo json_encode(['success' => true, 'message' => 'เปลี่ยนรหัสผ่านสำเร็จ']);
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'เปลี่ยนรหัสผ่านไม่สำเร็จ']);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Internal server error']);
}
