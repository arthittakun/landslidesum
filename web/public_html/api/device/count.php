<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../database/table_device.php';

try {
    $deviceTable = new Table_device();
    
    $activeDevices = $deviceTable->getCountDevices();
    $deletedDevices = $deviceTable->getCountDevicesdelete();
    
    $response = [
        'status' => 'success',
        'data' => [
            'active_devices' => $activeDevices,
            'deleted_devices' => $deletedDevices,
            'total_devices' => $activeDevices + $deletedDevices
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล',
        'error' => $e->getMessage()
    ]);
}
?>

