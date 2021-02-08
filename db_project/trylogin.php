<?php
include_once('connect_to_db.php');
if($database) {
	include_once('database_commands.php');
	$username = $_POST["username"];
	$password = $_POST["password"];
	if(is_proper_user($database, $username, $password) == 0) {
		header("Location: http://students.mimuw.edu.pl/~dw418484/db_project/");
		exit();
	}
	else {
		session_start();
		$_SESSION['username'] = $username;
		header("Location: http://students.mimuw.edu.pl/~dw418484/db_project/user_interface.php");
		exit();
	}
}
?>
