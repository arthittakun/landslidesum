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

// Get input data
$device_id = $_POST['device_id'] ?? '';
$device_name = $_POST['device_name'] ?? '';
$location_id = $_POST['location_id'] ?? '';
$serialno = $_POST['serialno'] ?? '';

// Validate required fields
if (empty($device_id) || empty($device_name) || empty($location_id) || empty($serialno)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบทุกช่อง']);
    exit;
}

// Validate device_id length (4 characters)
if (strlen($device_id) !== 4) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสอุปกรณ์ต้องมี 4 ตัวอักษร']);
    exit;
}

// Validate location_id length (3 characters)
if (strlen($location_id) !== 3) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสตำแหน่งต้องมี 3 ตัวอักษร']);
    exit;
}

// Validate serialno length (5 characters)
if (strlen($serialno) !== 5) {
    http_response_code(400);
    echo json_encode(['error' => 'หมายเลขซีเรียลต้องมี 5 ตัวอักษร']);
    exit;
}

// Check if device_id already exists
if ($device->deviceExists($device_id)) {
    http_response_code(409);
    echo json_encode(['error' => 'รหัสอุปกรณ์นี้มีในระบบแล้ว']);
    exit;
}

// Check if serial number already exists
if ($device->serialExists($serialno)) {
    http_response_code(409);
    echo json_encode(['error' => 'หมายเลขซีเรียลนี้มีในระบบแล้ว']);
    exit;
}

// Create device
$result = $device->createDevice($device_id, $device_name, $location_id, $serialno);

if ($result) {
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มอุปกรณ์สำเร็จ',
        'data' => [
            'device_id' => $device_id,
            'device_name' => $device_name,
            'location_id' => $location_id,
            'serialno' => $serialno
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการเพิ่มอุปกรณ์']);
}
?>
