<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../database/table_environment.php';

try {
    $envTable = new Table_environment();
    
    // รับพารามิเตอร์จาก query string
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $location = isset($_GET['location']) ? $_GET['location'] : null;
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    
    // ดึงข้อมูลทั้งหมดหรือตามเงื่อนไข
    if ($start_date && $end_date) {
        $data = $envTable->getAllDataByDateRange($start_date, $end_date);
    } else {
        // ใช้ displayData เพื่อดึงข้อมูลทั้งหมด
        $data = $envTable->displayData();
    }
    
    // กรองข้อมูลที่มีค่าเซนเซอร์เป็น 0 หรือน้อยกว่า 0
    if (!empty($data)) {
        $data = array_filter($data, function($item) {
            // เช็คว่าค่าเซนเซอร์ทั้งหมดมีค่ามากกว่า 0
            $temp = isset($item['temp']) ? (float)$item['temp'] : 0;
            $humid = isset($item['humid']) ? (float)$item['humid'] : 0;
            $rain = isset($item['rain']) ? (float)$item['rain'] : 0;
            $vibration = isset($item['vibration']) ? (float)$item['vibration'] : 0;
            $distance = isset($item['distance']) ? (float)$item['distance'] : 0;
            $soil = isset($item['soil']) ? (float)$item['soil'] : 0;
            
            // ส่งข้อมูลออกเฉพาะที่มีค่าเซนเซอร์อย่างน้อย 1 ตัวที่มากกว่า 0
            return ($temp > 0 || $humid > 0 || $rain > 0 || $vibration > 0 || $distance > 0 || $soil > 0);
        });
        $data = array_values($data); // reindex array
    }
    
    // ถ้าต้องการ filter ตาม location
    if ($location && !empty($data)) {
        $data = array_filter($data, function($item) use ($location) {
            return isset($item['location_name']) && $item['location_name'] === $location;
        });
        $data = array_values($data); // reindex array
    }
    
    // ถ้ามี limit parameter ให้ตัดข้อมูล
    if ($limit && !empty($data)) {
        $data = array_slice($data, $offset, $limit);
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'total' => count($data)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
