<?php
require_once __DIR__ . '/../../../auth/Auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../../database/connect.php';

try {
    // สร้าง Auth instance และตรวจสอบ authentication
    $auth = new Auth();
    $user = $auth->requireAuth();
    $db = new database();
    $conn = $db->getConnection();
    
    // Get all locations with latitude and longitude
    $locationSql = "SELECT location_id, location_name, latitude, longtitude FROM lnd_location WHERE void = 0 ORDER BY location_id";
    $locationStmt = $conn->prepare($locationSql);
    $locationStmt->execute();
    $locations = $locationStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data as requested
    $locationData = [];
    foreach ($locations as $location) {
        $locationData[] = [
            'lat' => (float)$location['latitude'],
            'lon' => (float)$location['longtitude'],
            'location_id' => $location['location_id'],
            'location_name' => $location['location_name']
        ];
    }
    
    echo json_encode($locationData, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลสถานที่',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
