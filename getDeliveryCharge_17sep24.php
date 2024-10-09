<?php
include("dbConfiguration.php");

$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData = json_decode($json);

$api_key = "AIzaSyDkCjzv4fVu7wlsp31Tu0AnpbyQaxm4Kz8";
$custAddId = $jsonData->custAddId;
$restId = $jsonData->restId;

$sql = "SELECT * FROM `Distance` where `CustAddId`=? and `RestId`=? and `IsDeleted`=0 ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii",$custAddId, $restId);
$stmt->execute();
$result = $stmt->get_result();
$rowCount = mysqli_num_rows($result);
if($rowCount != 0){
	$row = mysqli_fetch_assoc($result);
	$distance = $row["Distance"];
	$deliveryCharge = $row["DeliveryCharge"];

	$output = array(
		'code' => 200,
		'message'=> 'Distance and delivery charge are calculated',
		'distance' => $distance, 
		'deliveryCharge' => $deliveryCharge
	);
	echo json_encode($output);
}
else{
	$sql = "SELECT * FROM `RestaurantMaster` where `RestId`=? and `Approve`=1 and `Enable`=1";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i",$restId);
	$stmt->execute();
	$result = $stmt->get_result();
	$rowCount = mysqli_num_rows($result);
	$origin = "";
	if($rowCount != 0){
		$row = mysqli_fetch_assoc($result);
		$restLatlong = $row["LatLong"];
		$origin = $restLatlong;
	}
	if($origin == ""){
		$output = array(
			'code' => 404,
			'message'=> 'Origin(Restaurant) LatLong not found'
		);
		echo json_encode($output);
		return;
	}

	$sql = "SELECT * FROM `CustomerAddress` where `CustAddId`=? and `IsDeleted`=0";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i",$custAddId);
	$stmt->execute();
	$result = $stmt->get_result();
	$rowCount = mysqli_num_rows($result);
	$distinations = "";
	if($rowCount != 0){
		$row = mysqli_fetch_assoc($result);
		$custLatlong = $row["LatLong"];
		$distinations = $custLatlong;
	}
	if($distinations == ""){
		$output = array(
			'code' => 404,
			'message'=> 'Distination(Customer) LatLong not found'
		);
		echo json_encode($output);
		return;
	}

	$sql = "SELECT `Value` FROM `Configuration` where `Id` = 3";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = mysqli_fetch_assoc($result);
	$perKM_charge = $row["Value"];
	
	$url='https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins='.$origin.'&destinations='.$distinations.'&key='.$api_key;
	// echo $url;
	$json_data = file_get_contents($url);	
	$distance = fnlGetDistance($json_data);
	$roundOfDistance = round($distance);
	if($roundOfDistance < $distance){
		$roundOfDistance++;
	}
	// $deliveryCharge = $roundOfDistance * $perKM_charge;
	// $deliveryCharge = $round * $perKM_charge;
	// $deliveryCharge = $distance * $perKM_charge;
	$deliveryCharge = ($roundOfDistance*$perKM_charge)+10;
	// echo $distance.'--'.$round.'--'.$deliveryCharge;

	$sql = "INSERT INTO `Distance`(`CustAddId`, `RestId`, `Distance`, `DeliveryCharge`) VALUES (?, ?, ?, ?)";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("iidd",$custAddId, $restId, $distance, $deliveryCharge);
	$stmt->execute();

	$output = array(
		'code' => 200,
		'message'=> 'Distance and delivery charge are calculated',
		'distance' => $distance, 
		'deliveryCharge' => $deliveryCharge
	);
	echo json_encode($output);
}

	
?>
<?php
function fnlGetDistance($json_data)
{
	$json_a=json_decode($json_data,true);
	$total_distance=0;
	foreach($json_a as $key => $value) 
	{
		if($key=="rows")
		{
			foreach($value as $key1 => $value1) 
			{
				foreach($value1 as $key2 => $value2) 
				{
					foreach($value2 as $key3 => $value3) 
					{
						foreach($value3 as $key4 => $value4) 
						{
							if($key4=="distance")
							{
								foreach($value4 as $key5 => $value5) 
								{
									if($key5=="text")
									{
										// $total_distance=$total_distance + str_replace(" km","",$value5);
										$dist = $value5;
										// echo $dist;
										if(strpos($dist, 'km') !== false){
											// echo $dist;
											$dist1 = str_replace(" km","",$dist);
											// echo $dist1.'--';
											$dist = $dist1*1000;
										}
										else{
											$dist1 = str_replace(" m","",$dist);
											// echo $dist1.'--';
											$dist = $dist1;
										}
										$total_distance = ($total_distance + $dist)/1000;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	return $total_distance;
}
function CallAPI($method, $url, $data)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
	//echo $result."\n";
    curl_close($curl);

    return $result;
}
?>
