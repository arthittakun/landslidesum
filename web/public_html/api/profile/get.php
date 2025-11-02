<?php
require_once __DIR__ . '/../../../database/table_user.php';
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
  $table = new Table_user();
  $user = null;
  if (!empty($_SESSION['username'])) {
    $user = $table->getUserByUsername($_SESSION['username']);
  }
  if (!$user && !empty($_SESSION['email'])) {
    $user = $table->getUserByEmail($_SESSION['email']);
  }
  if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
  }
  // Limit fields returned
  $data = [
    'id' => (int)$user['id'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => isset($user['role']) ? (int)$user['role'] : 0
  ];
  echo json_encode(['success' => true, 'data' => $data]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Internal server error']);
}
