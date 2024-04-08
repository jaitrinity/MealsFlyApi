<?php 
include("dbConfiguration.php");
$restId = $_REQUEST["restId"];
$sql = "SELECT mo.OrderId, ca.Name as CustomerName, ca.Contact as CustomerContact, ca.LatLong, concat(ca.Address,',',ca.City,',',ca.Pincode,',',ca.State) as CustomerAddress, mo.TotalPrice, mo.DeliveryCharge, mo.GrandTotal, mo.PaymentMode, mo.Instruction, mo.Status, date_format(mo.OrderDatetime,'%d-%b-%Y %H:%i') as OrderDatetime, date_format(mo.PreparingDatetime,'%d-%b-%Y %H:%i') as PreparingDatetime, date_format(mo.ReadyDatetime,'%d-%b-%Y %H:%i') as ReadyDatetime, date_format(mo.PickedUpDatetime,'%d-%b-%Y %H:%i') as PickedUpDatetime, date_format(mo.DeliveredDatetime,'%d-%b-%Y %H:%i') as DeliveredDatetime, date_format(mo.CancelledDatetime,'%d-%b-%Y %H:%i') as CancelledDatetime, mo.CancellationMsg, mo.RiderId FROM MyOrders mo join CustomerAddress ca on mo.CustAddId = ca.CustAddId where mo.Status != 7 and mo.RestId = $restId ORDER by mo.OrderDatetime DESC";
$result = mysqli_query($conn,$sql);
$orderList = array();
while($row = mysqli_fetch_assoc($result)){
	$orderId = $row["OrderId"];
	$sql1 = "SELECT im.Name as ItemName, cm.CatId, cm.Name as CatName, oi.Unit, oi.Quantity, oi.Price FROM MyOrderItems oi join CategoryMaster cm on oi.CatId = cm.CatId join ItemMaster im on oi.ItemId = im.ItemId where oi.OrderId = $orderId";
	$result1 = mysqli_query($conn,$sql1);
	$orderItemList = array();
	while($row1 = mysqli_fetch_assoc($result1)){
		$itemJson = array(
			'itemName' => $row1["ItemName"],
			'categoryName' => $row1["CatName"],
			'size' => $row1["Unit"],
			'quantity' => $row1["Quantity"],
			'price' => $row1["Price"]
		);
		array_push($orderItemList, $itemJson);
	}

	$riderId = $row["RiderId"];
	$sql2 = "SELECT * FROM `DeliveryBoyMaster` where `RiderId` = $riderId";
	$result2 = mysqli_query($conn,$sql2);
	$riderRow = mysqli_num_rows($result2);
	$riderInfo = new StdClass;
	if($riderRow !=0){
		$row2 = mysqli_fetch_assoc($result2);
		$riderInfo = array(
			'riderId' => $row2["RiderId"],
			'name' => $row2["Name"],
			'mobile' => $row2["Mobile"]
		);
	}

	$status = $row["Status"];
	$statusDatetime = "";
	if($status == 0) $statusDatetime = $row["CancelledDatetime"];
	else if($status == 2) $statusDatetime = $row["PreparingDatetime"];
	else if($status == 3) $statusDatetime = $row["ReadyDatetime"];
	else if($status == 4) $statusDatetime = $row["PickedUpDatetime"];
	else if($status == 5) $statusDatetime = $row["DeliveredDatetime"];
	$orderJson = array(
		'orderId' => $row["OrderId"],
		'customerName' => $row["CustomerName"],
		'latLong' => $row["LatLong"],
		'contact' => $row["CustomerContact"],
		'address' => $row["CustomerAddress"],
		'totalPrice' => $row["TotalPrice"],
		'deliveryCharge' => $row["DeliveryCharge"],
		'grandTotal' => $row["GrandTotal"],
		'paymentMode' => $row["PaymentMode"],
		'instruction' => $row["Instruction"],
		'status' => $row["Status"],
		'orderDatetime' => $row["OrderDatetime"],
		'statusDatetime' => $statusDatetime,
		'cancellationMsg' => $row["CancellationMsg"] == null ? "" : $row["CancellationMsg"],
		'riderInfo' => $riderInfo,
		'orderItemList' => $orderItemList
	);
	array_push($orderList, $orderJson);
}
echo json_encode($orderList);
?>