<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.in/html/MealsFly/log/riderVerifyOTP_1_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
$jsonData=json_decode($json);

$mobile = $jsonData->mobile;
$otp = $jsonData->otp;
$token = $jsonData->token;
$make = $jsonData->make;
$model = $jsonData->model;
$os = $jsonData->os;
$osVer = $jsonData->osVersion;
$appVer = $jsonData->appVersion;


$sql = "SELECT * FROM `DeliveryBoyMaster` where `Mobile` = ? and `OTP` = ? and `IsOTPExpired` = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $mobile, $otp);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$row = mysqli_fetch_assoc($query);
	$riderId = $row["RiderId"];
	$responseData = array(
		'riderId' => $riderId, 
		'name' => $row["Name"],
		'status' => $row["Status"]
	);


	$output = array(
		'code' => 200, 
		'message' => 'Valid OTP', 
		'riderInfo' => $responseData
	);
	echo json_encode($output);

	$sql = "UPDATE `DeliveryBoyMaster` set `IsOTPExpired` = 1 where `Mobile` = ? and `OTP` = ? and `IsOTPExpired` = 0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $mobile, $otp);
	$stmt->execute();

	if($token !=null && $token != ''){
		$deviceSql = "SELECT * FROM `Device` where `Mobile` = ? and `UserId` = $riderId and `AppName` = 3";
		$stmt = $conn->prepare($deviceSql);
		$stmt->bind_param("s", $mobile);
		$stmt->execute();
		$deviceQuery = $stmt->get_result();
		if(mysqli_num_rows($deviceQuery) != 0){
			$updateDevice = "UPDATE `Device` SET `Token`=?, `FcmToken`=?, `Make`=?, `Model`=?, `OS`=?, `OSVer`=?, `AppVer`=?, `UpdateDate`= current_timestamp WHERE `Mobile` = ? and `UserId` = $riderId and `AppName` = 3";
			$stmt = $conn->prepare($updateDevice);
			$stmt->bind_param("ssssssss", $token->deviceToken, $token->fcmToken, $make, $model, $os, $osVer, $appVer, $mobile);
			$stmt->execute();
		}
		else{
			$insertDevice = "INSERT INTO `Device`(`Mobile`, `Token`, `FcmToken`, `Make`, `Model`, `OS`, `OSVer`, `AppVer`, `UserId`, `AppName`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, $riderId, 3)";
			$stmt = $conn->prepare($insertDevice);
			$stmt->bind_param("ssssssss", $mobile, $token->deviceToken, $token->fcmToken, $make, $model, $os, $osVer, $appVer);
			$stmt->execute();
		}
	}	

}
else{
	$output = array('code' => 404, 'message'=>'Invalid OTP');
	echo json_encode($output);
}

?>