<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.in/html/MealsFly/log/restaurantVerifyOTP_1_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
$jsonData=json_decode($json);

$mobile = $jsonData->mobile;
$otp = $jsonData->otp;
$token = $jsonData->token;
$make = $jsonData->make;
$model = $jsonData->model;
$os = $jsonData->os;
$osVer = $jsonData->osVersion;
$appVer = $jsonData->appVersion;


$sql = "SELECT * FROM `RestaurantMaster` where `Mobile` = ? and `OTP` = ? and `IsOTPExpired` = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $mobile, $otp);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$row = mysqli_fetch_assoc($query);
	$restId = $row["RestId"];

	$sql1 = "SELECT DISTINCT c.* FROM ItemMaster i join CategoryMaster c on i.CatId = c.CatId where i.RestId = $restId and i.IsEnable = 1 and i.IsEditable = 0";
	$result1 = mysqli_query($conn,$sql1);
	$catList = array();
	while($row1 = mysqli_fetch_assoc($result1)){
		$catName = $row1["Name"];
		array_push($catList, $catName);
	}

	$responseData = array(
		'restId' => $restId, 
		'name' => $row["Name"],
		'address' => $row["Address"],
		'latLong' => $row["LatLong"],
		'pincode' => $row["Pincode"],
		'image' => $row["Image"],
		'banner' => $row["Banner"],
		'openTime' => $row["OpenTime"],
		'closeTime' => $row["CloseTime"],
		'status' => $row["Status"],
		'category' => implode(",", $catList)
	);

	$output = array(
		'code' => 200, 
		'message' => 'Valid OTP', 
		'restaurantInfo' => $responseData
	);
	echo json_encode($output);

	$sql = "UPDATE `RestaurantMaster` set `IsOTPExpired` = 1 where `Mobile` = ? and `OTP` = ? and `IsOTPExpired` = 0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("si", $mobile, $otp);
	$stmt->execute();

	if($token !=null && $token != ''){
		$deviceSql = "SELECT * FROM `Device` where `Mobile` = ? and `UserId` = $restId and `AppName` = 2";
		$stmt = $conn->prepare($deviceSql);
		$stmt->bind_param("s", $mobile);
		$stmt->execute();
		$deviceQuery = $stmt->get_result();
		if(mysqli_num_rows($deviceQuery) != 0){
			$updateDevice = "UPDATE `Device` SET `Token`=?, `FcmToken`=?, `Make`=?, `Model`=?, `OS`=?, `OSVer`=?, `AppVer`=?, `UpdateDate`= current_timestamp WHERE `Mobile` = ? and `UserId` = $restId and `AppName` = 2";
			$stmt = $conn->prepare($updateDevice);
			$stmt->bind_param("ssssssss", $token->deviceToken, $token->fcmToken, $make, $model, $os, $osVer, $appVer, $mobile);
			$stmt->execute();
		}
		else{
			$insertDevice = "INSERT INTO `Device`(`Mobile`, `Token`, `FcmToken`, `Make`, `Model`, `OS`, `OSVer`, `AppVer`, `UserId`, `AppName`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, $restId, 2)";
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