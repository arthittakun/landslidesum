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

  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');

  if ($username === '' || $email === '') {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกชื่อผู้ใช้และอีเมล']);
    exit;
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'รูปแบบอีเมลไม่ถูกต้อง']);
    exit;
  }

  $table = new Table_user();
  // Find current user by session
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

  $id = (int)$current['id'];

  // Uniqueness checks excluding current id
  if ($table->usernameExists($username, $id)) {
    http_response_code(409);
    echo json_encode(['error' => 'ชื่อผู้ใช้นี้ถูกใช้แล้ว']);
    exit;
  }
  if ($table->emailExists($email, $id)) {
    http_response_code(409);
    echo json_encode(['error' => 'อีเมลนี้ถูกใช้แล้ว']);
    exit;
  }

  // Keep role unchanged
  $role = isset($current['role']) ? (int)$current['role'] : 0;
  $ok = $table->updateUser($id, $username, $email, $role);

  if ($ok) {
    // Update session if username/email changed
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    echo json_encode(['success' => true, 'message' => 'อัปเดตโปรไฟล์สำเร็จ']);
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'อัปเดตไม่สำเร็จ']);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Internal server error']);
}
