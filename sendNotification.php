<?php 
$tokens = "dkJ5gJdtQVqqO8pOeOCjOe:APA91bHQmmoxnaVSnE5V4ep6BRx3AB4NnrBiSvtYXoQWnJkpD_GQLtFa_X2PpQxOzrCYqyKy3wmVxilOgXIwMkcKbH29-nC7LnJT-kgCmyBybMjml25oUrbM5WSpYKXciM75aF7nEi8z";
$title = "Title";
$body = "Body";
$image = "";
$link = "";

$output = array();
require_once 'FirebaseNotificationClass.php';
$classObj = new FirebaseNotificationClass();
$result = $classObj->sendNotification($tokens, $title, $body, $image, $link);
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