<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
// file_put_contents('/var/www/trinityapplab.in/html/MealsFly/log/getRestaurant_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
$jsonData=json_decode($json);
 
$custLatlong = $jsonData->custLatlong;
$searchPincode = $jsonData->searchPincode;

$latlongExp = explode(",", $custLatlong);
$latitude = $latlongExp[0];
$longitude = $latlongExp[1];
$timeStamp = time();

// Testing
// $api_key = "5ebRxxJWh4P0Q7tjvOfVZO4CN2RGaId2os1MOYe3";

// Mealsfly
$api_key = "3dZ5NGYdAbt1WEYDjol7piDitOdrpnrpe2yExXVU";

if($searchPincode != ""){
	$sqlList = array();
	require_once 'CallRestApiClass.php';
	$classObj = new CallRestApiClass();
	
	$url = "https://api.olamaps.io/places/v1/geocode?address=$searchPincode&language=English&api_key=$api_key";
	$result = $classObj->callGetApi($url);
	$response = json_decode($result);
	$geocodingResultsArr = $response->geocodingResults;
	for($i=0;$i<count($geocodingResultsArr);$i++){
		$resultObj = $geocodingResultsArr[$i];
		$geometry = $resultObj->geometry;
		$location = $geometry->location;
		$lat = $location->lat;
		$lng = $location->lng;

		$custLatlong = $lat.','.$lng;
		insertApplicationRestaurant($api_key, $custLatlong, $timeStamp, 'Pincode', $conn);
	}
}
else{
	$timeStamp = time();
	insertApplicationRestaurant($api_key, $custLatlong, $timeStamp, 'LatLong', $conn);
}

$restList = array();
$sql = "SELECT * FROM `ApplicationRestaurant` where `Distance` < getCustomerToPartnerDistance() and  `Timestamp`=$timeStamp GROUP by `RestId` ORDER by `Distance` ";
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
		'distance' => $row["Distance"],
		'catogory' => $row["Category"],
		'timestamp' => $row["Timestamp"]

	);
	array_push($restList, $jsonData);
}
echo json_encode($restList);
?>

<?php
function insertApplicationRestaurant($api_key, $custLatlong, $timeStamp, $insertType, $conn){
	require_once 'CallRestApiClass.php';
	$classObj = new CallRestApiClass();
	
	$mode = "driving"; // driving, walking, bike

	$sql = "SELECT RestId, Name, Mobile, Address, Pincode, LatLong, Image, Banner, Status, OpenTime, CloseTime, getCustomerToPartnerDistance() as DistRange FROM RestaurantMaster where IsActive=1 and Enable=1";
	$result = mysqli_query($conn,$sql);
	while($row=mysqli_fetch_assoc($result)){
		$restLatLong = $row["LatLong"];
		$distRange = $row["DistRange"];

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
			$distance = 1000;
		}

		$row["Distance"] = $distance;
		$row["Category"] = "";
		$row["Timestamp"] = $timeStamp;

		// if($distance <= $distRange){
			$insertRest = "INSERT INTO `ApplicationRestaurant`(`RestId`, `Name`, `Mobile`, `Address`, `Pincode`, `CustLatLong`, `LatLong`, `Image`, `Banner`, `Status`, `OpenTime`, `CloseTime`, `Distance`, `Category`, `Timestamp`, `InsertType`) VALUES 
			(".$row["RestId"].", '".str_replace("'", "\'", $row["Name"])."', '".$row["Mobile"]."', '".$row["Address"]."', ".$row["Pincode"].", '$custLatlong', '".$row["LatLong"]."', '".$row["Image"]."', '".$row["Banner"]."', '".$row["Status"]."', '".$row["OpenTime"]."', '".$row["CloseTime"]."', ".$row["Distance"].", '".$row["Category"]."', ".$row["Timestamp"].", '$insertType')";
			// echo $insertRest;
			$stmt = $conn->prepare($insertRest);
			$stmt->execute();
		// }
	}
}
?>