<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../../../database/table_policy.php';

try {
    $policyTable = new table_policy();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['policy_type']) || !isset($input['policy_text'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: policy_type and policy_text'
        ]);
        exit;
    }
    
    $policy_type = trim($input['policy_type']);
    $policy_text = $input['policy_text'];
    
    // Validate policy type
    if (!$policyTable->isValidPolicyType($policy_type)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid policy type. Valid types: term, policy, cookie, about'
        ]);
        exit;
    }
    
    // Update policy
    $response = $policyTable->updatePolicy($policy_type, $policy_text);
    
    // Set appropriate HTTP status code
    if (!$response['success']) {
        http_response_code(400);
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
