<?php
/**
 * Device by Location API
 * 
 * API สำหรับจัดการข้อมูลอุปกรณ์จัดกลุ่มตาม Location
 * 
 * การใช้งาน:
 * 1. GET /api/application/deviceBylocation.php - แสดงอุปกรณ์ทุก location
 * 2. GET /api/application/deviceBylocation.php?location=L01 - แสดงอุปกรณ์ใน location ID "L01"
 * 3. GET /api/application/deviceBylocation.php?location=Bangkok - แสดงอุปกรณ์ใน location ชื่อ "Bangkok"
 * 
 * Parameters:
 * - location: ID หรือชื่อของ location (optional)
 * - details: true/false - แสดงรายละเอียดอุปกรณ์หรือไม่ (default: true)
 * - format: 'detailed'/'simple' - รูปแบบการแสดงผล (default: 'detailed')
 * 
 * Authorization: Bearer Token required
 */

require_once __DIR__ . '/../../../auth/Auth.php';
require_once __DIR__ . '/../../../database/table_device.php';
require_once __DIR__ . '/../../../database/table_location.php';

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
    
    // สร้าง instance ของ table classes
    $deviceTable = new Table_device();
    $locationTable = new Table_location();
    
    // รับ parameters จาก GET request
    $location_filter = isset($_GET['location']) ? trim($_GET['location']) : null;
    $show_details = isset($_GET['details']) ? filter_var($_GET['details'], FILTER_VALIDATE_BOOLEAN) : true;
    $format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : 'detailed';
    
    if ($location_filter) {
        // ถ้ามีการระบุ location ให้ค้นหาข้อมูลของ location นั้นเฉพาะ
        $locationData = null;
        
        // ลองค้นหาด้วย location_id ก่อน
        $locationData = $locationTable->getLocationById($location_filter);
        
        // ถ้าไม่พบ ให้ลองค้นหาด้วยชื่อ location
        if (!$locationData) {
            $locations = $locationTable->getLocationsByName($location_filter);
            if (!empty($locations)) {
                $locationData = $locations[0]; // เอาผลลัพธ์แรกที่พบ
            }
        }
        
        if (!$locationData) {
            echo json_encode([
                'success' => false,
                'message' => 'Location not found',
                'query' => $location_filter,
                'code' => 404
            ]);
            exit;
        }
        
        // ดึงข้อมูลอุปกรณ์ของ location นี้พร้อมข้อมูล location
        $devices = $deviceTable->getDevicesByLocationWithInfo($locationData['location_id']);
        
        $result = [
            'location_info' => [
                'location_id' => $locationData['location_id'],
                'location_name' => $locationData['location_name'],
                'latitude' => $locationData['latitude'],
                'longtitude' => $locationData['longtitude'],
                'coordinates' => [
                    'lat' => (float)$locationData['latitude'],
                    'lng' => (float)$locationData['longtitude']
                ]
            ],
            'total_devices' => count($devices),
            'devices_count' => [
                'active' => count($devices),
                'total' => count($devices)
            ]
        ];
        
        if ($show_details) {
            $result['devices'] = $devices;
            
            // เพิ่มสถิติของอุปกรณ์
            $device_stats = [
                'device_ids' => array_column($devices, 'device_id'),
                'device_names' => array_column($devices, 'device_name'),
                'serial_numbers' => array_column($devices, 'serialno')
            ];
            $result['device_statistics'] = $device_stats;
        } else {
            $result['device_ids'] = array_column($devices, 'device_id');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Devices by location retrieved successfully',
            'data' => $result,
            // 'user_info' => $user,
            'timestamp' => date('Y-m-d H:i:s'),
            'code' => 200
        ]);
        
    } else {
        // ถ้าไม่มี location filter ให้แสดงทุก location พร้อมจำนวนอุปกรณ์
        if ($format === 'detailed' && $show_details) {
            // ใช้ method ใหม่ที่ให้ข้อมูลรายละเอียดครบถ้วน
            $devicesGrouped = $deviceTable->getDevicesGroupedByLocationDetailed();
            
            // เพิ่มข้อมูลเสริม
            foreach ($devicesGrouped as &$location) {
                $location['coordinates'] = [
                    'lat' => (float)$location['latitude'],
                    'lng' => (float)$location['longtitude']
                ];
                
                // สถิติอุปกรณ์
                if (!empty($location['devices'])) {
                    $location['device_statistics'] = [
                        'device_ids' => array_column($location['devices'], 'device_id'),
                        'device_names' => array_column($location['devices'], 'device_name'),
                        'serial_numbers' => array_column($location['devices'], 'serialno')
                    ];
                }
            }
        } else {
            // ใช้ method เดิมสำหรับข้อมูลพื้นฐาน
            $devicesGrouped = $deviceTable->getDevicesGroupedByLocation();
            
            if ($show_details) {
                // เพิ่มรายละเอียดอุปกรณ์ในแต่ละ location
                foreach ($devicesGrouped as &$location) {
                    $devices = $deviceTable->getDevicesByLocation($location['location_id']);
                    $location['devices'] = $devices;
                    
                    // เพิ่มข้อมูล location details
                    $locationInfo = $locationTable->getLocationById($location['location_id']);
                    if ($locationInfo) {
                        $location['latitude'] = $locationInfo['latitude'];
                        $location['longtitude'] = $locationInfo['longtitude'];
                        $location['coordinates'] = [
                            'lat' => (float)$locationInfo['latitude'],
                            'lng' => (float)$locationInfo['longtitude']
                        ];
                    }
                }
            }
        }
        
        $summary = [
            'total_locations' => count($devicesGrouped),
            'total_devices' => array_sum(array_column($devicesGrouped, 'total_devices')),
            'locations_with_devices' => count(array_filter($devicesGrouped, function($loc) { 
                return $loc['total_devices'] > 0; 
            })),
            'locations_without_devices' => count(array_filter($devicesGrouped, function($loc) { 
                return $loc['total_devices'] == 0; 
            }))
        ];
        
        echo json_encode([
            'success' => true,
            'message' => 'Devices grouped by location retrieved successfully',
            'data' => [
                'locations' => $devicesGrouped,
                'summary' => $summary
            ],
            // 'user_info' => $user,
            'timestamp' => date('Y-m-d H:i:s'),
            'code' => 200
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'code' => 500
    ]);
}
?>
