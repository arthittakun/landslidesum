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
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    $location_id = $_GET['location_id'] ?? null;
    $device_id = $_GET['device_id'] ?? null;
    $type = $_GET['type'] ?? 'summary'; // summary, critical, stats
    
    // Clean empty parameters
    if (empty($location_id)) $location_id = null;
    if (empty($device_id)) $device_id = null;
    
    $response = [
        'success' => true,
        'message' => 'Alert data retrieved successfully',
        'filters' => [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'location_id' => $location_id,
            'device_id' => $device_id,
            'type' => $type
        ],
        'debug' => [
            'query_params' => $_GET,
            'has_location_filter' => !empty($location_id),
            'has_device_filter' => !empty($device_id)
        ]
    ];
    
    switch ($type) {
        case 'stats':
            // สถิติการแจ้งเตือน
            $stats = $environment->getAlertStats($start_date, $end_date, $device_id, $location_id);
            $response['data'] = $stats;
            $response['message'] = 'Alert statistics retrieved successfully';
            break;
            
        case 'critical':
            // การแจ้งเตือนเฉพาะเหตุการณ์วิกฤต - แสดงข้อมูลทั้งหมดเพื่อการ demo
            $critical = $environment->getEnvironmentAnalysis($start_date, $end_date, $device_id, $location_id);
            $response['data'] = $critical;
            $response['total_critical'] = count($critical);
            $response['message'] = 'Environment data retrieved successfully (showing all data for demo)';
            break;
            
        case 'summary':
        default:
            // แสดงข้อมูลทั้งหมดแทนที่จะแสดงเฉพาะ alert เพื่อการ demo
            $alerts = $environment->getEnvironmentAnalysis($start_date, $end_date, $device_id, $location_id);
            $stats = $environment->getEnvironmentStats($start_date, $end_date, $device_id, $location_id);
            
            $response['data'] = $alerts;
            $response['stats'] = $stats;
            $response['total_records'] = count($alerts);
            $response['message'] = 'Environment data retrieved successfully (showing all data for demo)';
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
