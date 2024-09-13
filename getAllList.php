<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);
$searchType = $jsonData->searchType;
if($searchType == "allRider"){
	// $sql = "SELECT `RiderId`, `Name` FROM `DeliveryBoyMaster` where `IsActive` = 1";
	$sql = "SELECT dm.RiderId, dm.Name, count(mo.RiderId) FROM DeliveryBoyMaster dm left join MyOrders mo on dm.RiderId=mo.RiderId and mo.Status=5 where dm.IsActive = 1 GROUP by dm.RiderId ORDER by count(mo.RiderId) desc";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$riderList = array();
	while($row = mysqli_fetch_assoc($query)){
		$riderJson = array(
			'riderId' => $row["RiderId"], 
			'riderName' => $row["Name"]
		);
		array_push($riderList, $riderJson);
	}
	echo json_encode($riderList);
}
else if($searchType == "allRestaurant"){
	// $sql = "SELECT `RestId`, `Name` FROM `RestaurantMaster` where `IsActive`=1 and `Enable`=1 order by `pincode`,`DisplayOrder`";
	$sql = "SELECT re.RestId, re.Name, count(mo.RestId) FROM RestaurantMaster re left join MyOrders mo on re.RestId=mo.RestId and mo.Status=5 where re.IsActive=1 and re.Enable=1 GROUP by re.RestId ORDER by count(mo.RestId) desc";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$restList = array();
	while($row = mysqli_fetch_assoc($query)){
		$restJson = array(
			'restId' => $row["RestId"], 
			'restName' => $row["Name"]
		);
		array_push($restList, $restJson);
	}
	echo json_encode($restList);
}
else if($searchType == "riderReport"){
	$filterFromDate = $jsonData->filterFromDate;
	$filterToDate = $jsonData->filterToDate;
	$filterRider = $jsonData->filterRider;

	$filterSql = "";
	if($filterFromDate != ""){
		 $filterSql .= "and date_format(mo.`OrderDatetime`,'%Y-%m-%d') >= '$filterFromDate'";
	}
	if($filterToDate != ""){
		$filterSql .= "and date_format(mo.`OrderDatetime`,'%Y-%m-%d') <= '$filterToDate'";
	}

	$sql = "SELECT mo.OrderId, rm.Name as RestName, cm.Name as CustName, dm.Name as RiderName, mo.PaymentMode, mo.PaymentType, mo.GrandTotal, mo.TotalPrice, date_format(mo.OrderDatetime,'%d-%b-%Y %H:%i') as OrderDatetime, mo.Status, os.StatusTxt, os.StatusColor, mo.ReceiveAmount FROM MyOrders mo join CustomerAddress cm on mo.CustAddId = cm.CustAddId join RestaurantMaster rm on mo.RestId = rm.RestId join OrderStatus os on mo.Status = os.Status join DeliveryBoyMaster dm on mo.RiderId = dm.RiderId where 1=1 and mo.PaymentMode = 'COD' and mo.RiderId = $filterRider and mo.Status = 5 $filterSql ORDER by mo.OrderId desc";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->get_result();
	$riderReportList = array();
	$cashCount = 0;
	$cashAmount = 0;
	$onlineCount = 0;
	$onlineAmount = 0;
	$totalCount = 0;
	$totalReceiveAmount = 0;

	while($row = mysqli_fetch_assoc($result)){
		$paymentType = $row["PaymentType"]; // 1=Cash,2=Online
		$grandTotal = $row["GrandTotal"];
		$paymentTypeTxt = "";
		if($paymentType == 1) $paymentTypeTxt = "Cash";
		else if($paymentType == 2) $paymentTypeTxt = "Online";
		$receiveAmount = $row["ReceiveAmount"] == null ? "" : $row["ReceiveAmount"];
		$receiveStatus = "Pending";
		if($receiveAmount != ""){
			$receiveStatus = "Received";
		}
		else{
			$totalReceiveAmount += $grandTotal;
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
			'orderId' => strval($row["OrderId"]),
			'custName' => $row["CustName"], 
			'restName' => $row["RestName"],
			'riderName' => $row["RiderName"],
			'paymentMode' => $row["PaymentMode"],
			'grandTotal' => strval($row["GrandTotal"]),
			// 'subTotal' => strval($row["TotalPrice"]),
			'orderDatetime' => $row["OrderDatetime"],
			'paymentTypeTxt' => $paymentTypeTxt,
			'status' => $row["Status"],
			'statusTxt' => $row["StatusTxt"],
			'statusColor' => $row["StatusColor"],
			'receiveStatus' => $receiveStatus
		);
		array_push($riderReportList, $obj);
	}
	$output = array(
		'riderReportList' => $riderReportList, 
		'totalCount' => $totalCount,
		'totalReceiveAmount' => $totalReceiveAmount,
		'cashCount' => $cashCount,
		'cashAmount' => $cashAmount,
		'onlineCount' => $onlineCount,
		'onlineAmount' => $onlineAmount
	);
	echo json_encode($output);
}
else if($searchType == "partnerReport"){
	$filterFromDate = $jsonData->filterFromDate;
	$filterToDate = $jsonData->filterToDate;
	$filterRestaurant = $jsonData->filterRestaurant;
	$filterSql = "";
	if($filterFromDate != ""){
		 $filterSql .= "and date_format(mo.`OrderDatetime`,'%Y-%m-%d') >= '$filterFromDate'";
	}
	if($filterToDate != ""){
		$filterSql .= "and date_format(mo.`OrderDatetime`,'%Y-%m-%d') <= '$filterToDate'";
	}

	$sql = "SELECT mo.OrderId, rm.Name as RestName, rm.Commission, mo.GrandTotal, mo.TotalPrice, date_format(mo.OrderDatetime,'%d-%b-%Y %H:%i') as OrderDatetime, mo.Status, os.StatusTxt, os.StatusColor, mo.PayableAmount FROM MyOrders mo join RestaurantMaster rm on mo.RestId = rm.RestId join OrderStatus os on mo.Status = os.Status where 1=1 and mo.RestId = $filterRestaurant and mo.Status = 5 $filterSql ORDER by mo.OrderId desc";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->get_result();
	$partnerReportList = array();
	$totalCount = 0;
	$totalPayableAmount = 0;
	while($row = mysqli_fetch_assoc($result)){
		$orderId = $row["OrderId"];
		$restName = $row["RestName"];
		$commission = $row["Commission"];
		// $commission = 77;
		$grandTotal = $row["GrandTotal"];
		$subTotal = $row["TotalPrice"];
		$status = $row["Status"];
		$calPayableAmount = ($subTotal * $commission)/100;
		$calPayableAmount = round($calPayableAmount);
		$payableAmount = $row["PayableAmount"] == null ? "" : $row["PayableAmount"];
		$paymentStatus = "Pending";
		if($payableAmount != ""){
			$paymentStatus = "Paid";	
		} 
		else{
			$payableAmount = $calPayableAmount;
			$totalPayableAmount += $calPayableAmount;
			$totalCount++;
		}

		$obj = array(
			'orderId' => strval($orderId), 
			'restName' => $restName,
			'grandTotal' => strval($grandTotal),
			'subTotal' => strval($subTotal),
			'orderDatetime' => $row["OrderDatetime"],
			'status' => $status,
			'statusTxt' => $row["StatusTxt"],
			'statusColor' => $row["StatusColor"],
			'payableAmount' => strval($payableAmount),
			'paymentStatus' => $paymentStatus
		);
		array_push($partnerReportList, $obj);
	}
	$output = array(
		'partnerReportList' => $partnerReportList, 
		'totalPayableAmount' => $totalPayableAmount,
		'totalCount' => $totalCount
	);
	echo json_encode($output);
}
else if($searchType == "complaint"){
	$filterFromDate = $jsonData->filterFromDate;
	$filterToDate = $jsonData->filterToDate;
	$filterSql = "";
	if($filterFromDate != ""){
		 $filterSql .= "and date_format(c.`CreateDate`,'%Y-%m-%d') >= '$filterFromDate'";
	}
	if($filterToDate != ""){
		$filterSql .= "and date_format(c.`CreateDate`,'%Y-%m-%d') <= '$filterToDate'";
	}

	$sql = "SELECT c.Id, cm.Name as RaiseBy, cm.Mobile, c.Issue, c.Remark, date_format(c.CreateDate,'%d-%b-%Y %H:%i') as CreateDate, c.Status FROM Complaint c join CustomerMaster cm on c.RaiseBy = cm.CustId where 1=1 $filterSql ORDER by c.Id desc";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->get_result();
	$complaintList = array();
	while($row = mysqli_fetch_assoc($result)){
		$obj = new StdClass();
		foreach ($row as $key => $value) {
			$obj->$key = strval($value);
		}
		array_push($complaintList, $obj);
	}
	echo json_encode($complaintList);
}
else if($searchType == "resetItemList"){
	$restId = $jsonData->restId;
	$sql1 = "SELECT im.ItemId, im.Name as ItemName, im.Image as ItemImage, cm.CatId, cm.Name as CatName, im.Customize, im.IsEnable, um.Unit FROM ItemMaster im join CategoryMaster cm on im.CatId = cm.CatId join UnitMaster um on im.Customize = um.Customize where im.RestId = $restId and im.IsEditable = 0 order by im.Name";
	$stmt1 = $conn->prepare($sql1);
	$stmt1->execute();
	$result1 = $stmt1->get_result();
	$itemList = [];
	while($row = mysqli_fetch_assoc($result1)){
		$itemId = $row["ItemId"];
		$itemName = $row["ItemName"];
		// $itemImage = $row["ItemImage"] == '' ? '' : urlToBase64($row["ItemImage"]);
		$itemImage = $row["ItemImage"] == '' ? '' : $row["ItemImage"];
		$catId = $row["CatId"];
		$catName = $row["CatName"];
		$customize = $row["Customize"];
		$enable = $row["IsEnable"];
		$enableTxt = "Enable";
		if($enable != 1){
			$enableTxt = "Disabled";
		}
		$unit = $row["Unit"];
		$unitList = explode(",", $unit);

		$itemUnitList = array();
		$unitStrArr = array();
		$u=100;
		foreach ($unitList as $units) {
			$u++;
			$sql2 = "SELECT * FROM `ItemUnit` where `ItemId` = $itemId and `Unit` = '$units'";
			// echo $sql2.'--';
			$result2 = mysqli_query($conn,$sql2);
			$rowCount2 = mysqli_num_rows($result2);
			if($rowCount2 == 0){
				$unitJson = array(
					'itemUnitId' => $itemId*$u,
					'unit' => $units,
					'price' => '' 
				);
				array_push($itemUnitList, $unitJson);
			}
			else{
				while($row2 = mysqli_fetch_assoc($result2)){
					$unitJson = array(
						'itemUnitId' => $row2["ItemUnitId"],
						'unit' => $row2["Unit"],
						'price' => $row2["Price"] 
					);
					array_push($itemUnitList, $unitJson);

					$unitStr = $row2["Unit"]. " - ".$row2["Price"];
					array_push($unitStrArr, $unitStr);

				}
			}
		}

		
		// $sql2 = "SELECT * FROM `ItemUnit` where `ItemId` = $itemId";
		// $result2 = mysqli_query($conn,$sql2);
		// $itemUnitList = array();
		// $unitStrArr = array();
		// while($row2 = mysqli_fetch_assoc($result2)){
		// 	$unitJson = array(
		// 		'itemUnitId' => $row2["ItemUnitId"],
		// 		'unit' => $row2["Unit"],
		// 		'price' => $row2["Price"] 
		// 	);
		// 	array_push($itemUnitList, $unitJson);

		// 	$unitStr = $row2["Unit"]. " - ".$row2["Price"];
		// 	array_push($unitStrArr, $unitStr);

		// }
		$itemUnit = implode(": ", $unitStrArr);
		$itemJson = array(
			'itemId' => $itemId,
			'itemName' => $itemName,
			'itemImage' => $itemImage,
			'catId' => $catId,
			'catName' => $catName,
			'customize' => $customize,
			'enable' => $enable,
			'enableTxt' => $enableTxt,
			'itemUnitList' => $itemUnitList,
			'itemUnit' => $itemUnit

		);
		array_push($itemList, $itemJson);
	}
	echo json_encode($itemList);
}

else if($searchType == "restaurantData"){
	$restId = $jsonData->restId;
	$sql = "SELECT *, concat(date_format(`OpenTime`,'%r'),' - ',date_format(`CloseTime`,'%r')) as `OpenCloseTime` FROM `RestaurantMaster` where `RestId` = $restId";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = mysqli_fetch_assoc($result);
	// $image = $row["Image"] == '' ? '' :  urlToBase64($row["Image"]);
	$image = $row["Image"] == '' ? '' : $row["Image"];
	// $banner =  $row["Banner"] == '' ? '' : urlToBase64($row["Banner"]);
	$banner =  $row["Banner"] == '' ? '' : $row["Banner"];
	$enableTxt = "Pending";
	if($row["Enable"] == 1) $enableTxt = "Enable";
	else if($row["Enable"] == 2) $enableTxt = "Disabled";
	$restaurantData = array(
		'restId' => $row["RestId"], 
		'name' => $row["Name"], 
		'mobile' => $row["Mobile"], 
		'address' => $row["Address"],
		'pincode' => $row["Pincode"],
		'image' => $image, 
		'banner' => $banner, 
		'status' => $row["Status"],
		'openTime' => $row["OpenTime"],
		'closeTime' => $row["CloseTime"],
		'openCloseTime' => $row["OpenCloseTime"],
		'latLong' => $row["LatLong"], 
		'approve' => $row["Approve"], 
		'enable' => $row["Enable"],
		'enableTxt' => $enableTxt,
		'displayOrder' => $row["DisplayOrder"]
	);

	echo json_encode($restaurantData);
}	
else if($searchType == "restaurantMenu"){
	$sql = "SELECT * FROM `RestaurantMaster`";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$restList = array();
	while($row = mysqli_fetch_assoc($query)){
		$restJson = array(
			'restId' => $row["RestId"], 
			'name' => $row["Name"], 
			'routerLink' => 'rest-menu/'.$row["RestId"]
		);
		array_push($restList, $restJson);
	}
	echo json_encode($restList);
}
// else if($searchType == "menuList"){
// 	$sql = "SELECT `Id`, `Name`, `Icon`, `RouterLink` FROM `MenuMaster` where `IsActive` = 1 ORDER by `DisplayOrder`";
// 	$stmt = $conn->prepare($sql);
// 	$stmt->execute();
// 	$query = $stmt->get_result();
// 	$menuList = array();
// 	while($row = mysqli_fetch_assoc($query)){
// 		$menuJson = new StdClass();
// 		foreach ($row as $key => $value) {
// 			$key = lcfirst($key);
// 			$menuJson->$key = strval($value);
// 		}
// 		array_push($menuList, $menuJson);

// 		// $menuJson = array(
// 		// 	'menuId' => $row["Id"], 
// 		// 	'name' => $row["Name"], 
// 		// 	'routerLink' => $row["RouterLink"]
// 		// );
// 		// array_push($menuList, $menuJson);
// 	}
// 	echo json_encode($menuList);
// }
else if($searchType == "menuList"){
	$loginEmpRoleId = $jsonData->loginEmpRoleId;
	$filterSql = "";
	if($loginEmpRoleId != "1"){
		$filterSql .= "and  find_in_set(`RoleId`,$loginEmpRoleId)";
	}
	$sql = "SELECT `Id`, `Name`, `Icon`, `RouterLink` FROM `MenuMaster` where 1=1 $filterSql and `IsActive`=1 ORDER by `DisplayOrder`";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$menuList = array();
	while($row = mysqli_fetch_assoc($query)){
		$menuJson = new StdClass();
		foreach ($row as $key => $value) {
			$key = lcfirst($key);
			$menuJson->$key = strval($value);
		}
		array_push($menuList, $menuJson);
	}
	echo json_encode($menuList);
}
else if($searchType == "allCategory"){
	$restId = $jsonData->restId;
	$sql = "SELECT * FROM `CategoryMaster` where `RestId` = $restId and `IsActive` = 1";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$categoryList = array();
	while($row = mysqli_fetch_assoc($query)){
		$catJson = array(
			'catId' => $row["CatId"],
			'catName' => $row["Name"]
		);
		array_push($categoryList, $catJson);
	}
	

	$sql = "SELECT * FROM `UnitMaster` where `CustomizeVal` != ''";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$unitList = array();
	while($row = mysqli_fetch_assoc($query)){
		$unitJson = array(
			'custId' => $row["Customize"],
			'custVal' => $row["CustomizeVal"],
			'unit' => explode(",", $row["Unit"])
		);
		array_push($unitList, $unitJson);
	}
	$output = array('categoryList' => $categoryList, 'unitList' => $unitList);
	echo json_encode($output);

}

else if($searchType == "restaurant"){
	$sql = "SELECT *, concat(date_format(`OpenTime`,'%r'),' - ',date_format(`CloseTime`,'%r')) as `OpenCloseTime` FROM `RestaurantMaster` where `IsActive` = 1 order by `Pincode`, `DisplayOrder`";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$restPincode = array();
	$restList = array();
	while($row = mysqli_fetch_assoc($query)){
		$pincode = $row["Pincode"];
		array_push($restPincode, $pincode);
		$approve = $row["Approve"];
		$enable = $row["Enable"];
		$approveTxt = "Pending";
		if($approve == 1) $approveTxt = "Approved";
		else if($approve == 2) $approveTxt = "Rejected";
		$enableTxt = "Pending";
		if($enable == 1) $enableTxt = "Enable";
		else if($enable == 2) $enableTxt = "Disabled";
		$restJson = array(
			'restId' => strval($row["RestId"]), 
			'name' => $row["Name"], 
			'mobile' => $row["Mobile"], 
			'address' => $row["Address"],
			'pincode' => strval($pincode),
			'image' => $row["Image"], 
			'banner' => $row["Banner"], 
			'latLong' => $row["LatLong"], 
			'approve' => $row["Approve"], 
			'approveTxt' => $approveTxt,
			'openTime' => $row["OpenTime"],
			'closeTime' => $row["CloseTime"],
			'openCloseTime' => $row["OpenCloseTime"],
			'status' => $row["Status"],
			'enable' => $row["Enable"],
			'enableTxt' => $enableTxt,
			'displayOrder' => strval($row["DisplayOrder"])
		);
		array_push($restList, $restJson);
	}

	$restPincodeList = array_unique($restPincode);
	$restPincodeList = array_values($restPincodeList);

	$output = array('restPincodeList' => $restPincodeList, 'restList' => $restList);

	echo json_encode($output);
}
else if($searchType == "restaurantItem"){
	$restId = $jsonData->restId;
	$sql = "SELECT DISTINCT c.* FROM ItemMaster i join CategoryMaster c on i.CatId = c.CatId where i.RestId = $restId and i.IsEditable = 0 order by c.Name";
	$result = mysqli_query($conn,$sql);
	$dataList = [];
	while($row = mysqli_fetch_assoc($result)){
		$catId = $row["CatId"];
		$catName = $row["Name"];
		$catImage = $row["Image"];

		$sql1 = "SELECT * FROM ItemMaster where RestId = $restId and CatId = $catId and IsEditable = 1 order by Name";
		$result1 = mysqli_query($conn,$sql1);
		$itemList = array();
		while($row1 = mysqli_fetch_assoc($result1)){
			$itemId = $row1["ItemId"];
			$sql2 = "SELECT * FROM `ItemUnit` where `ItemId` = $itemId";
			$result2 = mysqli_query($conn,$sql2);
			$itemUnitList = array();
			while($row2 = mysqli_fetch_assoc($result2)){
				$unitJson = array(
					'itemUnitId' => $row2["ItemUnitId"],
					'unit' => $row2["Unit"],
					'price' => $row2["Price"] 
				);
				array_push($itemUnitList, $unitJson);
			}
			

			$jsonData = array(
				'itemId' => $itemId,
				'itemName' => $row1["Name"],
				'image' => $row1["Image"],
				'customize' => $row1["Customize"],
				'itemUnitList' => $itemUnitList

			);
			array_push($itemList, $jsonData);
		}

		$dataJson = array(
			'catId' => $catId, 
			'name' => $catName,
			'image' => $catImage,
			'itemSize' => count($itemList),
			'itemList' => $itemList
		);
		array_push($dataList, $dataJson);
	}
	echo json_encode($dataList);
}
else if($searchType == "rider"){
	$sql = "SELECT * FROM `DeliveryBoyMaster` order by `RiderId` desc";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$riderList = array();
	while($row = mysqli_fetch_assoc($query)){
		// $aadharPic = $row["AadharCardPic"] == '' ? '' : urlToBase64($row["AadharCardPic"]);
		$aadharPic = $row["AadharCardPic"] == '' ? '' : $row["AadharCardPic"];
		// $panPic = $row["PanPic"] == '' ? '' : urlToBase64($row["PanPic"]);
		$panPic = $row["PanPic"] == '' ? '' : $row["PanPic"];
		$riderJson = array(
			'riderId' => $row["RiderId"], 
			'name' => $row["Name"], 
			'mobile' => $row["Mobile"],
			'aadharNo' => $row["AadharNo"],
			'aadharPic' => $aadharPic,
			'panNo' => $row["PanNo"],
			'panPic' => $panPic,
			'isActive' => $row["IsActive"]
		);
		array_push($riderList, $riderJson);
	}
	echo json_encode($riderList);
}
else if($searchType == "orders"){
	$filterStartDate = $jsonData->filterStartDate;
	$filterEndDate = $jsonData->filterEndDate;
	$filterSql = "";
	if($filterStartDate != ""){
		 $filterSql .= "and date_format(`OrderDatetime`,'%Y-%m-%d') >= '$filterStartDate'";
	}
	if($filterEndDate != ""){
		$filterSql .= "and date_format(`OrderDatetime`,'%Y-%m-%d') <= '$filterEndDate'";
	}

	if($filterStartDate == "" && $filterEndDate == ""){
		// $filterSql .= "and `OrderDatetime` >= now()-interval 3 month";
	}

	
	$sql = "SELECT mo.OrderId, rm.Name as RestName, cm.Name as CustName, c.Mobile, concat(cm.Name,', ',cm.Address,', ', cm.City,', ',cm.Pincode, ', ', cm.State) as DeliveryAddress, cm.Contact as DeliveryMobile, mo.PaymentMode, mo.Instruction, mo.TotalPrice, mo.DeliveryCharge, mo.GrandTotal, mo.Status, os.StatusTxt, os.StatusColor, date_format(mo.OrderDatetime,'%d-%b-%Y %H:%i') as OrderDatetime, mo.CancellationMsg, mo.RiderId, dm.Name as RiderName, dm.Mobile as RiderMobile, mo.SelfAccept FROM MyOrders mo join CustomerMaster c on mo.CustId=c.CustId join CustomerAddress cm on mo.CustAddId = cm.CustAddId join RestaurantMaster rm on mo.RestId = rm.RestId join OrderStatus os on mo.Status = os.Status left join DeliveryBoyMaster dm on mo.RiderId=dm.RiderId where mo.Status != 7 $filterSql ORDER BY mo.OrderId  DESC";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$orderList = array();
	while($row = mysqli_fetch_assoc($query)){
		$riderId = $row["RiderId"];
		$selfPickUp = $row["SelfAccept"];
		$riderInfo = "";
		if($riderId !=0){
			$riderInfo = $row["RiderName"]." : ".$row["RiderMobile"];
		}
		$orderJson = array(
			'orderId' => strval($row["OrderId"]),
			'restName' => $row["RestName"],
			'custName' => $row["CustName"], 
			'primaryMobile' => $row["Mobile"],
			'deliveryAddress' => $row["DeliveryAddress"],
			'deliveryMobile' => $row["DeliveryMobile"],
			'paymentMode' => $row["PaymentMode"],
			'instruction' => $row["Instruction"],
			'totalPrice' => strval($row["TotalPrice"]), 
			'deliveryCharge' => strval($row["DeliveryCharge"]),
			'grandTotal' => strval($row["GrandTotal"]),
			'status' => $row["Status"],
			'statusTxt' => $row["StatusTxt"],
			'statusColor' => $row["StatusColor"],
			'orderDatetime' => $row["OrderDatetime"],
			'cancellationMsg' => $row["CancellationMsg"],
			'riderId' => $row["RiderId"],
			'riderName' => $row["RiderName"] == null ? "" : $row["RiderName"],
			'riderMobile' => $row["RiderMobile"] == null ? "" : $row["RiderMobile"],
			'riderInfo' => $riderInfo,
			'selfPickUp' => $selfPickUp == 1 ? "Self" : ""
		);
		array_push($orderList, $orderJson);
	}

	$columnName = array();
	$columnData = array();
	// $sql = "SELECT os.StatusTxt as Status, count(mo.Status) as TotalCount FROM OrderStatus os left join MyOrders mo on os.Status=mo.Status $filterSql where os.Status != 7 GROUP by os.Status order by os.Status";

	$sql = "SELECT (case when os.Status=3 then 'Ready' when os.Status=4 then 'Picked Up' else os.StatusTxt end) as Status, count(mo.Status) as TotalCount FROM OrderStatus os left join MyOrders mo on os.Status=mo.Status $filterSql where os.Status not in (0,6,7) GROUP by os.Status order by os.Status";

	$result = mysqli_query($conn,$sql);
	while($row=mysqli_fetch_assoc($result)){
		$status = $row["Status"];
		$totalCount = $row["TotalCount"];
		array_push($columnName, $status);
		array_push($columnData, $totalCount);
	}
	$output = array('orderList' => $orderList, 'columnName' => $columnName, 'columnData' => $columnData);

	echo json_encode($output);
}
else if($searchType == "orderItem"){
	$orderId = $jsonData->orderId;
	// $sql = "SELECT oi.OrderItemId, im.Name as ItemName, cm.Name as CatName, oi.Unit, oi.Quantity, oi.Price FROM MyOrderItems oi join ItemMaster im on oi.ItemId = im.ItemId join CategoryMaster cm on im.CatId = cm.CatId where oi.OrderId = $orderId";
	$sql = "SELECT oi.OrderItemId, im.Name as ItemName, cm.Name as CatName, oi.Unit, oi.Quantity, oi.Price, iu.Price as PerUnitPrice FROM MyOrderItems oi join ItemMaster im on oi.ItemId = im.ItemId join CategoryMaster cm on im.CatId = cm.CatId join ItemUnit iu on oi.ItemId=iu.ItemId and oi.Unit=iu.Unit where oi.OrderId = $orderId and oi.IsDeleted=0";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$orderItemList = array();
	while($row = mysqli_fetch_assoc($query)){
		$orderItemJson = array(
			'orderItemId' => $row["OrderItemId"],
			'itemName' => $row["ItemName"],
			'catName' => $row["CatName"],
			'size' => $row["Unit"],
			'quantity' => $row["Quantity"],
			'newQuantity' => '',
			'price' => $row["Price"],
			'perUnitPrice' => $row["PerUnitPrice"]
		);
		array_push($orderItemList, $orderItemJson);
	}
	echo json_encode($orderItemList);
}
else if($searchType == "customer"){
	$sql = "SELECT `Name` as custName, `Mobile` as `mobile`, date_format(`CreateDate`,'%d-%b-%Y') as `registerDate` FROM `CustomerMaster` where `IsActive`=1 order by `CustId` desc";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$customerList = array();
	$srNo=0;
	while($row = mysqli_fetch_assoc($query)){
		$srNo++;
		$row["srNo"] = strval($srNo);
		array_push($customerList, $row);
	}
	echo json_encode($customerList);
}
else if($searchType == "revenue"){
	$filterFromDate = $jsonData->filterFromDate;
	$filterToDate = $jsonData->filterToDate;
	$filterPincode = $jsonData->filterPincode;

	$filterSql = "";
	if($filterFromDate != ""){
		$filterSql .= "and `OrderDate` >= '$filterFromDate' ";
	}
	else{
		$filterSql .= "and `OrderDate` >= date(now()-interval 3 month) ";
	}
	if($filterToDate != ""){
		$filterSql .= "and `OrderDate` <= '$filterToDate' ";
	}
	if($filterPincode != ""){
		$filterSql .= "and `Pincode`='$filterPincode' ";
	}

	$sql = "SELECT * FROM `V_TotalRevenue` where 1=1 $filterSql order by `OrderDate` desc";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$revenueList = array();
	while($row = mysqli_fetch_assoc($query)){
		$orderDate = $row["OrderDate"];
		$pincode = $row["Pincode"];

		$sql2 = "SELECT `RestName`, `TotalPrice`, `Commission`, `DeliveryCharge`, `Revenue` FROM `V_Revenue` where `OrderDate`='$orderDate' and `Pincode`=$pincode";
		$stmt2 = $conn->prepare($sql2);
		$stmt2->execute();
		$query2 = $stmt2->get_result();
		$subRevenueList = array();
		while($row2 = mysqli_fetch_assoc($query2)){
			array_push($subRevenueList, $row2);
		}

		$row["subRevenueList"] = $subRevenueList;
		array_push($revenueList, $row);
	}
	echo json_encode($revenueList);
}
else if($searchType == "restPincode"){
	$sql = "SELECT DISTINCT `Pincode` FROM `RestaurantMaster` where `Pincode` is not null and `Approve`=1 and `Enable`=1 and `IsActive`=1";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	$pincodeList = array();
	while($row = mysqli_fetch_assoc($query)){
		array_push($pincodeList, $row["Pincode"]);
	}
	echo json_encode($pincodeList);
}
?>

<?php 
function urlToBase64($url){
	$ch = curl_init($url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $base64 = curl_exec($ch);
    curl_close($ch);
    return 'data:image/jpg;base64,'.base64_encode($base64);
}
?>