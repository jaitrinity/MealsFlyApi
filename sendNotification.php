<?php 
$appName="Restaurant";
$tokens = "c8YMmi3ERAOvDkieonqtNg:APA91bG4OQz3ADal4CLvDIUieV4CkBDMX7vqeEIekhvOOTJRMiojY9GxmRoCcZlmqT1D_g_OelQdTtTS-M8Ns3MwuMXBmj4tKgYXGHYG6mkA50uWwQ0Ro5pGOKIuD27v6MmCvK9uJl44";
$title = "Title";
$body = "Body";
$image = "";
$link = "";
$dataJson = array('orderId' => 1);

$output = array();
require_once 'FirebaseNotificationClass.php';
$classObj = new FirebaseNotificationClass();
$result = $classObj->sendNotification($appName, $tokens, $title, $body, $image, $link, $dataJson);
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