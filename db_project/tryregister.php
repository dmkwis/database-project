<html>
  <head>
    <title>Transaction manager</title>
  </head>
  <body>
	<?php
		include_once("connect_to_db.php");
		if($database) {
			include_once('database_commands.php');
			$username = $_POST["username"];
			$password = $_POST["password"];
			$name = $_POST["name"];
			$surname = $_POST["surname"];
			$balance = $_POST["balance"];
			echo register_user($database, $username, $name, $surname, $password, $balance);
		}
	?>
  </body>
</html>
