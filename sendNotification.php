<?php 
$appName="Restaurant";
$tokens = "cbIXC2KMT-2qk0LPGGqV41:APA91bG-RdtPIv49jb1eiblAkMIMa9dBU2q2R9gx3CJrwb1wNOY4SII1jH0ZpKtoiD-YaiD3Pzn-v3Wt43Re9BBgfOhBEKnfpy46_Vbn40MmUL9uOM-FzFBhl9HQDgrPG5Svm2oFY7qx";
$title = "Title";
$body = "Body - ".$appName;
$image = "";
$link = "";
$dataJson = array('orderId' => 1);

$output = array();
require_once 'FirebaseNotificationClass.php';
$classObj = new FirebaseNotificationClass();
$result = $classObj->sendNotification($appName, $tokens, $title, $body, $image, $link, $dataJson);
echo $result;
$notificationResult = json_decode($result);
$notificationStatus = $notificationResult->success;
if($notificationStatus !=0){
	$output = array('status' => 'success', 'message' => 'Successfully send');
}
else{
	$output = array('status' => 'fail', 'message' => 'Something went wrong');
}
echo json_encode($output);

?>