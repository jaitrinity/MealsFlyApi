<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$itemId = $jsonData->itemId;
$isEnable = $jsonData->isEnable;
$status = $isEnable == 1 ? "Enable" : "Disable";

$sql = "UPDATE `ItemMaster` set `IsEnable` = ? where `ItemId` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $isEnable, $itemId);
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