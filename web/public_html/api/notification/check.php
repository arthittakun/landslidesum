<?php
require_once __DIR__ . '/../../../database/table_notification.php';


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$notificationTable = new Table_notification();
$notify = $notificationTable->getNotifications();

if (empty($notify) || !is_array($notify) || count($notify) === 0 ) {
    echo json_encode([
        'status' => 'success',
        'data' => [],
        'message' => 'No notifications found'
    ]);
    exit;
}
$result = [
    'status' => 'success',
    'data' => $notify,
    'message' => 'Notifications retrieved successfully'
];

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>