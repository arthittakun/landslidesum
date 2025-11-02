<?php
// TABLE `notification` - Legacy class for backward compatibility
// ตาราง notification - คลาสเก่าสำหรับความเข้ากันได้ย้อนหลัง

require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/notification_functions.php';
date_default_timezone_set('Asia/Bangkok');

class Table_notification
{
    private $conn;
    private $notificationFunctions;

    public function __construct()
    {
        $this->conn = new database();
        $this->notificationFunctions = new NotificationFunctions();
    }

    /**
     * Get notifications (legacy method - now uses shared functions)
     * ดึงการแจ้งเตือน (เมธอดเก่า - ตอนนี้ใช้ฟังก์ชันร่วมกัน)
     */
    public function getNotifications()
    {
        return $this->notificationFunctions->getNotificationsForWeb();
    }

    /**
     * Get notification max ID (legacy method)
     * ดึง notification ID สูงสุด (เมธอดเก่า)
     */
    function getnotificationmaxid()
    {
        return $this->notificationFunctions->getMaxNotificationId();
    }

    /**
     * Add notification (legacy method)
     * เพิ่มการแจ้งเตือน (เมธอดเก่า)
     */
    function addNotification($device_id, $type, $text)
    {
        return $this->notificationFunctions->addNotification($device_id, $type, $text);
    }

    /**
     * Get location by device (legacy method)
     * ดึง location จาก device (เมธอดเก่า)
     */
    function getlocationByDevice($device_id)
    {
        // This method is now private in NotificationFunctions, so we'll implement it here
        // เมธอดนี้เป็น private ใน NotificationFunctions แล้ว ดังนั้นเราจะ implement ใหม่ที่นี่
        try {
            $sql = "SELECT l.location_id 
                    FROM lnd_device d 
                    LEFT JOIN lnd_location l ON d.location_id = l.location_id 
                    WHERE d.device_id = :device_id AND d.void = 0 AND l.void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['location_id'];
            }
            return null;
        } catch (PDOException $e) {
            error_log("Get location by device error: " . $e->getMessage());
            return null;
        }
    }

    // Additional methods for backward compatibility
    // เมธอดเพิ่มเติมสำหรับความเข้ากันได้ย้อนหลัง

    /**
     * Get notifications for application (new method)
     * ดึงการแจ้งเตือนสำหรับ application (เมธอดใหม่)
     */
    public function getNotificationsForApp($minutes = 15)
    {
        return $this->notificationFunctions->getNotificationsForApp($minutes);
    }

    /**
     * Get notifications by device (new method)
     * ดึงการแจ้งเตือนตาม device (เมธอดใหม่)
     */
    public function getNotificationsByDevice($device_id, $limit = 50)
    {
        return $this->notificationFunctions->getNotificationsByDevice($device_id, $limit);
    }

    /**
     * Get notifications by location (new method)
     * ดึงการแจ้งเตือนตาม location (เมธอดใหม่)
     */
    public function getNotificationsByLocation($location_id, $limit = 50)
    {
        return $this->notificationFunctions->getNotificationsByLocation($location_id, $limit);
    }

    /**
     * Get notification count by type (new method)
     * ดึงจำนวนการแจ้งเตือนตามประเภท (เมธอดใหม่)
     */
    public function getNotificationCountByType($type = null)
    {
        return $this->notificationFunctions->getNotificationCountByType($type);
    }

    /**
     * Get unread notification count (new method)
     * ดึงจำนวนการแจ้งเตือนที่ยังไม่ได้อ่าน (เมธอดใหม่)
     */
    public function getUnreadNotificationCount($last_read_id = 0)
    {
        return $this->notificationFunctions->getUnreadNotificationCount($last_read_id);
    }

    /**
     * Get notification statistics (new method)
     * ดึงสถิติการแจ้งเตือน (เมธอดใหม่)
     */
    public function getNotificationStats($days = 7)
    {
        return $this->notificationFunctions->getNotificationStats($days);
    }

    /**
     * Delete old notifications (new method)
     * ลบการแจ้งเตือนเก่า (เมธอดใหม่)
     */
    public function deleteOldNotifications($days = 30)
    {
        return $this->notificationFunctions->deleteOldNotifications($days);
    }

    /**
     * Mark notifications as read (new method)
     * ทำเครื่องหมายว่าการแจ้งเตือนได้อ่านแล้ว (เมธอดใหม่)
     */
    public function markNotificationsAsRead($notification_ids)
    {
        return $this->notificationFunctions->markNotificationsAsRead($notification_ids);
    }
}
?>