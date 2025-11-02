<?php
require_once __DIR__ . '/connect.php';

class Table_device
{
    private $conn;

    public function __construct()
    {
        $this->conn = new database();
    }

    // Create - Add new device
    public function createDevice($device_id, $device_name, $location_id, $serialno, $void = 0)
    {
        try {
            $sql = "INSERT INTO lnd_device (device_id, device_name, location_id, serialno, void) 
                    VALUES (:device_id, :device_name, :location_id, :serialno, :void)";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            $stmt->bindParam(':device_name', $device_name, PDO::PARAM_STR);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->bindParam(':serialno', $serialno, PDO::PARAM_STR);
            $stmt->bindParam(':void', $void, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Create device error: " . $e->getMessage());
            return false;
        }
    }

    // Read - Get all devices
    public function getAllDevices()
    {
        try {
            $sql = "SELECT * FROM lnd_device WHERE void = 0 ORDER BY device_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all devices error: " . $e->getMessage());
            return [];
        }
    }

    public function getCountDevicesdelete()
    {
        try {
            $sql = "SELECT COUNT(*) FROM lnd_device WHERE void = 1";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Get count devices error: " . $e->getMessage());
            return 0;
        }
    }
    public function getCountDevices()
    {
        try {
            $sql = "SELECT COUNT(*) FROM lnd_device WHERE void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Get count devices error: " . $e->getMessage());
            return 0;
        }
    }

    // Read - Get device by device_id
    public function getDeviceById($device_id)
    {
        try {
            $sql = "SELECT * FROM lnd_device WHERE device_id = :device_id AND void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get device by ID error: " . $e->getMessage());
            return false;
        }
    }

    // Read - Get devices by location_id
    public function getDevicesByLocation($location_id)
    {
        try {
            $sql = "SELECT * FROM lnd_device WHERE location_id = :location_id AND void = 0 ORDER BY device_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get devices by location error: " . $e->getMessage());
            return [];
        }
    }

    // Read - Get device by serial number
    public function getDeviceBySerial($serialno)
    {
        try {
            $sql = "SELECT * FROM lnd_device WHERE serialno = :serialno AND void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':serialno', $serialno, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get device by serial error: " . $e->getMessage());
            return false;
        }
    }

    // Update - Update device information
    public function updateDevice($device_id, $device_name, $location_id, $serialno)
    {
        try {
            $sql = "UPDATE lnd_device 
                    SET device_name = :device_name, location_id = :location_id, serialno = :serialno
                    WHERE device_id = :device_id AND void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            $stmt->bindParam(':device_name', $device_name, PDO::PARAM_STR);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->bindParam(':serialno', $serialno, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update device error: " . $e->getMessage());
            return false;
        }
    }

    // Delete - Soft delete (set void = 1)
    public function deleteDevice($device_id)
    {
        try {
            $sql = "UPDATE lnd_device SET void = 1 WHERE device_id = :device_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete device error: " . $e->getMessage());
            return false;
        }
    }

    // Delete - Hard delete (permanently remove from database)
    public function hardDeleteDevice($device_id)
    {
        try {
            $sql = "DELETE FROM lnd_device WHERE device_id = :device_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Hard delete device error: " . $e->getMessage());
            return false;
        }
    }

    // Restore - Restore soft deleted device (set void = 0)
    public function restoreDevice($device_id)
    {
        try {
            $sql = "UPDATE lnd_device SET void = 0 WHERE device_id = :device_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Restore device error: " . $e->getMessage());
            return false;
        }
    }

    // Get deleted devices (void = 1)
    public function getDeletedDevices()
    {
        try {
            $sql = "SELECT * FROM lnd_device WHERE void = 1 ORDER BY device_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get deleted devices error: " . $e->getMessage());
            return [];
        }
    }

    // Check if device_id exists
    public function deviceExists($device_id)
    {
        try {
            $sql = "SELECT COUNT(*) FROM lnd_device WHERE device_id = :device_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Device exists check error: " . $e->getMessage());
            return false;
        }
    }

    // Check if serial number exists
    public function serialExists($serialno, $exclude_device_id = null)
    {
        try {
            if ($exclude_device_id) {
                $sql = "SELECT COUNT(*) FROM lnd_device WHERE serialno = :serialno AND device_id != :exclude_device_id";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':serialno', $serialno, PDO::PARAM_STR);
                $stmt->bindParam(':exclude_device_id', $exclude_device_id, PDO::PARAM_STR);
            } else {
                $sql = "SELECT COUNT(*) FROM lnd_device WHERE serialno = :serialno";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':serialno', $serialno, PDO::PARAM_STR);
            }
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Serial exists check error: " . $e->getMessage());
            return false;
        }
    }

    // Get device count
    public function getDeviceCount()
    {
        try {
            $sql = "SELECT COUNT(*) FROM lnd_device WHERE void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Get device count error: " . $e->getMessage());
            return 0;
        }
    }

    // Get device count by location
    public function getDeviceCountByLocation($location_id)
    {
        try {
            $sql = "SELECT COUNT(*) FROM lnd_device WHERE location_id = :location_id AND void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Get device count by location error: " . $e->getMessage());
            return 0;
        }
    }

    // Search devices
    public function searchDevices($keyword)
    {
        try {
            $keyword = '%' . $keyword . '%';
            $sql = "SELECT * FROM lnd_device 
                    WHERE (device_id LIKE :keyword1 
                           OR device_name LIKE :keyword2 
                           OR location_id LIKE :keyword3 
                           OR serialno LIKE :keyword4) 
                    AND void = 0 
                    ORDER BY device_id";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':keyword1', $keyword, PDO::PARAM_STR);
            $stmt->bindParam(':keyword2', $keyword, PDO::PARAM_STR);
            $stmt->bindParam(':keyword3', $keyword, PDO::PARAM_STR);
            $stmt->bindParam(':keyword4', $keyword, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search devices error: " . $e->getMessage());
            return [];
        }
    }

    // Get devices grouped by location
    public function getDevicesGroupedByLocation()
    {
        try {
            $sql = "SELECT 
                        l.location_id,
                        l.location_name,
                        COUNT(d.device_id) as total_devices
                    FROM lnd_location l
                    LEFT JOIN lnd_device d ON l.location_id = d.location_id AND d.void = 0
                    GROUP BY l.location_id, l.location_name
                    ORDER BY l.location_name";

            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get devices grouped by location error: " . $e->getMessage());
            return [];
        }
    }

    // Get devices grouped by location with detailed information
    public function getDevicesGroupedByLocationDetailed()
    {
        try {
            $sql = "SELECT 
                        l.location_id,
                        l.location_name,
                        l.latitude,
                        l.longtitude,
                        COUNT(d.device_id) as total_devices
                    FROM lnd_location l
                    LEFT JOIN lnd_device d ON l.location_id = d.location_id AND d.void = 0
                    WHERE l.void = 0
                    GROUP BY l.location_id, l.location_name, l.latitude, l.longtitude
                    ORDER BY l.location_name";

            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // เพิ่มข้อมูลอุปกรณ์ในแต่ละ location
            foreach ($results as &$result) {
                $devices = $this->getDevicesByLocation($result['location_id']);
                $result['devices'] = $devices;
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get devices grouped by location detailed error: " . $e->getMessage());
            return [];
        }
    }

    // Read - Get all devices including deleted ones
    public function getAllDevicesIncludingDeleted()
    {
        try {
            $sql = "SELECT 
                        d.*,
                        l.location_name
                    FROM lnd_device d
                    LEFT JOIN lnd_location l ON d.location_id = l.location_id
                    ORDER BY d.device_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all devices including deleted error: " . $e->getMessage());
            return [];
        }
    }

    // Get devices by location with location information
    public function getDevicesByLocationWithInfo($location_id)
    {
        try {
            $sql = "SELECT 
                        d.*,
                        l.location_name,
                        l.latitude,
                        l.longtitude
                    FROM lnd_device d
                    JOIN lnd_location l ON d.location_id = l.location_id
                    WHERE d.location_id = :location_id AND d.void = 0 AND l.void = 0
                    ORDER BY d.device_id";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get devices by location with info error: " . $e->getMessage());
            return [];
        }
    }
}
?>