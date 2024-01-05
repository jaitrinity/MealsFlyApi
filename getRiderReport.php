<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$fromDate = $jsonData->fromDate;
$toDate = $jsonData->toDate;
$riderId = $jsonData->riderId;
$filterSql = "";
if($fromDate != ""){
	 $filterSql .= "and date_format(mo.`OrderDatetime`,'%Y-%m-%d') >= '$fromDate'";
}
if($toDate != ""){
	$filterSql .= "and date_format(mo.`OrderDatetime`,'%Y-%m-%d') <= '$toDate'";
}


$sql = "SELECT mo.OrderId, rm.Name as RestName, cm.Name as CustName, dm.Name as RiderName, mo.PaymentMode, mo.PaymentType, mo.GrandTotal, mo.TotalPrice, date_format(mo.OrderDatetime,'%d-%b-%Y %H:%i') as OrderDatetime, mo.Status, os.StatusTxt, os.StatusColor, mo.ReceiveAmount FROM MyOrders mo join CustomerAddress cm on mo.CustAddId = cm.CustAddId join RestaurantMaster rm on mo.RestId = rm.RestId join OrderStatus os on mo.Status = os.Status join DeliveryBoyMaster dm on mo.RiderId = dm.RiderId where 1=1 and mo.PaymentMode = 'COD' and mo.RiderId = $riderId and mo.Status = 5 $filterSql ORDER by mo.OrderId desc";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$riderReportList = array();
$cashCount = 0;
$cashAmount = 0;
$onlineCount = 0;
$onlineAmount = 0;
$totalCount = 0;
$totalAmount = 0;

while($row = mysqli_fetch_assoc($result)){
	$paymentType = $row["PaymentType"]; // 1=Cash,2=Online
	$grandTotal = $row["GrandTotal"];
	// $paymentTypeTxt = "";
	// if($paymentType == 1) $paymentTypeTxt = "Cash";
	// else if($paymentType == 2) $paymentTypeTxt = "Online";
	$receiveAmount = $row["ReceiveAmount"] == null ? "" : $row["ReceiveAmount"];
	$receiveStatus = "Pending";
	if($receiveAmount != ""){
		$receiveStatus = "Received";
	}
	else{
		$totalAmount += $grandTotal;
		$totalCount++;
		if($paymentType == 1){
			$cashAmount += $grandTotal;
			$cashCount++;
		}
		else if($paymentType == 2){
			$onlineAmount += $grandTotal;
			$onlineCount++;
		}
	}
	$obj = array(
		'orderId' => $row["OrderId"],
		'custName' => $row["CustName"], 
		'restName' => $row["RestName"],
		// 'riderName' => $row["RiderName"],
		// 'paymentMode' => $row["PaymentMode"],
		'grandTotal' => strval($row["GrandTotal"]),
		// 'subTotal' => strval($row["TotalPrice"]),
		'orderDatetime' => $row["OrderDatetime"],
		'paymentType' => $paymentType,
		// 'paymentTypeTxt' => $paymentTypeTxt,
		// 'status' => $row["Status"],
		// 'statusTxt' => $row["StatusTxt"],
		// 'statusColor' => $row["StatusColor"],
		'receiveStatus' => $receiveStatus
	);
	array_push($riderReportList, $obj);
}
$output = array(
	// 'totalCount' => $totalCount,
	// 'totalAmount' => $totalAmount,
	'cashCount' => $cashCount,
	'cashAmount' => $cashAmount,
	'onlineCount' => $onlineCount,
	'onlineAmount' => $onlineAmount,
	'riderReportList' => $riderReportList, 
	
);
echo json_encode($output);

?>