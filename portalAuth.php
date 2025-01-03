<?php
include("dbConfiguration.php");
$methodType = $_SERVER['REQUEST_METHOD'];
if($methodType != "POST"){
	return;
}
$json = file_get_contents('php://input');
$jsonData=json_decode($json);

$username = $jsonData->username;
$password = $jsonData->password;

$sql = "SELECT * FROM `Admin` where `Username` = '$username' and `Password` = BINARY('$password')";
$stmt = $conn->prepare($sql);
$stmt->execute();
$query = $stmt->get_result();
if(mysqli_num_rows($query) != 0){
	$row = mysqli_fetch_assoc($query);
	$userInfo = array(
		'userId' => $row["UserId"],
		'name' => $row["Name"],
		'roleId' => $row["RoleId"]
	);
	$output = array('code' => 200, 'message' => 'Success','userInfo' => $userInfo);
	echo json_encode($output);
}
else{
	$output = array('code' => 404, 'message' => 'Invalid credential');
	echo json_encode($output);
}

?>