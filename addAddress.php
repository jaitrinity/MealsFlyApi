<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData = json_decode($json);

$custId = $jsonData->custId;
$name = $jsonData->name;
$contact = $jsonData->contact;
$email = $jsonData->email;
$latLong = $jsonData->latLong;
$address = $jsonData->address;
$city = $jsonData->city;
$pincode = $jsonData->pincode;
$state = $jsonData->state;

$sql = "INSERT INTO `CustomerAddress`(`CustId`, `Name`, `Contact`, `Email`, `LatLong`,`Address`, `City`, `Pincode`, `State`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issssssss", $custId, $name, $contact, $email, $latLong, $address, $city, $pincode, $state);
$code = 0;
$message = "";

if($stmt->execute()){
	$code = 200;
	$message = "Successfully inserted";
}
else{
	$code = 0;
	$message = "Something went wrong";
}

$addressList = array();
$addSql = "SELECT * FROM `CustomerAddress` where `CustId` = ? and `IsDeleted` = 0 order by `CreateDate` desc";
$addStmt = $conn->prepare($addSql);
$addStmt->bind_param("i", $custId);
$addStmt->execute();
$addResult = $addStmt->get_result();
while($addRow = mysqli_fetch_assoc($addResult)){
	$addJson = array(
		'custAddId' => $addRow["CustAddId"],
		'name' => $addRow["Name"],
		'contact' => $addRow["Contact"],
		'email' => $addRow["Email"],
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