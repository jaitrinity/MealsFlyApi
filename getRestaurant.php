<?php 
include("dbConfiguration.php");
$sql = "SELECT * FROM `RestaurantMaster` where `IsActive` = 1 order by `DisplayOrder`";
$result = mysqli_query($conn,$sql);
$restList = array();
while($row=mysqli_fetch_assoc($result)){
	$restId = $row["RestId"];
	$sql1 = "SELECT DISTINCT c.* FROM ItemMaster i join CategoryMaster c on i.CatId = c.CatId where i.RestId = $restId and i.IsEnable = 1 and i.IsEditable = 0 order by c.Name limit 0,3";
	$result1 = mysqli_query($conn,$sql1);
	$catList = array();
	while($row1 = mysqli_fetch_assoc($result1)){
		$catName = $row1["Name"];
		array_push($catList, $catName);
	}

	$jsonData = array(
		'restId' => $row["RestId"], 
		'name' => $row["Name"],
		'mobile' => $row["Mobile"],
		'address' => $row["Address"],
		'pincode' => $row["Pincode"],
		'latLong' => $row["LatLong"],
		'image' => $row["Image"],
		'banner' => $row["Banner"],
		'status' => $row["Status"],
		'openTime' => $row["OpenTime"],
		'closeTime' => $row["CloseTime"],
		'catogory' => implode(',', $catList)

	);
	array_push($restList, $jsonData);
}
echo json_encode($restList);
?>