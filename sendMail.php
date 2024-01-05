<?php 
include("dbConfiguration.php");
require 'SendMailClass.php';

$toMailId = "ordersalertmealsfly@gmail.com";
// $toMailId = "jai.prakash@trinityapplab.co.in";
// $ccMailId = "pushkar.tyagi@trinityapplab.co.in";
$ccMailId = "";
$bccMailId = "";
$greeting = "Dear Mealsfly Admin,<br><br>";
$regard = "Regards,<br>";
$regard .= "Trinity automation team...<br>";

// Order not accepted by restaurant till 10 minute
$sql1="SELECT t.OrderId, rm.Name as RestName, rm.Mobile as RestMobile, ca.Name as CustName, t.OrderDatetime from( SELECT *, TIMESTAMPDIFF(Minute,`OrderDatetime`,CURRENT_TIMESTAMP) as `MinuteDiff` FROM `MyOrders` where `Status` = 1) t join RestaurantMaster rm on t.RestId = rm.RestId join CustomerAddress ca on t.CustAddId = ca.CustAddId where t.MinuteDiff > 10";
$stmt1 = $conn->prepare($sql1);
$stmt1->execute();
$query1 = $stmt1->get_result();
$msg1 = $greeting;
$msg1 .= "Following order is pending with restaurant partner:<br><br>";
$response1 = "A-";
while($row1 = mysqli_fetch_assoc($query1)){
	$orderId = $row1["OrderId"];
	$restName = $row1["RestName"];
	$restMobile = $row1["RestMobile"];
	// $custName = $row1["CustName"];
	$orderDatetime = $row1["OrderDatetime"];
	$orderDatetime = date("d-M-Y H:i:s", strtotime($orderDatetime));
	$msg1 .= "<b>Order id:</b> $orderId <br>";
	$msg1 .= "<b>Restaurant Name:</b> $restName <br>";
	$msg1 .= "<b>Contact No:</b> $restMobile <br>";
	$msg1 .= "<b>Order Date & Time:</b> $orderDatetime <br>";
	$msg1 .= "<b>Order Details:</b> <br>";
	$sql11 = "SELECT im.Name as ItemName, oi.Unit, oi.Quantity, oi.Price FROM MyOrderItems oi join ItemMaster im on oi.ItemId = im.ItemId where oi.OrderId = $orderId";
	$stmt11 = $conn->prepare($sql11);
	$stmt11->execute();
	$query11 = $stmt11->get_result();
	$msg1 .= "<table border=1 cellspacing=0 cellpadding=5>
				<thead>
				<tr> 
				<th>Item Name</th> 
				<th>Unit</th> 
				<th>Quantity</th> 
				<th>Price</th> 
				</tr> 
				<tbody>";
	while($row11 = mysqli_fetch_assoc($query11)){
		$msg1 .= "<tr>
					<td>".$row11['ItemName']."</td>
					<td>".$row11['Unit']."</td>
					<td>".$row11['Quantity']."</td>
					<td style='text-align:right'>₹ ".$row11['Price']."</td>
					</tr>";
	}
	$msg1 .= "</tbody></table> <br><br>";
}
$msg1 .= $regard;
$subject1 = "Order not accepted by restaurant";
$classObj1 = new SendMailClass();
$response1 .= $classObj1->sendMail($toMailId, $ccMailId, $bccMailId, $subject1, $msg1, null);


// Order not pickup order by rider till 20 minute
$sql2 = "SELECT t.OrderId, rm.Name as RestName, ca.Name as CustName, t.OrderDatetime, dm.Name as RiderName, dm.Mobile as RiderMobile from( SELECT *, TIMESTAMPDIFF(Minute,`ReadyDatetime`,CURRENT_TIMESTAMP) as `MinuteDiff` FROM `MyOrders` where `Status` = 3) t join RestaurantMaster rm on t.RestId = rm.RestId join CustomerAddress ca on t.CustAddId = ca.CustAddId join DeliveryBoyMaster dm on t.RiderId = dm.RiderId where t.MinuteDiff > 20";
$stmt2 = $conn->prepare($sql2);
$stmt2->execute();
$query2 = $stmt2->get_result();
$msg2 = $greeting;
$msg2 .= "Following order is pending for pickup:<br><br>";
$response2 = "B-";
while($row2 = mysqli_fetch_assoc($query2)){
	$orderId = $row2["OrderId"];
	// $restName = $row2["RestName"];
	// $custName = $row2["CustName"];
	$orderDatetime = $row2["OrderDatetime"];
	$orderDatetime = date("d-M-Y H:i:s", strtotime($orderDatetime));
	$riderName = $row2["RiderName"];
	$riderMobile = $row2["RiderMobile"];

	$msg2 .= "<b>Order id:</b> $orderId <br>";
	$msg2 .= "<b>Delivery boy:</b> $riderName <br>";
	$msg2 .= "<b>Contact No:</b> $riderMobile <br>";
	$msg2 .= "<b>Order Date & Time:</b> $orderDatetime <br>";
	$msg2 .= "<b>Order Details:</b> <br>";

	$sql22 = "SELECT im.Name as ItemName, oi.Unit, oi.Quantity, oi.Price FROM MyOrderItems oi join ItemMaster im on oi.ItemId = im.ItemId where oi.OrderId = $orderId";
	$stmt22 = $conn->prepare($sql22);
	$stmt22->execute();
	$query22 = $stmt22->get_result();
	$msg2 .= "<table border=1 cellspacing=0 cellpadding=5>
				<thead>
				<tr> 
				<th>Item Name</th> 
				<th>Unit</th> 
				<th>Quantity</th> 
				<th>Price</th> 
				</tr> 
				<tbody>";
	while($row22 = mysqli_fetch_assoc($query22)){
		$msg2 .= "<tr>
					<td>".$row22['ItemName']."</td>
					<td>".$row22['Unit']."</td>
					<td>".$row22['Quantity']."</td>
					<td style='text-align:right'>₹ ".$row22['Price']."</td>
					</tr>";
	}
	$msg2 .= "</tbody></table> <br>";
}
$msg2 .= $regard;
$subject2 = "Order not pick-up by rider.";
$classObj2 = new SendMailClass();
$response2 .= $classObj2->sendMail($toMailId, $ccMailId, $bccMailId, $subject2, $msg2, null);

// Rider not allocated to order
$sql3="SELECT mo.OrderId, ca.Name as CustName, ca.Contact as CustContact, mo.OrderDatetime FROM MyOrders mo join CustomerAddress ca on mo.CustAddId = ca.CustAddId where mo.Status = 2 and mo.OrderAcceptDatetime is null";
$stmt3 = $conn->prepare($sql3);
$stmt3->execute();
$query3 = $stmt3->get_result();
$msg3 = $greeting;
$msg3 .= "Following order is not accepted by any delivery partner.<br><br>";
$response3 = "C-";
while($row3 = mysqli_fetch_assoc($query3)){
	$orderId = $row3["OrderId"];
	$custName = $row3["CustName"];
	$custMobile = $row3["CustContact"];
	$orderDatetime = $row3["OrderDatetime"];
	$orderDatetime = date("d-M-Y H:i:s", strtotime($orderDatetime));

	$msg3 .= "<b>Order id:</b> $orderId <br>";
	$msg3 .= "<b>Customer Name:</b> $custName <br>";
	$msg3 .= "<b>Customer Mobile:</b> $custMobile <br>";
	$msg3 .= "<b>Order Date & Time:</b> $orderDatetime <br>";
	$msg3 .= "<b>Order Details:</b> <br>";

	$sql33 = "SELECT im.Name as ItemName, oi.Unit, oi.Quantity, oi.Price FROM MyOrderItems oi join ItemMaster im on oi.ItemId = im.ItemId where oi.OrderId = $orderId";
	$stmt33 = $conn->prepare($sql22);
	$stmt33->execute();
	$query33 = $stmt33->get_result();
	$msg3 .= "<table border=1 cellspacing=0 cellpadding=5>
				<thead>
				<tr> 
				<th>Item Name</th> 
				<th>Unit</th> 
				<th>Quantity</th> 
				<th>Price</th> 
				</tr> 
				<tbody>";
	while($row33 = mysqli_fetch_assoc($query33)){
		$msg3 .= "<tr>
					<td>".$row33['ItemName']."</td>
					<td>".$row33['Unit']."</td>
					<td>".$row33['Quantity']."</td>
					<td style='text-align:right'>₹ ".$row33['Price']."</td>
					</tr>";
	}
	$msg3 .= "</tbody></table> <br>";
}
$msg3 .= $regard;
$subject3 = "Order not allocated to rider";
$classObj3 = new SendMailClass();
$response3 .= $classObj3->sendMail($toMailId, $ccMailId, $bccMailId, $subject3, $msg3, null);


header('Content-Type: text/html');
echo $msg1.'<br>'.$msg2.'<br>'.$msg3;
?>