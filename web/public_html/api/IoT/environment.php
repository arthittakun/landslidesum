<?php
require_once __DIR__ . '/../../../database/table_for_IoT.php';
require_once __DIR__ . '/../../../database/table_notification.php';
require_once __DIR__ . '/../../../service/Gemini_api.php';
require_once __DIR__ . '/simple_image_storage.php';

// ตรวจสอบ request method ก่อน
$request_method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($request_method !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only POST requests are accepted.',
        'allowed_methods' => ['POST'],
        'received_method' => $request_method,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

$environment = new tableIoT_environment();
$notificationTable = new Table_notification();

// รับข้อมูลจาก POST request เท่านั้น
$device_id = $_POST['device_id'] ?? '';
$temperature = $_POST['temperature'] ?? 0;
$humidity = $_POST['humidity'] ?? 0;
$rain = $_POST['rain'] ?? 0;
$vibration = $_POST['vibration'] ?? 0;
$distance = $_POST['distance'] ?? 0;
$soil = $_POST['soil'] ?? 0;
$soil_high = $_POST['soil_high'] ?? 0;
$image = $_POST['image'] ?? null; //base64 encoded image

// ตรวจสอบว่ามี device_id เท่านั้น
if (empty($device_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Device ID is required',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// แปลงค่าที่ได้รับให้เป็นประเภทที่ถูกต้อง
$temperature = floatval($temperature);
$humidity = floatval($humidity);
$rain = floatval($rain);
$vibration = floatval($vibration);
$distance = floatval($distance);
$soil = floatval($soil);
$soil_high = floatval($soil_high);

if (!$environment->getdeviceid($device_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Device not found',
        'device_id' => $device_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// ฟังก์ชันตรวจสอบความถูกต้องของรูปภาพ base64
if (!function_exists('validateBase64Image')) {
    function validateBase64Image($base64_data) {
        // ตรวจสอบว่ามีข้อมูลหรือไม่
        if (empty($base64_data)) {
            return ['valid' => false, 'error' => 'Empty image data'];
        }
        
        // ลองแปลง base64 เป็นข้อมูลไบนารี
        $binary_data = base64_decode($base64_data, true);
        if ($binary_data === false) {
            return ['valid' => false, 'error' => 'Invalid base64 encoding'];
        }
        
        // ตรวจสอบขนาดข้อมูล (ต้องมีขนาดอย่างน้อย 100 bytes สำหรับรูปภาพจริง)
        if (strlen($binary_data) < 100) {
            return ['valid' => false, 'error' => 'Image data too small'];
        }
        
        // ตรวจสอบ magic bytes ของไฟล์รูปภาพ
        $image_headers = [
            'jpeg' => [0xFF, 0xD8, 0xFF],           // JPEG
            'png'  => [0x89, 0x50, 0x4E, 0x47],    // PNG
            'gif'  => [0x47, 0x49, 0x46],          // GIF
            'bmp'  => [0x42, 0x4D],                // BMP
            'webp' => [0x52, 0x49, 0x46, 0x46]     // WebP (RIFF)
        ];
        
        $is_valid_image = false;
        $detected_type = 'unknown';
        
        foreach ($image_headers as $type => $header) {
            $match = true;
            for ($i = 0; $i < count($header); $i++) {
                if (!isset($binary_data[$i]) || ord($binary_data[$i]) !== $header[$i]) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $is_valid_image = true;
                $detected_type = $type;
                break;
            }
        }
        
        if (!$is_valid_image) {
            return ['valid' => false, 'error' => 'Not a valid image format'];
        }
        
        // ตรวจสอบเพิ่มเติมสำหรับ JPEG (ตรวจสอบแค่ header เพียงพอ)
        if ($detected_type === 'jpeg') {
            $length = strlen($binary_data);
            // ตรวจสอบแค่ว่าเป็น JPEG header ที่ถูกต้อง
            // ไม่ต้องตรวจสอบ end marker เพราะอาจมีปัญหากับ minimal JPEG
            if ($length < 10) {
                return ['valid' => false, 'error' => 'JPEG file too small'];
            }
        }
        
        return [
            'valid' => true, 
            'type' => $detected_type,
            'size' => strlen($binary_data),
            'error' => null
        ];
    }
}

// ประมวลผลรูปภาพ base64
$image_result = null;
$ai_analysis = null;
$text_critical = '';

// ตรวจสอบว่ามีรูปภาพส่งมาหรือไม่
if (!empty($image) && $image !== null && $image !== '') {
    // ตรวจสอบความถูกต้องของรูปภาพก่อน
    $validation = validateBase64Image($image);
    
    if (!$validation['valid']) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid image format',
            'error' => $validation['error'],
            'validation_details' => $validation,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        http_response_code(400);
        exit;
    }
    
    // บันทึกรูปภาพ
    $image_result = convertBase64ToImage($image, $device_id, [
        'prefix' => 'device',
        'max_width' => 800,
        'max_height' => 600,
        'quality' => 80,
        'format' => 'webp'
    ]);
    
    // ส่งรูปภาพไปให้ Gemini AI วิเคราะห์
    $gemini = new Gemini_api();
    $gemini_result = $gemini->analysis($image);

    if ($gemini_result['error']) {
        // ถ้า AI ผิดพลาด ให้ใช้ค่าเริ่มต้น
        $floot = 0;
        $landslide = 0;
        $text_critical = 'AI analysis failed: ' . $gemini_result['error'];
    } else {
        // ประมวลผลข้อมูลจาก AI
        $ai_analysis = $gemini_result['json'];
        
        if (is_array($ai_analysis)) {
            // แปลงค่าจาก AI เป็นตัวเลข 0-3
            $landslide = intval($ai_analysis['landslide'] ?? 0);
            $floot = intval($ai_analysis['flood'] ?? 0);
            $text_critical = $ai_analysis['text'] ?? '';
            
            // ตรวจสอบว่าค่าอยู่ในช่วง 0-3
            $landslide = max(0, min(3, $landslide));
            $floot = max(0, min(3, $floot));
            if ($landslide == 1 || $floot == 1) {
                $criticals = 1;
            }else if ($landslide == 2 || $floot == 2) {
                $criticals = 2;
            }else if ($landslide == 3 || $floot == 3) {
                $criticals = 3;
            }else{
                $criticals = 0;
            }
            
            $add_notification = $notificationTable->addNotification($device_id, $criticals, $text_critical);
            if (!$add_notification) {
                // Note: notification failure doesn't stop the main process
            }
        } else {
            // ถ้า AI ส่งข้อมูลผิดรูปแบบ
            $floot = 0;
            $landslide = 0;
            $text_critical = 'Invalid AI response format';
        }
    }
} else {
    // ไม่มีรูปภาพ - ไม่ส่งให้ AI และใช้ค่าเริ่มต้น
    $floot = 0;
    $landslide = 0;
    $text_critical = '';
    $image_result = null;
    $ai_analysis = null;
}

$image_path_to_insert = $image_result && $image_result['success'] ? $image_result['relative_path'] : '';

$result = $environment->insertenvironment(
    $device_id, 
    $temperature, 
    $humidity, 
    $rain, 
    $vibration, 
    $distance, 
    $soil, 
    $floot, 
    $landslide, 
    $soil_high,
    $image_path_to_insert,
    $text_critical
);

// ตรวจสอบผลลัพธ์และส่ง response
if ($result) {
    $response_data = [
        'success' => true,
        'message' => 'Environment data inserted successfully',
        'device_id' => $device_id,
        'data' => [
            'temperature' => $temperature,
            'humidity' => $humidity,
            'rain' => $rain,
            'vibration' => $vibration,
            'distance' => $distance,
            'soil' => $soil,
            'floot' => $floot,
            'landslide' => $landslide,
            'soil_high' => $soil_high
        ],
        'ai_analysis' => [
            'text_critical' => $text_critical,
            'floot_level' => $floot,
            'landslide_level' => $landslide,
            'analysis_status' => $ai_analysis ? 'success' : 'failed'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // เพิ่มข้อมูลรูปภาพถ้ามี
    if ($image_result && $image_result['success']) {
        $response_data['image'] = [
            'filename' => $image_result['filename'],
            'path' => $image_result['relative_path'],
            'status' => 'saved',
            'original_size' => $image_result['original_size'],
            'new_size' => $image_result['new_size'],
            'file_size' => $image_result['file_size']
        ];
    }
    
    echo json_encode($response_data);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to insert environment data - Database error',
        'device_id' => $device_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}