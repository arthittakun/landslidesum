<?php
require_once __DIR__ . '/../../../database/table_user.php';

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
	$table = new Table_user();

	$id = $_GET['id'] ?? '';
	$search = $_GET['search'] ?? '';
	$type = $_GET['type'] ?? 'all';
	$include_deleted = ($_GET['include_deleted'] ?? 'false') === 'true';

	switch ($type) {
		case 'by-id':
			if (empty($id)) {
				http_response_code(400);
				echo json_encode(['error' => 'กรุณาระบุรหัสผู้ใช้']);
				exit;
			}
			$user = $table->getUserById((int)$id);
			if ($user) {
				echo json_encode(['success' => true, 'data' => $user]);
			} else {
				http_response_code(404);
				echo json_encode(['error' => 'ไม่พบผู้ใช้ที่ระบุ']);
			}
			break;
		case 'search':
			$result = $table->searchUsers($search, $include_deleted);
			echo json_encode(['success' => true, 'data' => $result, 'count' => count($result)]);
			break;
		default:
			$result = $table->getAllUsers($include_deleted);
			echo json_encode(['success' => true, 'data' => $result, 'count' => count($result)]);
			break;
	}
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลผู้ใช้']);
}
?>

