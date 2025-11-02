<?
include("conn.php"); //เรียกใช้ conn.php เพื่อติดต่อ database
$device_id 	= $_REQUEST['device_id'];		// อุณหภูมิ
$buzzer_alert=;
$sql= "SELECT * FROM lnd_camera_config WHERE device_id='$device_id' AND status=1 LIMIT 1 ";
//echo $sql;
$result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['buzzer_alert'])) {
            $buzzer_alert = intval($row['buzzer_alert']); // แปลงเป็น int
        }
    }


	//echo $buzzer_alert;
	// ส่งกลับเป็น JSON
echo json_encode(["buzzer_alert" => $buzzer_alert]);

?>