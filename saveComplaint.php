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

	$subject = "New complaint - ".$complaintId;
	$msg = "Dear Mealsfly Admin,<br><br>";
	$msg .= "Below complaint is raise by customer:<br><br>";
	$msg .= "Complaint id: $complaintId<br>";
	$msg .= "Issue: $issue<br>";
	$msg .= "Remark: $remark<br><br>";
	$msg .= "Regards,<br>";
	$msg .= "Trinity automation team...<br>";

	$toMailId = "ordersalertmealsfly@gmail.com";
	$ccMailId = "";
	$bccMailId = "";

	require 'SendMailClass.php';
	$classObj = new SendMailClass();
	$response = $classObj->sendMail($toMailId, $ccMailId, $bccMailId, $subject, $msg, null);
}
$output = array('code' => $code, 'message' => $message, 'complaintId' => $complaintId);
echo json_encode($output);
?>