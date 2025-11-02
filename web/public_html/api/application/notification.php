<?php
// Notification API for Application
// API การแจ้งเตือนสำหรับ Application

require_once __DIR__ . '/../../../database/notification_functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$notificationFunctions = new NotificationFunctions();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetRequest();
            break;
        case 'POST':
            handlePostRequest();
            break;
        default:
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not allowed'
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}

function handleGetRequest() {
    global $notificationFunctions;
    
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            // Get notifications for app (filtered by time)
            $minutes = isset($_GET['minutes']) ? (int)$_GET['minutes'] : 15;
            $notifications = $notificationFunctions->getNotificationsForApp($minutes);
            
            echo json_encode([
                'status' => 'success',
                'data' => $notifications,
                'message' => 'Notifications retrieved successfully',
                'filter_minutes' => $minutes
            ]);
            break;
            
        case 'device':
            // Get notifications by device
            $device_id = $_GET['device_id'] ?? null;
            if (!$device_id) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Device ID is required'
                ]);
                return;
            }
            
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $notifications = $notificationFunctions->getNotificationsByDevice($device_id, $limit);
            
            echo json_encode([
                'status' => 'success',
                'data' => $notifications,
                'message' => 'Device notifications retrieved successfully',
                'device_id' => $device_id
            ]);
            break;
            
        case 'location':
            // Get notifications by location
            $location_id = $_GET['location_id'] ?? null;
            if (!$location_id) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Location ID is required'
                ]);
                return;
            }
            
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $notifications = $notificationFunctions->getNotificationsByLocation($location_id, $limit);
            
            echo json_encode([
                'status' => 'success',
                'data' => $notifications,
                'message' => 'Location notifications retrieved successfully',
                'location_id' => $location_id
            ]);
            break;
            
        case 'count':
            // Get notification count by type
            $type = isset($_GET['type']) ? (int)$_GET['type'] : null;
            $count = $notificationFunctions->getNotificationCountByType($type);
            
            echo json_encode([
                'status' => 'success',
                'data' => $count,
                'message' => 'Notification count retrieved successfully'
            ]);
            break;
            
        case 'unread':
            // Get unread notification count
            $last_read_id = isset($_GET['last_read_id']) ? (int)$_GET['last_read_id'] : 0;
            $count = $notificationFunctions->getUnreadNotificationCount($last_read_id);
            
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'unread_count' => $count,
                    'last_read_id' => $last_read_id
                ],
                'message' => 'Unread count retrieved successfully'
            ]);
            break;
            
        case 'stats':
            // Get notification statistics
            $days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
            $stats = $notificationFunctions->getNotificationStats($days);
            
            echo json_encode([
                'status' => 'success',
                'data' => $stats,
                'message' => 'Notification statistics retrieved successfully',
                'period_days' => $days
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action parameter'
            ]);
            break;
    }
}

function handlePostRequest() {
    global $notificationFunctions;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid JSON input'
        ]);
        return;
    }
    
    $action = $input['action'] ?? 'add';
    
    switch ($action) {
        case 'add':
            // Add new notification
            $device_id = $input['device_id'] ?? null;
            $type = $input['type'] ?? null;
            $text = $input['text'] ?? null;
            $location_id = $input['location_id'] ?? null;
            
            if (!$device_id || !$type || !$text) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Device ID, type, and text are required'
                ]);
                return;
            }
            
            $result = $notificationFunctions->addNotification($device_id, $type, $text, $location_id);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Notification added successfully',
                    'data' => [
                        'device_id' => $device_id,
                        'type' => $type,
                        'text' => $text
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to add notification'
                ]);
            }
            break;
            
        case 'mark_read':
            // Mark notifications as read
            $notification_ids = $input['notification_ids'] ?? null;
            
            if (!$notification_ids || !is_array($notification_ids)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Notification IDs array is required'
                ]);
                return;
            }
            
            $result = $notificationFunctions->markNotificationsAsRead($notification_ids);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Notifications marked as read successfully',
                    'data' => [
                        'marked_count' => count($notification_ids)
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to mark notifications as read'
                ]);
            }
            break;
            
        case 'cleanup':
            // Delete old notifications
            $days = $input['days'] ?? 30;
            $result = $notificationFunctions->deleteOldNotifications($days);
            
            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Old notifications cleaned up successfully',
                    'data' => [
                        'cleanup_days' => $days
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to cleanup old notifications'
                ]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action parameter'
            ]);
            break;
    }
}
?>
