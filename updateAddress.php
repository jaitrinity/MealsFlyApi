<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData = json_decode($json);

$custId = $jsonData->custId;
$custAddId = $jsonData->custAddId;
$name = $jsonData->name;
$contact = $jsonData->contact;
$latLong = $jsonData->latLong;
$address = $jsonData->address;
$city = $jsonData->city;
$pincode = $jsonData->pincode;
$state = $jsonData->state;

$sql = "SELECT * FROM `CustomerAddress` where `CustAddId`=? and `CustId`=? and `IsDeleted`=0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii",$custAddId, $custId);
$stmt->execute();
$result = $stmt->get_result();
$rowCount = mysqli_num_rows($result);
$code = 0;
$message = "";
if($rowCount == 0){
	$code = 404;
	$message = "No address found";
}
else{
	$sql = "UPDATE `CustomerAddress` set `Name`=?, `Contact`=?, `LatLong`=?, `Address`=?, `City`=?, `Pincode`=?, `State`=?, `UpdateDate`=CURRENT_TIMESTAMP where `CustId`=? and `CustAddId`=?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("sssssssii", $name, $contact, $latLong, $address, $city, $pincode, $state, $custId, $custAddId);

	if($stmt->execute()){
		$code = 200;
		$message = "Successfully update";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
}

$addressList = array();
$addSql = "SELECT * FROM `CustomerAddress` where `CustId` = ? and `IsDeleted` = 0";
$addStmt = $conn->prepare($addSql);
$addStmt->bind_param("i", $custId);
$addStmt->execute();
$addResult = $addStmt->get_result();
while($addRow = mysqli_fetch_assoc($addResult)){
	$addJson = array(
		'custAddId' => $addRow["CustAddId"],
		'name' => $addRow["Name"],
		'contact' => $addRow["Contact"],
		'latLong' => $addRow["LatLong"],
		'address' => $addRow["Address"],
		'city' => $addRow["City"],
		'pincode' => $addRow["Pincode"],
		'state' => $addRow["State"] 
	);
	array_push($addressList, $addJson);
}

$output = array('code' => $code, 'message' => $message, 'addressList' => $addressList);
echo json_encode($output);

?>