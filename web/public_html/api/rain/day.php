<?php
// rain.php (Standalone)

// ====== ปรับค่าที่นี่ถ้าจำเป็น ======
$DWR_BASE = 'https://ews.dwr.go.th/website/webservice/rain_daily.php';
$DWR_UID  = 'arthittakun123';
$DWR_PASS = 'Arthit0987944735';
$DEFAULT_DMODE = 1;  // ล่าสุดวันนี้
$DEFAULT_DTYPE = 2;  // JSON
$HTTP_TIMEOUT  = 30; // วินาที

// CORS (โปรดเปลี่ยน * เป็นโดเมนจริงในโปรดักชัน)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, X-Client-Domain');
header('Access-Control-Allow-Methods: GET, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

header('Content-Type: application/json; charset=utf-8');

// ====== Helpers ======
function respond($code, $payload) {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ====== รับพารามิเตอร์จาก client ======
$dmode    = isset($_GET['dmode']) ? (int)$_GET['dmode'] : $DEFAULT_DMODE;
$ondate   = isset($_GET['ondate']) ? trim((string)$_GET['ondate']) : null; // YYYY-MM-DD
$province = isset($_GET['province']) ? trim((string)$_GET['province']) : null;
$top      = isset($_GET['top']) ? (int)$_GET['top'] : null;
$min12h   = isset($_GET['min12h']) ? (float)$_GET['min12h'] : null;
$sort     = isset($_GET['sort']) ? strtolower((string)$_GET['sort']) : 'rain12h_desc'; // rain12h_desc|rain12h_asc|order

// ====== ยิงไป DWR ======
$params = [
    'uid'   => $DWR_UID,
    'upass' => $DWR_PASS,
    'dmode' => $dmode,
    'dtype' => $DEFAULT_DTYPE,
];
if ($ondate) { $params['ondate'] = $ondate; }

$url = $DWR_BASE . '?' . http_build_query($params);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => $HTTP_TIMEOUT,
    CURLOPT_TIMEOUT => $HTTP_TIMEOUT,
    CURLOPT_HTTPGET => true,
    CURLOPT_USERAGENT => 'RainProxy/1.0 (+standalone)',
    CURLOPT_ENCODING => '',
    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
]);
$response = curl_exec($ch);
$errno = curl_errno($ch);
$err   = curl_error($ch);
$code  = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

if ($errno !== 0 || $response === false) {
    respond(502, ['ok' => false, 'error' => "Upstream error: {$err} (HTTP {$code})"]);
}

$dwr = json_decode($response, true);
if (!is_array($dwr) || !isset($dwr['station']) || !is_array($dwr['station'])) {
    respond(500, ['ok' => false, 'error' => 'Unexpected upstream shape']);
}

$stations = $dwr['station'];

// ====== กรอง ======
if ($province) {
    $needle = mb_strtolower($province, 'UTF-8');
    $stations = array_values(array_filter($stations, function ($s) use ($needle) {
        $p = isset($s['province']) ? mb_strtolower((string)$s['province'], 'UTF-8') : '';
        return $needle === '' ? true : (mb_strpos($p, $needle) !== false);
    }));
}
if ($min12h !== null) {
    $stations = array_values(array_filter($stations, function ($s) use ($min12h) {
        $v = isset($s['rain12h']) ? (float)$s['rain12h'] : 0.0;
        return $v >= $min12h;
    }));
}

// ====== เรียง ======
if ($sort === 'rain12h_asc') {
    usort($stations, fn($a, $b) => (float)$a['rain12h'] <=> (float)$b['rain12h']);
} elseif ($sort === 'rain12h_desc') {
    usort($stations, fn($a, $b) => (float)$b['rain12h'] <=> (float)$a['rain12h']);
} else { // order
    usort($stations, fn($a, $b) => (int)$a['order'] <=> (int)$b['order']);
}

// ====== Top N ======
if ($top !== null && $top > 0) {
    $stations = array_slice($stations, 0, $top);
}

// ====== ตอบกลับ ======
respond(200, [
    'ok'        => true,
    'source'    => 'DWR',
    'title'     => $dwr['title'] ?? null,
    'date_text' => $dwr['date']  ?? null,
    'count'     => count($stations),
    'filters'   => [
        'province' => $province,
        'top'      => $top,
        'min12h'   => $min12h,
        'sort'     => $sort,
        'dmode'    => $dmode,
        'ondate'   => $ondate,
    ],
    'stations'  => $stations,
]);
