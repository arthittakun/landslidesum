<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../../../database/connect.php';

try {
    $database = new database();
    $conn = $database->getConnection();
    
    // รับค่า location_id จาก GET parameter (ถ้าไม่มีจะแสดงทั้งหมด)
    $location_id = isset($_GET['location_id']) ? $_GET['location_id'] : null;
    
    $whereClause = "";
    $params = [];
    
    if ($location_id && $location_id !== 'all') {
        $whereClause = "AND l.location_id = :location_id";
        $params[':location_id'] = $location_id;
    }
    
    $sql = "SELECT 
                l.location_id,
                l.location_name,
                l.latitude,
                l.longtitude,
                d.device_id,
                d.device_name,
                e.temp,
                e.humid,
                e.rain,
                e.vibration,
                e.distance,
                e.soil,
                e.soil_high,
                e.landslide,
                e.floot,
                e.datekey,
                e.timekey,
                CONCAT(e.datekey, ' ', e.timekey) as datetime
            FROM lnd_location l
            LEFT JOIN lnd_device d ON l.location_id = d.location_id AND d.void = 0
            LEFT JOIN lnd_environment e ON d.device_id = e.device_id
            WHERE l.void = 0 $whereClause
            AND e.docno IN (
                SELECT MAX(e2.docno) 
                FROM lnd_environment e2 
                WHERE e2.device_id = e.device_id
            )
            ORDER BY l.location_name, d.device_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // จัดกลุ่มข้อมูลตาม location
    $locations = [];
    
    foreach ($results as $row) {
        $location_id = $row['location_id'];
        
        if (!isset($locations[$location_id])) {
            $locations[$location_id] = [
                'location_id' => $row['location_id'],
                'location_name' => $row['location_name'],
                'latitude' => $row['latitude'],
                'longtitude' => $row['longtitude'],
                'devices' => []
            ];
        }
        
        if ($row['device_id']) {
            $locations[$location_id]['devices'][] = [
                'device_id' => $row['device_id'],
                'device_name' => $row['device_name'],
                'latest_data' => [
                    'temp' => (float)$row['temp'],
                    'humid' => (float)$row['humid'],
                    'rain' => (float)$row['rain'],
                    'vibration' => (float)$row['vibration'],
                    'distance' => (float)$row['distance'],
                    'soil' => (float)$row['soil'],
                    'soil_high' => (float)$row['soil_high'],
                    'landslide' => (int)$row['landslide'],
                    'flood' => (int)$row['floot'],
                    'date' => $row['datekey'],
                    'time' => $row['timekey'],
                    'datetime' => $row['datetime']
                ]
            ];
        }
    }
    
    // แปลงเป็น array
    $response = [
        'success' => true,
        'message' => 'Data retrieved successfully',
        'data' => array_values($locations),
        'total_locations' => count($locations)
    ];
    
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
