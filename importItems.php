<?php
include("dbConfiguration.php");
$sql = "SELECT * FROM `ImportItems`";
$stmt = $conn->prepare($sql);
$stmt->execute();
$query = $stmt->get_result();
$rowCount = mysqli_num_rows($query);
$successArr = array();
$failArr = array();
while($row = mysqli_fetch_assoc($query)){
	$restName = $row["RestaurantName"];
	$sql1 = "SELECT `RestId` FROM `RestaurantMaster` where `Name`=?";
	$stmt1 = $conn->prepare($sql1);
	$stmt1->bind_param("s", $restName);
	$stmt1->execute();
	$result1 = $stmt1->get_result();
	// $result1 = mysqli_query($conn,$sql1);
	$rowCount1 = mysqli_num_rows($result1);
	if($rowCount1 != 0){
		$defaultImg = "https://www.trinityapplab.in/MealsFly/logo/mealsfly.png";
		$row1 = mysqli_fetch_assoc($result1);
		$restId = $row1["RestId"];
		$category = $row["Category"];

		$sql2 = "SELECT `CatId` FROM `CategoryMaster` where `RestId`=$restId and `Name`='$category' and `IsActive`=1";
		$result2 = mysqli_query($conn,$sql2);
		$rowCount2 = mysqli_num_rows($result2);
		if($rowCount2 == 0){
			$insCat = "INSERT INTO `CategoryMaster`(`RestId`, `Name`, `Image`) VALUES ($restId, '$category','$defaultImg')";
			$insCatStmt = $conn->prepare($insCat);
			if($insCatStmt->execute()){
				$catId = $conn->insert_id;
			}

		}
		else{
			$row2 = mysqli_fetch_assoc($result2);
			$catId = $row2["CatId"];
		}

		$itemName = $row["ItemName"];
		$unitList = array();
		$quantity = $row["Quantity"];
		$unitObj = array('title' => 'Quantity', 'price' => $quantity);
		array_push($unitList, $unitObj);

		$half = $row["Half"];
		$unitObj = array('title' => 'Half', 'price' => $half);
		array_push($unitList, $unitObj);

		$full = $row["Full"];
		$unitObj = array('title' => 'Full', 'price' => $full);
		array_push($unitList, $unitObj);

		$gram200 = $row["200Gram"];
		$unitObj = array('title' => '200Gram', 'price' => $gram200);
		array_push($unitList, $unitObj);

		$gram500 = $row["500Gram"];
		$unitObj = array('title' => '500Gram', 'price' => $gram500);
		array_push($unitList, $unitObj);

		$kg1 = $row["1KG"];
		$unitObj = array('title' => '1KG', 'price' => $kg1);
		array_push($unitList, $unitObj);

		$gram1500 = $row["1.5KG"];
		$unitObj = array('title' => '1.5KG', 'price' => $gram1500);
		array_push($unitList, $unitObj);

		$kg2 = $row["2KG"];
		$unitObj = array('title' => '2KG', 'price' => $kg2);
		array_push($unitList, $unitObj);

		$ml100 = $row["100ML"];
		$unitObj = array('title' => '100ML', 'price' => $ml100);
		array_push($unitList, $unitObj);

		$ml500 = $row["500ML"];
		$unitObj = array('title' => '500ML', 'price' => $ml500);
		array_push($unitList, $unitObj);

		$l1 = $row["1L"];
		$unitObj = array('title' => '1L', 'price' => $l1);
		array_push($unitList, $unitObj);

		$small = $row["Small"];
		$unitObj = array('title' => 'Small', 'price' => $small);
		array_push($unitList, $unitObj);

		$large = $row["Large"];
		$unitObj = array('title' => 'Large', 'price' => $large);
		array_push($unitList, $unitObj);

		$customize = 0;
		if($half !=0 || $full !=0) $customize = 1;
		else if($gram200 !=0 || $gram500 !=0 || $kg1 !=0 || $gram1500 !=0 || $kg2 !=0) $customize=2;
		else if($ml100 !=0 || $ml500 !=0 || $l1 !=0) $customize=3;
		else if($small !=0 || $large !=0) $customize=4;

		$sql3 = "INSERT INTO `ItemMaster`(`RestId`, `CatId`, `Name`, `Image`, `Customize`) VALUES ($restId, $catId, '$itemName', '$defaultImg', $customize)";
		$stmt3 = $conn->prepare($sql3);
		if($stmt3->execute()){
			$itemId = $conn->insert_id;
			for($j=0;$j<count($unitList);$j++){
				$unitObj = $unitList[$j];
				$unit = $unitObj["title"];
				$price = $unitObj["price"];
				if($price !=0){
					$unitSql = "INSERT INTO `ItemUnit`(`ItemId`, `Unit`, `Price`) VALUES ($itemId, '$unit', $price)";
					$unitStmt = $conn->prepare($unitSql);
					if($unitStmt->execute()){

					}
				}	
			}
			array_push($successArr, $itemName);
		}
		else{
			array_push($failArr, $itemName);
		}
	}
}
if(count($successArr) == $rowCount){
	$trunSql = "TRUNCATE `ImportItems`";
	$trunStmt = $conn->prepare($trunSql);
	$trunStmt->execute();
}
$output = array('successArr' => $successArr, 'failArr' => $failArr);
echo json_encode($output);
?>