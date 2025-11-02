<?php
include("conn.php"); 

$device_id = $_REQUEST['device_id'] ?? '';
$min_position = 0;
$max_position = 10;

if (!empty($device_id)) {
    $sql = "SELECT * FROM lnd_rain_status WHERE device_id='$device_id' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $min_position = intval($row['min_position']);
        $max_position = intval($row['max_position']);
    }
}

// ส่งออกเป็น JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    "min_position" => $min_position,
    "max_position" => $max_position
]);
?>
