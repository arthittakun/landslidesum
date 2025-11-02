<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../database/table_environment.php';

try {
    $days = isset($_GET['days']) ? intval($_GET['days']) : 7; // Default to 7 days if not specified

    $environmentTable = new Table_environment();
    $data = $environmentTable->getDataByTimeRange($days);

    if ($data) {
        // Add new calculations to the response
        $response = [
            'status' => 'success',
            'data' => [
                'avg_temp' => round($data['avg_temp'], 1),
                'min_temp' => $data['min_temp'] ?? 0,
                'max_temp' => $data['max_temp'] ?? 0,
                'avg_humid' => round($data['avg_humid'], 1),
                'min_humid' => $data['min_humid'] ?? 0,
                'max_humid' => $data['max_humid'] ?? 0,
                'avg_rain' => round($data['avg_rain'], 1),
                'max_rain' => $data['max_rain'] ?? 0,
                'avg_vibration' => round($data['avg_vibration'], 2),
                'max_vibration' => $data['max_vibration'] ?? 0,
                'avg_soil' => round($data['avg_soil'], 0),
                'max_soil' => $data['max_soil'] ?? 0
            ]
        ];
        echo json_encode($response);
    } else {
        throw new Exception('No data found for the specified time range.');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล',
        'error' => $e->getMessage()
    ]);
}
?>
