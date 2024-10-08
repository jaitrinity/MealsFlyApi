<?php 
class FirebaseNotificationClass{
	function sendNotification($appName, $multiToken, $title, $body, $image, $link, $dataJson){
		if($appName == "Restaurant"){
			$API_ACCESS_KEY = "[API_ACCESS_KEY]";
			$android_channel_id = "restaurantAppId";
		}
		else if($appName == "Rider"){
			$API_ACCESS_KEY = "[API_ACCESS_KEY]";
			$android_channel_id = "delivery_app";
		}
		else if($appName == "Customer"){
			$API_ACCESS_KEY = "[API_ACCESS_KEY]";
			$android_channel_id = "mealsfly_app";
		}
		

		$fcmUrl = 'https://fcm.googleapis.com/fcm/send';

		$notification = [
	            'title' => $title,
	            'body' => $body,
	            'android_channel_id' => $android_channel_id,
	            'sound' => "sound.wav",
	            'image' => $image,
	            'link' => $link
	    ];
	    
	    $tokenList = explode(",", $multiToken);

	    $fcmNotification = [
	    	'registration_ids' => $tokenList,
	    	'priority' => "high",
	        'notification' => $notification,
	        'data' => $dataJson
	    ];

	    $headers = [
	        'Authorization: key=' .$API_ACCESS_KEY,
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

	function sendNotificationNew($appName, $multiToken, $title, $body, $image, $link, $dataJson){
		if($appName == "Restaurant"){
			$project_id = "[Restaurant_project_id]";
		}
		else if($appName == "Rider"){
			$project_id = "[Rider_project_id]";
		}
		else if($appName == "Customer"){
			$project_id = "[Customer_project_id]";
		}
		
		$API_ACCESS_KEY = "[API_ACCESS_KEY]";
		$fcmUrl = "https://fcm.googleapis.com/v1/projects/$project_id/messages:send";

		$notification = [
	            'title' => $title,
	            'body' => $body,
	            'image' => $image
	    ];
	    
	    // $tokenList = explode(",", $multiToken);
	    $tokenList = $multiToken;

	    $message = array(
	    	'token' => $tokenList,
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

	function sendNotificationNew1($appName, $deviceToken, $fcmToken, $title, $body, $image, $link, $dataJson){
		if($appName == "Restaurant"){
			$project_id = "[Restaurant_project_id]";
		}
		else if($appName == "Rider"){
			$project_id = "[Rider_project_id]";
		}
		else if($appName == "Customer"){
			$project_id = "[Customer_project_id]";
		}
		

		$fcmUrl = "https://fcm.googleapis.com/v1/projects/$project_id/messages:send";

		$notification = [
	            'title' => $title,
	            'body' => $body,
	            'image' => $image
	    ];
	    
	    

	    $message = array(
	    	'token' => $deviceToken,
	    	'notification' => $notification,
	        'data' => $dataJson
	    );

	    $fcmNotification = [
	    	'message' => $message
	        
	    ];

	    $headers = [
	        'Authorization: Bearer '.$fcmToken,
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

	function sendNotificationOld($multiToken, $title, $body, $image, $link){
		$API_ACCESS_KEY = "[API_ACCESS_KEY]";
		$fcmUrl = 'https://fcm.googleapis.com/fcm/send';

		$notification = [
	            'title' => $title,
	            'body' => $body,
	            'image' => $image,
	            'link' => $link
	    ];
	    $extraNotificationData = ["message" => $notification,"moredata" =>'dd'];

	    $tokenList = explode(",", $multiToken);

	    $fcmNotification = [
	    	'registration_ids' => $tokenList,
	        // 'to'        => $token,
	        'notification' => $notification,
	        'data' => $extraNotificationData
	    ];

	    $headers = [
	        'Authorization: key=' .$API_ACCESS_KEY,
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
}
?>