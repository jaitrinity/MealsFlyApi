<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$riderId = $jsonData->riderId;
$currentLatlong = $jsonData->currentLatlong;
$sql = "UPDATE `DeliveryBoyMaster` set `CurrentLatlong` = ? where `RiderId` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $currentLatlong, $riderId);
$code = 0;
$message = "";
if($stmt->execute()){
	$code = 200;
	$message = "Successfully update";
}
else{
	$code = 0;
	$message = "Something went wrong";
}
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);
?>