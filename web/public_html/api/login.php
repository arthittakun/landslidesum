<?php 

require_once __DIR__ . '../../../database/toble_login.php';
$login = new Table_get();

header('Content-Type: application/json');

$recaptcha_token = $_POST['recaptcha_token'] ?? '';
$recaptcha_secret = '6LemoWIrAAAAADISjgTbnylmkTf5KSSnyP2HLRlO';

if (empty($recaptcha_token)) {
    http_response_code(400);
    echo json_encode(['error' => 'reCAPTCHA token is required']);
    exit;
}

$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = [
    'secret' => $recaptcha_secret,
    'response' => $recaptcha_token
];

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$recaptcha_response = json_decode($result, true);

if (!$recaptcha_response['success'] || $recaptcha_response['score'] < 0.5) {
    http_response_code(400);
    echo json_encode(['error' => 'reCAPTCHA verification failed']);
    exit;
}

$identifier = $_POST['identifier'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($identifier) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'กรอกข้อมูลให้ครบถ้วน']);
    exit;
}

$user = $login->Getlogin($identifier);

if ($user) {
    if (md5($password) !== $user['password']) {
        http_response_code(401);
        echo json_encode(['error' => 'รหัสผ่านไม่ถูกต้อง']);
        exit;
    }
    if (md5($password) === $user['password']) {
        // Log the user in
        session_start();
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        if (isset($user['role'])) {
            $_SESSION['role'] = (int)$user['role'];
        }
        echo json_encode(['success' => true, 'message' => 'เข้าสู่ระบบสำเร็จ', 'user' => $user]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'รหัสผ่านไม่ถูกต้อง']);
        exit;
    }
   
} else {
    // Invalid credentials
    http_response_code(401);
    echo json_encode(['error' => 'ไม่พบข้อมูลผู้ใช้งาน', 'message' => $user]);
    exit;
}
