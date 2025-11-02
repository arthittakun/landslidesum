<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include database connection file
require_once __DIR__ . '/../../../database/connect.php';

try {
    $db = new database();
    $pdo = $db->getConnection();
    
    $response = [
        'success' => true,
        'data' => [
            'device_counts' => [],
            'location_counts' => [],
            'environment_counts' => [],
            'alert_counts' => [],
            'hourly_averages' => []
        ],
        'message' => 'Dashboard data loaded successfully'
    ];

    // Get device counts with status breakdown
    try {
        $stmt = $pdo->prepare("SELECT 
            COUNT(d.device_id) as total,
            COUNT(CASE WHEN s.status = 1 THEN 1 END) as active,
            COUNT(CASE WHEN s.status = 0 OR s.status IS NULL THEN 1 END) as inactive
            FROM lnd_device d
            LEFT JOIN lnd_status s ON d.device_id = s.device_id
            WHERE d.void = 0");
        $stmt->execute();
        $device_counts = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['data']['device_counts'] = [
            'total' => (int)$device_counts['total'],
            'active' => (int)$device_counts['active'],
            'inactive' => (int)$device_counts['inactive']
        ];
    } catch (Exception $e) {
        $response['data']['device_counts'] = ['total' => 0, 'active' => 0, 'inactive' => 0];
    }

    // Get location counts
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM lnd_location WHERE void = 0");
        $stmt->execute();
        $location_counts = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['data']['location_counts'] = [
            'total' => (int)$location_counts['total']
        ];
    } catch (Exception $e) {
        $response['data']['location_counts'] = ['total' => 0];
    }

    // Get environment data counts
    try {
        $stmt = $pdo->prepare("SELECT 
            COUNT(*) as total_readings,
            COUNT(DISTINCT device_id) as active_devices
            FROM lnd_environment 
            WHERE datekey >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute();
        $env_counts = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['data']['environment_counts'] = [
            'total_readings' => (int)$env_counts['total_readings'],
            'active_devices' => (int)$env_counts['active_devices']
        ];
    } catch (Exception $e) {
        $response['data']['environment_counts'] = ['total_readings' => 0, 'active_devices' => 0];
    }

    // Get alert counts with landslide and flood detection
    try {
        $stmt = $pdo->prepare("SELECT 
            COUNT(*) as total_records,
            COUNT(CASE WHEN landslide = 1 OR floot = 1 THEN 1 END) as total_alerts,
            COUNT(CASE WHEN landslide = 1 THEN 1 END) as landslide_alerts,
            COUNT(CASE WHEN floot = 1 THEN 1 END) as flood_alerts
            FROM lnd_environment 
            WHERE datekey >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute();
        $alert_counts = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['data']['alert_counts'] = [
            'total_alerts' => (int)$alert_counts['total_alerts'],
            'landslide_alerts' => (int)$alert_counts['landslide_alerts'],
            'flood_alerts' => (int)$alert_counts['flood_alerts']
        ];
    } catch (Exception $e) {
        $response['data']['alert_counts'] = [
            'total_alerts' => 0,
            'landslide_alerts' => 0,
            'flood_alerts' => 0
        ];
    }

    // Get hourly averages for the last 24 hours using timekey
    try {
        $stmt = $pdo->prepare("SELECT 
            timekey,
            AVG(temp) as avg_temp,
            AVG(humid) as avg_humid,
            AVG(rain) as avg_rain,
            COUNT(*) as readings_count
            FROM lnd_environment 
            WHERE datekey = CURDATE()
            GROUP BY timekey
            ORDER BY timekey");
        $stmt->execute();
        $hourly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create hourly array (00:00 to 23:00)
        $hourly_averages = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hour_key = sprintf('%02d:00', $hour);
            $found = false;
            
            foreach ($hourly_data as $data) {
                if ($data['timekey'] === $hour_key) {
                    $hourly_averages[] = [
                        'hour' => $hour,
                        'avg_temp' => round((float)$data['avg_temp'], 1),
                        'avg_humid' => round((float)$data['avg_humid'], 1),
                        'avg_rain' => round((float)$data['avg_rain'], 1),
                        'readings_count' => (int)$data['readings_count']
                    ];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $hourly_averages[] = [
                    'hour' => $hour,
                    'avg_temp' => 0,
                    'avg_humid' => 0,
                    'avg_rain' => 0,
                    'readings_count' => 0
                ];
            }
        }
        $response['data']['hourly_averages'] = $hourly_averages;
        
    } catch (Exception $e) {
        // Provide default hourly data if query fails
        $hourly_averages = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourly_averages[] = [
                'hour' => $hour,
                'avg_temp' => 0,
                'avg_humid' => 0,
                'avg_rain' => 0,
                'readings_count' => 0
            ];
        }
        $response['data']['hourly_averages'] = $hourly_averages;
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage(),
        'data' => [
            'device_counts' => ['total' => 0, 'active' => 0, 'inactive' => 0],
            'location_counts' => ['total' => 0],
            'environment_counts' => ['total_readings' => 0, 'active_devices' => 0],
            'alert_counts' => ['total_alerts' => 0, 'landslide_alerts' => 0, 'flood_alerts' => 0],
            'hourly_averages' => array_fill(0, 24, ['hour' => 0, 'avg_temp' => 0, 'avg_humid' => 0, 'avg_rain' => 0, 'readings_count' => 0])
        ]
    ];
}

echo json_encode($response);
?>
