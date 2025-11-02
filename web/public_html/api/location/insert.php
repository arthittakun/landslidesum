<?php
require_once __DIR__ . '/../../../database/table_location.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$location = new Table_location();

// Get input data
$location_id = $_POST['location_id'] ?? '';
$location_name = $_POST['location_name'] ?? '';
$latitude = $_POST['latitude'] ?? '';
$longtitude = $_POST['longtitude'] ?? '';

// Validate required fields
if (empty($location_id) || empty($location_name) || empty($latitude) || empty($longtitude)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบทุกช่อง']);
    exit;
}

// Validate location_id length (3 characters)
if (strlen($location_id) !== 3) {
    http_response_code(400);
    echo json_encode(['error' => 'รหัสตำแหน่งต้องมี 3 ตัวอักษร']);
    exit;
}

// Validate coordinates
if (!$location->validateCoordinates($latitude, $longtitude)) {
    http_response_code(400);
    echo json_encode(['error' => 'พิกัดไม่ถูกต้อง (ละติจูด: -90 ถึง 90, ลองจิจูด: -180 ถึง 180)']);
    exit;
}

// Check if location_id already exists
if ($location->locationExists($location_id)) {
    http_response_code(409);
    echo json_encode(['error' => 'รหัสตำแหน่งนี้มีในระบบแล้ว']);
    exit;
}

// Check if location name already exists
if ($location->locationNameExists($location_name)) {
    http_response_code(409);
    echo json_encode(['error' => 'ชื่อตำแหน่งนี้มีในระบบแล้ว']);
    exit;
}

// Create location
$result = $location->createLocation($location_id, $location_name, $latitude, $longtitude);

if ($result) {
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'เพิ่มตำแหน่งสำเร็จ',
        'data' => [
            'location_id' => $location_id,
            'location_name' => $location_name,
            'latitude' => $latitude,
            'longtitude' => $longtitude
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการเพิ่มตำแหน่ง']);
}
?>
