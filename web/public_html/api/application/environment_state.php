<?php
require_once __DIR__ . '/../../../auth/Auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../../database/connect.php';

try {
    // สร้าง Auth instance และตรวจสอบ authentication
    $auth = new Auth();
    $user = $auth->requireAuth();
    $db = new database();
    $conn = $db->getConnection();
    
    // รับค่า pagination parameters
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $page_size = isset($_GET['page_size']) ? max(1, min(100, (int)$_GET['page_size'])) : 20;
    $offset = ($page - 1) * $page_size;
    
    // รับค่า device_id filter (optional)
    $device_id = isset($_GET['device_id']) ? $_GET['device_id'] : null;
    
    // สร้าง WHERE clause
    $whereClause = "WHERE 1=1";
    $params = [];
    
    if ($device_id) {
        $whereClause .= " AND device_id = :device_id";
        $params[':device_id'] = $device_id;
    }
    
    // นับจำนวนข้อมูลทั้งหมด (JOIN กับตาราง device และ location)
    $countSql = "SELECT COUNT(*) as total 
                 FROM lnd_environment e
                 LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
                 LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
                 " . $whereClause;
    $countStmt = $conn->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // คำนวณ pagination
    $total_pages = ceil($totalCount / $page_size);
    $has_next = $page < $total_pages;
    $has_previous = $page > 1;
    
    // หาค่าสูงสุดของแต่ละพารามิเตอร์จากข้อมูลทั้งหมด
    $maxValuesSql = "SELECT 
                        MAX(rain) as max_rain,
                        MAX(temp) as max_temp, 
                        MAX(humid) as max_humid,
                        MAX(soil) as max_soil
                     FROM lnd_environment";
    $maxStmt = $conn->prepare($maxValuesSql);
    $maxStmt->execute();
    $maxValues = $maxStmt->fetch(PDO::FETCH_ASSOC);
    
    // ดึงข้อมูล environment พร้อม location name
    $dataSql = "SELECT 
                    e.device_id,
                    e.rain,
                    e.temp,
                    e.humid,
                    e.soil,
                    e.datekey,
                    e.timekey,
                    CONCAT(e.datekey, ' ', e.timekey) as create_at,
                    l.location_name,
                    l.location_id
                FROM lnd_environment e
                LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
                LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
                " . $whereClause . "
                ORDER BY e.datekey DESC, e.timekey DESC 
                LIMIT :limit OFFSET :offset";
    
    $dataStmt = $conn->prepare($dataSql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }
    $dataStmt->bindValue(':limit', $page_size, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $dataStmt->execute();
    $environmentData = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format ข้อมูลตาม response ที่ต้องการ (คำนวณเปอร์เซ็นต์จากค่าสูงสุด)
    $formattedData = [];
    foreach ($environmentData as $row) {
        // คำนวณเปอร์เซ็นต์จากค่าสูงสุด (ป้องกันการหารด้วย 0)
        $rainPercent = $maxValues['max_rain'] > 0 ? 
            (((float)$row['rain'] / (float)$maxValues['max_rain']) * 100) : 0;
        $tempPercent = $maxValues['max_temp'] > 0 ? 
            (((float)$row['temp'] / (float)$maxValues['max_temp']) * 100) : 0;
        $humidPercent = $maxValues['max_humid'] > 0 ? 
            (((float)$row['humid'] / (float)$maxValues['max_humid']) * 100) : 0;
        $soilPercent = $maxValues['max_soil'] > 0 ? 
            (((float)$row['soil'] / (float)$maxValues['max_soil']) * 100) : 0;
        
        $formattedData[] = [
            'device_id' => $row['device_id'],
            'location_id' => $row['location_id'],
            'location_name' => $row['location_name'],
            'rain' => number_format($rainPercent, 2) . '%',
            'temp' => number_format($tempPercent, 2) . '%',
            'humid' => number_format($humidPercent, 2) . '%',
            'soil' => number_format($soilPercent, 2) . '%',
            'create_at' => $row['create_at']
        ];
    }
    
    // Response format
    $response = [
        'data' => $formattedData,
        'pagination' => [
            'page' => $page,
            'page_size' => $page_size,
            'total_count' => (int)$totalCount,
            'total_pages' => $total_pages,
            'has_next' => $has_next,
            'has_previous' => $has_previous
        ],
        'status' => 'success'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("ERROR in environment_state.php: " . $e->getMessage());
    error_log("ERROR trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล Environment',
        'error' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>
