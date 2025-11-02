<?php
require_once __DIR__ . '/../../../database/table_device.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$device = new Table_device();

// Get device_id from different sources
$device_id = '';
$hard_delete = false;

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Try to get data from raw input first
    $raw_input = file_get_contents('php://input');
    
    // Check if it's JSON
    $json_input = json_decode($raw_input, true);
    if ($json_input) {
        $device_id = $json_input['device_id'] ?? '';
        $hard_delete = $json_input['hard_delete'] ?? false;
    } else {
        // Parse URL-encoded data
        parse_str($raw_input, $parsed_input);
        $device_id = $parsed_input['device_id'] ?? $_GET['device_id'] ?? '';
        $hard_delete = $parsed_input['hard_delete'] ?? $_GET['hard_delete'] ?? false;
    }
} else {
    $device_id = $_POST['device_id'] ?? '';
    $hard_delete = $_POST['hard_delete'] ?? false;
}

// Validate device_id
if (empty($device_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณาระบุรหัสอุปกรณ์']);
    exit;
}

// Check if device exists
if (!$device->deviceExists($device_id)) {
    http_response_code(404);
    echo json_encode(['error' => 'ไม่พบอุปกรณ์ที่ระบุ']);
    exit;
}

// Get device data before deletion
$device_data = $device->getDeviceById($device_id);

// Perform deletion
if ($hard_delete) {
    // Hard delete (permanent)
    $result = $device->hardDeleteDevice($device_id);
    $message = 'ลบอุปกรณ์ออกจากระบบถาวรแล้ว';
} else {
    // Soft delete (set void = 1)
    $result = $device->deleteDevice($device_id);
    $message = 'ลบอุปกรณ์เรียบร้อยแล้ว';
}

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $device_data
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการลบอุปกรณ์']);
}
?>
