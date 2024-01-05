<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$raiseBy = $jsonData->raiseBy;
$issue = $jsonData->issue;
$remark = $jsonData->remark;

$sql = "INSERT INTO `Complaint`(`RaiseBy`, `Issue`, `Remark`) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $raiseBy, $issue, $remark);
$code = 0;
$message = "Fail";
$complaintId = -1;
if($stmt->execute()){
	$code = 200;
	$message = "Success";
	$complaintId = $conn->insert_id;
}
$output = array('code' => $code, 'message' => $message, 'complaintId' => $complaintId);
echo json_encode($output);
?>