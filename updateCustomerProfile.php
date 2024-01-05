<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$custId = $jsonData->custId;
$name = $jsonData->name;

$sql = "SELECT * FROM `CustomerMaster` where `CustId` = ? and `IsActive` = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $custId);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$sql = "UPDATE `CustomerMaster` set `Name` = ? where `CustId` = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $name, $custId);
	if($stmt->execute()){
		$output = array('code' => 200, 'message' => 'Profile updated');
		echo json_encode($output);
	}
	else{
		$output = array('code' => 500, 'message' => 'Something went wrong while update profile');
		echo json_encode($output);
	}
}
else{
	$output = array('code' => 404, 'message'=>'Customer id not found');
	echo json_encode($output);
}

	
?>