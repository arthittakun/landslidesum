<?php
// Notification Functions - Shared between Web and Application
// ฟังก์ชันการแจ้งเตือนที่ใช้ร่วมกันระหว่างเว็บและ application

require_once __DIR__ . '/connect.php';
date_default_timezone_set('Asia/Bangkok');

class NotificationFunctions
{
    private $conn;

    public function __construct()
    {
        $this->conn = new database();
    }

    /**
     * Get notifications for web interface (latest 100)
     * ดึงการแจ้งเตือนสำหรับเว็บ (ล่าสุด 100 รายการ)
     */
    public function getNotificationsForWeb()
    {
        try {
            $sql = "SELECT n.*, d.device_name, l.location_name
                FROM notification n
                LEFT JOIN lnd_device d ON n.device_id = d.device_id AND d.void = 0
                LEFT JOIN lnd_location l ON n.location_id = l.location_id AND l.void = 0
                ORDER BY n.create_at DESC
                LIMIT 100";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $notifications;
            }

            return [];
        } catch (PDOException $e) {
            error_log("Get notifications for web error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get notifications for application (filtered by time)
     * ดึงการแจ้งเตือนสำหรับ application (กรองตามเวลา)
     * @param int|string $time_range ช่วงเวลา (นาที) หรือ 'all' สำหรับข้อมูลทั้งหมด
     */
    public function getNotificationsForApp($time_range = 15)
    {
        try {
            $sql = "SELECT n.*, d.device_name, l.location_name, 
                    (SELECT e.img_path 
                     FROM lnd_environment e 
                     WHERE e.device_id = n.device_id 
                     AND e.img_path IS NOT NULL 
                     AND e.img_path != ''
                     ORDER BY e.datekey DESC, e.timekey DESC 
                     LIMIT 1) as img_path
                FROM notification n
                LEFT JOIN lnd_device d ON n.device_id = d.device_id AND d.void = 0
                LEFT JOIN lnd_location l ON n.location_id = l.location_id AND l.void = 0";
            
            // Add time filter if not 'all'
            if ($time_range !== 'all') {
                $datetime = new DateTime();
                $datetime->modify("-{$time_range} minutes");
                $time_filter = $datetime->format('Y-m-d H:i:s');
                
                $sql .= " WHERE n.create_at >= :time_filter";
                $sql .= " ORDER BY n.create_at DESC";
                
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':time_filter', $time_filter, PDO::PARAM_STR);
            } else {
                $sql .= " ORDER BY n.create_at DESC LIMIT 100";
                $stmt = $this->conn->getConnection()->prepare($sql);
            }
            
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $notifications;
            }

            return [];
        } catch (PDOException $e) {
            error_log("Get notifications for app error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get notifications by device ID
     * ดึงการแจ้งเตือนตาม device ID
     */
    public function getNotificationsByDevice($device_id, $limit = 50)
    {
        try {
            $sql = "SELECT n.*, d.device_name, l.location_name
                FROM notification n
                LEFT JOIN lnd_device d ON n.device_id = n.device_id AND d.void = 0
                LEFT JOIN lnd_location l ON n.location_id = l.location_id AND l.void = 0
                WHERE n.device_id = :device_id
                ORDER BY n.create_at DESC
                LIMIT :limit";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $notifications;
            }

            return [];
        } catch (PDOException $e) {
            error_log("Get notifications by device error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get notifications by location ID
     * ดึงการแจ้งเตือนตาม location ID
     */
    public function getNotificationsByLocation($location_id, $limit = 50)
    {
        try {
            $sql = "SELECT n.*, d.device_name, l.location_name
                FROM notification n
                LEFT JOIN lnd_device d ON n.device_id = d.device_id AND d.void = 0
                LEFT JOIN lnd_location l ON n.location_id = n.location_id AND l.void = 0
                WHERE n.location_id = :location_id
                ORDER BY n.create_at DESC
                LIMIT :limit";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $notifications;
            }

            return [];
        } catch (PDOException $e) {
            error_log("Get notifications by location error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get notification count by type
     * ดึงจำนวนการแจ้งเตือนตามประเภท
     */
    public function getNotificationCountByType($type = null)
    {
        try {
            if ($type !== null) {
                $sql = "SELECT COUNT(*) as count FROM notification WHERE type = :type";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':type', $type, PDO::PARAM_INT);
            } else {
                $sql = "SELECT type, COUNT(*) as count FROM notification GROUP BY type";
                $stmt = $this->conn->getConnection()->prepare($sql);
            }
            
            $stmt->execute();
            
            if ($type !== null) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? (int)$result['count'] : 0;
            } else {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Get notification count by type error: " . $e->getMessage());
            return $type !== null ? 0 : [];
        }
    }

    /**
     * Get unread notification count
     * ดึงจำนวนการแจ้งเตือนที่ยังไม่ได้อ่าน
     */
    public function getUnreadNotificationCount($last_read_id = 0)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM notification WHERE notification_id > :last_read_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':last_read_id', $last_read_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Get unread notification count error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get notification statistics
     * ดึงสถิติการแจ้งเตือน
     */
    public function getNotificationStats($days = 7)
    {
        try {
            $datetime = new DateTime();
            $datetime->modify("-{$days} days");
            $date_filter = $datetime->format('Y-m-d H:i:s');
            
            $sql = "SELECT 
                        DATE(n.create_at) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN n.type = 1 THEN 1 ELSE 0 END) as warning_count,
                        SUM(CASE WHEN n.type = 2 THEN 1 ELSE 0 END) as flood_count,
                        SUM(CASE WHEN n.type = 3 THEN 1 ELSE 0 END) as info_count
                    FROM notification n
                    WHERE n.create_at >= :date_filter
                    GROUP BY DATE(n.create_at)
                    ORDER BY date DESC";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':date_filter', $date_filter, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return [];
        } catch (PDOException $e) {
            error_log("Get notification stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get max notification ID
     * ดึง notification ID สูงสุด
     */
    public function getMaxNotificationId()
    {
        try {
            $sql = "SELECT MAX(notification_id) AS max_id FROM notification";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['max_id'] ? (int)$result['max_id'] + 1 : 1;
            }
            return 1;
        } catch (PDOException $e) {
            error_log("Get max notification ID error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Add new notification
     * เพิ่มการแจ้งเตือนใหม่
     */
    public function addNotification($device_id, $type, $text, $location_id = null)
    {
        try {
            // If location_id not provided, get it from device
            if (!$location_id) {
                $location_id = $this->getLocationByDevice($device_id);
            }
            
            if (!$location_id) {
                error_log("Location not found for device: " . $device_id);
                return false;
            }
            
            $notification_id = $this->getMaxNotificationId();
            
            $sql = "INSERT INTO notification (notification_id, device_id, location_id, type, text) 
                    VALUES (:notification_id, :device_id, :location_id, :type, :text)";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_INT);
            $stmt->bindParam(':text', $text, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Add notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get location by device ID
     * ดึง location ID จาก device ID
     */
    private function getLocationByDevice($device_id)
    {
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

    /**
     * Delete old notifications
     * ลบการแจ้งเตือนเก่า
     */
    public function deleteOldNotifications($days = 30)
    {
        try {
            $datetime = new DateTime();
            $datetime->modify("-{$days} days");
            $date_filter = $datetime->format('Y-m-d H:i:s');
            
            $sql = "DELETE FROM notification WHERE create_at < :date_filter";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':date_filter', $date_filter, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete old notifications error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark notifications as read
     * ทำเครื่องหมายว่าการแจ้งเตือนได้อ่านแล้ว
     */
    public function markNotificationsAsRead($notification_ids)
    {
        try {
            if (empty($notification_ids) || !is_array($notification_ids)) {
                return false;
            }
            
            $placeholders = implode(',', array_fill(0, count($notification_ids), '?'));
            $sql = "UPDATE notification SET is_read = 1 WHERE notification_id IN ($placeholders)";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            return $stmt->execute($notification_ids);
        } catch (PDOException $e) {
            error_log("Mark notifications as read error: " . $e->getMessage());
            return false;
        }
    }
}
?>
