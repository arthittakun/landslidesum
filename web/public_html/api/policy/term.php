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
    
    // Get both policy and term
    $policyResponse = $policyTable->getPolicyByType('policy');
    $termResponse = $policyTable->getPolicyByType('term');
    
    if (!$policyResponse['success'] || !$termResponse['success']) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Policy or Terms not found'
        ]);
        exit;
    }
    
    // Combine the content
    $combinedContent = $policyResponse['data']['policy_text'] . '<br><br>' . $termResponse['data']['policy_text'];
    
    // Get the latest update time
    $policyUpdateTime = strtotime($policyResponse['data']['updated_at']);
    $termUpdateTime = strtotime($termResponse['data']['updated_at']);
    $latestUpdateTime = max($policyUpdateTime, $termUpdateTime);
    
    $response = [
        'success' => true,
        'message' => $combinedContent,
        'updated_at' => date('Y-m-d H:i:s', $latestUpdateTime),
        'data' => [
            'policy' => $policyResponse['data'],
            'term' => $termResponse['data']
        ]
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
