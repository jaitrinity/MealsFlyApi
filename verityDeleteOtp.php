<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$mobile = $jsonData->mobile;
$otp = $jsonData->otp;

$sql = "SELECT * FROM `CustomerMaster` where `Mobile` = ? and `OTP` = ? and `IsOTPExpired` = 0 and `IsActive` = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $mobile, $otp);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$row = mysqli_fetch_assoc($query);
	$custId = $row["CustId"];

	$sql = "UPDATE `CustomerMaster` set `IsOTPExpired`=1 where `CustId`=$custId";
	$stmt = $conn->prepare($sql);
	$stmt->execute();

	require 'SendMailClass.php';
	$toMailId = "mealsfly@gmail.com";
	$ccMailId = "";
	$bccMailId = "";

	$msg = "Dear Support Team,

	I would like to request the deletion of my account and all associated data from your service.

	Here are my account details:

	Mobile No: $mobile

	Thank you for your assistance.

	Best regards";

	$msg = nl2br($msg);
	$subject = "Request for Account Deletion";
	$classObj = new SendMailClass();
	$response = $classObj->sendMail($toMailId, $ccMailId, $bccMailId, $subject, $msg, null);
	

	$output = array(
		'code' => 200, 
		'message' => 'Your request submitted'
	);
	echo json_encode($output);

}
else{
	$output = array('code' => 404, 'message'=>'Invalid OTP');
	echo json_encode($output);
}
?>