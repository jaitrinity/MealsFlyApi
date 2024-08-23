<?php
include("dbConfiguration.php");

$sql="SELECT * FROM `DeliveryBoyMaster` where `CurrentLatlong` is not null";
$result = mysqli_query($conn,$sql);
$successArr = [];
$failArr = [];
while($row=mysqli_fetch_assoc($result)){
	$riderId = $row["RiderId"];
	$currLatLong = $row["CurrentLatlong"];
	$expCurrLatLong = explode(",", $currLatLong);
	$lati = $expCurrLatLong[0];
	$longi = $expCurrLatLong[1];

	$json = array('riderId' => $riderId, 'lati' => $lati, 'longi' => $longi);

	$sql = "UPDATE `DeliveryBoyMaster` set `Latitude`='$lati', `Longitude`='$longi' where `RiderId` = $riderId";
	$stmt = $conn->prepare($sql);
	if($stmt->execute()){
		array_push($successArr, $json);
	}
	else{
		array_push($failArr, $json);
	}
}
$output = array('successArr' => $successArr, 'failArr' => $failArr);
echo json_encode($output);
?>