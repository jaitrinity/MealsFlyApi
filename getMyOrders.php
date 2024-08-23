<?php
include("dbConfiguration.php");
$custId = $_REQUEST["custId"];
$sql = "SELECT o.OrderId, res.Name as RestName, res.LatLong as RestLatlong, o.TotalPrice, o.DeliveryCharge, o.GrandTotal, ca.Name as CustomerName, ca.Contact as CustomerContact, concat(ca.Name, ', ', ca.Address, ', ', ca.City, ', ', ca.Pincode, ', ', ca.State) as Address, ca.LatLong, o.PaymentMode, o.PaymentStatus, o.Instruction, o.Status, os.StatusTxt, date_format(o.OrderDatetime,'%d-%b-%Y %H:%i') as OrderDatetime, date_format(o.PreparingDatetime,'%d-%b-%Y %H:%i') as PreparingDatetime, date_format(o.ReadyDatetime,'%d-%b-%Y %H:%i') as ReadyDatetime, date_format(o.PickedUpDatetime,'%d-%b-%Y %H:%i') as PickedUpDatetime, date_format(o.DeliveredDatetime,'%d-%b-%Y %H:%i') as DeliveredDatetime, date_format(o.CancelledDatetime,'%d-%b-%Y %H:%i') as CancelledDatetime, o.CancellationMsg, o.RiderId, o.SelfAccept FROM MyOrders o join CustomerAddress ca on o.CustAddId = ca.CustAddId join OrderStatus os on o.Status = os.Status join RestaurantMaster res on o.RestId = res.RestId where o.Status != 7 and o.CustId = $custId ORDER by o.OrderDatetime";
$result = mysqli_query($conn,$sql);
$orderList = array();
while($row = mysqli_fetch_assoc($result)){
	$orderId = $row["OrderId"];
	$status = $row["Status"];
	$statusTxt = $row["StatusTxt"];
	$orderItemList = array();
	$sql1 = "SELECT im.Name, oi.Unit, oi.Quantity, oi.Price FROM MyOrderItems oi join ItemMaster im on oi.ItemId = im.ItemId where oi.OrderId = $orderId";
	$result1 = mysqli_query($conn,$sql1);
	while($row1 = mysqli_fetch_assoc($result1)){
		$orderItemJson = array(
			'itemName' => $row1["Name"],
			'size' => $row1["Unit"],
			'quantity' => $row1["Quantity"],
			'price' => $row1["Price"]
		);
		array_push($orderItemList, $orderItemJson);
	}

	$riderId = $row["RiderId"];
	$sql2 = "SELECT * FROM `DeliveryBoyMaster` where `RiderId` = $riderId";
	$result2 = mysqli_query($conn,$sql2);
	$riderRow = mysqli_num_rows($result2);
	$riderInfo = new StdClass;
	if($riderRow !=0){
		$row2 = mysqli_fetch_assoc($result2);
		$riderInfo = array(
			'riderId' => intval($row2["RiderId"]),
			'name' => $row2["Name"],
			'mobile' => intval($row2["Mobile"])
		);
	}
	
	$statusDatetime = "";
	if($status == 0) $statusDatetime = $row["CancelledDatetime"];
	else if($status == 2) $statusDatetime = $row["PreparingDatetime"];
	else if($status == 3) $statusDatetime = $row["ReadyDatetime"];
	else if($status == 4) $statusDatetime = $row["PickedUpDatetime"];
	else if($status == 5) $statusDatetime = $row["DeliveredDatetime"];

	$orderJson = array(
		'orderId' => $orderId,
		'restName' => $row["RestName"],
		'restLatlong' => $row["RestLatlong"],
		'totalPrice' => $row["TotalPrice"],
		'deliveryCharge' => $row["DeliveryCharge"],
		'grandTotal' => $row["GrandTotal"],
		'customerName' => $row["CustomerName"],
		'contact' => $row["CustomerContact"],
		'address' => $row["Address"],
		'latLong' => $row["LatLong"],
		'paymentMode' => $row["PaymentMode"],
		'paymentStatus' => $row["PaymentStatus"],
		'instruction' => $row["Instruction"],
		'orderDatetime' => $row["OrderDatetime"],
		'statusDatetime' => $statusDatetime,
		// 'preparingDatetime' => $row["PreparingDatetime"],
		// 'readyDatetime' => $row["ReadyDatetime"],
		// 'pickedUpDatetime' => $row["PickedUpDatetime"],
		// 'deliveredDatetime' => $row["DeliveredDatetime"],
		// 'cancelledDatetime' => $row["CancelledDatetime"],
		'cancellationMsg' => $row["CancellationMsg"] == null ? "" : $row["CancellationMsg"],
		'status' => $status,
		'statusTxt' => $statusTxt,
		'selfAccept' => $row["SelfAccept"],
		'riderInfo' => $riderInfo,
		'orderItemList' => $orderItemList

	);
	array_push($orderList, $orderJson);
}
echo json_encode($orderList);
?>