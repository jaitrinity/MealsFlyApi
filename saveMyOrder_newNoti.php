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
$selfAccept = $jsonData->selfAccept;
if($deliveryCharge == 0 && $selfAccept == 0){
	$dcSql = "SELECT `DeliveryCharge` FROM `Distance` WHERE `RestId`=$restId and `CustAddId`=$custAddId and `IsDeleted`=0";
	$dcQuery=mysqli_query($conn,$dcSql);
	$dcRow = mysqli_fetch_assoc($dcQuery);
	$deliveryCharge = $dcRow["DeliveryCharge"];
}
$grandTotal = floatval($totalPrice) + floatval($deliveryCharge);
$paymentMode = $jsonData->paymentMode;
$paymentId = $jsonData->paymentId;
$instruction = $jsonData->instruction;
$paymentStatus = $jsonData->paymentStatus;
$status = 1;
if($paymentStatus == 0){
	// Payment decline
	$status = 6;
}

// if($selfAccept == 1){
// 	// Deliver
// 	$status = 5;
// }
$itemList = $jsonData->itemList;
$sql = "INSERT INTO `MyOrders`(`RestId`, `CustId`, `TotalPrice`, `DeliveryCharge`, `GrandTotal`, `CustAddId`, `PaymentMode`, `PaymentId`, `PaymentStatus`, `Instruction`, `Status`, `SelfAccept`, `Tokens`, `NotificationResponse`) VALUES ($restId, $custId, $totalPrice, $deliveryCharge, $grandTotal, $custAddId, '$paymentMode', '$paymentId', $paymentStatus, '$instruction', $status, $selfAccept,' ',' ')";
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
	if($paymentStatus == 1 && $paymentMode == "online"){
		require_once 'CallRestApiClass.php';
		$razorPayTotal = intval($grandTotal) * 100;
		$classObj = new CallRestApiClass();
		$request = array('amount' => $razorPayTotal,'currency' => 'INR');
		$request = json_encode($request);
		$url = "https://api.razorpay.com/v1/payments/".$paymentId."/capture";
		$razorPayResult = $classObj->razorPayApi($url, $request);
		$razorPaySql = "UPDATE `MyOrders` set `RazorpayStatus`='$razorPayResult' where `OrderId` = $orderId";
		$razorPayStmt = $conn->prepare($razorPaySql);
		$razorPayStmt->execute();
	}

	// if($selfAccept == 0){
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
			$notiResult = $classObj->sendNotificationNew($appName, $tokens, $title, $body, $image, $link, $orderJson);
			$notiSql = "UPDATE `MyOrders` set `NotificationResponse`= concat(`NotificationResponse`,'-Customer-\n',?,'\n'), `Tokens`=concat(`Tokens`,'-Customer-\n','$tokens','\n') where `OrderId` = $orderId";
			// echo $notiSql;
			$notiStmt = $conn->prepare($notiSql);
			$notiStmt->bind_param("s", $notiResult);
			$notiStmt->execute();
		}

		// Restaurant Notification
		if($status == 1){
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
						// 'orderItemList' => $orderItemList
					);

					$appName = "Restaurant";
					require_once 'FirebaseNotificationClass.php';
					$classObj = new FirebaseNotificationClass();
					$notiResult = $classObj->sendNotificationNew($appName, $tokens, $title, $body, $image, $link, $orderJson);
					$notiSql = "UPDATE `MyOrders` set `NotificationResponse`= concat(`NotificationResponse`,'-Restaurant-\n',?,'\n'), `Tokens`=concat(`Tokens`,'-Restaurant-\n','$tokens','\n') where `OrderId` = $orderId";
					// echo $notiSql;
					$notiStmt = $conn->prepare($notiSql);
					$notiStmt->bind_param("s", $notiResult);
					$notiStmt->execute();
					
				}	
			}
		}
			
	// }
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

file_put_contents('/var/www/trinityapplab.in/html/MealsFly/log/log_'.date("Y-m-d").'.log', date("Y-m-d H:i:s").' '.json_encode($output)."\n", FILE_APPEND);
?>