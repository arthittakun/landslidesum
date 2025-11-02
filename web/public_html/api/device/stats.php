<?php
require_once __DIR__ . '/../../../database/table_device.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$device = new Table_device();

try {
    // Get total device count (active only)
    $total_active_devices = $device->getCountDevices();
    
    // Get deleted devices count
    $deleted_devices = $device->getCountDevicesdelete();
    
    // Get total devices (active + deleted)
    $total_devices = $total_active_devices + $deleted_devices;
    
    // Get devices grouped by location
    $all_devices = $device->getAllDevices();
    $devices_by_location = [];
    
    foreach ($all_devices as $dev) {
        $location = $dev['location_id'];
        if (!isset($devices_by_location[$location])) {
            $devices_by_location[$location] = 0;
        }
        $devices_by_location[$location]++;
    }
    
    // Get recent devices (you can modify this logic based on your needs)
    $recent_devices = array_slice($all_devices, -5); // Last 5 devices
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_devices' => $total_devices,
            'active_devices' => $total_active_devices,
            'deleted_devices' => $deleted_devices,
            'devices_by_location' => $devices_by_location,
            'recent_devices' => $recent_devices
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงสถิติ']);
}
?>
