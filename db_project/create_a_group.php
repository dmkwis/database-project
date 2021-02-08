<?php
include_once('connect_to_db.php');

if($database) {
	session_start();
	$groupname = $_POST['groupname'];
	$grouppass = $_POST['grouppass'];
	$username = $_SESSION['username'];
	echo $groupname.$grouppass;
	
	$stid = oci_parse($database, "BEGIN
		:result := GROUP_EXISTS(:gname);
		END;");
	$result = 0;
	oci_bind_by_name($stid, ':gname', $groupname);
	oci_bind_by_name($stid, ':result', $result, -1, SQLT_INT);
	oci_execute($stid);
	if($result > 0) {
		echo "group name already taken";
	}
	else {
		echo "zero groups with that name";
		
		$stid = oci_parse($database, 
			"BEGIN ADD_GROUP(:gname, :gpass); END;");
		oci_bind_by_name($stid, ':gname', $groupname);
		oci_bind_by_name($stid, ':gpass', $grouppass);
		
		if(oci_execute($stid)) {
			echo "success";
			echo " ".$username." ".$groupname." ".$grouppass." ";
			$stid = oci_parse($database, 
			"BEGIN 
			 ADD_USER_TO_GROUP(:uname, :gname, :gpass); 
			 END;"
			);
			oci_bind_by_name($stid, ':uname', $username);
			oci_bind_by_name($stid, ':gname', $groupname);
			oci_bind_by_name($stid, ':gpass', $grouppass);
			if(oci_execute($stid)) {
				echo "success ";
			}
			else {
				echo "failure";
			}
		}
		else {
			echo "failure";
		}
	}
}


?>
