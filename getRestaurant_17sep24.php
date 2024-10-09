<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.in/html/MealsFly/log/getRestaurant_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
$jsonData=json_decode($json);
 
$custLatlong = $jsonData->custLatlong;
$searchPincode = $jsonData->searchPincode;

$latlongExp = explode(",", $custLatlong);
$latitude = $latlongExp[0];
$longitude = $latlongExp[1];


if($searchPincode != ""){
	$sql = "SELECT * FROM `RestaurantMaster` where `Pincode` = $searchPincode and `IsActive`=1 and `Enable`=1 order by `DisplayOrder`";
}
else{
	$sql = "SELECT * from (SELECT *, ST_Distance_Sphere(point($latitude,$longitude), point(Latitude, Longitude)) as Distance, getCustomerToPartnerDistance() as DistRange FROM RestaurantMaster where 1=1 and IsActive=1 and Enable=1) t where t.Distance < t.DistRange Order BY t.Distance";
}


	// echo $sql;


$result = mysqli_query($conn,$sql);
$restList = array();
while($row=mysqli_fetch_assoc($result)){
	$restId = $row["RestId"];
	$sql1 = "SELECT DISTINCT c.* FROM ItemMaster i join CategoryMaster c on i.CatId = c.CatId where i.RestId = $restId and i.IsEnable = 1 and i.IsEditable = 0 order by c.Name limit 0,3";
	$result1 = mysqli_query($conn,$sql1);
	$catList = array();
	while($row1 = mysqli_fetch_assoc($result1)){
		$catName = $row1["Name"];
		array_push($catList, $catName);
	}

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
		'catogory' => implode(',', $catList)

	);
	array_push($restList, $jsonData);
}
echo json_encode($restList);
?>