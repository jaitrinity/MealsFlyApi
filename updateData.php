<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$updateType = $jsonData->updateType;
if($updateType == "riderReceiveAmount"){
	$checkedData = $jsonData->checkedData;
	$successArr = [];
	$failArr = [];
	for($i=0;$i<count($checkedData);$i++){
		$obj = $checkedData[$i];
		$orderId = $obj->orderId;
		$receiveAmount = $obj->receiveAmount;
		$sql = "UPDATE `MyOrders` set `ReceiveAmount`=$receiveAmount  where `OrderId`=$orderId";
		$stmt = $conn->prepare($sql);
		if($stmt->execute()){
			array_push($successArr, $obj);
		}
		else{
			array_push($failArr, $obj);
		}
	}
	if(count($failArr) == 0){
		$output = array('code' => 200, 'successArr' => $successArr, 'failArr' => $failArr);
		echo json_encode($output);
	}
	else{
		$output = array('code' => 500, 'successArr' => $successArr, 'failArr' => $failArr);
		echo json_encode($output);
	}
}
else if($updateType == "orderPayment"){
	$checkedData = $jsonData->checkedData;
	$successArr = [];
	$failArr = [];
	for($i=0;$i<count($checkedData);$i++){
		$obj = $checkedData[$i];
		$orderId = $obj->orderId;
		$payableAmount = $obj->payableAmount;
		$sql = "UPDATE `MyOrders` set `PayableAmount`=$payableAmount  where `OrderId`=$orderId";
		$stmt = $conn->prepare($sql);
		if($stmt->execute()){
			array_push($successArr, $obj);
		}
		else{
			array_push($failArr, $obj);
		}
	}
	if(count($failArr) == 0){
		$output = array('code' => 200, 'successArr' => $successArr, 'failArr' => $failArr);
		echo json_encode($output);
	}
	else{
		$output = array('code' => 500, 'successArr' => $successArr, 'failArr' => $failArr);
		echo json_encode($output);
	}
	
}
else if($updateType == "complaintStatus"){
	$compaintId = $jsonData->compaintId;
	$status = $jsonData->status;
	$sql = "UPDATE `Complaint` set `Status` = '$status', `CloseDate` = current_timestamp where `Id` = $compaintId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully updated";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "updateRestPriority"){
	$restId = $jsonData->restId;
	$priority = $jsonData->priority;
	$sql = "UPDATE `RestaurantMaster` set `DisplayOrder`='$priority', `UpdateDate`=current_timestamp where `RestId` = $restId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully updated";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "deleteRestItem"){
	$itemId = $jsonData->itemId;
	$sql = "UPDATE `ItemMaster` set `IsEditable` = -1 where `ItemId` = $itemId";
	$stmt = $conn->prepare($sql);
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully deleted";	
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "updateItem"){
	require 'base64ToAny.php';
	$t=time();
	$base64 = new Base64ToAny();

	$restId = $jsonData->restId;
	$itemId = $jsonData->itemId;
	$name = $jsonData->name;
	$image64 = $jsonData->image64;
	$image = "";
	if($image64 != ""){
		$image = $base64->base64_to_jpeg($image64,$t.'_Image');
	}
	else{
		$ssql = "SELECT * FROM `ItemMaster` where `ItemId` = $itemId";
		$sstmt = $conn->prepare($ssql);
		$sstmt->execute();
		$rresult = $sstmt->get_result();
		while($rrow = mysqli_fetch_assoc($rresult)){
			$image = $rrow["Image"];
		}
	}
	
	$catId = $jsonData->catId;
	$customize = $jsonData->customize;
	$unitList = $jsonData->unitList;

	$sql = "INSERT INTO `ItemMaster`(`RestId`, `CatId`, `Name`, `Image`, `Customize`) VALUES ($restId, $catId, '$name', '$image', $customize)";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$newItemId = $conn->insert_id;
		for($j=0;$j<count($unitList);$j++){
			$unitObj = $unitList[$j];
			$unit = $unitObj->title;
			$price = $unitObj->price;
			$unitSql = "INSERT INTO `ItemUnit`(`ItemId`, `Unit`, `Price`) VALUES ($newItemId, '$unit', $price)";
			$unitStmt = $conn->prepare($unitSql);
			if($unitStmt->execute()){

			}
		}
		$sql1 = "UPDATE `ItemMaster` set `IsEditable` = $newItemId where `ItemId` = $itemId";
		$stmt1 = $conn->prepare($sql1);
		$stmt1->execute();

		$code = 200;
		$message =  "Successfully updated";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);

}
else if($updateType == "editRider"){
	require 'base64ToAny.php';
	$t=time();
	$base64 = new Base64ToAny();

	$riderId = $jsonData->riderId;
	$name = $jsonData->name;
	$mobile = $jsonData->mobile;
	$aadharNo = $jsonData->aadharNo;
	$aadharBase64 = $jsonData->aadharBase64;
	$panNo = $jsonData->panNo;
	$panBase64 = $jsonData->panBase64;
	$riderLatLong = $jsonData->riderLatLong;
	$moreUpdate = "";
	if($aadharBase64 != ""){
		$aadhar = $base64->base64_to_jpeg($aadharBase64,$t.'_Aadhar');
		$moreUpdate .= ", `AadharCardPic` = '$aadhar'";
	}
	if($panBase64 != ""){
		$pan = $base64->base64_to_jpeg($panBase64,$t.'_PAN');
		$moreUpdate .= ", `PanPic` = '$pan'";
	}

	$riderLatLong = str_replace(" ", "", $riderLatLong);

	$sql = "UPDATE `DeliveryBoyMaster` set `Name` = '$name', `Mobile` = '$mobile', `AadharNo` = '$aadharNo', `PanNo` = '$panNo', `CurrentLatlong`='$riderLatLong' $moreUpdate , `UpdateDate`=current_timestamp where `RiderId` = $riderId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully updated";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "editRestaurant"){
	require 'base64ToAny.php';
	$t=time();
	$base64 = new Base64ToAny();

	$restId = $jsonData->restId;
	$name = $jsonData->name;
	$mobile = $jsonData->mobile;
	$address = $jsonData->address;
	$pincode = $jsonData->pincode;
	$latlong = $jsonData->latlong;
	$image64 = $jsonData->image64;
	$banner64 = $jsonData->banner64;
	$openTime = $jsonData->openTime;
	$closeTime = $jsonData->closeTime;
	$displayOrder = $jsonData->displayOrder;
	$moreUpdate = "";
	if($image64 != ''){
		$image = $base64->base64_to_jpeg($image64,$t.'_Image');
		$moreUpdate .= ", `Image` = '$image'";
	}
	if($banner64 != ''){
		$banner = $base64->base64_to_jpeg($banner64,$t.'_Banner');
		$moreUpdate .= ", `Banner` = '$banner'";
	}

	$restId = $jsonData->restId;
	$sql = "UPDATE `RestaurantMaster` set `Name`='$name', `Mobile`='$mobile', `Address`='$address', `Pincode`='$pincode', `LatLong`='$latlong', `DisplayOrder`='$displayOrder', `OpenTime`='$openTime', `CloseTime`='$closeTime', `UpdateDate`=current_timestamp $moreUpdate where `RestId` = $restId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully updated";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);


}
else if($updateType == "appRejRest"){
	$restId = $jsonData->restId;
	$action = $jsonData->action;
	$moreUpdate = "";
	if($action == 2){
		$moreUpdate = ", `Enable` = $action, `IsActive` = $action";
	}
	$sql = "UPDATE `RestaurantMaster` set `Approve`=$action, `UpdateDate`=current_timestamp $moreUpdate where `RestId` = $restId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$actTxt = $action == 1 ? 'Approve' : 'Reject';
		$message =  "Successfully ".$actTxt;
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "openCloseRest"){
	$restId = $jsonData->restId;
	$actionTxt = $jsonData->actionTxt;
	$sql = "UPDATE `RestaurantMaster` set `Status`='$actionTxt', `UpdateDate`=current_timestamp where `RestId` = $restId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully $actionTxt";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}

else if($updateType == "enaDisRest"){
	$restId = $jsonData->restId;
	$action = $jsonData->action;
	$actionTxt = $jsonData->actionTxt;
	$sql = "UPDATE `RestaurantMaster` set `Enable`=$action, `UpdateDate`=current_timestamp where `RestId` = $restId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Successfully $actionTxt";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "actDeactRider"){
	$riderId = $jsonData->riderId;
	$action = $jsonData->action;
	$sql = "UPDATE `DeliveryBoyMaster` set `IsActive` = $action where `RiderId` = $riderId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$actTxt = $action == 1 ? 'Active' : 'Deactive';
		$message =  "Successfully ".$actTxt;
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "deleteOrder"){
	$orderId = $jsonData->orderId;
	$sql = "UPDATE `MyOrders` set `Status`=7, `DeletedDatetime`=current_timestamp where `OrderId`=$orderId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Order $orderId successfully deleted";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
else if($updateType == "updateOrderItem"){
	$orderId = $jsonData->orderId;
	$totalPrice = $jsonData->totalPrice;
	$grandTotal = $jsonData->grandTotal;
	$orderItemList = $jsonData->orderItemList;
	for($i=0;$i<count($orderItemList);$i++){
		$orderItemObj = $orderItemList[$i];
		$orderItemId = $orderItemObj->orderItemId;
		$newQuantity = $orderItemObj->newQuantity;
		$price = $orderItemObj->price;

		if($newQuantity != ""){
			$updateItemSql = "UPDATE `MyOrderItems` set `Quantity`=$newQuantity, `Price`=$price where `OrderItemId`=$orderItemId";
			$updateItemStmt = $conn->prepare($updateItemSql);
			$updateItemStmt->execute();
		}
	}

	$sql = "UPDATE `MyOrders` set `TotalPrice`=$totalPrice, `GrandTotal`=$grandTotal where `OrderId`=$orderId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Order $orderId successfully updated";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);

}
else if($updateType == "deleteOrderItem"){
	$orderId = $jsonData->orderId;
	$deleteItemPrice = $jsonData->deleteItemPrice;
	$deleteItemArr = $jsonData->deleteItemArr;
	for($i=0;$i<count($deleteItemArr);$i++){
		$orderItemId = $deleteItemArr[$i];
		$updateItemSql = "UPDATE `MyOrderItems` set `IsDeleted`=1 where `OrderItemId`=$orderItemId";
		$updateItemStmt = $conn->prepare($updateItemSql);
		$updateItemStmt->execute();
	}

	$sql = "UPDATE `MyOrders` set `TotalPrice`=`TotalPrice`-$deleteItemPrice, `GrandTotal`=`GrandTotal`-$deleteItemPrice where `OrderId`=$orderId";
	$stmt = $conn->prepare($sql);
	$code = 0;
	$message = "";
	if($stmt->execute()){
		$code = 200;
		$message =  "Order item successfully deleted";
	}
	else{
		$code = 0;
		$message = "Something went wrong";
	}
	$output = array('code' => $code, 'message' => $message);
	echo json_encode($output);
}
?>