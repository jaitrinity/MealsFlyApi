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

$latlongExp = explode(",", $custLatlong);
$latitude = $latlongExp[0];
$longitude = $latlongExp[1];

$logRestList = array();
$restList = array();

$sql = "SELECT * from (SELECT *, ST_Distance_Sphere(point($latitude,$longitude), point(Latitude, Longitude)) as Distance, getCustomerToPartnerDistance() as DistRange FROM RestaurantMaster where 1=1 and IsActive=1 and Enable=1) t where t.Distance < t.DistRange Order BY t.Distance";

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
		'radius' => $row["Distance"],
		'catogory' => ""

	);
	array_push($restList, $jsonData);

	$logJsonData = array('restId' => $row["RestId"], 'name' => $row["Name"]);
	array_push($logRestList, $logJsonData);
}
echo json_encode($restList);

$logFilePath = '/var/www/trinityapplab.in/html/MealsFly/log/getRestaurant_new1_'.date("Y-m-d").'.log';
file_put_contents($logFilePath, date("Y-m-d H:i:s").' :: '.$json."\n", FILE_APPEND);

file_put_contents($logFilePath, date("Y-m-d H:i:s").' :: :: '.json_encode($logRestList)."\n", FILE_APPEND);
?>