<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lon = isset($_GET['lon']) ? floatval($_GET['lon']) : null;

if ($lat === null || $lon === null) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing lat/lon parameters.'
    ]);
    exit;
}

// *** เอา humidity_2m ออก ***
$hourly = "temperature_2m,precipitation,windspeed_10m,weathercode,pressure_msl,cloudcover";
$daily  = "temperature_2m_min,temperature_2m_max,precipitation_sum,sunrise,sunset,weathercode";
$url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}" .
       "&hourly={$hourly}" .
       "&daily={$daily}" .
       "&current_weather=true" .
       "&timezone=Asia%2FBangkok";

// echo $url; // DEBUG URL ที่สร้างได้

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode === 200 && $response) {
    echo $response;
} else {
    http_response_code(502);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to fetch weather data',
        'error' => $error,
        'http_code' => $httpCode,
        'response' => $response,
        'url' => $url
    ]);
}
