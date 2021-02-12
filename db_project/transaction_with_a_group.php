<?php
include_once('connect_to_db.php');
if($database) {
	session_start();
	$username = $_SESSION['username'];
	$groupname = $_POST['groupname'];
	$amount = $_POST['amount'];
	$stid = oci_parse($database,
		"BEGIN 
		:result := IS_MEMBER_OF_GROUP(:uname, :gname);
		 END;");
	$result = 0;
	oci_bind_by_name($stid, ':uname', $username);
	oci_bind_by_name($stid, ':gname', $groupname);
	oci_bind_by_name($stid, ':result', $result, -1, SQLT_INT);
	oci_execute($stid);
	if($result == 0) {
		echo "u r not a member of this group";
	}
	else {
		echo "u r a member of this group";
		echo "gonna try perform transaction";
		$stid = oci_parse($database,
			"BEGIN 
			user_group_transaction(:uname, :gname, :am); 
			 END;"
			);
		oci_bind_by_name($stid, ':uname', $username);
        	oci_bind_by_name($stid, ':gname', $groupname);
		oci_bind_by_name($stid, ':am', $amount);
		if(oci_execute($stid)) {
			echo "success";
		}
		else {
			echo "failure";	
		}
	}
}
?>
