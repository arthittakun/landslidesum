<?php
require_once __DIR__ . '/../../../database/table_environment.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$environment = new Table_environment();
$environments = $environment->displayCriticalData();

if ($environments) {
    echo json_encode([
        "status" => "success",
        "data" => $environments
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No critical environment data found"
    ]);
}   