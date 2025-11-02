<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../../database/table_environment.php';

try {
    $type = isset($_GET['type']) ? $_GET['type'] : 'combined'; // Default to 'combined'

    $environmentTable = new Table_environment();

    switch ($type) {
        case 'landslide':
            $data = $environmentTable->getLandslideDataByDevice();
            $response = [
                'status' => 'success',
                'type' => 'landslide',
                'data' => $data
            ];
            break;

        case 'flood':
            $data = $environmentTable->getFloodDataByDevice();
            $response = [
                'status' => 'success',
                'type' => 'flood',
                'data' => $data
            ];
            break;

        case 'combined':
            $totalCounts = $environmentTable->getTotalLandslideAndFloodCounts();
            $response = [
                'status' => 'success',
                'type' => 'combined',
                'data' => $totalCounts
                
            ];
            break;

        default:
             $landslideData = $environmentTable->getLandslideDataByDevice();
            $floodData = $environmentTable->getFloodDataByDevice();
            $totalCounts = $environmentTable->getTotalLandslideAndFloodCounts();
            $response = [
                'status' => 'success',
                'type' => 'combined',
                'data' => [
                    'landslide' => $landslideData,
                    'flood' => $floodData,
                    'totals' => $totalCounts
                ]
            ];
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล',
        'error' => $e->getMessage()
    ]);
}
?>
