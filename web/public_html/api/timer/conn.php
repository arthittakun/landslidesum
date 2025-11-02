
<?php
	header("Access-Control-Allow-Origin: *");
  $conn = mysqli_connect("localhost","landslid_admin","8abS2rGkgvM4BKMzxBUJ") or die("Couldn't connect to database");
  mysqli_select_db($conn ,"landslid_db") or die("Couldn't find database");
  mysqli_query($conn,"SET NAMES UTF8");
    
?>
