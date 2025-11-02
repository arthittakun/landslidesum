<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

include_once '../../database/connect.php';

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Get total readings count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_readings FROM lnd_environment");
    $stmt->execute();
    $totalReadings = $stmt->fetch(PDO::FETCH_ASSOC)['total_readings'];
    
    // Get landslide alerts count
    $stmt = $pdo->prepare("SELECT COUNT(*) as landslide_alerts FROM lnd_environment WHERE landslide = 1");
    $stmt->execute();
    $landslideAlerts = $stmt->fetch(PDO::FETCH_ASSOC)['landslide_alerts'];
    
    // Get flood alerts count
    $stmt = $pdo->prepare("SELECT COUNT(*) as flood_alerts FROM lnd_environment WHERE floot = 1");
    $stmt->execute();
    $floodAlerts = $stmt->fetch(PDO::FETCH_ASSOC)['flood_alerts'];
    
    // Get temperature statistics
    $stmt = $pdo->prepare("
        SELECT 
            AVG(temp) as avg_temp,
            MIN(temp) as min_temp,
            MAX(temp) as max_temp
        FROM lnd_environment 
        WHERE datekey >= CURDATE() - INTERVAL 30 DAY
    ");
    $stmt->execute();
    $tempStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get humidity statistics
    $stmt = $pdo->prepare("
        SELECT 
            AVG(humid) as avg_humid,
            MIN(humid) as min_humid,
            MAX(humid) as max_humid
        FROM lnd_environment 
        WHERE datekey >= CURDATE() - INTERVAL 30 DAY
    ");
    $stmt->execute();
    $humidStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get rain statistics
    $stmt = $pdo->prepare("
        SELECT 
            AVG(rain) as avg_rain,
            MIN(rain) as min_rain,
            MAX(rain) as max_rain,
            SUM(rain) as total_rain
        FROM lnd_environment 
        WHERE datekey >= CURDATE() - INTERVAL 30 DAY
    ");
    $stmt->execute();
    $rainStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get readings per day (last 7 days)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(datekey) as date,
            COUNT(*) as readings_count
        FROM lnd_environment 
        WHERE datekey >= CURDATE() - INTERVAL 7 DAY
        GROUP BY DATE(datekey)
        ORDER BY DATE(datekey) DESC
    ");
    $stmt->execute();
    $dailyReadings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_readings' => (int)$totalReadings,
            'landslide_alerts' => (int)$landslideAlerts,
            'flood_alerts' => (int)$floodAlerts,
            'temperature' => [
                'avg' => round((float)$tempStats['avg_temp'], 2),
                'min' => round((float)$tempStats['min_temp'], 2),
                'max' => round((float)$tempStats['max_temp'], 2)
            ],
            'humidity' => [
                'avg' => round((float)$humidStats['avg_humid'], 2),
                'min' => round((float)$humidStats['min_humid'], 2),
                'max' => round((float)$humidStats['max_humid'], 2)
            ],
            'rain' => [
                'avg' => round((float)$rainStats['avg_rain'], 2),
                'min' => round((float)$rainStats['min_rain'], 2),
                'max' => round((float)$rainStats['max_rain'], 2),
                'total' => round((float)$rainStats['total_rain'], 2)
            ],
            'daily_readings' => $dailyReadings
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
