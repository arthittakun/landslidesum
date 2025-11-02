<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../../../database/table_policy.php';

try {
    $policyTable = new table_policy();
    $response = [];

    // Get policy type from query parameter
    $policy_type = $_GET['type'] ?? null;

    if ($policy_type) {
        // Get specific policy by type
        if (!$policyTable->isValidPolicyType($policy_type)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid policy type. Valid types: term, policy, cookie, about'
            ]);
            exit;
        }

        $response = $policyTable->getPolicyByType($policy_type);
    } else {
        // Get all policies
        $response = $policyTable->getAllPolicies();
    }

    // Set appropriate HTTP status code
    if (!$response['success']) {
        http_response_code(404);
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
