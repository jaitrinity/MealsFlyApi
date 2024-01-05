<?php
include("dbConfiguration.php");
$sql = "SELECT * FROM `Configuration` where `Id` = 4";
$stmt = $conn->prepare($sql);
$stmt->execute();
$query = $stmt->get_result();
$row = mysqli_fetch_assoc($query);
$pincode = $row["Value"];
$pincodeList = explode(",", $pincode);

echo json_encode($pincodeList);
?>