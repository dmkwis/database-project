<?php
session_start();
?>
<!doctype html>
<head>
	<link rel="stylesheet" href="css_files/user_interface_style.css" type="text/css"/>
	<?php if(isset($_POST['second_user'])) { ?>
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    	<script type="text/javascript">
      	google.charts.load('current', {'packages':['corechart']});
      	google.charts.setOnLoadCallback(drawChart);

      	function drawChart() {

        var data = google.visualization.arrayToDataTable([
		['Task', 'Hours per Day'],
		<?php
		$su = $_POST['second_user'];
		$fu = $_SESSION['username'];
		include_once('connect_to_db.php');
		if($database) {
			include_once('database_commands.php');
			if(exists_username($database, $su) == 0) {
				$_POST['second_user'] = null;
				goto end;
			}
			$fu_amount = sum_transactions($database, $fu, $su);
			$su_amount = sum_transactions($database, $su, $fu);
			echo "['$fu',".$fu_amount." ],";
			echo "['$su',".$su_amount." ]";
			end:
		}
		?>
        ]);

        var options = {
          title: 'Transaction comparison'
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart'));

        chart.draw(data, options);
	}
	</script>
	<?php } ?>
</head>
<body>
	<div id="container">
	<div id="box">
		Perform a transaction<br/>
		<form action="performtransaction.php" method="POST">
			<label for="destination">To whom:</label>
			<input type="text" id="destination" name="destination">
			<label for="amount">Amount:</label>
			<input type="text" id="amount" name="amount">
			<label for="comment">Comment:</label>
			<input type="text" id="comment" name="comment">
			<input type="submit" value="Submit">
		</form>
		Create a group <br/>
		<form action="create_a_group.php" method="POST">
			<label for="groupname">Group name:</label><br/>
			<input type="text" id="groupname" name="groupname"><br/>
			<label for="grouppass">Group password:</label><br/>
			<input type="text" id="grouppass" name="grouppass"><br/>
			<input type="submit" value="Submit"><br/>
		</form>
		Join a group <br/>
		<form action="join_a_group.php" method="POST">
			<label for="groupname">Group name:</label><br/>
               		<input type="text" id="groupname" name="groupname"><br/>
                	<label for="grouppass">Group password:</label><br/>
                	<input type="text" id="grouppass" name="grouppass"><br/>
                	<input type="submit" value="Submit"><br/>
		</form>
		Transaction with a group <br/>
		<form action = "transaction_with_a_group.php" method="POST">
			<label for="groupname">Group name:</label><br/>
                	<input type="text" id="groupname" name="groupname"><br/>
			<label for="amount">Amount:</label><br/>
			<input type="text" id="amount" name="amount"><br/>
                	<input type="submit" value="Submit"><br/>
		</form>
	</div>
	<div id="box">
		<div id="sb">
			Past transactions:
			<?php
				include_once('connect_to_db.php');
				if($database) {
					include_once('database_commands.php');
					$stid = users_transactions($database, $_SESSION['username']);
					if($stid == "FAIL") {
						echo "failure";
					}
					else {
						while(($row = oci_fetch_array($stid, OCI_BOTH)) != false) {
							echo '<div id="info"> FROM: '.$row[1].'<br/> TO: '.$row[2].'<br/> COMMENT: '.$row[3].'<br/>  AMOUNT: '.$row[4].'</br> DATE: '.$row[5].'</div>';
						}
					}
				}
			?>
		</div>
	</div>
	<div id="box">
		Type in user to see piechart of transactions:
		<form action="" method="POST">
			<input type="text" name="second_user">
			<input type="submit" value="Submit">
		</form>
		<?php
				if(isset($_POST['second_user'])) {
					echo '<div id="piechart" style="width: 200px; height: 200px;"></div>';
				}
		?>
	</div>
	<div id="box">
	Usernames of all users:
	<div id="sb">
		<?php
			include_once('connect_to_db.php');
			if($database) {
				include_once('database_commands.php');
				$stid = all_usernames($database);
				if($stid == "FAIL") {
					echo "failed to load usernames";
				}
				else {
					while(($row = oci_fetch_array($stid, OCI_BOTH)) != false) {
						echo '<div id="info">'.$row[0].'</div>';
					}
				}			
			}
		?>
	</div>
	</div>
	<div id="box">
	Your groups:
	<div id="sb">
		<?php
			include_once('connect_to_db.php');
			if($database) {
				include_once('database_commands.php');
				$stid = users_group_and_their_balance($database, $_SESSION['username']);
				if($stid == "FAIL") {
					echo "failure";
				}
				else {
					while(($row = oci_fetch_array($stid, OCI_BOTH)) != false) {
						echo '<div id="info"> GROUP NAME: '.$row[0]."<br/> GROUP BALANCE: ".$row[1].'</div>';
					}	
				}
			}
		?>
	</div>
	</div>
	<div id="box">
	Informations about user:<br/>
	<?php
		include_once('connect_to_db.php');
		if($database) {
			include_once('database_commands.php');
			$info = info_about_user($database, $_SESSION['username']);
			if($info == "FAIL") {
				echo "failure";
			}
			else {
				echo "username: ".$info[0]."<br/> name: ".$info[1]."<br/> surname: ".$info[2]."<br/> current balance: ".$info[4];
			}
		}		
	?>
	</div>
	</div>
</body>
