<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');

$timeStamp = time();
$logFilePath = '/var/www/trinityapplab.in/html/MealsFly/log/getRestaurant_new1_'.date("Y-m-d").'.log';
file_put_contents($logFilePath, date("Y-m-d H:i:s").' :: Start :: '.$timeStamp.' :: '.$json."\n", FILE_APPEND);

$jsonData=json_decode($json);

$custLatlong = $jsonData->custLatlong;
$custLatlong = str_replace(" ", "", $custLatlong);
if($custLatlong == "0.0,0.0"){
	$restList = array();
	$jsonData = array(
		'restId' => "", 
		'name' => "To see Restaurants near you",
		'mobile' => "",
		'address' => "Turn on your phone's location",
		'pincode' => "",
		'latLong' => "",
		'image' => "https://www.trinityapplab.in/MealsFly/logo/no_location.png",
		'banner' => "",
		'status' => "Close",
		'openTime' => "00:00:00",
		'closeTime' => "00:00:00",
		'catogory' => "",
		'distance' => ""
	);
	array_push($restList, $jsonData);
	echo json_encode($restList);
	return;
}
$searchPincode = $jsonData->searchPincode;

$custLatlongExp = explode(",", $custLatlong);
$custLatitude = $custLatlongExp[0];
$custLongitude = $custLatlongExp[1];

$distanceRangeInKM = 5;

$sql = "SELECT * FROM `RestaurantMaster` where 1=1 and `IsActive`=1 and `Enable`=1";
$result = mysqli_query($conn,$sql);
while($row=mysqli_fetch_assoc($result)){
	$restLatLong = $row["LatLong"];
	$restLatLong = str_replace(" ", "", $restLatLong);

	$restLatlongExp = explode(",", $restLatLong);
	$restLatitude = $restLatlongExp[0];
	$restLongitude = $restLatlongExp[1];

	$distance = haversine($custLatitude, $custLongitude, $restLatitude, $restLongitude);

	if ($distance <= $distanceRangeInKM) {
		$row["CustLatlong"] = $custLatlong;
		$row["Distance"] = $distance;
		$row["Category"] = "";
		$row["Timestamp"] = $timeStamp;
		insertApplicationRestaurant($conn, $row, 'LatLong');
	}
		
}

$logRestList = array();
$restList = array();
$sql = "SELECT * FROM `ApplicationRestaurant` where `Distance` < $distanceRangeInKM and  `Timestamp`=$timeStamp GROUP by `RestId` ORDER by `Distance` ";
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
		'distance' => $row["Distance"]
	);
	array_push($restList, $jsonData);

	$logJsonData = array('restId' => $row["RestId"], 'name' => $row["Name"]);
	array_push($logRestList, $logJsonData);
}
echo json_encode($restList);

file_put_contents($logFilePath, date("Y-m-d H:i:s").' :: End :: '.$timeStamp.' :: '.json_encode($logRestList)."\n", FILE_APPEND);
?>

<?php
function haversine($lat1, $lon1, $lat2, $lon2) {
    // Convert latitude and longitude from degrees to radians
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    
    // Haversine formula
    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;
    $a = sin($dlat / 2) * sin($dlat / 2) +
         cos($lat1) * cos($lat2) *
         sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    // Radius of the Earth in kilometers
    $R = 6371.0;
    $distance = $R * $c; // Distance in kilometers
    // echo $distance;
    return $distance;
}

function insertApplicationRestaurant($conn, $row, $insertType){
	$insertRest = "INSERT INTO `ApplicationRestaurant`(`RestId`, `Name`, `Mobile`, `Address`, `Pincode`, `CustLatLong`, `LatLong`, `Image`, `Banner`, `Status`, `OpenTime`, `CloseTime`, `DisplayOrder`, `Distance`, `Category`, `Timestamp`, `InsertType`) VALUES 
	(".$row["RestId"].", '".str_replace("'", "\'", $row["Name"])."', '".$row["Mobile"]."', '".$row["Address"]."', ".$row["Pincode"].", '".$row["CustLatlong"]."', '".$row["LatLong"]."', '".$row["Image"]."', '".$row["Banner"]."', '".$row["Status"]."', '".$row["OpenTime"]."', '".$row["CloseTime"]."', ".$row["DisplayOrder"].", ".$row["Distance"].", '".$row["Category"]."', ".$row["Timestamp"].", '$insertType')";
	// echo $insertRest;
	$stmt = $conn->prepare($insertRest);
	$stmt->execute();
}
?>