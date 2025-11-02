<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Clean output buffer to prevent any previous output
if (ob_get_level()) {
    ob_clean();
}

try {
    require_once __DIR__ . '/../../../database/table_device.php';
    
    $deviceTable = new Table_device();
    
    // Get both active and deleted devices
    $allDevices = $deviceTable->getAllDevicesIncludingDeleted();
    
    $response = [
        'status' => 'success',
        'success' => true,
        'data' => $allDevices ?: []
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Get devices API error: " . $e->getMessage());
    http_response_code(500);
    
    $errorResponse = [
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลอุปกรณ์',
        'data' => []
    ];
    
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
}

// Ensure no additional output
exit;
?>
