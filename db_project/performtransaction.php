<html>
<head>
<title></title>
</head>
<body>
<?php
session_start();
include_once('connect_to_db.php');
if($database) {
	$source = $_SESSION['username'];
	$destination = $_POST['destination'];
	$amount = $_POST['amount'];
	$comment = $_POST['comment'];
	$cur_date = date("d/m/Y");
	$sql = "BEGIN PERFORM_TRANSACTION('$source', '$destination', '$comment', ".$amount.", '$cur_date'); END;";
	echo $sql;
	$transaction_to_execute = oci_parse($database, $sql);
	if(oci_execute($transaction_to_execute)) {
		echo "success";
	}
	else {
		echo "failure";
	}
}
?>
</body>
</html>
