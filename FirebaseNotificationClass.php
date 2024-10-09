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
			$project_id = "restaurantapp-ced4a";
			// $API_ACCESS_KEY = "ya29.a0AcM612yHy6_EpAFFK8DNJmSt-EPKN3SVWJaieKbmKVMHZE5V73KvQE3JtG5E-MXBtkyLeB3KE-veoo1JMQdEHc8pLyOTmkEVi59vGhL6UXJLHUzH5r-XXx2rMGM44QdgEDq6nfbfsyyqC5ZbZpFEiISVQ9wp4D89ywS28bxjaCgYKAegSARMSFQHGX2MixCbmWJP8-exI-3YnQfQisQ0175";
		}
		else if($appName == "Rider"){
			$project_id = "deliveryapp-87bc7";
			// $API_ACCESS_KEY = "ya29.a0AcM612yCih5A3n_WdThYJLJpxfCLSGFRpLf3i9VXWZvjvP2qPRnTnyPNGB9F80Fx4Y819tFBMNKyP23hMYygqdfHcd9fzo1ZyvqgPxgwe52D-1E4TyQWD4Dt6U8q4exnLFIgal5jLQC7oZ3CeytkMNBw9hhD57vjUhv86FQCaCgYKAXsSARMSFQHGX2Mi7L_v50FQWBHJFd9msUGDvQ0175";
		}
		else if($appName == "Customer"){
			$project_id = "mealsfly-400907";
			// $API_ACCESS_KEY = "ya29.a0AcM612w2bLQb_DwZtjesnOwjiANVilQXZfs_31hHlunpUFMojXa5fBNX-KiYqN2MJoT_uwj6yUI0LYoUnIg2-VzSy3gwADfJoxWnh8QSdO-pxRAIxyE5jZYdKZR5xoXhdTMkUgNNM91lhe5nultfi8BlLQ8UJtRo57AAODgKaCgYKARoSARMSFQHGX2Mip-2kr8dIJKT6xcCx_hYWPg0175";
		}
		
		$API_ACCESS_KEY = "ya29.c.c0ASRK0Ga2EU18mGhMiqysEgbutZ-nCs_nUIAL7Yw73VcG5oe_xJxg4j-MAC8iscQy5m1OLph1tpao1YCYtwoNUHaECIScEIa4iYdzy2dlKUPCU_PxY6TJdKQxt6waIkI0ZeBsN_Bd8bGmDQtyHvAskduabOeDFliguy6x0IUR-UjFWilmxtGWMmOnaFxHJZ7tVJDrQ4GVhSuVqSbLxnm4B0uQkNGwomgO_gZwzxZ_OdlsstyvAMS3N6dTS8uYqgeKN8HdKrkFdB2eh1pPd22FW-1kFiv0_sicrd9_QAS4AqAp7TsQeZyWtLQtAi78-WMf83mDXnHddkj_Xp0cy6vfpIURtxBV_-Lxod1gkgGe9kg6CjIotiMEdrT6kAE387Cme8F9-RM0dSc3OYvQb0mb-peSr1a9BM1zqkajncxnfdd3zlXX-rZZ9767Sh3eWvYMp8wF7wQIS7bals4xuQcxB4F8i0rmeYkSMfxOaY6mfs04_MV5XbVXiXdrpv9cOpQ0mF40RjQxgv29RhIYofsJrBYanIJ5Bmsb25stipw7OVtUvf8fFkjFd4grvMjRr0djd8m5uj4QV4u29xBOv5sXsOpmM0daWOyb05vfJcMhhcIjf670z6YpdxSXv1oM-mrejvx0arJ0s6wtjjgcf20MpszUz2VyRB-iVu4WlOJ30UiwIiZXwrdkos7F1rh75M0dXrzlQJmpgRglYe0UxekYJIeZtQ04Q_Mw6FWbXIygbuJOUW6eMI1fWZdcr4gMy_avaB6oB0gWjFx1QzSO8Rlpud7u2Shp6Iaaw1096eS7YvJXn44YMxQOQWrtJja0SSrQe8pBd2BOwops81hMSkJd44VU_vpbWtpQ34wnltJ4V6sMhim1neqJ86g3Wwwbb9l_qlynF7reBOgkI8hVghurxjy6ogUqf0Uv_Qyg5y9wUYhZwM0VhYFY-QyX_F6iVnn544QUovz5eg8J7sk_igVUugF8jwMrRdX8sOF4abtOZrsQRdXXyyzkQaO";
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
			$project_id = "restaurantapp-ced4a";
		}
		else if($appName == "Rider"){
			$project_id = "deliveryapp-87bc7";
		}
		else if($appName == "Customer"){
			$project_id = "mealsfly-400907";
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