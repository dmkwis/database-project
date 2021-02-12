<?php
$file_with_information = "/home/students/inf/d/dw418484/un_pw_students/db_params.php";
$database = false;
if(file_exists($file_with_information)) {
	include_once($file_with_information);
	$database = oci_connect($db_username, $db_password, $db_server);
}
?>
