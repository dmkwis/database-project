<?php
include_once('connect_to_db.php');
echo "trying to add user to group";
if($database) {
	echo "got db connection";
	session_start();
	$groupname = $_POST['groupname'];
	$grouppass = $_POST['grouppass'];
	$username = $_SESSION['username'];
	$stid = oci_parse($database, "BEGIN
                :result := IS_PROPER_GROUP(:gname, :gpass);
                END;");
        $result = 0;
	oci_bind_by_name($stid, ':gname', $groupname);
	oci_bind_by_name($stid, ':gpass', $grouppass);
        oci_bind_by_name($stid, ':result', $result, -1, SQLT_INT);
        oci_execute($stid);

	echo $groupname." ".$grouppass.$result;
	if($result == 0) {
		echo "no group with a given name and password";
	}
	else {
		echo "there is a group with a given name and password! adding u to it!";
		$stid = oci_parse($database, 
                        "BEGIN 
                         ADD_USER_TO_GROUP(:uname, :gname, :gpass); 
                         END;"
			);
		echo $username." ".$groupname." ".$grouppass;
               	oci_bind_by_name($stid, ':uname', $username);
                oci_bind_by_name($stid, ':gname', $groupname);
		oci_bind_by_name($stid, ':gpass', $grouppass);
		if(oci_execute($stid)) {
			echo "success";
		}
		else {
			echo "failure";
		}
	}
}
?>
