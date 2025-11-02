<?

/* 
// chiangrai_smartfarm
//http://27.254.66.29/phpmyadmin/index.php?route=/database/structure&server=1&db=chiangrai_smartfarm&table=iot_environment 
 
//header('Access-Control-Allow-Origin:*');
include("conn.php"); //เรียกใช้ conn.php เพื่อติดต่อ database

$device_id 	= $_REQUEST['device_id'];		// อุณหภูมิ
$timer=15;
$sql= "SELECT * FROM lnd_device WHERE device_id='$device_id' LIMIT 1 ";
//echo $sql;
$result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['timer'])) {
            $timer = intval($row['timer']); // แปลงเป็น int

        }
    }


	//echo $timer;
	// ส่งกลับเป็น JSON
//echo json_encode(["timer" => $timer,"z1"=>$z1,"z2"=>$z2,"z3"=>$z3]);
echo json_encode(["timer" => $timer]);
*/
 
include("conn.php"); 

$device_id = $_REQUEST['device_id'] ?? ''; // รับ device_id
$timer = 15; // ค่า default

if (!empty($device_id)) {
    $sql = "SELECT * FROM lnd_device WHERE device_id='$device_id' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (!empty($row['timer'])) {
            $timer = intval($row['timer']); // แปลงเป็น int
        }
    }
}

// ส่งค่ากลับเป็นตัวเลขตรงๆ
header('Content-Type: text/plain'); // ส่งเป็น plain text
echo $timer;
?>


 