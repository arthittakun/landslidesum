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

$location_id = $_POST['location_id'] ?? '';

// Validate location_id
if (empty($location_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรุณาระบุรหัสตำแหน่ง']);
    exit;
}

// Check if location exists in deleted records
$deleted_locations = $location->getDeletedLocations();
$location_found = false;
$location_data = null;

foreach ($deleted_locations as $deleted_location) {
    if ($deleted_location['location_id'] === $location_id) {
        $location_found = true;
        $location_data = $deleted_location;
        break;
    }
}

if (!$location_found) {
    http_response_code(404);
    echo json_encode(['error' => 'ไม่พบตำแหน่งที่ถูกลบ']);
    exit;
}

// Restore location
$result = $location->restoreLocation($location_id);

if ($result) {
    // Get restored location data
    $restored_location = $location->getLocationById($location_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'กู้คืนตำแหน่งสำเร็จ',
        'data' => $restored_location
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการกู้คืนตำแหน่ง']);
}
?>
