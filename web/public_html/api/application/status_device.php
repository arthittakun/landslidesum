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
    
    // Get total count of locations
    $locationCountSql = "SELECT COUNT(*) as total_locations FROM lnd_location WHERE void = 0";
    $locationStmt = $conn->prepare($locationCountSql);
    $locationStmt->execute();
    $locationResult = $locationStmt->fetch(PDO::FETCH_ASSOC);
    $count_location = (int)$locationResult['total_locations'];
    
    // Get all devices
    $deviceSql = "SELECT device_id FROM lnd_device WHERE void = 0";
    $deviceStmt = $conn->prepare($deviceSql);
    $deviceStmt->execute();
    $devices = $deviceStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count_online = 0;
    $count_offline = 0;
    
    foreach ($devices as $device) {
        $device_id = $device['device_id'];
        
        // Check latest data for each device (within 2 hours)
        $checkSql = "SELECT COUNT(*) as data_count 
                     FROM lnd_environment 
                     WHERE device_id = :device_id 
                     AND CONCAT(datekey, ' ', timekey) >= DATE_SUB(NOW(), INTERVAL 2 HOUR)";
        
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        $data_count = $result['data_count'];
        
        if ($data_count > 0) {
            $count_online++;
        } else {
            $count_offline++;
        }
    }
    
    echo json_encode([
        'count_online' => $count_online,
        'count_offline' => $count_offline,
        'count_location' => $count_location,
        'status' => 'success'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // ตรวจสอบว่าเป็น authentication error หรือไม่
    if (strpos($e->getMessage(), 'Authentication') !== false || strpos($e->getMessage(), 'token') !== false) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'ไม่ได้รับอนุญาตให้เข้าถึงข้อมูล',
            'error' => 'Authentication required'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลสถานะอุปกรณ์',
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>
