<?php
include("dbConfiguration.php");
$orderId = $_REQUEST["orderId"];
$sql = "SELECT DISTINCT cm.CatId, cm.Name FROM MyOrderItems oi join CategoryMaster cm on oi.CatId = cm.CatId where oi.OrderId = $orderId";
$result = mysqli_query($conn,$sql);
$orderItemList = array();
while($row = mysqli_fetch_assoc($result)){
	$catId = $row["CatId"];
	$catName = $row["Name"];

	$sql1 = "SELECT * FROM `MyOrderItems` where `OrderId` = $orderId and `CatId` = $catId";
	$result1 = mysqli_query($conn,$sql1);
	$itemList = array();
	while($row1 = mysqli_fetch_assoc($result1)){
		$itemJson = array(
			'size' => $row1["Unit"],
			'quantity' => $row1["Quantity"],
			'price' => $row1["Price"]
		);
		array_push($itemList, $itemJson);
	}



	$orderItemJson = array(
		'categoryName' => $catName,
		'itemList' => $itemList
	);
	array_push($orderItemList, $orderItemJson);
}
echo json_encode($orderItemList);

?>