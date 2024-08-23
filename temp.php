<?php
// require_once 'CallRestApiClass.php';

// $classObj = new CallRestApiClass();
// $paymentId = "pay_OnbTUgl4EuJWP0";
// $request = array('amount' => 33000,'currency' => 'INR');
// $request = json_encode($request);
// $url = "https://api.razorpay.com/v1/payments/".$paymentId."/capture";
// $result = $classObj->razorPayApi($url, $request);
// echo $result;

// $url="https://www.trinityapplab.in/MealsFly_v1/getVersion.php?app=1";
// $result = $classObj->callGetApi($url);
// $response = json_decode($result);
// echo $response->andVer;

// $request = array('mobile' => '9716744965');
// $request = json_encode($request);
// $url="https://www.trinityapplab.in/MealsFly_v1/restaurantSignIn.php";
// $result = $classObj->callPostApi($url, $request);
// $response = json_decode($result);
// echo $response->code.'<br>';
// echo $response->message;

// require_once 'SendOtpClass.php';
// $otp = "1234";
// $mobile = "9716744965";
// $appName = "Test";
// $classObj = new SendOtpClass();
// $result = $classObj->sendOtp($otp, $mobile, $appName);
// echo $result;

// ---- Notification start ----
include("dbConfiguration.php");
// $sql = "SELECT `Id`, `Mobile`, `Token`, `AppName`  FROM `Device` WHERE `Id` = 501";
// $sql = "SELECT `Id`, `Mobile`, `Token`, `AppName`  FROM `Device` WHERE `Mobile`='9540095509'";

$liveVersion = "1.0.5";
$sql = "SELECT * FROM `Device` where `AppName`=1 and `AppVer` != '$liveVersion'";
$result = mysqli_query($conn,$sql);
$rowCount =mysqli_num_rows($result);
if($rowCount != 0){

	require_once 'FirebaseNotificationClass.php';
	$classObj = new FirebaseNotificationClass();

	$title = "Please Update Mealsfly";
	$body = "A new version ($liveVersion) is available with important improvements. Update now to continue enjoying seamless service!";
	$image = "";
	$link = "";
	$orderJson = new StdClass;

	$notiResultList = array();
	while($row = mysqli_fetch_assoc($result)){
		$id = $row["Id"];
		$mobile = $row["Mobile"];
		$token = $row["Token"];
		$appName = $row["AppName"];
		if($appName == "1"){
			$appName = "Customer";
		}
		else if($appName == "2"){
			$appName = "Restaurant";
		}
		else if($appName == "3"){
			$appName = "Rider";
		}


		$notiResult = $classObj->sendNotification($appName, $token, $title, $body, $image, $link, $orderJson);
		$notiJson = array('deviceId' => $id,'mobile' => $mobile, 'appName' => $appName, 'notiResult'=>$notiResult);
		array_push($notiResultList, $notiJson);

	}

	echo json_encode($notiResultList);
}
// ---- Notification end ----

?>