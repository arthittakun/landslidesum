<?php
/**
 * Manual trigger for testing offline device checker
 * สำหรับทดสอบการทำงานของ offline checker ด้วยตนเอง
 */

require_once __DIR__ . '/../../../auth/Auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // ตรวจสอบ authentication (เฉพาะการทดสอบ)
    $auth = new Auth();
    $user = $auth->requireAuth();
    
    echo "<h2>🔍 Manual Offline Device Checker Test</h2>";
    echo "<p><strong>Triggered by:</strong> " . ($user['username'] ?? 'Unknown') . "</p>";
    echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "<hr>";
    
    // เรียกใช้ offline checker
    $checkerUrl = 'offline-checker.php';
    $checkerPath = __DIR__ . '/offline-checker.php';
    
    if (!file_exists($checkerPath)) {
        throw new Exception('Offline checker file not found');
    }
    
    echo "<h3>📋 Executing Offline Checker...</h3>";
    
    // Execute the offline checker and capture output
    ob_start();
    include $checkerPath;
    $output = ob_get_clean();
    
    // Try to decode as JSON
    $result = json_decode($output, true);
    
    if ($result) {
        echo "<div style='background: #f0f8f0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ Result:</h4>";
        echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        echo "</div>";
        
        if ($result['status'] === 'success') {
            $summary = $result['summary'] ?? [];
            echo "<h3>📊 Summary:</h3>";
            echo "<ul>";
            echo "<li>🔍 <strong>Devices Checked:</strong> " . ($summary['checked_devices'] ?? 0) . "</li>";
            echo "<li>🟢 <strong>Online Devices:</strong> " . ($summary['online_devices'] ?? 0) . "</li>";
            echo "<li>🔴 <strong>Offline Devices:</strong> " . ($summary['offline_devices'] ?? 0) . "</li>";
            echo "<li>📢 <strong>Notifications Sent:</strong> " . ($summary['notifications_sent'] ?? 0) . "</li>";
            echo "<li>📈 <strong>Offline Percentage:</strong> " . ($summary['offline_percentage'] ?? 0) . "%</li>";
            echo "</ul>";
            
            if (!empty($result['offline_devices'])) {
                echo "<h3>🚨 Offline Devices:</h3>";
                echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background: #f5f5f5;'>";
                echo "<th>Device ID</th><th>Device Name</th><th>Location</th><th>Hours Offline</th><th>Last Update</th>";
                echo "</tr>";
                
                foreach ($result['offline_devices'] as $device) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($device['device_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($device['device_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($device['location_name']) . "</td>";
                    echo "<td style='color: red; font-weight: bold;'>" . ($device['hours_offline'] ?? 'N/A') . " hrs</td>";
                    echo "<td>" . htmlspecialchars($device['last_update'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: green;'>🎉 <strong>All devices are online!</strong></p>";
            }
        }
    } else {
        echo "<div style='background: #fff8f0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>⚠️ Raw Output:</h4>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        echo "</div>";
    }
    
    echo "<hr>";
    echo "<h3>ℹ️ Instructions for Cronjob Setup:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Add this to your crontab to run every 30 minutes:</strong></p>";
    echo "<code style='background: #e9ecef; padding: 5px; border-radius: 3px;'>";
    echo "*/30 * * * * /usr/bin/php " . realpath($checkerPath);
    echo "</code>";
    echo "<p style='margin-top: 10px;'><strong>Or via wget/curl:</strong></p>";
    echo "<code style='background: #e9ecef; padding: 5px; border-radius: 3px;'>";
    echo "*/30 * * * * wget -q -O /dev/null https://landslide-alerts.com/api/cronjob/offline-checker.php";
    echo "</code>";
    echo "</div>";
    
    echo "<p style='margin-top: 20px; color: #666;'><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffe6e6; padding: 15px; border-radius: 5px; color: #d63384;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
