<?php
require_once 'connect.php';

class Table_password_reset {
    private $conn;

    public function __construct() {
        $this->conn = new database();
    }

    /**
     * สร้าง reset token ใหม่
     * @param string $email อีเมลของผู้ใช้
     * @param string $token Token ที่สร้างขึ้น
     * @param int $expiresIn จำนวนวินาทีก่อนหมดอายุ (default 1200 = 20 นาที)
     */
    public function createResetToken($email, $token, $expiresIn = 1200) { // 20 นาที
        // ลบเฉพาะ token ที่หมดอายุหรือถูกใช้แล้ว (ไม่ลบทั้งหมด เพื่อนับ rate limit)
        $this->deleteExpiredTokens();
        
        $sql = "INSERT INTO password_resets (email, token, expires_at, ip_address) 
                VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL :expires_in SECOND), :ip_address)";
        
        try {
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_in', $expiresIn);
            $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Create reset token error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ตรวจสอบ reset token
     */
    public function verifyResetToken($token) {
        $sql = "SELECT * FROM password_resets 
                WHERE token = :token 
                AND expires_at > NOW() 
                AND used = 0 
                LIMIT 1";
        
        try {
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Verify reset token error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ใช้ reset token (mark as used)
     */
    public function useResetToken($token) {
        $sql = "UPDATE password_resets SET used = 1 WHERE token = :token";
        
        try {
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':token', $token);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Use reset token error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ไม่ลบ token - เก็บไว้ทั้งหมดเป็นประวัติ
     * Method นี้จะไม่ทำอะไรเลย เพื่อเก็บประวัติการ reset password
     */
    public function deleteExpiredTokens($email = null) {
        // ไม่ลบ token ใดๆ - เก็บไว้ทั้งหมดเป็นประวัติ
        // Rate limit จะนับจาก created_at ภายใน 12 ชั่วโมง
        return true;
    }

    /**
     * Mark token ว่าส่งอีเมลไม่สำเร็จ (ใช้ used = -1 แทนการลบ)
     * Token ที่ส่งไม่สำเร็จจะไม่นับใน rate limit
     */
    public function markTokenAsFailed($token) {
        $sql = "UPDATE password_resets SET used = -1 WHERE token = :token";
        
        try {
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':token', $token);
            $result = $stmt->execute();
            error_log("Token marked as failed (email send failed)");
            return $result;
        } catch (PDOException $e) {
            error_log("Mark token as failed error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ลบ token เก่ามากๆ (เก่ากว่า 30 วัน) เพื่อทำ cleanup
     * ควรเรียกใช้เป็นระยะๆ เช่น ผ่าน cron job
     */
    public function cleanupOldTokens($daysOld = 30) {
        $sql = "DELETE FROM password_resets 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days_old DAY)";
        
        try {
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':days_old', $daysOld, PDO::PARAM_INT);
            $deleted = $stmt->execute();
            $count = $stmt->rowCount();
            error_log("Cleaned up $count old tokens (older than $daysOld days)");
            return $deleted;
        } catch (PDOException $e) {
            error_log("Cleanup old tokens error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ดึงประวัติการ reset password ของอีเมล
     */
    public function getResetHistory($email, $limit = 10) {
        $sql = "SELECT token, created_at, expires_at, used, ip_address 
                FROM password_resets 
                WHERE email = :email 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        try {
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get reset history error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ตรวจสอบ rate limit
     * นับเฉพาะ token ที่ส่งอีเมลสำเร็จ (used = 0 หรือ used = 1)
     * ไม่นับ token ที่ส่งอีเมลไม่สำเร็จ (used = -1)
     */
    public function checkRateLimit($email, $maxAttempts = 5, $timeWindow = 43200) { // 5 ครั้งใน 12 ชั่วโมง
        $sql = "SELECT COUNT(*) as attempts FROM password_resets 
                WHERE email = :email 
                AND created_at > DATE_SUB(NOW(), INTERVAL :time_window SECOND)
                AND used >= 0";
        
        try {
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':time_window', $timeWindow);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['attempts'] < $maxAttempts;
        } catch (PDOException $e) {
            error_log("Check rate limit error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * รับจำนวนครั้งที่เหลือ
     * นับเฉพาะ token ที่ส่งอีเมลสำเร็จ
     */
    public function getRemainingAttempts($email, $maxAttempts = 5, $timeWindow = 43200) {
        $sql = "SELECT COUNT(*) as attempts FROM password_resets 
                WHERE email = :email 
                AND created_at > DATE_SUB(NOW(), INTERVAL :time_window SECOND)
                AND used >= 0";
        
        try {
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':time_window', $timeWindow);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return max(0, $maxAttempts - $result['attempts']);
        } catch (PDOException $e) {
            error_log("Get remaining attempts error: " . $e->getMessage());
            return 0;
        }
    }
}
