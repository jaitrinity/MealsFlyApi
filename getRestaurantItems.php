<?php
include("dbConfiguration.php");
$restId = $_REQUEST["restId"];
$sql = "SELECT DISTINCT c.* FROM ItemMaster i join CategoryMaster c on i.CatId = c.CatId where i.RestId = $restId and i.IsEditable = 0 order by c.Name";
$result = mysqli_query($conn,$sql);
$dataList = [];
while($row = mysqli_fetch_assoc($result)){
	$catId = $row["CatId"];
	$catName = $row["Name"];
	$catImage = $row["Image"];

	$sql1 = "SELECT * FROM ItemMaster where RestId = $restId and CatId = $catId and IsEditable = 0 order by Name";
	$result1 = mysqli_query($conn,$sql1);
	$itemList = array();
	while($row1 = mysqli_fetch_assoc($result1)){
		$itemId = $row1["ItemId"];
		$isEnable = $row1["IsEnable"];
		$sql2 = "SELECT * FROM `ItemUnit` where `ItemId` = $itemId";
		$result2 = mysqli_query($conn,$sql2);
		$unitSize = array();
		while($row2 = mysqli_fetch_assoc($result2)){
			$unitJson = array(
				'sizeId' => $row2["ItemUnitId"],
				'title' => $row2["Unit"],
				'price' => $row2["Price"] 
			);
			array_push($unitSize, $unitJson);
		}


		$jsonData = array(
			'catId' => $catId,
			'itemId' => $itemId,
			'itemName' => $row1["Name"],
			'image' => $row1["Image"],
			'customize' => $row1["Customize"],
			'isEnable' => $isEnable,
			'size' => $unitSize
		);
		array_push($itemList, $jsonData);
	}

	$dataJson = array(
		'catId' => $catId, 
		'name' => $catName,
		'image' => $catImage,
		'itemList' => $itemList
	);
	array_push($dataList, $dataJson);
}
echo json_encode($dataList);
?>