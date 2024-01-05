<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$deleteType = $jsonData->deleteType;
if($deleteType == "restItem"){
	$itemId = $jsonData->itemId;
	$sql = "DELETE FROM `ItemMaster` WHERE `ItemId` = $itemId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully deleted";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}

?>