<?php
require_once __DIR__ . '/../../../service/Gemini_api.php';
require_once __DIR__ . '/../../../database/table_environment.php';
date_default_timezone_set('Asia/Bangkok');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Method not allowed"
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['image'])) {
    echo json_encode([
        "status" => "error",
        "message" => "No image data received"
    ]);
    exit;
}

// รับค่าที่จำเป็นสำหรับ createEnvironmentData
$device_id = $data['device_id'] ?? null;
$temp = $data['temp'] ?? null;
$humid = $data['humid'] ?? null;
$rain = $data['rain'] ?? null;
$vibration = $data['vibration'] ?? null;
$distance = $data['distance'] ?? null;
$soil = $data['soil'] ?? null;

// ใช้เวลาปัจจุบัน
$datekey = date('Y-m-d');
$timekey = date('H:i');
// docno อัตโนมัติ (timestamp+rand)
$docno = date('YmdHis') . rand(100, 999);

$image = $data['image'];

$gemini = new Gemini_api();
$result = $gemini->analysis($image);

if ($result['error']) {
    echo json_encode([
        "status" => "error",
        "message" => "cURL Error: " . $result['error']
    ]);
    exit;
}

if ($result['http_code'] == 200 && $result['json']) {
    $ai = $result['json'];
    // ตรวจสอบว่า AI ตอบกลับเป็น array และมี status = "True"
    if (is_array($ai) && isset($ai['status']) && $ai['status'] === "True") {
        // แปลงค่าจาก AI เป็น int สำหรับฐานข้อมูล
        $landslide = (isset($ai['landslide']) && $ai['landslide'] === "True") ? 1 : 0;
        $floot = (isset($ai['Floot']) && $ai['Floot'] === "True") ? 1 : 0;
        $text_critical = $ai['Text'] ?? '';

        if ($device_id) {
            $env = new Table_environment();
            $inserted = $env->createEnvironmentData(
                $device_id,
                $timekey,
                $temp,
                $humid,
                $rain,
                $vibration,
                $distance,
                $datekey,
                $soil,
                $docno,
                $landslide,
                $floot,
                $text_critical
            );
            if ($inserted === false) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to insert data to database"
                ]);
                exit;
            }
        }
    }
   
    // ส่งผลลัพธ์ AI กลับไป
    if (is_array($ai)) {
        echo json_encode($ai);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "AI response is not valid JSON",
            "raw" => $ai
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "API request failed with HTTP code: " . $result['http_code'],
        "response" => $result['response']
    ]);
}
