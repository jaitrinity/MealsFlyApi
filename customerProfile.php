<?php 

require_once 'dbConfiguration.php';
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  exit();
}

?>
<?php
$filePath = "/var/www/trinityapplab.in/html/MealsFly/files/";
$dir = "Profile";
if (!file_exists($filePath.''.$dir)) {
    mkdir($filePath.''.$dir, 0777, true);
}
$t=date("YmdHis");
$target_dir = "files/".$dir."/";

$custId = $_REQUEST["custId"];
$name = $_REQUEST["name"];
$email = $_REQUEST["email"];
$profilePic = $_FILES["profilePic"]["name"];
$code = 0;
$message = "";
if($profilePic != ""){
	// -- deleting old file from server -- //
	// ----------- Start --------------------
	$oldProSql = "SELECT REPLACE(`ProfilePic`,'https://www.trinityapplab.in','/var/www/trinityapplab.in/html') OldProfilePic FROM `CustomerMaster` where `CustId` = $custId";
	$oldProStmt = $conn->prepare($oldProSql);
	$oldProStmt->execute();
	$oldProResult = $oldProStmt->get_result();
	$oldProRow = mysqli_fetch_assoc($oldProResult);
	$oldFile = $oldProRow["OldProfilePic"];
	unlink($oldFile);
	// ----------- End --------------------

	$target_file = $target_dir."".$t.$_FILES["profilePic"]["name"];
	$isWrite = move_uploaded_file($_FILES["profilePic"]["tmp_name"], $target_file); 
	if($isWrite){
		$parts = explode('/', $_SERVER['REQUEST_URI']);
		$link = $_SERVER['HTTP_HOST']; 
		$fileURL = "https://".$link."/".$parts[1]."/".$target_file;
		$sql = "UPDATE `CustomerMaster` set `Name` = '$name', `Email` = '$email', `ProfilePic` = '$fileURL'  where `CustId` = '$custId'";	
		if(mysqli_query($conn,$sql)){
			$code = 200;
			$message = "Save Successfully";
		}
		else{
			$code = 500;
			$message = "Something wrong while save data in db";
		}		
	}
	else{
		$code = 500;
		$message = "Something wrong while write file";
	}
}
else{
	$sql = "UPDATE `CustomerMaster` set `Name` = '$name', `Email` = '$email'  where `CustId` = '$custId'";	
	if(mysqli_query($conn,$sql)){
		$code = 200;
		$message = "Save Successfully";
	}
	else{
		$code = 500;
		$message = "Something wrong while save data in db";
	}
}

if($code == 200){
	$sql = "SELECT * FROM `CustomerMaster` where `CustId` = $custId";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = mysqli_fetch_assoc($result);
	$arr = array('code' => $code, 'message'=> $message, 'name' => $row["Name"], 
		'email' => $row["Email"], 'profilePic' => $row["ProfilePic"]);
	echo json_encode($arr);
}
else{
	$arr = array('code' => $code, 'message'=> $message);
	echo json_encode($arr);
}


?>