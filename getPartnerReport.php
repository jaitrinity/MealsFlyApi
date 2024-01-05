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
$restId = $jsonData->restId;
$filterSql = "";
if($fromDate != ""){
	 $filterSql .= "and date_format(mo.`OrderDatetime`,'%Y-%m-%d') >= '$fromDate'";
}
if($toDate != ""){
	$filterSql .= "and date_format(mo.`OrderDatetime`,'%Y-%m-%d') <= '$toDate'";
}

$sql = "SELECT mo.OrderId, rm.Name as RestName, cm.Name as CustName, rm.Commission, mo.GrandTotal, mo.TotalPrice, date_format(mo.OrderDatetime,'%d-%b-%Y %H:%i') as OrderDatetime, mo.Status, os.StatusTxt, os.StatusColor, mo.PayableAmount FROM MyOrders mo join CustomerAddress cm on mo.CustAddId = cm.CustAddId join RestaurantMaster rm on mo.RestId = rm.RestId join OrderStatus os on mo.Status = os.Status where 1=1 and mo.RestId = $restId and mo.Status = 5 $filterSql ORDER by mo.OrderId desc";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$partnerReportList = array();
$totalCount = 0;
$totalAmount = 0;
while($row = mysqli_fetch_assoc($result)){
	$orderId = $row["OrderId"];
	$restName = $row["RestName"];
	$custName = $row["CustName"];
	$commission = $row["Commission"];
	$grandTotal = $row["GrandTotal"];
	$subTotal = $row["TotalPrice"];
	$status = $row["Status"];
	$calPayableAmount = ($subTotal * $commission)/100;
	$payableAmount = $row["PayableAmount"] == null ? "" : $row["PayableAmount"];
	$paymentStatus = "Pending";
	if($payableAmount != ""){
		$paymentStatus = "Paid";	
	} 
	else{
		$payableAmount = $calPayableAmount;
		$totalAmount += $calPayableAmount;
		$totalCount++;
	}

	$obj = array(
		'orderId' => $orderId, 
		// 'restName' => $restName,
		'custName' => $custName,
		// 'grandTotal' => $grandTotal,
		'subTotal' => $subTotal,
		'orderDatetime' => $row["OrderDatetime"],
		// 'status' => $status,
		// 'statusTxt' => $row["StatusTxt"],
		// 'statusColor' => $row["StatusColor"],
		'payableAmount' => $payableAmount,
		'paymentStatus' => $paymentStatus
	);
	array_push($partnerReportList, $obj);
}
$output = array(
	'totalAmount' => $totalAmount,
	'totalCount' => $totalCount,
	'partnerReportList' => $partnerReportList
);
echo json_encode($output);
?>