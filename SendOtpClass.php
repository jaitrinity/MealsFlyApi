<?php
class SendOtpClass{
	function sendOtp($otp, $mobile, $appName){
		//api for sending the otp
		$message = "Your one time password (OTP) is ".$otp." for ".$appName." application.";
		$apikey = "[sms_api_key]";
		$senderId = "TRIAPP";
		$route = "default";
		$st = true;
		$postData = array(
	        'apikey' => $apikey,	
		    'dest_mobileno' => $mobile,
		    'message' => $message,
		    'senderid' => $senderId,
		    'route' => $route,
		    'response' => "Y",
		    'msgtype' => "TXT"
		);
		$url="http://www.smsjust.com/sms/user/urlsms.php";
		// init the resource
		$ch = curl_init();
		curl_setopt_array($ch, array(
		    CURLOPT_URL => $url,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_POST => true,
		    CURLOPT_POSTFIELDS => $postData
		    //,CURLOPT_FOLLOWLOCATION => true

		));
			//Ignore SSL certificate verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		//get response
		$output = curl_exec($ch);
		//Print error if any
		if(curl_errno($ch))
		{
		    echo 'error:' . curl_error($ch);
			$st = false;
		}
		curl_close($ch);
		return $st;
	}
}

?>