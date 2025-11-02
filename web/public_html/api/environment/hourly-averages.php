<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../database/table_environment.php';

try {
    // Retrieve device_id from POST body
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    $device_id = isset($input['device_id']) ? $input['device_id'] : null;

    $environmentTable = new Table_environment();
    $data = $environmentTable->getHourlyAverages($device_id);

    if ($data && count($data) > 0) {
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'No hourly average data found for the specified device or all devices.'
        ]);
    }
} catch (Exception $e) {
    error_log("Error in get_hourly_averages API: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while processing the request.',
        'details' => $e->getMessage()
    ]);
}
?>
