<?php 
include("dbConfiguration.php");
$sql = "SELECT * FROM `Configuration`";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
while($row = mysqli_fetch_assoc($result)){
	$id = $row["Id"];
	$value = $row["Value"];
	if($id == 5){
		$issueList = explode(",", $value);
	}
	else if($id == 6){
		$helpdesk = $value;
	}
}

$output = array('issueList' => $issueList, 'helpdesk' => $helpdesk);
echo json_encode($output);

?>