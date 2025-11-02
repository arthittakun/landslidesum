<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../database/table_environment.php';

try {
    $environmentTable = new Table_environment();

    $floodData = $environmentTable->getCountFlood();
    $landslideData = $environmentTable->getCountLandslide();

    $response = [
        'status' => 'success',
        'data' => [
            'flood' => $floodData,
            'landslide' => $landslideData,
            'total_critical' => $floodData + $landslideData
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล',
    ]);
}
?>
