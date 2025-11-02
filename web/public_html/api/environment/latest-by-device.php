<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../database/table_environment.php';

try {
    $start = isset($_GET['start']) ? $_GET['start'] : null;
    $end = isset($_GET['end']) ? $_GET['end'] : null;
    $envTable = new Table_environment();

    // ถ้ามี start/end ให้แสดงข้อมูลทุกแถวในช่วงวันที่, ถ้าไม่มีก็แสดงล่าสุดต่ออุปกรณ์
    if ($start && $end) {
        $data = $envTable->getAllDataByDateRange($start, $end);
    } else {
        $data = $envTable->getLatestDataByDevice();
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล',
        'error' => $e->getMessage()
    ]);
}
