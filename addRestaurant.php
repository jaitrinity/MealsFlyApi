<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$name = $jsonData->name;
$mobile = $jsonData->mobile;
$address = $jsonData->address;
$image = '';
$banner = '';
$latLong = $jsonData->latLong;
$pincode = $jsonData->pincode;

$sql = "SELECT * FROM `RestaurantMaster` WHERE `Mobile` = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mobile);
$stmt->execute();
$query = $stmt->get_result();
$rowCount = mysqli_num_rows($query);
$code = 0;
$message = "";
if($rowCount != 0){
	$code = 403;
	$message = "Already exist $mobile";
}
else{
	$sql = "INSERT INTO `RestaurantMaster`(`Name`, `Mobile`, `Address`, `Pincode`, `Image`, `Banner`, `LatLong`) VALUES (?, ?, ?, ?, ?, ?, ?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("sssisss", $name, $mobile, $address, $pincode, $image, $banner, $latLong);

	if($stmt->execute()){
		$code = 200;
		$message = "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
}	

	
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);

?>