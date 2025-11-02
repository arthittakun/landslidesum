<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../../../database/table_device.php';

try {
    $device = new Table_device();
    $location_id = $_GET['location_id'] ?? null;
    
    if ($location_id) {
        $data = $device->getDevicesByLocationWithInfo($location_id);
    } else {
        $data = $device->getAllDevices();
    }
    
    $response = [
        'success' => true,
        'message' => 'Devices retrieved successfully',
        'data' => $data,
        'total' => count($data),
        'location_id' => $location_id
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
