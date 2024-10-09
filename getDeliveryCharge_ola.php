<?php
include("dbConfiguration.php");

$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData = json_decode($json);

$custAddId = $jsonData->custAddId;
$restId = $jsonData->restId;

$sql = "SELECT * FROM `RestaurantMaster` where `RestId`=? and `Approve`=1 and `Enable`=1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$restId);
$stmt->execute();
$result = $stmt->get_result();
$rowCount = mysqli_num_rows($result);
$origin = "";
if($rowCount != 0){
	$row = mysqli_fetch_assoc($result);
	$restLatlong = $row["LatLong"];
	$origin = $restLatlong;
}
if($origin == ""){
	$output = array(
		'code' => 404,
		'message'=> 'Origin(Restaurant) LatLong not found'
	);
	echo json_encode($output);
	return;
}

$sql = "SELECT * FROM `CustomerAddress` where `CustAddId`=? and `IsDeleted`=0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$custAddId);
$stmt->execute();
$result = $stmt->get_result();
$rowCount = mysqli_num_rows($result);
$destinations = "";
if($rowCount != 0){
	$row = mysqli_fetch_assoc($result);
	$custLatlong = $row["LatLong"];
	$destinations = $custLatlong;
}
if($destinations == ""){
	$output = array(
		'code' => 404,
		'message'=> 'destinations(Customer) LatLong not found'
	);
	echo json_encode($output);
	return;
}

$sql = "SELECT `Value` FROM `Configuration` where `Id` = 3";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = mysqli_fetch_assoc($result);
$perKM_charge = $row["Value"];

$api_key = "3dZ5NGYdAbt1WEYDjol7piDitOdrpnrpe2yExXVU";
$mode = "driving"; // driving, walking, bike
$url = "https://api.olamaps.io/routing/v1/distanceMatrix?origins=$origin&destinations=$destinations&mode=$mode&api_key=$api_key";

require_once 'CallRestApiClass.php';
$classObj = new CallRestApiClass();
$result = $classObj->callGetApi($url);
$response = json_decode($result);
$status = $response->status;
if($status == "SUCCESS"){
	$row = $response->rows[0];
	$elements = $row->elements[0];
	$distanceInMeter = $elements->distance;
	$distance = $distanceInMeter/1000;
}
else{
	$distance = 1;
}
$roundOfDistance = round($distance);
if($roundOfDistance < $distance){
	$roundOfDistance++;
}	

$deliveryCharge = ($roundOfDistance*$perKM_charge)+10;

$sql = "UPDATE `Distance` set `IsDeleted`=1 where `CustAddId`=? and `RestId`=? ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii",$custAddId, $restId);
$stmt->execute();

$sql = "INSERT INTO `Distance`(`CustAddId`, `RestId`, `Distance`, `DeliveryCharge`) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iidd",$custAddId, $restId, $distance, $deliveryCharge);
$stmt->execute();

$output = array(
	'code' => 200,
	'message'=> 'Distance and delivery charge are calculated',
	'distance' => $distance, 
	'roundOfDistance' => $roundOfDistance,
	'deliveryCharge' => $deliveryCharge
	
);
echo json_encode($output);
?>

