<?php
require_once __DIR__ . '/../database/toble_login.php';

class EmailService {
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $fromEmail;
    private $fromName;
    private $userTable;

    public function __construct() {
        // SMTP Configuration - กรุณาใส่ข้อมูล SMTP ของคุณ
        $this->smtpHost = 'da94.hostneverdie.com'; // เช่น smtp.gmail.com
        $this->smtpPort = 587; // เช่น 587 สำหรับ STARTTLS หรือ 465 สำหรับ SSL
        $this->smtpUsername = 'landslid@landslide-alerts.com'; // เช่น your-email@domain.com
        $this->smtpPassword = '#MnULgh9066f#N'; // รหัสผ่าน SMTP หรือ App Password
        $this->fromEmail = 'landslid@landslide-alerts.com'; // อีเมลผู้ส่ง เช่น noreply@yourdomain.com
        $this->fromName = 'Landslide Alert Platform'; // ชื่อระบบ
        
        // ตรวจสอบ SMTP configuration
        $this->validateSMTPConfig();
        
        // Initialize user table for database access
        try {
            $this->userTable = new Table_get();
        } catch (Exception $e) {
            error_log("EmailService: Failed to initialize user table: " . $e->getMessage());
        }
    }

    /**
     * ตรวจสอบ SMTP configuration
     */
    private function validateSMTPConfig() {
        error_log("=== SMTP CONFIGURATION VALIDATION ===");
        error_log("SMTP Host: " . ($this->smtpHost ?: 'NOT SET'));
        error_log("SMTP Port: " . ($this->smtpPort ?: 'NOT SET'));
        error_log("SMTP Username: " . ($this->smtpUsername ?: 'NOT SET'));
        error_log("SMTP Password: " . ($this->smtpPassword ? 'SET' : 'NOT SET'));
        error_log("From Email: " . ($this->fromEmail ?: 'NOT SET'));
        error_log("From Name: " . ($this->fromName ?: 'NOT SET'));
        
        if (empty($this->smtpHost) || empty($this->smtpPort) || 
            empty($this->smtpUsername) || empty($this->smtpPassword)) {
            error_log("WARNING: SMTP configuration incomplete. Email sending may fail.");
        } else {
            error_log("✓ SMTP configuration appears complete");
        }
    }

    /**
     * ส่งอีเมล reset password ไปยังอีเมลที่ผู้ใช้กรอกมา
     * @param string $toEmail อีเมลผู้รับที่ผู้ใช้กรอกในฟอร์ม forgot password
     * @param string $resetToken โทเค็นสำหรับ reset password
     * @param string $userName ชื่อผู้ใช้ (optional)
     * @return bool ผลลัพธ์การส่งอีเมล
     */
    public function sendResetPasswordEmail($toEmail, $resetToken, $userName = '') {
        // ใช้อีเมลที่ผู้ใช้กรอกมาเป็นหลัก
        // ดึงข้อมูลผู้ใช้จากฐานข้อมูลเฉพาะเมื่อต้องการชื่อผู้ใช้
        if (empty($userName)) {
            $userData = $this->getUserDataByEmail($toEmail);
            $userName = $userData['username'] ?? '';
        }

        $resetUrl = $this->getBaseUrl() . "/auth/reset-password?token=" . urlencode($resetToken);
        
        $subject = "Reset Your Password - Landslide Alert System";
        $htmlBody = $this->getResetPasswordTemplate($resetUrl, $userName, $toEmail);
        $textBody = $this->getResetPasswordTextTemplate($resetUrl, $userName);
        
        // ใช้ SMTP การส่งอีเมลแทน PHP mail()
        $result = $this->sendEmailWithSMTP($toEmail, $subject, $htmlBody, $textBody);
        
        return $result;
    }

    /**
     * ดึงข้อมูลผู้ใช้จากฐานข้อมูลโดยใช้อีเมล
     * @param string $email อีเมลผู้ใช้
     * @return array|null ข้อมูลผู้ใช้
     */
    private function getUserDataByEmail($email) {
        try {
            return $this->userTable->Getlogin($email);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * ส่งอีเมลทั่วไป
     */
    private function sendEmail($to, $subject, $htmlBody, $textBody = '') {
        // Headers สำหรับ HTML email
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
            'Reply-To: ' . $this->fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];

        try {
            // ใช้ PHP mail() function (สำหรับ basic SMTP)
            // หากต้องการ SMTP authentication ขั้นสูง ให้ใช้ PHPMailer
            $result = mail($to, $subject, $htmlBody, implode("\r\n", $headers));
            
            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * ส่งอีเมลด้วย SMTP แบบ Pure PHP (ไม่ใช้ PHPMailer)
     */
    public function sendEmailWithSMTP($to, $subject, $htmlBody, $textBody = '') {
        try {
            // ตรวจสอบ SMTP configuration
            if (empty($this->smtpHost) || empty($this->smtpPort) || 
                empty($this->smtpUsername) || empty($this->smtpPassword)) {
                error_log("SMTP configuration incomplete. Using fallback method.");
                return $this->sendEmail($to, $subject, $htmlBody, $textBody);
            }
            
            // สร้าง connection ไปยัง SMTP server
            // เพิ่ม context options สำหรับ SSL/TLS
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);
            
            $socket = @stream_socket_client(
                "tcp://{$this->smtpHost}:{$this->smtpPort}",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if (!$socket) {
                error_log("SMTP Connection Error: $errno - $errstr");
                error_log("Falling back to basic mail() function");
                return $this->sendEmail($to, $subject, $htmlBody, $textBody);
            }
            
            // Helper function to read response
            $readResponse = function() use ($socket) {
                $response = '';
                while (($line = fgets($socket, 515)) !== false) {
                    $response .= $line;
                    if (substr($line, 3, 1) === ' ') break; // End of multi-line response
                }
                return trim($response);
            };
            
            // Helper function to check SMTP response
            $checkResponse = function($response, $expectedCode, $errorMessage) {
                $code = substr($response, 0, 3);
                if ($code != $expectedCode) {
                    error_log("SMTP Error: Expected $expectedCode, got $code. Response: $response");
                    throw new Exception("$errorMessage (Code: $code)", intval($code));
                }
                return true;
            };
            
            // อ่าน response แรก
            $response = $readResponse();
            $checkResponse($response, '220', 'SMTP server not ready');
            
            // ส่ง EHLO command
            fwrite($socket, "EHLO {$this->smtpHost}\r\n");
            $response = $readResponse();
            
            // ตรวจสอบว่าต้องการ STARTTLS หรือไม่
            if (strpos($response, 'STARTTLS') !== false) {
                // เริ่ม STARTTLS
                fwrite($socket, "STARTTLS\r\n");
                $response = $readResponse();
                $checkResponse($response, '220', 'STARTTLS not available');
                
                // อัปเกรดเป็น TLS connection
                if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($socket);
                    throw new Exception('Failed to enable TLS encryption');
                }
                
                // ส่ง EHLO อีกครั้งหลัง TLS
                fwrite($socket, "EHLO {$this->smtpHost}\r\n");
                $response = $readResponse();
            }
            
            // Authentication
            fwrite($socket, "AUTH LOGIN\r\n");
            $response = $readResponse();
            $checkResponse($response, '334', 'AUTH LOGIN not supported');
            
            // ส่ง username (base64 encoded)
            fwrite($socket, base64_encode($this->smtpUsername) . "\r\n");
            $response = $readResponse();
            $checkResponse($response, '334', 'Username not accepted');
            
            // ส่ง password (base64 encoded)
            fwrite($socket, base64_encode($this->smtpPassword) . "\r\n");
            $response = $readResponse();
            if (substr($response, 0, 3) != '235') {
                error_log("SMTP Authentication failed. Falling back to basic mail() function");
                fclose($socket);
                return $this->sendEmail($to, $subject, $htmlBody, $textBody);
            }
            
            // ส่ง MAIL FROM
            fwrite($socket, "MAIL FROM: <{$this->fromEmail}>\r\n");
            $response = $readResponse();
            $checkResponse($response, '250', 'MAIL FROM rejected');
            
            // ส่ง RCPT TO
            fwrite($socket, "RCPT TO: <$to>\r\n");
            $response = $readResponse();
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                throw new Exception("Recipient address rejected: $to", intval(substr($response, 0, 3)));
            }
            
            // เริ่ม DATA
            fwrite($socket, "DATA\r\n");
            $response = $readResponse();
            $checkResponse($response, '354', 'DATA command failed');
            
            // สร้าง email headers
            $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            $headers .= "Message-ID: <" . uniqid() . "@{$this->smtpHost}>\r\n";
            $headers .= "X-Mailer: Landslide Alert System\r\n";
            $headers .= "\r\n";
            
            // ส่ง headers และ body
            $emailContent = $headers . $htmlBody;
            fwrite($socket, $emailContent . "\r\n.\r\n");
            $response = $readResponse();
            $checkResponse($response, '250', 'Message not accepted for delivery');
            
            // ปิด connection
            fwrite($socket, "QUIT\r\n");
            fclose($socket);
            
            error_log("Email sent successfully to: $to");
            return true;
            
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
            
            // ปิด socket ถ้ายังเปิดอยู่
            if (isset($socket) && is_resource($socket)) {
                fclose($socket);
            }
            
            // ลองใช้ fallback method
            error_log("Attempting to use fallback mail() function");
            try {
                $fallbackResult = $this->sendEmail($to, $subject, $htmlBody, $textBody);
                if ($fallbackResult) {
                    error_log("Fallback mail() function succeeded");
                    return true;
                } else {
                    error_log("Fallback mail() function also failed");
                    throw new Exception("Both SMTP and fallback methods failed: " . $e->getMessage(), $e->getCode());
                }
            } catch (Exception $fallbackException) {
                error_log("Fallback method error: " . $fallbackException->getMessage());
                throw new Exception("Email send failed: " . $e->getMessage(), $e->getCode());
            }
        }
    }

    /**
     * HTML Template สำหรับ Reset Password - ใช้ AppTheme สีเขียว
     */
    private function getResetPasswordTemplate($resetUrl, $userName, $userEmail = '') {
        $greeting = $userName ? "Hello " . htmlspecialchars($userName) : "Hello";
        if ($userEmail && !$userName) {
            $greeting = "Hello " . htmlspecialchars($userEmail);
        }
        
        // Base64 encoded logo image (green mountain icon)
        $logoBase64 = 'data:image/png;base64,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';
        
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - Landslide Alert System</title>
    <!--[if mso]>
    <style>
        table { border-collapse: collapse; }
        .container { width: 600px !important; }
    </style>
    <![endif]-->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #E8F5E8;
            line-height: 1.6;
            color: #333;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .email-wrapper {
            width: 100%;
            background-color: #E8F5E8;
            padding: 20px 0;
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(76, 175, 80, 0.15);
            border: 1px solid #A5D6A7;
        }
        
        .content {
            padding: 50px 40px;
            background: linear-gradient(to bottom, #ffffff 0%, #F1F8E9 100%);
        }
        
        .greeting {
            font-size: 24px;
            color: #4CAF50;
            margin-bottom: 24px;
            font-weight: 600;
            text-align: center;
        }
        
        .message {
            color: #757575;
            margin-bottom: 32px;
            font-size: 17px;
            text-align: center;
            line-height: 1.7;
        }
        
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
            color: white;
            padding: 18px 50px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(76, 175, 80, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            cursor: pointer;
        }
        
        .reset-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(76, 175, 80, 0.4);
            background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
        }
        
        .info-card {
            background: linear-gradient(135deg, #F1F8E9 0%, #E8F5E8 100%);
            border-left: 5px solid #66BB6A;
            padding: 24px;
            margin: 32px 0;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(76, 175, 80, 0.1);
        }
        
        .info-card h3 {
            color: #4CAF50;
            margin-bottom: 16px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .info-card h3::before {
            content: "🔒";
            margin-right: 8px;
            font-size: 20px;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .info-list li {
            color: #757575;
            margin-bottom: 8px;
            padding-left: 24px;
            position: relative;
            font-size: 15px;
        }
        
        .info-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #66BB6A;
            font-weight: bold;
        }
        
        .warning-card {
            background: linear-gradient(135deg, #FFF3E0 0%, #FFECB3 100%);
            border-left: 5px solid #FF9800;
            color: #E65100;
            padding: 24px;
            margin: 32px 0;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(255, 152, 0, 0.1);
        }
        
        .warning-card h3 {
            margin-bottom: 12px;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .warning-card h3::before {
            content: "⚠️";
            margin-right: 8px;
        }
        
        .warning-card a {
            color: #F57C00;
            text-decoration: underline;
            word-break: break-all;
        }
        
        .divider {
            height: 2px;
            background: linear-gradient(to right, transparent 0%, #A5D6A7 50%, transparent 100%);
            margin: 40px 0;
        }
        
        .final-message {
            text-align: center;
            color: #757575;
            font-size: 16px;
            font-style: italic;
            margin-top: 32px;
        }
        
        .footer {
            background: linear-gradient(135deg, #F1F8E9 0%, #E8F5E8 100%);
            padding: 32px 40px;
            text-align: center;
            border-top: 1px solid #A5D6A7;
        }
        
        .footer p {
            color: #757575;
            font-size: 14px;
            margin: 0;
            line-height: 1.6;
        }
        
        .footer a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .footer .copyright {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #C8E6C9;
            font-size: 13px;
            color: #9E9E9E;
        }
        
        /* Responsive Design */
        @media screen and (max-width: 640px) {
            .email-wrapper {
                padding: 10px;
            }
            
            .container {
                margin: 0 10px;
                border-radius: 12px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .greeting {
                font-size: 20px;
            }
            
            .message {
                font-size: 16px;
            }
            
            .reset-button {
                padding: 16px 40px;
                font-size: 16px;
                width: 100%;
                max-width: 280px;
            }
            
            .info-card, .warning-card {
                padding: 20px;
                margin: 24px 0;
            }
            
            .footer {
                padding: 24px 20px;
            }
        }
        
        @media screen and (max-width: 480px) {
            .header h1 {
                font-size: 22px;
            }
            
            .greeting {
                font-size: 18px;
            }
            
            .reset-button {
                padding: 14px 30px;
                font-size: 15px;
            }
            
            .info-card, .warning-card {
                padding: 16px;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .container {
                background-color: #1e1e1e;
                border-color: #4CAF50;
            }
            
            .content {
                background: linear-gradient(to bottom, #1e1e1e 0%, #0d1f0d 100%);
            }
            
            .greeting {
                color: #66BB6A;
            }
            
            .message, .final-message {
                color: #cccccc;
            }
            
            .info-list li {
                color: #cccccc;
            }
        }
        
        /* Print styles */
        @media print {
            .email-wrapper {
                background-color: white;
            }
            
            .container {
                box-shadow: none;
                border: 1px solid #ccc;
            }
            
            .reset-button {
                background: #4CAF50 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="container">
            <div class="content">
                <div class="greeting">
                    ' . $greeting . '! 👋
                </div>
                
                <div class="message">
                    เราได้รับคำขอรีเซ็ตรหัสผ่านสำหรับบัญชี Landslide Alert Platform ของคุณ 
                    เพื่อความปลอดภัยของบัญชี กรุณาคลิกปุ่มด้านล่างเพื่อสร้างรหัสผ่านใหม่
                </div>
                
                <div class="button-container">
                    <a href="' . $resetUrl . '" class="reset-button">🔑 รีเซ็ตรหัสผ่านของฉัน</a>
                </div>
                
                <div class="info-card">
                    <h3>ข้อมูลความปลอดภัย</h3>
                    <ul class="info-list">
                        <li>ลิงก์นี้จะหมดอายุใน 20 นาทีเพื่อความปลอดภัย</li>
                        <li>คุณสามารถใช้ลิงก์นี้ได้เพียงครั้งเดียว</li>
                        <li>หากคุณไม่ได้ขอรีเซ็ตรหัสผ่าน กรุณาเพิกเฉยต่ออีเมลนี้</li>
                        <li>รหัสผ่านปัจจุบันของคุณยังคงใช้งานได้จนกว่าจะทำการรีเซ็ต</li>
                    </ul>
                </div>
                
                <div class="warning-card">
                    <h3>สำคัญ</h3>
                    <p>หากปุ่มด้านบนไม่ทำงาน คุณสามารถคัดลอกและวางลิงก์ต่อไปนี้ในเบราว์เซอร์ของคุณ:</p>
                    <p style="margin-top: 12px; font-family: monospace; font-size: 14px;">
                        <a href="' . $resetUrl . '">' . $resetUrl . '</a>
                    </p>
                </div>
                
                <div class="divider"></div>
                
                <div class="final-message">
                    หากคุณไม่ได้ขอรีเซ็ตรหัสผ่าน ไม่จำเป็นต้องดำเนินการใดๆ เพิ่มเติม 
                    บัญชีของคุณยังคงปลอดภัยและรหัสผ่านจะไม่เปลี่ยนแปลง
                </div>
            </div>
            
            <div class="footer">
                <p>
                    ต้องการความช่วยเหลือ? ติดต่อทีมสนับสนุนของเราที่ 
                    <a href="mailto:support@landslide-alert.com">support@landslide-alert.com</a>
                </p>
                <p>
                    เยี่ยมชมเว็บไซต์ของเรา: 
                    <a href="https://landslide-alert.com">landslide-alert.com</a>
                </p>
                <div class="copyright">
                    © 2024 Landslide Alert System. สงวนลิขสิทธิ์ทุกประการ<br>
                    อีเมลนี้ถูกส่งไปยัง ' . htmlspecialchars($userEmail ?: 'อีเมลที่ลงทะเบียน') . '
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Text Template สำหรับ Reset Password (fallback)
     */
    private function getResetPasswordTextTemplate($resetUrl, $userName) {
        $greeting = $userName ? "Hello " . $userName : "Hello";
        return $greeting . "!\n\n" .
               "We received a request to reset your password for your Landslide Alert System account.\n\n" .
               "Please click the following link to reset your password:\n" .
               $resetUrl . "\n\n" .
               "This link will expire in 20 minutes and can only be used once.\n\n" .
               "If you did not request this password reset, please ignore this email.\n\n" .
               "Best regards,\n" .
               "Landslide Alert System Team";
    }

    /**
     * รับ Base URL ของเว็บไซต์
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $protocol . '://' . $host;
        
        error_log("=== BASE URL GENERATION ===");
        error_log("Protocol: " . $protocol);
        error_log("Host: " . $host);
        error_log("Base URL: " . $baseUrl);
        error_log("Server variables:");
        error_log("- HTTPS: " . ($_SERVER['HTTPS'] ?? 'NOT SET'));
        error_log("- HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET'));
        error_log("- SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET'));
        
        return $baseUrl;
    }

    /**
     * สร้าง SVG Logo แบบ Base64
     */
    private function generateBase64Logo() {
        $svg = '
        <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="30" cy="30" r="30" fill="#4CAF50"/>
            <path d="M15 40L25 25L30 30L35 20L45 40" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="35" cy="18" r="3" fill="#FFF9C4"/>
        </svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode(trim($svg));
    }
}

