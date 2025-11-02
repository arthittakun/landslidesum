<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../database/table_device.php';

try {
    $deviceTable = new Table_device();
    $data = $deviceTable->getDevicesGroupedByLocation();

    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'ไม่พบข้อมูลอุปกรณ์ในพื้นที่'
        ]);
        exit;
    }

    $response = [
        'status' => 'success',
        'data' => $data
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Get devices by location API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล'
    ]);
}
?>
