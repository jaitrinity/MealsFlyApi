<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
require 'base64ToAny.php';
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$insertType = $jsonData->insertType;
if($insertType == "addCategory"){
	$restId = $jsonData->restId;
	$category = $jsonData->category;
	$imageBase64 = $jsonData->imageBase64;
	$t=time();
	$base64 = new Base64ToAny();
	$imageBase64 = $base64->base64_to_jpeg($imageBase64,$t.'_Image');

	$sql = "INSERT INTO `CategoryMaster`(`RestId`, `Name`, `Image`) VALUES ($restId, '$category', '$imageBase64')";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully inserted";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($insertType == "addRider"){
	$name = $jsonData->name;
	$mobile = $jsonData->mobile;
	$aadharNo = $jsonData->aadharNo;	
	$aadharBase64 = $jsonData->aadharBase64;
	$panNo = $jsonData->panNo;
	$panBase64 = $jsonData->panBase64;

	$sql = "SELECT * FROM `DeliveryBoyMaster` where `Mobile` = ? and `IsActive` = 1";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $mobile);
	$stmt->execute();
	$query = $stmt->get_result();
	$rowCount = mysqli_num_rows($query);
	$code = 0;
	$message = "";
	if($rowCount != 0){
		$code = 403;
		$message = "Already exist $mobile";
	}
	else{
		$t=time();
		$base64 = new Base64ToAny();
		$aadhar = $base64->base64_to_jpeg($aadharBase64,$t.'_Aadhar');
		$pan = $base64->base64_to_jpeg($panBase64,$t.'_PAN');

		$sql = "INSERT INTO `DeliveryBoyMaster`(`Name`, `Mobile`, `AadharNo`, `AadharCardPic`, `PanNo`, `PanPic`) VALUES ('$name', '$mobile', '$aadharNo', '$aadhar', '$panNo', '$pan')";
		$stmt = $conn->prepare($sql);
		if($stmt->execute()){
			$code = 200;
			$message =  "Successfully inserted";
		}
		else{
			$code = 0;
			$message = "Something went wrong";
		}
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($insertType == "moreItem"){
	$restId = $jsonData->restId;
	$itemList = $jsonData->itemList;
	$failIdArr = array();
	$status = false;
	$insertArr = array();
	for($i=0;$i<count($itemList);$i++){
		$itemObj = $itemList[$i];
		$id = $itemObj->id;
		$catId = $itemObj->catId;
		$image = $itemObj->image;
		if($image != ""){
			$t=time()*$i;
			$base64 = new Base64ToAny();
			$image = $base64->base64_to_jpeg($image,$t.'_Image');
		}
		$name = $itemObj->name;
		$customize = $itemObj->customize;
		$unitList = $itemObj->unitList;
		$sql = "INSERT INTO `ItemMaster`(`RestId`, `CatId`, `Name`, `Image`, `Customize`) VALUES ($restId, $catId, '$name', '$image', $customize)";
		$stmt = $conn->prepare($sql);
		if($stmt->execute()){
			$itemId = $conn->insert_id;
			for($j=0;$j<count($unitList);$j++){
				$unitObj = $unitList[$j];
				$unit = $unitObj->title;
				$price = $unitObj->price;
				$unitSql = "INSERT INTO `ItemUnit`(`ItemId`, `Unit`, `Price`) VALUES ($itemId, '$unit', $price)";
				$unitStmt = $conn->prepare($unitSql);
				if($unitStmt->execute()){

				}
			}
			

			$status = true;
			array_push($insertArr, $itemId);
		}
		else{
			array_push($failIdArr, $id);
			$status = false;
			break;
		}
	}
	$code = 0;
	$message = "";
	if($status){
		$code = 200;
		$message =  "Successfully inserted";
	}
	else{
		$failId = implode(",", $failIdArr);
		$code = 0;
		$message = "Something went wrong".$failId;
	}
	
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
?>