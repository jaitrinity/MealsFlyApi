<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$orderId = $jsonData->orderId;
$status = $jsonData->status;
$sql = "";
$code = 0;
$message = "";
if($status < 0){
	$sql = "SELECT * FROM `MyOrders` where `OrderId` = $orderId and `RiderId` != 0";
	$result = mysqli_query($conn,$sql);
	$rowCount = mysqli_num_rows($result);
	if($rowCount == 0){
		$riderId = $jsonData->riderId;
		$sql = "UPDATE `MyOrders` set `RiderId` = ?, `OrderAcceptDatetime` = CURRENT_TIMESTAMP where `OrderId` = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ii", $riderId, $orderId);
		if($stmt->execute()){
			$code = 200;
			$message = "Successfully ";
		}
		else{
			$code = 0;
			$message = "Something went wrong";
		}
	}
	else{
		$code = 404;
		$message = "This order already accepted by other rider..";
	}
		
}
else{
	$sql = "SELECT * FROM `MyOrders` where `OrderId` = $orderId";
	$result = mysqli_query($conn,$sql);
	$row = mysqli_fetch_assoc($result);
	$ordStatus = $row["Status"];
	if($ordStatus != $status){
		if($status == 0){
			$cancellationMsg = $jsonData->cancellationMsg;
			$sql = "UPDATE `MyOrders` set `Status` = ?, `CancellationMsg` = '$cancellationMsg', `CancelledDatetime` = CURRENT_TIMESTAMP where `OrderId` = ?";
		}
		else if($status == 2){
			$sql = "UPDATE `MyOrders` set `Status` = ?, `PreparingDatetime` = CURRENT_TIMESTAMP where `OrderId` = ?";
		}
		else if($status == 3){
			$sql = "UPDATE `MyOrders` set `Status` = ?, `ReadyDatetime` = CURRENT_TIMESTAMP where `OrderId` = ?";
		}
		else if($status == 4){
			$sql = "UPDATE `MyOrders` set `Status` = ?, `PickedUpDatetime` = CURRENT_TIMESTAMP where `OrderId` = ?";
		}
		else if($status == 5){
			$paymentType = $jsonData->paymentType;
			$sql = "UPDATE `MyOrders` set `Status`=?, `PaymentType`=$paymentType, `DeliveredDatetime` = CURRENT_TIMESTAMP where `OrderId` = ?";
		}
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("ii", $status, $orderId);

		if($stmt->execute()){
			$sql = "SELECT `StatusTxt` FROM `OrderStatus` where `Status` = $status";
			$result = mysqli_query($conn,$sql);
			$row = mysqli_fetch_assoc($result);
			$statusTxt = $row["StatusTxt"];

			$code = 200;
			$message = "Successfully ".$statusTxt;
		}
		else{
			$code = 0;
			$message = "Something went wrong";
		}
	}
	else{
		$sql = "SELECT `StatusTxt` FROM `OrderStatus` where `Status` = $status";
		$result = mysqli_query($conn,$sql);
		$row = mysqli_fetch_assoc($result);
		$statusTxt = $row["StatusTxt"];

		$code = 204;
		$message = "Order already ".$statusTxt;
	}	
}
$output = array('code' => $code, 'message' => $message);
echo json_encode($output);

// Notification
if($code == 200){
	// Preparing
	if($status == 2){
		// Get Rider device token
		$sql = "SELECT d.Token FROM Device d join DeliveryBoyMaster de on d.UserId = de.RiderId and de.Status = 1 where d.AppName = 3";
		$result = mysqli_query($conn,$sql);
		$rowCount =mysqli_num_rows($result);
		if($rowCount != 0){
			$tokenList = [];
			while($row = mysqli_fetch_assoc($result)){
				array_push($tokenList, $row["Token"]);
			}
			$tokens = implode(",", $tokenList);
			$title = "";
			$body = "Deliver now ! New order received";
			$image = "";
			$link = "";

			$sql1 = "SELECT mo.OrderId, ca.Name as CustomerName, ca.Contact as CustomerContact, concat(ca.Address, ',', ca.City, ',', ca.Pincode, ca.State) as Address, rm.Name as RestaurantName, rm.Mobile as RestMobile, rm.Address as RestAddress, mo.TotalPrice, mo.PaymentMode, mo.Instruction, mo.Status, mo.OrderDatetime FROM MyOrders mo join RestaurantMaster rm on mo.RestId = rm.RestId join CustomerAddress ca on mo.CustAddId = ca.CustAddId where mo.OrderId = $orderId";
			$result1 = mysqli_query($conn,$sql1);
			$rowCount1 = mysqli_num_rows($result1);
			$orderJson = new StdClass;
			if($rowCount1 !=0){
				$row1 = mysqli_fetch_assoc($result1);

				$orderItemList = array();
				$sql2 = "SELECT im.Name, cm.Name as CatName, moi.Unit, moi.Quantity, moi.Price FROM MyOrderItems moi join ItemMaster im on moi.ItemId = im.ItemId join CategoryMaster cm on moi.CatId = cm.CatId where moi.OrderId = $orderId";
				$result2 = mysqli_query($conn,$sql2);
				while($row2 = mysqli_fetch_assoc($result2)){
					$orderItemJson = array(
						'itemName' => $row2["Name"],
						'categoryName' => $row2["CatName"],
						'size' => $row2["Unit"],
						'quantity' => $row2["Quantity"],
						'price' => $row2["Price"]
					);
					array_push($orderItemList, $orderItemJson);
				}

				$orderJson = array(
					'orderId' => $row1["OrderId"],
					'customerName' => $row1["CustomerName"],
					'contact' => $row1["CustomerContact"],
					'address' => $row1["Address"],
					'restaurantName' => $row1["RestaurantName"],
					'restaurantMobile' => $row1["RestMobile"],
					'restaurantAddress' => $row1["RestAddress"],
					'totalPrice' => $row1["TotalPrice"],
					'paymentMode' => $row1["PaymentMode"],
					'instruction' => $row1["Instruction"],
					'status' => $row1["Status"],
					'orderItemList' => $orderItemList
				);
			}

			$appName = "Rider";
			require_once 'FirebaseNotificationClass.php';
			$classObj = new FirebaseNotificationClass();
			$notiResult = $classObj->sendNotification($appName, $tokens, $title, $body, $image, $link, $orderJson);
			// $notificationResult = json_decode($notiResult);
			// $notificationStatus = $notificationResult->success;
			// if($notificationStatus !=0){
			// 	$output = array('status' => 'success', 'message' => 'Successfully send');
			// }
			// else{
			// 	$output = array('status' => 'fail', 'message' => 'Something went wrong');
			// }
			$notificationResult = json_decode($notiResult);
			$notificationStatus = $notificationResult->success;
			$notiSql = "UPDATE `MyOrders` set `IsSendNotification`=$notificationStatus, `Tokens`='$tokens' where `OrderId` = $orderId";
			$notiStmt = $conn->prepare($notiSql);
			$notiStmt->execute();
		}
	}
	// Preparing
	if($status == 2){
		$sql = "SELECT rm.Name as RestName, d.Token FROM MyOrders mo join CustomerMaster cm on mo.CustId = cm.CustId join Device d on mo.CustId = d.UserId join RestaurantMaster rm on mo.RestId = rm.RestId where mo.OrderId = $orderId and d.AppName = 1";
		$result = mysqli_query($conn,$sql);
		$rowCount =mysqli_num_rows($result);
		if($rowCount != 0){
			$tokenList = [];
			$row = mysqli_fetch_assoc($result);
			$restName = $row["RestName"];
			$token = $row["Token"];
			array_push($tokenList, $token);
			$tokens = implode(",", $tokenList);
			$title = "";
			$body = "Your order has accepted by $restName, on the way to kitchen";
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
	}

	// Ready 
	if($status == 3){
		$sql = "SELECT d.Token FROM MyOrders mo join CustomerMaster cm on mo.CustId = cm.CustId join Device d on mo.CustId = d.UserId where mo.OrderId = $orderId and d.AppName = 1";
		$result = mysqli_query($conn,$sql);
		$rowCount =mysqli_num_rows($result);
		if($rowCount != 0){
			$tokenList = [];
			$row = mysqli_fetch_assoc($result);
			$token = $row["Token"];
			array_push($tokenList, $token);
			$tokens = implode(",", $tokenList);
			$title = "";
			$body = "Yeah ! Your order is Ready, will be picked up soon";
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
	}
	// Pickup 
	if($status == 4){
		$sql = "SELECT d.Token FROM MyOrders mo join CustomerMaster cm on mo.CustId = cm.CustId join Device d on mo.CustId = d.UserId where mo.OrderId = $orderId and d.AppName = 1";
		$result = mysqli_query($conn,$sql);
		$rowCount =mysqli_num_rows($result);
		if($rowCount != 0){
			$tokenList = [];
			$row = mysqli_fetch_assoc($result);
			$token = $row["Token"];
			array_push($tokenList, $token);
			$tokens = implode(",", $tokenList);
			$title = "";
			$body = "Your order has been successfully picked up by the delivery person. For more details, Track in my orders";
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
	}
	// Deliver
	if($status == 5){
		$sql = "SELECT d.Token FROM MyOrders mo join CustomerMaster cm on mo.CustId = cm.CustId join Device d on mo.CustId = d.UserId where mo.OrderId = $orderId and d.AppName = 1";
		$result = mysqli_query($conn,$sql);
		$rowCount =mysqli_num_rows($result);
		if($rowCount != 0){
			$tokenList = [];
			$row = mysqli_fetch_assoc($result);
			$token = $row["Token"];
			array_push($tokenList, $token);
			$tokens = implode(",", $tokenList);
			$title = "";
			$body = "Delivered to you ! Enjoy the food , thanks for choosing mealsfly as delivery partner";
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
	}
	// Cancel
	if($status == 0){
		// When order deliver, then notification send to customer.
		// Get Customer
		$sql = "SELECT cm.Name, mo.TotalPrice, d.Token FROM MyOrders mo join CustomerMaster cm on mo.CustId = cm.CustId join Device d on mo.CustId = d.UserId where mo.OrderId = $orderId and cm.IsActive = 1 and d.AppName = 1";
		$result = mysqli_query($conn,$sql);
		$rowCount =mysqli_num_rows($result);
		if($rowCount != 0){
			$tokenList = [];
			$row = mysqli_fetch_assoc($result);
			$custName = $row["Name"];
			$totalPrice = $row["TotalPrice"];
			$token = $row["Token"];
			array_push($tokenList, $token);
			$tokens = implode(",", $tokenList);
			$title = "Order ".$statusTxt;
			$body = "Hi $custName, your order is $statusTxt of ₹ $totalPrice";
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
	}
	// Ready
	if($status == 3){
		// When food is ready, then notication send to rider..
		$sql = "SELECT d.Token FROM MyOrders mo join Device d on mo.RiderId = d.UserId and d.AppName = 3 where mo.OrderId = $orderId";
		$result = mysqli_query($conn,$sql);
		$rowCount =mysqli_num_rows($result);
		if($rowCount != 0){
			$tokenList = [];
			while($row = mysqli_fetch_assoc($result)){
				array_push($tokenList, $row["Token"]);
			}
			$tokens = implode(",", $tokenList);
			$title = "Order ready";
			$body = "Order ready for pickup, please reach restaurant location as soon as.";
			$image = "";
			$link = "";

			$orderJson = new StdClass;
			$appName = "Rider";
			require_once 'FirebaseNotificationClass.php';
			$classObj = new FirebaseNotificationClass();
			$notiResult = $classObj->sendNotification($appName, $tokens, $title, $body, $image, $link, $orderJson);
			$notificationResult = json_decode($notiResult);
			$notificationStatus = $notificationResult->success;
			$notiSql = "UPDATE `MyOrders` set `IsSendNotification`=$notificationStatus, `Tokens`='$tokens' where `OrderId` = $orderId";
			$notiStmt = $conn->prepare($notiSql);
			$notiStmt->execute();
		}
	}
}

?>