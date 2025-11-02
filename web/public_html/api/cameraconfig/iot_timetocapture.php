<?
include("conn.php"); //เรียกใช้ conn.php เพื่อติดต่อ database
$device_id 	= $_REQUEST['device_id'];		// อุณหภูมิ
$timetotake=30;
$sql= "SELECT * FROM lnd_camera_config WHERE device_id='$device_id' AND status=1 LIMIT 1 ";
//echo $sql;
$result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['timetotake'])) {
            $timetotake = intval($row['timetotake']); // แปลงเป็น int
        }
    }


	//echo $timetotake;
	// ส่งกลับเป็น JSON
echo json_encode(["timetotake" => $timetotake]);

?>