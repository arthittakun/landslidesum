<?php
include("conn.php");

$device_id = $_REQUEST['device_id'] ?? ''; // รับ device_id
$tilt_status = 0; // ค่าเริ่มต้น

if (!empty($device_id)) {
    $sql = "SELECT * FROM lnd_environment WHERE device_id='$device_id' ORDER BY DOCNO DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['vibration'])) {
            $vibration = intval($row['vibration']); // แปลงเป็น int
        }
    }
}

// ==== ส่งค่าออกเป็น JSON ====
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['tilt_status' => $vibration]);
?>
