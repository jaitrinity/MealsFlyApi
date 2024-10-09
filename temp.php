<?php
require_once 'CallRestApiClass.php';

$classObj = new CallRestApiClass();
// $paymentId = "pay_OnbTUgl4EuJWP0";
// $request = array('amount' => 33000,'currency' => 'INR');
// $request = json_encode($request);
// $url = "https://api.razorpay.com/v1/payments/".$paymentId."/capture";
// $result = $classObj->razorPayApi($url, $request);
// echo $result;

// $url="https://www.trinityapplab.in/MealsFly_v1/getVersion.php?app=1";
// $url = "https://api.olamaps.io/places/v1/geocode?address=110040&language=English&api_key=5ebRxxJWh4P0Q7tjvOfVZO4CN2RGaId2os1MOYe3";
// $result = $classObj->callGetApi($url);
// $response = json_decode($result);
// $geocodingResultsArr = $response->geocodingResults;
// for($i=0;$i<count($geocodingResultsArr);$i++){
// 	$resultObj = $geocodingResultsArr[$i];
// 	$geometry = $resultObj->geometry;
// 	$location = $geometry->location;
// 	$lat = $location->lat;
// 	$lng = $location->lng;
	
// }

// =====Start=======
// $request = array('mobile' => '9716744965');
// $url="https://www.trinityapplab.in/MealsFly/restaurantSignIn.php";
// $result = $classObj->callPostApi($url, $request);
// $response = json_decode($result);
// echo $response->code.'<br>';
// echo $response->message;
// =====End====

// =====Start=======
// $restToken = "fYIG9CLGQ262Kl1Gu0P4aa:APA91bEZhqPe2pc3ckq_6ZRRxHzXZwg2MOW2zUk2RbOlDylp6zsesLPprSwkRSKANU8y_2mRP4VLr7dKRinckhvG0B46J_DWDySPis3y7DFivQ0iZskCtRSIn2xY-P4aX2Xh27z2ITXA";
// $riderToken = "dDqhiBNFSHCLzAFb0ZUQye:APA91bHk-CCQerctcJ8BdbD0yj_k0r6ftb-iAbkWRNpP7IlKE9zJd-7WUw3aknRqEFzp3ediGEDjFBs9c0Cfjoo8BZFmG1fsUKLs2rnGww4rUAb_emzFcTVWqC5lfZxGB8BbiqUJF0_i";
// $custToken = "fEJ0wua0SieLoD5C7N-36i:APA91bG3pXACUjEPmudWNQXAf1KresRPEbt1PZjTCYxjYUFk45NRJ-g7RRL67Rw2UY5uuda4TxV5ZBqOQhlO2_w1G0lcxdeIlZd1ayi3WinRHIfX2RYF-2453bUcvZzd55Kz_lfmYDcr";
// $request = array('title' => 'Live', 'message' => 'Hi im on live', 'topic' => 'Live', 'token' => $custToken);
// $request = json_encode($request);
// $url = "http://www.in3.co.in:8080/RestaurantApp/notification/token";
// $url = "http://www.in3.co.in:8080/RiderApp/notification/token";
// $url = "http://www.in3.co.in:8080/CustomerApp/notification/token";
// $result = $classObj->callPostApi($url, $request);
// $response = json_decode($result);
// echo $response->status.'<br>';
// echo $response->message;
// ======End=======

// =====Start====
$title="";
$message="";
$topic="";
$token="";
$appName = "Restaurant"; // Customer, Restaurant, Rider
if($appName == "Customer"){
	$custToken = "fEJ0wua0SieLoD5C7N-36i:APA91bG3pXACUjEPmudWNQXAf1KresRPEbt1PZjTCYxjYUFk45NRJ-g7RRL67Rw2UY5uuda4TxV5ZBqOQhlO2_w1G0lcxdeIlZd1ayi3WinRHIfX2RYF-2453bUcvZzd55Kz_lfmYDcr";
	$title = "Customer";
	$message = "Hi, i m a customer";
	$token = $custToken;
}
else if($appName == "Restaurant"){
	$restToken = "fYIG9CLGQ262Kl1Gu0P4aa:APA91bEZhqPe2pc3ckq_6ZRRxHzXZwg2MOW2zUk2RbOlDylp6zsesLPprSwkRSKANU8y_2mRP4VLr7dKRinckhvG0B46J_DWDySPis3y7DFivQ0iZskCtRSIn2xY-P4aX2Xh27z2ITXA";
	$title = "Restaurant";
	$message = "Hi, i m a restaurant";
	$token = $restToken;
}
else if($appName == "Rider"){
	$riderToken = "dDqhiBNFSHCLzAFb0ZUQye:APA91bHk-CCQerctcJ8BdbD0yj_k0r6ftb-iAbkWRNpP7IlKE9zJd-7WUw3aknRqEFzp3ediGEDjFBs9c0Cfjoo8BZFmG1fsUKLs2rnGww4rUAb_emzFcTVWqC5lfZxGB8BbiqUJF0_i";
	$title = "Rider";
	$message = "Hi, i m a rider";
	$token = $riderToken;
}

$request = array('title' => $title, 'message' => $message, 'topic' => $topic, 'token' => $token);

$request = json_encode($request);
$result = $classObj->callPostApiForSendNotification($appName, $request);
$response = json_decode($result);
echo $response->status.'<br>';
echo $response->message;
// =====End========

// require_once 'SendOtpClass.php';
// $otp = "1234";
// $mobile = "9716744965";
// $appName = "Test";
// $classObj = new SendOtpClass();
// $result = $classObj->sendOtp($otp, $mobile, $appName);
// echo $result;

// ---- Notification start ----
// include("dbConfiguration.php");
// $sql = "SELECT `Id`, `Mobile`, `Token`, `AppName`  FROM `Device` WHERE `Id` = 501";
// $sql = "SELECT `Id`, `Mobile`, `Token`, `AppName`  FROM `Device` WHERE `Mobile`='9540095509'";

// $liveVersion = "1.0.5";
// customer: 22,26,1935;
// Rest: 1368
// Rider: 27
// $sql = "SELECT * FROM `Device` where `Id`=22";
// $result = mysqli_query($conn,$sql);
// $rowCount =mysqli_num_rows($result);
// if($rowCount != 0){

// 	// require_once 'FirebaseNotificationClass.php';
// 	// $classObj = new FirebaseNotificationClass();

// 	$title = "New Order";
// 	$body = "New order ! You have recieved a new order on mealsfly";
// 	$image = "";
// 	$link = "";
// 	// $orderJson = new StdClass;
// 	$orderItemList = array();
// 	for($i=0;$i<5;$i++){
// 		$orderItemJson = array(
// 			'itemName' => 'itemName '.$i,
// 			'categoryName' => 'categoryName '.$i,
// 			'size' => 'size '.$i,
// 			'quantity' => 'quantity '.$i,
// 			'price' => 'price '.$i
// 		);
// 		array_push($orderItemList, $orderItemJson);
// 	}
// 	$orderJson = array(
// 		'orderId' => '4007',
// 		'customerName' => 'sumit',
// 		'contact' => '9999999999',
// 		'address' => 'Noida one',
// 		'restaurantName' => 'Trinty',
// 		'totalPrice' => '100',
// 		'paymentMode' => 'COD',
// 		'instruction' => 'No any',
// 		'status' => 'OK',
// 		// 'orderItemList' => $orderItemList
// 	);

// 	$notiResultList = array();
// 	while($row = mysqli_fetch_assoc($result)){
// 		$id = $row["Id"];
// 		$mobile = $row["Mobile"];
// 		$token = $row["Token"];
// 		$appName = $row["AppName"];
// 		if($appName == "1"){
// 			$appName = "Customer";
// 		}
// 		else if($appName == "2"){
// 			$appName = "Restaurant";
// 		}
// 		else if($appName == "3"){
// 			$appName = "Rider";
// 		}


// 		// $notiResult = $classObj->sendNotification($appName, $token, $title, $body, $image, $link, $orderJson);
// 		$notiResult = sendNotification($appName, $token, $title, $body, $image, $link, $orderJson);
// 		echo $notiResult;
// 		// $notiJson = array('deviceId' => $id,'mobile' => $mobile, 'appName' => $appName, 'notiResult'=>$notiResult);
// 		// array_push($notiResultList, $notiJson);

// 	}

	// echo json_encode($notiResultList);
// }
// ---- Notification end ----

?>

<?php 
// require 'vendor/autoload.php';
// use Google\Client;

function sendNotification($appName, $multiToken, $title, $body, $image, $link, $dataJson){
	// $serviceAccountPath = 'mealsfly-400907.json';
	// $API_ACCESS_KEY = getAccessToken($serviceAccountPath);

	if($appName == "Restaurant"){
		$project_id = "restaurantapp-ced4a";
		$API_ACCESS_KEY = "ya29.a0AcM612ySZdhjtNtd9w2bDty1HB7UOOROJ2zckkV3Z4Jy97K12QJJpYTE5uTmOIcyoRWqE3GIcnlD6XLw4RiTmstm4BFmMZ87xl3KJDBXa3yIq0uFresGfesCg-wigYVYIAoNOVXMzrWVyr_cGftm9Tk3THVaZUjDxPhSbsztaCgYKAU4SARMSFQHGX2Mi91uuevPFhuhinHxoAOrdFQ0175";
	}
	else if($appName == "Rider"){
		$project_id = "deliveryapp-87bc7";
		$API_ACCESS_KEY = "ya29.a0AcM612x8xt9LSwB-jJxPZ3YLwHXywZdAkC7UKHosIjAO1yUhz7ztLb9Cp5QmwFlE8DAMbM5JJ_XI_BezTjxD6Pnj-6i3MI_izF4_KBWtzJPoJntunZ-DOAGt5RP8fDOVNEifQrEMDbIO-tbzqLYLTbnb4yf5UAWWiSPd2aWhaCgYKAVISARMSFQHGX2Mi9EZMYVX9JJ2MkVBP5HJ50g0175";
	}
	else if($appName == "Customer"){
		$project_id = "mealsfly-400907";
		$API_ACCESS_KEY = "ya29.c.c0ASRK0Ga2EU18mGhMiqysEgbutZ-nCs_nUIAL7Yw73VcG5oe_xJxg4j-MAC8iscQy5m1OLph1tpao1YCYtwoNUHaECIScEIa4iYdzy2dlKUPCU_PxY6TJdKQxt6waIkI0ZeBsN_Bd8bGmDQtyHvAskduabOeDFliguy6x0IUR-UjFWilmxtGWMmOnaFxHJZ7tVJDrQ4GVhSuVqSbLxnm4B0uQkNGwomgO_gZwzxZ_OdlsstyvAMS3N6dTS8uYqgeKN8HdKrkFdB2eh1pPd22FW-1kFiv0_sicrd9_QAS4AqAp7TsQeZyWtLQtAi78-WMf83mDXnHddkj_Xp0cy6vfpIURtxBV_-Lxod1gkgGe9kg6CjIotiMEdrT6kAE387Cme8F9-RM0dSc3OYvQb0mb-peSr1a9BM1zqkajncxnfdd3zlXX-rZZ9767Sh3eWvYMp8wF7wQIS7bals4xuQcxB4F8i0rmeYkSMfxOaY6mfs04_MV5XbVXiXdrpv9cOpQ0mF40RjQxgv29RhIYofsJrBYanIJ5Bmsb25stipw7OVtUvf8fFkjFd4grvMjRr0djd8m5uj4QV4u29xBOv5sXsOpmM0daWOyb05vfJcMhhcIjf670z6YpdxSXv1oM-mrejvx0arJ0s6wtjjgcf20MpszUz2VyRB-iVu4WlOJ30UiwIiZXwrdkos7F1rh75M0dXrzlQJmpgRglYe0UxekYJIeZtQ04Q_Mw6FWbXIygbuJOUW6eMI1fWZdcr4gMy_avaB6oB0gWjFx1QzSO8Rlpud7u2Shp6Iaaw1096eS7YvJXn44YMxQOQWrtJja0SSrQe8pBd2BOwops81hMSkJd44VU_vpbWtpQ34wnltJ4V6sMhim1neqJ86g3Wwwbb9l_qlynF7reBOgkI8hVghurxjy6ogUqf0Uv_Qyg5y9wUYhZwM0VhYFY-QyX_F6iVnn544QUovz5eg8J7sk_igVUugF8jwMrRdX8sOF4abtOZrsQRdXXyyzkQaO";

		// $API_ACCESS_KEY = "ya29.a0AcM612yjLZlV1Vm2rVyvhcwsxPYg1NSpk8EnqsrIw8lVIszSgjmN3pTTXuY9vx_iOQu4OAA5op1YfjdNuVnRVaIqtN-g3ab70D1ZNl2a3O1Nia71AXmlnzXJ8GWbiMEXp6zoeqPcxOEnUlk-PiPFYpg_Y3hAyxPQXMOAizKfaCgYKAd4SARMSFQHGX2MiUUbRlfOSyx7gnuaBpE4USg0175";
	}

	$fcmUrl = "https://fcm.googleapis.com/v1/projects/$project_id/messages:send";

	$notification = [
            'title' => $title,
            'body' => $body,
            'image' => $image
    ];
    
    // $tokenList = explode(",", $multiToken);
    $tokenList = $multiToken;

    $message = array(
    	// 'token' => $tokenList,
    	'token' => 'f_-y4GmwQSuB9r1xkAZvPw:APA91bFBtXmFQARq9WLnkLDOT_Y8RHxtq3FDvlXaQo_2wT5sZdjh71kyIYylfOPB0ztaOV3thb4lTMOiVyMK--AEvR3GwKIgc5bdvhsuFwUe0LXK83-fTBpC6XwBg9I5vAnjZXWUJkEY',
    	'notification' => $notification,
        'data' => $dataJson
    );

    $fcmNotification = [
    	'message' => $message
        
    ];

    $headers = [
        'Authorization: Bearer '.$API_ACCESS_KEY,
        'Content-Type: application/json'
    ];

	// echo json_encode($fcmNotification);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$fcmUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
    $result = curl_exec($ch);

	if(curl_errno($ch))
	{
	    // echo 'error:' . curl_error($ch);
	}

    curl_close($ch);

    // echo $result;
    return $result;
}


// function getAccessToken($serviceAccountPath) {
//    $client = new Client();
//    $client->setAuthConfig($serviceAccountPath);
//    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
//    $client->useApplicationDefaultCredentials();
//    $token = $client->fetchAccessTokenWithAssertion();
//    return $token['access_token'];
// }

 ?>