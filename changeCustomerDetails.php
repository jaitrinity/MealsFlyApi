<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$custId = $jsonData->custId;
$pincode = $jsonData->pincode;
$latlong = $jsonData->latlong;

$latlongExp = explode(",", $latlong);
$latitude = $latlongExp[0];
$longitude = $latlongExp[1];

$sql = "UPDATE `CustomerMaster` set `Pincode`=?, `LatLong`=?, `Latitude`=?, `Longitude`=? where `CustId`=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssi", $pincode, $latlong, $latitude, $longitude, $custId);
if($stmt->execute()){
	$code = 200;
	$message = "Successfully update";
}
else{
	$code = 0;
	$message = "Something went wrong";
}
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);
?>