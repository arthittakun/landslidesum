<?php
require_once __DIR__ . '/../../../database/table_password_reset.php';
require_once __DIR__ . '/../../../database/connect.php';

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
    $token = $input['token'] ?? $_POST['token'] ?? '';
    $newPassword = $input['new_password'] ?? $_POST['new_password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? $_POST['confirm_password'] ?? '';
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required',
            'code' => 400
        ]);
        exit;
    }

    // ตรวจสอบรหัสผ่านที่ตรงกัน
    if ($newPassword !== $confirmPassword) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Passwords do not match',
            'code' => 400
        ]);
        exit;
    }

    // ตรวจสอบความแข็งแรงของรหัสผ่าน
    if (strlen($newPassword) < 8) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters long',
            'code' => 400
        ]);
        exit;
    }

    // สร้าง instances
    $passwordReset = new Table_password_reset();

    // ตรวจสอบ reset token
    $resetData = $passwordReset->verifyResetToken($token);
    
    if (!$resetData) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired reset token',
            'code' => 400
        ]);
        exit;
    }

    // อัปเดตรหัสผ่านใหม่
    $hashedPassword = md5($newPassword);
    
    try {
        $db = new database();
        $sql = "UPDATE users SET password = :password WHERE email = :email";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $resetData['email']);
        
        $passwordUpdated = $stmt->execute();
        
        if ($passwordUpdated) {
            // mark token as used
            $passwordReset->useResetToken($token);
            
            // ไม่ต้องลบ token - เก็บไว้เป็นประวัติ
            // $passwordReset->deleteExpiredTokens($resetData['email']);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Password reset successful',
                'data' => [
                    'email' => $resetData['email'],
                    'reset_at' => date('Y-m-d H:i:s')
                ],
                'code' => 200
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update password',
                'code' => 500
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Password update error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred',
            'code' => 500
        ]);
    }

} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'code' => 500
    ]);
}
