<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
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
$custLatlongList = array();
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
		array_push($custLatlongList, $custLatlong);
		
	}

	// $sql = "SELECT RestId, Name, Mobile, Address, Pincode, LatLong, Image, Banner, Status, OpenTime, CloseTime, getCustomerToPartnerDistance() as DistRange FROM RestaurantMaster where IsActive=1 and Enable=1";
	// $result = mysqli_query($conn,$sql);
	// while($row=mysqli_fetch_assoc($result)){
	// 	$restLatLong = $row["LatLong"];
	// 	$distRange = $row["DistRange"];
	// 	for($i=0;$i<count($custLatlongList);$i++){
	// 		$custLatlong = $custLatlongList[$i];

	// 		$distance = getCustToRestDistance($api_key, $custLatlong, $restLatLong);
	// 		// echo $distance.' -- ';
	// 		if($distance < $distRange){
	// 			$row["CustLatlong"] = $custLatlong;
	// 			$row["Distance"] = $distance;
	// 			$row["Category"] = "";
	// 			$row["Timestamp"] = $timeStamp;
	// 			insertApplicationRestaurant($conn, $row, 'Pincode');
	// 		}
	// 	}
	// }
}
else{
	array_push($custLatlongList, $custLatlong);
	// $sql = "SELECT RestId, Name, Mobile, Address, Pincode, LatLong, Image, Banner, Status, OpenTime, CloseTime, getCustomerToPartnerDistance() as DistRange FROM RestaurantMaster where IsActive=1 and Enable=1";
	// $result = mysqli_query($conn,$sql);
	// while($row=mysqli_fetch_assoc($result)){
	// 	$restLatLong = $row["LatLong"];
	// 	$distRange = $row["DistRange"];
	// 	$distance = getCustToRestDistance($api_key, $custLatlong, $restLatLong);
	// 	if($distance < $distRange){
	// 		$row["CustLatlong"] = $custLatlong;
	// 		$row["Distance"] = $distance;
	// 		$row["Category"] = "";
	// 		$row["Timestamp"] = $timeStamp;
	// 		insertApplicationRestaurant($conn, $row, 'LatLong');
	// 	}
	// }

}

$sql = "SELECT `RestId`, `Name`, `Mobile`, `Address`, `Pincode`, `LatLong`, `Image`, `Banner`, `Status`, `OpenTime`, `CloseTime`, getCustomerToPartnerDistance() as `DistRange` FROM `RestaurantMaster` where `IsActive`=1 and `Enable`=1";
$result = mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($result)){
	$restLatLong = $row["LatLong"];
	$distRange = $row["DistRange"];
	for($i=0;$i<count($custLatlongList);$i++){
		$custLatlong = $custLatlongList[$i];

		$distance = getCustToRestDistance($api_key, $custLatlong, $restLatLong);
		// echo $distance.' -- ';
		if($distance < $distRange){
			$row["CustLatlong"] = $custLatlong;
			$row["Distance"] = $distance;
			$row["Category"] = "";
			$row["Timestamp"] = $timeStamp;
			insertApplicationRestaurant($conn, $row, 'Pincode');
		}
	}
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
		'catogory' => $row["Category"],
		// 'distance' => $row["Distance"],
		// 'timestamp' => $row["Timestamp"]

	);
	array_push($restList, $jsonData);
}
echo json_encode($restList);

$logFilePath = '/var/www/trinityapplab.in/html/MealsFly/log/getRestaurant'.date("Y-m-d").'.log';
file_put_contents($logFilePath, date("Y-m-d H:i:s").' :: '.$timeStamp.' :: '.$json."\n", FILE_APPEND);

file_put_contents($logFilePath, date("Y-m-d H:i:s").' :: '.$timeStamp.' :: [('.implode('),(',$custLatlongList).')] :: '.json_encode($restList)."\n", FILE_APPEND);
?>

<?php
function getCustToRestDistance($api_key, $custLatlong, $restLatLong){
	require_once 'CallRestApiClass.php';
	$classObj = new CallRestApiClass();
	
	$mode = "driving"; // driving, walking, bike

	$url = "https://api.olamaps.io/routing/v1/distanceMatrix?origins=$custLatlong&destinations=$restLatLong&mode=$mode&api_key=$api_key";
	// echo $url.' || ';
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