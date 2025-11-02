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

// Get query parameters
$location_id = $_GET['location_id'] ?? '';
$location_name = $_GET['location_name'] ?? '';
$search = $_GET['search'] ?? '';
$latitude = $_GET['latitude'] ?? '';
$longtitude = $_GET['longtitude'] ?? '';
$radius = $_GET['radius'] ?? 10; // default 10km radius
$type = $_GET['type'] ?? 'all';

try {
    switch ($type) {
        case 'options':
            // Get location options for dropdown
            $result = $location->getLocationOptions();
            echo json_encode([
                'success' => true,
                'data' => $result,
                'count' => count($result),
                'type' => 'options'
            ]);
            break;

        case 'nearest':
            if (empty($latitude) || empty($longtitude)) {
                http_response_code(400);
                echo json_encode(['error' => 'กรุณาระบุพิกัดสำหรับค้นหาตำแหน่งใกล้เคียง']);
                exit;
            }
            $limit = $_GET['limit'] ?? 5;
            $result = $location->findNearestLocations($latitude, $longtitude, $limit);
            echo json_encode([
                'success' => true,
                'data' => $result,
                'count' => count($result),
                'type' => 'nearest'
            ]);
            break;

        default:
            if (!empty($location_id)) {
                // Get specific location by ID
                $result = $location->getLocationById($location_id);
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'data' => $result
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'ไม่พบตำแหน่งที่ระบุ']);
                }
            } elseif (!empty($location_name)) {
                // Get locations by name
                $result = $location->getLocationsByName($location_name);
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'count' => count($result)
                ]);
            } elseif (!empty($search)) {
                // Search locations
                $result = $location->searchLocations($search);
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'count' => count($result)
                ]);
            } else {
                // Get all locations (including deleted if requested)
                $include_deleted = $_GET['include_deleted'] ?? 'false';
                if ($include_deleted === 'true') {
                    $result = $location->getAllLocationsIncludingDeleted();
                } else {
                    $result = $location->getAllLocations();
                }
                echo json_encode([
                    'success' => true,
                    'data' => $result,
                    'count' => count($result)
                ]);
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล']);
}
?>
