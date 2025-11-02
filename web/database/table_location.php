
<?php
require_once __DIR__ . '/connect.php';
// schema table

// TABLE `lnd_location` (
//   `location_id` varchar(3) NOT NULL,
//   `location_name` varchar(50) NOT NULL,
//   `latitude` varchar(20) NOT NULL,
//   `longtitude` varchar(20) NOT NULL,
//   `void` int(1) NOT NULL DEFAULT 0
// ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

class Table_location
{
    private $conn;

    public function __construct()
    {
        $this->conn = new database();
    }

    // Create - Add new location
    public function createLocation($location_id, $location_name, $latitude, $longtitude, $void = 0)
    {
        try {
            $sql = "INSERT INTO lnd_location (location_id, location_name, latitude, longtitude, void) 
                    VALUES (:location_id, :location_name, :latitude, :longtitude, :void)";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->bindParam(':location_name', $location_name, PDO::PARAM_STR);
            $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
            $stmt->bindParam(':longtitude', $longtitude, PDO::PARAM_STR);
            $stmt->bindParam(':void', $void, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Create location error: " . $e->getMessage());
            return false;
        }
    }

    // Read - Get all locations
    public function getAllLocations()
    {
        try {
            $sql = "SELECT * FROM lnd_location WHERE void = 0 ORDER BY location_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all locations error: " . $e->getMessage());
            return [];
        }
    }

    // Read - Get all locations including deleted ones
    public function getAllLocationsIncludingDeleted()
    {
        try {
            $sql = "SELECT * FROM lnd_location ORDER BY void ASC, location_id ASC";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all locations including deleted error: " . $e->getMessage());
            return [];
        }
    }

    // Read - Get location by location_id
    public function getLocationById($location_id)
    {
        try {
            $sql = "SELECT * FROM lnd_location WHERE location_id = :location_id AND void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get location by ID error: " . $e->getMessage());
            return false;
        }
    }

    // Read - Get locations by name (search)
    public function getLocationsByName($location_name)
    {
        try {
            $location_name = '%' . $location_name . '%';
            $sql = "SELECT * FROM lnd_location WHERE location_name LIKE :location_name AND void = 0 ORDER BY location_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_name', $location_name, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get locations by name error: " . $e->getMessage());
            return [];
        }
    }

    // Read - Get locations by coordinates (within range)
    public function getLocationsByCoordinates($lat_min, $lat_max, $lng_min, $lng_max)
    {
        try {
            $sql = "SELECT * FROM lnd_location 
                    WHERE CAST(latitude AS DECIMAL(10,8)) BETWEEN :lat_min AND :lat_max 
                    AND CAST(longtitude AS DECIMAL(11,8)) BETWEEN :lng_min AND :lng_max 
                    AND void = 0 
                    ORDER BY location_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':lat_min', $lat_min, PDO::PARAM_STR);
            $stmt->bindParam(':lat_max', $lat_max, PDO::PARAM_STR);
            $stmt->bindParam(':lng_min', $lng_min, PDO::PARAM_STR);
            $stmt->bindParam(':lng_max', $lng_max, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get locations by coordinates error: " . $e->getMessage());
            return [];
        }
    }

    // Update - Update location information
    public function updateLocation($location_id, $location_name, $latitude, $longtitude)
    {
        try {
            $sql = "UPDATE lnd_location 
                    SET location_name = :location_name, latitude = :latitude, longtitude = :longtitude
                    WHERE location_id = :location_id AND void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->bindParam(':location_name', $location_name, PDO::PARAM_STR);
            $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
            $stmt->bindParam(':longtitude', $longtitude, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update location error: " . $e->getMessage());
            return false;
        }
    }

    // Delete - Soft delete (set void = 1)
    public function deleteLocation($location_id)
    {
        try {
            $sql = "UPDATE lnd_location SET void = 1 WHERE location_id = :location_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete location error: " . $e->getMessage());
            return false;
        }
    }

    // Delete - Hard delete (permanently remove from database)
    public function hardDeleteLocation($location_id)
    {
        try {
            $sql = "DELETE FROM lnd_location WHERE location_id = :location_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Hard delete location error: " . $e->getMessage());
            return false;
        }
    }

    // Restore - Restore soft deleted location (set void = 0)
    public function restoreLocation($location_id)
    {
        try {
            $sql = "UPDATE lnd_location SET void = 0 WHERE location_id = :location_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Restore location error: " . $e->getMessage());
            return false;
        }
    }

    // Get deleted locations (void = 1)
    public function getDeletedLocations()
    {
        try {
            $sql = "SELECT * FROM lnd_location WHERE void = 1 ORDER BY location_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get deleted locations error: " . $e->getMessage());
            return [];
        }
    }

    // Check if location_id exists
    public function locationExists($location_id)
    {
        try {
            $sql = "SELECT COUNT(*) FROM lnd_location WHERE location_id = :location_id";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':location_id', $location_id, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Location exists check error: " . $e->getMessage());
            return false;
        }
    }

    // Check if location name exists
    public function locationNameExists($location_name, $exclude_location_id = null)
    {
        try {
            if ($exclude_location_id) {
                $sql = "SELECT COUNT(*) FROM lnd_location WHERE location_name = :location_name AND location_id != :exclude_location_id";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':location_name', $location_name, PDO::PARAM_STR);
                $stmt->bindParam(':exclude_location_id', $exclude_location_id, PDO::PARAM_STR);
            } else {
                $sql = "SELECT COUNT(*) FROM lnd_location WHERE location_name = :location_name";
                $stmt = $this->conn->getConnection()->prepare($sql);
                $stmt->bindParam(':location_name', $location_name, PDO::PARAM_STR);
            }
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Location name exists check error: " . $e->getMessage());
            return false;
        }
    }

    // Get location count
    public function getLocationCount()
    {
        try {
            $sql = "SELECT COUNT(*) FROM lnd_location WHERE void = 0";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Get location count error: " . $e->getMessage());
            return 0;
        }
    }

    // Search locations (by name or coordinates)
    public function searchLocations($keyword)
    {
        try {
            $keyword = '%' . $keyword . '%';
            $sql = "SELECT * FROM lnd_location 
                    WHERE (location_id LIKE :keyword1 
                           OR location_name LIKE :keyword2 
                           OR latitude LIKE :keyword3 
                           OR longtitude LIKE :keyword4) 
                    AND void = 0 
                    ORDER BY location_id";
            
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->bindParam(':keyword1', $keyword, PDO::PARAM_STR);
            $stmt->bindParam(':keyword2', $keyword, PDO::PARAM_STR);
            $stmt->bindParam(':keyword3', $keyword, PDO::PARAM_STR);
            $stmt->bindParam(':keyword4', $keyword, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search locations error: " . $e->getMessage());
            return [];
        }
    }

    // Get locations for dropdown/select options
    public function getLocationOptions()
    {
        try {
            $sql = "SELECT location_id, location_name FROM lnd_location WHERE void = 0 ORDER BY location_name";
            $stmt = $this->conn->getConnection()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get location options error: " . $e->getMessage());
            return [];
        }
    }

    // Validate coordinates format
    public function validateCoordinates($latitude, $longtitude)
    {
        // Check if coordinates are valid decimal numbers
        if (!is_numeric($latitude) || !is_numeric($longtitude)) {
            return false;
        }
        
        // Check latitude range (-90 to 90)
        if ($latitude < -90 || $latitude > 90) {
            return false;
        }
        
        // Check longitude range (-180 to 180)
        if ($longtitude < -180 || $longtitude > 180) {
            return false;
        }
        
        return true;
    }

    // Calculate distance between two points (in kilometers)
    public function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earth_radius = 6371; // Earth's radius in kilometers
        
        $lat1_rad = deg2rad($lat1);
        $lng1_rad = deg2rad($lng1);
        $lat2_rad = deg2rad($lat2);
        $lng2_rad = deg2rad($lng2);
        
        $delta_lat = $lat2_rad - $lat1_rad;
        $delta_lng = $lng2_rad - $lng1_rad;
        
        $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
             cos($lat1_rad) * cos($lat2_rad) *
             sin($delta_lng / 2) * sin($delta_lng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earth_radius * $c;
    }

    // Find nearest locations to given coordinates
    public function findNearestLocations($latitude, $longtitude, $limit = 5)
    {
        try {
            $locations = $this->getAllLocations();
            $distances = [];
            
            foreach ($locations as $location) {
                $distance = $this->calculateDistance(
                    $latitude, 
                    $longtitude, 
                    floatval($location['latitude']), 
                    floatval($location['longtitude'])
                );
                
                $location['distance'] = round($distance, 2);
                $distances[] = $location;
            }
            
            // Sort by distance
            usort($distances, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });
            
            return array_slice($distances, 0, $limit);
        } catch (Exception $e) {
            error_log("Find nearest locations error: " . $e->getMessage());
            return [];
        }
    }
}
?>