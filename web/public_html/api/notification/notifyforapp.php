<?php
require_once __DIR__ . '/../../../database/table_notification.php';
require_once __DIR__ . '/../../../database/notification_functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$notificationFunctions = new NotificationFunctions();

// Get critical notifications only (flood > 0 or landslide > 0) with images
// ดึงการแจ้งเตือนวิกฤตเท่านั้น (น้ำท่วม > 0 หรือดินถล่ม > 0) พร้อมรูปภาพ
$notify = $notificationFunctions->getNotificationsForApp(15); // Last 15 minutes

if (empty($notify) || !is_array($notify) || count($notify) === 0 ) {
    echo json_encode([
        'status' => 'success',
        'data' => [],
        'message' => 'No critical notifications found'
    ]);
    exit;
}

$result = [
    'status' => 'success',
    'data' => $notify,
    'message' => 'Critical notifications retrieved successfully',
    'count' => count($notify),
    'filter' => 'Only flood > 0 or landslide > 0 notifications with images'
];

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>