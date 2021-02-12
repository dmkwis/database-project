<?php
	function all_usernames(&$database) {
		$stid = oci_parse($database, "SELECT Username FROM Users");
		if(!oci_execute($stid)) {
			return "FAIL";
		}
		return $stid;
	}
	function info_about_user(&$database, $username) {
		$stid = oci_parse($database, "SELECT * FROM Users WHERE username = :uname");
		oci_bind_by_name($stid, ':uname', $username);
		if(!oci_execute($stid)) {
			return "FAIL";
		}
		$info = oci_fetch_array($stid, OCI_BOTH);
		if($info == false) {
			return "FAIL";
		}
		return $info;
	}
	function users_group_and_their_balance(&$database, $username) {
		$stid = oci_parse($database, 
                                  "SELECT A.group_name, A.balance FROM groups A JOIN group_memberships B ON A.group_name = B.group_name AND B.username = :uname"); 
		oci_bind_by_name($stid, ':uname', $username);
		if(!oci_execute($stid)) {
			return "FAIL";
		}
		return $stid;
	}
	function users_transactions(&$database, $username) {
		$stid = oci_parse($database, "SELECT * FROM done_transactions WHERE source LIKE :uname OR destination LIKE :uname ORDER BY id DESC");
		oci_bind_by_name($stid, ':uname', $username);
		if(!oci_execute($stid)) {
			return "FAIL";
		}
		return $stid;
	}
	function exists_username(&$database, $username) {
		$stid = oci_parse($database, "BEGIN
						:result := USERNAME_EXISTS(:uname);
						END;");
		$res = 0;
		oci_bind_by_name($stid, ':result', $res, -1, SQLT_INT);
		oci_bind_by_name($stid, ':uname', $username);
		oci_execute($stid);
		return $res;
	}
	function sum_transactions(&$database, $source, $destination) {
		$stid = oci_parse($database, "SELECT SUM(amount) FROM done_transactions WHERE source LIKE :s AND destination LIKE :d GROUP BY source");
		oci_bind_by_name($stid, ':s', $source);
		oci_bind_by_name($stid, ':d', $destination);
		$am = 0;
		if(oci_execute($stid)) {
			$val = oci_fetch_array($stid, $OCI_BOTH);
			$am = $am + $val[0];
		}
		return $am;
	}
	function is_proper_user(&$database, $username, $password) {
		$stid = oci_parse($database, "BEGIN
			:result := IS_PROPER_USER(:uname, :pword);
			END;");
		$result = 0;
		oci_bind_by_name($stid, ':uname', $username);
		oci_bind_by_name($stid, ':pword', $password);
		oci_bind_by_name($stid, ':result', $result, -1, SQLT_INT);
		oci_execute($stid);
		return $result;
	}
	function register_user(&$database, $username, $name, $surname, $password, $balance) {
		if(exists_username($database, $username) != 0) {
			return "username already taken";
		}
		else {
			$stid = oci_parse($database,"BEGIN ADD_USER(:uname, :name, :surname, :password, :balance); END;");
			oci_bind_by_name($stid, ':uname', $username);
			oci_bind_by_name($stid, ':name', $name);
			oci_bind_by_name($stid, ':surname', $surname);
			oci_bind_by_name($stid, ':password', $password);
			oci_bind_by_name($stid, ':balance', $balance);
			if(!oci_execute($stid)) {
				return "failed to add user";
			}	
			else {
				return "successfully created an account";
			}
		}
			
	}
?>
