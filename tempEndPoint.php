<?php
include("dbConfiguration.php");
$sql = "UPDATE `MyOrders` set `Status` = 3, `PickedUpDatetime` = null, `DeliveredDatetime`= null where `OrderId` = 5724";
$stmt = $conn->prepare($sql);
if($stmt->execute()){
	$code = 200;
	$message = "Successfully ";
}
else{
	$code = 0;
	$message = "Something went wrong";
}
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);
?>