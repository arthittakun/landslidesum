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
    $risk_level = $_GET['risk_level'] ?? null; // 1, 2, 3 หรือ null (ทั้งหมด)
    $type = $_GET['type'] ?? 'both'; // flood, landslide, both
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    
    // เรียกใช้ method จาก class
    $locations = $environment->getCriticalLocationsByRiskLevel($risk_level, $type, $start_date, $end_date);
    
    // แปลงค่าเป็น integer และเพิ่มคำอธิบายความเสี่ยง
    foreach ($locations as &$location) {
        // แปลงค่าเป็น integer (ใช้สำหรับ logic)
        $max_risk_level = (int)$location['max_risk_level'];
        
        // เพิ่มชื่อระดับความเสี่ยง
        if ($max_risk_level == 3) {
            $location['risk_level_text'] = 'ความเสี่ยงสูง';
        } elseif ($max_risk_level == 2) {
            $location['risk_level_text'] = 'เข้าใกล้อันตราย';
        } else {
            $location['risk_level_text'] = 'มีความเสี่ยง';
        }
        
        // ใช้ค่า risk_description จาก textes ในฐานข้อมูล
        // ถ้าไม่มีค่าให้ใช้ข้อความเริ่มต้น
        if (empty($location['risk_description'])) {
            $location['risk_description'] = $location['risk_level_text'];
        }
        
        // ลบฟิลด์ที่ไม่ต้องการแสดง
        unset($location['affected_devices']);
        unset($location['total_alerts']);
        unset($location['landslide_level_1']);
        unset($location['landslide_level_2']);
        unset($location['landslide_level_3']);
        unset($location['flood_level_1']);
        unset($location['flood_level_2']);
        unset($location['flood_level_3']);
        unset($location['max_risk_level']);
    }
    
    $response = [
        'success' => true,
        'message' => 'ดึงข้อมูลหมู่บ้านที่มีความเสี่ยงสำเร็จ',
        'total_locations' => count($locations),
        'filters' => [
            'risk_level' => $risk_level,
            'type' => $type,
            'start_date' => $start_date,
            'end_date' => $end_date
        ],
        'data' => $locations
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
