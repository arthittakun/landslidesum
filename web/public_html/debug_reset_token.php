<?php
require_once '../database/table_password_reset.php';
require_once '../database/connect.php';

// เปิด error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug Reset Token</h1>";

// รับ token จาก URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo "<p style='color: red;'>❌ ไม่มี token ใน URL</p>";
    echo "<p>ใช้: ?token=YOUR_TOKEN_HERE</p>";
    exit;
}

echo "<h2>📋 Token Information</h2>";
echo "<p><strong>Token:</strong> " . htmlspecialchars($token) . "</p>";
echo "<p><strong>Token Length:</strong> " . strlen($token) . " characters</p>";

// ทดสอบการเชื่อมต่อฐานข้อมูล
echo "<h2>🔌 Database Connection Test</h2>";
try {
    $db = new database();
    $connection = $db->getConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// ตรวจสอบ table password_resets
echo "<h2>📊 Table Check</h2>";
try {
    $sql = "SHOW TABLES LIKE 'password_resets'";
    $stmt = $connection->prepare($sql);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Table 'password_resets' exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Table 'password_resets' does not exist</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Table check failed: " . $e->getMessage() . "</p>";
}

// ตรวจสอบ token ในฐานข้อมูล
echo "<h2>🔍 Token Database Check</h2>";
try {
    $sql = "SELECT * FROM password_resets WHERE token = :token";
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✅ Token found in database</p>";
        echo "<pre>" . print_r($tokenData, true) . "</pre>";
        
        // ตรวจสอบสถานะ
        $now = new DateTime();
        $expiresAt = new DateTime($tokenData['expires_at']);
        $isExpired = $expiresAt < $now;
        $isUsed = $tokenData['used'] == 1;
        
        echo "<h3>📅 Token Status</h3>";
        echo "<p><strong>Current Time:</strong> " . $now->format('Y-m-d H:i:s') . "</p>";
        echo "<p><strong>Expires At:</strong> " . $tokenData['expires_at'] . "</p>";
        echo "<p><strong>Is Expired:</strong> " . ($isExpired ? 'Yes ❌' : 'No ✅') . "</p>";
        echo "<p><strong>Is Used:</strong> " . ($isUsed ? 'Yes ❌' : 'No ✅') . "</p>";
        
        if (!$isExpired && !$isUsed) {
            echo "<p style='color: green;'>✅ Token is valid and can be used</p>";
        } else {
            echo "<p style='color: red;'>❌ Token cannot be used</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Token not found in database</p>";
        
        // แสดง tokens ทั้งหมดเพื่อ debug
        echo "<h3>🔍 All Tokens in Database</h3>";
        $sql = "SELECT id, email, token, expires_at, used, created_at FROM password_resets ORDER BY created_at DESC LIMIT 10";
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Email</th><th>Token (first 16 chars)</th><th>Expires</th><th>Used</th><th>Created</th></tr>";
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars(substr($row['token'], 0, 16)) . "...</td>";
                echo "<td>" . $row['expires_at'] . "</td>";
                echo "<td>" . ($row['used'] ? 'Yes' : 'No') . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No tokens found in database</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Token check failed: " . $e->getMessage() . "</p>";
}

// ทดสอบ Table_password_reset class
echo "<h2>🧪 Class Test</h2>";
try {
    $passwordReset = new Table_password_reset();
    echo "<p style='color: green;'>✅ Table_password_reset class instantiated successfully</p>";
    
    // ทดสอบ verifyResetToken method
    $result = $passwordReset->verifyResetToken($token);
    if ($result) {
        echo "<p style='color: green;'>✅ verifyResetToken() returned data</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ verifyResetToken() returned false</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Class test failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// ตรวจสอบ PHP error log
echo "<h2>📝 Error Log Check</h2>";
$errorLogPath = ini_get('error_log');
if ($errorLogPath) {
    echo "<p><strong>Error Log Path:</strong> " . $errorLogPath . "</p>";
    
    if (file_exists($errorLogPath)) {
        $lastLines = file($errorLogPath);
        $recentErrors = array_slice($lastLines, -10);
        
        echo "<h3>Recent Error Log Entries:</h3>";
        echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: auto;'>";
        foreach ($recentErrors as $line) {
            if (strpos($line, 'password_reset') !== false || strpos($line, 'reset') !== false) {
                echo htmlspecialchars($line);
            }
        }
        echo "</pre>";
    } else {
        echo "<p>Error log file not found</p>";
    }
} else {
    echo "<p>Error log path not configured</p>";
}

echo "<hr>";
echo "<p><strong>Debug completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
