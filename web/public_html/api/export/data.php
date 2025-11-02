<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../../database/table_environment.php';
require_once '../../../database/table_device.php';
require_once '../../../database/table_location.php';

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Debug log
error_log('Export API called with params: ' . json_encode($_GET));

try {
    $type = $_GET['type'] ?? 'environment';
    $format = $_GET['format'] ?? 'json';
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    $device_id = $_GET['device_id'] ?? null;
    $location_id = $_GET['location_id'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;

    error_log("Processing type: $type, format: $format, limit: $limit");

    switch ($type) {
        case 'environment':
            $tableEnv = new Table_environment();
            $data = $tableEnv->getEnvironmentAnalysis($start_date, $end_date, $device_id, $location_id);
            if ($limit) {
                $data = array_slice($data, 0, $limit);
            }
            error_log('Environment data count: ' . count($data));
            break;
            
        case 'devices':
            $tableDevice = new Table_device();
            $data = $tableDevice->getAllDevices();
            break;
            
        case 'locations':
            $tableLocation = new Table_location();
            $data = $tableLocation->getAllLocations();
            break;
            
        case 'alerts':
            $tableEnv = new Table_environment();
            $allData = $tableEnv->getEnvironmentAnalysis($start_date, $end_date, $device_id, $location_id);
            // Filter only alert records
            $data = array_filter($allData, function($record) {
                return $record['landslide'] == 1 || $record['floot'] == 1;
            });
            $data = array_values($data); // Reset array keys
            if ($limit) {
                $data = array_slice($data, 0, $limit);
            }
            break;
            
        case 'summary':
            $tableEnv = new Table_environment();
            $tableDevice = new Table_device();
            $tableLocation = new Table_location();
            
            $envData = $tableEnv->getEnvironmentAnalysis($start_date, $end_date, $device_id, $location_id);
            $deviceData = $tableDevice->getAllDevices();
            $locationData = $tableLocation->getAllLocations();
            
            $alertCount = count(array_filter($envData, function($record) {
                return $record['landslide'] == 1 || $record['floot'] == 1;
            }));
            
            $data = [
                'summary' => [
                    'total_records' => count($envData),
                    'total_devices' => count($deviceData),
                    'total_locations' => count($locationData),
                    'total_alerts' => $alertCount,
                    'export_date' => date('Y-m-d H:i:s'),
                    'date_range' => [
                        'start_date' => $start_date,
                        'end_date' => $end_date
                    ],
                    'filters' => [
                        'device_id' => $device_id,
                        'location_id' => $location_id,
                        'limit' => $limit
                    ]
                ],
                'environment_data' => $limit ? array_slice($envData, 0, $limit) : $envData,
                'device_data' => $deviceData,
                'location_data' => $locationData
            ];
            break;
            
        default:
            throw new Exception('Invalid export type');
    }

    // Handle different output formats
    switch ($format) {
        case 'csv':
            if ($type === 'summary') {
                // For summary, export environment data as CSV
                $csvData = $data['environment_data'];
            } else {
                $csvData = $data;
            }
            
            if (empty($csvData)) {
                throw new Exception('No data to export');
            }
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $type . '_export_' . date('Y-m-d_H-i-s') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding in Excel
            fwrite($output, "\xEF\xBB\xBF");
            
            // Write CSV header
            if (!empty($csvData)) {
                fputcsv($output, array_keys($csvData[0]));
                
                // Write data rows
                foreach ($csvData as $row) {
                    // Convert each field to UTF-8 if needed
                    $utf8Row = array_map(function($field) {
                        if (is_string($field) && !mb_check_encoding($field, 'UTF-8')) {
                            return mb_convert_encoding($field, 'UTF-8', 'auto');
                        }
                        return $field;
                    }, $row);
                    fputcsv($output, $utf8Row);
                }
            }
            
            fclose($output);
            exit;
            
        case 'excel':
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $type . '_export_' . date('Y-m-d_H-i-s') . '.xls"');
            
            // Add BOM for UTF-8
            echo "\xEF\xBB\xBF";
            echo '<html>';
            echo '<head><meta charset="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>';
            echo '<body>';
            echo '<table border="1">';
            
            if ($type === 'summary') {
                $excelData = $data['environment_data'];
            } else {
                $excelData = $data;
            }
            
            if (!empty($excelData)) {
                // Header row
                echo '<tr>';
                foreach (array_keys($excelData[0]) as $header) {
                    $utf8Header = is_string($header) && !mb_check_encoding($header, 'UTF-8') 
                        ? mb_convert_encoding($header, 'UTF-8', 'auto') 
                        : $header;
                    echo '<th>' . htmlspecialchars($utf8Header, ENT_QUOTES, 'UTF-8') . '</th>';
                }
                echo '</tr>';
                
                // Data rows
                foreach ($excelData as $row) {
                    echo '<tr>';
                    foreach ($row as $cell) {
                        $utf8Cell = is_string($cell) && !mb_check_encoding($cell, 'UTF-8') 
                            ? mb_convert_encoding($cell, 'UTF-8', 'auto') 
                            : $cell;
                        echo '<td>' . htmlspecialchars($utf8Cell, ENT_QUOTES, 'UTF-8') . '</td>';
                    }
                    echo '</tr>';
                }
            }
            
            echo '</table>';
            echo '</body>';
            echo '</html>';
            exit;
            
        case 'json':
        default:
            header('Content-Type: application/json; charset=utf-8');
            
            // Ensure all data is properly UTF-8 encoded
            $processedData = json_decode(json_encode($data, JSON_UNESCAPED_UNICODE), true);
            
            echo json_encode([
                'success' => true,
                'type' => $type,
                'format' => $format,
                'count' => is_array($processedData) ? count($processedData) : 1,
                'export_time' => date('Y-m-d H:i:s'),
                'data' => $processedData
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'export_time' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
+