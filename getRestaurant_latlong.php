<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$custLatlong = $jsonData->custLatlong;
$custLatlong = str_replace(" ", "", $custLatlong);
$searchPincode = $jsonData->searchPincode;

$timeStamp = time();

$getCustomerToPartnerDistance = 10000;

$sql = "SELECT `RestId`, `Name`, `Mobile`, `Address`, `Pincode`, `LatLong`, `Image`, `Banner`, `Status`, `OpenTime`, `CloseTime`, $getCustomerToPartnerDistance as `DistRange` FROM `RestaurantMaster` where `IsActive`=1 and `Enable`=1";
$result = mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($result)){
	$restLatLong = $row["LatLong"];
	$restLatLong = str_replace(" ", "", $restLatLong);
	$distRange = $row["DistRange"];
	$distance = getCustToRestDistanceGoogle($custLatlong, $restLatLong);
	// $distance = getCustToRestDistanceOla($custLatlong, $restLatLong);
	if($distance < $distRange){
		$row["CustLatlong"] = $custLatlong;
		$row["Distance"] = $distance;
		$row["Category"] = "";
		$row["Timestamp"] = $timeStamp;
		insertApplicationRestaurant($conn, $row, 'LatLong');
	}
}

$logRestList = array();
$restList = array();
$sql = "SELECT * FROM `ApplicationRestaurant` where `Distance` < $getCustomerToPartnerDistance and  `Timestamp`=$timeStamp GROUP by `RestId` ORDER by `Distance` ";
$result = mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($result)){
	$jsonData = array(
		'restId' => $row["RestId"], 
		'name' => $row["Name"],
		'mobile' => $row["Mobile"],
		'address' => $row["Address"],
		'pincode' => $row["Pincode"],
		'latLong' => $row["LatLong"],
		'image' => $row["Image"],
		'banner' => $row["Banner"],
		'status' => $row["Status"],
		'openTime' => $row["OpenTime"],
		'closeTime' => $row["CloseTime"],
		'catogory' => $row["Category"]

	);
	array_push($restList, $jsonData);

	$logJsonData = array('restId' => $row["RestId"], 'name' => $row["Name"]);
	array_push($logRestList, $logJsonData);
}
echo json_encode($restList);

$logFilePath = '/var/www/trinityapplab.in/html/MealsFly/log/getRestaurant_new1_'.date("Y-m-d").'.log';
file_put_contents($logFilePath, date("Y-m-d H:i:s").' :: '.$timeStamp.' :: '.$json."\n", FILE_APPEND);

file_put_contents($logFilePath, date("Y-m-d H:i:s").' :: '.$timeStamp.' :: '.json_encode($logRestList)."\n", FILE_APPEND);
?>

<?php
function getCustToRestDistanceGoogle($custLatlong, $restLatLong){
	require_once 'CallRestApiClass.php';
	$classObj = new CallRestApiClass();

	// Testing
	$api_key = "AIzaSyDkCjzv4fVu7wlsp31Tu0AnpbyQaxm4Kz8";

	// Mealsfly
	// $api_key = "";

	$url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=$custLatlong&destinations=$restLatLong&key=$api_key";

	// echo $url;
	
	$apiResult = $classObj->callGetApi($url);
	$apiResponse = json_decode($apiResult);
	$apiStatus = $apiResponse->status;
	if($apiStatus == "OK"){
		$apiRow = $apiResponse->rows[0];
		$elements = $apiRow->elements[0];
		$distance = $elements->distance;
		$distanceInMeter = $distance->value;
	}
	else{
		$distanceInMeter = 0;
	}
	return $distanceInMeter;
}

function getCustToRestDistanceOla($custLatlong, $restLatLong){
	require_once 'CallRestApiClass.php';
	$classObj = new CallRestApiClass();
	
	$mode = "driving"; // driving, walking, bike

	// Testing
	// $api_key = "5ebRxxJWh4P0Q7tjvOfVZO4CN2RGaId2os1MOYe3";

	// Mealsfly
	$api_key = "3dZ5NGYdAbt1WEYDjol7piDitOdrpnrpe2yExXVU";

	$url = "https://api.olamaps.io/routing/v1/distanceMatrix?origins=$custLatlong&destinations=$restLatLong&mode=$mode&api_key=$api_key";
	
	$apiResult = $classObj->callGetApi($url);
	$apiResponse = json_decode($apiResult);
	$apiStatus = $apiResponse->status;
	if($apiStatus == "SUCCESS"){
		$apiRow = $apiResponse->rows[0];
		$elements = $apiRow->elements[0];
		$distance = $elements->distance;
	}
	else{
		$distance = 0;
	}
	return $distance;
}

function insertApplicationRestaurant($conn, $row, $insertType){
	$insertRest = "INSERT INTO `ApplicationRestaurant`(`RestId`, `Name`, `Mobile`, `Address`, `Pincode`, `CustLatLong`, `LatLong`, `Image`, `Banner`, `Status`, `OpenTime`, `CloseTime`, `Distance`, `Category`, `Timestamp`, `InsertType`) VALUES 
	(".$row["RestId"].", '".str_replace("'", "\'", $row["Name"])."', '".$row["Mobile"]."', '".$row["Address"]."', ".$row["Pincode"].", '".$row["CustLatlong"]."', '".$row["LatLong"]."', '".$row["Image"]."', '".$row["Banner"]."', '".$row["Status"]."', '".$row["OpenTime"]."', '".$row["CloseTime"]."', ".$row["Distance"].", '".$row["Category"]."', ".$row["Timestamp"].", '$insertType')";
	// echo $insertRest;
	$stmt = $conn->prepare($insertRest);
	$stmt->execute();
}
?>