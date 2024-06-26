<?php 
include("dbConfiguration.php");
$riderId = $_REQUEST["riderId"];
// $sql = "SELECT mo.OrderId, ca.Name as CustomerName, ca.Contact as CustomerContact, ca.LatLong as CustomerLatlong, concat(ca.Address, ',', ca.City, ',', ca.Pincode, ',', ca.State) as CustomerAddress, rm.Name as RestaurantName, rm.Mobile as RestMobile, rm.Address as RestAddress, rm.LatLong as RestaurantLatlong, mo.TotalPrice, mo.DeliveryCharge, mo.GrandTotal, mo.PaymentMode, mo.Instruction, mo.Status, date_format(mo.OrderDatetime,'%d-%b-%Y %H:%i') as OrderDatetime, date_format(mo.PreparingDatetime,'%d-%b-%Y %H:%i') as PreparingDatetime, date_format(mo.ReadyDatetime,'%d-%b-%Y %H:%i') as ReadyDatetime, date_format(mo.PickedUpDatetime,'%d-%b-%Y %H:%i') as PickedUpDatetime, date_format(mo.DeliveredDatetime,'%d-%b-%Y %H:%i') as DeliveredDatetime, date_format(mo.CancelledDatetime,'%d-%b-%Y %H:%i') as CancelledDatetime, mo.CancellationMsg FROM MyOrders mo join RestaurantMaster rm on mo.RestId = rm.RestId join CustomerAddress ca on mo.CustAddId = ca.CustAddId where mo.Status != 7 and mo.RiderId = $riderId";

$sql = "SELECT mo.OrderId, ca.Name as CustomerName, ca.Contact as CustomerContact, ca.LatLong as CustomerLatlong, concat(ca.Address, ',', ca.City, ',', ca.Pincode, ',', ca.State) as CustomerAddress, rm.Name as RestaurantName, rm.Mobile as RestMobile, rm.Address as RestAddress, rm.LatLong as RestaurantLatlong, mo.TotalPrice, mo.DeliveryCharge, mo.GrandTotal, mo.PaymentMode, mo.Instruction, mo.Status, date_format(mo.OrderDatetime,'%d-%b-%Y %H:%i') as OrderDatetime, date_format(mo.PreparingDatetime,'%d-%b-%Y %H:%i') as PreparingDatetime, date_format(mo.ReadyDatetime,'%d-%b-%Y %H:%i') as ReadyDatetime, date_format(mo.PickedUpDatetime,'%d-%b-%Y %H:%i') as PickedUpDatetime, date_format(mo.DeliveredDatetime,'%d-%b-%Y %H:%i') as DeliveredDatetime, date_format(mo.CancelledDatetime,'%d-%b-%Y %H:%i') as CancelledDatetime, mo.CancellationMsg FROM MyOrders mo join DeliveryBoyMaster dm on mo.RiderId=dm.RiderId and dm.IsActive=1 join RestaurantMaster rm on mo.RestId = rm.RestId join CustomerAddress ca on mo.CustAddId = ca.CustAddId where mo.Status != 7 and mo.RiderId = $riderId";
$result = mysqli_query($conn,$sql);
$rowCount = mysqli_num_rows($result);
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
		'customerContact' => $row["CustomerContact"],
		'customerAddress' => $row["CustomerAddress"],
		'customerLatlong' => $row["CustomerLatlong"],
		'restaurantName' => $row["RestaurantName"],
		'restaurantMobile' => $row["RestMobile"],
		'restaurantAddress' => $row["RestAddress"],
		'restaurantLatlong' => $row["RestaurantLatlong"],
		'totalPrice' => $row["TotalPrice"],
		'deliveryCharge' => $row["DeliveryCharge"],
		'grandTotal' => $row["GrandTotal"],
		'paymentMode' => $row["PaymentMode"],
		'instruction' => $row["Instruction"],
		'status' => $row["Status"],
		'orderDatetime' => $row["OrderDatetime"],
		'statusDatetime' => $statusDatetime,
		'cancellationMsg' => $row["CancellationMsg"] == null ? "" : $row["CancellationMsg"],
		'status' => $status,
		'orderItemList' => $orderItemList
	);
	array_push($orderList, $orderJson);
}
echo json_encode($orderList);

?>