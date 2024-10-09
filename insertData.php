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
else if($insertType == "addCategoryNew"){
	$restId = $jsonData->restId;
	$category = $jsonData->category;
	$oldCategory = $jsonData->oldCategory;
	if($oldCategory == ""){
		$oldCategory = $category;
	}
	$imageBase64 = $jsonData->imageBase64;
	$updateImg = "";
	if($imageBase64 !=""){
		$t=time();
		$base64 = new Base64ToAny();
		$imageBase64 = $base64->base64_to_jpeg($imageBase64,$t.'_Image');

		$updateImg .= ", `Image`='$imageBase64'";
	}

	$sql = "SELECT `CatId` FROM `CategoryMaster` where `RestId`=$restId and `Name`=? and `IsActive`=1";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("s", $oldCategory);
	$stmt->execute();
	$query = $stmt->get_result();
	$rowCount = mysqli_num_rows($query);
	if($rowCount == 0){
		if($imageBase64 == ""){
			$imageBase64 = "https://www.trinityapplab.in/MealsFly/logo/mealsfly.png";
		}
		$type = "insert";
		$sql = "INSERT INTO `CategoryMaster`(`RestId`, `Name`, `Image`) VALUES ($restId, ?, '$imageBase64')";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $category);
	}
	else{
		$type = "update";
		$sql = "UPDATE `CategoryMaster` set `Name`=? $updateImg where `RestId`=$restId and `Name`=?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ss", $category, $oldCategory);
	}
	
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully $type";
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
	$latLong = $jsonData->latLong;

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

		$latLong = str_replace(" ", "", $latLong);
		$sql = "INSERT INTO `DeliveryBoyMaster`(`Name`, `Mobile`, `AadharNo`, `AadharCardPic`, `PanNo`, `PanPic`, `CurrentLatlong`) VALUES ('$name', '$mobile', '$aadharNo', '$aadhar', '$panNo', '$pan', '$latLong')";
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
else if($insertType == "restaurant"){
	$t=time();
	$base64 = new Base64ToAny();

	$name = $jsonData->name;
	$mobile = $jsonData->mobile;
	$address = $jsonData->address;
	$pincode = $jsonData->pincode;
	$latlong = $jsonData->latlong;
	$latlong = str_replace(" ", "", $latlong);
	$image64 = $jsonData->image64;
	$banner64 = $jsonData->banner64;
	$openTime = $jsonData->openTime;
	$closeTime = $jsonData->closeTime;
	if($image64 != ''){
		$image64 = $base64->base64_to_jpeg($image64,$t.'_Image');
	}
	if($banner64 != ''){
		$banner64 = $base64->base64_to_jpeg($banner64,$t.'_Banner');
	}

	$sql = "INSERT INTO `RestaurantMaster`(`Name`, `Mobile`, `Address`, `Pincode`, `Image`, `Banner`, `OpenTime`, `CloseTime`, `LatLong`) VALUES ('$name', '$mobile', '$address', '$pincode', '$image64', '$banner64', '$openTime', '$closeTime', '$latlong')";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully insert";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);


}
else if($insertType == "importItem"){
	$restId = $jsonData->restId;
	$importData = $jsonData->importData;
	for($i=0;$i<count($importData);$i++){
		$itemObj = $importData[$i];
		$tableColumn = "";
		$tableData = "";
		foreach ($itemObj as $key => $value) {
			$tableColumn .= "`".$key."`,";
			$value = str_replace("'", "\'", $value);
			$tableData .= "'".$value."',";
		}

		$tableColumn .= "`RestId`";
		$tableData .= "$restId";

		$insertSql = "INSERT into `ImportItems` ($tableColumn) values ($tableData) ";
		$query=mysqli_query($conn,$insertSql);
	}

	$sql = "SELECT * FROM `ImportItems` where `IsDeleted`=0";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$rowCount = mysqli_num_rows($query);
	$successArr = array();
	$failArr = array();
	while($row = mysqli_fetch_assoc($query)){
		$defaultImg = "https://www.trinityapplab.in/MealsFly/logo/mealsfly.png";
		$restId = $row["RestId"];
		$category = $row["Category"];
		$category = str_replace("'", "\'", $category);

		$sql2 = "SELECT `CatId` FROM `CategoryMaster` where `RestId`=$restId and `Name`='$category' and `IsActive`=1";
		$result2 = mysqli_query($conn,$sql2);
		$rowCount2 = mysqli_num_rows($result2);
		if($rowCount2 == 0){
			$insCat = "INSERT INTO `CategoryMaster`(`RestId`, `Name`, `Image`) VALUES ($restId, '$category','$defaultImg')";
			$insCatStmt = $conn->prepare($insCat);
			if($insCatStmt->execute()){
				$catId = $conn->insert_id;
			}

		}
		else{
			$row2 = mysqli_fetch_assoc($result2);
			$catId = $row2["CatId"];
		}

		$itemName = $row["ItemName"];
		$itemName = str_replace("'", "\'", $itemName);
		$unitList = array();
		$quantity = $row["Quantity"];
		$unitObj = array('title' => 'Quantity', 'price' => $quantity);
		array_push($unitList, $unitObj);

		$half = $row["Half"];
		$unitObj = array('title' => 'Half', 'price' => $half);
		array_push($unitList, $unitObj);

		$full = $row["Full"];
		$unitObj = array('title' => 'Full', 'price' => $full);
		array_push($unitList, $unitObj);

		$gram200 = $row["200Gram"];
		$unitObj = array('title' => '200Gram', 'price' => $gram200);
		array_push($unitList, $unitObj);

		$gram500 = $row["500Gram"];
		$unitObj = array('title' => '500Gram', 'price' => $gram500);
		array_push($unitList, $unitObj);

		$kg1 = $row["1KG"];
		$unitObj = array('title' => '1KG', 'price' => $kg1);
		array_push($unitList, $unitObj);

		$gram1500 = $row["1.5KG"];
		$unitObj = array('title' => '1.5KG', 'price' => $gram1500);
		array_push($unitList, $unitObj);

		$kg2 = $row["2KG"];
		$unitObj = array('title' => '2KG', 'price' => $kg2);
		array_push($unitList, $unitObj);

		$ml100 = $row["100ML"];
		$unitObj = array('title' => '100ML', 'price' => $ml100);
		array_push($unitList, $unitObj);

		$ml500 = $row["500ML"];
		$unitObj = array('title' => '500ML', 'price' => $ml500);
		array_push($unitList, $unitObj);

		$l1 = $row["1L"];
		$unitObj = array('title' => '1L', 'price' => $l1);
		array_push($unitList, $unitObj);

		$small = $row["Small"];
		$unitObj = array('title' => 'Small', 'price' => $small);
		array_push($unitList, $unitObj);

		$large = $row["Large"];
		$unitObj = array('title' => 'Large', 'price' => $large);
		array_push($unitList, $unitObj);

		$customize = 0;
		if($half !=0 || $full !=0) $customize = 1;
		else if($gram200 !=0 || $gram500 !=0 || $kg1 !=0 || $gram1500 !=0 || $kg2 !=0) $customize=2;
		else if($ml100 !=0 || $ml500 !=0 || $l1 !=0) $customize=3;
		else if($small !=0 || $large !=0) $customize=4;

		$sql3 = "INSERT INTO `ItemMaster`(`RestId`, `CatId`, `Name`, `Image`, `Customize`) VALUES ($restId, $catId, '$itemName', '$defaultImg', $customize)";
		$stmt3 = $conn->prepare($sql3);
		if($stmt3->execute()){
			$itemId = $conn->insert_id;
			for($j=0;$j<count($unitList);$j++){
				$unitObj = $unitList[$j];
				$unit = $unitObj["title"];
				$price = $unitObj["price"];
				if($price !=0){
					$unitSql = "INSERT INTO `ItemUnit`(`ItemId`, `Unit`, `Price`) VALUES ($itemId, '$unit', $price)";
					$unitStmt = $conn->prepare($unitSql);
					if($unitStmt->execute()){

					}
				}	
			}
			array_push($successArr, $itemName);
		}
		else{
			array_push($failArr, $itemName);
		}
	}

	$code = 0;
	$message = "";
	if(count($successArr) == $rowCount){
		// $trunSql = "TRUNCATE `ImportItems`";
		$trunSql = "UPDATE `ImportItems` set `IsDeleted`=1";
		$trunStmt = $conn->prepare($trunSql);
		$trunStmt->execute();

		$code = 200;
		$message =  "Successfully imported";
	}

	$output = array(
		'code' => $code, 'message' => $message, 
		'restId' => $restId, 'importData' => $importData, 'successArr' => $successArr, 'failArr' => $failArr
	);
	echo json_encode($output);
	file_put_contents('/var/www/trinityapplab.in/html/MealsFly/log/ImportItems_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($output)."\n", FILE_APPEND);
}
?>