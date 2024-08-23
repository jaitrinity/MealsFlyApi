<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$mobile = $jsonData->mobile;

$sql = "SELECT * FROM `DeliveryBoyMaster` where `Mobile` = ? and `IsActive` = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mobile);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$randomOtp = 0;
	$msgStatus = false;
	$confiSql = "SELECT * FROM `Configuration` where `Id` = 1";
	$confiStmt = $conn->prepare($confiSql);
	$confiStmt->execute();
	$confiResult = $confiStmt->get_result();
	$confiRow = mysqli_fetch_assoc($confiResult);
	$mobileStr = $confiRow["Value"];
	$mobileArr = explode(",", $mobileStr);
	if(in_array($mobile,$mobileArr)){
		$randomOtp = 1234;	
		$msgStatus = true;
	}
	else{
		$randomOtp = rand(1000,9999);
		require_once 'SendOtpClass.php';
		$classObj = new SendOtpClass();
		$appName = "MealsFly - Rider";
		$msgStatus = $classObj->sendOtp($randomOtp, $mobile, $appName);
	}

	if($msgStatus){
		$sql = "UPDATE `DeliveryBoyMaster` set `OTP` = ?, `IsOTPExpired` = 0 where `Mobile` = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("is", $randomOtp, $mobile);
		if($stmt->execute()){
			$output = array('code' => 200, 'message' => 'OTP send to mobile');
			echo json_encode($output);
		}
		else{
			$output = array('code' => 500, 'message' => 'Something went wrong while sending OTP');
			echo json_encode($output);
		}
	}
	else{
		$output = array('code' => 500, 'message' => 'Something went wrong while sending OTP');
		echo json_encode($output);
	}
}
else{
	$output = array('code' => 404, 'message' => 'Invalid Mobile');
	echo json_encode($output);
}
?>
