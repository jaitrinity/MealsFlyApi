<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$mobile = $jsonData->mobile;

$sql = "SELECT * FROM `DeliveryBoyMaster` where `Mobile` = ? and `IsActive` = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mobile);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$randomOtp = 0;
	$msgStatus = false;
	$confiSql = "SELECT * FROM `Configuration` where `Id` = 1";
	$confiStmt = $conn->prepare($confiSql);
	$confiStmt->execute();
	$confiResult = $confiStmt->get_result();
	$confiRow = mysqli_fetch_assoc($confiResult);
	$mobileStr = $confiRow["Value"];
	$mobileArr = explode(",", $mobileStr);
	if(in_array($mobile,$mobileArr)){
		$randomOtp = 1234;	
		$msgStatus = true;
	}
	else{
		$randomOtp = rand(1000,9999);
		$msgStatus = sendOtp($mobile,$randomOtp);
	}

	if($msgStatus){
		$sql = "UPDATE `DeliveryBoyMaster` set `OTP` = ?, `IsOTPExpired` = 0 where `Mobile` = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("is", $randomOtp, $mobile);
		if($stmt->execute()){
			$output = array('code' => 200, 'message' => 'OTP send to mobile');
			echo json_encode($output);
		}
		else{
			$output = array('code' => 500, 'message' => 'Something went wrong while sending OTP');
			echo json_encode($output);
		}
	}
	else{
		$output = array('code' => 500, 'message' => 'Something went wrong while sending OTP');
		echo json_encode($output);
	}
}
else{
	$output = array('code' => 404, 'message' => 'Invalid Mobile');
	echo json_encode($output);
}
?>

<?php
function sendOtp($mobile,$taskotp)
{
	//api for sending the otp
	$appName = "MealsFly - Rider";
	$message = "Your one time password (OTP) is ".$taskotp." for ".$appName." application.";
	$apikey = "ae6fa4-5cab56-4bc26d-caa56f-b27aab";
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

?>