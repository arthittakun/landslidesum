<?php
require_once __DIR__ . '/../../../database/table_location.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$location = new Table_location();

// Get location_id from different sources
$location_id = '';
$hard_delete = false;

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Try to get data from raw input first
    $raw_input = file_get_contents('php://input');
    
    // Check if it's JSON
    $json_input = json_decode($raw_input, true);
    if ($json_input) {
        $location_id = $json_input['location_id'] ?? '';
        $hard_delete = $json_input['hard_delete'] ?? false;
    } else {
        // Parse URL-encoded data
        parse_str($raw_input, $parsed_input);
        $location_id = $parsed_input['location_id'] ?? $_GET['location_id'] ?? '';
        $hard_delete = $parsed_input['hard_delete'] ?? $_GET['hard_delete'] ?? false;
    }
} else {
    $location_id = $_POST['location_id'] ?? '';
    $hard_delete = $_POST['hard_delete'] ?? false;
}

// Validate location_id
if (empty($location_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณาระบุรหัสตำแหน่ง']);
    exit;
}

// Check if location exists
if (!$location->locationExists($location_id)) {
    http_response_code(404);
    echo json_encode(['error' => 'ไม่พบตำแหน่งที่ระบุ']);
    exit;
}

// Get location data before deletion
$location_data = $location->getLocationById($location_id);

// Perform deletion
if ($hard_delete) {
    // Hard delete (permanent)
    $result = $location->hardDeleteLocation($location_id);
    $message = 'ลบตำแหน่งออกจากระบบถาวรแล้ว';
} else {
    // Soft delete (set void = 1)
    $result = $location->deleteLocation($location_id);
    $message = 'ลบตำแหน่งสำเร็จ';
}

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $location_data
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการลบตำแหน่ง']);
}
?>
