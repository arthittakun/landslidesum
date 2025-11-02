<?php
require_once __DIR__ . '/../../../auth/Auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// เฉพาะ POST method เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'code' => 405
    ]);
    exit;
}

try {
    // รับข้อมูลจาก request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    // รับข้อมูลจาก POST หรือ JSON
    $identifier = $input['identifier'] ?? $_POST['identifier'] ?? '';
    $password = $input['password'] ?? $_POST['password'] ?? '';
    $recaptcha_token = $input['recaptcha_token'] ?? $_POST['recaptcha_token'] ?? '';

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($identifier) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields',
            'code' => 400
        ]);
        exit;
    }

    // ตรวจสอบ reCAPTCHA (ถ้ามี)
    if (!empty($recaptcha_token)) {
        $recaptcha_secret = '6LemoWIrAAAAADISjgTbnylmkTf5KSSnyP2HLRlO';
        
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
            echo json_encode([
                'success' => false,
                'message' => 'reCAPTCHA verification failed',
                'code' => 400
            ]);
            exit;
        }
    }

    // สร้าง Auth instance และทำการเข้าสู่ระบบ
    $auth = new Auth();
    $result = $auth->login($identifier, $password);

    // sync session for role-based menus if login succeeded
    if ($result['success'] && isset($result['data']['user'])) {
        session_start();
        $_SESSION['username'] = $result['data']['user']['username'] ?? '';
        $_SESSION['email'] = $result['data']['user']['email'] ?? '';
        // Try to fetch role from DB to set session role if available
        try {
            require_once __DIR__ . '/../../../database/table_user.php';
            $t = new Table_user();
            $u = null;
            if (!empty($_SESSION['username'])) { $u = $t->getUserByUsername($_SESSION['username']); }
            if (!$u && !empty($_SESSION['email'])) { $u = $t->getUserByEmail($_SESSION['email']); }
            if ($u && isset($u['role'])) { $_SESSION['role'] = (int)$u['role']; }
        } catch (Throwable $e) { /* ignore */ }
    }

    // ส่งผลลัพธ์
    http_response_code($result['code']);
    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'code' => 500
    ]);
}
