<?php 
include("dbConfiguration.php");
$riderId = $_REQUEST["riderId"];
$sql = "SELECT * FROM `DeliveryBoyMaster` where `RiderId` = $riderId";
$result = mysqli_query($conn,$sql);
$rowCount = mysqli_num_rows($result);
$output = new StdClass;
if($rowCount != 0){
	$row = mysqli_fetch_assoc($result);
	$output = array(
		'currentLatlong' => $row["CurrentLatlong"]
	);
}
echo json_encode($output);

?>