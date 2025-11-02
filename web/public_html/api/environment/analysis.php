<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../../../database/table_environment.php';

try {
    $environment = new Table_environment();
    
    // รับ parameters
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $device_id = $_GET['device_id'] ?? null;
    $location_id = $_GET['location_id'] ?? null;
    $type = $_GET['type'] ?? 'analysis';
    
    // ตรวจสอบและปรับปรุงวันที่
    if (!$end_date && !$start_date) {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-30 days'));
    } elseif (!$start_date) {
        $start_date = date('Y-m-d', strtotime($end_date . ' -30 days'));
    } elseif (!$end_date) {
        $end_date = date('Y-m-d');
    }
    
    $response = [
        'success' => true,
        'message' => 'Data retrieved successfully',
        'filters' => [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'device_id' => $device_id,
            'location_id' => $location_id,
            'type' => $type
        ]
    ];
    
    switch ($type) {
        case 'stats':
            // ข้อมูลสถิติ
            $stats = $environment->getEnvironmentStats($start_date, $end_date, $device_id, $location_id);
            $response['data'] = $stats;
            $response['total_records'] = $stats['total_records'] ?? 0;
            $response['message'] = 'Environment statistics retrieved successfully';
            break;
            
        case 'critical':
            // ข้อมูลวิกฤต
            $data = $environment->getCriticalAnalysis($start_date, $end_date);
            $response['data'] = $data;
            $response['total_critical'] = count($data);
            $response['message'] = 'Critical analysis data retrieved successfully';
            break;
            
        case 'trends':
            // แนวโน้มรายวัน
            $days = $_GET['days'] ?? 30;
            $data = $environment->getDailyTrends($days, $device_id, $location_id);
            $response['data'] = $data;
            $response['days'] = $days;
            $response['message'] = 'Daily trends data retrieved successfully';
            break;
            
        case 'location-comparison':
            // เปรียบเทียบโลเคชัน
            $data = $environment->getLocationComparison($start_date, $end_date);
            $response['data'] = $data;
            $response['total_locations'] = count($data);
            $response['message'] = 'Location comparison data retrieved successfully';
            break;
            
        case 'analysis':
        default:
            // ข้อมูลวิเคราะห์ทั่วไป
            $data = $environment->getEnvironmentAnalysis($start_date, $end_date, $device_id, $location_id);
            $stats = $environment->getEnvironmentStats($start_date, $end_date, $device_id, $location_id);
            
            $response['data'] = $data;
            $response['stats'] = $stats;
            $response['total_records'] = count($data);
            $response['message'] = 'Environment analysis data retrieved successfully';
            break;
    }
    
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
