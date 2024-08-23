<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$mobile = $jsonData->mobile;

$sql = "SELECT * FROM `RestaurantMaster` where `Mobile` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mobile);
$stmt->execute();
$restResult = $stmt->get_result();
$rowCount = mysqli_num_rows($restResult);
if($rowCount == 0){
	$output = array('code' => 404, 'message' => 'Invalid Mobile');
	echo json_encode($output);
}
else{
	$restRow = mysqli_fetch_assoc($restResult);
	$isApprove = $restRow["Approve"];
	if($isApprove == 0){
		$output = array('code' => 401, 'message' => 'Pending for approval');
		echo json_encode($output);
	}
	else if($isApprove == 2){
		$output = array('code' => 401, 'message' => 'Rejected by admin');
		echo json_encode($output);
	}
	else{
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
			$appName = "MealsFly - Restaurant";
			$msgStatus = $classObj->sendOtp($randomOtp, $mobile, $appName);
		}
		if($msgStatus){
			$sql = "UPDATE `RestaurantMaster` set `OTP` = ?, `IsOTPExpired` = 0 where `Mobile` = ?";
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
}
?>
