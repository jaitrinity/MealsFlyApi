<?php 
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
file_put_contents('/var/www/trinityapplab.in/html/MealsFly/log/log_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.$json."\n", FILE_APPEND);
$jsonData = json_decode($json);

$restId = $jsonData->restId;
$custId = $jsonData->custId;

// same order hit within 60 sec, deny this order.
$orSql = "SELECT * from(SELECT TIMESTAMPDIFF(SECOND,`OrderDatetime`, CURRENT_TIMESTAMP) as SecondDiff FROM `MyOrders` where `RestId`=$restId and `CustId`=$custId order by `OrderDatetime` desc LIMIT 0,1) t where t.SecondDiff < 60";
$orQuery=mysqli_query($conn,$orSql);
$orRowCount=mysqli_num_rows($orQuery);
if($orRowCount != 0){
	return;
}

$custAddId = $jsonData->custAddId;
$totalPrice = $jsonData->totalPrice;
$deliveryCharge = $jsonData->deliveryCharge;
$grandTotal = floatval($totalPrice) + floatval($deliveryCharge);
$paymentMode = $jsonData->paymentMode;
$instruction = $jsonData->instruction;
$paymentStatus = $jsonData->paymentStatus;
$status = 1;
if($paymentStatus == 0) $status = 6;
$itemList = $jsonData->itemList;
$sql = "INSERT INTO `MyOrders`(`RestId`, `CustId`, `TotalPrice`, `DeliveryCharge`, `GrandTotal`, `CustAddId`, `PaymentMode`, `PaymentStatus`, `Instruction`, `Status`) VALUES ($restId, $custId, $totalPrice, $deliveryCharge, $grandTotal, $custAddId, '$paymentMode', $paymentStatus, '$instruction', $status)";
$stmt = $conn->prepare($sql);
$message = "";
$orderIdList = array();
$orderItemIdList = array();
if($stmt->execute()){
	$orderId = $conn->insert_id;
	array_push($orderIdList, $orderId);
	for($i=0;$i<count($itemList);$i++){
		$itemObj = $itemList[$i];
		$catId = $itemObj->catId;
		$itemId = $itemObj->itemId;
		$unit = $itemObj->size;
		$quantity = $itemObj->quantity;
		$total = $itemObj->total;
		$price = intval($quantity) * intval($total);
		$sql1 = "INSERT INTO `MyOrderItems`(`OrderId`, `CatId`, `ItemId`, `Unit`, `Quantity`, `Price`) VALUES ($orderId, $catId, $itemId, '$unit', $quantity, $price)";
		$stmt1 = $conn->prepare($sql1);
		if($stmt1->execute()){
			$orderItemId = $conn->insert_id;
			array_push($orderItemIdList, $orderItemId);
		}
		else{
			$message = "Something wrong while inserting data in `MyOrders` table.";
			break;
		}
		
	}
	
}
else{
	$message = "Something wrong while inserting data in `MyOrderItems` table.";
}
$code = 0;
if($message == ""){
	$code = 200;
	$message = "Successfully inserted";

	// Customer notication
	$sql2 = "SELECT `Token` FROM `Device` where `UserId` = $custId and `AppName` = 1";
	$result2 = mysqli_query($conn,$sql2);
	$rowCount =mysqli_num_rows($result2);
	if($rowCount != 0){
		$tokenList = [];
		while($row2 = mysqli_fetch_assoc($result2)){
			array_push($tokenList, $row2["Token"]);
		}
		$tokens = implode(",", $tokenList);
		$title = "New Order";
		$body = "Thank you for ordering on mealsfly, your food will be delivered soon";
		$image = "";
		$link = "";
		$orderJson = new StdClass;
		$appName = "Customer";
		require_once 'FirebaseNotificationClass.php';
		$classObj = new FirebaseNotificationClass();
		$notiResult = $classObj->sendNotification($appName, $tokens, $title, $body, $image, $link, $orderJson);
		$notificationResult = json_decode($notiResult);
		$notificationStatus = $notificationResult->success;
		$notiSql = "UPDATE `MyOrders` set `IsSendNotification`=$notificationStatus, `Tokens`='$tokens' where `OrderId` = $orderId";
		$notiStmt = $conn->prepare($notiSql);
		$notiStmt->execute();
	}

	// Restaurant Notification
	$sql2 = "SELECT `Token` FROM `Device` where `UserId` = $restId and `AppName` = 2";
	$result2 = mysqli_query($conn,$sql2);
	$rowCount =mysqli_num_rows($result2);
	if($rowCount != 0){
		$tokenList = [];
		while($row2 = mysqli_fetch_assoc($result2)){
			array_push($tokenList, $row2["Token"]);
		}
		$tokens = implode(",", $tokenList);
		$title = "";
		$body = "New order ! You have recieved a new order on mealsfly";
		$image = "";
		$link = "";

		$sql3 = "SELECT mo.OrderId, ca.Name as CustomerName, ca.Contact as CustomerContact, concat(ca.Contact,', ',ca.Address,', ', ca.City,', ', ca.Pincode,', ', ca.State) as Address, rm.Name as RestaurantName, mo.TotalPrice, mo.PaymentMode, mo.Instruction, mo.Status, mo.OrderDatetime FROM MyOrders mo join RestaurantMaster rm on mo.RestId = rm.RestId join CustomerAddress ca on mo.CustAddId = ca.CustAddId where mo.OrderId = $orderId";
		$result3 = mysqli_query($conn,$sql3);
		$rowCount3 = mysqli_num_rows($result3);
		if($rowCount3 !=0){
			$row3 = mysqli_fetch_assoc($result3);

			$orderItemList = array();
			$sql4 = "SELECT im.Name, cm.Name as CatName, moi.Unit, moi.Quantity, moi.Price FROM MyOrderItems moi join ItemMaster im on moi.ItemId = im.ItemId join CategoryMaster cm on moi.CatId = cm.CatId where moi.OrderId = $orderId";
			$result4 = mysqli_query($conn,$sql4);
			while($row4 = mysqli_fetch_assoc($result4)){
				$orderItemJson = array(
					'itemName' => $row4["Name"],
					'categoryName' => $row4["CatName"],
					'size' => $row4["Unit"],
					'quantity' => $row4["Quantity"],
					'price' => $row4["Price"]
				);
				array_push($orderItemList, $orderItemJson);
			}

			$orderJson = array(
				'orderId' => $row3["OrderId"],
				'customerName' => $row3["CustomerName"],
				'contact' => $row3["CustomerContact"],
				'address' => $row3["Address"],
				'restaurantName' => $row3["RestaurantName"],
				'totalPrice' => $row3["TotalPrice"],
				'paymentMode' => $row3["PaymentMode"],
				'instruction' => $row3["Instruction"],
				'status' => $row3["Status"],
				'orderItemList' => $orderItemList
			);

			// $output = array();
			$appName = "Restaurant";
			require_once 'FirebaseNotificationClass.php';
			$classObj = new FirebaseNotificationClass();
			$notiResult = $classObj->sendNotification($appName, $tokens, $title, $body, $image, $link, $orderJson);
			$notificationResult = json_decode($notiResult);
			$notificationStatus = $notificationResult->success;
			$notiSql = "UPDATE `MyOrders` set `IsSendNotification`=$notificationStatus, `Tokens`='$tokens' where `OrderId` = $orderId";
			$notiStmt = $conn->prepare($notiSql);
			$notiStmt->execute();
			// if($notificationStatus !=0){
			// 	$output = array('status' => 'success', 'message' => 'Successfully send');
			// }
			// else{
			// 	$output = array('status' => 'fail', 'message' => 'Something went wrong');
			// }
		}	
	}
}
else{
	$delOrder = "DELETE FROM `MyOrders` where `OrderId` in (implode(',', $orderIdList))";
	$delOrderStmt = $conn->prepare($delOrder);
	$delOrderStmt->execute();

	$delOrderItem = "DELETE FROM `MyOrderItems` WHERE `OrderItemId` in (implode(',', $orderItemIdList))";
	$delOrderItemStmt = $conn->prepare($delOrderItem);
	$delOrderItemStmt->execute();
}
$output = array('orderId' => $orderId, 'code' => $code, 'message' => $message);
echo json_encode($output);
?>