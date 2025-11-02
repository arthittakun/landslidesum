<?php
require_once __DIR__ . '/connect.php';

class Table_environment
{
    private $conn;

    public function __construct()
    {
        $this->conn = new database();
    }

    // Create - Add new environment data
    public function createEnvironmentData($device_id, $timekey, $temp, $humid, $rain, $vibration, $distance, $datekey, $soil, $docno, $landslide = 0, $floot = 0, $text_critical = '')
    {
        try {
            $sql = "INSERT INTO lnd_environment (device_id, timekey, temp, humid, rain, vibration, distance, datekey, soil, docno, landslide, floot , text_critical) 
                    VALUES (:device_id, :timekey, :temp, :humid, :rain, :vibration, :distance, :datekey, :soil, :docno, :landslide, :floot, :text_critical)";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            $stmt->bindParam(':timekey', $timekey, PDO::PARAM_STR);
            $stmt->bindParam(':temp', $temp, PDO::PARAM_STR);
            $stmt->bindParam(':humid', $humid, PDO::PARAM_STR);
            $stmt->bindParam(':rain', $rain, PDO::PARAM_STR);
            $stmt->bindParam(':vibration', $vibration, PDO::PARAM_STR);
            $stmt->bindParam(':distance', $distance, PDO::PARAM_STR);
            $stmt->bindParam(':datekey', $datekey, PDO::PARAM_STR);
            $stmt->bindParam(':soil', $soil, PDO::PARAM_STR);
            $stmt->bindParam(':docno', $docno, PDO::PARAM_STR);
            $stmt->bindParam(':landslide', $landslide, PDO::PARAM_INT);
            $stmt->bindParam(':floot', $floot, PDO::PARAM_INT);
            $stmt->bindParam(':text_critical', $text_critical, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Create environment data error: " . $e->getMessage());
            return false;
        }
    }
    public function getCountLandslide(){
        try {
            $sql = "SELECT COUNT(*) as count FROM lnd_environment WHERE landslide = 1";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Get landslide count error: " . $e->getMessage());
            return 0;
        }
    }

    public function getCountFlood(){
        try {
            $sql = "SELECT COUNT(*) as count FROM lnd_environment WHERE floot = 1";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Get flood count error: " . $e->getMessage());
            return 0;
        }
    }
    // Reusable function to query data based on a time range
    public function getDataByTimeRange($days)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_readings,
                        COUNT(DISTINCT device_id) as active_devices,
                        AVG(temp) as avg_temp,
                        MIN(temp) as min_temp,
                        MAX(temp) as max_temp,
                        AVG(humid) as avg_humid,
                        MIN(humid) as min_humid,
                        MAX(humid) as max_humid,
                        AVG(rain) as avg_rain,
                        MAX(rain) as max_rain,
                        AVG(vibration) as avg_vibration,
                        MAX(vibration) as max_vibration,
                        AVG(soil) as avg_soil,
                        MAX(soil) as max_soil
                    FROM lnd_environment
                    WHERE datekey >= DATE_SUB(CURDATE(), INTERVAL :days DAY)";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get data by time range error: " . $e->getMessage());
            return false;
        }
    }

    public function getHourlyAverages($device_id = null)
    {
        try {
            $whereClause = '';
            if ($device_id) {
                $whereClause = "WHERE device_id = :device_id";
            }

            $sql = "SELECT 
                        HOUR(STR_TO_DATE(timekey, '%H:%i')) as hour,
                        AVG(temp) as avg_temp,
                        AVG(humid) as avg_humid,
                        AVG(rain) as avg_rain,
                        AVG(vibration) as avg_vibration,
                        AVG(soil) as avg_soil
                    FROM lnd_environment
                    $whereClause
                    GROUP BY HOUR(STR_TO_DATE(timekey, '%H:%i'))
                    ORDER BY hour";

            $stmt = $this->conn->getConnection()->prepare($sql);

            if ($device_id) {
                $stmt->bindParam(':device_id', $device_id, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get hourly averages error: " . $e->getMessage());
            return [];
        }
    }

    // Get landslide data for each device
   public function getLandslideDataByDevice()
    {
        try {
            $sql = "SELECT 
                        device_id,
                        SUM(landslide) as total_landslides,
                        MAX(datekey) as latest_date
                    FROM lnd_environment
                    WHERE datekey = :today
                    GROUP BY device_id
                    ORDER BY device_id";

            $stmt = $this->conn->getConnection()->prepare($sql);
            $today = date('Y-m-d'); // Get current date in YYYY-MM-DD format (e.g., 2025-06-23)
            $stmt->bindParam(':today', $today, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get landslide data by device error: " . $e->getMessage());
            return [];
        }
    }

    // Get flood data for each device for today
    public function getFloodDataByDevice()
    {
        try {
            $sql = "SELECT 
                        device_id,
                        SUM(floot) as total_floods,
                        MAX(datekey) as latest_date
                    FROM lnd_environment
                    WHERE datekey = :today
                    GROUP BY device_id
                    ORDER BY device_id";

            $stmt = $this->conn->getConnection()->prepare($sql);
            $today = date('Y-m-d'); // Get current date in YYYY-MM-DD format
            $stmt->bindParam(':today', $today, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get flood data by device error: " . $e->getMessage());
            return [];
        }
    }

    // Get total landslide and flood counts for today
    public function getTotalLandslideAndFloodCounts()
    {
        try {
            $sql = "SELECT 
                        SUM(landslide) as total_landslides,
                        SUM(floot) as total_floods,
                        MAX(datekey) as latest_date
                    FROM lnd_environment
                    WHERE datekey = :today";

            $stmt = $this->conn->getConnection()->prepare($sql);
            $today = date('Y-m-d'); // Get current date in YYYY-MM-DD format
            $stmt->bindParam(':today', $today, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get total landslide and flood counts error: " . $e->getMessage());
            return [];
        }
    }

    // Get latest environment data for each device (latest row per device) in date range (optional)
    public function getLatestDataByDevice($start = null, $end = null)
    {
        try {
            if ($start && $end) {
                $sql = "SELECT e.device_id, e.temp, e.humid, e.rain, e.vibration, e.soil, e.landslide, e.floot, e.datekey, e.timekey
                        FROM lnd_environment e
                        INNER JOIN (
                            SELECT device_id, MAX(CONCAT(datekey, ' ', timekey)) AS max_dt
                            FROM lnd_environment
                            WHERE datekey BETWEEN :start AND :end
                            GROUP BY device_id
                        ) latest
                        ON e.device_id = latest.device_id
                        AND CONCAT(e.datekey, ' ', e.timekey) = latest.max_dt
                        WHERE e.datekey BETWEEN :start AND :end
                        ORDER BY e.datekey DESC, e.timekey DESC";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':start', $start, PDO::PARAM_STR);
                $stmt->bindParam(':end', $end, PDO::PARAM_STR);
            } else {
                $sql = "SELECT e.device_id, e.temp, e.humid, e.rain, e.vibration, e.soil, e.landslide, e.floot, e.datekey, e.timekey
                        FROM lnd_environment e
                        INNER JOIN (
                            SELECT device_id, MAX(CONCAT(datekey, ' ', timekey)) AS max_dt
                            FROM lnd_environment
                            GROUP BY device_id
                        ) latest
                        ON e.device_id = latest.device_id
                        AND CONCAT(e.datekey, ' ', e.timekey) = latest.max_dt
                        ORDER BY e.datekey DESC, e.timekey DESC";
                $stmt = $this->conn->getConnection()->prepare($sql);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get latest data by device error: " . $e->getMessage());
            return [];
        }
    }

    // Get all environment data in date range (for all rows, not just latest per device)
    public function getAllDataByDateRange($start = null, $end = null)
    {
        try {
            if ($start && $end) {
                $sql = "SELECT device_id, temp, humid, rain, vibration, soil, landslide, floot, datekey, timekey
                        FROM lnd_environment
                        WHERE datekey BETWEEN :start AND :end
                        ORDER BY datekey DESC, timekey DESC";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':start', $start, PDO::PARAM_STR);
                $stmt->bindParam(':end', $end, PDO::PARAM_STR);
            } else {
                $sql = "SELECT device_id, temp, humid, rain, vibration, soil, landslide, floot, datekey, timekey
                        FROM lnd_environment
                        ORDER BY datekey DESC, timekey DESC";
                $stmt = $this->conn->getConnection()->prepare($sql);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all data by date range error: " . $e->getMessage());
            return [];
        }
    }


    // display users nomal นะจ๊ะ

    public function displayData()
    {
        try {
            $sql = "SELECT 
                l.location_name,
                e.temp, 
                e.humid, 
                e.rain, 
                e.vibration, 
                e.distance, 
                e.soil, 
                e.docno,
                e.datekey,
                e.timekey
            FROM lnd_environment AS e JOIN lnd_device AS d ON d.device_id = e.device_id JOIN lnd_location AS l ON l.location_id = d.location_id ORDER BY datekey DESC, timekey DESC";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Replace docno with datekey+timekey
            foreach ($result as &$row) {
                if (isset($row['datekey']) && isset($row['timekey'])) {
                    $row['docno'] = $row['datekey'] ."-". $row['timekey'];
                }
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Display all data error: " . $e->getMessage());
            return [];
        }




        //   try {
        //     $sql = "SELECT 
        //     l.location_name,
        //     e.temp, 
        //     e.humid, 
        //     e.rain, 
        //     e.vibration, 
        //     e.distance, 
        //     e.soil, 
        //     e.docno, 
        //     e.landslide, 
        //     e.floot, 
        //     e.text_critical
        // FROM lnd_environment AS e JOIN lnd_device AS d ON d.device_id = e.device_id JOIN lnd_location AS l ON l.location_id = d.location_id ORDER BY datekey DESC, timekey DESC";
        //     $stmt = $this->conn->getConnection()->prepare($sql);
        //     $stmt->execute();
        //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
        // } catch (PDOException $e) {
        //     error_log("Display all data error: " . $e->getMessage());
        //     return [];
        // }
    }



    public function displayCriticalData()
    {
        try {
            $sql = "SELECT 
            l.location_name,
            e.docno, 
            e.landslide, 
            e.floot, 
            e.text_critical
        FROM lnd_environment AS e JOIN lnd_device AS d ON d.device_id = e.device_id JOIN lnd_location AS l ON l.location_id = d.location_id ORDER BY datekey DESC, timekey DESC";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Display all data error: " . $e->getMessage());
            return [];
        }
    }

    function getenvironment($page , $page_size)
    {
        try {
            $offset = ($page - 1) * $page_size;
            $sql = "SELECT 
                        e.*,
                        l.location_name,
                        l.latitude,
                        l.longtitude,
                        d.device_name,
                        d.serialno
                    FROM lnd_environment e 
                    LEFT JOIN lnd_device d ON d.device_id = e.device_id AND d.void = 0
                    LEFT JOIN lnd_location l ON l.location_id = d.location_id AND l.void = 0
                    ORDER BY e.datekey DESC, e.timekey DESC 
                    LIMIT :offset, :page_size";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':page_size', $page_size, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return []; // Return empty array if no records found
        } catch (PDOException $e) {
            error_log("Get environment with location error: " . $e->getMessage());
            return [];
        }
    }

    // Get environment analysis data by date range
    public function getEnvironmentAnalysis($start_date = null, $end_date = null, $device_id = null, $location_id = null)
    {
        try {
            $whereConditions = [];
            $params = [];
            
            if ($start_date) {
                $whereConditions[] = "e.datekey >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $whereConditions[] = "e.datekey <= :end_date";
                $params[':end_date'] = $end_date;
            }
            
            if ($device_id) {
                $whereConditions[] = "e.device_id = :device_id";
                $params[':device_id'] = $device_id;
            }
            
            if ($location_id) {
                $whereConditions[] = "d.location_id = :location_id";
                $params[':location_id'] = $location_id;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $sql = "SELECT 
                        e.*,
                        d.device_name,
                        d.location_id,
                        l.location_name,
                        l.latitude,
                        l.longtitude,
                        CASE 
                            WHEN e.landslide = 1 AND e.floot = 1 THEN 'ดินถล่มและน้ำท่วม'
                            WHEN e.landslide = 1 THEN 'ดินถล่ม'
                            WHEN e.floot = 1 THEN 'น้ำท่วม'
                            ELSE 'ปกติ'
                        END as alert_type,
                        CASE 
                            WHEN e.landslide = 1 OR e.floot = 1 THEN 'วิกฤต'
                            ELSE 'ปกติ'
                        END as severity_level
                    FROM lnd_environment e
                    LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
                    LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
                    $whereClause
                    ORDER BY e.datekey DESC, e.timekey DESC";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Get environment analysis error: " . $e->getMessage());
            return [];
        }
    }

    // Get environment statistics by date range
    public function getEnvironmentStats($start_date = null, $end_date = null, $device_id = null, $location_id = null)
    {
        try {
            $whereConditions = [];
            $params = [];
            
            if ($start_date) {
                $whereConditions[] = "e.datekey >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $whereConditions[] = "e.datekey <= :end_date";
                $params[':end_date'] = $end_date;
            }
            
            if ($device_id) {
                $whereConditions[] = "e.device_id = :device_id";
                $params[':device_id'] = $device_id;
            }
            
            if ($location_id) {
                $whereConditions[] = "d.location_id = :location_id";
                $params[':location_id'] = $location_id;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $sql = "SELECT 
                        COUNT(*) as total_records,
                        COUNT(DISTINCT e.device_id) as active_devices,
                        COUNT(DISTINCT d.location_id) as locations,
                        AVG(e.temp) as avg_temp,
                        MIN(e.temp) as min_temp,
                        MAX(e.temp) as max_temp,
                        AVG(e.humid) as avg_humid,
                        MIN(e.humid) as min_humid,
                        MAX(e.humid) as max_humid,
                        AVG(e.rain) as avg_rain,
                        MIN(e.rain) as min_rain,
                        MAX(e.rain) as max_rain,
                        AVG(e.vibration) as avg_vibration,
                        MIN(e.vibration) as min_vibration,
                        MAX(e.vibration) as max_vibration,
                        AVG(e.distance) as avg_distance,
                        MIN(e.distance) as min_distance,
                        MAX(e.distance) as max_distance,
                        AVG(e.soil) as avg_soil,
                        MIN(e.soil) as min_soil,
                        MAX(e.soil) as max_soil,
                        AVG(e.soil_high) as avg_soil_high,
                        MIN(e.soil_high) as min_soil_high,
                        MAX(e.soil_high) as max_soil_high,
                        SUM(e.landslide) as total_landslide,
                        SUM(e.floot) as total_flood,
                        MIN(e.datekey) as earliest_date,
                        MAX(e.datekey) as latest_date
                    FROM lnd_environment e
                    LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
                    LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
                    $whereClause";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get environment stats error: " . $e->getMessage());
            return [];
        }
    }

    // Get critical data analysis
    public function getCriticalAnalysis($start_date = null, $end_date = null)
    {
        try {
            $whereConditions = ["(e.landslide = 1 OR e.floot = 1)"];
            $params = [];
            
            if ($start_date) {
                $whereConditions[] = "e.datekey >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $whereConditions[] = "e.datekey <= :end_date";
                $params[':end_date'] = $end_date;
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            
            $sql = "SELECT 
                        e.*,
                        d.device_name,
                        d.location_id,
                        l.location_name,
                        l.latitude,
                        l.longtitude,
                        CASE 
                            WHEN e.landslide = 1 AND e.floot = 1 THEN 'ดินถล่มและน้ำท่วม'
                            WHEN e.landslide = 1 THEN 'ดินถล่ม'
                            WHEN e.floot = 1 THEN 'น้ำท่วม'
                            ELSE 'ปกติ'
                        END as alert_type
                    FROM lnd_environment e
                    LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
                    LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
                    $whereClause
                    ORDER BY e.datekey DESC, e.timekey DESC";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get critical analysis error: " . $e->getMessage());
            return [];
        }
    }

    // Get daily trend analysis
    public function getDailyTrends($days = 30, $device_id = null, $location_id = null)
    {
        try {
            $whereConditions = ["e.datekey >= DATE_SUB(CURDATE(), INTERVAL :days DAY)"];
            $params = [':days' => $days];
            
            if ($device_id) {
                $whereConditions[] = "e.device_id = :device_id";
                $params[':device_id'] = $device_id;
            }
            
            if ($location_id) {
                $whereConditions[] = "d.location_id = :location_id";
                $params[':location_id'] = $location_id;
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            
            $sql = "SELECT 
                        e.datekey,
                        COUNT(*) as total_readings,
                        AVG(e.temp) as avg_temp,
                        AVG(e.humid) as avg_humid,
                        AVG(e.rain) as avg_rain,
                        AVG(e.vibration) as avg_vibration,
                        AVG(e.distance) as avg_distance,
                        AVG(e.soil) as avg_soil,
                        AVG(e.soil_high) as avg_soil_high,
                        SUM(e.landslide) as daily_landslide,
                        SUM(e.floot) as daily_flood
                    FROM lnd_environment e
                    LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
                    $whereClause
                    GROUP BY e.datekey
                    ORDER BY e.datekey DESC";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get daily trends error: " . $e->getMessage());
            return [];
        }
    }

    // Get location comparison data
    public function getLocationComparison($start_date = null, $end_date = null)
    {
        try {
            $whereConditions = [];
            $params = [];
            
            if ($start_date) {
                $whereConditions[] = "e.datekey >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $whereConditions[] = "e.datekey <= :end_date";
                $params[':end_date'] = $end_date;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $sql = "SELECT 
                        l.location_id,
                        l.location_name,
                        l.latitude,
                        l.longtitude,
                        COUNT(*) as total_readings,
                        COUNT(DISTINCT e.device_id) as device_count,
                        AVG(e.temp) as avg_temp,
                        AVG(e.humid) as avg_humid,
                        AVG(e.rain) as avg_rain,
                        AVG(e.vibration) as avg_vibration,
                        AVG(e.distance) as avg_distance,
                        AVG(e.soil) as avg_soil,
                        AVG(e.soil_high) as avg_soil_high,
                        SUM(e.landslide) as total_landslide,
                        SUM(e.floot) as total_flood,
                        MIN(e.datekey) as earliest_reading,
                        MAX(e.datekey) as latest_reading
                    FROM lnd_environment e
                    LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
                    LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
                    $whereClause
                    GROUP BY l.location_id, l.location_name, l.latitude, l.longtitude
                    HAVING COUNT(*) > 0
                    ORDER BY l.location_name";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get location comparison error: " . $e->getMessage());
            return [];
        }
    }

    // Get alert summary data
    public function getAlertSummary($start_date = null, $end_date = null, $device_id = null, $location_id = null)
    {
        try {
            $whereConditions = [];
            $params = [];
            
            if ($start_date) {
                $whereConditions[] = "e.datekey >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $whereConditions[] = "e.datekey <= :end_date";
                $params[':end_date'] = $end_date;
            }
            
            if ($device_id) {
                $whereConditions[] = "e.device_id = :device_id";
                $params[':device_id'] = $device_id;
            }
            
            if ($location_id) {
                $whereConditions[] = "d.location_id = :location_id";
                $params[':location_id'] = $location_id;
            }
            
            // Get all environment data (not just alerts) but categorize them
            // $whereConditions[] = "(e.landslide = 1 OR e.floot = 1)";  // Comment out for demo
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $sql = "SELECT 
                        e.*,
                        d.device_name,
                        d.device_type,
                        l.location_name,
                        l.province,
                        l.district,
                        CASE 
                            WHEN e.landslide = 1 AND e.floot = 1 THEN 'ดินถล่มและน้ำท่วม'
                            WHEN e.landslide = 1 THEN 'ดินถล่ม'
                            WHEN e.floot = 1 THEN 'น้ำท่วม'
                            ELSE 'ปกติ'
                        END as alert_type,
                        CASE 
                            WHEN e.landslide = 1 OR e.floot = 1 THEN 'วิกฤต'
                            ELSE 'ปกติ'
                        END as severity_level
                    FROM lnd_environment e
                    LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
                    LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
                    $whereClause
                    ORDER BY e.datekey DESC, e.timekey DESC";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get alert summary error: " . $e->getMessage());
            return [];
        }
    }

    // Get alert statistics
    public function getAlertStats($start_date = null, $end_date = null, $device_id = null, $location_id = null)
    {
        try {
            $whereConditions = [];
            $params = [];
            
            if ($start_date) {
                $whereConditions[] = "e.datekey >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $whereConditions[] = "e.datekey <= :end_date";
                $params[':end_date'] = $end_date;
            }
            
            if ($device_id) {
                $whereConditions[] = "e.device_id = :device_id";
                $params[':device_id'] = $device_id;
            }
            
            if ($location_id) {
                $whereConditions[] = "d.location_id = :location_id";
                $params[':location_id'] = $location_id;
            }
            
            // Get all environment data statistics (not just alerts) but calculate alert counts
            // $whereConditions[] = "(e.landslide = 1 OR e.floot = 1)";  // Comment out for demo
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $sql = "SELECT 
                        COUNT(*) as total_records,
                        COUNT(DISTINCT e.device_id) as affected_devices,
                        COUNT(DISTINCT d.location_id) as affected_locations,
                        SUM(e.landslide) as total_landslide,
                        SUM(e.floot) as total_flood,
                        SUM(CASE WHEN e.landslide = 1 OR e.floot = 1 THEN 1 ELSE 0 END) as total_alerts,
                        SUM(CASE WHEN e.landslide = 1 AND e.floot = 1 THEN 1 ELSE 0 END) as combined_alerts,
                        MIN(e.datekey) as earliest_alert,
                        MAX(e.datekey) as latest_alert,
                        AVG(e.temp) as avg_temp_during_alert,
                        AVG(e.humid) as avg_humid_during_alert,
                        AVG(e.rain) as avg_rain_during_alert,
                        AVG(e.vibration) as avg_vibration_during_alert
                    FROM lnd_environment e
                    LEFT JOIN lnd_device d ON e.device_id = d.device_id AND d.void = 0
                    LEFT JOIN lnd_location l ON d.location_id = l.location_id AND l.void = 0
                    $whereClause";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get alert stats error: " . $e->getMessage());
            return [];
        }
    }

    // Get critical locations by risk level (1-3)
    public function getCriticalLocationsByRiskLevel($risk_level = null, $type = 'both', $start_date = null, $end_date = null)
    {
        try {
            $whereConditions = [];
            $params = [];
            
            // กรองตามประเภทภัย
            if ($type === 'flood') {
                $whereConditions[] = "e.floot > 0";
            } elseif ($type === 'landslide') {
                $whereConditions[] = "e.landslide > 0";
            } else {
                $whereConditions[] = "(e.floot > 0 OR e.landslide > 0)";
            }
            
            // กรองตามระดับความเสี่ยง
            if ($risk_level !== null) {
                $risk_level = (int)$risk_level;
                if ($type === 'flood') {
                    $whereConditions[] = "e.floot = :risk_level";
                    $params[':risk_level'] = $risk_level;
                } elseif ($type === 'landslide') {
                    $whereConditions[] = "e.landslide = :risk_level";
                    $params[':risk_level'] = $risk_level;
                } else {
                    $whereConditions[] = "(e.floot = :risk_level OR e.landslide = :risk_level)";
                    $params[':risk_level'] = $risk_level;
                }
            }
            
            // กรองตามวันที่
            if ($start_date) {
                $whereConditions[] = "e.datekey >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($end_date) {
                $whereConditions[] = "e.datekey <= :end_date";
                $params[':end_date'] = $end_date;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Query หาข้อมูลความเสี่ยงแยกตามโลเคชัน
            $sql = "SELECT 
                        l.location_id,
                        l.location_name,
                        l.latitude,
                        l.longtitude,
                        MAX(GREATEST(e.landslide, e.floot)) as max_risk_level,
                        MAX(e.datekey) as latest_alert_date,
                        MAX(e.timekey) as latest_alert_time,
                        (SELECT e2.textes 
                         FROM lnd_environment e2 
                         JOIN lnd_device d2 ON d2.device_id = e2.device_id AND d2.void = 0
                         WHERE d2.location_id = l.location_id
                         AND GREATEST(e2.landslide, e2.floot) = MAX(GREATEST(e.landslide, e.floot))
                         ORDER BY e2.datekey DESC, e2.timekey DESC 
                         LIMIT 1) as risk_description,
                        GROUP_CONCAT(DISTINCT d.device_id ORDER BY d.device_id SEPARATOR ', ') as device_list
                    FROM lnd_environment e
                    JOIN lnd_device d ON d.device_id = e.device_id AND d.void = 0
                    JOIN lnd_location l ON l.location_id = d.location_id AND l.void = 0
                    $whereClause
                    GROUP BY l.location_id, l.location_name, l.latitude, l.longtitude
                    ORDER BY max_risk_level DESC, latest_alert_date DESC";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get critical locations by risk level error: " . $e->getMessage());
            return [];
        }
    }
}
?>


