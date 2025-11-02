<?php
require_once __DIR__ . '/../../../auth/Auth.php';
require_once __DIR__ . '/../../../auth/EmailService.php';
require_once __DIR__ . '/../../../database/table_password_reset.php';
require_once __DIR__ . '/../../../database/table_user.php';

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

// Error codes constants
define('ERROR_VALIDATION_FAILED', 'VALIDATION_FAILED');
define('ERROR_RATE_LIMIT', 'RATE_LIMIT_EXCEEDED');
define('ERROR_DB_CONNECTION', 'DATABASE_CONNECTION_ERROR');
define('ERROR_TOKEN_CREATION', 'TOKEN_CREATION_FAILED');
define('ERROR_EMAIL_SEND', 'EMAIL_SEND_FAILED');
define('ERROR_SMTP_CONNECTION', 'SMTP_CONNECTION_ERROR');
define('ERROR_SMTP_AUTH', 'SMTP_AUTHENTICATION_ERROR');
define('ERROR_INTERNAL', 'INTERNAL_SERVER_ERROR');

try {
    // เปิด error reporting สำหรับ debug
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // ไม่แสดงใน output แต่ log
    
    // รับข้อมูลจาก request body
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    error_log("=== FORGOT PASSWORD API CALLED ===");
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'));
    error_log("Raw input length: " . strlen($rawInput));
    error_log("Raw input: " . $rawInput);
    error_log("JSON decode error: " . json_last_error_msg());
    error_log("Parsed input: " . print_r($input, true));
    
    // ตรวจสอบ JSON parsing error
    if (json_last_error() !== JSON_ERROR_NONE && !empty($rawInput)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON format',
            'error_code' => ERROR_VALIDATION_FAILED,
            'code' => 400
        ]);
        exit;
    }
    
    // รับข้อมูลจาก POST หรือ JSON
    $email = $input['email'] ?? $_POST['email'] ?? '';
    
    error_log("Email from input: " . ($input['email'] ?? 'null'));
    error_log("Email from POST: " . ($_POST['email'] ?? 'null'));
    error_log("Final email: " . $email);
    error_log("POST data: " . print_r($_POST, true));
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($email)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email is required',
            'error_code' => ERROR_VALIDATION_FAILED,
            'code' => 400
        ]);
        exit;
    }

    // ตรวจสอบรูปแบบอีเมล
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format',
            'error_code' => ERROR_VALIDATION_FAILED,
            'code' => 400
        ]);
        exit;
    }

    // สร้าง instances พร้อมการจัดการ error
    error_log("=== CREATING INSTANCES ===");
    
    try {
        $auth = new Auth();
        error_log("✓ Auth instance created");
    } catch (Exception $e) {
        error_log("✗ Auth creation failed: " . $e->getMessage());
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'Service temporarily unavailable',
            'error_code' => ERROR_DB_CONNECTION,
            'code' => 503
        ]);
        exit;
    }
    
    try {
        $passwordReset = new Table_password_reset();
        error_log("✓ Table_password_reset instance created");
    } catch (Exception $e) {
        error_log("✗ Table_password_reset creation failed: " . $e->getMessage());
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'Database service unavailable',
            'error_code' => ERROR_DB_CONNECTION,
            'code' => 503
        ]);
        exit;
    }
    
    try {
        $emailService = new EmailService();
        error_log("✓ EmailService instance created");
        
        // ทดสอบ method ที่จำเป็น
        if (method_exists($emailService, 'sendResetPasswordEmail')) {
            error_log("✓ sendResetPasswordEmail method exists");
        } else {
            error_log("✗ sendResetPasswordEmail method NOT exists");
            throw new Exception('Email service not properly configured');
        }
    } catch (Exception $e) {
        error_log("✗ EmailService creation failed: " . $e->getMessage());
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'Email service unavailable',
            'error_code' => ERROR_EMAIL_SEND,
            'code' => 503
        ]);
        exit;
    }
    
    // Debug log
    error_log("=== FORGOT PASSWORD DEBUG ===");
    error_log("Email requested: " . $email);

    // ตรวจสอบ rate limit
    try {
        if (!$passwordReset->checkRateLimit($email)) {
            $remaining = $passwordReset->getRemainingAttempts($email);
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => 'Too many reset requests. Please try again later.',
                'error_code' => ERROR_RATE_LIMIT,
                'remaining_attempts' => $remaining,
                'reset_after' => '12 hours',
                'code' => 429
            ]);
            exit;
        }
    } catch (Exception $e) {
        error_log("Rate limit check error: " . $e->getMessage());
        // ถ้าเช็ค rate limit ไม่ได้ ให้ดำเนินการต่อเพื่อความสะดวกของ user
    }

    // ตรวจสอบว่าอีเมลมีในระบบหรือไม่
    try {
        $userTable = new Table_get();
        $user = $userTable->Getlogin($email);
        
        error_log("User lookup result: " . ($user ? "USER FOUND" : "USER NOT FOUND"));
        if ($user) {
            error_log("Username: " . ($user['username'] ?? 'No username'));
        }
    } catch (Exception $e) {
        error_log("User lookup error: " . $e->getMessage());
        // ถ้าเช็ค user ไม่ได้ ให้ถือว่าไม่มี user
        $user = null;
    }
    
    // เพื่อความปลอดภัย เราจะส่ง response เดียวกันไม่ว่าอีเมลจะมีในระบบหรือไม่
    // เพื่อป้องกัน email enumeration attack
    
    $responseMessage = 'If an account with that email exists, we have sent a password reset link.';
    $internalError = null;
    
    if ($user) {
        error_log("=== PROCESSING PASSWORD RESET FOR EXISTING USER ===");
        
        // สร้าง reset token
        try {
            $resetToken = bin2hex(random_bytes(32)); // 64 characters
            error_log("Generated reset token: " . substr($resetToken, 0, 16) . "...");
        } catch (Exception $e) {
            error_log("Token generation error: " . $e->getMessage());
            $internalError = ERROR_TOKEN_CREATION;
        }
        
        if (!$internalError) {
            // บันทึก token ลงฐานข้อมูล (อายุ 20 นาที = 1200 วินาที)
            try {
                $tokenCreated = $passwordReset->createResetToken($email, $resetToken, 1200);
                error_log("Token creation result: " . ($tokenCreated ? "SUCCESS" : "FAILED"));
                
                if (!$tokenCreated) {
                    $internalError = ERROR_TOKEN_CREATION;
                    error_log("Failed to save reset token to database");
                }
            } catch (Exception $e) {
                error_log("Token storage error: " . $e->getMessage());
                $internalError = ERROR_DB_CONNECTION;
            }
        }
        
        if (!$internalError && $tokenCreated) {
            error_log("=== ATTEMPTING TO SEND EMAIL ===");
            error_log("Email Service Class exists: " . (class_exists('EmailService') ? "YES" : "NO"));
            error_log("Email Service instance: " . (is_object($emailService) ? "YES" : "NO"));
            error_log("Method exists: " . (method_exists($emailService, 'sendResetPasswordEmail') ? "YES" : "NO"));
            
            // พารามิเตอร์ที่จะส่งไป
            error_log("Parameters for sendResetPasswordEmail:");
            error_log("- Email: " . $email);
            error_log("- Token: " . substr($resetToken, 0, 16) . "...");
            error_log("- Username: " . ($user['username'] ?? 'empty'));
            
            // ส่งอีเมล
            error_log("*** CALLING sendResetPasswordEmail ***");
            
            try {
                $emailSent = $emailService->sendResetPasswordEmail($email, $resetToken, $user['username']);
                error_log("*** sendResetPasswordEmail RETURNED: " . ($emailSent ? "TRUE" : "FALSE") . " ***");
                
                if (!$emailSent) {
                    // ถ้าส่งอีเมลไม่สำเร็จ mark token ว่าล้มเหลว (ไม่ลบออกจาก database)
                    try {
                        $passwordReset->markTokenAsFailed($resetToken);
                        error_log("Marked token as failed due to email send failure");
                    } catch (Exception $e) {
                        error_log("Failed to mark token as failed: " . $e->getMessage());
                    }
                    
                    $internalError = ERROR_EMAIL_SEND;
                    error_log("Email service returned false for: " . $email);
                }
            } catch (Exception $emailException) {
                error_log("*** sendResetPasswordEmail EXCEPTION: " . $emailException->getMessage() . " ***");
                error_log("Exception trace: " . $emailException->getTraceAsString());
                
                // พยายามระบุประเภทของ error
                $errorMessage = strtolower($emailException->getMessage());
                if (strpos($errorMessage, 'smtp') !== false || strpos($errorMessage, 'connection') !== false) {
                    $internalError = ERROR_SMTP_CONNECTION;
                } elseif (strpos($errorMessage, 'auth') !== false || strpos($errorMessage, 'password') !== false) {
                    $internalError = ERROR_SMTP_AUTH;
                } else {
                    $internalError = ERROR_EMAIL_SEND;
                }
                
                // Mark token ว่าส่งอีเมลล้มเหลว (ไม่ลบออกจาก database)
                try {
                    $passwordReset->markTokenAsFailed($resetToken);
                    error_log("Marked token as failed due to exception");
                } catch (Exception $e) {
                    error_log("Failed to mark token as failed: " . $e->getMessage());
                }
                
                $emailSent = false;
            }
            
            error_log("Email send result: " . ($emailSent ? "SUCCESS" : "FAILED"));
            
            if ($emailSent) {
                error_log("Reset email sent successfully to: " . $email);
            }
        }
    } else {
        error_log("=== EMAIL NOT IN SYSTEM - SKIPPING EMAIL SEND ===");
    }

    // ส่ง response
    error_log("=== SENDING RESPONSE ===");
    
    // ถ้ามี internal error จริงๆ และเป็น critical error
    if ($internalError && in_array($internalError, [ERROR_DB_CONNECTION, ERROR_INTERNAL])) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'message' => 'Service temporarily unavailable. Please try again later.',
            'error_code' => $internalError,
            'code' => 503
        ]);
    } else {
        // ส่ง response ปกติเพื่อความปลอดภัย (ไม่เปิดเผยว่าอีเมลมีในระบบหรือไม่)
        http_response_code(200);
        $response = [
            'success' => true,
            'message' => $responseMessage,
            'data' => [
                'email' => $email,
                'expires_in' => '20 minutes',
                'remaining_attempts' => $passwordReset->getRemainingAttempts($email) - 1
            ],
            'code' => 200
        ];
        
        // เพิ่ม debug info ถ้าอยู่ใน development mode
        if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
            $response['debug'] = [
                'user_found' => $user !== null,
                'email_sent' => isset($emailSent) ? $emailSent : null,
                'internal_error' => $internalError
            ];
        }
        
        echo json_encode($response);
    }
    
    error_log("Response sent successfully");

} catch (Exception $e) {
    error_log("Forgot password error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error_code' => ERROR_INTERNAL,
        'code' => 500
    ]);
}
