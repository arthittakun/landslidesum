<?php
require_once __DIR__ . '/../../../database/table_device.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$device = new Table_device();

$device_id = $_POST['device_id'] ?? '';

// Validate device_id
if (empty($device_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณาระบุรหัสอุปกรณ์']);
    exit;
}

// Check if device exists in deleted records
$deleted_devices = $device->getDeletedDevices();
$device_found = false;
$device_data = null;

foreach ($deleted_devices as $deleted_device) {
    if ($deleted_device['device_id'] === $device_id) {
        $device_found = true;
        $device_data = $deleted_device;
        break;
    }
}

if (!$device_found) {
    http_response_code(404);
    echo json_encode(['error' => 'ไม่พบอุปกรณ์ที่ถูกลบ']);
    exit;
}

// Restore device
$result = $device->restoreDevice($device_id);

if ($result) {
    // Get restored device data
    $restored_device = $device->getDeviceById($device_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'กู้คืนอุปกรณ์สำเร็จ',
        'data' => $restored_device
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการกู้คืนอุปกรณ์']);
}
?>
