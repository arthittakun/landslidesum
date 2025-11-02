<?php
require_once __DIR__ . '/JWT.php';
require_once __DIR__ . '/../database/toble_login.php';

class Auth {
    private $jwt;
    private $userTable;

    public function __construct() {
        $this->jwt = new JWT();
        $this->userTable = new Table_get();
    }

    /**
     * เข้าสู่ระบบและสร้าง JWT token
     * @param string $identifier username หรือ email
     * @param string $password รหัสผ่าน
     * @return array ผลลัพธ์การเข้าสู่ระบบ
     */
    public function login($identifier, $password) {
        try {
            // ตรวจสอบข้อมูลผู้ใช้
            $user = $this->userTable->Getlogin($identifier);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'code' => 404
                ];
            }

            // ตรวจสอบรหัสผ่าน
            if (md5($password) !== $user['password']) {
                return [
                    'success' => false,
                    'message' => 'Invalid password',
                    'code' => 401
                ];
            }

            // สร้าง payload สำหรับ JWT
            $payload = [
                'username' => $user['username'],
                'email' => $user['email'],
                'login_time' => time()
            ];

            // สร้าง access token (1 ชั่วโมง)
            $accessToken = $this->jwt->encode($payload, 3600);
            
            // สร้าง refresh token (7 วัน)
            $refreshToken = $this->jwt->generateRefreshToken($payload);

            return [
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'username' => $user['username'],
                        'email' => $user['email']
                    ],
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_in' => 3600 // 1 ชั่วโมง
                ],
                'code' => 200
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * ลงทะเบียนผู้ใช้ใหม่
     * @param string $username ชื่อผู้ใช้
     * @param string $email อีเมล
     * @param string $password รหัสผ่าน
     * @param string $confirmPassword ยืนยันรหัสผ่าน
     * @return array ผลลัพธ์การลงทะเบียน
     */
    public function register($username, $email, $password, $confirmPassword) {
        try {
            // ตรวจสอบข้อมูลที่จำเป็น
            if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                return [
                    'success' => false,
                    'message' => 'All fields are required',
                    'code' => 400
                ];
            }

            // ตรวจสอบรูปแบบอีเมล
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid email format',
                    'code' => 400
                ];
            }

            // ตรวจสอบรหัสผ่านที่ตรงกัน
            if ($password !== $confirmPassword) {
                return [
                    'success' => false,
                    'message' => 'Passwords do not match',
                    'code' => 400
                ];
            }

            // ตรวจสอบความแข็งแรงของรหัสผ่าน
            if (strlen($password) < 8) {
                return [
                    'success' => false,
                    'message' => 'Password must be at least 8 characters long',
                    'code' => 400
                ];
            }

            // ตรวจสอบว่าผู้ใช้มีอยู่แล้วหรือไม่
            $userExists = $this->userTable->checkUserExists($username, $email);
            if ($userExists) {
                return [
                    'success' => false,
                    'message' => 'Username or email already exists',
                    'code' => 409
                ];
            }

            // สร้างรหัสผ่านที่เข้ารหัส
            $hashedPassword = md5($password);

            // ลงทะเบียนผู้ใช้
            $result = $this->userTable->registerUser($username, $email, $hashedPassword);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Registration successful',
                    'data' => [
                        'username' => $username,
                        'email' => $email
                    ],
                    'code' => 201
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Registration failed. Please try again',
                    'code' => 500
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * ตรวจสอบ JWT token
     * @param string $token JWT token
     * @return array ผลลัพธ์การตรวจสอบ
     */
    public function verifyToken($token) {
        try {
            $payload = $this->jwt->decode($token);
            
            if (!$payload) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired token',
                    'code' => 401
                ];
            }

            return [
                'success' => true,
                'message' => 'Token is valid',
                'data' => $payload,
                'code' => 200
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Token verification failed',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * รีเฟรช access token ใหม่
     * @param string $refreshToken refresh token
     * @return array ผลลัพธ์การรีเฟรช
     */
    public function refreshToken($refreshToken) {
        try {
            $payload = $this->jwt->decode($refreshToken);
            
            if (!$payload) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired refresh token',
                    'code' => 401
                ];
            }

            // สร้าง access token ใหม่
            $newPayload = [
                'username' => $payload['username'],
                'email' => $payload['email'],
                'login_time' => time()
            ];

            $newAccessToken = $this->jwt->encode($newPayload, 3600);

            return [
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'access_token' => $newAccessToken,
                    'expires_in' => 3600
                ],
                'code' => 200
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * ดึง token จาก Authorization header
     * @return string|null JWT token หรือ null ถ้าไม่พบ
     */
    public function getTokenFromHeader() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            return str_replace('Bearer ', '', $headers['Authorization']);
        } elseif (isset($headers['authorization'])) {
            return str_replace('Bearer ', '', $headers['authorization']);
        }
        
        return null;
    }

    /**
     * Middleware สำหรับตรวจสอบ authentication
     * @return array|null ข้อมูลผู้ใช้หรือ null ถ้าไม่ผ่าน
     */
    public function requireAuth() {
        $token = $this->getTokenFromHeader();
        
        if (!$token) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Access token not found',
                'code' => 401
            ]);
            exit;
        }

        $result = $this->verifyToken($token);
        
        if (!$result['success']) {
            http_response_code(401);
            echo json_encode($result);
            exit;
        }

        return $result['data'];
    }
}
