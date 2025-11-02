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
            'alert_counts' => []
        ],
        'message' => 'Data loaded successfully'
    ];

    // Get device counts from lnd_device and lnd_status
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

    // Get location counts from lnd_location
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

    // Get environment data counts from lnd_environment
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_readings FROM lnd_environment");
        $stmt->execute();
        $env_counts = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['data']['environment_counts'] = [
            'total_readings' => (int)$env_counts['total_readings']
        ];
    } catch (Exception $e) {
        $response['data']['environment_counts'] = ['total_readings' => 0];
    }

    // Get alert counts (critical alerts based on landslide and flood flags)
    try {
        $stmt = $pdo->prepare("SELECT 
            COUNT(*) as total_records,
            COUNT(CASE WHEN landslide = 1 OR floot = 1 THEN 1 END) as total_alerts,
            COUNT(CASE WHEN landslide = 1 THEN 1 END) as landslide_alerts,
            COUNT(CASE WHEN floot = 1 THEN 1 END) as flood_alerts
            FROM lnd_environment 
            WHERE datekey = CURDATE()");
        $stmt->execute();
        $alert_counts = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['data']['alert_counts'] = [
            'total_alerts' => (int)$alert_counts['total_alerts'],
            'landslide_alerts' => (int)$alert_counts['landslide_alerts'],
            'flood_alerts' => (int)$alert_counts['flood_alerts']
        ];
    } catch (Exception $e) {
        $response['data']['alert_counts'] = ['total_alerts' => 0, 'landslide_alerts' => 0, 'flood_alerts' => 0];
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage(),
        'data' => [
            'device_counts' => ['total' => 0, 'active' => 0, 'inactive' => 0],
            'location_counts' => ['total' => 0],
            'environment_counts' => ['total_readings' => 0],
            'alert_counts' => ['total_alerts' => 0, 'landslide_alerts' => 0, 'flood_alerts' => 0]
        ]
    ];
}

echo json_encode($response);
?>
