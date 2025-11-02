<?php

require_once __DIR__ . '/../../../database/table_device.php';
require_once __DIR__ . '/../../../database/table_notification.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set timezone
date_default_timezone_set('Asia/Bangkok');

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] Offline Checker: $message");
}

try {
    logMessage("Starting offline device check");
    
    // สร้าง instances
    $deviceTable = new Table_device();
    $notificationTable = new Table_notification();
    
    // ดึงรายการอุปกรณ์ทั้งหมด
    $devices = $deviceTable->getDeviceStatusList();
    
    if (empty($devices)) {
        logMessage("No devices found");
        echo json_encode([
            'status' => 'success',
            'message' => 'ไม่พบอุปกรณ์ในระบบ',
            'checked_devices' => 0,
            'offline_devices' => 0,
            'notifications_sent' => 0,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $checkedDevices = 0;
    $offlineDevices = 0;
    $notificationsSent = 0;
    $offlineDevicesList = [];
    
    foreach ($devices as $device) {
        $checkedDevices++;
        $deviceId = $device['device_id'];
        $deviceName = $device['device_name'] ?? "อุปกรณ์ $deviceId";
        $locationName = $device['location_name'] ?? 'ไม่ระบุตำแหน่ง';
        $lastUpdate = $device['last_update'];
        
        logMessage("Checking device: $deviceId ($deviceName)");
        
        // ตรวจสอบว่าอุปกรณ์ออฟไลน์หรือไม่
        $isOffline = false;
        $minutesSinceUpdate = $device['minutes_since_update'];
        
        if ($minutesSinceUpdate === null || $minutesSinceUpdate > 90) {
            $isOffline = true;
            $offlineDevices++;
            
            $offlineDevicesList[] = [
                'device_id' => $deviceId,
                'device_name' => $deviceName,
                'location_name' => $locationName,
                'last_update' => $lastUpdate,
                'minutes_offline' => $minutesSinceUpdate,
                'hours_offline' => $minutesSinceUpdate ? round($minutesSinceUpdate / 60, 1) : null
            ];
            
            logMessage("Device $deviceId is OFFLINE (last update: $lastUpdate, minutes: $minutesSinceUpdate)");
            
            // ตรวจสอบว่าเคยส่งการแจ้งเตือนแล้วหรือไม่ (ใน 2 ชั่วโมงที่ผ่านมา)
            $recentNotifications = $notificationTable->getNotificationsByDevice($deviceId, 10);
            $alreadyNotified = false;
            
            foreach ($recentNotifications as $notification) {
                $notificationTime = new DateTime($notification['create_at']);
                $currentTime = new DateTime();
                $timeDiff = $currentTime->diff($notificationTime);
                $hoursDiff = ($timeDiff->days * 24) + $timeDiff->h;
                
                // ถ้าเคยส่งการแจ้งเตือนเรื่องออฟไลน์ไปแล้วใน 2 ชั่วโมงที่ผ่านมา
                if ($hoursDiff < 2 && strpos($notification['text'], 'ออฟไลน์') !== false) {
                    $alreadyNotified = true;
                    break;
                }
            }
            
            // ส่งการแจ้งเตือนถ้ายังไม่เคยส่ง
            if (!$alreadyNotified) {
                $hoursOffline = $minutesSinceUpdate ? round($minutesSinceUpdate / 60, 1) : 'ไม่ทราบ';
                $notificationText = "อุปกรณ์ $deviceName ที่ตำแหน่ง $locationName ออฟไลน์แล้ว $hoursOffline ชั่วโมง กรุณาตรวจสอบ";
                
                $success = $notificationTable->addNotification($deviceId, 2, $notificationText); // type 2 = warning
                
                if ($success) {
                    $notificationsSent++;
                    logMessage("Sent offline notification for device $deviceId");
                } else {
                    logMessage("Failed to send notification for device $deviceId");
                }
            } else {
                logMessage("Notification already sent recently for device $deviceId");
            }
        } else {
            logMessage("Device $deviceId is ONLINE (last update: $lastUpdate, minutes: $minutesSinceUpdate)");
        }
    }
    
    logMessage("Offline check completed. Checked: $checkedDevices, Offline: $offlineDevices, Notifications sent: $notificationsSent");
    
    // ส่งผลลัพธ์
    echo json_encode([
        'status' => 'success',
        'message' => "ตรวจสอบอุปกรณ์เสร็จสิ้น",
        'summary' => [
            'checked_devices' => $checkedDevices,
            'offline_devices' => $offlineDevices,
            'online_devices' => $checkedDevices - $offlineDevices,
            'notifications_sent' => $notificationsSent,
            'offline_percentage' => $checkedDevices > 0 ? round(($offlineDevices / $checkedDevices) * 100, 1) : 0
        ],
        'offline_devices' => $offlineDevicesList,
        'timestamp' => date('Y-m-d H:i:s'),
        'next_check' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการตรวจสอบอุปกรณ์ออฟไลน์',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>
