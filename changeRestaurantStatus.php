<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$restId = $jsonData->restId;
$isOpen = $jsonData->isOpen;
$status = $isOpen == 1 ? 'Open' : 'Close';

$sql = "UPDATE `RestaurantMaster` set `Status` = ? where `RestId` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $restId);
$code = 0;
$message = "";
if($stmt->execute()){
	$code = 200;
	$message = "Successfully status ".$status;
}
else{
	$code = 0;
	$message = "Something went wrong";
}
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);

?>