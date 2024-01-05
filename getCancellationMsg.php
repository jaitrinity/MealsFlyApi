<?php
include("dbConfiguration.php");
$sql = "SELECT * FROM `Configuration` where `Id` = 2";
$stmt = $conn->prepare($sql);
$stmt->execute();
$query = $stmt->get_result();
$row = mysqli_fetch_assoc($query);
$data = $row["Value"];
$dataList = explode(",", $data);
echo json_encode($dataList);
?>