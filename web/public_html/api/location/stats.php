<?php
require_once __DIR__ . '/../../../database/table_location.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$location = new Table_location();

try {
    // Get all locations including deleted ones for comprehensive stats
    $all_locations = $location->getAllLocationsIncludingDeleted();
    $active_locations = array_filter($all_locations, function($loc) { return $loc['void'] == 0; });
    $deleted_locations = array_filter($all_locations, function($loc) { return $loc['void'] == 1; });
    
    $total_count = count($all_locations);
    $active_count = count($active_locations);
    $deleted_count = count($deleted_locations);
    
    // Calculate coordinate statistics from active locations only
    $latitudes = array_column($active_locations, 'latitude');
    $longitudes = array_column($active_locations, 'longtitude');
    
    $coordinate_ranges = [
        'latitude' => [
            'min' => count($latitudes) > 0 ? min($latitudes) : 0,
            'max' => count($latitudes) > 0 ? max($latitudes) : 0,
            'avg' => count($latitudes) > 0 ? array_sum($latitudes) / count($latitudes) : 0
        ],
        'longitude' => [
            'min' => count($longitudes) > 0 ? min($longitudes) : 0,
            'max' => count($longitudes) > 0 ? max($longitudes) : 0,
            'avg' => count($longitudes) > 0 ? array_sum($longitudes) / count($longitudes) : 0
        ]
    ];
    
    // Get recent locations (last 5 active)
    $recent_locations = array_slice($active_locations, -5);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_locations' => $total_count,
            'active_locations' => $active_count,
            'deleted_locations' => $deleted_count,
            'avg_latitude' => $coordinate_ranges['latitude']['avg'],
            'avg_longitude' => $coordinate_ranges['longitude']['avg'],
            'coordinate_ranges' => $coordinate_ranges,
            'recent_locations' => $recent_locations
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงสถิติ']);
}
?>
