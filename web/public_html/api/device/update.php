<?php
require_once __DIR__ . '/../../../database/table_device.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$device = new Table_device();

// Get input data (support both POST and PUT methods)
$input = $_POST;
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
}

$device_id = $input['device_id'] ?? '';
$device_name = $input['device_name'] ?? '';
$location_id = $input['location_id'] ?? '';
$serialno = $input['serialno'] ?? '';

if (empty($device_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกรหัสอุปกรณ์']);
    exit;
}
if (empty($device_name) ) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบทุกช่อง']);
    exit;
}

if (empty($location_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกรหัสตำแหน่ง']);
    exit;
}

if (empty($serialno)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกรหัสซีเรียล']);
    exit;
}

// Validate field lengths
if (strlen($device_id) !== 4) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสอุปกรณ์ต้องมี 4 ตัวอักษร']);
    exit;
}

if (strlen($location_id) !== 3) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสตำแหน่งต้องมี 3 ตัวอักษร']);
    exit;
}

if (strlen($serialno) !== 5) {
    http_response_code(400);
    echo json_encode(['error' => 'หมายเลขซีเรียลต้องมี 5 ตัวอักษร']);
    exit;
}

// Check if device exists
if (!$device->deviceExists($device_id)) {
    http_response_code(404);
    echo json_encode(['error' => 'ไม่พบอุปกรณ์ที่ระบุ']);
    exit;
}

// Check if serial number already exists (excluding current device)
if ($device->serialExists($serialno, $device_id)) {
    http_response_code(409);
    echo json_encode(['error' => 'หมายเลขซีเรียลนี้มีในระบบแล้ว']);
    exit;
}

// Update device
$result = $device->updateDevice($device_id, $device_name, $location_id, $serialno);

if ($result) {
    // Get updated device data
    $updated_device = $device->getDeviceById($device_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'อัพเดทอุปกรณ์สำเร็จ',
        'data' => $updated_device
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการอัพเดทอุปกรณ์']);
}
?>
