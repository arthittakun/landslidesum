<?php
require_once __DIR__ . '/../../../database/table_location.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$location = new Table_location();

// Get input data
$input = $_POST;
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
}

$location_id = $input['location_id'] ?? '';
$location_name = $input['location_name'] ?? '';
$latitude = $input['latitude'] ?? '';
$longtitude = $input['longtitude'] ?? '';

// Validate required fields
if (empty($location_id) || empty($location_name) || empty($latitude) || empty($longtitude)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณากรอกข้อมูลให้ครบทุกช่อง']);
    exit;
}

// Validate coordinates
if (!$location->validateCoordinates($latitude, $longtitude)) {
    http_response_code(400);
    echo json_encode(['error' => 'พิกัดไม่ถูกต้อง (ละติจูด: -90 ถึง 90, ลองจิจูด: -180 ถึง 180)']);
    exit;
}

// Check if location exists
if (!$location->locationExists($location_id)) {
    http_response_code(404);
    echo json_encode(['error' => 'ไม่พบตำแหน่งที่ระบุ']);
    exit;
}

// Check if location name already exists (excluding current location)
if ($location->locationNameExists($location_name, $location_id)) {
    http_response_code(409);
    echo json_encode(['error' => 'ชื่อตำแหน่งนี้มีในระบบแล้ว']);
    exit;
}

// Update location
$result = $location->updateLocation($location_id, $location_name, $latitude, $longtitude);

if ($result) {
    // Get updated location data
    $updated_location = $location->getLocationById($location_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'อัพเดทตำแหน่งสำเร็จ',
        'data' => $updated_location
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการอัพเดทตำแหน่ง']);
}
?>
