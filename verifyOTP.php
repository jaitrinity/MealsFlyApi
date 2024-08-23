<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$mobile = $jsonData->mobile;
$otp = $jsonData->otp;
$token = $jsonData->token;
$make = $jsonData->make;
$model = $jsonData->model;
$os = $jsonData->os;
$osVer = $jsonData->osVersion;
$appVer = $jsonData->appVersion;
$latlong = $jsonData->latlong;

$sql = "SELECT * FROM `CustomerMaster` where `Mobile` = ? and `OTP` = ? and `IsOTPExpired` = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $mobile, $otp);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$row = mysqli_fetch_assoc($query);
	$custId = $row["CustId"];
	$responseData = array(
		'custId' => $custId, 
		'name' => $row["Name"],
		'email' => $row["Email"],
		'profilePic' => $row["ProfilePic"]
	);

	$sql1 = "SELECT * FROM `CustomerAddress` where `CustId` = ? and `IsDeleted` = 0 order by `CreateDate` desc";
	$stmt1 = $conn->prepare($sql1);
	$stmt1->bind_param("i", $custId);
	$stmt1->execute();
	$query1 = $stmt1->get_result();
	$addList = array();
	while($row1 = mysqli_fetch_assoc($query1)){
		$addJson = array(
			'custAddId' => $row1["CustAddId"], 
			'name' => $row1["Name"],
			'contact' => $row1["Contact"],
			'email' => $row1["Email"],
			'latLong' => $row1["LatLong"],
			'address' => $row1["Address"],
			'city' => $row1["City"],
			'pincode' => $row1["Pincode"],
			'state' => $row1["State"] 
		);
		array_push($addList, $addJson);
	}

	$sql2 = "SELECT * FROM `Configuration` where `Id` = 4";
	$stmt2 = $conn->prepare($sql2);
	$stmt2->execute();
	$query2 = $stmt2->get_result();
	$row2 = mysqli_fetch_assoc($query2);
	$pincode = $row2["Value"];
	$pincodeList = explode(",", $pincode);

	$output = array(
		'code' => 200, 
		'message' => 'Valid OTP', 
		'customerInfo' => $responseData, 
		'addressList' => $addList,
		'pincodeList' => $pincodeList
	);
	echo json_encode($output);

	$sql = "UPDATE `CustomerMaster` set `LatLong`=?, `IsOTPExpired`=1 where `Mobile`=? and `OTP`=? and `IsOTPExpired`=0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ssi", $latlong, $mobile, $otp);
	$stmt->execute();

	if($token !=null && $token != ''){
		$deviceSql = "SELECT * FROM `Device` where `Mobile` = ? and `UserId` = $custId and `AppName` = 1";
		$stmt = $conn->prepare($deviceSql);
		$stmt->bind_param("s", $mobile);
		$stmt->execute();
		$deviceQuery = $stmt->get_result();
		if(mysqli_num_rows($deviceQuery) != 0){
			$updateDevice = "UPDATE `Device` SET `Token`=?, `Make`=?, `Model`=?, `OS`=?, `OSVer`=?, `AppVer`=?, `UpdateDate`= current_timestamp WHERE `Mobile` = ? and `UserId` = $custId and `AppName` = 1";
			$stmt = $conn->prepare($updateDevice);
			$stmt->bind_param("sssssss", $token, $make, $model, $os, $osVer, $appVer, $mobile);
			$stmt->execute();
		}
		else{
			$insertDevice = "INSERT INTO `Device`(`Mobile`, `Token`, `Make`, `Model`, `OS`, `OSVer`, `AppVer`, `UserId`, `AppName`) VALUES (?, ?, ?, ?, ?, ?, ?, $custId, 1)";
			$stmt = $conn->prepare($insertDevice);
			$stmt->bind_param("sssssss", $mobile, $token, $make, $model, $os, $osVer, $appVer);
			$stmt->execute();
		}
	}	

}
else{
	$output = array('code' => 404, 'message'=>'Invalid OTP');
	echo json_encode($output);
}

?>