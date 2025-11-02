<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$url = 'https://api.rainviewer.com/public/weather-maps.json';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 200 && $response) {
    $data = json_decode($response, true);

    // เตรียมข้อมูลที่ใช้
    $host = $data['host'];
    $frames = $data['radar']['past'];

    // รวมรายการ future frames เพิ่มความรู้สึกเรียลไทม์
    if (!empty($data['radar']['nowcast'])) {
        $frames = array_merge($frames, $data['radar']['nowcast']);
    }

    echo json_encode([
        'host' => $host,
        'frames' => $frames,
    ]);
} else {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to fetch radar data',
        'error' => $error,
        'http_code' => $httpCode
    ]);
}
