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

require_once __DIR__ . '/../../../database/table_environment.php';

try {
    // สร้าง Auth instance และตรวจสอบ authentication
    $auth = new Auth();
    $user = $auth->requireAuth();
    // รับพารามิเตอร์จาก query string
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page_size = isset($_GET['page_size']) ? (int)$_GET['page_size'] : 10;
    
    // ตรวจสอบค่าพารามิเตอร์
    if ($page < 1) {
        $page = 1;
    }
    
    if ($page_size < 1 || $page_size > 100) {
        $page_size = 10; // Default page size และจำกัดไม่เกิน 100
    }
    
    // สร้าง instance ของ Table_environment
    $envTable = new Table_environment();
    
    // เรียกใช้ function getenvironment
    $data = $envTable->getenvironment($page, $page_size);
    
    // นับจำนวนข้อมูลทั้งหมดสำหรับ pagination
    $totalData = $envTable->getAllDataByDateRange();
    $totalCount = count($totalData);
    
    // คำนวณข้อมูล pagination
    $totalPages = ceil($totalCount / $page_size);
    $hasNext = $page < $totalPages;
    $hasPrevious = $page > 1;
    
    // ส่งผลลัพธ์
    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'page_size' => $page_size,
            'total_records' => $totalCount,
            'total_pages' => $totalPages,
            'has_next' => $hasNext,
            'has_previous' => $hasPrevious
        ],
        'message' => 'ดึงข้อมูลสำเร็จ'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // ตรวจสอบว่าเป็น authentication error หรือไม่
    if (strpos($e->getMessage(), 'Authentication') !== false || strpos($e->getMessage(), 'token') !== false) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'ไม่ได้รับอนุญาตให้เข้าถึงข้อมูล',
            'error' => 'Authentication required',
            'pagination' => [
                'current_page' => 0,
                'page_size' => 0,
                'total_records' => 0,
                'total_pages' => 0,
                'has_next' => false,
                'has_previous' => false
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล',
            'error' => $e->getMessage(),
            'pagination' => [
                'current_page' => 0,
                'page_size' => 0,
                'total_records' => 0,
                'total_pages' => 0,
                'has_next' => false,
                'has_previous' => false
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>