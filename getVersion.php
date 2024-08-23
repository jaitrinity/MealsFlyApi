<?php
include("dbConfiguration.php");
$app = $_REQUEST["app"];
$sql = "SELECT `Android`, `Ios` FROM `Version` where `AppName` = $app";
$query= mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($query);
$android = explode(";",$row['Android']);
$ios = explode(";",$row['Ios']);
$output = array(
	'andVer'=>$android[0],
	'andForce'=>$android[1],
	'iosVer'=>$ios[0],
	'iosForce'=>$ios[1]
);
echo json_encode($output);

file_put_contents('/var/www/trinityapplab.in/html/MealsFly/log/version_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$output."\n", FILE_APPEND);

?>