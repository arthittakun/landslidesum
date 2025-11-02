<?php
require_once __DIR__ . '/../../../auth/Auth.php';
require_once __DIR__ . '/../../../database/table_device.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // สร้าง Auth instance และตรวจสอบ authentication
    $auth = new Auth();
    $user = $auth->requireAuth();
    
    // สร้าง instance ของ Table_device
    $deviceTable = new Table_device();
    
    // ดึงข้อมูลสถานะอุปกรณ์ทั้งหมด
    $deviceStatusList = $deviceTable->getDeviceStatusList();
    
    if ($deviceStatusList && count($deviceStatusList) > 0) {
        // คำนวณสถิติ
        $totalDevices = count($deviceStatusList);
        $onlineDevices = 0;
        $offlineDevices = 0;
        $warningDevices = 0;
        
        foreach ($deviceStatusList as &$device) {
            // เพิ่มการคำนวณสถานะ
            $device['status_text'] = $deviceTable->getDeviceStatusText($device['status'], $device['last_update']);
            $device['status_color'] = $deviceTable->getDeviceStatusColor($device['status'], $device['last_update']);
            $device['is_online'] = $deviceTable->isDeviceOnline($device['last_update']);
            
            // นับสถิติ
            if ($device['is_online']) {
                $onlineDevices++;
            } else {
                $offlineDevices++;
            }
            
            if ($device['status'] == 2 || $device['status'] == 3) {
                $warningDevices++;
            }
        }
        
        // ส่งผลลัพธ์พร้อมสถิติ
        echo json_encode([
            'status' => 'success',
            'data' => $deviceStatusList,
            'statistics' => [
                'total_devices' => $totalDevices,
                'online_devices' => $onlineDevices,
                'offline_devices' => $offlineDevices,
                'warning_devices' => $warningDevices,
                'online_percentage' => $totalDevices > 0 ? round(($onlineDevices / $totalDevices) * 100, 1) : 0
            ],
            'message' => 'ดึงข้อมูลสถานะอุปกรณ์สำเร็จ',
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        echo json_encode([
            'status' => 'success',
            'data' => [],
            'statistics' => [
                'total_devices' => 0,
                'online_devices' => 0,
                'offline_devices' => 0,
                'warning_devices' => 0,
                'online_percentage' => 0
            ],
            'message' => 'ไม่พบข้อมูลอุปกรณ์',
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    // ตรวจสอบว่าเป็น authentication error หรือไม่
    if (strpos($e->getMessage(), 'Authentication') !== false || strpos($e->getMessage(), 'token') !== false) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'ไม่ได้รับอนุญาตให้เข้าถึงข้อมูล',
            'error' => 'Authentication required'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลสถานะอุปกรณ์',
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>
