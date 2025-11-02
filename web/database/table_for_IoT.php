<?php
require_once __DIR__ . '/connect.php';
date_default_timezone_set('Asia/Bangkok');


// schema table
// TABLE `lnd_environment` (
//   `device_id` varchar(4) DEFAULT NULL,
//   `timekey` varchar(5) DEFAULT NULL,
//   `temp` float(5,2) DEFAULT 0.00,
//   `humid` float(5,2) DEFAULT 0.00,
//   `rain` float(5,2) DEFAULT 0.00,
//   `vibration` float(5,2) DEFAULT 0.00,
//   `distance` float(5,2) DEFAULT 0.00,
//   `datekey` date DEFAULT NULL,
//   `soil` float(10,2) DEFAULT 0.00,
//   `docno` varchar(10) NOT NULL,
//   `landslide` int(1) NOT NULL DEFAULT 0,
//   `floot` int(1) NOT NULL DEFAULT 0,
//   `img_path` text NOT NULL,
//   `textes` text NOT NULL,
//   `soil_high` float(5,2) NOT NULL DEFAULT 0.00
// ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

class tableIoT_environment
{
    private $conn;

    public function __construct()
    {
        $this->conn = new database();
    }

    function getdeviceid($device_id)
    {
        $sql = "SELECT device_id FROM lnd_device WHERE device_id = :device_id LIMIT 1";
        $stmt = $this->conn->getConnection()->prepare($sql);
        $stmt->bindParam(':device_id', $device_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false; // Return null if no device found
    }
    function getdocnomaxbydevice($device_id)
    {
        $doc = date('Ymd');
        $sql = "SELECT MAX(docno) AS max_docno FROM lnd_environment";
        $stmt = $this->conn->getConnection()->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC)['max_docno'];
            return $result;
        }else {
            $docno = substr($doc, 2, 4) . '0000';
            return $docno; // Return 0 if no records found
        }
        return null; 
    }
    function insertenvironment($device_id, $temperature, $humidity, $rain, $vibration, $distance, $soil , $floot , $landslide , $soil_high, $image = '', $text = '')
    {
        try {
            $date = date('Y-m-d');
            $doc = date('Ymd');
            $time = date('H:i'); 
            $max_docno = $this->getdocnomaxbydevice($device_id);
            $docno = substr($doc, 2, 4) . str_pad((intval(substr($max_docno, -4)) + 1), 4, '0', STR_PAD_LEFT);
            
            // ตรวจสอบและแปลง NULL เป็น empty string
            $image = $image ?? '';
            $text = $text ?? '';
            
            $sql = "INSERT INTO lnd_environment (device_id, temp, humid, rain, vibration, distance, soil, floot, landslide, soil_high, datekey, docno, timekey, img_path, textes) 
                    VALUES (:device_id, :temperature, :humidity, :rain, :vibration, :distance, :soil, :floot, :landslide, :soil_high, :datekey, :docno, :timekey, :img_path, :textes)";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id);
            $stmt->bindParam(':temperature', $temperature);
            $stmt->bindParam(':humidity', $humidity);
            $stmt->bindParam(':rain', $rain);
            $stmt->bindParam(':vibration', $vibration);
            $stmt->bindParam(':distance', $distance);
            $stmt->bindParam(':soil', $soil);
            $stmt->bindParam(':floot', $floot);
            $stmt->bindParam(':landslide', $landslide);
            $stmt->bindParam(':soil_high', $soil_high);
            $stmt->bindParam(':datekey', $date);
            $stmt->bindParam(':docno', $docno);
            $stmt->bindParam(':timekey', $time);
            $stmt->bindParam(':img_path', $image);
            $stmt->bindParam(':textes', $text);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Insert environment error: " . $e->getMessage());
            return false;
        }
    }
}
   

?>


