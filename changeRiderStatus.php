<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$riderId = $jsonData->riderId;
$isLogin = $jsonData->isLogin;
$status = $isLogin == 1 ? 'Login' : 'Logout';

$sql = "UPDATE `DeliveryBoyMaster` set `Status` = ? where `RiderId` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $isLogin, $riderId);
$code = 0;
$message = "";
if($stmt->execute()){
	$code = 200;
	$message = "Successfully ".$status;
}
else{
	$code = 0;
	$message = "Something went wrong";
}
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);


?>